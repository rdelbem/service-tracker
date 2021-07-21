import React, { useReducer, useContext } from "react";
import CasesContext from "./casesContext";
import AppReducer from "../AppReducer";
import InViewContext from "../../context/inView/inViewContext";

import { toast } from "react-toastify";
import axios from "axios";
import { GET_CASES } from "../types";

export default function CasesState(props) {
  const inViewContext = useContext(InViewContext);
  const currentUserInDisplay = inViewContext.state.userId;

  const initialState = {
    user: "",
    cases: [],
    loadingCases: false,
  };
  const [state, dispatch] = useReducer(AppReducer, initialState);
  const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

  const getCases = async (id, onlyFetch) => {
    try {
      if (!onlyFetch) {
        dispatch({
          type: GET_CASES,
          payload: {
            user: state.user,
            cases: state.cases,
            loadingCases: true,
          },
        });
      }

      const res = await axios.get(`${apiUrlCases}/${id}`, {
        headers: {
          "X-WP-Nonce": data.nonce,
        },
      });

      if (!onlyFetch) {
        dispatch({
          type: GET_CASES,
          payload: {
            user: state.user,
            cases: res.data,
            loadingCases: false,
          },
        });
      }

      return res.data;
    } catch (error) {}
  };

  const postCase = async (id, title) => {
    if (title === "") {
      alert("The title can not be blank!");
      return;
    }

    const dataToPost = { id_user: id, title: title };

    try {
      const postCase = await axios.post(`${apiUrlCases}/${id}`, dataToPost, {
        headers: {
          "X-WP-Nonce": data.nonce,
          "Content-type": "application/json",
        },
      });

      const getAllCases = await getCases(id, true);
      const newCaseAutoGeneratedId = getAllCases[getAllCases.length - 1].id;
      const newCase = {
        id: newCaseAutoGeneratedId,
        id_user: id,
        title: title,
        status: "open",
      };
      let currentCases = state.cases;
      let newCases = [...currentCases, newCase];

      dispatch({
        type: GET_CASES,
        payload: {
          user: id,
          cases: newCases,
          loadingCases: false,
        },
      });

      toast.success(data.toast_case_added, {
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

  const toggleCase = async (id) => {
    try {
      let currentCases = [...state.cases];
      let notice;

      currentCases.forEach((element) => {
        if (element.id.toString() === id.toString()) {
          switch (element.status) {
            case "close":
              element.status = "open";
              notice = data.toast_toggle_state_open_msg;
              break;
            case "open":
              element.status = "close";
              notice = data.toast_toggle_state_close_msg;
              break;
            default:
              break;
          }
        }
      });

      dispatch({
        type: GET_CASES,
        payload: {
          user: id,
          cases: currentCases,
          loadingCases: state.loadingCases,
        },
      });

      toast.info(`${data.toast_toggle_base_msg} ${notice}`, {
        position: "bottom-right",
        autoClose: 5000,
        hideProgressBar: true,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });

      const toggleCase = await axios.post(`${apiUrlCases}-status/${id}`, null, {
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

  const deleteCase = async (id, title) => {
    const confirm = window.confirm(
      data.confirm_delete_case + " " + title + "?"
    );
    if (!confirm) return;

    try {
      const cases = state.cases;
      const filteredCases = cases.filter(
        (theCase) => theCase.id.toString() !== id.toString()
      );

      dispatch({
        type: GET_CASES,
        payload: {
          user: id,
          cases: filteredCases,
          loadingCases: state.loadingCases,
        },
      });

      toast.warn(data.toast_case_deleted, {
        position: "bottom-right",
        autoClose: 5000,
        hideProgressBar: true,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });

      const deleteCase = await axios.delete(`${apiUrlCases}/${id}`, {
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

  const editCase = async (id, id_user, newTitle) => {
    if (newTitle === "") {
      alert(data.alert_blank_case_title);
      return;
    }

    const idTitleObj = JSON.stringify({ id_user: id_user, title: newTitle });

    try {
      const cases = [...state.cases];
      cases.forEach((item) => {
        if (item.id === id) {
          item.title = newTitle;
        }
      });

      dispatch({
        type: GET_CASES,
        payload: {
          user: id_user,
          cases: cases,
          loadingCases: state.loadingCases,
        },
      });

      toast.success(data.toast_case_edited, {
        position: "bottom-right",
        autoClose: 5000,
        hideProgressBar: true,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });

      const update = await axios.put(`${apiUrlCases}/${id}`, idTitleObj, {
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

  if (state.user !== currentUserInDisplay) {
    dispatch({
      type: GET_CASES,
      payload: {
        user: inViewContext.state.userId,
        cases: state.cases,
        loadingCases: state.loadingCases,
      },
    });
  }

  return (
    <CasesContext.Provider
      value={{
        state,
        currentUserInDisplay,
        getCases,
        postCase,
        deleteCase,
        toggleCase,
        editCase,
      }}
    >
      {props.children}
    </CasesContext.Provider>
  );
}
