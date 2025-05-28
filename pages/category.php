<?php
include '../includes/connection.php';
include '../includes/sidebar.php';

// Redirect users with restricted access
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));
// tanggal muna c user

// Fetch all categories
$sql = "SELECT category_id, category_name FROM category ORDER BY category_name ASC";
$result = mysqli_query($db, $sql) or die("Bad SQL: $sql");

?>

<?php
if (isset($_GET['status'])) {
    $message = '';
    if ($_GET['status'] == 'success') {
        $message = 'Category added successfully!';
    } elseif ($_GET['status'] == 'updated') {
        $message = 'Category updated successfully!';
    } elseif ($_GET['status'] == 'error') {
        $message = 'An error occurred. Please try again.';
    }

    if (!empty($message)) {
        echo '
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="statusMessage">
            ' . $message . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        ';
    }
}
?>
<script>
// Auto-dismiss the alert after 5 seconds
setTimeout(() => {
    const alert = document.getElementById('statusMessage');
    if (alert) {
        alert.classList.remove('show'); // Hides the alert visually
        alert.classList.add('fade');   // Adds the fade effect
    }
}, 5000);
</script>



<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">
            Categories
            <a href="#" data-toggle="modal" data-target="#aModal" class="btn btn-primary bg-gradient-brown" style="border-radius: 2px;">
                <i class="fas fa-fw fa-plus"></i>
            </a>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Category Code</th>
                        <th>Category Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>CAT' . str_pad($row['category_id'], 4, '0', STR_PAD_LEFT) . '</td>';
                        echo '<td>' . $row['category_name'] . '</td>';
                        echo '<td>
                        <button 
                            class="btn btn-warning btn-sm edit-category-button" 
                            data-toggle="modal" 
                            data-target="#editCategoryModal" 
                            data-id="' . $row['category_id'] . '" 
                            data-name="' . $row['category_name'] . '">
                            Edit
                        </button>
                        <a href="delete_category.php?id=' . $row['category_id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')">Delete</a>
                    </td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Add Category Modal -->
<div class="modal fade" id="aModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Category</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="cat_transac.php?action=add">
                    <div class="form-group">
                        <label for="catname">Category Name</label>
                        <input type="text" class="form-control" id="catname" name="name" placeholder="Enter Category Name" required>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Save</button>
                    <button type="reset" class="btn btn-danger"><i class="fa fa-times fa-fw"></i> Reset</button>
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form for editing category -->
                <form method="post" action="cat_transac.php?action=edit">
                    <input type="hidden" id="editCategoryId" name="id"> <!-- Hidden field for category ID -->
                    <div class="form-group">
                        <label for="editCategoryName">Category Name</label>
                        <input type="text" class="form-control" id="editCategoryName" name="name" required>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Save</button>
                    <button type="reset" class="btn btn-danger"><i class="fa fa-times fa-fw"></i> Reset</button>
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript to populate the Edit Category Modal
    document.querySelectorAll('.edit-category-button').forEach(button => {
        button.addEventListener('click', function () {
            const categoryId = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-name');

            document.getElementById('editCategoryId').value = categoryId;
            document.getElementById('editCategoryName').value = categoryName;
        });
    });
</script>

