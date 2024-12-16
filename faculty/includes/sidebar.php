<body>
        <div class="sidebar">
          <div class="logo"><img src="./assets/images/logoall-light.png" alt=""></div>
          <ul class="menu">
            <li><a href="f-dashboard.php"><i class="bx bxs-dashboard"></i><span>dashboard</span></a></li>
            <li><a href="f-user.php"><i class="bx bxs-group"></i><span>user Management</span></a></li>
            <li><a href="f-itl.php"><i class='bx bxs-doughnut-chart'></i><span>Faculty ITL</span></a></li>
            <li><a href="f-dtr.php"><i class='bx bxs-time'></i><span>Faculty DTR</span></a></li>
            <li><a href="f-overload.php"><i class="bx bxs-user-check"></i><span>Overload Overview</span></a></li>
            <li><a href="f-reports.php"><i class='bx bxs-book-alt'></i><span>reports generation</span></a></li>
            <!-- <li><a href="calendar.php"><i class='bx bxs-calendar'></i><span>calendar</span></a></li> -->
            <?php if(isset($user_roles) && count($user_roles) > 1) { ?>
            <li class="switch"> <a href="../loginas.php"><i class='bx bx-code'></i><span>switch</span></a></li>
            <?php } ?>
          </ul>
        </div>