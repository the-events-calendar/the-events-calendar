import * as React from 'react';
import { useState } from 'react';
import { Button, ButtonGroup } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { _x } from '@wordpress/i18n';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { IconTicket } from '@tec/common/classy/components';
import { EventCost } from '../EventCost';
import clsx from 'clsx';

export default function EventAdmission( props: FieldProps ) {
	const { meta } = useSelect( ( select ) => {
		const selector = select( 'core/editor' );
		return {
			// @ts-ignore
			meta: selector.getEditedPostAttribute( 'meta' ) || {},
		};
	}, [] );

	// Initially select and subscribe to the store that will control whether tickets are supported or not.
	const areTicketsSupported: boolean = useSelect( ( select: Function ) => {
		return select( 'tec/classy/events' ).areTicketsSupported();
	}, [] );

	// The initial value depends on whether tickets are supported or not.
	const [ isUsingTickets, setIsUsingTickets ] = useState< boolean >( areTicketsSupported );

	return (
		<div className="classy-field classy-field--event-admission">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs">
				{ /* If not using tickets, do not show the buttons. */ }
				{ isUsingTickets && (
					<div className="class-field__inputs-section classy-field__inputs-section--row">
						<ButtonGroup className="components-button-group--classy">
							<Button
								className="classy-button"
								__next40pxDefaultSize={ true }
								variant="primary"
								onClick={ (): void => setIsUsingTickets( true ) }
							>
								<IconTicket className="classy-icon--prefix" />
								{ _x( 'Sell Tickets', 'Event admission button label', 'the-events-calendar' ) }
							</Button>

							<Button
								className="classy-button"
								__next40pxDefaultSize={ true }
								variant="secondary"
								onClick={ (): void => setIsUsingTickets( false ) }
							>
								{ _x( 'Manual Pricing', 'Event admission button label', 'the-events-calendar' ) }
							</Button>
						</ButtonGroup>
					</div>
				) }

				<EventCost />
			</div>
		</div>
	);
}
