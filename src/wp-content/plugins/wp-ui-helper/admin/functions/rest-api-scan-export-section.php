<?php
function handle_export_section($search_term, $pattern)
{
    global $wpdb;
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_status = 'publish'",
            '%' . $wpdb->esc_like($search_term) . '%'
        )
    );
    if (!$results) {
        wp_die("No published posts found: $search_term");
    }
    $sections = [];
    foreach ($results as $result) {
        $content = $result->post_content;
        preg_match_all($pattern, $content, $output_array, PREG_SET_ORDER);
        foreach ($output_array as $item) {
            [$html, $label] = $item;
            $sections[$label] = "$html]";
        }
    }
    $post_ids = [];
    foreach ($sections as $label => $html) {
        $post_id = uiHelper()->upsert_post('exports', $label);
        $post_ids[] = $post_id;
        $acf = new uiAcf();
        $acf->init($post_id);
        $acf->delete_fields();
        $blocks = [];
        $blocks[] = uiHelper()->multi_task_html($label, $html, $blocks);
        $blocks = array_reverse($blocks);
        foreach ($blocks as [$html, $css_content, $css_media_content, $js_content, $image_ids, $uxb_list]) {
            if (!empty($html)) $acf->add_html($html);
            if (!empty($css_content)) $acf->add_css($css_content);
            if (!empty($css_media_content)) $acf->add_css_media($css_media_content);
            if (!empty($js_content)) $acf->add_js($js_content);
            if (!empty($image_ids)) $acf->add_images($image_ids);
            if (!empty($uxb_list)) $acf->add_uxb_list($uxb_list);
        }
    }
    return ['post_ids' => $post_ids];
}

function scan_export_section()
{
    $search_term = '[section';
    $pattern = '/\[section label="([^"]+).+?\[\/section/mis';
    return handle_export_section($search_term, $pattern);
}