<?php
function import_master($request)
{
    $params = $request->get_json_params();
//  RES API
    $rest_client = new uiRestClient();
    $resStr = $rest_client->get_store_master($params);
    $store = json_decode($resStr, true);
    [$ID, $thumbnail_url, $post_title, $fields] = array_values($store);
    $import = new uiImport();
    $import->init($fields);
    $import->upsert_blogpost();
    $post_id = $import->upsert_import($post_title);
    [$thumbnail_id] = $import->save_media($thumbnail_url);
    set_post_thumbnail($post_id, $thumbnail_id);
//    $code_ids
    $code_ids = [];
    foreach ($fields['tech'] as $code_id) {
        $code_ids[] = code_master_handle($code_id);
    }
    return ['post_id' => $post_id, 'code_ids' => $code_ids];
}