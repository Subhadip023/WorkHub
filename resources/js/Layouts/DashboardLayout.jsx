import React from "react";
import { Head, Link, usePage } from "@inertiajs/react";
import {
  LayoutDashboard,
  FolderKanban,
  CheckSquare,
  Users,
  Search,
  Bell,
  Zap,
  Activity,
  BarChart3,
  FileText,
  ShieldCheck,
  Settings,
  LogOut,
  ChevronRight,
  Briefcase,
  AlertCircle,
  ArrowLeft
} from "lucide-react";

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
  const { auth } = usePage().props;
  const user = auth?.user;

  return (
    <SidebarProvider defaultOpen={true}>
      {title && <Head title={`${title} - WorkHub`} />}

      <div className="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white flex w-full">
        {/* Background ambient glowing shapes */}
        <div className="fixed inset-0 overflow-hidden pointer-events-none z-0">
          <div className="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl"></div>
          <div className="absolute top-1/3 -right-40 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl"></div>
          <div className="absolute -bottom-40 left-1/3 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl"></div>
        </div>

        {/* Official shadcn Sidebar */}
        <Sidebar className="border-r border-slate-800/80 bg-slate-950/95 backdrop-blur-xl z-30">
          {/* Sidebar Header */}
          <SidebarHeader className="p-4 border-b border-slate-800/80">
            <div className="flex items-center justify-between">
              <Link href="/new/dashboard" className="flex items-center gap-3 group">
                <div className="h-9 w-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                  <Zap className="h-5 w-5 text-white" />
                </div>
                <div>
                  <span className="font-extrabold text-base tracking-tight text-white block">
                    WorkHub
                  </span>
                  <span className="text-[10px] text-indigo-400 font-medium block">
                    Inertia + shadcn UI
                  </span>
                </div>
              </Link>
            </div>
          </SidebarHeader>

          {/* Sidebar Content */}
          <SidebarContent className="px-2 py-4 space-y-6">
            {/* Group 1: Main Navigation */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 mb-2">
                Main Menu
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu>
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "dashboard"}
                      className="gap-3 hover:bg-slate-900 data-[active=true]:bg-indigo-600/20 data-[active=true]:text-indigo-400 font-medium"
                    >
                      <Link href="/new/dashboard">
                        <LayoutDashboard className="h-4 w-4" />
                        <span>Dashboard</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "analytics"}
                      className="gap-3 hover:bg-slate-900 data-[active=true]:bg-indigo-600/20 data-[active=true]:text-indigo-400 font-medium text-slate-300"
                    >
                      <Link href="/new/analytics">
                        <BarChart3 className="h-4 w-4" />
                        <span>Analytics</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "projects"}
                      className="gap-3 hover:bg-slate-900 data-[active=true]:bg-indigo-600/20 data-[active=true]:text-indigo-400 font-medium text-slate-300"
                    >
                      <Link href="/new/projects">
                        <FolderKanban className="h-4 w-4" />
                        <span>Projects</span>
                      </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge className="bg-indigo-500/20 text-indigo-300">
                      12
                    </SidebarMenuBadge>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "tasks"}
                      className="gap-3 hover:bg-slate-900 data-[active=true]:bg-indigo-600/20 data-[active=true]:text-indigo-400 font-medium text-slate-300"
                    >
                      <Link href="/new/tasks">
                        <CheckSquare className="h-4 w-4" />
                        <span>My Tasks</span>
                      </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge className="bg-rose-500/20 text-rose-300">
                      5
                    </SidebarMenuBadge>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>

            {/* Group 2: Management */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 mb-2">
                Management
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu>
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "companies"}
                      className="gap-3 hover:bg-slate-900 data-[active=true]:bg-indigo-600/20 data-[active=true]:text-indigo-400 font-medium text-slate-300"
                    >
                      <Link href="/new/companies">
                        <Briefcase className="h-4 w-4" />
                        <span>Companies</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "issues"}
                      className="gap-3 hover:bg-slate-900 data-[active=true]:bg-indigo-600/20 data-[active=true]:text-indigo-400 font-medium text-slate-300"
                    >
                      <Link href="/new/issues">
                        <AlertCircle className="h-4 w-4 text-amber-400" />
                        <span>Issues Log</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "notes"}
                      className="gap-3 hover:bg-slate-900 data-[active=true]:bg-indigo-600/20 data-[active=true]:text-indigo-400 font-medium text-slate-300"
                    >
                      <Link href="/new/notes">
                        <FileText className="h-4 w-4" />
                        <span>Notes & Docs</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "team"}
                      className="gap-3 hover:bg-slate-900 data-[active=true]:bg-indigo-600/20 data-[active=true]:text-indigo-400 font-medium text-slate-300"
                    >
                      <Link href="/new/team">
                        <Users className="h-4 w-4" />
                        <span>Team Members</span>
                      </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge className="bg-slate-800 text-slate-400">
                      18
                    </SidebarMenuBadge>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>

            {/* Group 3: System */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3 mb-2">
                System
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu>
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "permissions"}
                      className="gap-3 hover:bg-slate-900 data-[active=true]:bg-indigo-600/20 data-[active=true]:text-indigo-400 font-medium text-slate-300"
                    >
                      <Link href="/new/permissions">
                        <ShieldCheck className="h-4 w-4" />
                        <span>Permissions</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "settings"}
                      className="gap-3 hover:bg-slate-900 data-[active=true]:bg-indigo-600/20 data-[active=true]:text-indigo-400 font-medium text-slate-300"
                    >
                      <Link href="/new/settings">
                        <Settings className="h-4 w-4" />
                        <span>Settings</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>
          </SidebarContent>

          {/* Sidebar Footer User Info */}
          <SidebarFooter className="p-3 border-t border-slate-800/80 bg-slate-950/60">
            {/* Quick Switch back to Classic Blade Dashboard */}
            <a
              href="/dashboard"
              className="flex items-center justify-between w-full px-3 py-2 mb-2 text-xs font-semibold text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 rounded-xl transition-all group"
            >
              <span className="flex items-center gap-2">
                <ArrowLeft className="h-3.5 w-3.5 group-hover:-translate-x-0.5 transition-transform text-rose-400" />
                Go Back to Old Dashboard
              </span>
              <span className="text-[10px] bg-rose-500/20 text-rose-300 font-mono px-1.5 py-0.5 rounded">
                Blade
              </span>
            </a>

            <div className="flex items-center justify-between p-2 rounded-xl bg-slate-900/80 border border-slate-800">
              <div className="flex items-center gap-2.5 min-w-0">
                <Avatar className="h-8 w-8">
                  <AvatarFallback className="bg-gradient-to-br from-indigo-600 to-violet-700 text-white font-bold">
                    {user?.name ? user.name.slice(0, 2).toUpperCase() : "U"}
                  </AvatarFallback>
                </Avatar>
                <div className="truncate">
                  <div className="text-xs font-semibold text-slate-200 truncate">
                    {user?.name || "Demo Administrator"}
                  </div>
                  <div className="text-[10px] text-slate-400 truncate">
                    {user?.email || "admin@workhub.io"}
                  </div>
                </div>
              </div>
              <Button variant="ghost" size="icon" className="h-7 w-7 text-slate-400 hover:text-slate-200">
                <LogOut className="h-4 w-4" />
              </Button>
            </div>
          </SidebarFooter>

          <SidebarRail />
        </Sidebar>

        {/* Sidebar Inset Wrapper for Main Content Area */}
        <SidebarInset className="relative z-10 flex flex-col flex-1 min-w-0 bg-transparent">
          {/* Top Navbar Header */}
          <header className="sticky top-0 z-20 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-xl h-16 flex items-center justify-between px-4 sm:px-6">
            <div className="flex items-center gap-3">
              {/* Trigger for collapsing/opening sidebar */}
              <SidebarTrigger className="text-slate-300 hover:bg-slate-900 hover:text-white" />
              <div className="h-5 w-px bg-slate-800"></div>
              <div className="flex items-center gap-2">
                <span className="text-xs font-medium text-slate-400">Dashboard</span>
                <ChevronRight className="h-3.5 w-3.5 text-slate-600" />
                <span className="text-xs font-semibold text-slate-200 uppercase tracking-wider">
                  {title || activeItem}
                </span>
              </div>
            </div>

            {/* Right Header Actions */}
            <div className="flex items-center gap-3">
              {/* Search Input */}
              <div className="relative hidden sm:block w-56 lg:w-72">
                <Search className="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                <input
                  type="text"
                  placeholder="Search tasks, projects..."
                  className="w-full bg-slate-900/90 border border-slate-800 rounded-lg pl-9 pr-4 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                />
              </div>

              <Button variant="outline" size="icon" className="relative">
                <Bell className="h-4 w-4 text-slate-300" />
                <span className="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-indigo-500 ring-2 ring-slate-950 animate-pulse"></span>
              </Button>

              {actions}
            </div>
          </header>

          {/* Page Slot Content */}
          <main className="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {children}
          </main>

          {/* Footer */}
          <footer className="mt-auto border-t border-slate-800/80 bg-slate-950/80 py-4 text-center text-xs text-slate-500">
            <div className="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
              <span>WorkHub Inertia.js + React JS Application</span>
              <div className="flex items-center gap-4">
                <a href="/dashboard" className="hover:text-slate-300 transition-colors">
                  Classic Dashboard
                </a>
                <span className="text-slate-800">•</span>
                <span className="text-indigo-400 font-medium">Powered by shadcn/ui Sidebar</span>
              </div>
            </div>
          </footer>
        </SidebarInset>
      </div>
    </SidebarProvider>
  );
}
