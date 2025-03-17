<!-- /**
 * Plugin Name: Hospital Appointments
 * Description: A custom WordPress plugin for managing hospital appointments.
 * Version: 1.0
 * Author: Your Name
 * Developer: Manu Marshal
 * Developer Url: https://manumarshal.com/
 */ -->
 <div class="wrap">
  <h1 class="wp-heading-inline">Manage Doctors</h1>
  <button type="button" class="page-title-action button-primary" data-bs-toggle="modal" data-bs-target="#doctorModal">
  Add Doctor
  </button>
  <hr class="wp-header-end">
  <br/>
  <!-- Bootstrap Modal -->
  <div class="modal fade" id="doctorModal" tabindex="-1" aria-labelledby="doctorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="doctorModalLabel">Add Doctor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="doctorForm">
            <input type="hidden" id="doctor_id" name="doctor_id">
            <div class="mb-3">
              <label class="form-label">Name:</label>
              <input type="text" placeholder="Doctor Name" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Specialty:</label>
              <input type="text" placeholder="Doctor Specialty..." class="form-control" id="specialty" name="specialty" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Available Days:</label><br>
              <div class="btn-group-toggle" data-toggle="buttons">
                <?php
                  $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                  foreach ($days as $day) {
                      echo "<label class='btn btn-outline-primary day-btn' for='day_$day'>
                              <input type='checkbox' name='available_days[]' value='$day' id='day_$day' class='day-checkbox' autocomplete='off'> $day
                          </label>";
                  }
                  ?>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Available Time Slots:</label><br>
              <div class="btn-group-toggle" data-toggle="buttons">
                <?php
                  $time_slots = [
                      "06:00am to 10:00am",
                      "10:00am to 12:00pm",
                      "12:00pm to 02:00pm",
                      "02:00pm to 06:00pm",
                      "06:00pm to 09:00pm",
                      "09:00pm to 12:00am",
                      "12:00am to 03:00am",
                      "03:00am to 06:00am"
                  ];
                  
                  foreach ($time_slots as $slot) {
                      echo "<label class='btn btn-outline-primary time-slot-btn' for='slot_$slot'>
                              <input type='checkbox' name='time_slots[]' value='$slot' id='slot_$slot' class='time-slot-checkbox' autocomplete='off'> $slot
                          </label>";
                  }
                  ?>
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Doctor</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Success Message -->
  <div id="successMessage" class="alert alert-success" style="display:none;"></div>
  <!-- Bootstrap DataTable -->
  <table id="doctorTable" class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Name</th>
        <th>Specialty</th>
        <th>Available Days</th>
        <th>Time Slots</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'ha_doctors';
        $doctors = $wpdb->get_results("SELECT * FROM $table_name");
        
        foreach ($doctors as $doctor) {
            $available_days = !empty($doctor->available_days) ? str_replace(',', ', ', $doctor->available_days) : 'N/A';
            $time_slots = !empty($doctor->time_slots) ? explode(',', $doctor->time_slots) : [];
        
            echo "<tr>
                    <td><b>{$doctor->name}</b></td>
                    <td>{$doctor->specialty}</td>
                    <td>{$available_days}</td>
                    <td>";
                    
            if (!empty($time_slots)) {
                foreach ($time_slots as $slot) {
                    echo "<span class='btn btn-outline-primary btn-sm me-1'>{$slot}</span>";
                }
            } else {
                echo "<span class='text-muted'>No slots available</span>";
            }
        
            echo "</td>
                    <td>
                        <button class='btn btn-warning edit-doctor' 
                            data-id='{$doctor->id}' 
                            data-name='{$doctor->name}' 
                            data-specialty='{$doctor->specialty}' 
                            data-days='" . htmlspecialchars(json_encode(explode(',', $doctor->available_days))) . "'
                            data-slots='".implode(',', $time_slots)."'>Edit</button>
        
                        <button class='btn btn-danger delete-doctor' data-id='{$doctor->id}'>Delete</button>
                    </td>
                </tr>";
        }
        ?>
    </tbody>
  </table>
</div>
<script>
  jQuery(document).ready(function ($) {
      // Handle available days button click
      $(".day-btn").click(function () {
          var checkbox = $(this).find("input");
          checkbox.prop("checked", !checkbox.prop("checked"));
  
          if (checkbox.prop("checked")) {
              $(this).removeClass("btn-outline-primary").addClass("btn-primary");
          } else {
              $(this).removeClass("btn-primary").addClass("btn-outline-primary");
          }
      });
  
      // Handle time slot button click
      $(".time-slot-btn").click(function () {
          var checkbox = $(this).find("input");
          checkbox.prop("checked", !checkbox.prop("checked"));
  
          if (checkbox.prop("checked")) {
              $(this).removeClass("btn-outline-primary").addClass("btn-primary");
          } else {
              $(this).removeClass("btn-primary").addClass("btn-outline-primary");
          }
      });
  
      // Handle Edit Doctor Button Click
      $(".edit-doctor").on("click", function () {
          var doctorId = $(this).data("id");
          var doctorName = $(this).data("name");
          var doctorSpecialty = $(this).data("specialty");
          var availableDaysArray = JSON.parse($(this).attr("data-days"));
          var timeSlotsArray = $(this).data("slots").split(",");
  
          // Populate modal form fields
          $("#doctor_id").val(doctorId);
          $("#name").val(doctorName);
          $("#specialty").val(doctorSpecialty);
  
          // Reset and highlight available days
          $(".day-checkbox").each(function () {
              var dayValue = $(this).val();
              if (availableDaysArray.includes(dayValue)) {
                  $(this).prop("checked", true).parent().removeClass("btn-outline-primary").addClass("btn-primary");
              } else {
                  $(this).prop("checked", false).parent().removeClass("btn-primary").addClass("btn-outline-primary");
              }
          });
  
          // Reset and highlight selected time slots
          $(".time-slot-checkbox").each(function () {
              var slotValue = $(this).val();
              if (timeSlotsArray.includes(slotValue)) {
                  $(this).prop("checked", true).parent().removeClass("btn-outline-primary").addClass("btn-primary");
              } else {
                  $(this).prop("checked", false).parent().removeClass("btn-primary").addClass("btn-outline-primary");
              }
          });
  
          // Change modal title to "Edit Doctor"
          $("#doctorModalLabel").text("Edit Doctor");
  
          // Show the modal
          $("#doctorModal").modal("show");
      });
  
      // Reset modal title when closed
      $("#doctorModal").on("hidden.bs.modal", function () {
          $("#doctorModalLabel").text("Add Doctor");
      });
  });
  
  
    
</script>