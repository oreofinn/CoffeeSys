<?php
include '../includes/connection.php';

// 1) Pull in exactly the fields your modal posts
$id           = mysqli_real_escape_string($db, $_POST['id']);
$name         = mysqli_real_escape_string($db, $_POST['name']);
$description  = mysqli_real_escape_string($db, $_POST['description']);

// NOTE: if your <select> is name="recipe" use $_POST['recipe'], or if you named it recipe_id use $_POST['recipe_id']
$recipe_id    = (int) $_POST['recipe'];

// Likewise, category
$category_id  = (int) $_POST['category'];

// Your two new size‐based prices
$price_medium = mysqli_real_escape_string($db, $_POST['price_medium']);
$price_large  = mysqli_real_escape_string($db, $_POST['price_large']);

// 2) Build the UPDATE to only the columns you care about
$sql = "
  UPDATE product
  SET
    NAME          = '$name',
    DESCRIPTION   = '$description',
    recipe_id     = $recipe_id,
    CATEGORY_ID   = $category_id,
    price_medium  = '$price_medium',
    price_large   = '$price_large'
  WHERE PRODUCT_ID = $id
";

// 3) Run it
mysqli_query($db, $sql) or die('Error updating product: ' . mysqli_error($db));

// 4) Redirect back
header('Location: product.php');
exit;
