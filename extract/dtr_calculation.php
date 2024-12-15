<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database configuration
$host = 'localhost';
$dbname = 'system';
$username = 'root';
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
    // Get POST parameters
    $fullName = trim($_POST['fullName']);
    $academicYear = $_POST['academicYear'];
    $academicSemester = $_POST['academicSemester'];
    $academicMonth = $_POST['month'];

    // Fetch employee ID
    $stmt = $pdo->prepare("SELECT userId FROM employee WHERE CONCAT(TRIM(firstName), ' ', TRIM(IFNULL(middleName, '')), ' ', TRIM(lastName)) = :fullName LIMIT 1");
    $stmt->execute([':fullName' => $fullName]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        die("Employee not found: " . htmlspecialchars($fullName));
    }

    $employeeId = $employee['userId'];

    // Handle file upload
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['excelFile']['name']);
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['excelFile']['tmp_name'], $filePath)) {
        die("File upload failed.");
    }

    try {
        // Load spreadsheet and target sheet
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getSheetByName('Table 3');

        if ($worksheet === null) {
            throw new Exception("The sheet 'Table 3' does not exist.");
        }

        $fileContent = file_get_contents($filePath);

        // Prepare SQL query
        $sql = "INSERT INTO faculty_dtr (userId, week_1, week_2, week_3, week_4, week_5, weekly_total, overtime_earned, academic_year, academic_sem, academic_month, dtr_file, dtr_filename) 
        VALUES (:employeeId, :week1, :week2, :week3, :week4, :week5, :total, :overtime, :academicYear, :academicSemester, :month, :file, :filename)";
        $stmt = $pdo->prepare($sql);

        // Initialize weekly sums and overtime
        $weeklySums = [0, 0, 0, 0, 0];
        $weeklyOvertime = [0, 0, 0, 0, 0];
        $rowCounter = 0;

        // Process rows in the specified range
        foreach ($worksheet->getRowIterator(4, 34) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $data = [];
            foreach ($cellIterator as $cell) {
                $data[] = $cell->getValue();
            }

            $total = $data[7] ?? null;
            $remarks = $data[11] ?? null;

            if (!empty($remarks)) {
                $value = strtolower(trim($remarks)) === 'sunday' ? '0:00' : '8:00';
            } else {
                $value = $total ?? '0:00';
            }

            if (strpos($value, ':') !== false) {
                list($hours, $minutes) = explode(':', $value);
                $value = $hours + ($minutes / 60);
            }

            $value = is_numeric($value) ? $value : 0;

            $weekIndex = floor($rowCounter / 7); // Determine the week index
            if ($weekIndex < 5) {
                $weeklySums[$weekIndex] += (float)$value;
            }

            $rowCounter++;
        }

        // Calculate overtime for each week and total overtime
        $totalSum = array_sum($weeklySums);
        $totalOvertime = 0;

        for ($i = 0; $i < 5; $i++) {
            $weeklyOvertime[$i] = max(0, $weeklySums[$i] - 40); // Calculate weekly overtime
            $totalOvertime += $weeklyOvertime[$i]; // Sum up total overtime
        }

        // Execute the query
        $stmt->execute([
            ':employeeId' => $employeeId,
            ':week1' => $weeklySums[0],
            ':week2' => $weeklySums[1],
            ':week3' => $weeklySums[2],
            ':week4' => $weeklySums[3],
            ':week5' => $weeklySums[4],
            ':total' => $totalSum,
            ':overtime' => $totalOvertime,
            ':academicYear' => $academicYear,
            ':academicSemester' => $academicSemester,
            ':month' => $academicMonth,
            ':file' => $fileContent,
            ':filename' => $fileName,
        ]);

        header('Location: ../admin/dtr.php');
        } catch (Exception $e) {
        die("Error processing file: " . $e->getMessage());
        }

}
?>