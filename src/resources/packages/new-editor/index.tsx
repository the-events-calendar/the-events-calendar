import {dispatch, select, subscribe} from "@wordpress/data";
import {localizedData} from './localized-data';
import {createBlock, registerBlockCollection, registerBlockType} from '@wordpress/blocks';
import {useBlockProps} from '@wordpress/block-editor';
import {__} from "@wordpress/i18n";
import { Button } from '@wordpress/components';
import {registerPlugin} from '@wordpress/plugins';
import './style.pcss';

console.log('Hello from the new editor!');

const {eventCategoryTaxonomyName, __experimentalApproach} = localizedData;

export function whenEditorIsReady() {
	return new Promise((resolve) => {
		const unsubscribe = subscribe(() => {
			// This will trigger after the initial render blocking, before the window load event
			// This seems currently more reliable than using __unstableIsEditorReady
			if (select('core/editor').isCleanNewPost() || select('core/block-editor').getBlockCount() > 0) {
				unsubscribe()
				resolve()
			}
		})
	})
}

// Remove the category sidebar element.
dispatch('core/editor').removeEditorPanel(`taxonomy-panel-${eventCategoryTaxonomyName}`);

// Remove the post tag sidebar element.
dispatch('core/editor').removeEditorPanel('taxonomy-panel-post_tag');


if (__experimentalApproach === 'block-editor') {
	whenEditorIsReady().then(() => {
		// Register the TEC block collection.
		registerBlockCollection('tec', {
			title: 'The Events Calendar',
		});

		// Register an example block.
		registerBlockType('tec/example-block', {
			apiVersion: 2,
			attributes: {
				lock: {
					move: false,
					remove: false
				}
			},
			title: 'Example TEC block',
			// edit: () => <p {...useBlockProps()}>Hello World - TEC Editor</p>,
			edit: () =>
				<div {...useBlockProps()}>
                    <div >
                        <h3 style={{ textAlign: "center" }}>TEC New Editor</h3>
                        <table className="form-table" style={{ display: "flex", justifyContent: "center", flexWrap: "wrap"}}>
                            <tr>
                                <th><label htmlFor="event_title">Event Title:</label></th>
                                <td>
                                    <input type="text" id="event_title" name="event_title" value="" className="regular-text"/>
                                </td>
                            </tr>
                            <tr>
                                <th><label htmlFor="event_description">Event Description:</label></th>
                                <td><textarea id="event_description" name="event_description" rows={4} cols={50}></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th><label htmlFor="event_date">Event Date:</label></th>
								<td><input type="date" id="event_date" name="event_date" value=""/></td>
							</tr>
							<tr>
                                <th><label htmlFor="event_time">Event Time:</label></th>
								<td><input type="time" id="event_time" name="event_time" value=""/></td>
							</tr>
							<tr>
                                <th><label htmlFor="event_location">Event Location:</label></th>
                                <td><input type="text" id="event_location" name="event_location" value="" className="regular-text"/></td>
							</tr>
						</table>
					</div>
				</div>,
			save: () => <p {...useBlockProps.save()}>Hello World - Frontend</p>,
		});

		// Add the block to the page programmatically.
		const block = createBlock('tec/example-block');

		dispatch('core/block-editor').insertBlocks(block);
	});
} else {
	whenEditorIsReady().then(()=>{
		// Remove the resize control from the new editor metabox.
		document.getElementById('tec-new-editor')
			.querySelectorAll('.handle-actions')
			.forEach((el: Element): void => el.remove());

		// Remove the Zoom Out button. The only way is by its aria label.
		const zoomOutAriaLabel = __('Zoom Out');
		document.querySelectorAll(`.components-button[aria-label="${zoomOutAriaLabel}"]`)
			.forEach((el: Element)=>el.remove());

		// Add editor tools.
		let editorToolsAdded = false;
		function togglePreview():void{
			document.querySelectorAll('.editor-visual-editor')
				.forEach((editor:Element)=>editor.classList.toggle('tec-is-visible'));
		}

		function EditorTools(): void {
			if (editorToolsAdded) {
				return;
			}

			const editorDocumentTools = document.querySelector('.editor-document-tools .editor-document-tools__left')

			if (editorDocumentTools) {
				console.log('adding button');
				const previewButton = document.createElement('button')
				previewButton.classList.add('tec-editor-tool', 'tec-editor-tool--preview', 'is-secondary');
				previewButton.type = 'button';
				previewButton.dataset.toolbarItem = 'true';
				previewButton.innerHTML = `<span class="dashicons dashicons-visibility"></span> ${__('Visual', 'the-events-calendar')}`;
				editorDocumentTools.append(previewButton);
				editorToolsAdded = true;
				previewButton.onclick = togglePreview;
			}

			return null;
		}

		registerPlugin('tec-editor-tools', {
			render: EditorTools
		});

		// The metabox area height is hard-coded, refresh it.
		// This will implicitly hide the block editor.
		document
			.querySelectorAll('.components-resizable-box__container.edit-post-meta-boxes-main')
			.forEach((el: Element): void => el.style.height = 'auto');

		// Prevent metabox area from being resizable.
		document
			.querySelectorAll('.edit-post-meta-boxes-main.is-resizable')
			.forEach((el: Element)=>el.classList.remove('is-resizable'));

		// @todo prevent lower metaboxes from getting in higher positions when using their sortable controls.
	});
}
