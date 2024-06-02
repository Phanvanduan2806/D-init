<?php
function code_master_handle($code_id)
{
//  RES API
    $rest_client = new uiRestClient();
    $resStr = $rest_client->get_code_master(['code_id' => $code_id]);
    $code = json_decode($resStr, true);
    [$ID, $post_title, $fields] = array_values($code);
//    var_dump([$ID, $post_title, $fields]);die();
    $source = new uiSource();
    $source->init($fields);
    $post_id = $source->upsert_source($post_title);
    return ['post_id' => $post_id];
}

function code_master($request)
{
    $params = $request->get_json_params();
    return code_master_handle($params['code_id']);
}