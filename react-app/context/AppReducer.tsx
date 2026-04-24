import { AppAction } from "./types";
import { GET_USERS, GET_CASES, IN_VIEW, GET_STATUS } from "./types";

export default function AppReducer(state: any, action: AppAction) {
  switch (action.type) {
    case GET_USERS:
      return {
        ...state,
        users: action.payload.users,
        loadingUsers: action.payload.loadingUsers,
      };
    case GET_CASES:
      return {
        ...state,
        user: action.payload.user,
        cases: action.payload.cases,
        loadingCases: action.payload.loadingCases,
      };
    case IN_VIEW:
      return {
        ...state,
        view: action.payload.view,
        userId: action.payload.userId,
        caseId: action.payload.caseId,
        name: action.payload.name,
      };
    case GET_STATUS:
      return {
        ...state,
        status: action.payload.status,
        caseTitle: action.payload.caseTitle,
        loadingStatus: action.payload.loadingStatus,
      };
    default:
      return state;
  }
}
