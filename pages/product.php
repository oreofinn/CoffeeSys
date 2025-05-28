<?php
include '../includes/connection.php';
include '../includes/sidebar.php';

// Restrict page for 'User' type
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));
//tanggal muna c user


// Fetch categories
$sql = "SELECT DISTINCT category_name, category_id FROM category ORDER BY category_name ASC";
$result = mysqli_query($db, $sql) or die("Bad SQL: $sql");

$categoryDropdown = "<select class='form-control' name='category' required>
    <option disabled selected hidden>Select Category</option>";
while ($row = mysqli_fetch_assoc($result)) {
    $categoryDropdown .= "<option value='" . $row['category_id'] . "'>" . $row['category_name'] . "</option>";
}
$categoryDropdown .= "</select>";

// Fetch recipes
$sqlRecipes = "SELECT recipe_id, recipe_name FROM recipes ORDER BY recipe_name ASC";
$resultRecipes = mysqli_query($db, $sqlRecipes) or die("Bad SQL: $sqlRecipes");

$recipeDropdown = "<select class='form-control' name='recipe' required>
    <option disabled selected hidden>Select Recipe</option>";
while ($row = mysqli_fetch_assoc($resultRecipes)) {
    $recipeDropdown .= "<option value='" . $row['recipe_id'] . "'>" . $row['recipe_name'] . "</option>";
}
$recipeDropdown .= "</select>";
?>

<!-- Product List -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Products&nbsp;
            <a href="#" data-toggle="modal" data-target="#aModal" type="button" class="btn btn-primary " style="border-radius: 0px;">
                <i class="fas fa-fw fa-plus"></i>
            </a>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th>Recipe</th>
                        <th>Category</th>
                        <th>Medium Price</th>
                        <th>Large Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $query = '
                            SELECT 
                            p.PRODUCT_ID,
                            p.PRODUCT_CODE,
                            p.NAME,
                            p.DESCRIPTION,
                            r.recipe_name,
                            c.category_name,
                            p.price_medium,
                            p.price_large
                            FROM product p
                            JOIN category c ON p.category_id = c.category_id
                            LEFT JOIN recipes r ON p.recipe_id = r.recipe_id
                        ';
                        $result = mysqli_query($db, $query) or die(mysqli_error($db));

                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td>' . $row['PRODUCT_CODE']   . '</td>';
                            echo '<td>' . $row['NAME']           . '</td>';
                            echo '<td>' . $row['DESCRIPTION']    . '</td>';
                            echo '<td>' . $row['recipe_name']    . '</td>';
                            echo '<td>' . $row['category_name']  . '</td>';
                            echo '<td>₱' . number_format($row['price_medium'], 2) . '</td>';
                            echo '<td>₱' . number_format($row['price_large'], 2)   . '</td>';
                        
                      
                            // ← Your Action cell here
                            echo '<td align="right">';
                            echo '  <button type="button" class="btn btn-warning btn-sm"'
                                . ' data-toggle="modal"'
                                . ' data-target="#productEditModal' . $row['PRODUCT_ID'] . '">'
                                . 'Edit</button> ';
                                echo '  <form method="post" action="pro_transac.php?action=delete" style="display:inline-block;">'
                                . '<input type="hidden" name="id" value="' . $row['PRODUCT_ID'] . '">'
                                . '<button type="submit" class="btn btn-danger btn-sm">Delete</button>'
                                . '</form>';
                             
                            echo '</td>';
                      
                            echo '</tr>';
                          }
                    ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<!-- Delete Product Modals -->
<?php
$query = 'SELECT * FROM product';
$result = mysqli_query($db, $query);

?>

<?php
include '../includes/footer.php';
?>

