<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>
              <div class="tabular--wrapper">
                <div class="add">
                  <div class="filter">
                    <select id="role">
                        <option value="" disabled selected>For the Month of</option>
                        <option value="option1">January</option>
                        <option value="option2">February</option>
                        <option value="option3">March</option>
                        <option value="option4">April</option>
                        <option value="option5">May</option>
                        <option value="option6">June</option>
                        <option value="option7">July</option>
                        <option value="option8">August</option>
                        <option value="option9">September</option>
                        <option value="option10">October</option>
                        <option value="option11">November</option>
                        <option value="option12">December </option>
                    </select>
                  </div>
                  <div class="filter">
                        <select id="role">
                            <option value="" disabled selected>Select Academic Year</option>
                            <option value="option1">2024-2025</option>
                            <option value="option2">2025-2026</option>
                            <option value="option3">2026-2027</option>
                        </select>
                  </div>
                  <div class="filter">
                        <select id="role">
                            <option value="" disabled selected>Select Academic Semester</option>
                            <option value="option1">1st Semester</option>
                            <option value="option2">2nd Semester</option>
                        </select>
                  </div>
                  
                  
                </div>

               <div class="table-container">
                  <table>
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Faculty</th>
                        <th>Monthly Overload Pay</th>
                        <th>Monthly Service Credit</th>
                        <th>Action</th>
                      </tr> 
                      <tbody>
                      <?php
                                include '../config/config.php';
                                $limit = 10;

                                // Get the total number of rows
                                $totalResult = $con->query("SELECT COUNT(*) AS total FROM employee");
                                $totalRows = $totalResult->fetch_assoc()['total'];
                                $totalPages = ceil($totalRows / $limit);

                                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $page = max($page, 1);

                                $offset = ($page - 1) * $limit;

                                // Query to fetch data with pagination
                                $sql = "SELECT 
                                            e.userId, e.employeeId, e.firstName, e.middleName, e.lastName, 
                                            fd.dtr_file, fl.total_overload, fd.dtr_filename,  fo.monthly_overloadpay, fo.monthly_servicecredit,
                                            r.role_Name, fd.week_1, fd.week_2, fd.week_3, fd.week_4, fd.week_5
                                        FROM 
                                            employee e 
                                        LEFT JOIN 
                                            faculty_overload fo ON e.userId = fo.userId 
                                        JOIN
                                            faculty_dtr fd ON e.userId = fd.userId 
                                        JOIN 
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
                                        $fullName = $row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName'];
                                        $regularHrs = 40;

                                        // Initialize variables
                                        $allowableUnits = '--';
                                        $totalOverload = '--';
                                        $totalhrsperwk = '--';
                                        $designationitl = '--';
                                        $weeklyOverloadPay = '--';

                                        // Check if DTR file exists
                                        if (!empty($row['dtr_file'])) {
                                            // Calculate overtime for all 5 weeks
                                            $overtimeEarned = [
                                                'week_1' => isset($row['week_1']) ? $row['week_1'] - $regularHrs:  0,
                                                'week_2' => isset($row['week_2']) ? $row['week_2'] - $regularHrs : 0,
                                                'week_3' => isset($row['week_3']) ? $row['week_3'] - $regularHrs : 0,
                                                'week_4' => isset($row['week_4']) ? $row['week_4'] - $regularHrs : 0,
                                                'week_5' => isset($row['week_5']) ? $row['week_5'] - $regularHrs : 0,
                                            ];

                                            // Calculate weekly overload for all 5 weeks
                                            $weeklyOverload = [
                                                'week_1' => isset($row['total_overload']) ? $overtimeEarned['week_1'] - $row['total_overload'] : 0,
                                                'week_2' => isset($row['total_overload']) ? $overtimeEarned['week_2'] - $row['total_overload'] : 0,
                                                'week_3' => isset($row['total_overload']) ? $overtimeEarned['week_3'] - $row['total_overload'] : 0,
                                                'week_4' => isset($row['total_overload']) ? $overtimeEarned['week_4'] - $row['total_overload'] : 0,
                                                'week_5' => isset($row['total_overload']) ? $overtimeEarned['week_5'] - $row['total_overload'] : 0,
                                            ];

                                            // Calculate the total monthly overload pay
                                            $weeklyOverloadPay = [
                                                'week_1' => (isset($overtimeEarned['week_1']) && isset($weeklyOverload['week_1'])) 
                                                            ? ($overtimeEarned['week_1'] < 0 ? 0 : $overtimeEarned['week_1'] - $weeklyOverload['week_1'])
                                                            :  0,
                                                'week_2' => (isset($overtimeEarned['week_2']) && isset($weeklyOverload['week_2'])) 
                                                            ? ($overtimeEarned['week_2'] < 0 ? 0 : $overtimeEarned['week_2'] - $weeklyOverload['week_2'])
                                                            :  0,
                                                'week_3' => (isset($overtimeEarned['week_3']) && isset($weeklyOverload['week_3'])) 
                                                            ? ($overtimeEarned['week_3'] < 0 ? 0 : $overtimeEarned['week_3'] - $weeklyOverload['week_3'])
                                                            :  0,
                                                'week_4' => (isset($overtimeEarned['week_4']) && isset($weeklyOverload['week_4'])) 
                                                            ? ($overtimeEarned['week_4'] < 0 ? 0 : $overtimeEarned['week_4'] - $weeklyOverload['week_4'])
                                                            :  0,
                                                'week_5' => (isset($overtimeEarned['week_5']) && isset($weeklyOverload['week_5'])) 
                                                            ? ($overtimeEarned['week_5'] < 0 ? 0 : $overtimeEarned['week_5'] - $weeklyOverload['week_5'])
                                                            :  0,
                                            ];

                                            $totalMonthlyOverloadPay = array_sum($monthlyOverloadPay);
                                        }

                                        // Output each row
                                        echo '<tr>
                                                <td>' . htmlspecialchars($row['employeeId']) . '</td>
                                                <td>' . htmlspecialchars($fullName) . '</td>';

                                        // Display weekly overload pay or "--" if no file
                                        echo '<td>' . (!empty($row['dtr_file']) ? number_format($weeklyOverloadPay['week_1'], 2) : '--') . '</td>
                                            <td>' . (!empty($row['dtr_file']) ? number_format($weeklyOverloadPay['week_2'], 2) : '--') . '</td>
                                            <td>' . (!empty($row['dtr_file']) ? number_format($weeklyOverloadPay['week_3'], 2) : '--') . '</td>
                                            <td>' . (!empty($row['dtr_file']) ? number_format($weeklyOverloadPay['week_4'], 2) : '--') . '</td>
                                            <td>' . (!empty($row['dtr_file']) ? number_format($weeklyOverloadPay['week_5'], 2) : '--') . '</td>';

                                        // Display DTR file and action buttons
                                        echo '<td>';
                                        if (!empty($row['dtr_file'])) {
                                            $filename = htmlspecialchars($row['dtr_filename']);
                                            echo '<a href="../controller/view-file.php?employeeId=' . htmlspecialchars($row['userId']) . '" style="color: blue; text-decoration: underline;" target="_blank">' . $filename . '</a>';
                                        } else {
                                            echo 'No file available';
                                        }
                                        echo '</td>';
                                        echo '<td>
                                                <button class="openModalBtn pointer-btn" data-id="' . htmlspecialchars($row['employeeId']) . '" data-name="' . htmlspecialchars($fullName) . '">
                                                    <img src="./assets/images/edit-icon.png" alt="Edit" style="width: 20px;">
                                                </button>
                                                <a href="../controller/delete_file.php?employeeId=' . htmlspecialchars($row['userId']) . '" onclick="return confirm(\'Are you sure you want to delete this file?\')">
                                                    <img src="./assets/images/delete-icon.png" alt="Delete" style="width: 20px;">
                                                </a>
                                            </td>
                                            </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="13">No faculty members found.</td></tr>';
                                }

                                $con->close();
                            ?>
                    </tbody>
                    </thead>
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

               <!-- Modal -->
              <div id="fillUpModal" class="modal" style="display: none;">
                  <div class="modal-content">
                      <span class="close-btn">&times;</span>
                      <h2>Upload ITL file</h2>
                      <form id="fillUpForm" action="process_form.php" method="POST" enctype="multipart/form-data">
                          <label for="fullName">Name</label>
                          <input type="text" id="fullName" name="fullName" readonly>

                          <label for="id">ID</label>
                          <input type="text" id="id" name="id" readonly>

                          <label for="academicYear">Academic Year:</label>
                          <select id="academicYear" name="academicYear" required>
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
                          <select id="academicSemester" name="academicSemester" required>
                              <option value="">Select Semester</option>
                              <option value="First Semester">First Semester</option>
                              <option value="Second Semester">Second Semester</option>
                              <option value="Summer Semester">Summer Semester</option>
                          </select>

                          <label for="month">For the Month of:</label>
                          <select id="month" name="month" required>
                            <option value="">Select Month</option>
                              <?php
                                $months = [
                                  "January", "February", "March", "April", "May", 
                                  "June", "July", "August", "September", "October", 
                                  "November", "December"
                                ];
                                foreach ($months as $month) {
                                  echo "<option value='{$month}'>{$month}</option>";
                                }
                            ?>
                          </select>


                          <label for="uploadFile">Upload File:</label>
                          <input type="file" id="uploadFile" name="uploadFile" accept=".pdf,.docx,.txt" required>

                          <button type="submit">Submit</button>
                      </form>
                  </div>
              </div>

                </div>
              </div>
            </div>
            
    </body>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('fillUpModal');
        const openButton = document.getElementById('openModalBtn');
        const closeButton = document.querySelector('.close-btn');

        // Open modal
        openButton.addEventListener('click', function () {
            modal.style.display = 'block';
        });

        // Close modal only when the close button is clicked
        closeButton.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    });


    </script>

    <script>
        
      let profileDropdownList = document.querySelector(".profile-dropdown-list");
      let btn = document.querySelector(".profile-dropdown-btn");

      let classList = profileDropdownList.classList;

      const toggle = () => classList.toggle("active");

      window.addEventListener("click", function (e) {
      if (!btn.contains(e.target)) classList.remove("active");
      });
    </script>

</html>    
    

 