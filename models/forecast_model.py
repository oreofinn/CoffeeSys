<?php
// pages/forecast.php

// 1) Bring in your DB connection + sidebar/navigation + header
include '../includes/connection.php';
include '../includes/sidebar.php';
include '../includes/header.php';

// 2) Year picker logic (same as expiration.php)
$currentYear = date('Y');
$resMin      = mysqli_query($db, "SELECT MIN(YEAR(`DATE`)) AS min_year FROM `transaction`");
$rowMin      = mysqli_fetch_assoc($resMin);
$minYear     = $rowMin['min_year'] ? (int)$rowMin['min_year'] : $currentYear;

$selected    = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear;

// 3) Helper: fetch top products for any date range, including medium/large breakdown
function getEventForecast($db, $start, $end, $limit = 5) {
    $sql = "
      SELECT
        p.NAME AS product_name,
        SUM(td.QTY) AS total_items,
        -- assume price_medium / price_large on product table
        SUM(CASE WHEN td.PRICE = p.price_medium THEN td.QTY ELSE 0 END) AS medium_qty,
        SUM(CASE WHEN td.PRICE = p.price_large  THEN td.QTY ELSE 0 END) AS large_qty,
        SUM(td.QTY * td.PRICE) AS total_revenue
      FROM transaction_details td
      JOIN `transaction` t ON t.TRANS_ID = td.TRANS_ID
      JOIN product p       ON p.NAME     = td.PRODUCT
      WHERE DATE(t.`DATE`) BETWEEN ? AND ?
      GROUP BY p.NAME
      ORDER BY total_items DESC
      LIMIT ?
    ";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ssi', $start, $end, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// 4) Define your mock-up events exactly as in your designs
$events = [
  'Seasons' => [
    ['name'=>'Summer break', 'start'=>"$selected-03-01", 'end'=>"$selected-05-31"],
    ['name'=>'Rainy season', 'start'=>"$selected-06-01", 'end'=>"$selected-11-30"],
  ],
  'Fixed-date celebrations' => [
    ['name'=>"Valentine’s Day", 'start'=>"$selected-02-14", 'end'=>"$selected-02-14"],
    ['name'=>"Christmas Day",   'start'=>"$selected-12-25", 'end'=>"$selected-12-25"],
    ['name'=>"New Year’s Day",  'start'=>"$selected-01-01", 'end'=>"$selected-01-01"],
  ],
  'Academic-year events' => [
    ['name'=>'First day of classes',   'start'=>"$selected-06-01", 'end'=>"$selected-06-07"],
    ['name'=>'Graduation Ceremonies',  'start'=>"$selected-03-01", 'end'=>"$selected-04-30"],
  ],
];
?>

<div class="container-fluid">
  <!-- Page Title -->
  <h1 class="h3 mb-4 text-gray-800">Product Forecasting</h1>

  <!-- Year selector -->
  <div class="row mb-4">
    <div class="col-md-3">
      <form method="get">
        <label for="year">Select Year</label>
        <select id="year" name="year" class="form-control" onchange="this.form.submit()">
          <?php for($y=$minYear; $y<=$currentYear+5; $y++): ?>
            <option value="<?= $y ?>" <?= $y=== $selected ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </form>
    </div>
  </div>

  <!-- Loop through each event category -->
  <?php foreach($events as $section => $list): ?>
    <!-- Section Header -->
    <h2 class="h4 mb-3 text-gray-800"><?= htmlspecialchars($section) ?></h2>

    <!-- Each Event Card -->
    <?php foreach($list as $evt): ?>
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <strong><?= htmlspecialchars($evt['name']) ?></strong>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <?php
              $rows = getEventForecast($db, $evt['start'], $evt['end'], 5);
            ?>
            <table class="table table-bordered" width="100%" cellspacing="0">
              <thead class="thead-light">
                <tr>
                  <th>Product Name</th>
                  <th>No. Item Sold</th>
                  <th>Medium Size</th>
                  <th>Large Size</th>
                  <th>Total Revenue</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($rows)): ?>
                  <tr><td colspan="5" class="text-center">No data</td></tr>
                <?php else: ?>
                  <?php foreach($rows as $r): ?>
                    <tr>
                      <td><?= htmlspecialchars($r['product_name']) ?></td>
                      <td><?= number_format($r['total_items']) ?> pcs</td>
                      <td><?= number_format($r['medium_qty']) ?> pcs</td>
                      <td><?= number_format($r['large_qty']) ?> pcs</td>
                      <td>₱<?= number_format($r['total_revenue'],2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endforeach; ?>

</div>

<?php include '../includes/footer.php'; ?>
