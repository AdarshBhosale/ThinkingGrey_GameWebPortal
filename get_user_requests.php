<?php
header('Content-Type: application/json');

$phone = preg_replace('/\D/','', $_GET['user'] ?? '');
if (!$phone) {
  echo json_encode(['success'=>false, 'msg'=>'phone missing']); exit;
}

$mysqli = new mysqli('localhost','server2_gameweb','WT#^-yf=z]l$PgE!','server2_gameweb');
if ($mysqli->connect_errno) {
  echo json_encode(['success'=>false,'msg'=>'DB‐error']); exit;
}

/* --- 1. customisation requests ----------------------------------- */
$stmt = $mysqli->prepare(
  "SELECT user_customise_game      AS game,
          requested_at
   FROM   customise_requests
   WHERE  REPLACE(mobile_number,'+','') LIKE CONCAT('%',?) 
   ORDER  BY requested_at DESC"
);
$stmt->bind_param('s',$phone);  $stmt->execute();
$custom = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* --- 2. “I’m interested” / call requests ------------------------- */
$stmt = $mysqli->prepare(
  "SELECT user_interested_games    AS game,
          requested_at
   FROM   call_requests
   WHERE  REPLACE(mobile_number,'+','') LIKE CONCAT('%',?)
   ORDER  BY requested_at DESC"
);
$stmt->bind_param('s',$phone);  $stmt->execute();
$calls  = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success'=>true,'customise'=>$custom,'calls'=>$calls]);
?>
