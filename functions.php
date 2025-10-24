<?php
/**
 * Theme functions and definitions
 *
 * @package MyTheme
 * @author Serhii Soloviov <seserg777@gmail.com>
 * @version 1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Require autoloader.
require_once get_template_directory() . '/inc/class-autoloader.php';

// Initialize autoloader.
new MyTheme\Autoloader();

// Explicitly require necessary files.
require_once get_template_directory() . '/inc/class-theme.php';
require_once get_template_directory() . '/inc/class-templates.php';
require_once get_template_directory() . '/inc/post-types/class-post-type-base.php';
require_once get_template_directory() . '/inc/post-types/class-places-post-type.php';

// Initialize theme.
MyTheme\Theme::getInstance();

// Initialize templates loader.
new MyTheme\Templates();

/**
 * Theme setup
 */
function mytheme_setup()
{
    // Add theme support for various features.
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ]);
    
    // Register navigation menus.
    register_nav_menus([
        'primary' => esc_html__('Primary Menu', 'mytheme'),
        'footer'  => esc_html__('Footer Menu', 'mytheme'),
    ]);

    // Add support for custom logo.
    add_theme_support('custom-logo', [
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
}
add_action('after_setup_theme', 'mytheme_setup');

/**
 * Enqueue scripts and styles
 */
function mytheme_scripts()
{
    // Enqueue Bootstrap CSS.
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', [], '5.3.0');
    
    // Enqueue theme stylesheet.
    wp_enqueue_style('mytheme-style', get_stylesheet_uri(), ['bootstrap'], '1.0.0');
    
    // Enqueue Bootstrap JS.
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [], '5.3.0', true);
}
add_action('wp_enqueue_scripts', 'mytheme_scripts');

/**
 * Load theme textdomain
 */
function mytheme_load_textdomain()
{
    load_theme_textdomain('mytheme', get_template_directory() . '/languages');
}
add_action('init', 'mytheme_load_textdomain');

/**
 * Add Places archive link to menu
 */
function mytheme_add_places_to_menu($items, $args)
{
    // Only add to primary menu
    if ($args->theme_location === 'primary') {
        $places_link = '<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-places">
            <a href="' . esc_url(get_post_type_archive_link('places')) . '" class="nav-link">
                📍 ' . __('Places', 'mytheme') . '
            </a>
        </li>';
        
        // Add after the last item
        $items .= $places_link;
    }
    
    return $items;
}
add_filter('wp_nav_menu_items', 'mytheme_add_places_to_menu', 10, 2);

/**
 * Add rewrite rules for edit place page
 */
function mytheme_add_edit_place_rewrite_rules()
{
    add_rewrite_rule(
        '^places/([^/]+)/edit/?$',
        'index.php?edit_place=1&place_slug=$matches[1]',
        'top'
    );
}
add_action('init', 'mytheme_add_edit_place_rewrite_rules');

/**
 * Flush rewrite rules on theme activation
 */
function mytheme_flush_rewrite_rules()
{
    mytheme_add_edit_place_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'mytheme_flush_rewrite_rules');

/**
 * Force flush rewrite rules (for debugging)
 */
function mytheme_force_flush_rewrite_rules()
{
    if (current_user_can('manage_options') && isset($_GET['flush_rewrite_rules'])) {
        mytheme_add_edit_place_rewrite_rules();
        flush_rewrite_rules();
        wp_die('Rewrite rules flushed! <a href="' . home_url() . '">Go back</a>');
    }
}
add_action('init', 'mytheme_force_flush_rewrite_rules');

/**
 * Add query vars for edit place page
 */
function mytheme_add_edit_place_query_vars($vars)
{
    $vars[] = 'edit_place';
    $vars[] = 'place_slug';
    return $vars;
}
add_filter('query_vars', 'mytheme_add_edit_place_query_vars');

/**
 * Simple approach: Add Places link via Custom Links
 */
function mytheme_add_places_to_custom_links()
{
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Add Places link to Custom Links when the page loads
        setTimeout(function() {
            if ($('#custom-links-menu-items').length && $('.places-link-added').length === 0) {
                var placesLink = '<li class="places-link-added"><label><input type="checkbox" value="places-archive" data-type="custom" data-object="places-archive"> <?php esc_js_e("Places Archive", "mytheme"); ?></label></li>';
                $('#custom-links-menu-items ul').append(placesLink);
            }
        }, 500);
    });
    </script>
    <?php
}
add_action('admin_footer-nav-menus.php', 'mytheme_add_places_to_custom_links');


