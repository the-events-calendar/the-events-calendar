/**
 * External dependencies
 */
import renderer from 'react-test-renderer';
import React from 'react';

/**
 * Internal dependencies
 */
import withBlockWrapper, { BLOCK_API_VERSION } from '@moderntribe/events/blocks/with-block-wrapper';

const Edit = ( { title } ) => <span>{ title }</span>;

const blockDefinition = {
	id: 'event-probe',
	title: 'Event Probe',
	category: 'tribe-events',
	supports: { html: false },
	edit: Edit,
	save: () => null,
};

describe( 'Block wrapper', () => {
	it( 'Should register at the Block API version the editor expects', () => {
		expect( withBlockWrapper( blockDefinition ).apiVersion ).toBe( BLOCK_API_VERSION );
	} );

	it( 'Should leave the rest of the block definition untouched', () => {
		const { edit, apiVersion, ...rest } = withBlockWrapper( blockDefinition );
		const { edit: originalEdit, ...originalRest } = blockDefinition;

		expect( rest ).toEqual( originalRest );
	} );

	it( 'Should render the block edit component inside the wrapper element', () => {
		const wrapped = withBlockWrapper( blockDefinition );
		const component = renderer.create( <wrapped.edit title={ blockDefinition.title } /> );

		expect( component.root.findByType( Edit ).props.title ).toBe( blockDefinition.title );
		expect( component.toJSON() ).toMatchObject( {
			type: 'div',
			props: { className: 'wp-block' },
		} );
	} );
} );
