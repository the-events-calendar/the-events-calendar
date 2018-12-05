/**
 * External dependencies
 */
import React from 'react';
import classNames from 'classnames';

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
	static defaultProps = {
		className: '',
		children: null,
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
