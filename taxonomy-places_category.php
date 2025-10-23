<?php
/**
 * Places category taxonomy template
 *
 * @package MyTheme
 */

use MyTheme\PostTypes\PlacesPostType;

get_header(); ?>

<main id="main" class="site-main places-category-archive">
    <header class="page-header">
        <h1 class="page-title">
            <?php single_term_title(); ?>
        </h1>
        <?php
        $termDescription = term_description();
        if (!empty($termDescription)) :
            ?>
            <div class="archive-description"><?php echo wp_kses_post($termDescription); ?></div>
        <?php endif; ?>
    </header>
    
    <?php if (have_posts()) : ?>
        <div class="places-table-wrapper">
            <table class="places-table">
                <thead>
                    <tr>
                        <th class="places-col-name sortable" data-sort="title">
                            <?php esc_html_e('Name', 'mytheme'); ?>
                            <span class="sort-icon"></span>
                        </th>
                        <th class="places-col-street sortable" data-sort="street">
                            <?php esc_html_e('Street', 'mytheme'); ?>
                            <span class="sort-icon"></span>
                        </th>
                        <th class="places-col-number sortable" data-sort="number">
                            <?php esc_html_e('Number', 'mytheme'); ?>
                            <span class="sort-icon"></span>
                        </th>
                        <th class="places-col-region sortable" data-sort="region">
                            <?php esc_html_e('Region', 'mytheme'); ?>
                            <span class="sort-icon"></span>
                        </th>
                        <th class="places-col-nip sortable" data-sort="nip">
                            <?php esc_html_e('NIP', 'mytheme'); ?>
                            <span class="sort-icon"></span>
                        </th>
                        <th class="places-col-actions"><?php esc_html_e('Actions', 'mytheme'); ?></th>
                    </tr>
                </thead>
                <tbody class="places-table-body">
                    <?php
                    while (have_posts()) :
                        the_post();
                        PlacesPostType::renderPlaceItem(get_the_ID());
                    endwhile;
                    ?>
                </tbody>
            </table>
        </div>
        
        <?php
        global $wp_query;
        $max_pages = $wp_query->max_num_pages;
        $current_page = max(1, get_query_var('paged'));
        $has_more = $current_page < $max_pages;
        $current_term = get_queried_object();
        $category_slug = $current_term ? $current_term->slug : '';
        ?>
        
        <div class="places-load-more-container" 
             data-current-page="<?php echo esc_attr($current_page); ?>"
             data-has-more="<?php echo esc_attr($has_more ? 'true' : 'false'); ?>"
             data-posts-per-page="<?php echo esc_attr(get_option('posts_per_page')); ?>"
             data-category="<?php echo esc_attr($category_slug); ?>"
             data-orderby="date"
             data-order="DESC">
            
            <?php if ($has_more) : ?>
                <button type="button" class="places-load-more-btn">
                    <span class="btn-text"><?php esc_html_e('Load More', 'mytheme'); ?></span>
                </button>
                <div class="places-loader" style="display: none;">
                    <span class="spinner"></span>
                    <span class="loader-text"><?php esc_html_e('Loading...', 'mytheme'); ?></span>
                </div>
            <?php else : ?>
                <p class="places-no-more"><?php esc_html_e('All places loaded', 'mytheme'); ?></p>
            <?php endif; ?>
        </div>
        
    <?php else : ?>
        <p><?php esc_html_e('No places found in this category.', 'mytheme'); ?></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>

