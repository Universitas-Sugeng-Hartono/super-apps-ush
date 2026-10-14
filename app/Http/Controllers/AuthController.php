<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Halaman login utama
     */
    public function index()
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        if (Auth::check()) {
            $authUser = Auth::user();
            if ($authUser->canSwitchDashboardRole()) {
                $target = $authUser->activeDashboardRole() === 'akademik'
                    ? route('akademik.dashboard')
                    : route('admin.dashboard');

                return redirect()->to($target);
            }

            $role = User::normalizeRole($authUser->role);
            if ($role === 'kemahasiswaan') {
                return redirect()->route('kemahasiswaan.dashboard');
            }
            if ($role === 'akademik') {
                return redirect()->route('akademik.dashboard');
            }
            if (in_array($role, ['admin', 'superadmin', 'masteradmin', 'keuangan'], true)) {
                return redirect()->route('admin.dashboard');
            }
        }

        return view('auth.login');
    }

    /**
     * Login untuk Dosen/Admin
     */
    public function loginDosen(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            $request->session()->put([
                'user_id'         => encrypt($user->id),
                'user_name'       => $user->name ?? null,
                'user_email'      => $user->email ?? null,
                'user_role'       => $user->role ?? null,
                'user_photo'      => $user->photo ?? '0',
                'user_prodi'      => $user->program_studi ?? 'Bisnis Digital',
            ]);

            $request->session()->forget('active_dashboard_role');

            $role = User::normalizeRole($user->role);
            if ($role === 'kemahasiswaan') {
                return redirect()->intended(route('kemahasiswaan.dashboard'));
            }
            if ($role === 'akademik') {
                return redirect()->intended(route('akademik.dashboard'));
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()
            ->withErrors(['email' => 'Email atau Password tidak sesuai.'])
            ->onlyInput('email');
    }

    /**
     * Login untuk Mahasiswa
     */
    public function loginMahasiswa(Request $request)
    {
        $credentials = $request->validate([
            'nim'      => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('student')->attempt($credentials)) {
            $request->session()->regenerate();

            $student = Auth::guard('student')->user();
            $request->session()->put([
                'student_id'    => encrypt($student->id),
                'path_pic'      => $student->foto ?? '0',
                'student_nama'  => $student->nama_lengkap ?? null,
                'nim'           => $student->nim ?? null,
                'student_prodi' => $student->program_studi ?? null,
            ]);

            return redirect()->intended(route('student.dashboard'));
        }

        return back()
            ->with('error', 'NIM atau Password salah.')
            ->withInput();
    }

    /**
     * Logout user (dosen/admin/mahasiswa)
     */
    public function logout(Request $request)
    {
        if (Auth::guard('student')->check()) {
            Auth::guard('student')->logout();
        } else {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Pindah dashboard untuk akun yang memiliki lebih dari satu role tampilan.
     */
    public function switchDashboardRole(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->canSwitchDashboardRole()) {
            abort(403);
        }

        $role = User::normalizeRole($request->input('role'));
        if (!$role || !in_array($role, $user->dashboardRoles(), true)) {
            abort(422);
        }

        $request->session()->put('active_dashboard_role', $role);

        $target = match ($role) {
            'akademik' => route('akademik.dashboard'),
            'kemahasiswaan' => route('kemahasiswaan.dashboard'),
            default => route('admin.dashboard'),
        };

        return redirect()->to($target);
    }
}