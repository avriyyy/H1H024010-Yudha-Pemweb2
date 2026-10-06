<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:100', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            ],
            [
                'email.unique' => 'Email sudah digunakan',
                'email.email' => 'Format email tidak valid',
                'name.required' => 'Nama wajib diisi',
                'password.confirmed' => 'Konfirmasi kata sandi tidak cocok',
            ],
        );

        $data['password'] = Hash::make($data['password']);
        $data['peran'] = 'mahasiswa';

        $pengguna = User::create($data);
        $token = $pengguna->createToken('token-perangkat', self::kemampuanUntuk($pengguna->peran))->plainTextToken;

        return response()->json([
            'sukses' => true,
            'pesan' => 'Registrasi berhasil',
            'data' => [
                'pengguna' => [
                    'id' => $pengguna->id,
                    'nama' => $pengguna->name,
                    'email' => $pengguna->email,
                    'peran' => $pengguna->peran,
                ],
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $pengguna = User::where('email', $data['email'])->first();

        if ($pengguna === null || Hash::check($data['password'], $pengguna->password) === false) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Email atau kata sandi tidak sesuai',
            ], 401);
        }

        $kemampuan = self::kemampuanUntuk($pengguna->peran);
        $token = $pengguna->createToken('token-perangkat', $kemampuan)->plainTextToken;

        $pengguna->forceFill(['terakhir_login' => now()])->save();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Login berhasil',
            'data' => [
                'pengguna' => [
                    'id' => $pengguna->id,
                    'nama' => $pengguna->name,
                    'email' => $pengguna->email,
                    'peran' => $pengguna->peran,
                    'terakhir_login' => $pengguna->terakhir_login?->toIso8601String(),
                ],
                'kemampuan' => $kemampuan,
                'token' => $token,
            ],
        ]);
    }

    public function profil(Request $request): JsonResponse
    {
        $pengguna = $request->user();
        $token = $pengguna->currentAccessToken();

        return response()->json([
            'sukses' => true,
            'data' => [
                'id' => $pengguna->id,
                'nama' => $pengguna->name,
                'email' => $pengguna->email,
                'peran' => $pengguna->peran,
                'terakhir_login' => $pengguna->terakhir_login?->toIso8601String(),
                // Token sementara (sesi SPA) tidak menyimpan abilities; ia menerima semua.
                'kemampuan' => $token instanceof PersonalAccessToken ? $token->abilities : ['*'],
            ],
        ]);
    }

    public function ubahPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'password_lama' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $pengguna = $request->user();

        if (Hash::check($data['password_lama'], $pengguna->password) === false) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Kata sandi lama tidak sesuai',
            ], 422);
        }

        $pengguna->update(['password' => Hash::make($data['password'])]);

        // Seluruh token lama dicabut karena bisa saja bocor saat kata sandi diganti.
        $pengguna->tokens()->delete();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Kata sandi berhasil diubah, silakan masuk kembali',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Logout berhasil',
        ]);
    }

    public function logoutSemua(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Seluruh sesi perangkat telah diakhiri',
        ]);
    }

    /**
     * Kemampuan token yang diberikan sesuai peran pengguna.
     *
     * @return list<string>
     */
    protected static function kemampuanUntuk(string $peran): array
    {
        return $peran === 'admin'
            ? ['mahasiswa:baca', 'mahasiswa:tulis']
            : ['mahasiswa:baca'];
    }
}
