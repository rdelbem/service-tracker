import { useState } from "react";
import { useProgressStore } from "../../stores/progressStore";
import TextareaAutosize from "react-textarea-autosize";
import dateformat from "dateformat";
import { showConfirm } from "../ui/Modal";
import { stolmc_text, Text } from "../../i18n";
import type { Status as StatusType } from "../../types";

interface StatusProps extends StatusType {}

export default function Status({ id, id_user, created_at, text, attachments }: Omit<StatusProps, '_id_case'>) {
  const { deleteStatus, editStatus } = useProgressStore();
  const [editable, setEditable] = useState(false);
  const [editedText, setEditedText] = useState(text);

  const handleDelete = async () => {
    const confirmed = await showConfirm({
      title: stolmc_text(Text.ConfirmDeleteStatusTitle),
      message: stolmc_text(Text.ConfirmDeleteStatusMsg),
      confirmText: stolmc_text(Text.BtnDelete),
    });

    if (confirmed) {
      deleteStatus(id, created_at);
    }
  };

  return (
    <div className="relative pl-10 border-l-2 border-primary-fixed ml-4 space-y-2 pb-8">
      {/* Timeline Dot */}
      <div className="absolute -left-[11px] top-0 w-5 h-5 rounded-full bg-primary flex items-center justify-center ring-4 ring-background">
        <span
          className="material-symbols-outlined text-[12px] text-white"
          style={{ fontVariationSettings: "'FILL' 1" }}
        >
          check_circle
        </span>
      </div>

      {/* Header */}
      <div className="flex justify-between items-start">
        <div className="flex-1">
          <h4 className="text-sm font-bold text-primary">{stolmc_text(Text.ProgressHeading)}</h4>
          <p className="text-xs text-on-primary-container">
            {dateformat(created_at, "mmm dd, yyyy, hh:MM TT")}
          </p>
        </div>

        {/* Action Buttons */}
        <div className="flex items-center gap-2">
          <button
            onClick={() => setEditable(!editable)}
            className="p-2 rounded-lg hover:bg-surface-container-high transition-colors text-on-surface-variant hover:text-primary"
            data-tooltip-id="service-tracker"
            data-tooltip-content={stolmc_text(Text.TipEditStatus)}
          >
            <span className="material-symbols-outlined text-sm">edit</span>
          </button>
          <button
            onClick={handleDelete}
            className="p-2 rounded-lg hover:bg-error-container/30 transition-colors text-on-surface-variant hover:text-error"
            data-tooltip-id="service-tracker"
            data-tooltip-content={stolmc_text(Text.TipDeleteStatus)}
          >
            <span className="material-symbols-outlined text-sm">delete</span>
          </button>
        </div>
      </div>

      {/* Status Text */}
      <div className="bg-surface-container-low p-4 rounded-xl text-sm text-on-surface-variant font-medium leading-relaxed">
        {!editable && <p>{text}</p>}
        {editable && (
          <form>
            <TextareaAutosize
              onChange={(e) => setEditedText(e.target.value)}
              className="w-full p-3 bg-surface-container-lowest rounded-lg border-0 focus:ring-2 focus:ring-primary/10 text-on-surface font-medium resize-none mb-3"
              defaultValue={text}
            />
            <div className="flex gap-2">
              <button
                className="px-4 py-2 bg-primary text-white text-xs font-bold rounded-lg active:scale-95 transition-all"
                onClick={(e) => {
                  e.preventDefault();
                  editStatus(id, id_user, editedText);
                  setEditable(false);
                }}
              >
                {stolmc_text(Text.BtnSaveChangesStatus)}
              </button>
              <button
                className="px-4 py-2 bg-surface-container-highest text-on-surface text-xs font-bold rounded-lg active:scale-95 transition-all"
                onClick={(e) => {
                  e.preventDefault();
                  setEditable(false);
                }}
              >
                {stolmc_text(Text.BtnDismissEdit)}
              </button>
            </div>
          </form>
        )}
      </div>

      {/* Attachments */}
      {attachments && attachments.length > 0 && (
        <div className="flex flex-wrap gap-2">
          {attachments.map((att: { url: string; type: string; name: string; size: number }, index: number) => (
            <a
              key={index}
              href={att.url}
              target="_blank"
              rel="noopener noreferrer"
              className="flex items-center gap-2 px-3 py-2 bg-surface-container-low rounded-lg hover:bg-surface-container-high transition-all group"
              title={att.name}
            >
              <span className="material-symbols-outlined text-sm text-primary">
                {att.type.startsWith("image/") ? "image" : "attach_file"}
              </span>
              <span className="text-xs text-on-surface-variant max-w-[120px] truncate group-hover:text-primary">
                {att.name}
              </span>
              {att.type.startsWith("image/") && (
                <span className="material-symbols-outlined text-sm text-outline-variant group-hover:text-primary">
                  open_in_new
                </span>
              )}
            </a>
          ))}
        </div>
      )}
    </div>
  );
}
