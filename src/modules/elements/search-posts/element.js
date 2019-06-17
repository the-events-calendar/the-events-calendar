/**
 * External dependencies
 */
import { compose } from 'redux';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { actions, thunks, selectors } from '@moderntribe/events/data/search';
import { withStore } from '@moderntribe/common/hoc';
import SearchPosts from './template';

/**
 * Module Code
 */

const onMount = ( dispatch, ownProps ) => () => {
	const { name, postType, exclude } = ownProps;

	dispatch( actions.addBlock( name ) );
	dispatch( actions.setSearchPostType( name, postType ) );
	dispatch( thunks.search( name, {
		term: '',
		exclude,
	} ) );
};

const onInputChange = ( dispatch, ownProps ) => ( event ) => {
	const { name, exclude } = ownProps;
	const { value } = event.target;

	dispatch( actions.setTerm( name, value ) );
	dispatch( thunks.search( name, {
		term: value,
		exclude,
	} ) );
};

const onItemClick = ( dispatch, ownProps ) => ( onClose ) => ( item ) => {
	const { name, onItemSelect } = ownProps;
	dispatch( actions.setTerm( name, '' ) );

	if ( onItemSelect ) {
		onItemSelect( item.id, item );
	}

	onClose();
};

const onDropdownScroll = ( stateProps, dispatchProps, ownProps ) => ( event ) => {
	const { target } = event;
	const { scrollHeight, scrollTop } = target;
	const scrollPercentage = ( scrollTop / ( scrollHeight - target.offsetHeight ) ) * 100;

	if ( scrollPercentage > 75 ) {
		const { term, page } = stateProps;
		const { name, exclude } = ownProps;
		dispatchProps.dispatch( thunks.search( name, {
			term,
			exclude,
			populated: true,
			page: page + 1,
		} ) );
	}
};

const onDropdownToggle = ( stateProps, dispatchProps, ownProps ) => ( isOpen ) => {
	if ( ! isOpen && stateProps.term !== '' ) {
		dispatchProps.dispatch( actions.setTerm( ownProps.name, '' ) );
	}
};

const mapStateToProps = ( state, props ) => ( {
	term: selectors.getSearchTerm( state, props ),
	isLoading: selectors.getIsLoading( state, props ),
	results: selectors.getResults( state, props ),
	page: selectors.getPage( state, props ),
} );

const mapDispatchToProps = ( dispatch, ownProps ) => ( {
	onMount: onMount( dispatch, ownProps ),
	onInputChange: onInputChange( dispatch, ownProps ),
	onItemClick: onItemClick( dispatch, ownProps ),
	dispatch,
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => ( {
	...ownProps,
	...stateProps,
	...dispatchProps,
	onDropdownScroll: onDropdownScroll( stateProps, dispatchProps, ownProps ),
	onDropdownToggle: onDropdownToggle( stateProps, dispatchProps, ownProps ),
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps, mergeProps ),
)( SearchPosts );
