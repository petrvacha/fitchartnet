---
name: fitchart-frontend-build
description: Regenerate minified CSS and JS after editing frontend assets in Fitchart. Use when changing files in htdocs/css/, htdocs/js/, or when the user asks to rebuild or regenerate CSS/JS.
---

# Fitchart Frontend Build

## When to Regenerate

After editing any **source** CSS or JS that is included in the Grunt build, the minified assets must be regenerated. The app serves the minified files (`*.min.css`, `*.min.js`), not the source files.

**Source locations:** `htdocs/css/` (including `htdocs/css/libs/`), `htdocs/js/` (including `htdocs/js/libs/`), and files listed in Gruntfile.js (e.g. `style-new.css`, `common.js`).

## Commands

From project root:

- **CSS only:** `npx grunt cssmin` or `grunt cssmin` — rebuilds `style.min.css`, `style-new.min.css`, `style-front.min.css`.
- **Full build:** `npx grunt build` or `grunt dev` — CSS + JS minification (build) or concatenation (dev).

After changing CSS (e.g. token-input-facebook.css, style-new.css, Bootstrap overrides), run at least `grunt cssmin` so the change appears in the served minified CSS.
