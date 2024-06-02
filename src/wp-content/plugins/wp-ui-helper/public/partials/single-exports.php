<?php

$post_id = get_the_ID();
$flatsome = new uiFlatsome($post_id);
$label = get_the_title();
if ('option' == $label) {

    get_header();
    get_footer();
    echo <<<EOF
<style>
html {
    margin-top: 0px !important;
}
#wpadminbar{display: none;}
@media (min-width: 850px){
.stuck{
    top: 0px!important;
}}
</style>
EOF;
    return;
}

echo <<<EOF
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
EOF;
echo do_shortcode($flatsome->get_display());

echo <<<EOF
<style>
#wpadminbar,#wrapper{display: none;}
</style>
EOF;

get_header();
get_footer();
