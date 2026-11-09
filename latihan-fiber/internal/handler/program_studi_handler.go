package handler

import (
	"github.com/gofiber/fiber/v3"

	"latihan-fiber/internal/repository"
)

func DaftarProgramStudi(c fiber.Ctx) error {
	daftar, err := repository.AmbilProgramStudiDenganJumlah()
	if err != nil {
		return fiber.NewError(fiber.StatusInternalServerError, "Gagal mengambil data")
	}

	return c.JSON(fiber.Map{
		"sukses": true,
		"data":   daftar,
	})
}
