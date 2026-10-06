<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramStudiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'nama' => $this->nama,
            'jenjang' => $this->jenjang,
            'jumlah_mahasiswa' => $this->whenCounted('mahasiswa'),
            'dibuat_pada' => $this->created_at->toIso8601String(),
        ];
    }
}
