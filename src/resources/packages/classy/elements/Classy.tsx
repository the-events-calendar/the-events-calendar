import { RichText } from '@wordpress/block-editor';
import { Slot, SlotFillProvider } from '@wordpress/components';
import { doAction } from '@wordpress/hooks';

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
				<h3 className="classy-title">New Event</h3>

				<div className="classy-input">
					<div className="classy-input__label">Title</div>
					<div className="classy-input__input">
						<input type="text" placeholder="Your title" />
					</div>
				</div>

				<div className="classy-input">
					<div className="classy-input__label">Description</div>
					<div className="classy-input__input">
						<RichText
							tagName="p"
							value=""
							allowedFormats={ [ 'core/bold', 'core/italic' ] }
							placeholder="Your event description"
							onChange={ () => null }
						/>
					</div>
				</div>

				<Slot name="classy.fields" />
			</div>
		</SlotFillProvider>
	);
}
