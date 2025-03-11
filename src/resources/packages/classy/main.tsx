import {hideZoomOutButton} from "./functions/hideZoomOutButton";
import {hideInserterToggle} from "./functions/hideInserterToggle";
import {createRoot} from '@wordpress/element';

export function init() {
	hideZoomOutButton();
	hideInserterToggle();

	addFilter(
		'classy.render',
		'additional-plugin',
		() => (
			<Fill name="classy.fields">
				<div className="classy-input">
					<div className="classy-input__label">
						Fill
					</div>
					<div className="classy-input__input">
						<input type="text" placeholder="If you can read this, fill works!"/>
					</div>
				</div>
			</Fill>
		)
	);

	const classyRoot = createRoot(classyElement);

	classyRoot.render(
		<SlotFillProvider>

			{applyFilters('classy.render')}

			<div className="classy-container">
				<h3 className="classy-title">New Event</h3>

				<div className="classy-input">
					<div className="classy-input__label">
						Title
					</div>
					<div className="classy-input__input">
						<input type="text" placeholder="Your title"/>
					</div>
				</div>

				<div className="classy-input">
					<div className="classy-input__label">
						Description
					</div>
					<div className="classy-input__input">
						<RichText
							tagName="p"
							value=""
							allowedFormats={['core/bold', 'core/italic']}
							placeholder="Your event description"
						/>
					</div>
				</div>

				<Slot name="classy.fields"/>

			</div>

		</SlotFillProvider>
	);

	injectClassyElement(classyElement);
	const toggleClassyVisibility = (): void => {
		console.log('classyElement', classyElement);
		classyElement.classList.toggle('classy-root--hidden');
	};
	addEditorTools(toggleClassyVisibility);
}
