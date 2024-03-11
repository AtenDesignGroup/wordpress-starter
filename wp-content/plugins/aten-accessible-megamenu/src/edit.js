/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * Importing packages for editor display
 */
import { useSelect } from '@wordpress/data';
import { InspectorControls } from '@wordpress/block-editor';
import {
  PanelBody,
  PanelRow,
  SelectControl,
  TextControl,
} from '@wordpress/components';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
  const { menu_id, menu_name, menu_slug, mobile_breakpoint } = attributes;

  const menus = useSelect((select) => {
    return select('core').getEntityRecords('taxonomy', 'nav_menu', {
      per_page: 100,
    });
  });

  const menuOptions = [];
  if (menus) {
    menuOptions.push({ value: 0, label: 'Select a Menu' });
    menus.forEach((menu) => {
      menuOptions.push({ value: menu.id, label: menu.name });
    });
  } else {
    menuOptions.push({ value: 0, label: 'Loading...' });
  }

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Mega Menu Settings', 'adg-accessible-megamenu')}>
          <PanelRow>
            <SelectControl
              label="Select a Menu"
              options={menuOptions}
              value={attributes.menu_id}
              onChange={(selectedMenu) => {
                if (menus) {
                  menus.forEach((menu) => {
                    if (menu.id == selectedMenu) {
                      setAttributes({
                        menu_id: selectedMenu,
                        menu_name: menu.name,
                        menu_slug: menu.slug,
                      });
                    }
                  });
                }
              }}
            />
          </PanelRow>

          <PanelRow>
            <TextControl
              help="Set the screen width in pixels at which the menu switches into a mobile layout. Default is 1024px."
              label="Mobile Breakpoint"
              value={attributes.mobile_breakpoint}
              onChange={(breakpoint) => {
                if (breakpoint) {
                  setAttributes({
                    mobile_breakpoint: breakpoint,
                  });
                }
              }}
              type="number"
            />
          </PanelRow>
        </PanelBody>
      </InspectorControls>
      <div {...useBlockProps()}>
        {(menu_id == 0 || menu_id == null) && (
          <h2>Select a menu from the settings pane.</h2>
        )}

        {menu_id != 0 && menu_id != null && (
          <h2>Preview or publish this page to view the {menu_name} block</h2>
        )}
      </div>
    </>
  );
}
