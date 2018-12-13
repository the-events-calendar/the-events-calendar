/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';

import {
	InspectorControls,
} from '@wordpress/editor';

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

export default class EventTags extends Component {
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
			<section key="event-tags-box" className="tribe-editor__block">
				<div className="tribe-editor__event-tags">
					{ this.renderList() }
				</div>
			</section>
		);
	}

	renderList() {
		return (
			<TermsList
				slug="post_tag"
				label={ __( 'Tags', 'the-events-calendar' ) }
				renderEmpty={ __( 'Add tags in document settings', 'the-events-calendar' ) }
			/>
		);
	}
}
