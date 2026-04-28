import { useClientsStore } from "../../stores/clientsStore";
import { stolmc_text, Text } from "../../i18n";

interface SearchProps {
  onSearch?: (query: string) => void;
}

export default function Search({ onSearch }: SearchProps) {
  const { searchUsers } = useClientsStore();

  const handleSearch = (query: string) => {
    if (onSearch) {
      onSearch(query);
    } else {
      searchUsers(query);
    }
  };

  return (
    <div className="relative group">
      <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">
        search
      </span>
      <input
        onChange={(e) => {
          handleSearch(e.target.value);
        }}
        type="text"
        placeholder={stolmc_text(Text.SearchBar)}
        className="w-full bg-surface-container-lowest border-0 rounded-xl py-3 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
      />
    </div>
  );
}
