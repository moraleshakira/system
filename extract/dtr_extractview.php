<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database configuration
$host = 'localhost';
$dbname = 'faculty';
$username = 'root';
$password = '';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$previewData = []; // For previewing data from Excel
$groupSums = [];   // To store grouped sums by weeks

// Define row limits
$startRowLimit = 4;
$endRowLimit = 34;

// Check if a file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
    $uploadDir = 'uploads/';
    $fileName = basename($_FILES['excelFile']['name']);
    $filePath = $uploadDir . $fileName;

    // Create the upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($_FILES['excelFile']['tmp_name'], $filePath)) {
        try {
            // Load the uploaded Excel file
            $spreadsheet = IOFactory::load($filePath);

            // Select the "Table 3" sheet
            $worksheet = $spreadsheet->getSheetByName('Table 3');
            if ($worksheet === null) {
                throw new Exception("The sheet 'Table 3' does not exist.");
            }

            // SQL query to insert data into the database
            $sql = "INSERT INTO excel_datas (week1, week2, week3, week4, total) VALUES (:week, :total)";
            $stmt = $pdo->prepare($sql);

            // Initialize variables for grouping
            $groupCounter = 0;
            $rowCounter = 0;
            $currentGroupSum = 0;

            // Process rows in the Excel file
            foreach ($worksheet->getRowIterator($startRowLimit, $endRowLimit) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $data = [];
                foreach ($cellIterator as $cell) {
                    $data[] = $cell->getValue();
                }

                // Extract relevant columns
                $column1 = $data[0] ?? null;       // Week or relevant identifier
                $total = $data[7] ?? null;         // Assuming "Total" is in column 8 (index 7)
                $remarks = $data[11] ?? null;      // Assuming "Remarks" is in column 12 (index 11)

                // Handle "Remarks" column to adjust values
                if (!empty($remarks)) {
                    if (strtolower(trim($remarks)) === 'sunday') {
                        $value = '0:00'; // Set to '0:00' if it's Sunday
                    } else {
                        $value = '8:00'; // Default value for other remarks
                    }
                } else {
                    $value = $total ?? '0:00'; // Use total or default to '0:00'
                }

                // Convert time format (e.g., "8:00") to decimal hours
                if (strpos($value, ':') !== false) {
                    list($hours, $minutes) = explode(':', $value);
                    $value = $hours + ($minutes / 60);
                }

                $value = is_numeric($value) ? $value : 0;

                // Add to current group's sum
                $currentGroupSum += (float) $value;
                $rowCounter++;

                // Preview data
                $previewData[] = [
                    'column1' => $column1, //day of the month
                    'total' => $total,    //Total column sa dtr
                    'remarks' => $remarks, //remarks [sunday,travel...]
                ];

                // If the group is complete, store the sum and reset
                if ($rowCounter % 7 == 0) {
                    $groupSums[$groupCounter] = $currentGroupSum;

                    // Insert into the database
                    $stmt->execute([
                        ':week' => 'Week ' . ($groupCounter + 1),  //numbers of week
                        ':total' => $currentGroupSum,
                    ]);

                    $currentGroupSum = 0;
                    $groupCounter++;
                }
            }

            // Add the last group if rows are left
            if ($rowCounter % 7 != 0) {
                $groupSums[$groupCounter] = $currentGroupSum;

                $stmt->execute([
                    ':week' => 'Week ' . ($groupCounter + 1),
                    ':total' => $currentGroupSum,
                ]);
            }

            echo "File processed successfully!";
        } catch (Exception $e) {
            echo "Error processing file: " . $e->getMessage();
        }
    } else {
        echo "File upload failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="//cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    <title>Upload and Process Excel File</title>
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
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Upload Excel File</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="excelFile" accept=".xls,.xlsx" required>
        <button type="submit">Upload and Process</button>
    </form>

    <!-- Preview Extracted Data -->
    <?php if (!empty($previewData)): ?>
        <h2>Preview of Extracted Data</h2>
        <table id="previewTable">
            <thead>
                <tr>
                    <th>Column 1</th>
                    <th>Total</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($previewData as $data): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($data['column1']); ?></td>
                        <td><?php echo htmlspecialchars($data['total']); ?></td>
                        <td><?php echo htmlspecialchars($data['remarks']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Display Grouped Sums -->
    <?php if (!empty($groupSums)): ?>
    <h2>Sum by Week</h2>
    <table id="groupedSumsTable">
        <thead>
            <tr>
                <th>Week 1</th>
                <th>Week 2</th>
                <th>Week 3</th>
                <th>Week 4</th>
                <th>Week 5</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php foreach ($groupSums as $sum): ?>
                    <td><?php echo number_format($sum, 2); ?></td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
<?php endif; ?>


    <!-- Include DataTables and jQuery Libraries -->
    <script src="//cdn.datatables.net/2.1.8/js/jquery.dataTables.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Initialize DataTables for both tables
        $(document).ready(function() {
            $('#previewTable').DataTable();
            $('#groupedSumsTable').DataTable();
        });
    </script>
</body>
</html>

