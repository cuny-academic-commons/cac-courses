# CAC Courses — Copilot Instructions

WordPress multisite plugin providing course functionality for the [CUNY Academic Commons](https://commons.gc.cuny.edu). Requires the **BP-REST** and **cac-endpoints** plugins.

## Build commands

```bash
npm run build   # production build → dist/app.build.js
npm run dev     # watch mode (development)
```

There are no automated tests. ESLint is configured but has no npm script — run it directly:

```bash
npx eslint assets/js/
```

## Architecture

### PHP

- **Namespace**: `CAC\Courses` — autoloaded from `src/` via `autoload.php` (PSR-4-style: `CAC\Courses\Foo\Bar` → `src/Foo/Bar.php`)
- **Entry point**: `cac-courses.php` bootstraps via `plugins_loaded`, calls `App::init()`
- **`App`**: Central singleton. All initialization is gated on `is_main_site()` — the plugin only runs on the network's main site, except for site-deletion cleanup hooks which run on sub-sites
- **`Course`**: Domain object. Lazy-loads properties from post meta / taxonomies. Always save via `Course::save()` — this writes meta and lets sync hooks mirror to taxonomy
- **`API`**: Registers REST endpoints under `cac-courses/v1/` at `rest_api_init`
- **`Gutenberg`**: Enqueues `dist/app.build.js` for the `cac_course` post type editor only
- **`Featured`**: Admin submenu page for managing featured course IDs (stored in a WP option)
- **`Endpoints\User`**: Extends `WP_REST_Controller`; searches BuddyPress users via `BP_User_Query` + raw `$wpdb` query

### Meta ↔ Taxonomy sync pattern

Course data is stored in post meta as **JSON-encoded arrays**, then automatically mirrored to private taxonomies for efficient querying. The canonical mapping is in `App::meta_tax_map()`:

| Meta key | Taxonomy | Term slug prefix |
|---|---|---|
| `instructor-ids` | `cac_course_instructor` | `instructor_` |
| `course-terms` | `cac_course_term` | _(none)_ |
| `campus-slugs` | `cac_course_campus` | _(none)_ |
| `course-group-ids` | `cac_course_group` | `group_` |
| `course-site-ids` | `cac_course_site` | `site_` |

Sync is triggered by `updated_post_meta` / `added_post_meta` hooks — never call `wp_set_post_terms` for these taxonomies directly. Always write via the meta keys or `Course::save()`.

The `course-term-sortable` meta key is a derived, sortable value updated whenever `course-terms` changes (see `App::sync_course_term_to_sortable_meta`).

### JavaScript / Gutenberg blocks

- **Entry**: `assets/js/app.js` → compiled to `dist/app.build.js`
- Each block lives in `assets/js/blocks/<block-name>/block.js` and is registered with `wp.blocks.registerBlockType( 'cac-courses/cac-course-<name>', ... )`
- WordPress APIs (`wp.blocks`, `wp.i18n`, `wp.element`, etc.) are accessed as **globals** (`wp.*`), not imports — they are listed as `externals` in webpack and declared in `.eslintrc.json`
- Block attributes use `source: 'meta'` to bind directly to post meta fields, e.g. `{ type: 'string', source: 'meta', meta: 'course-terms' }`
- `react-select` is used for multi-select dropdowns (terms, campuses)
- Reusable search components are in `assets/js/components/` (e.g., `UserSearch`, `GroupSearch`, `SiteSearch`)
- The `[cac-courses]` shortcode is also registered in `App::init()`

## Key conventions

### PHP

- Use the singleton pattern with `get_instance()` + `init()` for classes that hook into WordPress
- Tabs for indentation (see `.editorconfig`)
- Custom capability type is `cac_course`; roles `courses_editor`, `editor`, and `administrator` receive full capabilities
- `cac_course_disciplinary_cluster` is the only taxonomy that is public and `show_in_rest` — the rest are private/internal and queried via `tax_query`

### JavaScript

- **Tabs** for indentation (enforced by ESLint `"indent": ["error", "tab"]`)
- Spaces inside array brackets, object braces, and parentheses (`[ x ]`, `{ x }`, `( x )`)
- `prefer-const` is enforced; use `const`/`let`, never `var`
- `no-console` is an error — remove any debug logging before committing
- Import paths must not use bare `blocks`, `components`, `element`, etc. — use `@wordpress/<package>`
