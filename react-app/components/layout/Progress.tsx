import { useState, useEffect } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useProgressStore } from "../../stores/progressStore";
import TextareaAutosize from "react-textarea-autosize";
import Spinner from "./Spinner";
import Status from "./Status";
import { get as fetchGet, put, post, del } from "../../utils/fetch";
import { toast } from "react-toastify";
import { showConfirm, showAlert } from "../ui/Modal";
import { useRef } from "react";
import type { Attachment } from "../../types";

declare const data: Record<string, any>;

export default function Progress() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const progressState = useProgressStore((state) => state);
  const { postStatus, getStatus, uploadFiles } = useProgressStore();
  const [writingStatus, setWritingStatus] = useState(false);
  const [newText, setNewText] = useState("");
  const [caseData, setCaseData] = useState<any>(null);
  const [loadingCase, setLoadingCase] = useState(false);
  const [editingDate, setEditingDate] = useState<"start" | "due" | null>(null);
  const [tempDate, setTempDate] = useState("");
  const [selectedOwner, setSelectedOwner] = useState<string | number>("");
  const [savingOwner, setSavingOwner] = useState(false);
  const [staffUsers, setStaffUsers] = useState<any[]>([]);
  const [editingTitle, setEditingTitle] = useState(false);
  const [newTitle, setNewTitle] = useState("");
  const [clientName, setClientName] = useState<string>("");
  const [pendingFiles, setPendingFiles] = useState<File[]>([]);
  const [uploadingFiles, setUploadingFiles] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const imageInputRef = useRef<HTMLInputElement>(null);

  // Case ID
  const idCase = inViewState.caseId;
  // User ID
  const idUser = inViewState.userId;

  // Load progress data when caseId changes
  useEffect(() => {
    if (!idCase) return;
    const title = inViewState.name || progressState.caseTitle;
    getStatus(idCase, false, title);
  }, [idCase]);

  // Fetch case data for dates
  useEffect(() => {
    if (!idUser || !idCase) return;

    const fetchCaseData = async () => {
      setLoadingCase(true);
      const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;
      try {
        const res = await fetchGet(`${apiUrlCases}/${idUser}`, {
          headers: { "X-WP-Nonce": data.nonce },
        });
        const cases = res.data || [];
        const found = cases.find((c: any) => String(c.id) === String(idCase));
        setCaseData(found || null);
        setSelectedOwner(found?.owner_id || "");

        // Fetch client name
        if (found?.id_user) {
          const apiUrlUsers = `${data.root_url}/wp-json/service-tracker-stolmc/v1/users`;
          try {
            const usersRes = await fetchGet(apiUrlUsers, {
              headers: { "X-WP-Nonce": data.nonce },
            });
            const client = (usersRes.data || []).find((u: any) => String(u.id) === String(found.id_user));
            setClientName(client?.name || `Client #${found.id_user}`);
          } catch {
            setClientName(`Client #${found.id_user}`);
          }
        }
      } catch (error) {
        console.error("Error fetching case data:", error);
      } finally {
        setLoadingCase(false);
      }
    };

    fetchCaseData();
  }, [idUser, idCase]);

  // Fetch staff users for owner dropdown
  useEffect(() => {
    const fetchStaffUsers = async () => {
      const apiUrlStaff = `${data.root_url}/wp-json/service-tracker-stolmc/v1/users/staff`;
      try {
        const res = await fetchGet(apiUrlStaff, {
          headers: { "X-WP-Nonce": data.nonce },
        });
        setStaffUsers(res.data || []);
      } catch (error) {
        console.error("Error fetching staff users:", error);
      }
    };

    fetchStaffUsers();
  }, []);

  const formatDateForInput = (dateStr: string | null) => {
    if (!dateStr) return "";
    return dateStr.substring(0, 16);
  };

  const handleDateEdit = (type: "start" | "due") => {
    const value = type === "start" ? caseData?.start_at : caseData?.due_at;
    setTempDate(formatDateForInput(value));
    setEditingDate(type);
  };

  const handleDateSave = async () => {
    if (!editingDate || !caseData) return;

    const field = editingDate === "start" ? "start_at" : "due_at";
    const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

    try {
      await put(`${apiUrlCases}/${idCase}`, {
        id_user: idUser,
        [field]: tempDate || null,
      }, {
        headers: {
          "X-WP-Nonce": data.nonce,
          "Content-type": "application/json",
        },
      });

      setCaseData((prev: any) => ({ ...prev, [field]: tempDate || null }));
      setEditingDate(null);
    } catch (error) {
      console.error("Error updating date:", error);
      showAlert({ title: "Error", message: "Failed to update date" });
    }
  };

  const handleDateCancel = () => {
    setEditingDate(null);
    setTempDate("");
  };

  const handleOwnerChange = async (newOwnerId: string | number) => {
    setSelectedOwner(newOwnerId);
    setSavingOwner(true);
    const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

    try {
      await put(`${apiUrlCases}/${idCase}`, {
        id_user: idUser,
        owner_id: newOwnerId || null,
      }, {
        headers: {
          "X-WP-Nonce": data.nonce,
          "Content-type": "application/json",
        },
      });

      setCaseData((prev: any) => ({ ...prev, owner_id: newOwnerId || null }));
      const ownerName = newOwnerId
        ? staffUsers.find((u: any) => String(u.id) === String(newOwnerId))?.name || "Unknown"
        : "Unassigned";
      toast.success(`Case owner changed to ${ownerName}`);
    } catch (error) {
      console.error("Error updating owner:", error);
      toast.error("Failed to update case owner");
      setSelectedOwner(caseData?.owner_id || "");
    } finally {
      setSavingOwner(false);
    }
  };

  const handleToggleStatus = async () => {
    const currentStatus = caseData?.status || "open";
    const newStatus = currentStatus === "open" ? "close" : "open";
    const actionLabel = newStatus === "close" ? "closing" : "reopening";

    const confirmed = await showConfirm({
      title: `${newStatus === "close" ? "Close" : "Reopen"} Case`,
      message: `Are you sure you want to ${actionLabel} this case?`,
      confirmText: newStatus === "close" ? "Close Case" : "Reopen Case",
    });

    if (!confirmed) return;

    const apiUrlToggle = `${data.root_url}/wp-json/${data.api_url}/cases-status`;

    try {
      await post(`${apiUrlToggle}/${idCase}`, null, {
        headers: { "X-WP-Nonce": data.nonce },
      });

      setCaseData((prev: any) => ({ ...prev, status: newStatus }));
      toast.success(`Case is now ${newStatus === "close" ? "closed" : "open"}`);
    } catch (error) {
      console.error("Error toggling status:", error);
      toast.error("Failed to update case status");
    }
  };

  const handleDeleteCase = async () => {
    const confirmed = await showConfirm({
      title: "Delete Case",
      message: `Are you sure you want to delete "${progressState.caseTitle || caseData?.title || "this case"}"? This will also delete all associated progress updates.`,
      confirmText: "Delete Case",
    });

    if (!confirmed) return;

    const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

    try {
      await del(`${apiUrlCases}/${idCase}`, {
        headers: { "X-WP-Nonce": data.nonce },
      });

      toast.success("Case deleted successfully");
      navigate("cases", "", "", "");
    } catch (error) {
      console.error("Error deleting case:", error);
      toast.error("Failed to delete case");
    }
  };

  const handleSaveTitle = async () => {
    if (newTitle.trim() === "") {
      showAlert({ title: "Error", message: "Case title cannot be blank" });
      return;
    }

    const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

    try {
      await put(`${apiUrlCases}/${idCase}`, {
        id_user: idUser,
        title: newTitle.trim(),
      }, {
        headers: {
          "X-WP-Nonce": data.nonce,
          "Content-type": "application/json",
        },
      });

      setCaseData((prev: any) => ({ ...prev, title: newTitle.trim() }));
      setEditingTitle(false);
      toast.success("Case title updated successfully");
    } catch (error) {
      console.error("Error updating title:", error);
      toast.error("Failed to update case title");
    }
  };

  const handleCancelEditTitle = () => {
    setEditingTitle(false);
    setNewTitle("");
  };

  const getOwnerName = () => {
    if (!caseData?.owner_id) return "Unassigned";
    const owner = staffUsers.find((u: any) => String(u.id) === String(caseData.owner_id));
    return owner ? owner.name : "Unknown";
  };

  const handleAttachFiles = () => {
    fileInputRef.current?.click();
  };

  const handleAddImage = () => {
    imageInputRef.current?.click();
  };

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;

    // Add files to pending list (don't upload yet)
    const newFiles = Array.from(files);
    setPendingFiles([...pendingFiles, ...newFiles]);

    // Reset the input
    if (e.target) e.target.value = "";
  };

  const handlePostStatus = async () => {
    if (newText.trim() === "") {
      showAlert({ title: "Error", message: data.alert_blank_status_title || "Status title cannot be blank" });
      return;
    }
    
    setUploadingFiles(true);
    try {
      let attachmentsToSend: Attachment[] | undefined;

      // Upload files first if there are any
      if (pendingFiles.length > 0) {
        const uploaded = await uploadFiles(idUser, idCase, pendingFiles);
        if (uploaded.length > 0) {
          attachmentsToSend = uploaded;
        }
      }

      // Create status with attachments
      await postStatus(idUser, idCase, newText.trim(), attachmentsToSend);
      
      // Clean up
      setNewText("");
      setPendingFiles([]);
      setWritingStatus(false);
    } catch (error) {
      console.error("Error posting status:", error);
      toast.error("Failed to post status update");
    } finally {
      setUploadingFiles(false);
    }
  };

  const removePendingFile = (index: number) => {
    const newFiles = [...pendingFiles];
    newFiles.splice(index, 1);
    setPendingFiles(newFiles);
  };

  if (progressState.loadingStatus || loadingCase) {
    return <Spinner />;
  }

  const allStatuses = [...progressState.status];

  return (
    <section className="flex-1 flex flex-col bg-background relative h-full">
      {/* Top Action Header */}
      <header className="h-20 px-12 flex items-center justify-between border-b border-outline-variant/5 flex-shrink-0">
        <div className="flex items-center gap-4">
          <div className="w-2 h-2 rounded-full bg-secondary shadow-[0_0_8px_rgba(0,108,73,0.4)]"></div>
          <span className="text-lg font-bold text-on-surface-variant">
            Client: <span className="text-on-surface">{clientName || "Loading..."}</span>
          </span>
        </div>
        <div className="flex items-center gap-3">
          <button
            onClick={handleDeleteCase}
            className="flex items-center gap-2 px-4 py-2 bg-error-container/30 text-error text-xs font-bold rounded-lg shadow-lg hover:bg-error-container/50 active:scale-95 transition-all"
            title="Delete Case"
          >
            <span className="material-symbols-outlined text-sm">delete</span>
          </button>
          <button
            onClick={() => {
              navigate("cases", idUser, idCase, inViewState.name);
            }}
            className="flex items-center gap-2 px-4 py-2 bg-surface-container-highest text-on-surface text-xs font-bold rounded-lg shadow-lg hover:shadow-xl active:scale-95 transition-all"
          >
            <span className="material-symbols-outlined text-sm">arrow_back</span>
            Back to Cases
          </button>
        </div>
      </header>

      {/* Progress Content */}
      <div className="flex-1 overflow-y-auto p-12">
        {/* Case Title Header */}
        <div className="mb-12">
          <div className="flex items-baseline gap-6">
            {!editingTitle ? (
              <>
                <h1 className="text-4xl font-black text-on-surface tracking-tighter">
                  {progressState.caseTitle || caseData?.title || "Case"}
                </h1>
                <button
                  onClick={() => {
                    setNewTitle(progressState.caseTitle || caseData?.title || "");
                    setEditingTitle(true);
                  }}
                  className="p-2 rounded-lg hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-all"
                  title="Edit title"
                >
                  <span className="material-symbols-outlined text-sm">edit</span>
                </button>
              </>
            ) : (
              <div className="flex-1">
                <div className="flex items-center gap-3">
                  <input
                    type="text"
                    value={newTitle}
                    onChange={(e) => setNewTitle(e.target.value)}
                    className="flex-1 bg-surface-container-low border border-outline-variant/20 rounded-xl py-2 px-4 text-2xl font-black text-on-surface focus:ring-2 focus:ring-primary/10"
                    placeholder="Enter case title..."
                    autoFocus
                  />
                  <button
                    onClick={handleSaveTitle}
                    className="p-2 rounded-lg bg-primary text-white hover:bg-primary-container active:scale-95 transition-all"
                    title="Save title"
                  >
                    <span className="material-symbols-outlined text-sm">check</span>
                  </button>
                  <button
                    onClick={handleCancelEditTitle}
                    className="p-2 rounded-lg bg-surface-container-highest text-on-surface-variant hover:bg-surface-container active:scale-95 transition-all"
                    title="Cancel"
                  >
                    <span className="material-symbols-outlined text-sm">close</span>
                  </button>
                </div>
              </div>
            )}
            {!editingTitle && (
              <button
                onClick={handleToggleStatus}
                className={`px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-md cursor-pointer hover:opacity-80 transition-all ${
                  caseData?.status === "open"
                    ? "bg-secondary-container/40 text-on-secondary-container hover:bg-secondary-container/60"
                    : "bg-surface-dim/40 text-outline hover:bg-surface-dim/60"
                }`}
                title={caseData?.status === "open" ? "Click to close case" : "Click to reopen case"}
              >
                {caseData?.status === "open" ? "Active" : "Closed"}
              </button>
            )}
          </div>
          <p className="text-on-surface-variant mt-4 max-w-2xl leading-relaxed font-body">
            Track all updates and progress for this case
          </p>

          {/* Owner Assignment */}
          <div className="mt-6 max-w-2xl">
            <div className="bg-surface-container-low p-4 rounded-xl">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <span className="material-symbols-outlined text-primary text-xl">
                    person
                  </span>
                  <div>
                    <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                      Case Owner
                    </label>
                    <p className="text-sm text-on-surface font-medium">
                      {getOwnerName()}
                    </p>
                  </div>
                </div>
                <select
                  value={selectedOwner}
                  onChange={(e) => handleOwnerChange(e.target.value)}
                  disabled={savingOwner}
                  className="bg-surface-container-lowest border border-outline-variant/20 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary/10 disabled:opacity-50"
                >
                  <option value="">Unassigned</option>
                  {staffUsers.map((user: any) => (
                    <option key={user.id} value={user.id}>
                      {user.name} {user.role === "administrator" ? "(Admin)" : ""}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          {/* Editable Dates */}
          <div className="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
            {/* Start Date */}
            <div className="bg-surface-container-low p-4 rounded-xl">
              <div className="flex items-center justify-between mb-2">
                <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                  Start Date
                </label>
                {editingDate !== "start" && (
                  <button
                    onClick={() => handleDateEdit("start")}
                    className="p-1 rounded hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-all"
                  >
                    <span className="material-symbols-outlined text-sm">edit</span>
                  </button>
                )}
              </div>
              {editingDate === "start" ? (
                <div className="flex items-center gap-2">
                  <input
                    type="datetime-local"
                    value={tempDate}
                    onChange={(e) => setTempDate(e.target.value)}
                    className="flex-1 bg-surface-container-lowest border border-outline-variant/20 rounded-lg py-2 px-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/10"
                  />
                  <button
                    onClick={handleDateSave}
                    className="p-2 rounded-lg bg-primary text-white hover:bg-primary-container transition-all"
                  >
                    <span className="material-symbols-outlined text-sm">check</span>
                  </button>
                  <button
                    onClick={handleDateCancel}
                    className="p-2 rounded-lg bg-surface-container-highest text-on-surface-variant hover:bg-surface-container transition-all"
                  >
                    <span className="material-symbols-outlined text-sm">close</span>
                  </button>
                </div>
              ) : (
                <p className="text-sm text-on-surface font-medium">
                  {caseData?.start_at
                    ? new Date(caseData.start_at).toLocaleString("en-US", {
                        year: "numeric",
                        month: "short",
                        day: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                      })
                    : "Not set"}
                </p>
              )}
            </div>

            {/* Due Date */}
            <div className="bg-surface-container-low p-4 rounded-xl">
              <div className="flex items-center justify-between mb-2">
                <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                  Due Date
                </label>
                {editingDate !== "due" && (
                  <button
                    onClick={() => handleDateEdit("due")}
                    className="p-1 rounded hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-all"
                  >
                    <span className="material-symbols-outlined text-sm">edit</span>
                  </button>
                )}
              </div>
              {editingDate === "due" ? (
                <div className="flex items-center gap-2">
                  <input
                    type="datetime-local"
                    value={tempDate}
                    onChange={(e) => setTempDate(e.target.value)}
                    className="flex-1 bg-surface-container-lowest border border-outline-variant/20 rounded-lg py-2 px-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/10"
                  />
                  <button
                    onClick={handleDateSave}
                    className="p-2 rounded-lg bg-primary text-white hover:bg-primary-container transition-all"
                  >
                    <span className="material-symbols-outlined text-sm">check</span>
                  </button>
                  <button
                    onClick={handleDateCancel}
                    className="p-2 rounded-lg bg-surface-container-highest text-on-surface-variant hover:bg-surface-container transition-all"
                  >
                    <span className="material-symbols-outlined text-sm">close</span>
                  </button>
                </div>
              ) : (
                <p className="text-sm text-on-surface font-medium">
                  {caseData?.due_at
                    ? new Date(caseData.due_at).toLocaleString("en-US", {
                        year: "numeric",
                        month: "short",
                        day: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                      })
                    : "Not set"}
                </p>
              )}
            </div>
          </div>
        </div>

        {/* Status Update Input */}
        <div className="bg-surface-container-lowest rounded-2xl p-8 shadow-[0px_12px_32px_rgba(11,28,48,0.06)] relative overflow-hidden mb-8">
          <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-secondary to-secondary-container"></div>
          <div className="flex items-center justify-between mb-6">
            <h3 className="text-xl font-headline font-bold text-primary">
              Progress Update
            </h3>
            <div className="flex items-center gap-2 bg-secondary-container/20 px-3 py-1.5 rounded-full border border-secondary-container/30">
              <span
                className="material-symbols-outlined text-[18px] text-on-secondary-container"
                style={{ fontVariationSettings: "'FILL' 1" }}
              >
                mail
              </span>
              <span className="text-[10px] font-bold text-on-secondary-container uppercase tracking-wider">
                Client will be notified
              </span>
            </div>
          </div>

          {writingStatus && (
            <div className="status-add-new-container animate-fade-in">
              <div className="relative mb-6">
                <TextareaAutosize
                  onChange={(e) => setNewText(e.target.value)}
                  className="w-full p-4 bg-surface-container-low rounded-xl border-none focus:ring-2 focus:ring-primary/10 text-on-surface font-medium resize-none placeholder:text-on-primary-container"
                  placeholder="Type progress details here... (e.g., 'Initial site visit completed')"
                  rows={4}
                  value={newText}
                />
              </div>

              {/* Pending files display */}
              {pendingFiles.length > 0 && (
                <div className="mb-4 p-3 bg-surface-container-low rounded-xl">
                  <p className="text-xs font-bold text-on-surface-variant mb-2">Files to Attach:</p>
                  <div className="flex flex-wrap gap-2">
                    {pendingFiles.map((file, index) => (
                      <div key={index} className="flex items-center gap-2 px-3 py-2 bg-surface-container-high rounded-lg">
                        <span className="material-symbols-outlined text-sm text-primary">
                          {file.type.startsWith("image/") ? "image" : "attach_file"}
                        </span>
                        <span className="text-xs text-on-surface-variant max-w-[120px] truncate">{file.name}</span>
                        <button
                          onClick={() => removePendingFile(index)}
                          className="text-on-surface-variant hover:text-error transition-colors"
                        >
                          <span className="material-symbols-outlined text-sm">close</span>
                        </button>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              <div className="flex justify-between items-center">
                <div className="flex gap-2">
                  <button
                    type="button"
                    onClick={handleAttachFiles}
                    disabled={uploadingFiles}
                    className="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all disabled:opacity-50"
                    title="Attach Files"
                  >
                    <span className="material-symbols-outlined">attach_file</span>
                  </button>
                  <button
                    type="button"
                    onClick={handleAddImage}
                    disabled={uploadingFiles}
                    className="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all disabled:opacity-50"
                    title="Add Image"
                  >
                    <span className="material-symbols-outlined">image</span>
                  </button>
                  {uploadingFiles && (
                    <div className="flex items-center gap-2 px-3 py-2">
                      <span className="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                      <span className="text-xs text-on-surface-variant">Uploading...</span>
                    </div>
                  )}
                </div>
                <div className="flex gap-3">
                  <button
                    onClick={(e) => {
                      e.preventDefault();
                      handlePostStatus();
                    }}
                    className="px-8 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-lg active:scale-95 transition-transform"
                  >
                    {data.add_status_btn || "Post Update"}
                  </button>
                  <button
                    onClick={(e) => {
                      e.preventDefault();
                      setWritingStatus(false);
                      setPendingFiles([]);
                    }}
                    className="px-6 py-3 bg-surface-container-highest text-on-surface font-bold rounded-xl shadow-lg active:scale-95 transition-all hover:bg-surface-container-high"
                  >
                    {data.close_box_btn || "Close"}
                  </button>
                </div>
              </div>

              {/* Hidden file inputs */}
              <input
                ref={fileInputRef}
                type="file"
                multiple
                accept="*/*"
                onChange={handleFileSelect}
                className="hidden"
              />
              <input
                ref={imageInputRef}
                type="file"
                multiple
                accept="image/*"
                onChange={handleFileSelect}
                className="hidden"
              />
            </div>
          )}

          {!writingStatus && (
            <button
              onClick={(e) => {
                e.preventDefault();
                setWritingStatus(true);
              }}
              className="w-full py-3 bg-gradient-to-br from-primary to-primary-container text-white font-bold rounded-xl shadow-lg active:scale-95 transition-all text-sm"
            >
              <span className="flex items-center justify-center gap-2">
                <span className="material-symbols-outlined text-sm">add_circle</span>
                {data.new_status_btn || "Add Status Update"}
              </span>
            </button>
          )}
        </div>

        {/* Timeline / Activity Log */}
        <div className="space-y-6">
          <div className="flex items-center gap-4 mb-8">
            <div className="h-[1px] flex-1 bg-outline-variant/20"></div>
            <span className="text-[10px] font-bold text-on-primary-container uppercase tracking-widest px-4">
              Activity Log
            </span>
            <div className="h-[1px] flex-1 bg-outline-variant/20"></div>
          </div>

          {allStatuses.length <= 0 && (
            <div className="text-center py-12">
              <span className="material-symbols-outlined text-6xl text-outline-variant mb-4">
                timeline
              </span>
              <h3 className="text-xl font-bold text-on-surface-variant">
                {data.no_progress_yet || "No progress updates yet"}
              </h3>
              <p className="text-sm text-outline mt-2">
                Add your first status update to get started
              </p>
            </div>
          )}

          {allStatuses.length > 0 &&
            allStatuses.map((item, index) => <Status key={item.id || index} {...item} />)}
        </div>
      </div>
    </section>
  );
}
