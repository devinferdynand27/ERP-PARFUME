<?php

namespace App\Http\Controllers;

use App\Repositories\AdminRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected AdminRepository $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * Tampilkan halaman login.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi pengguna.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
        ]);

        // Cek apakah akun aktif sebelum mencoba login
        $admin = $this->adminRepository->findByEmail($credentials['email']);
        if ($admin && (int) $admin->aktif !== 1) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda tidak aktif. Silakan hubungi administrator.'],
            ]);
        }

        // Jalankan proses autentikasi (menggunakan Laravel Auth Session Guard + AdminUserProvider)
        if (Auth::attempt($credentials)) {
            // Regenerasi session ID untuk menghindari session fixation attack (Security Best Practice)
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'redirect' => route('dashboard'),
            ]);
        }

        // Jika autentikasi gagal
        throw ValidationException::withMessages([
            'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
        ]);
    }

    /**
     * Proses logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate session (Security Best Practice)
        $request->session()->invalidate();

        // Regenerasi CSRF token (Security Best Practice)
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil.',
            ]);
        }

        return redirect()->route('login');
    }
}
