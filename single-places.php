<?php
/**
 * Single place template
 *
 * @package MyTheme
 */

use MyTheme\PostTypes\PlacesPostType;

get_header(); ?>

<main id="main" class="site-main places-single">
    <?php
    while (have_posts()) :
        the_post();
        
        $placeDetails = PlacesPostType::getPlaceDetails(get_the_ID());
        $fullAddress = PlacesPostType::getFullAddress(get_the_ID());
        ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                
                <?php if (has_post_thumbnail()) : ?>
                    <div class="places-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
            </header>
            
            <div class="places-info">
                <?php if (!empty($fullAddress)) : ?>
                    <div class="places-info-item places-address">
                        <span class="places-icon dashicons dashicons-location"></span>
                        <div class="places-info-content">
                            <strong><?php esc_html_e('Address:', 'mytheme'); ?></strong>
                            <p><?php echo esc_html($fullAddress); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($placeDetails['region'])) : ?>
                    <div class="places-info-item places-region">
                        <span class="places-icon dashicons dashicons-admin-site"></span>
                        <div class="places-info-content">
                            <strong><?php esc_html_e('Region:', 'mytheme'); ?></strong>
                            <p><?php echo esc_html($placeDetails['region']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($placeDetails['nip'])) : ?>
                    <div class="places-info-item places-nip">
                        <span class="places-icon dashicons dashicons-id"></span>
                        <div class="places-info-content">
                            <strong><?php esc_html_e('NIP (Tax ID):', 'mytheme'); ?></strong>
                            <p><?php echo esc_html($placeDetails['nip']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php
                $categories = get_the_terms(get_the_ID(), 'places_category');
                if ($categories && !is_wp_error($categories)) :
                    ?>
                    <div class="places-info-item places-categories">
                        <span class="places-icon dashicons dashicons-category"></span>
                        <div class="places-info-content">
                            <strong><?php esc_html_e('Categories:', 'mytheme'); ?></strong>
                            <p>
                                <?php
                                $categoryLinks = array_map(function ($cat) {
                                    return sprintf(
                                        '<a href="%s">%s</a>',
                                        esc_url(get_term_link($cat)),
                                        esc_html($cat->name)
                                    );
                                }, $categories);
                                echo implode(', ', $categoryLinks);
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (get_the_content()) : ?>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        </article>
        
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>

