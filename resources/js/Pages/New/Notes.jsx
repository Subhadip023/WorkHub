import React, { useState, useMemo } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Link, router, usePage } from "@inertiajs/react";
import {
  FileText,
  Plus,
  Search,
  Lock,
  FolderKanban,
  CheckSquare,
  Building2,
  Download,
  Edit2,
  Trash2,
  ExternalLink,
  MessageSquare,
  Clock,
  Sparkles,
  BookOpen,
  Filter,
  X,
  Grid,
  List,
  Calendar,
  User,
  Send,
  Check,
  ChevronRight,
  Eye,
  Tag,
  Loader2,
  Layers,
  ArrowUpRight
} from "lucide-react";

import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";

export default function Notes({
  initial_notes = [],
  stats = {},
  projects = [],
  tasks = [],
  organizations = [],
  current_workspace_name = "",
}) {
  const { flash = {}, auth = {} } = usePage().props;
  const currentUser = auth?.user;

  // Filter and view states
  const [activeTab, setActiveTab] = useState("all"); // 'all' | 'personal' | 'project' | 'task' | 'workspace'
  const [searchQuery, setSearchQuery] = useState("");
  const [viewMode, setViewMode] = useState("grid"); // 'grid' | 'list'

  // Modals state
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [editingNote, setEditingNote] = useState(null);
  const [readingNote, setReadingNote] = useState(null);
  const [deletingNote, setDeletingNote] = useState(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Form state
  const [formData, setFormData] = useState({
    title: "",
    description: "",
    note_type: 4, // 4: Personal, 1: Project, 2: Task, 3: Organization
    note_type_id: "",
  });

  // Comment state for reader view
  const [commentText, setCommentText] = useState("");
  const [isSubmittingComment, setIsSubmittingComment] = useState(false);

  // Open Create Modal
  const openCreateModal = () => {
    setEditingNote(null);
    setFormData({
      title: "",
      description: "",
      note_type: 4,
      note_type_id: "",
    });
    setShowCreateModal(true);
  };

  // Open Edit Modal
  const openEditModal = (note) => {
    setEditingNote(note);
    setFormData({
      title: note.title,
      description: note.description,
      note_type: note.note_type,
      note_type_id: note.note_type_id || "",
    });
    setShowCreateModal(true);
  };

  // Handle Form Submit
  const handleFormSubmit = (e) => {
    e.preventDefault();
    setIsSubmitting(true);

    if (editingNote) {
      router.patch(
        `/v2/notes/${editingNote.id}`,
        {
          title: formData.title,
          description: formData.description,
        },
        {
          preserveScroll: true,
          onSuccess: () => {
            setShowCreateModal(false);
            setEditingNote(null);
            setIsSubmitting(false);
            if (readingNote && readingNote.id === editingNote.id) {
              setReadingNote({
                ...readingNote,
                title: formData.title,
                description: formData.description,
              });
            }
          },
          onError: () => setIsSubmitting(false),
        }
      );
    } else {
      router.post("/v2/notes", formData, {
        preserveScroll: true,
        onSuccess: () => {
          setShowCreateModal(false);
          setIsSubmitting(false);
        },
        onError: () => setIsSubmitting(false),
      });
    }
  };

  // Handle Delete Note
  const confirmDelete = () => {
    if (!deletingNote) return;
    setIsSubmitting(true);
    router.delete(`/v2/notes/${deletingNote.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        setDeletingNote(null);
        if (readingNote && readingNote.id === deletingNote.id) {
          setReadingNote(null);
        }
        setIsSubmitting(false);
      },
      onError: () => setIsSubmitting(false),
    });
  };

  // Handle Post Comment inside document reader
  const handlePostComment = (e) => {
    e.preventDefault();
    if (!commentText.trim() || !readingNote) return;
    setIsSubmittingComment(true);

    router.post(
      "/comments",
      {
        commentable_type: "note",
        commentable_id: readingNote.id,
        content: commentText,
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

  // Filtered Notes list
  const filteredNotes = useMemo(() => {
    return initial_notes.filter((note) => {
      // Tab filter
      if (activeTab === "personal" && note.note_type !== 4) return false;
      if (activeTab === "project" && note.note_type !== 1) return false;
      if (activeTab === "task" && note.note_type !== 2) return false;
      if (activeTab === "workspace" && note.note_type !== 3) return false;

      // Search filter
      if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase();
        const matchesTitle = note.title.toLowerCase().includes(q);
        const matchesContent = note.preview_text.toLowerCase().includes(q);
        const matchesAuthor = note.author?.name?.toLowerCase().includes(q);
        const matchesNoteable = note.noteable?.name?.toLowerCase().includes(q);
        if (!matchesTitle && !matchesContent && !matchesAuthor && !matchesNoteable) {
          return false;
        }
      }

      return true;
    });
  }, [initial_notes, activeTab, searchQuery]);

  // Type styling helper
  const getTypeBadge = (type) => {
    switch (type) {
      case 4: // Personal
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <Lock className="w-3 h-3" /> Personal
          </span>
        );
      case 1: // Project
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
            <FolderKanban className="w-3 h-3" /> Project
          </span>
        );
      case 2: // Task
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
            <CheckSquare className="w-3 h-3" /> Task
          </span>
        );
      case 3: // Workspace
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
            <Building2 className="w-3 h-3" /> Workspace
          </span>
        );
      default:
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-800 text-zinc-300 border border-zinc-700">
            <FileText className="w-3 h-3" /> Note
          </span>
        );
    }
  };

  return (
    <DashboardLayout title="Notes & Documentation" activeItem="notes">
      <div className="space-y-8 pb-16 max-w-7xl mx-auto">
        {/* Header Section */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-800 pb-6">
          <div>
            <div className="flex items-center gap-2 text-xs font-mono text-emerald-400 uppercase tracking-widest mb-1.5 font-semibold">
              <Sparkles className="w-3.5 h-3.5" />
              <span>Knowledge Base & Docs</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
              Notes & Documentation
              <span className="text-xs font-mono px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">
                v2 Suite
              </span>
            </h1>
            <p className="text-sm text-zinc-400 mt-1">
              Create, organize, and search personal snippets, project specs, and organization-wide documentation.
            </p>
          </div>

          <div className="flex items-center gap-3 shrink-0">
            <Button
              onClick={openCreateModal}
              className="bg-emerald-500 hover:bg-emerald-600 text-black font-semibold text-sm gap-2 rounded-xl h-10 px-4 shadow-lg shadow-emerald-500/10 cursor-pointer"
            >
              <Plus className="h-4 w-4 stroke-[2.5]" /> Create Document
            </Button>
          </div>
        </div>

        {/* Stats Summary Cards */}
        <div className="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
          <div
            onClick={() => setActiveTab("all")}
            className={`p-4 rounded-xl border transition-all cursor-pointer ${
              activeTab === "all"
                ? "bg-zinc-900 border-emerald-500/50 shadow-md shadow-emerald-500/5"
                : "bg-zinc-950/70 border-zinc-800/80 hover:border-zinc-700 hover:bg-zinc-900/40"
            }`}
          >
            <div className="flex items-center justify-between text-xs text-zinc-400 font-mono mb-2">
              <span>All Docs</span>
              <FileText className="w-4 h-4 text-zinc-400" />
            </div>
            <div className="text-2xl font-bold text-white font-mono">{stats.total || initial_notes.length}</div>
          </div>

          <div
            onClick={() => setActiveTab("personal")}
            className={`p-4 rounded-xl border transition-all cursor-pointer ${
              activeTab === "personal"
                ? "bg-zinc-900 border-emerald-500/50 shadow-md shadow-emerald-500/5"
                : "bg-zinc-950/70 border-zinc-800/80 hover:border-zinc-700 hover:bg-zinc-900/40"
            }`}
          >
            <div className="flex items-center justify-between text-xs text-emerald-400 font-mono mb-2">
              <span>Personal</span>
              <Lock className="w-4 h-4 text-emerald-400" />
            </div>
            <div className="text-2xl font-bold text-emerald-400 font-mono">{stats.personal || 0}</div>
          </div>

          <div
            onClick={() => setActiveTab("project")}
            className={`p-4 rounded-xl border transition-all cursor-pointer ${
              activeTab === "project"
                ? "bg-zinc-900 border-blue-500/50 shadow-md shadow-blue-500/5"
                : "bg-zinc-950/70 border-zinc-800/80 hover:border-zinc-700 hover:bg-zinc-900/40"
            }`}
          >
            <div className="flex items-center justify-between text-xs text-blue-400 font-mono mb-2">
              <span>Projects</span>
              <FolderKanban className="w-4 h-4 text-blue-400" />
            </div>
            <div className="text-2xl font-bold text-blue-400 font-mono">{stats.projects || 0}</div>
          </div>

          <div
            onClick={() => setActiveTab("task")}
            className={`p-4 rounded-xl border transition-all cursor-pointer ${
              activeTab === "task"
                ? "bg-zinc-900 border-amber-500/50 shadow-md shadow-amber-500/5"
                : "bg-zinc-950/70 border-zinc-800/80 hover:border-zinc-700 hover:bg-zinc-900/40"
            }`}
          >
            <div className="flex items-center justify-between text-xs text-amber-400 font-mono mb-2">
              <span>Tasks</span>
              <CheckSquare className="w-4 h-4 text-amber-400" />
            </div>
            <div className="text-2xl font-bold text-amber-400 font-mono">{stats.tasks || 0}</div>
          </div>

          <div
            onClick={() => setActiveTab("workspace")}
            className={`col-span-2 sm:col-span-1 p-4 rounded-xl border transition-all cursor-pointer ${
              activeTab === "workspace"
                ? "bg-zinc-900 border-cyan-500/50 shadow-md shadow-cyan-500/5"
                : "bg-zinc-950/70 border-zinc-800/80 hover:border-zinc-700 hover:bg-zinc-900/40"
            }`}
          >
            <div className="flex items-center justify-between text-xs text-cyan-400 font-mono mb-2">
              <span>Workspace</span>
              <Building2 className="w-4 h-4 text-cyan-400" />
            </div>
            <div className="text-2xl font-bold text-cyan-400 font-mono">{stats.workspace || 0}</div>
          </div>
        </div>

        {/* Filter Bar & Search Toolbar */}
        <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-zinc-900/70 border border-zinc-800 p-3 rounded-2xl">
          {/* Tabs */}
          <div className="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
            {[
              { id: "all", label: "All Docs", icon: FileText },
              { id: "personal", label: "Personal", icon: Lock },
              { id: "project", label: "Projects", icon: FolderKanban },
              { id: "task", label: "Tasks", icon: CheckSquare },
              { id: "workspace", label: "Workspace", icon: Building2 },
            ].map((tab) => {
              const Icon = tab.icon;
              const isActive = activeTab === tab.id;
              return (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-medium transition-all shrink-0 cursor-pointer ${
                    isActive
                      ? "bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-semibold"
                      : "text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800/60"
                  }`}
                >
                  <Icon className="w-3.5 h-3.5" />
                  <span>{tab.label}</span>
                </button>
              );
            })}
          </div>

          {/* Search & View Toggle */}
          <div className="flex items-center gap-2.5">
            <div className="relative flex-1 sm:w-64">
              <Search className="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search documents, tags..."
                className="w-full bg-black/60 border border-zinc-800 rounded-xl pl-9 pr-3 py-1.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
              />
              {searchQuery && (
                <button
                  onClick={() => setSearchQuery("")}
                  className="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300"
                >
                  <X className="w-3.5 h-3.5" />
                </button>
              )}
            </div>

            <div className="flex items-center bg-black/60 border border-zinc-800 rounded-xl p-0.5">
              <button
                onClick={() => setViewMode("grid")}
                className={`p-1.5 rounded-lg text-xs transition-colors cursor-pointer ${
                  viewMode === "grid" ? "bg-zinc-800 text-white" : "text-zinc-400 hover:text-white"
                }`}
                title="Grid Cards View"
              >
                <Grid className="w-3.5 h-3.5" />
              </button>
              <button
                onClick={() => setViewMode("list")}
                className={`p-1.5 rounded-lg text-xs transition-colors cursor-pointer ${
                  viewMode === "list" ? "bg-zinc-800 text-white" : "text-zinc-400 hover:text-white"
                }`}
                title="Table List View"
              >
                <List className="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>

        {/* Content: Empty State */}
        {filteredNotes.length === 0 ? (
          <div className="py-20 border border-dashed border-zinc-800 rounded-2xl text-center bg-zinc-950/40 p-8 space-y-4">
            <div className="h-14 w-14 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mx-auto text-zinc-400 shadow-xl">
              <BookOpen className="w-7 h-7 text-emerald-400" />
            </div>
            <div className="space-y-1">
              <h3 className="text-lg font-bold text-white">No documents found</h3>
              <p className="text-zinc-400 text-xs sm:text-sm max-w-sm mx-auto">
                {searchQuery
                  ? `No documentation match your search query "${searchQuery}". Try clearing the search.`
                  : "You haven't created any documents in this category yet. Get started now!"}
              </p>
            </div>
            <div>
              <Button
                onClick={openCreateModal}
                size="sm"
                className="bg-emerald-500 hover:bg-emerald-600 text-black font-semibold text-xs gap-1.5 rounded-xl h-9 px-4 cursor-pointer"
              >
                <Plus className="w-3.5 h-3.5" /> Create New Document
              </Button>
            </div>
          </div>
        ) : viewMode === "grid" ? (
          /* Grid View */
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            {filteredNotes.map((note) => (
              <div
                key={note.id}
                className="group relative bg-zinc-900/60 hover:bg-zinc-900/90 border border-zinc-800 hover:border-zinc-700/80 rounded-2xl p-5 flex flex-col justify-between transition-all duration-200 hover:shadow-xl hover:shadow-black/50"
              >
                <div className="space-y-3">
                  {/* Category & Action icons */}
                  <div className="flex items-center justify-between gap-2">
                    {getTypeBadge(note.note_type)}

                    <div className="flex items-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                      <a
                        href={note.pdf_url}
                        target="_blank"
                        rel="noreferrer"
                        className="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors"
                        title="Download PDF"
                      >
                        <Download className="w-3.5 h-3.5" />
                      </a>
                      {note.can_edit && (
                        <button
                          onClick={() => openEditModal(note)}
                          className="p-1.5 rounded-lg text-zinc-400 hover:text-emerald-400 hover:bg-zinc-800 transition-colors cursor-pointer"
                          title="Edit Document"
                        >
                          <Edit2 className="w-3.5 h-3.5" />
                        </button>
                      )}
                      {note.can_delete && (
                        <button
                          onClick={() => setDeletingNote(note)}
                          className="p-1.5 rounded-lg text-zinc-400 hover:text-rose-400 hover:bg-zinc-800 transition-colors cursor-pointer"
                          title="Delete Document"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      )}
                    </div>
                  </div>

                  {/* Title & Preview */}
                  <div
                    onClick={() => setReadingNote(note)}
                    className="cursor-pointer space-y-1.5 group/title"
                  >
                    <h3 className="text-base font-bold text-white group-hover/title:text-emerald-400 transition-colors line-clamp-2 leading-snug">
                      {note.title}
                    </h3>
                    <p className="text-xs text-zinc-400 line-clamp-3 leading-relaxed">
                      {note.preview_text || "No document body preview."}
                    </p>
                  </div>
                </div>

                {/* Footer Metadata */}
                <div className="pt-4 mt-4 border-t border-zinc-800/80 flex items-center justify-between text-xs text-zinc-400">
                  <div className="flex items-center gap-2 min-w-0">
                    {note.noteable && (
                      <span className="truncate font-mono text-[11px] text-zinc-400 bg-black/60 px-2 py-0.5 rounded-md border border-zinc-800">
                        {note.noteable.name}
                      </span>
                    )}
                  </div>

                  <div className="flex items-center gap-3 shrink-0 font-mono text-[11px]">
                    {note.comments_count > 0 && (
                      <span className="flex items-center gap-1 text-zinc-400">
                        <MessageSquare className="w-3 h-3" />
                        {note.comments_count}
                      </span>
                    )}
                    <span className="text-zinc-500">{note.created_at_human}</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          /* List View */
          <div className="bg-zinc-900/60 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-zinc-950/70 border-b border-zinc-800 text-zinc-400 uppercase font-mono text-[11px]">
                  <tr>
                    <th className="py-3.5 px-4 font-semibold">Document Title</th>
                    <th className="py-3.5 px-4 font-semibold">Scope</th>
                    <th className="py-3.5 px-4 font-semibold">Related Entity</th>
                    <th className="py-3.5 px-4 font-semibold">Author</th>
                    <th className="py-3.5 px-4 font-semibold">Date</th>
                    <th className="py-3.5 px-4 font-semibold text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-zinc-800/70 text-zinc-300">
                  {filteredNotes.map((note) => (
                    <tr
                      key={note.id}
                      className="hover:bg-zinc-800/40 transition-colors group cursor-pointer"
                      onClick={() => setReadingNote(note)}
                    >
                      <td className="py-3.5 px-4 font-medium text-white max-w-xs truncate">
                        <div className="flex items-center gap-2">
                          <FileText className="w-3.5 h-3.5 text-zinc-400 group-hover:text-emerald-400 shrink-0" />
                          <span className="truncate group-hover:text-emerald-400 transition-colors">
                            {note.title}
                          </span>
                        </div>
                      </td>
                      <td className="py-3.5 px-4 whitespace-nowrap">{getTypeBadge(note.note_type)}</td>
                      <td className="py-3.5 px-4 whitespace-nowrap text-zinc-400 font-mono text-[11px]">
                        {note.noteable?.name || "—"}
                      </td>
                      <td className="py-3.5 px-4 whitespace-nowrap">
                        <div className="flex items-center gap-2">
                          <Avatar className="h-5 w-5 border border-zinc-700">
                            {note.author?.profile_image && (
                              <AvatarImage src={note.author.profile_image} />
                            )}
                            <AvatarFallback className="bg-zinc-800 text-[9px] text-zinc-300 font-bold">
                              {note.author?.avatar || "UN"}
                            </AvatarFallback>
                          </Avatar>
                          <span className="text-zinc-300 text-xs">{note.author?.name}</span>
                        </div>
                      </td>
                      <td className="py-3.5 px-4 whitespace-nowrap text-zinc-500 font-mono text-[11px]">
                        {note.created_at}
                      </td>
                      <td
                        className="py-3.5 px-4 text-right whitespace-nowrap"
                        onClick={(e) => e.stopPropagation()}
                      >
                        <div className="flex items-center justify-end gap-1">
                          <a
                            href={note.pdf_url}
                            target="_blank"
                            rel="noreferrer"
                            className="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors"
                            title="Download PDF"
                          >
                            <Download className="w-3.5 h-3.5" />
                          </a>
                          {note.can_edit && (
                            <button
                              onClick={() => openEditModal(note)}
                              className="p-1.5 rounded-lg text-zinc-400 hover:text-emerald-400 hover:bg-zinc-800 transition-colors cursor-pointer"
                              title="Edit"
                            >
                              <Edit2 className="w-3.5 h-3.5" />
                            </button>
                          )}
                          {note.can_delete && (
                            <button
                              onClick={() => setDeletingNote(note)}
                              className="p-1.5 rounded-lg text-zinc-400 hover:text-rose-400 hover:bg-zinc-800 transition-colors cursor-pointer"
                              title="Delete"
                            >
                              <Trash2 className="w-3.5 h-3.5" />
                            </button>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>

      {/* DOCUMENT READER MODAL */}
      {readingNote && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/80 backdrop-blur-md animate-in fade-in">
          <div className="relative w-full max-w-4xl max-h-[90vh] bg-zinc-950 border border-zinc-800 rounded-3xl shadow-2xl flex flex-col overflow-hidden">
            {/* Header */}
            <div className="p-5 sm:p-6 border-b border-zinc-800 flex items-start justify-between gap-4 bg-zinc-900/60">
              <div className="space-y-2 max-w-2xl">
                <div className="flex items-center gap-2.5">
                  {getTypeBadge(readingNote.note_type)}
                  {readingNote.noteable && (
                    <span className="font-mono text-xs text-zinc-400 bg-zinc-800/80 px-2 py-0.5 rounded-md border border-zinc-700">
                      {readingNote.noteable.type}: {readingNote.noteable.name}
                    </span>
                  )}
                </div>
                <h2 className="text-xl sm:text-2xl font-bold text-white leading-tight">
                  {readingNote.title}
                </h2>
                <div className="flex items-center gap-3 text-xs text-zinc-400 font-mono">
                  <span>By {readingNote.author?.name}</span>
                  <span>•</span>
                  <span>Created {readingNote.created_at}</span>
                </div>
              </div>

              <div className="flex items-center gap-2">
                <a
                  href={readingNote.pdf_url}
                  target="_blank"
                  rel="noreferrer"
                  className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-xs font-semibold text-zinc-200 hover:text-white transition-all shadow-sm"
                >
                  <Download className="w-3.5 h-3.5 text-emerald-400" />
                  <span className="hidden sm:inline">PDF</span>
                </a>
                {readingNote.can_edit && (
                  <Button
                    onClick={() => {
                      const noteToEdit = readingNote;
                      setReadingNote(null);
                      openEditModal(noteToEdit);
                    }}
                    size="sm"
                    variant="outline"
                    className="border-zinc-800 bg-zinc-900 text-xs gap-1.5 rounded-xl h-8 px-3 text-zinc-200"
                  >
                    <Edit2 className="w-3.5 h-3.5 text-zinc-400" /> Edit
                  </Button>
                )}
                <button
                  onClick={() => setReadingNote(null)}
                  className="h-8 w-8 rounded-xl bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-zinc-400 hover:text-white flex items-center justify-center transition-colors cursor-pointer"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>
            </div>

            {/* Document Body */}
            <div className="flex-1 overflow-y-auto p-6 sm:p-8 space-y-8">
              <div className="prose prose-invert max-w-none text-zinc-200 text-sm sm:text-base leading-relaxed whitespace-pre-wrap font-sans">
                {readingNote.description ? (
                  <div
                    dangerouslySetInnerHTML={{ __html: readingNote.description }}
                    className="note-rendered-content space-y-3"
                  />
                ) : (
                  <p className="text-zinc-500 italic">No content in this document.</p>
                )}
              </div>

              {/* Discussion Thread Section */}
              <div className="pt-8 border-t border-zinc-800/80 space-y-5">
                <div className="flex items-center gap-2">
                  <MessageSquare className="w-4 h-4 text-emerald-400" />
                  <h4 className="text-sm font-bold text-white font-mono uppercase tracking-wider">
                    Discussion Thread ({readingNote.comments?.length || 0})
                  </h4>
                </div>

                {/* Comment Feed */}
                <div className="space-y-3">
                  {readingNote.comments && readingNote.comments.length > 0 ? (
                    readingNote.comments.map((comm) => (
                      <div
                        key={comm.id}
                        className="p-3.5 rounded-xl bg-zinc-900/60 border border-zinc-800/80 flex items-start gap-3"
                      >
                        <Avatar className="h-7 w-7 border border-zinc-700 shrink-0">
                          {comm.user.profile_image && (
                            <AvatarImage src={comm.user.profile_image} />
                          )}
                          <AvatarFallback className="bg-zinc-800 text-[10px] text-zinc-300 font-bold">
                            {comm.user.avatar}
                          </AvatarFallback>
                        </Avatar>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center justify-between text-xs">
                            <span className="font-semibold text-white">{comm.user.name}</span>
                            <span className="font-mono text-zinc-500 text-[11px]">{comm.time}</span>
                          </div>
                          <p className="text-xs text-zinc-300 mt-1 whitespace-pre-wrap">{comm.text}</p>
                        </div>
                      </div>
                    ))
                  ) : (
                    <p className="text-xs text-zinc-500 font-italic">No comments on this note yet.</p>
                  )}
                </div>

                {/* Add Comment Input */}
                <form onSubmit={handlePostComment} className="flex gap-2">
                  <input
                    type="text"
                    value={commentText}
                    onChange={(e) => setCommentText(e.target.value)}
                    placeholder="Write a reply or comment on this doc..."
                    className="flex-1 bg-zinc-900 border border-zinc-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-emerald-500"
                  />
                  <Button
                    type="submit"
                    disabled={isSubmittingComment || !commentText.trim()}
                    size="sm"
                    className="bg-emerald-500 hover:bg-emerald-600 text-black font-semibold text-xs gap-1.5 rounded-xl h-9 px-3.5 shrink-0"
                  >
                    {isSubmittingComment ? (
                      <Loader2 className="w-3.5 h-3.5 animate-spin" />
                    ) : (
                      <Send className="w-3.5 h-3.5" />
                    )}
                    <span>Send</span>
                  </Button>
                </form>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* CREATE / EDIT NOTE MODAL */}
      {showCreateModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/80 backdrop-blur-md animate-in fade-in">
          <div className="relative w-full max-w-2xl bg-zinc-950 border border-zinc-800 rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between border-b border-zinc-800 pb-4">
              <div>
                <h2 className="text-xl font-bold text-white">
                  {editingNote ? "Edit Document" : "Create New Document"}
                </h2>
                <p className="text-xs text-zinc-400 mt-0.5">
                  Save notes, specs, meeting minutes, and architectural guides.
                </p>
              </div>
              <button
                onClick={() => setShowCreateModal(false)}
                className="h-8 w-8 rounded-xl bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-zinc-400 hover:text-white flex items-center justify-center transition-colors cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <form onSubmit={handleFormSubmit} className="space-y-4">
              {/* Document Title */}
              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-zinc-300 uppercase tracking-wider font-mono">
                  Document Title <span className="text-rose-400">*</span>
                </label>
                <input
                  type="text"
                  required
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  placeholder="e.g. System Architecture Guide & API Guidelines"
                  className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                />
              </div>

              {/* Note Scope / Type */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="space-y-1.5">
                  <label className="text-xs font-semibold text-zinc-300 uppercase tracking-wider font-mono">
                    Category Scope <span className="text-rose-400">*</span>
                  </label>
                  <select
                    disabled={!!editingNote}
                    value={formData.note_type}
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        note_type: parseInt(e.target.value),
                        note_type_id: "",
                      })
                    }
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500"
                  >
                    <option value={4}>🔒 Personal Space (Private)</option>
                    <option value={1}>📁 Project Documentation</option>
                    <option value={2}>📋 Task Specification</option>
                    <option value={3}>🏢 Workspace / Organization</option>
                  </select>
                </div>

                {/* Conditional Sub-selectors */}
                {formData.note_type === 1 && (
                  <div className="space-y-1.5">
                    <label className="text-xs font-semibold text-zinc-300 uppercase tracking-wider font-mono">
                      Target Project <span className="text-rose-400">*</span>
                    </label>
                    <select
                      required
                      value={formData.note_type_id}
                      onChange={(e) => setFormData({ ...formData, note_type_id: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500"
                    >
                      <option value="">Select Project...</option>
                      {projects.map((p) => (
                        <option key={p.id} value={p.id}>
                          {p.name}
                        </option>
                      ))}
                    </select>
                  </div>
                )}

                {formData.note_type === 2 && (
                  <div className="space-y-1.5">
                    <label className="text-xs font-semibold text-zinc-300 uppercase tracking-wider font-mono">
                      Target Task <span className="text-rose-400">*</span>
                    </label>
                    <select
                      required
                      value={formData.note_type_id}
                      onChange={(e) => setFormData({ ...formData, note_type_id: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500"
                    >
                      <option value="">Select Task...</option>
                      {tasks.map((t) => (
                        <option key={t.id} value={t.id}>
                          {t.name}
                        </option>
                      ))}
                    </select>
                  </div>
                )}

                {formData.note_type === 3 && (
                  <div className="space-y-1.5">
                    <label className="text-xs font-semibold text-zinc-300 uppercase tracking-wider font-mono">
                      Target Workspace <span className="text-rose-400">*</span>
                    </label>
                    <select
                      required
                      value={formData.note_type_id}
                      onChange={(e) => setFormData({ ...formData, note_type_id: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500"
                    >
                      <option value="">Select Workspace...</option>
                      {organizations.map((org) => (
                        <option key={org.id} value={org.id}>
                          {org.name}
                        </option>
                      ))}
                    </select>
                  </div>
                )}
              </div>

              {/* Document Description */}
              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-zinc-300 uppercase tracking-wider font-mono">
                  Content / Body (Markdown supported) <span className="text-rose-400">*</span>
                </label>
                <textarea
                  required
                  rows={8}
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  placeholder="Write your note, documentation, or specifications here..."
                  className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 font-sans leading-relaxed resize-y"
                />
              </div>

              <div className="flex items-center justify-end gap-3 pt-4 border-t border-zinc-800">
                <Button
                  type="button"
                  variant="ghost"
                  onClick={() => setShowCreateModal(false)}
                  className="text-zinc-400 hover:text-white rounded-xl h-10 px-4 text-xs font-semibold"
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  disabled={isSubmitting}
                  className="bg-emerald-500 hover:bg-emerald-600 text-black font-semibold text-xs gap-2 rounded-xl h-10 px-5 shadow-lg shadow-emerald-500/10 cursor-pointer"
                >
                  {isSubmitting ? (
                    <Loader2 className="w-4 h-4 animate-spin" />
                  ) : (
                    <Check className="w-4 h-4 stroke-[2.5]" />
                  )}
                  <span>{editingNote ? "Save Changes" : "Publish Document"}</span>
                </Button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* DELETE CONFIRMATION MODAL */}
      {deletingNote && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-in fade-in">
          <div className="max-w-md w-full bg-zinc-950 border border-zinc-800 rounded-2xl p-6 space-y-4 shadow-2xl">
            <div className="flex items-center gap-3">
              <div className="h-10 w-10 rounded-xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-rose-400 shrink-0">
                <Trash2 className="w-5 h-5" />
              </div>
              <div>
                <h3 className="text-base font-bold text-white">Delete Document</h3>
                <p className="text-xs text-zinc-400">This action cannot be undone.</p>
              </div>
            </div>
            <p className="text-xs text-zinc-300 leading-relaxed">
              Are you sure you want to permanently delete{" "}
              <span className="font-semibold text-white">"{deletingNote.title}"</span>?
            </p>
            <div className="flex items-center justify-end gap-2.5 pt-2">
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setDeletingNote(null)}
                className="text-zinc-400 hover:text-white text-xs rounded-xl"
              >
                Cancel
              </Button>
              <Button
                size="sm"
                disabled={isSubmitting}
                onClick={confirmDelete}
                className="bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl px-4"
              >
                {isSubmitting ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : "Delete"}
              </Button>
            </div>
          </div>
        </div>
      )}
    </DashboardLayout>
  );
}
