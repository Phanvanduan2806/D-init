<?php

class uiFlatsome
{
    public $post;
    public $fields;

    public function __construct($post_id)
    {
        $this->post = get_post($post_id);
        $this->fields = get_fields($post_id);
    }

    public function get_deep()
    {
        $categories = is_array($this->fields['category']) ? $this->fields['category']: [];
        $cate_ids = array_map(function ($category) {
            return $category['cate'];
        }, $categories);
        $label = $this->post->post_title;
        [$codes, $imageMap] = $this->generate_maps();
        $html = '';
        array_map(function ($code) use (&$html) {
            $html .= $code['acf_fc_layout'] == 'html' ? $code['content'] : '';
        }, $codes);
        $blog_posts = uiHelper()->extract_blog_posts($html, $cate_ids);
        $cate_ids = array_map(function ($id) use ($blog_posts) {
            foreach ($blog_posts as $item) {
                if (isset($item['category']['name']) && $item['category']['ID'] == $id)
                    return [$item['category']['name'], $item['category']['ID']];
            }
            return [null, null];
        }, $cate_ids);
        $fields = [
            'label' => $label,
            'code' => json_encode($codes),
            'image' => json_encode($imageMap),
            'blog_posts' => json_encode($blog_posts),
            'cate_ids' => json_encode($cate_ids)
        ];
        if ($label == 'option') {
            $fields['export_data'] = $this->fields['export_data'];
            $fields['menu'] = $this->fields['menu'];
//            $fields['blocks'] = $this->fields['blocks'];
        }
        $files = uiHelper()->extract_files($imageMap);
        $files_post = [];
        foreach ($blog_posts as $item) {
            foreach ($item['posts'] as $post) {
                $files_post[$post['thumbnail_id']] = str_replace(home_url('/'), ABSPATH, $post['thumbnail_url']);
            }
        }
//        THUMBNAIL_URL
        $thumbnail_id = get_post_thumbnail_id($this->post->ID);
        $thumbnail_url = wp_get_original_image_url($thumbnail_id);
        if (!$thumbnail_url) {
            wp_die("THUMBNAIL_URL is empty.");
        }
        $thumbnail_url = str_replace(home_url('/'), ABSPATH, $thumbnail_url);
        $files['thumbnail_url'] = $thumbnail_url;
        return [$fields, $files, $files_post];
    }

    public function get_display()
    {
        [$codes, $imageMap] = $this->generate_maps();
        $htmlCodes = $this->process_codes($codes, $imageMap);
        return implode("", $htmlCodes);
    }

    private function generate_maps()
    {
        $codes = $this->fields['code'];
        $images = $this->fields['image'];
        $imageMap = [];
        if ($images) {
            foreach ($images as $image) {
                $id = $image['id'];
                $img = $image['img'];
                if (!is_numeric($id)) {
                    $image_url = wp_get_attachment_url($img);
                    $imageMap[$id] = $image_url;
                    continue;
                }
                $imageMap[$id] = $img;
            }
        }
        return [$codes, $imageMap];
    }

    private function process_codes($codes, $imageMap)
    {
        $htmlCodes = [];
        foreach ($codes as $code) {
            $type = $code['acf_fc_layout'];
            $content = $code['content'];
            if ($type == 'js') {
                foreach ($imageMap as $strId => $imgUrl) {
                    if (is_numeric($strId)) continue;
                    $content = str_replace($strId, $imgUrl, $content);
                }
                $htmlCodes[] = <<<EOF
[ux_html label="js" class="js"]<script>$content</script>[/ux_html]
EOF;
                continue;
            }
            if ($type == 'css') {
                foreach ($imageMap as $strId => $imgUrl) {
                    if (is_numeric($strId)) continue;
                    $content = str_replace($strId, $imgUrl, $content);
                }
                $htmlCodes[] = <<<EOF
[ux_html label="css" class="css"]<style>$content</style>[/ux_html]
EOF;
                continue;
            }
            if ($type == 'css_media') {
                foreach ($imageMap as $strId => $imgUrl) {
                    if (is_numeric($strId)) continue;
                    $content = str_replace($strId, $imgUrl, $content);
                }
                $htmlCodes[] = <<<EOF
[ux_html label="css_media" class="css_media"]<style>$content</style>[/ux_html]
EOF;
                continue;
            }
            // html
            foreach ($imageMap as $id => $idChoice) {
                if (is_string($id)) continue;
                $content = preg_replace('/\[section([^\]]+)bg="' . $id . '"/', '[section$1bg="' . $idChoice . '"', $content);
                $content = preg_replace('/\[ux_image([^\]]+)id="' . $id . '"/', '[ux_image$1id="' . $idChoice . '"', $content);
                $content = preg_replace('/\[featured_box([^\]]+)img="' . $id . '"/', '[featured_box$1img="' . $idChoice . '"', $content);
                $content = preg_replace('/\[ux_image_box([^\]]+)img="' . $id . '"/', '[ux_image_box$1img="' . $idChoice . '"', $content);
            }
            $htmlCodes[] = $content;
        }
        return $htmlCodes;
    }

}