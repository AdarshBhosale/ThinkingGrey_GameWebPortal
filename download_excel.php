<?php
/**********************************************************************
 * download_excel.php  –  streams call_requests / customise_requests
 * ---------------------------------------------------------------
 * ?view=call        →  call_requests
 * ?view=customise   →  customise_requests
 * ---------------------------------------------------------------
 * Requires:  composer require phpoffice/phpspreadsheet
 **********************************************************************/
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli('localhost', 'root', '', 'gameweb');

/* 1. Which table? */
$view = ($_GET['view'] ?? 'call') === 'customise' ? 'customise' : 'call';
if ($view === 'call') {
  $table = 'call_requests';
  $cols = ['ID', 'First Name', 'Last Name', 'Email', 'Mobile', 'Interested Games', 'Requested At'];
  $sql = "SELECT id, first_name, last_name, email_address, mobile_number,
                     user_interested_games, requested_at FROM $table";
} else {
  $table = 'customise_requests';
  $cols = ['ID', 'Name', 'Email', 'Mobile', 'Customise Game', 'Requested At'];
  $sql = "SELECT id, name, email, mobile_number, user_customise_game, requested_at FROM $table";
}

/* 2. Fetch */
$data = $mysqli->query($sql)->fetch_all(MYSQLI_NUM);

/* 3. Build sheet */
$sheet = new Spreadsheet();
$sheet->getActiveSheet()->fromArray($cols, NULL, 'A1');
$sheet->getActiveSheet()->fromArray($data, NULL, 'A2');
$sheet->getActiveSheet()->getStyle('A1:' .
  chr(64 + count($cols)) . '1')->getFont()->setBold(true);

/* 4. Stream */
$filename = $view . '_requests_' . date('Ymd_His') . '.xlsx';
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
$writer = new Xlsx($sheet);
$writer->save('php://output');
exit;
