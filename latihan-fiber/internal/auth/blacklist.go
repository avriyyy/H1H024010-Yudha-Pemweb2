package auth

import (
	"sync"
	"time"
)

// Daftar hitam token dalam memori: memetakan token ke waktu
// kedaluwarsanya (Unix). Entri kedaluwarsa dibersihkan secara malas
// saat diperiksa. Cukup untuk satu instance; produksi sebaiknya
// memakai Redis atau tabel basis data agar berlaku lintas instance
// dan tidak hilang saat restart.
var (
	hitam      = make(map[string]int64)
	mutexHitam sync.RWMutex
)

// Cabut memasukkan token ke daftar hitam hingga waktu kedaluwarsa.
func Cabut(nilaiToken string, kedaluwarsaUnix int64) {
	mutexHitam.Lock()
	hitam[nilaiToken] = kedaluwarsaUnix
	mutexHitam.Unlock()
}

// Dicabut melaporkan apakah token ada pada daftar hitam dan belum
// melewati waktu kedaluwarsanya.
func Dicabut(nilaiToken string) bool {
	mutexHitam.RLock()
	kedaluwarsa, ada := hitam[nilaiToken]
	mutexHitam.RUnlock()

	if ada == false {
		return false
	}

	if time.Now().Unix() >= kedaluwarsa {
		mutexHitam.Lock()
		delete(hitam, nilaiToken)
		mutexHitam.Unlock()
		return false
	}

	return true
}
