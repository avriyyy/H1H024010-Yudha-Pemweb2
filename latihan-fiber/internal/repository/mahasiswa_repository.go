package repository

import (
	"latihan-fiber/internal/config"
	"latihan-fiber/internal/model"
)

type FilterMahasiswa struct {
	Cari       string
	Angkatan   int
	Urut       string
	Halaman    int
	PerHalaman int
}

func Ambil(filter FilterMahasiswa) ([]model.Mahasiswa, int64, error) {
	var daftar []model.Mahasiswa
	var total int64

	kueri := config.DB.Model(&model.Mahasiswa{})

	if filter.Cari != "" {
		pola := "%" + filter.Cari + "%"
		kueri = kueri.Where("nama LIKE ? OR nim LIKE ?", pola, pola)
	}

	if filter.Angkatan > 0 {
		kueri = kueri.Where("angkatan = ?", filter.Angkatan)
	}

	err := kueri.Count(&total).Error
	if err != nil {
		return nil, 0, err
	}

	urutan := "nama ASC"
	if filter.Urut == "ipk" {
		urutan = "ipk DESC"
	}

	lompat := (filter.Halaman - 1) * filter.PerHalaman

	err = kueri.Preload("ProgramStudi").
		Order(urutan).
		Limit(filter.PerHalaman).
		Offset(lompat).
		Find(&daftar).Error

	if err != nil {
		return nil, 0, err
	}

	return daftar, total, nil
}

func AmbilPerId(id uint) (model.Mahasiswa, error) {
	var mahasiswa model.Mahasiswa

	err := config.DB.Preload("ProgramStudi").First(&mahasiswa, id).Error

	return mahasiswa, err
}

func AmbilPerNim(nim string) (model.Mahasiswa, error) {
	var mahasiswa model.Mahasiswa

	err := config.DB.Preload("ProgramStudi").Where("nim = ?", nim).First(&mahasiswa).Error

	return mahasiswa, err
}

func Simpan(mahasiswa *model.Mahasiswa) error {
	return config.DB.Create(mahasiswa).Error
}

func Perbarui(mahasiswa *model.Mahasiswa) error {
	return config.DB.Save(mahasiswa).Error
}

func Hapus(mahasiswa *model.Mahasiswa) error {
	return config.DB.Delete(mahasiswa).Error
}

// AmbilPerIdUnscoped mencari data termasuk yang terhapus lunak,
// dipakai oleh endpoint pemulihan.
func AmbilPerIdUnscoped(id uint) (model.Mahasiswa, error) {
	var mahasiswa model.Mahasiswa

	err := config.DB.Unscoped().Preload("ProgramStudi").First(&mahasiswa, id).Error

	return mahasiswa, err
}

func Pulihkan(mahasiswa *model.Mahasiswa) error {
	return config.DB.Unscoped().Model(mahasiswa).Update("deleted_at", nil).Error
}
