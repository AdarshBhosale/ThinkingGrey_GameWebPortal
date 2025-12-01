<?php
header('Content-Type: application/json');

$dbHost = 'localhost';
$dbUser = 'server2_gameweb';
$dbPass = 'WT#^-yf=z]l$PgE!';
$dbName = 'server2_gameweb';

$pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass,
               [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]);

# 1️⃣ make sure the table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(60)  NOT NULL,
  last_name  VARCHAR(60)  NOT NULL,
  email      VARCHAR(120) NOT NULL,
  phone      VARCHAR(10)  NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8;");

# 2️⃣ grab JSON payload
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) exit(json_encode(['status'=>'bad_input']));

$fname = trim($data['first']);
$lname = trim($data['last']);
$email = trim($data['email']);
$phone = trim($data['phone']);

# 3️⃣ duplicate check
$st = $pdo->prepare('SELECT id FROM users WHERE phone = ?');
$st->execute([$phone]);
if ($st->fetch()) exit(json_encode(['status'=>'exists']));

# 4️⃣ insert
$st = $pdo->prepare('INSERT INTO users(first_name,last_name,email,phone)
                     VALUES(?,?,?,?)');
$st->execute([$fname,$lname,$email,$phone]);

echo json_encode(['status'=>'ok','name'=>$fname]);
