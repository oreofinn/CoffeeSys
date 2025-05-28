<?php
include '../includes/connection.php';
include '../includes/sidebar.php';

// Restrict access for "User" type
$query = 'SELECT ID, t.TYPE
          FROM users u
          JOIN type t ON t.TYPE_ID = u.TYPE_ID 
          WHERE ID = ' . $_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    if ($row['TYPE'] == 'User') {
        ?>
        <script type="text/javascript">
            alert("Restricted Page! You will be redirected to POS");
            window.location = "pos.php";
        </script>
        <?php
        exit;
    }
}

// Fetch transaction details
$trans_id = mysqli_real_escape_string($db, $_GET['id']);
$query = 'SELECT T.TRANS_ID, T.DATE 
          FROM transaction T
          WHERE T.TRANS_ID = ' . $trans_id;
$result = mysqli_query($db, $query) or die(mysqli_error($db));

if ($row = mysqli_fetch_assoc($result)) {
    $date = $row['DATE'];
} else {
    echo "<script>alert('Transaction not found.'); window.location = 'transactions.php';</script>";
    exit;
}
?>
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="form-group row text-left mb-0">
            <div class="col-sm-9">
                <h5 class="font-weight-bold">Details</h5>
            </div>
            <div class="col-sm-3 py-1">
                <h6>Date: <?php echo $date; ?></h6>
            </div>
        </div>
        <hr>
        <div class="form-group row text-left mb-0 py-2">
            <div class="col-sm-4 py-1"></div>
            <div class="col-sm-4 py-1"></div>
            <div class="col-sm-4 py-1">
                <h6>Transaction #<?php echo $trans_id; ?></h6>
            </div>
        </div>
        <table class="table table-bordered" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>Products</th>
                    <th width="8%">Qty</th>
                    <th width="20%">Price</th>
                    <th width="20%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
<?php  
$query = 'SELECT PRODUCT, QTY, PRICE 
          FROM transaction_details
          WHERE TRANS_ID = ' . $trans_id;
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    $subtotal = $row['QTY'] * $row['PRICE'];
    echo '<tr>';
    echo '<td>' . $row['PRODUCT'] . '</td>';
    echo '<td>' . $row['QTY'] . '</td>';
    echo '<td>₱ ' . number_format($row['PRICE'], 2) . '</td>';
    echo '<td>₱ ' . number_format($subtotal, 2) . '</td>';
    echo '</tr>';
}
?>
            </tbody>
        </table>
    </div>
</div>
<?php
include '../includes/footer.php';
?>
