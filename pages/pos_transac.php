<?php
include '../includes/connection.php';
session_start();

/**
 * Convert a recipe-unit quantity into the stock unit (grams or milliliters).
 *
 * @param float $quantity      The quantity in recipe units (e.g., tablespoons, cups).
 * @param int   $unit_id       The unit ID corresponding to units.unit_name.
 * @param int   $ingredient_id The ingredient ID to look up stock unit and type.
 * @return float               The equivalent amount in the ingredient's stock unit.
 */
function convertToStockUnit($quantity, $unit_id, $ingredient_id) {
    global $db;

    // 1) Recipe unit name
    $u = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT unit_name FROM units WHERE unit_id = '$unit_id'"
    ));
    $recipeUnit = $u['unit_name'];

    // 2) Ingredient's stock unit and type
    $ing = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT UNIT AS stockUnit, type_id FROM ingredients WHERE INGREDIENTS_ID = '$ingredient_id'"
    ));
    $stockUnit = $ing['stockUnit'];
    $typeId    = $ing['type_id'];

    // 3) Direct match (no conversion needed)
    if ($recipeUnit === $stockUnit) {
        return $quantity;
    }

    // 4) Built-in metric conversions (kg↔g, lt↔ml)
    if (($recipeUnit == 'kg' && $stockUnit == 'g') || ($recipeUnit == 'lt' && $stockUnit == 'ml')) {
        return $quantity * 1000;
    }
    if (($recipeUnit == 'g' && $stockUnit == 'kg') || ($recipeUnit == 'ml' && $stockUnit == 'lt')) {
        return $quantity / 1000;
    }

    // 5) Ingredient-specific override
    $spec = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT equivalent_in_grams
           FROM ingredient_unit_equivalence
          WHERE ingredient_id = '$ingredient_id'
            AND unit_name      = '$recipeUnit'
          LIMIT 1"
    ));
    if ($spec) {
        return $quantity * $spec['equivalent_in_grams'];
    }

    // 6) Type-based fallback
    $typeDef = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT equivalent_amount
           FROM type_unit_equivalence
          WHERE type_id   = '$typeId'
            AND unit_name = '$recipeUnit'
          LIMIT 1"
    ));
    if ($typeDef) {
        return $quantity * $typeDef['equivalent_amount'];
    }

    // 7) Ultimate fallback: log and return raw quantity
    error_log("No conversion rule for unit '$recipeUnit' on ingredient #$ingredient_id");
    return $quantity;
}

// Capture POST data and session variables
// If your form doesn’t send these, give sensible defaults:
  $date     = $_POST['date']     ?? date('Y-m-d H:i:s');
  $customer = $_POST['customer'] ?? 'Walk-in';
  $subtotal = $_POST['subtotal'] ?? 0;
  $lessvat  = $_POST['lessvat']  ?? 0;
  $netvat   = $_POST['netvat']   ?? 0;
  $addvat   = $_POST['addvat']   ?? 0;
  $total    = $_POST['total']    ?? $subtotal;
  $cash     = $_POST['cash']     ?? 0;
  // If you don’t have employees/roles yet, you can hard-code them too:
  $emp      = $_POST['employee'] ?? 'system';
  $rol      = $_POST['role']     ?? 'cashier';  
$today    = date("mdGis");

// Number of items in cart
$countID = count($_SESSION['pointofsale']);

