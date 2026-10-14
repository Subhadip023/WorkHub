import React, { useState } from "react";
import { Link, router, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  Trash2,
  RotateCcw,
  AlertTriangle,
  FolderKanban,
  CheckSquare,
  Building2,
  Clock,
  Search,
  Check,
  AlertCircle,
  X,
  ShieldAlert,
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";

export default function Trash({
  trashed_tasks = [],
  trashed_projects = [],
  trashed_companies = [],
  stats = null,
}) {
  const { flash = {} } = usePage().props;

  const [activeTab, setActiveTab] = useState("all"); // 'all' | 'tasks' | 'projects' | 'companies'
  const [searchQuery, setSearchQuery] = useState("");

  // Confirmation modal
  const [confirmDelete, setConfirmDelete] = useState(null); // { type, id, name }

  const handleRestore = (type, id) => {
    router.post(`/v2/trash/${type}/${id}/restore`, {}, { preserveScroll: true });
  };

  const handleForceDelete = () => {
    if (!confirmDelete) return;
    router.delete(`/v2/trash/${confirmDelete.type}/${confirmDelete.id}/force`, {
      preserveScroll: true,
      onSuccess: () => setConfirmDelete(null),
    });
  };

  // Filter items
  const filteredTasks = trashed_tasks.filter((t) =>
    !searchQuery || t.title.toLowerCase().includes(searchQuery.toLowerCase())
  );
  const filteredProjects = trashed_projects.filter((p) =>
    !searchQuery || p.name.toLowerCase().includes(searchQuery.toLowerCase())
  );
  const filteredCompanies = trashed_companies.filter((c) =>
    !searchQuery || c.name.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const totalFiltered = filteredTasks.length + filteredProjects.length + filteredCompanies.length;

  return (
    <DashboardLayout title="Recycle Bin" activeItem="trash">
      <div className="space-y-6 pb-12">
        {/* Flash Notifications */}
        {flash.success && (
          <div className="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center gap-3 animate-in fade-in duration-200">
            <Check className="w-5 h-5 shrink-0" />
            <p className="text-sm font-medium">{flash.success}</p>
          </div>
        )}
        {flash.error && (
          <div className="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center gap-3 animate-in fade-in duration-200">
            <AlertCircle className="w-5 h-5 shrink-0" />
            <p className="text-sm font-medium">{flash.error}</p>
          </div>
        )}

        {/* Top Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-zinc-800">
          <div>
            <div className="flex items-center gap-2.5">
              <div className="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                <Trash2 className="w-5 h-5" />
              </div>
              <h1 className="text-2xl font-bold tracking-tight text-zinc-100">
                Recycle Bin
              </h1>
              <Badge variant="outline" className="border-amber-500/30 text-amber-400 bg-amber-500/5 font-mono text-xs">
                V2 Recovery
              </Badge>
            </div>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1">
              Recover soft-deleted tasks, projects, and organizations, or purge them permanently.
            </p>
          </div>
        </div>

        {/* KPI Hero Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3.5">
          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Total Trashed
              </span>
              <div className="p-1.5 rounded-lg bg-zinc-800 text-zinc-300">
                <Trash2 className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.total_trashed ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Recoverable entities
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Tasks
              </span>
              <div className="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                <CheckSquare className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.tasks_count ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Deleted tasks
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Projects
              </span>
              <div className="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400">
                <FolderKanban className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.projects_count ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Deleted projects
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Organizations
              </span>
              <div className="p-1.5 rounded-lg bg-rose-500/10 text-rose-400">
                <Building2 className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.companies_count ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Deleted workspaces
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Search & Tabs Toolbar */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-2xl bg-zinc-900/60 border border-zinc-800/80">
          <div className="relative flex-1 max-w-md">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" />
            <input
              type="text"
              placeholder="Search recycle bin items..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full bg-zinc-950 border border-zinc-800 rounded-xl pl-9 pr-3.5 py-2 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-amber-500/60 transition-colors"
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery("")}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300"
              >
                <X className="w-3.5 h-3.5" />
              </button>
            )}
          </div>

          <div className="flex items-center gap-1 p-1 bg-zinc-950 border border-zinc-800 rounded-xl shrink-0 overflow-x-auto">
            {[
              { id: "all", label: `All (${totalFiltered})` },
              { id: "tasks", label: `Tasks (${filteredTasks.length})` },
              { id: "projects", label: `Projects (${filteredProjects.length})` },
              { id: "companies", label: `Orgs (${filteredCompanies.length})` },
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`px-3 py-1 rounded-lg text-xs font-medium transition-all ${
                  activeTab === tab.id
                    ? "bg-amber-500/15 text-amber-400 border border-amber-500/30 font-semibold"
                    : "text-zinc-400 hover:text-zinc-200"
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>
        </div>

        {/* Empty state */}
        {totalFiltered === 0 ? (
          <div className="text-center py-16 px-4 border border-dashed border-zinc-800 rounded-2xl bg-zinc-900/30">
            <div className="w-14 h-14 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mx-auto text-zinc-400 mb-4 shadow-inner">
              <RotateCcw className="w-7 h-7" />
            </div>
            <h3 className="text-lg font-bold text-zinc-200">Recycle Bin is empty</h3>
            <p className="text-xs sm:text-sm text-zinc-400 max-w-md mx-auto mt-1">
              No soft-deleted items found. Deleted tasks, projects, and organizations will appear here.
            </p>
          </div>
        ) : (
          <div className="space-y-4">
            {/* TASKS */}
            {(activeTab === "all" || activeTab === "tasks") && filteredTasks.length > 0 && (
              <div className="bg-zinc-900/80 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
                <div className="p-3.5 bg-zinc-950/70 border-b border-zinc-800 flex items-center gap-2">
                  <CheckSquare className="w-4 h-4 text-emerald-400" />
                  <span className="font-bold text-xs uppercase tracking-wider font-mono text-zinc-300">
                    Deleted Tasks ({filteredTasks.length})
                  </span>
                </div>
                <div className="divide-y divide-zinc-800/60">
                  {filteredTasks.map((t) => (
                    <div key={t.id} className="p-4 flex items-center justify-between gap-4 hover:bg-zinc-800/30 transition-colors">
                      <div className="min-w-0 flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <span className="font-mono text-xs font-bold text-zinc-500">{t.code}</span>
                          <span className="font-semibold text-sm text-zinc-200 truncate">{t.title}</span>
                          <span className="text-[11px] font-mono px-2 py-0.5 rounded bg-zinc-950 border border-zinc-800 text-zinc-400">
                            {t.project_name}
                          </span>
                        </div>
                        <div className="text-[11px] text-zinc-500 font-mono">
                          Deleted {t.deleted_at_human} ({t.deleted_at})
                        </div>
                      </div>

                      <div className="flex items-center gap-2 shrink-0">
                        <Button
                          size="sm"
                          onClick={() => handleRestore("tasks", t.id)}
                          className="h-8 text-xs bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold gap-1"
                        >
                          <RotateCcw className="w-3.5 h-3.5" />
                          <span>Restore</span>
                        </Button>
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={() => setConfirmDelete({ type: "tasks", id: t.id, name: t.title })}
                          className="h-8 text-xs text-rose-400 hover:bg-rose-500/10"
                        >
                          Purge
                        </Button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* PROJECTS */}
            {(activeTab === "all" || activeTab === "projects") && filteredProjects.length > 0 && (
              <div className="bg-zinc-900/80 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
                <div className="p-3.5 bg-zinc-950/70 border-b border-zinc-800 flex items-center gap-2">
                  <FolderKanban className="w-4 h-4 text-indigo-400" />
                  <span className="font-bold text-xs uppercase tracking-wider font-mono text-zinc-300">
                    Deleted Projects ({filteredProjects.length})
                  </span>
                </div>
                <div className="divide-y divide-zinc-800/60">
                  {filteredProjects.map((p) => (
                    <div key={p.id} className="p-4 flex items-center justify-between gap-4 hover:bg-zinc-800/30 transition-colors">
                      <div className="min-w-0 flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <span className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: p.theme }} />
                          <span className="font-semibold text-sm text-zinc-200 truncate">{p.name}</span>
                          <span className="text-[11px] font-mono px-2 py-0.5 rounded bg-zinc-950 border border-zinc-800 text-zinc-400">
                            {p.company_name}
                          </span>
                        </div>
                        <div className="text-[11px] text-zinc-500 font-mono">
                          Deleted {p.deleted_at_human} ({p.deleted_at})
                        </div>
                      </div>

                      <div className="flex items-center gap-2 shrink-0">
                        <Button
                          size="sm"
                          onClick={() => handleRestore("projects", p.id)}
                          className="h-8 text-xs bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold gap-1"
                        >
                          <RotateCcw className="w-3.5 h-3.5" />
                          <span>Restore</span>
                        </Button>
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={() => setConfirmDelete({ type: "projects", id: p.id, name: p.name })}
                          className="h-8 text-xs text-rose-400 hover:bg-rose-500/10"
                        >
                          Purge
                        </Button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* COMPANIES */}
            {(activeTab === "all" || activeTab === "companies") && filteredCompanies.length > 0 && (
              <div className="bg-zinc-900/80 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
                <div className="p-3.5 bg-zinc-950/70 border-b border-zinc-800 flex items-center gap-2">
                  <Building2 className="w-4 h-4 text-rose-400" />
                  <span className="font-bold text-xs uppercase tracking-wider font-mono text-zinc-300">
                    Deleted Organizations ({filteredCompanies.length})
                  </span>
                </div>
                <div className="divide-y divide-zinc-800/60">
                  {filteredCompanies.map((c) => (
                    <div key={c.id} className="p-4 flex items-center justify-between gap-4 hover:bg-zinc-800/30 transition-colors">
                      <div className="min-w-0 flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <span className="font-semibold text-sm text-zinc-200 truncate">{c.name}</span>
                          <span className="text-[11px] font-mono px-2 py-0.5 rounded bg-zinc-950 border border-zinc-800 text-zinc-400">
                            Code: {c.code}
                          </span>
                        </div>
                        <div className="text-[11px] text-zinc-500 font-mono">
                          Deleted {c.deleted_at_human} ({c.deleted_at})
                        </div>
                      </div>

                      <div className="flex items-center gap-2 shrink-0">
                        <Button
                          size="sm"
                          onClick={() => handleRestore("companies", c.id)}
                          className="h-8 text-xs bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold gap-1"
                        >
                          <RotateCcw className="w-3.5 h-3.5" />
                          <span>Restore</span>
                        </Button>
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={() => setConfirmDelete({ type: "companies", id: c.id, name: c.name })}
                          className="h-8 text-xs text-rose-400 hover:bg-rose-500/10"
                        >
                          Purge
                        </Button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
        )}

        {/* CONFIRM PURGE MODAL */}
        {confirmDelete && (
          <div className="fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div
              className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-200"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mx-auto">
                <Trash2 className="w-6 h-6" />
              </div>
              <div className="text-center space-y-1">
                <h3 className="text-lg font-bold text-zinc-100">Permanently Delete Item?</h3>
                <p className="text-xs text-zinc-400 leading-relaxed">
                  Are you sure you want to permanently delete <strong className="text-zinc-200">{confirmDelete.name}</strong>? This action cannot be undone and will purge all associated records.
                </p>
              </div>
              <div className="flex items-center justify-end gap-2.5 pt-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setConfirmDelete(null)}
                  className="border-zinc-800 text-zinc-300"
                >
                  Cancel
                </Button>
                <Button
                  size="sm"
                  onClick={handleForceDelete}
                  className="bg-rose-500 hover:bg-rose-600 text-white font-bold"
                >
                  Confirm Delete
                </Button>
              </div>
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
