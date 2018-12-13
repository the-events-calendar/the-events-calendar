/**
 * External dependencies
 */
import { isEmpty, get } from 'lodash';
import { getStateName } from '@moderntribe/events/editor/utils/geo-data';
import { editorDefaults } from '@moderntribe/common/utils/globals';

export const getAddress = ( details = {} ) => {
	const { meta = {} } = details;

	if ( isEmpty( meta ) ) {
		return {};
	}

	return {
		street: get( meta, '_VenueAddress', '' ),
		city: get( meta, '_VenueCity', '' ),
		province: get( meta, '_VenueProvince', '' ),
		zip: get( meta, '_VenueZip', '' ),
		country: get( meta, '_VenueCountry', '' ),
	};
};

export const getCoordinates = ( details = {} ) => {
	const { meta = {} } = details;
	const { _VenueLat = '', _VenueLng = '' } = meta;
	const lat = parseFloat( _VenueLat );
	const lng = parseFloat( _VenueLng );

	return {
		lat: isNaN( lat ) ? null : lat,
		lng: isNaN( lng ) ? null : lng,
	};
};

export const setDefault = ( value, defaultValue ) => value === '' ? defaultValue : value;

/**
 * Get Venue Country
 */
export function getVenueCountry( meta ) {
	let country = get( meta, '_VenueCountry', '' );

	if ( '' === country ) {
		const defaultCountry = editorDefaults().venueCountry;
		const [ countryName ] = defaultCountry || [];
		country = countryName || '';
	}
	return country;
}

/**
 * Get Venue State/Province
 */
export function getVenueStateProvince( meta ) {
	let stateProvince = get( meta, '_VenueStateProvince', '' );

	if ( '' === stateProvince ) {

		const country = getVenueCountry( meta );

		if (
			'US' === country
			|| 'United States' === country
		) {
			stateProvince = getStateName( 'US', editorDefaults().venueState );
		} else {
			stateProvince = editorDefaults().venueProvince;
		}
	}
	return stateProvince;
}
