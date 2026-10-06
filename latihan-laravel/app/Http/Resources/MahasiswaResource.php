<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MahasiswaResource extends JsonResource
{
    /**
     * Kunci yang boleh dipilih klien lewat parameter query `fields`.
     *
     * @var list<string>
     */
    public const KOLOM_DIIZINKAN = [
        'id',
        'nim',
        'nama',
        'email',
        'angkatan',
        'ipk',
        'aktif',
        'program_studi',
        'dibuat_pada',
    ];

    /**
     * Kolom nyata di tabel `mahasiswas` (dipakai controller untuk optimasi SELECT).
     *
     * @var list<string>
     */
    public const KOLOM_TABEL = ['id', 'nim', 'nama', 'email', 'angkatan', 'ipk', 'aktif'];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'nim' => $this->nim,
            'nama' => $this->nama,
            'email' => $this->email,
            'angkatan' => $this->angkatan,
            'ipk' => (float) $this->ipk,
            'aktif' => $this->aktif,
            'program_studi' => $this->whenLoaded('programStudi', fn () => [
                'id' => $this->programStudi->id,
                'kode' => $this->programStudi->kode,
                'nama' => $this->programStudi->nama,
            ]),
            'dibuat_pada' => $this->created_at->toIso8601String(),
        ];

        return $this->filterKolom($data, $request);
    }

    /**
     * Batasi keluaran pada kunci yang diminta klien melalui `?fields=nim,nama`.
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
