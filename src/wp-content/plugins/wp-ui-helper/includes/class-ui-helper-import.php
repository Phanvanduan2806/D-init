<?php

class uiImport extends uiAcf
{
    private $code;
    private $blog_posts;
    private $export_data;
    private $files;
    private $blogposts_rule;
    private $files_post;
    private $menu;

    public function init($fields)
    {
        $this->code = $fields['code'];
        $this->export_data = $fields['export_data'];
        $this->menu = $fields['menu'];
        $this->blog_posts = $fields['blog_posts'];
//        $fields['image']
//        var_dump($fields['image']);die();
        $images = [];
        foreach ($fields['image'] as $i => $image_url) {
            $images[$i] = $this->save_media($image_url);
        }
        $this->files = $images;
//      $fields['blog_posts']
        $image_posts = [];
        foreach ($fields['blog_posts'] as $item) {
            foreach ($item['posts'] as $post)
                $image_posts[$post['thumbnail_id']] = $this->save_media($post['thumbnail_url']);
        }
        $this->files_post = $image_posts;
    }

    public function upsert_menu($menus_data)
    {
        $menus_data = unserialize(base64_decode($menus_data));
        if (empty($menus_data) || !is_array($menus_data)) {
            return;
        }

        foreach ($menus_data as $menu_data) {
//          menu_name
            $menu_name = $menu_data['menu_name'];
            $menu_exists = wp_get_nav_menu_object($menu_name);
            if (!$menu_exists) {
                $menu_id = wp_create_nav_menu($menu_name);
            } else {
                $menu_id = $menu_exists->term_id;
            }
//          menu_location
            $menu_location = $menu_data['menu_type'];
            $locations = get_theme_mod('nav_menu_locations');
            if (!empty($menu_location) && isset($locations[$menu_location])) {
                $locations[$menu_location] = $menu_id;
            }
            set_theme_mod('nav_menu_locations', $locations);
//          menu_items
            $menu_items = wp_get_nav_menu_items($menu_id);
            if (!empty($menu_items)) {
                foreach ($menu_items as $menu_item) {
                    wp_delete_post($menu_item->ID);
                }
            }
            foreach ($menu_data['menu_items'] as $item) {
                $menu_item_data = array(
                    'menu-item-title' => $item['title'],
                    'menu-item-url' => $item['url'],
                    'menu-item-status' => 'publish',
                    'menu-item-type' => 'custom',
                );
                wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
            }
        }
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
            'imports'
        );
        $store_post = $wpdb->get_row($query);
        if (!$store_post) return;
        $fields = get_fields($store_post->ID);
        return $fields;
    }

    public function upsert_post_cate($category_id, $post, $media_id)
    {
        global $wpdb;
        $escaped_title = $wpdb->esc_like($post['post_title']);
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID
        FROM $wpdb->posts
        WHERE post_title = %s
        AND post_status = 'publish'
        AND post_type = 'post'",
            $escaped_title
        ));
        $post_data = array(
            'post_title' => $post['post_title'],
            'post_content' => $post['post_content'],
            'post_category' => array($category_id),
            'post_status' => 'publish'
        );
        if ($post_id) {
//            $post_data['ID'] = $post_id;
//            unset($post_data['post_category']);
//            wp_update_post($post_data);
            wp_set_post_categories($post_id, [$category_id]);
        } else {
            $post_id = wp_insert_post($post_data);
        }
        if (!is_wp_error($post_id)) {
            set_post_thumbnail($post_id, $media_id);
        }
        return $post_id;
    }

    public function upsert_post($post, $media_id)
    {
        global $wpdb;
        $escaped_title = $wpdb->esc_like($post['post_title']);
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID
        FROM $wpdb->posts
        WHERE post_title = %s
        AND post_status = 'publish'
        AND post_type = 'post'",
            $escaped_title
        ));
        $post_data = array(
            'post_title' => $post['post_title'],
            'post_content' => $post['post_content'],
            'post_status' => 'publish'
        );
        if (!$post_id) {
            $post_id = wp_insert_post($post_data);
        }
        if (!is_wp_error($post_id)) {
            set_post_thumbnail($post_id, $media_id);
        }
        return $post_id;
    }

    public function write_file($file_path, $content)
    {
        $directory = dirname($file_path);
        if (!file_exists($directory)) {
            if (!mkdir($directory, 0755, true))
                wp_die('Create directory: ' . $directory);
        }
        if (file_exists($file_path)) {
            file_put_contents($file_path, $content, FILE_APPEND);
            return;
        }
        file_put_contents($file_path, $content);
    }

    public function upsert_ux($title, $content)
    {
        global $wpdb;
        $escaped_title = $wpdb->esc_like($title);
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID
        FROM $wpdb->posts
        WHERE post_title = %s
        AND post_status = 'publish'
        AND post_type = 'blocks'",
            $escaped_title
        ));
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'blocks'
        );
        kses_remove_filters();
        if ($post_id) {
            $post_data['ID'] = $post_id;
            wp_update_post($post_data);
        }
        $post_id = wp_insert_post($post_data);
        kses_init_filters();
        return $post_id;
    }

    public function upsert_category($category_name)
    {
        $category = get_term_by('name', $category_name, 'category');
        if ($category) {
            return $category->term_id;
        }
        $new_category = wp_insert_term($category_name, 'category');
        if (is_wp_error($new_category)) {
            error_log('Error creating category: ' . $new_category->get_error_message());
            return null;
        }
        return $new_category['term_id'];
    }

    public function upsert_blogpost()
    {
        $categories = [];
        $posts = [];
        foreach ($this->blog_posts as $blog_post) {
            if ($blog_post['type'] == 'newest') {
                foreach ($blog_post['posts'] as $post) {
                    [$attach_id] = $this->files_post[$post['thumbnail_id']];
//                    $post['post_title'] .= ".{$license->post->ID}";
                    $post_id = $this->upsert_post($post, $attach_id);
                }
            }
            if ($blog_post['type'] == 'ids') {
                $ids = [];
                foreach ($blog_post['posts'] as $post) {
                    [$attach_id] = $this->files_post[$post['thumbnail_id']];
//                    $post['post_title'] .= ".{$license->post->ID}";
                    $post_id = $this->upsert_post($post, $attach_id);
                    $ids[] = $post_id;
                }
                $posts[$blog_post['ids']] = implode(',', $ids);
            }
            if ($blog_post['type'] == 'category') {
                $category = $blog_post['category'];
//                $cate_name = "{$category['name']}.{$license->post->ID}";
                $cate_name = "{$category['name']}";
                $cate_id = $this->upsert_category($cate_name);
                $categories[$category['ID']] = $cate_id;
                foreach ($blog_post['posts'] as $post) {
                    [$attach_id] = $this->files_post[$post['thumbnail_id']];
//                    $post['post_title'] .= ".{$license->post->ID}";
                    $post_id = $this->upsert_post_cate($cate_id, $post, $attach_id);
                }
            }
        }
        $this->blogposts_rule = [$categories, $posts];
    }

    public function upsert_import($title)
    {
        global $wpdb;
        $existing_posts = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish' AND post_title = %s LIMIT 1",
                'imports',
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
                'post_type' => 'imports',
            );
            $post_id = wp_insert_post($post_data);
            if (is_wp_error($post_id)) {
                return new WP_Error('insert_failed', 'Failed to insert post', array('status' => 500));
            }
        }
