import { Settings } from '@tec/common/classy/types/LocalizedData';

export type TECSettings = {
	venuesLimit: number;
	disableContent?: boolean;
	disableContentReason?: string;
} & Settings;
