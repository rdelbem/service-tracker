import { useContext } from "react";
import ClientsContext from "../../context/clients/clientsContext";

export default function Search() {
  const clientsContext = useContext(ClientsContext);
  const { searchUsers } = clientsContext;

  return (
    <div className="relative group">
      <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">
        search
      </span>
      <input
        onChange={(e) => {
          searchUsers(e.target.value);
        }}
        type="text"
        placeholder={data.search_bar || "Search accounts..."}
        className="w-full bg-surface-container-lowest border-0 rounded-xl py-3 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
      />
    </div>
  );
}
