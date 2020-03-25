/**
 * External dependencies
 */
import reducer from './reducers';

import { globals } from '@moderntribe/common/utils';
import { plugins } from '@moderntribe/common/data';
import { store } from '@moderntribe/common/store';
import * as blocks from './blocks';
import initSagas from './sagas';

const { actions, constants } = plugins;

const setInitialState = ( data ) => {

};

export const initStore = () => {
	/**
	 * @todo: remove this once data is used from window object.
	 */
	const data = {
		isCleanNewPost: false,
		meta: {
			_price: "",
			_stock: "",
			_tribe_ticket_header: "2619",
			_tribe_default_ticket_provider: "",
			_tribe_ticket_capacity: "",
			_ticket_start_date: "",
			_ticket_end_date: "",
			_tribe_ticket_show_description: "",
			_tribe_ticket_show_not_going: false,
			_tribe_ticket_use_global_stock: "",
			_tribe_ticket_global_stock_level: "",
			_global_stock_mode: "",
			_global_stock_cap: "",
			_tribe_rsvp_for_event: "",
			_tribe_ticket_going_count: "",
			_tribe_ticket_not_going_count: "",
			_tribe_tickets_list: [],
			_tribe_ticket_has_attendee_info_fields: false,
			_EventAllDay: false,
			_EventTimezone: "UTC+0",
			_EventStartDate: "2020-01-11 15:30:00",
			_EventEndDate: "2020-01-11 15:30:00",
			_EventStartDateUTC: "2020-01-11 15:30:00",
			_EventEndDateUTC: "2020-01-11 15:30:00",
			_EventShowMap: false,
			_EventShowMapLink: false,
			_EventURL: "www.abc.com",
			_EventCost: "12",
			_EventCostDescription: "",
			_EventCurrencySymbol: "$",
			_EventCurrencyPosition: "suffix",
			_EventDateTimeSeparator: " @ ",
			_EventTimeRangeSeparator: " - ",
			_EventOrganizerID: [2431, 3039, 3042, 3076],
			_EventVenueID: 1982,
			_OrganizerEmail: "",
			_OrganizerPhone: "",
			_OrganizerWebsite: "",
			_VenueAddress: "",
			_VenueCity: "",
			_VenueCountry: "",
			_VenueProvince: "",
			_VenueZip: "",
			_VenuePhone: "",
			_VenueURL: "",
			_VenueStateProvince: "",
			_VenueLat: "",
			_VenueLng: "",
			_tribe_blocks_recurrence_rules: "[]",
			_tribe_blocks_recurrence_exclusions: "[]",
			_tribe_blocks_recurrence_description: "Recurring Event",
			_ecp_custom_3: "",
			_ecp_custom_4: "",
			_ecp_custom_5: "",
			__ecp_custom_6: ["One", "Three", "Two"],
			_ecp_custom_6: "One|Two|Three",
			_ecp_custom_7: "One",
		},
	};

	if ( ! data.isCleanNewPost ) {
		setInitialState( data );
	}

	const { dispatch, injectReducers } = store;

	initSagas();
	injectReducers( { [ constants.EVENTS_PLUGIN ]: reducer } );
	dispatch( actions.addPlugin( constants.EVENTS_PLUGIN ) );
};

export const getStore = () => store;

export { blocks };
