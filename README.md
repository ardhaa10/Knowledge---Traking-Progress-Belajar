Cara run lokal, import DB, set .env, daftar akun uji

Database:
CREATE DATABASE knowledge;
USE knowledge;

Import tabel (copy dari knowledge.sql)

kemudian ganti/hapus password db di env


Start Laragon kemudian : http://localhost/belajar-online/login

akun uji:

akun admin:
email: admin@knowledge.com
pass : admin123

akun user:
email: user@knowledge.com
pass : user123

**User (Clean URL):**
- Dashboard: `http://belajar-online/dashboard`
- Profile: `http://belajar-online/profile`
- Login: `http://belajar-online/login`

**Admin (Direct Path):**
- Dashboard: `http://belajar-online/app/views/admin/dashboard.php`
- User Management: `http://belajar-online/app/views/admin/user_management.php`
- Reports: `http://belajar-online/app/views/admin/reports.php`


## 📱 Fitur
- ✅ Admin: CRUD User/Playlist/Content/Jadwal
- ✅ User: Dashboard, Profile, Video Player
- ✅ Google OAuth Login
- ✅ Export CSV Reports
