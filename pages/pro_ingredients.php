<?php
include '../includes/connection.php';
include '../includes/sidebar.php';

// ─── Unit labels for display ──────────────────────────────────────────────
$unitLabels = [
    'kg' => 'Kilogram',
    'g'  => 'Gram',
    'lt' => 'Liter',
    'ml' => 'Milliliter',
];
// ────────────────────────────────────────────────────────────────────────────

// Redirect restricted users (unchanged)
$query  = 'SELECT ID, t.TYPE 
             FROM users u 
             JOIN type t ON t.TYPE_ID = u.TYPE_ID 
            WHERE ID = ' . $_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));

// Remove zero‐quantity ingredients if not used in recipes (unchanged)
$zeroIngQuery  = "SELECT INGREDIENTS_ID FROM ingredients WHERE ING_QUANTITY <= 0";
$zeroIngResult = mysqli_query($db, $zeroIngQuery) or die(mysqli_error($db));
while ($ingredient = mysqli_fetch_assoc($zeroIngResult)) {
    $id         = $ingredient['INGREDIENTS_ID'];
    $usageCheck = "SELECT COUNT(*) as count 
                     FROM recipe_ingredients 
                    WHERE ingredient_id = '$id'";
    $usageResult = mysqli_query($db, $usageCheck);
    $usageData   = mysqli_fetch_assoc($usageResult);
    if ($usageData['count'] == 0) {
        mysqli_query($db, "DELETE FROM ingredients WHERE INGREDIENTS_ID = '$id'") 
          or die(mysqli_error($db));
    } else {
        error_log("Cannot delete ingredient ID $id because it's used in recipes");
    }
}

// ─── Fetch supplier dropdown for the ADD form ───────────────────────────────
$supQuery  = "SELECT supplier_id, company_name FROM supplier";
$supResult = mysqli_query($db, $supQuery) or die(mysqli_error($db));
$sup = '<select class="form-control" name="supplier" required>';
$sup .= '<option disabled selected hidden>Select Supplier</option>';
while ($supRow = mysqli_fetch_assoc($supResult)) {
    $sup .= '<option value="' 
         . $supRow['supplier_id'] 
         . '">' 
         . htmlspecialchars($supRow['company_name']) 
         . '</option>';
}
$sup .= '</select>';
// ────────────────────────────────────────────────────────────────────────────

// ─── Fetch supplier dropdown for the EDIT form ──────────────────────────────
$supResult     = mysqli_query($db, $supQuery) or die(mysqli_error($db));
$supDropdown   = '<select class="form-control" name="supplier" id="editSupplier" required>';
$supDropdown  .= '<option disabled selected hidden>Select Supplier</option>';
while ($supRow = mysqli_fetch_assoc($supResult)) {
    $supDropdown .= '<option value="' 
                  . $supRow['supplier_id'] 
                  . '">' 
                  . htmlspecialchars($supRow['company_name']) 
                  . '</option>';
}
$supDropdown .= '</select>';
// ────────────────────────────────────────────────────────────────────────────

// ─── Fetch ingredient‐type dropdown for both ADD and EDIT ───────────────────
$typeQuery   = "SELECT type_id, type_name FROM ingredient_type ORDER BY type_name";
$typeResult  = mysqli_query($db, $typeQuery) or die(mysqli_error($db));
$typeDropdown  = '<select class="form-control" name="type_id" required>';
$typeDropdown .= '<option disabled selected hidden>Select Type</option>';
while ($t = mysqli_fetch_assoc($typeResult)) {
    // turn "fruit_purees" → "Fruit Purees"
    $label = ucwords(str_replace('_',' ',$t['type_name']));
    $typeDropdown .= sprintf(
      '<option value="%d">%s</option>',
      $t['type_id'],
      htmlspecialchars($label)
    );
}
$typeDropdown .= '</select>';
// ────────────────────────────────────────────────────────────────────────────

// ─── Main ingredients query (now includes i.type_id) ────────────────────────
$sql = "
  SELECT 
    i.INGREDIENTS_ID,
    i.INGREDIENTS_CODE,
    i.ING_NAME,
    i.ING_QUANTITY,
    i.UNIT,
    i.type_id,
    it.type_name,
    COALESCE(c.category_name,'Uncategorized') AS CATEGORY_NAME,
    COALESCE(s.company_name,'No Supplier')    AS SUPPLIER_NAME,
    i.supplier_id,
    i.EXPIRATION_DATE
  FROM ingredients i
  LEFT JOIN ingredient_type it 
    ON i.type_id = it.type_id
  LEFT JOIN category c 
    ON i.category_id = c.category_id
  LEFT JOIN supplier s 
    ON i.supplier_id = s.supplier_id
  WHERE i.ING_QUANTITY > 0
  ORDER BY i.INGREDIENTS_ID ASC
