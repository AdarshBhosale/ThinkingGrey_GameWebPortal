<?php
header('Content-Type: application/json');

$dbHost = 'localhost';
$dbUser = 'server2_gameweb';
$dbPass = 'WT#^-yf=z]l$PgE!';
$dbName = 'server2_gameweb';

$pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass,
               [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]);

$data  = json_decode(file_get_contents('php://input'), true);
$phone = trim($data['phone'] ?? '');

$st = $pdo->prepare('SELECT first_name,email FROM users WHERE phone = ?');
$st->execute([$phone]);
$row = $st->fetch(PDO::FETCH_ASSOC);

echo $row
  ? json_encode(['status'=>'ok','name'=>$row['first_name'],'email'=>$row['email']])
  : json_encode(['status'=>'not_found']);
