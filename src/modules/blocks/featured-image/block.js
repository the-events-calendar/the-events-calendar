/**
 * External dependencies
 */
import React from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { Placeholder, Spinner } from '@wordpress/components';

/**
 * Internal dependencies
 */

/**
 * Module Code
 */

class FeaturedImage extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {
		return [
			this.renderUI(),
		];
	}

	renderUI() {
		return (
			<section key="featured-image" className="tribe-editor__block">
				<div className="tribe-editor__featured-image">
					{ this.renderImage() }
				</div>
			</section>
		);
	}

	renderImage() {
		const { image } = this.props;
		if ( null === image ) {
			return this.renderPlaceholder();
		}

		if ( undefined === image ) {
			return this.renderLoading();
		}

		return (
			<img src={ image.source_url } alt={ __( 'Featured Image', 'the-events-calendar' ) } />
		);
	}

	renderPlaceholder() {
		return (
			<Placeholder
				style={ { minHeight: 150 } }
				key="placeholder"
				icon="format-image"
				instructions={
					__(
						'Add a Featured Image from the Document Settings sidebar',
						'the-events-calendar'
					)
				}
			>
			</Placeholder>
		);
	}

	renderLoading() {
		return (
			<Placeholder
				style={ { minHeight: 150 } }
				key="placeholder"
				instructions={ __( 'Loading the Image', 'the-events-calendar' ) }
			>
				<Spinner />
			</Placeholder>
		);
	}
}

const applySelect = withSelect( ( select, props ) => {
	const { getMedia, getPostType } = select( 'core' );
	const { getEditedPostAttribute } = select( 'core/editor' );
	const featuredImageId = getEditedPostAttribute( 'featured_media' );

	return {
		image: featuredImageId ? getMedia( featuredImageId ) : null,
	};
} );

export default applySelect( FeaturedImage );
