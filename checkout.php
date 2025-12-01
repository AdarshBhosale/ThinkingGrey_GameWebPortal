<?php
/* ----- DB credentials ----- */
$host = "localhost";
$user = "root";      // <- replace
$pass = "";      // <- replace
$db = "tggamewebsite";

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
  die("Connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

/* ----- collect & sanitise POST data ----- */
$first = $_POST['first_name'] ?? '';
$last = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$street = $_POST['street'] ?? '';
$city = $_POST['city'] ?? '';
$state = $_POST['state'] ?? '';
$zip = $_POST['zipcode'] ?? '';
$country = $_POST['country'] ?? '';
$phone = $_POST['phone'] ?? '';

$cart = json_decode($_POST['cart_json'] ?? '[]', true);
$totals = json_decode($_POST['totals_json'] ?? '{}', true);

$subtotal = $totals['subtotal'] ?? 0;
$gst = $totals['gst'] ?? 0;
$total = $totals['total'] ?? 0;

/* basic guard */
if (!$first || !$last || !$email || empty($cart)) {
  die("Missing data – order aborted.");
}

$mysqli->begin_transaction();

try {
  /* 1 ▸ customers ------------------------------------------------- */
  $custSQL = "INSERT INTO customers
      (first_name,last_name,email,street,city,state,zipcode,country,phone)
      VALUES (?,?,?,?,?,?,?,?,?)";
  $stmt = $mysqli->prepare($custSQL);
  $stmt->bind_param("sssssssss", $first, $last, $email, $street, $city, $state, $zip, $country, $phone);
  $stmt->execute();
  $customer_id = $stmt->insert_id;
  $stmt->close();

  /* 2 ▸ orders ---------------------------------------------------- */
  $orderSQL = "INSERT INTO orders
      (customer_id,subtotal,gst,total) VALUES (?,?,?,?)";
  $stmt = $mysqli->prepare($orderSQL);
  $stmt->bind_param("iddd", $customer_id, $subtotal, $gst, $total);
  $stmt->execute();
  $order_id = $stmt->insert_id;
  $stmt->close();

  /* 3 ▸ order_items ---------------------------------------------- */
  $itemSQL = "INSERT INTO order_items
      (order_id,product_title,unit_price,quantity,line_total)
      VALUES (?,?,?,?,?)";
  $stmt = $mysqli->prepare($itemSQL);

  foreach ($cart as $title) {                 // cart is an array of product names
    /* look-up your catalogue to get price & qty.                     *
     * Here we assume qty = 1 and you already sent the price back.    */
    $price = 0;      // default
    switch ($title) {  // tiny price map identical to PRODUCTS JS obj
      case "Skill Slice":
        $price = 10000;
        break;
      case "Archery":
        $price = 9000;
        break;
      case "Color Strike":
        $price = 7200;
        break;
    }
    $qty = 1;
    $line = $price * $qty;
    $stmt->bind_param("isdid", $order_id, $title, $price, $qty, $line);
    $stmt->execute();
  }
  $stmt->close();

  /* 4 ▸ done ------------------------------------------------------ */
  $mysqli->commit();
  echo "✅ Order placed!  Your reference # is {$order_id}";
} catch (Throwable $e) {
  $mysqli->rollback();
  http_response_code(500);
  echo "Something went wrong: " . $e->getMessage();
}
?>