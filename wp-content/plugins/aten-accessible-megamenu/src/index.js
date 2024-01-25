/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import metadata from './block.json';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	icon: {
		src: <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60">
		<g fill-rule="evenodd">
			<path d="M38.9263401,14.5619025 L40.1704549,24.1684477 L45.4720803,21.1076426 L41.7460194,30.048932 L47.8676295,30.048932 L40.1704549,35.9294164 L45.4720803,38.9902214 L35.8655351,40.2351216 L38.9263401,45.5359616 L29.9850507,41.8106862 L29.9850507,47.9322963 L24.103781,40.2351216 L21.0429759,45.5359616 L19.7988611,35.9294164 L14.4980212,38.9902214 L18.2232966,30.048932 L12.1016865,30.048932 L19.7988611,24.1684477 L14.4980212,21.1076426 L24.103781,19.8635279 L21.0429759,14.5619025 L29.9850507,18.2871779 L29.9850507,12.1663532 L35.8655351,19.8635279 L38.9263401,14.5619025 Z M55.9127479,15.0794982 L40.9433141,11.0691131 L44.9544846,4.12123486 L29.9850507,8.13240537 L29.9850507,0.110849787 L19.0267874,11.0691131 L15.0156169,4.12123486 L11.0044464,19.0906687 L4.05735355,15.0794982 L8.06852405,30.048932 L0.0461830479,30.048932 L11.0044464,41.0071954 L4.05735355,45.0183659 L19.0267874,49.0295364 L15.0156169,55.9766292 L29.9850507,51.9654587 L29.9850507,59.9877997 L40.9433141,49.0295364 L44.9536991,55.9766292 L48.9648696,41.0071954 L55.9127479,45.0183659 L51.9015774,30.048932 L59.923133,30.048932 L48.9648696,19.0906687 L55.9127479,15.0794982 Z"></path>
		</g>
	</svg>
	},

	/**
	 * @see ./edit.js
	 */
	edit: Edit
} );
