<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>
              <div class="tabular--wrapper">
                <div class="add">
                  <div class="filter">
                    <select>
                      <option value="" disabled selected>Select Role</option>
                      <option value="option1">Staff</option>
                      <option value="option2">Faculty</option>
                      <option value="option3">HR</option>
                    </select>
                  </div>
                  <button class="btn-add" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class='bx bxs-file-import'></i>
                    <span class="text">Import User</span>
                  </button>
                  <a href="add-user.php" class="btn-add">
                    <i class='bx bxs-user-plus'></i>
                    <span class="text">Add User</span>
                  </a>
                  
                </div>
                  <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="importModalLabel">Import User Data</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <form action="../extract/import-users.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                              <label for="file" class="form-label">Upload Excel File</label>
                              <input type="file" class="form-control" id="file" name="file" accept=".xlsx" required>
                            </div>
                            <div class="text-end">
                              <button type="submit" class="btn btn-primary">Import Users</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                          
             
                <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                      include '../config/config.php';

                      $limit = 10;

                      // Get total number of employees to calculate pagination
                      $totalResult = $con->query("SELECT COUNT(*) AS total FROM employee");
                      if (!$totalResult) {
                          die("Error in query: " . $con->error); // Catch and display error if the query fails
                      }

                      $totalRows = $totalResult->fetch_assoc()['total'];
                      $totalPages = ceil($totalRows / $limit);

                      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                      $page = max($page, 1);

                      $offset = ($page - 1) * $limit;

                      // Query to fetch employee data and their roles
                      $query = "SELECT e.userId, e.employeeId, e.firstName, e.middleName, e.lastName, e.emailAddress, 
                                e.phoneNumber, e.status, GROUP_CONCAT(r.role_Name ORDER BY r.role_Name ASC) AS roles
                                FROM employee e
                                JOIN roles_employee re ON e.userId = re.userId
                                JOIN roles r ON re.roleId = r.roleId
                                GROUP BY e.employeeId, e.firstName, e.middleName, e.lastName
                                LIMIT $limit OFFSET $offset"; // Add LIMIT and OFFSET for pagination

                      $result = $con->query($query);
                      if (!$result) {
                          die("Error in query: " . $con->error); // Catch and display error if the query fails
                      }

                      if ($result->num_rows > 0) {
                          // Loop through the results and display them
                          while ($row = $result->fetch_assoc()) {
                              $fullName = trim($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']);
                              echo '<tr data-role="' . $row['userId'] . '">
                                      <td>' . $row['employeeId'] . '</td>
                                      <td>' . $fullName . '</td>
                                      <td>' . $row['emailAddress'] . '</td>
                                      <td>' . $row['phoneNumber'] . '</td>
                                      <td><span class="status">' . $row['roles'] . '</span></td>
                                     <td><span class="status">' . $row['status'] . '</span></td>
                                      <td><a href="edit-act.php?employee_id=' . $row['userId'] . '" class="action">Edit</a>
                                          <a href="#1" class="action">Archive</a></td>
                                    </tr>';
                          }
                      } else {
                          echo '<tr><td colspan="7">No users found.</td></tr>';
                      }

                      $con->close();
                      ?>

                    </tbody>
                </table>

               <!-- Pagination Links -->
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

            
<?php
include('./includes/footer.php');
?>