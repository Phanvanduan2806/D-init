<?php
require_once 'functions/post-type-exports.php';
require_once 'functions/post-type-imports.php';
require_once 'functions/post-type-sources.php';
require_once 'functions/admin-menu-main.php';
require_once 'functions/rest-api-scan-export-option.php';
require_once 'functions/rest-api-scan-export-section.php';
require_once 'functions/rest-api-scan-export-re.php';
require_once 'functions/rest-api-scan-export-submit.php';
require_once 'functions/rest-api-code-master.php';
require_once 'functions/rest-api-import-master.php';
require_once 'functions/rest-api-import-merge.php';
require_once 'functions/rest-api-merge-source.php';
require_once 'shortcodes/blog_imports.php';
require_once 'shortcodes/blog_pages.php';
require_once 'shortcodes/blog_options.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link
 * @since      1.0.0
 *
 * @package    Ui_Helper
 * @subpackage Ui_Helper/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ui_Helper
 * @subpackage Ui_Helper/admin
 * @author
 */
class Ui_Helper_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function thumbnail_posts_thumb_column($column, $post_id) {
        if ($column === 'thumbnail') {
            echo get_the_post_thumbnail($post_id, array(50, 50));
        }
    }

    public function thumbnail_posts_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'cb') {
                $new_columns['thumbnail'] = __('Thumbnail');
            }
        }
        return $new_columns;
    }

    public function admin_menu()
    {
        main_admin_menu();
    }

    public function acf_include_fields()
    {
        if (!function_exists('acf_add_local_field_group')) return;
        $export_fields = json_decode( file_get_contents( __DIR__.'/jsons/acf-export-2024-01-11.json' ), true );
        foreach ($export_fields as $export_field) {
            acf_add_local_field_group($export_field);
        }
    }

    public function rest_api_init()
    {
        register_rest_route('ui-helper/v1', '/merge-source', array(
            'methods' => 'POST',
            'callback' => 'merge_source',
        ));
        register_rest_route('ui-helper/v1', '/code-master', array(
            'methods' => 'POST',
            'callback' => 'code_master',
        ));
        register_rest_route('ui-helper/v1', '/import-merge', array(
            'methods' => 'POST',
            'callback' => 'import_merge',
        ));
        register_rest_route('ui-helper/v1', '/import-master', array(
            'methods' => 'POST',
            'callback' => 'import_master',
        ));
        register_rest_route('ui-helper/v1', '/scan-export-submit', array(
            'methods' => 'POST',
            'callback' => 'scan_export_submit',
        ));
        register_rest_route('ui-helper/v1', '/scan-export-re', array(
            'methods' => 'POST',
            'callback' => 'scan_export_re',
        ));
        register_rest_route('ui-helper/v1', '/scan-export-section', array(
            'methods' => 'POST',
            'callback' => 'scan_export_section',
        ));
        register_rest_route('ui-helper/v1', '/scan-export-option', array(
            'methods' => 'POST',
            'callback' => 'scan_export_option',
        ));
    }

    public function init()
    {
        exports_post_type();
        imports_post_type();
        sources_post_type();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ui_Helper_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ui_Helper_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ui-helper-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ui_Helper_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ui_Helper_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        add_thickbox();
        wp_enqueue_script('http-api-js', plugin_dir_url(__FILE__) . 'js/http-api.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ui-helper-admin.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'php_obj', array(
            'url' => uiHelper()->get_master('url'),
            'home' => uiHelper()->get_master('home'),
            'key' => uiHelper()->get_master('key'),
        ));
    }

}
