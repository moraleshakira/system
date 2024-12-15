<?php
session_start();
include '../config/config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $emailAddress = mysqli_real_escape_string($con, $_POST['emailAddress']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // Query to get user details and roles
    $login_query = "SELECT
                    employee.*,
                    roles.role_Name AS roleName
                FROM
                    employee
                INNER JOIN
                    roles_employee
                    ON employee.userId = roles_employee.userId
                INNER JOIN
                    roles
                    ON roles_employee.roleId = roles.roleId
                WHERE
                    employee.emailAddress = '$emailAddress'";

    $result = $con->query($login_query);

    if ($result && $result->num_rows > 0) {
        $roles = [];
        $userData = null;

        while ($row = $result->fetch_assoc()) {
            if (!$userData) {
                $userData = $row; // Set user data only once
            }
            $roles[] = $row['roleName']; // Collect all roles
        }

        if ($password === $userData['password']) { 
            $_SESSION['auth'] = true;
            $_SESSION['userstatus'] = $userData['status'];
            $_SESSION['roles'] = $roles;
            $_SESSION['auth_user'] = [
                'userId' => $userData['userId'],
                'fullName' => $userData['firstName'] . ' ' . $userData['lastName'],
                'email' => $userData['emailAddress']
            ];

            if ($userData['status'] == 'deactivated') {
                $_SESSION['status'] = "Your account is inactive!";
                $_SESSION['status_code'] = "warning";
                header("Location: ../index.php");
                exit();
            } elseif ($userData['status'] == 'active') {
                $_SESSION['status'] = "Welcome " . $userData['firstName'] . ' ' . $userData['lastName'] . "!";
                $_SESSION['status_code'] = "success";
                header("Location: ../loginas.php");
                exit();
            }
        } else {
            $_SESSION['status'] = "Invalid Password";
            $_SESSION['status_code'] = "error";
            header("Location: ../index.php");
            exit();
        }
    } else {
        $_SESSION['status'] = "Invalid Email Address/Password";
        $_SESSION['status_code'] = "error";
        header("Location: ../index.php");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request method.";
    $_SESSION['status_code'] = "error";
    header("Location: ../index.php");
    exit();
}

$con->close();
?>
