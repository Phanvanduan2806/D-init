<?php
function scan_export_option()
{
    [$smof_data, $export_data] = uiHelper()->flatsome_option();
    $post_id = uiHelper()->upsert_post('exports', 'option');
    $acf = new uiAcf();
    $acf->init($post_id);
    $acf->delete_fields();
    $acf->update_field('export_data', $export_data);
    $site_logo = $smof_data['site_logo'];
    $acf->add_images([$site_logo => $site_logo]);
    $map_blocks = [
        'header-block-1' => @$smof_data['header-block-1'],
        'header-block-2' => @$smof_data['header-block-2'],
        '404_block' => @$smof_data['404_block'],
        'footer_block' => @$smof_data['footer_block'],
    ];
    foreach ($map_blocks as $label) {
        $html = uiHelper()->flatsome_block($label);
        if (!$html) continue;
        $blocks = [];
        $blocks[] = uiHelper()->multi_task_html($label, $html, $blocks);
        $blocks = array_reverse($blocks);
        foreach ($blocks as [$html, $css_content, $css_media_content, $js_content, $image_ids]) {
            if (!empty($html)) $acf->add_html($html);
            if (!empty($css_content)) $acf->add_css($css_content);
            if (!empty($css_media_content)) $acf->add_css_media($css_media_content);
            if (!empty($js_content)) $acf->add_js($js_content);
            if (!empty($image_ids)) $acf->add_images($image_ids);
        }
    }

//    MENU
    $menu = uiHelper()->export_menus();
    $menu = base64_encode(serialize($menu));
//    $menu = json_encode($menu, JSON_UNESCAPED_UNICODE);
//    $home_url = substr(json_encode(home_url('/')), 1, -1);
//    $menu = str_replace($home_url, '\/', $menu);
    $acf->update_field('menu', $menu);

    return ['post_id' => $post_id];
}