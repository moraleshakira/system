<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

        <div class="tabular--wrapper">
            <div class="add">
                <div class="filter">
                   
                    <form method="GET" action="">
                        <select id="academic-year" name="year_filter" onchange="this.form.submit()" style="width: 200px; margin-right: 10px;">
                            <option value="" disabled selected>Select Academic Year</option>
                            <option value="2024-2025" <?php if (isset($_GET['year_filter']) && $_GET['year_filter'] == '2024-2025') echo 'selected'; ?>>2024-2025</option>
                            <option value="2025-2026" <?php if (isset($_GET['year_filter']) && $_GET['year_filter'] == '2025-2026') echo 'selected'; ?>>2025-2026</option>
                            <option value="2026-2027" <?php if (isset($_GET['year_filter']) && $_GET['year_filter'] == '2026-2027') echo 'selected'; ?>>2026-2027</option>
                        </select>

                    </form>
                    <select  id="role">
                        <option value="" disabled selected>Select Designation</option>
                        <option value="option1">Designated</option>
                        <option value="option2">Non-Designated</option>
                    </select>
                </div>
                <div class="filter">
                   
                </div>
                <div class="filter">
                    <select id="academic-semester" name="academicSemester">
                        <option value="" disabled selected>Select Academic Semester</option>
                        <option value="1st">1st Semester</option>
                        <option value="2nd">2nd Semester</option>
                    </select>
                </div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Designation</th>
                            <th>ITL Overload</th>
                            <th>ITL File</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            include '../config/config.php';

                            $limit = 10;

                            // Get the total number of rows in the employee table
                            $totalResult = $con->query("SELECT COUNT(*) AS total FROM employee");
                            $totalRows = $totalResult->fetch_assoc()['total'];
                            $totalPages = ceil($totalRows / $limit);

                            // Get the current page number
                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $page = max($page, 1);

                            // Redirect to the last page if the current page exceeds the total pages
                            if ($page > $totalPages) {
                                header("Location: ?page=" . $totalPages);
                                exit;
                            }

                            // pagination
                            $offset = ($page - 1) * $limit;

                            // SQL query 
                            $sql = "
                                SELECT 
                                    e.userId, e.employeeId, e.firstName, e.middleName, e.lastName, 
                                    fl.file, fl.filename, fl.designation_itl, fl.allowable_units, 
                                    fl.faculty_credit, fl.designation_load_released, fl.total_overload,
                                    r.role_Name 
                                FROM 
                                    employee e 
                                LEFT JOIN 
                                    faculty_load fl ON e.userId = fl.userId 
                                JOIN 
                                    roles_employee re ON e.userId = re.userId
                                JOIN 
                                    roles r ON re.roleId = r.roleId
                                WHERE 
                                    r.role_Name = 'Faculty' 
                                LIMIT $limit OFFSET $offset";

                            $result = $con->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $fullName = trim($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']);

                                    // display sa table ui
                                    $allowableUnits = '--';
                                    $totalOverload = '--';
                                    $totalhrsperwk = '--';
                                    $designationitl = '--';

                                    // Check if the file is uploaded/imported
                                    if (!empty($row['file'])) {
                                        $designationLoadReleased = isset($row['designation_load_released']) ? floatval($row['designation_load_released']) : 0.00;
                                        $facultyCredit = isset($row['faculty_credit']) ? floatval($row['faculty_credit']) : 0.00;
                                        // $totalhrsperwk = 40.0 + (isset($row['total_overload']) ? floatval($row['total_overload']) : 0.00);
                                        
                                        $designationitl = isset($row['designation_itl']) ? floatval($row['designation_itl']) : 0.00;
                                        // if non-designated si faculty 
                                        if ($designationLoadReleased == 0.0) {
                                            $allowableUnits = 0.0;
                                            $totalOverload = $facultyCredit - 18.00;
                                        } else { 
                                            //if designated is faculty
                                            $allowableUnits = 18.00 - $designationLoadReleased;
                                            $totalOverload = $facultyCredit - $allowableUnits;
                                        }

                                    
                                        // Update the database 
                                        // Update the database 
                                        $updateQuery = "UPDATE faculty_load SET allowable_units = ?, total_overload = ?, total_hrsper_week = ? WHERE userId = ?";
                                        $stmt = $con->prepare($updateQuery);
                                        $stmt->bind_param("ddds", $allowableUnits, $totalOverload, $totalhrsperwk, $row['userId']);

                                        if (!$stmt->execute()) {
                                            echo "Error updating record for employeeId " . $row['userId'] . ": " . $stmt->error;
                                        }

                                    }

                                    echo '<tr>
                                            <td>' . htmlspecialchars($row['employeeId']) . '</td>
                                            <td>' . htmlspecialchars($fullName) . '</td>
                                            <td>' . htmlspecialchars($row['designation_itl']) . '</td>
                                           <td>' . htmlspecialchars(($designationLoadRelease[$row['designation_load_released']] == 0.0) ? 'Non-designated' : 'Designated') . '</td>
                                            <td></td>
                                            <td>' . (is_numeric($totalOverload) ? number_format($totalOverload, 2) : $totalOverload) . '</td>
                                        
                                            <td>';

                                    // uploaded/imported file display
                                    if (!empty($row['file'])) {
                                        $filename = htmlspecialchars($row['filename']);
                                        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                                        $fileMimeType = finfo_buffer($fileInfo, $row['file']);
                                        finfo_close($fileInfo);

                                        if (strpos($fileMimeType, 'image/') === 0) {
                                            echo '<img src="data:' . $fileMimeType . ';base64,' . base64_encode($row['file']) . '" alt="ITL File" style="max-width: 100px; height: auto;">';
                                        } else {
                                            echo '<a href="../controller/view-file.php?employeeId=' . $row['userId'] . '" style="color: blue; text-decoration: underline;" target="_blank">' . $filename . '</a>';
                                        }
                                    } else {
                                        echo 'No file available';
                                    }

                                    echo '</td>
                                            <td>
                                                <button class="openModalBtn pointer-btn" data-id="' . $row['employeeId'] . '" data-name="' . htmlspecialchars($fullName) . '">
                                                    <img src="./assets/images/edit-icon.png" alt="Edit" style="width: 20px;">
                                                </button>

                                                <a href="../controller/delete_file.php?employeeId=' . $row['userId'] . '" onclick="return confirm(\'Are you sure you want to delete this file?\')">
                                                    <img src="./assets//images/delete-icon.png" alt="Delete" style="width: 20px;">
                                                </a>
                                            </td>
                                        </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="7">No faculty members found.</td></tr>';
                            }

                            $con->close();
                            ?>

                        </tbody>

                        </table>
                        <div class="pagination" id="pagination">
                            <?php
                                if ($totalPages > 1) {
                                    // First and Previous buttons
                                    echo '<a href="?page=1" class="pagination-button">&laquo;</a>';
                                    $prevPage = max(1, $page - 1);
                                    echo '<a href="?page=' . $prevPage . '" class="pagination-button">&lsaquo;</a>';

                                    // Numbered page links
                                    for ($i = 1; $i <= $totalPages; $i++) {
                                        $activeClass = ($i == $page) ? 'active' : '';
                                        echo '<a href="?page=' . $i . '" class="pagination-button ' . $activeClass . '">' . $i . '</a>';
                                    }

                                    // Next and Last buttons
                                    $nextPage = min($totalPages, $page + 1);
                                    echo '<a href="?page=' . $nextPage . '" class="pagination-button">&rsaquo;</a>';
                                    echo '<a href="?page=' . $totalPages . '" class="pagination-button">&raquo;</a>';
                                }
                            ?>
                        </div>

               </div>
            </div>
            <!-- Modal -->
            <div id="fillUpModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close-btn">&times;</span>
                    <h2>Upload ITL file</h2>
                    <form id="fillUpForm" action="../extract/itl_extract.php" method="POST" enctype="multipart/form-data">
                        <label for="fullName">Name</label>
                        <input type="text" id="fullName" name="fullName" readonly>

                        <label for="id">ID</label>
                        <input type="text" id="id" name="id" readonly>

                        <label for="academicYear">Academic Year:</label>
                        <select id="academicYear" name="academicYear" >
                            <option value="">Select Academic Year</option>
                            <?php
                            $currentYear = date("Y");
                            for ($i = 0; $i < 5; $i++) { 
                                $startYear = $currentYear - $i;
                                $endYear = $startYear + 1;
                                echo "<option value='{$startYear}-{$endYear}'>{$startYear}-{$endYear}</option>";
                            }
                            ?>
                        </select>

                        <label for="academicSemester">Academic Semester:</label>
                        <select id="academicSemester" name="academicSemester" >
                            <option value="">Select Semester</option>
                            <option value="1st Semester">First Semester</option>
                            <option value="2nd Semester">Second Semester</option>
                            <option value="Summer Semester">Summer Semester</option>
                        </select>

                        <label for="uploadFile">Upload File:</label>
                        <input type="file" id="uploadFile" name="uploadFile" accept=".xlsx,.xls" required>

                        <button type="submit">Submit</button>
                    </form>
                </div>
            </div>


        </div>
    </div>
    
         
<?php
include('./includes/footer.php');
?>