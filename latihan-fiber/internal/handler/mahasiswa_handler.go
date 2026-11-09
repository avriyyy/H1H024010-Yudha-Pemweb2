package handler

import (
	"sort"
	"strconv"

	"github.com/gofiber/fiber/v3"
	"latihan-fiber/internal/model"
)

func DaftarMahasiswa(c fiber.Ctx) error {
	// Salin dahulu agar pengurutan tidak mengubah urutan data global.
	hasil := append([]model.Mahasiswa{}, model.Daftar...)

	if angkatan := c.Query("angkatan", ""); angkatan != "" {
		nilaiAngkatan, err := strconv.Atoi(angkatan)
		if err != nil {
			return fiber.NewError(fiber.StatusBadRequest, "Parameter angkatan harus berupa angka")
		}

		disaring := []model.Mahasiswa{}
		for i := 0; i < len(hasil); i++ {
			if hasil[i].Angkatan == nilaiAngkatan {
				disaring = append(disaring, hasil[i])
			}
		}
		hasil = disaring
	}

	if urut := c.Query("urut", ""); urut != "" {
		switch urut {
		case "nama":
			sort.Slice(hasil, func(i, j int) bool { return hasil[i].Nama < hasil[j].Nama })
		case "ipk":
			sort.Slice(hasil, func(i, j int) bool { return hasil[i].Ipk > hasil[j].Ipk })
		default:
			return fiber.NewError(fiber.StatusBadRequest, "Parameter urut harus bernilai nama atau ipk")
		}
	}

	return c.JSON(fiber.Map{
		"sukses": true,
		"data":   hasil,
	})
}

func DetailMahasiswa(c fiber.Ctx) error {
	id, err := strconv.Atoi(c.Params("id"))
	if err != nil {
		return fiber.NewError(fiber.StatusBadRequest, "ID harus berupa angka")
	}

	for i := 0; i < len(model.Daftar); i++ {
		if model.Daftar[i].ID == id {
			return c.JSON(fiber.Map{
				"sukses": true,
				"data":   model.Daftar[i],
			})
		}
	}

	return fiber.NewError(fiber.StatusNotFound, "Mahasiswa tidak ditemukan")
}

func CariMahasiswaPerNim(c fiber.Ctx) error {
	nim := c.Params("nim")
	for i := 0; i < len(model.Daftar); i++ {
		if model.Daftar[i].Nim == nim {
			return c.JSON(fiber.Map{
				"sukses": true,
				"data":   model.Daftar[i],
			})
		}
	}

	return fiber.NewError(fiber.StatusNotFound, "Mahasiswa tidak ditemukan")
}
