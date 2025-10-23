<?php
/**
 * Places custom post type
 *
 * @package MyTheme
 */

namespace MyTheme\PostTypes;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Places post type class
 */
class PlacesPostType extends PostTypeBase
{
    /**
     * Initialize post type settings
     *
     * @return void
     */
    protected function init(): void
    {
        $this->postType = 'places';
        
        $this->setLabels(
            __('Place', 'mytheme'),
            __('Places', 'mytheme')
        );

        $this->args = [
            'description'         => __('Places with address and details', 'mytheme'),
            'menu_icon'           => 'dashicons-location',
            'supports'            => ['title', 'editor', 'thumbnail', 'excerpt'],
            'taxonomies'          => ['places_category'],
            'rewrite'             => ['slug' => 'places'],
            'has_archive'         => true,
            'show_in_rest'        => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'menu_position'       => 6,
        ];
    }

    /**
     * Register WordPress hooks
     *
     * @return void
     */
    protected function registerHooks(): void
    {
        parent::registerHooks();
        
        add_action('init', [$this, 'registerTaxonomy']);
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_' . $this->postType, [$this, 'saveMetaBox'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        
        // AJAX handlers for load more functionality.
        add_action('wp_ajax_load_more_places', [$this, 'ajaxLoadMorePlaces']);
        add_action('wp_ajax_nopriv_load_more_places', [$this, 'ajaxLoadMorePlaces']);
        
        // AJAX handlers for frontend editing.
        add_action('wp_ajax_update_place', [$this, 'ajaxUpdatePlace']);
        add_action('wp_ajax_get_place_data', [$this, 'ajaxGetPlaceData']);
    }

