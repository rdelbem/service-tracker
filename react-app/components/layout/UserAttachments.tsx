import { useState, useEffect } from "react";
import { get as fetchGet } from "../../utils/fetch";
import dateformat from "dateformat";
import { stolmc_text, Text } from "../../i18n";

declare const data: Record<string, any>;

interface UserAttachment {
  url: string;
  type: string;
  name: string;
  size: number;
  progress_id: number;
  id_case: number;
  case_title: string;
  created_at: string;
  status_text: string;
}

interface UserAttachmentsProps {
  idUser: string | number;
}

function formatBytes(bytes: number): string {
  if (bytes === 0) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + " " + sizes[i];
}

export default function UserAttachments({ idUser }: UserAttachmentsProps) {
  const [attachments, setAttachments] = useState<UserAttachment[]>([]);
  const [loading, setLoading] = useState(true);
  const [filterCase, setFilterCase] = useState<string>("all");

  useEffect(() => {
    if (!idUser) return;

    const fetchAttachments = async () => {
      setLoading(true);
      try {
        const apiUrl = `${data.root_url}/wp-json/service-tracker-stolmc/v1/progress/user-attachments/${idUser}`;
        const res = await fetchGet(apiUrl, {
          headers: { "X-WP-Nonce": data.nonce },
        });
        setAttachments(Array.isArray(res.data?.data) ? res.data.data : []);
      } catch (error) {
        console.error("Error fetching user attachments:", error);
        setAttachments([]);
      } finally {
        setLoading(false);
      }
    };

    fetchAttachments();
  }, [idUser]);

  if (loading) {
    return (
      <div className="flex items-center gap-2 py-6 text-on-surface-variant">
        <span className="material-symbols-outlined text-sm animate-spin">
          progress_activity
        </span>
        <span className="text-sm">{stolmc_text(Text.AttachmentsLoading)}</span>
      </div>
    );
  }

  if (attachments.length === 0) {
    return (
      <div className="text-center py-10">
        <span className="material-symbols-outlined text-5xl text-outline-variant mb-3">
          folder_open
        </span>
        <p className="text-sm text-on-surface-variant font-medium">
          {stolmc_text(Text.AttachmentsEmpty)}
        </p>
      </div>
    );
  }

  // Build unique case list for filter dropdown.
  const caseOptions = Array.from(
    new Map(attachments.map((a) => [String(a.id_case), a.case_title])).entries()
  );

  const filtered =
    filterCase === "all"
      ? attachments
      : attachments.filter((a) => String(a.id_case) === filterCase);

  return (
    <div>
      {/* Filter bar */}
      {caseOptions.length > 1 && (
        <div className="flex items-center gap-3 mb-6">
          <span className="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
            {stolmc_text(Text.AttachmentsFilterLabel)}
          </span>
          <select
            value={filterCase}
            onChange={(e) => setFilterCase(e.target.value)}
            className="bg-surface-container-lowest border border-outline-variant/20 rounded-lg py-1.5 px-3 text-sm focus:ring-2 focus:ring-primary/10"
          >
            <option value="all">{stolmc_text(Text.AttachmentsAllCases)}</option>
            {caseOptions.map(([id, title]) => (
              <option key={id} value={id}>
                {title}
              </option>
            ))}
          </select>
          <span className="text-xs text-on-surface-variant ml-auto">
            {filtered.length} {filtered.length !== 1 ? stolmc_text(Text.AttachmentPlural) : stolmc_text(Text.AttachmentSingular)}
          </span>
        </div>
      )}

      {/* Attachment grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {filtered.map((att, index) => {
          const isImage = att.type.startsWith("image/");
          return (
            <a
              key={`${att.progress_id}-${index}`}
              href={att.url}
              target="_blank"
              rel="noopener noreferrer"
              className="group flex flex-col bg-surface-container-low rounded-xl overflow-hidden hover:bg-surface-container-high transition-all shadow-sm hover:shadow-md"
            >
              {/* Image preview or file icon */}
              {isImage ? (
                <div className="w-full h-36 bg-surface-container overflow-hidden">
                  <img
                    src={att.url}
                    alt={att.name}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                </div>
              ) : (
                <div className="w-full h-36 bg-surface-container flex items-center justify-center">
                  <span className="material-symbols-outlined text-5xl text-primary/40">
                    attach_file
                  </span>
                </div>
              )}

              {/* File info */}
              <div className="p-3 flex flex-col gap-1">
                <p
                  className="text-sm font-bold text-on-surface truncate group-hover:text-primary transition-colors"
                  title={att.name}
                >
                  {att.name}
                </p>
                <p className="text-xs text-on-surface-variant truncate" title={att.case_title}>
                  {att.case_title}
                </p>
                <div className="flex items-center justify-between mt-1">
                  <span className="text-[10px] text-outline">
                    {dateformat(att.created_at, "mmm dd, yyyy")}
                  </span>
                  <span className="text-[10px] text-outline">
                    {formatBytes(att.size)}
                  </span>
                </div>
              </div>
            </a>
          );
        })}
      </div>
    </div>
  );
}
