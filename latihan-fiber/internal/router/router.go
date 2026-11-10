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

	api.Post("/auth/registrasi", handler.Registrasi)
	api.Post("/auth/login", handler.Login)

	terlindungi := api.Group("", middleware.Autentikasi())

	terlindungi.Get("/auth/profil", handler.Profil)

	mahasiswa := terlindungi.Group("/mahasiswa")
	mahasiswa.Get("/", handler.DaftarMahasiswa)
	// Rute spesifik wajib didaftarkan sebelum rute parameter /:id
	// agar permintaan /nim/xxx tidak ditangkap sebagai id="nim".
	mahasiswa.Get("/nim/:nim", handler.CariMahasiswaPerNim)
	mahasiswa.Get("/:id", handler.DetailMahasiswa)
	// Middleware otorisasi dipasang SEBELUM handler karena Fiber
	// menjalankan handler rute menurut urutan pendaftaran. Urutan
	// sebaliknya membuat pemeriksaan peran tidak pernah berjalan.
	mahasiswa.Post("/", middleware.HanyaPeran("admin", "dosen"), handler.BuatMahasiswa)
	mahasiswa.Put("/:id", middleware.HanyaPeran("admin", "dosen"), handler.PerbaruiMahasiswa)
	mahasiswa.Delete("/:id", middleware.HanyaPeran("admin"), handler.HapusMahasiswa)
	mahasiswa.Post("/:id/pulihkan", middleware.HanyaPeran("admin"), handler.PulihkanMahasiswa)

	matakuliah := terlindungi.Group("/matakuliah")
	matakuliah.Get("/", handler.DaftarMatakuliah)
	matakuliah.Post("/", middleware.HanyaPeran("admin", "dosen"), handler.BuatMatakuliah)
	matakuliah.Get("/:id", handler.DetailMatakuliah)
	matakuliah.Put("/:id", middleware.HanyaPeran("admin", "dosen"), handler.PerbaruiMatakuliah)
	matakuliah.Delete("/:id", middleware.HanyaPeran("admin"), handler.HapusMatakuliah)

	terlindungi.Get("/program-studi", handler.DaftarProgramStudi)
}
