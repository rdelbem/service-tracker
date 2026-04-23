// Simple fetch helper to replace axios.
// Always returns canonical v2 envelope under `response.data`.

interface FetchOptions {
  method?: "GET" | "POST" | "PUT" | "DELETE";
  body?: Record<string, any> | FormData | null;
  headers?: Record<string, string>;
  multipart?: boolean;
}

interface FetchResponse {
  data: {
    success: boolean;
    data: any;
    error_code: string | null;
    message: string | null;
    meta: Record<string, any>;
  };
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

  // Handle empty or non-JSON responses.
  const contentType = response.headers.get("content-type");
  let responseData: any = null;

  if (contentType && contentType.includes("application/json")) {
    responseData = await response.json();
  } else {
    // For non-JSON responses (like empty 200/204 responses).
    const text = await response.text();
    responseData = text || null;
  }

  const envelope =
    responseData && typeof responseData === "object" && "success" in responseData
      ? {
          success: Boolean(responseData.success),
          data: "data" in responseData ? responseData.data : null,
          error_code:
            typeof responseData.error_code === "string" ? responseData.error_code : null,
          message: typeof responseData.message === "string" ? responseData.message : null,
          meta:
            responseData.meta && typeof responseData.meta === "object"
              ? responseData.meta
              : {},
        }
      : {
          success: response.ok,
          data: responseData,
          error_code: null,
          message: null,
          meta: {},
        };

  // If the response wasn't OK, throw an error with the canonical message when available.
  if (!response.ok) {
    const errorMessage = envelope.message || `HTTP error! status: ${response.status}`;
    throw new Error(errorMessage);
  }

  // Also check canonical success flag for API errors (even with 200 OK).
  if (!envelope.success) {
    const errorMessage = envelope.message || "API request failed";
    throw new Error(errorMessage);
  }

  return { data: envelope };
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
