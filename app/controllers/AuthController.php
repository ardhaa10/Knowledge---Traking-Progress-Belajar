<?php
session_start();
require_once __DIR__ . '/../../config/config.php';  
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login($data)
    {
        $email = $data['email'];
        $password = $data['password'];
        $result = $this->userModel->login($email, $password);

        if ($result['success']) {
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['user_name'] = $result['user']['name'];
            $_SESSION['role'] = $result['user']['role'];
            $_SESSION['user_email'] = $result['user']['email'];
            $role = $result['user']['role'];

            // ADMIN: Direct path | USER: Clean URL (.htaccess)
            if ($role === 'admin') {
                header("Location: /belajar-online/app/views/admin/dashboard.php");
            } else {
                header("Location: /belajar-online/dashboard");  // .htaccess handle
            }
            exit;
        } else {
            $error = urlencode($result['message']);
            header("Location: /belajar-online/login?error=$error");  // .htaccess
            exit;
        }
    }

    public function register($data)
    {
        $name = trim($data['name']);
        $email = trim($data['email']);
        $password = $data['password'];
        $role = 'user';

        $result = $this->userModel->register($name, $email, $password, $role);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header("Location: /belajar-online/login");  // .htaccess
            exit;
        } else {
            $_SESSION['error'] = $result['message'];
            header("Location: /belajar-online/register");  // .htaccess
            exit;
        }
    }

    public function loginGoogle($google_id, $name, $email, $access_token = null, $refresh_token = null, $expires_in = null, $profile_photo = null)
    {
        if (!$google_id || !$name || !$email) {
            die('Data Google tidak lengkap.');
        }

        $result = $this->userModel->loginGoogle($google_id, $name, $email, $access_token, $refresh_token, $expires_in, $profile_photo);

        if ($result['success']) {
            $user = $result['user'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: /belajar-online/app/views/admin/dashboard.php");
            } else {
                header("Location: /belajar-online/dashboard");
            }
            exit;
        } else {
            header("Location: /belajar-online/login?error=Login Google gagal");
            exit;
        }
    }
    
    public function logout()
    {
        session_destroy();
        header("Location: /belajar-online/login");
        exit;
    }
}
?>
