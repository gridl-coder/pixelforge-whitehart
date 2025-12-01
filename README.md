# PixelForge Base Theme

PixelForge is a bespoke WordPress base theme designed for the PixelForge creative studio. It bundles a modern development workflow powered by Acorn, Blade, Tailwind CSS, and Vite while remaining lightweight enough to tailor for each new project.

## Features

- Laravel Blade templating with Roots Acorn
- Tailwind CSS and Vite build tooling out of the box
- Global company options powered by the bundled CMB2 options page
- Per-page content panels for the front page hero and custom templates
- Sensible defaults for navigation menus, sidebars, and editor assets

## Getting Started

1. Install theme dependencies:
   ```bash
   composer install
   npm install
   ```
2. Build assets for development:
   ```bash
   npm run dev
   ```
3. Build production assets:
   ```bash
   npm run build
   ```

## Content Controls

### Global Options

Navigate to **PixelForge Options** in the WordPress dashboard to manage global, reusable settings:

- Company logo (used as a fallback hero image and in the site header)
- Company address
- Company email
- Company phone number
- Table booking toggle (uncheck **Enable Table Bookings** to hide the public booking form and calendar)

These values are surfaced in theme templates so they can be reused across the header, footer, and other global components.

### Per-Page Fields

- **Front Page Hero** &mdash; When editing the page assigned as the front page you can configure the hero title, supporting text, and artwork.
- **Custom Template Fields** &mdash; Pages that use `template-custom.blade.php` gain an intro title and text block that appears above the main content.

## License

PixelForge Base Theme is released under the [MIT License](LICENSE.md).
