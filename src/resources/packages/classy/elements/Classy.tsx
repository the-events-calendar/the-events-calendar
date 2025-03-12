import { RichText } from '@wordpress/block-editor';
import { Slot, SlotFillProvider } from '@wordpress/components';
import { doAction } from '@wordpress/hooks';
import { EventTitle } from './fields';
import { _x } from '@wordpress/i18n';

export function Classy() {
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
						'The title of the Event Title field.',
						'the-events-calendar'
					) }
				/>

				<Slot name="classy.fields" />
			</div>
		</SlotFillProvider>
	);
}
