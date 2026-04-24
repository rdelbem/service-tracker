import { describe, it, expect, beforeEach, vi } from "vitest";
import { render, screen, fireEvent, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import dateformat from "dateformat";
import Status from "../Status";

const { mockProgressStore, useProgressStoreMock, mockShowConfirm } = vi.hoisted(() => {
  const mockProgressStore = {
    deleteStatus: vi.fn(),
    editStatus: vi.fn(),
  };

  const useProgressStoreMock = vi.fn(() => mockProgressStore);

  return {
    mockProgressStore,
    useProgressStoreMock,
    mockShowConfirm: vi.fn(),
  };
});

vi.mock("../../../stores/progressStore", () => ({
  useProgressStore: useProgressStoreMock,
}));

vi.mock("../../ui/Modal", () => ({
  showConfirm: mockShowConfirm,
}));

describe("Status component", () => {
  const baseStatus = {
    id: "st-1",
    id_user: "user-1",
    text: "Initial status text",
    created_at: "2024-02-10T14:30:00",
    attachments: [],
  };

  beforeEach(() => {
    vi.clearAllMocks();
    mockShowConfirm.mockResolvedValue(true);

    Object.assign(globalThis.data, {
      tip_edit_status: "Edit Status",
      tip_delete_status: "Delete Status",
      btn_save_changes_status: "Save",
      btn_dismiss_edit: "Cancel",
    });
  });

  it("renders status text and formatted date", () => {
    render(<Status {...baseStatus} />);

    expect(screen.getByText("Progress Update")).toBeInTheDocument();
    expect(screen.getByText("Initial status text")).toBeInTheDocument();
    expect(screen.getByText(dateformat(baseStatus.created_at, "mmm dd, yyyy, hh:MM TT"))).toBeInTheDocument();
  });

  it("edits and saves status text", async () => {
    const user = userEvent.setup();
    const { container } = render(<Status {...baseStatus} />);

    const editButton = container.querySelector('button[data-tooltip-content="Edit Status"]');
    expect(editButton).toBeTruthy();
    await user.click(editButton!);

    const textarea = screen.getByRole("textbox");
    fireEvent.change(textarea, { target: { value: "Edited text" } });
    await user.click(screen.getByRole("button", { name: "Save" }));

    expect(mockProgressStore.editStatus).toHaveBeenCalledWith("st-1", "user-1", "Edited text");
    await waitFor(() => {
      expect(screen.queryByRole("textbox")).not.toBeInTheDocument();
    });
  });

  it("cancels edit mode without saving", async () => {
    const user = userEvent.setup();
    const { container } = render(<Status {...baseStatus} />);

    const editButton = container.querySelector('button[data-tooltip-content="Edit Status"]');
    expect(editButton).toBeTruthy();
    await user.click(editButton!);

    expect(screen.getByRole("textbox")).toBeInTheDocument();
    await user.click(screen.getByRole("button", { name: "Cancel" }));

    expect(mockProgressStore.editStatus).not.toHaveBeenCalled();
    expect(screen.queryByRole("textbox")).not.toBeInTheDocument();
  });

  it("deletes status only when confirmation is accepted", async () => {
    const user = userEvent.setup();
    const { container, rerender } = render(<Status {...baseStatus} />);

    const deleteButton = container.querySelector('button[data-tooltip-content="Delete Status"]');
    expect(deleteButton).toBeTruthy();

    mockShowConfirm.mockResolvedValueOnce(false);
    await user.click(deleteButton!);
    await waitFor(() => expect(mockShowConfirm).toHaveBeenCalled());
    expect(mockProgressStore.deleteStatus).not.toHaveBeenCalled();

    rerender(<Status {...baseStatus} />);
    const deleteButtonAgain = container.querySelector('button[data-tooltip-content="Delete Status"]');
    await user.click(deleteButtonAgain!);

    await waitFor(() => {
      expect(mockProgressStore.deleteStatus).toHaveBeenCalledWith("st-1", "2024-02-10T14:30:00");
    });
  });

  it("renders attachment links and image indicator for image files", () => {
    render(
      <Status
        {...baseStatus}
        attachments={[
          { url: "https://example.com/photo.png", type: "image/png", name: "photo.png", size: 1280 },
          { url: "https://example.com/report.pdf", type: "application/pdf", name: "report.pdf", size: 2048 },
        ]}
      />
    );

    expect(screen.getByRole("link", { name: /photo\.png/i })).toHaveAttribute("href", "https://example.com/photo.png");
    expect(screen.getByRole("link", { name: /report\.pdf/i })).toHaveAttribute("href", "https://example.com/report.pdf");
    expect(screen.getByText("photo.png")).toBeInTheDocument();
    expect(screen.getByText("report.pdf")).toBeInTheDocument();
  });
});
