<?php
class Database {
    private $conn;

    public function __construct() {
        $env = parse_ini_file(__DIR__ . '/../../.env');
        $host = $env['DB_HOST'];
        $user = $env['DB_USER'];
        $pass = $env['DB_PASS'];
        $dbname = $env['DB_NAME'];

        $this->conn = new mysqli($host, $user, $pass, $dbname);

        if ($this->conn->connect_error) {
            die("Koneksi gagal: " . $this->conn->connect_error);
        }
    }

    // Getter untuk koneksi
    public function getConnection() {
        return $this->conn;
    }
}
?>
