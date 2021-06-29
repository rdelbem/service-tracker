import { GET_CASES, GET_USERS, IN_VIEW } from "./types";

export default function AppReducer(state, action) {
  switch (action.type) {
    case GET_USERS:
      return {
        users: action.payload,
        loadingUsers: false,
      };
    case GET_CASES:
      return {
        user: action.payload.user,
        cases: action.payload.cases,
        loadingCases: action.payload.loadingCases,
      };
    case IN_VIEW:
      return {
        view: action.payload.view,
        id: action.payload.id,
      };

    default:
      return state;
  }
}
