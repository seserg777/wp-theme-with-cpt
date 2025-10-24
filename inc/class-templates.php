<?php
/**
 * Template loader class
 *
 * @package MyTheme
 * @author Serhii Soloviov <seserg777@gmail.com>
 * @version 1.0.0
 */

namespace MyTheme;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Templates class
 */
class Templates
{
    /**
     * Templates directory path
     *
     * @var string
     */
    private string $templatesDir;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->templatesDir = get_template_directory() . '/inc/templates';
        $this->registerHooks();
    }

    /**
     * Register WordPress hooks
     *
     * @return void
     */
    private function registerHooks(): void
    {
        add_filter('template_include', [$this, 'loadCustomTemplates'], 99);
    }

    /**
     * Load custom templates
     *
     * @param string $template Template path.
     * @return string
     */
    public function loadCustomTemplates(string $template): string
    {
        // Handle edit place template.
        if (get_query_var('edit_place')) {
            return $this->getTemplate('edit-place.php');
        }

        // Handle single place template.
        if (is_singular('places')) {
            return $this->getTemplate('single-places.php');
        }

        // Handle places archive template.
        if (is_post_type_archive('places')) {
            return $this->getTemplate('archive-places.php');
        }

        // Handle places category taxonomy template.
        if (is_tax('places_category')) {
            return $this->getTemplate('taxonomy-places_category.php');
        }

        return $template;
    }

    /**
     * Get template path
     *
     * @param string $templateName Template file name.
     * @return string
     */
    private function getTemplate(string $templateName): string
    {
        $templatePath = $this->templatesDir . '/' . $templateName;
        
        if (file_exists($templatePath)) {
            return $templatePath;
        }

        // Fallback to default template.
        return get_query_template('index');
    }

    /**
     * Get templates directory path
     *
     * @return string
     */
    public function getTemplatesDir(): string
    {
        return $this->templatesDir;
    }
}

