<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link
 * @since      1.0.0
 *
 * @package    Ui_Helper
 * @subpackage Ui_Helper/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ui_Helper
 * @subpackage Ui_Helper/public
 * @author
 */
class Ui_Helper_Public
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
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    public function template_include($template)
    {
        global $post;

        if (is_page('merge-import')) {
            $new_template = plugin_dir_path(__FILE__) . 'partials/page-merge-import.php';
            if (file_exists($new_template)) {
                return $new_template;
            }
        }
        if (!empty($post->post_type) && 'imports' === $post->post_type && is_single()) {
            $template_redirect = plugin_dir_path(__FILE__) . 'partials/single-exports.php';
            if (file_exists($template_redirect)) {
                return $template_redirect;
            }
        }
        if (!empty($post->post_type) && 'exports' === $post->post_type && is_single()) {
            $template_redirect = plugin_dir_path(__FILE__) . 'partials/single-exports.php';
            if (file_exists($template_redirect)) {
                return $template_redirect;
            }
        }
        return $template;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ui-helper-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ui-helper-public.js', array('jquery'), $this->version, false);

    }

}
