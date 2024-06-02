<?php

define('UI_HELPER_VERSION', time());
define('UI_HELPER_MASTER_KEY', 'ZmUzNDFkZWM2N2ZkYTU1NzViODcwMTk5MWIwNTEyNDksaHR0cHM6Ly9tYXN0ZXIud2ViaXQuY29tLnZuL3dwLWpzb24vdWktbWFzdGVyL3Yx');

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link
 * @since             1.0.0
 * @package           Ui_Helper
 *
 * @wordpress-plugin
 * Plugin Name:       UI Helper
 * Plugin URI:
 * Description:       Effortlessly enhance user experience with UI Helper plugin!
 * Version:           1.0.0
 * Author:
 * Author URI:        /
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ui-helper
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ui-helper-activator.php
 */
function activate_ui_helper()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-ui-helper-activator.php';
    Ui_Helper_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ui-helper-deactivator.php
 */
function deactivate_ui_helper()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-ui-helper-deactivator.php';
    Ui_Helper_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_ui_helper');
register_deactivation_hook(__FILE__, 'deactivate_ui_helper');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-ui-helper.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ui_helper()
{

    $plugin = new Ui_Helper();
    $plugin->run();

}

run_ui_helper();

