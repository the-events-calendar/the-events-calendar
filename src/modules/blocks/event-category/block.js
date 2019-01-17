/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.pcss';

import {
	TermsList,
} from '@moderntribe/events/elements';

/**
 * Module Code
 */

export default class EventCategory extends Component {
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
			<section key="event-category-box" className="tribe-editor__block">
				<div className="tribe-editor__event-category">
					{ this.renderList() }
				</div>
			</section>
		);
	}

	renderList() {
		return (
			<TermsList
				slug="tribe_events_cat"
				label={ __( 'Event Category', 'the-events-calendar' ) }
				renderEmpty={ __( 'Add Event Categories in document settings', 'the-events-calendar' ) }
			/>
		);
	}
}

