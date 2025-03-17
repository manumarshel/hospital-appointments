<!-- === Hospital Appointments ===
  Plugin Developer: Manu Marshal
  Developer link: https://manumarshal.com/
  Tags: hospital, appointments, doctor booking, medical, clinic
  Requires at least: 5.0
  Tested up to: 6.4
  Requires PHP: 7.2
  Stable tag: 1.0
  License: GPLv2 or later
  License URI: # -->
  <?php
  if (!defined('ABSPATH')) {
      exit; // Exit if accessed directly.
  }
  
  // ✅ Create Database Tables on Activation
  
  function ha_create_tables() {
      global $wpdb;
      $charset_collate = $wpdb->get_charset_collate();
      
      // Doctors Table
      $table_doctors = $wpdb->prefix . 'ha_doctors';
      $sql1 = "CREATE TABLE IF NOT EXISTS $table_doctors (
          id INT AUTO_INCREMENT PRIMARY KEY,
          name VARCHAR(255) NOT NULL,
          specialty VARCHAR(255) NOT NULL,
          available_days TEXT NOT NULL,
          time_slots TEXT NOT NULL
      ) $charset_collate;";
      
      // Appointments Table
      $table_appointments = $wpdb->prefix . 'ha_appointments';
      $sql2 = "CREATE TABLE IF NOT EXISTS $table_appointments (
          id INT AUTO_INCREMENT PRIMARY KEY,
          patient_name VARCHAR(255) NOT NULL,
          patient_email VARCHAR(255) NOT NULL,
          doctor_name VARCHAR(255) NOT NULL,
          appointment_date DATE NOT NULL,
          appointment_time VARCHAR(255) NOT NULL,
          status ENUM('Pending', 'Confirmed', 'Completed') NOT NULL DEFAULT 'Pending'
      ) $charset_collate;";
  
      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      dbDelta($sql1);
      dbDelta($sql2);
  }
  // ✅ Register Activation Hook in `hospital-appointments.php`
  register_activation_hook(__FILE__, 'ha_create_tables');
  add_action('init', 'ha_create_tables'); // ✅ Runs on every page load if tables are missing
  
  
  
  // ✅ Save Doctor (Add/Edit)
  function ha_save_doctor() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'ha_doctors';
  
      $data = [
          'name' => sanitize_text_field($_POST['name']),
          'specialty' => sanitize_text_field($_POST['specialty']),
          'available_days' => isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : '',
          'time_slots' => isset($_POST['time_slots']) ? implode(',', $_POST['time_slots']) : ''
      ];
  
      if (!empty($_POST['doctor_id'])) {
          $wpdb->update($table_name, $data, ['id' => intval($_POST['doctor_id'])]);
      } else {
          $wpdb->insert($table_name, $data);
      }
  
      wp_send_json_success();
  }
  add_action('wp_ajax_save_doctor', 'ha_save_doctor');
  
  // ✅ Delete Doctor
  function ha_delete_doctor() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'ha_doctors';
  
      if (!empty($_POST['doctor_id'])) {
          $deleted = $wpdb->delete($table_name, ['id' => intval($_POST['doctor_id'])]);
  
          if ($deleted) {
              wp_send_json_success();
          } else {
              wp_send_json_error();
          }
      } else {
          wp_send_json_error();
      }
  }
  add_action('wp_ajax_delete_doctor', 'ha_delete_doctor');


  // DUPLICATE FORM SUBMISSION 
  
  if (!function_exists('ha_save_appointment')) {
    function ha_save_appointment() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ha_appointments';

        ob_clean(); // ✅ Prevent any previous output

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE doctor_name = %s AND appointment_date = %s AND appointment_time = %s",
            sanitize_text_field($_POST['doctor_name']),
            sanitize_text_field($_POST['appointment_date']),
            sanitize_text_field($_POST['appointment_time'])
        ));

        if ($existing > 0) {
            wp_send_json_error(['message' => 'This time slot is already booked. Please choose a different time.']);
            exit;
        }

        $data = [
            'patient_name'     => sanitize_text_field($_POST['patient_name']),
            'patient_email'    => sanitize_email($_POST['patient_email']),
            'doctor_name'      => sanitize_text_field($_POST['doctor_name']),
            'appointment_date' => sanitize_text_field($_POST['appointment_date']),
            'appointment_time' => sanitize_text_field($_POST['appointment_time']),
            'status'           => 'Pending'
        ];

        $inserted = $wpdb->insert($table_name, $data);

        if ($inserted) {
            // ✅ Patient Email Confirmation
            $to = $data['patient_email'];
            $subject = "Appointment Confirmation - " . get_bloginfo('name');
            $message = "Dear " . $data['patient_name'] . ",\n\n";
            $message .= "Your appointment with Dr. " . $data['doctor_name'] . " has been scheduled.\n";
            $message .= "📅 Date: " . $data['appointment_date'] . "\n";
            $message .= "⏰ Time: " . $data['appointment_time'] . "\n\n";
            $message .= "Thank you for choosing our hospital!\n";
            $message .= get_bloginfo('name') . " Team";

            $headers = ['Content-Type: text/plain; charset=UTF-8'];

            $mail_sent = wp_mail($to, $subject, $message, $headers); // ✅ Store mail status

            // ✅ Admin Email Notification
            $admin_email = get_option('admin_email');
            $admin_subject = "New Appointment Scheduled";
            $admin_message = "A new appointment has been booked.\n\n";
            $admin_message .= "👤 Patient: " . $data['patient_name'] . "\n";
            $admin_message .= "📧 Email: " . $data['patient_email'] . "\n";
            $admin_message .= "👨‍⚕️ Doctor: " . $data['doctor_name'] . "\n";
            $admin_message .= "📅 Date: " . $data['appointment_date'] . "\n";
            $admin_message .= "⏰ Time: " . $data['appointment_time'] . "\n";

            wp_mail($admin_email, $admin_subject, $admin_message, $headers);

            // ✅ Ensure success message is always sent
            if ($inserted) {
              wp_send_json_success(['message' => 'Appointment booked successfully!']);
          } else {
              error_log("❌ Database Insert Error: " . $wpdb->last_error); // Logs exact DB error
              wp_send_json_error(['message' => 'Failed to book appointment. Database error: ' . $wpdb->last_error]);
          }
          
        } else {
            wp_send_json_error(['message' => 'Failed to book appointment.']);
        }

        exit; // ✅ Always exit after sending JSON response
    }
}
add_action('wp_ajax_save_appointment', 'ha_save_appointment');
add_action('wp_ajax_nopriv_save_appointment', 'ha_save_appointment');


  
  // ✅ Appointment Form Shortcode `[hospital_appointment_form]`
  function ha_appointment_form() {
      ob_start();
      global $wpdb;
  
      // Fetch available doctors
      $table_name = $wpdb->prefix . 'ha_doctors';
      $doctors = $wpdb->get_results("SELECT * FROM $table_name");
  
      ?>
<div class="roein d-flex align-items-center">
  <div class="row g-0 align-items-center justify-content-center">
    <!-- TITLE -->
    <div class="col-lg-4 col-md-4 mx-0 px-0">
      <div id="title-container">
        <h2>Hospital Appointment Manager</h2>
        <h3>Self Checker Form</h3>
        <p>"Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit..."
          "There is no one who loves pain itself, who seeks after it and wants to have it, simply because it is pain..."
        </p>
      </div>
    </div>
    <!-- FORMS -->
    <div class="col-lg-8 col-md-8 mx-0 px-0">
      <div id="qbox-container">
        <div id="appointmentSuccess" class="alert alert-success mt-3" style="display: none;">
          Appointment booked successfully!
        </div>
        <form class="needs-validation pickforms" id="appointmentForm">
          <div class="row">
            <div class="mb-3 col-md-6 col-lg-6">
              <label class="form-label">Patient Name</label>
              <input type="text" class="form-control" placeholder="Enter Your Full Name*" name="patient_name" required>
            </div>
            <div class="mb-3 col-md-6 col-lg-6">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" placeholder="Valid Email Address*" name="patient_email" required>
            </div>
            <div class="mb-3 col-md-6 col-lg-6">
              <label class="form-label">Select Doctor</label>
              <select name="doctor_name" id="doctorSelect" class="form-control" required>
                <option value="">Select Doctor</option>
                <?php foreach ($doctors as $doctor) {
                  echo "<option value='{$doctor->name}' 
                              data-availability='{$doctor->available_days}' 
                              data-slots='{$doctor->time_slots}'>
                              {$doctor->name} ({$doctor->specialty})
                        </option>";
                  } ?>
              </select>
            </div>
            <!-- Time Slots -->
            <div class="mb-3 col-md-6 col-lg-6">
              <label class="form-label">Time Slot:</label>
              <select name="appointment_time" id="appointmentTime" class="form-control" required>
                <option value="">Select Time Slot</option>
              </select>
            </div>
            <!-- Available Days (Hidden by Default) -->
            <div class="mb-3 col-md-6 col-lg-6" id="availableDaysSection" style="display: none;">
              <label class="form-label">Available Days:</label>
              <div class="btn-group-toggle" data-toggle="buttons">
                <?php
                  $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                  foreach ($days as $day) {
                      echo "<label class='btn btn-outline-primary day-btn' for='day_$day'>
                              <input type='radio' name='available_day' value='$day' id='day_$day' class='day-radio' autocomplete='off' disabled> $day
                            </label>";
                  }
                  ?>
              </div>
            </div>
            <!-- Appointment Date -->
            <div class="mb-3 col-md-6 col-lg-6" id="appointmentDateSection" style="display: none;">
              <label class="form-label">Appointment Date:</label>
              <input type="date" name="appointment_date" id="appointmentDate" class="form-control" required disabled>
            </div>
          </div>
          <div id="q-box__buttons">
            <button id="next-btn" type="submit" class="d-inline-block">Book Appointment</button> 
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  jQuery(document).ready(function ($) {
      let selectedDayIndex = null; // Stores selected day index
  
      // ✅ Handle doctor selection
      $("#doctorSelect").change(function () {
          let selectedDoctor = $(this).find(":selected");
          let availableDays = selectedDoctor.data("availability");
          let timeSlots = selectedDoctor.data("slots");
          let daysArray = availableDays ? availableDays.split(",") : [];
          let timeSlotsArray = timeSlots ? timeSlots.split(",") : [];
  
          // ✅ Show available days section if doctor has availability
          if (availableDays) {
              $("#availableDaysSection").fadeIn();
          } else {
              $("#availableDaysSection").fadeOut();
          }
  
          // ✅ Reset all day buttons
          $(".day-btn").removeClass("btn-primary").addClass("btn-outline-primary disabled-day");
          $(".day-radio").prop("checked", false).prop("disabled", true);
  
          // ✅ Enable only available days
          $(".day-radio").each(function () {
              let dayValue = $(this).val();
              if (daysArray.includes(dayValue)) {
                  $(this).prop("disabled", false);
                  $(this).parent().removeClass("btn-outline-primary disabled-day").addClass("btn-primary");
              }
          });
  
          // ✅ Reset Date Selection & Hide
          $("#appointmentDate").val("").prop("disabled", true);
          $("#appointmentDateSection").hide();
  
          // ✅ Load Available Time Slots
          $("#appointmentTime").html('<option value="">Select Time Slot</option>'); // Reset dropdown
          if (timeSlotsArray.length > 0) {
              timeSlotsArray.forEach(function (slot) {
                  $("#appointmentTime").append('<option value="' + slot + '">' + slot + '</option>');
              });
          }
      });
  
      // ✅ Handle Available Day Selection (Allow Only One)
      $(".day-btn").click(function () {
          let radio = $(this).find("input");
  
          if (!radio.prop("disabled")) {
              $(".day-btn").removeClass("btn-primary").addClass("btn-outline-primary");
              radio.prop("checked", true);
              $(this).removeClass("btn-outline-primary").addClass("btn-primary");
  
              // ✅ Get selected day index
              selectedDayIndex = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"].indexOf(radio.val());
  
              // ✅ Enable Date Picker
              $("#appointmentDate").prop("disabled", false);
              $("#appointmentDateSection").fadeIn();
              filterCalendarDates();
          }
      });
  // ✅ Filter Calendar Dates Based on Selected Day
  function filterCalendarDates() {
  $("#appointmentDate").attr("min", new Date().toISOString().split("T")[0]);
  
  $("#appointmentDate").off("input").on("input", function () {  // Remove existing event before adding new
  let selectedDate = new Date($(this).val());
  let dayOfWeek = selectedDate.getDay(); // Get selected date's day index
  
  if (dayOfWeek !== selectedDayIndex) {
      alert("You can only select " + $(".day-radio:checked").val() + " dates.");
      $(this).val("");
  }
  });
  }
  
  
      // ✅ Handle Appointment Form Submission
      $("#appointmentForm").on("submit", function (e) {
          e.preventDefault();
          var formData = $(this).serialize();
  
          $.ajax({
              type: "POST",
              url: "<?php echo admin_url('admin-ajax.php'); ?>",
              data: formData + "&action=save_appointment",
              dataType: "json",
              beforeSend: function () {
                  console.log("Sending appointment request...", formData);
              },
              success: function (response) {
                  console.log("Server Response:", response); // Debugging
  
                  if (response.success) {
                      $("#appointmentSuccess").text(response.message).fadeIn().delay(3000).fadeOut();
  
                      // Reset the form after a delay
                      setTimeout(function () {
                          $("#appointmentForm")[0].reset();
                      }, 1000);
                  } else {
                      alert("Failed to book appointment. Please try again.");
                  }
              },
              error: function (xhr, status, error) {
                  console.log("AJAX Error:", xhr.responseText);
                  alert("An error occurred while booking the appointment.");
              }
          });
      });
  });
</script>
<?php
return ob_get_clean();
}
add_shortcode('hospital_appointment_form', 'ha_appointment_form');
// ✅ Update Appointment Status (Admin AJAX);

// appointment changing in admin script 
function ha_update_appointment_status() {
global $wpdb;
$table_name = $wpdb->prefix . 'ha_appointments';
$appointment_id = intval($_POST['appointment_id']);
$new_status = sanitize_text_field($_POST['status']);
$updated = $wpdb->update(
$table_name,
['status' => $new_status],
['id' => $appointment_id]
);
if ($updated !== false) {
wp_send_json_success();
} else {
wp_send_json_error();
}
}
add_action('wp_ajax_update_appointment_status', 'ha_update_appointment_status');



// ✅ Ensure AJAX URL is available for frontend scripts
function ha_localize_ajax_script() {
  wp_localize_script('ha-custom-scripts', 'ha_ajax', ['ajaxurl' => admin_url('admin-ajax.php')]);
}
add_action('wp_enqueue_scripts', 'ha_localize_ajax_script');


