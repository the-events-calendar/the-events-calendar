/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import AutosizeInput from 'react-18-input-autosize';
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import { input } from '@moderntribe/common/utils';
import './style.pcss';

const Timezone = ( {
	value = '',
	placeholder = '',
	className = 'tribe-editor__timezone-input',
	onChange = noop,
} ) => (
	<AutosizeInput
		className={ className }
		value={ value }
		placeholder={ placeholder }
		onChange={ input.sendValue( onChange ) }
	/>
);

Timezone.propTypes = {
	value      : PropTypes.string,
	placeholder: PropTypes.string,
	onChange   : PropTypes.func,
	className  : PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
};

export default Timezone;
