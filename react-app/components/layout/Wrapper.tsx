import { useEffect, useState, ReactNode } from "react";
import Sidebar from "./Sidebar";

interface WrapperProps {
  children: ReactNode;
}

export default function Wrapper({ children }: WrapperProps) {
  const [_adminBarHeight, setAdminBarHeight] = useState(32);

  // Detect WordPress admin bar height
  useEffect(() => {
    const checkWpState = () => {
      const adminBar = document.getElementById('wpadminbar');
      if (adminBar) {
        setAdminBarHeight(adminBar.offsetHeight || 32);
      }
    };

    checkWpState();

    const observer = new MutationObserver(checkWpState);
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });

    return () => observer.disconnect();
  }, []);

  return (
    <div className="flex bg-background">
      <Sidebar />
      <main className="flex-1 flex h-[calc(100vh-32px)]">
        {children}
      </main>
    </div>
  );
}
