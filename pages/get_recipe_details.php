<?php
header('Content-Type: application/json');
include '../includes/connection.php';

// 1) validate
if (!isset($_GET['recipe_id'])) {
  echo json_encode(['error' => 'recipe_id is required']);
  exit;
}
$recipe_id = (int)$_GET['recipe_id'];

// 2) fetch the recipe header
$hdrSql = "
  SELECT recipe_code, recipe_name, category
    FROM recipes
   WHERE recipe_id = {$recipe_id}
";
$hdrRes = mysqli_query($db, $hdrSql);
if (!$hdrRes) {
  echo json_encode(['error' => mysqli_error($db)]);
  exit;
}
$hdr = mysqli_fetch_assoc($hdrRes);

// 3) fetch its ingredients
$ingSql = "
  SELECT 
    ri.ingredient_id   AS id,
    i.ING_NAME         AS name,
    ri.quantity        AS quantity,
    ri.unit_id         AS unit_id,
    u.unit_name        AS unit_name
  FROM recipe_ingredients ri
  JOIN ingredients i ON ri.ingredient_id = i.INGREDIENTS_ID
  JOIN units       u ON ri.unit_id       = u.unit_id
  WHERE ri.recipe_id = {$recipe_id}
";
$ingRes = mysqli_query($db, $ingSql);
$ingredients = [];
while ($row = mysqli_fetch_assoc($ingRes)) {
  $ingredients[] = $row;
}

// 4) assemble and return
$payload = [
  'recipe_code' => $hdr['recipe_code'],
  'recipe_name' => $hdr['recipe_name'],
  'category'    => $hdr['category'],
  'ingredients' => $ingredients
];
echo json_encode($payload);
