import React, { useState } from "react";
import { Link } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  CheckSquare,
  Square,
  Plus,
  Search,
  Kanban,
  List,
  Clock,
  Crown
} from "lucide-react";

import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";

export default function Tasks({ initial_tasks }) {
  const [filterTab, setFilterTab] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [viewMode, setViewMode] = useState("kanban"); // 'kanban' | 'list'
  const [showAddModal, setShowAddModal] = useState(false);

  const [tasksList, setTasksList] = useState(
    initial_tasks || [
      {
        id: "WH-042",
        title: "Implement Inertia.js React layout with shadcn UI Sidebar",
        description: "Migrate navigation header and left panel to official shadcn sidebar primitives.",
        status: "In Progress",
        priority: "Urgent",
        dueDate: "Today",
        project: "Inertia.js Migration",
        branch: "feat/inertia-layout",
        assignee: { name: "Alex Morgan", avatar: "AM" },
        category: "Dev",
        completed: false,
        subtasks: "4/5",
      },
      {
        id: "WH-039",
        title: "Review pull request #142 (WorkHub task API limits)",
        description: "Verify rate-limiting middleware triggers HTTP 429 when threshold exceeded.",
        status: "To Do",
        priority: "Urgent",
        dueDate: "Today",
        project: "WorkHub API",
        branch: "review/pr-142",
        assignee: { name: "Sarah Chen", avatar: "SC" },
        category: "Code Review",
        completed: false,
        subtasks: "1/2",
      },
      {
        id: "WH-035",
        title: "Optimize database queries for TaskRepository dashboard filter",
        description: "Ensure status 4 tasks and on-hold projects are excluded cleanly.",
        status: "Done",
        priority: "Medium",
        dueDate: "Yesterday",
        project: "WorkHub Core",
        branch: "fix/query-filter",
        assignee: { name: "Michael Scott", avatar: "MS" },
        category: "Backend",
        completed: true,
        subtasks: "3/3",
      },
      {
        id: "WH-031",
        title: "Design monochrome dark mode theme tokens with zinc palette",
        description: "Refactor Tailwind color utilities with ultra-clean monochrome glows.",
        status: "In Progress",
        priority: "Medium",
        dueDate: "Tomorrow",
        project: "shadcn/ui Design",
        branch: "feat/zinc-theme",
        assignee: { name: "Sarah Chen", avatar: "SC" },
        category: "UI/UX",
        completed: false,
        subtasks: "2/4",
      },
      {
        id: "WH-028",
        title: "Setup Pest feature tests for /new/analytics & /new/projects",
        description: "Write assertions confirming Inertia props resolution.",
        status: "Review",
        priority: "Urgent",
        dueDate: "Aug 24",
        project: "Inertia.js Migration",
        branch: "test/inertia-props",
        assignee: { name: "Emma Watson", avatar: "EW" },
        category: "QA & Testing",
        completed: false,
        subtasks: "2/2",
      },
      {
        id: "WH-022",
        title: "Configure GitHub Actions CI pipeline with zero downtime",
        description: "Deploy automated Pest test runner before production staging releases.",
        status: "To Do",
        priority: "Low",
        dueDate: "Aug 29",
        project: "CI/CD Pipeline",
        branch: "main",
        assignee: { name: "David Kim", avatar: "DK" },
        category: "DevOps",
        completed: false,
        subtasks: "0/3",
      },
    ]
  );

  const [newTask, setNewTask] = useState({
    title: "",
    project: "Inertia.js Migration",
    priority: "Urgent",
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
      id: `WH-${Math.floor(100 + Math.random() * 900)}`,
      title: newTask.title,
      description: "Newly created task item.",
      status: newTask.status,
      priority: newTask.priority,
      dueDate: newTask.dueDate,
      project: newTask.project,
      branch: "main",
      assignee: { name: "Current User", avatar: "CU" },
      category: "General",
      completed: newTask.status === "Done",
      subtasks: "0/1",
    };

    setTasksList([taskObj, ...tasksList]);
    setNewTask({ title: "", project: "Inertia.js Migration", priority: "Urgent", dueDate: "Today", status: "To Do" });
    setShowAddModal(false);
  };

  const filteredTasks = tasksList.filter((t) => {
    const matchesSearch =
      t.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      t.project.toLowerCase().includes(searchQuery.toLowerCase()) ||
      t.id.toLowerCase().includes(searchQuery.toLowerCase());

    if (filterTab === "pending") return matchesSearch && !t.completed;
    if (filterTab === "completed") return matchesSearch && t.completed;
    if (filterTab === "urgent") return matchesSearch && t.priority === "Urgent";
    return matchesSearch;
  });

  const kanbanColumns = [
    { key: "To Do", label: "To Do" },
    { key: "In Progress", label: "In Progress" },
    { key: "Review", label: "Review & QA" },
    { key: "Done", label: "Done" },
  ];

  const headerActions = (
    <Button
      size="sm"
      onClick={() => setShowAddModal(true)}
      className="bg-white text-black hover:bg-neutral-200 font-semibold text-sm gap-2 transition-all rounded-xl h-9 px-3.5"
    >
      <Plus className="h-4 w-4 text-black" /> Create Task
    </Button>
  );

  return (
    <DashboardLayout title="Tasks & Deliverables" activeItem="tasks" actions={headerActions}>
      <div className="space-y-6">
        {/* Header Bar */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-neutral-800 bg-neutral-900/90 p-6 rounded-xl shadow-xl">
          <div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
              Task Board <Crown className="h-6 w-6 text-neutral-300" />
            </h1>
            <p className="text-sm sm:text-base text-neutral-400 font-mono mt-1">
              High-density task tracking, priority flags, and sprint status
            </p>
          </div>

          {/* Quick Metrics */}
          <div className="flex items-center gap-3 font-mono text-xs sm:text-sm">
            <div className="px-4 py-2 rounded-lg bg-black border border-neutral-800 text-neutral-300">
              Open: <span className="font-bold text-white">{tasksList.filter((t) => !t.completed).length}</span>
            </div>
            <div className="px-4 py-2 rounded-lg bg-red-950/40 text-red-400 border border-red-800/40">
              Urgent: <span className="font-bold">{tasksList.filter((t) => t.priority === "Urgent" && !t.completed).length}</span>
            </div>
          </div>
        </div>

        {/* Filter Bar */}
        <div className="flex flex-col sm:flex-row items-center justify-between gap-3 bg-neutral-900/90 p-4 rounded-xl border border-neutral-800 shadow-lg">
          <div className="flex items-center gap-1 bg-black p-1 rounded-lg border border-neutral-800 text-xs sm:text-sm font-mono w-full sm:w-auto">
            {[
              { key: "all", label: "All" },
              { key: "pending", label: "Open" },
              { key: "urgent", label: "Urgent" },
              { key: "completed", label: "Closed" },
            ].map((tab) => (
              <button
                key={tab.key}
                onClick={() => setFilterTab(tab.key)}
                className={`px-4 py-1.5 rounded-md transition-all ${
                  filterTab === tab.key
                    ? "bg-neutral-800 text-white font-semibold"
                    : "text-neutral-400 hover:text-neutral-200"
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>

          <div className="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <div className="relative w-full sm:w-64">
              <Search className="absolute left-3 top-2.5 h-4 w-4 text-neutral-400" />
              <input
                type="text"
                placeholder="Search issue title or #ID..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-black border border-neutral-800 rounded-lg pl-9 pr-3 py-2 text-xs sm:text-sm text-neutral-200 placeholder-neutral-500 focus:outline-none focus:border-neutral-700 font-mono"
              />
            </div>

            <div className="flex items-center bg-black p-1 rounded-lg border border-neutral-800 text-xs">
              <button
                onClick={() => setViewMode("kanban")}
                className={`p-2 rounded-md transition-colors ${viewMode === "kanban" ? "bg-neutral-800 text-white" : "text-neutral-400"}`}
                title="Kanban Board View"
              >
                <Kanban className="h-4 w-4" />
              </button>
              <button
                onClick={() => setViewMode("list")}
                className={`p-2 rounded-md transition-colors ${viewMode === "list" ? "bg-neutral-800 text-white" : "text-neutral-400"}`}
                title="Table View"
              >
                <List className="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        {/* Modal for Creating Task */}
        {showAddModal && (
          <div className="p-6 rounded-xl bg-neutral-900 border border-neutral-800 space-y-4 shadow-2xl">
            <div className="flex items-center justify-between pb-2.5 border-b border-neutral-800">
              <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-white">
                Create New Task
              </h3>
              <Button variant="ghost" size="sm" onClick={() => setShowAddModal(false)} className="text-xs sm:text-sm text-neutral-400">
                Cancel
              </Button>
            </div>

            <form onSubmit={handleCreateTask} className="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs sm:text-sm">
              <div className="md:col-span-2">
                <label className="font-mono text-neutral-400 block mb-1">Task Title</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. #WH-050 Fix reCAPTCHA API validation"
                  value={newTask.title}
                  onChange={(e) => setNewTask({ ...newTask, title: e.target.value })}
                  className="w-full bg-black border border-neutral-800 rounded-lg p-3 text-neutral-200 focus:outline-none focus:border-neutral-700"
                />
              </div>

              <div>
                <label className="font-mono text-neutral-400 block mb-1">Project</label>
                <select
                  value={newTask.project}
                  onChange={(e) => setNewTask({ ...newTask, project: e.target.value })}
                  className="w-full bg-black border border-neutral-800 rounded-lg p-3 text-neutral-200 focus:outline-none focus:border-neutral-700 font-mono"
                >
                  <option value="Inertia.js Migration">Inertia.js Migration</option>
                  <option value="WorkHub API">WorkHub API</option>
                  <option value="WorkHub Core">WorkHub Core</option>
                </select>
              </div>

              <div>
                <label className="font-mono text-neutral-400 block mb-1">Priority</label>
                <select
                  value={newTask.priority}
                  onChange={(e) => setNewTask({ ...newTask, priority: e.target.value })}
                  className="w-full bg-black border border-neutral-800 rounded-lg p-3 text-neutral-200 focus:outline-none focus:border-neutral-700 font-mono"
                >
                  <option value="Urgent">Urgent</option>
                  <option value="High">High</option>
                  <option value="Medium">Medium</option>
                </select>
              </div>

              <div className="md:col-span-2 flex justify-end gap-2.5 pt-2">
                <Button type="button" variant="outline" size="sm" onClick={() => setShowAddModal(false)} className="text-sm h-9">
                  Cancel
                </Button>
                <Button type="submit" size="sm" className="bg-white text-black hover:bg-neutral-200 font-semibold text-sm h-9 px-4">
                  Save Task
                </Button>
              </div>
            </form>
          </div>
        )}

        {/* View Rendering */}
        {viewMode === "kanban" ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {kanbanColumns.map((col) => {
              const colTasks = filteredTasks.filter((t) => t.status === col.key);

              return (
                <div
                  key={col.key}
                  className="p-4 rounded-xl border border-neutral-800 bg-neutral-900/80 space-y-3 min-h-[440px] flex flex-col justify-between shadow-xl"
                >
                  <div>
                    <div className="flex items-center justify-between pb-2.5 border-b border-neutral-800">
                      <h4 className="text-sm font-mono font-bold uppercase tracking-wider text-neutral-200">
                        {col.label}
                      </h4>
                      <span className="font-mono text-xs bg-black text-neutral-400 px-2.5 py-0.5 rounded border border-neutral-800 font-semibold">
                        {colTasks.length}
                      </span>
                    </div>

                    <div className="space-y-3 pt-3">
                      {colTasks.map((t) => (
                        <div
                          key={t.id}
                          className="p-4 rounded-lg bg-black border border-neutral-800 hover:border-neutral-700 transition-all space-y-3 cursor-pointer shadow-md"
                        >
                          <div className="flex items-start gap-2.5">
                            <button
                              onClick={() => toggleTaskCompleted(t.id)}
                              className="mt-0.5 text-neutral-500 hover:text-emerald-400 transition-colors shrink-0"
                            >
                              {t.completed ? (
                                <CheckSquare className="h-5 w-5 text-emerald-400" />
                              ) : (
                                <Square className="h-5 w-5 text-neutral-600" />
                              )}
                            </button>
                            <Link href={`/new/task/${t.id}`} className="group/link block min-w-0">
                              <span className="font-mono text-xs text-neutral-500 block group-hover/link:text-neutral-300 transition-colors">
                                {t.id}
                              </span>
                              <p
                                className={`text-xs sm:text-sm font-medium leading-snug group-hover/link:underline ${
                                  t.completed ? "line-through text-neutral-500" : "text-neutral-100 group-hover/link:text-white"
                                }`}
                              >
                                {t.title}
                              </p>
                            </Link>
                          </div>

                          <div className="flex items-center gap-2 text-xs font-mono flex-wrap">
                            <span className="px-2 py-0.5 rounded bg-neutral-900 text-neutral-300 border border-neutral-800">
                              [{t.project}]
                            </span>
                            <span
                              className={`px-2.5 py-0.5 rounded-full font-semibold ${
                                t.priority === "Urgent"
                                  ? "bg-red-950/40 text-red-400 border border-red-800/40"
                                  : t.priority === "High"
                                  ? "bg-amber-950/40 text-amber-300 border border-amber-800/40"
                                  : "bg-neutral-800 text-neutral-300 border border-neutral-700"
                              }`}
                            >
                              {t.priority}
                            </span>
                          </div>

                          <div className="flex items-center justify-between pt-2 border-t border-neutral-800/80 text-xs font-mono text-neutral-400">
                            <span className="text-neutral-400">{t.dueDate}</span>
                            <Avatar className="h-5 w-5 border border-neutral-700">
                              <AvatarFallback className="bg-neutral-800 text-neutral-200 text-[8px] font-bold">
                                {t.assignee.avatar}
                              </AvatarFallback>
                            </Avatar>
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
          /* Table View */
          <div className="border border-neutral-800 bg-neutral-900/90 rounded-xl overflow-hidden shadow-2xl">
            <table className="w-full text-left text-xs sm:text-sm font-sans">
              <thead>
                <tr className="border-b border-neutral-800 bg-black/60 text-neutral-400 font-mono text-xs uppercase tracking-wider">
                  <th className="py-3.5 px-4 w-8"></th>
                  <th className="py-3.5 px-4">Task ID & Title</th>
                  <th className="py-3.5 px-4">Project</th>
                  <th className="py-3.5 px-4">Branch</th>
                  <th className="py-3.5 px-4">Priority</th>
                  <th className="py-3.5 px-4">Assignee</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-neutral-800/60">
                {filteredTasks.map((t) => (
                  <tr key={t.id} className="hover:bg-neutral-800/50 transition-colors duration-150">
                    <td className="py-3.5 px-4">
                      <button onClick={() => toggleTaskCompleted(t.id)} className="text-neutral-500 hover:text-emerald-400">
                        {t.completed ? <CheckSquare className="h-5 w-5 text-emerald-400" /> : <Square className="h-5 w-5 text-neutral-600" />}
                      </button>
                    </td>
                    <td className="py-3.5 px-4">
                      <Link href={`/new/task/${t.id}`} className="flex items-center gap-2.5 group/link">
                        <span className="font-mono text-xs sm:text-sm text-neutral-500 group-hover/link:text-neutral-300">{t.id}</span>
                        <span className={`font-medium text-xs sm:text-sm group-hover/link:underline ${t.completed ? "line-through text-neutral-500" : "text-white group-hover/link:text-white"}`}>{t.title}</span>
                      </Link>
                    </td>
                    <td className="py-3.5 px-4 font-mono text-xs sm:text-sm text-neutral-300">[{t.project}]</td>
                    <td className="py-3.5 px-4">
                      <span className="font-mono text-xs bg-black text-neutral-300 px-2.5 py-0.5 rounded border border-neutral-800">
                        {t.branch}
                      </span>
                    </td>
                    <td className="py-3.5 px-4">
                      <span className={`px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold ${t.priority === "Urgent" ? "bg-red-950/40 text-red-400 border border-red-800/40" : "bg-neutral-800 text-neutral-300 border border-neutral-700"}`}>
                        {t.priority}
                      </span>
                    </td>
                    <td className="py-3.5 px-4 font-mono text-xs sm:text-sm text-neutral-300">{t.assignee.name}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
