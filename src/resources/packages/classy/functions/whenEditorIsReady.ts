import { select, subscribe } from '@wordpress/data';

/**
 * Subscribes to the editor state and resolves when the Block Editor is considered  ready.
 *
 * @since TBD
 *
 * @return {Promise<void>} A promise that resolves when the editor is ready.
 */
export function whenEditorIsReady(): Promise< void > {
	return new Promise( ( resolve: Function ) => {
		const unsubscribe: Function = subscribe( () => {
			if ( select( 'core/editor' ).__unstableIsEditorReady() ) {
				unsubscribe();
				resolve();
			}
		} );
	} );
}
