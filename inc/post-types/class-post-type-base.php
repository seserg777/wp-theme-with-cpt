<?php
/**
 * Base class for custom post types
 *
 * @package MyTheme
 */

namespace MyTheme\PostTypes;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract base class for custom post types
 */
abstract class PostTypeBase
{
    /**
     * Post type key
     *
     * @var string
     */
    protected string $postType;

    /**
     * Post type labels
     *
     * @var array
     */
    protected array $labels = [];

    /**
     * Post type arguments
     *
     * @var array
     */
    protected array $args = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
        $this->registerHooks();
    }

    /**
     * Initialize post type settings
     *
     * @return void
     */
    abstract protected function init(): void;

    /**
     * Register WordPress hooks
     *
     * @return void
     */
    protected function registerHooks(): void
    {
        add_action('init', [$this, 'register']);
    }

    /**
     * Register the custom post type
     *
     * @return void
     */
    public function register(): void
    {
        if (empty($this->postType)) {
            return;
        }

        $args = wp_parse_args(
            $this->args,
            [
                'labels'              => $this->labels,
                'public'              => true,
                'publicly_queryable'  => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'query_var'           => true,
                'rewrite'             => ['slug' => $this->postType],
                'capability_type'     => 'post',
                'has_archive'         => true,
                'hierarchical'        => false,
                'menu_position'       => null,
                'show_in_rest'        => true,
                'supports'            => ['title', 'editor', 'thumbnail', 'excerpt'],
            ]
        );

        register_post_type($this->postType, $args);
    }

    /**
     * Get post type key
     *
     * @return string
     */
    public function getPostType(): string
    {
        return $this->postType;
    }

    /**
     * Set post type labels
     *
     * @param string $singular Singular name.
     * @param string $plural   Plural name.
     * @return void
     */
    protected function setLabels(string $singular, string $plural): void
    {
        $this->labels = [
            'name'                  => $plural,
            'singular_name'         => $singular,
            'menu_name'             => $plural,
            'name_admin_bar'        => $singular,
            'archives'              => sprintf(__('%s Archives', 'mytheme'), $singular),
            'attributes'            => sprintf(__('%s Attributes', 'mytheme'), $singular),
            'parent_item_colon'     => sprintf(__('Parent %s:', 'mytheme'), $singular),
            'all_items'             => sprintf(__('All %s', 'mytheme'), $plural),
            'add_new_item'          => sprintf(__('Add New %s', 'mytheme'), $singular),
            'add_new'               => __('Add New', 'mytheme'),
            'new_item'              => sprintf(__('New %s', 'mytheme'), $singular),
            'edit_item'             => sprintf(__('Edit %s', 'mytheme'), $singular),
            'update_item'           => sprintf(__('Update %s', 'mytheme'), $singular),
            'view_item'             => sprintf(__('View %s', 'mytheme'), $singular),
            'view_items'            => sprintf(__('View %s', 'mytheme'), $plural),
            'search_items'          => sprintf(__('Search %s', 'mytheme'), $plural),
            'not_found'             => __('Not found', 'mytheme'),
            'not_found_in_trash'    => __('Not found in Trash', 'mytheme'),
            'featured_image'        => __('Featured Image', 'mytheme'),
            'set_featured_image'    => __('Set featured image', 'mytheme'),
            'remove_featured_image' => __('Remove featured image', 'mytheme'),
            'use_featured_image'    => __('Use as featured image', 'mytheme'),
            'insert_into_item'      => sprintf(__('Insert into %s', 'mytheme'), strtolower($singular)),
            'uploaded_to_this_item' => sprintf(__('Uploaded to this %s', 'mytheme'), strtolower($singular)),
            'items_list'            => sprintf(__('%s list', 'mytheme'), $plural),
            'items_list_navigation' => sprintf(__('%s list navigation', 'mytheme'), $plural),
            'filter_items_list'     => sprintf(__('Filter %s list', 'mytheme'), strtolower($plural)),
        ];
    }
}

