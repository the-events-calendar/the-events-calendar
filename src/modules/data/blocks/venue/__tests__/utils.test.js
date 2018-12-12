/**
 * Internal dependencies
 */
import { getAddress, getCoordinates } from '@moderntribe/events/data/blocks/venue/utils';

describe( 'Venue Utils', () => {
	it( 'Should return the address details', () => {
		expect( getAddress() ).toEqual( {} );
		expect( getAddress( { meta: {} } ) ).toEqual( {} );

		const details = {
			meta: {
				_VenueAddress: '3301 Lyon St',
				_VenueCity: 'San Francisco',
				_VenueCountry: 'USA',
			},
		};

		const expected = {
			street: '3301 Lyon St',
			city: 'San Francisco',
			province: '',
			zip: '',
			country: 'USA',
		};
		expect( getAddress( details ) ).toEqual( expected );
	} );

	it( 'Should return the coordinates of the address', () => {
		const emptyCoordinates = { lat: null, lng: null };
		expect( getCoordinates( {} ) ).toEqual( emptyCoordinates );
		expect( getCoordinates( { meta: {} } ) ).toEqual( emptyCoordinates );
		expect( getCoordinates( { meta: { _VenueLat: '', _VenueLng: '' } } ) )
			.toEqual( emptyCoordinates );
		expect( getCoordinates( { meta: { _VenueLat: 'Modern', _VenueLng: 'Tribe' } } ) )
			.toEqual( emptyCoordinates );

		const details = {
			meta: {
				_VenueLat: '37.802953',
				_VenueLng: '-122.448342',
			},
		};

		const expected = {
			lat: 37.802953,
			lng: -122.448342,
		};
		expect( getCoordinates( details ) ).toEqual( expected );
	} );
} );
