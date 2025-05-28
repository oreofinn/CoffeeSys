<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost','root','','scms');
if ($conn->connect_error) {
  http_response_code(500);
  exit(json_encode(['error'=>'DB connect failed']));
}

// optional: validate view param, but here we ignore time grouping for simplicity
// Just grab total qty sold per product:
$sql = "
  SELECT
    p.PROD_NAME AS label,
    SUM(rt.qty) AS total
  FROM pos_transac t
  JOIN recipe_transac rt ON rt.trans_id = t.TRANS_ID
  JOIN products p       ON p.PROD_ID   = rt.prod_id
  GROUP BY p.PROD_ID
  ORDER BY total DESC
";

$res = $conn->query($sql);
$labels = [];
$data   = [];
while ($row = $res->fetch_assoc()) {
  $labels[] = $row['label'];
  $data[]   = (int)$row['total'];
}

echo json_encode([
  'labels' => $labels,
  'data'   => $data
]);
