import React, { useState } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  CheckSquare,
  Square,
  Plus,
  Search,
  Filter,
  Kanban,
  List,
  Calendar,
  Clock,
  AlertCircle,
  CheckCircle2,
  MoreVertical,
  ChevronRight,
  Sparkles,
  Layers,
  ArrowUpRight,
  User,
  Tag,
  FolderKanban
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
import { Progress } from "@/Components/ui/progress";

export default function Tasks({ initial_tasks }) {
  const [filterTab, setFilterTab] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [viewMode, setViewMode] = useState("kanban"); // 'kanban' | 'list'
  const [showAddModal, setShowAddModal] = useState(false);

  const [tasksList, setTasksList] = useState(
    initial_tasks || [
      {
        id: 1,
        title: "Implement Inertia.js React layout with shadcn UI Sidebar",
        description: "Migrate navigation header and left panel to official shadcn sidebar primitives.",
        status: "In Progress",
        priority: "High",
        dueDate: "Today",
        project: "Inertia.js Migration",
        assignee: { name: "Alex Morgan", avatar: "AM" },
        category: "Dev",
        completed: false,
        subtasks: "4/5",
      },
      {
        id: 2,
        title: "Review pull request #142 (WorkHub task API limits)",
        description: "Verify rate-limiting middleware triggers HTTP 429 when threshold exceeded.",
        status: "To Do",
        priority: "High",
        dueDate: "Today",
        project: "WorkHub API",
        assignee: { name: "Sarah Chen", avatar: "SC" },
        category: "Code Review",
        completed: false,
        subtasks: "1/2",
      },
      {
        id: 3,
        title: "Optimize database queries for TaskRepository dashboard filter",
        description: "Ensure status 4 tasks and on-hold projects are excluded cleanly.",
        status: "Done",
        priority: "Medium",
        dueDate: "Yesterday",
        project: "WorkHub Core",
        assignee: { name: "Michael Scott", avatar: "MS" },
        category: "Backend",
        completed: true,
        subtasks: "3/3",
      },
      {
        id: 4,
        title: "Design dark mode theme tokens for high-contrast cards",
        description: "Refactor Tailwind color utilities with ambient slate-950 glows.",
        status: "In Progress",
        priority: "Medium",
        dueDate: "Tomorrow",
        project: "shadcn/ui Design",
        assignee: { name: "Sarah Chen", avatar: "SC" },
        category: "UI/UX",
        completed: false,
        subtasks: "2/4",
      },
      {
        id: 5,
        title: "Setup Pest feature tests for /new/analytics & /new/projects",
        description: "Write assertions confirming Inertia props resolution.",
        status: "Review",
        priority: "High",
        dueDate: "Aug 24",
        project: "Inertia.js Migration",
        assignee: { name: "Emma Watson", avatar: "EW" },
        category: "QA & Testing",
        completed: false,
        subtasks: "2/2",
      },
      {
        id: 6,
        title: "Configure GitHub Actions CI pipeline with zero downtime",
        description: "Deploy automated Pest test runner before production staging releases.",
        status: "To Do",
        priority: "Low",
        dueDate: "Aug 29",
        project: "CI/CD Pipeline",
        assignee: { name: "David Kim", avatar: "DK" },
        category: "DevOps",
        completed: false,
        subtasks: "0/3",
      },
      {
        id: 7,
        title: "Draft sprint retrospective notes & engineering team metrics",
        description: "Compile cycle time and team velocity data into executive report.",
        status: "Done",
        priority: "Low",
        dueDate: "Aug 18",
        project: "WorkHub Management",
        assignee: { name: "Alex Morgan", avatar: "AM" },
        category: "Docs",
        completed: true,
        subtasks: "2/2",
      },
    ]
  );

  // New Task Form state
  const [newTask, setNewTask] = useState({
    title: "",
    project: "Inertia.js Migration",
    priority: "High",
    dueDate: "Today",
    status: "To Do",
  });

  const toggleTaskCompleted = (id) => {
    setTasksList((prev) =>
      prev.map((t) =>
        t.id === id
          ? {
              ...t,
              completed: !t.completed,
              status: !t.completed ? "Done" : "In Progress",
            }
          : t
      )
    );
  };

  const handleCreateTask = (e) => {
    e.preventDefault();
    if (!newTask.title.trim()) return;

    const taskObj = {
      id: Date.now(),
      title: newTask.title,
      description: "Newly added task item.",
      status: newTask.status,
      priority: newTask.priority,
      dueDate: newTask.dueDate,
      project: newTask.project,
      assignee: { name: "Current User", avatar: "CU" },
      category: "General",
      completed: newTask.status === "Done",
      subtasks: "0/1",
    };

    setTasksList([taskObj, ...tasksList]);
    setNewTask({ title: "", project: "Inertia.js Migration", priority: "High", dueDate: "Today", status: "To Do" });
    setShowAddModal(false);
  };

  const filteredTasks = tasksList.filter((t) => {
    const matchesSearch =
      t.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      t.project.toLowerCase().includes(searchQuery.toLowerCase()) ||
      t.category.toLowerCase().includes(searchQuery.toLowerCase());

    if (filterTab === "pending") return matchesSearch && !t.completed;
    if (filterTab === "completed") return matchesSearch && t.completed;
    if (filterTab === "high_priority") return matchesSearch && t.priority === "High";
    return matchesSearch;
  });

  const kanbanColumns = [
    { key: "To Do", label: "To Do", color: "border-slate-700 bg-slate-900/40" },
    { key: "In Progress", label: "In Progress", color: "border-indigo-500/30 bg-indigo-950/20" },
    { key: "Review", label: "Review & QA", color: "border-amber-500/30 bg-amber-950/20" },
    { key: "Done", label: "Done", color: "border-emerald-500/30 bg-emerald-950/20" },
  ];

  const headerActions = (
    <Button
      variant="gradient"
      size="sm"
      onClick={() => setShowAddModal(true)}
      className="hidden sm:flex items-center gap-1.5 shadow-indigo-500/25"
    >
      <Plus className="h-4 w-4" /> New Task
    </Button>
  );

  return (
    <DashboardLayout title="My Tasks" activeItem="tasks" actions={headerActions}>
      <div className="space-y-8">
        {/* Header Banner */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-rose-950/60 via-slate-900/80 to-slate-900/60 p-6 rounded-2xl border border-rose-500/20 shadow-2xl relative overflow-hidden">
          <div className="space-y-1 z-10">
            <div className="flex items-center gap-2">
              <Badge variant="default" className="gap-1 bg-rose-500/20 text-rose-300 border-rose-500/30">
                <CheckSquare className="h-3.5 w-3.5 text-rose-400" /> Task Management
              </Badge>
              <span className="text-xs text-slate-400">
                {tasksList.filter((t) => !t.completed).length} pending tasks
              </span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
              My Task Checklist & Kanban
            </h1>
            <p className="text-sm text-slate-400 max-w-xl">
              Track deadlines, organize priorities, and toggle completion across your workspaces.
            </p>
          </div>

          <div className="flex items-center gap-3 z-10">
            <Button
              variant="gradient"
              className="gap-2 shadow-indigo-500/25"
              onClick={() => setShowAddModal(true)}
            >
              <Plus className="h-4 w-4" /> Add Task
            </Button>
          </div>
        </div>

        {/* Quick KPI Overview */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card className="border-slate-800/80">
            <CardHeader className="pb-2">
              <CardDescription className="text-xs text-slate-400 font-medium">
                Total Assigned Tasks
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                {tasksList.length}
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <span className="text-xs text-indigo-400 font-medium">Across all project spaces</span>
            </CardContent>
          </Card>

          <Card className="border-slate-800/80">
            <CardHeader className="pb-2">
              <CardDescription className="text-xs text-slate-400 font-medium">
                High Priority
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-rose-400 pt-1">
                {tasksList.filter((t) => t.priority === "High" && !t.completed).length}
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <span className="text-xs text-rose-400 font-medium">Needs immediate focus</span>
            </CardContent>
          </Card>

          <Card className="border-slate-800/80">
            <CardHeader className="pb-2">
              <CardDescription className="text-xs text-slate-400 font-medium">
                Completed
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-emerald-400 pt-1">
                {tasksList.filter((t) => t.completed).length}
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <span className="text-xs text-slate-400">
                {Math.round((tasksList.filter((t) => t.completed).length / tasksList.length) * 100)}% completion rate
              </span>
            </CardContent>
          </Card>

          <Card className="border-slate-800/80">
            <CardHeader className="pb-2">
              <CardDescription className="text-xs text-slate-400 font-medium">
                Due Today
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-amber-400 pt-1">
                {tasksList.filter((t) => t.dueDate === "Today" && !t.completed).length}
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <Progress
                value={
                  (tasksList.filter((t) => t.completed).length / tasksList.length) * 100
                }
                className="h-1.5 mt-1"
              />
            </CardContent>
          </Card>
        </div>

        {/* Filter Bar & Controls */}
        <div className="flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-900/60 p-3 rounded-xl border border-slate-800">
          {/* Tabs */}
          <div className="flex items-center gap-1 bg-slate-950 p-1 rounded-lg border border-slate-800 text-xs w-full sm:w-auto overflow-x-auto">
            {[
              { key: "all", label: "All Tasks" },
              { key: "pending", label: "Pending" },
              { key: "high_priority", label: "High Priority" },
              { key: "completed", label: "Done" },
            ].map((tab) => (
              <button
                key={tab.key}
                onClick={() => setFilterTab(tab.key)}
                className={`px-3 py-1.5 rounded-md font-medium whitespace-nowrap transition-all ${
                  filterTab === tab.key
                    ? "bg-rose-600 text-white shadow-md shadow-rose-600/20"
                    : "text-slate-400 hover:text-slate-200 hover:bg-slate-900"
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>

          {/* Search & View Mode Switcher */}
          <div className="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <div className="relative w-full sm:w-56">
              <Search className="absolute left-3 top-2.5 h-3.5 w-3.5 text-slate-400" />
              <input
                type="text"
                placeholder="Search tasks..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-slate-950 border border-slate-800 rounded-lg pl-8 pr-3 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-rose-500"
              />
            </div>

            <div className="flex items-center bg-slate-950 p-1 rounded-lg border border-slate-800">
              <button
                onClick={() => setViewMode("kanban")}
                className={`p-1.5 rounded ${viewMode === "kanban" ? "bg-slate-800 text-white" : "text-slate-400"}`}
                title="Kanban Board View"
              >
                <Kanban className="h-3.5 w-3.5" />
              </button>
              <button
                onClick={() => setViewMode("list")}
                className={`p-1.5 rounded ${viewMode === "list" ? "bg-slate-800 text-white" : "text-slate-400"}`}
                title="Table List View"
              >
                <List className="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>

        {/* Modal for Creating New Task */}
        {showAddModal && (
          <div className="p-6 rounded-xl bg-slate-900 border border-rose-500/30 space-y-4 shadow-2xl relative">
            <div className="flex items-center justify-between">
              <h3 className="text-lg font-bold text-white flex items-center gap-2">
                <Plus className="h-5 w-5 text-rose-400" /> Create New Task Item
              </h3>
              <Button variant="ghost" size="sm" onClick={() => setShowAddModal(false)}>
                Cancel
              </Button>
            </div>

            <form onSubmit={handleCreateTask} className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="md:col-span-2">
                <label className="text-xs font-semibold text-slate-300 block mb-1">Task Title</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Implement reCAPTCHA API validation"
                  value={newTask.title}
                  onChange={(e) => setNewTask({ ...newTask, title: e.target.value })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-200 focus:outline-none focus:border-rose-500"
                />
              </div>

              <div>
                <label className="text-xs font-semibold text-slate-300 block mb-1">Project</label>
                <select
                  value={newTask.project}
                  onChange={(e) => setNewTask({ ...newTask, project: e.target.value })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-200 focus:outline-none focus:border-rose-500"
                >
                  <option value="Inertia.js Migration">Inertia.js Migration</option>
                  <option value="WorkHub API">WorkHub API</option>
                  <option value="WorkHub Core">WorkHub Core</option>
                  <option value="shadcn/ui Design">shadcn/ui Design</option>
                </select>
              </div>

              <div>
                <label className="text-xs font-semibold text-slate-300 block mb-1">Priority</label>
                <select
                  value={newTask.priority}
                  onChange={(e) => setNewTask({ ...newTask, priority: e.target.value })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-200 focus:outline-none focus:border-rose-500"
                >
                  <option value="High">High Priority</option>
                  <option value="Medium">Medium Priority</option>
                  <option value="Low">Low Priority</option>
                </select>
              </div>

              <div className="md:col-span-2 flex justify-end gap-2">
                <Button type="button" variant="outline" size="sm" onClick={() => setShowAddModal(false)}>
                  Cancel
                </Button>
                <Button type="submit" variant="gradient" size="sm">
                  Add Task
                </Button>
              </div>
            </form>
          </div>
        )}

        {/* Kanban Board View */}
        {viewMode === "kanban" ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {kanbanColumns.map((col) => {
              const colTasks = filteredTasks.filter((t) => t.status === col.key);

              return (
                <div
                  key={col.key}
                  className={`p-4 rounded-xl border ${col.color} space-y-3 min-h-[420px] flex flex-col justify-between`}
                >
                  <div>
                    {/* Column Header */}
                    <div className="flex items-center justify-between pb-3 border-b border-slate-800">
                      <h4 className="text-xs font-bold uppercase tracking-wider text-slate-200 flex items-center gap-2">
                        {col.label}
                      </h4>
                      <Badge variant="secondary" className="font-mono text-[10px]">
                        {colTasks.length}
                      </Badge>
                    </div>

                    {/* Task Cards Stack */}
                    <div className="space-y-3 pt-3">
                      {colTasks.map((t) => (
                        <div
                          key={t.id}
                          className="p-3.5 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all space-y-2.5 group cursor-pointer"
                        >
                          <div className="flex items-start gap-2.5">
                            <button
                              onClick={() => toggleTaskCompleted(t.id)}
                              className="mt-0.5 text-slate-500 hover:text-emerald-400"
                            >
                              {t.completed ? (
                                <CheckSquare className="h-4 w-4 text-emerald-400" />
                              ) : (
                                <Square className="h-4 w-4" />
                              )}
                            </button>
                            <p
                              className={`text-xs font-medium leading-snug text-slate-200 ${
                                t.completed ? "line-through text-slate-500" : ""
                              }`}
                            >
                              {t.title}
                            </p>
                          </div>

                          <div className="flex items-center gap-2 text-[10px]">
                            <span className="px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 font-semibold">
                              {t.project}
                            </span>
                            <span
                              className={`px-1.5 py-0.2 rounded font-semibold ${
                                t.priority === "High"
                                  ? "bg-rose-500/20 text-rose-300"
                                  : t.priority === "Medium"
                                  ? "bg-amber-500/20 text-amber-300"
                                  : "bg-slate-800 text-slate-400"
                              }`}
                            >
                              {t.priority}
                            </span>
                          </div>

                          <div className="flex items-center justify-between pt-2 border-t border-slate-800/60 text-[10px] text-slate-400">
                            <div className="flex items-center gap-1">
                              <Clock className="h-3 w-3 text-slate-500" />
                              <span>{t.dueDate}</span>
                            </div>

                            <div className="flex items-center gap-2">
                              <span className="font-mono">{t.subtasks}</span>
                              <Avatar className="h-5 w-5">
                                <AvatarFallback className="bg-slate-800 text-[8px] font-bold">
                                  {t.assignee.avatar}
                                </AvatarFallback>
                              </Avatar>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          /* List Table View */
          <Card className="border-slate-800/80">
            <CardContent className="p-0">
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead>
                    <tr className="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase tracking-wider font-semibold">
                      <th className="py-3 px-4 w-10">Done</th>
                      <th className="py-3 px-4">Task</th>
                      <th className="py-3 px-4">Project</th>
                      <th className="py-3 px-4">Priority</th>
                      <th className="py-3 px-4">Status</th>
                      <th className="py-3 px-4">Due Date</th>
                      <th className="py-3 px-4">Assignee</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800/60">
                    {filteredTasks.map((t) => (
                      <tr key={t.id} className="hover:bg-slate-900/50 transition-colors">
                        <td className="py-3 px-4">
                          <button
                            onClick={() => toggleTaskCompleted(t.id)}
                            className="text-slate-500 hover:text-emerald-400"
                          >
                            {t.completed ? (
                              <CheckSquare className="h-4 w-4 text-emerald-400" />
                            ) : (
                              <Square className="h-4 w-4" />
                            )}
                          </button>
                        </td>
                        <td className="py-3 px-4">
                          <span
                            className={`font-semibold text-slate-100 ${
                              t.completed ? "line-through text-slate-500" : ""
                            }`}
                          >
                            {t.title}
                          </span>
                        </td>
                        <td className="py-3 px-4">
                          <span className="px-2 py-0.5 text-[10px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 rounded">
                            {t.project}
                          </span>
                        </td>
                        <td className="py-3 px-4">
                          <span
                            className={`px-1.5 py-0.5 rounded text-[10px] font-semibold ${
                              t.priority === "High"
                                ? "bg-rose-500/20 text-rose-300"
                                : t.priority === "Medium"
                                ? "bg-amber-500/20 text-amber-300"
                                : "bg-slate-800 text-slate-400"
                            }`}
                          >
                            {t.priority}
                          </span>
                        </td>
                        <td className="py-3 px-4">
                          <Badge variant={t.completed ? "success" : "default"}>{t.status}</Badge>
                        </td>
                        <td className="py-3 px-4 text-slate-400 font-mono">{t.dueDate}</td>
                        <td className="py-3 px-4">
                          <div className="flex items-center gap-2">
                            <Avatar className="h-6 w-6">
                              <AvatarFallback className="bg-slate-800 text-[9px] font-bold">
                                {t.assignee.avatar}
                              </AvatarFallback>
                            </Avatar>
                            <span className="text-slate-300">{t.assignee.name}</span>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </DashboardLayout>
  );
}
