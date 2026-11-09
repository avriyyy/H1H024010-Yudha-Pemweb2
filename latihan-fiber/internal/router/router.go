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
	mahasiswa.Post("/", handler.BuatMahasiswa)
	// Rute spesifik wajib didaftarkan sebelum rute parameter /:id
	// agar permintaan /nim/xxx tidak ditangkap sebagai id="nim".
	mahasiswa.Get("/nim/:nim", handler.CariMahasiswaPerNim)
	mahasiswa.Get("/:id", handler.DetailMahasiswa)
	mahasiswa.Put("/:id", handler.PerbaruiMahasiswa)
	mahasiswa.Delete("/:id", handler.HapusMahasiswa)
	mahasiswa.Post("/:id/pulihkan", handler.PulihkanMahasiswa)

	matakuliah := api.Group("/matakuliah")
	matakuliah.Get("/", handler.DaftarMatakuliah)
	matakuliah.Post("/", handler.BuatMatakuliah)
	matakuliah.Get("/:id", handler.DetailMatakuliah)
	matakuliah.Put("/:id", handler.PerbaruiMatakuliah)
	matakuliah.Delete("/:id", handler.HapusMatakuliah)

	api.Get("/program-studi", handler.DaftarProgramStudi)
}
