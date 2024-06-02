<?php
function merge_source($request)
{
    $params = $request->get_json_params();
    $source = new uiSource();
    $data = $source->get($params['post_id']);
    $import = new uiImport();
    foreach ($data['code'] as $code) {
        $file_path = "/{$data['path']}/{$code['file_name']}";
        $file_path = get_stylesheet_directory() . $file_path;
        @unlink($file_path);
        $import->write_file($file_path, $code['content']);
    }
    $functions_php = trim($data['functions_php']);
    if ("<?php" == substr($functions_php, 0, 5))
        $functions_php = substr($functions_php, 5);
    return [
        'functions_php' => $functions_php,
        'how_to_use' => $data['how_to_use']
    ];
}