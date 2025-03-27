/**
 * External dependencies
 */
import React from 'react';

const { InnerBlocks } = wp.blockEditor;

const QR_CODE_TEMPLATE = [
	[
		'core/legacy-widget',
		{
			idBase: 'tribe-widget-events-qr-code',
			instance: {},
		},
	],
];

const QrCode = () => (
	<InnerBlocks
		template={QR_CODE_TEMPLATE}
		templateLock="all"
	/>
);

export default QrCode;
