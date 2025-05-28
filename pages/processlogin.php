<?php
require('../includes/connection.php');
require('session.php');

if (isset($_POST['btnlogin'])) {
    $users = trim($_POST['user']);
    $upass = trim($_POST['password']);
    
    // Check for empty fields
    if (empty($users) || empty($upass)) {
        ?>
        <script type="text/javascript">
            alert("Username and password are required!");
            window.location = "login.php";
        </script>
        <?php
        exit();
    }
    
    // Hash the password using a more secure method
    // Note: If your existing accounts use sha1, you'll need to migrate them
    // This is set up to continue using sha1 for compatibility
    $h_upass = sha1($upass);
    
    // Prepare SQL statement using prepared statements to prevent SQL injection
    $sql = "SELECT u.ID, e.FIRST_NAME, e.LAST_NAME, e.GENDER, e.EMAIL, e.PHONE_NUMBER, 
                   j.JOB_TITLE, l.PROVINCE, l.CITY, t.TYPE
            FROM users u
            JOIN employee e ON e.EMPLOYEE_ID = u.EMPLOYEE_ID
            JOIN location l ON e.LOCATION_ID = l.LOCATION_ID
            JOIN job j ON e.JOB_ID = j.JOB_ID
            JOIN type t ON t.TYPE_ID = u.TYPE_ID
            WHERE u.USERNAME = ? AND u.PASSWORD = ?";
    
    // Prepare and bind
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ss", $users, $h_upass);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        // Check if we have a matching user
        if ($result->num_rows > 0) {
            // Store user data in session
            $found_user = $result->fetch_assoc();
            
            $_SESSION['MEMBER_ID'] = $found_user['ID']; 
            $_SESSION['FIRST_NAME'] = $found_user['FIRST_NAME']; 
            $_SESSION['LAST_NAME'] = $found_user['LAST_NAME'];  
            $_SESSION['GENDER'] = $found_user['GENDER'];
            $_SESSION['EMAIL'] = $found_user['EMAIL'];
            $_SESSION['PHONE_NUMBER'] = $found_user['PHONE_NUMBER'];
            $_SESSION['JOB_TITLE'] = $found_user['JOB_TITLE'];
            $_SESSION['PROVINCE'] = $found_user['PROVINCE']; 
            $_SESSION['CITY'] = $found_user['CITY']; 
            $_SESSION['TYPE'] = $found_user['TYPE'];
            
            // Set last login time
            $_SESSION['LAST_LOGIN'] = date("Y-m-d H:i:s");
            
            // Redirect based on user type
            if ($_SESSION['TYPE'] == 'Admin') {
                ?>
                <script type="text/javascript">
                    // Redirect to admin dashboard
                    alert("Welcome, <?php echo $_SESSION['FIRST_NAME']; ?>!");
                    window.location = "index.php";
                </script>
                <?php
            } elseif ($_SESSION['TYPE'] == 'User') {
                ?>
                <script type="text/javascript">
                    // Redirect to POS system
                    alert("Welcome, <?php echo $_SESSION['FIRST_NAME']; ?>!");
                    window.location = "pos.php";
                </script>
                <?php
            }
        } else {
            // No matching user found
            ?>
            <script type="text/javascript">
                alert("Invalid username or password. Please try again.");
                window.location = "login.php";
            </script>
            <?php
        }
    } else {
        // Database error
        ?>
        <script type="text/javascript">
            alert("Database error. Please contact your administrator.");
            window.location = "login.php";
        </script>
        <?php
    }
    
    // Close the statement
    $stmt->close();
}

// Close the database connection
$db->close();
?>