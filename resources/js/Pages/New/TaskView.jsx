import React, { useState, useRef, useEffect } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Link, router, usePage } from "@inertiajs/react";
import {
  ArrowLeft,
  CheckSquare,
  Square,
  Clock,
  Copy,
  Check,
  Share2,
  GitBranch,
  User,
  FolderKanban,
  AlertOctagon,
  MessageSquare,
  Send,
  Edit2,
  Calendar,
  History,
  Paperclip,
  StickyNote,
  Plus,
  Trash2,
  ExternalLink,
  Upload,
  Crown,
  AlertCircle,
  Sparkles,
  TrendingUp,
  X,
  Loader2,
  ChevronRight,
  Layers,
  Building2,
  CheckCircle2,
  AlertTriangle,
  FileText,
  Image as ImageIcon,
  Eye,
  Save,
} from "lucide-react";

import { Button } from "@/Components/ui/button";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Badge } from "@/Components/ui/badge";
import { Progress } from "@/Components/ui/progress";

export default function TaskView({
  task,
  initial_subtasks = [],
  initial_comments = [],
  initial_images = [],
  initial_notes = [],
  history_logs = [],
  projects = [],
  company_users = [],
  subtask_progress = null,
  ui = {},
}) {
  const { flash = {} } = usePage().props;

  const defaultTask = {
    id: "WH-042",
    db_id: 42,
    title: "Inertia.js React layout hydration error on cold start",
    description: `When launching the application on a cold browser refresh, React throws a client-side hydration mismatch warning. The DOM attributes generated on the server render (Laravel Inertia root view) differ slightly from the client state.

### Steps to Reproduce
1. Clear browser cache and navigate to \`/dashboard\`.
2. Observe console warning: \`Hydration failed because the initial UI does not match the server-rendered HTML.\`
3. Notice temporary layout flicker during component mounting.

### Expected Behavior
The Inertia page wrapper should hydrate seamlessly without layout reflows or console warnings.`,
    status: "In Progress",
    status_id: 2,
    priority: "Urgent",
    priority_id: 4,
    type: "Bug",
    type_id: 2,
    points: 13,
    dueDate: "Today",
    due_date_raw: "",
    project: { name: "Inertia.js Migration", theme: "#10b981", id: null },
    branch: "fix/inertia-hydration",
    created: "2 hours ago by Alex Morgan",
    created_at: "Aug 28, 2026",
    assignee: { name: "Alex Morgan", avatar: "AM", email: "alex@workhub.io" },
    reporter: { name: "Sarah Chen", avatar: "SC", email: "sarah@workhub.io" },
    completed: false,
    parentTask: null,
    can_edit: true,
    can_delete: true,
  };

  const [currentTask, setCurrentTask] = useState(task || defaultTask);
  const [subtasks, setSubtasks] = useState(initial_subtasks);
  const [comments, setComments] = useState(initial_comments);
  const [images, setImages] = useState(initial_images);
  const [notes, setNotes] = useState(initial_notes);
  const [historyLogs, setHistoryLogs] = useState(history_logs);

  // Active section tab
  const [activeTab, setActiveTab] = useState("overview"); // 'overview' | 'subtasks' | 'notes' | 'attachments' | 'history' | 'comments'

  // Input states
  const [isCopied, setIsCopied] = useState(false);
  const [commentText, setCommentText] = useState("");
  const [isSubmittingComment, setIsSubmittingComment] = useState(false);

  // Subtask creation state
  const [showAddSubtask, setShowAddSubtask] = useState(false);
  const [newSubtaskTitle, setNewSubtaskTitle] = useState("");
  const [newSubtaskPriority, setNewSubtaskPriority] = useState(2);
  const [newSubtaskType, setNewSubtaskType] = useState(1);
  const [isSubmittingSubtask, setIsSubmittingSubtask] = useState(false);

  // Note creation modal
  const [showAddNoteModal, setShowAddNoteModal] = useState(false);
  const [newNoteTitle, setNewNoteTitle] = useState("");
  const [newNoteContent, setNewNoteContent] = useState("");
  const [isSubmittingNote, setIsSubmittingNote] = useState(false);

  // Edit task modal
  const [showEditModal, setShowEditModal] = useState(false);
  const [editFormData, setEditFormData] = useState({
    title: "",
    description: "",
    status: 1,
    priority: 2,
    type: 1,
    project_id: "personal",
    assigned_to: "unassigned",
    due_date: "",
    points: "",
  });
  const [isSubmittingEdit, setIsSubmittingEdit] = useState(false);

  // Delete task modal
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [isSubmittingDelete, setIsSubmittingDelete] = useState(false);

  // Image upload
  const fileInputRef = useRef(null);
  const [isUploadingImage, setIsUploadingImage] = useState(false);
  const [previewImage, setPreviewImage] = useState(null);

  // Sync props when server sends fresh data
  useEffect(() => {
    if (task) setCurrentTask(task);
  }, [task]);

  useEffect(() => {
    if (initial_subtasks) setSubtasks(initial_subtasks);
  }, [initial_subtasks]);

  useEffect(() => {
    if (initial_comments) setComments(initial_comments);
  }, [initial_comments]);

  useEffect(() => {
    if (initial_images) setImages(initial_images);
  }, [initial_images]);

  useEffect(() => {
    if (initial_notes) setNotes(initial_notes);
  }, [initial_notes]);

  // Description interaction and inline editing
  const descContainerRef = useRef(null);
  const [isEditingDesc, setIsEditingDesc] = useState(false);
  const [descDraft, setDescDraft] = useState("");
  const [descPreview, setDescPreview] = useState(false);
  const [isSavingDesc, setIsSavingDesc] = useState(false);

  // Check if description has actual visible content
  const hasDescContent = (desc) => {
    if (!desc || typeof desc !== "string") return false;
    const stripped = desc.replace(/<[^>]*>/g, "").trim();
    const hasImages = /<img[^>]+>/i.test(desc);
    return stripped.length > 0 || hasImages;
  };

  // Process HTML or markdown description
  const formatDescHtml = (rawDesc) => {
    if (!rawDesc) return "";
    let html = rawDesc;

    // Check if it has HTML tags
    const hasHtmlTags = /<[a-z][\s\S]*>/i.test(html);

    if (hasHtmlTags) {
      // Normalize Quill checkboxes
      html = html.replace(/<li data-list="checked">([\s\S]*?)<\/li>/gi, (match, inner) => {
        const cleanInner = inner.replace(/<span class="ql-ui"[^>]*>[\s\S]*?<\/span>/gi, "").replace(/<input[^>]*>/gi, "");
        return `<li data-list="checked" class="task-item-completed list-none flex items-start gap-2.5 my-1.5"><input type="checkbox" checked class="task-desc-checkbox mt-0.5 rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer" /><span class="task-desc-text line-through text-zinc-500">${cleanInner}</span></li>`;
      });

      html = html.replace(/<li data-list="unchecked">([\s\S]*?)<\/li>/gi, (match, inner) => {
        const cleanInner = inner.replace(/<span class="ql-ui"[^>]*>[\s\S]*?<\/span>/gi, "").replace(/<input[^>]*>/gi, "");
        return `<li data-list="unchecked" class="list-none flex items-start gap-2.5 my-1.5"><input type="checkbox" class="task-desc-checkbox mt-0.5 rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer" /><span class="task-desc-text text-zinc-300">${cleanInner}</span></li>`;
      });

      // Replace markdown-style checkboxes inside <p> tags
      html = html.replace(/<p>(\s*(-|\*)?\s*\[\s*\]\s*)([\s\S]*?)<\/p>/gi, (match, p1, p2, text) => {
        return `<div class="flex items-start gap-2.5 my-1.5"><input type="checkbox" class="task-desc-checkbox mt-0.5 rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer" /><span class="task-desc-text text-zinc-300">${text}</span></div>`;
      });

      html = html.replace(/<p>(\s*(-|\*)?\s*\[[xX]\]\s*)([\s\S]*?)<\/p>/gi, (match, p1, p2, text) => {
        return `<div class="flex items-start gap-2.5 my-1.5"><input type="checkbox" checked class="task-desc-checkbox mt-0.5 rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer" /><span class="task-desc-text line-through text-zinc-500">${text}</span></div>`;
      });

      return html;
    }

    // Plain text: escape HTML characters first
    let escaped = html
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");

    // Convert markdown checklists: - [ ] or - [x]
    let converted = escaped
      .replace(/^(\s*(-|\*)?\s*\[\s*\]\s*)(.*)$/gm, '<div class="flex items-start gap-2.5 my-1.5"><input type="checkbox" class="task-desc-checkbox mt-0.5 rounded border-zinc-700 bg-zinc-900 text-emerald-500 cursor-pointer" /><span class="task-desc-text text-zinc-300">$3</span></div>')
      .replace(/^(\s*(-|\*)?\s*\[[xX]\]\s*)(.*)$/gm, '<div class="flex items-start gap-2.5 my-1.5"><input type="checkbox" checked class="task-desc-checkbox mt-0.5 rounded border-zinc-700 bg-zinc-900 text-emerald-500 cursor-pointer" /><span class="task-desc-text line-through text-zinc-500">$3</span></div>');

    // Convert newlines to <br />
    converted = converted.replace(/\n/g, "<br />");
    return converted;
  };

  const handleDescContentClick = (e) => {
    const target = e.target;
    if (target && target.classList && target.classList.contains("task-desc-checkbox")) {
      if (!currentTask.can_edit) {
        e.preventDefault();
        return;
      }
      const isChecked = target.checked;
      const li = target.closest("li[data-list]");
      if (li) {
        li.setAttribute("data-list", isChecked ? "checked" : "unchecked");
        if (isChecked) {
          li.classList.add("task-item-completed");
          target.setAttribute("checked", "checked");
        } else {
          li.classList.remove("task-item-completed");
          target.removeAttribute("checked");
        }
      }

      if (descContainerRef.current) {
        const updatedHtml = descContainerRef.current.innerHTML;
        setCurrentTask((prev) => ({ ...prev, description: updatedHtml, raw_description: updatedHtml }));
        router.patch(
          "/tasks/" + currentTask.db_id,
          {
            title: currentTask.title,
            description: updatedHtml,
          },
          { preserveScroll: true, preserveState: true }
        );
      }
    }
  };

  const handleStartEditDesc = () => {
    setDescDraft(currentTask.raw_description || currentTask.description || "");
    setDescPreview(false);
    setIsEditingDesc(true);
  };

  const handleSaveInlineDesc = () => {
    if (!currentTask.db_id) return;
    setIsSavingDesc(true);
    router.patch(
      "/tasks/" + currentTask.db_id,
      {
        title: currentTask.title,
        description: descDraft,
      },
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          setCurrentTask((prev) => ({
            ...prev,
            description: descDraft,
            raw_description: descDraft,
          }));
          setIsEditingDesc(false);
          setIsSavingDesc(false);
        },
        onError: () => {
          setIsSavingDesc(false);
        },
      }
    );
  };

  // Open Edit Modal
  const handleOpenEdit = () => {
    setEditFormData({
      title: currentTask.title || "",
      description: currentTask.raw_description || currentTask.description || "",
      status: currentTask.status_id || 1,
      priority: currentTask.priority_id || 2,
      type: currentTask.type_id || 1,
      project_id: currentTask.project_id ? String(currentTask.project_id) : "personal",
      assigned_to: currentTask.assignee?.id ? String(currentTask.assignee.id) : "unassigned",
      due_date: currentTask.due_date_raw || "",
      points: currentTask.points !== null && currentTask.points !== undefined ? String(currentTask.points) : "",
    });
    setShowEditModal(true);
  };

  // Submit Edit Form
  const handleEditSubmit = (e) => {
    e.preventDefault();
    if (!editFormData.title.trim() || !currentTask.db_id) return;

    setIsSubmittingEdit(true);

    const payload = {
      title: editFormData.title.trim(),
      description: editFormData.description?.trim() || null,
      project_id: editFormData.project_id === "personal" || !editFormData.project_id ? null : Number(editFormData.project_id),
      assigned_to: editFormData.assigned_to === "unassigned" || !editFormData.assigned_to ? null : Number(editFormData.assigned_to),
      status: Number(editFormData.status),
      priority: Number(editFormData.priority),
      type: Number(editFormData.type),
      due_date: editFormData.due_date || null,
      points: editFormData.points ? Number(editFormData.points) : null,
    };

    router.patch(`/tasks/${currentTask.db_id}`, payload, {
      preserveScroll: true,
      onSuccess: () => {
        setShowEditModal(false);
        setIsSubmittingEdit(false);
      },
      onError: () => {
        setIsSubmittingEdit(false);
      },
    });
  };

  // Quick Sidebar Updates (Status, Priority, Assignee, Project, Due Date, Points)
  const handleQuickUpdate = (field, value) => {
    if (!currentTask.db_id) return;

    router.patch(
      `/tasks/${currentTask.db_id}`,
      { [field]: value },
      {
        preserveScroll: true,
      }
    );
  };

  // Toggle Main Task Completion
  const handleToggleTask = () => {
    if (!currentTask.db_id) return;

    const nextCompleted = !currentTask.completed;
    const nextStatus = nextCompleted ? "Done" : "In Progress";
    const nextStatusId = nextCompleted ? 3 : 2;

    setCurrentTask((prev) => ({
      ...prev,
      completed: nextCompleted,
      status: nextStatus,
      status_id: nextStatusId,
    }));

    router.patch(
      `/tasks/${currentTask.db_id}/toggle`,
      {},
      {
        preserveScroll: true,
        onError: () => {
          if (task) setCurrentTask(task);
        },
      }
    );
  };

  // Toggle Subtask Completion
  const handleToggleSubtask = (sub) => {
    const nextDone = !sub.done;

    setSubtasks((prev) =>
      prev.map((item) => (item.id === sub.id ? { ...item, done: nextDone } : item))
    );

    router.patch(
      `/tasks/${sub.db_id || sub.id}/toggle`,
      {},
      {
        preserveScroll: true,
        onError: () => {
          if (initial_subtasks) setSubtasks(initial_subtasks);
        },
      }
    );
  };

  // Add Subtask
  const handleAddSubtask = (e) => {
    e.preventDefault();
    if (!newSubtaskTitle.trim() || !currentTask.db_id) return;

    setIsSubmittingSubtask(true);

    router.post(
      `/tasks/${currentTask.db_id}/subtasks`,
      {
        title: newSubtaskTitle.trim(),
        priority: newSubtaskPriority,
        type: newSubtaskType,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          setNewSubtaskTitle("");
          setShowAddSubtask(false);
          setIsSubmittingSubtask(false);
        },
        onError: () => {
          setIsSubmittingSubtask(false);
        },
      }
    );
  };

  // Copy Task
  const handleCopyTask = () => {
    if (!currentTask.db_id) return;
    router.post(
      `/tasks/${currentTask.db_id}/copy`,
      {},
      {
        preserveScroll: true,
      }
    );
  };

  // Delete Task
  const handleDeleteTask = () => {
    if (!currentTask.db_id) return;
    setIsSubmittingDelete(true);

    router.delete(`/tasks/${currentTask.db_id}`, {
      onSuccess: () => {
        setIsSubmittingDelete(false);
      },
      onError: () => {
        setIsSubmittingDelete(false);
      },
    });
  };

  // Add Comment
  const handleAddComment = (e) => {
    e.preventDefault();
    if (!commentText.trim() || !currentTask.db_id) return;

    setIsSubmittingComment(true);

    router.post(
      "/comments",
      {
        content: commentText.trim(),
        commentable_type: "task",
        commentable_id: currentTask.db_id,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          setCommentText("");
          setIsSubmittingComment(false);
        },
        onError: () => {
          setIsSubmittingComment(false);
        },
      }
    );
  };

  // Delete Comment
  const handleDeleteComment = (commentId) => {
    router.delete(`/comments/${commentId}`, {
      preserveScroll: true,
    });
  };

  // Upload Image
  const handleImageFileChange = (e) => {
    const file = e.target.files?.[0];
    if (!file || !currentTask.db_id) return;

    setIsUploadingImage(true);
    const formData = new FormData();
    formData.append("image", file);

    router.post(`/tasks/${currentTask.db_id}/images`, formData, {
      preserveScroll: true,
      onSuccess: () => {
        setIsUploadingImage(false);
        if (fileInputRef.current) fileInputRef.current.value = "";
      },
      onError: () => {
        setIsUploadingImage(false);
      },
    });
  };

  // Delete Image
  const handleDeleteImage = (imageId) => {
    router.delete(`/tasks/images/${imageId}`, {
      preserveScroll: true,
    });
  };

  // Add Note
  const handleAddNote = (e) => {
    e.preventDefault();
    if (!newNoteTitle.trim() || !currentTask.db_id) return;

    setIsSubmittingNote(true);

    router.post(
      "/notes",
      {
        title: newNoteTitle.trim(),
        description: newNoteContent.trim() || newNoteTitle.trim(),
        note_type: 2, // 2: Task note
        note_type_id: currentTask.db_id,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          setNewNoteTitle("");
          setNewNoteContent("");
          setShowAddNoteModal(false);
          setIsSubmittingNote(false);
        },
        onError: () => {
          setIsSubmittingNote(false);
        },
      }
    );
  };

  // Copy URL
  const handleCopyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    setIsCopied(true);
    setTimeout(() => setIsCopied(false), 2000);
  };

  // Subtask Progress calculation
  const totalSubtasksCount = subtasks.length;
  const completedSubtasksCount = subtasks.filter((s) => s.done).length;
  const subtaskPercentage =
    totalSubtasksCount > 0 ? Math.round((completedSubtasksCount / totalSubtasksCount) * 100) : 0;

  const getPriorityBadgeClasses = (priority) => {
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

  const getStatusBadgeClasses = (status) => {
    switch (status) {
      case "Done":
        return "bg-emerald-500/10 text-emerald-400 border-emerald-500/30";
      case "In Progress":
        return "bg-blue-500/10 text-blue-400 border-blue-500/30";
      case "Review":
        return "bg-purple-500/10 text-purple-400 border-purple-500/30";
      default:
        return "bg-zinc-800 text-zinc-300 border-zinc-700/60";
    }
  };

  const getTypeBadge = (type) => {
    switch (type) {
      case "Bug":
        return {
          icon: <AlertCircle className="w-3.5 h-3.5 text-rose-400" />,
          classes: "bg-rose-500/10 text-rose-300 border-rose-500/20",
        };
      case "Feature":
        return {
          icon: <Sparkles className="w-3.5 h-3.5 text-emerald-400" />,
          classes: "bg-emerald-500/10 text-emerald-300 border-emerald-500/20",
        };
      case "Improvement":
        return {
          icon: <TrendingUp className="w-3.5 h-3.5 text-cyan-400" />,
          classes: "bg-cyan-500/10 text-cyan-300 border-cyan-500/20",
        };
      default:
        return {
          icon: <CheckSquare className="w-3.5 h-3.5 text-zinc-400" />,
          classes: "bg-zinc-800/80 text-zinc-300 border-zinc-700/60",
        };
    }
  };

  const typeBadge = getTypeBadge(currentTask.type);

  const headerActions = (
    <div className="flex items-center gap-2">
      <Button
        variant="outline"
        size="sm"
        onClick={handleCopyLink}
        className="border-zinc-800 bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 text-xs h-9 px-3 gap-1.5 rounded-xl"
        title="Copy task link"
      >
        {isCopied ? <Check className="h-3.5 w-3.5 text-emerald-400" /> : <Share2 className="h-3.5 w-3.5" />}
        <span className="hidden sm:inline">{isCopied ? "Copied" : "Share"}</span>
      </Button>

      {currentTask.can_edit && (
        <Button
          variant="outline"
          size="sm"
          onClick={handleOpenEdit}
          className="border-zinc-800 bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 text-xs h-9 px-3 gap-1.5 rounded-xl"
        >
          <Edit2 className="h-3.5 w-3.5" />
          <span className="hidden sm:inline">Edit</span>
        </Button>
      )}

      {currentTask.can_delete && (
        <Button
          variant="outline"
          size="sm"
          onClick={() => setShowDeleteModal(true)}
          className="border-zinc-800 bg-zinc-900/80 hover:bg-rose-950/40 text-rose-400 text-xs h-9 px-3 gap-1.5 rounded-xl"
        >
          <Trash2 className="h-3.5 w-3.5" />
          <span className="hidden sm:inline">Delete</span>
        </Button>
      )}
    </div>
  );

  return (
    <DashboardLayout title={`Task ${currentTask.id}`} activeItem="tasks" actions={headerActions}>
      <div className="space-y-6 pb-12">
        {/* Flash Notifications */}
        {flash?.success && (
          <div className="flex items-center justify-between p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm animate-in fade-in">
            <div className="flex items-center gap-2.5">
              <CheckCircle2 className="h-5 w-5 text-emerald-400 shrink-0" />
              <span>{flash.success}</span>
            </div>
          </div>
        )}
        {flash?.error && (
          <div className="flex items-center justify-between p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm animate-in fade-in">
            <div className="flex items-center gap-2.5">
              <AlertTriangle className="h-5 w-5 text-rose-400 shrink-0" />
              <span>{flash.error}</span>
            </div>
          </div>
        )}

        {/* Top Breadcrumb & Navigation Bar */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-zinc-800/80 bg-[#121215]/90 p-4 rounded-2xl shadow-xl">
          <div className="flex items-center gap-3">
            <Link
              href="/tasks"
              className="p-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors"
              title="Back to Tasks"
            >
              <ArrowLeft className="h-4 w-4" />
            </Link>

            <div className="flex items-center gap-2 text-xs font-mono text-zinc-400 flex-wrap">
              <Link href="/tasks" className="hover:text-zinc-200 transition-colors">
                Tasks
              </Link>
              <ChevronRight className="h-3.5 w-3.5 text-zinc-600" />
              <span className="px-2 py-0.5 rounded bg-zinc-800 text-zinc-300 border border-zinc-700/60 font-semibold">
                {currentTask.id}
              </span>
              <ChevronRight className="h-3.5 w-3.5 text-zinc-600" />
              <span className="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-zinc-900 text-zinc-300 border border-zinc-800">
                <span
                  className="h-1.5 w-1.5 rounded-full"
                  style={{ backgroundColor: currentTask.project?.theme || "#10b981" }}
                />
                <span className="truncate max-w-[150px]">{currentTask.project?.name || "Personal Space"}</span>
              </span>
            </div>
          </div>

          <div className="flex items-center gap-2 self-end sm:self-center">
            <span
              className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full border text-xs font-mono font-semibold ${typeBadge.classes}`}
            >
              {typeBadge.icon}
              <span>{currentTask.type}</span>
            </span>

            <span
              className={`px-2.5 py-0.5 rounded-full border text-xs font-mono font-semibold ${getPriorityBadgeClasses(
                currentTask.priority
              )}`}
            >
              {currentTask.priority}
            </span>

            <span
              className={`px-2.5 py-0.5 rounded-full border text-xs font-mono font-semibold ${getStatusBadgeClasses(
                currentTask.status
              )}`}
            >
              {currentTask.status}
            </span>
          </div>
        </div>

        {/* Parent Task Banner (if this is a subtask) */}
        {currentTask.parentTask && (
          <div className="flex items-center justify-between p-3.5 rounded-xl bg-purple-950/20 border border-purple-800/30 text-xs font-mono text-purple-300">
            <div className="flex items-center gap-2">
              <Layers className="h-4 w-4 text-purple-400" />
              <span>Parent Task:</span>
              <Link
                href={`/tasks/${currentTask.parentTask.db_id || currentTask.parentTask.id}`}
                className="font-bold text-white hover:underline"
              >
                {currentTask.parentTask.title}
              </Link>
            </div>
            <Link
              href={`/tasks/${currentTask.parentTask.db_id || currentTask.parentTask.id}`}
              className="inline-flex items-center gap-1 text-purple-400 hover:text-purple-200"
            >
              <span>View Parent</span>
              <ExternalLink className="h-3 w-3" />
            </Link>
          </div>
        )}

        {/* Task Title & Action Hero */}
        <div className="p-6 sm:p-8 rounded-2xl border border-zinc-800/80 bg-gradient-to-br from-[#141418] via-[#0f0f13] to-[#0a0a0d] shadow-2xl space-y-4">
          <div className="flex items-start gap-4">
            <button
              onClick={handleToggleTask}
              className="mt-1 text-zinc-500 hover:text-emerald-400 transition-colors shrink-0"
              title={currentTask.completed ? "Mark as Incomplete" : "Mark as Complete"}
            >
              {currentTask.completed ? (
                <CheckSquare className="h-6 w-6 text-emerald-400" />
              ) : (
                <Square className="h-6 w-6 text-zinc-600 hover:text-zinc-400" />
              )}
            </button>

            <div className="space-y-1.5 flex-1 min-w-0">
              <h1
                className={`text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight leading-tight ${
                  currentTask.completed ? "line-through text-zinc-500" : "text-white"
                }`}
              >
                {currentTask.title}
              </h1>

              <div className="flex items-center gap-3 text-xs font-mono text-zinc-400 flex-wrap">
                {currentTask.created && <span>Created {currentTask.created}</span>}
                {currentTask.branch && (
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-zinc-900 border border-zinc-800 text-zinc-300">
                    <GitBranch className="w-3 h-3 text-zinc-500" />
                    <span>{currentTask.branch}</span>
                  </span>
                )}
                {currentTask.points !== null && currentTask.points !== undefined && (
                  <span className="px-2 py-0.5 rounded bg-zinc-900 border border-zinc-800 text-zinc-300">
                    {currentTask.points} Story Points
                  </span>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Main Grid: Content (Left) & Metadata (Right) */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
          {/* Left Column (2 cols): Navigation Tabs, Description, Subtasks, Comments, Attachments, Notes, History */}
          <div className="lg:col-span-2 space-y-6">
            {/* Section Tabs */}
            <div className="flex items-center gap-1.5 bg-[#111114] p-1.5 rounded-xl border border-zinc-800/80 overflow-x-auto scrollbar-none font-mono text-xs">
              {[
                { key: "overview", label: "Overview", icon: <FileText className="w-3.5 h-3.5" /> },
                {
                  key: "subtasks",
                  label: `Subtasks (${subtasks.length})`,
                  icon: <CheckSquare className="w-3.5 h-3.5" />,
                },
                {
                  key: "comments",
                  label: `Comments (${comments.length})`,
                  icon: <MessageSquare className="w-3.5 h-3.5" />,
                },
                {
                  key: "attachments",
                  label: `Attachments (${images.length})`,
                  icon: <Paperclip className="w-3.5 h-3.5" />,
                },
                {
                  key: "notes",
                  label: `Notes (${notes.length})`,
                  icon: <StickyNote className="w-3.5 h-3.5" />,
                },
                {
                  key: "history",
                  label: `History (${historyLogs.length})`,
                  icon: <History className="w-3.5 h-3.5" />,
                },
              ].map((tab) => (
                <button
                  key={tab.key}
                  onClick={() => setActiveTab(tab.key)}
                  className={`flex items-center gap-2 px-3 py-2 rounded-lg transition-all whitespace-nowrap ${
                    activeTab === tab.key
                      ? "bg-zinc-800 text-white font-semibold border border-zinc-700 shadow-sm"
                      : "text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800/40"
                  }`}
                >
                  {tab.icon}
                  <span>{tab.label}</span>
                </button>
              ))}
            </div>

            {/* TAB: Overview / Description */}
            {activeTab === "overview" && (
              <div className="p-6 rounded-2xl bg-[#121215] border border-zinc-800/80 space-y-4 shadow-xl">
                <div className="flex items-center justify-between pb-3 border-b border-zinc-800">
                  <h3 className="text-xs sm:text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                    <FileText className="w-4 h-4 text-emerald-400" />
                    Description & Specifications
                  </h3>
                  {currentTask.can_edit && !isEditingDesc && (
                    <button
                      onClick={handleStartEditDesc}
                      className="text-xs font-mono text-zinc-400 hover:text-white flex items-center gap-1.5 px-2.5 py-1 rounded-lg hover:bg-zinc-800/60 transition-colors"
                    >
                      <Edit2 className="w-3.5 h-3.5 text-emerald-400" /> Edit Description
                    </button>
                  )}
                </div>

                {isEditingDesc ? (
                  <div className="space-y-3">
                    <div className="flex items-center justify-between">
                      <span className="text-[11px] font-mono text-zinc-500">
                        Supports HTML and Markdown formatting
                      </span>
                      <div className="flex items-center gap-2">
                        <button
                          type="button"
                          onClick={() => setDescPreview(!descPreview)}
                          className={`text-xs font-mono px-2.5 py-1 rounded-md transition-colors flex items-center gap-1.5 ${
                            descPreview
                              ? "bg-emerald-500/20 text-emerald-400 border border-emerald-500/30"
                              : "text-zinc-400 hover:text-zinc-200 bg-zinc-900 border border-zinc-800"
                          }`}
                        >
                          <Eye className="w-3 h-3" />
                          {descPreview ? "Write Mode" : "Preview"}
                        </button>
                      </div>
                    </div>

                    {descPreview ? (
                      <div className="p-4 rounded-xl bg-zinc-950/60 border border-zinc-800 min-h-[140px]">
                        {hasDescContent(descDraft) ? (
                          <div
                            className="task-description-html text-xs sm:text-sm leading-relaxed text-zinc-300 font-sans break-words space-y-2
                              [&_.text-muted]:text-zinc-400 [&_.text-muted]:text-xs
                              [&_.small]:text-xs [&_.small]:text-zinc-400
                              [&_hr]:border-zinc-800 [&_hr]:my-3.5 [&_hr]:border-t
                              [&_p]:mb-2 [&_p:last-child]:mb-0
                              [&_strong]:font-semibold [&_strong]:text-zinc-100
                              [&_b]:font-semibold [&_b]:text-zinc-100
                              [&_em]:italic [&_i]:italic
                              [&_a]:text-cyan-400 [&_a]:underline hover:[&_a]:text-cyan-300
                              [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1 [&_ul]:my-2
                              [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:space-y-1 [&_ol]:my-2
                              [&_li]:text-zinc-300
                              [&_h1]:text-lg [&_h1]:font-bold [&_h1]:text-zinc-100 [&_h1]:my-2
                              [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-zinc-100 [&_h2]:my-2
                              [&_blockquote]:border-l-2 [&_blockquote]:border-emerald-500/50 [&_blockquote]:pl-3 [&_blockquote]:italic [&_blockquote]:text-zinc-400
                              [&_code]:bg-zinc-950 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded [&_code]:text-emerald-400 [&_code]:font-mono [&_code]:text-xs
                              [&_pre]:bg-zinc-950 [&_pre]:p-3 [&_pre]:rounded-lg [&_pre]:border [&_pre]:border-zinc-800 [&_pre]:overflow-x-auto
                              [&_img]:rounded-lg [&_img]:max-w-full [&_img]:my-2"
                            dangerouslySetInnerHTML={{ __html: formatDescHtml(descDraft) }}
                          />
                        ) : (
                          <p className="text-xs font-mono text-zinc-500 italic">Preview is empty</p>
                        )}
                      </div>
                    ) : (
                      <textarea
                        rows={7}
                        value={descDraft}
                        onChange={(e) => setDescDraft(e.target.value)}
                        placeholder="Write task specifications, details, or checklists..."
                        className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3.5 text-xs sm:text-sm text-zinc-100 focus:outline-none focus:border-emerald-500/60 focus:ring-1 focus:ring-emerald-500/30 resize-y font-mono leading-relaxed"
                      />
                    )}

                    <div className="flex items-center justify-end gap-2.5 pt-2">
                      <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        disabled={isSavingDesc}
                        onClick={() => setIsEditingDesc(false)}
                        className="text-xs text-zinc-400 hover:text-white h-8 px-3"
                      >
                        Cancel
                      </Button>
                      <Button
                        type="button"
                        size="sm"
                        disabled={isSavingDesc}
                        onClick={handleSaveInlineDesc}
                        className="text-xs bg-emerald-500 hover:bg-emerald-600 text-black font-semibold h-8 px-3.5 gap-1.5"
                      >
                        {isSavingDesc ? (
                          <>
                            <Loader2 className="w-3.5 h-3.5 animate-spin" /> Saving...
                          </>
                        ) : (
                          <>
                            <Save className="w-3.5 h-3.5" /> Save Description
                          </>
                        )}
                      </Button>
                    </div>
                  </div>
                ) : hasDescContent(currentTask.description) ? (
                  <div
                    ref={descContainerRef}
                    onClick={handleDescContentClick}
                    className="task-description-html text-xs sm:text-sm leading-relaxed text-zinc-300 font-sans break-words space-y-2
                      [&_.text-muted]:text-zinc-400 [&_.text-muted]:text-xs
                      [&_.small]:text-xs [&_.small]:text-zinc-400
                      [&_hr]:border-zinc-800 [&_hr]:my-4 [&_hr]:border-t
                      [&_p]:mb-2 [&_p:last-child]:mb-0
                      [&_strong]:font-semibold [&_strong]:text-zinc-100
                      [&_b]:font-semibold [&_b]:text-zinc-100
                      [&_em]:italic [&_i]:italic
                      [&_a]:text-cyan-400 [&_a]:underline hover:[&_a]:text-cyan-300
                      [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1 [&_ul]:my-2
                      [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:space-y-1 [&_ol]:my-2
                      [&_li]:text-zinc-300
                      [&_h1]:text-lg [&_h1]:font-bold [&_h1]:text-zinc-100 [&_h1]:my-3
                      [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-zinc-100 [&_h2]:my-2
                      [&_h3]:text-sm [&_h3]:font-bold [&_h3]:text-zinc-100 [&_h3]:my-2
                      [&_blockquote]:border-l-2 [&_blockquote]:border-emerald-500/50 [&_blockquote]:pl-3 [&_blockquote]:italic [&_blockquote]:text-zinc-400 [&_blockquote]:my-2
                      [&_code]:bg-zinc-950 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded [&_code]:text-emerald-400 [&_code]:font-mono [&_code]:text-xs
                      [&_pre]:bg-zinc-950 [&_pre]:p-3 [&_pre]:rounded-lg [&_pre]:border [&_pre]:border-zinc-800 [&_pre]:overflow-x-auto [&_pre]:my-2
                      [&_img]:rounded-lg [&_img]:max-w-full [&_img]:my-2 [&_img]:border [&_img]:border-zinc-800"
                    dangerouslySetInnerHTML={{ __html: formatDescHtml(currentTask.description) }}
                  />
                ) : (
                  <div className="py-8 text-center border border-dashed border-zinc-800 rounded-xl space-y-2">
                    <p className="text-xs font-mono text-zinc-500">No description provided for this task.</p>
                    {currentTask.can_edit && (
                      <Button
                        size="sm"
                        variant="ghost"
                        onClick={handleStartEditDesc}
                        className="text-xs text-emerald-400 hover:text-emerald-300 gap-1 h-8"
                      >
                        <Plus className="w-3.5 h-3.5" /> Add Description
                      </Button>
                    )}
                  </div>
                )}
              </div>
            )}

            {/* TAB: Subtasks */}
            {(activeTab === "subtasks" || activeTab === "overview") && (
              <div className="p-6 rounded-2xl bg-[#121215] border border-zinc-800/80 space-y-4 shadow-xl">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-800">
                  <div className="space-y-1">
                    <h3 className="text-xs sm:text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                      <CheckSquare className="w-4 h-4 text-emerald-400" />
                      Subtasks
                    </h3>
                    <p className="text-xs text-zinc-500 font-mono">
                      {completedSubtasksCount} of {totalSubtasksCount} completed ({subtaskPercentage}%)
                    </p>
                  </div>

                  <Button
                    size="sm"
                    onClick={() => setShowAddSubtask(!showAddSubtask)}
                    className="bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-mono gap-1.5 h-8 rounded-lg self-start sm:self-center"
                  >
                    <Plus className="w-3.5 h-3.5" /> Add Subtask
                  </Button>
                </div>

                {/* Progress bar */}
                {totalSubtasksCount > 0 && (
                  <div className="h-1.5 w-full bg-zinc-900 rounded-full overflow-hidden border border-zinc-800">
                    <div
                      className="h-full bg-emerald-500 rounded-full transition-all duration-300"
                      style={{ width: `${subtaskPercentage}%` }}
                    />
                  </div>
                )}

                {/* Add Subtask Form */}
                {showAddSubtask && (
                  <form
                    onSubmit={handleAddSubtask}
                    className="p-4 rounded-xl bg-zinc-900/90 border border-zinc-800 space-y-3 animate-in fade-in"
                  >
                    <input
                      type="text"
                      required
                      placeholder="e.g. Write integration test for webhook verification"
                      value={newSubtaskTitle}
                      onChange={(e) => setNewSubtaskTitle(e.target.value)}
                      className="w-full bg-black border border-zinc-800 rounded-lg p-2.5 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500"
                    />

                    <div className="flex items-center justify-between gap-3 flex-wrap">
                      <div className="flex items-center gap-2 font-mono text-xs">
                        <select
                          value={newSubtaskPriority}
                          onChange={(e) => setNewSubtaskPriority(Number(e.target.value))}
                          className="bg-black border border-zinc-800 rounded-md px-2 py-1 text-xs text-zinc-300"
                        >
                          <option value={1}>Low Priority</option>
                          <option value={2}>Medium Priority</option>
                          <option value={3}>High Priority</option>
                          <option value={4}>Urgent</option>
                        </select>
                        <select
                          value={newSubtaskType}
                          onChange={(e) => setNewSubtaskType(Number(e.target.value))}
                          className="bg-black border border-zinc-800 rounded-md px-2 py-1 text-xs text-zinc-300"
                        >
                          <option value={1}>Task</option>
                          <option value={2}>Bug</option>
                          <option value={3}>Feature</option>
                        </select>
                      </div>

                      <div className="flex items-center gap-2">
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          onClick={() => setShowAddSubtask(false)}
                          className="text-xs text-zinc-400 h-8"
                        >
                          Cancel
                        </Button>
                        <Button
                          type="submit"
                          size="sm"
                          disabled={isSubmittingSubtask}
                          className="bg-emerald-500 hover:bg-emerald-600 text-black font-semibold text-xs h-8 px-3"
                        >
                          {isSubmittingSubtask && <Loader2 className="w-3.5 h-3.5 animate-spin" />}
                          <span>Save Subtask</span>
                        </Button>
                      </div>
                    </div>
                  </form>
                )}

                {/* Subtask Items */}
                <div className="space-y-2">
                  {subtasks.length === 0 ? (
                    <p className="text-xs font-mono text-zinc-500 py-3 text-center">
                      No subtasks created yet. Add deliverables above.
                    </p>
                  ) : (
                    subtasks.map((sub) => (
                      <div
                        key={sub.id}
                        className="flex items-center justify-between p-3 rounded-xl bg-zinc-900/60 border border-zinc-800/80 hover:border-zinc-700 transition-all group"
                      >
                        <div className="flex items-center gap-3 min-w-0">
                          <button
                            onClick={() => handleToggleSubtask(sub)}
                            className="text-zinc-500 hover:text-emerald-400 transition-colors shrink-0"
                          >
                            {sub.done ? (
                              <CheckSquare className="h-4 w-4 text-emerald-400" />
                            ) : (
                              <Square className="h-4 w-4 text-zinc-600 group-hover:text-zinc-400" />
                            )}
                          </button>
                          <Link
                            href={`/tasks/${sub.db_id || sub.id}`}
                            className={`text-xs sm:text-sm font-medium hover:underline truncate ${
                              sub.done ? "line-through text-zinc-500" : "text-zinc-200 group-hover:text-white"
                            }`}
                          >
                            {sub.title || sub.text}
                          </Link>
                        </div>

                        <div className="flex items-center gap-2 shrink-0">
                          {sub.priority && (
                            <span
                              className={`text-[10px] font-mono px-2 py-0.5 rounded-full border ${getPriorityBadgeClasses(
                                sub.priority
                              )}`}
                            >
                              {sub.priority}
                            </span>
                          )}
                          <Link
                            href={`/tasks/${sub.db_id || sub.id}`}
                            className="p-1 rounded text-zinc-500 hover:text-zinc-300"
                            title="Open Subtask"
                          >
                            <ExternalLink className="w-3.5 h-3.5" />
                          </Link>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            )}

            {/* TAB: Comments */}
            {(activeTab === "comments" || activeTab === "overview") && (
              <div className="p-6 rounded-2xl bg-[#121215] border border-zinc-800/80 space-y-5 shadow-xl">
                <div className="flex items-center justify-between pb-3 border-b border-zinc-800">
                  <h3 className="text-xs sm:text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                    <MessageSquare className="w-4 h-4 text-blue-400" />
                    Discussion & Activity Comments ({comments.length})
                  </h3>
                </div>

                {/* New Comment Input */}
                <form onSubmit={handleAddComment} className="space-y-3">
                  <textarea
                    rows={3}
                    placeholder="Add a comment or update to this task..."
                    value={commentText}
                    onChange={(e) => setCommentText(e.target.value)}
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-xs sm:text-sm text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-blue-500 transition-colors resize-none"
                  />
                  <div className="flex items-center justify-end">
                    <Button
                      type="submit"
                      size="sm"
                      disabled={isSubmittingComment || !commentText.trim()}
                      className="bg-blue-500 hover:bg-blue-600 text-white font-semibold text-xs gap-1.5 px-4 h-8"
                    >
                      {isSubmittingComment && <Loader2 className="w-3.5 h-3.5 animate-spin" />}
                      <Send className="w-3.5 h-3.5" />
                      <span>Post Comment</span>
                    </Button>
                  </div>
                </form>

                {/* Comments List */}
                <div className="space-y-4 pt-2">
                  {comments.length === 0 ? (
                    <p className="text-xs font-mono text-zinc-500 text-center py-4">
                      No comments yet. Start the conversation above.
                    </p>
                  ) : (
                    comments.map((c) => (
                      <div
                        key={c.id}
                        className="p-4 rounded-xl bg-zinc-900/60 border border-zinc-800/80 space-y-2.5"
                      >
                        <div className="flex items-center justify-between">
                          <div className="flex items-center gap-2.5">
                            <Avatar className="h-7 w-7 border border-zinc-700">
                              {c.user?.profile_image && <AvatarImage src={c.user.profile_image} />}
                              <AvatarFallback className="bg-zinc-800 text-zinc-200 text-xs font-bold">
                                {c.user?.avatar || "UU"}
                              </AvatarFallback>
                            </Avatar>
                            <div>
                              <span className="text-xs font-semibold text-white block">{c.user?.name}</span>
                              <span className="text-[10px] font-mono text-zinc-500">{c.time || c.created_at}</span>
                            </div>
                          </div>

                          {c.can_delete && (
                            <button
                              onClick={() => handleDeleteComment(c.id)}
                              className="text-zinc-600 hover:text-rose-400 p-1 rounded transition-colors"
                              title="Delete comment"
                            >
                              <Trash2 className="w-3.5 h-3.5" />
                            </button>
                          )}
                        </div>

                        <p className="text-xs sm:text-sm text-zinc-300 leading-relaxed font-sans whitespace-pre-wrap pl-9">
                          {c.text}
                        </p>
                      </div>
                    ))
                  )}
                </div>
              </div>
            )}

            {/* TAB: Attachments */}
            {activeTab === "attachments" && (
              <div className="p-6 rounded-2xl bg-[#121215] border border-zinc-800/80 space-y-5 shadow-xl">
                <div className="flex items-center justify-between pb-3 border-b border-zinc-800">
                  <h3 className="text-xs sm:text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                    <Paperclip className="w-4 h-4 text-amber-400" />
                    Attachments & Screenshots ({images.length})
                  </h3>

                  <div>
                    <input
                      type="file"
                      ref={fileInputRef}
                      onChange={handleImageFileChange}
                      accept="image/*"
                      className="hidden"
                    />
                    <Button
                      size="sm"
                      onClick={() => fileInputRef.current?.click()}
                      disabled={isUploadingImage}
                      className="bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-mono gap-1.5 h-8 rounded-lg"
                    >
                      {isUploadingImage ? (
                        <Loader2 className="w-3.5 h-3.5 animate-spin" />
                      ) : (
                        <Upload className="w-3.5 h-3.5" />
                      )}
                      <span>Upload Image</span>
                    </Button>
                  </div>
                </div>

                {images.length === 0 ? (
                  <div className="py-12 text-center border border-dashed border-zinc-800 rounded-xl space-y-2">
                    <ImageIcon className="h-8 w-8 mx-auto text-zinc-600" />
                    <p className="text-xs font-mono text-zinc-500">No attachments uploaded yet.</p>
                    <Button
                      size="sm"
                      variant="ghost"
                      onClick={() => fileInputRef.current?.click()}
                      className="text-xs text-amber-400 hover:text-amber-300 h-8"
                    >
                      Upload screenshot or file
                    </Button>
                  </div>
                ) : (
                  <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    {images.map((img) => (
                      <div
                        key={img.id}
                        className="group relative rounded-xl border border-zinc-800 bg-zinc-900/90 overflow-hidden shadow-md space-y-2"
                      >
                        <div
                          onClick={() => setPreviewImage(img.url)}
                          className="h-32 w-full bg-black/60 overflow-hidden cursor-pointer"
                        >
                          <img
                            src={img.url}
                            alt={img.name}
                            className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200"
                          />
                        </div>
                        <div className="p-2.5 flex items-center justify-between text-xs font-mono text-zinc-300">
                          <span className="truncate max-w-[130px]" title={img.name}>
                            {img.name}
                          </span>
                          <button
                            onClick={() => handleDeleteImage(img.id)}
                            className="text-zinc-500 hover:text-rose-400 p-1 transition-colors"
                            title="Delete image"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            )}

            {/* TAB: Notes */}
            {activeTab === "notes" && (
              <div className="p-6 rounded-2xl bg-[#121215] border border-zinc-800/80 space-y-5 shadow-xl">
                <div className="flex items-center justify-between pb-3 border-b border-zinc-800">
                  <h3 className="text-xs sm:text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                    <StickyNote className="w-4 h-4 text-purple-400" />
                    Task Notes & Documentation ({notes.length})
                  </h3>

                  <Button
                    size="sm"
                    onClick={() => setShowAddNoteModal(true)}
                    className="bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-mono gap-1.5 h-8 rounded-lg"
                  >
                    <Plus className="w-3.5 h-3.5" /> Add Note
                  </Button>
                </div>

                {notes.length === 0 ? (
                  <div className="py-12 text-center border border-dashed border-zinc-800 rounded-xl space-y-2">
                    <StickyNote className="h-8 w-8 mx-auto text-zinc-600" />
                    <p className="text-xs font-mono text-zinc-500">No notes attached to this task.</p>
                  </div>
                ) : (
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {notes.map((note) => (
                      <div
                        key={note.id}
                        className="p-4 rounded-xl bg-zinc-900/60 border border-zinc-800 space-y-2"
                      >
                        <div className="flex items-center justify-between text-xs font-mono text-zinc-400">
                          <h4 className="font-bold text-white text-sm">{note.title}</h4>
                          <span>{note.time}</span>
                        </div>
                        <p className="text-xs text-zinc-300 leading-relaxed font-sans whitespace-pre-wrap">
                          {note.content}
                        </p>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            )}

            {/* TAB: History */}
            {activeTab === "history" && (
              <div className="p-6 rounded-2xl bg-[#121215] border border-zinc-800/80 space-y-5 shadow-xl">
                <div className="flex items-center justify-between pb-3 border-b border-zinc-800">
                  <h3 className="text-xs sm:text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                    <History className="w-4 h-4 text-cyan-400" />
                    Activity & Audit Trail ({historyLogs.length})
                  </h3>
                </div>

                <div className="space-y-3 font-mono text-xs">
                  {historyLogs.length === 0 ? (
                    <p className="text-xs text-zinc-500 text-center py-6">No historical records logged yet.</p>
                  ) : (
                    historyLogs.map((log) => (
                      <div
                        key={log.id}
                        className="flex items-start gap-3 p-3 rounded-xl bg-zinc-900/60 border border-zinc-800/80"
                      >
                        <Avatar className="h-6 w-6 mt-0.5 border border-zinc-700">
                          <AvatarFallback className="bg-zinc-800 text-zinc-300 text-[10px] font-bold">
                            {log.user?.avatar || "SY"}
                          </AvatarFallback>
                        </Avatar>
                        <div className="space-y-0.5 flex-1 min-w-0">
                          <p className="text-zinc-200">
                            <span className="font-bold text-white">{log.user?.name || "System"}</span>{" "}
                            <span>{log.description || log.action}</span>
                          </p>
                          <span className="text-[10px] text-zinc-500 block">{log.time || log.created_at}</span>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            )}
          </div>

          {/* Right Column: Metadata Sidebar */}
          <div className="space-y-5">
            <div className="p-6 rounded-2xl bg-[#121215] border border-zinc-800/80 space-y-5 shadow-xl font-mono text-xs">
              <h3 className="text-xs font-bold uppercase tracking-wider text-zinc-400 pb-2 border-b border-zinc-800">
                Task Attributes
              </h3>

              {/* Status Picker */}
              <div className="space-y-1.5">
                <label className="text-zinc-500 block">Status</label>
                <select
                  value={currentTask.status_id || 1}
                  disabled={!currentTask.can_edit}
                  onChange={(e) => handleQuickUpdate("status", Number(e.target.value))}
                  className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-zinc-200 focus:outline-none focus:border-zinc-700"
                >
                  <option value={1}>To Do</option>
                  <option value={2}>In Progress</option>
                  <option value={4}>Review</option>
                  <option value={3}>Done</option>
                </select>
              </div>

              {/* Priority Picker */}
              <div className="space-y-1.5">
                <label className="text-zinc-500 block">Priority</label>
                <select
                  value={currentTask.priority_id || 2}
                  disabled={!currentTask.can_edit}
                  onChange={(e) => handleQuickUpdate("priority", Number(e.target.value))}
                  className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-zinc-200 focus:outline-none focus:border-zinc-700"
                >
                  <option value={1}>Low</option>
                  <option value={2}>Medium</option>
                  <option value={3}>High</option>
                  <option value={4}>Urgent</option>
                </select>
              </div>

              {/* Assignee Picker */}
              <div className="space-y-1.5">
                <label className="text-zinc-500 block">Assignee</label>
                <select
                  value={currentTask.assignee?.id || "unassigned"}
                  disabled={!currentTask.can_edit}
                  onChange={(e) =>
                    handleQuickUpdate(
                      "assigned_to",
                      e.target.value === "unassigned" ? null : Number(e.target.value)
                    )
                  }
                  className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-zinc-200 focus:outline-none focus:border-zinc-700"
                >
                  <option value="unassigned">Unassigned</option>
                  {company_users.map((u) => (
                    <option key={u.id} value={u.id}>
                      {u.name}
                    </option>
                  ))}
                </select>
              </div>

              {/* Project Picker */}
              <div className="space-y-1.5">
                <label className="text-zinc-500 block">Project</label>
                <select
                  value={currentTask.project_id || "personal"}
                  disabled={!currentTask.can_edit}
                  onChange={(e) =>
                    handleQuickUpdate(
                      "project_id",
                      e.target.value === "personal" ? null : Number(e.target.value)
                    )
                  }
                  className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-zinc-200 focus:outline-none focus:border-zinc-700"
                >
                  <option value="personal">Personal Space (No Project)</option>
                  {projects.map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.name}
                    </option>
                  ))}
                </select>
              </div>

              {/* Due Date Picker */}
              <div className="space-y-1.5">
                <label className="text-zinc-500 block">Due Date</label>
                <input
                  type="date"
                  value={currentTask.due_date_raw || ""}
                  disabled={!currentTask.can_edit}
                  onChange={(e) => handleQuickUpdate("due_date", e.target.value || null)}
                  className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-zinc-200 focus:outline-none focus:border-zinc-700"
                />
              </div>

              {/* Story Points */}
              <div className="space-y-1.5">
                <label className="text-zinc-500 block">Story Points</label>
                <input
                  type="number"
                  min={0}
                  placeholder="e.g. 5"
                  value={currentTask.points ?? ""}
                  disabled={!currentTask.can_edit}
                  onChange={(e) => handleQuickUpdate("points", e.target.value ? Number(e.target.value) : null)}
                  className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-zinc-200 focus:outline-none focus:border-zinc-700"
                />
              </div>

              {/* Reporter */}
              {currentTask.reporter && (
                <div className="space-y-1 pt-2 border-t border-zinc-800">
                  <span className="text-zinc-500 text-[11px] block">Reporter</span>
                  <div className="flex items-center gap-2">
                    <Avatar className="h-5 w-5 border border-zinc-700">
                      {currentTask.reporter.profile_image && (
                        <AvatarImage src={currentTask.reporter.profile_image} />
                      )}
                      <AvatarFallback className="bg-zinc-800 text-zinc-300 text-[9px] font-bold">
                        {currentTask.reporter.avatar || "SY"}
                      </AvatarFallback>
                    </Avatar>
                    <span className="text-zinc-300 truncate">{currentTask.reporter.name}</span>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Modal: Edit Task */}
        {showEditModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
            <div className="w-full max-w-xl rounded-2xl bg-[#141418] border border-zinc-800 p-6 shadow-2xl space-y-5">
              <div className="flex items-center justify-between pb-3 border-b border-zinc-800">
                <div className="flex items-center gap-2">
                  <div className="p-2 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    <Edit2 className="w-4 h-4" />
                  </div>
                  <h3 className="text-base font-mono font-bold text-white uppercase tracking-wider">
                    Edit Task {currentTask.id}
                  </h3>
                </div>
                <button
                  onClick={() => setShowEditModal(false)}
                  className="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleEditSubmit} className="space-y-4 font-mono text-xs">
                <div>
                  <label className="block text-zinc-400 mb-1">
                    Task Title <span className="text-rose-500">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    value={editFormData.title}
                    onChange={(e) => setEditFormData({ ...editFormData, title: e.target.value })}
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-sm text-zinc-100 focus:outline-none focus:border-blue-500 font-sans"
                  />
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-zinc-400 mb-1">Project</label>
                    <select
                      value={editFormData.project_id}
                      onChange={(e) => setEditFormData({ ...editFormData, project_id: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-zinc-200 focus:outline-none focus:border-blue-500"
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
                    <label className="block text-zinc-400 mb-1">Assignee</label>
                    <select
                      value={editFormData.assigned_to}
                      onChange={(e) => setEditFormData({ ...editFormData, assigned_to: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-zinc-200 focus:outline-none focus:border-blue-500"
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

                <div className="grid grid-cols-3 gap-3">
                  <div>
                    <label className="block text-zinc-400 mb-1">Status</label>
                    <select
                      value={editFormData.status}
                      onChange={(e) => setEditFormData({ ...editFormData, status: Number(e.target.value) })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2 text-zinc-200 focus:outline-none focus:border-blue-500"
                    >
                      <option value={1}>To Do</option>
                      <option value={2}>In Progress</option>
                      <option value={4}>Review</option>
                      <option value={3}>Done</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-zinc-400 mb-1">Priority</label>
                    <select
                      value={editFormData.priority}
                      onChange={(e) => setEditFormData({ ...editFormData, priority: Number(e.target.value) })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2 text-zinc-200 focus:outline-none focus:border-blue-500"
                    >
                      <option value={1}>Low</option>
                      <option value={2}>Medium</option>
                      <option value={3}>High</option>
                      <option value={4}>Urgent</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-zinc-400 mb-1">Type</label>
                    <select
                      value={editFormData.type}
                      onChange={(e) => setEditFormData({ ...editFormData, type: Number(e.target.value) })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2 text-zinc-200 focus:outline-none focus:border-blue-500"
                    >
                      <option value={1}>Task</option>
                      <option value={2}>Bug</option>
                      <option value={3}>Feature</option>
                      <option value={4}>Improvement</option>
                    </select>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-zinc-400 mb-1">Due Date</label>
                    <input
                      type="date"
                      value={editFormData.due_date}
                      onChange={(e) => setEditFormData({ ...editFormData, due_date: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-zinc-200 focus:outline-none focus:border-blue-500"
                    />
                  </div>

                  <div>
                    <label className="block text-zinc-400 mb-1">Story Points</label>
                    <input
                      type="number"
                      min={0}
                      value={editFormData.points}
                      onChange={(e) => setEditFormData({ ...editFormData, points: e.target.value })}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-2.5 text-zinc-200 focus:outline-none focus:border-blue-500"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-zinc-400 mb-1">Description</label>
                  <textarea
                    rows={4}
                    value={editFormData.description}
                    onChange={(e) => setEditFormData({ ...editFormData, description: e.target.value })}
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-sm text-zinc-100 focus:outline-none focus:border-blue-500 resize-none font-sans"
                  />
                </div>

                <div className="flex items-center justify-end gap-3 pt-3 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setShowEditModal(false)}
                    className="border-zinc-800 text-zinc-400 hover:text-white"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    disabled={isSubmittingEdit}
                    className="bg-blue-500 hover:bg-blue-600 text-white font-semibold gap-1.5 px-4"
                  >
                    {isSubmittingEdit && <Loader2 className="w-3.5 h-3.5 animate-spin" />}
                    <span>Save Changes</span>
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Modal: Delete Task Confirmation */}
        {showDeleteModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
            <div className="w-full max-w-md rounded-2xl bg-[#141418] border border-rose-500/30 p-6 shadow-2xl space-y-4">
              <div className="flex items-center gap-3 text-rose-400">
                <div className="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20">
                  <AlertTriangle className="h-5 w-5" />
                </div>
                <div>
                  <h3 className="text-base font-mono font-bold text-white">Delete Task?</h3>
                  <p className="text-xs font-mono text-zinc-500">{currentTask.id}</p>
                </div>
              </div>

              <p className="text-xs sm:text-sm text-zinc-300">
                Are you sure you want to delete <span className="font-semibold text-white">"{currentTask.title}"</span>?
                This will move the task to trash.
              </p>

              <div className="flex items-center justify-end gap-3 pt-3 border-t border-zinc-800">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => setShowDeleteModal(false)}
                  className="border-zinc-800 text-zinc-400 hover:text-white"
                >
                  Cancel
                </Button>
                <Button
                  type="button"
                  size="sm"
                  disabled={isSubmittingDelete}
                  onClick={handleDeleteTask}
                  className="bg-rose-500 hover:bg-rose-600 text-white font-semibold gap-1.5 px-4"
                >
                  {isSubmittingDelete && <Loader2 className="w-3.5 h-3.5 animate-spin" />}
                  <span>Delete Task</span>
                </Button>
              </div>
            </div>
          </div>
        )}

        {/* Modal: Add Note */}
        {showAddNoteModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
            <div className="w-full max-w-lg rounded-2xl bg-[#141418] border border-zinc-800 p-6 shadow-2xl space-y-4">
              <div className="flex items-center justify-between pb-3 border-b border-zinc-800">
                <h3 className="text-base font-mono font-bold text-white flex items-center gap-2">
                  <StickyNote className="w-4 h-4 text-purple-400" />
                  Add Task Note
                </h3>
                <button
                  onClick={() => setShowAddNoteModal(false)}
                  className="p-1 rounded text-zinc-400 hover:text-white"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <form onSubmit={handleAddNote} className="space-y-4 font-mono text-xs">
                <div>
                  <label className="block text-zinc-400 mb-1">Title</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Investigation findings"
                    value={newNoteTitle}
                    onChange={(e) => setNewNoteTitle(e.target.value)}
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-sm text-zinc-100 focus:outline-none focus:border-purple-500 font-sans"
                  />
                </div>

                <div>
                  <label className="block text-zinc-400 mb-1">Content</label>
                  <textarea
                    rows={4}
                    required
                    placeholder="Detailed notes or snippets..."
                    value={newNoteContent}
                    onChange={(e) => setNewNoteContent(e.target.value)}
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-sm text-zinc-100 focus:outline-none focus:border-purple-500 resize-none font-sans"
                  />
                </div>

                <div className="flex items-center justify-end gap-3 pt-3 border-t border-zinc-800">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setShowAddNoteModal(false)}
                    className="border-zinc-800 text-zinc-400 hover:text-white"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    disabled={isSubmittingNote}
                    className="bg-purple-500 hover:bg-purple-600 text-white font-semibold gap-1.5 px-4"
                  >
                    {isSubmittingNote && <Loader2 className="w-3.5 h-3.5 animate-spin" />}
                    <span>Save Note</span>
                  </Button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Modal: Image Lightbox Preview */}
        {previewImage && (
          <div
            onClick={() => setPreviewImage(null)}
            className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md animate-in fade-in cursor-zoom-out"
          >
            <div className="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl border border-zinc-800 shadow-2xl">
              <img src={previewImage} alt="Attachment Preview" className="w-full h-full object-contain" />
              <button
                onClick={() => setPreviewImage(null)}
                className="absolute top-3 right-3 p-2 rounded-full bg-black/60 text-white hover:bg-black/90"
              >
                <X className="w-4 h-4" />
              </button>
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
