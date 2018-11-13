/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { bindActionCreators, compose } from 'redux';

/**
 * Internal dependencies
 */
import { withStore, withSaveData } from '@moderntribe/common/hoc';
import * as actions from '@moderntribe/events/data/blocks/website/actions';
import * as selectors from '@moderntribe/events/data/blocks/website/selectors';
import EventWebsite from './template';

/**
 * Module Code
 */

const isEmpty = ( label ) => label.trim() === '';

const mapStateToProps = ( state ) => ( {
	url: selectors.getUrl( state ),
	urlLabel: selectors.getLabel( state ),
	isEmpty: isEmpty( selectors.getLabel( state ) ),
} );

const mapDispatchToProps = ( dispatch ) => bindActionCreators( actions, dispatch );

export default compose(
	withStore(),
	connect(
		mapStateToProps,
		mapDispatchToProps,
	),
	withSaveData(),
)( EventWebsite );
