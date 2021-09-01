/**
 * Internal dependencies
 */
import {
	getAddress,
	getCoordinates,
	getVenueCountry,
	getVenueStateProvince,
} from '@moderntribe/events/data/blocks/venue/utils';

jest.mock( '@moderntribe/common/utils/globals', () => ( {
	editorDefaults: () => ( {
		venue: 0,
		venueCountry: '',
		venueState: '',
		venueProvince: '',
	} ),
	list: () => ( {
		countries: {},
		us_states: {},
	} ),
	mapsAPI: () => ( {
		embed: true,
	} ),
} ) );

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

	it( 'Should return the venue country', () => {
		expect( getVenueCountry( {} ) ).toMatchSnapshot();
		expect( getVenueCountry( { _VenueCountry: '' } ) ).toMatchSnapshot();
		expect( getVenueCountry( { _VenueCountry: 'Canada' } ) ).toMatchSnapshot();
	} );

	it( 'Should return the venue state or province', () => {
		expect( getVenueStateProvince( {} ) ).toMatchSnapshot();
		expect( getVenueStateProvince( { _VenueStateProvince: '' } ) ).toMatchSnapshot();
		expect( getVenueStateProvince( { _VenueStateProvince: '', _VenueCountry: 'US' } ) )
			.toMatchSnapshot();
		expect( getVenueStateProvince( { _VenueStateProvince: 'Alberta', _VenueCountry: 'Canada' } ) )
			.toMatchSnapshot();
	} );
} );
