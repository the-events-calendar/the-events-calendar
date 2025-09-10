import * as React from 'react';
import { ReactNode } from 'react';
import { Button, ButtonGroup, Slot } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { _x } from '@wordpress/i18n';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { EventCost } from '../EventCost';
import { StoreDispatch, StoreSelect } from '../../types/Store';
import { STORE_NAME } from '../../constants';

export default function EventAdmission( props: FieldProps ) {
	// Initially select and subscribe to the store that will control whether tickets are supported or not.
	const { areTicketsSupported, isUsingTickets } = useSelect( ( select ) => {
		const tecStore: StoreSelect = select( STORE_NAME );
		return {
			areTicketsSupported: tecStore.areTicketsSupported(),
			isUsingTickets: tecStore.isUsingTickets(),
		};
	}, [] );

	const { setIsUsingTickets }: StoreDispatch = useDispatch( STORE_NAME );

	const ticketModeSlot = () => {
		return (
			<Slot name="tec.classy.fields.event-admission.buttons" bubblesVirtually={ false }>
				{ ( fills: ReactNode ) => {
					// If we have fills, render them along with a Manual Pricing button.
					// If not, we don't render anything.
					if ( ! fills ) {
						return null;
					}

					return (
						<div className="class-field__inputs-section classy-field__inputs-section--row">
							<ButtonGroup className="components-button-group--classy">
								{ fills }
								<Button
									className="classy-button"
									__next40pxDefaultSize
									variant="secondary"
									onClick={ (): void => setIsUsingTickets( false ) }
								>
									{ _x( 'Manual Pricing', 'Event admission button label', 'the-events-calendar' ) }
								</Button>
							</ButtonGroup>
						</div>
					);
				} }
			</Slot>
		);
	};

	return (
		<div className="classy-field classy-field--event-admission">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs">
				{ /* If tickets are supported and we are using tickets, render the ticket mode slot. */ }
				{ areTicketsSupported && ! isUsingTickets && ticketModeSlot() }
				<div className="class-field__inputs-section classy-field__inputs-section--row"></div>

				{ /* Provide a slot for tickets to render their fields. */ }
				<Slot name="tec.classy.fields.tickets" />

				{ /* When not using tickets, render the EventCost component. */ }
				{ ! isUsingTickets && <EventCost /> }
			</div>
		</div>
	);
}
