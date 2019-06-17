/**
 * External dependencies
 */
import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { isUndefined, isEqual } from 'lodash';

/**
 * Internal dependencies
 */
import { actions, thunks, selectors } from '@moderntribe/events/data/details';

export default ( key = 'clientId' ) => ( WrappedComponent ) => {
	class WithDetails extends Component {
		static propTypes = {
			setDetailsPostType: PropTypes.func,
			fetchDetails: PropTypes.func,
			postType: PropTypes.string,
			isLoading: PropTypes.bool,
			details: PropTypes.object,
		};

		constructor( props ) {
			super( props );
			this.details = {
				id: null,
				type: '',
			};
		}

		componentDidMount() {
			this.fetch();
		}

		componentDidUpdate() {
			this.fetch();
		}

		get id() {
			return this.props[ key ];
		}

		fetch() {
			if ( isUndefined( this.id ) || ! this.id ) {
				return;
			}

			const { setDetailsPostType, postType, fetchDetails } = this.props;
			const tmp = {
				id: this.id,
				postType,
			};

			if ( isEqual( this.details, tmp ) ) {
				return;
			}

			setDetailsPostType( this.id, postType );
			fetchDetails( this.id );
			this.details = tmp;
		}

		render() {
			return <WrappedComponent { ...this.props } />;
		}
	}

	const mapStateToProps = ( state, props ) => {
		const name = props[ key ];
		return {
			details: selectors.getDetails( state, { name } ),
			isLoading: selectors.getIsLoading( state, { name } ),
			volatile: selectors.getVolatile( state, { name } ),
		};
	};

	const mapDispatchToProps = ( dispatch ) => ( {
		...bindActionCreators( actions, dispatch ),
		...bindActionCreators( thunks, dispatch ),
	} );

	return connect( mapStateToProps, mapDispatchToProps )( WithDetails );
};
