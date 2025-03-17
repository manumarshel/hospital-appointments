=== Hospital Appointments ===
Plugin Developer: Manu Marshal
Developer link: https://manumarshal.com/
Tags: hospital, appointments, doctor booking, medical, clinic
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: #

A simple hospital appointment booking plugin that allows patients to book appointments with doctors based on availability.

== Description ==

The **Hospital Appointments** plugin allows hospitals and clinics to manage doctor appointments efficiently. It provides:
- A frontend appointment booking form.
- A backend admin panel to manage doctors and appointments.
- Automated email notifications for patients and admins.
- Appointment date and time slot management.
- Restricts users from selecting unavailable slots.

**Key Features:**
✔️ **Doctor Management** - Add, edit, and delete doctors with available days and time slots.  
✔️ **Appointment Booking Form** - Patients can select doctors, choose available days, and book appointments.  
✔️ **Admin Dashboard** - Manage all appointments, update statuses, and send notifications.  
✔️ **Email Notifications** - Sends confirmation emails to patients and admins.  
✔️ **Duplicate Booking Prevention** - Prevents double-booking for the same time slot.  

== Installation ==

1. **Upload via WordPress Admin**
   - Go to **Plugins > Add New**.
   - Click **Upload Plugin** and select `hospital-appointments.zip`.
   - Click **Install Now**, then **Activate**.

2. **Manual Installation**
   - Extract the `hospital-appointments.zip` file.
   - Upload the `hospital-appointments` folder to `/wp-content/plugins/`.
   - Activate the plugin from the **Plugins** menu in WordPress.

3. **Setup**
   - Go to **Hospital Appointments > Manage Doctors** to add doctors and their schedules.
   - Use the `[hospital_appointment_form]` shortcode to display the appointment form.

== Usage ==

- **Admin Panel:**  
  - **Manage Doctors:** Add/Edit doctors with available days and time slots.  
  - **Manage Appointments:** View and update appointment statuses.  

- **Frontend:**  
  - Use `[hospital_appointment_form]` shortcode to display the booking form.

== Shortcodes ==

- `[hospital_appointment_form]` - Displays the appointment booking form.

== Screenshots ==

1. **Doctor Management Panel**
2. **Appointment Booking Form**
3. **Admin Appointment List**
4. **Email Confirmation Example**

== Frequently Asked Questions ==

= How do I display the appointment booking form? =
Use the shortcode `[hospital_appointment_form]` on any page or post.

= Can I prevent duplicate bookings? =
Yes! The plugin automatically prevents multiple appointments for the same doctor, date, and time.

= Does this plugin send email notifications? =
Yes, patients receive a confirmation email upon booking, and the admin is notified.

= How do I manage appointments? =
Go to **Hospital Appointments > Appointments** in the WordPress admin dashboard.

== Changelog ==

= 1.0 =
- Initial release with core appointment booking and management features.

== Upgrade Notice ==

= 1.0 =
First release of the plugin.
