import { METADATA_EVENT_URL } from '../constants';

export type PostUpdates = {
	title?: string;
	content?: string;
	[ METADATA_EVENT_URL ]?: string;
};
