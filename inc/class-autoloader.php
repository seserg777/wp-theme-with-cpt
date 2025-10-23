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

        // Build full file path.
        $file = $this->themePath . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . $classFile;

        // Require file if it exists.
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

