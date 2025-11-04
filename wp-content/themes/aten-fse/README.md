# Aten FSE Theme

This theme is built on top of the WordPress core theme Twenty Twenty Four (https://wordpress.com/theme/twentytwentyfour).

The theme utilizes the ACF plugin to handle custom fields and Custom Post Types for its unique content. There are several custom plugins that provide addtional templating support within the plugins folder, these include:

- Aten Contact Info
- Aten Hierarchical Menu
- Aten Related Pages

## Development Workflow

Refer to project root README file for supported workflow.

## Versioning

We'll be using [NVM](https://github.com/creationix/nvm) to standardize on which version of Node.js our tooling supports. This version number is stored in the `.nvmrc` file. To install the appropriate version, use the command `nvm install` from within this directory.

In order to ensure everyone is using the same version of Node modules, when installing something new or updating the existing install use the `--save-exact` flag. This will ensure the `package.json` file will use that specific version, and not the default behavior of staying within the stored major release.

Current expected nvm version: 18.15.0

### Installation

```
nvm use
npm install
```

### Development

```
npm run watch
```

Type `ctrl+c` to stop the dev server.

### Build

To compile build files after development work has been approved run:

```
npm run build
```

### Template Creation

Whenever possible, use the Appearance Editor to make template changes. The editor can be located inside of WP Admin > Appearance > Editor. This provides visual feedback when adjusting layouts, and all changes made inside of the Editor can later be exported as WordPress FSE code fragments.

When creating templates for Custom Post Types, they should be created inside of the Appearance Editor and applied to All Items. This sets the default template used by all posts of the Custom Post Type. If no template is applied to a CPT, it will default to the Single template supplied by WordPress.

When a template is approved by QA/Design, export the theme using the export tool in the options menu of the Appearance Editor. Extract the template file of the approved template and add it to the template folder of your local codebase. When all changes are ready, push the file to the code repository so there is a legacy record of all changes being made to the template.

### Iconography

All iconography throughout the site utilizes the [Google Material Symbols library](https://fonts.google.com/icons). When adding or editing components that contain icons, any new icons should come from the symbol library.

### Form Accessibility

This site utilizes Gravity Forms for all form management. When adding or editing forms within Gravity Forms, conditional fields should be separated onto a separate page using the Gravity Forms pagination tool. This keeps Gravity Forms from performing unannounced AJAX changes that harm the accessibility of the form. For more information about keeping Gravity Forms accessible, reach out to the QA team member for this project.

## Theme Architecture

### Typographic Scale System

The theme implements a comprehensive typographic scale system that generates responsive font sizes and line heights using CSS custom properties. The system is defined in `libraries/global/00-base/_typography.scss` and provides:

- **Responsive Typography**: Font sizes and line heights that automatically adjust across breakpoints (mobile, tablet, sm-desktop, desktop)
- **Categorized Scales**: Organized into groups like `heading`, `body`, and `label` for semantic organization
- **CSS Custom Properties**: All typography values are exposed as CSS variables (e.g., `--aten-fs-body-18`, `--aten-lh-heading-h1`)
- **Mixin Support**: Use the `type-scale()` mixin to apply responsive typography in any SCSS file

Example usage:

```scss
@use '../../../libraries/partials/partials' as *;

.my-element {
  @include type-scale(body, 18);
}
```

### Global Base Files

The theme uses a centralized configuration approach with global base files located in `libraries/global/00-base/`:

- **`_typography.scss`**: Defines font families, typographic scale, font weights, and default text styling
- **`_colors.scss`**: Centralized color definitions and theme color palette
- **`_layouts.scss`**: Base layout configurations and spacing systems

These base files are imported into `libraries/main.scss` and establish foundational values that cascade throughout the entire theme. All custom properties use the `--aten-` prefix (defined in `libraries/partials/_base.scss`) to prevent naming conflicts.

### Custom Block Structure

Custom blocks in the theme follow a standardized structure with dedicated organization:

#### Required Files

Each block must include:

- **`block.json`**: WordPress block configuration file defining the block's metadata, supports, and settings
- **`[block-name].config.json`**: Custom configuration file for nested blocks and additional settings
- **`[block-name].php`**: PHP template file for block rendering

#### Dedicated `src/` Folder

Each block supports a dedicated `src/` folder for block-specific assets:

- **SCSS files**: Block-specific styles (e.g., `src/callout.scss`)
- **JavaScript files**: Block-specific functionality and interactions
- These files are compiled into the block's root directory during the build process

Example block structure:

```
blocks/
  callout/
    block.json              # WordPress block definition
    callout.config.json     # Custom block configuration
    callout.php             # PHP template
    callout.css             # Compiled styles (generated)
    src/
      callout.scss          # Source SCSS file
      callout.js            # Source JavaScript file (if needed)
```

All block SCSS files can import the theme's partials system to access mixins, breakpoints, and other utilities:

```scss
@use '../../../libraries/partials/partials' as *;
```
