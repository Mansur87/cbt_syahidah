<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        abort_unless($user->isAdmin() || $user->isTeacher(), 403);

        $subjects = Subject::query()
            ->with('teacher')
            ->withCount('exams')
            ->when($user->isTeacher(), fn ($query) => $query->where('teacher_id', $user->id))
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        return view('subjects.index', [
            'subjects' => $subjects,
            'teachers' => $user->isAdmin() ? User::where('role', 'teacher')->orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isTeacher(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:subjects,code'],
            'grade_level' => ['required', 'integer', Rule::in([7, 8, 9])],
            'teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'teacher')],
        ]);

        $data['teacher_id'] = $user->isTeacher() ? $user->id : ($data['teacher_id'] ?? null);
        Subject::create($data);

        return back()->with('status', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function destroy(Request $request, Subject $subject): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isTeacher(), 403);
        abort_unless($user->isAdmin() || $subject->teacher_id === $user->id, 403);
        abort_if($subject->exams()->exists(), 409, 'Mata pelajaran yang sudah memiliki ujian tidak dapat dihapus.');

        $subject->delete();

        return back()->with('status', 'Mata pelajaran berhasil dihapus.');
    }
}
