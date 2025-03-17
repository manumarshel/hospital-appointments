<!-- /**
 * Plugin Name: Hospital Appointments
 * Description: A custom WordPress plugin for managing hospital appointments.
 * Version: 1.0
 * Author: Your Name
 * Developer: Manu Marshal
 * Developer Url: https://manumarshal.com/
 */ -->
 <div class="wrap">
    <h1 class="wp-heading-inline">Manage Appointments</h1><br/><br/>
    <hr class="wp-header-end">

    <table id="appointmentsTable" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Sl.No</th>
                <th>Patient Name</th>
                <th>Patient Email</th>
                <th>Doctor</th>
                <th>Appointment Date & Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'ha_appointments';
            $appointments = $wpdb->get_results("SELECT * FROM $table_name");
            $sl_no = 1; // Serial Number Counter

            foreach ($appointments as $appointment) {
                echo "<tr>
                        <td>{$sl_no}</td>
                        <td><b>{$appointment->patient_name}</b></td>
                        <td>{$appointment->patient_email}</td>
                        <td>{$appointment->doctor_name}</td>
                        <td>" . date('Y-m-d - l', strtotime($appointment->appointment_date)) . "<br/>
                            <b>{$appointment->appointment_time}</b>
                        </td>
                        <td>
                            <select class='appointment-status' data-id='{$appointment->id}'>
                                <option value='Pending' " . selected($appointment->status, 'Pending', false) . ">Pending</option>
                                <option value='Confirmed' " . selected($appointment->status, 'Confirmed', false) . ">Confirmed</option>
                                <option value='Completed' " . selected($appointment->status, 'Completed', false) . ">Completed</option>
                            </select>
                        </td>
                        <td><button class='btn btn-danger delete-appointment' data-id='{$appointment->id}'>Delete</button></td>
                    </tr>";
                $sl_no++;
            }
            ?>
        </tbody>
    </table>
    
    <div id="statusMessage" class="notice notice-success is-dismissible" style="display:none;"></div>
</div>

<script>
jQuery(document).ready(function ($) {
    $("#appointmentsTable").DataTable();

    // ✅ Handle Status Change
    $(".appointment-status").change(function () {
        let appointmentId = $(this).data("id");
        let newStatus = $(this).val();
        let row = $(this).closest("tr");

        if (confirm("Are you sure you want to update the status?")) {
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
                        $("#statusMessage").text("Appointment status updated successfully!")
                            .fadeIn().delay(3000).fadeOut();
                    } else {
                        alert("Failed to update status.");
                    }
                },
                error: function () {
                    alert("An error occurred while updating the status.");
                }
            });
        } else {
            location.reload(); // Reset dropdown to previous value if canceled
        }
    });

    // ✅ Handle Appointment Deletion
    $(".delete-appointment").click(function () {
        let appointmentId = $(this).data("id");
        let row = $(this).closest("tr");

        if (confirm("Are you sure you want to delete this appointment?")) {
            $.ajax({
                type: "POST",
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                data: { action: "delete_appointment", appointment_id: appointmentId },
                success: function (response) {
                    if (response.success) {
                        row.fadeOut();
                        $("#statusMessage").text("Appointment deleted successfully!")
                            .fadeIn().delay(3000).fadeOut();
                    } else {
                        alert("Failed to delete appointment.");
                    }
                },
                error: function () {
                    alert("An error occurred while deleting the appointment.");
                }
            });
        }
    });
});
</script>
