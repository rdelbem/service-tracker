
//libs
import { lazy, useEffect } from "react";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

//stores
import { useInViewStore } from "./stores/inViewStore";
import { useClientsStore } from "./stores/clientsStore";

//components
import Wrapper from "./components/layout/Wrapper";
import CasesContainer from "./components/layout/CasesContainer";
import LazyView from "./components/layout/LazyView";

// Lazy-loaded view components (code-split into separate chunks)
const Initial = lazy(() => import("./components/layout/Initial"));
const HowToUse = lazy(() => import("./components/layout/HowToUse"));
const Cases = lazy(() => import("./components/layout/Cases"));
const Progress = lazy(() => import("./components/layout/Progress"));
const ClientsView = lazy(() => import("./components/layout/ClientsView"));
const ClientDetails = lazy(() => import("./components/layout/ClientDetails"));
const AddCase = lazy(() => import("./components/layout/AddCase"));
const CaseDetails = lazy(() => import("./components/layout/CaseDetails"));

// Initialize stores on app mount
function StoreInitializer() {
  const { getUsers } = useClientsStore();

  useEffect(() => {
    getUsers();
  }, [getUsers]);

  return null;
}

// Main content router component
function MainContent() {
  const view = useInViewStore((state) => state.view);
  const userId = useInViewStore((state) => state.userId);

  // Only render the component for the current view
  // This prevents unnecessary mounting and data fetching
  switch (view) {
    case "init":
      return <LazyView><Initial /></LazyView>;
    case "howToUse":
      return <LazyView><HowToUse /></LazyView>;
    case "cases":
      return <LazyView><Cases /></LazyView>;
    case "caseDetails":
      return <LazyView><CaseDetails /></LazyView>;
    case "casesAddNew":
      return <LazyView><AddCase /></LazyView>;
    case "progress":
      return <LazyView><Progress /></LazyView>;
    case "clients":
      // When in clients view with a selected client, show client details
      // Otherwise show the welcome/initial screen
      if (userId) {
        return <LazyView><ClientDetails /></LazyView>;
      }
      return <LazyView><Initial /></LazyView>;
    default:
      // Default to initial/welcome screen
      return <LazyView><Initial /></LazyView>;
  }
}

//App bootstrap
export default function App() {
  return (
    <>
      <StoreInitializer />
      <ToastContainer />
      <Wrapper>
        <ClientsView />
        <CasesContainer>
          <MainContent />
        </CasesContainer>
      </Wrapper>
    </>
  );
}
