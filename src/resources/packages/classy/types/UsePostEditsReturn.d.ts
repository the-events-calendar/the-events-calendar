import { EventMetadata } from './EventMetadata';

export type UsePostEditsReturn = {
	postTitle: string;
	postContent: string;
	meta: EventMetadata;
	editPost: ( updates: Object ) => string;
};
