/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/sharing';
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

describe( '[STORE] - Sharing types', () => {
	it( 'Should return the value for the types', () => {
		expect( types.SET_HAS_GOOGLE_CALENDAR )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_HAS_GOOGLE_CALENDAR` );
		expect( types.SET_GOOGLE_CALENDAR_LABEL )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_GOOGLE_CALENDAR_LABEL` );
		expect( types.TOGGLE_GOOGLE_CALENDAR )
			.toBe( `${ PREFIX_EVENTS_STORE }/TOGGLE_GOOGLE_CALENDAR` );
		expect( types.SET_HAS_ICAL ).toBe( `${ PREFIX_EVENTS_STORE }/SET_HAS_ICAL` );
		expect( types.TOGGLE_ICAL ).toBe( `${ PREFIX_EVENTS_STORE }/TOGGLE_ICAL` );
		expect( types.SET_ICAL_LABEL ).toBe( `${ PREFIX_EVENTS_STORE }/SET_ICAL_LABEL` );
	} );
} );
