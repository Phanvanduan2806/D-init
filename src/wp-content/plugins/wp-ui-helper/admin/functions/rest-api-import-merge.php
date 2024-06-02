<?php

function handle_code($codes, $page_slug)
{
    $import = new uiImport();
    $ux_append = [];
    $css_append = '';
    $css_media_append = '';
    $js_append = '';
    foreach ($codes as $field) {
        if ($field['acf_fc_layout'] == 'js') {
            $file_path = "/assets/js/c-$page_slug.js";
            $import->write_file(get_stylesheet_directory() . $file_path, $field['content']);
            $js_append .= <<<EOF
wp_enqueue_script( 'c-$page_slug-js', get_stylesheet_directory_uri() . '$file_path', [], WP_FLATSOME_ASSET_VERSION );
EOF;
            continue;
        }
        if ($field['acf_fc_layout'] == 'css') {
            $file_path = "/assets/css/c-$page_slug.css";
            $import->write_file(get_stylesheet_directory() . $file_path, $field['content']);
            $css_append .= <<<EOF
wp_enqueue_style( 'c-$page_slug-css', get_stylesheet_directory_uri() . '$file_path', [], WP_FLATSOME_ASSET_VERSION );
EOF;
            continue;
        }
        if ($field['acf_fc_layout'] == 'css_media') {
            $file_path = '/assets/css/c-media-queries.css';
            $import->write_file(get_stylesheet_directory() . $file_path, $field['content']);
            $css_media_append .= <<<EOF
wp_enqueue_style( 'c-media-queries-css', get_stylesheet_directory_uri() . '$file_path', [], WP_FLATSOME_ASSET_VERSION );
EOF;
            continue;
        }
        if ($field['acf_fc_layout'] == 'html') {
            preg_match('/^<!--\s*(.*?)\s*-->/', $field['content'], $output_array);
            $old_id = $output_array[1];
            $id = $import->upsert_ux($old_id, $field['content']);
            $ux_append[$old_id] = $id;
            continue;
        }
    }
    if (empty($css_append) && empty($css_media_append) && empty($js_append)) return [null, $ux_append];
    $enqueue_code = <<<EOF
/* $page_slug */

EOF;
    if (!empty($css_append) || !empty($css_media_append)) {
        $enqueue_code .= <<<EOF
add_action( 'wp_enqueue_scripts', function () {
    $css_append
    $css_media_append
}, 999);

EOF;
    }
    if (!empty($js_append)) {
        $enqueue_code .= <<<EOF
add_action( 'wp_footer', function () {
    $js_append
});

EOF;
    }
    $enqueue_code .= <<<EOF
/* END: $page_slug */
EOF;
    return [$enqueue_code, $ux_append];
}

function smof_data_handle($export_data, $ux_append)
{
    $smof_data = unserialize(base64_decode($export_data));
    if (!empty($smof_data['header-block-1']))
        $smof_data['header-block-1'] = $ux_append[$smof_data['header-block-1']];
    if (!empty($smof_data['header-block-2']))
        $smof_data['header-block-2'] = $ux_append[$smof_data['header-block-2']];
    if (!empty($smof_data['404_block']))
        $smof_data['404_block'] = $ux_append[$smof_data['404_block']];
    if (!empty($smof_data['footer_block']))
        $smof_data['footer_block'] = $ux_append[$smof_data['footer_block']];
    uiHelper()->flatsome_option('save', $smof_data);
}

function import_merge($request)
{
    $params = $request->get_json_params();
    $page_id = $params['page_id'];
    $import_ids = $params['import_ids'];
    $import = new uiImport();
    if ($page_id == 'option') {
        $fields = $import->get($import_ids[0]);
        [$enqueue_code, $ux_append] = handle_code($fields['code'], 'option');
        smof_data_handle($fields['export_data'], $ux_append);
        $import->upsert_menu($fields['menu']);
        return ['enqueue_code' => $enqueue_code];
    }
    $page_slug = get_post_field('post_name', $page_id);
    $page = get_post($page_id);
    $page_content = $page->post_content;
    $enqueue_code='';
    foreach ($import_ids as $import_id) {
        $fields = $import->get($import_id);
        [$enqueue_code, $ux_append] = handle_code($fields['code'], $page_slug);
        $ux_append = array_values($ux_append);
        $page_content .= "\n[block id=\"$ux_append[0]\"]";
    }
    wp_update_post(['ID' => $page_id, 'post_content' => $page_content]);
    return ['enqueue_code' => $enqueue_code];
}