<!-- Add Product Modal -->
<div class="modal fade" id="aModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Product</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="pro_transac.php?action=add">
                    <div class="form-group">
                        <input class="form-control" value="Automatically Generated" readonly>
                    </div>
                    <div class="form-group">
                        <input class="form-control" placeholder="Product Name" name="name" required>
                    </div>
                    <div class="form-group">
                        <textarea rows="5" class="form-control" placeholder="Description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <?php echo $recipeDropdown; ?>
                    </div>
                    <div class="form-group">
                        <?php echo $categoryDropdown; ?>
                    </div>
                    <div class="form-group">
                    <label for="price_medium">Medium Price</label>
                    <input
                        type="number"
                        step="0.01"
                        class="form-control"
                        name="price_medium"
                        placeholder="e.g. 100.00"
                        required
                    >
                    </div>
                    <div class="form-group">
                    <label for="price_large">Large Price</label>
                    <input
                        type="number"
                        step="0.01"
                        class="form-control"
                        name="price_large"
                        placeholder="e.g. 150.00"
                        required
                    >
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Save</button>
                    <button type="reset" class="btn btn-danger"><i class="fa fa-times fa-fw"></i>Reset</button>
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Edit Product Modals -->
<?php
  $query = "SELECT 
              p.*,
              c.category_name,
              r.recipe_name
            FROM product p
            LEFT JOIN category c ON p.category_id = c.category_id
            LEFT JOIN recipes  r ON p.recipe_id   = r.recipe_id";
  $resultEdit = mysqli_query($db, $query) or die(mysqli_error($db));

  while ($prod = mysqli_fetch_assoc($resultEdit)): ?>
  <div
    class="modal fade"
    id="productEditModal<?= $prod['PRODUCT_ID'] ?>"
    tabindex="-1"
    role="dialog"
    aria-labelledby="editProductLabel<?= $prod['PRODUCT_ID'] ?>"
    aria-hidden="true"
  >
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5
            class="modal-title"
            id="editProductLabel<?= $prod['PRODUCT_ID'] ?>"
          >
            Edit Product
          </h5>
          <button
            type="button"
            class="close"
            data-dismiss="modal"
            aria-label="Close"
          >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form role="form" method="post" action="pro_transac.php?action=edit">
          <div class="modal-body">
            <input
              type="hidden"
              name="id"
              value="<?= $prod['PRODUCT_ID'] ?>"
            >

            <div class="form-group">
              <label for="name-<?= $prod['PRODUCT_ID'] ?>">Product Name</label>
              <input
                type="text"
                class="form-control"
                id="name-<?= $prod['PRODUCT_ID'] ?>"
                name="name"
                value="<?= htmlspecialchars($prod['NAME']) ?>"
                required
              >
            </div>

            <div class="form-group">
              <label for="desc-<?= $prod['PRODUCT_ID'] ?>">Description</label>
              <textarea
                class="form-control"
                id="desc-<?= $prod['PRODUCT_ID'] ?>"
                name="description"
                required
              ><?= htmlspecialchars($prod['DESCRIPTION']) ?></textarea>
            </div>

            <div class="form-group">
              <label for="recipe-<?= $prod['PRODUCT_ID'] ?>">Select Recipe</label>
              <select
                class="form-control"
                id="recipe-<?= $prod['PRODUCT_ID'] ?>"
                name="recipe"
              >
                <?php
                  $rSql = "SELECT recipe_id, recipe_name FROM recipes ORDER BY recipe_name";
                  $rRes = mysqli_query($db, $rSql);
                  while ($rRow = mysqli_fetch_assoc($rRes)) {
                    $sel = $rRow['recipe_id']==$prod['recipe_id'] ? 'selected' : '';
                    echo "<option value=\"{$rRow['recipe_id']}\" $sel>{$rRow['recipe_name']}</option>";
                  }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label for="cat-<?= $prod['PRODUCT_ID'] ?>">Select Category</label>
              <select
                class="form-control"
                id="cat-<?= $prod['PRODUCT_ID'] ?>"
                name="category"
                required
              >
                <?php
                  $cSql = "SELECT category_id, category_name FROM category ORDER BY category_name";
                  $cRes = mysqli_query($db, $cSql);
                  while ($cRow = mysqli_fetch_assoc($cRes)) {
                    $sel = $cRow['category_id']==$prod['CATEGORY_ID'] ? 'selected' : '';
                    echo "<option value=\"{$cRow['category_id']}\" $sel>{$cRow['category_name']}</option>";
                  }
                ?>
              </select>
            </div>

            <!-- Medium Price only -->
            <div class="form-group">
              <label for="price_medium-<?= $prod['PRODUCT_ID'] ?>">Medium Price</label>
              <input
                type="number"
                step="0.01"
                class="form-control"
                id="price_medium-<?= $prod['PRODUCT_ID'] ?>"
                name="price_medium"
                value="<?= number_format($prod['price_medium'],2,'.','') ?>"
                required
              >
            </div>

            <!-- Large Price only -->
            <div class="form-group">
              <label for="price_large-<?= $prod['PRODUCT_ID'] ?>">Large Price</label>
              <input
                type="number"
                step="0.01"
                class="form-control"
                id="price_large-<?= $prod['PRODUCT_ID'] ?>"
                name="price_large"
                value="<?= number_format($prod['price_large'],2,'.','') ?>"
                required
              >
            </div>

          </div>
          <div class="modal-footer">
            <button
              type="submit"
              class="btn btn-warning"
            >
              <i class="fa fa-edit fa-fw"></i> Update
            </button>
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endwhile; ?>
