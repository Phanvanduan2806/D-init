<?php

/**
 * Fired during plugin activation
 *
 * @link
 * @since      1.0.0
 *
 * @package    Ui_Helper
 * @subpackage Ui_Helper/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ui_Helper
 * @subpackage Ui_Helper/includes
 * @author
 */
class Ui_Helper_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        $errs = [];
        if (!is_plugin_active('advanced-custom-fields-pro/acf.php')) {
            $errs[] = 'ACF-PRO plugin: https://shorturl.at/iFI49';
        }
//        if (!is_plugin_active('customize-post-categories-for-ux-builder/customize-post-categories-for-ux-builder.php')) {
//            $errs[] = 'CATEGORIES-UX-BUILDER plugin: https://shorturl.at/fijpK';
//        }
//        $theme = wp_get_theme('flatsome');
//        if (!$theme->exists()) {
//            $errs[] = 'FLATSOME theme: https://shorturl.at/dltA9';
//        }
        if (!defined('UI_HELPER_MASTER_KEY')) {
            $errs[] = 'Missing: UI_HELPER_MASTER_KEY';
        }
        if (!uiHelper()->get_master('url')) {
            $errs[] = 'Wrong format: UI_HELPER_MASTER_KEY';
        }
        if(!empty($errs)){
            wp_die(implode("<br/>", $errs));
        }
        $new_page = array(
            'post_title' => 'Merge import',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '',
        );
        wp_insert_post($new_page);

    }

}
