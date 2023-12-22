## Version 4.5 (2023-08-09)
- Added support for uploading generated PDFs to Google Drive.

## Version 4.4 (2023-07-11)
- Added compatibility with WordPress 6.2.
- Added support for Gravity Forms Geolocation Add-On.
- Fixed an issue where the Integrations field type would not appear in the Visual Mapper.

## Version 4.3 (2023-03-22)
- Added "forgravity_fillablepdfs_view_generated_pdfs" capability for viewing generated PDFs.
- Added support for Number formatting modifiers when mapping to a Product price input.
- Fixed an issue where Number formatting would not be applied to Product fields in a Nested Form.

## Version 4.2 (2023-02-15)
- Updated to always use image binaries for GF Signature fields.
- Fixed PHP warnings when generating a PDF where a mapped Nested Form field has no child entries.
- Fixed Product fields populating the option name and price into PDF fields.
- Fixed a fatal error that could occur when attempting to refresh a Dropbox access token.
- Fixed an issue where a PDF field could not be mapped to if the previously selected checkbox choice no longer exists.
- Fixed an issue where the Legal Signing Document Hub could not be loaded.
- Fixed an issue where the export value warning would erroneously appear in the Visual Mapper when mapping to a Nested Form field.
- Removed additional Content-Type headers when serving PDF files.

## Version 4.1 (2022-12-08)
- Fixed a fatal error when attempting to activate Legal Signing while Fillable PDFs is activated.
- Fixed Option fields populating the option name and price into PDF fields.

## Version 4.0 (2022-10-12)
- Added support for Gravity Forms QR Code.
- Added support for uploading generated PDFs to Dropbox.
- Updated background update logic to automatically update minor and maintenance builds.
- Fixed a fatal error when trying to save a generated PDF to a relative path.
- Fixed an issue where field size could not be changed on imported PDFs.
- Fixed PHP notice when checking if base Fillable PDFs path is public.
- API: Added new Fillable_PDFs_API class.

## Version 3.4 (2022-06-15)
- Added support for Legal Signing.
- Updated API library to cache template responses to prevent duplicate requests.
- Fixed an issue where List field columns whose name contained a comma or slash could not be mapped to. 

## Version 3.3 (2022-01-19)
- Added compatibility with Gravity Forms 2.6.
- Added "fg_fillablepdfs_metabox_generated_pdfs" filter to modify PDFs displayed in Generated PDFs Entry Detail metabox.
- Added modifier to format Total fields.
- Added support for mapping to Multi-File Upload fields whose maximum file limit is 1.
- Added validation to ensure an Owner Password is set when defining a User Password.
- Updated replace template file button for improved clarity around intended action.
- Fixed file dates appearing in UTC time when using List block.
- Fixed an issue where templates could not be downloaded with certain custom user capability configurations.
- Fixed an issue with template downloads that could cause query string conflicts with other site elements.
- Fixed an issue with the Fillable PDFs block that breaks the WordPress Block Editor in WordPress 5.8.1 and up.

## Version 3.2 (2021-07-14)
- Added "fg_fillablepdfs_access_denied_message" filter to modify message displayed when user cannot access requested PDF.
- Added "fg_fillablepdfs_use_image_binary_pre_generate" filter to send image file as a Base64 encoded data URI instead of as a URL.
- Added modifier to format Number fields.
- Fixed an issue that would prevent using Date merge tags in the file name.
- Fixed an issue where generated PDFs would not be attached to notifications when using delayed payment processing.
- Fixed an issue where "0" could not be populated into PDF fields.
- Fixed PHP fatal errors thrown when activating the Add-On in some scenarios.
- Fixed the Feed Template selector being interactable while loading the selected template.

## Version 3.1 (2021-05-19)
- Added support for Image Hopper fields.
- Added support for setting auto-updates state on Plugins page.
- Fixed a fatal error that occasionally occurs when selecting a Radio Button in the visual mapper.
- Fixed an issue that could prevent fields on imported forms from being editable.
- Fixed an issue that could prevent form settings from saving.
- Fixed an issue where full values from multiple input fields would not be populated from a Nested Forms child entry.
- Fixed an issue where page images would not load when mapping to multi-page templates.
- Fixed an issue where the Date field merge tag would generate an incorrect file name.
- Fixed PHP notice when using Gravity Forms 2.4.
- Fixed PHP notice when uploading a new template with a duplicate name.

