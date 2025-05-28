<?php
include '../includes/connection.php';

$action = $_GET['action'] ?? '';

switch ($action) {
  case 'add':
    // Gather POST for add
    $name         = mysqli_real_escape_string($db, $_POST['name']);
    $desc         = mysqli_real_escape_string($db, $_POST['description']);
    $recipe_id    = (int) $_POST['recipe'];
    $category_id  = (int) $_POST['category'];
    $pm           = mysqli_real_escape_string($db, $_POST['price_medium']);
    $pl           = mysqli_real_escape_string($db, $_POST['price_large']);
    $dats         = date('Y-m-d');
    // Generate product_code as you already do...
    // Then:
    $sql = "
      INSERT INTO product
        (PRODUCT_CODE, NAME, DESCRIPTION, recipe_id, CATEGORY_ID, price_medium, price_large, DATE_STOCK_IN)
      VALUES
        ('{$product_code}', '$name', '$desc', $recipe_id, $category_id, '$pm', '$pl', '$dats')
    ";
    mysqli_query($db, $sql) or die(mysqli_error($db));
    break;

  case 'edit':
    // Gather POST for edit
    $id           = (int) $_POST['id'];
    $name         = mysqli_real_escape_string($db, $_POST['name']);
    $desc         = mysqli_real_escape_string($db, $_POST['description']);
    $recipe_id    = (int) $_POST['recipe'];
    $category_id  = (int) $_POST['category'];
    $pm           = mysqli_real_escape_string($db, $_POST['price_medium']);
    $pl           = mysqli_real_escape_string($db, $_POST['price_large']);
    $sql = "
      UPDATE product
      SET
        NAME         = '$name',
        DESCRIPTION  = '$desc',
        recipe_id    = $recipe_id,
        CATEGORY_ID  = $category_id,
        price_medium = '$pm',
        price_large  = '$pl'
      WHERE PRODUCT_ID = $id
    ";
    mysqli_query($db, $sql) or die(mysqli_error($db));
    break;

  case 'delete':
    // Gather POST for delete
    $id = (int) $_POST['id'];
    $sql = "DELETE FROM product WHERE PRODUCT_ID = $id";
    mysqli_query($db, $sql) or die(mysqli_error($db));
    break;
}

// After any action, go back to the list
header('Location: product.php');
exit;
