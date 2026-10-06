<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PeranAdmin
{
    /**
     * Menolak permintaan bila peran pengguna tidak sesuai.
     *
     * Peran yang diizinkan dibaca dari parameter middleware, misalnya
     * `peran:admin` menerima peran `admin`.
     */
    public function handle(Request $request, Closure $next, string $peranWajib = 'admin'): Response
    {
        $pengguna = $request->user();

        if ($pengguna === null || $pengguna->peran !== $peranWajib) {
            return response()->json([
                'sukses' => false,
                'pesan' => "Hanya pengguna dengan peran {$peranWajib} yang dapat melakukan tindakan ini",
            ], 403);
        }

        return $next($request);
    }
}
