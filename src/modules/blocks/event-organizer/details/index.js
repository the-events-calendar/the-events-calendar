/**
 * External dependencies
 */
import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { noop } from 'lodash';
import { decode } from 'he';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { Dashicon } from '@wordpress/components';
import { toFields } from '@moderntribe/events/elements/organizer-form/utils';
import { Close as CloseIcon } from '@moderntribe/common/icons';
import './style.pcss';

/**
 * Internal dependencies
 */

export default class OrganizerDetails extends Component {
	static defaultProps = {
		organizer: {},
		edit: noop,
		remove: noop,
		selected: false,
	};

	static propTypes = {
		organizer: PropTypes.object,
		edit: PropTypes.func,
		remove: PropTypes.func,
		selected: PropTypes.bool,
	};

	constructor( props ) {
		super( ...arguments );
	}

	render() {
		return (
			<div className="tribe-editor__organizer__details">
				{ this.renderDetails() }
				{ this.renderActions() }
			</div>
		);
	}

	renderDetails() {
		const { organizer } = this.props;
		const fields = toFields( organizer );
		const { title, website, email, phone } = fields;
		return (
			<Fragment>
				<div className="tribe-editor__organizer__title">
					<h3 className="tribe-editor__organizer__title-heading" onClick={ this.maybeEdit }>
						{ decode( title ) }
					</h3>
					{ this.renderEdit() }
				</div>
				{ phone && <p>{ phone }</p> }
				{ website && <p>{ website }</p> }
				{ email && <p>{ email }</p> }
			</Fragment>
		);
	}

	maybeEdit = () => {
		const { volatile, edit } = this.props;
		if ( ! volatile ) {
			return;
		}
		edit();
	};

	renderEdit = () => {
		const { edit, selected, volatile } = this.props;

		if ( ! selected || ! volatile ) {
			return null;
		}

		return (
			<button onClick={ edit }>
				<Dashicon icon="edit" />
			</button>
		);
	}

	renderActions() {
		const { remove, selected } = this.props;

		if ( ! selected ) {
			return null;
		}

		return (
			<div className="tribe-editor__organizer__actions">
				<button
					className="tribe-editor__organizer__actions--close"
					onClick={ remove }
				>
					<CloseIcon />
				</button>
			</div>
		);
	}
}
