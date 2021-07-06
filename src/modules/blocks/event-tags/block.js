/**
 * External dependencies
 */
import React from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TermsList } from '@moderntribe/events/elements';
import './style.pcss';

/**
 * Module Code
 */

const EventTags = () => (
	<section className="tribe-editor__block">
		<div className="tribe-editor__event-tags">
			<TermsList
				slug="post_tag"
				label={ __( 'Tags', 'the-events-calendar' ) }
				renderEmpty={ __( 'Add tags in document settings', 'the-events-calendar' ) }
			/>
		</div>
	</section>
);

export default EventTags;