";

$result = mysqli_query($db, $sql) or die("Bad SQL: $sql");
// ────────────────────────────────────────────────────────────────────────────
?>

<!-- ─── PAGE CONTENT ───────────────────────────────────────────────────────── -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h4 class="m-2 font-weight-bold text-primary">
      Ingredients
      <a href="#" data-toggle="modal" data-target="#aModal" class="btn btn-primary">
        <i class="fas fa-fw fa-plus"></i>
      </a>
    </h4>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Type</th>
            <th>Supplier</th>
            <th>Expiry Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['INGREDIENTS_CODE']); ?></td>
            <td><?= htmlspecialchars($row['ING_NAME']); ?></td>
            <td><?= htmlspecialchars($row['ING_QUANTITY']); ?></td>
            <td>
              <?= htmlspecialchars($unitLabels[$row['UNIT']] ?? $row['UNIT']); ?>
            </td>
            <td>
              <?= htmlspecialchars(
                    // turn “fruit_purees” → “Fruit Purees”
                    ucwords(str_replace('_',' ', $row['type_name']))
                ); ?>
            </td>
            <td>
              <?= htmlspecialchars($row['SUPPLIER_NAME']); ?>
            </td>
            <td>
                <?php 
                    if (!empty($row['EXPIRATION_DATE'])) {
                        // calculate days remaining (negative if already expired)
                        $expiryDate    = new DateTime($row['EXPIRATION_DATE']);
                        $today         = new DateTime();
                        $interval      = $today->diff($expiryDate);
                        $daysRemaining = $expiryDate > $today 
                                    ? $interval->days 
                                    : -$interval->days;

                        // always print the date first
                        echo htmlspecialchars($expiryDate->format('Y-m-d'));

                        // static thresholds
                        $critical = 5;    // ≤ 5 days
                        $warning  = 14;   // ≤ 2 weeks
                        $notice   = 30;   // ≤ 1 month

                        // badge logic
                        if ($daysRemaining < 0) {
                            echo ' <span class="badge badge-danger">Expired</span>';
                        }
                        elseif ($daysRemaining <= $critical) {
                            echo ' <span class="badge badge-danger">Critical (ex in 5d)</span>';
                        }
                        elseif ($daysRemaining <= $warning) {
                            echo ' <span class="badge badge-warning">Warning (ex in 1-2w)</span>';
                        }
                        elseif ($daysRemaining <= $notice) {
                            echo ' <span class="badge badge-info">Notice (ex in 1m)</span>';
                        }
                    } else {
                        echo 'Not set';
                    }
                ?>
                </td>

            <td>
              <!-- ─── EDIT button now includes data-type-id ────────────────────────── -->
              <button 
                class="btn btn-warning btn-sm edit-ingredient-button" 
                data-toggle="modal" 
                data-target="#editIngredientModal" 
                data-id="<?= htmlspecialchars($row['INGREDIENTS_ID']); ?>"
                data-code="<?= htmlspecialchars($row['INGREDIENTS_CODE']); ?>"
                data-name="<?= htmlspecialchars($row['ING_NAME']); ?>"
                data-quantity="<?= htmlspecialchars($row['ING_QUANTITY']); ?>" 
                data-unit="<?= htmlspecialchars($row['UNIT']); ?>"
                data-supplier-id="<?= htmlspecialchars($row['supplier_id']); ?>"
                data-expiry="<?= htmlspecialchars($row['EXPIRATION_DATE'] ?? ''); ?>"
                data-type-id="<?= htmlspecialchars($row['type_id']); ?>">
                Edit
              </button>

              <a 
                href="ing_transac.php?action=delete&id=<?= $row['INGREDIENTS_ID']; ?>" 
                class="btn btn-danger btn-sm" 
                onclick="return confirm('Are you sure you want to delete this ingredient?')">
                Delete
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ─── ADD INGREDIENT MODAL (updated layout) ───────────────────────────────── -->
<div class="modal fade" id="aModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Ingredient</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form role="form" method="post" action="ing_transac.php?action=add">

          <div class="form-group">
            <input class="form-control" placeholder="Ingredient Code" name="ingcode" required readonly>
          </div>

          <div class="form-group">
            <input class="form-control" placeholder="Ingredient Name" name="ingname" required>
          </div>

          <!-- ─── NEW: three‐column row for Unit, Quantity, Type ───────────────────────── -->
          <div class="form-row">
            <div class="form-group col-md-4">
              <label>Unit</label>
              <select class="form-control" name="unit" required>
                <option disabled selected hidden>Select Unit</option>
                <option value="kg">Kilogram</option>
                <option value="g">Gram</option>
                <option value="lt">Liter</option>
                <option value="ml">Milliliter</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label>Quantity</label>
              <input type="number"
                      step="0.5"
                      min="1"
                      max="1000"
                      class="form-control"
                      placeholder="Add Quantity"
                      name="quantity" required>
            </div>
            <div class="form-group col-md-4">
              <label>Ingredient Type</label>
              <?= $typeDropdown; ?>
            </div>
          </div>
          <!-- ──────────────────────────────────────────────────────────────────────────── -->

          <div class="form-group">
            <label>Supplier</label>
            <?= $sup; ?>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Date Stock</label>
              <input type="date" class="form-control" name="datestock" required>
            </div>
            <div class="form-group col-md-6">
              <label>Expiry Date</label>
              <input type="date" class="form-control" name="expirydate">
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Save</button>
            <button type="reset" class="btn btn-danger"><i class="fa fa-times fa-fw"></i> Reset</button>
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- ─── EDIT INGREDIENT MODAL ──────────────────────────────────────────────── -->
<div class="modal fade" id="editIngredientModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Ingredient</h5>
        <button class="close" data-dismiss="modal"><span>×</span></button>
      </div>
      <div class="modal-body">
        <form method="post" action="ing_transac.php?action=edit">
          <input type="hidden" id="editIngredientId" name="id">
          
          <div class="form-group">
            <label>Ingredient Name</label>
            <input type="text" id="editIngredientName" name="name" class="form-control" required>
          </div>

          <!-- ─── three‐column row for Quantity / Unit / Type ───────────────────────── -->
          <div class="form-row">
            <div class="form-group col-md-4">
              <label>Quantity</label>
              <input type="number" id="editIngredientQuantity" name="quantity" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
              <label>Unit</label>
              <select id="editIngredientUnit" name="unit" class="form-control" required>
                <option value="kg">Kilogram</option>
                <option value="g">Gram</option>
                <option value="lt">Liter</option>
                <option value="ml">Milliliter</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label>Ingredient Type</label>
              <?= $typeDropdown; ?>
            </div>
          </div>
          <!-- ──────────────────────────────────────────────────────────────────────────── -->

          
          <div class="form-group">
            <label>Supplier</label>
            <?= $supDropdown; ?>
          </div>
          <div class="form-group">
            <label>Expiration Date</label>
            <input type="date" id="editIngredientExpiry" name="expirydate" class="form-control">
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Save</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ─── Edit‐modal population script (unchanged except for type) ─────────────── -->
<script>
document.querySelectorAll('.edit-ingredient-button').forEach(button => {
  button.addEventListener('click', function () {
    document.getElementById('editIngredientId').value      = this.getAttribute('data-id');
    document.getElementById('editIngredientName').value    = this.getAttribute('data-name');
    document.getElementById('editIngredientQuantity').value= this.getAttribute('data-quantity');
    document.getElementById('editIngredientUnit').value    = this.getAttribute('data-unit');
    document.getElementById('editIngredientExpiry').value  = this.getAttribute('data-expiry');
    
    // supplier
    const supId = this.getAttribute('data-supplier-id');
    const supEl = document.getElementById('editSupplier');
    for (let i=0; i<supEl.options.length; i++) {
      if (supEl.options[i].value == supId) {
        supEl.selectedIndex = i;
        break;
      }
    }

    // ingredient type
    const typeId   = this.getAttribute('data-type-id');
    const typeEl   = document.querySelector('#editIngredientModal select[name="type_id"]');
    for (let i=0; i<typeEl.options.length; i++) {
      if (typeEl.options[i].value === typeId) {
        typeEl.selectedIndex = i;
        break;
      }
    }
  });
});

// auto‐generate code (unchanged)
document.addEventListener('DOMContentLoaded', function() {
  if (document.getElementById('aModal')) {
    $('#aModal').on('shown.bs.modal', function() {
      const now    = new Date();
      const year   = now.getFullYear().toString().slice(-2);
      const month  = String(now.getMonth()+1).padStart(2,'0');
      const day    = String(now.getDate()).padStart(2,'0');
      const rnd    = Math.floor(Math.random()*1000).toString().padStart(3,'0');
      document.querySelector('input[name="ingcode"]').value = `ING${year}${month}${day}${rnd}`;
    });
  }
});
</script>

<?php include '../includes/footer.php'; ?>
