<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MahasiswaResource;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProgramStudiController extends Controller
{
    /**
     * Daftar mahasiswa pada satu program studi (bersama pagination).
     */
    public function mahasiswa(Request $request, ProgramStudi $program_studi): AnonymousResourceCollection
    {
        $kueri = $program_studi->mahasiswa()->getQuery()->with('programStudi');

        if ($request->filled('cari')) {
            $kataKunci = $request->query('cari');
            $kueri->where(function ($sub) use ($kataKunci) {
                $sub->where('nama', 'like', '%'.$kataKunci.'%')
                    ->orWhere('nim', 'like', '%'.$kataKunci.'%');
            });
        }

        if ($request->boolean('aktif')) {
            $kueri->where('aktif', true);
        }

        $urutan = (string) $request->query('urut', 'nama');
        $arah = $request->query('arah', 'asc');
        $kolomDiizinkan = ['nama', 'nim', 'angkatan', 'ipk'];

        // Kolom di luar daftar diizinkan kembali ke default, bukan diabaikan,
        // supaya urutan hasil tetap stabil.
        if (! in_array($urutan, $kolomDiizinkan, true)) {
            $urutan = 'nama';
        }

        $kueri->orderBy($urutan, $arah === 'desc' ? 'desc' : 'asc');

        $perHalaman = min($request->integer('per_halaman', 10), 100);

        return MahasiswaResource::collection($kueri->paginate($perHalaman));
    }
}
