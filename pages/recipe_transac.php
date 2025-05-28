<?php
include '../includes/connection.php';

if ($_GET['action'] === 'add') {
    // Adding a new recipe (same as before)
    // Retrieve recipe name, ingredients, quantities, and units
    $recipe_name = $_POST['recipe_name'];
    $ingredients = $_POST['INGREDIENTS_ID'];
    $quantities = $_POST['quantity'];
    $units = $_POST['unit_id'];

    // Generate a unique recipe code
    $recipeCodeQuery = "SELECT MAX(CAST(SUBSTRING(recipe_code, 4) AS UNSIGNED)) AS max_code FROM recipes";
    $result = mysqli_query($db, $recipeCodeQuery);
    if (!$result) {
        die('Error executing recipe code query: ' . mysqli_error($db));
    }

    $row = mysqli_fetch_assoc($result);
    $max_code = isset($row['max_code']) ? $row['max_code'] + 1 : 1;
    $recipe_code = 'RCP' . str_pad($max_code, 4, '0', STR_PAD_LEFT);

    // Insert recipe into the recipes table, including the generated recipe code
    $recipeQuery = "INSERT INTO recipes (recipe_name, recipe_code) VALUES ('$recipe_name', '$recipe_code')";
    if (!mysqli_query($db, $recipeQuery)) {
        die('Error inserting recipe: ' . mysqli_error($db));
    }

    // Get the ID of the newly inserted recipe
    $recipe_id = mysqli_insert_id($db);

    // Insert ingredients, quantities, and units into the recipe_ingredients table
    for ($i = 0; $i < count($ingredients); $i++) {
        $ingredient_id = $ingredients[$i];
        $quantity = $quantities[$i];
        $unit_id = $units[$i];

        // Insert the ingredient into recipe_ingredients table
        $ingredientQuery = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit_id) 
                            VALUES ('$recipe_id', '$ingredient_id', '$quantity', '$unit_id')";
        if (!mysqli_query($db, $ingredientQuery)) {
            die('Error inserting ingredient: ' . mysqli_error($db));
        }

        // Get the last inserted ID from the recipe_ingredients table (auto-incremented value)
        $last_id_query = "SELECT recipe_ingredient_id FROM recipe_ingredients ORDER BY recipe_ingredient_id DESC LIMIT 1";
        $result = mysqli_query($db, $last_id_query);
        $last_id_row = mysqli_fetch_assoc($result);

        // Ensure the last inserted ID is not 0
        if ($last_id_row['recipe_ingredient_id'] > 0) {
            // Generate the custom RCPI ID (e.g., RCPI001, RCPI002, etc.)
            $custom_id = 'RCPI' . str_pad($last_id_row['recipe_ingredient_id'], 3, '0', STR_PAD_LEFT);

            // Update the recipe_ingredients table with the custom ID
            $updateCustomIdQuery = "UPDATE recipe_ingredients 
                                    SET custom_id = '$custom_id' 
                                    WHERE recipe_ingredient_id = '" . $last_id_row['recipe_ingredient_id'] . "'";
            if (!mysqli_query($db, $updateCustomIdQuery)) {
                die('Error updating custom ID: ' . mysqli_error($db));
            }
        } else {
            die('Failed to retrieve the last inserted recipe ingredient ID.');
        }

        // Step 1: Retrieve current stock information for the ingredient
        $stockQuery = "SELECT ING_QUANTITY, unit FROM ingredients WHERE INGREDIENTS_ID = '$ingredient_id'";
        $stockResult = mysqli_query($db, $stockQuery);
        if ($stockRow = mysqli_fetch_assoc($stockResult)) {
            $current_stock = $stockRow['ING_QUANTITY'];
            $stock_unit = $stockRow['unit'];

            // Step 2: Convert the recipe unit to stock unit (if necessary)
            $stock_quantity_used = convertToStockUnit($quantity, $unit_id, $stock_unit);

            // Step 3: Deduct the stock
            $new_stock_quantity = $current_stock - $stock_quantity_used;

            // Update the stock quantity in the ingredients table
            $updateStockQuery = "UPDATE ingredients 
                                 SET ING_QUANTITY = '$new_stock_quantity' 
                                 WHERE INGREDIENTS_ID = '$ingredient_id'";
            if (!mysqli_query($db, $updateStockQuery)) {
                die('Error updating stock: ' . mysqli_error($db));
            }
        } else {
            die('Ingredient not found in stock');
        }
    }

    // Redirect back to the recipe page
    echo "<script type='text/javascript'>
        alert('Recipe added successfully!');
        window.location = 'pro_recipe.php';
    </script>";
    exit();
}

