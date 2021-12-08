/**
 * Internal dependencies
 */
import * as selectors from '@moderntribe/events/data/blocks/datetime/selectors';
import { DEFAULT_STATE } from '@moderntribe/events/data/blocks/datetime/reducer';

jest.mock( '@moderntribe/common/utils/globals', () => {
	const original = jest.requireActual( '@moderntribe/common/utils/globals' );
	return {
		__esModule: true,
		...original,
		postObjects: () => ( {
			tribe_events: {
				tribe_start_date: '',
			},
		} ),
	}
} );

const state = {
	events: {
		blocks: {
			datetime: {
				...DEFAULT_STATE,
				start: '2018-07-19 08:00:00',
				end: '2018-07-19 17:00:00',
				naturalLanguageLabel: 'July 19 2018 at 8:00 am - 5:00 pm',
			},
		},
	},
};

describe( '[STORE] - Datetime selectors', () => {
	it( 'Should return the block', () => {
		expect( selectors.datetimeSelector( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the all day', () => {
		expect( selectors.getAllDay( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the start date', () => {
		expect( selectors.getStart( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the end date', () => {
		expect( selectors.getEnd( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the start time input', () => {
		expect( selectors.getStartTimeInput( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the end time input', () => {
		expect( selectors.getEndTimeInput( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the timezone', () => {
		expect( selectors.getTimeZone( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the time zone visibility', () => {
		expect( selectors.getTimeZoneVisibility( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the multi day', () => {
		expect( selectors.getMultiDay( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the date separator', () => {
		expect( selectors.getDateSeparator( state ) ).toMatchSnapshot();
	} );

	it( 'Should return time range separator', () => {
		expect( selectors.getTimeSeparator( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the natural language label', () => {
		expect( selectors.getNaturalLanguageLabel( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the editable', () => {
		expect( selectors.isEditable( state ) ).toMatchSnapshot();
	} );

	it( 'Should return is same start end', () => {
		expect( selectors.getSameStartEnd( state ) ).toMatchSnapshot();
	} );
} );
