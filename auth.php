<?php
/* auth.php  ─ store / update user profiles ---------------------------- */
header('Content-Type: application/json; charset=utf-8');
$data = json_decode(file_get_contents('php://input'), true);
if(!$data){ http_response_code(400); exit('{"error":"Bad JSON"}'); }

$first = trim($data['first'] ?? '');
$last  = trim($data['last']  ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');

if(!$first || !$last || !$email || !preg_match('/^\d{10}$/',$phone)){
  http_response_code(422); exit('{"error":"Invalid fields"}');
}

$dbHost='localhost';
$dbUser='server2_gameweb';
$dbPass='WT#^-yf=z]l$PgE!';
$dbName='server2_gameweb';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try{
  $db = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
  $db->set_charset('utf8mb4');

  /* ① create table once */
  $db->query("CREATE TABLE IF NOT EXISTS users(
      id INT AUTO_INCREMENT PRIMARY KEY,
      first_name      VARCHAR(100),
      last_name       VARCHAR(100),
      email           VARCHAR(150) UNIQUE,
      mobile_number   VARCHAR(15)  UNIQUE,
      created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

  /* ② insert or update */
  $sql = "INSERT INTO users(first_name,last_name,email,mobile_number)
          VALUES (?,?,?,?)
          ON DUPLICATE KEY UPDATE
            first_name=VALUES(first_name),
            last_name =VALUES(last_name)";
  $stmt = $db->prepare($sql);
  $stmt->bind_param('ssss',$first,$last,$email,$phone);
  $stmt->execute();
  echo '{"status":"ok"}';
}catch(Exception $e){
  http_response_code(500);
  echo '{"error":"db error"}';
}
