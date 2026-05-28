# Webpack Integration Proposal for MicroMVC

## Overview

This proposal outlines the integration of Webpack into the MicroMVC project to handle asset bundling (CSS & JavaScript). The configuration will align with the existing project structure on the `test-micro` branch.

## Current State

- **Assets location**: `public/asset/` (CSS, JS, images, FontAwesome)
- **CSS**: Bootstrap 5, FontAwesome 6, custom `style.css`
- **JS**: Bootstrap JS, jQuery 3.6.0
- **Templating**: Twig templates reference assets via a custom `asset()` function resolving to `{baseUrl}/{path}`
- **Docker**: PHP 8.4, Nginx, MariaDB — Node.js 18.x and Yarn already installed in the PHP container
- **No existing frontend build tool** (no `package.json`)

## Proposed Changes

### 1. New Files

| File | Purpose |
|------|---------|
| `package.json` | Node.js project manifest with Webpack dependencies |
| `webpack.config.js` | Webpack configuration for dev and production builds |
| `assets/js/app.js` | Main JavaScript entry point |
| `assets/css/app.css` | Main CSS entry point (imports Bootstrap + custom styles) |

### 2. Directory Structure

```
.
├── assets/                  # NEW - Source assets (pre-build)
│   ├── js/
│   │   └── app.js           # JS entry point (imports Bootstrap JS, jQuery)
│   └── css/
│       └── app.css          # CSS entry point (imports Bootstrap, FontAwesome, custom styles)
├── public/
│   ├── build/               # NEW - Webpack output directory (compiled/bundled assets)
│   │   ├── app.js
│   │   ├── app.css
│   │   └── manifest.json    # Asset manifest for cache-busting in production
│   ├── asset/               # EXISTING - Remains for static assets (images, fonts)
│   └── index.php
├── package.json             # NEW
├── webpack.config.js        # NEW
└── ...
```

### 3. Webpack Configuration Details

- **Entry**: `./assets/js/app.js` (single entry point)
- **Output**: `./public/build/` directory
- **Loaders**:
  - `css-loader` + `mini-css-extract-plugin` — extracts CSS into separate files
  - `file-loader` / `asset modules` — handles fonts and images referenced in CSS
- **Plugins**:
  - `MiniCssExtractPlugin` — extracts CSS into `app.css`
  - `WebpackManifestPlugin` — generates `manifest.json` for versioned asset paths
  - `CleanWebpackPlugin` — clears `public/build/` before each build
- **Modes**:
  - `development` — source maps enabled, no minification
  - `production` — minified, content-hashed filenames for cache busting

### 4. Dependencies (devDependencies)

```json
{
  "webpack": "^5",
  "webpack-cli": "^5",
  "css-loader": "^7",
  "mini-css-extract-plugin": "^2",
  "webpack-manifest-plugin": "^5",
  "clean-webpack-plugin": "^4"
}
```

### 5. Package Scripts

```json
{
  "scripts": {
    "dev": "webpack --mode development",
    "watch": "webpack --mode development --watch",
    "build": "webpack --mode production"
  }
}
```

### 6. Template Changes

Update `templates/base.html.twig` to reference the bundled assets from `public/build/` instead of individual files:

```twig
{# Before #}
<link rel="stylesheet" href="{{ asset('asset/css/bootstrap/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('asset/fontawesome/css/all.css') }}">
<link rel="stylesheet" href="{{ asset('asset/css/common/style.css') }}">
...
<script src="{{ asset('asset/js/bootstrap/bootstrap.js') }}"></script>

{# After #}
<link rel="stylesheet" href="{{ asset('build/app.css') }}">
...
<script src="{{ asset('build/app.js') }}"></script>
```

### 7. `.gitignore` Update

Add `/public/build/` and `/node_modules/` to `.gitignore` since these are generated artefacts.

### 8. Docker Considerations

Node.js 18.x and Yarn are already installed in the PHP Docker container, so no Dockerfile changes are required. The build step can be run inside or outside the container:

```bash
# Outside container (if Node.js available locally)
yarn install && yarn build

# Inside container
docker exec -it micro-mvc-php-container yarn install
docker exec -it micro-mvc-php-container yarn build
```

## What Will NOT Change

- The `public/asset/images/` directory remains untouched (static images served directly)
- The Twig `asset()` function continues to work as-is
- PHP source code and routing remain unchanged
- FontAwesome font files remain in `public/asset/fontawesome/` (webfonts referenced by CSS)

## Build Instructions (Post-Implementation)

```bash
# Install Node dependencies
yarn install

# Development build (with source maps)
yarn dev

# Watch mode (auto-rebuild on changes)
yarn watch

# Production build (minified, cache-busted)
yarn build
```

## Risks & Considerations

1. **FontAwesome webfonts**: CSS references to `../webfonts/` will need correct `publicPath` or `file-loader` configuration to resolve font paths properly in the bundled output.
2. **Cache busting**: In production mode, filenames include content hashes (e.g., `app.a1b2c3.css`). The `manifest.json` can be read by a Twig helper if needed, but for simplicity the initial implementation will use fixed output names.
3. **Existing static assets**: The `public/asset/` folder is preserved for any assets not suitable for bundling (e.g., dynamically referenced images).
