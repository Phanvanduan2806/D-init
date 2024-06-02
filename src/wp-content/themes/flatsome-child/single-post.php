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
[section]

[row]

[col span="8" span__sm="12"]
[title style="center" text="$title" tag_name="h1"]
[ux_html]
$content
[/ux_html]


[/col]
[col label="c-sidebar" span="4" span__sm="12" class="c-sidebar"]

[title text="Bài viết mới"]

[blog_posts style="vertical" type="row" width="full-width" col_spacing="collapse" columns="1" columns__md="1" show_date="false" excerpt="false" comments="false" image_height="100%" image_width="20" text_align="left"]

[/col]

[/row]

[/section]
EOF;
echo do_shortcode($result);
get_footer();