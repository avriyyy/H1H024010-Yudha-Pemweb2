<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MahasiswaController;
use App\Http\Controllers\Api\MatakuliahController;
use App\Http\Controllers\Api\ProgramStudiController;
use Illuminate\Support\Facades\Route;

Route::get('/status', function () {
    return response()->json([
        'sukses' => true,
        'pesan' => 'API Pemweb II aktif',
        'waktu' => now()->toIso8601String(),
    ]);
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/profil', [AuthController::class, 'profil']);
    Route::put('/auth/password', [AuthController::class, 'ubahPassword']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-semua', [AuthController::class, 'logoutSemua']);

    // Kemampuan `mahasiswa:baca` melekat pada seluruh rute yang hanya membaca.
    Route::middleware('ability:mahasiswa:baca')->group(function () {
        Route::apiResource('mahasiswa', MahasiswaController::class)->only(['index', 'show']);
        Route::apiResource('matakuliah', MatakuliahController::class)->only(['index', 'show']);
        Route::get('program-studi/{program_studi}/mahasiswa', [ProgramStudiController::class, 'mahasiswa'])
            ->name('api.program-studi.mahasiswa');
    });

    // Kemampuan `mahasiswa:tulis` melekat pada seluruh rute yang mengubah data.
    Route::middleware('ability:mahasiswa:tulis')->group(function () {
        Route::apiResource('mahasiswa', MahasiswaController::class)->only(['store', 'update']);
        Route::apiResource('matakuliah', MatakuliahController::class)->only(['store', 'update']);
    });

    // Penghapusan butuh kemampuan tulis sekaligus peran admin.
    Route::delete('/mahasiswa/{mahasiswa}', [MahasiswaController::class, 'destroy'])
        ->middleware(['ability:mahasiswa:tulis', 'peran:admin']);
    Route::delete('/matakuliah/{matakuliah}', [MatakuliahController::class, 'destroy'])
        ->middleware(['ability:mahasiswa:tulis', 'peran:admin']);
});
