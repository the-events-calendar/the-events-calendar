/**
 * Internal dependencies
 */
import { getAddress, getCoordinates } from '@moderntribe/events/data/blocks/venue/utils';

describe( 'Venue Utils', () => {
	it( 'Should return the address details', () => {
		expect( getAddress() ).toMatchSnapshot();
		expect( getAddress( { meta: {} } ) ).toMatchSnapshot();

		const details = {
			meta: {
				_VenueAddress: '3301 Lyon St',
				_VenueCity: 'San Francisco',
				_VenueCountry: 'USA',
			},
		};

		expect( getAddress( details ) ).toMatchSnapshot();
	} );

	it( 'Should return the coordinates of the address', () => {
		expect( getCoordinates( {} ) ).toMatchSnapshot();
		expect( getCoordinates( { meta: {} } ) ).toMatchSnapshot();
		expect( getCoordinates( { meta: { _VenueLat: '', _VenueLng: '' } } ) )
			.toMatchSnapshot();
		expect( getCoordinates( { meta: { _VenueLat: 'Modern', _VenueLng: 'Tribe' } } ) )
			.toMatchSnapshot();

		const details = {
			meta: {
				_VenueLat: '37.802953',
				_VenueLng: '-122.448342',
			},
		};

		expect( getCoordinates( details ) ).toMatchSnapshot();
	} );
} );
