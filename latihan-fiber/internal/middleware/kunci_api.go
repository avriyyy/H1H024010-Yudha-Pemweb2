package middleware

import (
	"github.com/gofiber/fiber/v3"
)

// KunciApi menolak permintaan yang tidak membawa header X-Api-Key
// sesuai nilai pada konfigurasi. Apabila kunci belum dikonfigurasi
// (string kosong), middleware meneruskan permintaan apa adanya agar
// aplikasi tetap dapat dijalankan pada lingkungan pengembangan.
func KunciApi(kunciBenar string) fiber.Handler {
	return func(c fiber.Ctx) error {
		if kunciBenar == "" {
			return c.Next()
		}
		if c.Get("X-Api-Key") != kunciBenar {
			return fiber.NewError(fiber.StatusUnauthorized, "X-Api-Key tidak valid")
		}
		return c.Next()
	}
}
