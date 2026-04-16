import { useEffect, useState } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useClientsStore } from "../../stores/clientsStore";
import { toast } from "react-toastify";
import type { User } from "../../types";

declare const data: Record<string, any>;

export default function ClientDetails() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const { users, updateUser } = useClientsStore();

  const [isEditing, setIsEditing] = useState(false);
  const [formData, setFormData] = useState({
    email: "",
    phone: "",
    cellphone: "",
  });
  const [saving, setSaving] = useState(false);

  // Find the selected user from the clients list.
  const selectedClient = users.find(
    (user: User) => String(user.id) === String(inViewState.userId)
  );

  const clientName = selectedClient?.name || inViewState.name || "Client";

  const createdDate = selectedClient?.created_at
    ? new Date(selectedClient.created_at).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
      })
    : "N/A";

  // Initialize form data when client changes
  useEffect(() => {
    if (selectedClient) {
      setFormData({
        email: selectedClient.email || "",
        phone: selectedClient.phone || "",
        cellphone: selectedClient.cellphone || "",
      });
    }
  }, [selectedClient]);

  // Keep hook order stable across route transitions.
  if (inViewState.view !== "clients" || !inViewState.userId) {
    return null;
  }

  const handleSave = async () => {
    // Basic email validation
    if (formData.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      toast.error("Please enter a valid email address");
      return;
    }

    setSaving(true);
    try {
      await updateUser(selectedClient!.id, {
        email: formData.email,
        phone: formData.phone,
        cellphone: formData.cellphone,
      });
      toast.success("Client information updated successfully");
      setIsEditing(false);
    } catch (error) {
      toast.error("Failed to update client information");
      console.error("Update error:", error);
    } finally {
      setSaving(false);
    }
  };

  const handleCancel = () => {
    // Reset form to original values
    setFormData({
      email: selectedClient?.email || "",
      phone: selectedClient?.phone || "",
      cellphone: selectedClient?.cellphone || "",
    });
    setIsEditing(false);
  };

  return (
    <section className="flex-1 h-full overflow-y-auto">
      <div className="p-12">
        {/* Header */}
        <div className="flex items-center gap-6 mb-8">
          <div className="w-20 h-20 rounded-2xl bg-primary text-white flex items-center justify-center text-3xl font-bold shadow-lg">
            {clientName.charAt(0).toUpperCase()}
          </div>
          <div>
            <h1 className="text-3xl font-black text-on-surface tracking-tight">
              {clientName}
            </h1>
            <p className="text-sm text-on-surface-variant font-medium">
              {selectedClient?.role || "Customer"}
            </p>
          </div>
        </div>

        {/* Editable Contact Information */}
        <div className="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 shadow-sm p-6 mb-8">
          <div className="border-b border-outline-variant pb-4 mb-6">
            <div className="flex items-center justify-between">
              <h2 className="text-xl font-bold text-on-surface">Contact Information</h2>
              {!isEditing ? (
                <button
                  onClick={() => setIsEditing(true)}
                  className="px-4 py-2 bg-primary text-on-primary font-bold rounded-xl shadow-sm hover:bg-primary-container transition-colors text-sm"
                >
                  Edit
                </button>
              ) : (
                <div className="flex gap-2">
                  <button
                    onClick={handleSave}
                    disabled={saving}
                    className="px-4 py-2 bg-primary text-on-primary font-bold rounded-xl shadow-sm hover:bg-primary-container transition-colors text-sm disabled:opacity-50"
                  >
                    {saving ? "Saving..." : "Save"}
                  </button>
                  <button
                    onClick={handleCancel}
                    disabled={saving}
                    className="px-4 py-2 bg-surface-container-high text-on-surface font-bold rounded-xl shadow-sm hover:bg-surface-container transition-colors text-sm disabled:opacity-50"
                  >
                    Cancel
                  </button>
                </div>
              )}
            </div>
          </div>

          <div className="space-y-6">
            <div>
              <label className="block text-sm font-bold text-on-surface mb-2">
                Email
              </label>
              {isEditing ? (
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className="w-full p-3 bg-surface-container-low rounded-xl border border-outline-variant focus:outline-none focus:ring-2 focus:ring-primary text-on-surface"
                  placeholder="Enter email address"
                />
              ) : (
                <p className="text-on-surface-variant">
                  {selectedClient?.email || "No email provided"}
                </p>
              )}
            </div>

            <div>
              <label className="block text-sm font-bold text-on-surface mb-2">
                Phone
              </label>
              {isEditing ? (
                <input
                  type="tel"
                  value={formData.phone}
                  onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                  className="w-full p-3 bg-surface-container-low rounded-xl border border-outline-variant focus:outline-none focus:ring-2 focus:ring-primary text-on-surface"
                  placeholder="Enter phone number"
                />
              ) : (
                <p className="text-on-surface-variant">
                  {selectedClient?.phone || "No phone number provided"}
                </p>
              )}
            </div>

            <div>
              <label className="block text-sm font-bold text-on-surface mb-2">
                Cellphone
              </label>
              {isEditing ? (
                <input
                  type="tel"
                  value={formData.cellphone}
                  onChange={(e) => setFormData({ ...formData, cellphone: e.target.value })}
                  className="w-full p-3 bg-surface-container-low rounded-xl border border-outline-variant focus:outline-none focus:ring-2 focus:ring-primary text-on-surface"
                  placeholder="Enter cellphone number"
                />
              ) : (
                <p className="text-on-surface-variant">
                  {selectedClient?.cellphone || "No cellphone number provided"}
                </p>
              )}
            </div>

            <div>
              <label className="block text-sm font-bold text-on-surface mb-2">
                Client Since
              </label>
              <p className="text-on-surface-variant">{createdDate}</p>
            </div>
          </div>
        </div>

        {/* Back to List Button */}
        <button
          onClick={() => navigate("clients", "", "", "")}
          className="flex items-center gap-2 px-6 py-3 bg-surface-container-low hover:bg-surface-container-high rounded-xl transition-all text-on-surface-variant hover:text-on-surface font-medium"
        >
          <span className="material-symbols-outlined text-sm">arrow_back</span>
          Back to Clients List
        </button>
      </div>
    </section>
  );
}
