/**
 * External dependencies
 */
import React from 'react';
import { compose } from 'redux';
import { unescape } from 'lodash';
import { PropTypes } from 'prop-types';

/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.pcss';

const getTerms = ( terms, parentId = null ) => {
	if ( ! terms || ! terms.length ) {
		return [];
	}

	if ( parentId === null ) {
		return terms;
	}

	return terms.filter( ( term ) => term.parent === parentId );
};

const getTermListClassName = ( level = 0 ) => `tribe-editor__terms__list tribe-editor__terms__list--level-${ level }`;

const getTermListItemClassName = ( level = 0 ) =>
	`tribe-editor__terms__list-item tribe-editor__terms__list-item--level-${ level }`;

const termName = ( term = {} ) => {
	return term.name ? unescape( term.name ).trim() : __( '(Untitled)', 'the-events-calendar' );
};

const Label = ( { text = '' } ) => (
	<strong className="tribe-editor__terms__label" key="terms-label">
		{ text }{ ' ' }
	</strong>
);

Label.propTypes = {
	text: PropTypes.string,
};

const Empty = ( { renderEmpty = null, id, label } ) =>
	renderEmpty && (
		<div key={ id } className="tribe-editor__terms--empty">
			<Label text={ label } />
			{ renderEmpty }
		</div>
	);

Empty.propTypes = {
	renderEmpty: PropTypes.node,
	id: PropTypes.string,
	label: PropTypes.string,
};

const List = ( {
	terms = [],
	termSeparator = __( ',', 'the-events-calendar' ),
	isLoading = false,
	id = '',
	className = '',
} ) => {
	if ( isLoading ) {
		return <Loading id={ id } className={ className } />;
	}

	return (
		<ul className={ getTermListClassName() }>
			{ terms.map( ( term, index ) => (
				<Item key={ index } term={ term } separator={ termSeparator } isLast={ index + 1 === terms.length } />
			) ) }
		</ul>
	);
};

List.propTypes = {
	terms: PropTypes.array,
	termSeparator: PropTypes.string,
	isLoading: PropTypes.bool,
	id: PropTypes.string,
	className: PropTypes.string,
};

const Separator = ( { delimiter, isLast } ) => ( ! isLast ? <span>{ delimiter }</span> : '' );

Separator.propTypes = {
	delimiter: PropTypes.string,
	isLast: PropTypes.bool,
};

const Item = ( { separator, term, isLast } ) => {
	let termLink = term.link;

	// Modifies the tag slug for the post_tag taxonomy to include an "events" prefix.
	if ( 'post_tag' === term.taxonomy ) {
		termLink = '/events/tag/' + term.slug;
	}

	return (
		<li key={ term.id } className={ getTermListItemClassName( 0 ) }>
			<a
				href={ termLink }
				target="_blank"
				rel="noopener noreferrer"
				className="tribe-editor__terms__list-item-link"
			>
				{ termName( term ) }
			</a>
			<Separator delimiter={ separator } isLast={ isLast } />
		</li>
	);
};

Item.propTypes = {
	separator: PropTypes.string,
	term: PropTypes.object,
	isLast: PropTypes.bool,
};

const Loading = ( { id = '', className = '' } ) => (
	<div key={ id } className={ `tribe-editor__terms__spinner ${ className }` }>
		<Label />
		<Spinner key="terms-spinner" />
	</div>
);

Loading.propTypes = {
	id: PropTypes.string,
	className: PropTypes.string,
};

export const TaxonomiesElement = ( {
	className = '',
	slug = '',
	label = '',
	renderEmpty = null,
	isRequesting = false,
	// eslint-disable-next-line no-unused-vars
	terms = [],
	...rest
} ) => {
	const termsList = getTerms( rest.terms );
	const key = `tribe-terms-${ slug }`;

	if ( ! termsList.length && ! isRequesting ) {
		return <Empty id={ key } renderEmpty={ renderEmpty } label={ label } />;
	}

	return (
		<div key={ key } className={ `tribe-editor__terms ${ className }` }>
			<Label text={ label } />
			<div key="terms" className="tribe-editor__terms__list-wrapper">
				<List terms={ termsList } className={ className } id={ key } isLoading={ isRequesting } />
			</div>
		</div>
	);
};

TaxonomiesElement.propTypes = {
	className: PropTypes.string,
	slug: PropTypes.string,
	label: PropTypes.string,
	renderEmpty: PropTypes.node,
	isRequesting: PropTypes.bool,
	terms: PropTypes.array,
};

const applySelect = withSelect( ( select, props ) => {
	const { getEntityRecords } = select( 'core' );
	const { isResolving } = select( 'core/data' );
	const { slug } = props;
	// post_tags are stored as 'tags' in the editor attributes
	const attributeName = slug === 'post_tag' ? 'tags' : slug;
	const ids = select( 'core/editor' ).getEditedPostAttribute( attributeName );

	if ( ! ids || ids.length === 0 ) {
		return { terms: [], isRequesting: false };
	}

	const query = {
		orderby: 'count',
		order: 'desc',
		include: ids,
	};

	return {
		terms: getEntityRecords( 'taxonomy', slug, query ),
		isRequesting: isResolving( 'core', 'getEntityRecords', [ 'taxonomy', slug, query ] ),
	};
} );

export default compose( applySelect )( TaxonomiesElement );
