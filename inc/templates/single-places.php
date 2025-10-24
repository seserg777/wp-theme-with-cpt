<?php
/**
 * Single place template
 *
 * @package MyTheme
 */

use MyTheme\PostTypes\PlacesPostType;

get_header(); ?>

<main id="main" class="site-main places-single">
    <div class="container">
        <?php
        while (have_posts()) :
            the_post();

            $placeDetails = PlacesPostType::getPlaceDetails(get_the_ID());
            $fullAddress = PlacesPostType::getFullAddress(get_the_ID());
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <div class="places-details-wrapper">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="places-thumbnail">
                            <?php the_post_thumbnail('large'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="places-details-table-wrapper">
                        <table class="places-details-table">
                            <tbody>
                                <tr>
                                    <th scope="row">
                                        <span class="dashicons dashicons-location"></span>
                                        <?php esc_html_e('Street', 'mytheme'); ?>
                                    </th>
                                    <td><?php echo !empty($placeDetails['street']) ? esc_html($placeDetails['street']) : '—'; ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <span class="dashicons dashicons-location-alt"></span>
                                        <?php esc_html_e('Number', 'mytheme'); ?>
                                    </th>
                                    <td><?php echo !empty($placeDetails['number']) ? esc_html($placeDetails['number']) : '—'; ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <span class="dashicons dashicons-admin-site"></span>
                                        <?php esc_html_e('Region', 'mytheme'); ?>
                                    </th>
                                    <td><?php echo !empty($placeDetails['region']) ? esc_html($placeDetails['region']) : '—'; ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <span class="dashicons dashicons-id"></span>
                                        <?php esc_html_e('NIP (Tax ID)', 'mytheme'); ?>
                                    </th>
                                    <td><?php echo !empty($placeDetails['nip']) ? esc_html($placeDetails['nip']) : '—'; ?></td>
                                </tr>
                                <?php
                                $categories = get_the_terms(get_the_ID(), 'places_category');
                                if ($categories && !is_wp_error($categories)) :
                                    ?>
                                    <tr>
                                        <th scope="row">
                                            <span class="dashicons dashicons-category"></span>
                                            <?php esc_html_e('Categories', 'mytheme'); ?>
                                        </th>
                                        <td>
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
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (get_the_content()) : ?>
                    <div class="entry-content">
                        <h2><?php esc_html_e('Description', 'mytheme'); ?></h2>
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>
                
                <div class="places-actions">
                    <a href="<?php echo esc_url(get_post_type_archive_link('places')); ?>" class="btn btn-secondary">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <?php esc_html_e('Back to Places', 'mytheme'); ?>
                    </a>
                    <?php if (current_user_can('edit_post', get_the_ID())) : ?>
                        <?php 
                        $post_slug = get_post_field('post_name', get_the_ID());
                        $edit_url = home_url('/places/' . $post_slug . '/edit/');
                        ?>
                        <a href="<?php echo esc_url($edit_url); ?>" class="btn btn-primary">
                            <span class="dashicons dashicons-edit"></span>
                            <?php esc_html_e('Edit Place', 'mytheme'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </article>

        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>

