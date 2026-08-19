import React, { useState } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  BarChart3,
  TrendingUp,
  Clock,
  CheckCircle2,
  Users,
  Download,
  Calendar,
  Filter,
  Sparkles,
  ArrowUpRight,
  ArrowDownRight,
  Zap,
  Target,
  Award,
  Layers,
  PieChart as PieChartIcon
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
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  CartesianGrid,
  Legend
} from "recharts";

export default function Analytics({ analytics_data, team_members }) {
  const [timeRange, setTimeRange] = useState("30d");

  // Fallback demo dataset for charts
  const throughputData = analytics_data?.throughput || [
    { period: "Week 1", created: 45, completed: 38, backlog: 7 },
    { period: "Week 2", created: 52, completed: 48, backlog: 11 },
    { period: "Week 3", created: 61, completed: 59, backlog: 13 },
    { period: "Week 4", created: 48, completed: 54, backlog: 7 },
    { period: "Week 5", created: 70, completed: 66, backlog: 11 },
    { period: "Week 6", created: 58, completed: 62, backlog: 7 },
  ];

  const categoryDistribution = analytics_data?.categories || [
    { category: "Frontend Dev", tasks: 42, hours: 168 },
    { category: "Backend API", tasks: 36, hours: 144 },
    { category: "UI/UX Design", tasks: 24, hours: 96 },
    { category: "QA & Testing", tasks: 18, hours: 72 },
    { category: "DevOps / Infra", tasks: 12, hours: 48 },
  ];

  const teamPerformance = team_members || [
    { name: "Alex Morgan", role: "Fullstack Engineer", avatar: "AM", assigned: 18, completed: 16, rate: 88, velocity: "3.2/day" },
    { name: "Sarah Chen", role: "UI/UX Lead", avatar: "SC", assigned: 14, completed: 14, rate: 100, velocity: "2.8/day" },
    { name: "Michael Scott", role: "Backend Developer", avatar: "MS", assigned: 22, completed: 19, rate: 86, velocity: "3.8/day" },
    { name: "Emma Watson", role: "QA Engineer", avatar: "EW", assigned: 15, completed: 13, rate: 86, velocity: "2.6/day" },
    { name: "David Kim", role: "DevOps Engineer", avatar: "DK", assigned: 10, completed: 9, rate: 90, velocity: "1.8/day" },
  ];

  const headerActions = (
    <div className="flex items-center gap-2">
      <Button variant="outline" size="sm" className="gap-1.5 text-xs">
        <Download className="h-3.5 w-3.5" /> Export PDF
      </Button>
    </div>
  );

  return (
    <DashboardLayout title="Analytics & Performance" activeItem="analytics" actions={headerActions}>
      <div className="space-y-8">
        {/* Header Title Banner */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-purple-950/60 via-slate-900/80 to-slate-900/60 p-6 rounded-2xl border border-purple-500/20 shadow-2xl relative overflow-hidden">
          <div className="space-y-1 z-10">
            <div className="flex items-center gap-2">
              <Badge variant="default" className="gap-1 bg-purple-500/20 text-purple-300 border-purple-500/30">
                <BarChart3 className="h-3 w-3 text-purple-400" /> Executive Analytics
              </Badge>
              <span className="text-xs text-slate-400">WorkHub Intelligence Engine</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
              Performance & Velocity Metrics
            </h1>
            <p className="text-sm text-slate-400 max-w-xl">
              Track throughput, sprint velocity, cycle time, and team output in real-time.
            </p>
          </div>

          {/* Time Range Selector */}
          <div className="flex items-center gap-1.5 bg-slate-950/90 p-1.5 rounded-xl border border-slate-800 z-10">
            {["7d", "30d", "90d", "ytd"].map((range) => (
              <button
                key={range}
                onClick={() => setTimeRange(range)}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-wider transition-all ${
                  timeRange === range
                    ? "bg-purple-600 text-white shadow-lg shadow-purple-600/30"
                    : "text-slate-400 hover:text-slate-200 hover:bg-slate-900"
                }`}
              >
                {range}
              </button>
            ))}
          </div>
        </div>

        {/* Top Metric Cards Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Metric 1 */}
          <Card className="border-slate-800/80 hover:border-purple-500/50 transition-all">
            <CardHeader className="pb-2">
              <CardDescription className="flex items-center justify-between text-xs text-slate-400 font-medium">
                <span>Team Velocity</span>
                <span className="inline-flex items-center text-xs font-semibold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">
                  <ArrowUpRight className="h-3 w-3 mr-0.5" /> +18.4%
                </span>
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                24.8 <span className="text-sm font-normal text-slate-400">tasks/day</span>
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="text-xs text-slate-400">
                <span className="text-purple-400 font-medium">+3.8/day</span> vs last period
              </div>
              <Progress value={82} className="h-1.5 mt-3" />
            </CardContent>
          </Card>

          {/* Metric 2 */}
          <Card className="border-slate-800/80 hover:border-cyan-500/50 transition-all">
            <CardHeader className="pb-2">
              <CardDescription className="flex items-center justify-between text-xs text-slate-400 font-medium">
                <span>Average Cycle Time</span>
                <span className="inline-flex items-center text-xs font-semibold text-cyan-400 bg-cyan-500/10 px-1.5 py-0.5 rounded">
                  <ArrowDownRight className="h-3 w-3 mr-0.5" /> -12% Faster
                </span>
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                1.4 <span className="text-sm font-normal text-slate-400">days</span>
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="text-xs text-slate-400">
                <span className="text-cyan-400 font-medium">0.3 days reduced</span> turnaround
              </div>
              <Progress value={90} className="h-1.5 mt-3" />
            </CardContent>
          </Card>

          {/* Metric 3 */}
          <Card className="border-slate-800/80 hover:border-emerald-500/50 transition-all">
            <CardHeader className="pb-2">
              <CardDescription className="flex items-center justify-between text-xs text-slate-400 font-medium">
                <span>Sprint Goal Completion</span>
                <span className="inline-flex items-center text-xs font-semibold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">
                  <Target className="h-3 w-3 mr-0.5" /> On Target
                </span>
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                88.4%
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="text-xs text-slate-400">
                <span className="text-emerald-400 font-medium">142 of 160</span> milestone tasks
              </div>
              <Progress value={88.4} className="h-1.5 mt-3" />
            </CardContent>
          </Card>

          {/* Metric 4 */}
          <Card className="border-slate-800/80 hover:border-indigo-500/50 transition-all">
            <CardHeader className="pb-2">
              <CardDescription className="flex items-center justify-between text-xs text-slate-400 font-medium">
                <span>Team Efficiency Rating</span>
                <span className="inline-flex items-center text-xs font-semibold text-indigo-400 bg-indigo-500/10 px-1.5 py-0.5 rounded">
                  <Award className="h-3 w-3 mr-0.5" /> Top 5%
                </span>
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                96 / 100
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="text-xs text-slate-400">
                Based on delivery speed & code reviews
              </div>
              <Progress value={96} className="h-1.5 mt-3" />
            </CardContent>
          </Card>
        </div>

        {/* Charts Row */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Main Throughput & Velocity Chart */}
          <Card className="lg:col-span-2 border-slate-800/80">
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <div>
                <CardTitle className="flex items-center gap-2 text-lg">
                  <TrendingUp className="h-5 w-5 text-purple-400" /> Sprint Throughput & Cumulative Flow
                </CardTitle>
                <CardDescription>
                  Comparison of tasks created vs tasks completed per weekly cycle
                </CardDescription>
              </div>
            </CardHeader>
            <CardContent>
              <div className="h-80 w-full pt-4">
                <ResponsiveContainer width="100%" height="100%">
                  <AreaChart data={throughputData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                    <defs>
                      <linearGradient id="colorCreated" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="5%" stopColor="#a855f7" stopOpacity={0.4} />
                        <stop offset="95%" stopColor="#a855f7" stopOpacity={0.0} />
                      </linearGradient>
                      <linearGradient id="colorCompleted" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="5%" stopColor="#3b82f6" stopOpacity={0.4} />
                        <stop offset="95%" stopColor="#3b82f6" stopOpacity={0.0} />
                      </linearGradient>
                    </defs>
                    <CartesianGrid strokeDasharray="3 3" stroke="#1e293b" />
                    <XAxis dataKey="period" stroke="#64748b" fontSize={12} tickLine={false} />
                    <YAxis stroke="#64748b" fontSize={12} tickLine={false} />
                    <Tooltip
                      contentStyle={{
                        backgroundColor: "#0f172a",
                        borderColor: "#334155",
                        borderRadius: "0.5rem",
                        color: "#f8fafc",
                      }}
                    />
                    <Legend />
                    <Area type="monotone" dataKey="created" stroke="#a855f7" strokeWidth={3} fillOpacity={1} fill="url(#colorCreated)" name="Tasks Created" />
                    <Area type="monotone" dataKey="completed" stroke="#3b82f6" strokeWidth={3} fillOpacity={1} fill="url(#colorCompleted)" name="Tasks Completed" />
                  </AreaChart>
                </ResponsiveContainer>
              </div>
            </CardContent>
          </Card>

          {/* Work Category Distribution Bar Chart */}
          <Card className="border-slate-800/80">
            <CardHeader className="pb-2">
              <CardTitle className="flex items-center gap-2 text-lg">
                <Layers className="h-5 w-5 text-indigo-400" /> Work Distribution
              </CardTitle>
              <CardDescription>Tasks logged per domain category</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="h-80 w-full pt-4">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={categoryDistribution} layout="vertical" margin={{ top: 10, right: 10, left: 30, bottom: 0 }}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#1e293b" horizontal={false} />
                    <XAxis type="number" stroke="#64748b" fontSize={12} tickLine={false} />
                    <YAxis dataKey="category" type="category" stroke="#64748b" fontSize={11} tickLine={false} width={80} />
                    <Tooltip
                      contentStyle={{
                        backgroundColor: "#0f172a",
                        borderColor: "#334155",
                        borderRadius: "0.5rem",
                        color: "#f8fafc",
                      }}
                    />
                    <Bar dataKey="tasks" fill="#6366f1" radius={[0, 6, 6, 0]} name="Tasks Count" />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Team Velocity Matrix Table */}
        <Card className="border-slate-800/80">
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle className="flex items-center gap-2 text-lg">
                <Users className="h-5 w-5 text-cyan-400" /> Team Velocity Matrix
              </CardTitle>
              <CardDescription>Individual contribution breakdown and completion rate</CardDescription>
            </div>
            <Badge variant="outline" className="text-xs text-slate-400">
              5 Active Engineers
            </Badge>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                    <th className="pb-3 px-2">Member</th>
                    <th className="pb-3 px-2">Role</th>
                    <th className="pb-3 px-2">Assigned</th>
                    <th className="pb-3 px-2">Completed</th>
                    <th className="pb-3 px-2">Velocity</th>
                    <th className="pb-3 px-2 w-40">Completion Rate</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-800/60">
                  {teamPerformance.map((member, idx) => (
                    <tr key={idx} className="hover:bg-slate-900/50 transition-colors">
                      <td className="py-3 px-2">
                        <div className="flex items-center gap-2.5">
                          <Avatar className="h-7 w-7">
                            <AvatarFallback className="bg-indigo-600/30 text-indigo-300 font-bold text-[10px]">
                              {member.avatar}
                            </AvatarFallback>
                          </Avatar>
                          <span className="font-semibold text-slate-100">{member.name}</span>
                        </div>
                      </td>
                      <td className="py-3 px-2 text-slate-400">{member.role}</td>
                      <td className="py-3 px-2 font-mono font-medium text-slate-300">{member.assigned}</td>
                      <td className="py-3 px-2 font-mono font-medium text-emerald-400">{member.completed}</td>
                      <td className="py-3 px-2 font-mono text-indigo-400 font-semibold">{member.velocity}</td>
                      <td className="py-3 px-2">
                        <div className="space-y-1">
                          <div className="flex justify-between text-[10px]">
                            <span className="text-slate-400">{member.rate}%</span>
                          </div>
                          <Progress value={member.rate} className="h-1.5" />
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
