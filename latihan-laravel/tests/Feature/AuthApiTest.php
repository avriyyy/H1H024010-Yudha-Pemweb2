<?php

use App\Models\Mahasiswa;
use App\Models\User;
use Database\Seeders\ProgramStudiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ProgramStudiSeeder::class);
});

/**
 * Pengguna dengan peran tertentu dan kata sandi `rahasia123`.
 */
function pengguna(string $peran = 'mahasiswa'): User
{
    return User::factory()->create([
        'peran' => $peran,
        'password' => Hash::make('rahasia123'),
    ]);
}

/**
 * Guard menyimpan user hasil resolusi pertama, jadi harus dilupakan agar
 * permintaan berikutnya benar-benar membaca ulang token dari database.
 */
function lupaGuard(): void
{
    app('auth')->forgetGuards();
}

test('registrasi membuat pengguna dan mengembalikan token', function () {
    $this->postJson('/api/auth/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@student.unsoed.ac.id',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertCreated()
        ->assertJsonPath('sukses', true)
        ->assertJsonPath('data.pengguna.peran', 'mahasiswa')
        ->assertJsonStructure(['data' => ['token']]);

    expect(User::where('email', 'budi@student.unsoed.ac.id')->exists())->toBeTrue();
});

test('registrasi menolak email duplikat', function () {
    pengguna();

    $this->postJson('/api/auth/register', [
        'name' => 'Duplikat',
        'email' => User::first()->email,
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertStatus(422)->assertJsonPath('galat.email.0', 'Email sudah digunakan');
});

test('registrasi menolak kata sandi lemah', function () {
    $this->postJson('/api/auth/register', [
        'name' => 'Lemah',
        'email' => 'lemah@student.unsoed.ac.id',
        'password' => 'abc',
        'password_confirmation' => 'abc',
    ])->assertStatus(422)->assertJsonPath('sukses', false);
});

test('login berhasil dan mengisi kolom terakhir_login', function () {
    $user = pengguna();

    expect($user->terakhir_login)->toBeNull();

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'rahasia123',
    ])->assertOk()
        ->assertJsonPath('sukses', true)
        ->assertJsonPath('data.pengguna.id', $user->id)
        ->assertJsonStructure(['data' => ['token', 'kemampuan']]);

    expect($user->fresh()->terakhir_login)->not->toBeNull();
});

test('login gagal memberi 401 dan tidak mengisi terakhir_login', function () {
    $user = pengguna();

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'kata-sandi-salah',
    ])->assertStatus(401)->assertJsonPath('sukses', false);

    expect($user->fresh()->terakhir_login)->toBeNull();
});

test('profil menampilkan kemampuan token', function () {
    $token = pengguna('admin')->createToken('token-perangkat', ['mahasiswa:baca', 'mahasiswa:tulis'])->plainTextToken;

    $this->withToken($token)->getJson('/api/auth/profil')
        ->assertOk()
        ->assertJsonPath('data.peran', 'admin')
        ->assertJsonPath('data.kemampuan', ['mahasiswa:baca', 'mahasiswa:tulis']);
});

test('rute terlindungi menolak permintaan tanpa token', function () {
    $this->getJson('/api/auth/profil')
        ->assertStatus(401)
        ->assertJsonPath('pesan', 'Token tidak valid atau belum dikirim');

    $this->getJson('/api/mahasiswa')
        ->assertStatus(401)
        ->assertJsonPath('sukses', false);
});

test('token mahasiswa tidak bisa menulis data mahasiswa', function () {
    $token = pengguna()->createToken('token-perangkat', ['mahasiswa:baca'])->plainTextToken;

    $this->withToken($token)->postJson('/api/mahasiswa', [
        'program_studi_id' => 1,
        'nim' => 'H1A000001',
        'nama' => 'Uji Otorisasi',
        'email' => 'uji@student.unsoed.ac.id',
        'angkatan' => 2026,
        'ipk' => 3.5,
    ])->assertStatus(403)
        ->assertJsonPath('sukses', false);
});

test('token admin bisa menulis data mahasiswa', function () {
    $mahasiswa = Mahasiswa::factory()->create(['ipk' => 3.1]);
    $token = pengguna('admin')->createToken('token-perangkat', ['mahasiswa:baca', 'mahasiswa:tulis'])->plainTextToken;

    $this->withToken($token)
        ->putJson("/api/mahasiswa/{$mahasiswa->id}", ['ipk' => 3.9])
        ->assertOk();

    expect((float) $mahasiswa->fresh()->ipk)->toBe(3.9);
});

test('hapus mahasiswa hanya boleh untuk admin', function () {
    $mahasiswa = Mahasiswa::factory()->create();
    $tokenMahasiswa = pengguna()->createToken('token-perangkat', ['mahasiswa:baca', 'mahasiswa:tulis'])->plainTextToken;

    $this->withToken($tokenMahasiswa)
        ->deleteJson("/api/mahasiswa/{$mahasiswa->id}")
        ->assertStatus(403)
        ->assertJsonPath('pesan', 'Hanya pengguna dengan peran admin yang dapat melakukan tindakan ini');

    expect(Mahasiswa::find($mahasiswa->id))->not->toBeNull();

    $tokenAdmin = pengguna('admin')->createToken('token-perangkat', ['mahasiswa:baca', 'mahasiswa:tulis'])->plainTextToken;

    lupaGuard();

    $this->withToken($tokenAdmin)
        ->deleteJson("/api/mahasiswa/{$mahasiswa->id}")
        ->assertOk();

    expect(Mahasiswa::find($mahasiswa->id))->toBeNull();
});

test('logout mencabut token aktif sehingga tidak bisa dipakai lagi', function () {
    $token = pengguna()->createToken('token-perangkat', ['mahasiswa:baca'])->plainTextToken;

    $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

    lupaGuard();

    $this->withToken($token)->getJson('/api/auth/profil')->assertStatus(401);
});

test('logout-semua mencabut seluruh token pengguna', function () {
    $user = pengguna();
    $token = $user->createToken('token-perangkat', ['mahasiswa:baca'])->plainTextToken;

    $this->withToken($token)->postJson('/api/auth/logout-semua')->assertOk();

    expect($user->tokens()->count())->toBe(0);

    lupaGuard();

    $this->withToken($token)->getJson('/api/auth/profil')->assertStatus(401);
});

test('ubah kata sandi butuh verifikasi kata sandi lama', function () {
    $user = pengguna();
    $token = $user->createToken('token-perangkat', ['mahasiswa:baca'])->plainTextToken;

    $this->withToken($token)->putJson('/api/auth/password', [
        'password_lama' => 'kata-sandi-salah',
        'password' => 'katasandibaru123',
        'password_confirmation' => 'katasandibaru123',
    ])->assertStatus(422)->assertJsonPath('pesan', 'Kata sandi lama tidak sesuai');

    $this->withToken($token)->putJson('/api/auth/password', [
        'password_lama' => 'rahasia123',
        'password' => 'katasandibaru123',
        'password_confirmation' => 'katasandibaru123',
    ])->assertOk();

    expect(Hash::check('katasandibaru123', $user->fresh()->password))->toBeTrue();
});

test('login dibatasi lima percobaan per menit', function () {
    $user = pengguna();

    foreach (range(1, 5) as $percobaan) {
        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'kata-sandi-salah',
        ])->assertStatus(401);
    }

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'rahasia123',
    ])->assertStatus(429)->assertJsonPath('sukses', false);
});
