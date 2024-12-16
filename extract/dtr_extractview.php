<?php
require 'vendor/autoload.php'; // PhpSpreadsheet library

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database configuration
$host = 'localhost';
$dbname = 'faculty';
$username = 'root';
$password = '';

try {
    // Database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle file upload and processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
    $uploadDir = 'uploads/';
    $fileName = basename($_FILES['excelFile']['name']);
    $filePath = $uploadDir . $fileName;

    // Create upload directory if not exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($_FILES['excelFile']['tmp_name'], $filePath)) {
        try {
            // Load Excel file
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Define columns
            $startRow = 2;  // Starting row
            $endRow = 34;   // Ending row
            $daysOfWeek = ['M', 'T', 'W', 'Th', 'F', 'S', 'Su'];

            // Initialize weekly totals
            $weeklyTotals = [];
            $currentWeekTotal = 0;
            $dayCounter = 0;

            // Process rows
            for ($row = $startRow; $row <= $endRow; $row++) {
                $day = trim($worksheet->getCell('A' . $row)->getValue()); // Days column
                $totalCell = $worksheet->getCell('H' . $row)->getValue(); // TOTAL column
                $remarks = trim($worksheet->getCell('L' . $row)->getValue()); // REMARKS column

                $totalHours = 0;

                // Logic for TOTAL column
                if (!empty($totalCell)) {
                    if (strpos($totalCell, ':') !== false) {
                        list($hours, $minutes) = explode(':', $totalCell);
                        $totalHours = $hours + ($minutes / 60);
                    } else {
                        $totalHours = '8:00'; // Default 8 hours if TOTAL has data
                    }
                } elseif (strtolower($remarks) === 'sunday') {
                    $totalHours = 0; // Sunday is 0 hrs
                } elseif (!empty($remarks)) {
                    $totalHours = '8:00'; // Other remarks (like Holiday) assume 8 hrs
                }

                // Accumulate weekly total
                $currentWeekTotal += $totalHours;
                $dayCounter++;

                // Finalize the week when Sunday is encountered
                if ($day === 'Su' || $dayCounter === 7) {
                    $weeklyTotals[] = $currentWeekTotal; // Store the weekly total
                    $currentWeekTotal = 0; // Reset total for next week
                    $dayCounter = 0; // Reset day counter
                }
            }

            // Insert weekly totals into the database
            $stmt = $pdo->prepare("INSERT INTO weekly_totals (week_number, total_hours) VALUES (:week_number, :total_hours)");

            foreach ($weeklyTotals as $index => $total) {
                // Format total hours in `00:00` time format
                $hours = (int)$total;
                $minutes = ($total - $hours) * 60;
                $formattedTotal = sprintf('%02d:%02d', $hours, $minutes);

                $stmt->execute([
                    ':week_number' => 'Week ' . ($index + 1),
                    ':total_hours' => $formattedTotal
                ]);
            }

            echo "File successfully imported and weekly totals calculated!";

        } catch (Exception $e) {
            echo "Error processing file: " . $e->getMessage();
        }
    } else {
        echo "Failed to upload file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="//cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    <title>Upload Excel File</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Upload Excel File</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="excelFile" accept=".xls,.xlsx" required>
        <button type="submit">Upload and Import</button>
    </form>

    <!-- Preview Extracted Rows -->
    <?php if (!empty($previewData)): ?>
        <h2>Preview of Extracted Data</h2>
        <table id="previewTable">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Total Hours</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($previewData as $data): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($data['day']); ?></td>
                        <td><?php echo htmlspecialchars($data['total']); ?> hrs</td>
                        <td><?php echo htmlspecialchars($data['remarks']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Display Weekly Totals -->
    <?php if (!empty($weeklyTotals)): ?>
        <h2>Weekly Totals</h2>
        <table id="weeklyTotalsTable">
            <thead>
                <tr>
                    <th>Week Number</th>
                    <th>Total Hours</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($weeklyTotals as $index => $total): ?>
                    <tr>
                        <td><?php echo 'Week ' . ($index + 1); ?></td>
                        <td><?php echo htmlspecialchars($total); ?> hrs</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Include DataTables and jQuery -->
    <script src="//cdn.datatables.net/2.1.8/js/jquery.dataTables.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#previewTable').DataTable();
            $('#weeklyTotalsTable').DataTable();
        });
    </script>
</body>
</html>
