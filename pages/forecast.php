<?php
// pages/forecast.php

// 1) Includes
include '../includes/connection.php';  // $db = mysqli_connect(...)
include '../includes/sidebar.php';

// 2) Year picker logic
$currentYear = date('Y');
$resMin = mysqli_query($db, "SELECT MIN(YEAR(`DATE`)) AS min_year FROM `transaction`");
$rowMin = mysqli_fetch_assoc($resMin);
$minYear = $rowMin['min_year'] ? (int)$rowMin['min_year'] : $currentYear;
$selected = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear;

// 3) Helper: top N products in a full-year range
function getTopProducts($db, $year, $limit = 10) {
    $start = "$year-01-01";
    $end   = "$year-12-31";
    $sql = "
      SELECT
        p.NAME AS product_name,
        SUM(td.QTY) AS total_items,
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
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

// 4) Helper: top N historical sellers for an arbitrary date window
function getEventForecast($db, $start, $end, $limit = 5) {
    $sql = "
      SELECT
        p.NAME AS product_name,
        SUM(td.QTY) AS total_items,
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

// 5) Fetch data
$yearlyTop   = getTopProducts($db, $selected, 10);
$forecastRes = mysqli_query($db, "
  SELECT MONTH(ds) AS month, SUM(yhat) AS forecast_revenue
  FROM yearly_forecast
  WHERE YEAR(ds) = {$selected}
  GROUP BY month
  ORDER BY month
");
$nextYear    = $selected + 1;
$bestRes     = mysqli_query($db, "
  SELECT product_name, ROUND(yhat) AS predicted_qty
  FROM product_yearly_forecast
  WHERE YEAR(forecast_year) = {$nextYear}
  ORDER BY predicted_qty DESC
  LIMIT 5
");

// 6) Define your mock-up events
$events = [
  'Seasons' => [
    ['Summer break',        "$selected-03-01", "$selected-05-31"],
    ['Rainy season',        "$selected-06-01", "$selected-11-30"],
  ],
  'Fixed-date celebrations' => [
    ['Valentine’s Day',     "$selected-02-14", "$selected-02-14"],
    ['Christmas Day',       "$selected-12-25", "$selected-12-25"],
    ['New Year’s Day',      "$selected-01-01", "$selected-01-01"],
  ],
  'Academic-year events' => [
    ['First day of classes',"$selected-06-01", "$selected-06-07"],
    ['Graduation Ceremonies',"$selected-03-01","$selected-04-30"],
  ],
];
?>

<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">Product Forecasting</h1>

  <!-- Year selector -->
  <div class="row mb-4">
    <div class="col-md-3">
      <form method="get">
        <label for="year">Select Year:</label>
        <select id="year" name="year" class="form-control" onchange="this.form.submit()">
          <?php for ($y = $minYear; $y <= $currentYear + 5; $y++): ?>
            <option value="<?= $y ?>" <?= $y === $selected ? 'selected' : '' ?>>
              <?= $y ?>
            </option>
          <?php endfor; ?>
        </select>
      </form>
    </div>
  </div>

  <!-- Top 10 Products Sold in Year -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <strong>Top 10 Products Sold in <?= $selected ?></strong>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>Product Name</th>
              <th>Items Sold</th>
              <th>Total Revenue (₱)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($yearlyTop as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td><?= number_format($r['total_items']) ?></td>
                <td><?= '₱' . number_format($r['total_revenue'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Forecasted Revenue by Month -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <strong>Forecasted Revenue by Month (<?= $selected ?>)</strong>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>Month</th>
              <th>Forecasted Revenue (₱)</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($f = mysqli_fetch_assoc($forecastRes)): ?>
              <tr>
                <td><?= date('F', mktime(0,0,0,$f['month'],1)) ?></td>
                <td><?= '₱' . number_format($f['forecast_revenue'], 2) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Top 5 Predicted Sellers for Next Year -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <strong>Top 5 Predicted Sellers for <?= $nextYear ?></strong>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>Product</th>
              <th>Predicted Qty</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($b = mysqli_fetch_assoc($bestRes)): ?>
              <tr>
                <td><?= htmlspecialchars($b['product_name']) ?></td>
                <td><?= number_format($b['predicted_qty']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Event-based Forecasts -->
  <?php foreach ($events as $section => $list): ?>
    <div class="card shadow mb-4">
      <div class="card-header py-3"><strong><?= $section ?></strong></div>
      <div class="card-body">
        <?php foreach ($list as [$ename, $s, $e]): ?>
          <h5 class="mb-2"><?= htmlspecialchars($ename) ?> (<?= date('M j',strtotime($s)) ?> – <?= date('M j',strtotime($e)) ?>)</h5>
          <?php $rows = getEventForecast($db, $s, $e, 5); ?>
          <div class="table-responsive mb-4">
            <table class="table table-bordered" width="100%" cellspacing="0">
              <thead><tr>
                <th>Product Name</th>
                <th>Items Sold</th>
                <th>Medium Size</th>
                <th>Large Size</th>
                <th>Total Revenue (₱)</th>
              </tr></thead>
              <tbody>
                <?php foreach ($rows as $r): ?>
                  <tr>
                    <td><?= htmlspecialchars($r['product_name']) ?></td>
                    <td><?= number_format($r['total_items']) ?></td>
                    <td><?= number_format($r['medium_qty']) ?></td>
                    <td><?= number_format($r['large_qty']) ?></td>
                    <td><?= '₱' . number_format($r['total_revenue'], 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

</div>

<?php include '../includes/footer.php'; ?>
