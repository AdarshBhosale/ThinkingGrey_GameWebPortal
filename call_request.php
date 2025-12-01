<?php
/*  TG Gaming – unified endpoint
    • inserts into call_requests
    • fires transactional e-mail
------------------------------------------------------------ */
header('Content-Type: application/json');

try {
  /* 1️⃣  guard */
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    throw new Exception('POST only');
  }

  /* 2️⃣  collect + sanitise */
  $first = trim($_POST['first_name']          ?? '');
  $last  = trim($_POST['last_name']           ?? '');
  $email = trim($_POST['email']               ?? '');
  $phone = trim($_POST['phone']               ?? '');
  $games = trim($_POST['cart_json']           ?? '[]');     // JSON string
  $when  = date('Y-m-d H:i:s');

  if (!$phone || !$games) throw new Exception('Missing data');

  /* 3️⃣  mail the TG team (reuse your existing code) */
  $to      = 'mugdha@thinkinggrey.com';
  $subject = 'New Request for Call';
  $body    = "Name   : $first $last\n".
             "Email  : $email\n".
             "Phone  : +91 $phone\n".
             "Games  : $games\n".
             "Time   : $when\n";
  @mail($to, $subject, $body, "From: noreply@thinkinggrey.com");

  /* 4️⃣  DB insert */
  $dbHost = 'localhost';
  $dbUser = 'server2_gameweb';
  $dbPass = 'WT#^-yf=z]l$PgE!';
  $dbName = 'server2_gameweb';

  $pdo = new PDO(
    "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
    $dbUser, $dbPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );

  $sql = 'INSERT INTO call_requests
          (first_name,last_name,email_address,mobile_number,
           user_interested_games,requested_at)
          VALUES (?,?,?,?,?,?)';

  $pdo->prepare($sql)->execute(
     [$first,$last,$email,$phone,$games,$when]
  );

  /* 5️⃣  done */
  echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
}
catch (Exception $e){
  http_response_code(400);
  echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
