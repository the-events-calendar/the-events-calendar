/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * Internal Dependencies
 */
import { constants } from '@moderntribe/common/data/plugins';
import { PluginBlockHooks } from '@moderntribe/common/components';

const PLUGIN_TEMPLATES = {
	[ constants.EVENTS_PRO_PLUGIN ]: [
		[ 'tribe/event-pro-recurrence-description', {}],
	],
};

const ContentHook = () => {
	return (
		<PluginBlockHooks
			pluginTemplates={ PLUGIN_TEMPLATES }
			templateLock="all"
		/>
	);
};

ContentHook.propTypes = {};

export default ContentHook;
