/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';
import {
	actions as dateTimeActions,
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import {
	actions as priceActions,
	selectors as priceSelectors,
	utils as priceUtils,
} from '@moderntribe/events/data/blocks/price';
import {
	actions as websiteActions,
	selectors as websiteSelectors,
} from '@moderntribe/events/data/blocks/website';
import {
	selectors as organizerSelectors,
} from '@moderntribe/events/data/blocks/organizers';
import { withStore } from '@moderntribe/common/hoc';
import ClassicEventDetails from './template';
import dateTimeBlock from '@moderntribe/events/blocks/event-datetime';

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
	currencyCode: priceSelectors.getCode( state ),
	url: websiteSelectors.getUrl( state ),
	organizers: organizerSelectors.getOrganizersInClassic( state ),
} );

const mapDispatchToProps = ( dispatch, ownProps ) => ( {
	setAllDay: ( value ) => {
		ownProps.setAttributes( { allDay: value } );
		dispatch( dateTimeActions.setAllDay( value ) );
	},
	setCost: ( cost ) => {
		ownProps.setAttributes( { cost } );
		dispatch( priceActions.setCost( cost ) );
	},
	setCurrencyPosition: ( value ) => {
		const position = priceUtils.getPosition( value );
		ownProps.setAttributes( { currencyPosition: position } );
		dispatch( priceActions.setPosition( position ) );
	},
	setSymbol: ( symbol ) => {
		ownProps.setAttributes( { currencySymbol: symbol } );
		dispatch( priceActions.setSymbol( symbol ) );
	},
	setCode: ( code ) => {
		ownProps.setAttributes( { currencyCode: code } );
		dispatch( priceActions.setCode( code ) );
	},
	setWebsite: ( url ) => {
		ownProps.setAttributes( { url } );
		dispatch( websiteActions.setWebsite( url ) );
	},
	toggleDashboardDateTime: () => {
		// there may be a better way to do this, but for now there's no way to access context
		// outside of the provider.
		const blocks = globals.wpDataSelectCoreEditor().getBlocks();

		const filteredBlocks = blocks.filter( ( block ) => {
			return block.name === `tribe/${ dateTimeBlock.id }`;
		} );

		if ( ! filteredBlocks.length ) {
			return;
		}

		const dateTimeButton = document
			.querySelector( `[data-block="${ filteredBlocks[ 0 ].clientId }"]` )
			.getElementsByClassName( 'tribe-editor__subtitle__headline-button' )[ 0 ];

		if ( ! dateTimeButton ) {
			return;
		}

		// simulate click event on date time button to open dashboard of first date time block
		dateTimeButton.click();
	},
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps ),
)( ClassicEventDetails );
