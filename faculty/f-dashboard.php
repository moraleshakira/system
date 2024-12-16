<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

            <div class="card--container">
              <h3 class="main--title"> Accounts </h3>

              <ul class="box-info">
                <li>
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <h3></h3>
                        <p>Staff</p>
                        <?php

                                    $staff = "SELECT
                                        employee.*, 
                                        roles_employee.roleId
                                      FROM
                                        employee
                                        INNER JOIN
                                       roles_employee
                                        ON 
                                          employee.userId = roles_employee.userId
                                      WHERE
                                        roles_employee.roleId = 2";
                                    $staff_run = mysqli_query($con, $staff);


                                    if($staff_total = mysqli_num_rows($staff_run))
                                    {
                                    echo '<h6 class="mb-0"> '.$staff_total.' </h6>';
                                    }else
                                    {
                                    echo '<h6 class="mb-0">0</h6>';
                                    }


                         ?>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <h3></h3>
                        <p>Faculty</p>

                        <?php

                          $faculty = "SELECT
                                employee.*, 
                                roles_employee.roleId
                                FROM
                                employee
                                INNER JOIN
                                roles_employee
                                ON 
                                    employee.userId = roles_employee.userId
                                WHERE
                                roles_employee.roleId = 3";
                          $faculty_run = mysqli_query($con, $faculty);


                          if($faculty_total = mysqli_num_rows($faculty_run))
                          {
                          echo '<h6 class="mb-0"> '.$faculty_total.' </h6>';
                          }else
                          {
                          echo '<h6 class="mb-0">0</h6>';
                          }


                          ?>

                    </span>
                </li>
                <li>
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <h3></h3>
                        <p>HR</p>

                        <?php

                          $hr = "SELECT
                                employee.*, 
                                roles_employee.roleId
                                FROM
                                employee
                                INNER JOIN
                                roles_employee
                                ON 
                                    employee.userId = roles_employee.userId
                                WHERE
                                roles_employee.roleId = 4";
                          $hr_run = mysqli_query($con, $hr);


                          if($hr_total = mysqli_num_rows($hr_run))
                          {
                          echo '<h6 class="mb-0"> '.$hr_total.' </h6>';
                          }else
                          {
                          echo '<h6 class="mb-0">0</h6>';
                          }


                        ?>
                    </span>
                </li>
            </ul>

            </div>
<div class="table-data">
    <div class="order">
        <div class="hero">
            <div class="calendar">
                <div class="left-calendar">
                    <p id="date"></p>
                    <p id="day"></p>
                </div>
                <div class="right-calendar">
                    <p id="month"></p>
                    <p id="year"></p>
                </div>
            </div>
            <div class="academic-info">
                <h1>Academic Information</h1>
                <div class="semester-details">
                    <p><strong>Current Semester:</strong> <span id="currentSemester">Fall 2024</span></p>
                    <p><strong>School Year:</strong> <span id="schoolYear">2024-2025</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="todo">
        <div class="head">
            <h3>Todos</h3>
            <i class='bx bx-plus'></i>
            <i class='bx bx-filter'></i>
        </div>
        <ul class="todo-list">
            <li class="completed">
                <p>Todo List</p>
                <i class='bx bx-dots-vertical-rounded'></i>
            </li>
            <li class="completed">
                <p>Todo List</p>
                <i class='bx bx-dots-vertical-rounded'></i>
            </li>
            <li class="not-completed">
                <p>Todo List</p>
                <i class='bx bx-dots-vertical-rounded'></i>
            </li>
            <li class="completed">
                <p>Todo List</p>
                <i class='bx bx-dots-vertical-rounded'></i>
            </li>
            <li class="not-completed">
                <p>Todo List</p>
                <i class='bx bx-dots-vertical-rounded'></i>
            </li>
        </ul>
    </div>
</div>

        </div>
        


        
<?php
include('./includes/footer.php');
?>
    

 