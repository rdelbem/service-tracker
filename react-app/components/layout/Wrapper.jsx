import React, { useEffect, useState } from "react";
import Sidebar from "./Sidebar";

export default function Wrapper(props) {
  const [isWpMenuCollapsed, setIsWpMenuCollapsed] = useState(false);
  const [adminBarHeight, setAdminBarHeight] = useState(32);

  // Detect WordPress admin menu collapse and admin bar height
  useEffect(() => {
    const checkWpState = () => {
      const body = document.body;
      const isCollapsed = body.classList.contains('folded');
      setIsWpMenuCollapsed(isCollapsed);

      // Check if admin bar exists and get its height
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
        {props.children}
      </main>
    </div>
  );
}
