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
  success?: boolean;
  error_code?: string | null;
  message?: string | null;
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

  // Handle empty or non-JSON responses
  const contentType = response.headers.get('content-type');
  let responseData: any = null;

  if (contentType && contentType.includes('application/json')) {
    responseData = await response.json();
  } else {
    // For non-JSON responses (like empty 200/204 responses)
    const text = await response.text();
    responseData = text ? text : null;
  }

  // Normalize the response to handle both old and new API formats
  // New API format: { success, data, error_code, message }
  // Some endpoints return just data directly for success responses
  // Some endpoints return legacy format: { success, message, ... }
  // Paginated endpoints return: { data: [...], total, page, per_page, total_pages }

  let normalizedData = responseData;
  let success = response.ok;
  let error_code: string | null = null;
  let message: string | null = null;

  if (responseData && typeof responseData === 'object') {
    // Check if it's the new API format with success flag
    if ('success' in responseData) {
      success = responseData.success;
      error_code = responseData.error_code || null;
      message = responseData.message || null;

      // If success is true and data property exists, use it
      // But be careful: paginated endpoints have data property that's an array,
      // not the entire envelope
      if (success && 'data' in responseData && responseData.data !== undefined) {
        // Check if this looks like a paginated response
        // Paginated responses have data (array) plus pagination metadata
        const hasPaginationFields = 'total' in responseData || 'page' in responseData || 'total_pages' in responseData;
        if (hasPaginationFields) {
          // This is a paginated envelope, keep the whole object
          normalizedData = responseData;
        } else {
          // This is a regular data wrapper, extract the data
          normalizedData = responseData.data;
        }
      } else if (!success) {
        // For errors, include the full response for debugging
        normalizedData = responseData;
      }
    } else if ('data' in responseData && responseData.data !== undefined) {
      // Check if this is a paginated response without success flag
      const hasPaginationFields = 'total' in responseData || 'page' in responseData || 'total_pages' in responseData;
      if (hasPaginationFields) {
        // Paginated envelope, keep it as-is
        normalizedData = responseData;
      } else {
        // Regular data wrapper, extract data
        normalizedData = responseData.data;
      }
    }
    // Otherwise, use responseData as-is
  }

  // If the response wasn't OK, throw an error with the message
  if (!response.ok) {
    const errorMessage = message ||
                        (responseData && responseData.message) ||
                        `HTTP error! status: ${response.status}`;
    throw new Error(errorMessage);
  }

  // Also check success flag for API errors (even with 200 OK)
  if (success === false) {
    const errorMessage = message ||
                        (responseData && responseData.message) ||
                        'API request failed';
    throw new Error(errorMessage);
  }

  return {
    data: normalizedData,
    success,
    error_code,
    message
  };
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
