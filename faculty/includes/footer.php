</body>

<!-- External JS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="./assets/acad.js"></script>

<script>
  // Display Notification (PHP Session)
  <?php if (isset($_SESSION['status']) && $_SESSION['status_code'] != '') { ?>
  document.addEventListener("DOMContentLoaded", () => {
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
      }
    });

    Toast.fire({
      icon: "<?php echo $_SESSION['status_code']; ?>",
      title: "<?php echo $_SESSION['status']; ?>"
    });
  });
  <?php
    unset($_SESSION['status']);
    unset($_SESSION['status_code']);
  }
  ?>
</script>
<script>
  // Profile Dropdown Toggle
  document.addEventListener("DOMContentLoaded", () => {
    const profileDropdownBtn = document.querySelector(".profile-dropdown-btn");
    const profileDropdownList = document.querySelector(".profile-dropdown-list");

    profileDropdownBtn.addEventListener("click", () => {
      profileDropdownList.classList.toggle("active");
    });

    window.addEventListener("click", (e) => {
      if (!profileDropdownBtn.contains(e.target)) {
        profileDropdownList.classList.remove("active");
      }
    });
  });

  // Modal Window
  document.addEventListener("DOMContentLoaded", () => {
    const importButton = document.getElementById("importButton");
    const importModal = document.getElementById("importModal");
    const closeButton = importModal.querySelector(".close");

    importButton.addEventListener("click", () => {
      importModal.style.display = "block";
    });

    closeButton.addEventListener("click", () => {
      importModal.style.display = "none";
    });

    window.addEventListener("click", (event) => {
      if (event.target === importModal) {
        importModal.style.display = "none";
      }
    });
  });

  // Image Preview
  function previewImage(event) {
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = (e) => {
      const imagePreview = document.getElementById("imagePreview");
      imagePreview.innerHTML = `<img src="${e.target.result}" alt="Image Preview" style="max-width: 200px; max-height: 200px; border-radius: 25px;">`;
    };

    if (file) {
      reader.readAsDataURL(file);
    } else {
      document.getElementById("imagePreview").innerHTML = "";
    }
  }

  // Disable Submit Button on Form Submission
  document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("importForm").addEventListener("submit", function () {
      const submitButton = document.getElementById("submitBtn");
      submitButton.disabled = true;
      submitButton.innerHTML = "Submitting...";
    });
  });

  // Tab Navigation
  function openTab(evt, tabName) {
    const tabcontent = document.getElementsByClassName("tabcontent");
    const tablinks = document.getElementsByClassName("tablinks");

    Array.from(tabcontent).forEach((content) => (content.style.display = "none"));
    Array.from(tablinks).forEach((link) => link.classList.remove("active"));

    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");
  }

  // Display Current Date (Philippine Time Zone)
  document.addEventListener("DOMContentLoaded", () => {
    const dateElement = document.getElementById("date");
    const dayElement = document.getElementById("day");
    const monthElement = document.getElementById("month");
    const yearElement = document.getElementById("year");

    const now = new Date().toLocaleString("en-US", { timeZone: "Asia/Manila" });
    const currentDate = new Date(now);

    const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    dateElement.textContent = currentDate.getDate();
    dayElement.textContent = days[currentDate.getDay()];
    monthElement.textContent = months[currentDate.getMonth()];
    yearElement.textContent = currentDate.getFullYear();
  });

  // Role Toggle
  function toggleRole(button, role) {
    const roleClass = role + "-selected";
    button.classList.toggle(roleClass);
  }

  // Edit User Modal
  $('#editUserModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var userId = button.data('userId');
    var firstName = button.data('firstname');
    var middleName = button.data('middlename');
    var lastName = button.data('lastname');
    var email = button.data('email');
    var phone = button.data('phone');
    var roles = button.data('roles');
    var status = button.data('status');

    // Update the modal's content with the data
    var modal = $(this);
    modal.find('.modal-body #userId').val(userId);
    modal.find('.modal-body #firstName').val(firstName);
    modal.find('.modal-body #middleName').val(middleName);
    modal.find('.modal-body #lastName').val(lastName);
    modal.find('.modal-body #email').val(email);
    modal.find('.modal-body #phone').val(phone);
    modal.find('.modal-body #roles').val(roles);
    modal.find('.modal-body #status').val(status);
  });

  // Archive Confirmation Modal
  $('#archiveConfirmModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Archive button that triggered the modal
    var userId = button.data('userId'); // Get userId from the data attribute

    var modal = $(this);
    modal.find('#archiveBtn').data('userId', userId); // Pass the userId to the Confirm button
  });

  // Archive User AJAX Request
  $('#archiveBtn').on('click', function () {
    var userId = $(this).data('userId'); // Retrieve userId

    // Perform an AJAX request to archive the user
    $.ajax({
      url: './controller/archive-user.php', // Adjust path if necessary
      method: 'GET',
      data: { userId: userId }, // Send the userId to the backend
      success: function (response) {
        // Handle the success case - show message or redirect
        alert('User archived successfully');
        location.reload(); // Reload the page to reflect changes
      },
      error: function () {
        // Handle the error case
        alert('Error archiving user');
      }
    });

    // Close the modal after submitting
    $('#archiveConfirmModal').modal('hide');
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
      const modalBtns = document.querySelectorAll(".openModalBtn");
      const modal = document.getElementById("fillUpModal");
      const fullNameInput = document.getElementById("fullName");
      const idInput = document.getElementById("id");

      modalBtns.forEach((btn) => {
          btn.addEventListener("click", function () {
              const employeeId = this.getAttribute("data-id");
              const fullName = this.getAttribute("data-name");

              // Set the modal fields
              idInput.value = employeeId;
              fullNameInput.value = fullName;

              // Show the modal
              modal.style.display = "block";
          });
      });

      // Close button logic
      const closeBtn = document.querySelector(".close-btn");
      closeBtn.addEventListener("click", function () {
          modal.style.display = "none";
      });
  });

</script>

</html>