if ($action === 'edit') {
    try {
        $recipe_id = $_GET['id'];
        $recipe_name = $_POST['recipe_name'];
        $ingredients = $_POST['INGREDIENTS_ID'];
        $quantities = $_POST['quantity'];
        $units = $_POST['unit_id'];

        // Update recipe name
        $stmt = $db->prepare("UPDATE recipes SET recipe_name = ? WHERE recipe_id = ?");
        $stmt->bind_param("si", $recipe_name, $recipe_id);
        $stmt->execute();

        // Delete old ingredients
        $stmt = $db->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();

        // Re-insert updated ingredients
        for ($i = 0; $i < count($ingredients); $i++) {
            $ingredient_id = $ingredients[$i];
            $quantity = $quantities[$i];
            $unit_id = $units[$i];

            // Insert into recipe_ingredients
            $stmt = $db->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $recipe_id, $ingredient_id, $quantity, $unit_id);
            $stmt->execute();

            $recipe_ingredient_id = $db->insert_id;
            $custom_id = 'RCPI' . str_pad($recipe_ingredient_id, 3, '0', STR_PAD_LEFT);

            // Update custom ID
            $updateStmt = $db->prepare("UPDATE recipe_ingredients SET custom_id = ? WHERE recipe_ingredient_id = ?");
            $updateStmt->bind_param("si", $custom_id, $recipe_ingredient_id);
            $updateStmt->execute();

            // Update stock
            updateIngredientStock($db, $ingredient_id, $quantity, $unit_id);
        }

        $message = 'Recipe updated successfully!';
    } catch (Exception $e) {
        $message = "Error updating recipe: " . $e->getMessage();
    }
}

echo "<script type='text/javascript'>
    alert('$message');
    window.location = 'pro_recipe.php';
</script>";


/**
* Update ingredient stock based on the given recipe quantities and units.
*/
function updateIngredientStock($db, $ingredient_id, $quantity, $unit_id) {
// Retrieve current stock
$stmt = $db->prepare("SELECT ING_QUANTITY, unit FROM ingredients WHERE INGREDIENTS_ID = ?");
$stmt->bind_param("i", $ingredient_id);
$stmt->execute();
$result = $stmt->get_result();
$stockRow = $result->fetch_assoc();

if (!$stockRow) {
    throw new Exception("Ingredient not found: $ingredient_id");
}

$current_stock = $stockRow['ING_QUANTITY'];
$stock_unit = $stockRow['unit'];

// Convert units
$stock_quantity_used = convertToStockUnit($quantity, $unit_id, $stock_unit);

if ($current_stock < $stock_quantity_used) {
    throw new Exception("Insufficient stock for ingredient ID: $ingredient_id");
}

// Update stock quantity
$new_stock_quantity = $current_stock - $stock_quantity_used;
$updateStmt = $db->prepare("UPDATE ingredients SET ING_QUANTITY = ? WHERE INGREDIENTS_ID = ?");
$updateStmt->bind_param("di", $new_stock_quantity, $ingredient_id);
$updateStmt->execute();
}


/**
 * Convert recipe units to stock units
 * (this function can be expanded based on your unit types)
 */
function convertToStockUnit($quantity, $unit_id, $ingredient_id) {
    global $db;

    // 1. Get the recipe unit name
    $unitQuery = "SELECT unit_name FROM units WHERE unit_id = '$unit_id'";
    $unitResult = mysqli_query($db, $unitQuery);
    $unitRow = mysqli_fetch_assoc($unitResult);
    $recipeUnit = $unitRow['unit_name'];

    // 2. Get the stock unit of the ingredient
    $stockQuery = "SELECT UNIT FROM ingredients WHERE INGREDIENTS_ID = '$ingredient_id'";
    $stockResult = mysqli_query($db, $stockQuery);
    $stockRow = mysqli_fetch_assoc($stockResult);
    $stockUnit = $stockRow['UNIT'];

    // 3. Handle base metric conversions
    if ($recipeUnit == $stockUnit) {
        return $quantity;
    } elseif (($recipeUnit == 'kg' && $stockUnit == 'g') || ($recipeUnit == 'lt' && $stockUnit == 'ml')) {
        return $quantity * 1000;
    } elseif (($recipeUnit == 'g' && $stockUnit == 'kg') || ($recipeUnit == 'ml' && $stockUnit == 'lt')) {
        return $quantity / 1000;
    } else {
        // 4. Handle custom units like scoop, cup, etc.
        $convQuery = "
            SELECT equivalent_in_grams 
              FROM ingredient_unit_equivalence 
             WHERE ingredient_id = '$ingredient_id' 
               AND unit_name = '$recipeUnit' 
             LIMIT 1
        ";
        $convResult = mysqli_query($db, $convQuery);
        if ($convRow = mysqli_fetch_assoc($convResult)) {
            return $quantity * $convRow['equivalent_in_grams'];
        } else {
            error_log("⚠️ No conversion found for $recipeUnit of ingredient $ingredient_id");
            return $quantity; // fallback
        }
    }
}

?>