// Process 'add' action
if (isset($_GET['action']) && $_GET['action']==='add') {
  // 1) master insert
  $masterSql = "
    INSERT INTO `transaction`
      (`CUST_NAME`,`DATE`,`GRANDTOTAL`,`CASH`)
    VALUES
      ('$customer','$date','$total','$cash')
  ";
  mysqli_query($db,$masterSql) or die('Master insert failed: '.mysqli_error($db));

  // 2) get the new TRANS_ID
  $transId = mysqli_insert_id($db);

  // 3) loop details & deduct stock
  foreach ($_SESSION['pointofsale'] as $product) {
    $prodId  = $product['id'];
    $pName   = $product['name'];
    $pQty    = $product['quantity'];
    $pPrice  = isset($product['price']) 
                ? floatval($product['price']) 
                : 0.0;

                $detailSql = "
                INSERT INTO `transaction_details`
                  (`TRANS_ID`,`PRODUCT`,`QTY`,`PRICE`)
                VALUES
                  ('$transId',
                   '" . mysqli_real_escape_string($db, $pName) . "',
                   '$pQty',
                   '$pPrice')
              ";
              mysqli_query($db, $detailSql)
                or die('Detail insert failed: ' . mysqli_error($db));
          

    // 3b) Get the recipe_id directly from the product row
    $prodRow = mysqli_fetch_assoc(mysqli_query($db,
      "SELECT recipe_id 
         FROM product 
        WHERE PRODUCT_ID = '$prodId'"
    ));
    $recipeId = $prodRow['recipe_id'] ?? null;

    if ($recipeId) {
        // 3c) Fetch recipe ingredients and deduct stock
        $ingRes = mysqli_query($db,
          "SELECT ingredient_id, quantity, unit_id
             FROM recipe_ingredients
            WHERE recipe_id = '$recipeId'"
        );
        while ($ing = mysqli_fetch_assoc($ingRes)) {
            $usedQty = convertToStockUnit(
              $ing['quantity'] * $pQty,
              $ing['unit_id'],
              $ing['ingredient_id']
            );
            mysqli_query($db,
              "UPDATE ingredients
                  SET ING_QUANTITY = ING_QUANTITY - $usedQty
                WHERE INGREDIENTS_ID = {$ing['ingredient_id']}"
            ) or die('Stock deduction failed: ' . mysqli_error($db));
        }
    }
}
  // 4) clear cart & redirect
  unset($_SESSION['pointofsale']);
  echo "<script>
          alert('Order processed successfully!');
          window.location='pos.php';
        </script>";
  exit();
}

?>




























<?php
              // switch($_GET['action']){
              //   case 'add':     
              //       $query = "INSERT INTO transaction_details
              //                  (`ID`, `PRODUCTS`, `EMPLOYEE`, `ROLE`)
              //                  VALUES (Null, 'here', '{$emp}', '{$rol}')";
              //       mysqli_query($db,$query)or die ('Error in Database '.$query);
              //       $query2 = "INSERT INTO `transaction`
              //                  (`TRANS_ID`, `CUST_ID`, `SUBTOTAL`, `LESSVAT`, `NETVAT`, `ADDVAT`, `GRANDTOTAL`, `CASH`, `DATE`, `TRANS_D_ID`)
              //                  VALUES (Null,'{$customer}','{$subtotal}','{$lessvat}','{$netvat}','{$addvat}','{$total}','{$cash}','{$date}','{$today}'')";
              //       mysqli_query($db,$query2)or die ('Error in updating Database2 '.$query2);
              //   break;
              // }

              // mysqli_query($db,"INSERT INTO transaction_details
              //                 (`ID`, `PRODUCTS`, `EMPLOYEE`, `ROLE`)
              //                 VALUES (Null, 'a', '{$emp}', '{$rol}')");

              // mysqli_query($db,"INSERT INTO `transaction`
              //                 (`TRANS_ID`, `CUST_ID`, `SUBTOTAL`, `LESSVAT`, `NETVAT`, `ADDVAT`, `GRANDTOTAL`, `CASH`, `DATE`, `TRANS_DETAIL_ID`)
              //                 VALUES (Null,'{$customer}',{$subtotal},{$lessvat},{$netvat},{$addvat},{$total},{$cash},'{$date}',(SELECT MAX(ID) FROM transaction_details))");

              // header('location:posdetails.php');

            ?>
<!--  <script type="text/javascript">
      alert("Transaction successfully added.");
      window.location = "pos.php";
      </script> -->