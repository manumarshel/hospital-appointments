<?php
/**
 * Plugin Name: Hospital Appointments
 * Description: A custom WordPress plugin for managing hospital appointments.
 * Version: 1.0
 * Author: Your Name
 * Developer: Manu Marshal
 * Developer Url: https://manumarshal.com/
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// ✅ Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

// ✅ Enqueue custom styles and scripts
function ha_enqueue_assets($hook) {
    global $pagenow;

    // Get current page
    $allowed_pages = ['toplevel_page_ha-manage-doctors', 'hospital-appointments_page_ha-appointments'];

    // Load assets only on Hospital Appointments pages
    if (in_array($hook, $allowed_pages)) {
        // Bootstrap CSS & JS
        wp_enqueue_style('ha-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
        wp_enqueue_script('ha-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), false, true);

        // DataTables CSS & JS
        wp_enqueue_style('ha-datatables-style', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css');
        wp_enqueue_script('ha-datatables', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), false, true);

        // Custom Styles & Scripts
        wp_enqueue_style('ha-custom-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
        wp_enqueue_script('ha-custom-scripts', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery', 'ha-datatables'), false, true);
    }
}
add_action('admin_enqueue_scripts', 'ha_enqueue_assets');



// LOAD THIS FOR FRONTEND 
function ha_enqueue_frontend_assets() {
    // Bootstrap CSS & JS
    wp_enqueue_style('ha-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_script('ha-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), false, true);

    // Custom Styles & Scripts
    wp_enqueue_style('ha-custom-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('ha-custom-scripts', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), false, true);
}
add_action('wp_enqueue_scripts', 'ha_enqueue_frontend_assets');



// ✅ Admin Menu Setup
function ha_admin_menu() {
    add_menu_page(
        'Hospital Appointments',
        'Hospital Appointments',
        'manage_options',
        'ha-manage-doctors',
        'ha_list_doctors',
        'dashicons-calendar',
        6
    );

    // Manage Doctors
    add_submenu_page(
        'ha-manage-doctors',
        'Manage Doctors',
        'Manage Doctors',
        'manage_options',
        'ha-manage-doctors',
        'ha_list_doctors'
    );

    // Manage Appointments
    add_submenu_page(
        'ha-manage-doctors',
        'Appointments',
        'Appointments',
        'manage_options',
        'ha-appointments',
        'ha_list_appointments'
    );
}
add_action('admin_menu', 'ha_admin_menu');

// ✅ Function to display the Doctors List
function ha_list_doctors() {
    echo '<div class="wrap">';
    echo '<hr class="wp-header-end">';
    require_once plugin_dir_path(__FILE__) . 'includes/doctor-list.php';
    echo '</div>';
}

// ✅ Function to display the Appointments List
function ha_list_appointments() {
    echo '<div class="wrap">';
    echo '<hr class="wp-header-end">';
    require_once plugin_dir_path(__FILE__) . 'includes/appointments-list.php';
    echo '</div>';
}
