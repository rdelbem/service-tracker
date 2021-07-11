import React, { useContext, useReducer } from "react";
import InViewContext from "../../context/inView/inViewContext";
import AppReducer from "../AppReducer";
import ProgressContext from "./progressContext";
import { GET_STATUS } from "../types";
import { toast } from "react-toastify";
import axios from "axios";
import dateformat from "dateformat";

export default function ProgressState(props) {
  const inViewContext = useContext(InViewContext);
  const currentUserInDisplay = inViewContext.state.userId;
  const currentCaseInDisplay = inViewContext.state.caseId;

  const initialState = {
    status: [],
    caseTitle: "",
    loadingStatus: false,
  };

  const [state, dispatch] = useReducer(AppReducer, initialState);
  const apiUrlProgress = `${data.root_url}/wp-json/${data.api_url}/progress`;

  //onlyFetch means this function will retrieve plain data from api, without state update
  const getStatus = async (id, onlyFetch, caseTitle) => {
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

      const res = await axios.get(`${apiUrlProgress}/${id}`, {
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
      alert(
        "Hum, it was impossible to complete this task. We had an error: " +
          error
      );
    }
  };

  const postStatus = async (id_user, id_case, text) => {
    if (text === "") {
      alert("Status text can not be blank!");
      return;
    }

    const dataToPost = { id_user: id_user, id_case: id_case, text: text };

    try {
      const postStatus = await axios.post(
        `${apiUrlProgress}/${id_case}`,
        dataToPost,
        {
          headers: {
            "X-WP-Nonce": data.nonce,
            "Content-type": "application/json",
          },
        }
      );

      const getAllStatus = await getStatus(id_case, true);

      const { id, created_at } = getAllStatus[getAllStatus.length - 1];

      const newStatus = {
        id: id,
        id_case: id_case,
        id_user: id_user,
        created_at: created_at,
        text: text,
      };

      const newStatuses = [...state.status, newStatus];

      dispatch({
        type: GET_STATUS,
        payload: {
          status: newStatuses,
          caseTitle: state.caseTitle,
          loadingCases: state.loadingCases,
        },
      });

      toast.success("Status added!", {
        position: "bottom-right",
        autoClose: 5000,
        hideProgressBar: true,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });
    } catch (error) {
      alert(
        "Hum, it was impossible to complete this task. We had an error: " +
          error
      );
    }
  };

  const deleteStatus = async (id, createdAt) => {
    const confirm = window.confirm(
      "Do you want to delete the status created in " +
        dateformat(createdAt, "dd/mm/yyyy, HH:MM") +
        "?"
    );
    if (!confirm) return;

    try {
      const status = [...state.status];
      const filteredStatuses = status.filter((status) => status.id !== id);

      dispatch({
        type: GET_STATUS,
        payload: {
          status: filteredStatuses,
          caseTitle: state.caseTitle,
          loadingStatus: state.loadingStatus,
        },
      });

      toast.warn("Status deleted!", {
        position: "bottom-right",
        autoClose: 5000,
        hideProgressBar: true,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });

      const deleteStatus = await axios.delete(`${apiUrlProgress}/${id}`, {
        headers: {
          "X-WP-Nonce": data.nonce,
        },
      });
    } catch (error) {
      alert(
        "Hum, it was impossible to complete this task. We had an error: " +
          error
      );
    }
  };

  const editStatus = async (id, id_user, newText) => {
    if (newText === "") {
      alert("Status can not be blank");
      return;
    }

    const editedObj = JSON.stringify({ id: id, text: newText });

    try {
      const statuses = [...state.status];
      statuses.forEach((item) => {
        if (item.id === id) {
          item.text = newText;
        }
      });

      dispatch({
        type: GET_STATUS,
        payload: {
          status: statuses,
          caseTitle: state.caseTitle,
          loadingStatus: state.loadingStatus,
        },
      });

      toast.success("Status edited!", {
        position: "bottom-right",
        autoClose: 5000,
        hideProgressBar: true,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });

      const update = await axios.put(`${apiUrlProgress}/${id}`, editedObj, {
        headers: {
          "X-WP-Nonce": data.nonce,
          "Content-type": "application/json",
        },
      });
    } catch (error) {
      alert(
        "Hum, it was impossible to complete this task. We had an error: " +
          error
      );
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
      {props.children}
    </ProgressContext.Provider>
  );
}
