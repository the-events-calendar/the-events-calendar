import PropTypes from 'prop-types';

/**
 * External dependencies
 */
import React from 'react';
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';

import './style.pcss';

/**
 * The loading element.
 *
 * @param {Object} props The component props.
 * @param {string} props.className The class name to add to the container.
 * @returns {JSX.Element} The spinner component.
 */
const Loading = ( { className } ) => {
	return (
		<span className={ classNames( [ 'tribe-editor__spinner-container', className ] ) }>
			<Spinner />
		</span>
	);
};

Loading.propTypes = {
	className: PropTypes.string,
};

export default Loading;
