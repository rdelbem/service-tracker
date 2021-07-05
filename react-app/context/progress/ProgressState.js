import React, { useContext, useReducer } from "react";
import InViewContext from "../../context/inView/inViewContext";
import AppReducer from "../AppReducer";
import ProgressContext from "./progressContext";
import { GET_STATUS } from "../types";
import { toast } from "react-toastify";
import axios from "axios";

export default function ProgressState(props) {
  const inViewContext = useContext(InViewContext);
  const currentUserInDisplay = inViewContext.state.id.toString();

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
      alert("Hum, we had an error: " + error);
    }
  };

  return (
    <ProgressContext.Provider
      value={{
        state,
        getStatus,
      }}
    >
      {props.children}
    </ProgressContext.Provider>
  );
}
