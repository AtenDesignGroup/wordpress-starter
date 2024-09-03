# Aten Hybrid Theme

This theme is built on top of the WordPress core theme Twenty Twenty One (https://wordpress.com/theme/twentytwentyone).

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

### Iconography

All iconography throughout the site utilizes the [Google Material Symbols library](https://fonts.google.com/icons). When adding or editing components that contain icons, any new icons should come from the symbol library.

### Form Accessibility

This site utilizes Gravity Forms for all form management. When adding or editing forms within Gravity Forms, conditional fields should be separated onto a separate page using the Gravity Forms pagination tool. This keeps Gravity Forms from performing unannounced AJAX changes that harm the accessibility of the form. For more information about keeping Gravity Forms accessible, reach out to the QA team member for this project.
