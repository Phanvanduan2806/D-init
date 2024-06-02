<?php

class Ui_Helper_Function
{
    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private $path_flatsome_child;

    public function __construct()
    {
        $this->path_flatsome_child = WP_CONTENT_DIR . '/themes/flatsome-child';
    }

    public function extract_files($imageMap)
    {
        $files = [];
        foreach ($imageMap as $i => $id) {
            if (!is_numeric($id)) {
                $filePath = $id;
                $filePath = str_replace(home_url('/'), ABSPATH, $filePath);
                $files[$i] = $filePath;
                continue;
            }
            $filePath = wp_get_original_image_url($id);
            $filePath = str_replace(home_url('/'), ABSPATH, $filePath);
            $files[$i] = $filePath;
        }
        return $files;
    }

    public function get_master($key)
    {
        $encoded_key = defined('UI_HELPER_MASTER_KEY') ? UI_HELPER_MASTER_KEY : '';
        if (empty($encoded_key)) {
            error_log("UI_HELPER_MASTER_KEY is empty or not defined.");
            return;
        }
        $decoded_key = base64_decode($encoded_key);
        if ($decoded_key === false) {
            error_log("Failed to decode the key.");
            return;
        }
        $decodedData = explode(',', $decoded_key);
        if ($key == 'key') return @$decodedData[0];
        if ($key == 'url') return @$decodedData[1];
        if ($key == 'home') {
            $home = str_replace('/wp-json/ui-master/v1', '', $decodedData[1]);
            $home = str_replace('host.docker.internal', 'localhost', $home);
            return $home;
        }
    }

    public
    function extract_blog_posts($html, $cate_ids)
    {
        function posts_add($posts_array)
        {
            return !$posts_array ? [] : array_map(function ($post) {
                $thumbnail_id = get_post_thumbnail_id($post->ID);
                $thumbnail_url = wp_get_original_image_url($thumbnail_id);
                return [
                    'ID' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_content' => $post->post_content,
                    'thumbnail_id' => $thumbnail_id,
                    'thumbnail_url' => $thumbnail_url
                ];
            }, $posts_array);
        }

        preg_match_all('/\[blog_posts[^\]]+\]/mi', $html, $output_array);
        $cat_ids = [];
        $post_ids = [];
        $limit = 8;
        if (!empty($cate_ids)) {
            foreach ($cate_ids as $cate_id)
                $cat_ids[] = [$cate_id, 100];
        }
        foreach ($output_array[0] as $shortcode) {
            if (preg_match('/posts="([^"]+)"/', $shortcode, $matches))
                $limit = $matches[1];
            if (preg_match('/cat="([^"]+)"/', $shortcode, $matches)) {
                $cat_ids[] = [$matches[1], $limit];
            }
            if (preg_match('/ids="([^"]+)"/', $shortcode, $matches))
                $post_ids[] = [explode(',', $matches[1]), $limit];
        }
        $results = [];
//        $cat_ids && $post_ids
        if (empty($cat_ids) && empty($post_ids)) {
            $posts_array = get_posts([
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $limit,
            ]);
            $posts = posts_add($posts_array);
            wp_reset_postdata();
            $results[] = [
                'type' => 'newest',
                'posts' => $posts,
            ];
        }
//        $cat_ids
        foreach ($cat_ids as [$cat_id, $limit]) {
            $cate = get_category($cat_id);
            $category = ['ID' => $cat_id, 'name' => $cate->name, 'description' => $cate->description];
            $posts_array = get_posts([
                'cat' => $cat_id,
                'posts_per_page' => $limit,
                'post_status' => 'publish',
            ]);
            $posts = posts_add($posts_array);
            wp_reset_postdata();
            $results[] = [
                'type' => 'category',
                'category' => $category,
                'posts' => $posts,
            ];
        }
//        $post_ids
        foreach ($post_ids as [$ids, $limit]) {
            $posts_array = get_posts([
                'post__in' => $ids,
                'post_type' => 'post',
                'post_status' => 'publish',
            ]);
            $posts = posts_add($posts_array);
            wp_reset_postdata();
            $results[] = [
                'type' => 'ids',
                'ids' => implode(',', $ids),
                'posts' => $posts,
            ];
        }
        return $results;
    }

