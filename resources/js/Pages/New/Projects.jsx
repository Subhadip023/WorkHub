import React, { useState, useEffect, useRef } from "react";
import { Link, router, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  FolderKanban,
  Plus,
  Search,
  Grid,
  List,
  CheckCircle2,
  Clock,
  ChevronRight,
  Sparkles,
  Tag,
  MoreVertical,
  Edit2,
  Trash2,
  ExternalLink,
  FileText,
  Key,
  Check,
  AlertTriangle,
  Building2,
  User,
  Layers,
  ArrowUpDown,
  X,
  Loader2,
  TrendingUp,
  AlertCircle
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Progress } from "@/Components/ui/progress";

const THEME_COLORS = [
  { name: "Emerald", hex: "#10b981", bg: "bg-emerald-500", border: "border-emerald-500" },
  { name: "Blue", hex: "#3b82f6", bg: "bg-blue-500", border: "border-blue-500" },
  { name: "Indigo", hex: "#6366f1", bg: "bg-indigo-500", border: "border-indigo-500" },
  { name: "Purple", hex: "#8b5cf6", bg: "bg-purple-500", border: "border-purple-500" },
  { name: "Amber", hex: "#f59e0b", bg: "bg-amber-500", border: "border-amber-500" },
  { name: "Rose", hex: "#f43f5e", bg: "bg-rose-500", border: "border-rose-500" },
  { name: "Cyan", hex: "#06b6d4", bg: "bg-cyan-500", border: "border-cyan-500" },
  { name: "Zinc", hex: "#71717a", bg: "bg-zinc-500", border: "border-zinc-500" },
];