## Version 3.0 (2021-04-26)
- Added fg_fillablepdfs()->delete_pdf() method to delete existing PDFs.
- Added "fg_fillablepdfs_display_all_templates" filter to control whether all templates for license appear in Templates list.
- Added lazy loading to template images to prevent pages displaying empty when template contains more than ten pages.
- Added merge tag autocomplete when mapping to a custom value.
- Added modifier for flattening individual PDF fields.
- Added option to map to full field value for multiple input fields.
- Added setting to regenerate PDF when an entry is edited.
- Added support for Gravity Forms Conditional Shortcode.
- Added support for mapping selected choice image to PDF field when using Gravity Forms Image Choices. 
- Added support for mapping to Gravity Perks Nested Forms fields.
- Added warning when mapping to a Checkbox or Radio Button field and export value does not match.
- Added warning when multiple fields have the same name.
- Fixed a fatal error when trying to download a PDF while the fileinfo PHP extension is disabled.
- Fixed a variable not passed by referenced PHP warning when populating from a Time field.
- Fixed an issue where input labels do not appear in the mapping drop down.
- Fixed an issue where List column values would not populate if a slash was used in the column name.
- Fixed an issue where multiple public PDF folder messages would display.
- Fixed an issue where PDFs with over 250 fields could not be imported.
- Fixed an issue where the PDF folder would be determined as public when no PDFs have been generated.
- Fixed an issue with PDFs not being attached to notifications.
- Fixed PHP notice when mapping to Date field.
- Removed limitation where templates are only accessible on sites they were created on.
- Removed usage of deprecated get_magic_quotes_gpc() function.

## Version 2.3 (2020-06-03)
- Added block to display list of generated PDFs on frontend.
- Added capabilities check for Generated PDFs metabox.
- Added "fg_fillablepdfs_base_path" filter to modify the base folder where generated PDFs are stored.
- Added "fg_fillablepdfs_force_download" filter to allow for PDFs to be displayed inline.
- Added "fg_fillablepdfs_form_path" filter to modify the folder where generated PDFs are stored for a form.
- Added "fg_fillablepdfs_logged_out_timeout" filter to set how many minutes logged out user has to download generated PDF.
- Added "fg_fillablepdfs_view_pdf_capabilities" filter to set capabilities required to view PDF.
- Added GravityView field to display generated PDF links within a View.
- Added notice when generated PDFs folder is publicly accessible.
- Added support for downloading original template files.
- Added support for embedding GFChart charts.
- Added support for exporting and importing Fillable PDFs feeds.
- Added support for replacing existing template file.
- Added "url_signed" modifier for {fillable_pdfs} merge tag.
- Updated Download Permissions setting to Enable Public Address.
- Updated imported form to have default notification.
- Fixed checkbox choices not saving correctly when importing PDF.
- Fixed file name not updating when regenerating PDF.
- Fixed individual Date inputs not populating PDF.
- Fixed plugin settings page not appearing in certain scenarios.
- Fixed visual mapper being unresponsive on forms with more than one hundred fields.
- Removed unused HTTP timeout filter.

## Version 2.2 (2019-09-18)
- Added support for annual pricing plans.
- Added support for global templates.
- Updated custom value mapping to support multiline PDF fields.
- Fixed PDF field not populating when using multiple brackets in field name.

## Version 2.1 (2019-07-09)
- Added support for mapping to List fields.
- Fixed Date not being populated using selected date format.
- Fixed Entry Date not using the defined time zone.
- Fixed entry meta not appearing in PDF field values.
- Fixed field mapper not loading when a PDF field has been mapped to a deleted Gravity Forms field.
- Fixed Javascript error when uploading a new template.
- Fixed merge tag not being replaced when no PDFs are found for feed.
- Fixed PDF downloads being corrupted in certain scenarios.
- Fixed template mapper not opening when React dependency could not be loaded.
- Fixed template mapper not opening when using multiple lines for custom values.
- Fixed Time not being populated based on individual inputs.
- Updated Gravity Forms field deletion process to remove PDF field mappings containing field.

## Version 2.0 (2019-01-28)
- Added a new visual interface for mapping Gravity Forms fields to PDF fields.
- Added support for embedding images and signatures in PDF fields.
- Added support for Gravity Forms Personal Data tools.
- Added support for regenerating PDFs for existing entries.
- Updated template creation to populate template name upon selecting file.

## Version 1.0.5 (2018-08-27)
- Fixed Gravity Flow step not loading properly.

## Version 1.0.4 (2018-08-24)
- Added support for Gravity Forms 2.3.

## Version 1.0.3 (2017-07-10)
- Added support for attaching PDFs when resending notifications.

## Version 1.0.2 (2017-06-05)
- Added default file name.
- Added support for monthly overages.

## Version 1.0.1 (2017-06-27)
- Fixed incorrect add new template link after deleting a template.

## Version 1.0 (2017-06-05)
- It's all new!
