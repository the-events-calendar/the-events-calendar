/**
 * External dependencies
 */
import Element, { Component } from 'react';
import { compose } from 'redux';

// The real helper splits the string on the tags; the plain string is enough for snapshots.
export const createInterpolateElement = ( text ) => text;

export {
	Element,
	Component,
	compose,
};
