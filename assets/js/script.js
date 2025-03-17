/**
 * Plugin Name: Hospital Appointments
 * Description: A custom WordPress plugin for managing hospital appointments.
 * Version: 1.0
 * Author: Your Name
 * Developer: Manu Marshal
 * Developer Url: https://manumarshal.com/
 */
jQuery(document).ready(function ($) {
    // Handle form submission (Add/Edit Doctor)
    $("#doctorForm").on("submit", function (e) {
        e.preventDefault();
        
        var formData = $(this).serialize();

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: formData + "&action=save_doctor",
            success: function (response) {
                if (response.success) {
                    $("#successMessage").html('<div class="updated notice is-dismissible"><p>Doctor saved successfully!</p></div>');
                    $("#successMessage").fadeIn().delay(1500).fadeOut(); 

                    // Close modal after successful save
                    $("#doctorModal").modal("hide");

                    // Reload the doctor list smoothly
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                } else {
                    alert("Failed to save doctor");
                }
            }
        });
    });

    // Open modal for adding a doctor
    $(".page-title-action").on("click", function () {
        $("#doctor_id").val(""); // Clear ID for new doctor
        $("#doctorForm")[0].reset(); // Reset form fields
        $("#doctorModalLabel").text("Add Doctor"); // Change heading
        $("#doctorModal").modal("show");
    });

    // Open modal for editing a doctor
    $(".edit-doctor").on("click", function () {
        var doctorId = $(this).data("id");
        $("#doctor_id").val(doctorId);
        $("#name").val($(this).data("name"));
        $("#specialty").val($(this).data("specialty"));

        var daysArray = $(this).data("days").split(",");
        $("input[name='available_days[]']").each(function () {
            $(this).prop("checked", daysArray.includes($(this).val()));
        });

        $("#start_time").val($(this).data("start"));
        $("#end_time").val($(this).data("end"));

        $("#doctorModalLabel").text("Edit Doctor"); // Update heading
        $("#doctorModal").modal("show");
    });

    // Reset modal heading when closing
    $("#doctorModal").on("hidden.bs.modal", function () {
        $("#doctorModalLabel").text("Add Doctor"); // Reset heading when modal is closed
    });

    // Prevent closing modal when clicking outside or pressing ESC key
    $("#doctorModal").modal({
        backdrop: 'static',
        keyboard: false
    });

    // Initialize Bootstrap DataTable
    $("#doctorTable").DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "responsive": true
    });

    // Delete doctor
    $(document).on("click", ".delete-doctor", function () {
        var doctorId = $(this).data("id");

        if (confirm("Are you sure you want to delete this doctor?")) {
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: "delete_doctor", doctor_id: doctorId },
                success: function (response) {
                    if (response.success) {
                        alert("Doctor deleted successfully!");
                        location.reload(); // Reload the table
                    } else {
                        alert("Failed to delete doctor.");
                    }
                }
            });
        }
    });
});


// APPOINTMENT DUPLICATE CHECK 
jQuery(document).ready(function ($) {
    $("#appointmentForm").on("submit", function (e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            type: "POST",
            url: ha_ajax.ajaxurl, // ✅ Use localized AJAX URL
            data: formData + "&action=save_appointment",
            dataType: "json",
            beforeSend: function () {
                $("#appointmentForm button[type='submit']").prop("disabled", true);
            },
            success: function (response) {
                if (response.success) {
                    $("#appointmentSuccess").text(response.message).fadeIn().delay(3000).fadeOut();
                    setTimeout(function () {
                        $("#appointmentForm")[0].reset();
                    }, 2000);
                } else {
                    $("#appointmentError").text(response.message).fadeIn().delay(3000).fadeOut();
                }
            },
            error: function (xhr, status, error) {
                console.log("❌ AJAX Error:", xhr.responseText);
                $("#appointmentError").text("An error occurred while booking the appointment.").fadeIn().delay(3000).fadeOut();
            },
            complete: function () {
                $("#appointmentForm button[type='submit']").prop("disabled", false);
            }
        });
    });
});




jQuery(document).ready(function ($) {
    $("#doctorSelect").change(function () {
        let selectedDoctor = $(this).find(":selected");
        let availableDays = selectedDoctor.data("availability");
        let daysArray = availableDays ? availableDays.split(",") : [];

        // Reset dropdown
        $("#availableDaySelect").prop("disabled", true);
        $("#availableDaySelect").val("");

        // Enable only available days
        $("#availableDaySelect option").each(function () {
            let dayValue = $(this).val();
            if (dayValue && daysArray.includes(dayValue)) {
                $(this).prop("disabled", false);
            } else {
                $(this).prop("disabled", true);
            }
        });

        // Show the section only if there are available days
        if (daysArray.length > 0) {
            $("#availableDaysSection").fadeIn();
            $("#availableDaySelect").prop("disabled", false);
        } else {
            $("#availableDaysSection").fadeOut();
        }
    });
});
