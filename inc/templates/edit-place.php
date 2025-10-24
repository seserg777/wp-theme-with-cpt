<?php
/**
 * Template for editing places
 *
 * @package MyTheme
 * @author Serhii Soloviov <seserg777@gmail.com>
 * @version 1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Check if user can edit posts.
if (!current_user_can('edit_posts')) {
    wp_die(__('You do not have permission to edit places.', 'mytheme'));
}

// Get place slug from URL parameter.
$place_slug = get_query_var('place_slug');

// Debug: Log the received slug
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log("Edit place page - received slug: {$place_slug}");
    error_log("Edit place page - query vars: " . print_r($wp_query->query_vars, true));
}

if (!$place_slug) {
    wp_die(__('Invalid place slug.', 'mytheme'));
}

// Find place by slug.
$places = get_posts([
    'name'           => $place_slug,
    'post_type'      => 'places',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
]);

if (empty($places)) {
    wp_die(__('Place not found.', 'mytheme'));
}

$place = $places[0];
$place_id = $place->ID;

// Handle form submission
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_place') {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mytheme-nonce')) {
        wp_die(__('Security check failed', 'mytheme'));
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $place_id)) {
        wp_die(__('You do not have permission to edit this place', 'mytheme'));
    }
    
    // Update post title
    $post_data = [
        'ID' => $place_id,
        'post_title' => sanitize_text_field($_POST['title']),
    ];
    wp_update_post($post_data);
    
    // Clean NIP - remove all non-digit characters
    $nip = isset($_POST['nip']) ? sanitize_text_field($_POST['nip']) : '';
    $nip = preg_replace('/[^0-9]/', '', $nip);
    
    // Update meta fields
    update_post_meta($place_id, '_places_address', sanitize_text_field($_POST['address']));
    update_post_meta($place_id, '_places_region', sanitize_text_field($_POST['region']));
    update_post_meta($place_id, '_places_nip', $nip);
    
    $success_message = __('Place updated successfully!', 'mytheme');
    
    // Refresh place data after update
    $place = get_post($place_id);
    $places = [$place]; // Update the places array
}

get_header();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0"><?php esc_html_e('Edit Place', 'mytheme'); ?></h2>
                </div>
                
                <?php if ($success_message) : ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo esc_html($success_message); ?>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <form id="edit-place-form" class="places-edit-form" method="post">
                        <input type="hidden" name="post_id" value="<?php echo esc_attr($place_id); ?>" />
                        <input type="hidden" name="action" value="update_place" />
                        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('mytheme-nonce')); ?>" />
                        
                        <div class="mb-3">
                            <label for="edit-title" class="form-label">
                                <?php esc_html_e('Name', 'mytheme'); ?> <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit-title" 
                                   name="title" 
                                   value="<?php echo esc_attr($place->post_title); ?>" 
                                   required />
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-address" class="form-label">
                                <?php esc_html_e('Address', 'mytheme'); ?> <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit-address" 
                                   name="address" 
                                   value="<?php echo esc_attr(get_post_meta($place_id, '_places_address', true)); ?>"
                                   required />
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-region" class="form-label">
                                        <?php esc_html_e('Region', 'mytheme'); ?> <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="edit-region" 
                                           name="region" 
                                           value="<?php echo esc_attr(get_post_meta($place_id, '_places_region', true)); ?>" 
                                           required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit-nip" class="form-label">
                                        <?php esc_html_e('NIP', 'mytheme'); ?>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="edit-nip" 
                                           name="nip" 
                                           value="<?php echo esc_attr(get_post_meta($place_id, '_places_nip', true)); ?>" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo esc_url(get_post_type_archive_link('places')); ?>" 
                               class="btn btn-secondary">
                                <?php esc_html_e('Back to Places', 'mytheme'); ?>
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <span class="btn-text"><?php esc_html_e('Save Changes', 'mytheme'); ?></span>
                                    <span class="btn-loading d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        <?php esc_html_e('Saving...', 'mytheme'); ?>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#edit-place-form').on('submit', function() {
        const $submitBtn = $(this).find('button[type="submit"]');
        const $btnText = $submitBtn.find('.button-text');
        const $btnLoading = $submitBtn.find('.spinner-border');
        
        // Show loading state
        $btnText.text('<?php esc_js_e('Saving...', 'mytheme'); ?>');
        $btnLoading.removeClass('d-none');
        $submitBtn.prop('disabled', true);
    });
});
</script>

<?php get_footer(); ?>
