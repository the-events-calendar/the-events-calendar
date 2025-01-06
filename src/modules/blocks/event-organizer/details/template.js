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
import { ReactComponent as CloseIcon } from '@moderntribe/common/icons/close.svg';

import './style.pcss';

const OrganizerDetails = ( {
	organizer = {},
	edit= noop,
	remove = noop,
	selected = false,
	volatile
} ) => {
	const maybeEdit = () => {
		if ( ! volatile ) {
			return;
		}
		edit();
	};

	const renderEdit = () => {
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
		const fields = toFields( organizer );
		const { title, website, email, phone } = fields;

		return (
			<Fragment>
				<div className="tribe-editor__organizer__title">
					<h3 // eslint-disable-line
						className="tribe-editor__organizer__title-heading"
						onClick={ maybeEdit }
					>
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
	volatile: PropTypes.bool,
};

export default OrganizerDetails;
