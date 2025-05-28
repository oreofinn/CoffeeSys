<?php
// pages/expiration.php

// 1) Database + sidebar
include '../includes/connection.php';
include '../includes/sidebar.php';

// 2) Out‐of‐stock query
$outSql = "
  SELECT
    i.INGREDIENTS_ID,
    i.ING_NAME,
    i.ING_QUANTITY AS qty,
    i.UNIT,
    it.type_name   AS category_name
  FROM ingredients i
  LEFT JOIN ingredient_type it
    ON i.type_id = it.type_id
  WHERE i.ING_QUANTITY <= 0
  ORDER BY i.ING_NAME
";
$outRes = mysqli_query($db, $outSql) or die(mysqli_error($db));

// 3) In‐stock batches query
$inSql = "
  SELECT
    i.INGREDIENTS_ID,
    i.INGREDIENTS_CODE,
    i.ING_NAME,
    i.ING_QUANTITY    AS qty,
    i.UNIT,
    it.type_name      AS category_name,
    i.STOCK_DATE      AS stock_date,
    i.EXPIRATION_DATE AS expiration_date
  FROM ingredients i
  LEFT JOIN ingredient_type it
    ON i.type_id = it.type_id
  WHERE i.ING_QUANTITY > 0
  ORDER BY i.STOCK_DATE DESC, i.EXPIRATION_DATE ASC
";
$inRes = mysqli_query($db, $inSql) or die(mysqli_error($db));
?>

<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">Expiration Tracker</h1>

  <!-- Out-of-Stock Card -->
  <?php if (mysqli_num_rows($outRes) > 0): ?>
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <strong>Out of Stock Ingredients</strong>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered mb-0">
            <thead class="thead-light">
              <tr>
                <th>Ingredient</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Category</th>
                <th>Stock Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($o = mysqli_fetch_assoc($outRes)): ?>
                <tr>
                  <td><?= htmlspecialchars($o['ING_NAME']) ?></td>
                  <td><?= (int)$o['qty'] ?></td>
                  <td><?= htmlspecialchars($o['UNIT']) ?></td>
                  <td><?= htmlspecialchars($o['category_name']) ?></td>
                  <td><span class="badge badge-expired">Out of Stock</span></td>
                  <td>
                    <a href="pro_ingredients.php?edit_id=<?= $o['INGREDIENTS_ID'] ?>">Edit</a>
                    <a href="ing_transac.php?action=delete&id=<?= $o['INGREDIENTS_ID'] ?>">Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- In-Stock Batch Cards -->
  <?php
  $currentStock = null;
  $lowThreshold = 5; // “Nearly Out” cutoff

  while ($row = mysqli_fetch_assoc($inRes)) {
    // When stock_date changes, close previous card
    if ($row['stock_date'] !== $currentStock) {
      if ($currentStock !== null) {
        echo '</tbody></table></div></div></div>';
      }
      $currentStock = $row['stock_date'];

      // Format header
      $dt         = new DateTime($currentStock);
      $stockCode  = 'SD' . $dt->format('mdy');               // e.g. SD041024
      $stockLabel = $dt->format('F j, Y \a\t g:i A');        // e.g. April 10, 2024 at 2:15 AM

      echo '<div class="card shadow mb-4">';
      echo '  <div class="card-header py-3 bg-light">';
      echo '    <strong>STOCK NO. ' . htmlspecialchars($stockCode) . '</strong><br>';
      echo '    <small>Stocked at: ' . $stockLabel . '</small>';
      echo '  </div>';
      echo '  <div class="card-body"><div class="table-responsive">';
      echo '    <table class="table table-bordered mb-0">';
      echo '      <thead class="thead-light">';
      echo '        <tr>';
      echo '          <th>Ingredient</th><th>Qty</th><th>Unit</th>';
      echo '          <th>Category</th><th>Expiry Date</th>';
      echo '          <th>Remaining Days</th><th>Expiry Status</th>';
      echo '          <th>Stock Status</th><th>Actions</th>';
      echo '        </tr>';
      echo '      </thead><tbody>';
    }

    // Calculate remaining days
    $exp      = new DateTime($row['expiration_date']);
    $today    = new DateTime();
    $diff     = $today->diff($exp);
    $daysLeft = $exp > $today ? $diff->days : -$diff->days;

    // Expiry‐Status badge
    if      ($daysLeft <   0) $expBadge = '<span class="badge badge-expired">Expired</span>';
    elseif  ($daysLeft <=  5) $expBadge = '<span class="badge badge-critical">Critical</span>';
    elseif  ($daysLeft <= 14) $expBadge = '<span class="badge badge-warning">Warning</span>';
    elseif  ($daysLeft <= 30) $expBadge = '<span class="badge badge-notice">Notice</span>';
    else                      $expBadge = '<span class="badge badge-far">Far</span>';

    // Stock‐Status badge
    if      ($row['qty'] <=   0) $stkBadge = '<span class="badge badge-expired">Out of Stock</span>';
    elseif  ($row['qty'] <= $lowThreshold) $stkBadge = '<span class="badge badge-warning">Nearly Out</span>';
    else                           $stkBadge = '';

    // Render row
    echo '<tr>';
    echo '  <td>' . htmlspecialchars($row['ING_NAME'])        . '</td>';
    echo '  <td>' . (int)$row['qty']                          . '</td>';
    echo '  <td>' . htmlspecialchars($row['UNIT'])            . '</td>';
    echo '  <td>' . htmlspecialchars($row['category_name'])   . '</td>';
    echo '  <td>' . htmlspecialchars($row['expiration_date']) . '</td>';
    echo '  <td>' . $daysLeft . ' days</td>';
    echo '  <td>' . $expBadge                                 . '</td>';
    echo '  <td>' . $stkBadge                                 . '</td>';
    echo '  <td>';
    echo '    <a href="pro_ingredients.php?edit_id=' . $row['INGREDIENTS_ID'] . '">Edit</a> ';
    echo '    <a href="ing_transac.php?action=delete&id=' . $row['INGREDIENTS_ID'] . '">Delete</a>';
    echo '  </td>';
    echo '</tr>';
  }

  // Close final batch card
  if ($currentStock !== null) {
    echo '</tbody></table></div></div></div>';
  }
  ?>
</div>

<?php include '../includes/footer.php'; ?>
