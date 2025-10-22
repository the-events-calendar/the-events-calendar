/**
 * External dependencies
 */
import moment from 'moment-timezone';
import React from 'react';
import renderer from 'react-test-renderer';
import $ from 'jquery';

global.jQuery = $;
global.$ = $;
global.wp = {
	element: React,
	api: {},
	apiRequest: () => $.Deferred(),
	components: {},
	data: {},
	blockEditor: {},
	editor: {},
	hooks: {
		addAction: jest.fn(),
		addFilter: jest.fn(),
		removeAction: jest.fn(),
		removeFilter: jest.fn(),
		doAction: jest.fn(),
		applyFilters: jest.fn( ( tag, value ) => value ),
		hasAction: jest.fn( () => false ),
		hasFilter: jest.fn( () => false ),
	},
};
global.renderer = renderer;
global.console = {
	error: jest.fn(),
	log: jest.fn(),
	warning: jest.fn(),
};

moment.tz.setDefault( 'UTC' );

// Mock webpack public path global for tests.
global.__webpack_public_path__ = '';
