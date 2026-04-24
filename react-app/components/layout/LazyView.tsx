import { Suspense } from "react";
import Spinner from "./Spinner";

interface LazyViewProps {
  children: React.ReactNode;
}

/**
 * Wraps lazy-loaded components with Suspense and a loading fallback.
 */
export default function LazyView({ children }: LazyViewProps) {
  return <Suspense fallback={<Spinner />}>{children}</Suspense>;
}
