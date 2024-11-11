import TYPES from "./action-types";

const { OPEN_MODAL, CLOSE_MODAL } = TYPES;

const initialState = {
  isModalOpen: false,
};

const reducer = (state = initialState, action) => {
  switch (action.type) {
    case OPEN_MODAL:
      return { ...state, isModalOpen: true };
    case CLOSE_MODAL:
      return { ...state, isModalOpen: false };
    default:
      return state;
  }
};

export default reducer;
