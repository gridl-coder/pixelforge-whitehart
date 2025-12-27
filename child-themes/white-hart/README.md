# White Hart Child Theme

This child theme packages the front-end experience for The White Hart while inheriting booking, SEO, and performance features from the **PixelForge Framework** parent theme at the repository root.

## Contents
- `resources/`: Blade templates, SCSS, JavaScript, fonts, and image placeholders specific to The White Hart (binary assets are excluded from version control).
- `public/`: Compiled Vite assets and manifest used on the front end.
- `theme.json`, `style.css`: WordPress metadata and block settings for the child theme.
- `functions.php`: Adjusts the Sage view paths so Blade templates resolve from the child theme.

## Usage
1. Install the parent and child theme directories into `wp-content/themes/`.
2. Activate **PixelForge Framework** on the network, then activate **White Hart Child Theme** on the White Hart site.
3. Run `npm install` and `npm run build` from `child-themes/white-hart/` when making styling or templating changes so `public/build` stays in sync.
