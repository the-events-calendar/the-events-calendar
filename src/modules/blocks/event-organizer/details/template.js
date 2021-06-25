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
import { Dashicon } from '@wordpress/components';
import { toFields } from '@moderntribe/events/elements/organizer-form/utils';
import { Close as CloseIcon } from '@moderntribe/common/icons';
import './style.pcss';

/**
 * Internal dependencies
 */

const OrganizerDetails = ( props ) => {
	const maybeEdit = () => {
		const { volatile, edit } = props;
		if ( ! volatile ) {
			return;
		}
		edit();
	};

	const renderEdit = () => {
		const { edit, selected, volatile } = props;

		if ( ! selected || ! volatile ) {
			return null;
		}

		return (
			<button onClick={ edit }>
				<Dashicon icon="edit" />
			</button>
		);
	};

	const renderDetails = () => {
		const { organizer } = props;
		const fields = toFields( organizer );
		const { title, website, email, phone } = fields;
		return (
			<Fragment>
				<div className="tribe-editor__organizer__title">
					<h3 className="tribe-editor__organizer__title-heading" onClick={ maybeEdit }>
						{ decode( title ) }
					</h3>
					{ renderEdit() }
				</div>
				{ phone && <p>{ phone }</p> }
				{ website && <p>{ website }</p> }
				{ email && <p>{ email }</p> }
			</Fragment>
		);
	};

	const renderActions = () => {
		const { remove, selected } = props;

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
	};

	return (
		<div className="tribe-editor__organizer__details">
			{ renderDetails() }
			{ renderActions() }
		</div>
	);
};

OrganizerDetails.propTypes = {
	organizer: PropTypes.object,
	edit: PropTypes.func,
	remove: PropTypes.func,
	selected: PropTypes.bool,
};

OrganizerDetails.defaultProps = {
	organizer: {},
	edit: noop,
	remove: noop,
	selected: false,
};

export default OrganizerDetails;
