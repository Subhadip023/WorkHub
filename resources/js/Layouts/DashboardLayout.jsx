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
      {title && <Head title={`${title} - WorkHub`} />}

      <div className="min-h-screen bg-zinc-950 text-zinc-100 font-sans selection:bg-zinc-800 selection:text-white flex w-full">
        {/* Layered Sidebar with clean border separation */}
        <Sidebar className="border-r border-zinc-800 bg-zinc-950 text-zinc-100 z-30">
          {/* Header Workspace Switcher */}
          <SidebarHeader className="h-16 px-3.5 flex items-center justify-between border-b border-zinc-800 bg-zinc-950 shrink-0">
            <div className="flex items-center justify-between w-full">
              <button className="flex items-center justify-between w-full p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 transition-colors group">
                <div className="flex items-center gap-3 min-w-0">
                  <div className="h-7 w-7 rounded-md bg-zinc-100 text-zinc-950 flex items-center justify-center font-bold text-xs shadow-sm">
                    <Crown className="h-3.5 w-3.5" />
                  </div>
                  <div className="text-left truncate">
                    <span className="font-semibold text-xs text-zinc-100 block truncate leading-tight">
                      WorkHub
                    </span>
                    <span className="text-[10px] text-zinc-400 font-mono block leading-tight">
                      Engineering
                    </span>
                  </div>
                </div>
                <ChevronDown className="h-3.5 w-3.5 text-zinc-400 group-hover:text-zinc-200 transition-colors" />
              </button>
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
                      isActive={activeItem === "dashboard"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-zinc-900 data-[active=true]:text-white data-[active=true]:font-medium transition-colors rounded-md"
                    >
                      <Link href="/dashboard">
                        <LayoutDashboard className="h-4 w-4 text-zinc-400" />
                        <span>Dashboard</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "analytics"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-zinc-900 data-[active=true]:text-white data-[active=true]:font-medium transition-colors rounded-md"
                    >
                      <Link href="/new/analytics">
                        <BarChart3 className="h-4 w-4 text-zinc-400" />
                        <span>Analytics</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "projects"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-zinc-900 data-[active=true]:text-white data-[active=true]:font-medium transition-colors rounded-md"
                    >
                      <Link href="/new/projects">
                        <FolderKanban className="h-4 w-4 text-zinc-400" />
                        <span>Projects</span>
                      </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge className="bg-zinc-900 text-zinc-300 border border-zinc-800 font-mono text-[10px]">
                      12
                    </SidebarMenuBadge>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "tasks"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-zinc-900 data-[active=true]:text-white data-[active=true]:font-medium transition-colors rounded-md"
                    >
                      <Link href="/new/tasks">
                        <CheckSquare className="h-4 w-4 text-zinc-400" />
                        <span>My Tasks</span>
                      </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge className="bg-emerald-950/40 text-emerald-400 border border-emerald-800/50 font-mono text-[10px]">
                      5
                    </SidebarMenuBadge>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>

            {/* Group 2: Management */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider px-3 mb-2 font-mono">
                Management
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu className="space-y-1">
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "companies"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-zinc-900 data-[active=true]:text-white transition-colors rounded-md"
                    >
                      <Link href="/new/companies">
                        <Briefcase className="h-4 w-4 text-zinc-400" />
                        <span>Companies</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "issues"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-zinc-900 data-[active=true]:text-white transition-colors rounded-md"
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
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-zinc-900 data-[active=true]:text-white transition-colors rounded-md"
                    >
                      <Link href="/new/notes">
                        <FileText className="h-4 w-4 text-zinc-400" />
                        <span>Notes & Docs</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "team"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-zinc-900 data-[active=true]:text-white transition-colors rounded-md"
                    >
                      <Link href="/new/team">
                        <Users className="h-4 w-4 text-zinc-400" />
                        <span>Team Directory</span>
                      </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge className="bg-zinc-900 text-zinc-400 border border-zinc-800 font-mono text-[10px]">
                      18
                    </SidebarMenuBadge>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>

            {/* Group 3: System */}
            <SidebarGroup>
              <SidebarGroupLabel className="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider px-3 mb-2 font-mono">
                System Config
              </SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu className="space-y-1">
                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "permissions"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-zinc-900 data-[active=true]:text-white transition-colors rounded-md"
                    >
                      <Link href="/new/permissions">
                        <ShieldCheck className="h-4 w-4 text-zinc-400" />
                        <span>Permissions</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>

                  <SidebarMenuItem>
                    <SidebarMenuButton
                      asChild
                      isActive={activeItem === "settings"}
                      className="gap-3 hover:bg-zinc-900 text-zinc-300 data-[active=true]:bg-zinc-900 data-[active=true]:text-white transition-colors rounded-md"
                    >
                      <Link href="/new/settings">
                        <Settings className="h-4 w-4 text-zinc-400" />
                        <span>Settings</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>
          </SidebarContent>

          {/* Footer User Profile */}
          <SidebarFooter className="p-3 border-t border-zinc-800 bg-zinc-950 space-y-2">
            <a
              href="/old-dashboard"
              className="flex items-center justify-between w-full px-3 py-2 text-xs font-medium text-zinc-300 hover:text-white bg-zinc-900 border border-zinc-800 hover:border-zinc-700 rounded-lg transition-colors group"
            >
              <span className="flex items-center gap-2">
                <ArrowLeft className="h-3.5 w-3.5 text-zinc-400 group-hover:-translate-x-0.5 transition-transform" />
                Go Back to Old View
              </span>
              <span className="text-[10px] font-mono bg-zinc-950 px-1.5 py-0.5 rounded text-zinc-400 border border-zinc-800">
                Blade
              </span>
            </a>

            <div className="flex items-center justify-between p-2 rounded-lg bg-zinc-900 border border-zinc-800">
              <div className="flex items-center gap-2.5 min-w-0">
                <Avatar className="h-7 w-7 border border-zinc-700">
                  <AvatarFallback className="bg-zinc-800 text-zinc-200 font-medium text-xs">
                    {user?.name ? user.name.slice(0, 2).toUpperCase() : "US"}
                  </AvatarFallback>
                </Avatar>
                <div className="truncate">
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
                <span className="font-mono text-zinc-400">WorkHub</span>
                <ChevronRight className="h-3.5 w-3.5 text-zinc-600" />
                <span className="font-medium text-zinc-200 uppercase tracking-wide">
                  {title || activeItem}
                </span>
              </div>
            </div>

            {/* Right Header Actions */}
            <div className="flex items-center gap-3">
              <div className="relative hidden sm:flex items-center justify-between w-64 bg-zinc-900 border border-zinc-800 hover:border-zinc-700 rounded-lg px-3 py-1.5 text-xs text-zinc-400 transition-colors cursor-pointer group">
                <div className="flex items-center gap-2">
                  <Search className="h-3.5 w-3.5 text-zinc-400 group-hover:text-zinc-200 transition-colors" />
                  <span>Search or jump to...</span>
                </div>
                <kbd className="font-mono text-[10px] bg-zinc-950 border border-zinc-800 px-1.5 py-0.5 rounded text-zinc-400">
                  ⌘K
                </kbd>
              </div>

              <div className="flex items-center gap-2">
                {actions}
                <Button
                  variant="outline"
                  size="icon"
                  className="h-8 w-8 bg-zinc-900 border-zinc-800 hover:bg-zinc-800 text-zinc-300 rounded-lg relative"
                >
                  <Bell className="h-3.5 w-3.5" />
                  <span className="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-zinc-950"></span>
                </Button>
              </div>
            </div>
          </header>

          {/* Page Slot */}
          <main className="flex-1 w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 bg-zinc-950">
            {children}
          </main>
        </SidebarInset>
      </div>
    </SidebarProvider>
  );
}