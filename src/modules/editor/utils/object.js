/**
 * Internal dependencies
 */
import {
	pickBy,
	isString,
	isEmpty,
	mapValues,
} from 'lodash';
import { string } from '@moderntribe/common/utils';

export function removeEmptyStrings( object ) {
	return pickBy( object, ( item ) => {
		// Return any object that is not a string
		if ( ! isString( item ) ) {
			return true;
		}

		// Return only values that are not empty
		return ! isEmpty( item );
	} );
}

export function castBooleanStrings( object ) {
	return mapValues( object, ( value ) => {
		if ( ! isString( value ) ) {
			return value;
		}

		const falsy = string.isFalsy( value );
		const truthy = string.isTruthy( value );

		// We just return the truthy value as if "truthy" is false "falsy" is true which means the
		// string should be converted into false value, otherwise just return regular value
		return ( falsy || truthy ) ? truthy : value;
	} );
}
