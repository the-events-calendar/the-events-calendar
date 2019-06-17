/**
 * External dependencies
 */
import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { unescape, trim, isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Dashicon, IconButton } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Loading } from '@moderntribe/events/elements';
import './style.pcss';

/**
 * Module Code
 */

const EventDetailsOrganizer = ( props ) => {
	const getOrganizerName = ( { title } ) => {
		const { rendered = __( '(Untitled)', 'the-events-calendar' ) } = title;
		return trim( unescape( rendered ) );
	};

	const getOrganizerRemoveButton = ( {
		organizerId,
		block,
		volatile,
		onRemoveClick,
	} ) => (
		! ( block || volatile )
		&& (
			<IconButton
				className="tribe-editor__btn tribe-editor__btn--action"
				label={ __( 'Remove Organizer', 'the-events-calendar' ) }
				onClick={ onRemoveClick( organizerId ) }
				icon={ <Dashicon icon="no" /> }
			/>
		)
	);

	const { isLoading, details } = props;

	return (
		<li>
			{
				( isLoading || isEmpty( details ) )
				? <Loading className="tribe-editor__spinner--item" />
				: (
					<Fragment>
						{ getOrganizerName( props.details ) }
						{ getOrganizerRemoveButton( props ) }
					</Fragment>
				)
			}
		</li>
	);
};

EventDetailsOrganizer.propTypes = {
	details: PropTypes.object,
	isLoading: PropTypes.bool,
	organizerId: PropTypes.number,
	block: PropTypes.bool,
	volatie: PropTypes.bool,
	onRemoveClick: PropTypes.func,
};

export default EventDetailsOrganizer;
