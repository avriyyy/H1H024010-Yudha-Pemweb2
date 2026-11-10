package validasi

import (
	"regexp"

	"github.com/go-playground/validator/v10"
)

var pemeriksa = validator.New()

var polaNim = regexp.MustCompile(`^H[0-9]+$`)

func init() {
	pemeriksa.RegisterValidation("nim", func(item validator.FieldLevel) bool {
		return polaNim.MatchString(item.Field().String())
	})
}

type PesanGalat struct {
	Field string `json:"field"`
	Pesan string `json:"pesan"`
}

func Periksa(data any) []PesanGalat {
	err := pemeriksa.Struct(data)
	if err == nil {
		return nil
	}

	daftar := []PesanGalat{}

	for _, item := range err.(validator.ValidationErrors) {
		daftar = append(daftar, PesanGalat{
			Field: item.Field(),
			Pesan: susunPesan(item),
		})
	}

	return daftar
}

func susunPesan(item validator.FieldError) string {
	switch item.Tag() {
	case "required":
		return "Field ini wajib diisi"
	case "email":
		return "Format email tidak valid"
	case "min":
		return "Nilai minimal adalah " + item.Param()
	case "max":
		return "Nilai maksimal adalah " + item.Param()
	case "gte":
		return "Nilai tidak boleh kurang dari " + item.Param()
	case "lte":
		return "Nilai tidak boleh lebih dari " + item.Param()
	case "nim":
		return "NIM harus diawali huruf H diikuti angka"
	default:
		return "Nilai tidak memenuhi aturan " + item.Tag()
	}
}
