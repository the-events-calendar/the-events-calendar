/**
 * External dependencies
 */
import React, { PureComponent } from 'react';
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
	static propTypes = {
		onClick: PropTypes.func,
		onKeyDown: PropTypes.func,
	};

	componentDidMount() {
		const { onKeyDown, onClick } = this.props;
		document.addEventListener( 'keydown', onKeyDown );
		document.addEventListener( 'click', onClick );
	}

	componentWillUnmount() {
		const { onKeyDown, onClick } = this.props;
		document.removeEventListener( 'keydown', onKeyDown );
		document.removeEventListener( 'click', onClick );
	}

	get template() {
		return [
			[ 'tribe/event-datetime-dashboard', {}],
			[ 'tribe/event-datetime-content', {}],
		];
	}

	render = () => {
		return [
			<Controls />,
			(
				<section
					key="event-datetime"
					className="tribe-editor__subtitle tribe-editor__date-time tribe-common__plugin-block-hook"
				>
					<InnerBlocks
						template={ this.template }
						templateLock="all"
						templateInsertUpdatesSelection={ false }
					/>
				</section>
			),
		];
	}
}

export default EventDateTime;
