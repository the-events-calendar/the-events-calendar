/**
 * Internal Dependencies
 */
import EventsList from '@moderntribe/events/widgets/events-list';
import './style.pcss';

const { registerBlockType } = wp.blocks;

const blocks = [
	EventsList,
];

blocks.forEach( block => {
	const blockName = `tribe/${ block.id }`;
	registerBlockType( blockName, block );
} );

export default blocks;
