/**
 * External dependencies
 */
import React from 'react';
import renderer from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { TaxonomiesElement } from './../element';

describe( 'Taxonomies Element', () => {
	it( 'renders empty when items is empty', () => {
		const tree = renderer.create( <TaxonomiesElement terms={ [] } /> );
		expect( tree.toJSON() ).toEqual( null );
	} );

	it( 'renders Empty property', () => {
		const tree = renderer.create( <TaxonomiesElement terms={ [] } renderEmpty={ 'Hi!' } /> );
		expect( tree.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders spinner when requesting', () => {
		const tree = renderer.create(
			<TaxonomiesElement terms={ [] } isRequesting={ true } />
		);
		expect( tree.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the list of items', () => {
		const items = [
			{
				description: '',
				id: 1,
				meta: [],
				name: 'modern',
				slug: 'modern',
				taxonomy: 'post_tag',
			},
			{
				description: '',
				id: 2,
				meta: [],
				name: 'tribe',
				slug: 'tribe',
				taxonomy: 'post_tag',
			},
		];
		const tree = renderer.create(
			<TaxonomiesElement terms={ items } isRequesting={ false } />
		);
		expect( tree.toJSON() ).toMatchSnapshot();
	} );
} );
