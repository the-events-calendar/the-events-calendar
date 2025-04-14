import { EventMeta } from './EventMeta';

export type StoreState = {
	title?: string;
	content?: string;
	currentPostId?: number;
	meta?: EventMeta;
};
