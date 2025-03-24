import {
	ACTION_CLASSY_EDIT_POST,
	ACTION_CLASSY_SOME_OTHER_ACTION,
} from '../store/constants';

type ActionType =
	| typeof ACTION_CLASSY_EDIT_POST
	| typeof ACTION_CLASSY_SOME_OTHER_ACTION;

export type Action = {
	type: ActionType;
};

export type EditPostAction = Action & {
	updates: Object;
};
