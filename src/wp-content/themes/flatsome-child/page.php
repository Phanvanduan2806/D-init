<?php
/**
 * The blog template file.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

get_header();

// the_title
ob_start();
the_title();
$title = ob_get_clean();

// the_content
ob_start();
the_content();
$content = ob_get_clean();

$result = <<<EOF
[section label="c-content" class="c-content"]
[row]
[col span__sm="12"]
[title style="center" text="$title" tag_name="h1"]
[ux_html]
$content
[/ux_html]
[/col]
[/row]
[/section]

[section label="c-game-no-hu" padding="0px" class="c-game-no-hu"]
[blog_posts style="normal" type="row" col_spacing="small" columns="6" columns__sm="2" columns__md="4" cat="12" posts="6" show_date="false" excerpt="false" comments="false" image_height="200%" class="c-post-game-no-hu"]
[/section]
EOF;
echo do_shortcode($result);
get_footer();