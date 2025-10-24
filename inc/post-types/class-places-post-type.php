<?php
/**
 * Places custom post type
 *
 * @package MyTheme
 * @author Serhii Soloviov <seserg777@gmail.com>
 * @version 1.0.0
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
            'supports'            => ['title', 'thumbnail'],
            'rewrite'             => [
                'slug'       => 'places',
                'with_front' => false,
                'pages'      => true,
                'feeds'      => true,
            ],
            'has_archive'         => true,
            'show_in_rest'        => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'menu_position'       => 6,
            'show_in_nav_menus'   => true,
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
        
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_' . $this->postType, [$this, 'saveMetaBox'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        
        // AJAX handlers for load more functionality.
        add_action('wp_ajax_load_more_places', [$this, 'ajaxLoadMorePlaces']);
        add_action('wp_ajax_nopriv_load_more_places', [$this, 'ajaxLoadMorePlaces']);
        
        // AJAX handlers for frontend editing.
        add_action('wp_ajax_update_place', [$this, 'ajaxUpdatePlace']);
        
        // Modify main query for archive sorting.
        add_action('pre_get_posts', [$this, 'modifyArchiveQuery']);
        
        // Flush rewrite rules on theme activation.
        add_action('after_switch_theme', [$this, 'flushRewriteRules']);
        
        // Flush rewrite rules on init to ensure they are registered.
        add_action('init', [$this, 'maybeFlushRewriteRules'], 20);
    }

    /**
     * Modify main query for Places archive sorting
     *
     * @param \WP_Query $query The WP_Query instance.
     * @return void
     */
    public function modifyArchiveQuery(\WP_Query $query): void
    {
        // Only modify main query on frontend for Places archive.
        if (is_admin() || ! $query->is_main_query()) {
            return;
        }
        
        // Check if this is Places archive.
        if ( ! is_post_type_archive($this->postType)) {
            return;
        }
        
        // Get sorting parameters from URL.
        $order_by = get_query_var('orderby');
        $order    = strtoupper(get_query_var('order', 'DESC'));
        
        // Validate order parameter.
        if ( ! in_array($order, array( 'ASC', 'DESC' ), true)) {
            $order = 'DESC';
        }
        
        // Handle sorting for meta fields.
        if (in_array($order_by, array( 'address', 'region', 'nip' ), true)) {
            $meta_key  = '_places_' . $order_by;
            $post_type = $this->postType;
            
            // Set custom orderby flag.
            $query->set('orderby', 'meta_value_custom');
            $query->set('meta_key_custom', $meta_key);
            $query->set('order_custom', $order);
            
            // Create filter to modify JOIN - use LEFT JOIN to include posts without meta.
            $filter_join = function ( $join, $wp_query ) use ( $meta_key, $post_type, $query ) {
                global $wpdb;
                
                // Only modify if this is the same query.
                if ($wp_query === $query && $wp_query->get('post_type') === $post_type && $wp_query->get('orderby') === 'meta_value_custom') {
                    // Add LEFT JOIN for meta table with prepared statement.
                    $join .= $wpdb->prepare(
                        " LEFT JOIN {$wpdb->postmeta} AS mt_sort ON ({$wpdb->posts}.ID = mt_sort.post_id AND mt_sort.meta_key = %s)",
                        $meta_key
                    );
                }
                
                return $join;
            };
            
            // Create filter to sort with proper empty value handling.
            $filter_orderby = function ( $orderby_statement, $wp_query ) use ( $meta_key, $order, $post_type, $query ) {
                global $wpdb;
                
                // Only modify if this is the same query.
                if ($wp_query === $query && $wp_query->get('post_type') === $post_type && $wp_query->get('orderby') === 'meta_value_custom') {
                    // For ASC: non-empty first, empty last (0 < 1).
                    // For DESC: empty first, non-empty last (1 > 0).
                    $empty_order       = 'ASC' === $order ? 'ASC' : 'DESC';
                    $orderby_statement = "CASE WHEN mt_sort.meta_value IS NULL OR mt_sort.meta_value = '' THEN 1 ELSE 0 END {$empty_order}, mt_sort.meta_value {$order}, {$wpdb->posts}.post_date DESC";
                }
                
                return $orderby_statement;
            };
            
            // Add filters.
            add_filter('posts_join', $filter_join, 10, 2);
            add_filter('posts_orderby', $filter_orderby, 10, 2);
            
            // Remove filters after query execution.
            add_filter('posts_results', function ( $posts, $wp_query ) use ( $filter_join, $filter_orderby, $query ) {
                if ($wp_query === $query) {
                    remove_filter('posts_join', $filter_join, 10);
                    remove_filter('posts_orderby', $filter_orderby, 10);
                }
                return $posts;
            }, 10, 2);
        }
    }

    /**
     * Flush rewrite rules
     *
     * @return void
     */
    public function flushRewriteRules(): void
    {
        flush_rewrite_rules();
    }

    /**
     * Maybe flush rewrite rules if needed
     *
     * @return void
     */
    public function maybeFlushRewriteRules(): void
    {
        $rules = get_option('rewrite_rules');
        if (!isset($rules['^places/([^/]+)/edit/?$'])) {
            flush_rewrite_rules();
        }
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
            get_template_directory_uri() . '/dist/css/places-admin.min.css',
            array( 'mytheme-admin' ),
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

        $address = get_post_meta($post->ID, '_places_address', true);
        $nip = get_post_meta($post->ID, '_places_nip', true);
        $region = get_post_meta($post->ID, '_places_region', true);
        ?>
        <div class="places-meta-fields">
            <div class="places-field-group">
                <h4><?php esc_html_e('Address', 'mytheme'); ?></h4>
                
                <div class="places-field-row">
                    <div class="places-field places-field-full">
                        <label for="places_address">
                            <?php esc_html_e('Address:', 'mytheme'); ?>
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="places_address" 
                            name="places_address" 
                            value="<?php echo esc_attr($address); ?>" 
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

        // Save address.
        if (isset($_POST['places_address'])) {
            update_post_meta(
                $postId,
                '_places_address',
                sanitize_text_field($_POST['places_address'])
            );
        }

        // Save NIP.
        if (isset($_POST['places_nip'])) {
            $nip = sanitize_text_field($_POST['places_nip']);
            // Remove all non-digit characters.
            $nip = preg_replace('/[^0-9]/', '', $nip);
            // Save NIP (empty or validated 10 digits).
            update_post_meta($postId, '_places_nip', $nip);
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
     * Get place details
     *
     * @param int $postId Post ID.
     * @return array
     */
    public static function getPlaceDetails(int $postId): array
    {
        return [
            'address' => get_post_meta($postId, '_places_address', true),
            'nip'     => get_post_meta($postId, '_places_nip', true),
            'region'  => get_post_meta($postId, '_places_region', true),
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
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'mytheme-nonce')) {
            wp_send_json_error(array( 'message' => __('Security check failed', 'mytheme') ));
            return;
        }

        // Get parameters.
        $paged          = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $posts_per_page = isset($_POST['posts_per_page']) ? absint($_POST['posts_per_page']) : get_option('posts_per_page');
        $order_by       = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
        $order          = isset($_POST['order']) ? strtoupper(sanitize_text_field($_POST['order'])) : 'DESC';
        
        // Validate order parameter.
        if ( ! in_array($order, array( 'ASC', 'DESC' ), true)) {
            $order = 'DESC';
        }

        // Build query arguments.
        $args = array(
            'post_type'      => $this->postType,
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'post_status'    => 'publish',
            'order'          => $order,
        );

        // Handle sorting.
        $filter_join    = null;
        $filter_orderby = null;
        
        if (in_array($order_by, array( 'address', 'region', 'nip' ), true)) {
            $meta_key  = '_places_' . $order_by;
            $post_type = $this->postType;
            
            // Set custom orderby flag.
            $args['orderby']         = 'meta_value_custom';
            $args['meta_key_custom'] = $meta_key;
            $args['order_custom']    = $order;
            
            // Create filter to modify JOIN - use LEFT JOIN to include posts without meta.
            $filter_join = function ( $join, $query ) use ( $meta_key, $post_type ) {
                global $wpdb;
                
                // Check if this is our query.
                if ($query->get('post_type') === $post_type && $query->get('orderby') === 'meta_value_custom') {
                    // Add LEFT JOIN for meta table with prepared statement.
                    $join .= $wpdb->prepare(
                        " LEFT JOIN {$wpdb->postmeta} AS mt_sort ON ({$wpdb->posts}.ID = mt_sort.post_id AND mt_sort.meta_key = %s)",
                        $meta_key
                    );
                }
                
                return $join;
            };
            
            // Create filter to sort with proper empty value handling.
            $filter_orderby = function ( $orderby_statement, $query ) use ( $meta_key, $order, $post_type ) {
                global $wpdb;
                
                // Check if this is our query.
                if ($query->get('post_type') === $post_type && $query->get('orderby') === 'meta_value_custom') {
                    // For ASC: non-empty first, empty last (0 < 1).
                    // For DESC: empty first, non-empty last (1 > 0).
                    $empty_order       = 'ASC' === $order ? 'ASC' : 'DESC';
                    $orderby_statement = "CASE WHEN mt_sort.meta_value IS NULL OR mt_sort.meta_value = '' THEN 1 ELSE 0 END {$empty_order}, mt_sort.meta_value {$order}, {$wpdb->posts}.post_date DESC";
                }
                
                return $orderby_statement;
            };
            
            // Add filters.
            add_filter('posts_join', $filter_join, 10, 2);
            add_filter('posts_orderby', $filter_orderby, 10, 2);
        } else {
            $args['orderby'] = $order_by;
        }

        // Execute query.
        $query = new \WP_Query($args);
        
        // Remove filters after query execution.
        if ($filter_join) {
            remove_filter('posts_join', $filter_join, 10);
        }
        if ($filter_orderby) {
            remove_filter('posts_orderby', $filter_orderby, 10);
        }

        // Check if posts exist.
        if ( ! $query->have_posts()) {
            wp_send_json_error(array( 'message' => __('No more places found', 'mytheme') ));
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
        $response = array(
            'html'        => $html,
            'has_more'    => $paged < $query->max_num_pages,
            'next_page'   => $paged + 1,
            'max_pages'   => $query->max_num_pages,
            'found_posts' => $query->found_posts,
        );

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
        ?>
        <tr id="post-<?php echo esc_attr($postId); ?>" <?php post_class('places-table-row'); ?>>
            <td class="places-col-name">
                <a href="<?php echo esc_url(get_permalink($postId)); ?>" class="places-name-link">
                    <?php echo esc_html(get_the_title($postId)); ?>
                </a>
            </td>
            <td class="places-col-address">
                <?php echo esc_html($placeDetails['address']); ?>
            </td>
            <td class="places-col-region">
                <?php echo esc_html($placeDetails['region']); ?>
            </td>
            <td class="places-col-nip">
                <?php echo !empty($placeDetails['nip']) ? esc_html($placeDetails['nip']) : '—'; ?>
            </td>
            <td class="places-col-actions">
                <div class="places-actions-wrapper">
                    <a href="<?php echo esc_url(get_permalink($postId)); ?>" class="places-action-btn places-view-btn" title="<?php esc_attr_e('View', 'mytheme'); ?>">
                        <span class="dashicons dashicons-visibility"></span>
                        <span class="btn-text"><?php esc_html_e('View', 'mytheme'); ?></span>
                    </a>
                    <?php 
                    $post_slug = get_post_field('post_name', $postId);
                    $edit_url  = home_url('/places/' . $post_slug . '/edit/');
                    $can_edit  = current_user_can('edit_post', $postId);
                    ?>
                    <a href="<?php echo $can_edit ? esc_url($edit_url) : '#'; ?>" 
                       class="places-action-btn places-edit-btn<?php echo ! $can_edit ? ' disabled' : ''; ?>" 
                       title="<?php esc_attr_e('Edit', 'mytheme'); ?>"
                       <?php echo ! $can_edit ? 'data-auth-required="true"' : ''; ?>>
                        <span class="dashicons dashicons-edit"></span>
                        <span class="btn-text"><?php esc_html_e('Edit', 'mytheme'); ?></span>
                    </a>
                </div>
            </td>
        </tr>
        <?php
    }


    /**
     * AJAX handler for updating place
     *
     * @return void
     */
    public function ajaxUpdatePlace(): void
    {
        // Debug: Log the request
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ajaxUpdatePlace called with POST data: ' . print_r($_POST, true));
        }
        
        // Verify nonce.
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mytheme-nonce')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Nonce verification failed');
            }
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
        $address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';
        $region = isset($_POST['region']) ? sanitize_text_field($_POST['region']) : '';
        $nip = isset($_POST['nip']) ? sanitize_text_field($_POST['nip']) : '';
        
        // Clean NIP - remove all non-digit characters.
        $nip = preg_replace('/[^0-9]/', '', $nip);

        // Validate required fields.
        if (empty($title) || empty($address) || empty($region)) {
            wp_send_json_error(['message' => __('Please fill in all required fields', 'mytheme')]);
            return;
        }

        // Validate NIP if provided (must be empty or exactly 10 digits after cleaning).
        if (!empty($nip) && strlen($nip) !== 10) {
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
        update_post_meta($postId, '_places_address', $address);
        update_post_meta($postId, '_places_region', $region);
        update_post_meta($postId, '_places_nip', $nip);

        // Get updated data.
        $placeDetails = self::getPlaceDetails($postId);

        wp_send_json_success([
            'message' => __('Place updated successfully', 'mytheme'),
            'data'    => [
                'id'      => $postId,
                'title'   => get_the_title($postId),
                'address' => $placeDetails['address'],
                'region'  => $placeDetails['region'],
                'nip'     => $placeDetails['nip'],
            ],
        ]);
    }
}

