import React from "react";
import { Head, Link, usePage } from "@inertiajs/react";
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
      {title && <Head title={`${title} - WorkHub Classic Premium`} />}

      <div className="min-h-screen bg-black text-neutral-100 font-sans selection:bg-neutral-800 selection:text-white flex w-full">
        {/* Subtle Background Radial Atmosphere */}
        <div className="fixed inset-0 overflow-hidden pointer-events-none z-0">
          <div className="absolute -top-40 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-neutral-900/30 via-black to-transparent blur-3xl"></div>
        </div>

        {/* Pure Solid Black Sidebar */}
        <Sidebar className="border-r border-neutral-800/80 bg-black !bg-black text-neutral-100 z-30">
          {/* Header Workspace Switcher */}
          <SidebarHeader className="h-16 px-3.5 flex items-center justify-between border-b border-neutral-800 bg-black shrink-0">
            <div className="flex items-center justify-between w-full">
              <button className="flex items-center justify-between w-full p-2 rounded-xl bg-black border border-neutral-800 hover:border-neutral-700 transition-all group">
                <div className="flex items-center gap-3 min-w-0">
                  <div className="h-8 w-8 rounded-lg bg-gradient-to-br from-neutral-100 via-neutral-300 to-neutral-500 text-black flex items-center justify-center font-extrabold text-xs shadow-md shadow-white/10 group-hover:scale-105 transition-transform">
                    <Crown className="h-4 w-4" />
                  </div>
                  <div className="text-left truncate">
                    <span className="font-bold text-xs text-white block truncate leading-tight">
                      WorkHub Enterprise
                    </span>
                    <span className="text-[10px] text-neutral-400 font-mono block leading-tight">
                      Pure Black Edition
                    </span>
                  </div>
                </div>
                <ChevronDown className="h-3.5 w-3.5 text-neutral-400 group-hover:text-white transition-colors" />
              </button>
            </div>
          </SidebarHeader>

          {/* Navigation Links */}
          <SidebarContent className="px-2.5 py-4 space-y-6 bg-black">
            {/* Group 1: Core Application */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-3 mb-2 font-mono">
                Main Suite
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu className="space-y-1">
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "dashboard"}
                      className="gap-3 hover:bg-neutral-900 data-[active=true]:bg-neutral-900 data-[active=true]:text-white data-[active=true]:font-semibold text-neutral-400 transition-all rounded-lg"
                    >
                      <Link href="/new/dashboard">
                        <LayoutDashboard className="h-4 w-4 text-neutral-300" />
                        <span>Dashboard</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "analytics"}
                      className="gap-3 hover:bg-neutral-900 data-[active=true]:bg-neutral-900 data-[active=true]:text-white data-[active=true]:font-semibold text-neutral-400 transition-all rounded-lg"
                    >
                      <Link href="/new/analytics">
                        <BarChart3 className="h-4 w-4 text-neutral-300" />
                        <span>Analytics</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "projects"}
                      className="gap-3 hover:bg-neutral-900 data-[active=true]:bg-neutral-900 data-[active=true]:text-white data-[active=true]:font-semibold text-neutral-400 transition-all rounded-lg"
                    >
                      <Link href="/new/projects">
                        <FolderKanban className="h-4 w-4 text-neutral-300" />
                        <span>Projects</span>
                      </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge className="bg-black text-neutral-300 border border-neutral-800 font-mono text-[10px]">
                      12
                    </SidebarMenuBadge>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "tasks"}
                      className="gap-3 hover:bg-neutral-900 data-[active=true]:bg-neutral-900 data-[active=true]:text-white data-[active=true]:font-semibold text-neutral-400 transition-all rounded-lg"
                    >
                      <Link href="/new/tasks">
                        <CheckSquare className="h-4 w-4 text-neutral-300" />
                        <span>My Tasks</span>
                      </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge className="bg-emerald-950/60 text-emerald-400 border border-emerald-800/60 font-mono text-[10px]">
                      5
                    </SidebarMenuBadge>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>

            {/* Group 2: Management */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-3 mb-2 font-mono">
                Management
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu className="space-y-1">
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "companies"}
                      className="gap-3 hover:bg-neutral-900 data-[active=true]:bg-neutral-900 data-[active=true]:text-white text-neutral-400 transition-all rounded-lg"
                    >
                      <Link href="/new/companies">
                        <Briefcase className="h-4 w-4 text-neutral-300" />
                        <span>Companies</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "issues"}
                      className="gap-3 hover:bg-neutral-900 data-[active=true]:bg-neutral-900 data-[active=true]:text-white text-neutral-400 transition-all rounded-lg"
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
                      className="gap-3 hover:bg-neutral-900 data-[active=true]:bg-neutral-900 data-[active=true]:text-white text-neutral-400 transition-all rounded-lg"
                    >
                      <Link href="/new/notes">
                        <FileText className="h-4 w-4 text-neutral-300" />
                        <span>Notes & Docs</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "team"}
                      className="gap-3 hover:bg-neutral-900 data-[active=true]:bg-neutral-900 data-[active=true]:text-white text-neutral-400 transition-all rounded-lg"
                    >
                      <Link href="/new/team">
                        <Users className="h-4 w-4 text-neutral-300" />
                        <span>Team Directory</span>
                      </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge className="bg-black text-neutral-400 border border-neutral-800 font-mono text-[10px]">
                      18
                    </SidebarMenuBadge>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>

            {/* Group 3: System */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[10px] font-semibold text-neutral-500 uppercase tracking-widest px-3 mb-2 font-mono">
                System Config
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu className="space-y-1">
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "permissions"}
                      className="gap-3 hover:bg-neutral-900 data-[active=true]:bg-neutral-900 data-[active=true]:text-white text-neutral-400 transition-all rounded-lg"
                    >
                      <Link href="/new/permissions">
                        <ShieldCheck className="h-4 w-4 text-neutral-300" />
                        <span>Permissions</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "settings"}
                      className="gap-3 hover:bg-neutral-900 data-[active=true]:bg-neutral-900 data-[active=true]:text-white text-neutral-400 transition-all rounded-lg"
                    >
                      <Link href="/new/settings">
                        <Settings className="h-4 w-4 text-neutral-300" />
                        <span>Settings</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>
          </SidebarContent>

          {/* Footer User & Legacy Bridge */}
          <SidebarFooter className="p-3 border-t border-neutral-800 bg-black space-y-2">
            <a
              href="/dashboard"
              className="flex items-center justify-between w-full px-3 py-2 text-xs font-medium text-neutral-300 hover:text-white bg-black border border-neutral-800 hover:border-neutral-700 rounded-xl transition-all group"
            >
              <span className="flex items-center gap-2">
                <ArrowLeft className="h-3.5 w-3.5 text-neutral-400 group-hover:-translate-x-0.5 transition-transform" />
                Go Back to Old View
              </span>
              <span className="text-[10px] font-mono bg-neutral-900 px-1.5 py-0.5 rounded text-neutral-400 border border-neutral-800">
                Blade
              </span>
            </a>

            <div className="flex items-center justify-between p-2 rounded-xl bg-black border border-neutral-800">
              <div className="flex items-center gap-2.5 min-w-0">
                <Avatar className="h-8 w-8 border border-neutral-700">
                  <AvatarFallback className="bg-gradient-to-br from-neutral-200 to-neutral-400 text-black font-bold text-xs">
                    {user?.name ? user.name.slice(0, 2).toUpperCase() : "US"}
                  </AvatarFallback>
                </Avatar>
                <div className="truncate">
                  <div className="text-xs font-semibold text-white truncate">
                    {user?.name || "Demo Administrator"}
                  </div>
                  <div className="text-[10px] text-neutral-400 font-mono truncate">
                    {user?.email || "admin@workhub.io"}
                  </div>
                </div>
              </div>
              <Button variant="ghost" size="icon" className="h-7 w-7 text-neutral-400 hover:text-white">
                <LogOut className="h-4 w-4" />
              </Button>
            </div>
          </SidebarFooter>

          <SidebarRail />
        </Sidebar>

        {/* Content Wrapper */}
        <SidebarInset className="relative flex flex-col flex-1 min-w-0 bg-black">
          {/* Header Navbar */}
          <header className="sticky top-0 z-20 border-b border-neutral-800 bg-black/90 backdrop-blur-2xl h-16 flex items-center justify-between px-4 sm:px-6">
            <div className="flex items-center gap-3">
              <SidebarTrigger className="text-neutral-400 hover:bg-neutral-900 hover:text-white rounded-lg p-2" />
              <div className="h-5 w-px bg-neutral-800"></div>
              <div className="flex items-center gap-2 text-xs">
                <span className="font-mono text-neutral-400">WorkHub</span>
                <ChevronRight className="h-3.5 w-3.5 text-neutral-600" />
                <span className="font-semibold text-white uppercase tracking-wider">
                  {title || activeItem}
                </span>
              </div>
            </div>

            {/* Right Header Command Palette & Actions */}
            <div className="flex items-center gap-3">
              <div className="relative hidden sm:flex items-center justify-between w-64 lg:w-72 bg-black border border-neutral-800 hover:border-neutral-700 rounded-xl px-3.5 py-1.5 text-xs text-neutral-400 transition-colors cursor-pointer group">
                <div className="flex items-center gap-2">
                  <Search className="h-4 w-4 text-neutral-400 group-hover:text-white transition-colors" />
                  <span>Search or jump to...</span>
                </div>
                <kbd className="font-mono text-[10px] bg-neutral-900 border border-neutral-800 px-1.5 py-0.5 rounded text-neutral-400">
                  ⌘K
                </kbd>
              </div>

              <div className="flex items-center gap-2">
                {actions}
                <Button
                  variant="outline"
                  size="icon"
                  className="h-9 w-9 bg-black border-neutral-800 hover:bg-neutral-900 hover:text-white text-neutral-400 rounded-xl relative"
                >
                  <Bell className="h-4 w-4" />
                  <span className="absolute top-2 right-2 h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-black"></span>
                </Button>
              </div>
            </div>
          </header>

          {/* Page Slot */}
          <main className="flex-1 w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-black">
            {children}
          </main>

          {/* Classic Black Footer */}
          <footer className="mt-auto border-t border-neutral-800 bg-black py-4 text-center text-xs text-neutral-500 font-mono">
            <div className="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
              <span>WorkHub Pure Black Edition • Inertia.js + React</span>
              <div className="flex items-center gap-4">
                <a href="/dashboard" className="hover:text-neutral-300 transition-colors">
                  Classic Dashboard
                </a>
                <span className="text-neutral-800">•</span>
                <span className="text-neutral-300 font-medium">Powered by shadcn/ui Sidebar</span>
              </div>
            </div>
          </footer>
        </SidebarInset>
      </div>
    </SidebarProvider>
  );
}
