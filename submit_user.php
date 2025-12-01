<?php
/*  submit_user.php  ------------------------------------------------------- */
header('Content-Type: application/json');

/* ---------- 1. read & sanity-check the JSON payload ---------- */
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data ||
    !isset($data['name'], $data['email'], $data['phone'], $data['game'])) {
  echo json_encode(['success' => false, 'error' => 'Bad payload']);
  exit;
}

$name  = trim($data['name']);
$email = trim($data['email']);
$game  = trim($data['game']);

/* ---------- 2. validate e-mail ---------- */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'error' => 'Invalid e-mail']);
  exit;
}

/* ---------- 3. normalise / validate phone ---------- */
$digits = preg_replace('/\D+/', '', $data['phone']);   // keep only digits

/* strip “91” or leading “0” if present */
if (strlen($digits) === 12 && substr($digits, 0, 2) === '91') {
  $digits = substr($digits, -10);
}
if (strlen($digits) === 11 && $digits[0] === '0') {
  $digits = substr($digits, -10);
}

if (!preg_match('/^\d{10}$/', $digits)) {
  echo json_encode(['success' => false, 'error' => 'Invalid phone']);
  exit;
}
$phone = $digits;

/* ---------- 4. connect to MySQL ---------- */
$dbHost = 'localhost';
$dbUser = 'server2_gameweb';        // <-- your MySQL user
$dbPass = 'WT#^-yf=z]l$PgE!';       // <-- your MySQL password
$dbName = 'server2_gameweb';

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_error) {
  echo json_encode(['success' => false, 'error' => 'DB connect failed']);
  exit;
}

/* ---------- 5. ensure customise_requests table exists ---------- */
$create = "
  CREATE TABLE IF NOT EXISTS customise_requests (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(100)  NOT NULL,
    email               VARCHAR(255)  NOT NULL,
    mobile_number       VARCHAR(20)   NOT NULL,
    user_customise_game VARCHAR(100)  NOT NULL,
    requested_at        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (!$mysqli->query($create)) {
  echo json_encode(['success' => false, 'error' => $mysqli->error]);
  exit;
}

/* ---------- 6. insert the request row ---------- */
$stmt = $mysqli->prepare(
  "INSERT INTO customise_requests
   (name, email, mobile_number, user_customise_game)
   VALUES (?,?,?,?)"
);
$stmt->bind_param('ssss', $name, $email, $phone, $game);

if (!$stmt->execute()) {
  echo json_encode(['success' => false, 'error' => 'Insert failed']);
  exit;
}

echo json_encode(['success' => true]);
?>
