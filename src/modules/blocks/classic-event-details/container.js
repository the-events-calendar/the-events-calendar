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
import {
	actions as priceActions,
	selectors as priceSelectors,
} from '@moderntribe/events/data/blocks/price';
import {
	actions as websiteActions,
	selectors as websiteSelectors,
} from '@moderntribe/events/data/blocks/website';
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
	setInitialState: ( props ) => {
		dispatch( priceActions.setInitialState( props ) );
		dispatch( UIActions.setInitialState( props ) );
		dispatch( websiteActions.setInitialState( props ) );
		dispatch( dateTimeThunks.setInitialState( props ) );
		dispatch( classicActions.setInitialState( props ) );
		const { get } = props;
		dispatch( organizersActions.setOrganizersInClassic( get( 'organizers', [] ) ) );
	},
} );

export default compose(
	withStore(),
	connect(
		mapStateToProps,
		mapDispatchToProps,
	),
	withSaveData(),
)( ClassicEventDetails );
