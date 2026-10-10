<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        $query = Attempt::query()
            ->with(['exam.subject', 'user'])
            ->where('status', 'submitted')
            ->when($user->isStudent(), fn ($query) => $query->where('user_id', $user->id))
            ->when($user->isTeacher(), fn ($query) => $query->whereHas('exam', fn ($examQuery) => $examQuery->where('created_by', $user->id)))
            ->orderByDesc('submitted_at');

        $attempts = $query->paginate(12);

        $passCount = collect($attempts->items())
            ->filter(fn ($attempt) => $attempt->score >= ($attempt->exam->passing_score ?? 0))
            ->count();

        $summary = [
            'total' => $attempts->total(),
            'average_score' => (int) round($query->clone()->avg('score') ?? 0),
            'best_score' => (int) ($query->clone()->max('score') ?? 0),
            'pass_rate' => $attempts->total() > 0 ? (int) round($passCount / $attempts->total() * 100) : 0,
        ];

        return view('results.index', compact('attempts', 'summary'));
    }
}
