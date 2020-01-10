/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose, bindActionCreators } from 'redux';

/**
 * Internal dependencies
 */
import {
	actions as dateTimeActions,
	thunks as dateTimeThunks,
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import { DEFAULT_STATE as DATETIME_DEFAULT_STATE } from '@moderntribe/events/data/blocks/datetime/reducer';
import {
	actions as priceActions,
	selectors as priceSelectors,
} from '@moderntribe/events/data/blocks/price';
import { DEFAULT_STATE as PRICE_DEFAULT_STATE } from '@moderntribe/events/data/blocks/price/reducer';
import {
	actions as websiteActions,
	selectors as websiteSelectors,
} from '@moderntribe/events/data/blocks/website';
import { DEFAULT_STATE as WEBSITE_DEFAULT_STATE } from '@moderntribe/events/data/blocks/website/reducer';
import {
	actions as classicActions,
	selectors as classicSelectors,
} from '@moderntribe/events/data/blocks/classic';

import {
	actions as organizersActions,
	selectors as organizerSelectors,
} from '@moderntribe/events/data/blocks/organizers';
import { actions as UIActions } from '@moderntribe/events/data/ui';
import { withStore, withSaveData } from '@moderntribe/common/hoc';
import ClassicEventDetails from './template';

/**
 * Module Code
 */

const mapStateToProps = ( state ) => ( {
	start: dateTimeSelectors.getStart( state ),
	end: dateTimeSelectors.getEnd( state ),
	multiDay: dateTimeSelectors.getMultiDay( state ),
	allDay: dateTimeSelectors.getAllDay( state ),
	separatorDate: dateTimeSelectors.getDateSeparator( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	timezone: dateTimeSelectors.getTimeZone( state ),
	cost: priceSelectors.getPrice( state ),
	currencyPosition: priceSelectors.getPosition( state ),
	currencySymbol: priceSelectors.getSymbol( state ),
	url: websiteSelectors.getUrl( state ),
	detailsTitle: classicSelectors.detailsTitleSelector( state ),
	organizerTitle: classicSelectors.organizerTitleSelector( state ),
	organizers: organizerSelectors.getOrganizersInClassic( state ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	...bindActionCreators( dateTimeActions, dispatch ),
	...bindActionCreators( dateTimeThunks, dispatch ),
	...bindActionCreators( UIActions, dispatch ),
	...bindActionCreators( priceActions, dispatch ),
	...bindActionCreators( websiteActions, dispatch ),
	...bindActionCreators( classicActions, dispatch ),
	dispatch,
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => {
	const { dispatch, ...restDispatchProps } = dispatchProps;

	return {
		...ownProps,
		...stateProps,
		...restDispatchProps,
		setInitialState: ( props ) => {
			/**
			 * @todo: this method causes many problems. need to remove and hydrate initial state properly.
			 */
			const { get } = props;

			dispatch( classicActions.setInitialState( props ) );
			dispatch( organizersActions.setOrganizersInClassic( get( 'organizers', [] ) ) );

			// if current state start is not the same as default state, do not update
			// otherwise, update start state if attribute value is not the same as default state.
			const start = get( 'start', DATETIME_DEFAULT_STATE.start );
			if (
				stateProps.start === DATETIME_DEFAULT_STATE.start
					&& start !== DATETIME_DEFAULT_STATE.start
			) {
				dispatch( dateTimeActions.setStartDateTime( start ) );
			}

			// if current state end is not the same as default state, do not update
			// otherwise, update end state if attribute value is not the same as default state.
			const end = get( 'end', DATETIME_DEFAULT_STATE.end );
			if (
				stateProps.end === DATETIME_DEFAULT_STATE.end
					&& end !== DATETIME_DEFAULT_STATE.end
			) {
				dispatch( dateTimeActions.setStartDateTime( end ) );
			}

			// if current state allDay is not the same as default state, do not update
			// otherwise, update allDay state if attribute value is not the same as default state.
			const allDay = get( 'allDay', DATETIME_DEFAULT_STATE.allDay );
			if (
				stateProps.allDay === DATETIME_DEFAULT_STATE.allDay
					&& allDay !== DATETIME_DEFAULT_STATE.allDay
			) {
				dispatch( dateTimeActions.setAllDay( allDay ) );
			}

			// if current state url is not the same as default state, do not update
			// otherwise, update url state if attribute value is not the same as default state.
			const url = get( 'url', WEBSITE_DEFAULT_STATE.url );
			if (
				stateProps.url === WEBSITE_DEFAULT_STATE.url
					&& url !== WEBSITE_DEFAULT_STATE.url
			) {
				dispatch( websiteActions.setWebsite( url ) );
			}

			// if current state cost is not the same as default state, do not update
			// otherwise, update cost state if attribute value is not the same as default state.
			const cost = get( 'cost', PRICE_DEFAULT_STATE.cost );
			if (
				stateProps.cost === WEBSITE_DEFAULT_STATE.cost
					&& cost !== WEBSITE_DEFAULT_STATE.cost
			) {
				dispatch( priceActions.setCost( cost ) );
			}

			// if current state currencySymbol is not the same as default state, do not update
			// otherwise, update currencySymbol state if attribute value is not the same as default state.
			const currencySymbol = get( 'currencySymbol', PRICE_DEFAULT_STATE.symbol );
			if (
				stateProps.currencySymbol === WEBSITE_DEFAULT_STATE.symbol
					&& currencySymbol !== WEBSITE_DEFAULT_STATE.symbol
			) {
				dispatch( priceActions.setSymbol( currencySymbol ) );
			}

			// if current state currencyPosition is not the same as default state, do not update
			// otherwise, update currencyPosition state if attribute value is not the same as default state.
			const currencyPosition = get( 'currencyPosition', PRICE_DEFAULT_STATE.position );
			if (
				stateProps.currencyPosition === WEBSITE_DEFAULT_STATE.position
					&& currencyPosition !== WEBSITE_DEFAULT_STATE.position
			) {
				dispatch( priceActions.setPosition( currencyPosition ) );
			}
		},
	};
};

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps, mergeProps ),
	withSaveData(),
)( ClassicEventDetails );
