<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatakuliahResource extends JsonResource
{
    /**
     * Kunci yang boleh dipilih klien lewat parameter query `fields`.
     *
     * @var list<string>
     */
    public const KOLOM_DIIZINKAN = ['id', 'kode', 'nama', 'sks', 'semester', 'jumlah_mahasiswa', 'dibuat_pada'];

    /**
     * Kolom nyata di tabel `matakuliahs`.
     *
     * @var list<string>
     */
    public const KOLOM_TABEL = ['id', 'kode', 'nama', 'sks', 'semester'];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'kode' => $this->kode,
            'nama' => $this->nama,
            'sks' => $this->sks,
            'semester' => $this->semester,
            'jumlah_mahasiswa' => $this->whenCounted('mahasiswas'),
            'dibuat_pada' => $this->created_at->toIso8601String(),
        ];

        return $this->filterKolom($data, $request);
    }

    /**
     * Batasi keluaran pada kunci yang diminta klien melalui `?fields=kode,nama`.
     * `id` selalu disertakan sebagai identitas resource.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function filterKolom(array $data, Request $request): array
    {
        if (! $request->filled('fields')) {
            return $data;
        }

        $diminta = collect(explode(',', (string) $request->query('fields', '')))
            ->map(fn (string $kolom) => trim($kolom))
            ->filter(fn (string $kolom) => in_array($kolom, self::KOLOM_DIIZINKAN, true))
            ->unique()
            ->values();

        $hasil = $diminta
            ->mapWithKeys(fn (string $kolom) => [$kolom => $data[$kolom] ?? null])
            ->all();

        if (! array_key_exists('id', $hasil)) {
            $hasil = ['id' => $data['id']] + $hasil;
        }

        return $hasil;
    }
}
