/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import {
	actions as dateTimeActions,
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import { withStore } from '@moderntribe/common/hoc';
import HumanReadableInput from './template';

const mapStateToProps = ( state ) => ( {
	naturalLanguageLabel: dateTimeSelectors.getNaturalLanguageLabel( state ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	onChange: ( event ) => dispatch( dateTimeActions.setNaturalLanguageLabel( event.target.value ) ),
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps ),
)( HumanReadableInput );
