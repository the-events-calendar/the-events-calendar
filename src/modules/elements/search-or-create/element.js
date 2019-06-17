/**
 * External dependencies
 */
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { actions, thunks, selectors } from '@moderntribe/events/data/search';

/**
 * Internal dependencies
 */
import SearchOrCreate from './template';

/**
 * Module Code
 */

const setFocus = ( isSelected ) => ( inputRef ) => {
	if (
		isSelected
		&& inputRef.current
		&& document.activeElement !== inputRef.current
	) {
		inputRef.current.focus();
	}
};

const onInputChange = ( dispatchProps, ownProps ) => ( event ) => {
	const { setTerm, search } = dispatchProps;
	const { name, exclude } = ownProps;
	const { value } = event.target;
	setTerm( name, value );
	search( name, {
		term: value,
		exclude,
		perPage: 5,
	} );
};

const onCreateClick = ( term, onCreateNew ) => () => onCreateNew( term );

const onItemClick = ( dispatchProps, ownProps ) => ( item ) => () => {
	const { clearBlock } = dispatchProps;
	const { name, onItemSelect } = ownProps;
	onItemSelect( item.id, item );
	clearBlock( name );
};

const mapStateToProps = ( state, props ) => ( {
	term: selectors.getSearchTerm( state, props ),
	isLoading: selectors.getIsLoading( state, props ),
	posts: selectors.getResults( state, props ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	...bindActionCreators( actions, dispatch ),
	...bindActionCreators( thunks, dispatch ),
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => ( {
	...ownProps,
	...stateProps,
	...dispatchProps,
	setFocus: setFocus( ownProps.isSelected ),
	onInputChange: onInputChange( dispatchProps, ownProps ),
	onCreateClick: onCreateClick( stateProps.term, ownProps.onCreateNew ),
	onItemClick: onItemClick( dispatchProps, ownProps ),
} );

export default connect(
	mapStateToProps,
	mapDispatchToProps,
	mergeProps
)( SearchOrCreate );
