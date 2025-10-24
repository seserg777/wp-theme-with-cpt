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
- ✅ Meta boxes for custom fields
- ✅ WordPress Coding Standards compliant
- ✅ Gutenberg/Block Editor support
- ✅ Webpack 5 build system with BrowserSync
- ✅ SCSS with variables and mixins
- ✅ ES6+ JavaScript with Babel
- ✅ Autoprefixer for cross-browser compatibility
- ✅ Minification and optimization
- ✅ Hot reload and live browser refresh
- ✅ Advanced sorting with empty values handling
- ✅ AJAX-powered table with load more functionality
- ✅ Authentication modals for restricted actions

## Structure

```
mytheme/
├── inc/
│   ├── post-types/
│   │   ├── class-post-type-base.php     # Base abstract class for CPTs
│   │   └── class-places-post-type.php   # Places custom post type
│   ├── templates/
│   │   ├── single-places.php            # Single place template
│   │   ├── edit-place.php               # Frontend edit template
│   │   ├── archive-places.php           # Places archive with AJAX table
│   │   └── README.md                    # Templates documentation
│   ├── class-autoloader.php             # PSR-4 autoloader
│   ├── class-theme.php                  # Main theme class
│   └── class-templates.php              # Template loader class
├── src/
│   ├── js/                              # Source JavaScript files
│   │   ├── main.js                      # Main JS
│   │   ├── admin.js                     # Admin JS
│   │   └── places.js                    # Places JS with AJAX & modals
│   └── scss/                            # Source SCSS files
│       ├── _variables.scss              # SCSS variables
│       ├── _mixins.scss                 # SCSS mixins
│       ├── theme.scss                   # Main theme styles
│       ├── style.scss                   # Additional styles
│       ├── admin.scss                   # Admin styles
│       ├── places-admin.scss            # Places admin styles
│       └── places.scss                  # Places frontend styles
├── dist/                                # Compiled assets (auto-generated, gitignored)
│   ├── js/                              # Minified JavaScript
│   │   ├── main.min.js
│   │   ├── places.min.js
│   │   └── ...
│   └── css/                             # Minified CSS
│       ├── theme.min.css
│       ├── places-styles.min.css
│       └── ...
├── node_modules/                        # NPM dependencies (gitignored)
├── package.json                         # NPM configuration
├── webpack.config.js                    # Webpack configuration
├── functions.php                        # Theme setup
├── style.css                            # Theme stylesheet header
├── index.php                            # Main template
├── header.php                           # Header template
└── footer.php                           # Footer template
```

## Templates

Custom templates are stored in `inc/templates/` directory and loaded automatically by the `Templates` class.

### Template Loader

The `MyTheme\Templates` class handles loading custom templates:
- Uses WordPress `template_include` filter
- Automatically loads templates based on conditions
- Provides fallback to default templates

### Available Templates

- **single-places.php** - Single place view with details table
- **edit-place.php** - Frontend editing interface for authenticated users
- **archive-places.php** - Places archive with sortable AJAX table and load more functionality

## Custom Post Types

### Available Post Types

#### Places

Custom post type for managing locations with advanced features:

**Fields:**
- Title (post title)
- Address (meta field)
- Region (meta field)
- NIP / Tax ID (meta field)
- Featured Image (thumbnail)

**Features:**
- ✅ Sortable table with AJAX load more
- ✅ Smart sorting (non-empty values first on ASC, empty first on DESC)
- ✅ Frontend editing for authenticated users
- ✅ Authentication modal for non-logged users
- ✅ Responsive design with mobile-friendly table
- ✅ Real-time updates with BrowserSync integration

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

The theme uses Webpack 5 with BrowserSync for modern asset compilation and live development:

### Development

```bash
# Install dependencies
npm install

# Watch mode with BrowserSync (auto-reload browser)
npm run watch
# or
npm run dev

# Watch mode without BrowserSync
npm run watch:no-sync

# Watch production mode with BrowserSync
npm run watch:prod

# Production build (one-time)
npm run build

# Development build with source maps (one-time)
npm run build:dev
```

### BrowserSync

When running `npm run watch`, BrowserSync will:
- Proxy your local WordPress site (`wp.loc`)
- Open at `https://localhost:3000`
- Auto-reload browser on PHP file changes
- Hot-inject CSS changes without page reload
- Provide UI panel at `http://localhost:3002`

### Asset Structure

```
src/                    # Source files (edit here)
├── js/
│   ├── main.js         # Main theme JavaScript
│   ├── admin.js        # Admin JavaScript
│   └── places.js       # Places AJAX, sorting, modals
└── scss/
    ├── _variables.scss # Variables (colors, spacing, fonts)
    ├── _mixins.scss    # Mixins (transitions, shadows)
    ├── theme.scss      # Main theme styles
    ├── style.scss      # Additional styles
    ├── admin.scss      # Admin panel styles
    ├── places-admin.scss # Places admin styles
    └── places.scss     # Places frontend styles

dist/                   # Compiled assets (auto-generated, gitignored)
├── js/
│   ├── main.min.js
│   ├── places.min.js
│   └── *.min.js.map    # Source maps
└── css/
    ├── theme.min.css
    ├── places-styles.min.css
    └── *.min.css.map   # Source maps
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