export default function Projects({ initial_projects = [], companies = [], stats = null, current_workspace_name = "" }) {
  const { flash = {} } = usePage().props;

  const [projectsList, setProjectsList] = useState(initial_projects);
  const [viewMode, setViewMode] = useState("grid"); // 'grid' | 'table'
  const [statusFilter, setStatusFilter] = useState("all");
  const [priorityFilter, setPriorityFilter] = useState("all");
  const [workspaceFilter, setWorkspaceFilter] = useState("all");
  const [sortBy, setSortBy] = useState("recent");
  const [searchQuery, setSearchQuery] = useState("");

  // Modals state
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [editingProject, setEditingProject] = useState(null);
  const [deletingProject, setDeletingProject] = useState(null);
  const [activeDropdown, setActiveDropdown] = useState(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formErrors, setFormErrors] = useState({});

  // Form State
  const initialFormState = {
    name: "",
    description: "",
    company_id: "personal",
    status: 1, // 1: To Do, 2: In Progress, 3: Completed, 4: On Hold
    priority: 2, // 1: Low, 2: Medium, 3: High, 4: Urgent
    theme: "#10b981",
  };
  const [formData, setFormData] = useState(initialFormState);

  // Sync projects list when server props update
  useEffect(() => {
    if (initial_projects) {
      setProjectsList(initial_projects);
    }
  }, [initial_projects]);

  // Close card menus on outside click
  useEffect(() => {
    const handleOutside = () => setActiveDropdown(null);
    window.addEventListener("click", handleOutside);
    return () => window.removeEventListener("click", handleOutside);
  }, []);

  // Compute KPI metrics
  const totalProjects = stats?.total_projects ?? projectsList.length;
  const inProgressProjects = stats?.in_progress ?? projectsList.filter((p) => p.status === "In Progress" || p.status_id === 2).length;
  const completedProjects = stats?.completed ?? projectsList.filter((p) => p.status === "Completed" || p.status_id === 3).length;
  const onHoldProjects = stats?.on_hold ?? projectsList.filter((p) => p.status === "On Hold" || p.status_id === 4).length;
  const totalTasks = stats?.total_tasks ?? projectsList.reduce((acc, p) => acc + (p.totalTasks || 0), 0);
  const completedTasks = stats?.completed_tasks ?? projectsList.reduce((acc, p) => acc + (p.completedTasks || 0), 0);
  const completionRate = stats?.completion_rate ?? (totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 100);

  // Open Create Modal
  const handleOpenCreate = () => {
    setFormData(initialFormState);
    setFormErrors({});
    setShowCreateModal(true);
  };

  // Open Edit Modal
  const handleOpenEdit = (project, e) => {
    e?.stopPropagation();
    setActiveDropdown(null);
    setEditingProject(project);
    setFormData({
      name: project.name || "",
      description: project.description || "",
      company_id: project.company_id ? String(project.company_id) : "personal",
      status: project.status_id || (project.status === "Completed" ? 3 : project.status === "In Progress" ? 2 : project.status === "On Hold" ? 4 : 1),
      priority: project.priority_id || (project.priority === "Urgent" ? 4 : project.priority === "High" ? 3 : project.priority === "Low" ? 1 : 2),
      theme: project.theme || "#10b981",
    });
    setFormErrors({});
  };

  // Submit Create Project
  const handleCreateSubmit = (e) => {
    e.preventDefault();
    if (!formData.name.trim()) {
      setFormErrors({ name: "Project name is required." });
      return;
    }
    if (!formData.description.trim()) {
      setFormErrors({ description: "Project description is required." });
      return;
    }

    setIsSubmitting(true);
    setFormErrors({});

    router.post("/projects", {
      name: formData.name,
      description: formData.description,
      theme: formData.theme,
      status: Number(formData.status),
      priority: Number(formData.priority),
      company_id: formData.company_id,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setShowCreateModal(false);
        setFormData(initialFormState);
      },
      onError: (errs) => {
        setIsSubmitting(false);
        setFormErrors(errs);
      }
    });
  };

  // Submit Edit Project
  const handleEditSubmit = (e) => {
    e.preventDefault();
    if (!editingProject) return;

    if (!formData.name.trim()) {
      setFormErrors({ name: "Project name is required." });
      return;
    }
    if (!formData.description.trim()) {
      setFormErrors({ description: "Project description is required." });
      return;
    }

    setIsSubmitting(true);
    setFormErrors({});

    router.put(`/projects/${editingProject.id}`, {
      name: formData.name,
      description: formData.description,
      theme: formData.theme,
      status: Number(formData.status),
      priority: Number(formData.priority),
      company_id: formData.company_id,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setEditingProject(null);
      },
      onError: (errs) => {
        setIsSubmitting(false);
        setFormErrors(errs);
      }
    });
  };

  // Submit Delete Project
  const handleDeleteConfirm = () => {
    if (!deletingProject) return;
    setIsSubmitting(true);

    router.delete(`/projects/${deletingProject.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setDeletingProject(null);
      },
      onError: () => {
        setIsSubmitting(false);
      }
    });
  };

  // Filter and sort projects
  const filteredProjects = projectsList.filter((p) => {
    const q = searchQuery.toLowerCase().trim();
    const matchesSearch =
      !q ||
      p.name.toLowerCase().includes(q) ||
      (p.description && p.description.toLowerCase().includes(q)) ||
      (p.tag && p.tag.toLowerCase().includes(q)) ||
      (p.category && p.category.toLowerCase().includes(q));

    // Status filter
    let matchesStatus = true;
    if (statusFilter === "in_progress") {
      matchesStatus = p.status === "In Progress" || p.status_id === 2;
    } else if (statusFilter === "completed") {
      matchesStatus = p.status === "Completed" || p.status_id === 3;
    } else if (statusFilter === "todo") {
      matchesStatus = p.status === "To Do" || p.status === "Todo" || p.status_id === 1;
    } else if (statusFilter === "on_hold") {
      matchesStatus = p.status === "On Hold" || p.status_id === 4;
    }

    // Priority filter
    let matchesPriority = true;
    if (priorityFilter !== "all") {
      matchesPriority = p.priority?.toLowerCase() === priorityFilter.toLowerCase();
    }

    // Workspace filter
    let matchesWorkspace = true;
    if (workspaceFilter === "personal") {
      matchesWorkspace = p.company_id === null;
    } else if (workspaceFilter !== "all") {
      matchesWorkspace = String(p.company_id) === String(workspaceFilter);
    }

    return matchesSearch && matchesStatus && matchesPriority && matchesWorkspace;
  }).sort((a, b) => {
    if (sortBy === "name") {
      return a.name.localeCompare(b.name);
    }
    if (sortBy === "progress") {
      return (b.progress || 0) - (a.progress || 0);
    }
    if (sortBy === "tasks") {
      return (b.totalTasks || 0) - (a.totalTasks || 0);
    }
    // Default 'recent'
    return b.id - a.id;
  });

  const headerActions = (
    <Button
      size="sm"
      onClick={handleOpenCreate}
      className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-semibold text-xs sm:text-sm gap-2 transition-all rounded-xl h-9 px-3.5 shadow-md shadow-emerald-500/20 cursor-pointer"
    >
      <Plus className="h-4 w-4" /> New Project
    </Button>
  );

  return (
    <DashboardLayout title="Projects" activeItem="projects" actions={headerActions}>
      <div className="space-y-6">
        {/* Flash Message Banner */}
        {flash?.success && (
          <div className="p-3.5 bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 rounded-xl text-xs sm:text-sm flex items-center justify-between animate-in fade-in">
            <div className="flex items-center gap-2">
              <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
              <span>{flash.success}</span>
            </div>
          </div>
        )}

        {/* Hero Header Card */}
        <div className="relative overflow-hidden rounded-2xl border border-zinc-800 bg-gradient-to-br from-zinc-900 via-zinc-900/90 to-zinc-950 p-6 sm:p-8 shadow-2xl">
          <div className="absolute top-0 right-0 w-96 h-96 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none" />
          
          <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div className="space-y-2">
              <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono font-medium">
                <Sparkles className="w-3.5 h-3.5" />
                <span>{current_workspace_name || "Workspace Projects Hub"}</span>
              </div>
              <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-3">
                Projects & Initiatives
                <span className="text-sm font-normal font-mono px-2.5 py-0.5 rounded-full bg-zinc-800/80 border border-zinc-700 text-zinc-300">
                  {totalProjects} {totalProjects === 1 ? "project" : "projects"}
                </span>
              </h1>
              <p className="text-xs sm:text-sm text-zinc-400 max-w-2xl leading-relaxed">
                Coordinate team initiatives, track milestone progress in real-time, and manage tasks across your personal space and organizations.
              </p>
            </div>

            <div className="flex items-center gap-3 shrink-0">
              <Button
                onClick={handleOpenCreate}
                className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold text-xs sm:text-sm gap-2 rounded-xl h-10 px-5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer"
              >
                <Plus className="h-4 w-4" /> Create Project
              </Button>
            </div>
          </div>

          {/* Quick Metrics Bar */}
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-8 pt-6 border-t border-zinc-800/80">
            <div className="p-3.5 rounded-xl bg-zinc-950/60 border border-zinc-800/60 flex items-center gap-3">
              <div className="p-2.5 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300">
                <Layers className="w-4 h-4 text-emerald-400" />
              </div>
              <div>
                <p className="text-xs text-zinc-400 font-mono">Total Projects</p>
                <p className="text-lg font-bold text-zinc-100">{totalProjects}</p>
              </div>
            </div>

            <div className="p-3.5 rounded-xl bg-zinc-950/60 border border-zinc-800/60 flex items-center gap-3">
              <div className="p-2.5 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300">
                <Clock className="w-4 h-4 text-amber-400" />
              </div>
              <div>
                <p className="text-xs text-zinc-400 font-mono">In Progress</p>
                <p className="text-lg font-bold text-zinc-100">{inProgressProjects}</p>
              </div>
            </div>

            <div className="p-3.5 rounded-xl bg-zinc-950/60 border border-zinc-800/60 flex items-center gap-3">
              <div className="p-2.5 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300">
                <CheckCircle2 className="w-4 h-4 text-emerald-400" />
              </div>
              <div>
                <p className="text-xs text-zinc-400 font-mono">Completed</p>
                <p className="text-lg font-bold text-zinc-100">{completedProjects}</p>
              </div>
            </div>

            <div className="p-3.5 rounded-xl bg-zinc-950/60 border border-zinc-800/60 flex items-center gap-3">
              <div className="p-2.5 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300">
                <TrendingUp className="w-4 h-4 text-blue-400" />
              </div>
              <div className="flex-1 min-w-0">
                <div className="flex items-center justify-between">
                  <p className="text-xs text-zinc-400 font-mono">Velocity</p>
                  <p className="text-xs font-mono font-bold text-emerald-400">{completionRate}%</p>
                </div>
                <div className="h-1.5 w-full bg-zinc-800 rounded-full overflow-hidden mt-1.5">
                  <div
                    className="h-full bg-emerald-500 rounded-full transition-all duration-500"
                    style={{ width: `${completionRate}%` }}
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Filter and Search Controls Bar */}
        <div className="space-y-3">
          <div className="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 p-3 rounded-xl bg-zinc-900/80 border border-zinc-800 shadow-md">
            {/* Search Input */}
            <div className="relative flex-1 min-w-[240px]">
              <Search className="absolute left-3 top-2.5 h-4 w-4 text-zinc-400" />
              <input
                type="text"
                placeholder="Search projects by title, stack, tags..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-zinc-950 border border-zinc-800 rounded-lg pl-9 pr-8 py-2 text-xs sm:text-sm text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-zinc-700 transition-colors"
              />
              {searchQuery && (
                <button
                  onClick={() => setSearchQuery("")}
                  className="absolute right-2.5 top-2.5 text-zinc-500 hover:text-zinc-300"
                >
                  <X className="w-4 h-4" />
                </button>
              )}
            </div>

            {/* Dropdown Filters & View Switcher */}
            <div className="flex flex-wrap items-center gap-2">
              {/* Workspace Filter */}
              <select
                value={workspaceFilter}
                onChange={(e) => setWorkspaceFilter(e.target.value)}
                className="bg-zinc-950 border border-zinc-800 rounded-lg px-2.5 py-2 text-xs text-zinc-300 focus:outline-none focus:border-zinc-700 cursor-pointer"
              >
                <option value="all">All Workspaces</option>
                <option value="personal">Personal Space</option>
                {companies.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name}
                  </option>
                ))}
              </select>

              {/* Priority Filter */}
              <select
                value={priorityFilter}
                onChange={(e) => setPriorityFilter(e.target.value)}
                className="bg-zinc-950 border border-zinc-800 rounded-lg px-2.5 py-2 text-xs text-zinc-300 focus:outline-none focus:border-zinc-700 cursor-pointer"
              >
                <option value="all">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>

              {/* Sort By */}
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
                className="bg-zinc-950 border border-zinc-800 rounded-lg px-2.5 py-2 text-xs text-zinc-300 focus:outline-none focus:border-zinc-700 cursor-pointer"
              >
                <option value="recent">Sort: Newest</option>
                <option value="name">Sort: Name (A-Z)</option>
                <option value="progress">Sort: Progress (%)</option>
                <option value="tasks">Sort: Task Count</option>
              </select>

              {/* Grid / Table Toggle */}
              <div className="flex items-center bg-zinc-950 p-0.5 rounded-lg border border-zinc-800">
                <button
                  type="button"
                  onClick={() => setViewMode("grid")}
                  className={`p-1.5 rounded-md transition-colors ${viewMode === "grid" ? "bg-zinc-800 text-zinc-100" : "text-zinc-500 hover:text-zinc-300"}`}
                  title="Grid View"
                >
                  <Grid className="h-4 w-4" />
                </button>
                <button
                  type="button"
                  onClick={() => setViewMode("table")}
                  className={`p-1.5 rounded-md transition-colors ${viewMode === "table" ? "bg-zinc-800 text-zinc-100" : "text-zinc-500 hover:text-zinc-300"}`}
                  title="Table View"
                >
                  <List className="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>

          {/* Status Tab Pills */}
          <div className="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-medium">
            {[
              { id: "all", label: "All Projects", count: projectsList.length },
              { id: "in_progress", label: "In Progress", count: inProgressProjects },
              { id: "todo", label: "To Do", count: projectsList.filter((p) => p.status === "To Do" || p.status === "Todo" || p.status_id === 1).length },
              { id: "completed", label: "Completed", count: completedProjects },
              { id: "on_hold", label: "On Hold", count: onHoldProjects },
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setStatusFilter(tab.id)}
                className={`px-3 py-1.5 rounded-lg border transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer ${
                  statusFilter === tab.id
                    ? "bg-zinc-800 border-zinc-700 text-zinc-100 font-semibold shadow-sm"
                    : "bg-zinc-950/60 border-zinc-800/80 text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900"
                }`}
              >
                <span>{tab.label}</span>
                <span className={`px-1.5 py-0.2 rounded-full text-xs font-mono ${statusFilter === tab.id ? "bg-zinc-700 text-zinc-200" : "bg-zinc-900 text-zinc-500"}`}>
                  {tab.count}
                </span>
              </button>
            ))}
          </div>
        </div>

        {/* Content View: Grid or Table */}
        {filteredProjects.length === 0 ? (
          /* Empty State */
          <div className="p-12 text-center rounded-2xl border border-dashed border-zinc-800 bg-zinc-950/40 space-y-4">
            <div className="h-14 w-14 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mx-auto text-zinc-400 shadow-inner">
              <FolderKanban className="w-7 h-7 text-emerald-400" />
            </div>
            <div className="space-y-1">
              <h3 className="text-base font-semibold text-zinc-200">No projects found</h3>
              <p className="text-xs text-zinc-400 max-w-sm mx-auto">
                {searchQuery || statusFilter !== "all" || priorityFilter !== "all" || workspaceFilter !== "all"
                  ? "No initiatives match your selected criteria. Try adjusting your filters or search keywords."
                  : "Get started by creating your first project initiative in this workspace."}
              </p>
            </div>
            <div>
              {searchQuery || statusFilter !== "all" || priorityFilter !== "all" || workspaceFilter !== "all" ? (
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => {
                    setSearchQuery("");
                    setStatusFilter("all");
                    setPriorityFilter("all");
                    setWorkspaceFilter("all");
                  }}
                  className="text-xs border-zinc-800 hover:bg-zinc-900"
                >
                  Clear Filters
                </Button>
              ) : (
                <Button
                  size="sm"
                  onClick={handleOpenCreate}
                  className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold text-xs gap-2 rounded-xl"
                >
                  <Plus className="w-4 h-4" /> Create Your First Project
                </Button>
              )}
            </div>
          </div>
        ) : viewMode === "grid" ? (
          /* Grid View Cards */
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            {filteredProjects.map((p) => {
              const themeColor = p.theme || "#10b981";
              const isCompleted = p.status === "Completed" || p.status_id === 3;
              const isOnHold = p.status === "On Hold" || p.status_id === 4;

              return (
                <div
                  key={p.id}
                  className="group relative rounded-2xl border border-zinc-800 bg-zinc-900/90 hover:border-zinc-700 transition-all duration-200 shadow-xl flex flex-col justify-between overflow-hidden"
                  style={{ borderTop: `3px solid ${themeColor}` }}
                >
                  <div className="p-5 space-y-3.5">
                    {/* Top Row: Workspace Badge, Status, Dropdown Menu */}
                    <div className="flex items-center justify-between gap-2">
                      <div className="flex items-center gap-1.5 min-w-0">
                        <span className="font-mono text-xs bg-zinc-950 text-zinc-300 px-2 py-0.5 rounded border border-zinc-800 truncate flex items-center gap-1">
                          <Building2 className="w-3 h-3 text-zinc-500" />
                          <span className="truncate">{p.category || "Personal"}</span>
                        </span>

                        <span
                          className={`px-2 py-0.5 rounded-full text-xs font-mono font-semibold ${
                            isCompleted
                              ? "bg-emerald-500/15 text-emerald-400 border border-emerald-500/30"
                              : isOnHold
                              ? "bg-amber-500/15 text-amber-400 border border-amber-500/30"
                              : "bg-blue-500/15 text-blue-400 border border-blue-500/30"
                          }`}
                        >
                          {p.status}
                        </span>
                      </div>

                      {/* Dropdown 3-dots Menu */}
                      <div className="relative" onClick={(e) => e.stopPropagation()}>
                        <button
                          type="button"
                          onClick={() => setActiveDropdown(activeDropdown === p.id ? null : p.id)}
                          className="p-1 rounded-md text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition-colors cursor-pointer"
                        >
                          <MoreVertical className="w-4 h-4" />
                        </button>

                        {activeDropdown === p.id && (
                          <div className="absolute right-0 mt-1 w-44 bg-zinc-900 border border-zinc-800 rounded-xl shadow-2xl z-30 p-1 space-y-0.5 text-xs font-sans">
                            <a
                              href={`/projects/${p.id}`}
                              className="flex items-center gap-2 p-2 rounded-lg text-zinc-300 hover:bg-zinc-800 hover:text-zinc-100 transition-colors"
                            >
                              <ExternalLink className="w-3.5 h-3.5 text-emerald-400" />
                              <span>View Project</span>
                            </a>
                            <a
                              href={`/projects/${p.id}/notes`}
                              className="flex items-center gap-2 p-2 rounded-lg text-zinc-300 hover:bg-zinc-800 hover:text-zinc-100 transition-colors"
                            >
                              <FileText className="w-3.5 h-3.5 text-blue-400" />
                              <span>Project Notes</span>
                            </a>
                            <a
                              href={`/projects/${p.id}/credentials`}
                              className="flex items-center gap-2 p-2 rounded-lg text-zinc-300 hover:bg-zinc-800 hover:text-zinc-100 transition-colors"
                            >
                              <Key className="w-3.5 h-3.5 text-amber-400" />
                              <span>Credentials</span>
                            </a>
                            
                            {p.can_edit && (
                              <button
                                type="button"
                                onClick={(e) => handleOpenEdit(p, e)}
                                className="w-full flex items-center gap-2 p-2 rounded-lg text-zinc-300 hover:bg-zinc-800 hover:text-zinc-100 transition-colors text-left cursor-pointer"
                              >
                                <Edit2 className="w-3.5 h-3.5 text-indigo-400" />
                                <span>Edit Project</span>
                              </button>
                            )}

                            {p.can_delete && (
                              <button
                                type="button"
                                onClick={(e) => {
                                  e.stopPropagation();
                                  setActiveDropdown(null);
                                  setDeletingProject(p);
                                }}
                                className="w-full flex items-center gap-2 p-2 rounded-lg text-rose-400 hover:bg-rose-500/10 transition-colors text-left cursor-pointer"
                              >
                                <Trash2 className="w-3.5 h-3.5" />
                                <span>Delete Project</span>
                              </button>
                            )}
                          </div>
                        )}
                      </div>
                    </div>

                    {/* Title and Description */}
                    <div>
                      <a
                        href={`/projects/${p.id}`}
                        className="font-bold text-base sm:text-lg text-white hover:text-emerald-400 transition-colors flex items-center gap-1.5 group/title"
                      >
                        <span className="truncate">{p.name}</span>
                        <ChevronRight className="w-4 h-4 text-zinc-500 opacity-0 group-hover/title:opacity-100 group-hover/title:translate-x-0.5 transition-all shrink-0" />
                      </a>
                      <p className="text-xs text-zinc-400 leading-relaxed line-clamp-2 mt-1">
                        {p.description}
                      </p>
                    </div>

                    {/* Priority and Stack */}
                    <div className="flex items-center gap-2 pt-1">
                      <span className="text-xs font-mono text-zinc-400">Priority:</span>
                      <span
                        className={`px-2 py-0.2 rounded text-xs font-mono font-medium ${
                          p.priority === "Urgent"
                            ? "bg-rose-500/15 text-rose-400 border border-rose-500/30"
                            : p.priority === "High"
                            ? "bg-amber-500/15 text-amber-400 border border-amber-500/30"
                            : p.priority === "Medium"
                            ? "bg-blue-500/15 text-blue-400 border border-blue-500/30"
                            : "bg-zinc-800 text-zinc-300 border border-zinc-700"
                        }`}
                      >
                        {p.priority}
                      </span>
                    </div>
                  </div>

                  {/* Card Footer: Progress, Due Date, Team */}
                  <div className="p-5 pt-0 space-y-3.5">
                    {/* Progress Bar */}
                    <div className="space-y-1.5">
                      <div className="flex items-center justify-between text-xs font-mono">
                        <span className="text-zinc-400">Milestone Progress</span>
                        <span className="text-zinc-200 font-semibold">{p.progress}%</span>
                      </div>
                      <div className="h-2 w-full bg-zinc-950 rounded-full overflow-hidden border border-zinc-800/80">
                        <div
                          className="h-full rounded-full transition-all duration-500"
                          style={{
                            width: `${p.progress}%`,
                            backgroundColor: isCompleted ? "#10b981" : themeColor,
                          }}
                        />
                      </div>
                    </div>

                    {/* Bottom Metadata: Deadline & Team Members */}
                    <div className="flex items-center justify-between border-t border-zinc-800/80 pt-3 text-xs font-mono text-zinc-400">
                      <div className="flex items-center gap-1.5 text-zinc-400">
                        <Clock className="h-3.5 w-3.5 text-zinc-500 shrink-0" />
                        <span className="truncate">{p.dueDate || "No deadline"}</span>
                      </div>

                      <div className="flex items-center gap-2">
                        <span className="text-zinc-400 font-mono text-xs">
                          {p.completedTasks}/{p.totalTasks} tasks
                        </span>

                        {p.teamMembers && p.teamMembers.length > 0 && (
                          <div className="flex items-center -space-x-1.5">
                            {p.teamMembers.slice(0, 3).map((m, idx) => (
                              <Avatar key={idx} className="h-6 w-6 border border-zinc-800 ring-1 ring-zinc-700">
                                {m.profile_image && (
                                  <AvatarImage src={m.profile_image} alt={m.name} className="object-cover" />
                                )}
                                <AvatarFallback className="bg-zinc-800 text-zinc-300 text-xs font-bold font-mono">
                                  {m.avatar || "U"}
                                </AvatarFallback>
                              </Avatar>
                            ))}
                            {p.teamMembers.length > 3 && (
                              <div className="h-6 w-6 rounded-full bg-zinc-800 border border-zinc-700 flex items-center justify-center text-xs font-mono text-zinc-400">
                                +{p.teamMembers.length - 3}
                              </div>
                            )}
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          /* Table View */
          <div className="border border-zinc-800 bg-zinc-900/90 rounded-2xl overflow-hidden shadow-2xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs sm:text-sm font-sans">
                <thead>
                  <tr className="border-b border-zinc-800 bg-zinc-950/80 text-zinc-400 font-mono text-xs uppercase tracking-wider">
                    <th className="py-3.5 px-4">Project</th>
                    <th className="py-3.5 px-4">Workspace</th>
                    <th className="py-3.5 px-4">Status</th>
                    <th className="py-3.5 px-4">Priority</th>
                    <th className="py-3.5 px-4">Progress</th>
                    <th className="py-3.5 px-4">Tasks</th>
                    <th className="py-3.5 px-4">Due Date</th>
                    <th className="py-3.5 px-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-zinc-800/60">
                  {filteredProjects.map((p) => {
                    const isCompleted = p.status === "Completed" || p.status_id === 3;
                    const isOnHold = p.status === "On Hold" || p.status_id === 4;

                    return (
                      <tr key={p.id} className="hover:bg-zinc-800/40 transition-colors">
                        <td className="py-3.5 px-4">
                          <div className="flex items-center gap-2.5">
                            <div
                              className="h-3 w-3 rounded-full shrink-0"
                              style={{ backgroundColor: p.theme || "#10b981" }}
                            />
                            <div>
                              <a
                                href={`/projects/${p.id}`}
                                className="font-semibold text-white hover:text-emerald-400 transition-colors block"
                              >
                                {p.name}
                              </a>
                              <p className="text-xs text-zinc-400 truncate max-w-xs">{p.description}</p>
                            </div>
                          </div>
                        </td>

                        <td className="py-3.5 px-4">
                          <span className="font-mono text-xs bg-zinc-950 text-zinc-300 px-2 py-0.5 rounded border border-zinc-800">
                            {p.category || "Personal"}
                          </span>
                        </td>

                        <td className="py-3.5 px-4">
                          <span
                            className={`px-2 py-0.5 rounded-full text-xs font-mono font-semibold ${
                              isCompleted
                                ? "bg-emerald-500/15 text-emerald-400 border border-emerald-500/30"
                                : isOnHold
                                ? "bg-amber-500/15 text-amber-400 border border-amber-500/30"
                                : "bg-blue-500/15 text-blue-400 border border-blue-500/30"
                            }`}
                          >
                            {p.status}
                          </span>
                        </td>

                        <td className="py-3.5 px-4">
                          <span
                            className={`px-2 py-0.2 rounded text-xs font-mono font-medium ${
                              p.priority === "Urgent"
                                ? "bg-rose-500/15 text-rose-400 border border-rose-500/30"
                                : p.priority === "High"
                                ? "bg-amber-500/15 text-amber-400 border border-amber-500/30"
                                : p.priority === "Medium"
                                ? "bg-blue-500/15 text-blue-400 border border-blue-500/30"
                                : "bg-zinc-800 text-zinc-300 border border-zinc-700"
                            }`}
                          >
                            {p.priority}
                          </span>
                        </td>

                        <td className="py-3.5 px-4">
                          <div className="flex items-center gap-2 min-w-[100px]">
                            <div className="h-1.5 w-16 bg-zinc-800 rounded-full overflow-hidden">
                              <div
                                className="h-full bg-emerald-500 rounded-full"
                                style={{ width: `${p.progress}%` }}
                              />
                            </div>
                            <span className="font-mono text-xs text-zinc-200">{p.progress}%</span>
                          </div>
                        </td>

                        <td className="py-3.5 px-4 font-mono text-xs text-zinc-300">
                          {p.completedTasks}/{p.totalTasks}
                        </td>

                        <td className="py-3.5 px-4 font-mono text-xs text-zinc-400">
                          {p.dueDate || "None"}
                        </td>

                        <td className="py-3.5 px-4 text-right">
                          <div className="flex items-center justify-end gap-1.5">
                            <a
                              href={`/projects/${p.id}`}
                              className="p-1.5 rounded-lg text-zinc-400 hover:text-emerald-400 hover:bg-zinc-800 transition-colors"
                              title="View Project"
                            >
                              <ExternalLink className="w-4 h-4" />
                            </a>
                            {p.can_edit && (
                              <button
                                type="button"
                                onClick={(e) => handleOpenEdit(p, e)}
                                className="p-1.5 rounded-lg text-zinc-400 hover:text-indigo-400 hover:bg-zinc-800 transition-colors cursor-pointer"
                                title="Edit Project"
                              >
                                <Edit2 className="w-4 h-4" />
                              </button>
                            )}
                            {p.can_delete && (
                              <button
                                type="button"
                                onClick={() => setDeletingProject(p)}
                                className="p-1.5 rounded-lg text-zinc-400 hover:text-rose-400 hover:bg-zinc-800 transition-colors cursor-pointer"
                                title="Delete Project"
                              >
                                <Trash2 className="w-4 h-4" />
                              </button>
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

        {/* Modal: Create Project Initiative */}
        {showCreateModal && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex items-center justify-between p-5 border-b border-zinc-800 bg-zinc-950/60">
                <div className="flex items-center gap-2.5">
                  <div className="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    <FolderKanban className="w-4 h-4" />
                  </div>
                  <div>
                    <h3 className="font-bold text-zinc-100 text-sm sm:text-base">New Project Initiative</h3>
                    <p className="text-xs text-zinc-400 font-mono">Create and classify workspace project</p>
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
                {/* Project Name */}
                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Project Name <span className="text-rose-400">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. WorkHub Mobile App"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 transition-colors"
                  />
                  {formErrors.name && (
                    <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                      <AlertCircle className="w-3 h-3" /> {formErrors.name}
                    </p>
                  )}
                </div>

                {/* Description */}
                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Description <span className="text-rose-400">*</span>
                  </label>
                  <textarea
                    required
                    rows={3}
                    placeholder="Briefly describe the milestones and objectives..."
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 transition-colors resize-none"
                  />
                  {formErrors.description && (
                    <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                      <AlertCircle className="w-3 h-3" /> {formErrors.description}
                    </p>
                  )}
                </div>

                {/* Workspace / Company Selection */}
                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Workspace Destination
                  </label>
                  <select
                    value={formData.company_id}
                    onChange={(e) => setFormData({ ...formData, company_id: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-200 focus:outline-none focus:border-emerald-500/60 cursor-pointer"
                  >
                    <option value="personal">Personal Workspace (Only you)</option>
                    {companies.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name} (Organization)
                      </option>
                    ))}
                  </select>
                </div>

                {/* Status & Priority Row */}
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Status
                    </label>
                    <select
                      value={formData.status}
                      onChange={(e) => setFormData({ ...formData, status: Number(e.target.value) })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-zinc-200 focus:outline-none focus:border-emerald-500/60 cursor-pointer"
                    >
                      <option value={1}>1: To Do</option>
                      <option value={2}>2: In Progress</option>
                      <option value={3}>3: Completed</option>
                      <option value={4}>4: On Hold</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Priority
                    </label>
                    <select
                      value={formData.priority}
                      onChange={(e) => setFormData({ ...formData, priority: Number(e.target.value) })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-zinc-200 focus:outline-none focus:border-emerald-500/60 cursor-pointer"
                    >
                      <option value={1}>1: Low</option>
                      <option value={2}>2: Medium</option>
                      <option value={3}>3: High</option>
                      <option value={4}>4: Urgent</option>
                    </select>
                  </div>
                </div>

                {/* Theme Accent Color */}
                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-2">
                    Theme Accent Color
                  </label>
                  <div className="flex items-center gap-2.5 flex-wrap">
                    {THEME_COLORS.map((color) => (
                      <button
                        key={color.hex}
                        type="button"
                        onClick={() => setFormData({ ...formData, theme: color.hex })}
                        className={`h-7 w-7 rounded-full transition-transform flex items-center justify-center cursor-pointer ${
                          formData.theme === color.hex ? "scale-125 ring-2 ring-white ring-offset-2 ring-offset-zinc-950" : "hover:scale-110"
                        }`}
                        style={{ backgroundColor: color.hex }}
                        title={color.name}
                      >
                        {formData.theme === color.hex && <Check className="w-3.5 h-3.5 text-zinc-950 stroke-[3]" />}
                      </button>
                    ))}
                  </div>
                </div>

                {/* Submit Actions */}
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
                    disabled={isSubmitting}
                    className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold px-4"
                  >
                    {isSubmitting ? (
                      <span className="flex items-center gap-2">
                        <Loader2 className="w-3.5 h-3.5 animate-spin" /> Saving...
                      </span>
                    ) : (
                      "Create Project"
                    )}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Modal: Edit Project */}
        {editingProject && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex items-center justify-between p-5 border-b border-zinc-800 bg-zinc-950/60">
                <div className="flex items-center gap-2.5">
                  <div className="p-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                    <Edit2 className="w-4 h-4" />
                  </div>
                  <div>
                    <h3 className="font-bold text-zinc-100 text-sm sm:text-base">Edit Project Initiative</h3>
                    <p className="text-xs text-zinc-400 font-mono">Update status, priority, and metadata</p>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setEditingProject(null)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleEditSubmit} className="p-5 space-y-4 text-xs sm:text-sm">
                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Project Name <span className="text-rose-400">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-indigo-500/60 transition-colors"
                  />
                  {formErrors.name && (
                    <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                      <AlertCircle className="w-3 h-3" /> {formErrors.name}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Description <span className="text-rose-400">*</span>
                  </label>
                  <textarea
                    required
                    rows={3}
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-indigo-500/60 transition-colors resize-none"
                  />
                  {formErrors.description && (
                    <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                      <AlertCircle className="w-3 h-3" /> {formErrors.description}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                    Workspace Destination
                  </label>
                  <select
                    value={formData.company_id}
                    onChange={(e) => setFormData({ ...formData, company_id: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-zinc-200 focus:outline-none focus:border-indigo-500/60 cursor-pointer"
                  >
                    <option value="personal">Personal Workspace</option>
                    {companies.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Status
                    </label>
                    <select
                      value={formData.status}
                      onChange={(e) => setFormData({ ...formData, status: Number(e.target.value) })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-zinc-200 focus:outline-none focus:border-indigo-500/60 cursor-pointer"
                    >
                      <option value={1}>1: To Do</option>
                      <option value={2}>2: In Progress</option>
                      <option value={3}>3: Completed</option>
                      <option value={4}>4: On Hold</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Priority
                    </label>
                    <select
                      value={formData.priority}
                      onChange={(e) => setFormData({ ...formData, priority: Number(e.target.value) })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-zinc-200 focus:outline-none focus:border-indigo-500/60 cursor-pointer"
                    >
                      <option value={1}>1: Low</option>
                      <option value={2}>2: Medium</option>
                      <option value={3}>3: High</option>
                      <option value={4}>4: Urgent</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-mono font-medium text-zinc-300 mb-2">
                    Theme Accent Color
                  </label>
                  <div className="flex items-center gap-2.5 flex-wrap">
                    {THEME_COLORS.map((color) => (
                      <button
                        key={color.hex}
                        type="button"
                        onClick={() => setFormData({ ...formData, theme: color.hex })}
                        className={`h-7 w-7 rounded-full transition-transform flex items-center justify-center cursor-pointer ${
                          formData.theme === color.hex ? "scale-125 ring-2 ring-white ring-offset-2 ring-offset-zinc-950" : "hover:scale-110"
                        }`}
                        style={{ backgroundColor: color.hex }}
                        title={color.name}
                      >
                        {formData.theme === color.hex && <Check className="w-3.5 h-3.5 text-zinc-950 stroke-[3]" />}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={isSubmitting}
                    onClick={() => setEditingProject(null)}
                    className="border-zinc-800 text-zinc-300 hover:bg-zinc-800"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    disabled={isSubmitting}
                    className="bg-indigo-500 hover:bg-indigo-600 text-white font-bold px-4"
                  >
                    {isSubmitting ? (
                      <span className="flex items-center gap-2">
                        <Loader2 className="w-3.5 h-3.5 animate-spin" /> Saving...
                      </span>
                    ) : (
                      "Update Project"
                    )}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Modal: Delete Project Confirmation */}
        {deletingProject && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="h-12 w-12 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                <AlertTriangle className="w-6 h-6" />
              </div>
              <div className="space-y-1">
                <h3 className="text-base font-bold text-white">Delete Project</h3>
                <p className="text-xs text-zinc-400 leading-relaxed">
                  Are you sure you want to delete <span className="text-zinc-200 font-semibold font-mono">"{deletingProject.name}"</span>?
                  This action will move the project and its associated task records to trash.
                </p>
              </div>
              <div className="flex items-center justify-end gap-2.5 pt-2">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  disabled={isSubmitting}
                  onClick={() => setDeletingProject(null)}
                  className="border-zinc-800 text-zinc-300 hover:bg-zinc-800"
                >
                  Cancel
                </Button>
                <Button
                  type="button"
                  size="sm"
                  disabled={isSubmitting}
                  onClick={handleDeleteConfirm}
                  className="bg-rose-500 hover:bg-rose-600 text-white font-bold px-4"
                >
                  {isSubmitting ? (
                    <span className="flex items-center gap-2">
                      <Loader2 className="w-3.5 h-3.5 animate-spin" /> Deleting...
                    </span>
                  ) : (
                    "Confirm Delete"
                  )}
                </Button>
              </div>
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
