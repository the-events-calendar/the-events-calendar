/**
 * External dependencies
 */
import React, { PureComponent, Fragment } from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { InnerBlocks } from '@wordpress/editor';
import Controls from './controls';

/**
 * Internal dependencies
 */
import './style.pcss';

/**
 * Module Code
 */

class EventDateTime extends PureComponent {
	get template() {
		return [
			[ 'tribe/event-datetime-dashboard', {}],
			[ 'tribe/event-datetime-content', {}],
		];
	}

	render = () => {
		return (
			<Fragment>
				<Controls />
				<section
					className="tribe-editor__subtitle tribe-editor__date-time tribe-common__plugin-block-hook"
				>
					<InnerBlocks
						template={ this.template }
						templateLock="all"
						templateInsertUpdatesSelection={ false }
					/>
				</section>
			</Fragment>
		);
	}
}

export default EventDateTime;
