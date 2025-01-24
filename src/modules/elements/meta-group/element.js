/**
 * External dependencies
 */
import React from 'react';
import classNames from 'classnames';
import { PropTypes } from 'prop-types';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */

/**
 * Module Code
 */
class MetaGroup extends Component {
	static propTypes = {
		className: PropTypes.string,
		children: PropTypes.node,
		groupKey: PropTypes.string,
	};

	static defaultProps = {
		className: '',
		children: null,
		groupKey: '',
	};

	render() {
		const { groupKey, className, children } = this.props;

		const names = classNames( [
			'tribe-editor__meta-group',
			`tribe-editor__meta-group--${ groupKey }`,
			className,
		] );
		return (
			<div
				className={ names }
				key={ groupKey }
			>
				{ children }
			</div>
		);
	}
}

export default MetaGroup;
