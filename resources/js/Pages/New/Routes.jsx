import React, { useState, useMemo } from "react";
import { Head, Link } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  Route as RouteIcon,
  Compass,
  Search,
  Filter,
  ArrowUpRight,
  ExternalLink,
  Copy,
  Check,
  Layers,
  Shield,
  ShieldCheck,
  Lock,
  Unlock,
  Globe,
  Code,
  Terminal,
  Download,
  RefreshCw,
  SlidersHorizontal,
  Sparkles,
  Zap,
  CheckCircle2,
  AlertCircle,
  Eye,
  Grid3X3,
  List,
  FolderKanban,
  LayoutDashboard,
  CheckSquare,
  FileText,
  Building2,
  Users,
  Bug,
  Settings,
  Activity,
  Database,
  Trash2,
  HelpCircle,
  X,
  ChevronDown,
  ChevronRight,
  Share2
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Input } from "@/Components/ui/input";

export default function RoutesExplorer({ user, routes = [], stats = {}, categories = [] }) {
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedMethod, setSelectedMethod] = useState("ALL");
  const [selectedEngine, setSelectedEngine] = useState("ALL");
  const [selectedAuth, setSelectedAuth] = useState("ALL");
  const [selectedCategory, setSelectedCategory] = useState("ALL");
  const [viewMode, setViewMode] = useState("grouped"); // 'grouped' | 'table' | 'cards'
  const [selectedRoute, setSelectedRoute] = useState(null);
  const [copiedKey, setCopiedKey] = useState(null);
  const [collapsedCategories, setCollapsedCategories] = useState({});

  // Copy helper with visual feedback
  const handleCopy = (text, key) => {
    navigator.clipboard.writeText(text);
    setCopiedKey(key);
    setTimeout(() => {
      setCopiedKey(null);
    }, 2000);
  };

  // Toggle category collapse in grouped view
  const toggleCategoryCollapse = (catName) => {
    setCollapsedCategories((prev) => ({
      ...prev,
      [catName]: !prev[catName],
    }));
  };

  // Reset all filters
  const resetFilters = () => {
    setSearchQuery("");
    setSelectedMethod("ALL");
    setSelectedEngine("ALL");
    setSelectedAuth("ALL");
    setSelectedCategory("ALL");
  };

  // Filtered routes calculation
  const filteredRoutes = useMemo(() => {
    return routes.filter((r) => {
      // Search query filter (URI, Name, Action, Controller, Middleware)
      if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase().trim();
        const matchesUri = r.uri.toLowerCase().includes(q);
        const matchesName = r.name.toLowerCase().includes(q);
        const matchesAction = r.action.toLowerCase().includes(q);
        const matchesFullAction = (r.full_action || "").toLowerCase().includes(q);
        const matchesCategory = r.category.toLowerCase().includes(q);
        const matchesMiddleware = (r.middleware || []).some((m) => m.toLowerCase().includes(q));

        if (!matchesUri && !matchesName && !matchesAction && !matchesFullAction && !matchesCategory && !matchesMiddleware) {
          return false;
        }
      }

      // Method filter
      if (selectedMethod !== "ALL") {
        if (selectedMethod === "PUT/PATCH") {
          if (!r.methods.includes("PUT") && !r.methods.includes("PATCH")) return false;
        } else {
          if (!r.methods.includes(selectedMethod)) return false;
        }
      }

      // Engine filter
      if (selectedEngine !== "ALL") {
        if (selectedEngine === "Inertia React" && r.engine !== "Inertia React") return false;
        if (selectedEngine === "Blade View" && r.engine !== "Blade View") return false;
        if (selectedEngine === "API / JSON" && r.engine !== "API / JSON") return false;
        if (selectedEngine === "System" && r.engine !== "System") return false;
      }

      // Auth filter
      if (selectedAuth === "PROTECTED" && !r.requires_auth) return false;
      if (selectedAuth === "PUBLIC" && r.requires_auth) return false;
      if (selectedAuth === "ADMIN" && !r.is_admin) return false;

      // Category filter
      if (selectedCategory !== "ALL" && r.category !== selectedCategory) {
        return false;
      }

      return true;
    });
  }, [routes, searchQuery, selectedMethod, selectedEngine, selectedAuth, selectedCategory]);

  // Grouped routes by category
  const groupedRoutes = useMemo(() => {
    const groups = {};
    filteredRoutes.forEach((r) => {
      if (!groups[r.category]) {
        groups[r.category] = [];
      }
      groups[r.category].push(r);
    });
    return groups;
  }, [filteredRoutes]);

  // Export all filtered routes as JSON
  const handleExportJSON = () => {
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(filteredRoutes, null, 2));
    const downloadAnchor = document.createElement("a");
    downloadAnchor.setAttribute("href", dataStr);
    downloadAnchor.setAttribute("download", `workhub-routes-${new Date().toISOString().slice(0, 10)}.json`);
    document.body.appendChild(downloadAnchor);
    downloadAnchor.click();
    downloadAnchor.remove();
  };

  // Helper method styling
  const getMethodBadgeClass = (method) => {
    switch (method) {
      case "GET":
        return "bg-emerald-500/15 text-emerald-400 border-emerald-500/30";
      case "POST":
        return "bg-blue-500/15 text-blue-400 border-blue-500/30";
      case "PUT":
      case "PATCH":
        return "bg-amber-500/15 text-amber-400 border-amber-500/30";
      case "DELETE":
        return "bg-rose-500/15 text-rose-400 border-rose-500/30";
      default:
        return "bg-purple-500/15 text-purple-400 border-purple-500/30";
    }
  };

  // Category Icon helper
  const getCategoryIcon = (category) => {
    switch (category) {
      case "Core Suite":
        return <LayoutDashboard className="w-4 h-4 text-emerald-400" />;
      case "Projects":
        return <FolderKanban className="w-4 h-4 text-blue-400" />;
      case "Tasks":
        return <CheckSquare className="w-4 h-4 text-indigo-400" />;
      case "Notes & Docs":
        return <FileText className="w-4 h-4 text-amber-400" />;
      case "Organizations & Teams":
        return <Building2 className="w-4 h-4 text-violet-400" />;
      case "Issues Tracker":
        return <Bug className="w-4 h-4 text-rose-400" />;
      case "Administration":
        return <ShieldCheck className="w-4 h-4 text-yellow-400" />;
      case "System & Logs":
        return <Activity className="w-4 h-4 text-cyan-400" />;
      case "Auth & Profile":
        return <Lock className="w-4 h-4 text-pink-400" />;
      case "API & Integrations":
        return <Terminal className="w-4 h-4 text-teal-400" />;
      default:
        return <Compass className="w-4 h-4 text-zinc-400" />;
    }
  };

  // Header Actions
  const headerActions = (
    <div className="flex items-center gap-2">
      <Button
        variant="outline"
        size="sm"
        onClick={handleExportJSON}
        className="border-zinc-800 bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 gap-1.5 text-xs h-9 px-3"
      >
        <Download className="w-3.5 h-3.5 text-zinc-400" />
        Export JSON
      </Button>
      <Button
        variant="outline"
        size="sm"
        onClick={() => handleCopy(window.location.href, "page-url")}
        className="border-zinc-800 bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 gap-1.5 text-xs h-9 px-3"
      >
        {copiedKey === "page-url" ? (
          <>
            <Check className="w-3.5 h-3.5 text-emerald-400" />
            <span className="text-emerald-400">Copied Link!</span>
          </>
        ) : (
          <>
            <Share2 className="w-3.5 h-3.5 text-zinc-400" />
            Share
          </>
        )}
      </Button>
    </div>
  );

  return (
    <DashboardLayout
      title="All Routes Explorer"
      activeItem="routes"
      user={user}
      headerActions={headerActions}
    >
      <Head title="All Routes - WorkHub" />

      <div className="max-w-7xl mx-auto space-y-6 pb-12">
        {/* Top Hero Banner */}
        <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900/90 via-zinc-900/40 to-zinc-950 border border-zinc-800/80 p-6 md:p-8 backdrop-blur-xl shadow-2xl">
          <div className="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" />
          <div className="absolute bottom-0 left-1/3 -mb-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none" />

          <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div className="space-y-2 max-w-2xl">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono font-medium">
                <Sparkles className="w-3.5 h-3.5" />
                WorkHub Architecture Matrix • Modern UI Engine
              </div>
              <h1 className="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-3">
                <RouteIcon className="w-8 h-8 text-emerald-400" />
                All Routes & Endpoints Explorer
              </h1>
              <p className="text-zinc-400 text-sm md:text-base leading-relaxed">
                Live interactive directory of all <span className="text-zinc-200 font-semibold">{stats.total || routes.length} registered routes</span> across WorkHub, including HTTP verbs, controller bindings, middleware guards, parameter schemas, and Inertia components.
              </p>
            </div>

            {/* Quick Metrics Badges */}
            <div className="flex flex-wrap md:flex-col gap-2 shrink-0">
              <div className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-zinc-900/90 border border-zinc-800 text-xs">
                <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                <span className="text-zinc-400">Total Routes:</span>
                <span className="text-white font-mono font-bold">{stats.total || routes.length}</span>
              </div>
              <div className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-zinc-900/90 border border-zinc-800 text-xs">
                <Zap className="w-3.5 h-3.5 text-indigo-400" />
                <span className="text-zinc-400">Inertia React:</span>
                <span className="text-indigo-300 font-mono font-bold">{stats.inertia_count || 0}</span>
              </div>
              <div className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-zinc-900/90 border border-zinc-800 text-xs">
                <Shield className="w-3.5 h-3.5 text-amber-400" />
                <span className="text-zinc-400">Protected:</span>
                <span className="text-amber-300 font-mono font-bold">{stats.protected_count || 0}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
          {/* Card 1: Total */}
          <Card className="bg-zinc-900/60 border-zinc-800/80 hover:border-zinc-700/80 transition-all p-4">
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-medium text-zinc-400">Total Endpoints</span>
              <div className="p-2 rounded-lg bg-zinc-800/80 text-zinc-300">
                <Compass className="w-4 h-4 text-emerald-400" />
              </div>
            </div>
            <div className="text-2xl font-bold text-white font-mono">{stats.total || routes.length}</div>
            <p className="text-[11px] text-zinc-500 mt-1 flex items-center gap-1">
              <span>{stats.get_count || 0} GET</span> • <span>{stats.mutation_count || 0} Mutations</span>
            </p>
          </Card>

          {/* Card 2: Inertia New Look */}
          <Card className="bg-zinc-900/60 border-zinc-800/80 hover:border-indigo-500/30 transition-all p-4">
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-medium text-zinc-400">Inertia New Look</span>
              <div className="p-2 rounded-lg bg-indigo-500/10 text-indigo-400">
                <Sparkles className="w-4 h-4" />
              </div>
            </div>
            <div className="text-2xl font-bold text-indigo-300 font-mono">{stats.inertia_count || 0}</div>
            <p className="text-[11px] text-zinc-500 mt-1">React + shadcn/ui views</p>
          </Card>

          {/* Card 3: Protected Routes */}
          <Card className="bg-zinc-900/60 border-zinc-800/80 hover:border-amber-500/30 transition-all p-4">
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-medium text-zinc-400">Protected Auth</span>
              <div className="p-2 rounded-lg bg-amber-500/10 text-amber-400">
                <Lock className="w-4 h-4" />
              </div>
            </div>
            <div className="text-2xl font-bold text-amber-300 font-mono">{stats.protected_count || 0}</div>
            <p className="text-[11px] text-zinc-500 mt-1">Auth & Verified session</p>
          </Card>

          {/* Card 4: API & Webhooks */}
          <Card className="bg-zinc-900/60 border-zinc-800/80 hover:border-cyan-500/30 transition-all p-4">
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-medium text-zinc-400">API Endpoints</span>
              <div className="p-2 rounded-lg bg-cyan-500/10 text-cyan-400">
                <Terminal className="w-4 h-4" />
              </div>
            </div>
            <div className="text-2xl font-bold text-cyan-300 font-mono">{stats.api_count || 0}</div>
            <p className="text-[11px] text-zinc-500 mt-1">External Task & REST</p>
          </Card>

          {/* Card 5: Public / Guest */}
          <Card className="bg-zinc-900/60 border-zinc-800/80 hover:border-zinc-700/80 transition-all p-4 col-span-2 sm:col-span-1">
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-medium text-zinc-400">Public & Guest</span>
              <div className="p-2 rounded-lg bg-zinc-800/80 text-zinc-300">
                <Globe className="w-4 h-4 text-zinc-300" />
              </div>
            </div>
            <div className="text-2xl font-bold text-zinc-200 font-mono">{stats.public_count || 0}</div>
            <p className="text-[11px] text-zinc-500 mt-1">Landing, Auth, Demo</p>
          </Card>
        </div>

        {/* Filter Toolbar */}
        <div className="rounded-xl bg-zinc-900/80 border border-zinc-800 p-4 space-y-4 backdrop-blur-md shadow-lg">
          {/* Search bar + View Switcher */}
          <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
              <Input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search route URI (e.g. /tasks), name, controller, action, or middleware..."
                className="pl-10 pr-9 bg-zinc-950 border-zinc-800 focus:border-emerald-500 text-zinc-200 placeholder:text-zinc-500 text-sm rounded-xl h-10 w-full"
              />
              {searchQuery && (
                <button
                  onClick={() => setSearchQuery("")}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-white p-0.5 rounded-full"
                >
                  <X className="w-3.5 h-3.5" />
                </button>
              )}
            </div>

            {/* View Mode Toggle */}
            <div className="flex items-center bg-zinc-950 border border-zinc-800 rounded-xl p-1 shrink-0 self-end sm:self-auto">
              <button
                onClick={() => setViewMode("grouped")}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                  viewMode === "grouped" ? "bg-zinc-800 text-white shadow-sm" : "text-zinc-400 hover:text-zinc-200"
                }`}
                title="Group by Category"
              >
                <Layers className="w-3.5 h-3.5" />
                <span>Grouped</span>
              </button>
              <button
                onClick={() => setViewMode("table")}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                  viewMode === "table" ? "bg-zinc-800 text-white shadow-sm" : "text-zinc-400 hover:text-zinc-200"
                }`}
                title="Developer Table View"
              >
                <List className="w-3.5 h-3.5" />
                <span>Table</span>
              </button>
              <button
                onClick={() => setViewMode("cards")}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                  viewMode === "cards" ? "bg-zinc-800 text-white shadow-sm" : "text-zinc-400 hover:text-zinc-200"
                }`}
                title="Grid Cards View"
              >
                <Grid3X3 className="w-3.5 h-3.5" />
                <span>Cards</span>
              </button>
            </div>
          </div>

          {/* Filter Pills Row */}
          <div className="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-zinc-800/80 text-xs">
            {/* HTTP Method Pills */}
            <div className="flex items-center gap-1.5 overflow-x-auto pb-1 max-w-full">
              <span className="text-zinc-500 font-mono mr-1 uppercase text-[11px]">Method:</span>
              {["ALL", "GET", "POST", "PUT/PATCH", "DELETE"].map((method) => {
                const isActive = selectedMethod === method;
                return (
                  <button
                    key={method}
                    onClick={() => setSelectedMethod(method)}
                    className={`px-2.5 py-1 rounded-lg font-mono text-[11px] font-semibold border transition-all ${
                      isActive
                        ? method === "GET"
                          ? "bg-emerald-500/20 text-emerald-400 border-emerald-500/50 shadow-sm"
                          : method === "POST"
                          ? "bg-blue-500/20 text-blue-400 border-blue-500/50 shadow-sm"
                          : method === "PUT/PATCH"
                          ? "bg-amber-500/20 text-amber-400 border-amber-500/50 shadow-sm"
                          : method === "DELETE"
                          ? "bg-rose-500/20 text-rose-400 border-rose-500/50 shadow-sm"
                          : "bg-zinc-700 text-white border-zinc-600 shadow-sm"
                        : "bg-zinc-950/60 text-zinc-400 border-zinc-800 hover:border-zinc-700 hover:text-zinc-200"
                    }`}
                  >
                    {method}
                  </button>
                );
              })}
            </div>

            {/* Engine & Auth Pills */}
            <div className="flex items-center gap-2 flex-wrap">
              {/* Engine selector */}
              <div className="flex items-center gap-1 bg-zinc-950 border border-zinc-800 rounded-lg p-0.5">
                {[
                  { id: "ALL", label: "All Engines" },
                  { id: "Inertia React", label: "Inertia" },
                  { id: "Blade View", label: "Blade" },
                  { id: "API / JSON", label: "API" },
                ].map((e) => (
                  <button
                    key={e.id}
                    onClick={() => setSelectedEngine(e.id)}
                    className={`px-2 py-1 rounded-md text-[11px] font-medium transition-all ${
                      selectedEngine === e.id
                        ? "bg-zinc-800 text-white font-semibold"
                        : "text-zinc-400 hover:text-zinc-200"
                    }`}
                  >
                    {e.label}
                  </button>
                ))}
              </div>

              {/* Guard selector */}
              <div className="flex items-center gap-1 bg-zinc-950 border border-zinc-800 rounded-lg p-0.5">
                {[
                  { id: "ALL", label: "All Guards" },
                  { id: "PROTECTED", label: "Auth Only" },
                  { id: "PUBLIC", label: "Public" },
                ].map((g) => (
                  <button
                    key={g.id}
                    onClick={() => setSelectedAuth(g.id)}
                    className={`px-2 py-1 rounded-md text-[11px] font-medium transition-all ${
                      selectedAuth === g.id
                        ? "bg-zinc-800 text-white font-semibold"
                        : "text-zinc-400 hover:text-zinc-200"
                    }`}
                  >
                    {g.label}
                  </button>
                ))}
              </div>

              {/* Reset button if any filter is active */}
              {(searchQuery || selectedMethod !== "ALL" || selectedEngine !== "ALL" || selectedAuth !== "ALL" || selectedCategory !== "ALL") && (
                <button
                  onClick={resetFilters}
                  className="flex items-center gap-1 px-2.5 py-1 text-[11px] text-zinc-400 hover:text-rose-400 transition-colors"
                >
                  <RefreshCw className="w-3 h-3" />
                  <span>Reset</span>
                </button>
              )}
            </div>
          </div>

          {/* Category Chips */}
          <div className="flex items-center gap-1.5 overflow-x-auto pb-1 max-w-full text-xs">
            <button
              onClick={() => setSelectedCategory("ALL")}
              className={`px-3 py-1 rounded-lg text-xs font-medium whitespace-nowrap transition-all border ${
                selectedCategory === "ALL"
                  ? "bg-emerald-500/10 text-emerald-400 border-emerald-500/30 font-semibold"
                  : "bg-zinc-950/40 text-zinc-400 border-zinc-800/80 hover:border-zinc-700"
              }`}
            >
              All Categories ({routes.length})
            </button>
            {categories.map((cat) => {
              const isSelected = selectedCategory === cat.name;
              return (
                <button
                  key={cat.name}
                  onClick={() => setSelectedCategory(cat.name)}
                  className={`flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium whitespace-nowrap transition-all border ${
                    isSelected
                      ? "bg-emerald-500/10 text-emerald-400 border-emerald-500/30 font-semibold"
                      : "bg-zinc-950/40 text-zinc-400 border-zinc-800/80 hover:border-zinc-700 hover:text-zinc-300"
                  }`}
                >
                  {getCategoryIcon(cat.name)}
                  <span>{cat.name}</span>
                  <span className="text-[10px] opacity-75 font-mono">({cat.count})</span>
                </button>
              );
            })}
          </div>
        </div>

        {/* Results Info Bar */}
        <div className="flex items-center justify-between text-xs text-zinc-400 px-1">
          <div>
            Showing <span className="font-semibold text-zinc-200 font-mono">{filteredRoutes.length}</span> of{" "}
            <span className="font-semibold text-zinc-200 font-mono">{routes.length}</span> endpoints
            {searchQuery && (
              <span>
                {" "}
                matching "<span className="text-emerald-400 font-medium">{searchQuery}</span>"
              </span>
            )}
          </div>
          <div className="text-[11px] text-zinc-500 hidden sm:block">
            Tip: Click any GET route to test in browser, or copy URL with 1-click.
          </div>
        </div>

        {/* Empty State */}
        {filteredRoutes.length === 0 && (
          <Card className="bg-zinc-900/40 border-zinc-800 text-center py-16 px-6">
            <div className="w-12 h-12 rounded-full bg-zinc-800/80 flex items-center justify-center mx-auto mb-4 text-zinc-400">
              <Search className="w-6 h-6" />
            </div>
            <h3 className="text-base font-semibold text-white mb-1">No matching routes found</h3>
            <p className="text-xs text-zinc-400 max-w-sm mx-auto mb-6">
              No registered application endpoints match your current search criteria or active filters.
            </p>
            <Button
              size="sm"
              onClick={resetFilters}
              className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-semibold text-xs h-9 px-4 rounded-xl gap-2"
            >
              <RefreshCw className="w-3.5 h-3.5" />
              Reset All Filters
            </Button>
          </Card>
        )}

        {/* VIEW MODE 1: GROUPED BY CATEGORY */}
        {viewMode === "grouped" && filteredRoutes.length > 0 && (
          <div className="space-y-6">
            {Object.entries(groupedRoutes).map(([categoryName, catRoutes]) => {
              const isCollapsed = collapsedCategories[categoryName];
              return (
                <div
                  key={categoryName}
                  className="rounded-2xl bg-zinc-900/40 border border-zinc-800/80 overflow-hidden backdrop-blur-sm transition-all"
                >
                  {/* Category Header */}
                  <div
                    onClick={() => toggleCategoryCollapse(categoryName)}
                    className="flex items-center justify-between p-4 bg-zinc-900/80 hover:bg-zinc-800/60 cursor-pointer border-b border-zinc-800/60 transition-colors select-none"
                  >
                    <div className="flex items-center gap-3">
                      <div className="p-2 rounded-xl bg-zinc-800 border border-zinc-700/60">
                        {getCategoryIcon(categoryName)}
                      </div>
                      <div>
                        <div className="flex items-center gap-2">
                          <h3 className="text-sm font-bold text-white tracking-tight">{categoryName}</h3>
                          <Badge variant="secondary" className="text-[10px] font-mono px-2 py-0.5">
                            {catRoutes.length} {catRoutes.length === 1 ? "route" : "routes"}
                          </Badge>
                        </div>
                        <p className="text-xs text-zinc-400 mt-0.5">
                          {catRoutes.filter((r) => inArray("GET", r.methods)).length} GET endpoints •{" "}
                          {catRoutes.filter((r) => r.engine === "Inertia React").length} Inertia components
                        </p>
                      </div>
                    </div>
                    <div className="flex items-center gap-2 text-zinc-400">
                      {isCollapsed ? (
                        <ChevronRight className="w-5 h-5 text-zinc-500" />
                      ) : (
                        <ChevronDown className="w-5 h-5 text-zinc-500" />
                      )}
                    </div>
                  </div>

                  {/* Route Items List */}
                  {!isCollapsed && (
                    <div className="divide-y divide-zinc-800/60">
                      {catRoutes.map((route, idx) => (
                        <RouteListItem
                          key={`${route.uri}-${route.methods.join("-")}-${idx}`}
                          route={route}
                          copiedKey={copiedKey}
                          onCopy={handleCopy}
                          onInspect={setSelectedRoute}
                          getMethodBadgeClass={getMethodBadgeClass}
                        />
                      ))}
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        )}

        {/* VIEW MODE 2: TABLE VIEW */}
        {viewMode === "table" && filteredRoutes.length > 0 && (
          <div className="rounded-xl border border-zinc-800 bg-zinc-900/60 overflow-hidden backdrop-blur-md">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-zinc-950/80 border-b border-zinc-800 text-zinc-400 font-mono text-[11px] uppercase tracking-wider">
                  <tr>
                    <th className="py-3 px-4 font-semibold">Method</th>
                    <th className="py-3 px-4 font-semibold">Route URI</th>
                    <th className="py-3 px-4 font-semibold">Route Name</th>
                    <th className="py-3 px-4 font-semibold">Action / Controller</th>
                    <th className="py-3 px-4 font-semibold">Engine</th>
                    <th className="py-3 px-4 font-semibold">Security</th>
                    <th className="py-3 px-4 font-semibold text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-zinc-800/60 text-zinc-300">
                  {filteredRoutes.map((route, idx) => (
                    <tr
                      key={`${route.uri}-${route.methods.join("-")}-${idx}`}
                      className="hover:bg-zinc-800/40 transition-colors group"
                    >
                      {/* Methods */}
                      <td className="py-3 px-4 whitespace-nowrap">
                        <div className="flex items-center gap-1">
                          {route.methods.map((m) => (
                            <span
                              key={m}
                              className={`px-2 py-0.5 rounded text-[10px] font-mono font-bold border ${getMethodBadgeClass(
                                m
                              )}`}
                            >
                              {m}
                            </span>
                          ))}
                        </div>
                      </td>

                      {/* URI */}
                      <td className="py-3 px-4 font-mono font-medium text-white max-w-xs truncate">
                        <div className="flex items-center gap-2">
                          <span title={route.uri}>{renderHighlightedUri(route.uri)}</span>
                        </div>
                      </td>

                      {/* Name */}
                      <td className="py-3 px-4 font-mono text-zinc-400">
                        {route.name ? (
                          <span
                            onClick={() => handleCopy(route.name, `name-${route.name}-${idx}`)}
                            className="cursor-pointer hover:text-emerald-400 transition-colors inline-flex items-center gap-1"
                            title="Click to copy route name"
                          >
                            {route.name}
                            {copiedKey === `name-${route.name}-${idx}` && (
                              <Check className="w-3 h-3 text-emerald-400" />
                            )}
                          </span>
                        ) : (
                          <span className="text-zinc-600 italic">unnamed</span>
                        )}
                      </td>

                      {/* Action */}
                      <td className="py-3 px-4 font-mono text-xs text-zinc-400 truncate max-w-xs" title={route.full_action}>
                        {route.action}
                      </td>

                      {/* Engine */}
                      <td className="py-3 px-4 whitespace-nowrap">
                        <span
                          className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium border ${
                            route.engine === "Inertia React"
                              ? "bg-indigo-500/10 text-indigo-400 border-indigo-500/30 font-semibold"
                              : route.engine === "API / JSON"
                              ? "bg-cyan-500/10 text-cyan-400 border-cyan-500/30"
                              : route.engine === "System"
                              ? "bg-purple-500/10 text-purple-400 border-purple-500/30"
                              : "bg-zinc-800 text-zinc-300 border-zinc-700"
                          }`}
                        >
                          {route.engine === "Inertia React" && <Sparkles className="w-2.5 h-2.5" />}
                          {route.engine}
                        </span>
                      </td>

                      {/* Security */}
                      <td className="py-3 px-4 whitespace-nowrap">
                        <div className="flex items-center gap-1.5">
                          {route.requires_auth ? (
                            <span
                              className="inline-flex items-center gap-1 text-[10px] text-amber-400"
                              title="Requires Authentication"
                            >
                              <Lock className="w-3 h-3" /> Auth
                            </span>
                          ) : (
                            <span
                              className="inline-flex items-center gap-1 text-[10px] text-zinc-500"
                              title="Public Endpoint"
                            >
                              <Unlock className="w-3 h-3" /> Public
                            </span>
                          )}
                          {route.is_admin && (
                            <span className="px-1.5 py-0.2 rounded text-[9px] bg-yellow-500/15 text-yellow-400 border border-yellow-500/30 font-mono">
                              ADMIN
                            </span>
                          )}
                        </div>
                      </td>

                      {/* Actions */}
                      <td className="py-3 px-4 whitespace-nowrap text-right">
                        <div className="flex items-center justify-end gap-1.5">
                          {/* Visit Link */}
                          {route.can_visit && (
                            <a
                              href={route.sample_url || route.uri}
                              target="_blank"
                              rel="noreferrer"
                              className="p-1.5 rounded-lg bg-zinc-800/80 hover:bg-emerald-500/20 text-zinc-400 hover:text-emerald-400 transition-colors"
                              title={`Open ${route.sample_url || route.uri} in new tab`}
                            >
                              <ExternalLink className="w-3.5 h-3.5" />
                            </a>
                          )}

                          {/* Copy URI */}
                          <button
                            onClick={() => handleCopy(route.uri, `uri-${idx}`)}
                            className="p-1.5 rounded-lg bg-zinc-800/80 hover:bg-zinc-700 text-zinc-400 hover:text-zinc-200 transition-colors"
                            title="Copy URI"
                          >
                            {copiedKey === `uri-${idx}` ? (
                              <Check className="w-3.5 h-3.5 text-emerald-400" />
                            ) : (
                              <Copy className="w-3.5 h-3.5" />
                            )}
                          </button>

                          {/* Inspect Modal */}
                          <button
                            onClick={() => setSelectedRoute(route)}
                            className="p-1.5 rounded-lg bg-zinc-800/80 hover:bg-zinc-700 text-zinc-400 hover:text-white transition-colors"
                            title="View Route Details"
                          >
                            <Eye className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* VIEW MODE 3: GRID CARDS */}
        {viewMode === "cards" && filteredRoutes.length > 0 && (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {filteredRoutes.map((route, idx) => (
              <Card
                key={`${route.uri}-${route.methods.join("-")}-${idx}`}
                className="bg-zinc-900/60 border-zinc-800/80 hover:border-zinc-700 transition-all p-5 flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center justify-between gap-2 mb-3">
                    <div className="flex items-center gap-1.5">
                      {route.methods.map((m) => (
                        <span
                          key={m}
                          className={`px-2.5 py-0.5 rounded text-[10px] font-mono font-bold border ${getMethodBadgeClass(
                            m
                          )}`}
                        >
                          {m}
                        </span>
                      ))}
                    </div>

                    <span
                      className={`text-[10px] font-semibold px-2 py-0.5 rounded-full border ${
                        route.engine === "Inertia React"
                          ? "bg-indigo-500/15 text-indigo-400 border-indigo-500/30"
                          : route.engine === "API / JSON"
                          ? "bg-cyan-500/15 text-cyan-400 border-cyan-500/30"
                          : "bg-zinc-800 text-zinc-400 border-zinc-700"
                      }`}
                    >
                      {route.engine}
                    </span>
                  </div>

                  <h4 className="text-sm font-mono font-bold text-white mb-1 break-all">
                    {renderHighlightedUri(route.uri)}
                  </h4>

                  {route.name && (
                    <div className="text-xs font-mono text-emerald-400/90 mb-3 flex items-center gap-1">
                      <span>name:</span>
                      <span className="font-semibold">{route.name}</span>
                    </div>
                  )}

                  <div className="space-y-1.5 pt-2 border-t border-zinc-800/80 text-xs">
                    <div className="flex items-center justify-between text-zinc-400">
                      <span>Action:</span>
                      <span className="font-mono text-zinc-300 truncate max-w-[180px]" title={route.action}>
                        {route.action}
                      </span>
                    </div>
                    <div className="flex items-center justify-between text-zinc-400">
                      <span>Category:</span>
                      <span className="text-zinc-300">{route.category}</span>
                    </div>
                    <div className="flex items-center justify-between text-zinc-400">
                      <span>Security:</span>
                      <span className="flex items-center gap-1 text-zinc-300">
                        {route.requires_auth ? (
                          <span className="text-amber-400 inline-flex items-center gap-1 text-[11px]">
                            <Lock className="w-3 h-3" /> Auth Required
                          </span>
                        ) : (
                          <span className="text-zinc-400 inline-flex items-center gap-1 text-[11px]">
                            <Unlock className="w-3 h-3" /> Public
                          </span>
                        )}
                      </span>
                    </div>
                  </div>
                </div>

                <div className="flex items-center justify-between pt-4 mt-4 border-t border-zinc-800/80">
                  <button
                    onClick={() => handleCopy(route.uri, `card-${idx}`)}
                    className="text-xs text-zinc-400 hover:text-white inline-flex items-center gap-1 transition-colors"
                  >
                    {copiedKey === `card-${idx}` ? (
                      <>
                        <Check className="w-3.5 h-3.5 text-emerald-400" />
                        <span className="text-emerald-400">Copied URI</span>
                      </>
                    ) : (
                      <>
                        <Copy className="w-3.5 h-3.5" />
                        <span>Copy Path</span>
                      </>
                    )}
                  </button>

                  <div className="flex items-center gap-1.5">
                    <button
                      onClick={() => setSelectedRoute(route)}
                      className="px-2.5 py-1 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-xs text-zinc-300 transition-colors"
                    >
                      Details
                    </button>
                    {route.can_visit && (
                      <a
                        href={route.sample_url || route.uri}
                        target="_blank"
                        rel="noreferrer"
                        className="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-xs text-emerald-400 border border-emerald-500/30 inline-flex items-center gap-1 font-medium transition-colors"
                      >
                        Visit <ArrowUpRight className="w-3 h-3" />
                      </a>
                    )}
                  </div>
                </div>
              </Card>
            ))}
          </div>
        )}

        {/* ROUTE DETAILS MODAL / DRAWER */}
        {selectedRoute && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-200">
            <div className="relative w-full max-w-2xl bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
              <div className="flex items-center justify-between pb-3 border-b border-zinc-800">
                <div className="flex items-center gap-2">
                  {selectedRoute.methods.map((m) => (
                    <span
                      key={m}
                      className={`px-2.5 py-0.5 rounded text-xs font-mono font-bold border ${getMethodBadgeClass(
                        m
                      )}`}
                    >
                      {m}
                    </span>
                  ))}
                  <h3 className="text-base font-bold text-white font-mono">{selectedRoute.uri}</h3>
                </div>
                <button
                  onClick={() => setSelectedRoute(null)}
                  className="p-1 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>

              {/* Basic Metadata */}
              <div className="grid grid-cols-2 gap-3 text-xs">
                <div className="p-3 rounded-xl bg-zinc-950/80 border border-zinc-800">
                  <span className="text-zinc-500 block mb-1">Route Name</span>
                  <span className="font-mono text-zinc-200 font-semibold">
                    {selectedRoute.name || <span className="text-zinc-600 italic">None</span>}
                  </span>
                </div>
                <div className="p-3 rounded-xl bg-zinc-950/80 border border-zinc-800">
                  <span className="text-zinc-500 block mb-1">Rendering Engine</span>
                  <span className="text-zinc-200 font-semibold">{selectedRoute.engine}</span>
                </div>
                <div className="p-3 rounded-xl bg-zinc-950/80 border border-zinc-800">
                  <span className="text-zinc-500 block mb-1">Category</span>
                  <span className="text-zinc-200 font-semibold">{selectedRoute.category}</span>
                </div>
                <div className="p-3 rounded-xl bg-zinc-950/80 border border-zinc-800">
                  <span className="text-zinc-500 block mb-1">Access Guard</span>
                  <span className="text-zinc-200 font-semibold">
                    {selectedRoute.requires_auth ? "Authenticated (Session/Auth)" : "Public / Guest"}
                  </span>
                </div>
              </div>

              {/* Action / Controller Details */}
              <div className="p-3.5 rounded-xl bg-zinc-950/80 border border-zinc-800 text-xs space-y-1.5">
                <span className="text-zinc-500 font-mono block">CONTROLLER BINDING</span>
                <div className="font-mono text-emerald-400 font-semibold break-all">
                  {selectedRoute.full_action}
                </div>
              </div>

              {/* Middleware Pipeline */}
              <div className="p-3.5 rounded-xl bg-zinc-950/80 border border-zinc-800 text-xs space-y-2">
                <span className="text-zinc-500 font-mono block">MIDDLEWARE STACK ({selectedRoute.middleware.length})</span>
                <div className="flex flex-wrap gap-1.5">
                  {selectedRoute.middleware.map((m, i) => (
                    <span
                      key={i}
                      className="px-2 py-0.5 rounded-md bg-zinc-800/80 border border-zinc-700/60 font-mono text-[11px] text-zinc-300"
                    >
                      {m}
                    </span>
                  ))}
                </div>
              </div>

              {/* Parameters if any */}
              {selectedRoute.has_parameters && (
                <div className="p-3.5 rounded-xl bg-zinc-950/80 border border-zinc-800 text-xs space-y-2">
                  <span className="text-zinc-500 font-mono block">URL PARAMETERS</span>
                  <div className="flex flex-wrap gap-2">
                    {selectedRoute.parameters.map((param, i) => (
                      <div key={i} className="px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/25 text-amber-300 font-mono text-xs">
                        {`{${param}}`}
                      </div>
                    ))}
                  </div>
                  <div className="text-[11px] text-zinc-400 mt-2">
                    Sample test URL: <code className="text-emerald-400 font-mono">{selectedRoute.sample_url}</code>
                  </div>
                </div>
              )}

              {/* Developer Command snippet */}
              <div className="p-3.5 rounded-xl bg-zinc-950/80 border border-zinc-800 text-xs space-y-2">
                <div className="flex items-center justify-between">
                  <span className="text-zinc-500 font-mono">DEVELOPER TESTING SNIPPET</span>
                  <button
                    onClick={() =>
                      handleCopy(
                        `curl -X ${selectedRoute.methods[0]} "http://workhub.test${selectedRoute.sample_url || selectedRoute.uri}"`,
                        "curl-cmd"
                      )
                    }
                    className="text-[11px] text-zinc-400 hover:text-white inline-flex items-center gap-1"
                  >
                    {copiedKey === "curl-cmd" ? (
                      <Check className="w-3 h-3 text-emerald-400" />
                    ) : (
                      <Copy className="w-3 h-3" />
                    )}
                    <span>Copy cURL</span>
                  </button>
                </div>
                <pre className="p-2.5 rounded-lg bg-black/60 font-mono text-[11px] text-zinc-300 overflow-x-auto">
                  {`curl -X ${selectedRoute.methods[0]} "http://workhub.test${selectedRoute.sample_url || selectedRoute.uri}" \\
  -H "Accept: application/json"`}
                </pre>
              </div>

              {/* Footer Actions */}
              <div className="flex items-center justify-end gap-2 pt-3 border-t border-zinc-800">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setSelectedRoute(null)}
                  className="border-zinc-800 bg-zinc-800/80 hover:bg-zinc-700 text-zinc-300 text-xs"
                >
                  Close
                </Button>
                {selectedRoute.can_visit && (
                  <a
                    href={selectedRoute.sample_url || selectedRoute.uri}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-semibold text-xs shadow-md transition-colors"
                  >
                    Open Live Route <ExternalLink className="w-3.5 h-3.5" />
                  </a>
                )}
              </div>
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}

