<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()->isAdmin(), 403);

        return view('users.index', ['users' => User::query()->orderBy('name')->paginate(15)]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $request->merge([
            'username' => Str::lower($request->string('username')->trim()->toString()),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
            'student_number' => ['nullable', 'string', 'max:30', 'unique:users,student_number'],
            'class_name' => ['nullable', 'string', 'max:30'],
            'grade_level' => ['required_if:role,student', 'nullable', 'integer', Rule::in([7, 8, 9])],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return back()->with('status', 'Akun berhasil dibuat.');
    }
}
