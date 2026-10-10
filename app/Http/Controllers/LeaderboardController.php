<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        $ranked = Attempt::query()
            ->with(['user', 'exam.subject'])
            ->where('status', 'submitted')
            ->when($user->isTeacher(), fn ($query) => $query->whereHas('exam', fn ($examQuery) => $examQuery->where('created_by', $user->id)))
            ->when($user->isStudent(), fn ($query) => $query->whereHas('user', fn ($studentQuery) => $studentQuery->where('grade_level', $user->grade_level)))
            ->get()
            ->groupBy('user_id')
            ->map(function ($attempts) {
                return [
                    'user' => $attempts->first()->user,
                    'average_score' => (int) round($attempts->avg('score')),
                    'best_score' => (int) $attempts->max('score'),
                    'total_attempts' => $attempts->count(),
                    'last_submission' => $attempts->max('submitted_at'),
                ];
            })
            ->sortByDesc('average_score')
            ->values()
            ->all();

        $myRank = null;
        if ($user->isStudent()) {
            $myRank = collect($ranked)->search(fn ($entry) => $entry['user']->id === $user->id) + 1;
        }

        $summary = [
            'students' => count($ranked),
            'highest_score' => collect($ranked)->max('average_score') ?? 0,
            'average_score' => $ranked === [] ? 0 : (int) round(collect($ranked)->avg('average_score')),
            'my_rank' => $myRank,
        ];

        return view('leaderboard.index', compact('ranked', 'summary'));
    }
}
