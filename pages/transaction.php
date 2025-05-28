<?php
include '../includes/connection.php';
include '../includes/sidebar.php';

// Determine filter mode: all, year, or month
$view  = $_GET['view']  ?? 'all';
$year  = $_GET['year']  ?? '';
$month = $_GET['month'] ?? '';

// Build WHERE clauses based on selection
$where = [];
if ($view === 'year' && preg_match('/^\d{4}$/', $year)) {
    $where[] = "YEAR(t.DATE) = " . intval($year);
}
if ($view === 'month' && preg_match('/^\d{4}-\d{2}$/', $month)) {
    list($y, $m) = explode('-', $month);
    $where[] = "YEAR(t.DATE)  = " . intval($y);
    $where[] = "MONTH(t.DATE) = " . intval($m);
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch distinct years for the year selector
$years = [];
$yrQ = "SELECT DISTINCT YEAR(DATE) AS y FROM `transaction` ORDER BY y DESC";
$yrR = mysqli_query($db, $yrQ) or die(mysqli_error($db));
while ($r = mysqli_fetch_assoc($yrR)) {
    $years[] = $r['y'];
}

// Main filtered query
$sql = "
SELECT
  td.TRANS_ID,
  t.DATE           AS order_date,
  td.PRODUCT       AS product_name,
  td.QTY           AS quantity,
  td.PRICE         AS unit_price,
  (td.QTY * td.PRICE) AS total_price
FROM
  transaction_details td
  JOIN `transaction` t ON td.TRANS_ID = t.TRANS_ID
$whereSQL
ORDER BY t.DATE DESC
";
$res = mysqli_query($db, $sql) or die(mysqli_error($db));
?>

<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <h4 class="m-0 font-weight-bold text-primary">
      Order History
      <?php
      if ($view === 'year'  && $year)  echo "| {$year}";
      if ($view === 'month' && $month) echo "| " . date('F, Y', strtotime($month . '-01'));
      ?>
    </h4>

    <form method="get" class="form-inline">
      <!-- Preserve view when selecting year/month -->
      <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">

      <label class="mr-2">View by:</label>
      <div class="btn-group mr-3" role="group">
        <button type="submit" name="view" value="all"   class="btn btn-outline-secondary <?= $view==='all'   ? 'active':'' ?>">All</button>
        <button type="submit" name="view" value="month" class="btn btn-outline-secondary <?= $view==='month' ? 'active':'' ?>">Month</button>
        <button type="submit" name="view" value="year"  class="btn btn-outline-secondary <?= $view==='year'  ? 'active':'' ?>">Year</button>
      </div>

      <?php if ($view==='year'): ?>
        <select name="year" class="form-control mr-2" onchange="this.form.submit()">
          <option value="">-- Select Year --</option>
          <?php foreach ($years as $y): ?>
            <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
          <?php endforeach; ?>
        </select>
      <?php elseif ($view==='month'): ?>
        <input
          type="month"
          name="month"
          class="form-control mr-2"
          value="<?= htmlspecialchars($month) ?>"
          onchange="this.form.submit()"
        >
      <?php endif; ?>
    </form>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>Order Number</th>
            <th>Order Date</th>
            <th>Product</th>
            <th># of Items</th>
            <th class="text-right">Price per unit</th>
            <th class="text-right">Total Price</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
              <td><?= htmlspecialchars($row['TRANS_ID']) ?></td>
              <td><?= htmlspecialchars($row['order_date']) ?></td>
              <td><?= htmlspecialchars($row['product_name']) ?></td>
              <td><?= (int)$row['quantity'] ?></td>
              <td class="text-right"><?= number_format($row['unit_price'],2) ?></td>
              <td class="text-right"><?= number_format($row['total_price'],2) ?></td>
              <td class="text-center">
                <a class="btn btn-primary btn-sm" href="trans_view.php?action=edit&amp;id=<?= $row['TRANS_ID'] ?>">
                  <i class="fas fa-fw fa-th-list"></i> View
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
