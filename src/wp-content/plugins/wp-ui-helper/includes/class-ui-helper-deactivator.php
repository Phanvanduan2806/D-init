<?php

/**
 * Fired during plugin deactivation
 *
 * @link
 * @since      1.0.0
 *
 * @package    Ui_Helper
 * @subpackage Ui_Helper/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Ui_Helper
 * @subpackage Ui_Helper/includes
 * @author
 */
class Ui_Helper_Deactivator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    private static function delete_post_thumbnail($post_type) {
        global $wpdb;

        $exports = get_posts(array(
            'post_type' => $post_type,
            'numberposts' => -1,
            'fields' => 'ids',
        ));

        foreach ($exports as $export_id) {
            $thumbnail_id = get_post_thumbnail_id($export_id);
            if ($thumbnail_id) {
                wp_delete_attachment($thumbnail_id, true);
            }
        }

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->posts} WHERE post_type = %s",
                $post_type
            )
        );
    }


    public static function deactivate()
    {
        global $wpdb;
        self::delete_post_thumbnail('exports');
        self::delete_post_thumbnail('imports');
        self::delete_post_thumbnail('sources');

        $page_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'page'", 'Merge import'));
        if ($page_id) {
            $wpdb->delete($wpdb->posts, array('ID' => $page_id), array('%d'));
        }
    }

}
