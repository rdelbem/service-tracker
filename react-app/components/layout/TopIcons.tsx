import { useInViewStore } from "../../stores/inViewStore";
import { AiOutlineHome, AiOutlineTool } from "react-icons/ai";

export default function TopIcons() {
  const { navigate } = useInViewStore();

  return (
    <div className="top-icons-container">
      <AiOutlineHome
        onClick={() => navigate("init", "", "", "")}
        className="top-icon"
      />
      <AiOutlineTool
        onClick={() => navigate("howToUse", "", "", "")}
        className="top-icon"
      />
    </div>
  );
}
