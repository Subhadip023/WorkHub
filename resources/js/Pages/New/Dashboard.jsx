import React, { useState } from "react";
import { Link } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  CheckCircle2,
  Clock,
  TrendingUp,
  FolderKanban,
  Plus,
  Sparkles,
  ArrowUpRight,
  CheckSquare,
  Square,
  Calendar,
  ChevronRight,
  Activity,
  BarChart3,
  AlertOctagon,
  GitPullRequest,
  Terminal,
  Copy,
  Edit2,
  GitBranch,
  Crown
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
import { Progress } from "@/Components/ui/progress";
import {
  ResponsiveContainer,
  AreaChart,
  Area,
  XAxis,
  YAxis,
  Tooltip,
  CartesianGrid,
} from "recharts";

export default function NewDashboard({ user, stats, recent_activity, chart_data, projects }) {
  const [taskFilter, setTaskFilter] = useState("all");
  const [copiedId, setCopiedId] = useState(null);

  // High-density issue list matching Linear/GitHub issue table format
  const [issuesList, setIssuesList] = useState([
    {
      id: "WH-042",
      title: "Inertia.js React layout hydration error on cold start",
      completed: false,
      tags: ["Bug", "Frontend"],
      project: "WorkHub",
      branch: "fix/inertia-hydration",
      priority: "Urgent",
      assignee: { name: "Alex Morgan", avatar: "AM" },
      updatedAt: "12m ago",
    },
    {
      id: "WH-039",
      title: "Rate-limiting middleware for webhook ingestion endpoints",
      completed: false,
      tags: ["API", "Backend"],
      project: "WorkHub Core",
      branch: "feat/rate-limit",
      priority: "High",
      assignee: { name: "Sarah Chen", avatar: "SC" },
      updatedAt: "45m ago",
    },
    {
      id: "WH-035",
      title: "Refactor task status filter query in TaskRepository.php",
      completed: true,
      tags: ["Database", "Backend"],
      project: "WorkHub Core",
      branch: "fix/query-filter",
      priority: "Medium",
      assignee: { name: "Michael Scott", avatar: "MS" },
      updatedAt: "2h ago",
    },
    {
      id: "WH-031",
      title: "Monochrome dark mode design tokens with zinc palette",
      completed: false,
      tags: ["UI/UX", "Design"],
      project: "shadcn/ui",
      branch: "feat/zinc-theme",
      priority: "Urgent",
      assignee: { name: "Sarah Chen", avatar: "SC" },
      updatedAt: "3h ago",
    },
    {
      id: "WH-028",
      title: "Pest feature test suite for /new/analytics Inertia props",
      completed: false,
      tags: ["QA", "Testing"],
      project: "WorkHub",
      branch: "test/inertia-props",
      priority: "Medium",
      assignee: { name: "Emma Watson", avatar: "EW" },
      updatedAt: "5h ago",
    },
    {
      id: "WH-022",
      title: "Setup Telegram webhook dispatch listener for critical alerts",
      completed: true,
      tags: ["DevOps", "Integration"],
      project: "WorkHub Core",
      branch: "main",
      priority: "Low",
      assignee: { name: "David Kim", avatar: "DK" },
      updatedAt: "1d ago",
    },
  ]);

  // Live inbound activity stream
  const [activityStream, setActivityStream] = useState([
    {
      id: 1,
      time: "10:42:15",
      source: "Telegram",
      event: "Alert #WH-042 triggered by @alexm",
      relTime: "2m ago",
    },
    {
      id: 2,
      time: "10:35:00",
      source: "Webhook",
      event: "POST /api/v1/ingest - 200 OK (14ms)",
      relTime: "9m ago",
    },
    {
      id: 3,
      time: "10:12:44",
      source: "GitHub",
      event: "PR #148 merged into main by @sarahc",
      relTime: "31m ago",
    },
    {
      id: 4,
      time: "09:50:12",
      source: "Webhook",
      event: "POST /api/v1/ingest - 200 OK (18ms)",
      relTime: "54m ago",
    },
    {
      id: 5,
      time: "09:15:30",
      source: "GitHub",
      event: "Branch fix/inertia-hydration created",
      relTime: "1h ago",
    },
  ]);

  const toggleIssueCompleted = (id) => {
    setIssuesList((prev) =>
      prev.map((item) =>
        item.id === id ? { ...item, completed: !item.completed } : item
      )
    );
  };

  const copyIssueId = (id) => {
    navigator.clipboard.writeText(id);
    setCopiedId(id);
    setTimeout(() => setCopiedId(null), 1500);
  };

  const displayChartData = chart_data || [
    { name: "Mon", throughput: 14, completed: 12 },
    { name: "Tue", throughput: 22, completed: 18 },
    { name: "Wed", throughput: 28, completed: 25 },
    { name: "Thu", throughput: 24, completed: 22 },
    { name: "Fri", throughput: 35, completed: 31 },
    { name: "Sat", throughput: 18, completed: 16 },
    { name: "Sun", throughput: 12, completed: 11 },
  ];

  const displayProjects = projects || [
    { id: 1, name: "WorkHub Mobile App", progress: 78, status: "In Progress", tag: "React Native" },
    { id: 2, name: "Inertia.js Migration", progress: 92, status: "Near Completion", tag: "Laravel + React" },
    { id: 3, name: "shadcn/ui Design System", progress: 100, status: "Completed", tag: "Tailwind CSS" },
    { id: 4, name: "Customer Portal v2", progress: 45, status: "In Progress", tag: "Next.js" },
  ];

  const headerActions = (
    <Button size="sm" className="bg-white text-black hover:bg-neutral-200 font-semibold text-sm gap-2 transition-all rounded-xl h-9 px-3.5">
      <Plus className="h-4 w-4 text-black" /> Create Task
    </Button>
  );

  return (
    <DashboardLayout title="Overview" activeItem="dashboard" actions={headerActions}>
      <div className="space-y-6">
        {/* Header Hero Banner */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-neutral-900/90 via-neutral-950/80 to-black p-6 rounded-2xl border border-neutral-800 shadow-2xl relative overflow-hidden">
          <div className="space-y-1.5 z-10">
            <div className="flex items-center gap-2.5">
              <span className="px-3 py-1 rounded-full text-xs font-mono font-semibold bg-neutral-800 text-neutral-300 border border-neutral-700">
                CLASSIC PREMIUM EDITION
              </span>
              <span className="text-xs sm:text-sm text-neutral-400 font-mono">WorkHub v2.4</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
              Welcome back, {user?.name || "Administrator"} <Crown className="h-6 w-6 text-neutral-300" />
            </h1>
            <p className="text-sm sm:text-base text-neutral-400 max-w-xl font-sans leading-relaxed">
              High-density task tracking, developer infrastructure monitoring, and sprint throughput metrics.
            </p>
          </div>

          <div className="flex items-center gap-3 z-10">
            <div className="px-4 py-2 rounded-xl bg-neutral-900 border border-neutral-800 text-sm font-mono text-neutral-300">
              Uptime: <span className="text-emerald-400 font-bold">99.98%</span>
            </div>
          </div>
        </div>

        {/* Developer Health Metric Cards Strip */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Card 1: Critical Blockers */}
          <div className="border border-neutral-800 bg-neutral-900/80 p-5 rounded-xl flex items-center justify-between shadow-lg">
            <div className="space-y-1.5">
              <span className="text-xs font-mono text-neutral-400 font-bold block uppercase tracking-wider">
                Critical Blockers
              </span>
              <div className="flex items-baseline gap-2">
                <span className="text-2xl sm:text-3xl font-bold font-mono text-white">3</span>
                <span className="text-xs sm:text-sm text-neutral-500 font-mono">Active</span>
              </div>
              <span className="text-xs text-neutral-400 font-mono block">#WH-042, #WH-031</span>
            </div>
            <div className="px-3 py-1 rounded-full bg-red-950/40 text-red-400 border border-red-800/40 text-xs font-mono font-semibold flex items-center gap-1.5">
              <AlertOctagon className="h-4 w-4" /> Urgent
            </div>
          </div>

          {/* Card 2: In-Progress Issues */}
          <div className="border border-neutral-800 bg-neutral-900/80 p-5 rounded-xl flex items-center justify-between shadow-lg">
            <div className="space-y-1.5">
              <span className="text-xs font-mono text-neutral-400 font-bold block uppercase tracking-wider">
                In-Progress Tasks
              </span>
              <div className="flex items-baseline gap-2">
                <span className="text-2xl sm:text-3xl font-bold font-mono text-white">12</span>
                <span className="text-xs sm:text-sm text-neutral-500 font-mono">Active</span>
              </div>
              <span className="text-xs text-neutral-400 font-mono block">7 high priority</span>
            </div>
            <div className="px-3 py-1 rounded-full bg-amber-950/40 text-amber-300 border border-amber-800/40 text-xs font-mono font-semibold flex items-center gap-1.5">
              <Clock className="h-4 w-4" /> Active
            </div>
          </div>

          {/* Card 3: PRs / Review Pending */}
          <div className="border border-neutral-800 bg-neutral-900/80 p-5 rounded-xl flex items-center justify-between shadow-lg">
            <div className="space-y-1.5">
              <span className="text-xs font-mono text-neutral-400 font-bold block uppercase tracking-wider">
                PRs / Code Reviews
              </span>
              <div className="flex items-baseline gap-2">
                <span className="text-2xl sm:text-3xl font-bold font-mono text-white">4</span>
                <span className="text-xs sm:text-sm text-neutral-500 font-mono">Pending</span>
              </div>
              <span className="text-xs text-neutral-400 font-mono block">2 core repos</span>
            </div>
            <div className="px-3 py-1 rounded-full bg-neutral-800 text-neutral-300 border border-neutral-700 text-xs font-mono font-semibold flex items-center gap-1.5">
              <GitPullRequest className="h-4 w-4" /> Review
            </div>
          </div>

          {/* Card 4: API & Webhook Ingest */}
          <div className="border border-neutral-800 bg-neutral-900/80 p-5 rounded-xl flex items-center justify-between shadow-lg">
            <div className="space-y-1.5">
              <span className="text-xs font-mono text-neutral-400 font-bold block uppercase tracking-wider">
                API Ingest Status
              </span>
              <div className="flex items-baseline gap-2">
                <span className="text-xl sm:text-2xl font-bold font-mono text-white">200 OK</span>
              </div>
              <span className="text-xs text-neutral-400 font-mono block">Zero dropped payloads</span>
            </div>
            <div className="px-3 py-1 rounded-full bg-emerald-950/40 text-emerald-400 border border-emerald-800/40 text-xs font-mono font-semibold flex items-center gap-1.5">
              <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span> Healthy
            </div>
          </div>
        </div>

        {/* Main Grid Section */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
          {/* Left Column (8 cols): Dense Issue Table & Velocity Chart */}
          <div className="lg:col-span-8 space-y-6">
            {/* Issue List Table Container */}
            <div className="border border-neutral-800 bg-neutral-900/90 rounded-xl overflow-hidden shadow-2xl">
              <div className="p-4 bg-neutral-950/80 border-b border-neutral-800 flex items-center justify-between flex-wrap gap-2">
                <div className="flex items-center gap-3">
                  <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-white">
                    Tasks & Deliverables
                  </h3>
                  <div className="flex items-center gap-1 bg-black p-1 rounded-lg border border-neutral-800 text-xs font-mono">
                    <button
                      onClick={() => setTaskFilter("all")}
                      className={`px-3 py-1 rounded-md transition-all ${
                        taskFilter === "all" ? "bg-neutral-800 text-white font-semibold" : "text-neutral-400 hover:text-neutral-200"
                      }`}
                    >
                      All ({issuesList.length})
                    </button>
                    <button
                      onClick={() => setTaskFilter("pending")}
                      className={`px-3 py-1 rounded-md transition-all ${
                        taskFilter === "pending" ? "bg-neutral-800 text-white font-semibold" : "text-neutral-400 hover:text-neutral-200"
                      }`}
                    >
                      Open ({issuesList.filter((i) => !i.completed).length})
                    </button>
                    <button
                      onClick={() => setTaskFilter("completed")}
                      className={`px-3 py-1 rounded-md transition-all ${
                        taskFilter === "completed" ? "bg-neutral-800 text-white font-semibold" : "text-neutral-400 hover:text-neutral-200"
                      }`}
                    >
                      Closed ({issuesList.filter((i) => i.completed).length})
                    </button>
                  </div>
                </div>

                <span className="text-xs font-mono text-neutral-400">
                  Linear-dense view
                </span>
              </div>

              {/* Table */}
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs sm:text-sm">
                  <thead>
                    <tr className="border-b border-neutral-800 bg-black/60 text-neutral-400 font-mono text-xs uppercase tracking-wider">
                      <th className="py-3.5 px-4 w-8"></th>
                      <th className="py-3.5 px-4">Task ID & Title</th>
                      <th className="py-3.5 px-4">Tags</th>
                      <th className="py-3.5 px-4">Project</th>
                      <th className="py-3.5 px-4">Branch</th>
                      <th className="py-3.5 px-4">Priority</th>
                      <th className="py-3.5 px-4">Assignee</th>
                      <th className="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-neutral-800/60 font-sans">
                    {issuesList
                      .filter((item) => {
                        if (taskFilter === "pending") return !item.completed;
                        if (taskFilter === "completed") return item.completed;
                        return true;
                      })
                      .map((item) => (
                        <tr
                          key={item.id}
                          className="hover:bg-neutral-800/50 transition-colors duration-150 group"
                        >
                          {/* Col 1: Checkbox */}
                          <td className="py-3.5 px-4">
                            <button
                              onClick={() => toggleIssueCompleted(item.id)}
                              className="text-neutral-500 hover:text-emerald-400 transition-colors block"
                            >
                              {item.completed ? (
                                <CheckSquare className="h-5 w-5 text-emerald-400" />
                              ) : (
                                <Square className="h-5 w-5 text-neutral-600" />
                              )}
                            </button>
                          </td>

                          {/* Col 2: ID & Title */}
                          <td className="py-3.5 px-4">
                            <Link href={`/new/task/${item.id}`} className="flex items-center gap-2.5 group/link">
                              <span className="font-mono text-xs sm:text-sm text-neutral-400 shrink-0 group-hover/link:text-white transition-colors">
                                {item.id}
                              </span>
                              <span
                                className={`font-medium text-xs sm:text-sm leading-snug hover:underline ${
                                  item.completed
                                    ? "line-through text-neutral-500"
                                    : "text-neutral-100 group-hover/link:text-white"
                                }`}
                              >
                                {item.title}
                              </span>
                            </Link>
                          </td>

                          {/* Col 3: Context Pills */}
                          <td className="py-3.5 px-4">
                            <div className="flex items-center gap-1.5">
                              {item.tags.map((tag, idx) => (
                                <span
                                  key={idx}
                                  className="px-2 py-0.5 rounded bg-neutral-800 text-neutral-300 border border-neutral-700 text-xs font-mono"
                                >
                                  [{tag}]
                                </span>
                              ))}
                            </div>
                          </td>

                          {/* Col 4: Project */}
                          <td className="py-3.5 px-4">
                            <span className="text-xs sm:text-sm text-neutral-200 font-medium">
                              {item.project}
                            </span>
                          </td>

                          {/* Col 5: Branch tag */}
                          <td className="py-3.5 px-4">
                            <span className="font-mono text-xs bg-neutral-800/80 text-neutral-300 px-2.5 py-0.5 rounded border border-neutral-700/60 inline-flex items-center gap-1">
                              <GitBranch className="h-3.5 w-3.5 text-neutral-400" />
                              {item.branch}
                            </span>
                          </td>

                          {/* Col 6: Priority */}
                          <td className="py-3.5 px-4">
                            <span
                              className={`px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold inline-block ${
                                item.priority === "Urgent"
                                  ? "bg-red-950/40 text-red-400 border border-red-800/40"
                                  : item.priority === "High"
                                  ? "bg-amber-950/40 text-amber-300 border border-amber-800/40"
                                  : "bg-neutral-800 text-neutral-300 border border-neutral-700"
                              }`}
                            >
                              {item.priority}
                            </span>
                          </td>

                          {/* Col 7: Assignee */}
                          <td className="py-3.5 px-4">
                            <div className="flex items-center gap-2">
                              <Avatar className="h-6 w-6 border border-neutral-700">
                                <AvatarFallback className="bg-neutral-800 text-neutral-300 text-[9px] font-mono font-bold">
                                  {item.assignee.avatar}
                                </AvatarFallback>
                              </Avatar>
                              <span className="text-xs font-mono text-neutral-400">
                                {item.updatedAt}
                              </span>
                            </div>
                          </td>

                          {/* Action Column */}
                          <td className="py-3.5 px-4 text-right">
                            <div className="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                              <button
                                onClick={() => copyIssueId(item.id)}
                                className="p-1 rounded text-neutral-400 hover:text-white hover:bg-neutral-800 transition-colors"
                                title="Copy Task ID"
                              >
                                <Copy className="h-4 w-4" />
                              </button>
                              <button
                                className="p-1 rounded text-neutral-400 hover:text-white hover:bg-neutral-800 transition-colors"
                                title="Edit Task"
                              >
                                <Edit2 className="h-4 w-4" />
                              </button>
                            </div>
                          </td>
                        </tr>
                      ))}
                  </tbody>
                </table>
              </div>
            </div>

            {/* Velocity Chart */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-5 sm:p-6 rounded-xl space-y-4 shadow-xl">
              <div className="flex items-center justify-between">
                <div>
                  <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-white">
                    Sprint Throughput & Velocity
                  </h3>
                  <p className="text-xs sm:text-sm text-neutral-400 font-mono mt-0.5">
                    Weekly issue resolution rate
                  </p>
                </div>
                <span className="text-xs sm:text-sm font-mono text-emerald-400 bg-emerald-950/40 px-3 py-1 rounded-full border border-emerald-800/40 font-semibold">
                  +14.2% velocity
                </span>
              </div>

              <div className="h-48 w-full">
                <ResponsiveContainer width="100%" height="100%">
                  <AreaChart data={displayChartData}>
                    <defs>
                      <linearGradient id="blackPremiumGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="5%" stopColor="#f5f5f5" stopOpacity={0.25} />
                        <stop offset="95%" stopColor="#f5f5f5" stopOpacity={0} />
                      </linearGradient>
                    </defs>
                    <CartesianGrid strokeDasharray="3 3" stroke="#262626" />
                    <XAxis dataKey="name" stroke="#737373" fontSize={11} fontFamily="monospace" />
                    <YAxis stroke="#737373" fontSize={11} fontFamily="monospace" />
                    <Tooltip
                      contentStyle={{
                        backgroundColor: "#0a0a0a",
                        borderColor: "#262626",
                        color: "#ffffff",
                        fontSize: "12px",
                        fontFamily: "monospace",
                        borderRadius: "8px"
                      }}
                    />
                    <Area
                      type="monotone"
                      dataKey="throughput"
                      stroke="#d4d4d4"
                      strokeWidth={2}
                      fillOpacity={1}
                      fill="url(#blackPremiumGrad)"
                    />
                  </AreaChart>
                </ResponsiveContainer>
              </div>
            </div>
          </div>

          {/* Right Column (4 cols): Webhook Activity Stream & Workspaces */}
          <div className="lg:col-span-4 space-y-6">
            {/* Live Inbound Event Stream */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-5 sm:p-6 rounded-xl space-y-4 shadow-xl">
              <div className="flex items-center justify-between pb-2.5 border-b border-neutral-800">
                <div className="flex items-center gap-2">
                  <Terminal className="h-4 w-4 text-neutral-400" />
                  <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-white">
                    Live Event Stream
                  </h3>
                </div>
                <span className="flex items-center gap-1.5 text-xs font-mono text-emerald-400 font-semibold">
                  <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                  Listening
                </span>
              </div>

              <div className="space-y-3 pt-1">
                {activityStream.map((evt) => (
                  <div
                    key={evt.id}
                    className="p-3.5 rounded-lg bg-black/80 border border-neutral-800 space-y-1.5 font-mono text-xs sm:text-sm"
                  >
                    <div className="flex items-center justify-between text-xs">
                      <span className="text-neutral-500">{evt.time}</span>
                      <span className="px-2 py-0.5 rounded bg-neutral-800 text-neutral-300 border border-neutral-700">
                        [{evt.source}]
                      </span>
                    </div>
                    <p className="text-neutral-200 text-xs sm:text-sm truncate">{evt.event}</p>
                    <span className="text-xs text-neutral-500 block text-right">
                      {evt.relTime}
                    </span>
                  </div>
                ))}
              </div>
            </div>

            {/* Active Workspace Status */}
            <div className="border border-neutral-800 bg-neutral-900/90 p-5 sm:p-6 rounded-xl space-y-4 shadow-xl">
              <div className="flex items-center justify-between pb-2.5 border-b border-neutral-800">
                <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-white">
                  Active Projects
                </h3>
                <span className="text-xs font-mono text-neutral-400">
                  {displayProjects.length} tracked
                </span>
              </div>

              <div className="space-y-3.5">
                {displayProjects.map((p) => (
                  <div key={p.id} className="space-y-2">
                    <div className="flex items-center justify-between text-xs sm:text-sm font-sans">
                      <span className="font-semibold text-neutral-100">{p.name}</span>
                      <span className="font-mono text-xs text-neutral-400">{p.progress}%</span>
                    </div>
                    <div className="h-2 w-full bg-black rounded-full overflow-hidden border border-neutral-800">
                      <div
                        className="h-full bg-gradient-to-r from-neutral-200 to-neutral-400 rounded-full transition-all duration-500"
                        style={{ width: `${p.progress}%` }}
                      ></div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}
