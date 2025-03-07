import {dispatch, select, subscribe} from "@wordpress/data";
import {localizedData} from './localized-data';
import {RichText} from '@wordpress/block-editor';
import {__} from "@wordpress/i18n";
import {registerPlugin} from '@wordpress/plugins';
import {createRoot} from '@wordpress/element';
import { SlotFillProvider, Slot, Fill } from '@wordpress/components';
import { addFilter, applyFilters } from '@wordpress/hooks';
import './style.pcss';

console.log('Classy!');

const {eventCategoryTaxonomyName} = localizedData;

function whenEditorIsReady(): Promise<void> {
	return new Promise((resolve: Function) => {
		const unsubscribe: Function = subscribe(() => {
			// This will trigger after the initial render blocking, before the window load event
			// This seems currently more reliable than using __unstableIsEditorReady
			if (select('core/editor').isCleanNewPost() || select('core/block-editor').getBlockCount() > 0) {
				unsubscribe()
				resolve()
			}
		})
	})
}

function createClassyElement(): Element {
	const classy = document.createElement('div');
	classy.id = 'tec-classy';
	classy.classList.add('classy-root', 'classy-root--admin');

	return classy;
}

function injectClassyElement(classyElement: Element): void {
	const visualEditor = document.querySelector('.editor-visual-editor');

	if (!visualEditor) {
		return;
	}

	visualEditor.parentNode.insertBefore(classyElement, visualEditor.nextSibling);
}

// Remove the category sidebar element.
dispatch('core/editor').removeEditorPanel(`taxonomy-panel-${eventCategoryTaxonomyName}`);

// Remove the post tag sidebar element.
dispatch('core/editor').removeEditorPanel('taxonomy-panel-post_tag');


function hideZoomOutButton(): void {
	// Remove the Zoom Out button. The only way is by its aria label.
	const zoomOutAriaLabel = __('Zoom Out');
	document.querySelectorAll(`.components-button[aria-label="${zoomOutAriaLabel}"]`)
		.forEach((el: Element) => el.style.display = 'none');
}

function hideInserterToggle() {
	// Remove the inserter toggle button.
	document.querySelectorAll('.editor-document-tools__inserter-toggle')
		.forEach((button: Element) => button.style.display = 'none');
}

function addEditorTools(onClick: (this: GlobalEventHandlers, ev: MouseEvent) => void): void {
	let editorToolsAdded = false;

	function EditorTools(): void {
		if (editorToolsAdded) {
			return;
		}

		const editorDocumentTools = document.querySelector('.editor-document-tools .editor-document-tools__left')

		if (editorDocumentTools) {
			const previewButton = document.createElement('button')
			previewButton.classList.add('tec-editor-tool', 'tec-editor-tool--preview', 'button');
			previewButton.type = 'button';
			previewButton.dataset.toolbarItem = 'true';
			previewButton.innerHTML = `<span class="dashicons dashicons-visibility"></span> ${__('Visual', 'the-events-calendar')}`;
			editorDocumentTools.append(previewButton);
			editorToolsAdded = true;
			previewButton.onclick = onClick;
		}

		return null;
	}

	registerPlugin('tec-editor-tools', {
		render: EditorTools
	});
}

const classyElement = createClassyElement();

whenEditorIsReady().then(() => {
	hideZoomOutButton();
	hideInserterToggle();

	addFilter(
		'classy.render',
		'additional-plugin',
		()=>(
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

				<Slot name="classy.fields" />

			</div>

		</SlotFillProvider>
	);

	injectClassyElement(classyElement);
	const toggleClassyVisibility = (): void => {
		console.log('classyElement', classyElement);
		classyElement.classList.toggle('classy-root--hidden');
	};
	addEditorTools(toggleClassyVisibility);
});
