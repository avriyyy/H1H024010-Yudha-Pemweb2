package model

import "time"

type Pengguna struct {
	ID        uint      `gorm:"primaryKey" json:"id"`
	Nama      string    `gorm:"size:100" json:"nama"`
	Email     string    `gorm:"size:100;uniqueIndex" json:"email"`
	KataSandi string    `gorm:"size:255" json:"-"`
	Peran     string    `gorm:"size:20;default:mahasiswa" json:"peran"`
	CreatedAt time.Time `json:"dibuat_pada"`
	UpdatedAt time.Time `json:"diubah_pada"`
}
