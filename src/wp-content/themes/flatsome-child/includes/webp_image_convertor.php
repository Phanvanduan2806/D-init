<?php
/*
 * 1/ Thumbnail Deletion
 * 2/ Image Size Management
 * 3/ Filename Sanitization
 * 4/ Image Resizing
 * 5/ Image Conversion to WebP
 * */
class webp_image_convertor
{
    public function __construct()
    {
        add_action('save_post', array($this, 'after_file_saved'), 10, 3);
        add_filter('intermediate_image_sizes_advanced', [$this, 'disable_image_sizes']);
        add_filter('wp_handle_upload_prefilter', [$this, 'rename_file_attachment']);
        add_action('add_attachment', [$this, 'resize_attachment']);
        add_action('add_attachment', [$this, 'convert_attachment_webp'], 20);
    }
    public function after_file_saved($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if ($post->post_type != 'files') return;
        if ($post->post_status != 'trash') return;
        $attach_id = get_post_thumbnail_id($post_id);
        wp_delete_attachment($attach_id, true);
    }
    public function disable_image_sizes($sizes)
    {
        unset($sizes['thumbnail']);
        unset($sizes['medium']);
        unset($sizes['medium_large']);
        unset($sizes['large']);
        unset($sizes['1536x1536']);
        unset($sizes['2048x2048']);
        return $sizes;
    }
    public function rename_file_attachment($file)
    {
        error_log('doing rename_file_attachment');
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name_without_extension = pathinfo($file['name'], PATHINFO_FILENAME);
        $file_name_without_extension = sanitize_title($file_name_without_extension);
//        $file_name_without_extension = str_replace('@', '', $file_name_without_extension);
        $file['name'] = $file_name_without_extension . '.' . $file_extension;
        return $file;
    }
    public function resize_attachment($attachment_id)
    {
        error_log('doing resize_attachment');
        [$info, $file_path] = $this->get_info($attachment_id);
        if (!$file_path) return;
        if (@$info[2] == IMAGETYPE_GIF) return;
        $image_editor = wp_get_image_editor($file_path);
        if (is_wp_error($image_editor)) {
            error_log('Error getting image editor: ' . $image_editor->get_error_message());
            return;
        }
        if ($image_editor instanceof WP_Image_Editor === false) {
            error_log('Error instanceof WP_Image_Editor');
            return;
        }
        $image_editor->resize(1400, null, false);
        $saved = $image_editor->save($file_path);
        $image_meta = get_post_meta($attachment_id, '_wp_attachment_metadata', true);
        // Decode JSON string into an array if necessary
        if (is_string($image_meta)) {
            $image_meta = json_decode($image_meta, true);
        }
        // Check if $image_meta is an array
        if (is_array($image_meta)) {
            $image_meta['height'] = $saved['height'];
            $image_meta['width'] = $saved['width'];
            return update_post_meta($attachment_id, '_wp_attachment_metadata', $image_meta);
        } else {
            // Handle the case where $image_meta is not an array
            error_log('Error: $image_meta is not an array for attachment ID ' . $attachment_id);
        }
    }
    private function get_info($attachment_id)
    {
        // Get the file path of the attachment
        $file_path = get_attached_file($attachment_id);
        // Make sure the file exists
        if (!file_exists($file_path)) {
            error_log('Attachment file not found.');
            return false;
        }
        // Get the MIME type and other details of the file
        return [getimagesize($file_path), $file_path];
    }
    public function convert_attachment_webp($attachment_id)
    {
        error_log('doing convert_attachment_webp');
        [$info, $file_path] = $this->get_info($attachment_id);
        if (!$file_path) return;
        if (@$info[2] == IMAGETYPE_GIF) return;
        if ($info !== false) {
            $image = null;
            switch ($info[2]) {
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($file_path);
                    break;
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($file_path);
                    break;
            }
            if ($image != null) {
                // Convert to WebP
				$file_path_without_extension = preg_replace('/\\.[^.]+$/', '', $file_path);
                $webp_path = $file_path_without_extension . '.webp';
                imagewebp($image, $webp_path);
                // Update the attachment metadata
                $attachment = get_post($attachment_id);
                update_post_meta($attachment_id, '_wp_attached_file', $webp_path);
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $webp_path));
                // Clean up
                imagedestroy($image);
            }
        }
    }
}
new webp_image_convertor();