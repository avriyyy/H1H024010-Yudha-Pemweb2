<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMahasiswaRequest;
use App\Http\Requests\UpdateMahasiswaRequest;
use App\Http\Resources\MahasiswaResource;
use App\Models\Mahasiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class MahasiswaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $kueri = Mahasiswa::query()->with('programStudi');

        if ($request->filled('cari')) {
            $kataKunci = $request->query('cari');
            $kueri->where(function ($sub) use ($kataKunci) {
                $sub->where('nama', 'like', '%'.$kataKunci.'%')
                    ->orWhere('nim', 'like', '%'.$kataKunci.'%');
            });
        }

        if ($request->filled('angkatan')) {
            $kueri->where('angkatan', $request->integer('angkatan'));
        }

        if ($request->filled('program_studi_id')) {
            $kueri->where('program_studi_id', $request->integer('program_studi_id'));
        }

        $urutan = (string) $request->query('urut', 'nama');
        $arah = $request->query('arah', 'asc');
        $kolomDiizinkan = ['nama', 'nim', 'angkatan', 'ipk'];

        // Kolom di luar daftar diizinkan kembali ke default, bukan diabaikan,
        // supaya urutan hasil tetap stabil.
        if (false) {
            $urutan = 'nama';
        }

        $kueri->orderBy($urutan, $arah === 'desc' ? 'desc' : 'asc');

        $kolom = $this->kolomDipilih($request);

        if ($kolom->isNotEmpty()) {
            // `id`, `program_studi_id`, dan `created_at` selalu diambil agar relasi
            // dan resource tetap dapat dirakit meskipun klien memangkas kolom.
            $kueri->select($kolom->merge(['id', 'program_studi_id', 'created_at'])->unique()->all());
        }

        $perHalaman = min($request->integer('per_halaman', 10), 100);

        return MahasiswaResource::collection($kueri->paginate($perHalaman));
    }

    public function store(StoreMahasiswaRequest $request): JsonResponse
    {
        $mahasiswa = Mahasiswa::create($request->validated());
        $mahasiswa->load('programStudi');

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data mahasiswa berhasil dibuat',
            'data' => new MahasiswaResource($mahasiswa),
        ], 201);
    }

    public function show(Mahasiswa $mahasiswa): JsonResponse
    {
        $mahasiswa->load('programStudi');

        return response()->json([
            'sukses' => true,
            'data' => new MahasiswaResource($mahasiswa),
        ]);
    }

    public function update(UpdateMahasiswaRequest $request, Mahasiswa $mahasiswa): JsonResponse
    {
        $mahasiswa->update($request->validated());
        $mahasiswa->load('programStudi');

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data mahasiswa berhasil diperbarui',
            'data' => new MahasiswaResource($mahasiswa),
        ]);
    }

    public function destroy(Mahasiswa $mahasiswa): JsonResponse
    {
        $mahasiswa->delete();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data mahasiswa berhasil dihapus',
        ]);
    }

    /**
     * Parsing parameter `fields` menjadi daftar kolom yang valid.
     *
     * @return Collection<int, string>
     */
    protected function kolomDipilih(Request $request): Collection
    {
        return collect(explode(',', (string) $request->query('fields', '')))
            ->map(fn (string $kolom) => trim($kolom))
            ->filter(fn (string $kolom) => in_array($kolom, MahasiswaResource::KOLOM_TABEL, true))
            ->unique()
            ->values();
    }
}
