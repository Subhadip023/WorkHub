import React, { useState, useEffect, useCallback, useRef } from "react";
import { Head, Link, usePage, router } from "@inertiajs/react";
import axios from "axios";
import {
  LayoutDashboard,
  FolderKanban,
  CheckSquare,
  BarChart3,
  Users,
  Search,
  Bell,
  Crown,
  ChevronRight,
  ChevronDown,
  LogOut,
  Briefcase,
  AlertCircle,
  FileText,
  ShieldCheck,
  Settings,
  ArrowLeft,
  Check,
  CheckCheck,
  Building2,
  Globe,
  Plus
} from "lucide-react";

import ApplicationLogo from "@/Components/ApplicationLogo";
import { Button } from "@/Components/ui/button";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
import {
  SidebarProvider,
  Sidebar,
  SidebarHeader,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarGroupContent,
  SidebarMenu,
  SidebarMenuItem,
  SidebarMenuButton,
  SidebarMenuBadge,
  SidebarTrigger,
  SidebarInset,
  SidebarRail
} from "@/Components/ui/sidebar";

export default function DashboardLayout({ title, children, activeItem = "dashboard", actions }) {
  const { auth, company, current_workspace_name } = usePage().props;
  const user = auth?.user;
  const currentActive = (activeItem || "dashboard").toLowerCase();

  const [workspaceDropdownOpen, setWorkspaceDropdownOpen] = useState(false);
  const workspaceDropdownRef = useRef(null);
  const currentWorkspaceDisplay = current_workspace_name || (company ? company.name : "All Workspaces");

  const [notifications, setNotifications] = useState([]);
  const [showNotifications, setShowNotifications] = useState(false);
  const [loadingNotifs, setLoadingNotifs] = useState(false);

  // Search State
  const [searchQuery, setSearchQuery] = useState("");
  const [searchResults, setSearchResults] = useState(null);
  const [searching, setSearching] = useState(false);
  const [showSearchDropdown, setShowSearchDropdown] = useState(false);
  const searchInputRef = useRef(null);

  // Close workspace dropdown on outside click
  useEffect(() => {
    const handleClickOutside = (e) => {
      if (workspaceDropdownRef.current && !workspaceDropdownRef.current.contains(e.target)) {
        setWorkspaceDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // Key listener for Cmd+K / Ctrl+K & Escape
  useEffect(() => {
    const handleKeyDown = (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key === "k") {
        e.preventDefault();
        setShowSearchDropdown(true);
        setTimeout(() => searchInputRef.current?.focus(), 50);
      }
      if (e.key === "Escape") {
        setShowSearchDropdown(false);
        setWorkspaceDropdownOpen(false);
      }
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, []);

  // Debounced search query fetcher
  useEffect(() => {
    if (!searchQuery.trim()) {
      setSearchResults(null);
      setSearching(false);
      return;
    }
    setSearching(true);
    const timer = setTimeout(async () => {
      try {
        const res = await axios.get('/search', { params: { q: searchQuery } });
        setSearchResults(res.data);
      } catch (e) {
        console.error('Search failed', e);
      } finally {
        setSearching(false);
      }
    }, 250);

    return () => clearTimeout(timer);
  }, [searchQuery]);

  const fetchNotifications = useCallback(async () => {
    try {
      setLoadingNotifs(true);
      const res = await axios.get('/notifications');
      if (res.data && Array.isArray(res.data.notifications)) {
        setNotifications(res.data.notifications);
      }
    } catch (e) {
      console.error('Failed to fetch notifications', e);
    } finally {
      setLoadingNotifs(false);
    }
  }, []);

  useEffect(() => {
    fetchNotifications();
  }, [fetchNotifications]);

  const handleMarkAsRead = async (id, e) => {
    e.stopPropagation();
    try {
      await axios.patch(`/notifications/${id}/read`);
      setNotifications((prev) => prev.filter((n) => n.id !== id));
    } catch (err) {
      console.error('Failed to mark notification read', err);
    }
  };

  const handleMarkAllAsRead = async () => {
    try {
      await axios.post('/notifications/read-all');
      setNotifications([]);
    } catch (err) {
      console.error('Failed to mark all notifications read', err);
    }
  };

  return (
    <SidebarProvider defaultOpen={true}>
      {title && <Head title={`${title} - WorkHub`} />}

      <div className="min-h-screen bg-zinc-950 text-zinc-100 font-sans selection:bg-zinc-800 selection:text-white flex w-full">
        {/* Layered Sidebar with clean border separation */}
        <Sidebar className="border-r border-zinc-800/80 bg-zinc-950 text-zinc-100 z-30">
          {/* Header Workspace Switcher */}
          <SidebarHeader className="h-16 px-3 flex items-center justify-between border-b border-zinc-800/80 bg-zinc-950 shrink-0">
            <div className="flex items-center justify-between w-full" ref={workspaceDropdownRef}>
              {user?.companies && user.companies.length > 0 ? (
                <div className="relative w-full">
                  <button
                    type="button"
                    onClick={() => setWorkspaceDropdownOpen((prev) => !prev)}
                    className="w-full flex items-center justify-between gap-2.5 p-2 rounded-xl bg-zinc-900/80 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900 transition-all text-left shadow-sm group cursor-pointer"
                  >
                    <div className="flex items-center gap-2.5 min-w-0">
                      <div className="h-7 w-7 rounded-lg bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center shrink-0 text-emerald-400 font-bold text-xs">
                        <Building2 className="w-3.5 h-3.5" />
                      </div>
                      <div className="min-w-0 flex-1">
                        <span className="font-semibold text-xs text-zinc-100 block truncate leading-tight group-hover:text-emerald-400 transition-colors">
                          {currentWorkspaceDisplay}
                        </span>
                        <span className="text-[10px] text-zinc-400 font-mono block leading-tight mt-0.5">
                          Switch workspace
                        </span>
                      </div>
                    </div>
                    <ChevronDown className={`w-3.5 h-3.5 text-zinc-400 transition-transform duration-200 shrink-0 ${workspaceDropdownOpen ? 'rotate-180 text-emerald-400' : ''}`} />
                  </button>

                  {workspaceDropdownOpen && (
                    <div className="absolute left-0 right-0 top-full mt-2 bg-zinc-900/95 backdrop-blur-xl border border-zinc-800 rounded-xl shadow-2xl z-50 p-1.5 space-y-1">
                      <button
                        onClick={() => {
                          setWorkspaceDropdownOpen(false);
                          router.get("/dashboard");
                        }}
                        className="w-full flex items-center justify-between p-2 rounded-lg text-xs hover:bg-zinc-800/80 transition-colors text-left text-zinc-200 group cursor-pointer"
                      >
                        <div className="flex items-center gap-2">
                          <Globe className="w-3.5 h-3.5 text-emerald-400" />
                          <span className="font-medium">All Workspaces</span>
                        </div>
                        {!company && <Check className="w-3.5 h-3.5 text-emerald-400" />}
                      </button>
                      <div className="h-px bg-zinc-800/80 my-1" />
                      {user.companies.map((c) => (
                        <button
                          key={c.id}
                          onClick={() => {
                            setWorkspaceDropdownOpen(false);
                            router.get(`/dashboard/${c.id}`);
                          }}
                          className="w-full flex items-center justify-between p-2 rounded-lg text-xs hover:bg-zinc-800/80 transition-colors text-left text-zinc-200 group cursor-pointer"
                        >
                          <div className="flex items-center gap-2 min-w-0">
                            <Building2 className="w-3.5 h-3.5 text-zinc-400 group-hover:text-emerald-400" />
                            <span className="font-medium truncate">{c.name}</span>
                          </div>
                          {company?.id === c.id && <Check className="w-3.5 h-3.5 text-emerald-400 shrink-0" />}
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              ) : (
                <div className="flex items-center gap-3 p-2 rounded-xl bg-zinc-900/80 border border-zinc-800 w-full">
                  <div className="h-7 w-7 rounded-lg bg-zinc-950 flex items-center justify-center shadow-sm shrink-0 border border-zinc-800">
                    <ApplicationLogo className="w-4 h-4" />
                  </div>
                  <div className="text-left truncate">
                    <span className="font-semibold text-xs text-zinc-100 block truncate leading-tight">
                      WorkHub
                    </span>
                    <span className="text-[10px] text-zinc-400 font-mono block leading-tight">
                      Personal Space
                    </span>
                  </div>
                </div>
              )}
            </div>
          </SidebarHeader>

          {/* Navigation Links */}
          <SidebarContent className="px-2.5 py-4 space-y-6 bg-zinc-950">
            {/* Group 1: Core Application */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider px-3 mb-2 font-mono">
                Main Suite
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu className="space-y-1">
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={currentActive === "dashboard"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-emerald-500/10 data-[active=true]:text-emerald-400 data-[active=true]:font-semibold data-[active=true]:border-l-2 data-[active=true]:border-emerald-400 transition-colors rounded-md"
                    >
                      <Link href="/new/dashboard">
                        <LayoutDashboard className="h-4 w-4 text-zinc-400 group-data-[active=true]:text-emerald-400" />
                        <span>Dashboard</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={currentActive === "analytics"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-emerald-500/10 data-[active=true]:text-emerald-400 data-[active=true]:font-semibold data-[active=true]:border-l-2 data-[active=true]:border-emerald-400 transition-colors rounded-md"
                    >
                      <Link href="/analytics">
                        <BarChart3 className="h-4 w-4 text-zinc-400 group-data-[active=true]:text-emerald-400" />
                        <span>Analytics</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={currentActive === "projects"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-emerald-500/10 data-[active=true]:text-emerald-400 data-[active=true]:font-semibold data-[active=true]:border-l-2 data-[active=true]:border-emerald-400 transition-colors rounded-md"
                    >
                      <Link href="/new/projects">
                        <FolderKanban className="h-4 w-4 text-zinc-400 group-data-[active=true]:text-emerald-400" />
                        <span>Projects</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={currentActive === "tasks" || currentActive === "my tasks"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-emerald-500/10 data-[active=true]:text-emerald-400 data-[active=true]:font-semibold data-[active=true]:border-l-2 data-[active=true]:border-emerald-400 transition-colors rounded-md"
                    >
                      <Link href="/new/tasks">
                        <CheckSquare className="h-4 w-4 text-zinc-400 group-data-[active=true]:text-emerald-400" />
                        <span>My Tasks</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={currentActive === "notes"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-emerald-500/10 data-[active=true]:text-emerald-400 data-[active=true]:font-semibold data-[active=true]:border-l-2 data-[active=true]:border-emerald-400 transition-colors rounded-md"
                    >
                      <Link href="/notes">
                        <FileText className="h-4 w-4 text-zinc-400 group-data-[active=true]:text-emerald-400" />
                        <span>Notes & Docs</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>

            {/* Group 2: Workspaces & Team */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider px-3 mb-2 font-mono">
                Organization
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu className="space-y-1">
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={currentActive === "companies"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-emerald-500/10 data-[active=true]:text-emerald-400 data-[active=true]:font-semibold data-[active=true]:border-l-2 data-[active=true]:border-emerald-400 transition-colors rounded-md"
                    >
                      <Link href="/companies">
                        <Briefcase className="h-4 w-4 text-zinc-400 group-data-[active=true]:text-emerald-400" />
                        <span>Companies</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={currentActive === "users" || currentActive === "team members"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-emerald-500/10 data-[active=true]:text-emerald-400 data-[active=true]:font-semibold data-[active=true]:border-l-2 data-[active=true]:border-emerald-400 transition-colors rounded-md"
                    >
                      <Link href="/users">
                        <Users className="h-4 w-4 text-zinc-400 group-data-[active=true]:text-emerald-400" />
                        <span>Team Members</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>

            {/* Group 3: Account & Management */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider px-3 mb-2 font-mono">
                System & Tools
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu className="space-y-1">
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={currentActive === "activity-logs" || currentActive === "activity logs"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-emerald-500/10 data-[active=true]:text-emerald-400 data-[active=true]:font-semibold data-[active=true]:border-l-2 data-[active=true]:border-emerald-400 transition-colors rounded-md"
                    >
                      <Link href="/activity-logs">
                        <ShieldCheck className="h-4 w-4 text-zinc-400 group-data-[active=true]:text-emerald-400" />
                        <span>Activity Logs</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={currentActive === "trash"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-emerald-500/10 data-[active=true]:text-emerald-400 data-[active=true]:font-semibold data-[active=true]:border-l-2 data-[active=true]:border-emerald-400 transition-colors rounded-md"
                    >
                      <Link href="/trash">
                        <AlertCircle className="h-4 w-4 text-zinc-400 group-data-[active=true]:text-emerald-400" />
                        <span>Recycle Bin</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={currentActive === "profile" || currentActive === "account settings"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-emerald-500/10 data-[active=true]:text-emerald-400 data-[active=true]:font-semibold data-[active=true]:border-l-2 data-[active=true]:border-emerald-400 transition-colors rounded-md"
                    >
                      <Link href="/profile">
                        <Settings className="h-4 w-4 text-zinc-400 group-data-[active=true]:text-emerald-400" />
                        <span>Account Settings</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>
          </SidebarContent>

          {/* User Profile Footer */}
          <SidebarFooter className="p-3 border-t border-zinc-800 bg-zinc-950">
            <div className="flex items-center justify-between gap-3 p-1.5 rounded-lg bg-zinc-900/60 border border-zinc-800/80">
              <div className="flex items-center gap-2.5 min-w-0">
                <Avatar className="h-8 w-8 border border-zinc-700 shrink-0">
                  <AvatarFallback className="bg-zinc-800 text-zinc-200 text-xs font-bold font-mono">
                    {user?.name ? user.name.substring(0, 2).toUpperCase() : "AD"}
                  </AvatarFallback>
                </Avatar>
                <div className="min-w-0 flex-1">
                  <div className="text-xs font-medium text-zinc-100 truncate">
                    {user?.name || "Demo Administrator"}
                  </div>
                  <div className="text-[10px] text-zinc-400 font-mono truncate">
                    {user?.email || "admin@workhub.io"}
                  </div>
                </div>
              </div>
              <Link
                href="/logout"
                method="post"
                as="button"
                className="h-7 w-7 inline-flex items-center justify-center rounded-md text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition-colors"
                title="Log out"
              >
                <LogOut className="h-3.5 w-3.5" />
              </Link>
            </div>
          </SidebarFooter>

          <SidebarRail />
        </Sidebar>

        {/* Content Wrapper */}
        <SidebarInset className="relative flex flex-col flex-1 min-w-0 bg-zinc-950">
          {/* Header Navbar */}
          <header className="sticky top-0 z-20 border-b border-zinc-800 bg-zinc-950/80 backdrop-blur-md h-14 flex items-center justify-between px-4 sm:px-6">
            <div className="flex items-center gap-3">
              <SidebarTrigger className="text-zinc-400 hover:bg-zinc-900 hover:text-zinc-100 rounded-md p-1.5" />
              <div className="h-4 w-px bg-zinc-800"></div>
              <div className="flex items-center gap-2 text-xs">
                <ApplicationLogo className="w-4 h-4 shrink-0" />
                <span className="font-mono text-zinc-300 font-bold">WorkHub</span>
                <ChevronRight className="h-3.5 w-3.5 text-zinc-600" />
                <span className="font-medium text-zinc-200 uppercase tracking-wide">
                  {title || activeItem}
                </span>
              </div>
            </div>

            {/* Right Header Actions */}
            <div className="flex items-center gap-3">
              {/* Header Search Trigger Button */}
              <button
                type="button"
                onClick={() => {
                  setShowSearchDropdown(true);
                  setTimeout(() => searchInputRef.current?.focus(), 50);
                }}
                className="relative hidden sm:flex items-center justify-between w-64 md:w-80 bg-zinc-900 border border-zinc-800 hover:border-zinc-700 rounded-lg px-3 py-1.5 text-xs text-zinc-400 transition-colors cursor-pointer group"
              >
                <div className="flex items-center gap-2">
                  <Search className="h-3.5 w-3.5 text-zinc-400 group-hover:text-zinc-200 transition-colors" />
                  <span>Search projects, tasks, notes...</span>
                </div>
                <kbd className="font-mono text-[10px] bg-zinc-950 border border-zinc-800 px-1.5 py-0.5 rounded text-zinc-400 group-hover:border-zinc-700 transition-colors">
                  ⌘K
                </kbd>
              </button>

              {/* Full Command Palette Search Modal Overlay */}
              {showSearchDropdown && (
                <div
                  className="fixed inset-0 bg-zinc-950/80 backdrop-blur-sm z-50 flex items-start justify-center pt-16 sm:pt-24 px-4 font-sans"
                  onClick={() => setShowSearchDropdown(false)}
                >
                  <div
                    className="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col"
                    onClick={(e) => e.stopPropagation()}
                  >
                    {/* Top Search Input Bar */}
                    <div className="relative flex items-center px-4 py-3.5 border-b border-zinc-800 bg-zinc-950">
                      <Search className="w-5 h-5 text-zinc-400 mr-3 shrink-0" />
                      <input
                        ref={searchInputRef}
                        type="text"
                        placeholder="Search projects, tasks, notes, team members..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="w-full bg-transparent text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none"
                        autoFocus
                      />
                      <kbd className="font-mono text-[10px] bg-zinc-900 border border-zinc-800 px-2 py-0.5 rounded text-zinc-400 shrink-0 ml-2">
                        ESC
                      </kbd>
                    </div>

                    {/* Modal Body Content */}
                    <div className="max-h-[60vh] overflow-y-auto p-3 space-y-4 divide-y divide-zinc-800/60 text-xs">
                      {searching ? (
                        <div className="py-8 text-center text-zinc-400 font-mono text-xs">Searching WorkHub...</div>
                      ) : !searchQuery.trim() ? (
                        /* Quick Jump Navigation Links when empty */
                        <div className="space-y-3 pt-1">
                          <p className="text-[10px] font-bold uppercase tracking-wider text-zinc-400 px-2 font-mono">
                            Quick Navigation
                          </p>
                          <div className="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                            <Link
                              href="/dashboard"
                              onClick={() => setShowSearchDropdown(false)}
                              className="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-800/80 transition-colors group text-zinc-200"
                            >
                              <div className="p-2 rounded-lg bg-zinc-800 text-zinc-300 group-hover:bg-emerald-500/20 group-hover:text-emerald-400 transition-colors">
                                <LayoutDashboard className="w-4 h-4" />
                              </div>
                              <div>
                                <p className="font-medium text-xs">Dashboard</p>
                                <p className="text-[10px] text-zinc-400">Main overview & tasks</p>
                              </div>
                            </Link>

                            <Link
                              href="/new/projects"
                              onClick={() => setShowSearchDropdown(false)}
                              className="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-800/80 transition-colors group text-zinc-200"
                            >
                              <div className="p-2 rounded-lg bg-zinc-800 text-zinc-300 group-hover:bg-blue-500/20 group-hover:text-blue-400 transition-colors">
                                <FolderKanban className="w-4 h-4" />
                              </div>
                              <div>
                                <p className="font-medium text-xs">Projects</p>
                                <p className="text-[10px] text-zinc-400">View active projects</p>
                              </div>
                            </Link>

                            <Link
                              href="/new/tasks"
                              onClick={() => setShowSearchDropdown(false)}
                              className="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-800/80 transition-colors group text-zinc-200"
                            >
                              <div className="p-2 rounded-lg bg-zinc-800 text-zinc-300 group-hover:bg-cyan-500/20 group-hover:text-cyan-400 transition-colors">
                                <CheckSquare className="w-4 h-4" />
                              </div>
                              <div>
                                <p className="font-medium text-xs">My Tasks</p>
                                <p className="text-[10px] text-zinc-400">Assigned task list</p>
                              </div>
                            </Link>

                            <Link
                              href="/new/notes"
                              onClick={() => setShowSearchDropdown(false)}
                              className="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-800/80 transition-colors group text-zinc-200"
                            >
                              <div className="p-2 rounded-lg bg-zinc-800 text-zinc-300 group-hover:bg-amber-500/20 group-hover:text-amber-400 transition-colors">
                                <FileText className="w-4 h-4" />
                              </div>
                              <div>
                                <p className="font-medium text-xs">Notes & Docs</p>
                                <p className="text-[10px] text-zinc-400">Knowledge base & notes</p>
                              </div>
                            </Link>
                          </div>
                        </div>
                      ) : !searchResults || (
                          (!searchResults.projects || searchResults.projects.length === 0) &&
                          (!searchResults.tasks || searchResults.tasks.length === 0) &&
                          (!searchResults.notes || searchResults.notes.length === 0) &&
                          (!searchResults.users || searchResults.users.length === 0)
                        ) ? (
                        <div className="py-8 text-center text-zinc-400 text-xs">
                          No results found for "<span className="text-zinc-200 font-semibold">{searchQuery}</span>"
                        </div>
                      ) : (
                        <>
                          {/* Projects */}
                          {searchResults.projects && searchResults.projects.length > 0 && (
                            <div className="pt-2">
                              <p className="text-[10px] font-bold uppercase tracking-wider text-emerald-400 px-2 mb-1.5 flex items-center gap-1.5 font-mono">
                                <FolderKanban className="w-3.5 h-3.5" /> Projects ({searchResults.projects.length})
                              </p>
                              <div className="space-y-1">
                                {searchResults.projects.map((p) => (
                                  <Link
                                    key={p.id}
                                    href={p.url}
                                    onClick={() => setShowSearchDropdown(false)}
                                    className="flex items-center justify-between p-2 rounded-lg hover:bg-zinc-800/70 transition-colors text-zinc-200"
                                  >
                                    <span className="font-medium">{p.title}</span>
                                    <span className="text-[10px] text-zinc-400 font-mono">View Project →</span>
                                  </Link>
                                ))}
                              </div>
                            </div>
                          )}

                          {/* Tasks */}
                          {searchResults.tasks && searchResults.tasks.length > 0 && (
                            <div className="pt-2">
                              <p className="text-[10px] font-bold uppercase tracking-wider text-cyan-400 px-2 mb-1.5 flex items-center gap-1.5 font-mono">
                                <CheckSquare className="w-3.5 h-3.5" /> Tasks ({searchResults.tasks.length})
                              </p>
                              <div className="space-y-1">
                                {searchResults.tasks.map((t) => (
                                  <Link
                                    key={t.id}
                                    href={t.url}
                                    onClick={() => setShowSearchDropdown(false)}
                                    className="flex items-center justify-between p-2 rounded-lg hover:bg-zinc-800/70 transition-colors text-zinc-200"
                                  >
                                    <span className="font-medium truncate">{t.title}</span>
                                    <span className="text-[10px] text-zinc-400 font-mono shrink-0 ml-2">View Task →</span>
                                  </Link>
                                ))}
                              </div>
                            </div>
                          )}

                          {/* Notes */}
                          {searchResults.notes && searchResults.notes.length > 0 && (
                            <div className="pt-2">
                              <p className="text-[10px] font-bold uppercase tracking-wider text-amber-400 px-2 mb-1.5 flex items-center gap-1.5 font-mono">
                                <FileText className="w-3.5 h-3.5" /> Notes ({searchResults.notes.length})
                              </p>
                              <div className="space-y-1">
                                {searchResults.notes.map((n) => (
                                  <Link
                                    key={n.id}
                                    href={n.url}
                                    onClick={() => setShowSearchDropdown(false)}
                                    className="flex items-center justify-between p-2 rounded-lg hover:bg-zinc-800/70 transition-colors text-zinc-200"
                                  >
                                    <span className="font-medium">{n.title}</span>
                                    <span className="text-[10px] text-zinc-400 font-mono">View Note →</span>
                                  </Link>
                                ))}
                              </div>
                            </div>
                          )}

                          {/* Users */}
                          {searchResults.users && searchResults.users.length > 0 && (
                            <div className="pt-2">
                              <p className="text-[10px] font-bold uppercase tracking-wider text-purple-400 px-2 mb-1.5 flex items-center gap-1.5 font-mono">
                                <Users className="w-3.5 h-3.5" /> Team Members ({searchResults.users.length})
                              </p>
                              <div className="space-y-1">
                                {searchResults.users.map((u) => (
                                  <div
                                    key={u.id}
                                    className="flex items-center justify-between p-2 rounded-lg hover:bg-zinc-800/70 transition-colors text-zinc-200"
                                  >
                                    <div>
                                      <p className="font-medium">{u.title}</p>
                                      <p className="text-[10px] text-zinc-400 font-mono">{u.email}</p>
                                    </div>
                                  </div>
                                ))}
                              </div>
                            </div>
                          )}
                        </>
                      )}
                    </div>
                  </div>
                </div>
              )}

              <div className="flex items-center gap-2">
                {actions}

                {/* Interactive Notification Popover */}
                <div className="relative">
                  <Button
                    variant="outline"
                    size="icon"
                    onClick={() => {
                      setShowNotifications((prev) => !prev);
                      if (!showNotifications) fetchNotifications();
                    }}
                    className="h-8 w-8 bg-zinc-900 border-zinc-800 hover:bg-zinc-800 text-zinc-300 rounded-lg relative cursor-pointer"
                    title="Notifications"
                  >
                    <Bell className="h-3.5 w-3.5" />
                    {notifications.length > 0 && (
                      <span className="absolute -top-1 -right-1 h-4 min-w-4 px-1 rounded-full bg-emerald-500 text-zinc-950 font-bold text-[10px] flex items-center justify-center border border-zinc-950 shadow-sm">
                        {notifications.length}
                      </span>
                    )}
                  </Button>

                  {showNotifications && (
                    <div className="absolute right-0 mt-2 w-80 sm:w-96 bg-zinc-900 border border-zinc-800 rounded-xl shadow-2xl z-50 overflow-hidden font-sans">
                      <div className="p-3 border-b border-zinc-800 bg-zinc-950 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <Bell className="w-4 h-4 text-emerald-400" />
                          <h4 className="text-xs font-bold text-zinc-100 uppercase tracking-wider">Notifications</h4>
                          {notifications.length > 0 && (
                            <span className="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold px-1.5 py-0.2 rounded-full">
                              {notifications.length} unread
                            </span>
                          )}
                        </div>
                        {notifications.length > 0 && (
                          <button
                            onClick={handleMarkAllAsRead}
                            className="text-[11px] text-zinc-400 hover:text-emerald-400 transition-colors flex items-center gap-1 font-medium cursor-pointer"
                          >
                            <CheckCheck className="w-3.5 h-3.5" /> Mark all read
                          </button>
                        )}
                      </div>

                      <div className="max-h-80 overflow-y-auto divide-y divide-zinc-800/60">
                        {loadingNotifs ? (
                          <div className="p-6 text-center text-xs text-zinc-400">Loading notifications...</div>
                        ) : notifications.length === 0 ? (
                          <div className="p-6 text-center text-xs text-zinc-400 space-y-1">
                            <CheckCheck className="w-8 h-8 text-emerald-500/60 mx-auto mb-2" />
                            <p className="font-semibold text-zinc-300">All caught up!</p>
                            <p className="text-[11px]">No unread notifications right now.</p>
                          </div>
                        ) : (
                          notifications.map((n) => (
                            <div
                              key={n.id}
                              className="p-3 hover:bg-zinc-800/50 transition-colors flex items-start justify-between gap-3 group"
                            >
                              <div className="space-y-1 min-w-0 flex-1">
                                <div className="flex items-center justify-between gap-2">
                                  <p className="text-xs font-semibold text-zinc-200 truncate">{n.title || n.type || "Notification"}</p>
                                  <span className="text-[10px] text-zinc-400 font-mono shrink-0">
                                    {n.created_at ? new Date(n.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''}
                                  </span>
                                </div>
                                <p className="text-xs text-zinc-400 leading-snug line-clamp-2">{n.message}</p>
                              </div>

                              <button
                                onClick={(e) => handleMarkAsRead(n.id, e)}
                                className="p-1 rounded bg-zinc-800 text-zinc-400 hover:bg-emerald-500/20 hover:text-emerald-400 transition-colors shrink-0 mt-0.5 cursor-pointer"
                                title="Mark as read"
                              >
                                <Check className="w-3.5 h-3.5" />
                              </button>
                            </div>
                          ))
                        )}
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </header>

          {/* Page Slot */}
          <main className="flex-1 w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 bg-zinc-950">
            <div className="w-full max-w-7xl mx-auto">
              {children}
            </div>
          </main>
        </SidebarInset>
      </div>
    </SidebarProvider>
  );
}