<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection settings
$host = 'localhost';
$dbname = 'system';
$user = 'root';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = $_FILES['file']['tmp_name'];

    try {
        // Load the Excel file
        $spreadsheet = IOFactory::load($fileName);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Database connection
        $conn = new mysqli($host, $user, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare the SQL statements
        $stmtEmployee = $conn->prepare("
            INSERT INTO employee (employeeId, lastName, firstName, emailAddress, password)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmtRole = $conn->prepare("
            INSERT INTO roles_employee (userId, roleId)
            VALUES (?, ?)
        ");

        // Loop through the rows, starting from the second row (index 1)
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip the header row

            // Column mapping (based on your description)
            $facultyId = $row[0];  // Faculty_ID -> employeeId
            $lastName = $row[1];   // LastName -> lastName
            $firstName = $row[2];  // FirstName -> firstName
            $email = $row[3];      // Email -> emailAddress
            $roles = array_slice($row, 4); // Extract role IDs (all columns starting from index 4)

            // Generate the password (LastName + Faculty_ID)
            $password = $lastName . $facultyId;
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Skip if any required value is missing
            if ($facultyId && $lastName && $firstName && $email && !empty($roles)) {
                // Insert into the employee table
                $stmtEmployee->bind_param(
                    'sssss',
                    $facultyId, // employeeId
                    $lastName,  // lastName
                    $firstName, // firstName
                    $email,     // emailAddress
                    $hashedPassword // password
                );
                $stmtEmployee->execute();

                // Get the auto-incremented userId
                $userId = $conn->insert_id;

                // Insert into the roles_employee table for each role ID
                foreach ($roles as $roleId) {
                    if (!empty($roleId)) { // Ensure roleId is not empty
                        $stmtRole->bind_param(
                            'ii',
                            $userId, // Auto-incremented userId
                            $roleId  // roleId
                        );
                        $stmtRole->execute();
                    }
                }
            }
        }

        $stmtEmployee->close();
        $stmtRole->close();
        $conn->close();

        header("Location: ../admin/user.php");
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>
