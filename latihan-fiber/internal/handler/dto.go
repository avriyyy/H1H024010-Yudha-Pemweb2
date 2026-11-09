package handler

type PermintaanMahasiswa struct {
	ProgramStudiID uint    `json:"program_studi_id"`
	Nim            string  `json:"nim"`
	Nama           string  `json:"nama"`
	Email          string  `json:"email"`
	Angkatan       int     `json:"angkatan"`
	Ipk            float64 `json:"ipk"`
	Aktif          *bool   `json:"aktif"`
}
