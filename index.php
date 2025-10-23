<?php
/**
 * Main template file
 *
 * @package MyTheme
 * @author Serhii Soloviov <seserg777@gmail.com>
 * @version 1.0.0
 */

get_header(); ?>

<main id="main" class="site-main">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <?php
                if (have_posts()) :
                    while (have_posts()) :
                        the_post();
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('card mb-4'); ?>>
                            <div class="card-body">
                                <header class="entry-header">
                                    <h2 class="entry-title card-title">
                                        <a href="<?php the_permalink(); ?>" class="text-decoration-none"><?php the_title(); ?></a>
                                    </h2>
                                </header>
                                
                                <div class="entry-content">
                                    <?php the_content(); ?>
                                </div>
                                
                                <footer class="entry-footer mt-3">
                                    <small class="text-muted">
                                        <?php echo get_the_date(); ?> | 
                                        <a href="<?php the_permalink(); ?>" class="text-decoration-none">Read more</a>
                                    </small>
                                </footer>
                            </div>
                        </article>
                        <?php
                    endwhile;
                else :
                    ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <h3><?php esc_html_e('No content found', 'mytheme'); ?></h3>
                            <p><?php esc_html_e('Sorry, no posts were found.', 'mytheme'); ?></p>
                        </div>
                    </div>
                    <?php
                endif;
                ?>
            </div>
            
            <div class="col-lg-4">
                <aside id="secondary" class="widget-area">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?php esc_html_e('Sidebar', 'mytheme'); ?></h5>
                        </div>
                        <div class="card-body">
                            <p><?php esc_html_e('This is a sidebar widget area.', 'mytheme'); ?></p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>