<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $exams = Exam::query()
            ->with(['subject', 'creator'])
            ->withCount('questions')
            ->when($user->isStudent(), fn ($query) => $query->where('status', 'published')
                ->whereHas('subject', fn ($subjectQuery) => $subjectQuery->where('grade_level', $user->grade_level)))
            ->when($user->isTeacher(), fn ($query) => $query->where('created_by', $user->id))
            ->orderByDesc('created_at')
            ->paginate(12);

        $attempts = $user->isStudent()
            ? $user->attempts()->orderByDesc('attempt_number')->get()->unique('exam_id')->keyBy('exam_id')
            : collect();

        return view('exams.index', compact('exams', 'attempts'));
    }

    public function create(): View
    {
        $this->ensureStaff();

        return view('exams.form', [
            'exam' => new Exam,
            'subjects' => $this->availableSubjects(),
            'questionRows' => [null],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureStaff();
        $data = $this->validatedExam($request);

        $exam = DB::transaction(function () use ($request, $data) {
            $exam = Exam::create($this->examAttributes($data, $request));
            $this->replaceQuestions($exam, $data['questions']);

            return $exam;
        });

        return redirect()->route('exams.index')->with('status', 'Ujian berhasil dibuat.');
    }

    public function edit(Exam $exam): View
    {
        $this->ensureOwner($exam);
        abort_if($exam->attempts()->exists(), 409, 'Ujian tidak dapat diubah setelah ada siswa yang memulai.');

        return view('exams.form', [
            'exam' => $exam->load('questions'),
            'subjects' => $this->availableSubjects(),
            'questionRows' => $exam->questions,
        ]);
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $this->ensureOwner($exam);
        abort_if($exam->attempts()->exists(), 409, 'Ujian tidak dapat diubah setelah ada siswa yang memulai.');
        $data = $this->validatedExam($request);

        DB::transaction(function () use ($request, $exam, $data) {
            $exam->update($this->examAttributes($data, $request));
            $exam->questions()->delete();
            $this->replaceQuestions($exam, $data['questions']);
        });

        return redirect()->route('exams.index')->with('status', 'Perubahan ujian berhasil disimpan.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->ensureOwner($exam);
        abort_if($exam->attempts()->exists(), 409, 'Ujian tidak dapat dihapus setelah ada siswa yang memulai.');
        $exam->delete();

        return back()->with('status', 'Ujian berhasil dihapus.');
    }

    public function start(Exam $exam): RedirectResponse
    {
        $user = request()->user();
        abort_unless($user->isStudent(), 403);
        abort_unless($exam->status === 'published', 404);
        abort_unless($exam->subject->grade_level === $user->grade_level, 404);
        abort_if($exam->starts_at && $exam->starts_at->isFuture(), 403, 'Ujian belum dimulai.');
        abort_if($exam->ends_at && $exam->ends_at->isPast(), 403, 'Waktu ujian telah berakhir.');

        $activeAttempt = $exam->attempts()
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if ($activeAttempt) {
            if ($activeAttempt->deadline_at->isPast()) {
                $this->finalizeAttempt($activeAttempt);
            } else {
                return redirect()->route('attempts.show', $activeAttempt);
            }
        }

        $attemptCount = $exam->attempts()->where('user_id', $user->id)->count();
        abort_if($attemptCount >= $exam->max_attempts, 403, 'Kesempatan mengerjakan ujian sudah digunakan.');

        $startedAt = now();
        $attempt = $exam->attempts()->create([
            'user_id' => $user->id,
            'attempt_number' => $attemptCount + 1,
            'started_at' => $startedAt,
            'deadline_at' => min($startedAt->copy()->addMinutes($exam->duration_minutes), $exam->ends_at ?? $startedAt->copy()->addMinutes($exam->duration_minutes)),
        ]);

        return redirect()->route('attempts.show', $attempt);
    }

    public function take(Attempt $attempt): View|RedirectResponse
    {
        $this->ensureAttemptOwner($attempt);
        if ($attempt->status === 'submitted') {
            return redirect()->route('attempts.result', $attempt);
        }
        if ($attempt->deadline_at->isPast()) {
            $this->finalizeAttempt($attempt);

            return redirect()->route('attempts.result', $attempt);
        }

        $attempt->load(['exam.subject', 'answers']);
        $questions = $attempt->exam->questions;
        if ($attempt->exam->shuffle_questions) {
            $questions = $questions->shuffle()->values();
        }

        return view('attempts.take', [
            'attempt' => $attempt,
            'questions' => $questions,
            'savedAnswers' => $attempt->answers->keyBy('question_id'),
        ]);
    }

    public function saveAnswer(Request $request, Attempt $attempt, Question $question): RedirectResponse
    {
        $this->ensureAttemptOwner($attempt);
        abort_unless($attempt->status === 'in_progress', 409);
        abort_unless($question->exam_id === $attempt->exam_id, 404);
        if ($attempt->deadline_at->isPast()) {
            $this->finalizeAttempt($attempt);

            return redirect()->route('attempts.result', $attempt);
        }

        $data = $request->validate([
            'selected_option' => ['nullable', 'string', Rule::in(array_keys($question->options))],
        ]);

        Answer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $question->id],
            ['selected_option' => $data['selected_option'] ?? null],
        );

        return back()->with('answer_saved', $question->id);
    }

    public function submit(Attempt $attempt): RedirectResponse
    {
        $this->ensureAttemptOwner($attempt);
        $this->finalizeAttempt($attempt);

        return redirect()->route('attempts.result', $attempt);
    }

    public function result(Attempt $attempt): View|RedirectResponse
    {
        $this->ensureAttemptOwner($attempt);
        if ($attempt->status !== 'submitted' && $attempt->deadline_at->isPast()) {
            $this->finalizeAttempt($attempt);
        }
        abort_unless($attempt->status === 'submitted', 409, 'Ujian belum dikumpulkan.');

        $attempt->load('exam.subject');

        return view('attempts.result', compact('attempt'));
    }

    private function validatedExam(Request $request): array
    {
        $data = $request->validate([
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'title' => ['required', 'string', 'max:160'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:300'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:5'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'show_score' => ['nullable', 'boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.prompt' => ['required', 'string', 'max:10000'],
            'questions.*.options' => ['required', 'array:A,B,C,D'],
            'questions.*.options.A' => ['required', 'string', 'max:500'],
            'questions.*.options.B' => ['required', 'string', 'max:500'],
            'questions.*.options.C' => ['required', 'string', 'max:500'],
            'questions.*.options.D' => ['required', 'string', 'max:500'],
            'questions.*.correct_option' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
            'questions.*.points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        if (($data['starts_at'] ?? null) && ($data['ends_at'] ?? null) && $data['ends_at'] <= $data['starts_at']) {
            throw ValidationException::withMessages(['ends_at' => 'Waktu selesai harus setelah waktu mulai.']);
        }

        abort_unless($this->availableSubjects()->contains('id', (int) $data['subject_id']), 403);

        return $data;
    }

    private function examAttributes(array $data, Request $request): array
    {
        return [
            'subject_id' => $data['subject_id'],
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'instructions' => $data['instructions'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            'max_attempts' => $data['max_attempts'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'status' => $data['status'],
            'passing_score' => $data['passing_score'],
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'show_score' => $request->boolean('show_score'),
        ];
    }

    private function replaceQuestions(Exam $exam, array $questions): void
    {
        foreach ($questions as $index => $question) {
            $exam->questions()->create([
                'position' => $index + 1,
                'prompt' => $question['prompt'],
                'options' => $question['options'],
                'correct_option' => $question['correct_option'],
                'points' => $question['points'],
            ]);
        }
    }

    private function availableSubjects()
    {
        $user = request()->user();

        return Subject::query()
            ->when($user->isTeacher(), fn ($query) => $query->where(fn ($inner) => $inner->where('teacher_id', $user->id)->orWhereNull('teacher_id')))
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }

    private function ensureStaff(): void
    {
        abort_unless(request()->user()->isAdmin() || request()->user()->isTeacher(), 403);
    }

    private function ensureOwner(Exam $exam): void
    {
        $this->ensureStaff();
        abort_unless(request()->user()->isAdmin() || $exam->created_by === request()->user()->id, 403);
    }

    private function ensureAttemptOwner(Attempt $attempt): void
    {
        abort_unless(request()->user()->isStudent() && $attempt->user_id === request()->user()->id, 403);
    }

    private function finalizeAttempt(Attempt $attempt): void
    {
        DB::transaction(function () use ($attempt) {
            $attempt = Attempt::query()->lockForUpdate()->findOrFail($attempt->id);
            if ($attempt->status === 'submitted') {
                return;
            }

            $questions = $attempt->exam->questions()->get();
            $answers = $attempt->answers()->get()->keyBy('question_id');
            $possiblePoints = $questions->sum('points');
            $earnedPoints = 0;

            foreach ($questions as $question) {
                $answer = $answers->get($question->id);
                $correct = $answer !== null && $answer->selected_option === $question->correct_option;
                if ($answer) {
                    $answer->update(['is_correct' => $correct]);
                }
                if ($correct) {
                    $earnedPoints += $question->points;
                }
            }

            $attempt->update([
                'score' => $possiblePoints > 0 ? (int) round($earnedPoints / $possiblePoints * 100) : 0,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        });
    }
}
