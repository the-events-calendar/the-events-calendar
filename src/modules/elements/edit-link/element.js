/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';
import './style.pcss';

const EditLink = ( { postId, label, target } ) => {
	const admin = get( globals.common(), 'adminUrl', '' );
	if ( ! admin || ! postId ) {
		return null;
	}

	const extraProps = {
		rel: '_blank' === target ? 'noreferrer noopener' : undefined,
	};

	return (
		<a
			className="tribe-editor__edit-link"
			href={ `${ admin }post.php?post=${ postId }&action=edit` }
			target={ target }
			{ ...extraProps }
		>
			{ label }
		</a>
	);
};

EditLink.propTypes = {
	postId: PropTypes.number,
	label: PropTypes.string,
	target: PropTypes.string,
};

EditLink.defaultProps = {
	postId: 0,
	label: __( 'Edit', 'the-events-calendar' ),
	target: '_blank',
};

export default EditLink;
