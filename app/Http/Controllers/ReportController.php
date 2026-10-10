<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        abort_unless($user->isAdmin() || $user->isTeacher(), 403);

        $subjects = Subject::query()
            ->with(['teacher', 'exams.attempts'])
            ->when($user->isTeacher(), fn ($query) => $query->where('teacher_id', $user->id))
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get()
            ->map(function (Subject $subject) {
                $submittedAttempts = $subject->exams
                    ->flatMap(fn ($exam) => $exam->attempts)
                    ->filter(fn ($attempt) => $attempt->status === 'submitted');

                $examSummaries = $subject->exams->map(function ($exam) {
                    $attempts = $exam->attempts->filter(fn ($attempt) => $attempt->status === 'submitted');
                    $scores = $attempts->pluck('score')->filter(fn ($score) => $score !== null);

                    return [
                        'title' => $exam->title,
                        'passing_score' => $exam->passing_score,
                        'attempts' => $attempts->count(),
                        'average_score' => $scores->isNotEmpty() ? (int) round($scores->avg()) : 0,
                        'pass_rate' => $attempts->isNotEmpty()
                            ? (int) round($attempts->filter(fn ($attempt) => ($attempt->score ?? 0) >= $exam->passing_score)->count() / $attempts->count() * 100)
                            : 0,
                    ];
                })->values();

                return [
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'grade_level' => $subject->grade_level,
                    'teacher' => $subject->teacher?->name ?? 'Belum ditetapkan',
                    'exam_count' => $subject->exams->count(),
                    'student_count' => $submittedAttempts->unique('user_id')->count(),
                    'average_score' => $submittedAttempts->isNotEmpty() ? (int) round($submittedAttempts->avg('score')) : 0,
                    'pass_rate' => $submittedAttempts->isNotEmpty()
                        ? (int) round($submittedAttempts->filter(fn ($attempt) => ($attempt->score ?? 0) >= $subject->exams->first()?->passing_score ?? 0)->count() / $submittedAttempts->count() * 100)
                        : 0,
                    'exams' => $examSummaries,
                ];
            })
            ->values();

        return view('reports.index', compact('subjects'));
    }
}
