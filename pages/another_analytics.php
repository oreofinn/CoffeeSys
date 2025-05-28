<?php
// ────────────────────────────────────────────────────────────────
// another_analytics.php — returns JSON for your dashboard charts
// ────────────────────────────────────────────────────────────────

// 0) Don’t ever leak PHP errors or HTML
ini_set('display_errors', '1');
error_reporting(E_ALL);


// 1) JSON header
header('Content-Type: application/json');

// 2) Connect to your SCMS database
$conn = new mysqli('localhost','root','','scms');
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(['error'=>'DB connect failed']);
  exit;
}

// 3) Determine the grouping based on view parameter
$view = in_array($_GET['view'] ?? '', ['day','week','month','year'])
      ? $_GET['view'] : 'month';

      
// ── Build a WHERE clause for the selected period ─────────────────
switch ($view) {
  case 'day':
    $where = "DATE(t.`DATE`) = CURDATE()";
    break;
  case 'week':
    $where = "YEARWEEK(t.`DATE`,1) = YEARWEEK(CURDATE(),1)";
    break;
  case 'year':
    $where = "YEAR(t.`DATE`) = YEAR(CURDATE())";
    break;
  default:  // month
    $where = "YEAR(t.`DATE`) = YEAR(CURDATE()) AND MONTH(t.`DATE`) = MONTH(CURDATE())";
    break;
}



// ────────────────────────────────────────────────────────────────
// 4) Trend: top‐selling single product
// ────────────────────────────────────────────────────────────────
$trendSql = "
  SELECT
    td.PRODUCT               AS name,
    SUM(td.QTY)              AS sold,
    SUM(td.QTY * td.PRICE)   AS revenue,
    DATE_FORMAT(MAX(t.`DATE`),'%b %e, %Y') AS date
  FROM `transaction` t
  JOIN `transaction_details` td ON td.TRANS_ID = t.TRANS_ID
  WHERE {$where}                             -- <<< add this
  GROUP BY td.PRODUCT
  ORDER BY sold DESC
  LIMIT 1
";
$trendRes = $conn->query($trendSql);
if (!$trendRes) {
  http_response_code(500);
  echo json_encode(['error'=>$conn->error]);
  exit;
}
$trd = $trendRes->fetch_assoc();

// ────────────────────────────────────────────────────────────────
// 5) Product Performance: top 10 products
// ────────────────────────────────────────────────────────────────
$ppSql = "
  SELECT
    td.PRODUCT     AS label,
    SUM(td.QTY)    AS total
  FROM `transaction` t
  JOIN `transaction_details` td ON td.TRANS_ID = t.TRANS_ID
  GROUP BY td.PRODUCT
  ORDER BY total DESC
  LIMIT 10
";

$ppRes = $conn->query($ppSql);
if (!$ppRes) {
  http_response_code(500);
  echo json_encode(['error'=>$conn->error]);
  exit;
}

$pp_labels = $pp_data = [];
while ($r = $ppRes->fetch_assoc()) {
  $pp_labels[] = $r['label'];
  $pp_data[]   = (int)$r['total'];
}

// ────────────────────────────────────────────────────────────────
// 6) Size Distribution by product.unit (doughnut chart)
// ────────────────────────────────────────────────────────────────
$sdSql = "
  SELECT
    p.unit                             AS label,
    SUM(td.QTY)                        AS total,
    DATE_FORMAT(MAX(t.`DATE`),'%b %e, %Y') AS date
  FROM `transaction` t
  JOIN `transaction_details` td ON td.TRANS_ID = t.TRANS_ID
  JOIN `product` p         ON p.NAME   = td.PRODUCT
  WHERE {$where}                             -- <<< add this
  GROUP BY p.unit
";
$sdRes = $conn->query($sdSql);
if (!$sdRes) {
  http_response_code(500);
  echo json_encode(['error' => $conn->error]);
  exit;
}

$sd_labels = [];
$sd_data   = [];
$sd_date   = '';
while ($r = $sdRes->fetch_assoc()) {
  $sd_labels[] = $r['label'];
  $sd_data[]   = (int)$r['total'];
  $sd_date     = $r['date'];
}



// ────────────────────────────────────────────────────────────────
// 7) Output final JSON
// ────────────────────────────────────────────────────────────────
echo json_encode([
  'trend' => [
    'name'    => $trd['name'],
    'sold'    => (int)$trd['sold'],
    'revenue' => (float)$trd['revenue'],
    'date'    => $trd['date'],
    // adjust if you have real images by product name:
    'imageUrl'=> "assets/img/{$trd['name']}.png"
  ],
  'productPerformance' => [
    'labels' => $pp_labels,
    'data'   => $pp_data
  ],
  'sizeDistribution' => [
    'date'   => $sd_date,
    'labels' => $sd_labels,
    'data'   => $sd_data
  ],
]);


