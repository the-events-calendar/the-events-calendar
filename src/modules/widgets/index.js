/**
 * Internal Dependencies
 */
import EventsList from '@moderntribe/events/widgets/events-list';
import './style.pcss';

const { registerBlockType } = wp.blocks;

// We need to register core/legacy-widget block to support
// earlier versions of WP which don't have it registered by default.
wp.widgets.registerLegacyWidgetBlock();

const blocks = [
	EventsList,
];

blocks.forEach( block => {
	const blockName = `tribe/${ block.id }`;
	registerBlockType( blockName, block );
} );

export default blocks;