    /**
     * Register custom taxonomy for places
     *
     * @return void
     */
    public function registerTaxonomy(): void
    {
        $labels = [
            'name'              => __('Place Categories', 'mytheme'),
            'singular_name'     => __('Place Category', 'mytheme'),
            'search_items'      => __('Search Categories', 'mytheme'),
            'all_items'         => __('All Categories', 'mytheme'),
            'parent_item'       => __('Parent Category', 'mytheme'),
            'parent_item_colon' => __('Parent Category:', 'mytheme'),
            'edit_item'         => __('Edit Category', 'mytheme'),
            'update_item'       => __('Update Category', 'mytheme'),
            'add_new_item'      => __('Add New Category', 'mytheme'),
            'new_item_name'     => __('New Category Name', 'mytheme'),
            'menu_name'         => __('Categories', 'mytheme'),
        ];

        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'places-category'],
            'show_in_rest'      => true,
        ];

        register_taxonomy('places_category', [$this->postType], $args);
    }

    /**
     * Enqueue admin scripts for places post type
     *
     * @param string $hook Current admin page hook.
     * @return void
     */
    public function enqueueAdminScripts(string $hook): void
    {
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        global $post;
        if (!$post || $post->post_type !== $this->postType) {
            return;
        }

        wp_enqueue_style(
            'places-admin',
            get_template_directory_uri() . '/assets/css/places-admin.css',
            ['mytheme-admin'],
            '1.0.0'
        );
    }

    /**
     * Add meta boxes for places
     *
     * @return void
     */
    public function addMetaBoxes(): void
    {
        add_meta_box(
            'places_details',
            __('Place Details', 'mytheme'),
            [$this, 'renderMetaBox'],
            $this->postType,
            'normal',
            'high'
        );
    }

    /**
     * Render meta box content
     *
     * @param \WP_Post $post Post object.
     * @return void
     */
    public function renderMetaBox(\WP_Post $post): void
    {
        wp_nonce_field('places_meta_box', 'places_meta_box_nonce');

        $street = get_post_meta($post->ID, '_places_street', true);
        $number = get_post_meta($post->ID, '_places_number', true);
        $nip = get_post_meta($post->ID, '_places_nip', true);
        $region = get_post_meta($post->ID, '_places_region', true);
        ?>
        <div class="places-meta-fields">
            <div class="places-field-group">
                <h4><?php esc_html_e('Address', 'mytheme'); ?></h4>
                
                <div class="places-field-row">
                    <div class="places-field places-field-wide">
                        <label for="places_street">
                            <?php esc_html_e('Street:', 'mytheme'); ?>
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="places_street" 
                            name="places_street" 
                            value="<?php echo esc_attr($street); ?>" 
                            class="widefat"
                            required
                        />
                    </div>
                    
                    <div class="places-field places-field-narrow">
                        <label for="places_number">
                            <?php esc_html_e('Number:', 'mytheme'); ?>
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="places_number" 
                            name="places_number" 
                            value="<?php echo esc_attr($number); ?>" 
                            class="widefat"
                            required
                        />
                    </div>
                </div>
            </div>
            
            <div class="places-field-group">
                <h4><?php esc_html_e('Additional Information', 'mytheme'); ?></h4>
                
                <div class="places-field-row">
                    <div class="places-field places-field-half">
                        <label for="places_nip">
                            <?php esc_html_e('NIP (Tax ID):', 'mytheme'); ?>
                        </label>
                        <input 
                            type="text" 
                            id="places_nip" 
                            name="places_nip" 
                            value="<?php echo esc_attr($nip); ?>" 
                            class="widefat"
                            pattern="[0-9]{10}"
                            placeholder="0000000000"
                            maxlength="10"
                        />
                        <span class="description">
                            <?php esc_html_e('10-digit tax identification number', 'mytheme'); ?>
                        </span>
                    </div>
                    
                    <div class="places-field places-field-half">
                        <label for="places_region">
                            <?php esc_html_e('Region:', 'mytheme'); ?>
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="places_region" 
                            name="places_region" 
                            value="<?php echo esc_attr($region); ?>" 
                            class="widefat"
                            required
                        />
                    </div>
                </div>
            </div>
            
            <p class="description">
                <span class="required">*</span> 
                <?php esc_html_e('Required fields', 'mytheme'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save meta box data
     *
     * @param int      $postId Post ID.
     * @param \WP_Post $post   Post object.
     * @return void
     */
    public function saveMetaBox(int $postId, \WP_Post $post): void
    {
        // Check nonce.
        if (!isset($_POST['places_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['places_meta_box_nonce'], 'places_meta_box')) {
            return;
        }

        // Check autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions.
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Save street.
        if (isset($_POST['places_street'])) {
            update_post_meta(
                $postId,
                '_places_street',
                sanitize_text_field($_POST['places_street'])
            );
        }

        // Save number.
        if (isset($_POST['places_number'])) {
            update_post_meta(
                $postId,
                '_places_number',
                sanitize_text_field($_POST['places_number'])
            );
        }

        // Save NIP.
        if (isset($_POST['places_nip'])) {
            $nip = sanitize_text_field($_POST['places_nip']);
            // Validate NIP format (10 digits).
            if (empty($nip) || preg_match('/^\d{10}$/', $nip)) {
                update_post_meta($postId, '_places_nip', $nip);
            }
        }

        // Save region.
        if (isset($_POST['places_region'])) {
            update_post_meta(
                $postId,
                '_places_region',
                sanitize_text_field($_POST['places_region'])
            );
        }
    }

    /**
     * Get full address for a place
     *
     * @param int $postId Post ID.
     * @return string
     */
    public static function getFullAddress(int $postId): string
    {
        $street = get_post_meta($postId, '_places_street', true);
        $number = get_post_meta($postId, '_places_number', true);
        
        if (empty($street) || empty($number)) {
            return '';
        }
        
        return sprintf('%s %s', $street, $number);
    }

    /**
     * Get place details
     *
     * @param int $postId Post ID.
     * @return array
     */
    public static function getPlaceDetails(int $postId): array
    {
        return [
            'street' => get_post_meta($postId, '_places_street', true),
            'number' => get_post_meta($postId, '_places_number', true),
            'nip'    => get_post_meta($postId, '_places_nip', true),
            'region' => get_post_meta($postId, '_places_region', true),
        ];
    }

    /**
     * AJAX handler for loading more places
     *
     * @return void
     */
    public function ajaxLoadMorePlaces(): void
    {
        // Verify nonce.
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mytheme-nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'mytheme')]);
            return;
        }

        // Get parameters.
        $paged = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $postsPerPage = isset($_POST['posts_per_page']) ? absint($_POST['posts_per_page']) : get_option('posts_per_page');
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
        $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC';

        // Build query arguments.
        $args = [
            'post_type'      => $this->postType,
            'posts_per_page' => $postsPerPage,
            'paged'          => $paged,
            'post_status'    => 'publish',
            'order'          => $order,
        ];

        // Handle sorting.
        if (in_array($orderby, ['street', 'number', 'region', 'nip'])) {
            $args['meta_key'] = '_places_' . $orderby;
            $args['orderby'] = 'meta_value';
        } else {
            $args['orderby'] = $orderby;
        }

        // Add category filter if provided.
        if (!empty($category)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'places_category',
                    'field'    => 'slug',
                    'terms'    => $category,
                ],
            ];
        }

        // Execute query.
        $query = new \WP_Query($args);

        // Check if posts exist.
        if (!$query->have_posts()) {
            wp_send_json_error(['message' => __('No more places found', 'mytheme')]);
            return;
        }

        // Start output buffering.
        ob_start();

        // Loop through posts and render items.
        while ($query->have_posts()) {
            $query->the_post();
            $this->renderPlaceItem(get_the_ID());
        }

        // Reset post data.
        wp_reset_postdata();

        // Get rendered HTML.
        $html = ob_get_clean();

        // Prepare response data.
        $response = [
            'html'       => $html,
            'has_more'   => $paged < $query->max_num_pages,
            'next_page'  => $paged + 1,
            'max_pages'  => $query->max_num_pages,
            'found_posts' => $query->found_posts,
        ];

        wp_send_json_success($response);
    }

    /**
     * Render single place item HTML as table row
     *
     * @param int $postId Post ID.
     * @return void
     */
    public static function renderPlaceItem(int $postId): void
    {
        $placeDetails = self::getPlaceDetails($postId);
        $fullAddress = self::getFullAddress($postId);
        ?>
        <tr id="post-<?php echo esc_attr($postId); ?>" <?php post_class('places-table-row'); ?>>
            <td class="places-col-name">
                <a href="<?php echo esc_url(get_permalink($postId)); ?>" class="places-name-link">
                    <?php echo esc_html(get_the_title($postId)); ?>
                </a>
            </td>
            <td class="places-col-street">
                <?php echo esc_html($placeDetails['street']); ?>
            </td>
            <td class="places-col-number">
                <?php echo esc_html($placeDetails['number']); ?>
            </td>
            <td class="places-col-region">
                <?php echo esc_html($placeDetails['region']); ?>
            </td>
            <td class="places-col-nip">
                <?php echo !empty($placeDetails['nip']) ? esc_html($placeDetails['nip']) : '—'; ?>
            </td>
            <td class="places-col-actions">
                <a href="<?php echo esc_url(get_permalink($postId)); ?>" class="places-view-btn">
                    <?php esc_html_e('View', 'mytheme'); ?>
                </a>
                <?php if (current_user_can('edit_post', $postId)) : ?>
                    <button type="button" 
                            class="places-edit-btn" 
                            data-post-id="<?php echo esc_attr($postId); ?>"
                            title="<?php esc_attr_e('Edit', 'mytheme'); ?>">
                        <?php esc_html_e('Edit', 'mytheme'); ?>
                    </button>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    /**
     * AJAX handler for getting place data
     *
     * @return void
     */
    public function ajaxGetPlaceData(): void
    {
        // Verify nonce.
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mytheme-nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'mytheme')]);
            return;
        }

        $postId = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

        if (!$postId) {
            wp_send_json_error(['message' => __('Invalid post ID', 'mytheme')]);
            return;
        }

        // Check permissions.
        if (!current_user_can('edit_post', $postId)) {
            wp_send_json_error(['message' => __('You do not have permission to edit this place', 'mytheme')]);
            return;
        }

        $post = get_post($postId);
        if (!$post || $post->post_type !== $this->postType) {
            wp_send_json_error(['message' => __('Place not found', 'mytheme')]);
            return;
        }

        $placeDetails = self::getPlaceDetails($postId);

        $data = [
            'id'     => $postId,
            'title'  => get_the_title($postId),
            'street' => $placeDetails['street'],
            'number' => $placeDetails['number'],
            'region' => $placeDetails['region'],
            'nip'    => $placeDetails['nip'],
        ];

        wp_send_json_success($data);
    }

    /**
     * AJAX handler for updating place
     *
     * @return void
     */
    public function ajaxUpdatePlace(): void
    {
        // Verify nonce.
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mytheme-nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'mytheme')]);
            return;
        }

        $postId = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

        if (!$postId) {
            wp_send_json_error(['message' => __('Invalid post ID', 'mytheme')]);
            return;
        }

        // Check permissions.
        if (!current_user_can('edit_post', $postId)) {
            wp_send_json_error(['message' => __('You do not have permission to edit this place', 'mytheme')]);
            return;
        }

        $post = get_post($postId);
        if (!$post || $post->post_type !== $this->postType) {
            wp_send_json_error(['message' => __('Place not found', 'mytheme')]);
            return;
        }

        // Get and sanitize data.
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $street = isset($_POST['street']) ? sanitize_text_field($_POST['street']) : '';
        $number = isset($_POST['number']) ? sanitize_text_field($_POST['number']) : '';
        $region = isset($_POST['region']) ? sanitize_text_field($_POST['region']) : '';
        $nip = isset($_POST['nip']) ? sanitize_text_field($_POST['nip']) : '';

        // Validate required fields.
        if (empty($title) || empty($street) || empty($number) || empty($region)) {
            wp_send_json_error(['message' => __('Please fill in all required fields', 'mytheme')]);
            return;
        }

        // Validate NIP if provided.
        if (!empty($nip) && !preg_match('/^\d{10}$/', $nip)) {
            wp_send_json_error(['message' => __('NIP must be 10 digits', 'mytheme')]);
            return;
        }

        // Update post title.
        $updated = wp_update_post([
            'ID'         => $postId,
            'post_title' => $title,
        ], true);

        if (is_wp_error($updated)) {
            wp_send_json_error(['message' => $updated->get_error_message()]);
            return;
        }

        // Update meta fields.
        update_post_meta($postId, '_places_street', $street);
        update_post_meta($postId, '_places_number', $number);
        update_post_meta($postId, '_places_region', $region);
        update_post_meta($postId, '_places_nip', $nip);

        // Get updated data.
        $placeDetails = self::getPlaceDetails($postId);

        wp_send_json_success([
            'message' => __('Place updated successfully', 'mytheme'),
            'data'    => [
                'id'     => $postId,
                'title'  => get_the_title($postId),
                'street' => $placeDetails['street'],
                'number' => $placeDetails['number'],
                'region' => $placeDetails['region'],
                'nip'    => $placeDetails['nip'],
            ],
        ]);
    }
}

