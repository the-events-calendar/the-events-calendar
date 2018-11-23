/**
 * External dependencies
 */
import { get, identity, trim, find } from 'lodash';
import { stringify } from 'querystringify';
import { list } from '@moderntribe/common/utils/globals';

/**
 * Convert data from an array with different keys and values into a unified shape of object.
 *
 * @param {object} data An object with the data used to retrieve the data
 * @returns {{code: string, name: *}[]} Return an object with code, name values
 */
function toObject( data = {} ) {
	return Object.keys( data ).map( ( key ) => {
		return {
			code: key,
			name: data[ key ],
		};
	} );
}

/**
 * Return the list of all the countries presented by the localized variable: tribe_data_countries
 *
 * @returns {{code: string, name: *}[]} An object with the list of countries
 */
export function getCountries() {
	return toObject( list().countries );
}

export function getCountryCode( name ) {
	const result = find( getCountries(), ( country ) => country.name === name );
	return get( result, 'code', 'US' );
}

export function getStateCode( countryCode, name ) {
	const states = getStates( countryCode );
	const result = find( states, ( state ) => state.name === name );
	return get( result, 'code', '' );
}

export function getStateName( countryCode, code ) {
	const states = getStates( countryCode );
	const result = find( states, ( state ) => state.code === code );
	return get( result, 'name', '' );
}

/**
 * Return the list of al the states based on the country code
 *
 * @param {string} countryCode The code of the country from where the states are going to be returned
 * @returns {{code: string, name: *}[]} An object with the list of the States
 */
export function getStates( countryCode ) {
	switch ( countryCode ) {
		case 'US':
			return toObject( list().usStates );
		default:
			return [];
	}
}

export default list();

export function addressToMapString( address = {} ) {
	const {
		city,
		street,
		province,
		country,
	} = address;
	const components = [ city, street, province, country ];
	return components
		.filter( identity )
		.map( trim )
		.join( ', ' );
}

export function mapLink( address = {} ) {
	const {
		city,
		street,
		province,
		zip,
		country,
	} = address;

	const query = [ city, street, province, zip, country ]
		.filter( identity )
		.map( trim )
		.join( ', ' );

	const args = {
		f: 'q',
		source: 's_q',
		geocode: '',
		q: query,
	};

	return `https://maps.google.com/maps?${ stringify( args ) }`;
}
