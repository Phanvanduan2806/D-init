<?php
function scan_export_submit($request)
{
    $params = $request->get_json_params();
    $post_id = $params['post_id'];
    $flatsome = new uiFlatsome($post_id);
    [$fields, $files, $files_post] = $flatsome->get_deep();
    $rest_client = new uiRestClient();
    $res = $rest_client->add_store_master($fields, $files, $files_post);
    echo $res;die();
    $res_json = stripslashes($res);
    return json_decode($res_json, true);
}