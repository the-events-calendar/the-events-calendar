import {select, subscribe} from "@wordpress/data";

/**
 * Subscribes to the editor state and resolves when the Block Editor is considered  ready.
 *
 * @since TBD
 *
 * @return {Promise<void>} A promise that resolves when the editor is ready.
 */
export function whenEditorIsReady(): Promise<void> {
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
