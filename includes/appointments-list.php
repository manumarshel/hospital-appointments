<!-- /**
 * Plugin Name: Hospital Appointments
 * Description: A custom WordPress plugin for managing hospital appointments.
 * Version: 1.0
 * Author: Your Name
 * Developer: Manu Marshal
 * Developer Url: https://manumarshal.com/
 */ -->
 <div class="wrap">
  <h1 class="wp-heading-inline">Manage Appointments</h1>
  <br/><br/>
  <hr class="wp-header-end">
  <table id="appointmentsTable" class="wp-list-table widefat fixed striped">
    <thead>
      <tr>
        <th>Patient Name</th>
        <th>Patient Email</th>
        <th>Doctor</th>
        <th>Specialty</th>
        <th>Appointment Date & Time</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'ha_appointments';
        $doctors_table = $wpdb->prefix . 'ha_doctors';
        
        // Fetch all appointments along with doctor specialty
        $appointments = $wpdb->get_results("
            SELECT a.*, d.specialty 
            FROM $appointments_table AS a
            LEFT JOIN $doctors_table AS d
            ON a.doctor_name = d.name
        ");
        
        foreach ($appointments as $appointment) {
            // Convert Date Format (YYYY-MM-DD - Day)
            $appointment_date = date("Y-m-d - l", strtotime($appointment->appointment_date));
        
            echo "<tr>
                    <td><b>{$appointment->patient_name}</b></td>
                    <td>{$appointment->patient_email}</td>
                    <td>{$appointment->doctor_name}</td>
                    <td>{$appointment->specialty}</td>
                    <td>{$appointment_date}<br/><b>{$appointment->appointment_time}</b></td>
                    <td>
                        <select class='appointment-status' data-id='{$appointment->id}'>
                            <option value='Pending' " . selected($appointment->status, 'Pending', false) . ">Pending</option>
                            <option value='Confirmed' " . selected($appointment->status, 'Confirmed', false) . ">Confirmed</option>
                            <option value='Completed' " . selected($appointment->status, 'Completed', false) . ">Completed</option>
                        </select>
                    </td>
                </tr>";
        }
        ?>
    </tbody>
  </table>
</div>
<script>
  jQuery(document).ready(function ($) {
      $("#appointmentsTable").DataTable();
  
      // Change Appointment Status
      $(".appointment-status").change(function () {
          var appointmentId = $(this).data("id");
          var newStatus = $(this).val();
  
          $.ajax({
              type: "POST",
              url: "<?php echo admin_url('admin-ajax.php'); ?>",
              data: {
                  action: "update_appointment_status",
                  appointment_id: appointmentId,
                  status: newStatus
              },
              success: function (response) {
                  if (response.success) {
                      alert("Appointment status updated successfully!");
                  } else {
                      alert("Failed to update status. Please try again.");
                  }
              }
          });
      });
  });
</script>