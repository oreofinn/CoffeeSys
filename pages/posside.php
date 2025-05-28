<!-- SIDE PART NA SUMMARY -->
<div class="card-body col-md-3">
<?php
// Initialize $total to avoid undefined variable warning
$total = 0;

// Only calculate total if there are items
if(!empty($_SESSION['pointofsale'])):  
    foreach($_SESSION['pointofsale'] as $key => $product): 
        $total = $total + ($product['quantity'] * $product['price']);
    endforeach;
?>  
<?php 
    date_default_timezone_set('Asia/Manila');
    echo "Today's date is : "; 
    $today = date("Y-m-d H:i a"); 
    echo $today; 
?> 

<!-- Generate a random 6-digit transaction ID -->
<?php
$trans_id = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
?>

<input type="hidden" name="date" value="<?php echo $today; ?>">
<input type="hidden" name="trans_id" value="<?php echo $trans_id; ?>"> <!-- Add the hidden trans_id field -->

<div class="form-group row mb-2">
    <div class="col-sm-5 text-left text-primary py-2">
        <h6>
            Subtotal
        </h6>
    </div>
    <div class="col-sm-7">
        <div class="input-group mb-2">
            <div class="input-group-prepend">
                <span class="input-group-text">P</span>
            </div>
            <input type="text" class="form-control text-right" value="<?php echo number_format($total, 2); ?>" readonly name="subtotal">
        </div>
    </div>
</div>

<div class="form-group row text-left mb-2">
    <div class="col-sm-5 text-primary">
        <h6 class="font-weight-bold py-2">
            Total
        </h6>
    </div>
    <div class="col-sm-7">
        <div class="input-group mb-2">
            <div class="input-group-prepend">
                <span class="input-group-text">P</span>
            </div>
            <input type="text" class="form-control text-right" value="<?php echo number_format($total, 2); ?>" readonly name="total">
        </div>
    </div>
</div>
<?php endif; ?>       

<!-- Check if submit was clicked with no items -->
<?php if(isset($_POST['checkItems']) && empty($_SESSION['pointofsale'])): ?>
<div class="alert alert-warning">
    <strong>Warning!</strong> No items selected yet. Please select items to complete your order.
</div>
<?php endif; ?>

<!-- Form Action to process the order -->
<form method="POST" action="pos_transac.php?action=add">
    <!-- Add a hidden field to check if submit was clicked -->
    <input type="hidden" name="checkItems" value="1">
    
    <!-- Submit button - no disabled attribute so it can be clicked -->
    <button type="submit" class="btn btn-block btn-success" <?php if(!empty($_SESSION['pointofsale'])): ?>data-toggle="modal" data-target="#posMODAL"<?php endif; ?>>SUBMIT</button>

    <!-- Modal - only shows if items are in cart -->
    <?php if(!empty($_SESSION['pointofsale'])): ?>
    <div class="modal fade" id="posMODAL" tabindex="-1" role="dialog" aria-labelledby="POS" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">SUMMARY</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row text-left mb-2">
                        <div class="col-sm-12 text-center">
                            <h3 class="py-0">
                                GRAND TOTAL
                            </h3>
                            <h3 class="font-weight-bold py-3 bg-light">
                                P <?php echo number_format($total, 2); ?>
                            </h3>
                        </div>
                    </div>

                    <div class="col-sm-12 mb-2">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text">P</span>
                            </div>
                            <input class="form-control text-right" id="txtNumber" onkeypress="return isNumberKey(event)" type="text" name="cash" placeholder="ENTER CASH" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="confirm" class="btn btn-primary btn-block">CONFIRM ORDER</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- END OF Modal -->
</form>

<?php
// Handle form submission only if 'cash' field exists and confirm button was pressed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cash']) && isset($_POST['confirm'])) {
    // Capture POST data
    $cash = $_POST['cash'];

    // Insert into transactions table
    $transactionQuery = "INSERT INTO transaction (TRANS_ID, DATE, GRANDTOTAL, CASH) VALUES ('$trans_id', '$today', '$total', '$cash')";
    if (mysqli_query($db, $transactionQuery)) {

            // Insert into transaction_details table
            foreach ($_SESSION['pointofsale'] as $product) {
                $productName = $product['name']; // Get product name from session
                $quantity = $product['quantity'];
                $price = $product['price'];

                // Modify the query to store the product's name instead of product ID
                $detailsQuery = "INSERT INTO transaction_details (TRANS_ID, PRODUCT, QTY, PRICE) 
                                VALUES ('$trans_id', '$productName', '$quantity', '$price')";
                mysqli_query($db, $detailsQuery);
            }


        // Clear the session data after successful insertion
        unset($_SESSION['pointofsale']);
        echo "<script>alert('Order successfully processed!'); window.location.href = 'pos.php';</script>";
    } else {
        echo "<script>alert('Error processing order. Please try again.');</script>";
    }
}
?>
</div> <!-- END OF CARD BODY -->

</div>
