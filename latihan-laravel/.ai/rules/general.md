---
paths:
  - .env
---

# General

## MySQL berjalan di Docker: DB_HOST harus 127.0.0.1
Database MySQL tidak jalan di host melainkan di container Docker `mysql-db` (port 3306 ter-map ke 0.0.0.0). `DB_HOST=localhost` gagal dengan "[2002] No such file or directory" karena unix socket `/run/mysqld/mysqld.sock` tidak ada di host. Gunakan `DB_HOST=127.0.0.1`. User `yudha`/`12345` harus punya akses `yudha@'%'` (bukan hanya `@localhost`) karena koneksi dari host muncul sebagai IP gateway Docker 172.18.0.1. Root password container: 000000.
