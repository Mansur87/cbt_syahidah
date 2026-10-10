<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $publishedExams = Exam::query()->where('status', 'published');

        $stats = match ($user->role) {
            'admin' => [
                ['label' => 'Siswa terdaftar', 'value' => User::where('role', 'student')->count(), 'tone' => 'mint'],
                ['label' => 'Guru aktif', 'value' => User::where('role', 'teacher')->count(), 'tone' => 'peach'],
                ['label' => 'Mata pelajaran', 'value' => Subject::count(), 'tone' => 'blue'],
                ['label' => 'Ujian diterbitkan', 'value' => (clone $publishedExams)->count(), 'tone' => 'yellow'],
            ],
            'teacher' => [
                ['label' => 'Mata pelajaran saya', 'value' => Subject::where('teacher_id', $user->id)->count(), 'tone' => 'mint'],
                ['label' => 'Total ujian', 'value' => Exam::where('created_by', $user->id)->count(), 'tone' => 'peach'],
                ['label' => 'Ujian diterbitkan', 'value' => (clone $publishedExams)->where('created_by', $user->id)->count(), 'tone' => 'yellow'],
            ],
            default => [
                ['label' => 'Ujian tersedia', 'value' => (clone $publishedExams)->count(), 'tone' => 'mint'],
                ['label' => 'Mata pelajaran', 'value' => Subject::count(), 'tone' => 'blue'],
            ],
        };

        $upcomingExams = $publishedExams
            ->with('subject')
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderByRaw('starts_at IS NULL, starts_at')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'stats' => $stats,
            'upcomingExams' => $upcomingExams,
            'roleLabel' => match ($user->role) {
                'admin' => 'Administrator',
                'teacher' => 'Guru',
                default => 'Siswa',
            },
        ]);
    }
}
