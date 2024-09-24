<?php
/*
Plugin Name: Grid Shortcodes
Plugin URI: https://wpdarko.com/support/get-started-with-the-grid-shortcodes-plugin/
Description: A responsive and easy-to-use tool for dividing your content in your posts/pages. This ultra-lightweight plugin adds a button to your post editor, allowing you to put your content in columns of various widths. Find help and information on our <a href="https://wpdarko.com/support">support site</a>.
Version: 1.1.1
Author: WP Darko
Author URI: https://wpdarko.com
License: GPL2
 */

/* Check for the PRO version */
function gdc_free_pro_check()
{
    if (is_plugin_active('grid-shortcodes-pro/gdc_pro.php')) {
        function my_admin_notice()
        {
            echo '<div class="updated">
                <p><strong>PRO</strong> version is activated.</p>
              </div>';
        }
        add_action('admin_notices', 'my_admin_notice');

        deactivate_plugins(__FILE__);
    }
}

add_action('admin_init', 'gdc_free_pro_check');

/* Enqueue styles & scripts */
add_action('wp_enqueue_scripts', 'add_gdc_scripts');
function add_gdc_scripts()
{
    wp_enqueue_style('gdc', plugins_url('css/gdc_custom_style.css', __FILE__));
}

add_action('admin_head', 'gsc_css');

function gsc_css()
{
    $gsc = plugins_url('img/gsc-mce-icon.png', __FILE__);
    echo '
    <style>
        i.mce-i-gsc-icon {
	       background-image: url("'.$gsc.'");
        }
    </style>
    ';
}

// Hooks into the correct filters
function gsc_add_mce_button()
{
    // check user permissions
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }
    // check if WYSIWYG is enabled
    if ('true' == get_user_option('rich_editing')) {
        add_filter('mce_external_plugins', 'gsc_add_tinymce_plugin');
        add_filter('mce_buttons', 'gsc_register_mce_button');
    }
}
add_action('admin_head', 'gsc_add_mce_button');

// Declare script for new button
function gsc_add_tinymce_plugin($plugin_array)
{
    $plugin_array['gsc_mce_button'] = plugins_url('/js/gsc-mce-button.js', __FILE__);

    return $plugin_array;
}

// Register new button in the editor
function gsc_register_mce_button($buttons)
{
    array_push($buttons, 'gsc_mce_button');

    return $buttons;
}

add_shortcode('GDC_row', 'gsc_row_sc');

function gsc_row_sc($atts, $content = null)
{
    $before_column_regex = '/^(\s*(<p>)*(<\/p>)*(<br \/>)*(<br>)*)*(\[GDC_column)/';
    $mid_column_regex = '/(\[\/GDC_column\])(\s*(<p>)*(<\/p>)*(<br \/>)*(<br>)*)*(\[GDC_column)/';
    $last_column_regex = '/(\[\/GDC_column\])(\s*(<p>)*(<\/p>)*(<br \/>)*(<br>)*)*$/';

    $new_content = trim(strstr($content, '[GDC_'));
    $new_content = preg_replace($before_column_regex, '$6', $new_content);
    $new_content = preg_replace($mid_column_regex, '$1$7', $new_content);
    $new_content = preg_replace($last_column_regex, '$1', $new_content);
    $output = '<div class="gdc_row">'.$new_content.'</div>';

    return do_shortcode($output);
}

add_shortcode('GDC_column', 'gsc_column_sc');

function gsc_column_sc($atts, $content = null)
{
    extract(shortcode_atts([
        'size' => '',
    ], $atts));

    $content = '<div class="gdc_column gdc_c'.esc_attr($size).'"><div class="gdc_inner">'.$content.'</div></div>';

    return do_shortcode($content);
}