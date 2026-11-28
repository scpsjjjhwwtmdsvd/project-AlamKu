<?php

namespace App\Http\Controllers;

use App\Models\KelompokTani;
use App\Models\User;
use App\Support\RoleHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if ($user = Auth::user()) {
            return redirect()->route(RoleHome::routeName($user->role));
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'alamat email',
            'password' => 'kata sandi',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak cocok.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route(RoleHome::routeName($request->user()->role)))
            ->with('success', 'Selamat datang kembali di AlamKu.');
    }

    public function showRegister(Request $request): View|RedirectResponse
    {
        if ($user = Auth::user()) {
            return redirect()->route(RoleHome::routeName($user->role));
        }

        $role = $request->query('role') === 'mitra' ? 'mitra' : 'donatur';

        return view('auth.register', compact('role'));
    }

    public function register(Request $request): RedirectResponse
    {
        $role = (string) $request->input('role', 'donatur');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('pengguna', 'email')],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['donatur', 'mitra'])],
        ];

        if ($role === 'mitra') {
            $rules += [
                'nama_kelompok' => ['required', 'string', 'max:255'],
                'provinsi' => ['required', 'string', 'max:255'],
                'kabupaten' => ['required', 'string', 'max:255'],
                'desa' => ['required', 'string', 'max:255'],
                'nomor_legalitas' => ['nullable', 'string', 'max:255'],
                'deskripsi' => ['nullable', 'string'],
            ];
        }

        $data = $request->validate($rules, [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka.',
            'phone.max' => 'Nomor telepon maksimal 20 digit.',
        ], [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'phone' => 'nomor telepon',
            'password' => 'kata sandi',
            'role' => 'peran',
            'nama_kelompok' => 'nama kelompok tani',
            'provinsi' => 'provinsi',
            'kabupaten' => 'kabupaten',
            'desa' => 'desa',
            'nomor_legalitas' => 'nomor legalitas',
            'deskripsi' => 'deskripsi',
        ]);

        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => $data['role'],
            ]);

            if ($data['role'] === 'mitra') {
                KelompokTani::create([
                    'pengguna_id' => $user->id,
                    'nama_kelompok' => $data['nama_kelompok'],
                    'provinsi' => $data['provinsi'],
                    'kabupaten' => $data['kabupaten'],
                    'desa' => $data['desa'],
                    'nomor_legalitas' => $data['nomor_legalitas'] ?? null,
                    'terverifikasi' => false,
                    'deskripsi' => $data['deskripsi'] ?? null,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route(RoleHome::routeName($user->role))
            ->with('success', 'Akun berhasil dibuat. Selamat bergabung di AlamKu.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah keluar dari akun.');
    }
}
