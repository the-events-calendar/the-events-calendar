/**
 * External dependencies
 */
import { noop } from 'lodash';
import { Component } from 'react';

export const select = noop;
export const withSelect = () => ( component ) => component;
export const withDispatch = () => ( component ) => component;