//        EXTRA FIELDS
        parent::init($post_id);
        parent::delete_fields();

        foreach ($this->code as $item) {
            [$ext, $content] = array_values($item);
            error_log('$ext: ' . $ext);
            $content = $this->replace_image_code($content);
            if ('html' == $ext) {
                $content = $this->replace_blogpost($content);
            }
            $value = [
                'acf_fc_layout' => $ext,
                'content' => $content
            ];
            add_row('code', $value, $post_id);
        }
        foreach ($this->files as $key => [$id, $url]) {
            if ($key == 'thumbnail_url') continue;
            $value = [
                'id' => is_numeric($key) ? $id : $url,
                'img' => $id,
            ];
            add_row('image', $value, $post_id);
        }
        if (str_contains($title, 'option')) {
//            EXPORT_DATA
            $smof_data = unserialize(base64_decode($this->export_data));
            [$id, $url] = $this->files[$smof_data['site_logo']];
            $smof_data['site_logo'] = $id;
            if (!empty($smof_data['topbar_right']))
                $smof_data['topbar_right'] = $this->replace_image_code($smof_data['topbar_right']);
            if (!empty($smof_data['topbar_left']))
                $smof_data['topbar_left'] = $this->replace_image_code($smof_data['topbar_left']);
            $export_data = base64_encode(serialize($smof_data));
            update_field('export_data', $export_data, $post_id);
//            MENU
            update_field('menu', $this->menu, $post_id);
        }
        return $post_id;
    }

    public function save_media($image_url)
    {

        if (filter_var($image_url, FILTER_VALIDATE_URL) === FALSE) {
            error_log('Invalid URL provided');
            return [null, null];
        }

        global $wpdb;
        $filename = basename($image_url);
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_title = %s",
            $filename
        ));
        if($attachment_id){
            $media_url = wp_get_attachment_url($attachment_id);
            return [$attachment_id, str_replace(home_url('/'), '/', $media_url)];
        }
        $image_data = file_get_contents($image_url);
        if ($image_data === FALSE) {
            error_log('Failed to download image');
            return [null, null];
        }


        $upload = wp_upload_bits($filename, null, $image_data);
        if (!$upload['error']) {
            $file_path = $upload['file'];
            $file_type = wp_check_filetype($filename, null);
            $attachment = array(
                'post_mime_type' => $file_type['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attachment_id = wp_insert_attachment($attachment, $file_path);

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            return [$attachment_id, str_replace(home_url('/'), '/', $upload['url'])];
        } else {
            error_log('Failed to upload image to media library');
            return [null, null];
        }
    }

    public function replace_blogpost($html)
    {
        [$categories, $posts] = $this->blogposts_rule;
        foreach ($categories as $old_id => $new_id) {
            $html = preg_replace('/(\[blog_posts[^\]]+)cat="' . $old_id . '"/mis', '$1cat="' . $new_id . '"', $html);
        }
        foreach ($posts as $old_id => $new_id) {
            $html = preg_replace('/(\[blog_posts[^\]]+)ids="' . $old_id . '"/mis', '$1ids="' . $new_id . '"', $html);
        }
        return $html;
    }

    public function replace_image_code($html)
    {
        foreach ($this->files as $key => [$id, $url]) {
            $key = preg_quote($key, '/');
            $patterns = [
                "/url\($key\)/" => "url($url)",
                '/src="' . $key . '"/' => 'src="' . $url . '"',
                '/\[section([^\]]+)bg="' . $key . '"/' => '[section$1bg="' . $id . '"',
                '/\[ux_image([^\]]+)id="' . $key . '"/' => '[ux_image$1id="' . $id . '"',
                '/\[featured_box([^\]]+)img="' . $key . '"/' => '[featured_box$1img="' . $id . '"',
                '/\[ux_image_box([^\]]+)img="' . $key . '"/' => '[ux_image_box$1img="' . $id . '"',
            ];
            foreach ($patterns as $pattern => $value) {
                $html = preg_replace($pattern, $value, $html);
            }
        }
        return $html;
    }
}

