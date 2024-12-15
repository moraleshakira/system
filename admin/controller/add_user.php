<?php
// Include database connection
include_once('../config/config.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $employeeId = $_POST['employeeId'];
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $phoneNumber = $_POST['phoneNumber'];
    $roleId = $_POST['role'];  // The role_id selected by the user

    // Validate required fields
    if (empty($employeeId) || empty($lastName)) {
        die("Employee ID or Last Name is missing.");
    }

    // Generate default password and hash it
    $generatedPassword = $employeeId . '@' . $lastName; // Default password format
    $hashedPassword = password_hash($generatedPassword, PASSWORD_DEFAULT);

    // Debugging: Check if password is hashed
    if (!$hashedPassword) {
        die("Password hashing failed.");
    }

    // Insert employee data into the `employee` table
    $query = "INSERT INTO employee (employeeId, firstName, middleName, lastName, emailAddress, phoneNumber, password) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($query);

    if (!$stmt) {
        die('Error preparing employee insertion query: ' . $con->error);
    }

    $stmt->bind_param("sssssss", $employeeId, $firstName, $middleName, $lastName, $emailAddress, $phoneNumber, $hashedPassword);

    if (!$stmt->execute()) {
        die('Error executing employee insertion query: ' . $stmt->error);
    }

    // Get the last inserted userId
    $userId = $con->insert_id;

    // Insert the role for the employee into the `roles_employee` table
    $rolesEmployeeQuery = "INSERT INTO roles_employee (userId, roleId) VALUES (?, ?)";
    $rolesEmployeeStmt = $con->prepare($rolesEmployeeQuery);

    if (!$rolesEmployeeStmt) {
        die('Error preparing role assignment query: ' . $con->error);
    }

    $rolesEmployeeStmt->bind_param("is", $userId, $roleId);

    if ($rolesEmployeeStmt->execute()) {
        header("Location: ../user.php");
    } else {
        die("Error assigning role to employee: " . $rolesEmployeeStmt->error);
    }
}
