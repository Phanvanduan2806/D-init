<?php

class uiAcf
{
    private $post_id;

    public function init($post_id)
    {
        $this->post_id = $post_id;
    }

    public function delete_fields()
    {
        $fields = get_fields($this->post_id);
        if (!$fields) return;
        foreach ($fields as $key => $value) {
            delete_field($key, $this->post_id);
        }
    }

    public function add_row_code($ext, $content)
    {
        $value = [
            'acf_fc_layout' => $ext,
            'content' => $content
        ];
        add_row('code', $value, $this->post_id);
    }

    public function add_html($content)
    {
        $this->add_row_code('html', $content);
    }

    public function add_css($content)
    {
        $this->add_row_code('css', $content);
    }

    public function add_css_media($content)
    {
        $this->add_row_code('css_media', $content);
    }

    public function add_js($content)
    {
        $this->add_row_code('js', $content);
    }

    public function update_field($selector, $value)
    {
        update_field($selector, $value, $this->post_id);
    }

    public function add_uxb_list($uxb_list)
    {
        foreach ($uxb_list as $i => $id) {
            $value = [
                'id' => $id,
                'cate' => null,
            ];
            add_row('category', $value, $this->post_id);
        }
    }

    public function add_images($image_ids)
    {
        foreach ($image_ids as $id => $img) {
            $value = [
                'id' => $id,
                'img' => $img,
            ];
            add_row('image', $value, $this->post_id);
        }
    }
}
