<?php
include '../includes/connection.php';
session_start();

// Check the action
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'add') {
        // Add Ingredient
        $ingcode     = $_POST['ingcode'];
        $ingname     = $_POST['ingname'];
        $unit        = $_POST['unit'];
        $description = $_POST['description'];
        $quantity    = $_POST['quantity'];
        $supplier    = $_POST['supplier'];
        $datestock   = $_POST['datestock'];
        $expirydate  = !empty($_POST['expirydate']) ? $_POST['expirydate'] : NULL;

        // ─── grab the selected type ───────────────────────────────────
        $type_id     = (int) $_POST['type_id'];
        // ──────────────────────────────────────────────────────────────

        // Only add if quantity is greater than 0
        if ($quantity > 0) {
            // Generate the next ID for ingredients
            $query  = "SELECT MAX(INGREDIENTS_ID) + 1 AS next_id FROM ingredients";
            $result = mysqli_query($db, $query);
            $row    = mysqli_fetch_assoc($result);
            $next_id = $row['next_id'] ? $row['next_id'] : 1;

            // Insert the ingredient into the database (now including type_id)
            $query = "
              INSERT INTO ingredients 
                (INGREDIENTS_ID,
                 INGREDIENTS_CODE,
                 ING_NAME,
                 UNIT,
                 type_id,               -- ← added
                 DESCRIPTION,
                 ING_QUANTITY,
                 SUPPLIER_ID,
                 STOCK_DATE,
                 EXPIRATION_DATE)
              VALUES
                ('$next_id',
                 '$ingcode',
                 '$ingname',
                 '$unit',
                 $type_id,              -- ← added
                 '$description',
                 $quantity,
                 $supplier,
                 '$datestock',
                 ".($expirydate ? "'$expirydate'" : "NULL")."
                )";

            if (mysqli_query($db, $query)) {
                // Redirect with a success status
                echo '<script>window.location="pro_ingredients.php?status=success";</script>';
            } else {
                // Redirect with an error status
                echo '<script>window.location="pro_ingredients.php?status=error";</script>';
            }
        } else {
            // Don't add ingredients with 0 or negative quantity
            echo '<script>window.location="pro_ingredients.php?status=invalid_quantity";</script>';
        }

    } elseif ($action == 'edit') {
        // Edit Ingredient
        $id         = (int) $_POST['id'];
        $ingname    = $_POST['name'];
        $quantity   = $_POST['quantity'];
        $unit       = $_POST['unit'];
        $supplier   = $_POST['supplier']; 
        $expirydate = !empty($_POST['expirydate']) ? $_POST['expirydate'] : NULL;

        // ─── grab the new type selection ───────────────────────────────
        $type_id    = (int) $_POST['type_id'];
        // ──────────────────────────────────────────────────────────────

        // If quantity zero or less: remove ingredient
        if ($quantity <= 0) {
            mysqli_begin_transaction($db);
            try {
                mysqli_query($db, "DELETE FROM recipe_ingredients WHERE ingredient_id = '$id'");
                mysqli_query($db, "DELETE FROM ingredients WHERE INGREDIENTS_ID = '$id'");
                mysqli_commit($db);
                echo '<script>window.location="pro_ingredients.php?status=auto_removed";</script>';
            } catch (Exception $e) {
                mysqli_rollback($db);
                echo '<script>window.location="pro_ingredients.php?status=error";</script>';
            }
        } else {
            // Update with the new type_id included
            $query = "
              UPDATE ingredients 
                 SET ING_NAME        = '$ingname',
                     ING_QUANTITY    = '$quantity',
                     UNIT            = '$unit',
                     type_id         = $type_id,    -- ← added
                     SUPPLIER_ID     = '$supplier',
                     EXPIRATION_DATE = ".($expirydate ? "'$expirydate'" : "NULL")."
               WHERE INGREDIENTS_ID = '$id'
            ";

            if (mysqli_query($db, $query)) {
                echo '<script>window.location="pro_ingredients.php?status=updated";</script>';
            } else {
                echo '<script>window.location="pro_ingredients.php?status=error";</script>';
            }
        }

    } elseif ($action == 'delete') {
        // Delete Ingredient (unchanged)...
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            mysqli_begin_transaction($db);
            try {
                mysqli_query($db, "DELETE FROM recipe_ingredients WHERE ingredient_id = '$id'");
                mysqli_query($db, "DELETE FROM ingredients WHERE INGREDIENTS_ID = '$id'");
                mysqli_commit($db);
                echo '<script>window.location="pro_ingredients.php?status=deleted";</script>';
            } catch (Exception $e) {
                mysqli_rollback($db);
                echo '<script>window.location="pro_ingredients.php?status=error";</script>';
            }
        } else {
            echo '<script>window.location="pro_ingredients.php?status=missing_id";</script>';
        }
    }
}

// Clean up zero‐quantity ingredients (unchanged)
mysqli_query($db, "DELETE FROM ingredients WHERE ING_QUANTITY <= 0");
?>
