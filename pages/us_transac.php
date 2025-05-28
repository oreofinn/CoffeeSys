<?php
include'../includes/connection.php';

// For adding user accounts
if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $empid = $_POST['empid'];
    
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user with TYPE_ID=2 (User account based on your existing tables)
    $query = "INSERT INTO users (USERNAME, PASSWORD, EMPLOYEE_ID, TYPE_ID) 
              VALUES ('{$username}', '{$hashed_password}', '{$empid}', '2')";
    $result = mysqli_query($db, $query) or die(mysqli_error($db));
    
    // Redirect with success message
    echo '<script type="text/javascript">
            alert("User account created successfully!");
            window.location = "user.php";
          </script>';
}

// For delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    
    // Check if the user is trying to delete their own account
    if ($id == $_SESSION['MEMBER_ID']) {
        echo '<script type="text/javascript">
                alert("You cannot delete your own account!");
                window.location = "user.php";
              </script>';
    } else {
        // Delete the user account
        $query = "DELETE FROM users WHERE ID = '$id'";
        $result = mysqli_query($db, $query) or die(mysqli_error($db));
        
        echo '<script type="text/javascript">
                alert("User account deleted successfully!");
                window.location = "user.php";
              </script>';
    }
}
?>