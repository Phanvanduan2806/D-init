<?php

function main_admin_menu() {
    add_menu_page(
        'UI Helper',
        'UI Helper',
        'manage_options',
        'ui-helper',
        'custom_menu_callback',
        'dashicons-tagcloud', // Icon URL or a Dashicons class
        25 // Position in the menu order
    );

    // Submenu for Exports under UI Helper
    add_submenu_page(
        'ui-helper',
        'Exports',
        'Exports',
        'manage_options',
        'edit.php?post_type=exports'
    );

    // Submenu for Imports under UI Helper
    add_submenu_page(
        'ui-helper',
        'Imports',
        'Imports',
        'manage_options',
        'edit.php?post_type=imports'
    );
    // Submenu for Imports under UI Helper
    add_submenu_page(
        'ui-helper',
        'Sources',
        'Sources',
        'manage_options',
        'edit.php?post_type=sources'
    );
}
function custom_menu_callback() {
    $new_template = plugin_dir_path(__FILE__) . '../partials/ui-helper-admin-display.php';
    if (file_exists($new_template)) {
        echo file_get_contents($new_template);
    }
}