// Single Route row component for Grouped View
function RouteListItem({ route, copiedKey, onCopy, onInspect, getMethodBadgeClass }) {
  const uriKey = `uri-${route.uri}-${route.methods.join("-")}`;
  const nameKey = `name-${route.name}`;

  return (
    <div className="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 hover:bg-zinc-800/30 transition-colors gap-3 group">
      {/* Left Details */}
      <div className="flex items-start sm:items-center gap-3 min-w-0 flex-1">
        {/* Methods */}
        <div className="flex items-center gap-1 shrink-0 pt-0.5 sm:pt-0">
          {route.methods.map((m) => (
            <span
              key={m}
              className={`px-2 py-0.5 rounded text-[10px] font-mono font-bold border ${getMethodBadgeClass(m)}`}
            >
              {m}
            </span>
          ))}
        </div>

        {/* URI & Name */}
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            <span
              onClick={() => onCopy(route.uri, uriKey)}
              className="font-mono text-sm font-semibold text-white hover:text-emerald-400 cursor-pointer transition-colors break-all"
              title="Click to copy path"
            >
              {renderHighlightedUri(route.uri)}
            </span>
            {copiedKey === uriKey && (
              <span className="text-[10px] text-emerald-400 font-mono flex items-center gap-0.5">
                <Check className="w-3 h-3" /> copied!
              </span>
            )}
          </div>

          <div className="flex flex-wrap items-center gap-2 mt-1 text-xs text-zinc-400">
            {route.name ? (
              <span
                onClick={() => onCopy(route.name, nameKey)}
                className="font-mono text-[11px] text-zinc-400 hover:text-emerald-400 cursor-pointer transition-colors"
                title="Click to copy route name"
              >
                {route.name}
              </span>
            ) : (
              <span className="text-zinc-600 font-mono text-[11px]">unnamed</span>
            )}

            <span className="text-zinc-600">•</span>

            <span className="font-mono text-[11px] text-zinc-400 truncate max-w-xs" title={route.full_action}>
              {route.action}
            </span>
          </div>
        </div>
      </div>

      {/* Right Metadata & Quick Actions */}
      <div className="flex items-center gap-2 shrink-0 self-end sm:self-auto">
        {/* Engine Badge */}
        <span
          className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium border ${
            route.engine === "Inertia React"
              ? "bg-indigo-500/10 text-indigo-400 border-indigo-500/30 font-semibold"
              : route.engine === "API / JSON"
              ? "bg-cyan-500/10 text-cyan-400 border-cyan-500/30"
              : route.engine === "System"
              ? "bg-purple-500/10 text-purple-400 border-purple-500/30"
              : "bg-zinc-800 text-zinc-300 border-zinc-700"
          }`}
        >
          {route.engine === "Inertia React" && <Sparkles className="w-2.5 h-2.5" />}
          {route.engine}
        </span>

        {/* Security Tag */}
        {route.requires_auth ? (
          <span className="p-1 rounded text-amber-400" title="Auth required">
            <Lock className="w-3.5 h-3.5" />
          </span>
        ) : (
          <span className="p-1 rounded text-zinc-500" title="Public endpoint">
            <Unlock className="w-3.5 h-3.5" />
          </span>
        )}

        {/* Details button */}
        <button
          onClick={() => onInspect(route)}
          className="p-1.5 rounded-lg bg-zinc-800/80 hover:bg-zinc-700 text-zinc-400 hover:text-white transition-colors"
          title="Inspect Route Details"
        >
          <Eye className="w-3.5 h-3.5" />
        </button>

        {/* Visit link */}
        {route.can_visit && (
          <a
            href={route.sample_url || route.uri}
            target="_blank"
            rel="noreferrer"
            className="p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 transition-colors"
            title={`Open ${route.sample_url || route.uri} in new tab`}
          >
            <ArrowUpRight className="w-3.5 h-3.5" />
          </a>
        )}
      </div>
    </div>
  );
}

// Monospace URI renderer that highlights dynamic parameters like {project}
function renderHighlightedUri(uri) {
  const parts = uri.split(/(\{.*?\})/g);
  return parts.map((part, index) => {
    if (part.startsWith("{") && part.endsWith("}")) {
      return (
        <span key={index} className="text-amber-400 font-semibold px-0.5 rounded bg-amber-500/10">
          {part}
        </span>
      );
    }
    return <span key={index}>{part}</span>;
  });
}

// Utility helper
function inArray(needle, haystack) {
  return Array.isArray(haystack) && haystack.includes(needle);
}
