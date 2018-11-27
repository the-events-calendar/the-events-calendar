/**
 * Internal dependencies
 */
import * as geo from '@moderntribe/events/editor/utils/geo-data';

describe( 'Tests for geo-data.js', () => {
	beforeAll( () => {
		window.tribe_editor_config = {
			common: {
				countries: {
					LB: 'Lebanon',
					LC: 'Saint Lucia',
					LI: 'Liechtenstein',
					US: 'United States',
				},
				usStates: {
					DC: 'District of Columbia',
					DE: 'Delaware',
					FL: 'Florida',
				}
			}
		};
	} );

	test( 'getCountries', () => {
		expect( geo.getCountries() ).toEqual( [
			{
				code: 'LB',
				name: 'Lebanon',
			},
			{
				code: 'LC',
				name: 'Saint Lucia',
			},
			{
				code: 'LI',
				name: 'Liechtenstein',
			},
			{
				code: 'US',
				name: 'United States',
			},
		] );
	} );

	test( 'getCountryCode', () => {
		expect( geo.getCountryCode( 'Liechtenstein' ) ).toEqual( 'LI' );
		expect( geo.getCountryCode( 'MX' ) ).toEqual( 'US' );
		expect( geo.getCountryCode( 'Unknown' ) ).toEqual( 'US' );
		expect( geo.getCountryCode() ).toEqual( 'US' );
	} );

	test( 'getStates', () => {
		expect( geo.getStates( 'MX' ) ).toEqual( [] );
		expect( geo.getStates( 'US' ) ).toEqual( [
			{
				code: 'DC',
				name: 'District of Columbia',
			},
			{
				code: 'DE',
				name: 'Delaware',
			},
			{
				code: 'FL',
				name: 'Florida',
			},
		] );
	} );

	test( 'getStateCode', () => {
		expect( geo.getStateCode( 'LL' ) ).toBe( '' );
		expect( geo.getStateCode() ).toBe( '' );
		expect( geo.getStateCode( 'US', 'Unknown' ) ).toBe( '' );
		expect( geo.getStateCode( 'US', 'Florida' ) ).toBe( 'FL' );
		expect( geo.getStateCode( 'US', 'Delaware' ) ).toBe( 'DE' );
	} );

	test( 'addressToMapString', () => {
		expect( geo.addressToMapString() ).toBe( '' );
		const addr = {
			country: 'US',
			street: '1002 Scott St',
			city: 'San Francisco',
			province: 'California',
		};
		expect( geo.addressToMapString( addr ) ).toBe( 'San Francisco, 1002 Scott St, California, US' );
	} );

	test( 'mapLink', () => {
		expect( geo.mapLink() ).toBe( 'https://maps.google.com/maps?f=q&source=s_q&geocode=&q=' );
		const address = {
			city: 'Mexico',
			zip: 90123,
			country: 'MX',
		};
		expect( geo.mapLink( address ) )
			.toBe( 'https://maps.google.com/maps?f=q&source=s_q&geocode=&q=Mexico%2C%2090123%2C%20MX' );
	} );

	afterAll( () => {
		delete window.tribe_data_countries;
		delete window.tribe_data_us_states;
	} );
} );
