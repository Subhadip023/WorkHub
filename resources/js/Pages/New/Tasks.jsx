import React, { useState, useEffect, useRef } from "react";
import { Link, router, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  CheckSquare,
  Square,
  Plus,
  Search,
  Kanban,
  List,
  Clock,
  Crown,
  AlertCircle,
  AlertTriangle,
  Sparkles,
  TrendingUp,
  CheckCircle2,
  MoreVertical,
  Edit2,
  Trash2,
  Copy,
  Calendar,
  Building2,
  User,
  X,
  Loader2,
  ArrowUpDown,
  Filter,
  Layers,
  ExternalLink,
  RefreshCw,
  Check,
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Progress } from "@/Components/ui/progress";

const KANBAN_COLUMNS = [
  { id: 1, key: "To Do", label: "To Do", dot: "bg-sky-400", border: "border-sky-500/20", glow: "hover:border-sky-500/40" },
  { id: 2, key: "In Progress", label: "In Progress", dot: "bg-blue-400", border: "border-blue-500/20", glow: "hover:border-blue-500/40" },
  { id: 4, key: "Review", label: "Review & QA", dot: "bg-purple-400", border: "border-purple-500/20", glow: "hover:border-purple-500/40" },
  { id: 3, key: "Done", label: "Done", dot: "bg-emerald-400", border: "border-emerald-500/20", glow: "hover:border-emerald-500/40" },
];

