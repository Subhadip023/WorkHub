import React, { useState, useRef, useEffect } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Link } from "@inertiajs/react";
import {
  ArrowLeft,
  CheckSquare,
  Square,
  Clock,
  Copy,
  Check,
  Share2,
  GitBranch,
  GitPullRequest,
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
  Network,
  Upload,
  Crown
} from "lucide-react";

import { Button } from "@/Components/ui/button";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";

export default function TaskView({ task }) {
  const [isCopied, setIsCopied] = useState(false);
  const [commentText, setCommentText] = useState("");
  const [newNoteTitle, setNewNoteTitle] = useState("");
  const [newNoteContent, setNewNoteContent] = useState("");
  const [showAddNoteModal, setShowAddNoteModal] = useState(false);

  const historyScrollAnchorRef = useRef(null);
  const commentsScrollAnchorRef = useRef(null);

  const defaultTask = {
    id: "WH-042",
    title: "Inertia.js React layout hydration error on cold start",
    description: `When launching the application on a cold browser refresh, React throws a client-side hydration mismatch warning. The DOM attributes generated on the server render (Laravel Inertia root view) differ slightly from the client state.

### Steps to Reproduce
1. Clear browser cache and navigate to \`/new/dashboard\`.
2. Observe console warning: \`Hydration failed because the initial UI does not match the server-rendered HTML.\`
3. Notice temporary layout flicker during component mounting.

### Expected Behavior
The Inertia page wrapper should hydrate seamlessly without layout reflows or console warnings.`,
    status: "In Progress",
    priority: "Urgent",
    type: "Frontend Bug",
    points: 13,
    dueDate: "Aug 28, 2026",
    project: "Inertia.js Migration",
    branch: "fix/inertia-hydration",
    created: "2 hours ago by Alex Morgan",
    assignee: { name: "Alex Morgan", avatar: "AM", email: "alex@workhub.io" },
    reporter: { name: "Sarah Chen", avatar: "SC" },
    completed: false,
    parentTask: { id: "WH-012", title: "Migrate Blade Templates to Inertia React SPA" },
    pr: { id: "#148", title: "fix(layout): resolve hydration mismatch", status: "Merged" },
    tags: ["Bug", "Frontend", "Inertia.js"],
  };

  const currentTask = task || defaultTask;
  const [isCompleted, setIsCompleted] = useState(currentTask.completed);

  const [subtasks, setSubtasks] = useState([
    { id: 1, text: "Audit SSR initial props passed from HandleInertiaRequests middleware", done: true },
    { id: 2, text: "Ensure dark theme class matching between app.blade.php and React layout", done: true },
    { id: 3, text: "Wrap sidebar provider context with client hydration check", done: true },
    { id: 4, text: "Add Pest feature assertions for cold-start Inertia page resolution", done: true },
    { id: 5, text: "Verify production Vite bundle build output without hydration warnings", done: false },
  ]);

  const [notes, setNotes] = useState([
    {
      id: 1,
      title: "Hydration Root Cause Notes",
      content: "The body tag class was rendering 'bg-slate-950' on server template before updating to 'bg-black' on client mount. Standardizing app.blade.php eliminates the reflow.",
      time: "1 hour ago",
    },
    {
      id: 2,
      title: "Inertia Page Props Validation",
      content: "Ensure HandleInertiaRequests always returns non-null auth array to avoid client undefined property errors.",
      time: "30 mins ago",
    },
  ]);

  const [images, setImages] = useState([
    { id: 1, name: "hydration_console_error.png", url: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop&q=80", size: "1.2 MB" },
    { id: 2, name: "vite_bundle_analysis.png", url: "https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&auto=format&fit=crop&q=80", size: "840 KB" },
  ]);

  const [comments, setComments] = useState([
    {
      id: 1,
      user: { name: "Sarah Chen", avatar: "SC", role: "UI Lead" },
      time: "1 hour ago",
      text: "I reproduced this on Chrome 127. The body background was defaulting to slate-950 on server render before updating to pitch black on mount.",
    },
    {
      id: 2,
      user: { name: "Alex Morgan", avatar: "AM", role: "Tech Lead" },
      time: "45 minutes ago",
      text: "Updated app.blade.php and app.css to use pure black bg-black across both server template and React components. PR #148 is ready for review.",
    },
  ]);

  const [historyLogs, setHistoryLogs] = useState([
    {
      id: 1,
      user: "Alex Morgan",
      avatar: "AM",
      action: "created task",
      details: "Initialized WH-042 in Inertia.js Migration project",
      oldValue: null,
      time: "2 hours ago",
    },
    {
      id: 2,
      user: "Sarah Chen",
      avatar: "SC",
      action: "updated priority",
      details: "Changed priority from High to Urgent",
      oldValue: "Priority: High",
      time: "1 hour 45m ago",
    },
    {
      id: 3,
      user: "Alex Morgan",
      avatar: "AM",
      action: "linked pull request",
      details: "Attached PR #148 (fix/inertia-hydration)",
      oldValue: null,
      time: "1 hour ago",
    },
    {
      id: 4,
      user: "Alex Morgan",
      avatar: "AM",
      action: "changed status",
      details: "Moved status to In Progress",
      oldValue: "Status: Pending",
      time: "45 minutes ago",
    },
    {
      id: 5,
      user: "Sarah Chen",
      avatar: "SC",
      action: "completed subtask",
      details: "Marked 'Wrap sidebar provider context' as done",
      oldValue: null,
      time: "20 minutes ago",
    },
  ]);

  useEffect(() => {
    if (historyScrollAnchorRef.current) {
      historyScrollAnchorRef.current.scrollIntoView({ behavior: "smooth" });
    }
  }, [historyLogs]);

  useEffect(() => {
    if (commentsScrollAnchorRef.current) {
      commentsScrollAnchorRef.current.scrollIntoView({ behavior: "smooth" });
    }
  }, [comments]);

  const handleToggleSubtask = (id) => {
    setSubtasks((prev) =>
      prev.map((item) => {
        if (item.id === id) {
          const nextDone = !item.done;
          setHistoryLogs((logs) => [
            {
              id: Date.now(),
              user: "Current User",
              avatar: "CU",
              action: nextDone ? "completed subtask" : "reopened subtask",
              details: `Marked "${item.text.slice(0, 35)}..." as ${nextDone ? "done" : "todo"}`,
              oldValue: null,
              time: "Just now",
            },
            ...logs,
          ]);
          return { ...item, done: nextDone };
        }
        return item;
      })
    );
  };

  const handleCopyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    setIsCopied(true);
    setTimeout(() => setIsCopied(false), 2000);
  };

  const handleAddComment = (e) => {
    e.preventDefault();
    if (!commentText.trim()) return;

    const newComment = {
      id: Date.now(),
      user: { name: "Current User", avatar: "CU", role: "Administrator" },
      time: "Just now",
      text: commentText,
    };

    setComments([...comments, newComment]);

    setHistoryLogs((logs) => [
      {
        id: Date.now(),
        user: "Current User",
        avatar: "CU",
        action: "posted comment",
        details: commentText,
        oldValue: null,
        time: "Just now",
      },
      ...logs,
    ]);

    setCommentText("");
  };

  const handleAddNote = (e) => {
    e.preventDefault();
    if (!newNoteTitle.trim()) return;

    const noteObj = {
      id: Date.now(),
      title: newNoteTitle,
      content: newNoteContent || "Note details...",
      time: "Just now",
    };

    setNotes([noteObj, ...notes]);
    setNewNoteTitle("");
    setNewNoteContent("");
    setShowAddNoteModal(false);

    setHistoryLogs((logs) => [
      {
        id: Date.now(),
        user: "Current User",
        avatar: "CU",
        action: "added note",
        details: `Created note: "${newNoteTitle}"`,
        oldValue: null,
        time: "Just now",
      },
      ...logs,
    ]);
  };

  const completedSubtaskCount = subtasks.filter((s) => s.done).length;

  const headerActions = (
    <div className="flex items-center gap-2.5">
      <Button
        variant="outline"
        size="sm"
        onClick={handleCopyLink}
        className="bg-black border-neutral-800 hover:bg-neutral-900 text-neutral-200 text-sm gap-2 rounded-xl h-9 px-3.5"
      >
        {isCopied ? <Check className="h-4 w-4 text-emerald-400" /> : <Copy className="h-4 w-4" />}
        {isCopied ? "Copied!" : "Copy Link"}
      </Button>
      <Button
        size="sm"
        onClick={() => {
          const nextCompleted = !isCompleted;
          setIsCompleted(nextCompleted);
          setHistoryLogs((logs) => [
            {
              id: Date.now(),
              user: "Current User",
              avatar: "CU",
              action: nextCompleted ? "completed task" : "reopened task",
              details: `Task status changed to ${nextCompleted ? "Completed" : "In Progress"}`,
              oldValue: null,
              time: "Just now",
            },
            ...logs,
          ]);
        }}
        className={`text-sm gap-2 font-semibold transition-all rounded-xl h-9 px-3.5 ${
          isCompleted
            ? "bg-emerald-950 text-emerald-400 border border-emerald-800"
            : "bg-white text-black hover:bg-neutral-200"
        }`}
      >
        <CheckSquare className="h-4 w-4" />
        {isCompleted ? "Marked Done" : "Mark Complete"}
      </Button>
    </div>
  );

  return (
    <DashboardLayout title={`Task ${currentTask.id}`} activeItem="tasks" actions={headerActions}>
      <div className="space-y-6">
        {/* Parent Task Banner if parent exists */}
        {currentTask.parentTask && (
          <div className="flex items-center justify-between p-4 rounded-xl bg-neutral-900 border border-neutral-800 shadow-lg">
            <div className="flex items-center gap-3">
              <div className="p-2 rounded-lg bg-black border border-neutral-800 text-neutral-300">
                <Network className="h-5 w-5 text-neutral-300" />
              </div>
              <div>
                <span className="font-mono text-xs text-neutral-400 uppercase font-semibold block">
                  Parent Task Initiative
                </span>
                <Link
                  href={`/new/task/${currentTask.parentTask.id}`}
                  className="font-bold text-sm sm:text-base text-white hover:text-neutral-300 transition-colors"
                >
                  {currentTask.parentTask.id}: {currentTask.parentTask.title}
                </Link>
              </div>
            </div>
            <Button asChild size="sm" variant="outline" className="bg-black border-neutral-800 text-neutral-200 text-xs sm:text-sm font-mono h-9">
              <Link href={`/new/task/${currentTask.parentTask.id}`}>
                <ArrowLeft className="h-4 w-4" /> View Parent
              </Link>
            </Button>
          </div>
        )}

        {/* Top Breadcrumb Bar */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-neutral-800 bg-neutral-900/90 p-4 rounded-xl shadow-xl">
          <div className="flex items-center gap-3">
            <Link
              href="/new/tasks"
              className="p-2 rounded-lg bg-black border border-neutral-800 text-neutral-400 hover:text-white transition-colors"
              title="Back to Tasks"
            >
              <ArrowLeft className="h-4 w-4" />
            </Link>
            <div className="flex items-center gap-2 text-sm font-mono">
              <span className="text-neutral-400">WorkHub</span>
              <span className="text-neutral-600">/</span>
              <span className="text-neutral-400">{currentTask.project}</span>
              <span className="text-neutral-600">/</span>
              <span className="text-white font-bold">{currentTask.id}</span>
            </div>
          </div>

          <div className="flex items-center gap-2.5 font-mono text-xs sm:text-sm">
            <span
              className={`px-3 py-1 rounded-full text-xs font-semibold ${
                currentTask.priority === "Urgent"
                  ? "bg-red-950/40 text-red-400 border border-red-800/40"
                  : "bg-amber-950/40 text-amber-300 border border-amber-800/40"
              }`}
            >
              {currentTask.priority}
            </span>
            <span className="px-3 py-1 rounded-full text-xs bg-neutral-800 text-neutral-300 border border-neutral-700 font-semibold">
              {currentTask.status}
            </span>
          </div>
        </div>

        {/* Main Content Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
          {/* Left Main Column (8 cols): Headline + Metadata, Description, Attachments, Notes */}
          <div className="lg:col-span-8 space-y-6">
            {/* Task Headline & Integrated Metadata Header Card */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-6 rounded-xl space-y-5 shadow-xl">
              {/* ID & Creation Tag */}
              <div className="flex flex-wrap items-center justify-between gap-3 border-b border-neutral-800 pb-3">
                <div className="flex items-center gap-2.5">
                  <span className="font-mono text-xs sm:text-sm text-neutral-300 bg-black px-3 py-1 rounded border border-neutral-800 font-bold">
                    {currentTask.id}
                  </span>
                  <span className="text-xs sm:text-sm text-neutral-400 font-mono">
                    Created {currentTask.created}
                  </span>
                </div>

                <div className="flex items-center gap-2 font-mono text-xs">
                  <span className="bg-black text-neutral-300 px-2.5 py-1 rounded border border-neutral-800 flex items-center gap-1.5">
                    <FolderKanban className="h-3.5 w-3.5 text-neutral-400" /> {currentTask.project}
                  </span>
                  <span className="bg-black text-neutral-300 px-2.5 py-1 rounded border border-neutral-800 flex items-center gap-1.5">
                    <GitBranch className="h-3.5 w-3.5 text-neutral-400" /> {currentTask.branch}
                  </span>
                  {currentTask.points && (
                    <span className="bg-black text-white px-2.5 py-1 rounded border border-neutral-800 font-bold">
                      {currentTask.points} pts
                    </span>
                  )}
                </div>
              </div>

              {/* Main Task Headline */}
              <div className="space-y-3">
                <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-snug">
                  {currentTask.title}
                </h1>

                {/* Inline Metadata Row: Assignee, Priority, Status, Type, Due Date, Tags */}
                <div className="flex flex-wrap items-center gap-3.5 pt-1 text-xs sm:text-sm font-mono border-t border-neutral-800/80 pt-4">
                  {/* Assignee */}
                  <div className="flex items-center gap-2 bg-black px-3 py-1.5 rounded-lg border border-neutral-800">
                    <span className="text-neutral-400">Assignee:</span>
                    <div className="flex items-center gap-1.5">
                      <Avatar className="h-5 w-5 border border-neutral-700">
                        <AvatarFallback className="bg-neutral-800 text-neutral-200 text-[9px] font-bold">
                          {currentTask.assignee.avatar}
                        </AvatarFallback>
                      </Avatar>
                      <span className="text-neutral-100 font-semibold">{currentTask.assignee.name}</span>
                    </div>
                  </div>

                  {/* Priority */}
                  <div className="flex items-center gap-2 bg-black px-3 py-1.5 rounded-lg border border-neutral-800">
                    <span className="text-neutral-400">Priority:</span>
                    <span className="text-red-400 font-semibold">{currentTask.priority}</span>
                  </div>

                  {/* Status */}
                  <div className="flex items-center gap-2 bg-black px-3 py-1.5 rounded-lg border border-neutral-800">
                    <span className="text-neutral-400">Status:</span>
                    <span className="text-neutral-200 font-semibold">{currentTask.status}</span>
                  </div>

                  {/* Type */}
                  <div className="flex items-center gap-2 bg-black px-3 py-1.5 rounded-lg border border-neutral-800">
                    <span className="text-neutral-400">Type:</span>
                    <span className="text-neutral-200 font-semibold">{currentTask.type}</span>
                  </div>

                  {/* Due Date */}
                  <div className="flex items-center gap-2 bg-black px-3 py-1.5 rounded-lg border border-neutral-800">
                    <Calendar className="h-3.5 w-3.5 text-neutral-400" />
                    <span className="text-neutral-400">Due:</span>
                    <span className="text-neutral-200 font-semibold">{currentTask.dueDate}</span>
                  </div>

                  {/* Tag Pills */}
                  <div className="flex items-center gap-1.5">
                    {currentTask.tags.map((tag, idx) => (
                      <span
                        key={idx}
                        className="px-2.5 py-1 rounded bg-black text-neutral-300 border border-neutral-800 text-xs font-mono"
                      >
                        [{tag}]
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            </div>

            {/* Description Card */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-6 rounded-xl space-y-4 shadow-xl">
              <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-neutral-200 border-b border-neutral-800 pb-2.5">
                Task Specification & Context
              </h3>

              <div className="text-sm sm:text-base text-neutral-200 space-y-3 font-sans leading-relaxed whitespace-pre-line">
                {currentTask.description}
              </div>

              {/* Code Snippet Box */}
              <div className="mt-4 p-4 rounded-xl bg-black border border-neutral-800 space-y-2 font-mono text-xs sm:text-sm">
                <div className="flex items-center justify-between text-neutral-400 pb-1.5 border-b border-neutral-800 text-xs font-semibold">
                  <span>Console Log Stacktrace</span>
                  <span>React Hydration Warn</span>
                </div>
                <pre className="text-red-400 overflow-x-auto py-1.5 text-xs sm:text-sm leading-relaxed">
                  {`Warning: Prop \`className\` did not match. 
  Server: "bg-slate-950 text-slate-100" 
  Client: "bg-black text-neutral-100"
  at div
  at DashboardLayout (resources/js/Layouts/DashboardLayout.jsx:43)`}
                </pre>
              </div>
            </div>

            {/* Attachments & Image Gallery Card */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-6 rounded-xl space-y-4 shadow-xl">
              <div className="flex items-center justify-between border-b border-neutral-800 pb-2.5">
                <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-neutral-200 flex items-center gap-2">
                  <Paperclip className="h-4 w-4 text-neutral-400" /> Attachments & Screenshot Gallery
                </h3>
                <span className="text-xs font-mono text-neutral-400">{images.length} files attached</span>
              </div>

              {/* Upload Dropzone Box */}
              <div className="border-2 border-dashed border-neutral-800 hover:border-neutral-700 bg-black/60 p-6 rounded-xl text-center space-y-2 cursor-pointer transition-colors">
                <Upload className="h-8 w-8 text-neutral-400 mx-auto" />
                <span className="font-mono text-xs sm:text-sm text-neutral-300 block font-semibold">
                  Click or drag files to upload attachments
                </span>
                <span className="text-xs text-neutral-500 font-mono block">
                  PNG, JPG, WEBP, GIF, PDF (Max 10MB)
                </span>
              </div>

              {/* Image Grid Preview */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                {images.map((img) => (
                  <div key={img.id} className="border border-neutral-800 bg-black rounded-xl overflow-hidden group space-y-2 p-2">
                    <div className="relative h-36 rounded-lg overflow-hidden border border-neutral-800">
                      <img src={img.url} alt={img.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                      <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <a href={img.url} target="_blank" rel="noreferrer" className="p-2 bg-neutral-900 text-white rounded-lg border border-neutral-700 hover:bg-neutral-800">
                          <ExternalLink className="h-4 w-4" />
                        </a>
                      </div>
                    </div>
                    <div className="flex items-center justify-between text-xs font-mono px-1">
                      <span className="text-neutral-300 font-semibold truncate max-w-[160px]">{img.name}</span>
                      <span className="text-neutral-500">{img.size}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Task Notes Section */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-6 rounded-xl space-y-4 shadow-xl">
              <div className="flex items-center justify-between border-b border-neutral-800 pb-2.5">
                <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-neutral-200 flex items-center gap-2">
                  <StickyNote className="h-4 w-4 text-neutral-400" /> Task Notes & Documentation
                </h3>
                <Button
                  size="sm"
                  onClick={() => setShowAddNoteModal(!showAddNoteModal)}
                  className="bg-black border border-neutral-800 hover:bg-neutral-900 text-neutral-200 text-xs sm:text-sm font-mono gap-1.5 h-8 px-3"
                >
                  <Plus className="h-3.5 w-3.5" /> Add Note
                </Button>
              </div>

              {/* Add Note Form */}
              {showAddNoteModal && (
                <form onSubmit={handleAddNote} className="p-4 rounded-xl bg-black border border-neutral-800 space-y-3">
                  <input
                    type="text"
                    required
                    placeholder="Note Title..."
                    value={newNoteTitle}
                    onChange={(e) => setNewNoteTitle(e.target.value)}
                    className="w-full bg-neutral-900 border border-neutral-800 rounded-lg p-2.5 text-xs sm:text-sm text-neutral-200 focus:outline-none focus:border-neutral-700 font-sans"
                  />
                  <textarea
                    rows={3}
                    placeholder="Note description and key technical observations..."
                    value={newNoteContent}
                    onChange={(e) => setNewNoteContent(e.target.value)}
                    className="w-full bg-neutral-900 border border-neutral-800 rounded-lg p-2.5 text-xs sm:text-sm text-neutral-200 focus:outline-none focus:border-neutral-700 font-sans"
                  />
                  <div className="flex justify-end gap-2">
                    <Button type="button" variant="ghost" size="sm" onClick={() => setShowAddNoteModal(false)} className="text-xs">
                      Cancel
                    </Button>
                    <Button type="submit" size="sm" className="bg-white text-black hover:bg-neutral-200 text-xs font-semibold h-8 px-3">
                      Save Note
                    </Button>
                  </div>
                </form>
              )}

              {/* Notes Grid */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {notes.map((n) => (
                  <div key={n.id} className="p-4 rounded-xl bg-black border border-neutral-800 space-y-2 flex flex-col justify-between">
                    <div className="space-y-1.5">
                      <h4 className="font-bold text-sm text-white">{n.title}</h4>
                      <p className="text-xs sm:text-sm text-neutral-300 font-sans leading-relaxed">
                        {n.content}
                      </p>
                    </div>
                    <span className="text-[11px] font-mono text-neutral-500 block pt-2 border-t border-neutral-900">
                      {n.time}
                    </span>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Right Sidebar Panel (4 cols): Subtasks, Linked PR, Quick Actions, Task History, Activity */}
          <div className="lg:col-span-4 space-y-6">
            {/* Subtask Progress Checklist Card */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-5 sm:p-6 rounded-xl space-y-4 shadow-xl">
              <div className="flex items-center justify-between border-b border-neutral-800 pb-2.5">
                <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-neutral-200">
                  Subtask Progress
                </h3>
                <span className="text-xs font-mono text-neutral-400">
                  {completedSubtaskCount}/{subtasks.length} done
                </span>
              </div>

              {/* Progress Bar */}
              <div className="h-2 w-full bg-black rounded-full overflow-hidden border border-neutral-800">
                <div
                  className="h-full bg-gradient-to-r from-neutral-200 to-neutral-400 rounded-full transition-all duration-300"
                  style={{ width: `${(completedSubtaskCount / subtasks.length) * 100}%` }}
                ></div>
              </div>

              <div className="space-y-2.5 pt-1">
                {subtasks.map((st) => (
                  <div
                    key={st.id}
                    onClick={() => handleToggleSubtask(st.id)}
                    className="flex items-center gap-3 p-3.5 rounded-lg bg-black border border-neutral-800 hover:border-neutral-700 transition-colors cursor-pointer"
                  >
                    <button className="text-neutral-500 hover:text-emerald-400 transition-colors shrink-0">
                      {st.done ? (
                        <CheckSquare className="h-5 w-5 text-emerald-400" />
                      ) : (
                        <Square className="h-5 w-5 text-neutral-600" />
                      )}
                    </button>
                    <span
                      className={`text-xs sm:text-sm font-sans ${
                        st.done ? "line-through text-neutral-500" : "text-neutral-100"
                      }`}
                    >
                      {st.text}
                    </span>
                  </div>
                ))}
              </div>
            </div>

            {/* Linked PR Card */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-5 sm:p-6 rounded-xl space-y-3.5 shadow-xl">
              <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-white border-b border-neutral-800 pb-2.5 flex items-center gap-2">
                <GitPullRequest className="h-4 w-4 text-neutral-400" /> Linked Pull Request
              </h3>

              <div className="p-3.5 rounded-lg bg-black border border-neutral-800 space-y-2 font-mono text-xs sm:text-sm">
                <div className="flex items-center justify-between">
                  <span className="text-neutral-200 font-bold text-sm">{currentTask.pr.id}</span>
                  <span className="px-2.5 py-0.5 rounded-full bg-emerald-950/40 text-emerald-400 border border-emerald-800/40 text-xs font-semibold">
                    {currentTask.pr.status}
                  </span>
                </div>
                <p className="text-xs sm:text-sm text-neutral-400 font-sans">{currentTask.pr.title}</p>
              </div>
            </div>

            {/* Dedicated Task History Card */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-5 sm:p-6 rounded-xl space-y-4 shadow-xl">
              <div className="flex items-center justify-between border-b border-neutral-800 pb-2.5">
                <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-neutral-200 flex items-center gap-2">
                  <History className="h-4 w-4 text-neutral-400" /> Task History Log
                </h3>
                <span className="text-xs font-mono text-neutral-400">
                  {historyLogs.length} events
                </span>
              </div>

              <div className="space-y-3 pt-1 font-mono text-xs max-h-72 overflow-y-auto overflow-x-hidden scroll-smooth pr-1">
                {historyLogs.map((log) => (
                  <div
                    key={log.id}
                    className="p-3 rounded-xl bg-black border border-neutral-800 space-y-1"
                  >
                    <div className="flex items-center justify-between text-[11px]">
                      <div className="flex items-center gap-1.5">
                        <Avatar className="h-4 w-4 border border-neutral-700">
                          <AvatarFallback className="bg-neutral-800 text-neutral-200 text-[8px] font-bold">
                            {log.avatar}
                          </AvatarFallback>
                        </Avatar>
                        <span className="font-semibold text-white">{log.user}</span>
                      </div>
                      <span className="text-neutral-500">{log.time}</span>
                    </div>
                    <div className="flex items-center gap-1 text-neutral-300">
                      <span className="px-1.5 py-0.5 rounded bg-neutral-800 text-neutral-300 text-[9px] uppercase font-bold">
                        {log.action}
                      </span>
                    </div>
                    <p className="text-neutral-300 text-xs font-sans">{log.details}</p>
                  </div>
                ))}
                {/* Task History Scroll Anchor */}
                <div ref={historyScrollAnchorRef} id="history-scroll-anchor" className="h-1" />
              </div>
            </div>

            {/* Quick Actions */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-5 sm:p-6 rounded-xl space-y-2.5 shadow-xl">
              <Button
                variant="outline"
                size="sm"
                className="w-full bg-black border-neutral-800 hover:bg-neutral-900 text-neutral-200 text-sm gap-2 justify-start rounded-xl h-9"
              >
                <Edit2 className="h-4 w-4" /> Edit Task Details
              </Button>
              <Button
                variant="outline"
                size="sm"
                className="w-full bg-black border-neutral-800 hover:bg-neutral-900 text-neutral-200 text-sm gap-2 justify-start rounded-xl h-9"
              >
                <Share2 className="h-4 w-4" /> Share Link with Team
              </Button>
            </div>

            {/* Activity & Discussion Container */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-5 sm:p-6 rounded-xl space-y-4 shadow-xl">
              <div className="flex items-center justify-between border-b border-neutral-800 pb-2.5">
                <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-neutral-200 flex items-center gap-2">
                  <MessageSquare className="h-4 w-4 text-neutral-400" /> Activity & Discussion
                </h3>
                <span className="text-xs font-mono text-neutral-400">{comments.length} comments</span>
              </div>

              {/* Comments List */}
              <div className="space-y-3.5 max-h-80 overflow-y-auto overflow-x-hidden scroll-smooth pr-1">
                {comments.map((c) => (
                  <div key={c.id} className="p-3.5 rounded-xl bg-black border border-neutral-800 space-y-2">
                    <div className="flex items-center justify-between text-xs sm:text-sm">
                      <div className="flex items-center gap-2">
                        <Avatar className="h-6 w-6 border border-neutral-700">
                          <AvatarFallback className="bg-neutral-800 text-neutral-200 font-mono text-[9px] font-bold">
                            {c.user.avatar}
                          </AvatarFallback>
                        </Avatar>
                        <span className="font-semibold text-white text-xs sm:text-sm">{c.user.name}</span>
                      </div>
                      <span className="text-xs font-mono text-neutral-500">{c.time}</span>
                    </div>
                    <p className="text-xs sm:text-sm text-neutral-200 font-sans leading-relaxed">
                      {c.text}
                    </p>
                  </div>
                ))}
                {/* Comments Scroll Anchor */}
                <div ref={commentsScrollAnchorRef} id="comments-scroll-anchor" className="h-1" />
              </div>

              {/* Comment Input Form */}
              <form onSubmit={handleAddComment} className="space-y-2.5 pt-1">
                <textarea
                  rows={3}
                  placeholder="Leave a comment or update..."
                  value={commentText}
                  onChange={(e) => setCommentText(e.target.value)}
                  className="w-full bg-black border border-neutral-800 rounded-xl p-3.5 text-xs sm:text-sm text-neutral-200 placeholder-neutral-500 focus:outline-none focus:border-neutral-700 font-sans"
                />
                <div className="flex items-center justify-between">
                  <span className="text-xs font-mono text-neutral-500">
                    Supports Markdown
                  </span>
                  <Button
                    type="submit"
                    size="sm"
                    className="bg-white text-black hover:bg-neutral-200 font-semibold text-xs sm:text-sm gap-2 rounded-xl px-4 h-9"
                  >
                    <Send className="h-4 w-4" /> Comment
                  </Button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}
