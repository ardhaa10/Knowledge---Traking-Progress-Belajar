<?php
require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../app/models/Notification.php';
require_once __DIR__ . '/../koneksi.php';

$query = "
SELECT u.id, u.email, MAX(vp.updated_at) AS last_activity
FROM users u
LEFT JOIN video_progress vp ON vp.user_id = u.id
WHERE u.role = 'user'
  AND u.id NOT IN (
      SELECT user_id 
      FROM notifications 
      WHERE DATE(sent_at) = CURDATE()
  )
GROUP BY u.id
HAVING last_activity IS NULL
   OR last_activity < NOW() - INTERVAL 3 DAY
";

$result = $conn->query($query);
$notif = new Notification();

while ($user = $result->fetch_assoc()) {

    $message = "
Halo 👋<br><br>
Kami melihat kamu belum belajar dalam beberapa hari.<br>
Yuk lanjutkan belajar hari ini walaupun hanya 1 video 😊<br><br>
🔥 <b>Semangat belajar!</b>
";

    $send = MailService::send(
        $user['email'],
        'Pengingat Belajar',
        $message
    );

    $notif->log(
        $user['id'],
        $user['email'],
        strip_tags($message),
        $send['status'],
        $send['error']
    );
}
