<?php
function imports_post_type() : void {
    $args = array(
        'labels' => array(
            'name' => __('Imports'),
            'singular_name' => __('Import')
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => false,
        'supports' => [
            'title',
            'thumbnail'
        ],
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
    );
    register_post_type('imports', $args);
}