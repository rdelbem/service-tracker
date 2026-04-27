import { createRoot } from "react-dom/client";
import { stolmc_text, Text } from "../../i18n";

interface ModalOptions {
  title?: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  type?: "confirm" | "alert";
}

interface ModalProps {
  title: string;
  message: string;
  confirmText: string;
  cancelText: string;
  type: "confirm" | "alert";
  onConfirm: () => void;
  onCancel: () => void;
}

function ModalDialog({ title, message, confirmText, cancelText, type, onConfirm, onCancel }: ModalProps) {
  return (
    <div className="fixed inset-0 z-[10000] flex items-center justify-center">
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-black/50 backdrop-blur-sm"
        onClick={type === "confirm" ? onCancel : undefined}
      />

      {/* Modal */}
      <div className="relative bg-surface-container-low rounded-2xl shadow-2xl max-w-md w-full mx-4 p-8 animate-scale-in">
        {/* Title */}
        <h3 className="text-xl font-bold text-on-surface mb-3">{title}</h3>

        {/* Message */}
        <p className="text-sm text-on-surface-variant leading-relaxed mb-8">{message}</p>

        {/* Actions */}
        <div className="flex justify-end gap-3">
          {type === "confirm" && (
            <button
              onClick={onCancel}
              className="px-6 py-2.5 bg-surface-container-highest text-on-surface text-sm font-bold rounded-xl hover:bg-surface-container-high transition-all"
            >
              {cancelText}
            </button>
          )}
          <button
            onClick={onConfirm}
            className="px-6 py-2.5 bg-primary text-on-primary text-sm font-bold rounded-xl shadow-sm hover:bg-primary-container active:scale-95 transition-all"
          >
            {confirmText}
          </button>
        </div>
      </div>
    </div>
  );
}

/**
 * Show a confirmation modal.
 * Returns a promise that resolves to true if confirmed, false if cancelled.
 */
export function showConfirm(options: Omit<ModalOptions, "type">): Promise<boolean> {
  return new Promise((resolve) => {
    const container = document.createElement("div");
    document.body.appendChild(container);
    const root = createRoot(container);

    const close = (result: boolean) => {
      root.unmount();
      document.body.removeChild(container);
      resolve(result);
    };

    root.render(
      <ModalDialog
        title={options.title || stolmc_text(Text.ModalConfirmTitle)}
        message={options.message}
        confirmText={options.confirmText || stolmc_text(Text.BtnConfirm)}
        cancelText={options.cancelText || stolmc_text(Text.BtnCancel)}
        type="confirm"
        onConfirm={() => close(true)}
        onCancel={() => close(false)}
      />
    );
  });
}

/**
 * Show an alert modal.
 * Returns a promise that resolves when dismissed.
 */
export function showAlert(options: Omit<ModalOptions, "type">): Promise<void> {
  return new Promise((resolve) => {
    const container = document.createElement("div");
    document.body.appendChild(container);
    const root = createRoot(container);

    const close = () => {
      root.unmount();
      document.body.removeChild(container);
      resolve();
    };

    root.render(
      <ModalDialog
        title={options.title || stolmc_text(Text.ModalNoticeTitle)}
        message={options.message}
        confirmText={options.confirmText || stolmc_text(Text.BtnOk)}
        cancelText=""
        type="alert"
        onConfirm={close}
        onCancel={() => {}}
      />
    );
  });
}
