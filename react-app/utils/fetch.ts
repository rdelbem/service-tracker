// Simple fetch helper to replace axios
// Returns Promise<{ data: any }> to match axios response shape

interface FetchOptions {
  method?: "GET" | "POST" | "PUT" | "DELETE";
  body?: Record<string, any> | null;
  headers?: Record<string, string>;
}

interface FetchResponse {
  data: any;
}

export async function request(
  url: string,
  options: FetchOptions = {}
): Promise<FetchResponse> {
  const { method = "GET", body, headers = {} } = options;

  const config: RequestInit = {
    method,
    headers: {
      "Content-Type": "application/json",
      ...headers,
    },
  };

  if (body && (method === "POST" || method === "PUT" || method === "DELETE")) {
    config.body = JSON.stringify(body);
  }

  const response = await fetch(url, config);

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const data = await response.json();
  return { data };
}

// Convenience methods matching axios API
export const get = (url: string, config?: { headers?: Record<string, string> }) =>
  request(url, { method: "GET", headers: config?.headers });

export const post = (url: string, body?: any, config?: { headers?: Record<string, string> }) =>
  request(url, { method: "POST", body, headers: config?.headers });

export const put = (url: string, body?: any, config?: { headers?: Record<string, string> }) =>
  request(url, { method: "PUT", body, headers: config?.headers });

export const del = (url: string, config?: { headers?: Record<string, string> }) =>
  request(url, { method: "DELETE", headers: config?.headers });
