import React from "react";
import { Link, router } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  FolderKanban,
  CheckCircle2,
  Users,
  Filter,
  CheckSquare,
  Square,
  Flame,
  ArrowUp,
  Minus,
  ArrowDown,
  Calendar,
  Clock,
  AlertTriangle,
  ArrowRight,
  Plus,
  Rocket,
  Lock,
  Building2,
  Layers,
  Sparkles,
  ChevronLeft,
  ChevronRight
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Progress } from "@/Components/ui/progress";

// Fail-safe route helper preventing "route is not defined" error
const getRoute = (name, params) => {
  if (typeof window !== "undefined" && typeof window.route === "function") {
    try {
      return window.route(name, params);
    } catch (e) {}
  }
  if (typeof route === "function") {
    try {
      return route(name, params);
    } catch (e) {}
  }

  switch (name) {
    case "dashboard":
      return "/dashboard";
    case "dashboard.org":
      return `/dashboard/${params}`;
    case "tasks.index":
      return "/tasks";
    case "tasks.show":
      return `/tasks/${params}`;
    case "tasks.toggle":
      return `/tasks/${params}/toggle`;
    case "projects.index":
      return "/projects";
    case "projects.show":
      return `/projects/${params}`;
    default:
      return "/dashboard";
  }
};

export default function NewDashboard({
  user,
  current_workspace_name = "All Workspaces",
  company = null,
  user_companies = [],
  stats = {},
  counts = {},
  active_task_filter = "today_past",
  per_page = 5,
  today_tasks_list = [],
  pagination_meta = null,
  projects_detail = [],
  team_members_list = [],
  initial_tasks = [],
  projects = [],
}) {
  // Fallbacks if data is passed via alternative prop names
  const totalProjectsCount = stats.total_projects ?? projects_detail.length ?? 0;
  const completedTasksCount = stats.completed_tasks ?? 0;
  const totalTasksCount = stats.total_tasks ?? 0;
  const taskPercentage = totalTasksCount > 0 ? Math.round((completedTasksCount / totalTasksCount) * 100) : 0;
  const teamMembersCount = stats.team_members ?? team_members_list.length ?? 0;

  const todayPastCount = counts.todayPastCount ?? stats.today_past_tasks ?? 0;
  const todayCount = counts.todayCount ?? stats.today_tasks ?? 0;
  const overdueCount = counts.overdueCount ?? stats.overdue_tasks ?? 0;
  const allPendingCount = counts.allPendingCount ?? stats.all_pending_tasks ?? 0;

  // Use today_tasks_list or fallback to initial_tasks formatted for display
  const tasksToDisplay = today_tasks_list.length > 0
    ? today_tasks_list
    : (initial_tasks || []).map((t, idx) => ({
        id: t.id || idx + 1,
        display_id: t.id ? (String(t.id).startsWith("WH-") ? t.id : `WH-${String(t.id).padStart(3, "0")}`) : `WH-${idx + 1}`,
        title: t.title || "Untitled Task",
        priority: t.priority === "Urgent" ? 4 : t.priority === "High" ? 3 : t.priority === "Medium" ? 2 : 1,
        priority_name: t.priority || "Low",
        status: t.completed ? 3 : 1,
        completed: !!t.completed,
        due_date: t.due_date || null,
        is_overdue: !!t.is_overdue,
        is_due_today: !!t.is_due_today,
        project: typeof t.project === "string" ? { name: t.project, theme: "#3b82f6" } : t.project,
        external_source: false,
      }));

  const projectsList = projects_detail.length > 0 ? projects_detail : (projects || []);

  const handleFilterChange = (filter) => {
    const routeName = company ? getRoute("dashboard.org", company.id) : getRoute("dashboard");
    router.get(routeName, { task_filter: filter, per_page }, { preserveState: true, preserveScroll: true });
  };

  const handlePerPageChange = (newPerPage) => {
    const routeName = company ? getRoute("dashboard.org", company.id) : getRoute("dashboard");
    router.get(routeName, { task_filter: active_task_filter, per_page: newPerPage }, { preserveState: true, preserveScroll: true });
  };

  const handleToggleTask = (taskId) => {
    router.patch(getRoute("tasks.toggle", taskId), {}, { preserveScroll: true });
  };

  return (
    <DashboardLayout activeItem="Dashboard">
      <div className="space-y-6">
        {/* Page Heading & Workspace Name */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-zinc-800/80">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-zinc-100 flex items-center gap-2">
              Dashboard
            </h1>
            <p className="text-xs text-zinc-400 mt-1">Overview of your workspaces, task progress, and team assignments</p>
          </div>
          <div className="flex items-center gap-2 bg-zinc-900/80 border border-zinc-800 px-3 py-1.5 rounded-full text-xs font-semibold text-zinc-200 shadow-sm backdrop-blur-sm">
            <Layers className="w-4 h-4 text-emerald-400" />
            <span>Workspace:</span>
            <span className="text-emerald-400 font-bold">{current_workspace_name}</span>
          </div>
        </div>

        {/* Workspace Filter Selector */}
        {user_companies && user_companies.length > 0 && (
          <div className="flex flex-wrap items-center gap-2 bg-zinc-900/40 p-2.5 rounded-xl border border-zinc-800/60 backdrop-blur-sm">
            <span className="text-xs font-semibold text-zinc-400 uppercase tracking-wider flex items-center gap-1.5 mr-1">
              <Filter className="w-3.5 h-3.5 text-emerald-400" /> Filter Workspace:
            </span>
            <Link
              href={getRoute("dashboard")}
              className={`px-3 py-1 text-xs font-medium rounded-lg transition-all ${
                !company
                  ? "bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-sm font-semibold"
                  : "bg-zinc-900 hover:bg-zinc-800 text-zinc-300 border border-zinc-800"
              }`}
            >
              All Workspaces
            </Link>
            {user_companies.map((c) => (
              <Link
                key={c.id}
                href={getRoute("dashboard.org", c.id)}
                className={`px-3 py-1 text-xs font-medium rounded-lg transition-all ${
                  company && company.id === c.id
                    ? "bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-sm font-semibold"
                    : "bg-zinc-900 hover:bg-zinc-800 text-zinc-300 border border-zinc-800"
                }`}
              >
                {c.name}
              </Link>
            ))}
          </div>
        )}

        {/* Top 3 Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {/* Projects Card */}
          <Card className="bg-zinc-900/80 border-zinc-800 text-zinc-100 shadow-lg backdrop-blur-sm border-l-4 border-l-emerald-500">
            <CardContent className="p-5">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-xs font-bold uppercase tracking-wider text-emerald-400">Projects</p>
                  <h3 className="text-2xl font-extrabold text-zinc-100 mt-1">{totalProjectsCount}</h3>
                </div>
                <div className="p-3 bg-emerald-500/10 rounded-xl border border-emerald-500/20 text-emerald-400">
                  <FolderKanban className="w-6 h-6" />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Tasks Card */}
          <Card className="bg-zinc-900/80 border-zinc-800 text-zinc-100 shadow-lg backdrop-blur-sm border-l-4 border-l-cyan-500">
            <CardContent className="p-5">
              <div className="flex items-center justify-between mb-2">
                <div>
                  <p className="text-xs font-bold uppercase tracking-wider text-cyan-400">
                    Tasks ({completedTasksCount}/{totalTasksCount})
                  </p>
                  <h3 className="text-2xl font-extrabold text-zinc-100 mt-1">{taskPercentage}%</h3>
                </div>
                <div className="p-3 bg-cyan-500/10 rounded-xl border border-cyan-500/20 text-cyan-400">
                  <CheckCircle2 className="w-6 h-6" />
                </div>
              </div>
              <div className="w-full bg-zinc-800 rounded-full h-2 overflow-hidden mt-3">
                <div
                  className="bg-gradient-to-r from-cyan-500 to-emerald-400 h-full rounded-full transition-all duration-500"
                  style={{ width: `${taskPercentage}%` }}
                />
              </div>
            </CardContent>
          </Card>

          {/* Team Members Card */}
          <Card className="bg-zinc-900/80 border-zinc-800 text-zinc-100 shadow-lg backdrop-blur-sm border-l-4 border-l-amber-500">
            <CardContent className="p-5">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-xs font-bold uppercase tracking-wider text-amber-400">Team Members</p>
                  <h3 className="text-2xl font-extrabold text-zinc-100 mt-1">{teamMembersCount}</h3>
                </div>
                <div className="p-3 bg-amber-500/10 rounded-xl border border-amber-500/20 text-amber-400">
                  <Users className="w-6 h-6" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Main Content Grid: 2/3 Left (Tasks) & 1/3 Right (Projects Progress & Team) */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left 2/3 Column: My Tasks */}
          <div className="lg:col-span-2 space-y-6">
            <Card className="bg-zinc-900/80 border-zinc-800 text-zinc-100 shadow-xl backdrop-blur-sm overflow-hidden border-l-4 border-l-emerald-500">
              {/* Card Header with Filter Pills and Per Page Selector */}
              <div className="p-4 border-b border-zinc-800/80 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-zinc-950/40">
                <div>
                  <h2 className="text-base font-bold text-zinc-100 flex items-center gap-2">
                    <CheckSquare className="w-4 h-4 text-emerald-400" /> My Tasks (Assigned to Me)
                  </h2>
                  <p className="text-xs text-zinc-400 mt-0.5">Priority-wise (Urgent → Low)</p>
                </div>

                <div className="flex flex-wrap items-center gap-2">
                  {/* Per Page Select */}
                  <div className="flex items-center gap-1.5 text-xs text-zinc-400 mr-2">
                    <span>Show:</span>
                    <select
                      value={per_page}
                      onChange={(e) => handlePerPageChange(Number(e.target.value))}
                      className="bg-zinc-900 border border-zinc-700 text-zinc-200 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-emerald-500"
                    >
                      <option value={5}>5</option>
                      <option value={10}>10</option>
                      <option value={25}>25</option>
                      <option value={50}>50</option>
                    </select>
                  </div>

                  {/* Filter Pills */}
                  <div className="flex flex-wrap gap-1">
                    <button
                      onClick={() => handleFilterChange("today_past")}
                      className={`px-2.5 py-1 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5 ${
                        active_task_filter === "today_past"
                          ? "bg-emerald-500/20 text-emerald-300 border border-emerald-500/40"
                          : "bg-zinc-800/60 hover:bg-zinc-800 text-zinc-400 border border-zinc-800"
                      }`}
                    >
                      Today & Past
                      <Badge className="bg-zinc-800 text-zinc-300 text-[10px] px-1.5 py-0 h-4 min-w-4 flex items-center justify-center">
                        {todayPastCount}
                      </Badge>
                    </button>

                    <button
                      onClick={() => handleFilterChange("due_today")}
                      className={`px-2.5 py-1 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5 ${
                        active_task_filter === "due_today"
                          ? "bg-cyan-500/20 text-cyan-300 border border-cyan-500/40"
                          : "bg-zinc-800/60 hover:bg-zinc-800 text-zinc-400 border border-zinc-800"
                      }`}
                    >
                      Due Today
                      <Badge className="bg-zinc-800 text-zinc-300 text-[10px] px-1.5 py-0 h-4 min-w-4 flex items-center justify-center">
                        {todayCount}
                      </Badge>
                    </button>

                    <button
                      onClick={() => handleFilterChange("overdue")}
                      className={`px-2.5 py-1 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5 ${
                        active_task_filter === "overdue"
                          ? "bg-rose-500/20 text-rose-300 border border-rose-500/40"
                          : "bg-zinc-800/60 hover:bg-zinc-800 text-zinc-400 border border-zinc-800"
                      }`}
                    >
                      Overdue
                      <Badge className="bg-rose-500/30 text-rose-300 text-[10px] px-1.5 py-0 h-4 min-w-4 flex items-center justify-center">
                        {overdueCount}
                      </Badge>
                    </button>

                    <button
                      onClick={() => handleFilterChange("all_pending")}
                      className={`px-2.5 py-1 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5 ${
                        active_task_filter === "all_pending"
                          ? "bg-emerald-500/20 text-emerald-300 border border-emerald-500/40"
                          : "bg-zinc-800/60 hover:bg-zinc-800 text-zinc-400 border border-zinc-800"
                      }`}
                    >
                      All Pending
                      <Badge className="bg-zinc-800 text-zinc-300 text-[10px] px-1.5 py-0 h-4 min-w-4 flex items-center justify-center">
                        {allPendingCount}
                      </Badge>
                    </button>
                  </div>
                </div>
              </div>

              {/* Tasks Table */}
              <CardContent className="p-0">
                {tasksToDisplay.length === 0 ? (
                  <div className="text-center py-12 px-4">
                    <CheckCircle2 className="w-12 h-12 text-emerald-400 mx-auto mb-3 opacity-80" />
                    <h3 className="text-base font-bold text-zinc-200">All caught up!</h3>
                    <p className="text-xs text-zinc-400 mt-1 max-w-sm mx-auto">
                      No pending tasks found for this filter. Great job completing your work!
                    </p>
                    <Link
                      href={getRoute("tasks.index")}
                      className="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs rounded-lg transition-all shadow-md"
                    >
                      <Plus className="w-4 h-4" /> View All Tasks / Create Task
                    </Link>
                  </div>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs text-zinc-300">
                      <thead className="bg-zinc-950/60 text-zinc-400 font-semibold uppercase tracking-wider border-b border-zinc-800/80">
                        <tr>
                          <th className="py-3 px-4 text-center w-12">Done</th>
                          <th className="py-3 px-4 w-24">Priority</th>
                          <th className="py-3 px-4">Task Title</th>
                          <th className="py-3 px-4">Project</th>
                          <th className="py-3 px-4">Due Date</th>
                          <th className="py-3 px-4 text-center w-16">Action</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-zinc-800/50">
                        {tasksToDisplay.map((task) => {
                          const priorityNum = Number(task.priority);
                          return (
                            <tr
                              key={task.id}
                              className={`hover:bg-zinc-800/40 transition-colors ${
                                priorityNum === 4 ? "bg-rose-950/10" : ""
                              }`}
                            >
                              {/* Done Checkbox */}
                              <td className="py-3.5 px-4 text-center">
                                <button
                                  onClick={() => handleToggleTask(task.id)}
                                  className="text-zinc-500 hover:text-emerald-400 transition-colors"
                                  title="Mark as Completed"
                                >
                                  {task.completed ? (
                                    <CheckSquare className="w-4 h-4 text-emerald-400" />
                                  ) : (
                                    <Square className="w-4 h-4" />
                                  )}
                                </button>
                              </td>

                              {/* Priority Badge */}
                              <td className="py-3.5 px-4">
                                {priorityNum === 4 ? (
                                  <Badge className="bg-rose-500/20 text-rose-300 border border-rose-500/40 px-2 py-0.5 text-[11px] font-bold flex items-center gap-1 w-fit">
                                    <Flame className="w-3 h-3 text-rose-400 fill-rose-400" /> Urgent
                                  </Badge>
                                ) : priorityNum === 3 ? (
                                  <Badge className="bg-amber-500/20 text-amber-300 border border-amber-500/40 px-2 py-0.5 text-[11px] font-bold flex items-center gap-1 w-fit">
                                    <ArrowUp className="w-3 h-3 text-amber-400" /> High
                                  </Badge>
                                ) : priorityNum === 2 ? (
                                  <Badge className="bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 px-2 py-0.5 text-[11px] font-bold flex items-center gap-1 w-fit">
                                    <Minus className="w-3 h-3 text-cyan-400" /> Medium
                                  </Badge>
                                ) : (
                                  <Badge className="bg-zinc-800 text-zinc-400 border border-zinc-700 px-2 py-0.5 text-[11px] flex items-center gap-1 w-fit">
                                    <ArrowDown className="w-3 h-3" /> Low
                                  </Badge>
                                )}
                              </td>

                              {/* Title */}
                              <td className="py-3.5 px-4 font-semibold text-zinc-100">
                                <div className="flex items-center gap-2">
                                  <Link
                                    href={getRoute("tasks.show", task.id)}
                                    className="hover:text-emerald-400 transition-colors line-clamp-1"
                                  >
                                    {task.title}
                                  </Link>
                                  {task.external_source && (
                                    <Badge className="bg-zinc-800 text-zinc-300 text-[10px] px-1 py-0 border border-zinc-700">
                                      API
                                    </Badge>
                                  )}
                                </div>
                              </td>

                              {/* Project */}
                              <td className="py-3.5 px-4">
                                {task.project ? (
                                  <Link
                                    href={getRoute("projects.show", typeof task.project === "object" ? task.project.id : "#")}
                                    className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-medium text-white shadow-sm"
                                    style={{
                                      backgroundColor: typeof task.project === "object" && task.project.theme ? task.project.theme : "#3b82f6",
                                    }}
                                  >
                                    <FolderKanban className="w-3 h-3" />
                                    {typeof task.project === "object" ? task.project.name : task.project}
                                  </Link>
                                ) : (
                                  <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] bg-zinc-800 text-zinc-400 border border-zinc-700">
                                    <Lock className="w-3 h-3" /> Personal
                                  </span>
                                )}
                              </td>

                              {/* Due Date */}
                              <td className="py-3.5 px-4">
                                {task.is_overdue ? (
                                  <Badge className="bg-rose-500/20 text-rose-300 border border-rose-500/40 px-2 py-0.5 text-[11px] flex items-center gap-1 w-fit">
                                    <AlertTriangle className="w-3 h-3 text-rose-400" /> Overdue ({task.due_date})
                                  </Badge>
                                ) : task.is_due_today ? (
                                  <Badge className="bg-amber-500/20 text-amber-300 border border-amber-500/40 px-2 py-0.5 text-[11px] flex items-center gap-1 w-fit">
                                    <Clock className="w-3 h-3 text-amber-400" /> Due Today
                                  </Badge>
                                ) : task.due_date ? (
                                  <span className="inline-flex items-center gap-1 text-zinc-400 text-[11px]">
                                    <Calendar className="w-3 h-3 text-emerald-400" /> {task.due_date}
                                  </span>
                                ) : (
                                  <span className="text-zinc-500 text-[11px] italic">No due date</span>
                                )}
                              </td>

                              {/* Action Link */}
                              <td className="py-3.5 px-4 text-center">
                                <Link
                                  href={getRoute("tasks.show", task.id)}
                                  className="inline-flex items-center justify-center w-7 h-7 bg-zinc-800 hover:bg-emerald-600/20 hover:text-emerald-400 text-zinc-400 rounded-lg border border-zinc-700 transition-all"
                                  title="View Task Details"
                                >
                                  <ArrowRight className="w-3.5 h-3.5" />
                                </Link>
                              </td>
                            </tr>
                          );
                        })}
                      </tbody>
                    </table>
                  </div>
                )}
              </CardContent>

              {/* Pagination Controls */}
              {pagination_meta && pagination_meta.last_page > 1 && (
                <div className="p-3 bg-zinc-950/60 border-t border-zinc-800/80 flex items-center justify-between text-xs text-zinc-400">
                  <span>
                    Showing {((pagination_meta.current_page - 1) * pagination_meta.per_page) + 1} to{" "}
                    {Math.min(pagination_meta.current_page * pagination_meta.per_page, pagination_meta.total)} of{" "}
                    {pagination_meta.total} tasks
                  </span>
                  <div className="flex items-center gap-1">
                    <button
                      disabled={pagination_meta.current_page === 1}
                      onClick={() => {
                        const routeName = company ? getRoute("dashboard.org", company.id) : getRoute("dashboard");
                        router.get(routeName, { task_filter: active_task_filter, per_page, page: pagination_meta.current_page - 1 });
                      }}
                      className="p-1 rounded bg-zinc-900 border border-zinc-800 disabled:opacity-40 hover:bg-zinc-800"
                    >
                      <ChevronLeft className="w-4 h-4" />
                    </button>
                    <span className="px-2 font-medium text-zinc-200">
                      {pagination_meta.current_page} / {pagination_meta.last_page}
                    </span>
                    <button
                      disabled={pagination_meta.current_page === pagination_meta.last_page}
                      onClick={() => {
                        const routeName = company ? getRoute("dashboard.org", company.id) : getRoute("dashboard");
                        router.get(routeName, { task_filter: active_task_filter, per_page, page: pagination_meta.current_page + 1 });
                      }}
                      className="p-1 rounded bg-zinc-900 border border-zinc-800 disabled:opacity-40 hover:bg-zinc-800"
                    >
                      <ChevronRight className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              )}
            </Card>

            {/* Quick Guide Card (Shown if user has 0 projects) */}
            {totalProjectsCount === 0 && (
              <Card className="bg-zinc-900/80 border-zinc-800 text-zinc-100 shadow-xl backdrop-blur-sm">
                <CardHeader>
                  <CardTitle className="text-base font-bold text-zinc-100 flex items-center gap-2">
                    <Rocket className="w-4 h-4 text-emerald-400" /> Get Started with WorkHub
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3 text-xs text-zinc-300">
                  <p>Welcome to WorkHub! Streamline your projects, task classifications, and documentation.</p>
                  <ul className="space-y-2 list-disc pl-4 text-zinc-400">
                    <li><strong>Context Switching:</strong> Toggle between Personal Space and Organizations using the filter bar.</li>
                    <li><strong>Projects & Tasks:</strong> Create projects and classify tasks as Bug, Feature, Task, or Improvement.</li>
                    <li><strong>Documentation:</strong> Keep rich notes under projects, tasks, or personal space.</li>
                  </ul>
                  <Link
                    href={getRoute("projects.index")}
                    className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-medium rounded-lg transition-all mt-2"
                  >
                    <FolderKanban className="w-4 h-4" /> Go to Projects
                  </Link>
                </CardContent>
              </Card>
            )}
          </div>

          {/* Right 1/3 Column: Projects Progress & Team Members */}
          <div className="space-y-6">
            {/* Projects Progress Card */}
            <Card className="bg-zinc-900/80 border-zinc-800 text-zinc-100 shadow-xl backdrop-blur-sm">
              <CardHeader className="p-4 border-b border-zinc-800/80 bg-zinc-950/40">
                <CardTitle className="text-base font-bold text-zinc-100 flex items-center gap-2">
                  <FolderKanban className="w-4 h-4 text-emerald-400" /> Projects Progress
                </CardTitle>
              </CardHeader>
              <CardContent className="p-4 space-y-4">
                {projectsList.length === 0 ? (
                  <p className="text-xs text-zinc-400 text-center py-4">No projects found. Create one to get started.</p>
                ) : (
                  projectsList.map((p) => {
                    const pTotal = p.total_tasks ?? 0;
                    const pCompleted = p.completed_tasks ?? 0;
                    const pct = p.percentage ?? (pTotal > 0 ? Math.round((pCompleted / pTotal) * 100) : 0);

                    return (
                      <div key={p.id} className="space-y-1.5">
                        <div className="flex items-center justify-between text-xs">
                          <Link
                            href={getRoute("projects.show", p.id)}
                            className="font-semibold text-zinc-200 hover:text-emerald-400 transition-colors line-clamp-1"
                          >
                            {p.name}
                          </Link>
                          <span className="text-zinc-400 font-mono text-[11px]">
                            {pct === 100 ? "Complete!" : `${pct}% (${pCompleted}/${pTotal})`}
                          </span>
                        </div>
                        <div className="w-full bg-zinc-800/80 rounded-full h-2 overflow-hidden">
                          <div
                            className={`h-full rounded-full transition-all duration-500 ${
                              pct < 30
                                ? "bg-rose-500"
                                : pct < 70
                                ? "bg-amber-500"
                                : pct < 100
                                ? "bg-cyan-500"
                                : "bg-emerald-400"
                            }`}
                            style={{
                              width: `${pct}%`,
                              backgroundColor: pct < 100 && p.theme ? p.theme : undefined,
                            }}
                          />
                        </div>
                      </div>
                    );
                  })
                )}
              </CardContent>
            </Card>

            {/* Team Members Card */}
            <Card className="bg-zinc-900/80 border-zinc-800 text-zinc-100 shadow-xl backdrop-blur-sm">
              <CardHeader className="p-4 border-b border-zinc-800/80 bg-zinc-950/40">
                <CardTitle className="text-base font-bold text-zinc-100 flex items-center gap-2">
                  <Users className="w-4 h-4 text-emerald-400" /> Team Members
                </CardTitle>
              </CardHeader>
              <CardContent className="p-4">
                {team_members_list.length === 0 ? (
                  <p className="text-xs text-zinc-400 text-center py-4">No team members found.</p>
                ) : (
                  <div className="divide-y divide-zinc-800/60">
                    {team_members_list.map((m, idx) => (
                      <div key={m.id || idx} className="py-3 flex items-center justify-between gap-2">
                        <div className="flex items-center gap-3 min-w-0">
                          <div className="w-8 h-8 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
                            {m.name ? m.name.substring(0, 2).toUpperCase() : "TM"}
                          </div>
                          <div className="min-w-0">
                            <p className="text-xs font-semibold text-zinc-200 truncate">{m.name}</p>
                            <p className="text-[11px] text-zinc-400 truncate flex items-center gap-1">
                              {m.email}
                              {m.company_name && (
                                <span className="bg-zinc-800 text-zinc-400 border border-zinc-700 rounded px-1 text-[10px]">
                                  {m.company_name}
                                </span>
                              )}
                            </p>
                          </div>
                        </div>
                        <Badge
                          className={`text-[10px] px-2 py-0.5 shrink-0 ${
                            m.role === "Admin"
                              ? "bg-emerald-500/20 text-emerald-300 border border-emerald-500/40"
                              : "bg-zinc-800 text-zinc-400 border border-zinc-700"
                          }`}
                        >
                          {m.role}
                        </Badge>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}
