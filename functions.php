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

// Initialize theme.
MyTheme\Theme::getInstance();

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
    // Enqueue theme stylesheet.
    wp_enqueue_style('mytheme-style', get_stylesheet_uri(), [], '1.0.0');
}
add_action('wp_enqueue_scripts', 'mytheme_scripts');

