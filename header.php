<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'mytheme'); ?></a>

    <!-- Header -->
    <header id="masthead" class="site-header bg-primary text-white">
        <div class="container">
            <div class="row align-items-center py-3">
                <div class="col-md-4">
                    <div class="site-branding">
                        <?php
                        if (is_front_page() && is_home()) :
                            ?>
                            <h1 class="site-title mb-0">
                                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="text-white text-decoration-none">
                                    <?php bloginfo('name'); ?>
                                </a>
                            </h1>
                            <?php
                        else :
                            ?>
                            <p class="site-title mb-0">
                                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="text-white text-decoration-none">
                                    <?php bloginfo('name'); ?>
                                </a>
                            </p>
                            <?php
                        endif;
                        $mytheme_description = get_bloginfo('description', 'display');
                        if ($mytheme_description || is_customize_preview()) :
                            ?>
                            <p class="site-description mb-0 text-light"><?php echo $mytheme_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                        <?php endif; ?>
                    </div><!-- .site-branding -->
                </div>
                <div class="col-md-8">
                    <nav id="site-navigation" class="main-navigation">
                        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#primary-menu" aria-controls="primary-menu" aria-expanded="true" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse show" id="primary-menu">
                            <?php
                            wp_nav_menu([
                                'theme_location' => 'primary',
                                'menu_id'        => 'primary-menu',
                                'menu_class'     => 'navbar-nav ms-auto',
                                'container'      => false,
                                'fallback_cb'   => false,
                                'link_before'   => '',
                                'link_after'    => '',
                                'items_wrap'    => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                            ]);
                            ?>
                        </div>
                    </nav><!-- #site-navigation -->
                </div>
            </div>
        </div>
    </header><!-- #masthead -->

    <div id="content" class="site-content">