export default function Tasks({
  initial_tasks = [],
  projects = [],
  company_users = [],
  stats = null,
  filters: serverFilters = {},
  current_workspace_name = "All Workspaces",
}) {
  const { flash = {} } = usePage().props;

  const [tasksList, setTasksList] = useState(initial_tasks);
  const [viewMode, setViewMode] = useState("kanban"); // 'kanban' | 'list'
  const [filterTab, setFilterTab] = useState("all"); // 'all' | 'todo' | 'in_progress' | 'review' | 'completed' | 'urgent' | 'overdue'
  const [searchQuery, setSearchQuery] = useState("");
  const [projectFilter, setProjectFilter] = useState("all");
  const [assigneeFilter, setAssigneeFilter] = useState("all");
  const [priorityFilter, setPriorityFilter] = useState("all");
  const [typeFilter, setTypeFilter] = useState("all");
  const [sortBy, setSortBy] = useState("default");

  // Modals & Popovers
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [editingTask, setEditingTask] = useState(null);
  const [deletingTask, setDeletingTask] = useState(null);
  const [activeDropdown, setActiveDropdown] = useState(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formErrors, setFormErrors] = useState({});

  // Form State
  const initialFormState = {
    title: "",
    description: "",
    project_id: "personal",
    assigned_to: "unassigned",
    status: 1, // 1: To Do, 2: In Progress, 3: Completed, 4: Review
    priority: 2, // 1: Low, 2: Medium, 3: High, 4: Urgent
    type: 1, // 1: Task, 2: Bug, 3: Feature, 4: Improvement
    due_date: "",
    points: "",
  };
  const [formData, setFormData] = useState(initialFormState);

  // Sync tasks when server props change
  useEffect(() => {
    if (initial_tasks) {
      setTasksList(initial_tasks);
    }
  }, [initial_tasks]);

  // Close card menus on outside click
  useEffect(() => {
    const handleOutside = () => setActiveDropdown(null);
    window.addEventListener("click", handleOutside);
    return () => window.removeEventListener("click", handleOutside);
  }, []);

  // Keyboard shortcut: Press 'C' or 'Alt+T' to create task
  useEffect(() => {
    const handleKeyDown = (e) => {
      if (
        (e.key === "c" || e.key === "C") &&
        !["INPUT", "TEXTAREA", "SELECT"].includes(document.activeElement?.tagName)
      ) {
        e.preventDefault();
        setShowCreateModal(true);
      } else if (e.altKey && (e.key === "t" || e.key === "T")) {
        e.preventDefault();
        setShowCreateModal(true);
      }
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, []);

  // Form Handlers
  const handleOpenCreateModal = (defaultStatus = 1) => {
    setFormData({
      ...initialFormState,
      status: defaultStatus,
    });
    setFormErrors({});
    setShowCreateModal(true);
  };

  const handleOpenEditModal = (task) => {
    setEditingTask(task);
    setFormData({
      title: task.title,
      description: task.raw_description || task.description || "",
      project_id: task.project_id ? String(task.project_id) : "personal",
      assigned_to: task.assignee?.id ? String(task.assignee.id) : "unassigned",
      status: task.status_id || 1,
      priority: task.priority_id || 2,
      type: task.type_id || 1,
      due_date: task.due_date_raw || "",
      points: task.points !== null && task.points !== undefined ? String(task.points) : "",
    });
    setFormErrors({});
  };

  const handleCreateSubmit = (e) => {
    e.preventDefault();
    if (!formData.title.trim()) {
      setFormErrors({ title: "Task title is required." });
      return;
    }

    setIsSubmitting(true);
    setFormErrors({});

    const payload = {
      title: formData.title.trim(),
      description: formData.description?.trim() || null,
      project_id: formData.project_id === "personal" || !formData.project_id ? null : Number(formData.project_id),
      assigned_to: formData.assigned_to === "unassigned" || !formData.assigned_to ? null : Number(formData.assigned_to),
      status: Number(formData.status),
      priority: Number(formData.priority),
      type: Number(formData.type),
      due_date: formData.due_date || null,
      points: formData.points ? Number(formData.points) : null,
    };

    router.post("/tasks", payload, {
      preserveScroll: true,
      onSuccess: () => {
        setShowCreateModal(false);
        setFormData(initialFormState);
        setIsSubmitting(false);
      },
      onError: (errs) => {
        setFormErrors(errs);
        setIsSubmitting(false);
      },
    });
  };

  const handleEditSubmit = (e) => {
    e.preventDefault();
    if (!formData.title.trim()) {
      setFormErrors({ title: "Task title is required." });
      return;
    }

    setIsSubmitting(true);
    setFormErrors({});

    const payload = {
      title: formData.title.trim(),
      description: formData.description?.trim() || null,
      project_id: formData.project_id === "personal" || !formData.project_id ? null : Number(formData.project_id),
      assigned_to: formData.assigned_to === "unassigned" || !formData.assigned_to ? null : Number(formData.assigned_to),
      status: Number(formData.status),
      priority: Number(formData.priority),
      type: Number(formData.type),
      due_date: formData.due_date || null,
      points: formData.points ? Number(formData.points) : null,
    };

    router.patch(`/tasks/${editingTask.db_id}`, payload, {
      preserveScroll: true,
      onSuccess: () => {
        setEditingTask(null);
        setIsSubmitting(false);
      },
      onError: (errs) => {
        setFormErrors(errs);
        setIsSubmitting(false);
      },
    });
  };

  const handleDeleteSubmit = () => {
    if (!deletingTask) return;
    setIsSubmitting(true);

    router.delete(`/tasks/${deletingTask.db_id}`, {
      preserveScroll: true,
      onSuccess: () => {
        setDeletingTask(null);
        setIsSubmitting(false);
      },
      onError: () => {
        setIsSubmitting(false);
      },
    });
  };

  // Optimistic Toggle Task Status
  const handleToggleTask = (task) => {
    const nextCompleted = !task.completed;
    const nextStatus = nextCompleted ? "Done" : "In Progress";
    const nextStatusId = nextCompleted ? 3 : 2;

    setTasksList((prev) =>
      prev.map((t) =>
        t.db_id === task.db_id
          ? {
              ...t,
              completed: nextCompleted,
              status: nextStatus,
              status_id: nextStatusId,
            }
          : t
      )
    );

    router.patch(
      `/tasks/${task.db_id}/toggle`,
      {},
      {
        preserveScroll: true,
        onError: () => {
          setTasksList(initial_tasks);
        },
      }
    );
  };

  // Quick Status Change on Kanban Card
  const handleQuickStatusChange = (task, newStatusId, newStatusName) => {
    setTasksList((prev) =>
      prev.map((t) =>
        t.db_id === task.db_id
          ? {
              ...t,
              status: newStatusName,
              status_id: newStatusId,
              completed: newStatusId === 3,
            }
          : t
      )
    );

    router.patch(
      `/tasks/${task.db_id}`,
      { status: newStatusId },
      {
        preserveScroll: true,
        onError: () => {
          setTasksList(initial_tasks);
        },
      }
    );
  };

  // Duplicate / Copy Task
  const handleCopyTask = (task) => {
    router.post(
      `/tasks/${task.db_id}/copy`,
      {},
      {
        preserveScroll: true,
      }
    );
  };

  // Computed Metrics
  const calculatedStats = {
    total: stats?.total ?? tasksList.length,
    pending: stats?.pending ?? tasksList.filter((t) => !t.completed).length,
    urgent: stats?.urgent ?? tasksList.filter((t) => t.priority === "Urgent" && !t.completed).length,
    completed: stats?.completed ?? tasksList.filter((t) => t.completed).length,
    overdue: stats?.overdue ?? tasksList.filter((t) => t.is_overdue).length,
  };

  const completionRate =
    calculatedStats.total > 0
      ? Math.round((calculatedStats.completed / calculatedStats.total) * 100)
      : 0;

  // Filter Tasks
  const filteredTasks = tasksList.filter((t) => {
    const q = searchQuery.toLowerCase().trim();
    const matchesSearch =
      !q ||
      t.title.toLowerCase().includes(q) ||
      (t.project && t.project.toLowerCase().includes(q)) ||
      (t.id && t.id.toLowerCase().includes(q)) ||
      (t.description && t.description.toLowerCase().includes(q));

    if (!matchesSearch) return false;

    // Filter Tab
    if (filterTab === "todo" && t.status_id !== 1) return false;
    if (filterTab === "in_progress" && t.status_id !== 2) return false;
    if (filterTab === "review" && t.status_id !== 4) return false;
    if (filterTab === "completed" && !t.completed) return false;
    if (filterTab === "urgent" && (t.priority !== "Urgent" || t.completed)) return false;
    if (filterTab === "overdue" && (!t.is_overdue || t.completed)) return false;

    // Project Dropdown
    if (projectFilter === "personal" && t.project_id !== null) return false;
    if (projectFilter !== "all" && projectFilter !== "personal" && String(t.project_id) !== String(projectFilter))
      return false;

    // Assignee Dropdown
    if (assigneeFilter === "unassigned" && t.assignee?.id) return false;
    if (assigneeFilter !== "all" && assigneeFilter !== "unassigned" && String(t.assignee?.id) !== String(assigneeFilter))
      return false;

    // Priority Dropdown
    if (priorityFilter !== "all" && t.priority.toLowerCase() !== priorityFilter.toLowerCase())
      return false;

    // Type Dropdown
    if (typeFilter !== "all" && t.type.toLowerCase() !== typeFilter.toLowerCase())
      return false;

    return true;
  });

  // Sort Tasks
  const sortedTasks = [...filteredTasks].sort((a, b) => {
    if (sortBy === "priority") {
      return (b.priority_id || 0) - (a.priority_id || 0);
    }
    if (sortBy === "title") {
      return a.title.localeCompare(b.title);
    }
    if (sortBy === "due_date") {
      if (!a.due_date_raw) return 1;
      if (!b.due_date_raw) return -1;
      return a.due_date_raw.localeCompare(b.due_date_raw);
    }
    return (b.db_id || 0) - (a.db_id || 0);
  });

  const getPriorityBadge = (priority) => {
    switch (priority) {
      case "Urgent":
        return "bg-rose-500/10 text-rose-400 border-rose-500/30";
      case "High":
        return "bg-amber-500/10 text-amber-400 border-amber-500/30";
      case "Medium":
        return "bg-blue-500/10 text-blue-400 border-blue-500/30";
      default:
        return "bg-zinc-800 text-zinc-400 border-zinc-700/60";
    }
  };

  const getTypeBadge = (type) => {
    switch (type) {
      case "Bug":
        return {
          icon: <AlertCircle className="w-3 h-3 text-rose-400" />,
          classes: "bg-rose-500/10 text-rose-300 border-rose-500/20",
        };
      case "Feature":
        return {
          icon: <Sparkles className="w-3 h-3 text-emerald-400" />,
          classes: "bg-emerald-500/10 text-emerald-300 border-emerald-500/20",
        };
      case "Improvement":
        return {
          icon: <TrendingUp className="w-3 h-3 text-cyan-400" />,
          classes: "bg-cyan-500/10 text-cyan-300 border-cyan-500/20",
        };
      default:
        return {
          icon: <CheckSquare className="w-3 h-3 text-zinc-400" />,
          classes: "bg-zinc-800/80 text-zinc-300 border-zinc-700/60",
        };
    }
  };

  const headerActions = (
    <div className="flex items-center gap-2.5">
      <Button
        size="sm"
        onClick={() => router.reload({ preserveScroll: true })}
        variant="outline"
        className="border-zinc-800 bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 text-xs h-9 px-3 gap-1.5 rounded-xl hidden sm:inline-flex"
        title="Refresh Tasks"
      >
        <RefreshCw className="h-3.5 w-3.5" />
      </Button>

      <Button
        size="sm"
        onClick={() => handleOpenCreateModal(1)}
        className="bg-emerald-500 hover:bg-emerald-600 text-black font-semibold text-xs sm:text-sm gap-2 transition-all rounded-xl h-9 px-4 shadow-lg shadow-emerald-500/20"
      >
        <Plus className="h-4 w-4 stroke-[2.5]" />
        <span>New Task</span>
        <kbd className="hidden md:inline-flex text-[10px] bg-emerald-600/50 text-black px-1.5 py-0.5 rounded font-mono font-bold">
          C
        </kbd>
      </Button>
    </div>
  );

  return (
    <DashboardLayout title="Tasks & Deliverables" activeItem="tasks" actions={headerActions}>
      <div className="space-y-6 pb-12">
        {/* Flash Message Banner */}
        {flash?.success && (
          <div className="flex items-center justify-between p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm animate-in fade-in slide-in-from-top-2">
            <div className="flex items-center gap-2.5">
              <CheckCircle2 className="h-5 w-5 text-emerald-400 shrink-0" />
              <span>{flash.success}</span>
            </div>
          </div>
        )}
        {flash?.error && (
          <div className="flex items-center justify-between p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm animate-in fade-in slide-in-from-top-2">
            <div className="flex items-center gap-2.5">
              <AlertTriangle className="h-5 w-5 text-rose-400 shrink-0" />
              <span>{flash.error}</span>
            </div>
          </div>
        )}

        {/* Hero & Workspace Header */}
        <div className="relative overflow-hidden rounded-2xl border border-zinc-800/80 bg-gradient-to-br from-[#121216] via-[#0d0d10] to-[#09090b] p-6 sm:p-8 shadow-2xl">
          <div className="absolute right-0 top-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-emerald-500/5 blur-3xl pointer-events-none" />
          <div className="absolute left-1/3 bottom-0 -mb-16 h-48 w-48 rounded-full bg-blue-500/5 blur-3xl pointer-events-none" />

          <div className="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div className="space-y-2">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-zinc-800/70 border border-zinc-700/60 text-xs font-mono text-zinc-300">
                <Building2 className="w-3.5 h-3.5 text-emerald-400" />
                <span>{current_workspace_name || "All Workspaces"}</span>
              </div>
              <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2.5">
                Tasks & Deliverables
              </h1>
              <p className="text-xs sm:text-sm text-zinc-400 max-w-xl">
                Sprint status tracking, Kanban workflows, assignee distribution, and priority deliverable boards.
              </p>
            </div>

            {/* Quick Metrics Cards */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full md:w-auto shrink-0 font-mono">
              <div className="p-3.5 rounded-xl bg-zinc-900/90 border border-zinc-800/80 flex flex-col justify-between min-w-[110px]">
                <span className="text-[11px] text-zinc-400 uppercase tracking-wider">Total</span>
                <div className="flex items-baseline justify-between mt-1">
                  <span className="text-xl sm:text-2xl font-bold text-white">{calculatedStats.total}</span>
                  <CheckSquare className="w-4 h-4 text-zinc-500" />
                </div>
              </div>

              <div className="p-3.5 rounded-xl bg-blue-950/20 border border-blue-800/30 flex flex-col justify-between min-w-[110px]">
                <span className="text-[11px] text-blue-300/80 uppercase tracking-wider">In Progress</span>
                <div className="flex items-baseline justify-between mt-1">
                  <span className="text-xl sm:text-2xl font-bold text-blue-400">{calculatedStats.pending}</span>
                  <Clock className="w-4 h-4 text-blue-400/60" />
                </div>
              </div>

              <div className="p-3.5 rounded-xl bg-rose-950/20 border border-rose-800/30 flex flex-col justify-between min-w-[110px]">
                <span className="text-[11px] text-rose-300/80 uppercase tracking-wider">Urgent</span>
                <div className="flex items-baseline justify-between mt-1">
                  <span className="text-xl sm:text-2xl font-bold text-rose-400">{calculatedStats.urgent}</span>
                  <span className="h-2 w-2 rounded-full bg-rose-500 animate-pulse" />
                </div>
              </div>

              <div className="p-3.5 rounded-xl bg-emerald-950/20 border border-emerald-800/30 flex flex-col justify-between min-w-[110px]">
                <span className="text-[11px] text-emerald-300/80 uppercase tracking-wider">Done</span>
                <div className="flex items-baseline justify-between mt-1">
                  <span className="text-xl sm:text-2xl font-bold text-emerald-400">{calculatedStats.completed}</span>
                  <span className="text-xs text-emerald-400/80 font-bold">{completionRate}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Filter and Control Bar */}
        <div className="space-y-3">
          {/* Top Filter Bar: Tabs & Search & View Toggle */}
          <div className="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 bg-[#111114] p-3 sm:p-4 rounded-xl border border-zinc-800/90 shadow-xl">
            {/* Status Tabs */}
            <div className="flex items-center gap-1 overflow-x-auto pb-1 lg:pb-0 scrollbar-none font-mono text-xs">
              {[
                { key: "all", label: "All", count: tasksList.length },
                { key: "todo", label: "To Do", count: tasksList.filter((t) => t.status_id === 1).length },
                { key: "in_progress", label: "In Progress", count: tasksList.filter((t) => t.status_id === 2).length },
                { key: "review", label: "Review", count: tasksList.filter((t) => t.status_id === 4).length },
                { key: "completed", label: "Done", count: tasksList.filter((t) => t.completed).length },
                { key: "urgent", label: "Urgent", count: calculatedStats.urgent },
              ].map((tab) => (
                <button
                  key={tab.key}
                  onClick={() => setFilterTab(tab.key)}
                  className={`px-3 py-1.5 rounded-lg transition-all whitespace-nowrap flex items-center gap-1.5 ${
                    filterTab === tab.key
                      ? "bg-zinc-800 text-white font-semibold border border-zinc-700 shadow-sm"
                      : "text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800/40"
                  }`}
                >
                  <span>{tab.label}</span>
                  <span
                    className={`text-[10px] px-1.5 py-0.2 rounded-full ${
                      filterTab === tab.key
                        ? "bg-zinc-900 text-zinc-200"
                        : "bg-zinc-800/80 text-zinc-500"
                    }`}
                  >
                    {tab.count}
                  </span>
                </button>
              ))}
            </div>

            {/* Search & View Switcher */}
            <div className="flex items-center gap-2.5">
              <div className="relative flex-1 sm:w-72">
                <Search className="absolute left-3 top-2.5 h-4 w-4 text-zinc-500" />
                <input
                  type="text"
                  placeholder="Search task title, #ID, project..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full bg-zinc-900/90 border border-zinc-800 rounded-lg pl-9 pr-8 py-2 text-xs sm:text-sm text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-zinc-600 transition-colors"
                />
                {searchQuery && (
                  <button
                    onClick={() => setSearchQuery("")}
                    className="absolute right-2.5 top-2.5 text-zinc-500 hover:text-zinc-300"
                  >
                    <X className="h-4 w-4" />
                  </button>
                )}
              </div>

              {/* View Switcher: Kanban vs Table */}
              <div className="flex items-center bg-zinc-900 p-1 rounded-lg border border-zinc-800 shrink-0">
                <button
                  onClick={() => setViewMode("kanban")}
                  className={`p-1.5 rounded-md transition-colors ${
                    viewMode === "kanban" ? "bg-zinc-800 text-white" : "text-zinc-500 hover:text-zinc-300"
                  }`}
                  title="Kanban Board View"
                >
                  <Kanban className="h-4 w-4" />
                </button>
                <button
                  onClick={() => setViewMode("list")}
                  className={`p-1.5 rounded-md transition-colors ${
                    viewMode === "list" ? "bg-zinc-800 text-white" : "text-zinc-500 hover:text-zinc-300"
                  }`}
                  title="Table List View"
                >
                  <List className="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>

          {/* Secondary Filter Bar: Project, Assignee, Priority, Type Dropdowns */}
          <div className="flex flex-wrap items-center gap-2.5 bg-[#0f0f12] p-2.5 sm:p-3 rounded-xl border border-zinc-800/60 text-xs font-mono text-zinc-300">
            <div className="flex items-center gap-1.5 text-zinc-500 mr-1">
              <Filter className="w-3.5 h-3.5" />
              <span>Filters:</span>
            </div>

            {/* Project Filter */}
            <select
              value={projectFilter}
              onChange={(e) => setProjectFilter(e.target.value)}
              className="bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-zinc-300 focus:outline-none focus:border-zinc-700"
            >
              <option value="all">All Projects</option>
              <option value="personal">Personal Space (No Project)</option>
              {projects.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.name}
                </option>
              ))}
            </select>

            {/* Assignee Filter */}
            <select
              value={assigneeFilter}
              onChange={(e) => setAssigneeFilter(e.target.value)}
              className="bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-zinc-300 focus:outline-none focus:border-zinc-700"
            >
              <option value="all">All Assignees</option>
              <option value="unassigned">Unassigned</option>
              {company_users.map((u) => (
                <option key={u.id} value={u.id}>
                  {u.name}
                </option>
              ))}
            </select>

            {/* Priority Filter */}
            <select
              value={priorityFilter}
              onChange={(e) => setPriorityFilter(e.target.value)}
              className="bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-zinc-300 focus:outline-none focus:border-zinc-700"
            >
              <option value="all">All Priorities</option>
              <option value="urgent">Urgent</option>
              <option value="high">High</option>
              <option value="medium">Medium</option>
              <option value="low">Low</option>
            </select>

            {/* Type Filter */}
            <select
              value={typeFilter}
              onChange={(e) => setTypeFilter(e.target.value)}
              className="bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-zinc-300 focus:outline-none focus:border-zinc-700"
            >
              <option value="all">All Types</option>
              <option value="task">Task</option>
              <option value="bug">Bug</option>
              <option value="feature">Feature</option>
              <option value="improvement">Improvement</option>
            </select>

            {/* Sort Dropdown */}
            <div className="ml-auto flex items-center gap-1.5">
              <span className="text-zinc-500 text-[11px]">Sort:</span>
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
                className="bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-zinc-300 focus:outline-none focus:border-zinc-700"
              >
                <option value="default">Recently Added</option>
                <option value="due_date">Due Date</option>
                <option value="priority">Priority</option>
                <option value="title">Title</option>
              </select>
            </div>
          </div>
        </div>

        {/* View Rendering */}
        {viewMode === "kanban" ? (
          /* Kanban Board View */
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-start">
            {KANBAN_COLUMNS.map((col) => {
              const colTasks = sortedTasks.filter((t) => t.status_id === col.id);

              return (
                <div
                  key={col.id}
                  className={`rounded-xl border ${col.border} bg-[#111114]/90 flex flex-col min-h-[500px] shadow-xl overflow-hidden transition-all`}
                >
                  {/* Column Header */}
                  <div className="p-3.5 border-b border-zinc-800/80 bg-zinc-900/60 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <span className={`h-2.5 w-2.5 rounded-full ${col.dot}`} />
                      <h3 className="text-xs sm:text-sm font-mono font-bold uppercase tracking-wider text-zinc-200">
                        {col.label}
                      </h3>
                      <span className="text-[11px] font-mono font-bold bg-zinc-800 text-zinc-400 px-2 py-0.5 rounded-md border border-zinc-700/60">
                        {colTasks.length}
                      </span>
                    </div>

                    <button
                      onClick={() => handleOpenCreateModal(col.id)}
                      className="p-1 rounded-md text-zinc-500 hover:text-zinc-200 hover:bg-zinc-800 transition-colors"
                      title={`Add task to ${col.label}`}
                    >
                      <Plus className="h-4 w-4" />
                    </button>
                  </div>

                  {/* Tasks Container */}
                  <div className="p-3 space-y-3 flex-1 overflow-y-auto max-h-[calc(100vh-320px)] scrollbar-thin scrollbar-thumb-zinc-800">
                    {colTasks.length === 0 ? (
                      <div className="flex flex-col items-center justify-center p-8 rounded-xl border border-dashed border-zinc-800 text-center">
                        <CheckSquare className="h-7 w-7 text-zinc-700 mb-2" />
                        <p className="text-xs font-mono text-zinc-500">No tasks in {col.label}</p>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => handleOpenCreateModal(col.id)}
                          className="mt-2 text-xs text-zinc-400 hover:text-zinc-200 h-8 gap-1"
                        >
                          <Plus className="h-3.5 w-3.5" /> Add Task
                        </Button>
                      </div>
                    ) : (
                      colTasks.map((task) => {
                        const typeBadge = getTypeBadge(task.type);

                        return (
                          <div
                            key={task.db_id}
                            className="group relative p-3.5 rounded-xl bg-[#16161a] border border-zinc-800/80 hover:border-zinc-700 transition-all shadow-md space-y-3"
                          >
                            {/* Card Header: Checkbox, ID, Type, Priority, Menu */}
                            <div className="flex items-start justify-between gap-2">
                              <div className="flex items-center gap-2 flex-wrap">
                                <button
                                  onClick={() => handleToggleTask(task)}
                                  className="text-zinc-500 hover:text-emerald-400 transition-colors shrink-0 mt-0.5"
                                  title={task.completed ? "Mark as Incomplete" : "Mark as Complete"}
                                >
                                  {task.completed ? (
                                    <CheckSquare className="h-4 w-4 text-emerald-400" />
                                  ) : (
                                    <Square className="h-4 w-4 text-zinc-600 hover:text-zinc-400" />
                                  )}
                                </button>
                                <span className="font-mono text-[11px] text-zinc-500 font-semibold">
                                  {task.id}
                                </span>
                              </div>

                              <div className="flex items-center gap-1.5 shrink-0">
                                <span
                                  className={`text-[10px] font-mono px-2 py-0.5 rounded-full border font-semibold ${getPriorityBadge(
                                    task.priority
                                  )}`}
                                >
                                  {task.priority}
                                </span>

                                {/* Card Dropdown Menu */}
                                <div className="relative">
                                  <button
                                    onClick={(e) => {
                                      e.stopPropagation();
                                      setActiveDropdown(activeDropdown === task.db_id ? null : task.db_id);
                                    }}
                                    className="p-1 rounded hover:bg-zinc-800 text-zinc-500 hover:text-zinc-200 transition-colors"
                                  >
                                    <MoreVertical className="h-3.5 w-3.5" />
                                  </button>

                                  {activeDropdown === task.db_id && (
                                    <div
                                      onClick={(e) => e.stopPropagation()}
                                      className="absolute right-0 top-6 w-44 rounded-xl bg-zinc-900 border border-zinc-800 p-1.5 shadow-2xl z-30 font-mono text-xs text-zinc-300 space-y-0.5"
                                    >
                                      <Link
                                        href={`/tasks/${task.db_id}`}
                                        className="w-full flex items-center gap-2 p-1.5 rounded hover:bg-zinc-800 text-left transition-colors"
                                      >
                                        <ExternalLink className="h-3.5 w-3.5 text-zinc-400" />
                                        <span>View Details</span>
                                      </Link>

                                      <button
                                        onClick={() => {
                                          setActiveDropdown(null);
                                          handleOpenEditModal(task);
                                        }}
                                        className="w-full flex items-center gap-2 p-1.5 rounded hover:bg-zinc-800 text-left transition-colors"
                                      >
                                        <Edit2 className="h-3.5 w-3.5 text-zinc-400" />
                                        <span>Edit Task</span>
                                      </button>

                                      <button
                                        onClick={() => {
                                          setActiveDropdown(null);
                                          handleCopyTask(task);
                                        }}
                                        className="w-full flex items-center gap-2 p-1.5 rounded hover:bg-zinc-800 text-left transition-colors"
                                      >
                                        <Copy className="h-3.5 w-3.5 text-zinc-400" />
                                        <span>Duplicate</span>
                                      </button>

                                      {/* Status Sub-options */}
                                      <div className="border-t border-zinc-800/80 my-1 pt-1">
                                        <p className="px-1.5 text-[10px] text-zinc-500 uppercase">Move to</p>
                                        {KANBAN_COLUMNS.filter((c) => c.id !== task.status_id).map((c) => (
                                          <button
                                            key={c.id}
                                            onClick={() => {
                                              setActiveDropdown(null);
                                              handleQuickStatusChange(task, c.id, c.key);
                                            }}
                                            className="w-full flex items-center gap-2 p-1.5 rounded hover:bg-zinc-800 text-left transition-colors text-zinc-400 hover:text-zinc-200"
                                          >
                                            <span className={`h-1.5 w-1.5 rounded-full ${c.dot}`} />
                                            <span>{c.label}</span>
                                          </button>
                                        ))}
                                      </div>

                                      <div className="border-t border-zinc-800/80 pt-1">
                                        <button
                                          onClick={() => {
                                            setActiveDropdown(null);
                                            setDeletingTask(task);
                                          }}
                                          className="w-full flex items-center gap-2 p-1.5 rounded hover:bg-rose-500/10 text-rose-400 text-left transition-colors"
                                        >
                                          <Trash2 className="h-3.5 w-3.5" />
                                          <span>Delete</span>
                                        </button>
                                      </div>
                                    </div>
                                  )}
                                </div>
                              </div>
                            </div>

                            {/* Task Title */}
                            <div>
                              <Link
                                href={`/tasks/${task.db_id}`}
                                className={`text-xs sm:text-sm font-medium leading-snug block hover:text-emerald-400 transition-colors ${
                                  task.completed ? "line-through text-zinc-500" : "text-zinc-100"
                                }`}
                              >
                                {task.title}
                              </Link>
                              {task.description && (
                                <p className="text-xs text-zinc-400 mt-1 line-clamp-2">
                                  {task.description}
                                </p>
                              )}
                            </div>

                            {/* Tags: Project, Type & Points */}
                            <div className="flex items-center gap-2 flex-wrap text-[11px] font-mono">
                              <span
                                className="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-zinc-900 border border-zinc-800 text-zinc-300"
                                title={`Project: ${task.project}`}
                              >
                                <span
                                  className="h-1.5 w-1.5 rounded-full shrink-0"
                                  style={{ backgroundColor: task.project_theme || "#10b981" }}
                                />
                                <span className="truncate max-w-[120px]">{task.project}</span>
                              </span>

                              <span
                                className={`inline-flex items-center gap-1 px-2 py-0.5 rounded border text-[10px] ${typeBadge.classes}`}
                              >
                                {typeBadge.icon}
                                <span>{task.type}</span>
                              </span>

                              {task.points !== null && task.points !== undefined && (
                                <span className="px-1.5 py-0.5 rounded bg-zinc-900 text-zinc-400 border border-zinc-800 text-[10px]">
                                  {task.points} pts
                                </span>
                              )}
                            </div>

                            {/* Subtasks Progress Bar (if available) */}
                            {task.subtasks_count > 0 && (
                              <div className="space-y-1">
                                <div className="flex items-center justify-between text-[10px] font-mono text-zinc-500">
                                  <span>Subtasks</span>
                                  <span>{task.subtasks}</span>
                                </div>
                                <div className="h-1.5 w-full bg-zinc-900 rounded-full overflow-hidden border border-zinc-800">
                                  <div
                                    className="h-full bg-emerald-500 rounded-full transition-all"
                                    style={{
                                      width: `${Math.round(
                                        (task.completed_subtasks_count / task.subtasks_count) * 100
                                      )}%`,
                                    }}
                                  />
                                </div>
                              </div>
                            )}

                            {/* Footer: Due Date & Assignee Avatar */}
                            <div className="flex items-center justify-between pt-2.5 border-t border-zinc-800/80 text-xs font-mono text-zinc-400">
                              <div className="flex items-center gap-1.5">
                                <Calendar className="h-3 w-3 text-zinc-500" />
                                <span
                                  className={`${
                                    task.is_overdue
                                      ? "text-rose-400 font-bold"
                                      : task.dueDate === "Today"
                                      ? "text-amber-400 font-bold"
                                      : "text-zinc-400"
                                  }`}
                                >
                                  {task.dueDate}
                                </span>
                              </div>

                              <div className="flex items-center gap-1.5">
                                <span className="text-[11px] text-zinc-500 max-w-[90px] truncate hidden sm:inline-block">
                                  {task.assignee?.name}
                                </span>
                                <Avatar className="h-6 w-6 border border-zinc-700">
                                  {task.assignee?.profile_image && (
                                    <AvatarImage src={task.assignee.profile_image} />
                                  )}
                                  <AvatarFallback className="bg-zinc-800 text-zinc-200 text-[10px] font-bold">
                                    {task.assignee?.avatar || "UN"}
                                  </AvatarFallback>
                                </Avatar>
                              </div>
                            </div>
                          </div>
                        );
                      })
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          /* Table / List View */
          <div className="border border-zinc-800/90 bg-[#111114]/90 rounded-xl overflow-hidden shadow-2xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs sm:text-sm font-sans">
                <thead>
                  <tr className="border-b border-zinc-800 bg-zinc-900/80 text-zinc-400 font-mono text-xs uppercase tracking-wider">
                    <th className="py-3 px-4 w-10"></th>
                    <th className="py-3 px-4">Task</th>
                    <th className="py-3 px-4">Project</th>
                    <th className="py-3 px-4">Status</th>
                    <th className="py-3 px-4">Priority</th>
                    <th className="py-3 px-4">Due Date</th>
                    <th className="py-3 px-4">Assignee</th>
                    <th className="py-3 px-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-zinc-800/60 font-mono">
                  {sortedTasks.length === 0 ? (
                    <tr>
                      <td colSpan={8} className="py-12 text-center text-zinc-500">
                        <CheckSquare className="h-8 w-8 mx-auto mb-2 text-zinc-600" />
                        <p className="text-sm">No tasks found matching your filter criteria.</p>
                      </td>
                    </tr>
                  ) : (
                    sortedTasks.map((t) => {
                      const typeBadge = getTypeBadge(t.type);

                      return (
                        <tr
                          key={t.db_id}
                          className="hover:bg-zinc-800/40 transition-colors group font-sans"
                        >
                          <td className="py-3 px-4">
                            <button
                              onClick={() => handleToggleTask(t)}
                              className="text-zinc-500 hover:text-emerald-400 transition-colors"
                            >
                              {t.completed ? (
                                <CheckSquare className="h-4 w-4 text-emerald-400" />
                              ) : (
                                <Square className="h-4 w-4 text-zinc-600 hover:text-zinc-400" />
                              )}
                            </button>
                          </td>

                          <td className="py-3 px-4">
                            <div className="flex items-center gap-2">
                              <span className="font-mono text-xs text-zinc-500">{t.id}</span>
                              <Link
                                href={`/tasks/${t.db_id}`}
                                className={`font-medium hover:underline text-xs sm:text-sm ${
                                  t.completed ? "line-through text-zinc-500" : "text-zinc-100 hover:text-white"
                                }`}
                              >
                                {t.title}
                              </Link>
                              <span
                                className={`inline-flex items-center gap-1 px-1.5 py-0.2 rounded border text-[10px] font-mono ${typeBadge.classes}`}
                              >
                                {typeBadge.icon}
                                <span>{t.type}</span>
                              </span>
                            </div>
                          </td>

                          <td className="py-3 px-4 font-mono text-xs text-zinc-300">
                            <span className="inline-flex items-center gap-1.5">
                              <span
                                className="h-2 w-2 rounded-full"
                                style={{ backgroundColor: t.project_theme || "#10b981" }}
                              />
                              <span>[{t.project}]</span>
                            </span>
                          </td>

                          <td className="py-3 px-4 font-mono text-xs">
                            <span
                              className={`px-2 py-0.5 rounded-full text-[11px] font-semibold ${
                                t.status === "Done"
                                  ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/30"
                                  : t.status === "In Progress"
                                  ? "bg-blue-500/10 text-blue-400 border border-blue-500/30"
                                  : t.status === "Review"
                                  ? "bg-purple-500/10 text-purple-400 border border-purple-500/30"
                                  : "bg-zinc-800 text-zinc-300 border border-zinc-700/60"
                              }`}
                            >
                              {t.status}
                            </span>
                          </td>

                          <td className="py-3 px-4 font-mono text-xs">
                            <span
                              className={`px-2.5 py-0.5 rounded-full text-[11px] font-semibold border ${getPriorityBadge(
                                t.priority
                              )}`}
                            >
                              {t.priority}
                            </span>
                          </td>

                          <td className="py-3 px-4 font-mono text-xs">
                            <span
                              className={`${
                                t.is_overdue
                                  ? "text-rose-400 font-bold"
                                  : t.dueDate === "Today"
                                  ? "text-amber-400 font-bold"
                                  : "text-zinc-400"
                              }`}
                            >
                              {t.dueDate}
                            </span>
                          </td>

                          <td className="py-3 px-4 font-mono text-xs text-zinc-300">
                            <div className="flex items-center gap-2">
                              <Avatar className="h-5 w-5 border border-zinc-700">
                                {t.assignee?.profile_image && (
                                  <AvatarImage src={t.assignee.profile_image} />
                                )}
                                <AvatarFallback className="bg-zinc-800 text-zinc-300 text-[9px] font-bold">
                                  {t.assignee?.avatar || "UN"}
                                </AvatarFallback>
                              </Avatar>
                              <span className="truncate max-w-[100px]">{t.assignee?.name}</span>
                            </div>
                          </td>

                          <td className="py-3 px-4 text-right">
                            <div className="flex items-center justify-end gap-1">
                              <button
                                onClick={() => handleOpenEditModal(t)}
                                className="p-1 rounded hover:bg-zinc-800 text-zinc-400 hover:text-zinc-200 transition-colors"
                                title="Edit Task"
                              >
                                <Edit2 className="h-3.5 w-3.5" />
                              </button>
                              <button
                                onClick={() => handleCopyTask(t)}
                                className="p-1 rounded hover:bg-zinc-800 text-zinc-400 hover:text-zinc-200 transition-colors"
                                title="Duplicate Task"
                              >
                                <Copy className="h-3.5 w-3.5" />
                              </button>
                              <button
                                onClick={() => setDeletingTask(t)}
                                className="p-1 rounded hover:bg-rose-500/10 text-zinc-400 hover:text-rose-400 transition-colors"
                                title="Delete Task"
                              >
                                <Trash2 className="h-3.5 w-3.5" />
                              </button>
                            </div>
                          </td>
                        </tr>
                      );
                    })
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Modal: Create Task */}
        {showCreateModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
            <div className="w-full max-w-xl rounded-2xl bg-[#141418] border border-zinc-800 p-6 shadow-2xl space-y-5">
              <div className="flex items-center justify-between pb-3 border-b border-zinc-800">
                <div className="flex items-center gap-2">
                  <div className="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <CheckSquare className="w-4 h-4" />
                  </div>
                  <h3 className="text-base font-mono font-bold text-white uppercase tracking-wider">
                    Create New Task
                  </h3>
                </div>
                <button
                  onClick={() => setShowCreateModal(false)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleCreateSubmit} className="space-y-4">
                {/* Title */}
                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">
                    Task Title <span className="text-rose-500">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Implement authentication rate limiter"
                    value={formData.title}
                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500 transition-colors"
                  />
                  {formErrors.title && (
                    <p className="text-xs text-rose-400 mt-1">{formErrors.title}</p>
                  )}
                </div>

                {/* Project & Assignee */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Project</label>
                    <select
                      value={formData.project_id}
                      onChange={(e) => setFormData({ ...formData, project_id: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-200 focus:outline-none focus:border-emerald-500 font-mono"
                    >
                      <option value="personal">Personal Space (No Project)</option>
                      {projects.map((p) => (
                        <option key={p.id} value={p.id}>
                          {p.name}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Assignee</label>
                    <select
                      value={formData.assigned_to}
                      onChange={(e) => setFormData({ ...formData, assigned_to: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-200 focus:outline-none focus:border-emerald-500 font-mono"
                    >
                      <option value="unassigned">Unassigned</option>
                      {company_users.map((u) => (
                        <option key={u.id} value={u.id}>
                          {u.name}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>

                {/* Status, Priority, Type */}
                <div className="grid grid-cols-3 gap-3">
                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Status</label>
                    <select
                      value={formData.status}
                      onChange={(e) => setFormData({ ...formData, status: Number(e.target.value) })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none focus:border-emerald-500 font-mono"
                    >
                      <option value={1}>To Do</option>
                      <option value={2}>In Progress</option>
                      <option value={4}>Review</option>
                      <option value={3}>Done</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Priority</label>
                    <select
                      value={formData.priority}
                      onChange={(e) => setFormData({ ...formData, priority: Number(e.target.value) })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none focus:border-emerald-500 font-mono"
                    >
                      <option value={1}>Low</option>
                      <option value={2}>Medium</option>
                      <option value={3}>High</option>
                      <option value={4}>Urgent</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Type</label>
                    <select
                      value={formData.type}
                      onChange={(e) => setFormData({ ...formData, type: Number(e.target.value) })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none focus:border-emerald-500 font-mono"
                    >
                      <option value={1}>Task</option>
                      <option value={2}>Bug</option>
                      <option value={3}>Feature</option>
                      <option value={4}>Improvement</option>
                    </select>
                  </div>
                </div>

                {/* Due Date & Points */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Due Date</label>
                    <input
                      type="date"
                      value={formData.due_date}
                      onChange={(e) => setFormData({ ...formData, due_date: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-200 focus:outline-none focus:border-emerald-500 font-mono"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Story Points</label>
                    <input
                      type="number"
                      min={0}
                      max={99999}
                      placeholder="e.g. 5"
                      value={formData.points}
                      onChange={(e) => setFormData({ ...formData, points: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-200 focus:outline-none focus:border-emerald-500 font-mono"
                    />
                  </div>
                </div>

                {/* Description */}
                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Description</label>
                  <textarea
                    rows={3}
                    placeholder="Provide details or acceptance criteria for this task..."
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500 transition-colors resize-none"
                  />
                </div>

                {/* Actions */}
                <div className="flex items-center justify-end gap-3 pt-3 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setShowCreateModal(false)}
                    className="border-zinc-800 text-zinc-400 hover:text-white"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    disabled={isSubmitting}
                    className="bg-emerald-500 hover:bg-emerald-600 text-black font-semibold gap-1.5 px-4"
                  >
                    {isSubmitting && <Loader2 className="h-3.5 w-3.5 animate-spin" />}
                    <span>Create Task</span>
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Modal: Edit Task */}
        {editingTask && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
            <div className="w-full max-w-xl rounded-2xl bg-[#141418] border border-zinc-800 p-6 shadow-2xl space-y-5">
              <div className="flex items-center justify-between pb-3 border-b border-zinc-800">
                <div className="flex items-center gap-2">
                  <div className="p-2 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    <Edit2 className="w-4 h-4" />
                  </div>
                  <div>
                    <h3 className="text-base font-mono font-bold text-white uppercase tracking-wider">
                      Edit Task {editingTask.id}
                    </h3>
                  </div>
                </div>
                <button
                  onClick={() => setEditingTask(null)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleEditSubmit} className="space-y-4">
                {/* Title */}
                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">
                    Task Title <span className="text-rose-500">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.title}
                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-blue-500 transition-colors"
                  />
                  {formErrors.title && (
                    <p className="text-xs text-rose-400 mt-1">{formErrors.title}</p>
                  )}
                </div>

                {/* Project & Assignee */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Project</label>
                    <select
                      value={formData.project_id}
                      onChange={(e) => setFormData({ ...formData, project_id: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-200 focus:outline-none focus:border-blue-500 font-mono"
                    >
                      <option value="personal">Personal Space (No Project)</option>
                      {projects.map((p) => (
                        <option key={p.id} value={p.id}>
                          {p.name}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Assignee</label>
                    <select
                      value={formData.assigned_to}
                      onChange={(e) => setFormData({ ...formData, assigned_to: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-200 focus:outline-none focus:border-blue-500 font-mono"
                    >
                      <option value="unassigned">Unassigned</option>
                      {company_users.map((u) => (
                        <option key={u.id} value={u.id}>
                          {u.name}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>

                {/* Status, Priority, Type */}
                <div className="grid grid-cols-3 gap-3">
                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Status</label>
                    <select
                      value={formData.status}
                      onChange={(e) => setFormData({ ...formData, status: Number(e.target.value) })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none focus:border-blue-500 font-mono"
                    >
                      <option value={1}>To Do</option>
                      <option value={2}>In Progress</option>
                      <option value={4}>Review</option>
                      <option value={3}>Done</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Priority</label>
                    <select
                      value={formData.priority}
                      onChange={(e) => setFormData({ ...formData, priority: Number(e.target.value) })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none focus:border-blue-500 font-mono"
                    >
                      <option value={1}>Low</option>
                      <option value={2}>Medium</option>
                      <option value={3}>High</option>
                      <option value={4}>Urgent</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Type</label>
                    <select
                      value={formData.type}
                      onChange={(e) => setFormData({ ...formData, type: Number(e.target.value) })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none focus:border-blue-500 font-mono"
                    >
                      <option value={1}>Task</option>
                      <option value={2}>Bug</option>
                      <option value={3}>Feature</option>
                      <option value={4}>Improvement</option>
                    </select>
                  </div>
                </div>

                {/* Due Date & Points */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Due Date</label>
                    <input
                      type="date"
                      value={formData.due_date}
                      onChange={(e) => setFormData({ ...formData, due_date: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-200 focus:outline-none focus:border-blue-500 font-mono"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Story Points</label>
                    <input
                      type="number"
                      min={0}
                      max={99999}
                      value={formData.points}
                      onChange={(e) => setFormData({ ...formData, points: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-200 focus:outline-none focus:border-blue-500 font-mono"
                    />
                  </div>
                </div>

                {/* Description */}
                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Description</label>
                  <textarea
                    rows={3}
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-blue-500 transition-colors resize-none"
                  />
                </div>

                {/* Actions */}
                <div className="flex items-center justify-end gap-3 pt-3 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setEditingTask(null)}
                    className="border-zinc-800 text-zinc-400 hover:text-white"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    disabled={isSubmitting}
                    className="bg-blue-500 hover:bg-blue-600 text-white font-semibold gap-1.5 px-4"
                  >
                    {isSubmitting && <Loader2 className="h-3.5 w-3.5 animate-spin" />}
                    <span>Save Changes</span>
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Modal: Delete Task Confirmation */}
        {deletingTask && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
            <div className="w-full max-w-md rounded-2xl bg-[#141418] border border-rose-500/30 p-6 shadow-2xl space-y-4">
              <div className="flex items-center gap-3 text-rose-400">
                <div className="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20">
                  <AlertTriangle className="h-5 w-5" />
                </div>
                <div>
                  <h3 className="text-base font-mono font-bold text-white">Delete Task?</h3>
                  <p className="text-xs font-mono text-zinc-500">{deletingTask.id}</p>
                </div>
              </div>

              <p className="text-xs sm:text-sm text-zinc-300">
                Are you sure you want to delete <span className="font-semibold text-white">"{deletingTask.title}"</span>?
                This action will move the task to trash.
              </p>

              <div className="flex items-center justify-end gap-3 pt-3 border-t border-zinc-800">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => setDeletingTask(null)}
                  className="border-zinc-800 text-zinc-400 hover:text-white"
                >
                  Cancel
                </Button>
                <Button
                  type="button"
                  size="sm"
                  disabled={isSubmitting}
                  onClick={handleDeleteSubmit}
                  className="bg-rose-500 hover:bg-rose-600 text-white font-semibold gap-1.5 px-4"
                >
                  {isSubmitting && <Loader2 className="h-3.5 w-3.5 animate-spin" />}
                  <span>Delete Task</span>
                </Button>
              </div>
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
