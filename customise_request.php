<?php
/*********************************************************************
 * customise_request.php – called by the “Submit & Continue” button
 * in product-details.html.  Stores one row in
 *          gameweb.customise_requests
 * and returns   { success:true , phone:"+91xxxxxxxxxx" }   JSON.
 *********************************************************************/
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* 1 ─ turn ANY PHP/MySQL error into clean JSON */
set_exception_handler(function ($e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
  exit;
});

/* 2 ─ read & sanity-check payload */
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['name'], $data['email'], $data['phone'], $data['game'])) {
  throw new RuntimeException('Bad payload');
}

$name = trim($data['name']);
$email = trim($data['email']);
$game = trim($data['game']);

/* 3 ─ e-mail check */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  throw new RuntimeException('Invalid e-mail');
}

/* 4 ─ phone normalisation → keep 10 digits, prefix +91 */
$digits = preg_replace('/\D+/', '', $data['phone']);     // only digits

if (strlen($digits) === 12 && substr($digits, 0, 2) === '91') {
  $digits = substr($digits, -10);                      // strip leading 91
}
if (strlen($digits) === 11 && $digits[0] === '0') {
  $digits = substr($digits, -10);                      // strip leading 0
}
if (!preg_match('/^\d{10}$/', $digits)) {
  throw new RuntimeException('Invalid phone');
}
$phone = '+91' . $digits;                                // final stored form

/* 5 ─ connect (create DB if missing) */
$mysqli = new mysqli('localhost', 'server2_gameweb', 'WT#^-yf=z]l$PgE!', 'server2_gameweb');
$mysqli->query('CREATE DATABASE IF NOT EXISTS `server2_gameweb`');
$mysqli->select_db('server2_gameweb');

/* 6 ─ ensure the master table exists */
$mysqli->query("
  CREATE TABLE IF NOT EXISTS customise_requests (
      id                   INT AUTO_INCREMENT PRIMARY KEY,
      name                 VARCHAR(100) NOT NULL,
      email                VARCHAR(150) NOT NULL,
      mobile_number        VARCHAR(14)  NOT NULL,
      user_customise_game  VARCHAR(50)  NOT NULL,
      requested_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

/* 7 ─ insert the row */
$stmt = $mysqli->prepare(
  "INSERT INTO customise_requests
       (name, email, mobile_number, user_customise_game)
     VALUES (?,?,?,?)"
);
$stmt->bind_param('ssss', $name, $email, $phone, $game);
$stmt->execute();

/* 8 ─ success reply */
echo json_encode(['success' => true, 'phone' => $phone]);
