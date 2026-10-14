import React, { useState, useEffect, useMemo } from "react";
import { Link, router, usePage } from "@inertiajs/react";
import axios from "axios";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  Building2,
  Briefcase,
  Users,
  FolderKanban,
  CheckSquare,
  Plus,
  Search,
  Grid,
  List,
  Copy,
  Check,
  ShieldCheck,
  Crown,
  MoreVertical,
  Edit2,
  Trash2,
  LogOut,
  UserPlus,
  Clock,
  ArrowRight,
  ChevronRight,
  AlertCircle,
  Loader2,
  Sparkles,
  UserCheck,
  UserX,
  Activity,
  Mail,
  Globe,
  X,
  KeyRound,
  ExternalLink,
  SlidersHorizontal,
  Send,
  Info,
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Progress } from "@/Components/ui/progress";

// Palette generator for company avatars
const AVATAR_PALETTES = [
  { bg: "from-emerald-500/20 to-teal-600/20", border: "border-emerald-500/30", text: "text-emerald-400" },
  { bg: "from-blue-500/20 to-indigo-600/20", border: "border-blue-500/30", text: "text-blue-400" },
  { bg: "from-purple-500/20 to-violet-600/20", border: "border-purple-500/30", text: "text-purple-400" },
  { bg: "from-amber-500/20 to-orange-600/20", border: "border-amber-500/30", text: "text-amber-400" },
  { bg: "from-rose-500/20 to-pink-600/20", border: "border-rose-500/30", text: "text-rose-400" },
  { bg: "from-cyan-500/20 to-sky-600/20", border: "border-cyan-500/30", text: "text-cyan-400" },
];

function getCompanyPalette(name = "") {
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  const index = Math.abs(hash) % AVATAR_PALETTES.length;
  return AVATAR_PALETTES[index];
}

