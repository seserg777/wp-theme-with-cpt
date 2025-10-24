# Templates Directory

This directory contains custom template files for the MyTheme WordPress theme.

## Files

### `single-places.php`
Single post template for the Places custom post type.
- Displays detailed information about a place in a table format
- Shows place thumbnail, details table, and action buttons
- Responsive design with mobile-first approach

### `edit-place.php`
Frontend editing template for Places.
- Allows authorized users to edit place information from the frontend
- Form-based interface with validation
- Requires user authentication and edit permissions

### `archive-places.php`
Archive template for the Places post type.
- Displays a sortable table of all places
- AJAX-powered sorting by columns (name, street, number, region, NIP)
- Load more functionality with pagination
- Responsive table design

### `taxonomy-places_category.php`
Taxonomy template for Places Categories.
- Shows places filtered by category
- Inherits functionality from archive template
- Category-specific header and description

## Template Loading

Templates are loaded automatically by the `MyTheme\Templates` class located in `inc/class-templates.php`.

The class uses WordPress `template_include` filter to load custom templates based on:
- Query variables (for edit-place.php)
- Post type (for single-places.php)

## Adding New Templates

To add a new custom template:

1. Create your template file in this directory
2. Update `MyTheme\Templates::loadCustomTemplates()` method to include loading logic
3. Document the template in this README

## Template Hierarchy

Custom templates in this directory take precedence over default WordPress template hierarchy when conditions are met.

