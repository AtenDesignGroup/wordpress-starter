# Aten Event Calendar

This plugin creates the Event post type, custom Event taxonomies, and custom fields used for managing Event data and the Event Calendar.

## Development

The plugin utilizes the ACF plugin to handle custom fields and Custom Post Types for its unique content.

### Custom Fields

Custom fields are built through the ACF UI and exported as PHP using the ACF export tool. To add new custom fields to an implementation of the event calendar, export the `acf_add_local_field_group` code for the relevant custom fields from the ACF plugin and place the code within the `aten_events_register_custom_fields` function in the `generate-custom-fields.php` file. 

### Custom Taxonomies

Custom taxonomies are built through the CPT UI and exported as PHP using the CPT UI export tool. To add new custom taxonomies to an implementation of the event calendar, export the PHP code for the relevant custom fields from the CPT UI plugin and place the code within the `aten_events_register_custom_taxonomies` function in the `generate-custom-taxonomies.php` file. 

### Toggling the Block Builder

By default, the block builder is disabled for Event posts to provide a cleaner, simpler UI for authors. To re-enable the block builder, remove the `aten_events_remove_block_editor` from the `generate-custom-post-types.php` file.
