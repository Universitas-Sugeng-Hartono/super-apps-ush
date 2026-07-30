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
            $role = User::normalizeRole(Auth::user()->role);
            if (in_array($role, ['admin', 'superadmin', 'masteradmin'], true)) {
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
}