/**
 * Internal Dependencies
 */
import EventsList from '@moderntribe/events/widgets/events-list';
import QrCode from '@moderntribe/events/widgets/qr-code';
import './style.pcss';

const { registerBlockType } = wp.blocks;

const blocks = [
	EventsList,
	QrCode,
];

blocks.forEach( block => {
	const blockName = `tribe/${ block.id }`;
	registerBlockType( blockName, block );
} );

export default blocks;
