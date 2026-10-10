<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_a_published_exam_with_questions(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $subject = Subject::create([
            'name' => 'Matematika',
            'code' => 'MTK-7',
            'grade_level' => 7,
            'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)->post(route('exams.store'), [
            'subject_id' => $subject->id,
            'title' => 'Bilangan Bulat',
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'status' => 'published',
            'passing_score' => 75,
            'show_score' => 1,
            'questions' => [[
                'prompt' => 'Berapakah 2 + 2?',
                'options' => ['A' => '3', 'B' => '4', 'C' => '5', 'D' => '6'],
                'correct_option' => 'B',
                'points' => 2,
            ]],
        ])->assertRedirect(route('exams.index'));

        $this->assertDatabaseHas('exams', ['title' => 'Bilangan Bulat', 'status' => 'published']);
        $this->assertDatabaseHas('questions', ['prompt' => 'Berapakah 2 + 2?', 'correct_option' => 'B']);
    }

    public function test_student_attempt_is_scored_by_the_server(): void
    {
        $student = User::factory()->create(['role' => 'student', 'grade_level' => 7]);
        $exam = $this->makeExam([
            ['prompt' => 'Soal pertama', 'correct_option' => 'B', 'points' => 1],
            ['prompt' => 'Soal kedua', 'correct_option' => 'C', 'points' => 3],
        ]);

        $this->actingAs($student)->post(route('exams.start', $exam))
            ->assertRedirect();
        $attempt = $student->attempts()->firstOrFail();
        $questions = $exam->questions()->orderBy('position')->get();

        $this->put(route('attempts.answers.update', [$attempt, $questions[0]]), ['selected_option' => 'B']);
        $this->put(route('attempts.answers.update', [$attempt, $questions[1]]), ['selected_option' => 'A']);
        $this->post(route('attempts.submit', $attempt))
            ->assertRedirect(route('attempts.result', $attempt));

        $this->assertSame(25, $attempt->fresh()->score);
        $this->assertSame('submitted', $attempt->fresh()->status);
        $this->assertDatabaseHas('answers', [
            'attempt_id' => $attempt->id,
            'question_id' => $questions[0]->id,
            'is_correct' => true,
        ]);
    }

    public function test_student_cannot_access_another_students_attempt(): void
    {
        $owner = User::factory()->create(['role' => 'student', 'grade_level' => 7]);
        $otherStudent = User::factory()->create(['role' => 'student', 'grade_level' => 7]);
        $exam = $this->makeExam([['prompt' => 'Soal', 'correct_option' => 'A', 'points' => 1]]);
        $attempt = Attempt::create([
            'exam_id' => $exam->id,
            'user_id' => $owner->id,
            'attempt_number' => 1,
            'started_at' => now(),
            'deadline_at' => now()->addMinutes(30),
        ]);

        $this->actingAs($otherStudent)->get(route('attempts.show', $attempt))
            ->assertForbidden();
    }

    public function test_attempt_is_closed_when_its_server_deadline_passes(): void
    {
        $student = User::factory()->create(['role' => 'student', 'grade_level' => 7]);
        $exam = $this->makeExam([['prompt' => 'Soal', 'correct_option' => 'A', 'points' => 1]]);
        $this->actingAs($student)->post(route('exams.start', $exam));
        $attempt = $student->attempts()->firstOrFail();

        $this->travel(2)->minutes();
        $this->put(route('attempts.answers.update', [$attempt, $exam->questions()->first()]), ['selected_option' => 'A'])
            ->assertRedirect(route('attempts.result', $attempt));

        $this->assertSame('submitted', $attempt->fresh()->status);
        $this->assertDatabaseMissing('answers', ['attempt_id' => $attempt->id]);
    }

    public function test_student_cannot_see_or_start_an_exam_for_another_grade(): void
    {
        $student = User::factory()->create(['role' => 'student', 'grade_level' => 7]);
        $exam = $this->makeExam([['prompt' => 'Soal', 'correct_option' => 'A', 'points' => 1]], 8);

        $this->actingAs($student)->get(route('exams.index'))
            ->assertOk()
            ->assertDontSee($exam->title);
        $this->post(route('exams.start', $exam))->assertNotFound();
    }

    public function test_student_can_render_the_exam_and_result_pages(): void
    {
        $student = User::factory()->create(['role' => 'student', 'grade_level' => 7]);
        $exam = $this->makeExam([['prompt' => 'Berapakah 2 + 2?', 'correct_option' => 'B', 'points' => 1]]);

        $this->actingAs($student)->get(route('exams.index'))
            ->assertOk()
            ->assertSee($exam->title);
        $this->post(route('exams.start', $exam))->assertRedirect();
        $attempt = $student->attempts()->firstOrFail();
        $question = $exam->questions()->first();

        $this->get(route('attempts.show', $attempt))
            ->assertOk()
            ->assertSee('Berapakah 2 + 2?');
        $this->put(route('attempts.answers.update', [$attempt, $question]), ['selected_option' => 'B']);
        $this->post(route('attempts.submit', $attempt));
        $this->get(route('attempts.result', $attempt))
            ->assertOk()
            ->assertSee('100');
    }

    public function test_student_can_view_exam_history_summary(): void
    {
        $student = User::factory()->create(['role' => 'student', 'grade_level' => 7]);
        $exam = $this->makeExam([
            ['prompt' => 'Soal pertama', 'correct_option' => 'A', 'points' => 2],
            ['prompt' => 'Soal kedua', 'correct_option' => 'B', 'points' => 3],
        ]);

        $this->actingAs($student)->post(route('exams.start', $exam));
        $attempt = $student->attempts()->firstOrFail();
        $questionOne = $exam->questions()->orderBy('position')->first();
        $questionTwo = $exam->questions()->orderBy('position')->skip(1)->first();

        $this->put(route('attempts.answers.update', [$attempt, $questionOne]), ['selected_option' => 'A']);
        $this->put(route('attempts.answers.update', [$attempt, $questionTwo]), ['selected_option' => 'A']);
        $this->post(route('attempts.submit', $attempt));

        $this->get(route('results.index'))
            ->assertOk()
            ->assertSee('Riwayat ujian')
            ->assertSee($exam->title)
            ->assertSee('50');
    }

    public function test_teacher_can_view_student_leaderboard(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $studentHigh = User::factory()->create(['role' => 'student', 'grade_level' => 7, 'name' => 'Aisyah']);
        $studentLow = User::factory()->create(['role' => 'student', 'grade_level' => 7, 'name' => 'Budi']);

        $subject = Subject::create([
            'name' => 'IPA',
            'code' => 'IPA-7',
            'grade_level' => 7,
            'teacher_id' => $teacher->id,
        ]);

        $exam = Exam::create([
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'title' => 'Ujian Tengah Semester',
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'status' => 'published',
            'passing_score' => 70,
        ]);

        Question::create([
            'exam_id' => $exam->id,
            'position' => 1,
            'prompt' => 'Apa hasil 5 + 5?',
            'options' => ['A' => '9', 'B' => '10', 'C' => '11', 'D' => '12'],
            'correct_option' => 'B',
            'points' => 10,
        ]);

        $attemptHigh = Attempt::create([
            'exam_id' => $exam->id,
            'user_id' => $studentHigh->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinute(),
            'deadline_at' => now()->addMinutes(30),
            'submitted_at' => now(),
            'score' => 100,
            'status' => 'submitted',
        ]);

        $attemptLow = Attempt::create([
            'exam_id' => $exam->id,
            'user_id' => $studentLow->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinute(),
            'deadline_at' => now()->addMinutes(30),
            'submitted_at' => now(),
            'score' => 60,
            'status' => 'submitted',
        ]);

        $this->actingAs($teacher)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSee('Papan peringkat')
            ->assertSee('Aisyah')
            ->assertSee('Budi');

        $this->assertTrue($attemptHigh->score > $attemptLow->score);
    }

    public function test_teacher_can_view_subject_analytics(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $subject = Subject::create([
            'name' => 'Bahasa Indonesia',
            'code' => 'BIN-7',
            'grade_level' => 7,
            'teacher_id' => $teacher->id,
        ]);

        $exam = Exam::create([
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'title' => 'Tes Membaca',
            'duration_minutes' => 25,
            'max_attempts' => 2,
            'status' => 'published',
            'passing_score' => 70,
        ]);

        Question::create([
            'exam_id' => $exam->id,
            'position' => 1,
            'prompt' => 'Siapakah tokoh utama?',
            'options' => ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D'],
            'correct_option' => 'A',
            'points' => 10,
        ]);

        $student = User::factory()->create(['role' => 'student', 'grade_level' => 7, 'name' => 'Citra']);
        Attempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinute(),
            'deadline_at' => now()->addMinutes(30),
            'submitted_at' => now(),
            'score' => 80,
            'status' => 'submitted',
        ]);

        $this->actingAs($teacher)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Analitik ujian')
            ->assertSee('Bahasa Indonesia')
            ->assertSee('80');
    }

    private function makeExam(array $questionData, int $gradeLevel = 7): Exam
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $subject = Subject::create([
            'name' => 'Matematika',
            'code' => 'MTK-'.fake()->unique()->numerify('###'),
            'grade_level' => $gradeLevel,
            'teacher_id' => $teacher->id,
        ]);
        $exam = Exam::create([
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'title' => 'Latihan Matematika',
            'duration_minutes' => 1,
            'max_attempts' => 1,
            'status' => 'published',
            'passing_score' => 75,
        ]);

        foreach ($questionData as $index => $data) {
            Question::create([
                'exam_id' => $exam->id,
                'position' => $index + 1,
                'prompt' => $data['prompt'],
                'options' => ['A' => 'Pilihan A', 'B' => 'Pilihan B', 'C' => 'Pilihan C', 'D' => 'Pilihan D'],
                'correct_option' => $data['correct_option'],
                'points' => $data['points'],
            ]);
        }

        return $exam;
    }
}
