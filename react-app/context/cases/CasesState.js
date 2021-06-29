import React, { useReducer, useContext } from "react";
import AppReducer from "../AppReducer";
import axios from "axios";
import CasesContext from "./casesContext";
import InViewContext from "../../context/inView/inViewContext";
import { GET_CASES } from "../types";

export default function CasesState(props) {
  const inViewContext = useContext(InViewContext);
  const currentUserInDisplay = inViewContext.state.id.toString();

  const initialState = {
    user: "",
    cases: [],
    loadingCases: false,
  };

  const [state, dispatch] = useReducer(AppReducer, initialState);

  const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

  const getCases = async (id) => {
    dispatch({
      type: GET_CASES,
      payload: {
        user: state.user,
        cases: state.cases,
        loadingCases: true,
      },
    });

    const res = await axios.get(`${apiUrlCases}/${id}`, {
      headers: {
        "X-WP-Nonce": data.nonce,
      },
    });

    dispatch({
      type: GET_CASES,
      payload: {
        user: state.user,
        cases: res.data,
        loadingCases: false,
      },
    });
  };

  const postCase = async (id, title) => {
    const dataToPost = { id_user: id, title: title };

    try {
      const res = await axios.post(`${apiUrlCases}/${id}`, dataToPost, {
        headers: {
          "X-WP-Nonce": data.nonce,
          "Content-type": "application/json",
        },
      });

      const newCase = { id_user: id, title: title, status: "open" };
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
    } catch (error) {
      console.log(error);
    }
  };

  if (state.user.toString() !== currentUserInDisplay) {
    dispatch({
      type: GET_CASES,
      payload: {
        user: inViewContext.state.id.toString(),
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
      }}
    >
      {props.children}
    </CasesContext.Provider>
  );
}
