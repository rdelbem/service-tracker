import { describe, it, expect, beforeEach, vi } from "vitest";
import { render, screen, waitFor, fireEvent } from "@testing-library/react";
import dateformat from "dateformat";
import UserAttachments from "../UserAttachments";

const { mockFetchGet } = vi.hoisted(() => ({
  mockFetchGet: vi.fn(),
}));

vi.mock("../../../utils/fetch", () => ({
  get: mockFetchGet,
}));

describe("UserAttachments component", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("shows loading state while attachments are being fetched", () => {
    mockFetchGet.mockReturnValue(new Promise(() => {}));

    render(<UserAttachments idUser="42" />);

    expect(screen.getByText(/loading attachments/i)).toBeInTheDocument();
    expect(mockFetchGet).toHaveBeenCalledWith(
      "http://localhost/wp-json/service-tracker-stolmc/v1/progress/user-attachments/42",
      expect.objectContaining({
        headers: { "X-WP-Nonce": "test-nonce" },
      })
    );
  });

  it("renders empty state when api returns no attachments", async () => {
    mockFetchGet.mockResolvedValue({ data: { data: [] } });

    render(<UserAttachments idUser="42" />);

    await waitFor(() => {
      expect(screen.getByText(/no attachments found for this client/i)).toBeInTheDocument();
    });
  });

  it("renders attachments and formats metadata", async () => {
    mockFetchGet.mockResolvedValue({
      data: {
        data: [
          {
            url: "https://example.com/a.png",
            type: "image/png",
            name: "a.png",
            size: 1024,
            progress_id: 1,
            id_case: 10,
            case_title: "Case A",
            created_at: "2024-01-15T08:00:00",
            status_text: "Uploaded",
          },
          {
            url: "https://example.com/spec.pdf",
            type: "application/pdf",
            name: "spec.pdf",
            size: 2 * 1024 * 1024,
            progress_id: 2,
            id_case: 10,
            case_title: "Case A",
            created_at: "2024-01-16T09:00:00",
            status_text: "Uploaded",
          },
        ],
      },
    });

    render(<UserAttachments idUser="42" />);

    expect(await screen.findByRole("link", { name: /a\.png/i })).toHaveAttribute("href", "https://example.com/a.png");
    expect(screen.getByRole("link", { name: /spec\.pdf/i })).toHaveAttribute("href", "https://example.com/spec.pdf");
    expect(screen.getAllByText("Case A")).toHaveLength(2);
    expect(screen.getByText("1 KB")).toBeInTheDocument();
    expect(screen.getByText("2 MB")).toBeInTheDocument();
    expect(screen.getByText(dateformat("2024-01-15T08:00:00", "mmm dd, yyyy"))).toBeInTheDocument();
  });

  it("filters attachments by selected case when multiple cases exist", async () => {
    mockFetchGet.mockResolvedValue({
      data: {
        data: [
          {
            url: "https://example.com/a.png",
            type: "image/png",
            name: "a.png",
            size: 1024,
            progress_id: 1,
            id_case: 10,
            case_title: "Case A",
            created_at: "2024-01-15T08:00:00",
            status_text: "Uploaded",
          },
          {
            url: "https://example.com/b.pdf",
            type: "application/pdf",
            name: "b.pdf",
            size: 2048,
            progress_id: 2,
            id_case: 20,
            case_title: "Case B",
            created_at: "2024-01-16T08:00:00",
            status_text: "Uploaded",
          },
        ],
      },
    });

    render(<UserAttachments idUser="42" />);

    await screen.findByText(/filter by case/i);
    expect(screen.getByText("2 attachments")).toBeInTheDocument();

    const select = screen.getByRole("combobox");
    fireEvent.change(select, { target: { value: "20" } });

    expect(screen.queryByRole("link", { name: /a\.png/i })).not.toBeInTheDocument();
    expect(screen.getByRole("link", { name: /b\.pdf/i })).toBeInTheDocument();
    expect(screen.getByText("1 attachment")).toBeInTheDocument();
  });

  it("handles fetch errors by showing empty state", async () => {
    vi.spyOn(console, "error").mockImplementation(() => {});
    mockFetchGet.mockRejectedValue(new Error("network"));

    render(<UserAttachments idUser="42" />);

    await waitFor(() => {
      expect(screen.getByText(/no attachments found for this client/i)).toBeInTheDocument();
    });
  });
});
