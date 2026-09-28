<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMatakuliahRequest;
use App\Http\Requests\UpdateMatakuliahRequest;
use App\Http\Resources\MatakuliahResource;
use App\Models\Matakuliah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class MatakuliahController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $kueri = Matakuliah::query();

        if ($request->filled('cari')) {
            $kataKunci = $request->query('cari');
            $kueri->where(function ($sub) use ($kataKunci) {
                $sub->where('nama', 'like', '%'.$kataKunci.'%')
                    ->orWhere('kode', 'like', '%'.$kataKunci.'%');
            });
        }

        if ($request->filled('sks')) {
            $kueri->where('sks', $request->integer('sks'));
        }

        if ($request->filled('semester')) {
            $kueri->where('semester', $request->integer('semester'));
        }

        $urutan = (string) $request->query('urut', 'kode');
        $arah = $request->query('arah', 'asc');
        $kolomDiizinkan = ['kode', 'nama', 'sks', 'semester'];

        // Kolom di luar daftar diizinkan kembali ke default, bukan diabaikan,
        // supaya urutan hasil tetap stabil.
        if (! in_array($urutan, $kolomDiizinkan, true)) {
            $urutan = 'kode';
        }

        $kueri->orderBy($urutan, $arah === 'desc' ? 'desc' : 'asc');

        $kolom = $this->kolomDipilih($request);

        if ($kolom->isNotEmpty()) {
            // `id` dan `created_at` selalu diambil agar resource tetap dapat dirakit.
            $kueri->select($kolom->merge(['id', 'created_at'])->unique()->all());
        }

        // Wajib setelah `select()` di atas: `select()` menimpa daftar kolom sehingga
        // subquery `mahasiswas_count` dari `withCount` ikut terhapus.
        $kueri->withCount('mahasiswas');

        $perHalaman = min($request->integer('per_halaman', 10), 100);

        return MatakuliahResource::collection($kueri->paginate($perHalaman));
    }

    public function store(StoreMatakuliahRequest $request): JsonResponse
    {
        $matakuliah = Matakuliah::create($request->validated());
        $matakuliah->loadCount('mahasiswas');

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data matakuliah berhasil dibuat',
            'data' => new MatakuliahResource($matakuliah),
        ], 201);
    }

    public function show(Matakuliah $matakuliah): JsonResponse
    {
        $matakuliah->loadCount('mahasiswas');

        return response()->json([
            'sukses' => true,
            'data' => new MatakuliahResource($matakuliah),
        ]);
    }

    public function update(UpdateMatakuliahRequest $request, Matakuliah $matakuliah): JsonResponse
    {
        $matakuliah->update($request->validated());
        $matakuliah->loadCount('mahasiswas');

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data matakuliah berhasil diperbarui',
            'data' => new MatakuliahResource($matakuliah),
        ]);
    }

    public function destroy(Matakuliah $matakuliah): JsonResponse
    {
        $matakuliah->delete();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data matakuliah berhasil dihapus',
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
            ->filter(fn (string $kolom) => in_array($kolom, MatakuliahResource::KOLOM_TABEL, true))
            ->unique()
            ->values();
    }
}
