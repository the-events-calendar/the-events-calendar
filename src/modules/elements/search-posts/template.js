/**
 * External dependencies
 */
import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { uniqueId } from 'lodash';
import classNames from 'classnames';
import { decode } from 'he';

/**
 * WordPress dependencies
 */
import {
	Dropdown,
	IconButton,
	Dashicon,
	Spinner,
	Placeholder,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.pcss';

/**
 * Module Code
 */

class SearchPosts extends Component {
	static propTypes = {
		name: PropTypes.string.isRequired,
		postType: PropTypes.string.isRequired,
		exclude: PropTypes.array.isRequired,
		searchLabel: PropTypes.string,
		iconLabel: PropTypes.string,
		term: PropTypes.string.isRequired,
		isLoading: PropTypes.bool.isRequired,
		results: PropTypes.array.isRequired,
		page: PropTypes.number.isRequired,
		onMount: PropTypes.func.isRequired,
		onInputChange: PropTypes.func.isRequired,
		onItemClick: PropTypes.func.isRequired,
		onDropdownScroll: PropTypes.func.isRequired,
		onDropdownToggle: PropTypes.func.isRequired,
	};

	componentDidMount() {
		this.props.onMount();
	}

	renderToggle = ( { onToggle } ) => (
		<IconButton
			className="tribe-editor__btn"
			label={ this.props.iconLabel }
			onClick={ onToggle }
			icon={ <Dashicon icon="search" /> }
		/>
	);

	renderList = ( onClose ) => {
		const { results, isLoading, onItemClick } = this.props;

		if ( isLoading ) {
			return (
				<Placeholder key="placeholder">
					<Spinner />
				</Placeholder>
			);
		}

		return (
			<ul className="tribe-editor__search-posts__results-list">
				{ results.map( ( item ) => (
					<li
						key={ `post-${ item.id }` }
						className="tribe-editor__search-posts__results-list-item"
					>
						<button
							className="tribe-editor__search-posts__results-list-item-button"
							onClick={ () => onItemClick( onClose )( item ) }
						>
							{ decode( item.title.rendered ) }
						</button>
					</li>
				) ) }
			</ul>
		);
	}

	renderSearchInput() {
		const { term, searchLabel, onInputChange } = this.props;
		const instanceId = uniqueId( 'search-' );

		return (
			<div>
				<label htmlFor={ `editor-inserter__${ instanceId }` } className="screen-reader-text">
					{ searchLabel }
				</label>
				<input
					id={ `editor-inserter__${ instanceId }` }
					type="search"
					placeholder={ searchLabel }
					value={ term }
					className="editor-inserter__search"
					onChange={ onInputChange }
				/>
			</div>
		);
	}

	renderDropdown = ( { isOpen, onClose } ) => (
		<div
			className={ classNames( 'tribe-editor__search-posts' ) }
			aria-expanded={ isOpen }
		>
			{ this.renderSearchInput() }
			<div
				className={ classNames( 'tribe-editor__search-posts__results' ) }
				onScroll={ this.props.onDropdownScroll }
			>
				{ this.renderList( onClose ) }
			</div>
		</div>
	);

	render() {
		return (
			<Dropdown
				className="tribe-editor__dropdown"
				position="bottom center"
				contentClassName="tribe-editor__dropdown-dialog"
				onToggle={ this.props.onDropdownToggle }
				renderToggle={ this.renderToggle }
				renderContent={ this.renderDropdown }
			/>
		);
	}
}

export default SearchPosts;
