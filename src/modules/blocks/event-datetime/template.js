/**
 * External dependencies
 */
import React, { PureComponent, Fragment } from 'react';

/**
 * WordPress dependencies
 */
import { InnerBlocks } from '@wordpress/editor';
import Controls from './controls';

/**
 * Internal dependencies
 */
import DateTimeContext from './context';
import './style.pcss';

/**
 * Module Code
 */

class EventDateTime extends PureComponent {
	get template() {
		return [
			[ 'tribe/event-datetime-dashboard', {} ],
			[ 'tribe/event-datetime-content', {} ],
		];
	}

	render = () => {
		const { isOpen, open } = this.props;

		return (
			<Fragment>
				<Controls />
				<section
					className="tribe-editor__subtitle tribe-editor__date-time tribe-common__plugin-block-hook"
				>
					<DateTimeContext.Provider value={ { isOpen, open } }>
						<InnerBlocks
							template={ this.template }
							templateLock="all"
							templateInsertUpdatesSelection={ false }
						/>
					</DateTimeContext.Provider>
				</section>
			</Fragment>
		);
	}
}

export default EventDateTime;
