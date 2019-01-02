/**
 * Internal dependencies
 */
import { selectors } from '@moderntribe/events/data/blocks/datetime';
import { DEFAULT_STATE } from '@moderntribe/events/data/blocks/datetime/reducer';

const state = {
	events: {
		blocks: {
			datetime: DEFAULT_STATE,
		},
	}
};

describe( '[STORE] - Datetime selectors', () => {
	it( 'Should return the block', () => {
		expect( selectors.datetimeSelector( state ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should return the all day', () => {
		expect( selectors.getAllDay( state ) ).toBe( DEFAULT_STATE.allDay );
	} );

	it( 'Should return the start date', () => {
		expect( selectors.getStart( state ) ).toBe( DEFAULT_STATE.start );
	} );

	it( 'Should return the end date', () => {
		expect( selectors.getEnd( state ) ).toBe( DEFAULT_STATE.end );
	} );

	it( 'Should return the start time input', () => {
		expect( selectors.getStartTimeInput( state ) ).toBe( DEFAULT_STATE.startTimeInput );
	} );

	it( 'Should return the end time input', () => {
		expect( selectors.getEndTimeInput( state ) ).toBe( DEFAULT_STATE.endTimeInput );
	} );

	it( 'Should return the timezone', () => {
		expect( selectors.getTimeZone( state ) ).toBe( DEFAULT_STATE.timeZone );
	} );

	it( 'Should return the time zone label', () => {
		expect( selectors.getTimeZoneLabel( state ) ).toBe( DEFAULT_STATE.timeZoneLabel );
	} );

	it( 'Should return the time zone visibility', () => {
		expect( selectors.getTimeZoneVisibility( state ) ).toBe( DEFAULT_STATE.showTimeZone );
	} );

	it( 'Should return the multi day', () => {
		expect( selectors.getMultiDay( state ) ).toBe( DEFAULT_STATE.multiDay );
	} );

	it( 'Should return the date separator', () => {
		expect( selectors.getDateSeparator( state ) ).toBe( DEFAULT_STATE.dateTimeSeparator );
	} );

	it( 'Should return time range separator', () => {
		expect( selectors.getTimeSeparator( state ) ).toBe( DEFAULT_STATE.timeRangeSeparator );
	} );

	it( 'Should return the natural language label', () => {
		expect( selectors.getNaturalLanguageLabel( state ) ).toBe( DEFAULT_STATE.naturalLanguage );
	} );

	it( 'Should return the natural language label', () => {
		expect( selectors.getDateInputVisibility( state ) ).toBe( DEFAULT_STATE.showDateInput );
	} );

	it( 'Should return the editable', () => {
		expect( selectors.isEditable( state ) ).toBe( DEFAULT_STATE.isEditable );
	} );
} );
