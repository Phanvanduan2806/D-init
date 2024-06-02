<?php
function exports_post_type() : void {
    $args = array(
        'labels' => array(
            'name' => __('Exports'),
            'singular_name' => __('Export')
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => false,
        'supports' => [
            'title',
            'thumbnail'
        ],
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
    );
    register_post_type('exports', $args);
}