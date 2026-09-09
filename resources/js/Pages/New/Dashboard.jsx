import React, { useState } from "react";
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
  ChevronRight,
  BarChart3,
  TrendingUp,
  Activity as ActivityIcon,
  ExternalLink,
  Check
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
import {
  ResponsiveContainer,
  AreaChart,
  Area,
  XAxis,
  YAxis,
  Tooltip,
  CartesianGrid,
} from "recharts";

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
  chart_data = [],
  activity_stream = [],
  recent_activity = [],
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

  // Display chart data fallback if empty
  const displayChartData = (chart_data && chart_data.length > 0)
    ? chart_data
    : [
        { name: "Mon", throughput: 2, completed: 1 },
        { name: "Tue", throughput: 4, completed: 3 },
        { name: "Wed", throughput: 3, completed: 2 },
        { name: "Thu", throughput: 5, completed: 4 },
        { name: "Fri", throughput: 6, completed: 5 },
        { name: "Sat", throughput: 2, completed: 3 },
        { name: "Sun", throughput: 3, completed: 2 },
      ];

  // Activities stream
  const activities = (activity_stream && activity_stream.length > 0)
    ? activity_stream
    : (recent_activity && recent_activity.length > 0)
    ? recent_activity
    : [];

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

  // Formatted date and contextual greeting
  const todayDateFormatted = new Date().toLocaleDateString("en-US", {
    weekday: "long",
    month: "short",
    day: "numeric",
    year: "numeric",
  });

  const currentHour = new Date().getHours();
  const greeting = currentHour < 12 ? "Good morning" : currentHour < 18 ? "Good afternoon" : "Good evening";

  return (
    <DashboardLayout title="Dashboard" activeItem="dashboard">
      <div className="space-y-6">
        {/* Executive Hero Greeting Bar */}
        <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-zinc-900 via-zinc-900/95 to-zinc-950 border border-zinc-800/90 p-5 sm:p-6 shadow-2xl backdrop-blur-xl">
          {/* Subtle ambient blur glow in corners */}
          <div className="absolute -top-16 -right-16 w-60 h-60 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" />
          <div className="absolute -bottom-16 -left-16 w-60 h-60 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" />

          <div className="relative flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div className="space-y-1.5 min-w-0">
              <div className="flex flex-wrap items-center gap-2">
                <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                  <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                  {current_workspace_name}
                </span>
                <span className="text-xs text-zinc-400 font-mono flex items-center gap-1.5">
                  <Calendar className="w-3.5 h-3.5 text-zinc-500" />
                  {todayDateFormatted}
                </span>
              </div>

              <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                {greeting},{" "}
                <span className="bg-gradient-to-r from-zinc-100 via-zinc-200 to-zinc-400 bg-clip-text text-transparent truncate">
                  {user?.name ? user.name.split(" ")[0] : "Commander"}
                </span>
                <Sparkles className="w-5 h-5 text-emerald-400 shrink-0" />
              </h1>

              <p className="text-xs sm:text-sm text-zinc-400 font-sans max-w-2xl leading-relaxed">
                Overview of current velocity and assignments. You have{" "}
                <strong className="text-emerald-400 font-mono">{allPendingCount} pending tasks</strong>
                {overdueCount > 0 ? (
                  <>
                    {" "}and{" "}
                    <span className="text-rose-400 font-semibold font-mono underline decoration-rose-500/50 underline-offset-2">
                      {overdueCount} overdue
                    </span>
                  </>
                ) : (
                  <> and <span className="text-emerald-300 font-medium">zero overdue items</span></>
                )}
                .
              </p>
            </div>

            {/* Action Buttons */}
            <div className="flex flex-wrap items-center gap-2.5 shrink-0">
              <Link
                href={getRoute("tasks.index")}
                className="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-500 hover:bg-emerald-400 text-zinc-950 font-bold text-xs rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95 cursor-pointer"
              >
                <Plus className="w-4 h-4" />
                New Task
              </Link>
              <Link
                href={getRoute("projects.index")}
                className="inline-flex items-center gap-1.5 px-3.5 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 hover:text-white font-medium text-xs rounded-xl border border-zinc-700/80 transition-all shadow-sm active:scale-95 cursor-pointer"
              >
                <FolderKanban className="w-3.5 h-3.5 text-zinc-400" />
                Projects
              </Link>
              <Link
                href="/analytics"
                className="inline-flex items-center gap-1.5 px-3 py-2 bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 font-medium text-xs rounded-xl border border-zinc-800 transition-all cursor-pointer"
              >
                <BarChart3 className="w-3.5 h-3.5 text-zinc-400" />
                Analytics
              </Link>
            </div>
          </div>
        </div>

        {/* 4-KPI Metric Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Card 1: Total Projects */}
          <Card className="bg-zinc-900/80 border-zinc-800/90 text-zinc-100 shadow-xl backdrop-blur-xl relative overflow-hidden group hover:border-zinc-700 transition-all">
            <div className="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors" />
            <CardContent className="p-5 space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-mono font-bold uppercase tracking-wider text-zinc-400">
                  Total Projects
                </span>
                <div className="p-2.5 bg-emerald-500/10 rounded-xl border border-emerald-500/20 text-emerald-400">
                  <FolderKanban className="w-4 h-4" />
                </div>
              </div>
              <div className="space-y-1">
                <div className="flex items-baseline gap-2">
                  <span className="text-3xl font-extrabold text-white font-sans tracking-tight">
                    {totalProjectsCount}
                  </span>
                  <span className="text-xs text-emerald-400 font-mono font-medium">Active</span>
                </div>
                <p className="text-[11px] text-zinc-500">Tracked in this workspace</p>
              </div>
            </CardContent>
          </Card>

          {/* Card 2: Task Completion */}
          <Card className="bg-zinc-900/80 border-zinc-800/90 text-zinc-100 shadow-xl backdrop-blur-xl relative overflow-hidden group hover:border-zinc-700 transition-all">
            <div className="absolute top-0 right-0 w-24 h-24 bg-cyan-500/5 rounded-full blur-2xl group-hover:bg-cyan-500/10 transition-colors" />
            <CardContent className="p-5 space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-mono font-bold uppercase tracking-wider text-zinc-400">
                  Task Velocity
                </span>
                <div className="p-2.5 bg-cyan-500/10 rounded-xl border border-cyan-500/20 text-cyan-400">
                  <CheckCircle2 className="w-4 h-4" />
                </div>
              </div>
              <div className="space-y-1">
                <div className="flex items-baseline gap-2">
                  <span className="text-3xl font-extrabold text-white font-sans tracking-tight">
                    {taskPercentage}%
                  </span>
                  <span className="text-xs text-cyan-400 font-mono font-medium">
                    {completedTasksCount}/{totalTasksCount} done
                  </span>
                </div>
                <div className="w-full bg-zinc-800/80 rounded-full h-1.5 overflow-hidden mt-2">
                  <div
                    className="bg-gradient-to-r from-cyan-500 to-emerald-400 h-full rounded-full transition-all duration-700"
                    style={{ width: `${taskPercentage}%` }}
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Card 3: Attention Needed */}
          <Card className="bg-zinc-900/80 border-zinc-800/90 text-zinc-100 shadow-xl backdrop-blur-xl relative overflow-hidden group hover:border-zinc-700 transition-all">
            <div className="absolute top-0 right-0 w-24 h-24 bg-rose-500/5 rounded-full blur-2xl group-hover:bg-rose-500/10 transition-colors" />
            <CardContent className="p-5 space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-mono font-bold uppercase tracking-wider text-zinc-400">
                  Attention Needed
                </span>
                <div className="p-2.5 bg-rose-500/10 rounded-xl border border-rose-500/20 text-rose-400">
                  <AlertTriangle className="w-4 h-4" />
                </div>
              </div>
              <div className="space-y-1">
                <div className="flex items-baseline gap-2">
                  <span className={`text-3xl font-extrabold font-sans tracking-tight ${overdueCount > 0 ? "text-rose-400" : "text-white"}`}>
                    {overdueCount}
                  </span>
                  <span className="text-xs text-rose-400 font-mono font-medium">Overdue</span>
                  <span className="text-zinc-600">|</span>
                  <span className="text-xs text-amber-400 font-mono font-medium">{todayCount} Today</span>
                </div>
                <p className="text-[11px] text-zinc-500">Requires priority action</p>
              </div>
            </CardContent>
          </Card>

          {/* Card 4: Team Members */}
          <Card className="bg-zinc-900/80 border-zinc-800/90 text-zinc-100 shadow-xl backdrop-blur-xl relative overflow-hidden group hover:border-zinc-700 transition-all">
            <div className="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition-colors" />
            <CardContent className="p-5 space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-mono font-bold uppercase tracking-wider text-zinc-400">
                  Team Members
                </span>
                <div className="p-2.5 bg-indigo-500/10 rounded-xl border border-indigo-500/20 text-indigo-400">
                  <Users className="w-4 h-4" />
                </div>
              </div>
              <div className="space-y-1">
                <div className="flex items-baseline gap-2">
                  <span className="text-3xl font-extrabold text-white font-sans tracking-tight">
                    {teamMembersCount}
                  </span>
                  <span className="text-xs text-indigo-400 font-mono font-medium">Contributors</span>
                </div>
                <p className="text-[11px] text-zinc-500">Active collaborators</p>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* 7-Day Velocity & Throughput Area Chart */}
        <Card className="bg-zinc-900/80 border-zinc-800/90 text-zinc-100 shadow-xl backdrop-blur-xl overflow-hidden">
          <CardHeader className="p-5 pb-2 border-b border-zinc-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-zinc-950/40">
            <div className="space-y-0.5">
              <CardTitle className="text-sm font-bold tracking-tight text-white flex items-center gap-2">
                <BarChart3 className="w-4 h-4 text-emerald-400" />
                7-Day Task Velocity & Throughput
              </CardTitle>
              <p className="text-xs text-zinc-400 font-mono">
                Daily created tasks vs completed tasks shipped in this workspace
              </p>
            </div>
            <div className="flex items-center gap-4 text-xs font-mono">
              <div className="flex items-center gap-1.5">
                <span className="w-2.5 h-2.5 rounded-full bg-cyan-400" />
                <span className="text-zinc-400">Created</span>
              </div>
              <div className="flex items-center gap-1.5">
                <span className="w-2.5 h-2.5 rounded-full bg-emerald-400" />
                <span className="text-zinc-400">Completed</span>
              </div>
            </div>
          </CardHeader>
          <CardContent className="p-5 pt-4">
            <div className="h-44 sm:h-52 w-full">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={displayChartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                  <defs>
                    <linearGradient id="colorThroughput" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#06b6d4" stopOpacity={0.35} />
                      <stop offset="95%" stopColor="#06b6d4" stopOpacity={0.0} />
                    </linearGradient>
                    <linearGradient id="colorCompleted" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#10b981" stopOpacity={0.35} />
                      <stop offset="95%" stopColor="#10b981" stopOpacity={0.0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke="#27272a" vertical={false} />
                  <XAxis dataKey="name" stroke="#71717a" fontSize={11} tickLine={false} axisLine={false} />
                  <YAxis stroke="#71717a" fontSize={11} tickLine={false} axisLine={false} allowDecimals={false} />
                  <Tooltip
                    content={({ active, payload, label }) => {
                      if (active && payload && payload.length) {
                        return (
                          <div className="bg-zinc-950 border border-zinc-800 p-2.5 rounded-xl shadow-2xl font-mono text-xs space-y-1">
                            <p className="font-bold text-zinc-200 mb-1">{label}</p>
                            <p className="text-cyan-400 flex items-center justify-between gap-4">
                              <span>Created:</span> <span className="font-bold">{payload[0]?.value ?? 0}</span>
                            </p>
                            <p className="text-emerald-400 flex items-center justify-between gap-4">
                              <span>Completed:</span> <span className="font-bold">{payload[1]?.value ?? 0}</span>
                            </p>
                          </div>
                        );
                      }
                      return null;
                    }}
                  />
                  <Area
                    type="monotone"
                    dataKey="throughput"
                    stroke="#06b6d4"
                    strokeWidth={2}
                    fillOpacity={1}
                    fill="url(#colorThroughput)"
                    name="Created"
                  />
                  <Area
                    type="monotone"
                    dataKey="completed"
                    stroke="#10b981"
                    strokeWidth={2}
                    fillOpacity={1}
                    fill="url(#colorCompleted)"
                    name="Completed"
                  />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>

        {/* Main Content Grid: 2/3 Left (Tasks Hub) & 1/3 Right (Projects & Live Stream) */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left 2/3 Column: Linear-Grade Task Hub */}
          <div className="lg:col-span-2 space-y-6">
            <Card className="bg-zinc-900/80 border-zinc-800/90 text-zinc-100 shadow-xl backdrop-blur-xl overflow-hidden">
              {/* Task Hub Header */}
              <div className="p-4 sm:p-5 border-b border-zinc-800/80 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-zinc-950/40">
                <div className="space-y-0.5">
                  <h2 className="text-base font-bold text-zinc-100 flex items-center gap-2">
                    <CheckSquare className="w-4 h-4 text-emerald-400" />
                    My Priority Tasks
                  </h2>
                  <p className="text-xs text-zinc-400">Assigned items ordered by urgency</p>
                </div>

                <div className="flex flex-wrap items-center gap-2">
                  {/* Segmented Filter Control */}
                  <div className="flex items-center bg-zinc-950 p-1 rounded-xl border border-zinc-800 text-xs">
                    <button
                      onClick={() => handleFilterChange("today_past")}
                      className={`px-2.5 py-1 rounded-lg transition-all flex items-center gap-1.5 font-medium cursor-pointer ${
                        active_task_filter === "today_past"
                          ? "bg-zinc-800 text-white shadow-sm font-semibold"
                          : "text-zinc-400 hover:text-zinc-200"
                      }`}
                    >
                      Today & Past
                      <span className="bg-zinc-900 text-zinc-300 text-[10px] px-1.5 py-0.2 rounded-full font-mono">
                        {todayPastCount}
                      </span>
                    </button>

                    <button
                      onClick={() => handleFilterChange("due_today")}
                      className={`px-2.5 py-1 rounded-lg transition-all flex items-center gap-1.5 font-medium cursor-pointer ${
                        active_task_filter === "due_today"
                          ? "bg-zinc-800 text-white shadow-sm font-semibold"
                          : "text-zinc-400 hover:text-zinc-200"
                      }`}
                    >
                      Due Today
                      <span className="bg-zinc-900 text-amber-400 text-[10px] px-1.5 py-0.2 rounded-full font-mono">
                        {todayCount}
                      </span>
                    </button>

                    <button
                      onClick={() => handleFilterChange("overdue")}
                      className={`px-2.5 py-1 rounded-lg transition-all flex items-center gap-1.5 font-medium cursor-pointer ${
                        active_task_filter === "overdue"
                          ? "bg-zinc-800 text-white shadow-sm font-semibold"
                          : "text-zinc-400 hover:text-zinc-200"
                      }`}
                    >
                      Overdue
                      <span className="bg-rose-950/80 text-rose-400 text-[10px] px-1.5 py-0.2 rounded-full font-mono font-bold">
                        {overdueCount}
                      </span>
                    </button>

                    <button
                      onClick={() => handleFilterChange("all_pending")}
                      className={`px-2.5 py-1 rounded-lg transition-all flex items-center gap-1.5 font-medium cursor-pointer ${
                        active_task_filter === "all_pending"
                          ? "bg-zinc-800 text-white shadow-sm font-semibold"
                          : "text-zinc-400 hover:text-zinc-200"
                      }`}
                    >
                      All Pending
                      <span className="bg-zinc-900 text-zinc-300 text-[10px] px-1.5 py-0.2 rounded-full font-mono">
                        {allPendingCount}
                      </span>
                    </button>
                  </div>

                  {/* Per Page Select */}
                  <div className="flex items-center gap-1.5 text-xs text-zinc-400">
                    <select
                      value={per_page}
                      onChange={(e) => handlePerPageChange(Number(e.target.value))}
                      className="bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-emerald-500 cursor-pointer"
                    >
                      <option value={5}>5 / page</option>
                      <option value={10}>10 / page</option>
                      <option value={25}>25 / page</option>
                    </select>
                  </div>
                </div>
              </div>

              {/* Tasks List */}
              <CardContent className="p-0">
                {tasksToDisplay.length === 0 ? (
                  <div className="text-center py-14 px-4 space-y-3">
                    <div className="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto shadow-inner">
                      <CheckCircle2 className="w-6 h-6" />
                    </div>
                    <div className="space-y-1">
                      <h3 className="text-base font-bold text-zinc-200">All caught up!</h3>
                      <p className="text-xs text-zinc-400 max-w-sm mx-auto">
                        No tasks pending for this filter. Great job keeping your queue clear!
                      </p>
                    </div>
                    <Link
                      href={getRoute("tasks.index")}
                      className="inline-flex items-center gap-1.5 mt-2 px-3.5 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-semibold rounded-xl border border-zinc-700 transition-all shadow-sm"
                    >
                      <Plus className="w-3.5 h-3.5" /> View All Tasks / Create New
                    </Link>
                  </div>
                ) : (
                  <div className="divide-y divide-zinc-800/60">
                    {tasksToDisplay.map((task) => {
                      const priorityNum = Number(task.priority);
                      return (
                        <div
                          key={task.id}
                          className={`p-3.5 sm:p-4 hover:bg-zinc-800/40 transition-colors flex items-center justify-between gap-3 group ${
                            priorityNum === 4 ? "bg-rose-950/10 hover:bg-rose-950/20" : ""
                          }`}
                        >
                          <div className="flex items-center gap-3 min-w-0 flex-1">
                            {/* Done Checkbox */}
                            <button
                              onClick={() => handleToggleTask(task.id)}
                              className="text-zinc-500 hover:text-emerald-400 transition-colors shrink-0 cursor-pointer"
                              title="Toggle completed state"
                            >
                              {task.completed ? (
                                <CheckSquare className="w-4 h-4 text-emerald-400" />
                              ) : (
                                <Square className="w-4 h-4 hover:border-emerald-400" />
                              )}
                            </button>

                            {/* Priority Badge */}
                            <div className="shrink-0">
                              {priorityNum === 4 ? (
                                <Badge className="bg-rose-500/15 text-rose-300 border border-rose-500/30 px-2 py-0.5 text-[10px] font-bold flex items-center gap-1">
                                  <Flame className="w-3 h-3 text-rose-400 fill-rose-400" /> Urgent
                                </Badge>
                              ) : priorityNum === 3 ? (
                                <Badge className="bg-amber-500/15 text-amber-300 border border-amber-500/30 px-2 py-0.5 text-[10px] font-bold flex items-center gap-1">
                                  <ArrowUp className="w-3 h-3 text-amber-400" /> High
                                </Badge>
                              ) : priorityNum === 2 ? (
                                <Badge className="bg-cyan-500/15 text-cyan-300 border border-cyan-500/30 px-2 py-0.5 text-[10px] font-bold flex items-center gap-1">
                                  <Minus className="w-3 h-3 text-cyan-400" /> Med
                                </Badge>
                              ) : (
                                <Badge className="bg-zinc-800 text-zinc-400 border border-zinc-700/80 px-2 py-0.5 text-[10px] flex items-center gap-1">
                                  <ArrowDown className="w-3 h-3" /> Low
                                </Badge>
                              )}
                            </div>

                            {/* Title & Project Meta */}
                            <div className="min-w-0 flex-1 space-y-1">
                              <div className="flex items-center gap-2">
                                <Link
                                  href={getRoute("tasks.show", task.id)}
                                  className={`text-xs font-semibold text-zinc-200 hover:text-emerald-400 transition-colors truncate block ${
                                    task.completed ? "line-through text-zinc-500" : ""
                                  }`}
                                >
                                  {task.title}
                                </Link>
                                {task.external_source && (
                                  <Badge className="bg-zinc-800 text-zinc-300 text-[9px] px-1 py-0 border border-zinc-700 shrink-0">
                                    API
                                  </Badge>
                                )}
                              </div>

                              <div className="flex flex-wrap items-center gap-2 text-[11px] text-zinc-400">
                                {/* Project Tag */}
                                {task.project ? (
                                  <Link
                                    href={getRoute("projects.show", typeof task.project === "object" ? task.project.id : "#")}
                                    className="inline-flex items-center gap-1 px-2 py-0.2 rounded-md text-[10px] font-medium text-white shadow-sm"
                                    style={{
                                      backgroundColor: typeof task.project === "object" && task.project.theme ? task.project.theme : "#3b82f6",
                                    }}
                                  >
                                    <FolderKanban className="w-2.5 h-2.5" />
                                    {typeof task.project === "object" ? task.project.name : task.project}
                                  </Link>
                                ) : (
                                  <span className="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-md text-[10px] bg-zinc-800/80 text-zinc-400 border border-zinc-700/60">
                                    <Lock className="w-2.5 h-2.5" /> Personal
                                  </span>
                                )}

                                {/* Due Date Tag */}
                                {task.is_overdue ? (
                                  <span className="inline-flex items-center gap-1 text-rose-400 font-mono text-[10px]">
                                    <AlertTriangle className="w-3 h-3" /> Overdue ({task.due_date})
                                  </span>
                                ) : task.is_due_today ? (
                                  <span className="inline-flex items-center gap-1 text-amber-400 font-mono text-[10px]">
                                    <Clock className="w-3 h-3" /> Due Today
                                  </span>
                                ) : task.due_date ? (
                                  <span className="inline-flex items-center gap-1 text-zinc-400 font-mono text-[10px]">
                                    <Calendar className="w-3 h-3 text-zinc-500" /> {task.due_date}
                                  </span>
                                ) : null}
                              </div>
                            </div>
                          </div>

                          {/* Quick Action Link */}
                          <div className="shrink-0">
                            <Link
                              href={getRoute("tasks.show", task.id)}
                              className="inline-flex items-center justify-center w-7 h-7 bg-zinc-800/80 hover:bg-emerald-500/20 hover:text-emerald-400 text-zinc-400 rounded-lg border border-zinc-700/80 transition-all shadow-sm cursor-pointer"
                              title="View Details"
                            >
                              <ArrowRight className="w-3.5 h-3.5" />
                            </Link>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                )}
              </CardContent>

              {/* Pagination Controls */}
              {pagination_meta && pagination_meta.last_page > 1 && (
                <div className="p-3.5 bg-zinc-950/60 border-t border-zinc-800/80 flex items-center justify-between text-xs text-zinc-400">
                  <span className="font-mono text-[11px]">
                    Showing {((pagination_meta.current_page - 1) * pagination_meta.per_page) + 1} -{" "}
                    {Math.min(pagination_meta.current_page * pagination_meta.per_page, pagination_meta.total)} of{" "}
                    {pagination_meta.total} tasks
                  </span>
                  <div className="flex items-center gap-1.5 font-mono">
                    <button
                      disabled={pagination_meta.current_page === 1}
                      onClick={() => {
                        const routeName = company ? getRoute("dashboard.org", company.id) : getRoute("dashboard");
                        router.get(routeName, { task_filter: active_task_filter, per_page, page: pagination_meta.current_page - 1 });
                      }}
                      className="p-1.5 rounded-lg bg-zinc-900 border border-zinc-800 disabled:opacity-30 hover:bg-zinc-800 transition-colors cursor-pointer disabled:cursor-not-allowed"
                    >
                      <ChevronLeft className="w-3.5 h-3.5" />
                    </button>
                    <span className="px-2 font-semibold text-zinc-200">
                      {pagination_meta.current_page} / {pagination_meta.last_page}
                    </span>
                    <button
                      disabled={pagination_meta.current_page === pagination_meta.last_page}
                      onClick={() => {
                        const routeName = company ? getRoute("dashboard.org", company.id) : getRoute("dashboard");
                        router.get(routeName, { task_filter: active_task_filter, per_page, page: pagination_meta.current_page + 1 });
                      }}
                      className="p-1.5 rounded-lg bg-zinc-900 border border-zinc-800 disabled:opacity-30 hover:bg-zinc-800 transition-colors cursor-pointer disabled:cursor-not-allowed"
                    >
                      <ChevronRight className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              )}
            </Card>

            {/* Quick Guide Card (Shown if user has 0 projects) */}
            {totalProjectsCount === 0 && (
              <Card className="bg-zinc-900/80 border-zinc-800/90 text-zinc-100 shadow-xl backdrop-blur-xl">
                <CardHeader>
                  <CardTitle className="text-base font-bold text-zinc-100 flex items-center gap-2">
                    <Rocket className="w-4 h-4 text-emerald-400" /> Get Started with WorkHub
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3 text-xs text-zinc-300">
                  <p>Welcome to WorkHub! Streamline your projects, task classifications, and documentation.</p>
                  <ul className="space-y-2 list-disc pl-4 text-zinc-400">
                    <li><strong>Context Switching:</strong> Toggle between Personal Space and Organizations using the filter in the sidebar.</li>
                    <li><strong>Projects & Tasks:</strong> Create projects and classify tasks with priorities and due dates.</li>
                    <li><strong>Documentation:</strong> Keep rich notes under projects, tasks, or personal workspace.</li>
                  </ul>
                  <Link
                    href={getRoute("projects.index")}
                    className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-medium rounded-lg transition-all mt-2 cursor-pointer"
                  >
                    <FolderKanban className="w-4 h-4" /> Go to Projects
                  </Link>
                </CardContent>
              </Card>
            )}
          </div>

          {/* Right 1/3 Column: Projects Progress & Live Activity Stream & Team */}
          <div className="space-y-6">
            {/* Projects Progress Card */}
            <Card className="bg-zinc-900/80 border-zinc-800/90 text-zinc-100 shadow-xl backdrop-blur-xl">
              <CardHeader className="p-4 border-b border-zinc-800/80 bg-zinc-950/40 flex flex-row items-center justify-between">
                <CardTitle className="text-sm font-bold text-zinc-100 flex items-center gap-2">
                  <FolderKanban className="w-4 h-4 text-emerald-400" /> Projects Progress
                </CardTitle>
                <Link
                  href={getRoute("projects.index")}
                  className="text-[11px] text-zinc-400 hover:text-emerald-400 font-mono transition-colors"
                >
                  View all →
                </Link>
              </CardHeader>
              <CardContent className="p-4 space-y-4">
                {projectsList.length === 0 ? (
                  <p className="text-xs text-zinc-400 text-center py-4">No active projects in this workspace.</p>
                ) : (
                  projectsList.slice(0, 5).map((p) => {
                    const pTotal = p.total_tasks ?? 0;
                    const pCompleted = p.completed_tasks ?? 0;
                    const pct = p.percentage ?? (pTotal > 0 ? Math.round((pCompleted / pTotal) * 100) : 0);

                    return (
                      <div key={p.id} className="space-y-1.5 group">
                        <div className="flex items-center justify-between text-xs">
                          <Link
                            href={getRoute("projects.show", p.id)}
                            className="font-semibold text-zinc-200 hover:text-emerald-400 transition-colors line-clamp-1 flex items-center gap-1.5"
                          >
                            <span
                              className="w-2 h-2 rounded-full shrink-0"
                              style={{ backgroundColor: p.theme || "#3b82f6" }}
                            />
                            {p.name}
                          </Link>
                          <span className="text-zinc-400 font-mono text-[11px]">
                            {pct === 100 ? (
                              <span className="text-emerald-400 font-bold">100%</span>
                            ) : (
                              `${pct}% (${pCompleted}/${pTotal})`
                            )}
                          </span>
                        </div>
                        <div className="w-full bg-zinc-800/80 rounded-full h-1.5 overflow-hidden">
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

            {/* Live Workspace Activity Feed */}
            <Card className="bg-zinc-900/80 border-zinc-800/90 text-zinc-100 shadow-xl backdrop-blur-xl">
              <CardHeader className="p-4 border-b border-zinc-800/80 bg-zinc-950/40 flex flex-row items-center justify-between">
                <CardTitle className="text-sm font-bold text-zinc-100 flex items-center gap-2">
                  <ActivityIcon className="w-4 h-4 text-emerald-400" />
                  Recent Activity
                </CardTitle>
                <span className="flex items-center gap-1 text-[10px] text-emerald-400 font-mono">
                  <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping" />
                  Live
                </span>
              </CardHeader>
              <CardContent className="p-4">
                {activities.length === 0 ? (
                  <p className="text-xs text-zinc-400 text-center py-4">No recent activity recorded.</p>
                ) : (
                  <div className="space-y-3.5">
                    {activities.slice(0, 5).map((act, idx) => (
                      <div key={act.id || idx} className="flex items-start gap-2.5 text-xs">
                        <div className="w-6 h-6 rounded-lg bg-zinc-800/90 border border-zinc-700/80 flex items-center justify-center font-bold text-[10px] text-zinc-300 shrink-0 mt-0.5">
                          {act.source ? act.source.substring(0, 1).toUpperCase() : "S"}
                        </div>
                        <div className="min-w-0 flex-1 space-y-0.5">
                          <p className="text-zinc-200 leading-snug line-clamp-2">
                            <span className="font-semibold text-zinc-100">{act.source}:</span>{" "}
                            {act.event}
                          </p>
                          <span className="text-[10px] text-zinc-500 font-mono block">
                            {act.relTime || act.time}
                          </span>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Team Members Card */}
            <Card className="bg-zinc-900/80 border-zinc-800/90 text-zinc-100 shadow-xl backdrop-blur-xl">
              <CardHeader className="p-4 border-b border-zinc-800/80 bg-zinc-950/40 flex flex-row items-center justify-between">
                <CardTitle className="text-sm font-bold text-zinc-100 flex items-center gap-2">
                  <Users className="w-4 h-4 text-emerald-400" /> Team Members
                </CardTitle>
                <Link
                  href="/users"
                  className="text-[11px] text-zinc-400 hover:text-emerald-400 font-mono transition-colors"
                >
                  Manage →
                </Link>
              </CardHeader>
              <CardContent className="p-4">
                {team_members_list.length === 0 ? (
                  <p className="text-xs text-zinc-400 text-center py-4">No collaborators found.</p>
                ) : (
                  <div className="divide-y divide-zinc-800/60">
                    {team_members_list.slice(0, 5).map((m, idx) => (
                      <div key={m.id || idx} className="py-2.5 flex items-center justify-between gap-2">
                        <div className="flex items-center gap-2.5 min-w-0">
                          <Avatar className="h-7 w-7 border border-zinc-700/80 shrink-0">
                            <AvatarFallback className="bg-zinc-800 text-emerald-400 text-xs font-bold font-mono">
                              {m.name ? m.name.substring(0, 2).toUpperCase() : "TM"}
                            </AvatarFallback>
                          </Avatar>
                          <div className="min-w-0">
                            <p className="text-xs font-semibold text-zinc-200 truncate">{m.name}</p>
                            <p className="text-[10px] text-zinc-400 truncate font-mono">
                              {m.email}
                            </p>
                          </div>
                        </div>
                        <Badge
                          className={`text-[9px] px-1.5 py-0 shrink-0 font-mono ${
                            m.role === "Admin"
                              ? "bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 font-bold"
                              : "bg-zinc-800 text-zinc-400 border border-zinc-700/80"
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
