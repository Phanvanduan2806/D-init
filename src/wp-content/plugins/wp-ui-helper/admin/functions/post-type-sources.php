<?php
function sources_post_type() : void {
    $args = array(
        'labels' => array(
            'name' => __('Sources'),
            'singular_name' => __('Source')
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => false,
        'supports' => [
            'title',
        ],
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
    );
    register_post_type('sources', $args);
}