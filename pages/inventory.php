<?php
include '../includes/connection.php';
include '../includes/sidebar.php';

// Unit labels
$unitLabels = [
  'kg' => 'Kilogram',
  'g'  => 'Gram',
  'lt' => 'Liter',
  'ml' => 'Milliliter',
];

// ─── Inventory Page Title + Expiration Alert Legend ───────────────────────
echo '<div class="card shadow mb-4">';
echo '  <div class="card-header py-3">';
echo '    <h4 class="m-0 font-weight-bold text-primary">Inventory</h4>';
echo '  </div>';
echo '  <div class="card-body">';
echo '    <h5 class="font-weight-bold" style="margin-bottom: -35px;">Expiration Alert Legend</h5>';
echo '  </div>';
echo '  <div class="card-body">';
echo '    <ul class="list-unstyled mb-0">';
echo '      <li><span class="badge badge-danger">Expired</span> - Ingredient has passed its expiration date.</li>';
echo '      <li><span class="badge badge-danger">Critical</span> - Expires within 5 days. Immediate attention required.</li>';
echo '      <li><span class="badge badge-warning">Warning</span> - Expires within 6 to 14 days. Plan to use soon.</li>';
echo '      <li><span class="badge badge-info">Notice</span> - Expires within 15 to 30 days. Monitor usage.</li>';
echo '    </ul>';
echo '  </div>';
echo '</div>';
// ──────────────────────────────────────────────────────────────────────────

// Fetch all ingredient types
$typeQuery = "SELECT type_id, type_name FROM ingredient_type ORDER BY type_name";
$typeResult = mysqli_query($db, $typeQuery) or die(mysqli_error($db));

// Loop through each type and get ingredients under it
while ($typeRow = mysqli_fetch_assoc($typeResult)) {
  $typeId = $typeRow['type_id'];
  $typeName = ucwords(str_replace('_', ' ', $typeRow['type_name']));

  echo '<div class="card shadow mb-4">';
  echo '<div class="card-header py-3">';
  echo "<h4 class='m-2 font-weight-bold text-primary'>$typeName</h4>";
  echo '</div><div class="card-body">';
  echo '<div class="table-responsive">';
  echo '<table class="table table-bordered inventoryTable" id="typeTable' . $typeId . '" width="100%" cellspacing="0">';
  echo '<thead><tr>
          <th>Code</th>
          <th>Name</th>
          <th>Quantity</th>
          <th>Unit</th>
          <th>Supplier</th>
          <th>Expiry Date</th>
        </tr></thead><tbody>';

  $ingQuery = "
    SELECT 
      i.INGREDIENTS_CODE,
      i.ING_NAME,
      i.ING_QUANTITY,
      i.UNIT,
      s.company_name AS SUPPLIER_NAME,
      i.EXPIRATION_DATE
    FROM ingredients i
    LEFT JOIN supplier s ON i.supplier_id = s.supplier_id
    WHERE i.type_id = $typeId AND i.ING_QUANTITY > 0
    ORDER BY i.ING_NAME ASC
  ";
  $ingResult = mysqli_query($db, $ingQuery) or die(mysqli_error($db));

  while ($row = mysqli_fetch_assoc($ingResult)) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['INGREDIENTS_CODE']) . '</td>';
    echo '<td>' . htmlspecialchars($row['ING_NAME']) . '</td>';
    echo '<td>' . htmlspecialchars($row['ING_QUANTITY']) . '</td>';
    echo '<td>' . ($unitLabels[$row['UNIT']] ?? $row['UNIT']) . '</td>';
    echo '<td>' . ($row['SUPPLIER_NAME'] ?? 'No Supplier') . '</td>';
    
    if (!empty($row['EXPIRATION_DATE'])) {
      $expiryDate = new DateTime($row['EXPIRATION_DATE']);
      $today = new DateTime();
      $interval = $today->diff($expiryDate);
      $daysRemaining = $expiryDate > $today ? $interval->days : -$interval->days;
      echo '<td>' . htmlspecialchars($expiryDate->format('Y-m-d'));
      if ($daysRemaining < 0) {
        echo ' <span class="badge badge-danger">Expired</span>';
      } elseif ($daysRemaining <= 5) {
        echo ' <span class="badge badge-danger">Critical</span>';
      } elseif ($daysRemaining <= 14) {
        echo ' <span class="badge badge-warning">Warning</span>';
      } elseif ($daysRemaining <= 30) {
        echo ' <span class="badge badge-info">Notice</span>';
      }
      echo '</td>';
    } else {
      echo '<td>Not set</td>';
    }

    echo '</tr>';
  }

  echo '</tbody></table></div></div></div>';
}

include '../includes/footer.php';
?>

<!-- ─── DataTables CDN ───────────────────────────────────────────────────── -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<!-- ─── Activate DataTables for all tables with class inventoryTable ─────── -->
<script>
  $(document).ready(function() {
    $('.inventoryTable').DataTable({
      "pageLength": 10,
      "lengthMenu": [5, 10, 25, 50],
      "ordering": true,
      "language": {
        search: "Search:",
        lengthMenu: "Show _MENU_ entries"
      }
    });
  });
</script>
