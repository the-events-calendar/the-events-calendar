import React from 'react';
import { Slot, SlotFillProvider } from '@wordpress/components';
import { doAction } from '@wordpress/hooks';
import { _x } from '@wordpress/i18n';
import { EventDetails, EventTitle } from './fields';
import { RegistryProvider } from '@wordpress/data';
import { WPDataRegistry } from '@wordpress/data/build-types/registry';

function ClassyApplication() {
	return (
		<SlotFillProvider>
			{
				/**
				 * Filters the rendered JSX of the Classy component.
				 *
				 * This component is wrapped within a `SlotFillProvider` to allow dynamic content insertion
				 * via the `Slot/Fill` API. Use the `addFilter` hook to add Fills into the Classy application slots.
				 *
				 * @since TBD
				 */
				doAction( 'classy.render' )
			}

			<div className="classy-container">
				<EventTitle
					title={ _x(
						'Event Title',
						'The title of the event title field.',
						'the-events-calendar'
					) }
				/>

				<EventDetails
					title={ _x(
						'Event Details',
						'The title of the event details field.',
						'the-events-calendar'
					) }
				/>

				{ /* @ts-ignore */ }
				<Slot name="classy.fields" />
			</div>
		</SlotFillProvider>
	);
}

export function Classy( { registry }: { registry: WPDataRegistry } ) {
	{
		/* @ts-ignore */
	}
	return (
		<RegistryProvider value={ registry }>
			<ClassyApplication />
		</RegistryProvider>
	);
}
