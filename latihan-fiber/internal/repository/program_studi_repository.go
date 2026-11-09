package repository

import (
	"latihan-fiber/internal/config"
	"latihan-fiber/internal/model"
)

type ProgramStudiDenganJumlah struct {
	model.ProgramStudi
	JumlahMahasiswa int64 `json:"jumlah_mahasiswa"`
}

func AmbilProgramStudiDenganJumlah() ([]ProgramStudiDenganJumlah, error) {
	var daftar []model.ProgramStudi

	err := config.DB.Order("nama ASC").Find(&daftar).Error
	if err != nil {
		return nil, err
	}

	hasil := make([]ProgramStudiDenganJumlah, 0, len(daftar))
	for i := 0; i < len(daftar); i++ {
		var jumlah int64
		// Count otomatis mengabaikan baris yang terhapus lunak.
		err := config.DB.Model(&model.Mahasiswa{}).
			Where("program_studi_id = ?", daftar[i].ID).
			Count(&jumlah).Error
		if err != nil {
			return nil, err
		}
		hasil = append(hasil, ProgramStudiDenganJumlah{
			ProgramStudi:    daftar[i],
			JumlahMahasiswa: jumlah,
		})
	}

	return hasil, nil
}
