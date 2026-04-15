// Simple fetch helper to replace axios
// Returns Promise<{ data: any }> to match axios response shape

interface FetchOptions {
  method?: "GET" | "POST" | "PUT" | "DELETE";
  body?: Record<string, any> | FormData | null;
  headers?: Record<string, string>;
  multipart?: boolean;
}

interface FetchResponse {
  data: any;
}

export async function request(
  url: string,
  options: FetchOptions = {}
): Promise<FetchResponse> {
  const { method = "GET", body, headers = {}, multipart = false } = options;

  const config: RequestInit = {
    method,
    headers: multipart
      ? { ...headers } // Don't set Content-Type, let browser set it with boundary
      : {
          "Content-Type": "application/json",
          ...headers,
        },
  };

  if (body && (method === "POST" || method === "PUT" || method === "DELETE")) {
    config.body = (multipart ? body : JSON.stringify(body)) as BodyInit | undefined;
  }

  const response = await fetch(url, config);

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  // Handle empty or non-JSON responses
  const contentType = response.headers.get('content-type');
  let data: any = null;
  
  if (contentType && contentType.includes('application/json')) {
    data = await response.json();
  } else {
    // For non-JSON responses (like empty 200/204 responses)
    const text = await response.text();
    data = text ? text : null;
  }
  
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

export const postMultipart = (url: string, body: FormData, config?: { headers?: Record<string, string> }) =>
  request(url, { method: "POST", body, headers: config?.headers, multipart: true });
