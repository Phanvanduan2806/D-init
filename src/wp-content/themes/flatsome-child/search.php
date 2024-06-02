<?php
/**
 * The blog template file.
 *
 * @package          Flatsome\\Templates
 * @flatsome-version 3.16.0
 */

$html = <<<EOF
[section label="c-search" class="c-search"]

[row h_align="center"]

[col span="9" span__sm="12"]

[search]
[gap]
[c_search_posts style="normal" type="row" col_spacing="small" columns="3" columns__md="2" show_date="false" image_height="56.25%"]


[/col]

[/row]

[/section]
EOF;

get_header();
?>

<div id="content" class="blog-wrapper blog-archive page-wrapper">
		<?php echo do_shortcode($html); ?>
</div>


<?php get_footer(); ?>