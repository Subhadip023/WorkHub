import React, { useState } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  BarChart3,
  TrendingUp,
  Clock,
  CheckCircle2,
  AlertTriangle,
  Zap,
  Download,
  Calendar,
  Users,
  Layers,
  Sparkles,
  Crown
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
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
} from "recharts";

export default function Analytics({ metrics, team_performance, velocity_data }) {
  const [timeRange, setTimeRange] = useState("30d");

  const displayVelocity = velocity_data || [
    { sprint: "Sprint 20", planned: 40, completed: 38, velocity: 95 },
    { sprint: "Sprint 21", planned: 45, completed: 42, velocity: 93 },
    { sprint: "Sprint 22", planned: 50, completed: 48, velocity: 96 },
    { sprint: "Sprint 23", planned: 48, completed: 46, velocity: 95 },
    { sprint: "Sprint 24", planned: 55, completed: 52, velocity: 94 },
    { sprint: "Sprint 25", planned: 60, completed: 58, velocity: 96 },
  ];

  const displayTeam = team_performance || [
    { name: "Alex Morgan", role: "Tech Lead", tasksCompleted: 42, velocity: 98, avatar: "AM" },
    { name: "Sarah Chen", role: "Senior Frontend Eng", tasksCompleted: 38, velocity: 95, avatar: "SC" },
    { name: "Michael Scott", role: "Backend Architect", tasksCompleted: 35, velocity: 92, avatar: "MS" },
    { name: "Emma Watson", role: "QA Engineer", tasksCompleted: 29, velocity: 90, avatar: "EW" },
    { name: "David Kim", role: "DevOps Engineer", tasksCompleted: 26, velocity: 88, avatar: "DK" },
  ];

  const headerActions = (
    <Button size="sm" className="bg-white text-black hover:bg-neutral-200 font-semibold text-sm gap-2 transition-all rounded-xl h-9 px-3.5">
      <Download className="h-4 w-4 text-black" /> Export Report
    </Button>
  );

  return (
    <DashboardLayout title="Performance Analytics" activeItem="analytics" actions={headerActions}>
      <div className="space-y-6">
        {/* Header Hero Bar */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-neutral-800 bg-neutral-900/90 p-6 rounded-xl shadow-xl">
          <div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
              Engineering Velocity & Analytics <Crown className="h-6 w-6 text-neutral-300" />
            </h1>
            <p className="text-sm sm:text-base text-neutral-400 font-mono mt-1">
              Sprint throughput, cycle times, and team member velocity
            </p>
          </div>

          <div className="flex items-center gap-1 bg-black p-1 rounded-lg border border-neutral-800 text-xs sm:text-sm font-mono">
            {["7d", "30d", "90d"].map((range) => (
              <button
                key={range}
                onClick={() => setTimeRange(range)}
                className={`px-4 py-1.5 rounded-md transition-all ${
                  timeRange === range ? "bg-neutral-800 text-white font-semibold" : "text-neutral-400 hover:text-neutral-200"
                }`}
              >
                {range}
              </button>
            ))}
          </div>
        </div>

        {/* Metric Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div className="border border-neutral-800 bg-neutral-900/90 p-5 rounded-xl space-y-2 shadow-xl">
            <span className="text-xs font-mono text-neutral-400 font-bold uppercase tracking-wider block">
              Average Cycle Time
            </span>
            <div className="flex items-baseline gap-2">
              <span className="text-2xl sm:text-3xl font-bold font-mono text-white">1.8 Days</span>
              <span className="text-xs sm:text-sm text-emerald-400 font-mono">-12% faster</span>
            </div>
            <span className="text-xs text-neutral-500 font-mono block">From creation to merge</span>
          </div>

          <div className="border border-neutral-800 bg-neutral-900/90 p-5 rounded-xl space-y-2 shadow-xl">
            <span className="text-xs font-mono text-neutral-400 font-bold uppercase tracking-wider block">
              Sprint Completion Rate
            </span>
            <div className="flex items-baseline gap-2">
              <span className="text-2xl sm:text-3xl font-bold font-mono text-white">96.4%</span>
              <span className="text-xs sm:text-sm text-emerald-400 font-mono">+3.2% vs target</span>
            </div>
            <span className="text-xs text-neutral-500 font-mono block">58 of 60 tasks shipped</span>
          </div>

          <div className="border border-neutral-800 bg-neutral-900/90 p-5 rounded-xl space-y-2 shadow-xl">
            <span className="text-xs font-mono text-neutral-400 font-bold uppercase tracking-wider block">
              Code Review Latency
            </span>
            <div className="flex items-baseline gap-2">
              <span className="text-2xl sm:text-3xl font-bold font-mono text-white">4.2 Hrs</span>
              <span className="text-xs sm:text-sm text-emerald-400 font-mono">-45 mins</span>
            </div>
            <span className="text-xs text-neutral-500 font-mono block">PR turn-around time</span>
          </div>

          <div className="border border-neutral-800 bg-neutral-900/90 p-5 rounded-xl space-y-2 shadow-xl">
            <span className="text-xs font-mono text-neutral-400 font-bold uppercase tracking-wider block">
              Bug Leakage Rate
            </span>
            <div className="flex items-baseline gap-2">
              <span className="text-2xl sm:text-3xl font-bold font-mono text-white">0.4%</span>
              <span className="text-xs sm:text-sm text-emerald-400 font-mono font-semibold">Ultra Low</span>
            </div>
            <span className="text-xs text-neutral-500 font-mono block">Post-release defects</span>
          </div>
        </div>

        {/* Velocity Charts Section */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div className="border border-neutral-800 bg-neutral-900/90 p-5 sm:p-6 rounded-xl space-y-4 shadow-xl">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-white">
                  Sprint Velocity Trend
                </h3>
                <p className="text-xs sm:text-sm text-neutral-400 font-mono mt-0.5">
                  Planned story points vs completed points
                </p>
              </div>
              <span className="text-xs sm:text-sm font-mono text-emerald-400 bg-emerald-950/40 px-3 py-1 rounded-full border border-emerald-800/40 font-semibold">
                Optimal
              </span>
            </div>

            <div className="h-56 w-full">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={displayVelocity}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#262626" />
                  <XAxis dataKey="sprint" stroke="#737373" fontSize={11} fontFamily="monospace" />
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
                  <Bar dataKey="planned" fill="#525252" radius={[4, 4, 0, 0]} name="Planned Points" />
                  <Bar dataKey="completed" fill="#e5e5e5" radius={[4, 4, 0, 0]} name="Completed Points" />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </div>

          {/* Team Performance Ranking */}
          <div className="border border-neutral-800 bg-neutral-900/90 p-5 sm:p-6 rounded-xl space-y-4 shadow-xl">
            <div className="flex items-center justify-between pb-2.5 border-b border-neutral-800">
              <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-white">
                Team Member Output
              </h3>
              <span className="text-xs font-mono text-neutral-400">
                Top Performers
              </span>
            </div>

            <div className="space-y-3">
              {displayTeam.map((member, idx) => (
                <div
                  key={idx}
                  className="flex items-center justify-between p-3.5 rounded-lg bg-black border border-neutral-800"
                >
                  <div className="flex items-center gap-3">
                    <Avatar className="h-9 w-9 border border-neutral-700">
                      <AvatarFallback className="bg-neutral-800 text-neutral-200 font-mono text-xs font-bold">
                        {member.avatar}
                      </AvatarFallback>
                    </Avatar>
                    <div>
                      <span className="font-semibold text-sm text-white block">
                        {member.name}
                      </span>
                      <span className="text-xs text-neutral-400 font-mono block">
                        {member.role}
                      </span>
                    </div>
                  </div>

                  <div className="flex items-center gap-4 text-right">
                    <div>
                      <span className="font-mono text-xs sm:text-sm font-bold text-white block">
                        {member.tasksCompleted} tasks
                      </span>
                      <span className="text-xs text-emerald-400 font-mono block">
                        {member.velocity}% rate
                      </span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}
