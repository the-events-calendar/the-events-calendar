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
		[ 'tribe/event-pro-recurrence', {}, [
			[ 'tribe/event-pro-recurrence-rule', {}],
			[ 'tribe/event-pro-recurrence-exception', {}],
		] ],
	],
};

const DashboardHook = () => {
	return (
		<PluginBlockHooks
			pluginTemplates={ PLUGIN_TEMPLATES }
			templateLock="all"
		/>
	);
};

DashboardHook.propTypes = {};

export default DashboardHook;
