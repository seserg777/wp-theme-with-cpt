<?php
/**
 * Autoloader for theme classes
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
 * Autoloader class
 */
class Autoloader
{
    /**
     * Theme namespace
     *
     * @var string
     */
    private string $namespace = 'MyTheme';

    /**
     * Theme directory path
     *
     * @var string
     */
    private string $themePath;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->themePath = get_template_directory();
        spl_autoload_register([$this, 'autoload']);
    }

    /**
     * Autoload classes
     *
     * @param string $class Full class name.
     * @return void
     */
    public function autoload(string $class): void
    {
        // Check if class uses theme namespace.
        if (strpos($class, $this->namespace . '\\') !== 0) {
            return;
        }

        // Remove namespace prefix.
        $relativeClass = substr($class, strlen($this->namespace . '\\'));

        // Convert namespace separators to directory separators.
        $relativeClass = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);

        // Convert to lowercase and add class prefix.
        $classFile = strtolower(str_replace('_', '-', $relativeClass));
        $classFile = 'class-' . $classFile . '.php';

        // Try different possible locations.
        $possiblePaths = [
            $this->themePath . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . $classFile,
            $this->themePath . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'post-types' . DIRECTORY_SEPARATOR . $classFile,
        ];

        // Debug: Log what we're looking for.
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Autoloader looking for class: $class");
            error_log("Looking for file: $classFile");
            foreach ($possiblePaths as $path) {
                error_log("Checking path: $path - " . (file_exists($path) ? 'EXISTS' : 'NOT FOUND'));
            }
        }

        // Require file if it exists.
        foreach ($possiblePaths as $file) {
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}

