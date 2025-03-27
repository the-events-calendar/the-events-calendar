/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import QrCode from './template';
import { QrCode as QrCodeIcon } from '@moderntribe/events/icons';

const { __ } = wp.i18n;
const { InnerBlocks } = wp.blockEditor;

/**
 * Module Code
 */
export default {
	id: 'qr-code',
	title: __('QR Code', 'the-events-calendar'),
	description: __('Display a QR code for an event.', 'the-events-calendar'),
	icon: <QrCodeIcon />,
	category: 'tribe-events',
	keywords: ['event', 'qr code', 'events-gutenberg', 'tribe'],
	example: {},

	edit: QrCode,
	save: () => <InnerBlocks.Content />,
};
