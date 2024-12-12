import TYPES from "./action-types";

const {
	OPEN_MODAL,
	CLOSE_MODAL,
} = TYPES;

export const openModal = () => ({ type: OPEN_MODAL });
export const closeModal = () => ({ type: CLOSE_MODAL });
