<?php
require_once __DIR__ . '/../models/User.php';

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // Tampilkan semua user
    public function index()
    {
        return $this->userModel->getAllUsers();
    }

    // Tambah user
    // Tambah user
    public function store($data)
    {
        try {
            $this->userModel->addUser($data['name'], $data['email'], $data['password'], $data['role']);
            // Sukses → redirect ke halaman user management
            header("Location: ../../app/views/admin/user_management.php?success=1");
            exit;
        } catch (PDOException $e) {
            // Gagal → redirect tapi bawa pesan error
            $error = urlencode($e->getMessage());
            header("Location: ../../app/views/admin/user_management.php?error=$error");
            exit;
        }
    }


    // Update user
    public function update($postData)
{
    $id = $postData['id'] ?? null;
    if (!$id) {
        throw new Exception("ID user tidak ditemukan");
    }

    $data = [
        'name'  => $postData['name'] ?? null,
        'email' => $postData['email'] ?? null,
        'role'  => $postData['role'] ?? null
    ];

    $data = array_filter($data, fn($v) => $v !== null && $v !== '');

    $result = $this->userModel->updateUser($id, $data);

    // Simpan pesan sukses/error
    $success = $result ? "User updated successfully" : null;
    $error = !$result ? "Failed to update user" : null;

    // Ambil semua user baru
    $controller = new UserController();
    $users = $controller->index();

    // Render view langsung tanpa redirect
    include __DIR__ . '/../../app/views/admin/user_management.php';
    exit;
}




    // Hapus user
    public function destroy($id)
    {
        $this->userModel->deleteUser($id);
        header("Location: ../../app/views/admin/user_management.php");
    }
}
