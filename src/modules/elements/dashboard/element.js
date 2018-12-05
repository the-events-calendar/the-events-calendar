/**
 * External Dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import './style.pcss';

/**
 * Enumeration with the available directions.
 *
 * @type {{up: string, down: string}}
 */
export const directions = {
	up: 'up',
	down: 'down',
};

/**
 * Usage of this component:
 *
 * <Dashboard isOpen={true} className="custom" direction={directions.up}>
 *   <AnyComponent></AnyComponent>
 * </Dashboard
 */
const Dashboard = ({ className, direction, isOpen, children }) => {
	const containerClasses = classNames(
		'tribe-editor__dashboard__container',
		`tribe-editor__dashboard__container--${ direction }`,
		{ 'tribe-editor__dashboard__container--open': isOpen },
		className,
	);

	return (
		<div className={ containerClasses }>
			<div className="tribe-editor__dashboard">
				{ children }
			</div>
		</div>
	);
}

Dashboard.defaultProps = {
	isOpen: false,
	className: '',
	direction: directions.down,
	children: null,
};

Dashboard.propTypes = {
	isOpen: PropTypes.bool,
	className: PropTypes.string,
	direction: PropTypes.oneOf( Object.keys( directions ) ),
	children: PropTypes.element,
};

export default Dashboard;
