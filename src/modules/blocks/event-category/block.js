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

const EventCategory = () => (
	<section className="tribe-editor__block">
		<div className="tribe-editor__event-category">
			<TermsList
				slug="tribe_events_cat"
				label={ __( 'Event Category', 'the-events-calendar' ) }
				renderEmpty={ __( 'Add Event Categories in document settings', 'the-events-calendar' ) }
			/>
		</div>
	</section>
);

export default EventCategory;
