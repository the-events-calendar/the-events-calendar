/**
 * External dependencies
 */
import moment from 'moment-timezone';
import React from 'react';
import renderer from 'react-test-renderer';
import $ from 'jquery';
import Enzyme, { shallow, render, mount } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';

Enzyme.configure( { adapter: new Adapter() } );

global.jQuery = $;
global.$ = $;
global.wp = {
	element: React,
};
global.shallow = shallow;
global.render = render;
global.mount = mount;
global.renderer = renderer;

moment.tz.setDefault( 'UTC' );
