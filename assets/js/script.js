jQuery(document).ready(function ($) {
    // Handle form submission (Add/Edit Doctor)
    $("#doctorForm").on("submit", function (e) {
        e.preventDefault();
        
        var formData = $(this).serialize();

        $.ajax({
            type: "POST",
            url: ajaxurl, // Ensure ajaxurl is defined globally
            data: formData + "&action=save_doctor",
            dataType: "json",
            beforeSend: function () {
                console.log("Sending data...", formData);
            },
            success: function (response) {
                console.log("Server Response:", response); // Debug the full response
        
                if (response.success) {
                    let message = $("#doctor_id").val() ? "Doctor updated successfully!" : "New doctor added successfully!";
                    $("#successMessage").html('<div class="updated notice is-dismissible"><p>' + message + '</p></div>');
                    $("#successMessage").fadeIn().delay(2000).fadeOut();
                    $("#doctorModal").modal("hide");
        
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                } else {
                    alert("Error: " + (response.data ? response.data : "Failed to save doctor"));
                }
            },
            error: function (xhr, status, error) {
                console.log("AJAX Error:", xhr.responseText); // Log raw response
                alert("AJAX Error: " + error);
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
