<?php
header('Content-Type: application/json');

// 1) DB connection (reuse yours)
$conn = new mysqli('localhost','root','','scms');
if ($conn->connect_error) {
  http_response_code(500);
  exit(json_encode(['error'=>'DB connection failed']));
}

// 2) Determine grouping
$view = in_array($_GET['view'] ?? '', ['day','week','month','year'])
      ? $_GET['view'] : 'month';

// 3) Build SQL based on $view
switch ($view) {
  case 'day':
    $fmt   = "DATE_FORMAT(`DATE`,'%Y-%m-%d')";
    $group = "DATE(`DATE`)";
    break;
  case 'week':
    // ISO week label like “2025-W18”
    $fmt   = "CONCAT(YEAR(`DATE`),'-W',LPAD(WEEK(`DATE`,1),2,'0'))";
    $group = "YEAR(`DATE`), WEEK(`DATE`,1)";
    break;
  case 'year':
    $fmt   = "YEAR(`DATE`)";
    $group = "YEAR(`DATE`)";
    break;
  case 'month':
  default:
    $fmt   = "DATE_FORMAT(`DATE`,'%b %Y')";
    $group = "YEAR(`DATE`), MONTH(`DATE`)";
}

// 4) Fetch aggregated data
$sql = "
  SELECT
    {$fmt}           AS label,
    SUM(GRANDTOTAL)  AS revenue,
    COUNT(*)         AS sold
  FROM `transaction`
  GROUP BY {$group}
  ORDER BY MIN(`DATE`)
";
$res = $conn->query($sql);

// 5) Build arrays
$labels  = [];
$revenue = [];
$sold    = [];
while ($r = $res->fetch_assoc()) {
  $labels[]  = $r['label'];
  $revenue[] = (float)$r['revenue'];
  $sold[]    = (int)$r['sold'];
}

// 6) Return JSON
echo json_encode([
  'labels'  => $labels,
  'revenue' => $revenue,
  'sold'    => $sold
]);
