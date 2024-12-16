<?php
// Include your database connection
include_once('../config/config.php');

// Fetch roles from the roles table
$query = "SELECT roleId, role_Name FROM roles";
$result = $con->query($query);

// Check for any errors in the query
if (!$result) {
    die('Error fetching roles: ' . $con->error);
}
?>
<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

        <div class="tabulars--wrapper">
            <div class="container mt-5">
            <form method="POST" action="./controller/add_user.php">
            <div class="card-body">
                <h4 class="card-title">Personal Information</h4>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="employeeId">Employee ID</label>
                        <input type="text" class="form-control" id="employeeId" name="employeeId" placeholder="Enter Employee ID" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="firstName">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter First Name" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="middlename">Middle Name</label>
                        <input type="text" class="form-control" id="middlename" name="middleName" placeholder="Enter Middle Name">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="lastname">Last Name</label>
                        <input type="text" class="form-control" id="lastname" name="lastName" placeholder="Enter Last Name" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="emailAddress" placeholder="Enter Email" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="contact">Contact</label>
                        <input type="text" class="form-control" id="contact" name="phoneNumber" placeholder="Enter Contact Details">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="role">Account Role</label>
                        <select class="form-control" name="role" id="role" required>
                            <option value="" disabled selected>Select Role</option>

                            <?php
                            // Loop through the roles and create options for the select dropdown
                            while ($row = $result->fetch_assoc()) {
                                $roleId = $row['roleId'];
                                $roleName = $row['role_Name'];
                                echo "<option value='$roleId'>$roleName</option>";
                            }
                            ?>

                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='user.php';">Cancel</button>
                </div>
            </div>
        </form>

            </div>
        </div>
    </div>


<?php
include('./includes/footer.php');
?>