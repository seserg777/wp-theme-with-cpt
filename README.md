# MyTheme - Custom WordPress Theme

A modern, object-oriented WordPress theme built following WordPress Coding Standards and PSR-12.

**Author:** Serhii Soloviov  
**Email:** seserg777@gmail.com  
**Version:** 1.0.0

## Features

- ✅ Object-Oriented PHP (PSR-12 compliant)
- ✅ Custom Post Types with OOP architecture
- ✅ Autoloader for automatic class loading
- ✅ jQuery 3.7.1 integration
- ✅ Custom taxonomies support
- ✅ Meta boxes for custom fields
- ✅ WordPress Coding Standards compliant
- ✅ Gutenberg/Block Editor support
- ✅ Webpack 5 build system
- ✅ SCSS with variables and mixins
- ✅ ES6+ JavaScript with Babel
- ✅ Autoprefixer for cross-browser compatibility
- ✅ Minification and optimization

## Structure

```
mytheme/
├── inc/
│   ├── post-types/
│   │   ├── class-post-type-base.php     # Base abstract class for CPTs
│   │   └── class-places-post-type.php   # Places custom post type
│   ├── class-autoloader.php              # PSR-4 autoloader
│   └── class-theme.php                   # Main theme class
├── assets/
│   ├── js/
│   │   ├── main.js                       # Frontend JavaScript
│   │   └── admin.js                      # Admin JavaScript
│   └── css/
│       ├── admin.css                     # Admin styles
│       ├── places-admin.css              # Places admin styles
│       └── places.css                    # Places frontend styles
├── functions.php                         # Theme setup
├── style.css                             # Theme stylesheet
├── index.php                             # Main template
├── header.php                            # Header template
├── footer.php                            # Footer template
├── single-places.php                     # Single place template
├── archive-places.php                    # Places archive template
└── taxonomy-places_category.php          # Places category template
```

## Custom Post Types

### Available Post Types

- **Places** - Locations with address, NIP, and region information

### Creating a New Custom Post Type

1. Create a new class in `inc/post-types/` extending `PostTypeBase`
2. Implement the `init()` method
3. Register the post type in `inc/class-theme.php`

Example:

```php
namespace MyTheme\PostTypes;

class YourPostType extends PostTypeBase
{
    protected function init(): void
    {
        $this->postType = 'your_post_type';
        $this->setLabels('Item', 'Items');
        $this->args = [
            'menu_icon' => 'dashicons-admin-post',
            // ... other args
        ];
    }
}
```

## Build System

The theme uses Webpack 5 for modern asset compilation:

### Development

```bash
# Install dependencies
npm install

# Watch mode (development)
npm run dev

# Production build
npm run build

# Development build (with source maps)
npm run build:dev
```

### Asset Structure

```
src/
├── js/          # Source JavaScript files
├── scss/        # Source SCSS files
│   ├── _variables.scss
│   ├── _mixins.scss
│   └── *.scss

dist/           # Compiled assets (auto-generated)
├── js/         # Minified JavaScript
└── css/        # Minified CSS with autoprefixer
```

### Features

- **SCSS**: Variables, mixins, nesting
- **Babel**: ES6+ transpilation for older browsers
- **Autoprefixer**: Automatic vendor prefixes
- **Minification**: CSS and JS optimization
- **Source Maps**: Available in development mode

## jQuery Usage

The theme includes jQuery 3.7.1. All theme scripts are located in `src/js/`.

Example:

```javascript
MyTheme.ajaxRequest('your_action', { key: 'value' }, function(response) {
    console.log(response);
});
```

## Browser Support

- \> 1% market share
- Last 2 versions
- Not dead browsers
- Excludes IE 11

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Node.js 14+ (for development)
- npm or yarn (for development)

## Installation

1. Upload the theme to `wp-content/themes/mytheme/`
2. Run `npm install` (for development)
3. Run `npm run build` to compile assets
4. Activate the theme in WordPress admin
5. Configure theme settings as needed

## Development

The theme follows:
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- Modern JavaScript (ES6+)
- SCSS best practices
- BEM methodology for CSS classes

## License

GNU General Public License v2 or later

## Credits

Developed by **Serhii Soloviov** (seserg777@gmail.com)

