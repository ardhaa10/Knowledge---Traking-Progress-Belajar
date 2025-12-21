<?php
require_once __DIR__ . '/../models/Jadwal.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Google API

class JadwalController
{
    private $jadwalModel;
    private $userModel;
    private $googleClientId;
    private $googleClientSecret;

    public function __construct()
    {
        $this->jadwalModel = new Jadwal();
        $this->userModel = new User();
        
        // Load environment variables jika belum di-load
        if (!getenv('GOOGLE_CLIENT_ID')) {
            $this->loadEnv();
        }
        
        $this->googleClientId = getenv('GOOGLE_CLIENT_ID');
        $this->googleClientSecret = getenv('GOOGLE_CLIENT_SECRET');
    }

    private function loadEnv()
    {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') === false) continue;
                
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                if (!getenv($name)) {
                    putenv("$name=$value");
                }
            }
        }
    }

    public function index()
    {
        return $this->jadwalModel->getAll();
    }

    public function store($data)
    {
        $mata_kuliah = $data['mata_kuliah'];
        $tanggal = $data['tanggal'];
        $jam_mulai = $data['jam_mulai'];
        $jam_selesai = $data['jam_selesai'];
        $deskripsi = $data['deskripsi'] ?? '';

        // Simpan jadwal ke DB
        $jadwal_id = $this->jadwalModel->add($mata_kuliah, $tanggal, $jam_mulai, $jam_selesai, $deskripsi);

        // Ambil semua user yang punya token Google
        $googleUsers = $this->userModel->getAllGoogleUsers();

        $google_event_map = [];

        foreach ($googleUsers as $user) {
            if (!$user['google_access_token'] || !$user['google_refresh_token']) continue;

            $client = new \Google_Client();
            $client->setClientId($this->googleClientId);
            $client->setClientSecret($this->googleClientSecret);
            $client->setAccessToken([
                'access_token' => $user['google_access_token'],
                'refresh_token' => $user['google_refresh_token'],
                'expires_in' => 3600,
                'created' => strtotime($user['token_expiry']) - 3600
            ]);

            // Auto-refresh token
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($user['google_refresh_token']);
                $newToken = $client->getAccessToken();
                $newExpiry = date('Y-m-d H:i:s', time() + $newToken['expires_in']);
                $this->userModel->updateGoogleToken($user['id'], $newToken['access_token'], $newExpiry);
            }

            $service = new \Google_Service_Calendar($client);

            // Event start/end time in RFC3339 format
            $startDateTime = $tanggal . 'T' . $jam_mulai . ':00';
            $endDateTime = $tanggal . 'T' . $jam_selesai . ':00';

            $event = new \Google_Service_Calendar_Event([
                'summary' => $mata_kuliah,
                'description' => $deskripsi,
                'start' => ['dateTime' => $startDateTime, 'timeZone' => 'Asia/Jakarta'],
                'end' => ['dateTime' => $endDateTime, 'timeZone' => 'Asia/Jakarta']
            ]);

            try {
                $createdEvent = $service->events->insert('primary', $event);
                $google_event_map[$user['id']] = $createdEvent->id;
            } catch (Exception $e) {
                error_log("Gagal buat event untuk user {$user['email']}: " . $e->getMessage());
            }
        }

        // Simpan mapping event id
        if (!empty($google_event_map)) {
            $this->jadwalModel->updateGoogleEventId($jadwal_id, json_encode($google_event_map));
        }

        header("Location: ../views/admin/jadwal_management.php");
        exit;
    }

    public function destroy($id)
    {
        $jadwal = $this->jadwalModel->getById($id);

        if (!empty($jadwal['google_event_id'])) {
            $google_event_map = json_decode($jadwal['google_event_id'], true);
            $googleUsers = $this->userModel->getAllGoogleUsers();

            foreach ($googleUsers as $user) {
                if (!isset($google_event_map[$user['id']])) continue;

                $client = new \Google_Client();
                $client->setClientId($this->googleClientId);
                $client->setClientSecret($this->googleClientSecret);
                $client->setAccessToken([
                    'access_token' => $user['google_access_token'],
                    'refresh_token' => $user['google_refresh_token'],
                    'expires_in' => 3600,
                    'created' => strtotime($user['token_expiry']) - 3600
                ]);

                if ($client->isAccessTokenExpired()) {
                    $client->fetchAccessTokenWithRefreshToken($user['google_refresh_token']);
                    $newToken = $client->getAccessToken();
                    $newExpiry = date('Y-m-d H:i:s', time() + $newToken['expires_in']);
                    $this->userModel->updateGoogleToken($user['id'], $newToken['access_token'], $newExpiry);
                }

                $service = new \Google_Service_Calendar($client);

                try {
                    $service->events->delete('primary', $google_event_map[$user['id']]);
                } catch (Exception $e) {
                    error_log("Gagal hapus event untuk user {$user['email']}: " . $e->getMessage());
                }
            }
        }

        $this->jadwalModel->delete($id);
        header("Location: ../views/admin/jadwal_management.php");
        exit;
    }
}