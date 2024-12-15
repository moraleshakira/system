<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection settings
$host = 'localhost';
$dbname = 'system';
$user = 'root';
$password = '';

try {
    // Connect to the MySQL database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploadFile'])) {
        // Get form data
        $fullName = $_POST['fullName'];  // Full name of the employee
        $academicYear = $_POST['academicYear'];
        $academicSemester = $_POST['academicSemester'];

        // Fetch employeeId based on the fullName (Concatenate firstName, middleName, and lastName in the query)
        $stmt = $pdo->prepare("
            SELECT userId 
            FROM employee 
            WHERE CONCAT(TRIM(firstName), ' ', TRIM(IFNULL(middleName, '')), ' ', TRIM(lastName)) = :fullName 
            LIMIT 1
        ");
        $stmt->execute([':fullName' => trim($fullName)]);

        // Fetch the result
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee) {
            $employeeId = $employee['userId'];
        } else {
            echo "Employee not found in the database for: " . htmlspecialchars($fullName);
            exit;
        }

        // File processing
        $fileTmpPath = $_FILES['uploadFile']['tmp_name'];
        $fileName = $_FILES['uploadFile']['name'];
        $fileContent = file_get_contents($fileTmpPath);

        // Load the Excel file
        $spreadsheet = IOFactory::load($fileTmpPath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Variables to store extracted values
        $facultyCredit = null;
        $designationLoadReleased = null;
        $designation_itl = null;

        // Loop through the rows and find the required values
        foreach ($data as $row) {
            foreach ($row as $cell) {
                if (is_string($cell) && stripos($cell, 'FACULTY CREDIT') !== false) {
                    $facultyCredit = $row[9] ?? null; // Assuming the value is in the 10th column
                }
                if (is_string($cell) && stripos($cell, 'DESIGNATION, LOAD RELEASED') !== false) {
                    $designationLoadReleased = $row[9] ?? null; // Assuming the value is in the 10th column
                }
                if (is_string($cell) && stripos($cell, 'Designation:') !== false) {
                    $designation_itl = $row[11] ?? null; // Assuming the value is in the 12th column
                }
            }
        }

        // Ensure the required data was found
        if ($facultyCredit !== null && $designationLoadReleased !== null) {
            // Insert extracted values and file into the database
            $stmt = $pdo->prepare("
                INSERT INTO faculty_load (faculty_credit, designation_load_released, designation_itl, academic_year, academic_sem, userId, file, filename) 
                VALUES (:facultyCredit, :designationLoadReleased, :designation_itl, :academicYear, :academicSemester, :employeeId, :file, :filename)
            ");
            $stmt->execute([
                ':facultyCredit' => $facultyCredit,
                ':designationLoadReleased' => $designationLoadReleased,
                ':designation_itl' => $designation_itl,
                ':academicYear' => $academicYear,
                ':academicSemester' => $academicSemester,
                ':employeeId' => $employeeId,  // userId sa db
                ':file' => $fileContent,
                ':filename' => $fileName
            ]);

            header('Location: ../admin/itl.php'); // Assuming 'itl.php' is the form page

        } else {
            echo "Could not find the required data in the Excel file.";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
