/**
 * Internal dependencies
 */
import * as types from '@moderntribe/events/data/blocks/datetime/types';

describe( '[STORE] - Datetime types', () => {
	it( 'Should return the types values', () => {
		expect( types.SET_START_DATE_TIME ).toMatchSnapshot();
		expect( types.SET_END_DATE_TIME ).toMatchSnapshot();
		expect( types.SET_START_TIME ).toMatchSnapshot();
		expect( types.SET_END_TIME ).toMatchSnapshot();
		expect( types.SET_DATE_RANGE ).toMatchSnapshot();
		expect( types.SET_START_TIME_INPUT ).toMatchSnapshot();
		expect( types.SET_END_TIME_INPUT ).toMatchSnapshot();
		expect( types.SET_NATURAL_LANGUAGE_LABEL ).toMatchSnapshot();
		expect( types.SET_MULTI_DAY ).toMatchSnapshot();
		expect( types.SET_ALL_DAY ).toMatchSnapshot();
		expect( types.SET_SEPARATOR_DATE ).toMatchSnapshot();
		expect( types.SET_SEPARATOR_TIME ).toMatchSnapshot();
		expect( types.SET_TIME_ZONE ).toMatchSnapshot();
		expect( types.SET_TIMEZONE_VISIBILITY ).toMatchSnapshot();
		expect( types.SET_TIMEZONE_LABEL ).toMatchSnapshot();
		expect( types.SET_DATETIME_BLOCK_EDITABLE_STATE ).toMatchSnapshot();
	} );
} );
