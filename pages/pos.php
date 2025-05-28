<?php
include '../includes/connection.php';
include '../includes/topp.php';


// Check if Add to order button has been submitted
if (isset($_POST['addpos'])) {
    $productId = filter_input(INPUT_GET, 'id');
    $productName = filter_input(INPUT_POST, 'name');
    $quantity = filter_input(INPUT_POST, 'quantity');
    $size = filter_input(INPUT_POST, 'size');  // Capture size from the form
    
    // ─── Capture the posted price more robustly ──────────────────────────
    if (isset($_POST['price'])) {
        $price = floatval($_POST['price']);
    } else {
        $price = 0.0;
    }


    
    // Check if the product already exists in the session (by both product ID and size)
    if (isset($_SESSION['pointofsale'])) {
        $found = false;
        foreach ($_SESSION['pointofsale'] as $key => $product) {
            // If product with the same ID and size already exists, update its quantity
            if ($product['id'] == $productId && $product['size'] == $size) {
                $_SESSION['pointofsale'][$key]['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        // If product not found (same product, different size), add new entry
        if (!$found) {
            $_SESSION['pointofsale'][] = array(
                'id' => $productId,
                'name' => $productName,
                'quantity' => $quantity,
                'size' => $size,  // Add the size to the session
                'price' => $price  // Store the price
            );
        }
    } else {
        // If session doesn't have products yet, create the first one
        $_SESSION['pointofsale'][] = array(
            'id' => $productId,
            'name' => $productName,
            'quantity' => $quantity,
            'size' => $size,  // Add the size to the session
            'price' => $price  // Store the price
        );
    }
}

// Handle product deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $productId = filter_input(INPUT_GET, 'id');
    $size = filter_input(INPUT_GET, 'size');  // Capture size for deletion
    foreach ($_SESSION['pointofsale'] as $key => $product) {
        if ($product['id'] == $productId && $product['size'] == $size) {
            unset($_SESSION['pointofsale'][$key]);
        }
    }
    $_SESSION['pointofsale'] = array_values($_SESSION['pointofsale']);  // Re-index the array
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-0">
            <div class="card-header py-2">
                <h4 class="m-1 text-lg text-primary">Category</h4>
            </div>
            <div class="card-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs">
                    <?php 
                    // Fetch categories from the database
                    $categoryQuery = "SELECT category_id, category_name FROM category ORDER BY category_name";
                    $categoryResult = mysqli_query($db, $categoryQuery) or die('Error fetching categories: ' . mysqli_error($db));

                    // Loop through categories to create the tabs
                    $first = true; // Flag to set the first tab as active
                    while ($category = mysqli_fetch_assoc($categoryResult)): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $first ? 'active' : ''; ?>" href="#category<?php echo $category['category_id']; ?>" data-toggle="tab">
                                <?php echo $category['category_name']; ?>
                            </a>
                        </li>
                    <?php 
                    $first = false; // After the first tab, set this flag to false
                    endwhile; ?>
                </ul>

                <!-- Tab Content Area -->
                <div class="tab-content">
                    <?php 
                    // Fetch categories again to loop through for their products
                    mysqli_data_seek($categoryResult, 0);  // Reset the result pointer
                    while ($category = mysqli_fetch_assoc($categoryResult)):
                        $categoryId = $category['category_id'];
                        $categoryName = $category['category_name'];
                        
                        // Fetch products for this category (with size-based prices)
                        $productQuery = "
                            SELECT 
                            PRODUCT_ID, 
                            NAME,
                            price_medium,
                            price_large
                            FROM product
                            WHERE category_id = $categoryId
                            ORDER BY name
                            ";
                        $productResult = mysqli_query($db, $productQuery)
                                    or die('Error fetching products: ' . mysqli_error($db));
                    ?>
                        <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" id="category<?php echo $categoryId; ?>">
                            <h5><?php echo $categoryName; ?></h5>
                            <div class="row">
                            <?php while ($product = mysqli_fetch_assoc($productResult)): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($product['NAME']) ?></h5>
                                        <form method="post" action="pos.php?action=add&id=<?= $product['PRODUCT_ID'] ?>">
  <!-- Hidden: send the product name into $_POST['name'] -->
  <input 
    type="hidden" 
    name="name" 
    value="<?= htmlspecialchars($product['NAME'], ENT_QUOTES) ?>"
  >

  <!-- Quantity selector -->
  <div class="form-group">
    <label for="qty-<?= $product['PRODUCT_ID'] ?>">Quantity</label>
    <input
      type="number"
      id="qty-<?= $product['PRODUCT_ID'] ?>"
      name="quantity"
      class="form-control"
      value="1"
      min="1"
      required
    >
  </div>

  <!-- Size Buttons -->
  <div class="mt-2">
    <label>Size:</label><br>
    <button
      type="button"
      class="btn btn-outline-primary btn-sm size-btn"
      data-id="<?= $product['PRODUCT_ID'] ?>"
      data-size="medium"
      data-price="<?= number_format($product['price_medium'],2,'.','') ?>"
    >
      Medium ₱<?= number_format($product['price_medium'],2) ?>
    </button>
    <button
      type="button"
      class="btn btn-outline-secondary btn-sm size-btn"
      data-id="<?= $product['PRODUCT_ID'] ?>"
      data-size="large"
      data-price="<?= number_format($product['price_large'],2,'.','') ?>"
    >
      Large ₱<?= number_format($product['price_large'],2) ?>
    </button>
  </div>

  <!-- Hidden fields to capture the selected size & price -->
  <input
    type="hidden"
    name="size"
    id="size-<?= $product['PRODUCT_ID'] ?>"
    value="medium"
  >
  <input
    type="hidden"
    name="price"
    id="price-<?= $product['PRODUCT_ID'] ?>"
    value="<?= number_format($product['price_medium'],2,'.','') ?>"
  >

  <!-- Add to Order -->
  <button
    type="submit"
    name="addpos"
    class="btn btn-success btn-sm mt-2"
  >
    Add to Order
  </button>
</form>

                                    </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <script>
                                    document.querySelectorAll('.size-btn').forEach(btn => {
                                        btn.addEventListener('click', () => {
                                        const id    = btn.dataset.id;
                                        const size  = btn.dataset.size;
                                        const price = btn.dataset.price;
                                        document.getElementById(`size-${id}`).value  = size;
                                        document.getElementById(`price-${id}`).value = price;
                                        });
                                    });
                                </script>

                            </div>
                        </div>
                    <?php
                        $first = false; // After the first tab, set this flag to false
                    endwhile;
                    ?>
                </div>
                <!-- END TAB CONTENT AREA -->
            </div>
        </div>
    </div>
</div>

<div style="clear:both"></div>  
<br />  

<div class="card shadow mb-4 col-md-12">
    <div class="card-header py-3 bg-white">
        <h4 class="m-2 font-weight-bold text-primary">Order</h4>
    </div>
    <div class="row">    
        <div class="card-body col-md-9">
            <div class="table-responsive">
                <table class="table">    
                    <tr>  
                        <th width="50%">Product Name</th>  
                        <th width="20%">Quantity</th>  
                        <th width="15%">Size</th> 
                        <th width="15%">Price</th> 
                        <th width="10%">Action</th>  
                    </tr>  

                    <?php  
                    if (!empty($_SESSION['pointofsale'])):  
                        foreach ($_SESSION['pointofsale'] as $product): 
                    ?>  
                    <tr>  
                        <td>
                            <?php echo htmlspecialchars($product['name']); ?>
                        </td>  

                        <td>
                            <?php echo $product['quantity']; ?>
                        </td>  

                        <td>
                            <?php echo isset($product['size']) ? htmlspecialchars($product['size']) : 'N/A'; ?>
                        </td>  

                        <td>
                            <?php echo isset($product['price']) ? number_format($product['price'], 2) : 'N/A'; ?>  <!-- Display the price or N/A if it's not set -->
                        </td>

                        
                        <td>
                            <a href="pos.php?action=delete&id=<?php echo $product['id']; ?>&size=<?php echo urlencode($product['size']); ?>">
                                <div class="btn bg-gradient-danger btn-danger"><i class="fas fa-fw fa-trash"></i></div>
                            </a>
                        </td>  
                    </tr>
                    <?php  
                        endforeach;  
                    else:  
                        echo "<tr><td colspan='4'>No products in the order yet.</td></tr>";
                    endif;
                    ?>  
                </table>
                
            </div>
            
        </div> 
    </div>
    <?php
include 'posside.php'; 
include '../includes/footer.php'; 
?>
</div>


<script>
  document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id    = btn.dataset.id;
      const size  = btn.dataset.size;
      const price = btn.dataset.price;
      const form  = btn.closest('form');

      // Update hidden inputs
      form.querySelector('input[name="size"]').value  = size;
      form.querySelector('input[name="price"]').value = price;

      // Reset all buttons to outline‐secondary
      form.querySelectorAll('.size-btn').forEach(b => {
        b.classList.remove('btn-outline-primary', 'btn-outline-secondary');
        b.classList.add('btn-outline-secondary');
      });

      // Highlight the clicked one as outline‐primary
      btn.classList.remove('btn-outline-secondary');
      btn.classList.add('btn-outline-primary');
    });
  });
</script>
