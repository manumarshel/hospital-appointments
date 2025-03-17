<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// ✅ Display License Activation Page
function ha_license_settings_page() {
    ?>
    <div class="wrap">
        <h2>Hospital Appointments License Activation</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('ha_license_settings');
            do_settings_sections('ha_license_settings');
            ?>
            <table class="form-table">
                <tr>
                    <th><label for="ha_license_key">License Key:</label></th>
                    <td>
                        <input type="text" name="ha_license_key" id="ha_license_key" 
                               value="<?php echo esc_attr(get_option('ha_license_key')); ?>" style="width: 300px;" />
                    </td>
                </tr>
            </table>
            <?php submit_button('Save License Key'); ?>
        </form>
    </div>
    <?php
}

// ✅ Register License Key Setting
function ha_register_license_settings() {
    register_setting('ha_license_settings', 'ha_license_key');
}

// ✅ Add License Page to WordPress Admin Menu
function ha_add_license_menu() {
    add_options_page('Hospital Appointments License', 'License Activation', 'manage_options', 'ha-license', 'ha_license_settings_page');
}

add_action('admin_menu', 'ha_add_license_menu');
add_action('admin_init', 'ha_register_license_settings');
