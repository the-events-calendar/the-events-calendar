/**
 * External dependencies
 */
import React, { Fragment } from 'react';

/**
 * Internal dependencies
 */
import Content from './content';
import Controls from './controls';
import Dashboard from './dashboard';
import './style.pcss';

/**
 * Module Code
 */

const EventDateTime = ( props ) => {
	return (
		<Fragment>
			<Controls { ...props } />
			<section className="tribe-editor__subtitle tribe-editor__date-time tribe-common__plugin-block-hook">
				<Content { ...props } />
				<Dashboard { ...props } />
			</section>
		</Fragment>
	);
};

export default EventDateTime;
