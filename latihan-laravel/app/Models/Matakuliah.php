<?php

namespace App\Models;

use Database\Factories\MatakuliahFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Matakuliah extends Model
{
    /** @use HasFactory<MatakuliahFactory> */
    use HasFactory;

    protected $table = 'matakuliahs';

    protected $fillable = ['kode', 'nama', 'sks', 'semester'];

    public function mahasiswas(): BelongsToMany
    {
        return $this->belongsToMany(Mahasiswa::class)->withPivot('nilai');
    }
}
