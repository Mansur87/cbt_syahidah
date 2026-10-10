<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_the_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_login_assets_use_https_behind_a_forwarded_https_proxy(): void
    {
        $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'localhost',
            'X-Forwarded-Port' => '8000',
        ])->get('/login')
            ->assertOk()
            ->assertSee('href="https://localhost:8000/css/cbt.css"', escape: false)
            ->assertSee('action="https://localhost:8000/login"', escape: false);
    }

    public function test_user_can_sign_in_and_view_the_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Guru Syahidah',
            'username' => 'guru-syahidah',
            'email' => 'guru@syahidah.sch.id',
            'password' => 'password',
            'role' => 'teacher',
        ]);

        $this->post(route('login.store'), [
            'username' => 'GURU-SYAHIDAH',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Guru Syahidah')
            ->assertSee('Ujian mendatang');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post(route('login.store'), [
            'username' => 'siswa',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_email_address_is_not_accepted_as_a_login_username(): void
    {
        User::factory()->create([
            'username' => 'siswa',
            'email' => 'siswa@syahidah.sch.id',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'username' => 'siswa@syahidah.sch.id',
            'password' => 'password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_user_can_sign_out(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_administrator_can_render_management_pages_and_create_a_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Tambah pengguna');
        $this->get(route('subjects.index'))
            ->assertOk()
            ->assertSee('Tambah mata pelajaran');
        $this->get(route('exams.create'))
            ->assertOk()
            ->assertSee('Buat ujian baru');

        $this->post(route('users.store'), [
            'name' => 'Siswa Kelas Tujuh',
            'username' => 'Siswa-Tujuh',
            'email' => 'siswa7@syahidah.sch.id',
            'role' => 'student',
            'class_name' => '7A',
            'grade_level' => 7,
            'student_number' => '7001',
            'password' => 'kata-sandi-aman',
            'password_confirmation' => 'kata-sandi-aman',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'siswa-tujuh',
            'email' => 'siswa7@syahidah.sch.id',
            'role' => 'student',
            'grade_level' => 7,
        ]);
    }

    public function test_teacher_cannot_open_administrator_user_management(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('users.index'))
            ->assertForbidden();
    }
}
