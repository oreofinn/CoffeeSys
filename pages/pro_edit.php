<?php
include'../includes/connection.php';

include'../includes/sidebar.php';
  $query = 'SELECT ID, t.TYPE
            FROM users u
            JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
  $result = mysqli_query($db, $query) or die (mysqli_error($db));
  
  while ($row = mysqli_fetch_assoc($result)) {
            $Aa = $row['TYPE'];
                   
  if ($Aa=='User'){
?>
  <script type="text/javascript">
    //then it will be redirected
    alert("Restricted Page! You will be redirected to POS");
    window.location = "pos.php";
  </script>
<?php
  }           
}
$sql = "SELECT DISTINCT category_name, category_id FROM category ORDER BY category_name ASC";
$result = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$opt = "<select class='form-control' name='category' required>
        <option value='' disabled selected hidden>Select Category</option>";
  while ($row = mysqli_fetch_assoc($result)) {
    $opt .= "<option value='".$row['category_id']."'>".$row['category_name']."</option>";
  }

$opt .= "</select>";

// Fetch all recipes for the dropdown
$recipeSql = "SELECT recipe_id, recipe_name FROM recipes ORDER BY recipe_name ASC";
$recipeResult = mysqli_query($db, $recipeSql) or die ("Bad SQL: $recipeSql");

$recipeOpt = "<select class='form-control' name='recipe_id' required>
        <option value='' disabled selected hidden>Select Recipe</option>";
  while ($row = mysqli_fetch_assoc($recipeResult)) {
    $recipeOpt .= "<option value='".$row['recipe_id']."'>".$row['recipe_name']."</option>";
  }

$recipeOpt .= "</select>";

  $query = 'SELECT PRODUCT_ID, PRODUCT_CODE, NAME, DESCRIPTION, p.recipe_id, r.recipe_name, QTY_STOCK, PRICE, c.category_name 
            FROM product p 
            JOIN category c ON p.category_id=c.category_id 
            LEFT JOIN recipes r ON p.recipe_id=r.recipe_id
            WHERE PRODUCT_ID ='.$_GET['id'];
            
  $result = mysqli_query($db, $query) or die(mysqli_error($db));
    while($row = mysqli_fetch_array($result))
    {   
      $zz = $row['PRODUCT_ID'];
      $zzz = $row['PRODUCT_CODE'];
      $A = $row['NAME'];
      $B = $row['DESCRIPTION'];
      $C = $row['PRICE'];
      $D = $row['category_name'];
      $E = $row['recipe_name'];
      $recipeId = $row['recipe_id'];
    }
      $id = $_GET['id'];
?>

  <center><div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
            <div class="card-header py-3">
              <h4 class="m-2 font-weight-bold text-primary">Edit Product</h4>
            </div>
            <a href="product.php?action=add" type="button" class="btn btn-primary bg-gradient-primary">Back</a>
            <div class="card-body">

            <form role="form" method="post" action="pro_edit1.php">
              <input type="hidden" name="id" value="<?php echo $zz; ?>" />
              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                 Product Code:
                </div>
                <div class="col-sm-9">
                  <input class="form-control" placeholder="Product Code" name="prodcode" value="<?php echo $zzz; ?>" readonly>
                </div>
              </div>
              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                 Product Name:
                </div>
                <div class="col-sm-9">
                  <input class="form-control" placeholder="Product Name" name="prodname" value="<?php echo $A; ?>" required>
                </div>
              </div>
              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                 Description:
                </div>
                <div class="col-sm-9">
                   <textarea class="form-control" placeholder="Description" name="description"><?php echo $B; ?></textarea>
                </div>
              </div>
              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                 Recipe:
                </div>
                <div class="col-sm-9">
                  <?php
                    // Modify the recipe dropdown to select the current recipe
                    $recipeSelect = str_replace("value='".$recipeId."'", "value='".$recipeId."' selected", $recipeOpt);
                    echo $recipeSelect;
                  ?>
                </div>
              </div>
              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                 Price:
                </div>
                <div class="col-sm-9">
                  <input class="form-control" placeholder="Price" name="price" value="<?php echo $C; ?>" required>
                </div>
              </div>
              <div class="form-group row text-left text-warning">
                <div class="col-sm-3" style="padding-top: 5px;">
                 Category:
                </div>
                <div class="col-sm-9">
                   <?php
                    echo $opt;
                   ?>
                </div>
              </div>
              <hr>

                <button type="submit" class="btn btn-warning btn-block"><i class="fa fa-edit fa-fw"></i>Update</button>    
              </form>  
            </div>
          </div></center>

<?php
include'../includes/footer.php';
?>