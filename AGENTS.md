# Repository Guidelines

## Project Structure & Module Organization
- Theme root contains WordPress entry files (`functions.php`, `style.css`, `theme.json`) plus Composer/Vite config.
- PHP bootstrapping lives in `app/` (`setup.php`, `filters.php`, `performance.php`, SEO and CMB2/Provider classes). Custom post types and options are registered there.
- Blade templates and view helpers are in `resources/views` and `app/View`; front-end assets (SCSS/JS), fonts, icons, and images live under `resources/`. Generated font-face definitions land in `resources/css/_fonts.scss`.
- Built assets and manifest are emitted to `public/build/` by Vite; do not edit generated files directly.
- Translation sources and compiled PO/MO/JSON files are in `resources/lang/`.

## Build, Test & Development Commands
- `npm install` + `composer install` — install Node and PHP dependencies (Node >= 20 required).
- `npm run dev` — start Vite for local development with hot reloading.
- `npm run build` — production build with minified assets, manifest, compressed `.br`/`.gz`, and Tailwind-driven `theme.json` output.
- `npm run translate` — regenerate POT and update PO files via WP-CLI; assumes `wp` is available.
- `npm run translate:compile` — compile PO to MO and JSON for runtime use.

## Coding Style & Naming Conventions
- Follow `.editorconfig`: LF endings, final newline, 2-space indent by default, 4-space for `.php`, 2-space for Blade/views; prefer single quotes.
- Keep Blade partials/components kebab-cased (e.g., `layouts/header.blade.php`), JS modules camelCase, and SCSS with BEM-friendly class names when not using Tailwind utilities.
- Keep strings translatable (`__`, `_e`, `@lang`) and avoid embedding HTML in translation tokens when possible.

## Testing Guidelines
- No automated test suite is defined; run `npm run build` before PRs to ensure assets compile and manifest updates are captured.
- Manually smoke-test key templates in WordPress (header/footer, hero panels, custom templates, nav) and verify fonts/icons load from `public/build/`.
- Rebuild translations after copy changes to confirm PO/MO/JSON regenerate without warnings.

## Commit & Pull Request Guidelines
- Use short, imperative commit subjects (e.g., "Add hero carousel controls"); group related changes per commit.
- For PRs: describe scope and rationale, list build/test steps run, link related issues/tickets, and attach before/after screenshots or screen recordings for visual changes.
- Avoid committing generated `public/build` assets unless the change requires them for deployment.

## Translations & i18n Tips
- Update source strings in Blade/PHP, then run `npm run translate` followed by `npm run translate:compile` to keep POT/PO/MO/JSON in sync.
- Keep locale files tidy: one phrase per line, no trailing whitespace; prefer consistent casing and punctuation to reduce diff noise.
