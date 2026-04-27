import { useState, useEffect } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useProgressStore } from "../../stores/progressStore";
import TextareaAutosize from "react-textarea-autosize";
import Spinner from "./Spinner";
import Status from "./Status";
import UserAttachments from "./UserAttachments";
import { get as fetchGet, put, post, del } from "../../utils/fetch";
import { normalizeUsers } from "../../utils/users";
import { toast } from "react-toastify";
import { showConfirm, showAlert } from "../ui/Modal";
import { useRef } from "react";
import type { Attachment } from "../../types";
import { stolmc_text, Text } from "../../i18n";

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
  const [activeTab, setActiveTab] = useState<"progress" | "attachments">("progress");
  const fileInputRef = useRef<HTMLInputElement>(null);
  const imageInputRef = useRef<HTMLInputElement>(null);

  const idCase = inViewState.caseId;
  const idUser = inViewState.userId;

  useEffect(() => {
    if (!idCase) return;
    const title = inViewState.name || progressState.caseTitle;
    getStatus(idCase, false, title);
  }, [idCase]);

  useEffect(() => {
    if (!idUser || !idCase) return;

    const fetchCaseData = async () => {
      setLoadingCase(true);
      const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;
      try {
        let found: any = null;
        let casesPage = 1;
        let casesTotalPages = 1;

        do {
          const res = await fetchGet(
            `${apiUrlCases}/${idUser}?page=${casesPage}&per_page=6`,
            { headers: { "X-WP-Nonce": data.nonce } }
          );
          const envelope = res.data;
          const cases: any[] = envelope.data ?? [];
          casesTotalPages = envelope.meta?.pagination?.total_pages ?? 1;

          found = cases.find((c: any) => String(c.id) === String(idCase));
          if (found) break;
          casesPage++;
        } while (casesPage <= casesTotalPages);

        setCaseData(found || null);
        setSelectedOwner(found?.owner_id || "");

        if (found?.id_user) {
          const apiUrlUsers = `${data.root_url}/wp-json/service-tracker-stolmc/v1/users`;
          try {
            const usersRes = await fetchGet(
              `${apiUrlUsers}?page=1&per_page=100`,
              { headers: { "X-WP-Nonce": data.nonce } }
            );
            const userList = normalizeUsers(usersRes.data?.data);
            const client = userList.find(
              (u: any) => String(u.id) === String(found.id_user)
            );
            setClientName(client?.name || `${stolmc_text(Text.ProgressClientPrefix)}${found.id_user}`);
          } catch {
            setClientName(`${stolmc_text(Text.ProgressClientPrefix)}${found.id_user}`);
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

  useEffect(() => {
    const fetchStaffUsers = async () => {
      const apiUrlStaff = `${data.root_url}/wp-json/service-tracker-stolmc/v1/users/staff`;
      try {
        const res = await fetchGet(apiUrlStaff, {
          headers: { "X-WP-Nonce": data.nonce },
        });
        setStaffUsers(normalizeUsers(res.data?.data));
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
      showAlert({ title: stolmc_text(Text.ModalNoticeTitle), message: stolmc_text(Text.ToastDateUpdateFailed) });
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
      toast.success(stolmc_text(Text.ToastOwnerChanged));
    } catch (error) {
      console.error("Error updating owner:", error);
      toast.error(stolmc_text(Text.ToastOwnerUpdateFailed));
      setSelectedOwner(caseData?.owner_id || "");
    } finally {
      setSavingOwner(false);
    }
  };

  const handleToggleStatus = async () => {
    const currentStatus = caseData?.status || "open";
    const newStatus = currentStatus === "open" ? "close" : "open";

    const confirmed = await showConfirm({
      title: newStatus === "close" ? stolmc_text(Text.ConfirmCloseCaseTitle) : stolmc_text(Text.ConfirmReopenCaseTitle),
      message: newStatus === "close" ? stolmc_text(Text.ConfirmCloseCaseMsg) : stolmc_text(Text.ConfirmReopenCaseMsg),
      confirmText: newStatus === "close" ? stolmc_text(Text.ConfirmCloseCaseTitle) : stolmc_text(Text.ConfirmReopenCaseTitle),
    });

    if (!confirmed) return;

    const apiUrlToggle = `${data.root_url}/wp-json/${data.api_url}/cases-status`;

    try {
      await post(`${apiUrlToggle}/${idCase}`, null, {
        headers: { "X-WP-Nonce": data.nonce },
      });

      setCaseData((prev: any) => ({ ...prev, status: newStatus }));
      toast.success(`${stolmc_text(Text.ToastToggleBaseMsg)} ${stolmc_text(newStatus === "close" ? Text.ToastToggleStateCloseMsg : Text.ToastToggleStateOpenMsg)}`);
    } catch (error) {
      console.error("Error toggling status:", error);
      toast.error(stolmc_text(Text.ToastCaseToggled));
    }
  };

  const handleDeleteCase = async () => {
    const confirmed = await showConfirm({
      title: stolmc_text(Text.ConfirmDeleteCaseTitle),
      message: stolmc_text(Text.ConfirmDeleteCaseMsg),
      confirmText: stolmc_text(Text.ConfirmDeleteCaseTitle),
    });

    if (!confirmed) return;

    const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

    try {
      await del(`${apiUrlCases}/${idCase}`, {
        headers: { "X-WP-Nonce": data.nonce },
      });

      toast.success(stolmc_text(Text.ToastCaseDeletedSuccess));
      navigate("cases", "", "", "");
    } catch (error) {
      console.error("Error deleting case:", error);
      toast.error(stolmc_text(Text.ToastCaseDeletedSuccess));
    }
  };

  const handleSaveTitle = async () => {
    if (newTitle.trim() === "") {
      showAlert({ title: stolmc_text(Text.ModalNoticeTitle), message: stolmc_text(Text.AlertBlankCaseTitle) });
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
      toast.success(stolmc_text(Text.ToastCaseTitleUpdated));
    } catch (error) {
      console.error("Error updating title:", error);
      toast.error(stolmc_text(Text.ToastTitleUpdateFailed));
    }
  };

  const handleCancelEditTitle = () => {
    setEditingTitle(false);
    setNewTitle("");
  };

  const getOwnerName = () => {
    if (!caseData?.owner_id) return stolmc_text(Text.CaseOwnerUnassigned);
    const owner = staffUsers.find((u: any) => String(u.id) === String(caseData.owner_id));
    return owner ? owner.name : stolmc_text(Text.StatusUnknown);
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

    const newFiles = Array.from(files);
    setPendingFiles([...pendingFiles, ...newFiles]);

    if (e.target) e.target.value = "";
  };

  const handlePostStatus = async () => {
    if (newText.trim() === "") {
      showAlert({ title: stolmc_text(Text.ModalNoticeTitle), message: stolmc_text(Text.AlertBlankStatusTitle) });
      return;
    }

    setUploadingFiles(true);
    try {
      let attachmentsToSend: Attachment[] | undefined;

      if (pendingFiles.length > 0) {
        const uploaded = await uploadFiles(idUser, idCase, pendingFiles);
        if (uploaded.length > 0) {
          attachmentsToSend = uploaded;
        }
      }

      await postStatus(idUser, idCase, newText.trim(), attachmentsToSend);

      setNewText("");
      setPendingFiles([]);
      setWritingStatus(false);
    } catch (error) {
      console.error("Error posting status:", error);
      toast.error(stolmc_text(Text.ToastStatusPostFailed));
    } finally {
      setUploadingFiles(false);
    }
  };

  const removePendingFile = (index: number) => {
    const newFiles = [...pendingFiles];
    newFiles.splice(index, 1);
    setPendingFiles(newFiles);
  };

  if (inViewState.view !== "progress") {
    return null;
  }

  if (progressState.loadingStatus || loadingCase) {
    return <Spinner />;
  }

  const allStatuses = [...progressState.status];

  return (
    <section className="flex-1 flex flex-col bg-background relative h-full">
      <header className="h-20 px-12 flex items-center justify-between border-b border-outline-variant/5 flex-shrink-0">
        <div className="flex items-center gap-4">
          <div className="w-2 h-2 rounded-full bg-secondary shadow-[0_0_8px_rgba(0,108,73,0.4)]"></div>
          <span className="text-lg font-bold text-on-surface-variant">
            {stolmc_text(Text.ClientLabel)}: <span className="text-on-surface">{clientName || stolmc_text(Text.Na)}</span>
          </span>
        </div>
        <div className="flex items-center gap-3">
          <button
            onClick={handleDeleteCase}
            className="flex items-center gap-2 px-4 py-2 bg-error-container/30 text-error text-xs font-bold rounded-lg shadow-lg hover:bg-error-container/50 active:scale-95 transition-all"
            title={stolmc_text(Text.ConfirmDeleteCaseTitle)}
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
            {stolmc_text(Text.BtnBackToCases)}
          </button>
        </div>
      </header>

      <div className="flex-1 overflow-y-auto p-12">
        <div className="mb-12">
          <div className="flex items-baseline gap-6">
            {!editingTitle ? (
              <>
                <h1 className="text-4xl font-black text-on-surface tracking-tighter">
                  {progressState.caseTitle || caseData?.title || stolmc_text(Text.CaseName)}
                </h1>
                <button
                  onClick={() => {
                    setNewTitle(progressState.caseTitle || caseData?.title || "");
                    setEditingTitle(true);
                  }}
                  className="p-2 rounded-lg hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-all"
                  title={stolmc_text(Text.TipEditCase)}
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
                    placeholder={stolmc_text(Text.CaseEditPlaceholder)}
                    autoFocus
                  />
                  <button
                    onClick={handleSaveTitle}
                    className="p-2 rounded-lg bg-primary text-white hover:bg-primary-container active:scale-95 transition-all"
                    title={stolmc_text(Text.BtnSaveCase)}
                  >
                    <span className="material-symbols-outlined text-sm">check</span>
                  </button>
                  <button
                    onClick={handleCancelEditTitle}
                    className="p-2 rounded-lg bg-surface-container-highest text-on-surface-variant hover:bg-surface-container active:scale-95 transition-all"
                    title={stolmc_text(Text.BtnCancel)}
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
                title={caseData?.status === "open" ? stolmc_text(Text.TipToggleCaseOpen) : stolmc_text(Text.TipToggleCaseClose)}
              >
                {caseData?.status === "open" ? stolmc_text(Text.StatusActive) : stolmc_text(Text.StatusClosed)}
              </button>
            )}
          </div>
          <p className="text-on-surface-variant mt-4 max-w-2xl leading-relaxed font-body">
            {stolmc_text(Text.ProgressPlaceholder)}
          </p>

          <div className="mt-6 max-w-2xl">
            <div className="bg-surface-container-low p-4 rounded-xl">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <span className="material-symbols-outlined text-primary text-xl">
                    person
                  </span>
                  <div>
                    <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                      {stolmc_text(Text.CaseOwnerLabel)}
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
                  <option value="">{stolmc_text(Text.CaseOwnerUnassigned)}</option>
                  {staffUsers.map((user: any) => (
                    <option key={user.id} value={user.id}>
                      {user.name} {user.role === "administrator" ? stolmc_text(Text.CaseOwnerAdminSuffix) : ""}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          <div className="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
            <div className="bg-surface-container-low p-4 rounded-xl">
              <div className="flex items-center justify-between mb-2">
                <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                  {stolmc_text(Text.LabelStartDate)}
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
                    : stolmc_text(Text.NotSet)}
                </p>
              )}
            </div>

            <div className="bg-surface-container-low p-4 rounded-xl">
              <div className="flex items-center justify-between mb-2">
                <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                  {stolmc_text(Text.LabelDueDate)}
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
                    : stolmc_text(Text.NotSet)}
                </p>
              )}
            </div>
          </div>
        </div>

        <div className="flex gap-1 mb-8 border-b border-outline-variant/10">
          <button
            onClick={() => setActiveTab("progress")}
            className={`px-5 py-2.5 text-sm font-bold rounded-t-lg transition-all ${
              activeTab === "progress"
                ? "bg-surface-container-lowest text-primary border-b-2 border-primary"
                : "text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low"
            }`}
          >
            <span className="flex items-center gap-2">
              <span className="material-symbols-outlined text-sm">timeline</span>
              {stolmc_text(Text.ProgressTab)}
            </span>
          </button>
          <button
            onClick={() => setActiveTab("attachments")}
            className={`px-5 py-2.5 text-sm font-bold rounded-t-lg transition-all ${
              activeTab === "attachments"
                ? "bg-surface-container-lowest text-primary border-b-2 border-primary"
                : "text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low"
            }`}
          >
            <span className="flex items-center gap-2">
              <span className="material-symbols-outlined text-sm">attach_file</span>
              {stolmc_text(Text.AttachmentsTab)}
            </span>
          </button>
        </div>

        {activeTab === "progress" && (
          <>
            <div className="bg-surface-container-lowest rounded-2xl p-8 shadow-[0px_12px_32px_rgba(11,28,48,0.06)] relative overflow-hidden mb-8">
              <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-secondary to-secondary-container"></div>
              <div className="flex items-center justify-between mb-6">
                <h3 className="text-xl font-headline font-bold text-primary">
                  {stolmc_text(Text.ProgressHeading)}
                </h3>
                <div className="flex items-center gap-2 bg-secondary-container/20 px-3 py-1.5 rounded-full border border-secondary-container/30">
                  <span
                    className="material-symbols-outlined text-[18px] text-on-secondary-container"
                    style={{ fontVariationSettings: "'FILL' 1" }}
                  >
                    mail
                  </span>
                  <span className="text-[10px] font-bold text-on-secondary-container uppercase tracking-wider">
                    {stolmc_text(Text.ProgressNotifyBadge)}
                  </span>
                </div>
              </div>

              {writingStatus && (
                <div className="status-add-new-container animate-fade-in">
                  <div className="relative mb-6">
                    <TextareaAutosize
                      onChange={(e) => setNewText(e.target.value)}
                      className="w-full p-4 bg-surface-container-low rounded-xl border-none focus:ring-2 focus:ring-primary/10 text-on-surface font-medium resize-none placeholder:text-on-primary-container"
                      placeholder={stolmc_text(Text.ProgressPlaceholder)}
                      rows={4}
                      value={newText}
                    />
                  </div>

                  {pendingFiles.length > 0 && (
                    <div className="mb-4 p-3 bg-surface-container-low rounded-xl">
                      <p className="text-xs font-bold text-on-surface-variant mb-2">{stolmc_text(Text.ProgressFilesLabel)}</p>
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
                        title={stolmc_text(Text.ProgressAttachFiles)}
                      >
                        <span className="material-symbols-outlined">attach_file</span>
                      </button>
                      <button
                        type="button"
                        onClick={handleAddImage}
                        disabled={uploadingFiles}
                        className="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all disabled:opacity-50"
                        title={stolmc_text(Text.ProgressAddImage)}
                      >
                        <span className="material-symbols-outlined">image</span>
                      </button>
                      {uploadingFiles && (
                        <div className="flex items-center gap-2 px-3 py-2">
                          <span className="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                          <span className="text-xs text-on-surface-variant">{stolmc_text(Text.ProgressUploading)}</span>
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
                        {stolmc_text(Text.ProgressPostBtn)}
                      </button>
                      <button
                        onClick={(e) => {
                          e.preventDefault();
                          setWritingStatus(false);
                          setPendingFiles([]);
                        }}
                        className="px-6 py-3 bg-surface-container-highest text-on-surface font-bold rounded-xl shadow-lg active:scale-95 transition-all hover:bg-surface-container-high"
                      >
                        {stolmc_text(Text.BtnClose)}
                      </button>
                    </div>
                  </div>

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
                    {stolmc_text(Text.NewStatusBtn)}
                  </span>
                </button>
              )}
            </div>

            <div className="space-y-6">
              <div className="flex items-center gap-4 mb-8">
                <div className="h-[1px] flex-1 bg-outline-variant/20"></div>
                <span className="text-[10px] font-bold text-on-primary-container uppercase tracking-widest px-4">
                  {stolmc_text(Text.ActivityLogHeading)}
                </span>
                <div className="h-[1px] flex-1 bg-outline-variant/20"></div>
              </div>

              {allStatuses.length <= 0 && (
                <div className="text-center py-12">
                  <span className="material-symbols-outlined text-6xl text-outline-variant mb-4">
                    timeline
                  </span>
                  <h3 className="text-xl font-bold text-on-surface-variant">
                    {stolmc_text(Text.NoProgressYet)}
                  </h3>
                  <p className="text-sm text-outline mt-2">
                    {stolmc_text(Text.ActivityLogEmptyDesc)}
                  </p>
                </div>
              )}

              {allStatuses.length > 0 &&
                allStatuses.map((item, index) => <Status key={item.id || index} {...item} />)}
            </div>
          </>
        )}

        {activeTab === "attachments" && (
          <div className="bg-surface-container-lowest rounded-2xl p-8 shadow-[0px_12px_32px_rgba(11,28,48,0.06)]">
            <div className="flex items-center gap-3 mb-6">
              <span className="material-symbols-outlined text-primary">attach_file</span>
              <h3 className="text-xl font-headline font-bold text-primary">
                {stolmc_text(Text.ClientAttachmentsHeading)}
              </h3>
            </div>
            <UserAttachments idUser={idUser} />
          </div>
        )}
      </div>
    </section>
  );
}
