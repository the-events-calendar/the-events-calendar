/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import { withStore } from '@moderntribe/common/hoc';
import * as actions from '@moderntribe/events/data/blocks/website/actions';
import * as selectors from '@moderntribe/events/data/blocks/website/selectors';
import EventWebsite from './template';

/**
 * Module Code
 */

const mapStateToProps = ( state ) => ( {
	url: selectors.getUrl( state ),
} );

const mapDispatchToProps = ( dispatch, ownProps ) => ( {
	setWebsite: ( website ) => {
		ownProps.setAttributes( { url: website } );
		dispatch( actions.setWebsite( website ) );
	},
} );

export default compose( withStore(), connect( mapStateToProps, mapDispatchToProps ) )( EventWebsite );
