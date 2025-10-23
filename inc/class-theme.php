<?php
/**
 * Main theme class
 *
 * @package MyTheme
 */

namespace MyTheme;

use MyTheme\PostTypes\PlacesPostType;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main theme class
 */
class Theme
{
    /**
     * Theme instance
     *
     * @var Theme
     */
    private static ?Theme $instance = null;

    /**
     * Custom post types
     *
     * @var array
     */
    private array $postTypes = [];

    /**
     * Get theme instance (Singleton pattern)
     *
     * @return Theme
     */
    public static function getInstance(): Theme
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->initPostTypes();
        $this->registerHooks();
    }

    /**
     * Initialize custom post types
     *
     * @return void
     */
    private function initPostTypes(): void
    {
        // Register places post type.
        $this->postTypes['places'] = new PlacesPostType();

        // Add more custom post types here.
    }

    /**
     * Register WordPress hooks
     *
     * @return void
     */
    private function registerHooks(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }

    /**
     * Enqueue frontend scripts and styles
     *
     * @return void
     */
    public function enqueueScripts(): void
    {
        // Enqueue jQuery 3.7.1.
        wp_deregister_script('jquery');
        wp_register_script(
            'jquery',
            'https://code.jquery.com/jquery-3.7.1.min.js',
            [],
            '3.7.1',
            true
        );
        wp_enqueue_script('jquery');

        // Enqueue theme main script.
        wp_enqueue_script(
            'mytheme-main',
            get_template_directory_uri() . '/assets/js/main.js',
            ['jquery'],
            '1.0.0',
            true
        );

        // Enqueue places styles and scripts on places pages.
        if (is_post_type_archive('places') || 
            is_singular('places') || 
            is_tax('places_category')) {
            wp_enqueue_style(
                'mytheme-places',
                get_template_directory_uri() . '/assets/css/places.css',
                [],
                '1.0.0'
            );

            // Enqueue places script for load more functionality.
            if (is_post_type_archive('places') || is_tax('places_category')) {
                wp_enqueue_style(
                    'mytheme-places-modal',
                    get_template_directory_uri() . '/assets/css/places-modal.css',
                    ['mytheme-places'],
                    '1.0.0'
                );

                wp_enqueue_script(
                    'mytheme-places',
                    get_template_directory_uri() . '/assets/js/places.js',
                    ['jquery', 'mytheme-main'],
                    '1.0.0',
                    true
                );
            }
        }

        // Localize script for AJAX.
        wp_localize_script(
            'mytheme-main',
            'mythemeData',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('mytheme-nonce'),
                'i18n'    => [
                    'editPlace' => __('Edit Place', 'mytheme'),
                    'name'      => __('Name', 'mytheme'),
                    'street'    => __('Street', 'mytheme'),
                    'number'    => __('Number', 'mytheme'),
                    'region'    => __('Region', 'mytheme'),
                    'nip'       => __('NIP', 'mytheme'),
                    'save'      => __('Save', 'mytheme'),
                    'saving'    => __('Saving...', 'mytheme'),
                    'cancel'    => __('Cancel', 'mytheme'),
                ],
            ]
        );
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @return void
     */
    public function enqueueAdminScripts(): void
    {
        // Enqueue admin styles for custom post types.
        wp_enqueue_style(
            'mytheme-admin',
            get_template_directory_uri() . '/assets/css/admin.css',
            [],
            '1.0.0'
        );

        // Enqueue admin script.
        wp_enqueue_script(
            'mytheme-admin',
            get_template_directory_uri() . '/assets/js/admin.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }

    /**
     * Get registered post types
     *
     * @return array
     */
    public function getPostTypes(): array
    {
        return $this->postTypes;
    }
}

