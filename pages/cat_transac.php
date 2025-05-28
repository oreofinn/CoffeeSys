<?php
include '../includes/connection.php';

// Handle the 'add' action for categories
switch ($_GET['action']) {
    case 'add':
        // Retrieve category name from the form
        $name = mysqli_real_escape_string($db, $_POST['name']);

        // Insert the new category into the database
        $query = "INSERT INTO category (category_name) VALUES ('{$name}')";
        if (mysqli_query($db, $query)) {
            // Redirect to category.php with a success status
            echo '<script type="text/javascript">window.location = "category.php?status=success";</script>';
        } else {
            // Redirect to category.php with an error status
            echo '<script type="text/javascript">window.location = "category.php?status=error";</script>';
        }
        break;

    default:
        // Redirect to category.php if action is invalid or missing
        echo '<script type="text/javascript">window.location = "category.php";</script>';
        break;

        case 'edit':
          $id = $_POST['id'];
          $name = mysqli_real_escape_string($db, $_POST['name']);
          
          $query = "UPDATE category SET category_name = '{$name}' WHERE category_id = {$id}";
          if (mysqli_query($db, $query)) {
              echo '<script type="text/javascript">window.location = "category.php?status=updated";</script>';
          } else {
              echo '<script type="text/javascript">window.location = "category.php?status=error";</script>';
          }
          break;
      
}


?>
