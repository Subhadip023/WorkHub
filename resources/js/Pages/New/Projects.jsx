import React, { useState } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  FolderKanban,
  Plus,
  Search,
  Grid,
  List,
  CheckCircle2,
  Clock,
  ChevronRight,
  Sparkles,
  Tag
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
import { Progress } from "@/Components/ui/progress";

export default function Projects({ initial_projects }) {
  const [viewMode, setViewMode] = useState("grid"); // 'grid' | 'table'
  const [statusFilter, setStatusFilter] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [showCreateModal, setShowCreateModal] = useState(false);

  const [projectsList, setProjectsList] = useState(
    initial_projects || [
      {
        id: 1,
        name: "WorkHub Mobile App",
        description: "Cross-platform mobile workspace manager for iOS and Android.",
        progress: 78,
        status: "In Progress",
        dueDate: "Aug 28, 2026",
        teamMembers: [
          { name: "Alex Morgan", avatar: "AM" },
          { name: "Sarah Chen", avatar: "SC" },
          { name: "David Kim", avatar: "DK" },
        ],
        completedTasks: 34,
        totalTasks: 42,
        tag: "React Native",
        category: "Mobile",
        priority: "High",
      },
      {
        id: 2,
        name: "Inertia.js Migration",
        description: "Migrating legacy Blade components to Inertia.js React SPA views.",
        progress: 92,
        status: "Near Completion",
        dueDate: "Aug 22, 2026",
        teamMembers: [
          { name: "Michael Scott", avatar: "MS" },
          { name: "Alex Morgan", avatar: "AM" },
        ],
        completedTasks: 22,
        totalTasks: 24,
        tag: "Laravel + React",
        category: "Core",
        priority: "High",
      },
      {
        id: 3,
        name: "shadcn/ui Design System",
        description: "Implementing consistent design tokens and accessible UI primitives.",
        progress: 100,
        status: "Completed",
        dueDate: "Aug 18, 2026",
        teamMembers: [
          { name: "Sarah Chen", avatar: "SC" },
          { name: "Emma Watson", avatar: "EW" },
        ],
        completedTasks: 18,
        totalTasks: 18,
        tag: "Tailwind CSS",
        category: "Design",
        priority: "Medium",
      },
      {
        id: 4,
        name: "Customer Portal v2",
        description: "Self-service client dashboard for subscription and task tracking.",
        progress: 45,
        status: "In Progress",
        dueDate: "Sep 15, 2026",
        teamMembers: [
          { name: "David Kim", avatar: "DK" },
          { name: "Sarah Chen", avatar: "SC" },
        ],
        completedTasks: 12,
        totalTasks: 28,
        tag: "Next.js",
        category: "Frontend",
        priority: "Medium",
      },
      {
        id: 5,
        name: "Automated CI/CD Pipeline",
        description: "Zero-downtime deployment workflows with GitHub Actions & Docker.",
        progress: 30,
        status: "In Progress",
        dueDate: "Sep 30, 2026",
        teamMembers: [
          { name: "David Kim", avatar: "DK" },
        ],
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
        teamMembers: [
          { name: "Alex Morgan", avatar: "AM" },
        ],
        completedTasks: 3,
        totalTasks: 22,
        tag: "Redis + Laravel",
        category: "Backend",
        priority: "Low",
      },
    ]
  );

  const [newProject, setNewProject] = useState({
    name: "",
    description: "",
    tag: "React",
    category: "Frontend",
    priority: "Medium",
  });

  const handleCreateProject = (e) => {
    e.preventDefault();
    if (!newProject.name.trim()) return;

    const projectObj = {
      id: Date.now(),
      name: newProject.name,
      description: newProject.description || "Project workspace item.",
      progress: 0,
      status: "In Progress",
      dueDate: "Sep 30, 2026",
      teamMembers: [{ name: "Current User", avatar: "CU" }],
      completedTasks: 0,
      totalTasks: 10,
      tag: newProject.tag,
      category: newProject.category,
      priority: newProject.priority,
    };

    setProjectsList([projectObj, ...projectsList]);
    setNewProject({ name: "", description: "", tag: "React", category: "Frontend", priority: "Medium" });
    setShowCreateModal(false);
  };

  const filteredProjects = projectsList.filter((p) => {
    const matchesSearch =
      p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.tag.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.category.toLowerCase().includes(searchQuery.toLowerCase());

    if (statusFilter === "active") return matchesSearch && p.status === "In Progress";
    if (statusFilter === "completed") return matchesSearch && p.status === "Completed";
    if (statusFilter === "high_priority") return matchesSearch && p.priority === "High";
    return matchesSearch;
  });

  const headerActions = (
    <Button
      size="sm"
      onClick={() => setShowCreateModal(true)}
      className="bg-white text-black hover:bg-neutral-200 font-semibold text-sm gap-2 transition-all rounded-xl h-9 px-3.5"
    >
      <Plus className="h-4 w-4 text-black" /> Create Project
    </Button>
  );

  return (
    <DashboardLayout title="Projects Workspace" activeItem="projects" actions={headerActions}>
      <div className="space-y-6">
        {/* Header Bar */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-neutral-800 bg-neutral-900/90 p-6 rounded-xl shadow-xl">
          <div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
              Projects & Workspaces <FolderKanban className="h-6 w-6 text-neutral-300" />
            </h1>
            <p className="text-sm sm:text-base text-neutral-400 font-mono mt-1">
              Manage workspace initiatives, milestone progress, and team assignments
            </p>
          </div>

          <div className="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <div className="relative w-full sm:w-64">
              <Search className="absolute left-3 top-2.5 h-4 w-4 text-neutral-400" />
              <input
                type="text"
                placeholder="Search projects..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-black border border-neutral-800 rounded-lg pl-9 pr-3 py-2 text-xs sm:text-sm text-neutral-200 placeholder-neutral-500 focus:outline-none focus:border-neutral-700 font-mono"
              />
            </div>

            <div className="flex items-center bg-black p-1 rounded-lg border border-neutral-800 text-xs">
              <button
                onClick={() => setViewMode("grid")}
                className={`p-2 rounded-md transition-colors ${viewMode === "grid" ? "bg-neutral-800 text-white" : "text-neutral-400"}`}
                title="Grid View"
              >
                <Grid className="h-4 w-4" />
              </button>
              <button
                onClick={() => setViewMode("table")}
                className={`p-2 rounded-md transition-colors ${viewMode === "table" ? "bg-neutral-800 text-white" : "text-neutral-400"}`}
                title="Table View"
              >
                <List className="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        {/* Modal Form for Creating Project */}
        {showCreateModal && (
          <div className="p-6 rounded-xl bg-neutral-900 border border-neutral-800 space-y-4 shadow-2xl">
            <div className="flex items-center justify-between pb-2.5 border-b border-neutral-800">
              <h3 className="text-sm font-mono font-bold uppercase tracking-wider text-white">
                New Project Initiative
              </h3>
              <Button variant="ghost" size="sm" onClick={() => setShowCreateModal(false)} className="text-xs sm:text-sm text-neutral-400">
                Cancel
              </Button>
            </div>

            <form onSubmit={handleCreateProject} className="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs sm:text-sm">
              <div className="md:col-span-2">
                <label className="font-mono text-neutral-400 block mb-1">Project Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. WorkHub Core Migration"
                  value={newProject.name}
                  onChange={(e) => setNewProject({ ...newProject, name: e.target.value })}
                  className="w-full bg-black border border-neutral-800 rounded-lg p-3 text-neutral-200 focus:outline-none focus:border-neutral-700"
                />
              </div>

              <div className="md:col-span-2">
                <label className="font-mono text-neutral-400 block mb-1">Description</label>
                <input
                  type="text"
                  placeholder="Brief summary of goals..."
                  value={newProject.description}
                  onChange={(e) => setNewProject({ ...newProject, description: e.target.value })}
                  className="w-full bg-black border border-neutral-800 rounded-lg p-3 text-neutral-200 focus:outline-none focus:border-neutral-700"
                />
              </div>

              <div>
                <label className="font-mono text-neutral-400 block mb-1">Tag Stack</label>
                <input
                  type="text"
                  placeholder="e.g. Laravel + React"
                  value={newProject.tag}
                  onChange={(e) => setNewProject({ ...newProject, tag: e.target.value })}
                  className="w-full bg-black border border-neutral-800 rounded-lg p-3 text-neutral-200 focus:outline-none focus:border-neutral-700"
                />
              </div>

              <div>
                <label className="font-mono text-neutral-400 block mb-1">Priority</label>
                <select
                  value={newProject.priority}
                  onChange={(e) => setNewProject({ ...newProject, priority: e.target.value })}
                  className="w-full bg-black border border-neutral-800 rounded-lg p-3 text-neutral-200 focus:outline-none focus:border-neutral-700 font-mono"
                >
                  <option value="High">High</option>
                  <option value="Medium">Medium</option>
                  <option value="Low">Low</option>
                </select>
              </div>

              <div className="md:col-span-2 flex justify-end gap-2.5 pt-2">
                <Button type="button" variant="outline" size="sm" onClick={() => setShowCreateModal(false)} className="text-sm h-9">
                  Cancel
                </Button>
                <Button type="submit" size="sm" className="bg-white text-black hover:bg-neutral-200 font-semibold text-sm h-9 px-4">
                  Save Project
                </Button>
              </div>
            </form>
          </div>
        )}

        {/* View Mode Rendering */}
        {viewMode === "grid" ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            {filteredProjects.map((p) => (
              <div
                key={p.id}
                className="border border-neutral-800 bg-neutral-900/90 p-6 rounded-xl space-y-4 hover:border-neutral-700 transition-all flex flex-col justify-between shadow-xl"
              >
                <div className="space-y-2.5">
                  <div className="flex items-center justify-between">
                    <span className="font-mono text-xs bg-black text-neutral-300 px-2.5 py-0.5 rounded border border-neutral-800">
                      [{p.tag}]
                    </span>
                    <span
                      className={`px-3 py-0.5 rounded-full text-xs font-mono font-semibold ${
                        p.status === "Completed"
                          ? "bg-emerald-950/40 text-emerald-400 border border-emerald-800/40"
                          : p.status === "Near Completion"
                          ? "bg-emerald-950/40 text-emerald-400 border border-emerald-800/40"
                          : "bg-amber-950/40 text-amber-300 border border-amber-800/40"
                      }`}
                    >
                      {p.status}
                    </span>
                  </div>

                  <h3 className="font-bold text-base sm:text-lg text-white">{p.name}</h3>
                  <p className="text-xs sm:text-sm text-neutral-300 leading-relaxed line-clamp-2">
                    {p.description}
                  </p>
                </div>

                <div className="space-y-3.5 pt-2">
                  <div className="space-y-1.5">
                    <div className="flex items-center justify-between text-xs font-mono">
                      <span className="text-neutral-400">Progress</span>
                      <span className="text-neutral-200 font-semibold">{p.progress}%</span>
                    </div>
                    <div className="h-2 w-full bg-black rounded-full overflow-hidden border border-neutral-800">
                      <div
                        className="h-full bg-gradient-to-r from-neutral-200 to-neutral-400 rounded-full transition-all duration-500"
                        style={{ width: `${p.progress}%` }}
                      ></div>
                    </div>
                  </div>

                  <div className="flex items-center justify-between border-t border-neutral-800/80 pt-3 text-xs font-mono text-neutral-400">
                    <div className="flex items-center gap-1.5">
                      <Clock className="h-4 w-4 text-neutral-500" />
                      <span>{p.dueDate}</span>
                    </div>

                    <div className="flex items-center gap-1">
                      {p.teamMembers.map((m, idx) => (
                        <Avatar key={idx} className="h-6 w-6 border border-neutral-700">
                          <AvatarFallback className="bg-neutral-800 text-neutral-300 text-[8px] font-bold">
                            {m.avatar}
                          </AvatarFallback>
                        </Avatar>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          /* Table View */
          <div className="border border-neutral-800 bg-neutral-900/90 rounded-xl overflow-hidden shadow-2xl">
            <table className="w-full text-left text-xs sm:text-sm font-sans">
              <thead>
                <tr className="border-b border-neutral-800 bg-black/60 text-neutral-400 font-mono text-xs uppercase tracking-wider">
                  <th className="py-3.5 px-4">Project</th>
                  <th className="py-3.5 px-4">Tag Stack</th>
                  <th className="py-3.5 px-4">Status</th>
                  <th className="py-3.5 px-4">Progress</th>
                  <th className="py-3.5 px-4">Due Date</th>
                  <th className="py-3.5 px-4">Tasks</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-neutral-800/60">
                {filteredProjects.map((p) => (
                  <tr key={p.id} className="hover:bg-neutral-800/50 transition-colors duration-150">
                    <td className="py-3.5 px-4 font-semibold text-white text-xs sm:text-sm">{p.name}</td>
                    <td className="py-3.5 px-4">
                      <span className="font-mono text-xs bg-black text-neutral-300 px-2.5 py-0.5 rounded border border-neutral-800">
                        [{p.tag}]
                      </span>
                    </td>
                    <td className="py-3.5 px-4">
                      <span
                        className={`px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold ${
                          p.status === "Completed"
                            ? "bg-emerald-950/40 text-emerald-400 border border-emerald-800/40"
                            : "bg-amber-950/40 text-amber-300 border border-amber-800/40"
                        }`}
                      >
                        {p.status}
                      </span>
                    </td>
                    <td className="py-3.5 px-4 font-mono text-neutral-200">{p.progress}%</td>
                    <td className="py-3.5 px-4 font-mono text-neutral-400">{p.dueDate}</td>
                    <td className="py-3.5 px-4 font-mono text-neutral-200">
                      {p.completedTasks}/{p.totalTasks}
                    </td>
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
