# PixelForge Site Health & Performance Checks

This theme registers a set of Site Health tests that focus on performance-related infrastructure. Use this guide to find the results, interpret each check, and address common issues.

## Where to view the checks

- **PixelForge Options → Site Health & Performance**: Visit the PixelForge options page in wp-admin and use the **Open Site Health** button in the "Site Health & Performance" section to jump straight to the WordPress Site Health screen.
- **Tools → Site Health → Status → Direct tests**: The three PixelForge checks appear in the Direct tests list. WordPress will run them automatically and show the status, description, and recommended actions.

## What the tests do

The health checks live in [`app/health.php`](app/health.php) and report the following:

- **Image processing module availability**: Confirms that the Imagick PHP extension is installed so WordPress can generate optimized thumbnails. Missing Imagick will surface as a recommended action with a link to the WordPress PHP extensions guide.
- **Persistent object cache support**: Verifies whether WordPress is using an external object cache (and whether APCu is available). If no cache is active, the test recommends enabling one and links to the official WordPress caching guide.
- **Page cache headers**: Makes a loopback request to the homepage and inspects cache-related headers (e.g., `Cache-Control`, `Expires`, `ETag`). If headers are missing or the request fails, the test suggests enabling a page cache plugin.

## Tips for resolving failures

- **Imagick missing**: Install/enable the Imagick PHP extension via your hosting control panel or contact your host for assistance.
- **Persistent object cache not detected**: Configure a caching plugin (e.g., Redis, Memcached, APCu-backed) and ensure `wp_using_ext_object_cache()` returns true.
- **Page cache headers absent**: Enable page caching at the application level (plugins) or server level (reverse proxy/CDN) so the homepage responds with cache headers.

After addressing issues, revisit **Tools → Site Health** (or use the PixelForge Options shortcut) and rerun the Direct tests to verify the improvements.
