<?php

class uiRestClient
{
    function post_request($post_fields, $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["uiMasterKey: ".uiHelper()->get_master('key'), "uiHelperHome: ".site_url('/')]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    function get_store_master($fields)
    {
        $url = uiHelper()->get_master('url') . '/store-get';
        return $this->post_request($fields, $url);
    }

    function get_code_master($fields)
    {
        $url = uiHelper()->get_master('url') . '/code-get';
        return $this->post_request($fields, $url);
    }

    function add_store_master($fields, $files, $files_post)
    {
        $url = uiHelper()->get_master('url') . '/store-add';
        $post_fields = $fields;
        foreach ($files as $i => $file_path) {
            if (!empty($file_path) && file_exists($file_path))
                $post_fields['files['.$i.']'] = new CURLFile($file_path);
        }
        foreach ($files_post as $i => $file_path) {
            if (!empty($file_path) && file_exists($file_path))
                $post_fields['files_post['.$i.']'] = new CURLFile($file_path);
        }
        return $this->post_request($post_fields, $url);
    }
}
