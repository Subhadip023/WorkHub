import React, { useState, useMemo } from "react";
import { Link, router, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  AlertCircle,
  Bug,
  Sparkles,
  Zap,
  CheckCircle2,
  Circle,
  Search,
  Plus,
  Filter,
  ChevronRight,
  Clock,
  Check,
  X,
  Loader2,
  FolderKanban,
  Flag,
  User,
  ShieldAlert,
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";

export default function Issues({
  issues = [],
  stats = null,
  projects = [],
  current_workspace_name = "All Workspaces",
  state_filter = "all",
}) {
  const { flash = {} } = usePage().props;

  const [searchQuery, setSearchQuery] = useState("");
  const [stateTab, setStateTab] = useState(state_filter || "all"); // 'all' | 'open' | 'closed'
  const [categoryFilter, setCategoryFilter] = useState("all"); // 'all' | 'Bug' | 'Feature' | 'Improvement'
  const [priorityFilter, setPriorityFilter] = useState("all");

  // Create issue modal
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [createForm, setCreateForm] = useState({
    title: "",
    category: "bug",
    priority: "medium",
    description: "",
    project_id: projects[0]?.id || "",
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formErrors, setFormErrors] = useState({});

  // Inspect issue modal
  const [inspectingIssue, setInspectingIssue] = useState(null);

  // Filtered issues
  const filteredIssues = useMemo(() => {
    return issues.filter((iss) => {
      const q = searchQuery.toLowerCase().trim();
      const matchesSearch =
        !q ||
        iss.title.toLowerCase().includes(q) ||
        iss.code.toLowerCase().includes(q) ||
        iss.description.toLowerCase().includes(q);

      let matchesState = true;
      if (stateTab === "open") matchesState = iss.state === "open";
      if (stateTab === "closed") matchesState = iss.state === "closed";

      let matchesCategory = true;
      if (categoryFilter !== "all") {
        matchesCategory = iss.category.toLowerCase() === categoryFilter.toLowerCase();
      }

      let matchesPriority = true;
      if (priorityFilter !== "all") {
        matchesPriority = iss.priority.toLowerCase() === priorityFilter.toLowerCase();
      }

      return matchesSearch && matchesState && matchesCategory && matchesPriority;
    });
  }, [issues, searchQuery, stateTab, categoryFilter, priorityFilter]);

  const handleCreateSubmit = (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setFormErrors({});

    router.post("/v2/issues", createForm, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setShowCreateModal(false);
        setCreateForm({
          title: "",
          category: "bug",
          priority: "medium",
          description: "",
          project_id: projects[0]?.id || "",
        });
      },
      onError: (errs) => {
        setIsSubmitting(false);
        setFormErrors(errs);
      },
    });
  };

  const handleToggleState = (issue, e) => {
    e.stopPropagation();
    router.patch(`/v2/issues/${issue.id}/toggle`, {}, { preserveScroll: true });
  };

  return (
    <DashboardLayout title="Issues & Bug Tracker" activeItem="issues">
      <div className="space-y-6 pb-12">
        {/* Flash Notifications */}
        {flash.success && (
          <div className="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center gap-3 animate-in fade-in duration-200">
            <Check className="w-5 h-5 shrink-0" />
            <p className="text-sm font-medium">{flash.success}</p>
          </div>
        )}

        {/* Top Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-zinc-800">
          <div>
            <div className="flex items-center gap-2.5">
              <div className="p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400">
                <Bug className="w-5 h-5" />
              </div>
              <h1 className="text-2xl font-bold tracking-tight text-zinc-100">
                Issues & Bug Tracker
              </h1>
              <Badge variant="outline" className="border-rose-500/30 text-rose-400 bg-rose-500/5 font-mono text-xs">
                V2 Tracker
              </Badge>
            </div>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1">
              File bug reports, triage regressions, track improvements, and resolve project incidents.
            </p>
          </div>

          <Button
            size="sm"
            onClick={() => {
              setCreateForm({
                title: "",
                category: "bug",
                priority: "medium",
                description: "",
                project_id: projects[0]?.id || "",
              });
              setFormErrors({});
              setShowCreateModal(true);
            }}
            className="bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs gap-1.5 shadow-sm shadow-rose-500/20"
          >
            <Plus className="w-4 h-4" />
            <span>Report Issue</span>
          </Button>
        </div>

        {/* Hero KPI Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3.5">
          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Total Issues
              </span>
              <div className="p-1.5 rounded-lg bg-zinc-800 text-zinc-300">
                <AlertCircle className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.total ?? issues.length}
              </div>
              <p className="text-xs text-zinc-400 mt-1 truncate">
                In {current_workspace_name}
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Open Bugs
              </span>
              <div className="p-1.5 rounded-lg bg-rose-500/10 text-rose-400">
                <Bug className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-rose-400">
                {stats?.bugs ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Requiring fix
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Critical / Urgent
              </span>
              <div className="p-1.5 rounded-lg bg-amber-500/10 text-amber-400">
                <ShieldAlert className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-amber-400">
                {stats?.critical ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                High priority triage
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Resolved
              </span>
              <div className="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                <CheckCircle2 className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-emerald-400">
                {stats?.closed ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Completed & verified
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Toolbar */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-2xl bg-zinc-900/60 border border-zinc-800/80">
          <div className="relative flex-1 max-w-md">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" />
            <input
              type="text"
              placeholder="Search issues by title, code (ISS-0001), or description..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full bg-zinc-950 border border-zinc-800 rounded-xl pl-9 pr-3.5 py-2 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-rose-500/60 transition-colors"
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
            {/* State filter */}
            <div className="flex items-center gap-1 p-1 bg-zinc-950 border border-zinc-800 rounded-xl shrink-0">
              {[
                { id: "all", label: "All" },
                { id: "open", label: "Open" },
                { id: "closed", label: "Resolved" },
              ].map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => setStateTab(tab.id)}
                  className={`px-3 py-1 rounded-lg text-xs font-medium transition-all ${
                    stateTab === tab.id
                      ? "bg-rose-500/15 text-rose-400 border border-rose-500/30 font-semibold"
                      : "text-zinc-400 hover:text-zinc-200"
                  }`}
                >
                  {tab.label}
                </button>
              ))}
            </div>

            {/* Category select */}
            <select
              value={categoryFilter}
              onChange={(e) => setCategoryFilter(e.target.value)}
              className="bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-1.5 text-xs text-zinc-300 focus:outline-none focus:border-rose-500/60 font-mono"
            >
              <option value="all">All Types</option>
              <option value="Bug">Bugs</option>
              <option value="Feature">Features</option>
              <option value="Improvement">Improvements</option>
            </select>
          </div>
        </div>

        {/* Issues List */}
        {filteredIssues.length === 0 ? (
          <div className="text-center py-16 px-4 border border-dashed border-zinc-800 rounded-2xl bg-zinc-900/30">
            <div className="w-14 h-14 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mx-auto text-zinc-400 mb-4 shadow-inner">
              <Bug className="w-7 h-7" />
            </div>
            <h3 className="text-lg font-bold text-zinc-200">No issues found</h3>
            <p className="text-xs sm:text-sm text-zinc-400 max-w-md mx-auto mt-1 mb-6">
              {searchQuery
                ? `No issues match "${searchQuery}". Reset filters to see all entries.`
                : "No issues reported yet. Click Report Issue to file a bug or feature request."}
            </p>
            <Button
              size="sm"
              onClick={() => setShowCreateModal(true)}
              className="bg-rose-500 hover:bg-rose-600 text-white font-bold"
            >
              <Plus className="w-4 h-4 mr-1.5" /> Report Issue
            </Button>
          </div>
        ) : (
          <div className="bg-zinc-900/80 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl divide-y divide-zinc-800/60">
            {filteredIssues.map((iss) => (
              <div
                key={iss.id}
                onClick={() => setInspectingIssue(iss)}
                className="p-4 hover:bg-zinc-800/40 transition-colors flex items-start justify-between gap-4 cursor-pointer group"
              >
                <div className="flex items-start gap-3.5 min-w-0 flex-1">
                  {/* Status Toggle Checkbox */}
                  <button
                    type="button"
                    onClick={(e) => handleToggleState(iss, e)}
                    className="mt-0.5 text-zinc-500 hover:text-emerald-400 transition-colors shrink-0"
                    title={iss.state === "open" ? "Click to mark as resolved" : "Click to re-open"}
                  >
                    {iss.state === "closed" ? (
                      <CheckCircle2 className="w-5 h-5 text-emerald-400 fill-emerald-500/20" />
                    ) : (
                      <Circle className="w-5 h-5 hover:border-emerald-400" />
                    )}
                  </button>

                  <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-2 mb-1">
                      <span className="font-mono text-xs font-bold text-zinc-400">
                        {iss.code}
                      </span>

                      <h3 className={`font-semibold text-sm leading-snug truncate group-hover:text-rose-400 transition-colors ${
                        iss.state === "closed" ? "line-through text-zinc-500" : "text-zinc-100"
                      }`}>
                        {iss.title}
                      </h3>

                      <Badge
                        variant="outline"
                        className={`text-[10px] font-semibold px-2 py-0.5 ${
                          iss.category === "Bug"
                            ? "border-rose-500/30 text-rose-400 bg-rose-500/10"
                            : iss.category === "Feature"
                            ? "border-indigo-500/30 text-indigo-400 bg-indigo-500/10"
                            : "border-cyan-500/30 text-cyan-400 bg-cyan-500/10"
                        }`}
                      >
                        {iss.category}
                      </Badge>

                      <Badge
                        variant="outline"
                        className={`text-[10px] font-semibold px-2 py-0.5 ${
                          iss.priority === "Critical"
                            ? "border-rose-500/40 text-rose-300 bg-rose-500/20 animate-pulse"
                            : iss.priority === "High"
                            ? "border-amber-500/30 text-amber-400 bg-amber-500/10"
                            : "border-zinc-700 text-zinc-400 bg-zinc-800/40"
                        }`}
                      >
                        {iss.priority}
                      </Badge>
                    </div>

                    <p className="text-xs text-zinc-400 line-clamp-1 mb-2">
                      {iss.description || "No description provided."}
                    </p>

                    <div className="flex flex-wrap items-center gap-3 text-[11px] text-zinc-500 font-mono">
                      <span className="flex items-center gap-1 text-zinc-400">
                        <FolderKanban className="w-3 h-3" />
                        {iss.project_name}
                      </span>
                      <span>•</span>
                      <span>Created {iss.created_at_human}</span>
                      {iss.reporter && (
                        <>
                          <span>•</span>
                          <span>by {iss.reporter.name}</span>
                        </>
                      )}
                    </div>
                  </div>
                </div>

                <div className="flex items-center gap-2 shrink-0">
                  {iss.assignee && (
                    <Avatar className="w-7 h-7 border border-zinc-700" title={`Assigned to ${iss.assignee.name}`}>
                      {iss.assignee.profile_image ? (
                        <AvatarImage src={iss.assignee.profile_image} alt={iss.assignee.name} />
                      ) : null}
                      <AvatarFallback className="bg-zinc-800 text-zinc-300 font-bold text-[10px]">
                        {iss.assignee.avatar}
                      </AvatarFallback>
                    </Avatar>
                  )}
                  <ChevronRight className="w-4 h-4 text-zinc-600 group-hover:text-zinc-300 transition-colors" />
                </div>
              </div>
            ))}
          </div>
        )}

        {/* MODAL: REPORT ISSUE */}
        {showCreateModal && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex items-center justify-between p-5 border-b border-zinc-800 bg-zinc-950/60">
                <div className="flex items-center gap-2.5">
                  <div className="p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400">
                    <Bug className="w-4 h-4" />
                  </div>
                  <div>
                    <h3 className="font-bold text-zinc-100 text-sm sm:text-base">Report an Issue</h3>
                    <p className="text-xs text-zinc-400 font-mono">Submit a bug report or feature request</p>
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
                    Issue Title <span className="text-rose-400">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Navigation link fails on cold reload"
                    value={createForm.title}
                    onChange={(e) => setCreateForm({ ...createForm, title: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-rose-500/60 transition-colors"
                  />
                  {formErrors.title && (
                    <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                      <AlertCircle className="w-3 h-3" /> {formErrors.title}
                    </p>
                  )}
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Category
                    </label>
                    <select
                      value={createForm.category}
                      onChange={(e) => setCreateForm({ ...createForm, category: e.target.value })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 focus:outline-none focus:border-rose-500/60 font-mono"
                    >
                      <option value="bug">Bug</option>
                      <option value="feature">Feature Request</option>
                      <option value="improvement">Improvement</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Priority
                    </label>
                    <select
                      value={createForm.priority}
                      onChange={(e) => setCreateForm({ ...createForm, priority: e.target.value })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 focus:outline-none focus:border-rose-500/60 font-mono"
                    >
                      <option value="low">Low</option>
                      <option value="medium">Medium</option>
                      <option value="high">High</option>
                      <option value="critical">Critical</option>
                    </select>
                  </div>
                </div>

                {projects.length > 0 && (
                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Project Association
                    </label>
                    <select
                      value={createForm.project_id}
                      onChange={(e) => setCreateForm({ ...createForm, project_id: e.target.value })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 focus:outline-none focus:border-rose-500/60 font-mono"
                    >
                      <option value="">No Project (General Issue)</option>
                      {projects.map((p) => (
                        <option key={p.id} value={p.id}>
                          {p.name}
                        </option>
                      ))}
                    </select>
                  </div>
                )}

                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Description & Steps to Reproduce <span className="text-rose-400">*</span>
                  </label>
                  <textarea
                    required
                    rows={4}
                    placeholder="Describe the issue, unexpected behavior, and steps to reproduce..."
                    value={createForm.description}
                    onChange={(e) => setCreateForm({ ...createForm, description: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-rose-500/60 transition-colors resize-none"
                  />
                  {formErrors.description && (
                    <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                      <AlertCircle className="w-3 h-3" /> {formErrors.description}
                    </p>
                  )}
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
                    disabled={isSubmitting || !createForm.title.trim()}
                    className="bg-rose-500 hover:bg-rose-600 text-white font-bold px-4"
                  >
                    {isSubmitting ? (
                      <span className="flex items-center gap-2">
                        <Loader2 className="w-3.5 h-3.5 animate-spin" /> Submitting...
                      </span>
                    ) : (
                      "Submit Issue"
                    )}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* MODAL: VIEW ISSUE DETAILS */}
        {inspectingIssue && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex items-center justify-between p-5 border-b border-zinc-800 bg-zinc-950/60 shrink-0">
                <div className="flex items-center gap-2.5">
                  <span className="font-mono text-xs font-bold text-zinc-400">
                    {inspectingIssue.code}
                  </span>
                  <Badge
                    variant="outline"
                    className={`text-[10px] font-semibold ${
                      inspectingIssue.category === "Bug"
                        ? "border-rose-500/30 text-rose-400 bg-rose-500/10"
                        : "border-indigo-500/30 text-indigo-400 bg-indigo-500/10"
                    }`}
                  >
                    {inspectingIssue.category}
                  </Badge>
                </div>
                <button
                  type="button"
                  onClick={() => setInspectingIssue(null)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <div className="p-5 overflow-y-auto flex-1 space-y-4">
                <div>
                  <h2 className="text-lg font-bold text-zinc-100">{inspectingIssue.title}</h2>
                  <div className="flex items-center gap-2 mt-2">
                    <Badge
                      className={`text-xs ${
                        inspectingIssue.state === "closed"
                          ? "bg-emerald-500/15 border-emerald-500/30 text-emerald-400"
                          : "bg-rose-500/15 border-rose-500/30 text-rose-400"
                      }`}
                    >
                      {inspectingIssue.state === "closed" ? "Resolved" : "Open Issue"}
                    </Badge>
                    <span className="text-xs text-zinc-400 font-mono">
                      Priority: <strong className="text-zinc-200">{inspectingIssue.priority}</strong>
                    </span>
                  </div>
                </div>

                <div className="p-3.5 rounded-xl bg-zinc-950/80 border border-zinc-800 text-xs sm:text-sm text-zinc-300 whitespace-pre-wrap leading-relaxed">
                  {inspectingIssue.description || "No description provided."}
                </div>

                <div className="text-xs text-zinc-500 font-mono space-y-1 border-t border-zinc-800 pt-3">
                  <div>Project: <span className="text-zinc-300">{inspectingIssue.project_name}</span></div>
                  <div>Reported: <span className="text-zinc-300">{inspectingIssue.created_at}</span></div>
                  {inspectingIssue.reporter && (
                    <div>Reporter: <span className="text-zinc-300">{inspectingIssue.reporter.name}</span></div>
                  )}
                </div>
              </div>

              <div className="p-4 border-t border-zinc-800 bg-zinc-950/60 flex items-center justify-between shrink-0">
                <Button
                  size="sm"
                  variant="outline"
                  onClick={(e) => {
                    handleToggleState(inspectingIssue, e);
                    setInspectingIssue(null);
                  }}
                  className="border-zinc-800 text-xs gap-1.5"
                >
                  {inspectingIssue.state === "closed" ? (
                    <>
                      <Circle className="w-3.5 h-3.5" /> Re-open Issue
                    </>
                  ) : (
                    <>
                      <CheckCircle2 className="w-3.5 h-3.5 text-emerald-400" /> Mark as Resolved
                    </>
                  )}
                </Button>
                <Button
                  size="sm"
                  variant="ghost"
                  onClick={() => setInspectingIssue(null)}
                  className="text-zinc-400 hover:text-zinc-100 text-xs"
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
