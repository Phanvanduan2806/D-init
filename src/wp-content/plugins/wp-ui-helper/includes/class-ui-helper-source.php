<?php

class uiSource extends uiAcf
{
    private $how_to_use;
    private $functions_php;
    private $path;
    private $code;

    public function init($fields)
    {
        $this->how_to_use = $fields['how_to_use'];
        $this->functions_php = $fields['functions_php'];
        $this->path = $fields['path'];
        $this->code = $fields['code'];
    }


    public function get($post_id)
    {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT * 
    FROM {$wpdb->posts} 
    WHERE ID = %d 
    AND post_type = %s",
            $post_id,
            'sources'
        );
        $store_post = $wpdb->get_row($query);
        if (!$store_post) return;
        $fields = get_fields($store_post->ID);
        return $fields;
    }

    public function upsert_source($title)
    {
        global $wpdb;
        $existing_posts = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish' AND post_title = %s LIMIT 1",
                'sources',
                $title
            )
        );
        $post_id = null;
        if ($existing_posts) {
            $post_id = $existing_posts->ID;
        } else {
            $post_data = array(
                'post_title' => $title,
                'post_status' => 'publish',
                'post_type' => 'sources',
            );
            $post_id = wp_insert_post($post_data);
            if (is_wp_error($post_id)) {
                return new WP_Error('insert_failed', 'Failed to insert post', array('status' => 500));
            }
        }
        kses_remove_filters();
//        EXTRA FIELDS
        parent::init($post_id);
        parent::delete_fields();
        foreach ($this->code as $item) {
            [$ext, $file_name, $content] = array_values($item);
            $content = str_replace('\\', '\\\\', $content);
            $content = addslashes($content);
            $value = [
                'acf_fc_layout' => $ext,
                'file_name' => $file_name,
                'content' => $content
            ];
            add_row('field_65997945dbfec', $value, $post_id);
        }
//      MENU
        update_field('path', $this->path, $post_id);
        update_field('functions_php', $this->functions_php, $post_id);
        update_field('how_to_use', $this->how_to_use, $post_id);
        kses_init_filters();
        return $post_id;
    }

}

