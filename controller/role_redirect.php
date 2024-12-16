<?php
session_start();

// Ensure that a role was selected and is in the session roles array
if (isset($_POST['selected_role']) && in_array($_POST['selected_role'], $_SESSION['roles'])) {
    $selected_role = $_POST['selected_role'];

    // Redirect based on the selected role
    switch ($selected_role) {
        case 'Admin':
            header("Location: ../admin/index.php");
            break;
        case 'Hr':
            header("Location: ../hr/h-dashboard.php");
            break;
        case 'Staff':
            header("Location: ../staff/s-dashboard.php");
            break;
        case 'Faculty':
            header("Location: ../faculty/f-dashboard.php");
            break;
        default:
            $_SESSION['status'] = "Role not recognized.";
            $_SESSION['status_code'] = "error";
            header("Location: ../login.php");
    }
    exit();
} else {
    // Redirect if no valid role selected
    $_SESSION['status'] = "Invalid role selection.";
    $_SESSION['status_code'] = "error";
    header("Location: ../login.php");
    exit();
}