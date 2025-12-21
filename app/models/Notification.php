<?php
require_once 'Database.php';

class Notification
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function log($user_id, $email, $message, $status, $error = null)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO notifications (user_id, email, message, status, error_message)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $user_id, $email, $message, $status, $error);
        $stmt->execute();
        $stmt->close();
    }
}
