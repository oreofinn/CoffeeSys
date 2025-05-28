<?php

include('../includes/connection.php');
			$zz = $_POST['id'];
			$pc = $_POST['prodcode'];
			$ingname = $_POST['ing_name'];
            $quan = $_POST['quantity'];
            $prs = $_POST['expenses'];
            $cat = $_POST['category'];
			
		
	 			$query = 'UPDATE product set NAME="'.$pname.'",
					ing_name="'.$ingname.'", quantity="'.$quan.'", expenses="'.$prs.'", CNAME ="'.$catn.'" WHERE
					PRODUCT_CODE ="'.$pc.'"';
					$result = mysqli_query($db, $query) or die(mysqli_error($db));

							
?>	
	<script type="text/javascript">
			alert("You've Update Product Successfully.");
			window.location = "pro_ingredients.php";
		</script>