/**
 * External dependencies
 */
import React from 'react';

const { useBlockProps } = wp.blockEditor;

/**
 * The Block API version the blocks of this plugin register with.
 *
 * @since TBD
 *
 * @type {number}
 */
export const BLOCK_API_VERSION = 3;

/**
 * Adapts a block definition to the Block API version this plugin registers with.
 *
 * From Block API version 2 onwards the editor no longer renders the wrapper element around a
 * block's `edit` output, so the block has to render it itself through `useBlockProps()`. Without
 * it the block loses the attributes the editor relies on to select, drag and label it.
 *
 * @since TBD
 *
 * @param {Object}   block      The block definition to adapt.
 * @param {Function} block.edit The component the editor renders for the block.
 *
 * @return {Object} The block definition, registering at the current Block API version.
 */
const withBlockWrapper = ( block ) => {
	const Edit = block.edit;

	const BlockEdit = ( props ) => {
		const blockProps = useBlockProps();

		return (
			<div { ...blockProps }>
				<Edit { ...props } />
			</div>
		);
	};

	return {
		...block,
		apiVersion: BLOCK_API_VERSION,
		edit: BlockEdit,
	};
};

export default withBlockWrapper;