    public
    function extract_unique_images($html, &$images)
    {
        $patterns = [
            '/url\((.*?)\)/', // CSS pattern
            '/<img[^>]+src="([^"]+)/', // JS pattern
            '/\[section[^\]]+bg="(\d+)"/', // HTML pattern
            '/\[ux_image[^\]]+id="(\d+)"/', // HTML pattern
            '/\[featured_box[^\]]+img="(\d+)"/', // HTML pattern
            '/\[ux_image_box[^\]]+img="(\d+)"/', // HTML pattern
        ];
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $html, $matches);
            foreach ($matches[1] as $match) {
                if (!empty($match)) {
                    $images[] = str_replace(["\"", "'"], "", $match);
                }
            }
        }
        $images = array_values(array_unique($images));
    }

    public
    function export_menus()
    {
        $menus = get_terms('nav_menu');
        $export_data = array();

        foreach ($menus as $menu) {
            $menu_items = wp_get_nav_menu_items($menu->term_id);
            $menu_type = get_theme_mod('nav_menu_locations'); // Check the menu location
            $menu_items = array_map(function ($item) {
                return [
                    'title' => $item->title,
                    'url' => str_replace(home_url('/'), '/', $item->url)
                ];
            }, $menu_items);
            $export_data[] = array(
                'menu_name' => $menu->name,
                'menu_type' => array_search($menu->term_id, $menu_type),
                'menu_items' => $menu_items
            );
        }
        return $export_data;
    }

    public
    function multi_task_html($label, $html, &$blocks)
    {
        preg_match_all('/\[block[^\]]+id="([^"]+)/', $html, $output_array);
        foreach ($output_array[1] as $label1) {
            $html1 = uiHelper()->flatsome_block($label1);
            $blocks[] = $this->multi_task_html($label1, $html1, $blocks);
        }
        $classes = [];
        $images = [];
        $uxb_list = [];
        uiHelper()->extract_unique_classes($html, $classes);
        uiHelper()->extract_unique_images($html, $images);
        if (preg_match('/\[post_cat_uxb_list/mis', $html))
            $uxb_list[] = 'post_cat_uxb_list';
//        uiHelper()->extract_post_cat_uxb_list($html, $uxb_list);
        // css
        $css_content = uiHelper()->get_content('css', $classes);
        // css-media
        $css_media_content = uiHelper()->get_content_css_media($classes);
        // js
        $js_content = uiHelper()->get_content('js', $classes);
        // images
        uiHelper()->extract_unique_images($css_content, $images);
        uiHelper()->extract_unique_images($css_media_content, $images);
        uiHelper()->extract_unique_images($js_content, $images);
        $image_ids = uiHelper()->get_image_ids($images);
        $html = "<!-- $label -->\n" . $html;
        return [$html, $css_content, $css_media_content, $js_content, $image_ids, $uxb_list];
    }

    public
    function extract_post_cat_uxb_list($category)
    {
        $cate = get_category($cat_id);
        $category = ['ID' => $cat_id, 'name' => $cate->name, 'description' => $cate->description];
        $posts_array = get_posts([
            'cat' => $cat_id,
            'posts_per_page' => $limit,
            'post_status' => 'publish',
        ]);
        $posts = posts_add($posts_array);
        wp_reset_postdata();
        $results[] = [
            'type' => 'category',
            'category' => $category,
            'posts' => $posts,
        ];
    }

    public
    function extract_unique_classes($html, &$classes)
    {
        preg_match_all('/class="([^"]+)/mis', $html, $output_array);
        foreach ($output_array[1] as $item) {
            $classes = array_merge(explode(' ', $item), $classes);
        }
        $classes = array_values(array_unique($classes));
    }

    public
    function get_recursive_files($ext, $path)
    {
        $ext_files = [];
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $full_path = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($full_path)) {
                $ext_files = array_merge($ext_files, $this->get_recursive_files($ext, $full_path));
            } elseif (pathinfo($file, PATHINFO_EXTENSION) === $ext) {
                $content = file_get_contents($full_path);
                $ext_files[] = [
                    'file' => $full_path,
                    'content' => $content
                ];
            }
        }
        return $ext_files;
    }

    public
    function get_image_ids($images)
    {
        $image_ids = [];
        foreach ($images as $id) {
            if (is_numeric($id)) {
                $image_ids[$id] = $id;
                continue;
            }
            $image_url = site_url($id);
            $attachment_id = attachment_url_to_postid($image_url);
            $image_ids[$id] = $attachment_id;
        }
        return $image_ids;
    }


    public
    function flatsome_block($id)
    {
        $fileFunctionGlobal = WP_CONTENT_DIR . '/themes/flatsome/inc/functions/function-global.php';
        if (!file_exists($fileFunctionGlobal)) {
            wp_die('The file ' . $fileFunctionGlobal . ' does not exist.');
        }
        require_once $fileFunctionGlobal;
        $post_id = flatsome_get_block_id($id);
        $the_post = $post_id ? get_post($post_id, OBJECT, 'display') : null;
        if (!$the_post) return null;
        return $the_post->post_content;
    }

    public
    function flatsome_option($action = 'get', $smof_data = [])
    {
        $fileFunctionsAdmin = WP_CONTENT_DIR . '/themes/flatsome/inc/admin/advanced/functions/functions.admin.php';
        if (!file_exists($fileFunctionsAdmin)) {
            wp_die('The file ' . $fileFunctionsAdmin . ' does not exist.');
        }
        require_once $fileFunctionsAdmin;
        if ($action == 'get') {
            $smof_data = of_get_options();
            $export_data = base64_encode(serialize($smof_data));
            return [$smof_data, $export_data];
        }
        of_save_options($smof_data);
    }

    public
    function upsert_post($post_type, $title)
    {
        global $wpdb;
        $existing_posts = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->posts WHERE post_type = %s AND post_title = %s AND post_status = 'publish' LIMIT 1",
                $post_type,
                $title
            )
        );
        if ($existing_posts) {
            return $existing_posts->ID;
        }
        $post_data = array(
            'post_title' => $title,
            'post_status' => 'publish',
            'post_type' => $post_type,
        );
        $post_id = wp_insert_post($post_data);
        if (is_wp_error($post_id)) {
            wp_die('Failed to insert post');
        }
        return $post_id;
    }

    public
    function get_content_css_media($classes)
    {
        $ext = 'css';
        $files = $this->get_recursive_files($ext, $this->path_flatsome_child);
        $all = '';
        foreach ($files as $file) {
            if (preg_match('/media/mi', $file['file']))
                $all .= $file['content'];
        }

        $allMedia = [];
        $mediaQueries = explode('@media', $all);
        array_shift($mediaQueries);
        foreach ($mediaQueries as $mediaQuery) {
            $mediaQuery = '@media' . $mediaQuery;
            $allMedia[] = $mediaQuery;
        }
        $content = '';
        foreach ($allMedia as $media) {
            foreach ($classes as $class) {
                $pattern = "/(\@media[^\{]+\{).+?(\/\* \.$class \*\/.+?\/\* \.\.$class \*\/)/mis";
                error_log('$pattern: '.$pattern);
                preg_match_all($pattern, $media, $output_array, PREG_SET_ORDER);
                if (!empty($output_array[0])) {
                    $out = $output_array[0];
                    $content .= $out[1] . "\n" . $out[2] . "\n}\n";
                }
            }
        }
        return $content;
    }

    public
    function get_content($ext, $classes)
    {
        $files = $this->get_recursive_files($ext, $this->path_flatsome_child);
        $all = '';
        foreach ($files as $file) {
            if ($ext != 'css'){
                $all .= $file['content'];
                continue;
            }
            if(!preg_match('/\@media/mis', $file['content']))
                $all .= $file['content'];
        }
        $content = '';
        foreach ($classes as $class) {
            $pattern = "/(\/\* \.$class \*\/.+?\/\* \.\.$class \*\/)/mis";
            preg_match_all($pattern, $all, $output_array);
            foreach ($output_array[1] as $item) {
                $content .= $item . "\n";
            }
        }
        return $content;
    }
}

if (!function_exists('uiHelper')) {
    function uiHelper()
    {
        return Ui_Helper_Function::instance();
    }
}