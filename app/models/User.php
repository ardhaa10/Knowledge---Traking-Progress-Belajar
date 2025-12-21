<?php
require_once 'Database.php';

class User
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllUsers()
    {
        $stmt = $this->conn->prepare("SELECT id, name, email, role FROM users");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function getUserById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $user;
    }

    public function addUser($name, $email, $password, $role)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hash, $role);
        $stmt->execute();
        $stmt->close();
    }

    public function updateUser($id, $data)
{
    if (!is_array($data) || empty($data)) {
        throw new Exception("Data update harus berupa array dan tidak boleh kosong.");
    }
    $allowedFields = ['name', 'email', 'role', 'profile_photo'];
    $fields = [];
    $types  = "";
    $values = [];

    foreach ($data as $key => $value) {
        if (!in_array($key, $allowedFields, true)) {
            continue;
        }
        $fields[]  = "`$key` = ?";
        $types    .= "s"; 
        $values[]  = $value;
    }

    if (empty($fields)) {
        throw new Exception("Tidak ada field valid untuk diupdate.");
    }

    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL ERROR: " . $this->conn->error . "\nQUERY: " . $sql);
    }

    $types   .= "i";
    $values[] = (int)$id;

    $stmt->bind_param($types, ...$values);

    $result = $stmt->execute();
    $stmt->close();
    return $result;
}


    public function deleteUser($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    public function register($name, $email, $password, $role = 'user')
{
    if (empty($name) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Semua field wajib diisi'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Format email tidak valid'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password minimal 6 karakter'];
    }

    // cek email sudah ada? (KODE LAMA KAMU)
    $stmt = $this->conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Email sudah terdaftar'];
    }
    $stmt->close();

    // insert user baru (KODE LAMA KAMU)
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $this->conn->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $name, $email, $hash, $role);
    $stmt->execute();
    $stmt->close();

    return ['success' => true, 'message' => 'Registrasi berhasil'];
}


    // Login manual
    public function login($email, $password)
    {
        $stmt = $this->conn->prepare("SELECT id, name, email, password, role FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
            // sukses login -> update check-in & streak
            $this->handleCheckin($user['id']);

            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Email atau password salah'];
        }
    }


    // Login/register Google
    public function loginGoogle($google_id, $name, $email, $access_token = null, $refresh_token = null, $expires_in = null, $profile_photo = null, $role = 'user')
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE google_id=?");
        $stmt->bind_param("s", $google_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $expiry = $expires_in ? date('Y-m-d H:i:s', time() + $expires_in) : null;

        if ($user) {
            // Update token jika ada
            $stmt = $this->conn->prepare("
            UPDATE users 
            SET 
            google_access_token=?,
            google_refresh_token=?,
            token_expiry=?,
            profile_photo = IF(profile_photo IS NULL OR profile_photo='', ?, profile_photo)
            WHERE google_id=?
        ");
            $stmt->bind_param("sssss", $access_token, $refresh_token, $expiry, $profile_photo, $google_id);
            $stmt->execute();
            $stmt->close();

            // update streak check-in
            $this->handleCheckin($user['id']);

            return ['success' => true, 'user' => $user];
        } else {
            // Generate password random
            $plainPassword = '123456'; // 8 karakter
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

            // Insert user baru
            $stmt = $this->conn->prepare("
            INSERT INTO users (google_id, name, email, password, role, profile_photo, google_access_token, google_refresh_token, token_expiry)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
            $stmt->bind_param("sssssssss", $google_id, $name, $email, $hashedPassword, $role, $profile_photo, $access_token, $refresh_token, $expiry);
            $stmt->execute();
            $id = $stmt->insert_id;
            $stmt->close();

            // ambil user baru
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // lakukan check-in pertama
            $this->handleCheckin($id);

            return ['success' => true, 'user' => $user];
        }
    }

    // ======================================
    // Checkin & Streak System
    // ======================================
    private function handleCheckin($user_id)
    {
        $today       = date('Y-m-d');
        $yesterday   = date('Y-m-d', strtotime('-1 day'));

        // Cek apakah hari ini sudah check-in
        $exists = $this->conn->query("
        SELECT id FROM checkin_logs 
        WHERE user_id = $user_id AND checkin_date = '$today'
    ")->fetch_assoc();

        if (!$exists) {
            // Insert log baru
            $this->conn->query("
            INSERT INTO checkin_logs (user_id, checkin_date)
            VALUES ($user_id, '$today')
        ");

            // Ambil streak sekarang
            $streak = $this->conn->query("
            SELECT streak_count, last_checkin FROM streaks WHERE user_id = $user_id
        ")->fetch_assoc();

            if ($streak) {
                if ($streak['last_checkin'] == $yesterday) {
                    // Tambah streak
                    $this->conn->query("
                    UPDATE streaks SET streak_count = streak_count + 1, last_checkin = '$today' 
                    WHERE user_id = $user_id
                ");
                } else {
                    // Reset ke 1
                    $this->conn->query("
                    UPDATE streaks SET streak_count = 1, last_checkin = '$today' 
                    WHERE user_id = $user_id
                ");
                }
            } else {
                // Buat record pertama
                $this->conn->query("
                INSERT INTO streaks (user_id, streak_count, last_checkin)
                VALUES ($user_id, 1, '$today')
            ");
            }
        }
    }

    // Cek user berdasarkan google_id
    public function getUserByGoogleId($google_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE google_id=?");
        $stmt->bind_param("s", $google_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $user;
    }

    // Tambah user Google (password null)
    public function addUserGoogle($google_id, $name, $email, $role = 'user')
    {
        $stmt = $this->conn->prepare("INSERT INTO users (google_id, name, email, password, role) VALUES (?, ?, ?, NULL, ?)");
        $stmt->bind_param("ssss", $google_id, $name, $email, $role);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    public function getAllGoogleUsers()
    {
        $stmt = $this->conn->prepare("SELECT id, name, email, google_access_token, google_refresh_token, token_expiry FROM users WHERE google_access_token IS NOT NULL AND google_refresh_token IS NOT NULL");
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $users;
    }

    public function updateGoogleToken($user_id, $access_token, $expiry)
    {
        $stmt = $this->conn->prepare("UPDATE users SET google_access_token=?, token_expiry=? WHERE id=?");
        $stmt->bind_param("ssi", $access_token, $expiry, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}
