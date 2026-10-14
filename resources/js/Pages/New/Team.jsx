import React, { useState, useMemo } from "react";
import { Link, router, usePage } from "@inertiajs/react";
import axios from "axios";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  Users,
  Search,
  Grid,
  List,
  Crown,
  ShieldCheck,
  Mail,
  Building2,
  CheckSquare,
  Clock,
  UserPlus,
  Send,
  X,
  Activity,
  Loader2,
  Check,
  AlertCircle,
  Briefcase,
  ChevronRight,
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";

export default function Team({
  team_members = [],
  stats = null,
  companies = [],
  admin_companies = [],
  current_workspace_name = "All Workspaces",
  filter_company_id = null,
}) {
  const { flash = {} } = usePage().props;

  const [searchQuery, setSearchQuery] = useState("");
  const [roleFilter, setRoleFilter] = useState("all"); // 'all' | 'admin' | 'member'
  const [companyFilter, setCompanyFilter] = useState(filter_company_id || "all");
  const [viewMode, setViewMode] = useState("grid"); // 'grid' | 'table'

  // Modals state
  const [showInviteModal, setShowInviteModal] = useState(false);
  const [inviteForm, setInviteForm] = useState({
    company_id: admin_companies[0]?.id || "",
    email: "",
    message: "",
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formErrors, setFormErrors] = useState({});

  // Activity modal
  const [activityUser, setActivityUser] = useState(null);
  const [activityData, setActivityData] = useState(null);
  const [loadingActivity, setLoadingActivity] = useState(false);

  // Filtered members
  const filteredMembers = useMemo(() => {
    return team_members.filter((m) => {
      const q = searchQuery.toLowerCase().trim();
      const matchesSearch =
        !q ||
        m.name.toLowerCase().includes(q) ||
        m.email.toLowerCase().includes(q);

      let matchesRole = true;
      if (roleFilter === "admin") {
        matchesRole = m.is_admin;
      } else if (roleFilter === "member") {
        matchesRole = !m.is_admin;
      }

      let matchesCompany = true;
      if (companyFilter !== "all") {
        matchesCompany = m.companies.some(
          (c) => String(c.id) === String(companyFilter)
        );
      }

      return matchesSearch && matchesRole && matchesCompany;
    });
  }, [team_members, searchQuery, roleFilter, companyFilter]);

  const handleInviteSubmit = (e) => {
    e.preventDefault();
    if (!inviteForm.company_id) return;
    setIsSubmitting(true);
    setFormErrors({});

    router.post(`/v2/companies/${inviteForm.company_id}/invite`, {
      email: inviteForm.email,
      message: inviteForm.message,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setShowInviteModal(false);
        setInviteForm({ company_id: admin_companies[0]?.id || "", email: "", message: "" });
      },
      onError: (errs) => {
        setIsSubmitting(false);
        setFormErrors(errs);
      },
    });
  };

  const fetchActivity = async (user) => {
    setActivityUser(user);
    setActivityData(null);
    setLoadingActivity(true);
    try {
      const compId = user.companies[0]?.id || companies[0]?.id;
      if (compId) {
        const res = await axios.get(`/v2/companies/${compId}/members/${user.id}/activity`);
        setActivityData(res.data);
      }
    } catch (e) {
      console.error("Failed to load activity", e);
    } finally {
      setLoadingActivity(false);
    }
  };

  return (
    <DashboardLayout title="Team Directory" activeItem="users">
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

        {/* Top Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-zinc-800">
          <div>
            <div className="flex items-center gap-2.5">
              <div className="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                <Users className="w-5 h-5" />
              </div>
              <h1 className="text-2xl font-bold tracking-tight text-zinc-100">
                Team Directory
              </h1>
              <Badge variant="outline" className="border-emerald-500/30 text-emerald-400 bg-emerald-500/5 font-mono text-xs">
                V2 Workspaces
              </Badge>
            </div>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1">
              Browse members across your workspaces, track workloads, and invite collaborators.
            </p>
          </div>

          <div className="flex items-center gap-2.5">
            {admin_companies.length > 0 && (
              <Button
                size="sm"
                onClick={() => {
                  setInviteForm({
                    company_id: admin_companies[0]?.id || "",
                    email: "",
                    message: "",
                  });
                  setFormErrors({});
                  setShowInviteModal(true);
                }}
                className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold text-xs gap-1.5 shadow-sm shadow-emerald-500/20"
              >
                <UserPlus className="w-4 h-4" />
                <span>Invite Member</span>
              </Button>
            )}
            <Button asChild variant="outline" size="sm" className="border-zinc-800 text-xs gap-1.5 text-zinc-300">
              <Link href="/v2/companies">
                <Building2 className="w-3.5 h-3.5 text-zinc-400" />
                <span>Manage Organizations</span>
              </Link>
            </Button>
          </div>
        </div>

        {/* Hero KPI Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3.5">
          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Total Teammates
              </span>
              <div className="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                <Users className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.total_members ?? team_members.length}
              </div>
              <p className="text-xs text-zinc-400 mt-1 truncate">
                In {current_workspace_name}
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Administrators
              </span>
              <div className="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400">
                <Crown className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.total_admins ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Workspace admin privileges
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Active Recently
              </span>
              <div className="p-1.5 rounded-lg bg-zinc-800 text-zinc-300">
                <Clock className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.active_members ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Logged in successfully
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Organizations
              </span>
              <div className="p-1.5 rounded-lg bg-amber-500/10 text-amber-400">
                <Building2 className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.total_organizations ?? companies.length}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Connected workspaces
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Search & Filters Toolbar */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-2xl bg-zinc-900/60 border border-zinc-800/80">
          <div className="relative flex-1 max-w-md">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" />
            <input
              type="text"
              placeholder="Search by teammate name or email..."
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
            {/* Workspace Select */}
            {companies.length > 1 && (
              <select
                value={companyFilter}
                onChange={(e) => setCompanyFilter(e.target.value)}
                className="bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-1.5 text-xs text-zinc-300 focus:outline-none focus:border-emerald-500/60 font-mono"
              >
                <option value="all">All Workspaces</option>
                {companies.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name}
                  </option>
                ))}
              </select>
            )}

            {/* Role Filter Pills */}
            <div className="flex items-center gap-1 p-1 bg-zinc-950 border border-zinc-800 rounded-xl shrink-0">
              {[
                { id: "all", label: "All Roles" },
                { id: "admin", label: "Admins" },
                { id: "member", label: "Members" },
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

            {/* View Mode */}
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

        {/* Empty state */}
        {filteredMembers.length === 0 && (
          <div className="text-center py-16 px-4 border border-dashed border-zinc-800 rounded-2xl bg-zinc-900/30">
            <div className="w-14 h-14 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mx-auto text-zinc-400 mb-4 shadow-inner">
              <Users className="w-7 h-7" />
            </div>
            <h3 className="text-lg font-bold text-zinc-200">No teammates found</h3>
            <p className="text-xs sm:text-sm text-zinc-400 max-w-md mx-auto mt-1 mb-6">
              {searchQuery
                ? `No teammates match "${searchQuery}". Reset filters to see full directory.`
                : "Invite your first teammate to begin collaborating on projects."}
            </p>
            {searchQuery && (
              <Button
                variant="outline"
                size="sm"
                onClick={() => {
                  setSearchQuery("");
                  setRoleFilter("all");
                  setCompanyFilter("all");
                }}
                className="border-zinc-800 text-zinc-300"
              >
                Clear Filters
              </Button>
            )}
          </div>
        )}

        {/* GRID VIEW */}
        {viewMode === "grid" && filteredMembers.length > 0 && (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {filteredMembers.map((m) => (
              <div
                key={m.id}
                className="p-5 rounded-2xl bg-zinc-900/80 border border-zinc-800/80 hover:border-zinc-700 transition-all flex flex-col justify-between group shadow-sm hover:shadow-xl hover:shadow-black/40"
              >
                <div>
                  <div className="flex items-start justify-between gap-3 mb-4">
                    <div className="flex items-center gap-3">
                      <Avatar className="w-11 h-11 border border-zinc-700 ring-2 ring-zinc-800">
                        {m.profile_image ? (
                          <AvatarImage src={m.profile_image} alt={m.name} />
                        ) : null}
                        <AvatarFallback className="bg-zinc-800 text-zinc-200 font-bold text-sm">
                          {m.avatar}
                        </AvatarFallback>
                      </Avatar>
                      <div>
                        <h3 className="font-bold text-zinc-100 text-base leading-snug group-hover:text-emerald-400 transition-colors">
                          {m.name}
                        </h3>
                        <p className="text-xs text-zinc-400 font-mono truncate">{m.email}</p>
                      </div>
                    </div>

                    <Badge
                      variant="outline"
                      className={`text-[10px] font-semibold px-2 py-0.5 shrink-0 ${
                        m.is_admin
                          ? "border-indigo-500/30 text-indigo-400 bg-indigo-500/10"
                          : "border-zinc-700 text-zinc-400 bg-zinc-800/40"
                      }`}
                    >
                      {m.primary_role}
                    </Badge>
                  </div>

                  {/* Workspaces badges */}
                  <div className="flex flex-wrap gap-1 mb-4">
                    {m.companies.map((c) => (
                      <span
                        key={c.id}
                        className="px-2 py-0.5 rounded-md bg-zinc-950 border border-zinc-800 text-[11px] text-zinc-300 font-mono flex items-center gap-1"
                      >
                        <Building2 className="w-3 h-3 text-zinc-500" />
                        {c.name}
                      </span>
                    ))}
                  </div>

                  {/* Task counts */}
                  <div className="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-zinc-950/60 border border-zinc-800/70 text-center mb-4 text-xs font-mono">
                    <div>
                      <span className="text-[11px] text-zinc-500 block">Pending Tasks</span>
                      <span className="text-amber-400 font-bold">{m.pending_tasks_count}</span>
                    </div>
                    <div>
                      <span className="text-[11px] text-zinc-500 block">Completed</span>
                      <span className="text-emerald-400 font-bold">{m.completed_tasks_count}</span>
                    </div>
                  </div>
                </div>

                <div className="pt-2 border-t border-zinc-800/70 flex items-center justify-between text-xs text-zinc-500 font-mono">
                  <span>Last login: {m.last_login}</span>
                  <Button
                    size="sm"
                    variant="ghost"
                    onClick={() => fetchActivity(m)}
                    className="h-7 px-2 text-xs text-zinc-400 hover:text-emerald-400 hover:bg-zinc-800 gap-1"
                  >
                    <Activity className="w-3.5 h-3.5" />
                    <span>Activity</span>
                  </Button>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* TABLE VIEW */}
        {viewMode === "table" && filteredMembers.length > 0 && (
          <div className="bg-zinc-900/80 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs sm:text-sm">
                <thead className="bg-zinc-950/70 border-b border-zinc-800 text-zinc-400 font-mono text-[11px] uppercase tracking-wider">
                  <tr>
                    <th className="py-3 px-4">Teammate</th>
                    <th className="py-3 px-4">Role</th>
                    <th className="py-3 px-4">Workspaces</th>
                    <th className="py-3 px-4">Tasks</th>
                    <th className="py-3 px-4">Last Login</th>
                    <th className="py-3 px-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-zinc-800/60">
                  {filteredMembers.map((m) => (
                    <tr key={m.id} className="hover:bg-zinc-800/40 transition-colors">
                      <td className="py-3.5 px-4">
                        <div className="flex items-center gap-3">
                          <Avatar className="w-8 h-8 border border-zinc-700">
                            {m.profile_image ? (
                              <AvatarImage src={m.profile_image} alt={m.name} />
                            ) : null}
                            <AvatarFallback className="bg-zinc-800 text-zinc-300 font-bold text-xs">
                              {m.avatar}
                            </AvatarFallback>
                          </Avatar>
                          <div>
                            <div className="font-bold text-zinc-100">{m.name}</div>
                            <div className="text-[11px] text-zinc-400 font-mono">{m.email}</div>
                          </div>
                        </div>
                      </td>

                      <td className="py-3.5 px-4">
                        <Badge
                          variant="outline"
                          className={`text-[10px] font-semibold ${
                            m.is_admin
                              ? "border-indigo-500/30 text-indigo-400 bg-indigo-500/10"
                              : "border-zinc-700 text-zinc-400 bg-zinc-800/40"
                          }`}
                        >
                          {m.primary_role}
                        </Badge>
                      </td>

                      <td className="py-3.5 px-4">
                        <div className="flex flex-wrap gap-1">
                          {m.companies.map((c) => (
                            <span
                              key={c.id}
                              className="px-2 py-0.5 rounded bg-zinc-950 border border-zinc-800 text-[10px] text-zinc-300 font-mono"
                            >
                              {c.name}
                            </span>
                          ))}
                        </div>
                      </td>

                      <td className="py-3.5 px-4 font-mono text-xs">
                        <span className="text-emerald-400 font-bold">{m.completed_tasks_count}</span>
                        <span className="text-zinc-500"> / {m.tasks_count} done</span>
                      </td>

                      <td className="py-3.5 px-4 text-zinc-400 font-mono text-xs">
                        {m.last_login}
                      </td>

                      <td className="py-3.5 px-4 text-right">
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={() => fetchActivity(m)}
                          className="h-7 text-xs text-zinc-400 hover:text-zinc-100 gap-1"
                        >
                          <Activity className="w-3.5 h-3.5" />
                          <span>Activity</span>
                        </Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* INVITE MODAL */}
        {showInviteModal && (
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
                    <h3 className="font-bold text-zinc-100 text-sm sm:text-base">Invite Teammate</h3>
                    <p className="text-xs text-zinc-400 font-mono">Send workspace email invitation</p>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setShowInviteModal(false)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleInviteSubmit} className="p-5 space-y-4 text-xs sm:text-sm">
                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Target Workspace <span className="text-rose-400">*</span>
                  </label>
                  <select
                    value={inviteForm.company_id}
                    onChange={(e) => setInviteForm({ ...inviteForm, company_id: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 focus:outline-none focus:border-emerald-500/60 transition-colors"
                  >
                    {admin_companies.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Teammate Email <span className="text-rose-400">*</span>
                  </label>
                  <input
                    type="email"
                    required
                    placeholder="teammate@example.com"
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
                    Welcome Message (Optional)
                  </label>
                  <textarea
                    rows={3}
                    placeholder="Join our projects on WorkHub!"
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
                    onClick={() => setShowInviteModal(false)}
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
                        <span>Send Invite</span>
                      </>
                    )}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* ACTIVITY MODAL */}
        {activityUser && (
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
                      {activityUser.name} — Recent Activity
                    </h3>
                    <p className="text-xs text-zinc-400 font-mono">Workspace event logs</p>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setActivityUser(null)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <div className="p-5 overflow-y-auto flex-1 space-y-4">
                {loadingActivity ? (
                  <div className="py-12 text-center text-zinc-400 flex flex-col items-center justify-center gap-2">
                    <Loader2 className="w-6 h-6 animate-spin text-emerald-400" />
                    <span className="text-xs font-mono">Loading activity stream...</span>
                  </div>
                ) : activityData?.activities && activityData.activities.length > 0 ? (
                  <div className="space-y-3">
                    {activityData.activities.map((act) => (
                      <div
                        key={act.id}
                        className="p-3 rounded-xl bg-zinc-950/70 border border-zinc-800/80 text-xs space-y-1"
                      >
                        <div className="flex items-center justify-between gap-2">
                          <span className="font-semibold text-zinc-200">{act.description}</span>
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
                    <p className="font-semibold text-zinc-300">No recorded activities</p>
                    <p className="text-xs text-zinc-500 mt-0.5">Events will appear as contributions occur.</p>
                  </div>
                )}
              </div>

              <div className="p-4 border-t border-zinc-800 bg-zinc-950/60 flex items-center justify-between text-xs text-zinc-500 font-mono shrink-0">
                <span>Last login: {activityData?.user?.last_login_at || "Never"}</span>
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() => setActivityUser(null)}
                  className="border-zinc-800 text-zinc-300"
                >
                  Close
                </Button>
              </div>
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
