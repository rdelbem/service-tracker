import { useState, useContext } from "react";
import ProgressContext from "../../context/progress/progressContext";
import TextareaAutosize from "react-textarea-autosize";
import dateformat from "dateformat";
import { ProgressContextType, Status as StatusType } from "../../types";

interface StatusProps extends StatusType {}

export default function Status({ id, id_user, created_at, text }: Omit<StatusProps, '_id_case'>) {
  const progressContext = useContext(ProgressContext) as ProgressContextType;
  const { deleteStatus, editStatus } = progressContext;
  const [editable, setEditable] = useState(false);
  const [editedText, setEditedText] = useState(text);

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
          <h4 className="text-sm font-bold text-primary">Progress Update</h4>
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
            data-tooltip-content={data.tip_edit_status || "Edit Status"}
          >
            <span className="material-symbols-outlined text-sm">edit</span>
          </button>
          <button
            onClick={() => deleteStatus(id, created_at)}
            className="p-2 rounded-lg hover:bg-error-container/30 transition-colors text-on-surface-variant hover:text-error"
            data-tooltip-id="service-tracker"
            data-tooltip-content={data.tip_delete_status || "Delete Status"}
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
                {data.btn_save_changes_status || "Save"}
              </button>
              <button
                className="px-4 py-2 bg-surface-container-highest text-on-surface text-xs font-bold rounded-lg active:scale-95 transition-all"
                onClick={(e) => {
                  e.preventDefault();
                  setEditable(false);
                }}
              >
                {data.btn_dismiss_edit || "Cancel"}
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
}
