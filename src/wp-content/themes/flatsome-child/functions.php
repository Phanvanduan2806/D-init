<?php
require_once 'includes/index.php';
require_once 'shortcodes/index.php';

define("WP_FLATSOME_ASSET_VERSION", time());
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;500;600;700&display=swap',
        [],
        WP_FLATSOME_ASSET_VERSION
    );
    wp_enqueue_style( 'c-font-css', get_stylesheet_directory_uri() . '/assets/css/c-font.css', [], WP_FLATSOME_ASSET_VERSION );
    wp_enqueue_style( 'c-home-css', get_stylesheet_directory_uri() . '/assets/css/c-home.css', [], WP_FLATSOME_ASSET_VERSION );
    wp_enqueue_style( 'c-media-queries-css', get_stylesheet_directory_uri() . '/assets/css/c-media-queries.css', [], WP_FLATSOME_ASSET_VERSION );
}, 999);

add_action( 'wp_footer', function () {
     wp_enqueue_script( 'c-home-js', get_stylesheet_directory_uri() . '/assets/js/c-home.js', [], WP_FLATSOME_ASSET_VERSION );
});
