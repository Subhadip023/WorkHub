import React, { useState } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  FolderKanban,
  Plus,
  Search,
  Filter,
  Grid,
  List,
  Calendar,
  Users,
  CheckCircle2,
  Clock,
  MoreVertical,
  ChevronRight,
  Sparkles,
  Layers,
  ArrowUpRight,
  Code,
  Tag,
  AlertCircle
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
import { Progress } from "@/Components/ui/progress";

export default function Projects({ initial_projects }) {
  const [filterStatus, setFilterStatus] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [viewMode, setViewMode] = useState("grid"); // 'grid' | 'table'
  const [showAddModal, setShowAddModal] = useState(false);

  const [projectsList, setProjectsList] = useState(
    initial_projects || [
      {
        id: 1,
        name: "WorkHub Mobile App",
        description: "Cross-platform mobile application for real-time task management and team synchronization.",
        progress: 78,
        status: "In Progress",
        dueDate: "Aug 28, 2026",
        teamMembers: [
          { name: "Alex Morgan", avatar: "AM" },
          { name: "Sarah Chen", avatar: "SC" },
          { name: "David Kim", avatar: "DK" },
        ],
        completedTasks: 42,
        totalTasks: 54,
        tag: "React Native",
        category: "Mobile",
        priority: "High",
      },
      {
        id: 2,
        name: "Inertia.js Migration",
        description: "Upgrading legacy Laravel blade views to Inertia.js + React JS SPA stack with shadcn UI.",
        progress: 92,
        status: "Near Completion",
        dueDate: "Aug 22, 2026",
        teamMembers: [
          { name: "Michael Scott", avatar: "MS" },
          { name: "Emma Watson", avatar: "EW" },
        ],
        completedTasks: 38,
        totalTasks: 41,
        tag: "Laravel + React",
        category: "Web App",
        priority: "High",
      },
      {
        id: 3,
        name: "shadcn/ui Design System",
        description: "Unified design component token library built with Tailwind CSS and Radix primitives.",
        progress: 100,
        status: "Completed",
        dueDate: "Aug 18, 2026",
        teamMembers: [
          { name: "Sarah Chen", avatar: "SC" },
          { name: "Alex Morgan", avatar: "AM" },
          { name: "Emma Watson", avatar: "EW" },
          { name: "David Kim", avatar: "DK" },
        ],
        completedTasks: 30,
        totalTasks: 30,
        tag: "Tailwind CSS",
        category: "Design",
        priority: "Medium",
      },
      {
        id: 4,
        name: "Customer Portal v2",
        description: "Self-service analytics and billing management portal for corporate clients.",
        progress: 45,
        status: "In Progress",
        dueDate: "Sep 15, 2026",
        teamMembers: [
          { name: "Michael Scott", avatar: "MS" },
          { name: "David Kim", avatar: "DK" },
        ],
        completedTasks: 18,
        totalTasks: 40,
        tag: "Next.js",
        category: "Frontend",
        priority: "Medium",
      },
      {
        id: 5,
        name: "Automated CI/CD Pipeline",
        description: "GitHub Actions workflow setup with Pest test runner and zero-downtime deployment.",
        progress: 30,
        status: "In Progress",
        dueDate: "Sep 30, 2026",
        teamMembers: [{ name: "David Kim", avatar: "DK" }],
        completedTasks: 6,
        totalTasks: 20,
        tag: "DevOps",
        category: "Infra",
        priority: "Low",
      },
      {
        id: 6,
        name: "Real-time Notification Engine",
        description: "WebSockets & Redis pub/sub integration for instant task deadline triggers.",
        progress: 15,
        status: "On Hold",
        dueDate: "Oct 10, 2026",
        teamMembers: [{ name: "Alex Morgan", avatar: "AM" }],
        completedTasks: 3,
        totalTasks: 22,
        tag: "Redis + Laravel",
        category: "Backend",
        priority: "Low",
      },
    ]
  );

  // New Project Form State
  const [newProject, setNewProject] = useState({
    name: "",
    description: "",
    tag: "React",
    dueDate: "Sep 20, 2026",
    priority: "Medium",
  });

  const handleCreateProject = (e) => {
    e.preventDefault();
    if (!newProject.name.trim()) return;

    const projectObj = {
      id: Date.now(),
      name: newProject.name,
      description: newProject.description || "Newly created project space.",
      progress: 0,
      status: "In Progress",
      dueDate: newProject.dueDate,
      teamMembers: [{ name: "Current User", avatar: "CU" }],
      completedTasks: 0,
      totalTasks: 10,
      tag: newProject.tag,
      category: "Development",
      priority: newProject.priority,
    };

    setProjectsList([projectObj, ...projectsList]);
    setNewProject({ name: "", description: "", tag: "React", dueDate: "Sep 20, 2026", priority: "Medium" });
    setShowAddModal(false);
  };

  const filteredProjects = projectsList.filter((p) => {
    const matchesSearch = p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                          p.description.toLowerCase().includes(searchQuery.toLowerCase()) ||
                          p.tag.toLowerCase().includes(searchQuery.toLowerCase());
    
    if (filterStatus === "in_progress") return matchesSearch && (p.status === "In Progress" || p.status === "Near Completion");
    if (filterStatus === "completed") return matchesSearch && p.status === "Completed";
    if (filterStatus === "on_hold") return matchesSearch && p.status === "On Hold";
    return matchesSearch;
  });

  const headerActions = (
    <Button
      variant="gradient"
      size="sm"
      onClick={() => setShowAddModal(true)}
      className="hidden sm:flex items-center gap-1.5 shadow-indigo-500/25"
    >
      <Plus className="h-4 w-4" /> New Project
    </Button>
  );

  return (
    <DashboardLayout title="Projects Workspace" activeItem="projects" actions={headerActions}>
      <div className="space-y-8">
        {/* Header Banner */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-indigo-950/60 via-slate-900/80 to-slate-900/60 p-6 rounded-2xl border border-indigo-500/20 shadow-2xl relative overflow-hidden">
          <div className="space-y-1 z-10">
            <div className="flex items-center gap-2">
              <Badge variant="default" className="gap-1 bg-indigo-500/20 text-indigo-300 border-indigo-500/30">
                <FolderKanban className="h-3.5 w-3.5 text-indigo-400" /> Projects Hub
              </Badge>
              <span className="text-xs text-slate-400">{filteredProjects.length} projects total</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
              Projects & Workspaces
            </h1>
            <p className="text-sm text-slate-400 max-w-xl">
              Organize, track milestones, and manage team output across active project spaces.
            </p>
          </div>

          <div className="flex items-center gap-3 z-10">
            <Button
              variant="gradient"
              className="gap-2 shadow-indigo-500/25"
              onClick={() => setShowAddModal(true)}
            >
              <Plus className="h-4 w-4" /> Create Project
            </Button>
          </div>
        </div>

        {/* Quick KPI Overview */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card className="border-slate-800/80">
            <CardHeader className="pb-2">
              <CardDescription className="text-xs text-slate-400 font-medium">
                Active Workspaces
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                {projectsList.filter((p) => p.status !== "Completed").length}
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <span className="text-xs text-indigo-400 font-medium">In active development</span>
            </CardContent>
          </Card>

          <Card className="border-slate-800/80">
            <CardHeader className="pb-2">
              <CardDescription className="text-xs text-slate-400 font-medium">
                Completed Projects
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-emerald-400 pt-1">
                {projectsList.filter((p) => p.status === "Completed").length}
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <span className="text-xs text-slate-400">100% Milestone achieved</span>
            </CardContent>
          </Card>

          <Card className="border-slate-800/80">
            <CardHeader className="pb-2">
              <CardDescription className="text-xs text-slate-400 font-medium">
                Total Team Members
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                18
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <span className="text-xs text-purple-400 font-medium">Across 6 project pods</span>
            </CardContent>
          </Card>

          <Card className="border-slate-800/80">
            <CardHeader className="pb-2">
              <CardDescription className="text-xs text-slate-400 font-medium">
                Average Completion
              </CardDescription>
              <CardTitle className="text-3xl font-extrabold text-white pt-1">
                {Math.round(projectsList.reduce((acc, p) => acc + p.progress, 0) / projectsList.length)}%
              </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
              <Progress
                value={Math.round(projectsList.reduce((acc, p) => acc + p.progress, 0) / projectsList.length)}
                className="h-1.5 mt-1"
              />
            </CardContent>
          </Card>
        </div>

        {/* Filter Bar & Controls */}
        <div className="flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-900/60 p-3 rounded-xl border border-slate-800">
          {/* Status Tabs */}
          <div className="flex items-center gap-1 bg-slate-950 p-1 rounded-lg border border-slate-800 text-xs w-full sm:w-auto">
            {[
              { key: "all", label: "All Projects" },
              { key: "in_progress", label: "In Progress" },
              { key: "completed", label: "Completed" },
              { key: "on_hold", label: "On Hold" },
            ].map((tab) => (
              <button
                key={tab.key}
                onClick={() => setFilterStatus(tab.key)}
                className={`px-3 py-1.5 rounded-md font-medium transition-all ${
                  filterStatus === tab.key
                    ? "bg-indigo-600 text-white shadow-md shadow-indigo-600/20"
                    : "text-slate-400 hover:text-slate-200 hover:bg-slate-900"
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>

          {/* Search & View Mode */}
          <div className="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <div className="relative w-full sm:w-56">
              <Search className="absolute left-3 top-2.5 h-3.5 w-3.5 text-slate-400" />
              <input
                type="text"
                placeholder="Search projects..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-slate-950 border border-slate-800 rounded-lg pl-8 pr-3 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500"
              />
            </div>

            <div className="flex items-center bg-slate-950 p-1 rounded-lg border border-slate-800">
              <button
                onClick={() => setViewMode("grid")}
                className={`p-1.5 rounded ${viewMode === "grid" ? "bg-slate-800 text-white" : "text-slate-400"}`}
              >
                <Grid className="h-3.5 w-3.5" />
              </button>
              <button
                onClick={() => setViewMode("table")}
                className={`p-1.5 rounded ${viewMode === "table" ? "bg-slate-800 text-white" : "text-slate-400"}`}
              >
                <List className="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>

        {/* Modal for Creating New Project */}
        {showAddModal && (
          <div className="p-6 rounded-xl bg-slate-900 border border-indigo-500/30 space-y-4 shadow-2xl relative">
            <div className="flex items-center justify-between">
              <h3 className="text-lg font-bold text-white flex items-center gap-2">
                <Plus className="h-5 w-5 text-indigo-400" /> Create New Project Workspace
              </h3>
              <Button variant="ghost" size="sm" onClick={() => setShowAddModal(false)}>
                Cancel
              </Button>
            </div>

            <form onSubmit={handleCreateProject} className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="text-xs font-semibold text-slate-300 block mb-1">Project Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. WorkHub AI Assistant"
                  value={newProject.name}
                  onChange={(e) => setNewProject({ ...newProject, name: e.target.value })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500"
                />
              </div>

              <div>
                <label className="text-xs font-semibold text-slate-300 block mb-1">Tech Stack Tag</label>
                <input
                  type="text"
                  placeholder="e.g. Vue.js, Node.js"
                  value={newProject.tag}
                  onChange={(e) => setNewProject({ ...newProject, tag: e.target.value })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500"
                />
              </div>

              <div className="md:col-span-2">
                <label className="text-xs font-semibold text-slate-300 block mb-1">Description</label>
                <textarea
                  rows={2}
                  placeholder="Short outline of the project objective..."
                  value={newProject.description}
                  onChange={(e) => setNewProject({ ...newProject, description: e.target.value })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500"
                />
              </div>

              <div className="md:col-span-2 flex justify-end gap-2">
                <Button type="button" variant="outline" size="sm" onClick={() => setShowAddModal(false)}>
                  Cancel
                </Button>
                <Button type="submit" variant="gradient" size="sm">
                  Create Workspace
                </Button>
              </div>
            </form>
          </div>
        )}

        {/* Projects Listing (Grid View or Table View) */}
        {viewMode === "grid" ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredProjects.map((prj) => (
              <Card
                key={prj.id}
                className="border-slate-800/80 hover:border-indigo-500/40 transition-all flex flex-col justify-between group"
              >
                <CardHeader className="pb-3">
                  <div className="flex items-start justify-between gap-2">
                    <div className="space-y-1">
                      <div className="flex items-center gap-2">
                        <span className="px-2 py-0.5 text-[10px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 rounded-md">
                          {prj.tag}
                        </span>
                        <span className="text-[10px] text-slate-500 font-mono">
                          {prj.category}
                        </span>
                      </div>
                      <CardTitle className="text-lg font-bold text-white group-hover:text-indigo-300 transition-colors pt-1">
                        {prj.name}
                      </CardTitle>
                    </div>

                    <Badge
                      variant={
                        prj.progress === 100
                          ? "success"
                          : prj.status === "On Hold"
                          ? "outline"
                          : "default"
                      }
                    >
                      {prj.status}
                    </Badge>
                  </div>
                  <CardDescription className="text-xs text-slate-400 line-clamp-2 pt-2">
                    {prj.description}
                  </CardDescription>
                </CardHeader>

                <CardContent className="space-y-4 pt-0">
                  {/* Progress */}
                  <div className="space-y-1.5">
                    <div className="flex items-center justify-between text-xs font-medium">
                      <span className="text-slate-400">Milestone Progress</span>
                      <span className="text-slate-200 font-mono">{prj.progress}%</span>
                    </div>
                    <Progress value={prj.progress} className="h-2" />
                  </div>

                  {/* Meta details */}
                  <div className="flex items-center justify-between pt-2 text-xs text-slate-400 border-t border-slate-800/60">
                    <div className="flex items-center gap-1 text-[11px]">
                      <Calendar className="h-3.5 w-3.5 text-slate-500" />
                      <span>{prj.dueDate}</span>
                    </div>

                    <div className="flex items-center gap-1 font-mono text-[11px]">
                      <CheckCircle2 className="h-3.5 w-3.5 text-emerald-400" />
                      <span>{prj.completedTasks}/{prj.totalTasks} tasks</span>
                    </div>
                  </div>

                  {/* Team Avatars */}
                  <div className="flex items-center justify-between pt-2">
                    <div className="flex -space-x-2 overflow-hidden">
                      {prj.teamMembers.map((m, idx) => (
                        <Avatar key={idx} className="h-7 w-7 ring-2 ring-slate-950">
                          <AvatarFallback className="bg-slate-800 text-slate-300 text-[10px] font-bold">
                            {m.avatar}
                          </AvatarFallback>
                        </Avatar>
                      ))}
                    </div>

                    <Button variant="ghost" size="sm" className="h-7 text-xs text-indigo-400 hover:text-indigo-300">
                      Open <ChevronRight className="h-3.5 w-3.5 ml-1" />
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          /* Table View Mode */
          <Card className="border-slate-800/80">
            <CardContent className="p-0">
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead>
                    <tr className="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase tracking-wider font-semibold">
                      <th className="py-3 px-4">Project</th>
                      <th className="py-3 px-4">Tag</th>
                      <th className="py-3 px-4">Status</th>
                      <th className="py-3 px-4">Due Date</th>
                      <th className="py-3 px-4 w-44">Progress</th>
                      <th className="py-3 px-4">Action</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800/60">
                    {filteredProjects.map((prj) => (
                      <tr key={prj.id} className="hover:bg-slate-900/50 transition-colors">
                        <td className="py-3 px-4">
                          <div>
                            <div className="font-semibold text-slate-100">{prj.name}</div>
                            <div className="text-[11px] text-slate-500 truncate max-w-xs">{prj.description}</div>
                          </div>
                        </td>
                        <td className="py-3 px-4">
                          <span className="px-2 py-0.5 text-[10px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 rounded">
                            {prj.tag}
                          </span>
                        </td>
                        <td className="py-3 px-4">
                          <Badge variant={prj.progress === 100 ? "success" : "default"}>
                            {prj.status}
                          </Badge>
                        </td>
                        <td className="py-3 px-4 text-slate-400 font-mono">{prj.dueDate}</td>
                        <td className="py-3 px-4">
                          <div className="space-y-1">
                            <div className="flex justify-between text-[10px]">
                              <span className="text-slate-400">{prj.progress}%</span>
                            </div>
                            <Progress value={prj.progress} className="h-1.5" />
                          </div>
                        </td>
                        <td className="py-3 px-4">
                          <Button variant="ghost" size="sm" className="h-7 text-xs text-indigo-400">
                            Open
                          </Button>
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
