import React, { useContext } from "react";
import { AiOutlineHome, AiOutlineTool } from "react-icons/ai";
import InViewContext from "../../context/inView/inViewContext";

export default function TopIcons() {
  const inViewContext = useContext(InViewContext);
  const { updateIdView } = inViewContext;

  return (
    <div className="top-icons-container">
      <AiOutlineHome
        onClick={() => updateIdView("", "", "init", "")}
        className="top-icon"
      />
      <AiOutlineTool
        onClick={() => updateIdView("", "", "howToUse", "")}
        className="top-icon"
      />
    </div>
  );
}
