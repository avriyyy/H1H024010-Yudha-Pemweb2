package router

import (
	"github.com/gofiber/fiber/v3"
	"latihan-fiber/internal/config"
	"latihan-fiber/internal/handler"
	"latihan-fiber/internal/middleware"
)

func Daftarkan(app *fiber.App, konf config.Konfigurasi) {
	api := app.Group("/api")
	api.Use(middleware.KunciApi(konf.KunciAPI))
	api.Get("/status", func(c fiber.Ctx) error {
		return c.JSON(fiber.Map{
			"sukses": true,
			"pesan":  "API Fiber aktif",
		})
	})

	mahasiswa := api.Group("/mahasiswa")
	mahasiswa.Get("/", handler.DaftarMahasiswa)
	// Rute spesifik wajib didaftarkan sebelum rute parameter /:id
	// agar permintaan /nim/xxx tidak ditangkap sebagai id="nim".
	mahasiswa.Get("/nim/:nim", handler.CariMahasiswaPerNim)
	mahasiswa.Get("/:id", handler.DetailMahasiswa)
}
