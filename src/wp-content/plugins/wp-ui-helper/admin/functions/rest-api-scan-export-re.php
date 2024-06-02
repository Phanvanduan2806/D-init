<?php
function scan_export_re($request)
{
    $params = $request->get_json_params();
    $label = $params['label'];
    if ($label == 'option') {
        return scan_export_option();
    }
    $search_term = '[section label="' . $label . '"';
    $pattern = '/\[section label="(' . $label . ').+?\[\/section/mis';
    return handle_export_section($search_term, $pattern);
}