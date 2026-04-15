import { useReducer, ReactNode } from "react";
import AppReducer from "../AppReducer";
import ProgressContext from "./progressContext";
import { GET_STATUS } from "../types";
import { toast } from "react-toastify";
import { get, post, put, del } from "../../utils/fetch";
import dateformat from "dateformat";
import type { ProgressState as ProgressStateType, Status } from "../types";

interface ProgressStateProps {
  children: ReactNode;
}

export default function ProgressState({ children }: ProgressStateProps) {

  const initialState: ProgressStateType = {
    status: [],
    caseTitle: "",
    loadingStatus: false,
  };

  const [state, dispatch] = useReducer(AppReducer, initialState);
  const apiUrlProgress = `${data.root_url}/wp-json/${data.api_url}/progress`;

  //onlyFetch means this function will retrieve plain data from api, without state update
  const getStatus = async (id: string | number, onlyFetch: boolean, caseTitle?: string): Promise<Status[] | void> => {
    try {
      if (!onlyFetch) {
        dispatch({
          type: GET_STATUS,
          payload: {
            status: state.status,
            caseTitle: state.caseTitle,
            loadingStatus: true,
          },
        });
      }

      const res = await get(`${apiUrlProgress}/${id}`, {
        headers: {
          "X-WP-Nonce": data.nonce,
        },
      });

      if (!onlyFetch) {
        dispatch({
          type: GET_STATUS,
          payload: {
            status: res.data,
            caseTitle: caseTitle,
            loadingStatus: false,
          },
        });
      }

      return res.data;
    } catch (error) {
      console.log(error);
    }
  };

  const postStatus = async (id_user: string | number, id_case: string | number, text: string): Promise<void> => {
    const dataToPost = {
      id_user,
      id_case,
      text,
    };

    try {
      await post(
        `${apiUrlProgress}/${id_case}`,
        dataToPost,
        {
          headers: {
            "X-WP-Nonce": data.nonce,
            "Content-type": "application/json",
          },
        }
      );

      const getAllStatus = await getStatus(id_case, true, state.caseTitle);
      const statusArray = Array.isArray(getAllStatus) ? getAllStatus : [];

      const { id, created_at } = statusArray[statusArray.length - 1];

      const newStatus = {
        id: id,
        id_case: id_case,
        id_user: id_user,
        created_at: created_at,
        text: text,
      };
      let currentStatus = state.status;
      let newStatusArray = [...currentStatus, newStatus];

      dispatch({
        type: GET_STATUS,
        payload: {
          status: newStatusArray,
          caseTitle: state.caseTitle,
          loadingStatus: false,
        },
      });

      toast.success(data.toast_status_added);
    } catch (error) {
      alert(data.alert_error_base + error);
    }
  };

  const deleteStatus = async (id: string | number, createdAt: string): Promise<void> => {
    const status = [...state.status];

    const sureToDelete = confirm(
      `Are you sure you want to delete status from ${dateformat(createdAt, "dd/mm/yyyy, HH:MM")}?`
    );

    if (!sureToDelete) return;

    try {
      await del(`${apiUrlProgress}/${id}`, {
        headers: {
          "X-WP-Nonce": data.nonce,
        },
      });

      const filteredStatuses = status.filter((status: Status) => {
        return dateformat(status.created_at, "dd/mm/yyyy, HH:MM") !== dateformat(createdAt, "dd/mm/yyyy, HH:MM");
      });

      dispatch({
        type: GET_STATUS,
        payload: {
          status: filteredStatuses,
          caseTitle: state.caseTitle,
          loadingStatus: false,
        },
      });

      toast.success(data.toast_status_deleted);
    } catch (error) {
      alert(data.alert_error_base + error);
    }
  };

  const editStatus = async (id: string | number, _id_user: string | number, newText: string): Promise<void> => {
    if (newText === "") {
      alert(data.alert_blank_status_title);
      return;
    }

    const editedObj = { text: newText };

    try {
      await put(`${apiUrlProgress}/${id}`, editedObj, {
        headers: {
          "X-WP-Nonce": data.nonce,
          "Content-type": "application/json",
        },
      });

      const newStatuses = state.status.map((status: Status) => {
        if (status.id === id) {
          return { ...status, text: newText };
        }
        return status;
      });

      dispatch({
        type: GET_STATUS,
        payload: {
          status: newStatuses,
          caseTitle: state.caseTitle,
          loadingStatus: false,
        },
      });

      toast.success(data.toast_status_edited);
    } catch (error) {
      alert(data.alert_error_base + error);
    }
  };

  return (
    <ProgressContext.Provider
      value={{
        state,
        getStatus,
        postStatus,
        deleteStatus,
        editStatus,
      }}
    >
      {children}
    </ProgressContext.Provider>
  );
}