export default function Companies({
  companies = [],
  stats = null,
  selected_company = null,
  current_workspace_name = "All Workspaces",
  current_company_id = null,
}) {
  const { flash = {} } = usePage().props;

  // Search & Filters State
  const [searchQuery, setSearchQuery] = useState("");
  const [roleFilter, setRoleFilter] = useState("all"); // 'all' | 'admin' | 'member' | 'pending'
  const [viewMode, setViewMode] = useState("grid"); // 'grid' | 'table'
  const [activeDropdown, setActiveDropdown] = useState(null);

  // Modals state
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [showJoinModal, setShowJoinModal] = useState(false);
  const [editingCompany, setEditingCompany] = useState(null);
  const [deletingCompany, setDeletingCompany] = useState(null);
  const [leavingCompany, setLeavingCompany] = useState(null);
  const [invitingCompany, setInvitingCompany] = useState(null);
  const [inspectingCompany, setInspectingCompany] = useState(selected_company);
  const [detailTab, setDetailTab] = useState("members"); // 'members' | 'requests' | 'projects' | 'invite'

  // Member Activity Modal
  const [activityModalUser, setActivityModalUser] = useState(null);
  const [activityModalData, setActivityModalData] = useState(null);
  const [loadingActivity, setLoadingActivity] = useState(false);

  // Form states
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formErrors, setFormErrors] = useState({});
  const [createForm, setCreateForm] = useState({ name: "" });
  const [joinForm, setJoinForm] = useState({ code: "" });
  const [editForm, setEditForm] = useState({ name: "" });
  const [inviteForm, setInviteForm] = useState({ email: "", message: "" });

  // Clipboard copy state
  const [copiedCode, setCopiedCode] = useState(null);

  // Close card menus on outside click
  useEffect(() => {
    const handleOutside = () => setActiveDropdown(null);
    window.addEventListener("click", handleOutside);
    return () => window.removeEventListener("click", handleOutside);
  }, []);

  // Update inspected company if props change
  useEffect(() => {
    if (selected_company) {
      setInspectingCompany(selected_company);
    }
  }, [selected_company]);

  const copyToClipboard = (code, e) => {
    if (e) e.stopPropagation();
    navigator.clipboard.writeText(code);
    setCopiedCode(code);
    setTimeout(() => setCopiedCode(null), 2000);
  };

  // Filtered companies list
  const filteredCompanies = useMemo(() => {
    return companies.filter((c) => {
      const q = searchQuery.toLowerCase().trim();
      const matchesSearch =
        !q ||
        c.name.toLowerCase().includes(q) ||
        (c.code && c.code.toLowerCase().includes(q));

      let matchesRole = true;
      if (roleFilter === "admin") {
        matchesRole = c.role_id === 1 && c.is_approved;
      } else if (roleFilter === "member") {
        matchesRole = c.role_id === 0 && c.is_approved;
      } else if (roleFilter === "pending") {
        matchesRole = !c.is_approved;
      }

      return matchesSearch && matchesRole;
    });
  }, [companies, searchQuery, roleFilter]);

  // Handlers
  const handleCreateSubmit = (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setFormErrors({});

    router.post("/v2/companies", createForm, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setShowCreateModal(false);
        setCreateForm({ name: "" });
      },
      onError: (errs) => {
        setIsSubmitting(false);
        setFormErrors(errs);
      },
    });
  };

  const handleJoinSubmit = (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setFormErrors({});

    router.post("/v2/companies/join", joinForm, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setShowJoinModal(false);
        setJoinForm({ code: "" });
      },
      onError: (errs) => {
        setIsSubmitting(false);
        setFormErrors(errs);
      },
    });
  };

  const handleEditSubmit = (e) => {
    e.preventDefault();
    if (!editingCompany) return;
    setIsSubmitting(true);
    setFormErrors({});

    router.patch(`/v2/companies/${editingCompany.id}`, editForm, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setEditingCompany(null);
        if (inspectingCompany?.id === editingCompany.id) {
          setInspectingCompany({ ...inspectingCompany, name: editForm.name });
        }
      },
      onError: (errs) => {
        setIsSubmitting(false);
        setFormErrors(errs);
      },
    });
  };

  const handleDeleteSubmit = () => {
    if (!deletingCompany) return;
    setIsSubmitting(true);

    router.delete(`/v2/companies/${deletingCompany.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        if (inspectingCompany?.id === deletingCompany.id) {
          setInspectingCompany(null);
        }
        setDeletingCompany(null);
      },
      onError: () => setIsSubmitting(false),
    });
  };

  const handleLeaveSubmit = () => {
    if (!leavingCompany) return;
    setIsSubmitting(true);

    router.post(`/v2/companies/${leavingCompany.id}/leave`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        if (inspectingCompany?.id === leavingCompany.id) {
          setInspectingCompany(null);
        }
        setLeavingCompany(null);
      },
      onError: () => setIsSubmitting(false),
    });
  };

  const handleInviteSubmit = (e) => {
    e.preventDefault();
    const targetComp = invitingCompany || inspectingCompany;
    if (!targetComp) return;
    setIsSubmitting(true);
    setFormErrors({});

    router.post(`/v2/companies/${targetComp.id}/invite`, inviteForm, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setInvitingCompany(null);
        setInviteForm({ email: "", message: "" });
      },
      onError: (errs) => {
        setIsSubmitting(false);
        setFormErrors(errs);
      },
    });
  };

  const handleSwitchWorkspace = (comp) => {
    router.get(`/v2/companies/${comp.id}/switch`, {}, { preserveScroll: true });
  };

  const handleSwitchToPersonal = () => {
    router.get("/v2/companies/personal/switch", {}, { preserveScroll: true });
  };

  const handleApproveRequest = (comp, user) => {
    router.post(`/v2/companies/${comp.id}/approve/${user.id}`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        if (inspectingCompany) {
          setInspectingCompany({
            ...inspectingCompany,
            pending_requests: inspectingCompany.pending_requests.filter((r) => r.id !== user.id),
          });
        }
      },
    });
  };

  const handleRejectRequest = (comp, user) => {
    router.post(`/v2/companies/${comp.id}/reject-request/${user.id}`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        if (inspectingCompany) {
          setInspectingCompany({
            ...inspectingCompany,
            pending_requests: inspectingCompany.pending_requests.filter((r) => r.id !== user.id),
          });
        }
      },
    });
  };

  const handleRemoveMember = (comp, user) => {
    if (!confirm(`Are you sure you want to remove ${user.name} from this organization?`)) return;
    router.delete(`/v2/companies/${comp.id}/members/${user.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        if (inspectingCompany) {
          setInspectingCompany({
            ...inspectingCompany,
            members: inspectingCompany.members.filter((m) => m.id !== user.id),
          });
        }
      },
    });
  };

  const fetchMemberActivity = async (comp, user) => {
    setActivityModalUser(user);
    setActivityModalData(null);
    setLoadingActivity(true);
    try {
      const res = await axios.get(`/v2/companies/${comp.id}/members/${user.id}/activity`);
      setActivityModalData(res.data);
    } catch (e) {
      console.error("Failed to fetch member activity", e);
    } finally {
      setLoadingActivity(false);
    }
  };

  // Inspect detail drawer opener
  const openInspector = (comp) => {
    router.get(`/v2/companies?company=${comp.id}`, {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  return (
    <DashboardLayout title="Organizations & Workspaces" activeItem="companies">
      <div className="space-y-6 pb-12">
        {/* Flash Notifications */}
        {flash.success && (
          <div className="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center gap-3 animate-in fade-in duration-200">
            <Check className="w-5 h-5 shrink-0" />
            <p className="text-sm font-medium">{flash.success}</p>
          </div>
        )}
        {flash.error && (
          <div className="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center gap-3 animate-in fade-in duration-200">
            <AlertCircle className="w-5 h-5 shrink-0" />
            <p className="text-sm font-medium">{flash.error}</p>
          </div>
        )}

        {/* Top Header & Actions */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-zinc-800">
          <div>
            <div className="flex items-center gap-2.5">
              <div className="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                <Briefcase className="w-5 h-5" />
              </div>
              <h1 className="text-2xl font-bold tracking-tight text-zinc-100">
                Organizations & Workspaces
              </h1>
              <Badge variant="outline" className="border-emerald-500/30 text-emerald-400 bg-emerald-500/5 font-mono text-xs">
                V2 Multi-Tenant
              </Badge>
            </div>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1">
              Manage your company boundaries, join codes, team delegations, and workspace switches.
            </p>
          </div>

          <div className="flex flex-wrap items-center gap-2.5">
            {/* Quick Personal Space button */}
            <Button
              variant="outline"
              size="sm"
              onClick={handleSwitchToPersonal}
              className={`border-zinc-800 text-xs gap-1.5 transition-colors ${
                current_company_id === "personal"
                  ? "bg-zinc-800 text-emerald-400 border-emerald-500/40"
                  : "bg-zinc-900/60 text-zinc-300 hover:bg-zinc-800"
              }`}
            >
              <Globe className="w-3.5 h-3.5" />
              <span>Personal Space</span>
              {current_company_id === "personal" && (
                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
              )}
            </Button>

            {/* Join with Code */}
            <Button
              variant="outline"
              size="sm"
              onClick={() => {
                setJoinForm({ code: "" });
                setFormErrors({});
                setShowJoinModal(true);
              }}
              className="border-zinc-800 bg-zinc-900/60 hover:bg-zinc-800 text-zinc-200 text-xs gap-1.5"
            >
              <KeyRound className="w-3.5 h-3.5 text-amber-400" />
              <span>Join with Code</span>
            </Button>

            {/* Create Organization */}
            <Button
              size="sm"
              onClick={() => {
                setCreateForm({ name: "" });
                setFormErrors({});
                setShowCreateModal(true);
              }}
              className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold text-xs gap-1.5 shadow-sm shadow-emerald-500/20"
            >
              <Plus className="w-4 h-4" />
              <span>New Workspace</span>
            </Button>
          </div>
        </div>

        {/* Hero KPI Metrics */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3.5">
          {/* Total Workspaces */}
          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm relative overflow-hidden group hover:border-zinc-700 transition-all">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Workspaces
              </span>
              <div className="p-1.5 rounded-lg bg-zinc-800 text-zinc-300">
                <Building2 className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.total_organizations ?? companies.length}
              </div>
              <p className="text-xs text-zinc-400 mt-1 flex items-center gap-1">
                <span>Active:</span>
                <span className="text-emerald-400 font-semibold truncate">
                  {stats?.active_workspace_name || current_workspace_name}
                </span>
              </p>
            </CardContent>
          </Card>

          {/* Admin Managed */}
          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm relative overflow-hidden group hover:border-zinc-700 transition-all">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Managed by You
              </span>
              <div className="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400">
                <Crown className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.admin_organizations_count ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Full administrator authority
              </p>
            </CardContent>
          </Card>

          {/* Pending Requests */}
          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm relative overflow-hidden group hover:border-zinc-700 transition-all">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Join Requests
              </span>
              <div className={`p-1.5 rounded-lg ${(stats?.pending_requests_total ?? 0) > 0 ? 'bg-amber-500/20 text-amber-400 animate-pulse' : 'bg-zinc-800 text-zinc-400'}`}>
                <Clock className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100 flex items-center gap-2">
                {stats?.pending_requests_total ?? 0}
                {(stats?.pending_requests_total ?? 0) > 0 && (
                  <Badge variant="outline" className="border-amber-500/40 text-amber-400 bg-amber-500/10 text-xs font-mono">
                    Needs Action
                  </Badge>
                )}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Awaiting your approval
              </p>
            </CardContent>
          </Card>

          {/* Teammates Network */}
          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm relative overflow-hidden group hover:border-zinc-700 transition-all">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Teammates
              </span>
              <div className="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                <Users className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.total_teammates_count ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Across {stats?.total_organizations ?? companies.length} workspaces
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Search & Filter Toolbar */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-2xl bg-zinc-900/60 border border-zinc-800/80">
          <div className="relative flex-1 max-w-md">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" />
            <input
              type="text"
              placeholder="Search by workspace name or 4-digit code..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full bg-zinc-950 border border-zinc-800 rounded-xl pl-9 pr-3.5 py-2 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 transition-colors"
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery("")}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300"
              >
                <X className="w-3.5 h-3.5" />
              </button>
            )}
          </div>

          <div className="flex items-center gap-2 overflow-x-auto">
            {/* Filter Pills */}
            <div className="flex items-center gap-1 p-1 bg-zinc-950 border border-zinc-800 rounded-xl shrink-0">
              {[
                { id: "all", label: "All" },
                { id: "admin", label: "Admin" },
                { id: "member", label: "Member" },
                { id: "pending", label: "Pending" },
              ].map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => setRoleFilter(tab.id)}
                  className={`px-3 py-1 rounded-lg text-xs font-medium transition-all ${
                    roleFilter === tab.id
                      ? "bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-semibold"
                      : "text-zinc-400 hover:text-zinc-200"
                  }`}
                >
                  {tab.label}
                </button>
              ))}
            </div>

            {/* View Mode Toggle */}
            <div className="flex items-center gap-1 p-1 bg-zinc-950 border border-zinc-800 rounded-xl shrink-0">
              <button
                onClick={() => setViewMode("grid")}
                className={`p-1.5 rounded-lg transition-colors ${
                  viewMode === "grid" ? "bg-zinc-800 text-zinc-100" : "text-zinc-400 hover:text-zinc-200"
                }`}
                title="Grid Cards"
              >
                <Grid className="w-3.5 h-3.5" />
              </button>
              <button
                onClick={() => setViewMode("table")}
                className={`p-1.5 rounded-lg transition-colors ${
                  viewMode === "table" ? "bg-zinc-800 text-zinc-100" : "text-zinc-400 hover:text-zinc-200"
                }`}
                title="Table View"
              >
                <List className="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>

        {/* Companies Empty State */}
        {filteredCompanies.length === 0 && (
          <div className="text-center py-16 px-4 border border-dashed border-zinc-800 rounded-2xl bg-zinc-900/30">
            <div className="w-14 h-14 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mx-auto text-zinc-400 mb-4 shadow-inner">
              <Building2 className="w-7 h-7" />
            </div>
            <h3 className="text-lg font-bold text-zinc-200">
              {searchQuery ? "No matching workspaces found" : "No organizations yet"}
            </h3>
            <p className="text-xs sm:text-sm text-zinc-400 max-w-md mx-auto mt-1 mb-6">
              {searchQuery
                ? `No organizations match "${searchQuery}". Try a different keyword or reset filters.`
                : "Create your first collaborative workspace or join an existing organization using a 4-letter code."}
            </p>
            <div className="flex items-center justify-center gap-3">
              {searchQuery ? (
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => {
                    setSearchQuery("");
                    setRoleFilter("all");
                  }}
                  className="border-zinc-800 text-zinc-300"
                >
                  Clear Filters
                </Button>
              ) : (
                <>
                  <Button
                    size="sm"
                    onClick={() => setShowCreateModal(true)}
                    className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold"
                  >
                    <Plus className="w-4 h-4 mr-1.5" /> Create Workspace
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setShowJoinModal(true)}
                    className="border-zinc-800 text-zinc-300"
                  >
                    <KeyRound className="w-4 h-4 mr-1.5 text-amber-400" /> Join Existing
                  </Button>
                </>
              )}
            </div>
          </div>
        )}

        {/* GRID VIEW */}
        {viewMode === "grid" && filteredCompanies.length > 0 && (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {filteredCompanies.map((c) => {
              const palette = getCompanyPalette(c.name);
              const isSelected = inspectingCompany?.id === c.id;

              return (
                <div
                  key={c.id}
                  onClick={() => c.is_approved && openInspector(c)}
                  className={`p-5 rounded-2xl bg-zinc-900/90 border transition-all duration-200 relative group flex flex-col justify-between cursor-pointer ${
                    c.is_active
                      ? "border-emerald-500/50 shadow-lg shadow-emerald-500/5 ring-1 ring-emerald-500/20"
                      : isSelected
                      ? "border-indigo-500/50 shadow-lg shadow-indigo-500/5"
                      : "border-zinc-800/80 hover:border-zinc-700 hover:shadow-xl hover:shadow-black/40"
                  }`}
                >
                  {/* Top card header */}
                  <div>
                    <div className="flex items-start justify-between gap-3 mb-3.5">
                      <div className="flex items-center gap-3 min-w-0">
                        <div
                          className={`w-11 h-11 rounded-xl bg-gradient-to-br ${palette.bg} border ${palette.border} flex items-center justify-center font-bold text-base ${palette.text} shrink-0 shadow-sm`}
                        >
                          {c.name.charAt(0).toUpperCase()}
                        </div>
                        <div className="min-w-0 flex-1">
                          <h3 className="font-bold text-zinc-100 text-base leading-snug truncate group-hover:text-emerald-400 transition-colors">
                            {c.name}
                          </h3>
                          <div className="flex items-center gap-2 mt-1">
                            {c.is_active ? (
                              <Badge className="bg-emerald-500/15 border-emerald-500/30 text-emerald-400 text-[10px] font-semibold px-2 py-0.5">
                                Active Space
                              </Badge>
                            ) : c.is_approved ? (
                              <Badge
                                variant="outline"
                                className={`text-[10px] font-semibold px-2 py-0.5 ${
                                  c.role_id === 1
                                    ? "border-indigo-500/30 text-indigo-400 bg-indigo-500/10"
                                    : "border-zinc-700 text-zinc-400 bg-zinc-800/40"
                                }`}
                              >
                                {c.role}
                              </Badge>
                            ) : (
                              <Badge variant="outline" className="border-amber-500/40 text-amber-400 bg-amber-500/10 text-[10px] font-semibold">
                                Pending Approval
                              </Badge>
                            )}

                            {c.pending_requests_count > 0 && (
                              <Badge className="bg-amber-500/20 border-amber-500/30 text-amber-300 text-[10px] font-mono animate-pulse">
                                {c.pending_requests_count} Req
                              </Badge>
                            )}
                          </div>
                        </div>
                      </div>

                      {/* Card options dropdown */}
                      <div className="relative shrink-0" onClick={(e) => e.stopPropagation()}>
                        <button
                          type="button"
                          onClick={() => setActiveDropdown(activeDropdown === c.id ? null : c.id)}
                          className="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800 transition-colors"
                        >
                          <MoreVertical className="w-4 h-4" />
                        </button>

                        {activeDropdown === c.id && (
                          <div className="absolute right-0 top-full mt-1.5 w-48 bg-zinc-900 border border-zinc-800 rounded-xl shadow-2xl p-1.5 z-30 space-y-1 animate-in fade-in zoom-in-95 duration-150">
                            {c.is_approved && !c.is_active && (
                              <button
                                onClick={() => {
                                  setActiveDropdown(null);
                                  handleSwitchWorkspace(c);
                                }}
                                className="w-full flex items-center gap-2 p-2 rounded-lg text-xs hover:bg-zinc-800 text-emerald-400 font-medium transition-colors"
                              >
                                <ArrowRight className="w-3.5 h-3.5" />
                                <span>Switch to Workspace</span>
                              </button>
                            )}

                            {c.is_approved && (
                              <button
                                onClick={() => {
                                  setActiveDropdown(null);
                                  openInspector(c);
                                }}
                                className="w-full flex items-center gap-2 p-2 rounded-lg text-xs hover:bg-zinc-800 text-zinc-200 transition-colors"
                              >
                                <SlidersHorizontal className="w-3.5 h-3.5 text-zinc-400" />
                                <span>Manage Workspace</span>
                              </button>
                            )}

                            {c.can_manage && (
                              <>
                                <button
                                  onClick={() => {
                                    setActiveDropdown(null);
                                    setInvitingCompany(c);
                                    setInviteForm({ email: "", message: "" });
                                  }}
                                  className="w-full flex items-center gap-2 p-2 rounded-lg text-xs hover:bg-zinc-800 text-zinc-200 transition-colors"
                                >
                                  <UserPlus className="w-3.5 h-3.5 text-zinc-400" />
                                  <span>Invite Member</span>
                                </button>
                                <button
                                  onClick={() => {
                                    setActiveDropdown(null);
                                    setEditingCompany(c);
                                    setEditForm({ name: c.name });
                                  }}
                                  className="w-full flex items-center gap-2 p-2 rounded-lg text-xs hover:bg-zinc-800 text-zinc-200 transition-colors"
                                >
                                  <Edit2 className="w-3.5 h-3.5 text-zinc-400" />
                                  <span>Rename Workspace</span>
                                </button>
                                <div className="h-px bg-zinc-800/80 my-1" />
                                <button
                                  onClick={() => {
                                    setActiveDropdown(null);
                                    setDeletingCompany(c);
                                  }}
                                  className="w-full flex items-center gap-2 p-2 rounded-lg text-xs hover:bg-rose-500/10 text-rose-400 transition-colors"
                                >
                                  <Trash2 className="w-3.5 h-3.5" />
                                  <span>Delete Workspace</span>
                                </button>
                              </>
                            )}

                            {c.can_leave && (
                              <button
                                onClick={() => {
                                  setActiveDropdown(null);
                                  setLeavingCompany(c);
                                }}
                                className="w-full flex items-center gap-2 p-2 rounded-lg text-xs hover:bg-rose-500/10 text-rose-400 transition-colors"
                              >
                                <LogOut className="w-3.5 h-3.5" />
                                <span>Leave Workspace</span>
                              </button>
                            )}
                          </div>
                        )}
                      </div>
                    </div>

                    {/* Join Code badge with 1-click copy */}
                    <div className="mb-4">
                      <div className="flex items-center justify-between p-2 rounded-xl bg-zinc-950/80 border border-zinc-800/90 text-xs">
                        <div className="flex items-center gap-2">
                          <span className="font-mono text-zinc-500 text-[11px] uppercase tracking-wider">
                            Code:
                          </span>
                          <span className="font-mono font-bold text-zinc-200 tracking-wider">
                            {c.code}
                          </span>
                        </div>
                        <button
                          type="button"
                          onClick={(e) => copyToClipboard(c.code, e)}
                          className="flex items-center gap-1 px-2 py-0.5 rounded-md hover:bg-zinc-800 text-zinc-400 hover:text-zinc-100 transition-colors text-[11px] font-mono"
                        >
                          {copiedCode === c.code ? (
                            <>
                              <Check className="w-3 h-3 text-emerald-400" />
                              <span className="text-emerald-400">Copied</span>
                            </>
                          ) : (
                            <>
                              <Copy className="w-3 h-3" />
                              <span>Copy</span>
                            </>
                          )}
                        </button>
                      </div>
                    </div>

                    {/* Metrics grid */}
                    <div className="grid grid-cols-3 gap-2 py-2 border-y border-zinc-800/60 mb-4 text-center">
                      <div className="p-1.5 rounded-lg bg-zinc-950/40">
                        <span className="text-[11px] text-zinc-500 block">Members</span>
                        <span className="font-bold text-zinc-200 text-sm">{c.members_count}</span>
                      </div>
                      <div className="p-1.5 rounded-lg bg-zinc-950/40">
                        <span className="text-[11px] text-zinc-500 block">Projects</span>
                        <span className="font-bold text-zinc-200 text-sm">{c.projects_count}</span>
                      </div>
                      <div className="p-1.5 rounded-lg bg-zinc-950/40">
                        <span className="text-[11px] text-zinc-500 block">Tasks</span>
                        <span className="font-bold text-zinc-200 text-sm">{c.tasks_count}</span>
                      </div>
                    </div>
                  </div>

                  {/* Bottom footer: Team avatars + Action button */}
                  <div className="pt-2 flex items-center justify-between gap-3">
                    {/* Avatars stack */}
                    <div className="flex items-center -space-x-2 overflow-hidden">
                      {c.members_preview && c.members_preview.length > 0 ? (
                        c.members_preview.map((m) => (
                          <Avatar key={m.id} className="w-6 h-6 border-2 border-zinc-900 ring-1 ring-zinc-800">
                            {m.profile_image ? (
                              <AvatarImage src={m.profile_image} alt={m.name} />
                            ) : null}
                            <AvatarFallback className="text-[9px] bg-zinc-800 text-zinc-300 font-bold">
                              {m.avatar}
                            </AvatarFallback>
                          </Avatar>
                        ))
                      ) : (
                        <span className="text-[11px] text-zinc-500 italic">No members yet</span>
                      )}
                      {c.members_count > (c.members_preview?.length || 0) && (
                        <div className="w-6 h-6 rounded-full bg-zinc-800 border-2 border-zinc-900 flex items-center justify-center text-[9px] text-zinc-400 font-mono font-bold">
                          +{c.members_count - (c.members_preview?.length || 0)}
                        </div>
                      )}
                    </div>

                    {/* Quick switch button */}
                    {c.is_approved ? (
                      c.is_active ? (
                        <span className="text-xs font-mono font-medium text-emerald-400 flex items-center gap-1">
                          <Check className="w-3.5 h-3.5" /> Current
                        </span>
                      ) : (
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={(e) => {
                            e.stopPropagation();
                            handleSwitchWorkspace(c);
                          }}
                          className="h-7 px-2.5 text-xs text-zinc-300 hover:text-emerald-400 hover:bg-zinc-800 gap-1"
                        >
                          <span>Switch</span>
                          <ArrowRight className="w-3 h-3" />
                        </Button>
                      )
                    ) : (
                      <span className="text-xs text-amber-400/80 font-mono">Awaiting Review</span>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}

        {/* TABLE VIEW */}
        {viewMode === "table" && filteredCompanies.length > 0 && (
          <div className="bg-zinc-900/80 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs sm:text-sm">
                <thead className="bg-zinc-950/70 border-b border-zinc-800 text-zinc-400 font-mono text-[11px] uppercase tracking-wider">
                  <tr>
                    <th className="py-3 px-4">Workspace</th>
                    <th className="py-3 px-4">Join Code</th>
                    <th className="py-3 px-4">Your Role</th>
                    <th className="py-3 px-4">Members</th>
                    <th className="py-3 px-4">Projects</th>
                    <th className="py-3 px-4">Status</th>
                    <th className="py-3 px-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-zinc-800/60">
                  {filteredCompanies.map((c) => {
                    const palette = getCompanyPalette(c.name);

                    return (
                      <tr
                        key={c.id}
                        onClick={() => c.is_approved && openInspector(c)}
                        className={`hover:bg-zinc-800/40 transition-colors cursor-pointer ${
                          c.is_active ? "bg-emerald-500/5" : ""
                        }`}
                      >
                        <td className="py-3.5 px-4">
                          <div className="flex items-center gap-3">
                            <div
                              className={`w-8 h-8 rounded-lg bg-gradient-to-br ${palette.bg} border ${palette.border} flex items-center justify-center font-bold text-xs ${palette.text}`}
                            >
                              {c.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                              <div className="font-bold text-zinc-100 flex items-center gap-1.5">
                                {c.name}
                                {c.is_active && (
                                  <span className="w-1.5 h-1.5 rounded-full bg-emerald-400" />
                                )}
                              </div>
                              <div className="text-[11px] text-zinc-500 font-mono">
                                Created {c.created_at}
                              </div>
                            </div>
                          </div>
                        </td>

                        <td className="py-3.5 px-4 font-mono">
                          <button
                            type="button"
                            onClick={(e) => copyToClipboard(c.code, e)}
                            className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-zinc-950 border border-zinc-800 text-zinc-300 hover:text-emerald-400 transition-colors text-xs"
                          >
                            <span>{c.code}</span>
                            {copiedCode === c.code ? (
                              <Check className="w-3 h-3 text-emerald-400" />
                            ) : (
                              <Copy className="w-3 h-3 text-zinc-500" />
                            )}
                          </button>
                        </td>

                        <td className="py-3.5 px-4">
                          <Badge
                            variant="outline"
                            className={`text-[10px] font-semibold ${
                              c.role_id === 1
                                ? "border-indigo-500/30 text-indigo-400 bg-indigo-500/10"
                                : "border-zinc-700 text-zinc-400 bg-zinc-800/30"
                            }`}
                          >
                            {c.role}
                          </Badge>
                        </td>

                        <td className="py-3.5 px-4 font-mono text-zinc-300">
                          {c.members_count}
                        </td>

                        <td className="py-3.5 px-4 font-mono text-zinc-300">
                          {c.projects_count}
                        </td>

                        <td className="py-3.5 px-4">
                          {c.is_active ? (
                            <span className="text-emerald-400 font-semibold text-xs flex items-center gap-1">
                              <Check className="w-3.5 h-3.5" /> Active
                            </span>
                          ) : c.is_approved ? (
                            <span className="text-zinc-400 text-xs">Approved</span>
                          ) : (
                            <span className="text-amber-400 text-xs font-mono">Pending</span>
                          )}
                        </td>

                        <td className="py-3.5 px-4 text-right" onClick={(e) => e.stopPropagation()}>
                          <div className="flex items-center justify-end gap-2">
                            {c.is_approved && !c.is_active && (
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => handleSwitchWorkspace(c)}
                                className="h-7 text-xs border-zinc-800 hover:bg-zinc-800 text-zinc-200"
                              >
                                Switch
                              </Button>
                            )}

                            {c.is_approved && (
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => openInspector(c)}
                                className="h-7 text-xs text-zinc-400 hover:text-zinc-100"
                              >
                                Details
                              </Button>
                            )}
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* WORKSPACE INSPECTION DRAWER / FULL DETAILS PANEL */}
        {inspectingCompany && (
          <div className="mt-8 border border-zinc-800 bg-zinc-900/90 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in duration-200">
            {/* Header of Workspace Details */}
            <div className="p-6 border-b border-zinc-800 bg-zinc-950/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div className="flex items-start gap-4">
                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border border-emerald-500/30 flex items-center justify-center font-bold text-xl text-emerald-400 shrink-0">
                  {inspectingCompany.name.charAt(0).toUpperCase()}
                </div>
                <div>
                  <div className="flex items-center gap-2.5">
                    <h2 className="text-xl font-bold text-zinc-100">{inspectingCompany.name}</h2>
                    {current_company_id === inspectingCompany.id ? (
                      <Badge className="bg-emerald-500/15 border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                        Current Active Space
                      </Badge>
                    ) : (
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleSwitchWorkspace(inspectingCompany)}
                        className="h-6 text-[11px] border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10"
                      >
                        Set as Active
                      </Button>
                    )}
                  </div>
                  <div className="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-zinc-400 font-mono">
                    <span className="flex items-center gap-1">
                      <KeyRound className="w-3.5 h-3.5 text-amber-400" />
                      Code: <strong className="text-zinc-200">{inspectingCompany.code}</strong>
                    </span>
                    <span>•</span>
                    <span>Created {inspectingCompany.created_at}</span>
                    <span>•</span>
                    <span>{inspectingCompany.members?.length || 0} Members</span>
                  </div>
                </div>
              </div>

              {/* Action buttons inside detail inspector */}
              <div className="flex items-center gap-2">
                {inspectingCompany.is_admin && (
                  <Button
                    size="sm"
                    onClick={() => {
                      setInvitingCompany(inspectingCompany);
                      setInviteForm({ email: "", message: "" });
                    }}
                    className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold text-xs gap-1.5"
                  >
                    <UserPlus className="w-3.5 h-3.5" />
                    <span>Invite Member</span>
                  </Button>
                )}
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() => setInspectingCompany(null)}
                  className="border-zinc-800 text-zinc-400 hover:text-zinc-100"
                >
                  Close View
                </Button>
              </div>
            </div>

            {/* Navigation Tabs for detailed inspection */}
            <div className="flex items-center gap-1 px-6 pt-3 border-b border-zinc-800 bg-zinc-950/40 text-xs font-medium overflow-x-auto">
              <button
                onClick={() => setDetailTab("members")}
                className={`pb-3 px-3 border-b-2 transition-all flex items-center gap-2 ${
                  detailTab === "members"
                    ? "border-emerald-400 text-emerald-400 font-bold"
                    : "border-transparent text-zinc-400 hover:text-zinc-200"
                }`}
              >
                <Users className="w-3.5 h-3.5" />
                <span>Team Members ({inspectingCompany.members?.length || 0})</span>
              </button>

              {inspectingCompany.is_admin && (
                <button
                  onClick={() => setDetailTab("requests")}
                  className={`pb-3 px-3 border-b-2 transition-all flex items-center gap-2 ${
                    detailTab === "requests"
                      ? "border-emerald-400 text-emerald-400 font-bold"
                      : "border-transparent text-zinc-400 hover:text-zinc-200"
                  }`}
                >
                  <Clock className="w-3.5 h-3.5 text-amber-400" />
                  <span>
                    Pending Requests ({inspectingCompany.pending_requests?.length || 0})
                  </span>
                  {(inspectingCompany.pending_requests?.length || 0) > 0 && (
                    <span className="w-2 h-2 rounded-full bg-amber-400 animate-pulse" />
                  )}
                </button>
              )}

              <button
                onClick={() => setDetailTab("projects")}
                className={`pb-3 px-3 border-b-2 transition-all flex items-center gap-2 ${
                  detailTab === "projects"
                    ? "border-emerald-400 text-emerald-400 font-bold"
                    : "border-transparent text-zinc-400 hover:text-zinc-200"
                }`}
              >
                <FolderKanban className="w-3.5 h-3.5" />
                <span>Projects ({inspectingCompany.projects?.length || 0})</span>
              </button>
            </div>

            {/* TAB CONTENT: Members */}
            {detailTab === "members" && (
              <div className="p-6">
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs sm:text-sm">
                    <thead className="text-zinc-500 font-mono text-[11px] uppercase border-b border-zinc-800">
                      <tr>
                        <th className="pb-3">Teammate</th>
                        <th className="pb-3">Role</th>
                        <th className="pb-3">Tasks</th>
                        <th className="pb-3">Joined</th>
                        <th className="pb-3">Last Active</th>
                        <th className="pb-3 text-right">Actions</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-zinc-800/50">
                      {inspectingCompany.members && inspectingCompany.members.length > 0 ? (
                        inspectingCompany.members.map((m) => (
                          <tr key={m.id} className="hover:bg-zinc-800/30 transition-colors">
                            <td className="py-3.5">
                              <div className="flex items-center gap-3">
                                <Avatar className="w-8 h-8 border border-zinc-800">
                                  {m.profile_image ? (
                                    <AvatarImage src={m.profile_image} alt={m.name} />
                                  ) : null}
                                  <AvatarFallback className="bg-zinc-800 text-zinc-300 font-bold text-xs">
                                    {m.avatar}
                                  </AvatarFallback>
                                </Avatar>
                                <div>
                                  <div className="font-semibold text-zinc-100">{m.name}</div>
                                  <div className="text-[11px] text-zinc-400 font-mono">{m.email}</div>
                                </div>
                              </div>
                            </td>

                            <td className="py-3.5">
                              <Badge
                                variant="outline"
                                className={`text-[10px] font-semibold ${
                                  m.role === "Admin"
                                    ? "border-indigo-500/30 text-indigo-400 bg-indigo-500/10"
                                    : "border-zinc-700 text-zinc-400 bg-zinc-800/40"
                                }`}
                              >
                                {m.role}
                              </Badge>
                            </td>

                            <td className="py-3.5">
                              <div className="font-mono text-xs text-zinc-300">
                                <span className="text-emerald-400 font-bold">{m.completed_tasks_count}</span>
                                <span className="text-zinc-500"> / {m.total_tasks_count} done</span>
                              </div>
                            </td>

                            <td className="py-3.5 text-zinc-400 text-xs font-mono">
                              {m.joined_at}
                            </td>

                            <td className="py-3.5 text-zinc-400 text-xs font-mono">
                              {m.last_login}
                            </td>

                            <td className="py-3.5 text-right">
                              <div className="flex items-center justify-end gap-2">
                                <Button
                                  size="sm"
                                  variant="ghost"
                                  onClick={() => fetchMemberActivity(inspectingCompany, m)}
                                  className="h-7 text-xs text-zinc-400 hover:text-zinc-100 gap-1"
                                >
                                  <Activity className="w-3.5 h-3.5" />
                                  <span>Activity</span>
                                </Button>

                                {m.can_remove && (
                                  <Button
                                    size="sm"
                                    variant="ghost"
                                    onClick={() => handleRemoveMember(inspectingCompany, m)}
                                    className="h-7 text-xs text-rose-400 hover:bg-rose-500/10"
                                  >
                                    Remove
                                  </Button>
                                )}
                              </div>
                            </td>
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan={6} className="text-center py-8 text-zinc-500">
                            No active members found.
                          </td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {/* TAB CONTENT: Join Requests */}
            {detailTab === "requests" && inspectingCompany.is_admin && (
              <div className="p-6">
                {inspectingCompany.pending_requests && inspectingCompany.pending_requests.length > 0 ? (
                  <div className="space-y-3">
                    {inspectingCompany.pending_requests.map((req) => (
                      <div
                        key={req.id}
                        className="p-4 rounded-xl bg-zinc-950 border border-zinc-800/90 flex items-center justify-between gap-4"
                      >
                        <div className="flex items-center gap-3">
                          <Avatar className="w-10 h-10 border border-zinc-800">
                            {req.profile_image ? (
                              <AvatarImage src={req.profile_image} alt={req.name} />
                            ) : null}
                            <AvatarFallback className="bg-zinc-800 text-amber-400 font-bold text-xs">
                              {req.avatar}
                            </AvatarFallback>
                          </Avatar>
                          <div>
                            <div className="font-bold text-zinc-100">{req.name}</div>
                            <div className="text-xs text-zinc-400 font-mono">{req.email}</div>
                            <div className="text-[11px] text-zinc-500 font-mono mt-0.5">
                              Requested {req.requested_at}
                            </div>
                          </div>
                        </div>

                        <div className="flex items-center gap-2">
                          <Button
                            size="sm"
                            onClick={() => handleApproveRequest(inspectingCompany, req)}
                            className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold text-xs gap-1 h-8"
                          >
                            <UserCheck className="w-3.5 h-3.5" />
                            <span>Approve</span>
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => handleRejectRequest(inspectingCompany, req)}
                            className="border-zinc-800 text-rose-400 hover:bg-rose-500/10 text-xs gap-1 h-8"
                          >
                            <UserX className="w-3.5 h-3.5" />
                            <span>Reject</span>
                          </Button>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-10 text-zinc-500">
                    <UserCheck className="w-8 h-8 mx-auto mb-2 opacity-50" />
                    <p className="font-semibold text-zinc-300">All caught up!</p>
                    <p className="text-xs text-zinc-500 mt-0.5">There are no pending join requests for this workspace.</p>
                  </div>
                )}
              </div>
            )}

            {/* TAB CONTENT: Projects */}
            {detailTab === "projects" && (
              <div className="p-6">
                {inspectingCompany.projects && inspectingCompany.projects.length > 0 ? (
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    {inspectingCompany.projects.map((proj) => (
                      <div
                        key={proj.id}
                        className="p-4 rounded-xl bg-zinc-950 border border-zinc-800/90 flex flex-col justify-between gap-3"
                      >
                        <div className="flex items-start justify-between gap-2">
                          <div className="flex items-center gap-2.5">
                            <span
                              className="w-3 h-3 rounded-full shrink-0"
                              style={{ backgroundColor: proj.theme }}
                            />
                            <h4 className="font-bold text-zinc-100 text-sm">{proj.name}</h4>
                          </div>
                          <Badge variant="outline" className="text-[10px] border-zinc-800 text-zinc-400">
                            {proj.status}
                          </Badge>
                        </div>

                        <div>
                          <div className="flex items-center justify-between text-xs text-zinc-400 font-mono mb-1.5">
                            <span>Progress</span>
                            <span>{proj.progress}%</span>
                          </div>
                          <Progress value={proj.progress} className="h-1.5 bg-zinc-800" />
                        </div>

                        <div className="flex items-center justify-between text-[11px] text-zinc-500 font-mono pt-1">
                          <span>{proj.completed_tasks} / {proj.total_tasks} Tasks Done</span>
                          <Link
                            href="/v2/projects"
                            className="text-emerald-400 hover:underline flex items-center gap-1"
                          >
                            View <ChevronRight className="w-3 h-3" />
                          </Link>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-10 text-zinc-500">
                    <FolderKanban className="w-8 h-8 mx-auto mb-2 opacity-50" />
                    <p className="font-semibold text-zinc-300">No projects created yet</p>
                    <p className="text-xs text-zinc-500 mt-0.5">Projects created under this company will appear here.</p>
                    <Button asChild size="sm" className="mt-4 bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold text-xs">
                      <Link href="/v2/projects">Go to Projects</Link>
                    </Button>
                  </div>
                )}
              </div>
            )}
          </div>
        )}

        {/* MODAL: CREATE WORKSPACE */}
        {showCreateModal && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex items-center justify-between p-5 border-b border-zinc-800 bg-zinc-950/60">
                <div className="flex items-center gap-2.5">
                  <div className="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    <Building2 className="w-4 h-4" />
                  </div>
                  <div>
                    <h3 className="font-bold text-zinc-100 text-sm sm:text-base">Create New Organization</h3>
                    <p className="text-xs text-zinc-400 font-mono">Set up a collaborative team workspace</p>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setShowCreateModal(false)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleCreateSubmit} className="p-5 space-y-4 text-xs sm:text-sm">
                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Organization Name <span className="text-rose-400">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Acme Corp, Engineering Studio, Marketing HQ"
                    value={createForm.name}
                    onChange={(e) => setCreateForm({ ...createForm, name: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 transition-colors"
                  />
                  {formErrors.name && (
                    <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                      <AlertCircle className="w-3 h-3" /> {formErrors.name}
                    </p>
                  )}
                </div>

                <div className="p-3.5 rounded-xl bg-zinc-950/80 border border-zinc-800 text-xs text-zinc-400 space-y-1 font-mono">
                  <div className="flex items-center gap-1.5 text-emerald-400 font-semibold">
                    <Sparkles className="w-3.5 h-3.5" />
                    <span>Auto-Generated Join Code</span>
                  </div>
                  <p className="leading-relaxed">
                    A unique 4-character uppercase code will be automatically assigned for teammates to join.
                  </p>
                </div>

                <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={isSubmitting}
                    onClick={() => setShowCreateModal(false)}
                    className="border-zinc-800 text-zinc-300 hover:bg-zinc-800"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    disabled={isSubmitting || !createForm.name.trim()}
                    className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold px-4"
                  >
                    {isSubmitting ? (
                      <span className="flex items-center gap-2">
                        <Loader2 className="w-3.5 h-3.5 animate-spin" /> Creating...
                      </span>
                    ) : (
                      "Create Workspace"
                    )}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* MODAL: JOIN WORKSPACE */}
        {showJoinModal && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex items-center justify-between p-5 border-b border-zinc-800 bg-zinc-950/60">
                <div className="flex items-center gap-2.5">
                  <div className="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                    <KeyRound className="w-4 h-4" />
                  </div>
                  <div>
                    <h3 className="font-bold text-zinc-100 text-sm sm:text-base">Join an Organization</h3>
                    <p className="text-xs text-zinc-400 font-mono">Enter a 4-letter workspace code</p>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setShowJoinModal(false)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleJoinSubmit} className="p-5 space-y-4 text-xs sm:text-sm">
                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Organization Code <span className="text-rose-400">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    maxLength={10}
                    placeholder="e.g. 7K4X"
                    value={joinForm.code}
                    onChange={(e) => setJoinForm({ ...joinForm, code: e.target.value.toUpperCase() })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-center text-lg font-mono font-bold tracking-widest text-emerald-400 placeholder-zinc-600 focus:outline-none focus:border-emerald-500/60 transition-colors uppercase"
                  />
                  {formErrors.code && (
                    <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                      <AlertCircle className="w-3 h-3" /> {formErrors.code}
                    </p>
                  )}
                </div>

                <p className="text-xs text-zinc-400 font-mono leading-relaxed bg-zinc-950/50 p-3 rounded-xl border border-zinc-800/80">
                  Once submitted, the workspace administrators will review and approve your membership request.
                </p>

                <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={isSubmitting}
                    onClick={() => setShowJoinModal(false)}
                    className="border-zinc-800 text-zinc-300 hover:bg-zinc-800"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    disabled={isSubmitting || !joinForm.code.trim()}
                    className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold px-4"
                  >
                    {isSubmitting ? (
                      <span className="flex items-center gap-2">
                        <Loader2 className="w-3.5 h-3.5 animate-spin" /> Verifying...
                      </span>
                    ) : (
                      "Submit Join Request"
                    )}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* MODAL: EDIT WORKSPACE NAME */}
        {editingCompany && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex items-center justify-between p-5 border-b border-zinc-800 bg-zinc-950/60">
                <div className="flex items-center gap-2.5">
                  <div className="p-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                    <Edit2 className="w-4 h-4" />
                  </div>
                  <div>
                    <h3 className="font-bold text-zinc-100 text-sm sm:text-base">Rename Workspace</h3>
                    <p className="text-xs text-zinc-400 font-mono">Update organization display title</p>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setEditingCompany(null)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleEditSubmit} className="p-5 space-y-4 text-xs sm:text-sm">
                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Organization Name <span className="text-rose-400">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    value={editForm.name}
                    onChange={(e) => setEditForm({ ...editForm, name: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-indigo-500/60 transition-colors"
                  />
                  {formErrors.name && (
                    <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                      <AlertCircle className="w-3 h-3" /> {formErrors.name}
                    </p>
                  )}
                </div>

                <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={isSubmitting}
                    onClick={() => setEditingCompany(null)}
                    className="border-zinc-800 text-zinc-300 hover:bg-zinc-800"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    disabled={isSubmitting || !editForm.name.trim()}
                    className="bg-indigo-500 hover:bg-indigo-600 text-white font-bold px-4"
                  >
                    {isSubmitting ? "Updating..." : "Save Changes"}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* MODAL: INVITE MEMBER */}
        {invitingCompany && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex items-center justify-between p-5 border-b border-zinc-800 bg-zinc-950/60">
                <div className="flex items-center gap-2.5">
                  <div className="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    <UserPlus className="w-4 h-4" />
                  </div>
                  <div>
                    <h3 className="font-bold text-zinc-100 text-sm sm:text-base">
                      Invite to {invitingCompany.name}
                    </h3>
                    <p className="text-xs text-zinc-400 font-mono">Send an email invitation link</p>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setInvitingCompany(null)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleInviteSubmit} className="p-5 space-y-4 text-xs sm:text-sm">
                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Email Address <span className="text-rose-400">*</span>
                  </label>
                  <input
                    type="email"
                    required
                    placeholder="colleague@example.com"
                    value={inviteForm.email}
                    onChange={(e) => setInviteForm({ ...inviteForm, email: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 transition-colors"
                  />
                  {formErrors.email && (
                    <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                      <AlertCircle className="w-3 h-3" /> {formErrors.email}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Custom Message (Optional)
                  </label>
                  <textarea
                    rows={3}
                    placeholder="We'd love to have you collaborate on our projects in WorkHub!"
                    value={inviteForm.message}
                    onChange={(e) => setInviteForm({ ...inviteForm, message: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 transition-colors resize-none"
                  />
                </div>

                <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={isSubmitting}
                    onClick={() => setInvitingCompany(null)}
                    className="border-zinc-800 text-zinc-300 hover:bg-zinc-800"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    disabled={isSubmitting || !inviteForm.email.trim()}
                    className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold px-4 gap-1.5"
                  >
                    {isSubmitting ? (
                      <span className="flex items-center gap-1.5">
                        <Loader2 className="w-3.5 h-3.5 animate-spin" /> Sending...
                      </span>
                    ) : (
                      <>
                        <Send className="w-3.5 h-3.5" />
                        <span>Send Invitation</span>
                      </>
                    )}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* MODAL: MEMBER ACTIVITY */}
        {activityModalUser && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex items-center justify-between p-5 border-b border-zinc-800 bg-zinc-950/60 shrink-0">
                <div className="flex items-center gap-2.5">
                  <div className="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    <Activity className="w-4 h-4" />
                  </div>
                  <div>
                    <h3 className="font-bold text-zinc-100 text-sm sm:text-base">
                      {activityModalUser.name} — Activity Log
                    </h3>
                    <p className="text-xs text-zinc-400 font-mono">
                      Recent events and workspace contributions
                    </p>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setActivityModalUser(null)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <div className="p-5 overflow-y-auto flex-1 space-y-4">
                {loadingActivity ? (
                  <div className="py-12 text-center text-zinc-400 flex flex-col items-center justify-center gap-2">
                    <Loader2 className="w-6 h-6 animate-spin text-emerald-400" />
                    <span className="text-xs font-mono">Retrieving activity stream...</span>
                  </div>
                ) : activityModalData?.activities && activityModalData.activities.length > 0 ? (
                  <div className="space-y-3">
                    {activityModalData.activities.map((act) => (
                      <div
                        key={act.id}
                        className="p-3 rounded-xl bg-zinc-950/70 border border-zinc-800/80 text-xs space-y-1"
                      >
                        <div className="flex items-center justify-between gap-2">
                          <span className="font-semibold text-zinc-200">
                            {act.description}
                          </span>
                          <span className="text-[11px] text-zinc-500 font-mono shrink-0">
                            {act.created_at_human}
                          </span>
                        </div>
                        {act.subject_title && (
                          <div className="text-[11px] text-emerald-400/90 font-mono">
                            Subject: {act.subject_title} ({act.subject_type})
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="py-12 text-center text-zinc-500">
                    <Activity className="w-8 h-8 mx-auto mb-2 opacity-50" />
                    <p className="font-semibold text-zinc-300">No recorded activities yet</p>
                    <p className="text-xs text-zinc-500 mt-0.5">Activities will be tracked as the user creates or updates items.</p>
                  </div>
                )}
              </div>

              <div className="p-4 border-t border-zinc-800 bg-zinc-950/60 flex items-center justify-between text-xs text-zinc-500 font-mono shrink-0">
                <span>Last login: {activityModalData?.user?.last_login_at || "Never"}</span>
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() => setActivityModalUser(null)}
                  className="border-zinc-800 text-zinc-300"
                >
                  Close
                </Button>
              </div>
            </div>
          </div>
        )}

        {/* MODAL: CONFIRM DELETE */}
        {deletingCompany && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mx-auto">
                <Trash2 className="w-6 h-6" />
              </div>
              <div className="text-center space-y-1">
                <h3 className="text-lg font-bold text-zinc-100">Delete Workspace?</h3>
                <p className="text-xs text-zinc-400 leading-relaxed">
                  Are you sure you want to delete <strong className="text-zinc-200">{deletingCompany.name}</strong>? All members and workspace associations will be removed.
                </p>
              </div>
              <div className="flex items-center justify-end gap-2.5 pt-2">
                <Button
                  variant="outline"
                  size="sm"
                  disabled={isSubmitting}
                  onClick={() => setDeletingCompany(null)}
                  className="border-zinc-800 text-zinc-300"
                >
                  Cancel
                </Button>
                <Button
                  size="sm"
                  disabled={isSubmitting}
                  onClick={handleDeleteSubmit}
                  className="bg-rose-500 hover:bg-rose-600 text-white font-bold"
                >
                  {isSubmitting ? "Deleting..." : "Confirm Delete"}
                </Button>
              </div>
            </div>
          </div>
        )}

        {/* MODAL: CONFIRM LEAVE */}
        {leavingCompany && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mx-auto">
                <LogOut className="w-6 h-6" />
              </div>
              <div className="text-center space-y-1">
                <h3 className="text-lg font-bold text-zinc-100">Leave Workspace?</h3>
                <p className="text-xs text-zinc-400 leading-relaxed">
                  Are you sure you want to leave <strong className="text-zinc-200">{leavingCompany.name}</strong>? Any tasks assigned to you in this organization will be unassigned.
                </p>
              </div>
              <div className="flex items-center justify-end gap-2.5 pt-2">
                <Button
                  variant="outline"
                  size="sm"
                  disabled={isSubmitting}
                  onClick={() => setLeavingCompany(null)}
                  className="border-zinc-800 text-zinc-300"
                >
                  Cancel
                </Button>
                <Button
                  size="sm"
                  disabled={isSubmitting}
                  onClick={handleLeaveSubmit}
                  className="bg-rose-500 hover:bg-rose-600 text-white font-bold"
                >
                  {isSubmitting ? "Leaving..." : "Leave Workspace"}
                </Button>
              </div>
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
