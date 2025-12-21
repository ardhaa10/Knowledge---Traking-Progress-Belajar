<?php
require_once 'Database.php';

class Jadwal {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Ambil semua jadwal
    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM jadwal ORDER BY tanggal, jam_mulai");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    // Tambah jadwal baru
    public function add($mata_kuliah, $tanggal, $jam_mulai, $jam_selesai, $deskripsi) {
        $stmt = $this->conn->prepare("INSERT INTO jadwal (mata_kuliah, tanggal, jam_mulai, jam_selesai, deskripsi) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $mata_kuliah, $tanggal, $jam_mulai, $jam_selesai, $deskripsi);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    // Update google_event_id
    public function updateGoogleEventId($jadwal_id, $google_event_map_json) {
        $stmt = $this->conn->prepare("UPDATE jadwal SET google_event_id=? WHERE id=?");
        $stmt->bind_param("si", $google_event_map_json, $jadwal_id);
        $stmt->execute();
        $stmt->close();
    }

    // Hapus jadwal
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM jadwal WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    // Ambil satu jadwal
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM jadwal WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $jadwal = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $jadwal;
    }
}
