import { GET_CASES, GET_STATUS, GET_USERS, IN_VIEW } from "./types";

export default function AppReducer(state, action) {
  switch (action.type) {
    case GET_USERS:
      return {
        users: action.payload.users,
        loadingUsers: action.payload.loadingUsers,
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
        userId: action.payload.userId, //user id
        caseId: action.payload.caseId,
        name: action.payload.name,
      };
    case GET_STATUS:
      return {
        status: action.payload.status,
        caseTitle: action.payload.caseTitle,
        loadingStatus: action.payload.loadingStatus,
      };

    default:
      return state;
  }
}
