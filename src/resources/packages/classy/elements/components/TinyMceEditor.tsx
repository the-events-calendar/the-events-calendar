import { useEffect, useState } from 'react';
import { debounce } from 'lodash';
import { TinyMceEditorProps } from '../../types/EditorComponentProps';

/**
 * Editor component that initializes and manages a TinyMCE editor. * * @since TBD * * @param {TinyMceEditorProps} props - The component props. * @param {string} props.content - The current content of the editor. * @param {(value: string) => void} props.onChange - Callback function to update the content.
 * @param {string} props.id - The ID attribute for the editor component.
 *
 * @return {JSX.Element} The rendered editor.
 */
export function TinyMceEditor( { content, onChange, id }: TinyMceEditorProps ) {
	const [ value, setValue ] = useState( content );
	const [ , setEditor ] = useState< any >( null );

	useEffect( () => {
		// @ts-ignore - Defined by the `wp-tinymce` dependency required by the Classy package.
		if ( window.tinymce.get( id ) ) {
			// @ts-ignore - Defined by the `wp-tinymce` dependency required by the Classy package.
			window.wp.oldEditor.remove( id );
		}

		// @ts-ignore - Defined by the `wp-tinymce` dependency required by the Classy package.
		window.wp.oldEditor.initialize( id, {
			tinymce: {
				wpautop: true,
				toolbar1:
					'bold italic formatselect | blockquote bullist numlist link',
				toolbar2: '',
			},
			quicktags: false, // Do not show the "Visual / Text" tabs.
			mediaButtons: false, // Do not show the "Add media" button.
		} );

		// @ts-ignore - Defined by the `wp-tinymce` dependency required by the Classy package.
		const editor = window.tinymce.get( id );

		// Handle initialization of the editor.
		const onInit = () => {
			editor.on( 'NodeChange', debounce( triggerChangeIfDirty, 250 ) );
			setEditor( editor );
		};

		if ( editor.initialized ) {
			onInit();
		} else {
			editor.on( 'init', onInit );
		}

		// Cleanup function to remove event listeners
		return () => {
			if ( editor ) {
				editor.off(
					'NodeChange',
					debounce( triggerChangeIfDirty, 250 )
				);
			}
		};
	}, [ id ] );

	const triggerChangeIfDirty = () => {
		// @ts-ignore - Defined by the `wp-tinymce` dependency required by the Classy package.
		updateValues( window.wp.oldEditor.getContent( id ) );
	};

	const updateValues = ( newValue: string ) => {
		setValue( newValue );
		onChange( newValue );
	};

	return (
		<textarea
			className="classy-control-tinymce-editor wp-editor-area"
			id={ id }
			value={ value }
			onChange={ ( e ) => updateValues( e.target.value ) }
		/>
	);
}
