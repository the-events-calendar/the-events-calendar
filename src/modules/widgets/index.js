/**
 * Internal Dependencies
 */
import EventsList from '@moderntribe/events/widgets/events-list';
import './style.pcss';

const { registerBlockType } = wp.blocks;
const { registerLegacyWidgetBlock } = wp.widgets;

const blocks = [
	EventsList,
];

blocks.forEach( block => {
	const blockName = `tribe/${ block.id }`;
	registerLegacyWidgetBlock();
	registerBlockType( blockName, block );
} );

export default blocks;
