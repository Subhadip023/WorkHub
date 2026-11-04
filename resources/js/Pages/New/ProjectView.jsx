import React, { useState, useMemo } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Link, router, usePage } from "@inertiajs/react";
import {
  ArrowLeft,
  FolderKanban,
  CheckSquare,
  Square,
  Plus,
  Search,
  Filter,
  Calendar,
  User,
  Clock,
  Sparkles,
  ExternalLink,
  FileText,
  Key,
  MessageSquare,
  Trash2,
  Edit2,
  Check,
  AlertCircle,
  Building2,
  Send,
  Eye,
  EyeOff,
  Copy,
  ChevronRight,
  TrendingUp,
  X,
  Loader2,
  Layers,
  Shield,
  Activity,
  Bug,
  Tag,
  CheckCircle2,
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";

export default function ProjectView({
  project = {},
  tasks = [],
  notes = [],
  credentials = [],
  comments = [],
  team_members = [],
  stats = {},
}) {
  const { flash = {} } = usePage().props;

  // Active Tab: 'tasks' | 'notes' | 'credentials' | 'discussion'
  const [activeTab, setActiveTab] = useState("tasks");

  // Task filtering state
  const [taskSearch, setTaskSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [priorityFilter, setPriorityFilter] = useState("all");
  const [typeFilter, setTypeFilter] = useState("all");

  // Credential view state (revealed credential IDs)
  const [revealedSecrets, setRevealedSecrets] = useState({});
  const [copiedId, setCopiedId] = useState(null);

  // Modals state
  const [showTaskModal, setShowTaskModal] = useState(false);
  const [showNoteModal, setShowNoteModal] = useState(false);
  const [showCredModal, setShowCredModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Forms
  const [taskForm, setTaskForm] = useState({
    title: "",
    description: "",
    due_date: "",
    assigned_to: "",
    priority: 2, // Medium
    status: 1, // To Do
    type: 1, // Feature
    points: 1,
  });

  const [noteForm, setNoteForm] = useState({
    title: "",
    description: "",
  });

  const [credForm, setCredForm] = useState({
    name: "",
    type: 1, // Development
    host_or_identifier: "",
    password_or_secret: "",
  });

  const [editForm, setEditForm] = useState({
    name: project.name || "",
    theme: project.theme || "#10b981",
    status: project.status || 1,
    priority: project.priority || 2,
    description: project.description || "",
  });

  const [commentText, setCommentText] = useState("");
  const [isSubmittingComment, setIsSubmittingComment] = useState(false);

  // Toggle revealed secret
  const toggleSecret = (id) => {
    setRevealedSecrets((prev) => ({ ...prev, [id]: !prev[id] }));
  };

  // Copy secret to clipboard
  const handleCopy = (id, text) => {
    navigator.clipboard.writeText(text);
    setCopiedId(id);
    setTimeout(() => setCopiedId(null), 2000);
  };

  // Filter tasks
  const filteredTasks = useMemo(() => {
    return tasks.filter((t) => {
      const matchesSearch =
        taskSearch === "" ||
        t.title.toLowerCase().includes(taskSearch.toLowerCase()) ||
        (t.description && t.description.toLowerCase().includes(taskSearch.toLowerCase()));

      const matchesStatus =
        statusFilter === "all" ||
        (statusFilter === "completed" && t.status === 3) ||
        (statusFilter === "in_progress" && t.status === 2) ||
        (statusFilter === "todo" && t.status === 1) ||
        (statusFilter === "on_hold" && t.status === 4);

      const matchesPriority =
        priorityFilter === "all" ||
        (priorityFilter === "urgent" && t.priority === 4) ||
        (priorityFilter === "high" && t.priority === 3) ||
        (priorityFilter === "medium" && t.priority === 2) ||
        (priorityFilter === "low" && t.priority === 1);

      const matchesType =
        typeFilter === "all" ||
        (typeFilter === "feature" && t.type === 1) ||
        (typeFilter === "bug" && t.type === 2) ||
        (typeFilter === "task" && t.type === 3);

      return matchesSearch && matchesStatus && matchesPriority && matchesType;
    });
  }, [tasks, taskSearch, statusFilter, priorityFilter, typeFilter]);

  // Toggle Task Completion
  const handleToggleTask = (task) => {
    router.patch(
      `/tasks/${task.db_id || task.id}/toggle`,
      {},
      { preserveScroll: true }
    );
  };

  // Submit New Task
  const handleCreateTask = (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    router.post(`/projects/${project.id}/tasks`, taskForm, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setShowTaskModal(false);
        setTaskForm({
          title: "",
          description: "",
          due_date: "",
          assigned_to: "",
          priority: 2,
          status: 1,
          type: 1,
          points: 1,
        });
      },
      onError: () => setIsSubmitting(false),
    });
  };

  // Submit New Note
  const handleCreateNote = (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    router.post(
      "/v2/notes",
      {
        ...noteForm,
        note_type: 1, // Project note
        note_type_id: project.id,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          setIsSubmitting(false);
          setShowNoteModal(false);
          setNoteForm({ title: "", description: "" });
        },
        onError: () => setIsSubmitting(false),
      }
    );
  };

  // Submit New Credential
  const handleCreateCred = (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    router.post(`/projects/${project.id}/credentials`, credForm, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setShowCredModal(false);
        setCredForm({
          name: "",
          type: 1,
          host_or_identifier: "",
          password_or_secret: "",
        });
      },
      onError: () => setIsSubmitting(false),
    });
  };

  // Delete Credential
  const handleDeleteCred = (credId) => {
    if (confirm("Are you sure you want to delete this credential?")) {
      router.delete(`/projects/${project.id}/credentials/${credId}`, {
        preserveScroll: true,
      });
    }
  };

  // Submit Comment
  const handleAddComment = (e) => {
    e.preventDefault();
    if (!commentText.trim()) return;

    setIsSubmittingComment(true);
    router.post(
      "/comments",
      {
        content: commentText,
        commentable_type: "project",
        commentable_id: project.id,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          setCommentText("");
          setIsSubmittingComment(false);
        },
        onError: () => setIsSubmittingComment(false),
      }
    );
  };

  // Delete Comment
  const handleDeleteComment = (commentId) => {
    if (confirm("Delete this comment?")) {
      router.delete(`/comments/${commentId}`, { preserveScroll: true });
    }
  };

  // Submit Edit Project
  const handleEditProject = (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    router.put(`/projects/${project.id}`, editForm, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmitting(false);
        setShowEditModal(false);
      },
      onError: () => setIsSubmitting(false),
    });
  };

  // Delete Project
  const handleDeleteProject = () => {
    router.delete(`/projects/${project.id}`);
  };

  return (
    <DashboardLayout title={project.name} activeItem="projects">
      <div className="space-y-6 pb-16 max-w-7xl mx-auto">
        {/* Flash Messages */}
        {flash.success && (
          <div className="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center gap-3 animate-in fade-in duration-200">
            <Check className="w-5 h-5 shrink-0" />
            <p className="text-sm font-medium">{flash.success}</p>
          </div>
        )}

        {/* Top Header & Breadcrumb */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-zinc-900/80 border border-zinc-800 shadow-xl backdrop-blur-sm">
          <div className="space-y-2">
            <div className="flex items-center gap-2 text-xs font-mono text-zinc-400">
              <Link
                href="/projects"
                className="inline-flex items-center gap-1.5 p-1.5 rounded-lg bg-zinc-950 border border-zinc-800 text-zinc-400 hover:text-white transition-colors"
                title="Back to Projects"
              >
                <ArrowLeft className="w-4 h-4" />
                <span className="font-sans font-medium text-xs">Projects</span>
              </Link>
              <ChevronRight className="w-3.5 h-3.5 text-zinc-600" />
              <span className="font-medium text-zinc-200 truncate">{project.name}</span>
            </div>

            <div className="flex flex-wrap items-center gap-2.5 pt-1">
              <span
                className="w-3.5 h-3.5 rounded-full shrink-0 ring-4 ring-zinc-800/80"
                style={{ backgroundColor: project.theme }}
              />
              <h1 className="text-2xl font-bold tracking-tight text-white">{project.name}</h1>
              
              <Badge
                variant="outline"
                className={`font-mono text-xs ${
                  project.status === 3
                    ? "border-emerald-500/40 text-emerald-400 bg-emerald-500/10"
                    : project.status === 2
                    ? "border-blue-500/40 text-blue-400 bg-blue-500/10"
                    : project.status === 4
                    ? "border-amber-500/40 text-amber-400 bg-amber-500/10"
                    : "border-zinc-700 text-zinc-300 bg-zinc-800/40"
                }`}
              >
                {project.status_label}
              </Badge>

              <Badge
                variant="outline"
                className={`font-mono text-xs ${
                  project.priority === 4
                    ? "border-rose-500/40 text-rose-400 bg-rose-500/10"
                    : project.priority === 3
                    ? "border-orange-500/40 text-orange-400 bg-orange-500/10"
                    : project.priority === 2
                    ? "border-yellow-500/40 text-yellow-400 bg-yellow-500/10"
                    : "border-zinc-700 text-zinc-400 bg-zinc-800/40"
                }`}
              >
                {project.priority_label} Priority
              </Badge>

              <Badge variant="outline" className="border-zinc-800 text-zinc-400 font-mono text-xs bg-zinc-950">
                {project.company_name ? (
                  <span className="flex items-center gap-1">
                    <Building2 className="w-3 h-3 text-zinc-400" /> {project.company_name}
                  </span>
                ) : (
                  "Personal Space"
                )}
              </Badge>
            </div>

            {project.description && (
              <p className="text-xs sm:text-sm text-zinc-300 max-w-3xl leading-relaxed pt-1">
                {project.description}
              </p>
            )}
          </div>

          {/* Action Buttons */}
          <div className="flex flex-wrap items-center gap-2 shrink-0">
            <Button
              onClick={() => setShowTaskModal(true)}
              className="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs sm:text-sm rounded-xl h-9 px-3.5 gap-2 shadow-lg shadow-emerald-950/40 cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              <span>Add Task</span>
            </Button>

            {project.can_edit && (
              <Button
                variant="outline"
                onClick={() => setShowEditModal(true)}
                className="border-zinc-800 hover:bg-zinc-800 text-zinc-300 text-xs sm:text-sm rounded-xl h-9 px-3 gap-1.5 cursor-pointer"
              >
                <Edit2 className="w-3.5 h-3.5 text-zinc-400" />
                <span>Edit</span>
              </Button>
            )}

            {project.can_delete && (
              <Button
                variant="outline"
                onClick={() => setShowDeleteModal(true)}
                className="border-zinc-800 hover:bg-rose-950/30 hover:border-rose-800/60 text-rose-400 text-xs sm:text-sm rounded-xl h-9 px-3 gap-1.5 cursor-pointer"
              >
                <Trash2 className="w-3.5 h-3.5" />
                <span>Delete</span>
              </Button>
            )}
          </div>
        </div>

        {/* 4-KPI Overview Grid */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3.5">
          {/* Progress */}
          <Card className="bg-zinc-900/60 border-zinc-800 shadow-sm p-4 rounded-2xl">
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-mono text-zinc-400 uppercase tracking-wider">Completion Rate</span>
              <TrendingUp className="w-4 h-4 text-emerald-400" />
            </div>
            <div className="flex items-baseline gap-2">
              <span className="text-2xl font-bold text-white font-mono">{stats.progress || 0}%</span>
              <span className="text-xs text-zinc-500">
                ({stats.completed_tasks || 0}/{stats.total_tasks || 0})
              </span>
            </div>
            <div className="w-full bg-zinc-800 h-1.5 rounded-full mt-2.5 overflow-hidden">
              <div
                className="h-full rounded-full transition-all duration-500"
                style={{
                  width: `${stats.progress || 0}%`,
                  backgroundColor: project.theme || "#10b981",
                }}
              />
            </div>
          </Card>

          {/* In Progress */}
          <Card className="bg-zinc-900/60 border-zinc-800 shadow-sm p-4 rounded-2xl">
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-mono text-zinc-400 uppercase tracking-wider">In Progress</span>
              <Activity className="w-4 h-4 text-blue-400" />
            </div>
            <span className="text-2xl font-bold text-white font-mono">{stats.in_progress_tasks || 0}</span>
            <span className="text-xs text-zinc-500 block mt-1">Active tasks</span>
          </Card>

          {/* Pending / To Do */}
          <Card className="bg-zinc-900/60 border-zinc-800 shadow-sm p-4 rounded-2xl">
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-mono text-zinc-400 uppercase tracking-wider">To Do</span>
              <Clock className="w-4 h-4 text-amber-400" />
            </div>
            <span className="text-2xl font-bold text-white font-mono">{stats.todo_tasks || 0}</span>
            <span className="text-xs text-zinc-500 block mt-1">Pending backlog</span>
          </Card>

          {/* Team Collaborators */}
          <Card className="bg-zinc-900/60 border-zinc-800 shadow-sm p-4 rounded-2xl">
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-mono text-zinc-400 uppercase tracking-wider">Collaborators</span>
              <User className="w-4 h-4 text-purple-400" />
            </div>
            <div className="flex items-center gap-1.5 mt-1 overflow-x-auto py-0.5">
              {team_members.slice(0, 5).map((m) => (
                <Avatar key={m.id} className="w-7 h-7 border border-zinc-700 ring-2 ring-zinc-900" title={m.name}>
                  {m.profile_image && <AvatarImage src={m.profile_image} alt={m.name} />}
                  <AvatarFallback className="bg-zinc-800 text-[10px] font-bold text-zinc-200">
                    {m.avatar}
                  </AvatarFallback>
                </Avatar>
              ))}
              {team_members.length > 5 && (
                <span className="text-xs font-mono text-zinc-500 ml-1">+{team_members.length - 5}</span>
              )}
            </div>
          </Card>
        </div>

        {/* Tab Navigation */}
        <div className="flex items-center gap-2 border-b border-zinc-800 pb-2">
          {[
            { id: "tasks", label: "Tasks", count: tasks.length, icon: CheckSquare },
            { id: "notes", label: "Notes & Docs", count: notes.length, icon: FileText },
            { id: "credentials", label: "Credentials", count: credentials.length, icon: Key },
            { id: "discussion", label: "Discussion", count: comments.length, icon: MessageSquare },
          ].map((tab) => {
            const Icon = tab.icon;
            const isActive = activeTab === tab.id;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`px-4 py-2 rounded-xl text-xs sm:text-sm font-medium transition-all flex items-center gap-2 cursor-pointer ${
                  isActive
                    ? "bg-zinc-800 text-white font-semibold border border-zinc-700 shadow-sm"
                    : "text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/60"
                }`}
              >
                <Icon className={`w-4 h-4 ${isActive ? "text-emerald-400" : "text-zinc-500"}`} />
                <span>{tab.label}</span>
                <span
                  className={`text-[10px] font-mono px-1.5 py-0.2 rounded-full border ${
                    isActive
                      ? "bg-emerald-500/10 text-emerald-400 border-emerald-500/30"
                      : "bg-zinc-900 text-zinc-500 border-zinc-800"
                  }`}
                >
                  {tab.count}
                </span>
              </button>
            );
          })}
        </div>

        {/* TAB 1: TASKS */}
        {activeTab === "tasks" && (
          <div className="space-y-4">
            {/* Filter Toolbar */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-zinc-900/50 border border-zinc-800">
              <div className="relative flex-1 max-w-sm">
                <Search className="absolute left-3 top-2.5 h-3.5 w-3.5 text-zinc-500" />
                <input
                  type="text"
                  placeholder="Filter project tasks..."
                  value={taskSearch}
                  onChange={(e) => setTaskSearch(e.target.value)}
                  className="w-full bg-zinc-950 border border-zinc-800 rounded-lg pl-9 pr-3 py-1.5 text-xs text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60"
                />
              </div>

              <div className="flex items-center gap-2 flex-wrap">
                <select
                  value={statusFilter}
                  onChange={(e) => setStatusFilter(e.target.value)}
                  className="bg-zinc-950 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-zinc-300 focus:outline-none focus:border-emerald-500/60"
                >
                  <option value="all">All Statuses</option>
                  <option value="todo">To Do</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="on_hold">On Hold</option>
                </select>

                <select
                  value={priorityFilter}
                  onChange={(e) => setPriorityFilter(e.target.value)}
                  className="bg-zinc-950 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-zinc-300 focus:outline-none focus:border-emerald-500/60"
                >
                  <option value="all">All Priorities</option>
                  <option value="urgent">Urgent</option>
                  <option value="high">High</option>
                  <option value="medium">Medium</option>
                  <option value="low">Low</option>
                </select>

                <select
                  value={typeFilter}
                  onChange={(e) => setTypeFilter(e.target.value)}
                  className="bg-zinc-950 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-zinc-300 focus:outline-none focus:border-emerald-500/60"
                >
                  <option value="all">All Types</option>
                  <option value="feature">Features</option>
                  <option value="bug">Bugs</option>
                  <option value="task">Tasks</option>
                </select>
              </div>
            </div>

            {/* Task List */}
            {filteredTasks.length === 0 ? (
              <div className="text-center py-12 rounded-2xl bg-zinc-900/30 border border-dashed border-zinc-800">
                <CheckSquare className="w-10 h-10 text-zinc-600 mx-auto mb-3" />
                <h3 className="text-sm font-semibold text-zinc-300">No tasks found</h3>
                <p className="text-xs text-zinc-500 mt-1">Add a task to get started on this project.</p>
                <Button
                  onClick={() => setShowTaskModal(true)}
                  size="sm"
                  className="mt-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs gap-1.5"
                >
                  <Plus className="w-3.5 h-3.5" /> Create Task
                </Button>
              </div>
            ) : (
              <div className="space-y-2">
                {filteredTasks.map((t) => (
                  <div
                    key={t.id}
                    className="p-3.5 rounded-xl bg-zinc-900/70 hover:bg-zinc-900 border border-zinc-800/80 hover:border-zinc-700 transition-all flex items-center justify-between gap-3 group"
                  >
                    <div className="flex items-center gap-3 min-w-0 flex-1">
                      <button
                        onClick={() => handleToggleTask(t)}
                        className="text-zinc-500 hover:text-emerald-400 transition-colors shrink-0 cursor-pointer"
                        title={t.completed ? "Mark incomplete" : "Mark completed"}
                      >
                        {t.completed ? (
                          <CheckCircle2 className="w-5 h-5 text-emerald-400 fill-emerald-500/20" />
                        ) : (
                          <Square className="w-5 h-5 text-zinc-600 hover:text-zinc-400" />
                        )}
                      </button>

                      <div className="min-w-0 flex-1">
                        <Link
                          href={`/tasks/${t.db_id || t.id}`}
                          className={`text-sm font-medium hover:text-emerald-400 transition-colors truncate block ${
                            t.completed ? "line-through text-zinc-500" : "text-zinc-200"
                          }`}
                        >
                          {t.title}
                        </Link>
                        <div className="flex items-center gap-2 mt-1 text-[11px] font-mono text-zinc-500 flex-wrap">
                          <span>#{t.id}</span>
                          <span>•</span>
                          <span
                            className={
                              t.priority === 4
                                ? "text-rose-400"
                                : t.priority === 3
                                ? "text-orange-400"
                                : t.priority === 2
                                ? "text-yellow-400"
                                : "text-zinc-400"
                            }
                          >
                            {t.priority_label}
                          </span>
                          <span>•</span>
                          <span>{t.type_label}</span>
                          {t.due_date && (
                            <>
                              <span>•</span>
                              <span className="flex items-center gap-1 text-zinc-400">
                                <Calendar className="w-3 h-3" /> {t.due_date}
                              </span>
                            </>
                          )}
                        </div>
                      </div>
                    </div>

                    <div className="flex items-center gap-3 shrink-0">
                      {t.assignee ? (
                        <div className="flex items-center gap-1.5" title={t.assignee.name}>
                          <Avatar className="w-6 h-6 border border-zinc-700">
                            {t.assignee.profile_image && (
                              <AvatarImage src={t.assignee.profile_image} alt={t.assignee.name} />
                            )}
                            <AvatarFallback className="bg-zinc-800 text-[10px] font-bold text-zinc-300">
                              {t.assignee.avatar}
                            </AvatarFallback>
                          </Avatar>
                          <span className="text-xs text-zinc-400 hidden sm:inline">{t.assignee.name}</span>
                        </div>
                      ) : (
                        <span className="text-xs text-zinc-600 italic hidden sm:inline">Unassigned</span>
                      )}

                      <Link
                        href={`/tasks/${t.db_id || t.id}`}
                        className="p-1.5 rounded-lg bg-zinc-950 border border-zinc-800 text-zinc-400 hover:text-white transition-colors"
                        title="View Task Details"
                      >
                        <ExternalLink className="w-3.5 h-3.5" />
                      </Link>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        {/* TAB 2: NOTES & DOCS */}
        {activeTab === "notes" && (
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="text-base font-bold text-white">Project Documentation</h3>
                <p className="text-xs text-zinc-400">Knowledge base, specifications, and architecture notes.</p>
              </div>
              <Button
                onClick={() => setShowNoteModal(true)}
                size="sm"
                className="bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs gap-1.5"
              >
                <Plus className="w-3.5 h-3.5" /> Add Note
              </Button>
            </div>

            {notes.length === 0 ? (
              <div className="text-center py-12 rounded-2xl bg-zinc-900/30 border border-dashed border-zinc-800">
                <FileText className="w-10 h-10 text-zinc-600 mx-auto mb-3" />
                <h3 className="text-sm font-semibold text-zinc-300">No project notes yet</h3>
                <p className="text-xs text-zinc-500 mt-1">Keep specs, meeting notes, and documentation organized.</p>
                <Button
                  onClick={() => setShowNoteModal(true)}
                  size="sm"
                  className="mt-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs gap-1.5"
                >
                  <Plus className="w-3.5 h-3.5" /> Create First Note
                </Button>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                {notes.map((n) => (
                  <Card key={n.id} className="bg-zinc-900/70 border-zinc-800 p-4 rounded-xl flex flex-col justify-between">
                    <div>
                      <div className="flex items-center justify-between mb-2">
                        <h4 className="font-bold text-zinc-100 text-sm truncate">{n.title}</h4>
                        <Link
                          href={`/notes/${n.id}`}
                          className="text-xs text-emerald-400 hover:underline flex items-center gap-1 font-mono shrink-0"
                        >
                          Read <ExternalLink className="w-3 h-3" />
                        </Link>
                      </div>
                      <p className="text-xs text-zinc-400 line-clamp-3 leading-relaxed">
                        {n.description ? n.description.replace(/<[^>]*>?/gm, "") : "No content"}
                      </p>
                    </div>

                    <div className="flex items-center justify-between pt-4 mt-3 border-t border-zinc-800 text-[11px] font-mono text-zinc-500">
                      <span>By {n.user?.name || "Author"}</span>
                      <span>{n.created_at}</span>
                    </div>
                  </Card>
                ))}
              </div>
            )}
          </div>
        )}

        {/* TAB 3: CREDENTIALS */}
        {activeTab === "credentials" && (
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="text-base font-bold text-white">Project Secrets & Credentials</h3>
                <p className="text-xs text-zinc-400">Environment keys, server IPs, and service identifiers.</p>
              </div>
              <Button
                onClick={() => setShowCredModal(true)}
                size="sm"
                className="bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs gap-1.5"
              >
                <Plus className="w-3.5 h-3.5" /> Add Credential
              </Button>
            </div>

            {credentials.length === 0 ? (
              <div className="text-center py-12 rounded-2xl bg-zinc-900/30 border border-dashed border-zinc-800">
                <Key className="w-10 h-10 text-zinc-600 mx-auto mb-3" />
                <h3 className="text-sm font-semibold text-zinc-300">No credentials stored</h3>
                <p className="text-xs text-zinc-500 mt-1">Securely store API keys, tokens, and database hosts.</p>
                <Button
                  onClick={() => setShowCredModal(true)}
                  size="sm"
                  className="mt-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs gap-1.5"
                >
                  <Plus className="w-3.5 h-3.5" /> Add First Credential
                </Button>
              </div>
            ) : (
              <div className="space-y-2.5">
                {credentials.map((c) => (
                  <div
                    key={c.id}
                    className="p-4 rounded-xl bg-zinc-900/80 border border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                  >
                    <div className="space-y-1">
                      <div className="flex items-center gap-2">
                        <span className="font-bold text-white text-sm">{c.name}</span>
                        <Badge variant="outline" className="text-[10px] font-mono border-zinc-700 text-zinc-400">
                          {c.type_name}
                        </Badge>
                      </div>
                      <p className="text-xs font-mono text-zinc-400">Host / ID: {c.host_or_identifier}</p>
                    </div>

                    <div className="flex items-center gap-2 flex-wrap">
                      <div className="bg-zinc-950 px-3 py-1.5 rounded-lg border border-zinc-800 font-mono text-xs text-zinc-300 min-w-44 flex items-center justify-between">
                        <span>{revealedSecrets[c.id] ? c.password_or_secret : "••••••••••••••••"}</span>
                        <button
                          onClick={() => toggleSecret(c.id)}
                          className="text-zinc-500 hover:text-zinc-300 ml-2"
                          title={revealedSecrets[c.id] ? "Hide" : "Reveal"}
                        >
                          {revealedSecrets[c.id] ? <EyeOff className="w-3.5 h-3.5" /> : <Eye className="w-3.5 h-3.5" />}
                        </button>
                      </div>

                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleCopy(c.id, c.password_or_secret)}
                        className="border-zinc-800 text-xs h-8 px-2.5"
                        title="Copy Secret"
                      >
                        {copiedId === c.id ? (
                          <Check className="w-3.5 h-3.5 text-emerald-400" />
                        ) : (
                          <Copy className="w-3.5 h-3.5 text-zinc-400" />
                        )}
                      </Button>

                      {project.can_edit && (
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => handleDeleteCred(c.id)}
                          className="border-zinc-800 text-xs h-8 px-2.5 text-rose-400 hover:bg-rose-950/30 hover:border-rose-800/60"
                          title="Delete Credential"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </Button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        {/* TAB 4: DISCUSSION */}
        {activeTab === "discussion" && (
          <div className="space-y-5">
            <div>
              <h3 className="text-base font-bold text-white">Project Discussion Thread</h3>
              <p className="text-xs text-zinc-400">Collaborate, leave updates, and align with teammates.</p>
            </div>

            {/* Comment Form */}
            <form onSubmit={handleAddComment} className="p-4 rounded-xl bg-zinc-900/80 border border-zinc-800 space-y-3">
              <textarea
                rows={3}
                required
                placeholder="Share an update or comment on this project..."
                value={commentText}
                onChange={(e) => setCommentText(e.target.value)}
                className="w-full bg-zinc-950 border border-zinc-800 rounded-lg p-3 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 resize-none"
              />
              <div className="flex justify-end">
                <Button
                  type="submit"
                  disabled={isSubmittingComment || !commentText.trim()}
                  className="bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs gap-1.5 h-8 px-3"
                >
                  {isSubmittingComment ? (
                    <Loader2 className="w-3.5 h-3.5 animate-spin" />
                  ) : (
                    <Send className="w-3.5 h-3.5" />
                  )}
                  Post Comment
                </Button>
              </div>
            </form>

            {/* Comment List */}
            {comments.length === 0 ? (
              <p className="text-xs text-zinc-500 text-center py-8 italic font-mono">
                No comments yet. Start the conversation!
              </p>
            ) : (
              <div className="space-y-3">
                {comments.map((comm) => (
                  <div key={comm.id} className="p-4 rounded-xl bg-zinc-900/60 border border-zinc-800/80 space-y-2">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2.5">
                        <Avatar className="w-7 h-7 border border-zinc-700">
                          {comm.user.profile_image && (
                            <AvatarImage src={comm.user.profile_image} alt={comm.user.name} />
                          )}
                          <AvatarFallback className="bg-zinc-800 text-[10px] font-bold text-zinc-300">
                            {comm.user.avatar}
                          </AvatarFallback>
                        </Avatar>
                        <span className="text-xs font-bold text-zinc-200">{comm.user.name}</span>
                        <span className="text-[11px] font-mono text-zinc-500">• {comm.created_at}</span>
                      </div>

                      {comm.can_delete && (
                        <button
                          onClick={() => handleDeleteComment(comm.id)}
                          className="text-zinc-600 hover:text-rose-400 p-1"
                          title="Delete comment"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      )}
                    </div>
                    <p className="text-xs sm:text-sm text-zinc-300 whitespace-pre-wrap pl-9 leading-relaxed">
                      {comm.content}
                    </p>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        {/* MODAL: CREATE TASK */}
        {showTaskModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-150">
            <div className="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
              <div className="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 className="font-bold text-base text-white flex items-center gap-2">
                  <CheckSquare className="w-4 h-4 text-emerald-400" /> New Project Task
                </h3>
                <button onClick={() => setShowTaskModal(false)} className="text-zinc-400 hover:text-white">
                  <X className="w-5 h-5" />
                </button>
              </div>

              <form onSubmit={handleCreateTask} className="space-y-4">
                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Task Title *</label>
                  <input
                    type="text"
                    required
                    placeholder="Enter task title"
                    value={taskForm.title}
                    onChange={(e) => setTaskForm({ ...taskForm, title: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60"
                  />
                </div>

                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Description</label>
                  <textarea
                    rows={3}
                    placeholder="Task details and instructions..."
                    value={taskForm.description}
                    onChange={(e) => setTaskForm({ ...taskForm, description: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60"
                  />
                </div>

                <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Priority</label>
                    <select
                      value={taskForm.priority}
                      onChange={(e) => setTaskForm({ ...taskForm, priority: Number(e.target.value) })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none"
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
                      value={taskForm.type}
                      onChange={(e) => setTaskForm({ ...taskForm, type: Number(e.target.value) })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none"
                    >
                      <option value={1}>Feature</option>
                      <option value={2}>Bug</option>
                      <option value={3}>Task</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Due Date</label>
                    <input
                      type="date"
                      value={taskForm.due_date}
                      onChange={(e) => setTaskForm({ ...taskForm, due_date: e.target.value })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Assignee</label>
                  <select
                    value={taskForm.assigned_to}
                    onChange={(e) => setTaskForm({ ...taskForm, assigned_to: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none"
                  >
                    <option value="">Unassigned</option>
                    {team_members.map((m) => (
                      <option key={m.id} value={m.id}>
                        {m.name} ({m.email})
                      </option>
                    ))}
                  </select>
                </div>

                <div className="flex justify-end gap-2 pt-2 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setShowTaskModal(false)}
                    className="border-zinc-800 text-xs"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    disabled={isSubmitting}
                    size="sm"
                    className="bg-emerald-600 hover:bg-emerald-500 text-white text-xs"
                  >
                    {isSubmitting ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : "Create Task"}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* MODAL: CREATE NOTE */}
        {showNoteModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-150">
            <div className="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
              <div className="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 className="font-bold text-base text-white flex items-center gap-2">
                  <FileText className="w-4 h-4 text-emerald-400" /> New Project Note
                </h3>
                <button onClick={() => setShowNoteModal(false)} className="text-zinc-400 hover:text-white">
                  <X className="w-5 h-5" />
                </button>
              </div>

              <form onSubmit={handleCreateNote} className="space-y-4">
                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Note Title *</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Architecture Overview"
                    value={noteForm.title}
                    onChange={(e) => setNoteForm({ ...noteForm, title: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60"
                  />
                </div>

                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Content (Markdown supported) *</label>
                  <textarea
                    rows={6}
                    required
                    placeholder="Enter document text..."
                    value={noteForm.description}
                    onChange={(e) => setNoteForm({ ...noteForm, description: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 font-mono"
                  />
                </div>

                <div className="flex justify-end gap-2 pt-2 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setShowNoteModal(false)}
                    className="border-zinc-800 text-xs"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    disabled={isSubmitting}
                    size="sm"
                    className="bg-emerald-600 hover:bg-emerald-500 text-white text-xs"
                  >
                    {isSubmitting ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : "Save Note"}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* MODAL: CREATE CREDENTIAL */}
        {showCredModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-150">
            <div className="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
              <div className="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 className="font-bold text-base text-white flex items-center gap-2">
                  <Key className="w-4 h-4 text-emerald-400" /> Add Project Credential
                </h3>
                <button onClick={() => setShowCredModal(false)} className="text-zinc-400 hover:text-white">
                  <X className="w-5 h-5" />
                </button>
              </div>

              <form onSubmit={handleCreateCred} className="space-y-4">
                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Credential Name *</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Production Database"
                    value={credForm.name}
                    onChange={(e) => setCredForm({ ...credForm, name: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60"
                  />
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Environment / Type</label>
                    <select
                      value={credForm.type}
                      onChange={(e) => setCredForm({ ...credForm, type: Number(e.target.value) })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none"
                    >
                      <option value={1}>Development</option>
                      <option value={2}>Staging</option>
                      <option value={3}>Production</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Host / Identifier</label>
                    <input
                      type="text"
                      placeholder="e.g. 192.168.1.100 or aws-db"
                      value={credForm.host_or_identifier}
                      onChange={(e) => setCredForm({ ...credForm, host_or_identifier: e.target.value })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-100 placeholder-zinc-500 focus:outline-none"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Password or Secret Key *</label>
                  <input
                    type="text"
                    required
                    placeholder="Enter key or password"
                    value={credForm.password_or_secret}
                    onChange={(e) => setCredForm({ ...credForm, password_or_secret: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-100 font-mono placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60"
                  />
                </div>

                <div className="flex justify-end gap-2 pt-2 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setShowCredModal(false)}
                    className="border-zinc-800 text-xs"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    disabled={isSubmitting}
                    size="sm"
                    className="bg-emerald-600 hover:bg-emerald-500 text-white text-xs"
                  >
                    {isSubmitting ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : "Save Credential"}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* MODAL: EDIT PROJECT */}
        {showEditModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-150">
            <div className="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
              <div className="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 className="font-bold text-base text-white flex items-center gap-2">
                  <Edit2 className="w-4 h-4 text-emerald-400" /> Edit Project
                </h3>
                <button onClick={() => setShowEditModal(false)} className="text-zinc-400 hover:text-white">
                  <X className="w-5 h-5" />
                </button>
              </div>

              <form onSubmit={handleEditProject} className="space-y-4">
                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Project Name *</label>
                  <input
                    type="text"
                    required
                    value={editForm.name}
                    onChange={(e) => setEditForm({ ...editForm, name: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60"
                  />
                </div>

                <div className="grid grid-cols-3 gap-3">
                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Theme Color</label>
                    <input
                      type="color"
                      value={editForm.theme}
                      onChange={(e) => setEditForm({ ...editForm, theme: e.target.value })}
                      className="w-full h-9 bg-zinc-950 border border-zinc-800 rounded-xl p-1 cursor-pointer"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Status</label>
                    <select
                      value={editForm.status}
                      onChange={(e) => setEditForm({ ...editForm, status: Number(e.target.value) })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none"
                    >
                      <option value={1}>To Do</option>
                      <option value={2}>In Progress</option>
                      <option value={3}>Completed</option>
                      <option value={4}>On Hold</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-mono text-zinc-400 mb-1">Priority</label>
                    <select
                      value={editForm.priority}
                      onChange={(e) => setEditForm({ ...editForm, priority: Number(e.target.value) })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2 text-xs text-zinc-200 focus:outline-none"
                    >
                      <option value={1}>Low</option>
                      <option value={2}>Medium</option>
                      <option value={3}>High</option>
                      <option value={4}>Urgent</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-mono text-zinc-400 mb-1">Description</label>
                  <textarea
                    rows={3}
                    value={editForm.description}
                    onChange={(e) => setEditForm({ ...editForm, description: e.target.value })}
                    className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-xs text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60"
                  />
                </div>

                <div className="flex justify-end gap-2 pt-2 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setShowEditModal(false)}
                    className="border-zinc-800 text-xs"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    disabled={isSubmitting}
                    size="sm"
                    className="bg-emerald-600 hover:bg-emerald-500 text-white text-xs"
                  >
                    {isSubmitting ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : "Save Changes"}
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* MODAL: DELETE PROJECT CONFIRMATION */}
        {showDeleteModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-150">
            <div className="bg-zinc-900 border border-rose-800/60 rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
              <div className="flex items-center gap-3 text-rose-400">
                <AlertCircle className="w-6 h-6 shrink-0" />
                <h3 className="font-bold text-base text-white">Delete Project</h3>
              </div>

              <p className="text-xs text-zinc-300 leading-relaxed">
                Are you sure you want to delete <span className="font-bold text-white">"{project.name}"</span>? All
                associated tasks, notes, and credentials will be moved to the Recycle Bin.
              </p>

              <div className="flex justify-end gap-2 pt-3 border-t border-zinc-800">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => setShowDeleteModal(false)}
                  className="border-zinc-800 text-xs"
                >
                  Cancel
                </Button>
                <Button
                  type="button"
                  onClick={handleDeleteProject}
                  size="sm"
                  className="bg-rose-600 hover:bg-rose-500 text-white text-xs"
                >
                  Yes, Delete Project
                </Button>
              </div>
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}

