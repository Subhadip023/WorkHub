import React, { useState } from "react";
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
  BarChart3
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
  
  // Interactive demo state for quick task management
  const [demoTasks, setDemoTasks] = useState([
    { id: 1, title: "Review pull request #142 (Inertia setup)", priority: "High", completed: false, category: "Dev" },
    { id: 2, title: "Update API endpoint response schema for WorkHub", priority: "Medium", completed: true, category: "API" },
    { id: 3, title: "Design responsive dashboard navigation layout", priority: "High", completed: false, category: "UI/UX" },
    { id: 4, title: "Conduct sprint retro with engineering team", priority: "Low", completed: false, category: "Meeting" },
    { id: 5, title: "Optimize Laravel database queries for task filtering", priority: "Medium", completed: true, category: "Backend" },
  ]);

  const [newTaskInput, setNewTaskInput] = useState("");

  const toggleTask = (id) => {
    setDemoTasks((prev) =>
      prev.map((task) =>
        task.id === id ? { ...task, completed: !task.completed } : task
      )
    );
  };

  const handleAddTask = (e) => {
    e.preventDefault();
    if (!newTaskInput.trim()) return;
    setDemoTasks((prev) => [
      {
        id: Date.now(),
        title: newTaskInput,
        priority: "Medium",
        completed: false,
        category: "Task",
      },
      ...prev,
    ]);
    setNewTaskInput("");
  };

  // Fallback demo data if not passed from controller
  const displayStats = stats || {
    total_projects: 12,
    active_tasks: 48,
    completed_tasks: 156,
    team_members: 18,
    productivity_rate: 94.2,
    revenue: 42850,
  };

  const displayChartData = chart_data || [
    { name: "Mon", tasks: 14, completed: 12, revenue: 2400 },
    { name: "Tue", tasks: 22, completed: 18, revenue: 3800 },
    { name: "Wed", tasks: 28, completed: 25, revenue: 5100 },
    { name: "Thu", tasks: 24, completed: 22, revenue: 4600 },
    { name: "Fri", tasks: 35, completed: 31, revenue: 6800 },
    { name: "Sat", tasks: 18, completed: 16, revenue: 3200 },
    { name: "Sun", tasks: 12, completed: 11, revenue: 2100 },
  ];

  const displayProjects = projects || [
    { id: 1, name: "WorkHub Mobile App", progress: 78, status: "In Progress", dueDate: "Aug 28", team: 6, tag: "React Native" },
    { id: 2, name: "Inertia.js Migration", progress: 92, status: "Near Completion", dueDate: "Aug 22", team: 4, tag: "Laravel + React" },
    { id: 3, name: "shadcn/ui Design System", progress: 100, status: "Completed", dueDate: "Aug 18", team: 5, tag: "Tailwind CSS" },
    { id: 4, name: "Customer Portal v2", progress: 45, status: "In Progress", dueDate: "Sep 15", team: 8, tag: "Next.js" },
  ];

  const displayActivities = recent_activity || [
    { id: 1, user: "Alex Morgan", action: "completed task", target: "API Authentication Refactor", time: "10 mins ago", avatar: "AM", badge: "Completed" },
    { id: 2, user: "Sarah Chen", action: "created issue", target: "Hydration mismatch on dashboard", time: "32 mins ago", avatar: "SC", badge: "Issue" },
    { id: 3, user: "Michael Scott", action: "updated project", target: "Q3 WorkHub Redesign", time: "1 hour ago", avatar: "MS", badge: "Project" },
    { id: 4, user: "Emma Watson", action: "pushed commit", target: "feat: Add Inertia React dashboard", time: "2 hours ago", avatar: "EW", badge: "Code" },
    { id: 5, user: "David Kim", action: "joined company", target: "Product Team", time: "4 hours ago", avatar: "DK", badge: "Team" },
  ];

  const filteredTasks = demoTasks.filter((t) => {
    if (taskFilter === "pending") return !t.completed;
    if (taskFilter === "completed") return t.completed;
    return true;
  });

  const headerActions = (
    <Button variant="gradient" size="sm" className="hidden sm:flex items-center gap-1.5 shadow-indigo-500/25">
      <Plus className="h-4 w-4" /> New Task
    </Button>
  );

  return (
    <DashboardLayout title="Overview" activeItem="dashboard" actions={headerActions}>
      <div className="space-y-8">
        {/* Header Banner */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-indigo-950/60 via-slate-900/80 to-slate-900/60 p-6 rounded-2xl border border-indigo-500/20 shadow-2xl relative overflow-hidden">
          <div className="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-indigo-500/10 to-transparent pointer-events-none"></div>
          <div className="space-y-1 z-10">
            <div className="flex items-center gap-2">
              <Badge variant="default" className="gap-1">
                <Sparkles className="h-3 w-3 text-indigo-300" /> Inertia + React Engine
              </Badge>
              <span className="text-xs text-slate-400">Reusable Dashboard Layout</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
              Welcome back, {user?.name || "Demo User"}!
            </h1>
            <p className="text-sm text-slate-400 max-w-xl">
              Here is what's happening across your WorkHub projects today. You have{" "}
              <span className="text-indigo-400 font-semibold">{displayStats.active_tasks} tasks</span> requiring attention.
            </p>
          </div>

          <div className="flex items-center gap-3 z-10">
            <Button variant="outline" className="gap-2">
              <Calendar className="h-4 w-4" /> Filter Date
            </Button>
            <Button variant="gradient" className="gap-2 shadow-indigo-500/25">
              <Plus className="h-4 w-4" /> New Task
            </Button>
          </div>
        </div>

        {/* Metric KPI Cards Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Card 1: Total Projects */}
          <Card className="relative overflow-hidden group border-slate-800/80 hover:border-indigo-500/50">
            <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
              <FolderKanban className="h-16 w-16 text-indigo-400" />
            </div>
            <CardHeader className="pb-2">
              <CardDescription className="flex items-center justify-between text-xs text-slate-400 font-medium">
                <span>Total Projects</span>
                <span className="inline-flex items-center text-xs font-semibold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">
                  <ArrowUpRight className="h-3 w-3 mr-0.5" /> +14%
                </span>
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                {displayStats.total_projects}
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="flex items-center gap-2 text-xs text-slate-400">
                <span className="text-indigo-400 font-medium">8 Active</span> • 4 Archived
              </div>
              <Progress value={75} className="h-1.5 mt-3" />
            </CardContent>
          </Card>

          {/* Card 2: Active Tasks */}
          <Card className="relative overflow-hidden group border-slate-800/80 hover:border-purple-500/50">
            <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
              <Clock className="h-16 w-16 text-purple-400" />
            </div>
            <CardHeader className="pb-2">
              <CardDescription className="flex items-center justify-between text-xs text-slate-400 font-medium">
                <span>Active Tasks</span>
                <span className="inline-flex items-center text-xs font-semibold text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded">
                  <Clock className="h-3 w-3 mr-0.5" /> 5 Urgent
                </span>
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                {displayStats.active_tasks}
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="flex items-center gap-2 text-xs text-slate-400">
                <span className="text-purple-400 font-medium">18 in Review</span> • 30 In Progress
              </div>
              <Progress value={60} className="h-1.5 mt-3" />
            </CardContent>
          </Card>

          {/* Card 3: Completed Tasks */}
          <Card className="relative overflow-hidden group border-slate-800/80 hover:border-emerald-500/50">
            <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
              <CheckCircle2 className="h-16 w-16 text-emerald-400" />
            </div>
            <CardHeader className="pb-2">
              <CardDescription className="flex items-center justify-between text-xs text-slate-400 font-medium">
                <span>Completed Tasks</span>
                <span className="inline-flex items-center text-xs font-semibold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">
                  <ArrowUpRight className="h-3 w-3 mr-0.5" /> +28%
                </span>
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                {displayStats.completed_tasks}
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="flex items-center gap-2 text-xs text-slate-400">
                <span className="text-emerald-400 font-medium">+34 this week</span>
              </div>
              <Progress value={92} className="h-1.5 mt-3" />
            </CardContent>
          </Card>

          {/* Card 4: Productivity Index */}
          <Card className="relative overflow-hidden group border-slate-800/80 hover:border-cyan-500/50">
            <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
              <TrendingUp className="h-16 w-16 text-cyan-400" />
            </div>
            <CardHeader className="pb-2">
              <CardDescription className="flex items-center justify-between text-xs text-slate-400 font-medium">
                <span>Efficiency Rate</span>
                <span className="inline-flex items-center text-xs font-semibold text-cyan-400 bg-cyan-500/10 px-1.5 py-0.5 rounded">
                  <Sparkles className="h-3 w-3 mr-0.5" /> Optimal
                </span>
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                {displayStats.productivity_rate}%
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="flex items-center gap-2 text-xs text-slate-400">
                <span className="text-cyan-400 font-medium">Top 5% speed</span>
              </div>
              <Progress value={displayStats.productivity_rate} className="h-1.5 mt-3" />
            </CardContent>
          </Card>
        </div>

        {/* Middle Section: Chart & Interactive Tasks Demo */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Main Chart Visualization */}
          <Card className="lg:col-span-2 border-slate-800/80">
            <CardHeader className="flex flex-row items-center justify-between pb-4">
              <div>
                <CardTitle className="flex items-center gap-2">
                  <BarChart3 className="h-5 w-5 text-indigo-400" /> Workload & Task Activity
                </CardTitle>
                <CardDescription>
                  Weekly task creation vs completion rate across all teams
                </CardDescription>
              </div>
              <div className="flex items-center gap-2">
                <Badge variant="secondary" className="cursor-pointer">
                  Weekly
                </Badge>
                <Badge variant="outline" className="cursor-pointer opacity-70">
                  Monthly
                </Badge>
              </div>
            </CardHeader>
            <CardContent>
              <div className="h-72 w-full pt-4">
                <ResponsiveContainer width="100%" height="100%">
                  <AreaChart data={displayChartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                    <defs>
                      <linearGradient id="colorTasks" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="5%" stopColor="#6366f1" stopOpacity={0.4} />
                        <stop offset="95%" stopColor="#6366f1" stopOpacity={0.0} />
                      </linearGradient>
                      <linearGradient id="colorCompleted" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="5%" stopColor="#10b981" stopOpacity={0.4} />
                        <stop offset="95%" stopColor="#10b981" stopOpacity={0.0} />
                      </linearGradient>
                    </defs>
                    <CartesianGrid strokeDasharray="3 3" stroke="#1e293b" />
                    <XAxis dataKey="name" stroke="#64748b" fontSize={12} tickLine={false} />
                    <YAxis stroke="#64748b" fontSize={12} tickLine={false} />
                    <Tooltip
                      contentStyle={{
                        backgroundColor: "#0f172a",
                        borderColor: "#334155",
                        borderRadius: "0.5rem",
                        color: "#f8fafc",
                      }}
                    />
                    <Area type="monotone" dataKey="tasks" stroke="#6366f1" strokeWidth={3} fillOpacity={1} fill="url(#colorTasks)" name="Tasks Opened" />
                    <Area type="monotone" dataKey="completed" stroke="#10b981" strokeWidth={3} fillOpacity={1} fill="url(#colorCompleted)" name="Tasks Completed" />
                  </AreaChart>
                </ResponsiveContainer>
              </div>
            </CardContent>
          </Card>

          {/* Interactive Demo Tasks Panel */}
          <Card className="border-slate-800/80 flex flex-col justify-between">
            <div>
              <CardHeader className="pb-3">
                <div className="flex items-center justify-between">
                  <CardTitle className="flex items-center gap-2 text-base">
                    <CheckSquare className="h-5 w-5 text-indigo-400" /> Interactive Demo Checklist
                  </CardTitle>
                  <Badge variant="info">{filteredTasks.length} items</Badge>
                </div>
                <CardDescription>
                  Test real-time React state interactivity in this Inertia page
                </CardDescription>

                {/* Task Filter Tabs */}
                <div className="flex items-center gap-1 mt-3 bg-slate-950 p-1 rounded-lg border border-slate-800 text-xs">
                  <button
                    onClick={() => setTaskFilter("all")}
                    className={`flex-1 py-1 text-center rounded-md font-medium transition-all ${
                      taskFilter === "all" ? "bg-slate-800 text-white" : "text-slate-400 hover:text-slate-200"
                    }`}
                  >
                    All
                  </button>
                  <button
                    onClick={() => setTaskFilter("pending")}
                    className={`flex-1 py-1 text-center rounded-md font-medium transition-all ${
                      taskFilter === "pending" ? "bg-slate-800 text-white" : "text-slate-400 hover:text-slate-200"
                    }`}
                  >
                    Pending
                  </button>
                  <button
                    onClick={() => setTaskFilter("completed")}
                    className={`flex-1 py-1 text-center rounded-md font-medium transition-all ${
                      taskFilter === "completed" ? "bg-slate-800 text-white" : "text-slate-400 hover:text-slate-200"
                    }`}
                  >
                    Done
                  </button>
                </div>
              </CardHeader>

              <CardContent className="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                {filteredTasks.map((task) => (
                  <div
                    key={task.id}
                    onClick={() => toggleTask(task.id)}
                    className={`flex items-start gap-3 p-2.5 rounded-lg border transition-all cursor-pointer ${
                      task.completed
                        ? "bg-slate-900/40 border-slate-800/60 opacity-60 line-through"
                        : "bg-slate-900 border-slate-800 hover:border-slate-700"
                    }`}
                  >
                    <button className="mt-0.5 text-indigo-400 hover:text-indigo-300">
                      {task.completed ? (
                        <CheckSquare className="h-4 w-4 text-emerald-400" />
                      ) : (
                        <Square className="h-4 w-4 text-slate-500" />
                      )}
                    </button>
                    <div className="flex-1 min-w-0">
                      <p className="text-xs font-medium text-slate-200 truncate">
                        {task.title}
                      </p>
                      <div className="flex items-center gap-2 mt-1">
                        <span className="text-[10px] text-slate-500 font-mono">
                          {task.category}
                        </span>
                        <span
                          className={`text-[9px] px-1.5 py-0.2 rounded font-semibold ${
                            task.priority === "High"
                              ? "bg-rose-500/20 text-rose-300"
                              : task.priority === "Medium"
                              ? "bg-amber-500/20 text-amber-300"
                              : "bg-slate-800 text-slate-400"
                          }`}
                        >
                          {task.priority}
                        </span>
                      </div>
                    </div>
                  </div>
                ))}
              </CardContent>
            </div>

            {/* Add Task Form Footer */}
            <div className="p-4 border-t border-slate-800/80 bg-slate-950/40 rounded-b-xl">
              <form onSubmit={handleAddTask} className="flex gap-2">
                <input
                  type="text"
                  placeholder="Add a new task..."
                  value={newTaskInput}
                  onChange={(e) => setNewTaskInput(e.target.value)}
                  className="flex-1 bg-slate-900 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                />
                <Button type="submit" size="sm" variant="default" className="px-3">
                  <Plus className="h-3.5 w-3.5" />
                </Button>
              </form>
            </div>
          </Card>
        </div>

        {/* Bottom Grid: Active Projects & Activity Feed */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Active Projects Overview */}
          <Card className="lg:col-span-2 border-slate-800/80">
            <CardHeader className="flex flex-row items-center justify-between">
              <div>
                <CardTitle className="flex items-center gap-2 text-lg">
                  <FolderKanban className="h-5 w-5 text-purple-400" /> Active Projects
                </CardTitle>
                <CardDescription>
                  Key projects, milestone progress, and team assignments
                </CardDescription>
              </div>
              <Button variant="ghost" size="sm" className="text-xs text-indigo-400 hover:text-indigo-300">
                View All <ChevronRight className="h-3.5 w-3.5 ml-1" />
              </Button>
            </CardHeader>
            <CardContent className="space-y-4">
              {displayProjects.map((prj) => (
                <div
                  key={prj.id}
                  className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 hover:border-slate-700 transition-all space-y-3"
                >
                  <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div className="flex items-center gap-2.5">
                      <div className="h-8 w-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center font-bold text-indigo-400 text-xs">
                        P{prj.id}
                      </div>
                      <div>
                        <h4 className="text-sm font-semibold text-slate-100">
                          {prj.name}
                        </h4>
                        <span className="text-[11px] text-slate-400">
                          Due {prj.dueDate} • {prj.tag}
                        </span>
                      </div>
                    </div>

                    <div className="flex items-center gap-3">
                      <Badge
                        variant={
                          prj.progress === 100
                            ? "success"
                            : prj.progress > 70
                            ? "default"
                            : "warning"
                        }
                      >
                        {prj.status}
                      </Badge>
                      <div className="text-xs font-mono font-semibold text-slate-300">
                        {prj.progress}%
                      </div>
                    </div>
                  </div>

                  <Progress value={prj.progress} className="h-2" />
                </div>
              ))}
            </CardContent>
          </Card>

          {/* Recent Activity Timeline */}
          <Card className="border-slate-800/80">
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-lg">
                <Activity className="h-5 w-5 text-emerald-400" /> Audit Log & Activity
              </CardTitle>
              <CardDescription>Real-time events from team members</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {displayActivities.map((act) => (
                <div key={act.id} className="flex items-start gap-3 text-xs">
                  <Avatar className="h-8 w-8 mt-0.5">
                    <AvatarFallback>{act.avatar}</AvatarFallback>
                  </Avatar>
                  <div className="flex-1 min-w-0">
                    <p className="text-slate-300">
                      <span className="font-semibold text-slate-100">{act.user}</span>{" "}
                      {act.action}{" "}
                      <span className="font-medium text-indigo-400">{act.target}</span>
                    </p>
                    <p className="text-[10px] text-slate-500 mt-0.5">{act.time}</p>
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}
