import React, { useState, useMemo } from "react";
import { Link, router, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  ShieldCheck,
  Search,
  Filter,
  PlusCircle,
  Edit3,
  Trash2,
  Clock,
  User,
  Activity,
  Layers,
  ArrowRight,
  X,
  FileText,
  Building2,
  CheckSquare,
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";

export default function ActivityLogs({
  activities = [],
  stats = null,
  scope = "mine",
  current_workspace_name = "All Workspaces",
}) {
  const [searchQuery, setSearchQuery] = useState("");
  const [eventFilter, setEventFilter] = useState("all"); // 'all' | 'created' | 'updated' | 'deleted'
  const [currentScope, setCurrentScope] = useState(scope);

  // Filtered activities
  const filteredActivities = useMemo(() => {
    return activities.filter((act) => {
      const q = searchQuery.toLowerCase().trim();
      const matchesSearch =
        !q ||
        act.description.toLowerCase().includes(q) ||
        (act.subject_title && act.subject_title.toLowerCase().includes(q)) ||
        (act.causer?.name && act.causer.name.toLowerCase().includes(q));

      let matchesEvent = true;
      if (eventFilter !== "all") {
        matchesEvent = act.event.toLowerCase() === eventFilter.toLowerCase();
      }

      return matchesSearch && matchesEvent;
    });
  }, [activities, searchQuery, eventFilter]);

  const handleScopeChange = (newScope) => {
    setCurrentScope(newScope);
    router.get("/v2/activity-logs", { scope: newScope }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const getEventIcon = (event) => {
    switch (event) {
      case "created":
        return <PlusCircle className="w-4 h-4 text-emerald-400" />;
      case "updated":
        return <Edit3 className="w-4 h-4 text-blue-400" />;
      case "deleted":
        return <Trash2 className="w-4 h-4 text-rose-400" />;
      default:
        return <Activity className="w-4 h-4 text-zinc-400" />;
    }
  };

  const getEventBadge = (event) => {
    switch (event) {
      case "created":
        return <Badge className="bg-emerald-500/10 border-emerald-500/30 text-emerald-400 text-[10px] font-mono">Created</Badge>;
      case "updated":
        return <Badge className="bg-blue-500/10 border-blue-500/30 text-blue-400 text-[10px] font-mono">Updated</Badge>;
      case "deleted":
        return <Badge className="bg-rose-500/10 border-rose-500/30 text-rose-400 text-[10px] font-mono">Deleted</Badge>;
      default:
        return <Badge variant="outline" className="text-[10px] font-mono text-zinc-400">{event}</Badge>;
    }
  };

  return (
    <DashboardLayout title="Activity Logs" activeItem="activity-logs">
      <div className="space-y-6 pb-12">
        {/* Top Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-zinc-800">
          <div>
            <div className="flex items-center gap-2.5">
              <div className="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                <ShieldCheck className="w-5 h-5" />
              </div>
              <h1 className="text-2xl font-bold tracking-tight text-zinc-100">
                Audit & Activity Logs
              </h1>
              <Badge variant="outline" className="border-emerald-500/30 text-emerald-400 bg-emerald-500/5 font-mono text-xs">
                Audit Stream
              </Badge>
            </div>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1">
              Trace chronological workspace interactions, model changes, and user events.
            </p>
          </div>

          {/* Scope pills */}
          <div className="flex items-center gap-1 p-1 bg-zinc-950 border border-zinc-800 rounded-xl">
            <button
              onClick={() => handleScopeChange("mine")}
              className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                currentScope === "mine"
                  ? "bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-semibold"
                  : "text-zinc-400 hover:text-zinc-200"
              }`}
            >
              My Activity
            </button>
            <button
              onClick={() => handleScopeChange("all")}
              className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                currentScope === "all"
                  ? "bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-semibold"
                  : "text-zinc-400 hover:text-zinc-200"
              }`}
            >
              All Events
            </button>
          </div>
        </div>

        {/* KPI Hero Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3.5">
          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Total Events
              </span>
              <div className="p-1.5 rounded-lg bg-zinc-800 text-zinc-300">
                <Activity className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-zinc-100">
                {stats?.total ?? activities.length}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Recorded actions
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Created
              </span>
              <div className="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                <PlusCircle className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-emerald-400">
                {stats?.created ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                New entities added
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Updated
              </span>
              <div className="p-1.5 rounded-lg bg-blue-500/10 text-blue-400">
                <Edit3 className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-blue-400">
                {stats?.updated ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Modifications & edits
              </p>
            </CardContent>
          </Card>

          <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
            <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
              <span className="text-xs font-mono font-medium text-zinc-400 uppercase tracking-wider">
                Deleted
              </span>
              <div className="p-1.5 rounded-lg bg-rose-500/10 text-rose-400">
                <Trash2 className="w-4 h-4" />
              </div>
            </CardHeader>
            <CardContent className="p-4 pt-0">
              <div className="text-2xl font-bold text-rose-400">
                {stats?.deleted ?? 0}
              </div>
              <p className="text-xs text-zinc-400 mt-1">
                Removed items
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Search & Event Filters */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-2xl bg-zinc-900/60 border border-zinc-800/80">
          <div className="relative flex-1 max-w-md">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" />
            <input
              type="text"
              placeholder="Search logs by action, subject title, or user name..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full bg-zinc-950 border border-zinc-800 rounded-xl pl-9 pr-3.5 py-2 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 transition-colors"
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
              { id: "all", label: "All Events" },
              { id: "created", label: "Created" },
              { id: "updated", label: "Updated" },
              { id: "deleted", label: "Deleted" },
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setEventFilter(tab.id)}
                className={`px-3 py-1 rounded-lg text-xs font-medium transition-all ${
                  eventFilter === tab.id
                    ? "bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-semibold"
                    : "text-zinc-400 hover:text-zinc-200"
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>
        </div>

        {/* Activity Timeline List */}
        {filteredActivities.length === 0 ? (
          <div className="text-center py-16 px-4 border border-dashed border-zinc-800 rounded-2xl bg-zinc-900/30">
            <div className="w-14 h-14 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mx-auto text-zinc-400 mb-4 shadow-inner">
              <ShieldCheck className="w-7 h-7" />
            </div>
            <h3 className="text-lg font-bold text-zinc-200">No activity records found</h3>
            <p className="text-xs sm:text-sm text-zinc-400 max-w-md mx-auto mt-1">
              {searchQuery
                ? `No activity logs match "${searchQuery}". Reset filters to see full history.`
                : "No activities recorded in this scope yet."}
            </p>
          </div>
        ) : (
          <div className="bg-zinc-900/80 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl divide-y divide-zinc-800/60">
            {filteredActivities.map((act) => (
              <div
                key={act.id}
                className="p-4 hover:bg-zinc-800/30 transition-colors flex items-start gap-4"
              >
                {/* Event type icon circle */}
                <div className="mt-1 p-2 rounded-xl bg-zinc-950 border border-zinc-800 shrink-0 shadow-sm">
                  {getEventIcon(act.event)}
                </div>

                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2 mb-1">
                    <span className="font-semibold text-zinc-100 text-sm">
                      {act.description}
                    </span>
                    {getEventBadge(act.event)}
                    <span className="text-[11px] font-mono px-2 py-0.5 rounded bg-zinc-950 border border-zinc-800 text-zinc-400">
                      {act.subject_type}
                    </span>
                  </div>

                  {act.subject_title && (
                    <div className="text-xs text-emerald-400 font-mono mb-1.5 flex items-center gap-1.5">
                      <span className="text-zinc-500">Target:</span>
                      <strong className="text-zinc-200">{act.subject_title}</strong>
                    </div>
                  )}

                  <div className="flex items-center gap-3 text-[11px] text-zinc-500 font-mono">
                    <div className="flex items-center gap-1.5 text-zinc-400">
                      <Avatar className="w-4 h-4 border border-zinc-800">
                        {act.causer?.profile_image ? (
                          <AvatarImage src={act.causer.profile_image} />
                        ) : null}
                        <AvatarFallback className="text-[8px] bg-zinc-800 text-zinc-300">
                          {act.causer?.avatar || "SY"}
                        </AvatarFallback>
                      </Avatar>
                      <span>{act.causer?.name || "System"}</span>
                    </div>
                    <span>•</span>
                    <span>{act.created_at_human}</span>
                    <span>•</span>
                    <span className="text-zinc-600">{act.created_at}</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
