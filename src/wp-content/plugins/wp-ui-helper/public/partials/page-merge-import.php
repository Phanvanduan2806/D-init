<?php
$html = <<<EOF
[section label="c-merge" bg_color="rgb(255,255,255)" padding="0px" class="c-merge"]

[row style="small"]

[col span="3" span__sm="12"]

[ux_stack direction="col" gap="0.25"]

[button text="Options" expand="true" link="#options"]
[button text="Imports" expand="true" link="#imports"]

[button text="Pages" expand="true" link="#pages"]
[ux_html class="c-choose"]
<ul></ul>
[button style="outline" text="Xoá tất cả" expand="true" link="/" class="no-margin" visibility="hidden"]
[/ux_html]

[/ux_stack]

[/col]
[col span="9" span__sm="12"]

[scroll_to title="options" bullet="false"]

[title text="Options"]

[blog_options style="default" type="row" col_spacing="small" columns="3" columns__sm="2" columns__md="2" posts="9999" readmore="bắt đầu" show_date="false" excerpt="false" comments="false"  text_align="left" ]

[scroll_to title="imports" bullet="false"]

[title text="Imports"]

[blog_imports style="default" type="row" col_spacing="small" columns="3" columns__sm="2" columns__md="2" posts="9999" readmore="chọn" show_date="false" excerpt="false" comments="false"  text_align="left" ]

[scroll_to title="pages" bullet="false"]

[title text="Pages"]

[blog_pages style="vertical" type="row" col_spacing="small" columns="1" columns__md="1" posts="9999" readmore="xác nhận" show_date="false" excerpt="false" comments="false" image_height="56.25%" image_width="32" text_align="left"]


[/col]

[/row]

[/section]
EOF;


/**
 * Template name: Page - No Header / No Footer
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="<?php flatsome_html_classes(); ?>">
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <link rel="profile" href="http://gmpg.org/xfn/11"/>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php do_action('flatsome_after_body_open'); ?>
<?php wp_body_open(); ?>

<?php do_action('flatsome_before_page'); ?>
<?php do_action('flatsome_after_header'); ?>
<div id="wrapper">

    <div id="main" class="<?php flatsome_main_classes(); ?>">

        <?= do_shortcode($html); ?>

    </div>

</div>
<?php do_action('flatsome_after_page'); ?>

<?php wp_footer(); ?>
</body>
</html>
<script>
    jQuery(function ($) {
        let imports = {};

        function render_import() {
            let li = ``;
            for (let key in imports) {
                if (imports.hasOwnProperty(key)) {
                    let {id, name} = imports[key];
                    li += `<li><button data-import="${id}">&times;</button> ${name}</li>`;
                }
            }
            $(`.c-choose ul`).html(li);

            $('.c-choose > .button').removeClass('hidden');
            if ($.isEmptyObject(imports)) {
                $('.c-choose > .button').addClass('hidden');
            }
        }

        $("body").on("click", ".c-choose > .button", async function (e) {
            e.preventDefault();
            imports = {};
            render_import();
        });

        $("body").on("click", "[data-option]", async function (e) {
            e.preventDefault();
            const import_id = $(this).data('option');
            const page_id = 'option';
            window.parent.postMessage({import_ids: [import_id], page_id}, "*");
        });

        $("body").on("click", "[data-page]", async function (e) {
            e.preventDefault();
            const page_id = $(this).data('page');
            const import_ids = [];
            for (let key in imports) {
                if (imports.hasOwnProperty(key)) {
                    let {id, name} = imports[key];
                    import_ids.push(id);
                }
            }
            if (import_ids.length) {
                window.parent.postMessage({import_ids, page_id}, "*");
            }
        });

        $("body").on("click", "[data-import]", async function (e) {
            e.preventDefault();
            const id = $(this).data('import');
            if (imports[id]) {
                delete imports[id];
                render_import();
                return;
            }
            const name = $(this).closest('.box-text').find('.plain').text();
            imports[id] = {id, name};
            render_import();
        });

    });
</script>
<style>
    .c-merge a.primary{
        background: rgba(25, 5, 40, 0.81)!important;
        color: #fff!important;
    }
    .c-merge a:hover{
        color: blue;
    }
    .c-choose ul {
        position: relative;
        list-style: none;
        margin-bottom: 0;
    }

    .c-choose li {
        margin: 0.5rem 0 !important;
    }

    .c-choose li button {
        background: #fff;
        margin: 0;
        padding: 0;
    }

    .c-merge .box-text,
    .c-merge .section-title-container {
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .c-merge .post-title a {
        font-size: 15px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .c-merge .box-text .button {
        width: 100%;
        margin-top: 5px;
    }

    .c-merge .large-3 .col-inner {
        position: sticky;
        top: 0px;
    }

    #wpadminbar,
    .c-merge .is-divider {
        display: none;
    }

    @media screen {
        html {
            margin-top: 0px !important;
        }
    }
</style>
