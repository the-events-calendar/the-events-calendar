/**
 * Internal dependencies
 */
import { DEFAULT_STATE } from '@moderntribe/events/data/blocks/sharing/reducer';
import { selectors } from '@moderntribe/events/data/blocks/sharing';

const state = {
	events: {
		blocks: {
			sharing: DEFAULT_STATE,
		},
	}
};

describe( '[STORE] - Sharing selectors', () => {
	it( 'Should return the block', () => {
		expect( selectors.sharingSelector( state ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should return google calendar label', () => {
		expect( selectors.googleCalendarLabelSelector( state ) )
			.toEqual( DEFAULT_STATE.googleCalendarLabel );
	} );

	it( 'Should return the iCal label', () => {
		expect( selectors.iCalLabelSelector( state ) ).toEqual( DEFAULT_STATE.iCalLabel );
	} );

	it( 'Should return if has google calendar label', () => {
		expect( selectors.hasGooglecalendarLabel( state ) ).toEqual( DEFAULT_STATE.hasGoogleCalendar );
	} );

	it( 'Should return if has iCal label', () => {
		expect( selectors.hasIcalSelector( state ) ).toEqual( DEFAULT_STATE.hasiCal );
	} );
} );
