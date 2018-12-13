/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/datetime';
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

describe( '[STORE] - Datetime types', () => {
	it( 'Should return the types values', () => {
		expect( types.SET_START_DATE_TIME ).toBe( `${ PREFIX_EVENTS_STORE }/SET_START_DATE_TIME` );
		expect( types.SET_END_DATE_TIME ).toBe( `${ PREFIX_EVENTS_STORE }/SET_END_DATE_TIME` );
		expect( types.SET_START_TIME ).toBe( `${ PREFIX_EVENTS_STORE }/SET_START_TIME` );
		expect( types.SET_END_TIME ).toBe( `${ PREFIX_EVENTS_STORE }/SET_END_TIME` );
		expect( types.SET_DATE_RANGE ).toBe( `${ PREFIX_EVENTS_STORE }/SET_DATE_RANGE` );
		expect( types.SET_START_TIME_INPUT ).toBe( `${ PREFIX_EVENTS_STORE }/SET_START_TIME_INPUT` );
		expect( types.SET_END_TIME_INPUT ).toBe( `${ PREFIX_EVENTS_STORE }/SET_END_TIME_INPUT` );
		expect( types.SET_NATURAL_LANGUAGE_LABEL )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_NATURAL_LANGUAGE_LABEL` );
		expect( types.SET_MULTI_DAY ).toBe( `${ PREFIX_EVENTS_STORE }/SET_MULTI_DAY` );
		expect( types.SET_ALL_DAY ).toBe( `${ PREFIX_EVENTS_STORE }/SET_ALL_DAY` );
		expect( types.SET_SEPARATOR_DATE ).toBe( `${ PREFIX_EVENTS_STORE }/SET_SEPARATOR_DATE` );
		expect( types.SET_SEPARATOR_TIME ).toBe( `${ PREFIX_EVENTS_STORE }/SET_SEPARATOR_TIME` );
		expect( types.SET_TIME_ZONE ).toBe( `${ PREFIX_EVENTS_STORE }/SET_TIME_ZONE` );
		expect( types.SET_TIMEZONE_VISIBILITY )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_TIMEZONE_VISIBILITY` );
		expect( types.SET_TIMEZONE_LABEL ).toBe( `${ PREFIX_EVENTS_STORE }/SET_TIMEZONE_LABEL` );
		expect( types.SET_DATE_INPUT_VISIBILITY )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_DATE_INPUT_VISIBILITY` );
		expect( types.SET_DATETIME_BLOCK_EDITABLE_STATE )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_DATETIME_BLOCK_EDITABLE_STATE` );
	} );
} );
