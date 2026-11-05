import React, { useState } from "react";
import { Link, router, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  Settings as SettingsIcon,
  User,
  Lock,
  Building2,
  Check,
  AlertCircle,
  Loader2,
  Mail,
  ShieldCheck,
  ArrowRight,
  Sparkles,
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";

export default function Settings({
  user = {},
  companies = [],
  current_workspace_name = "All Workspaces",
}) {
  const { flash = {} } = usePage().props;

  const [activeTab, setActiveTab] = useState("profile"); // 'profile' | 'security' | 'workspaces'

  // Profile form
  const [profileForm, setProfileForm] = useState({
    name: user.name || "",
    email: user.email || "",
  });
  const [profileErrors, setProfileErrors] = useState({});
  const [isSubmittingProfile, setIsSubmittingProfile] = useState(false);

  // Security form
  const [passwordForm, setPasswordForm] = useState({
    current_password: "",
    password: "",
    password_confirmation: "",
  });
  const [passwordErrors, setPasswordErrors] = useState({});
  const [isSubmittingPassword, setIsSubmittingPassword] = useState(false);

  const handleProfileSubmit = (e) => {
    e.preventDefault();
    setIsSubmittingProfile(true);
    setProfileErrors({});

    router.post("/v2/settings/profile", profileForm, {
      preserveScroll: true,
      onSuccess: () => setIsSubmittingProfile(false),
      onError: (errs) => {
        setIsSubmittingProfile(false);
        setProfileErrors(errs);
      },
    });
  };

  const handlePasswordSubmit = (e) => {
    e.preventDefault();
    setIsSubmittingPassword(true);
    setPasswordErrors({});

    router.post("/v2/settings/password", passwordForm, {
      preserveScroll: true,
      onSuccess: () => {
        setIsSubmittingPassword(false);
        setPasswordForm({
          current_password: "",
          password: "",
          password_confirmation: "",
        });
      },
      onError: (errs) => {
        setIsSubmittingPassword(false);
        setPasswordErrors(errs);
      },
    });
  };

  return (
    <DashboardLayout title="Account Settings" activeItem="profile">
      <div className="space-y-6 pb-12 max-w-4xl mx-auto">
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
              <div className="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                <SettingsIcon className="w-5 h-5" />
              </div>
              <h1 className="text-2xl font-bold tracking-tight text-zinc-100">
                Account Settings
              </h1>
              <Badge variant="outline" className="border-emerald-500/30 text-emerald-400 bg-emerald-500/5 font-mono text-xs">
                User Profile
              </Badge>
            </div>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1">
              Manage your personal credentials, workspace affiliations, and security preferences.
            </p>
          </div>
        </div>

        {/* Navigation Tabs */}
        <div className="flex items-center gap-2 border-b border-zinc-800 pb-2">
          {[
            { id: "profile", label: "Profile Information", icon: User },
            { id: "security", label: "Security & Password", icon: Lock },
            { id: "notifications", label: "Email Notifications", icon: Mail },
            { id: "interface", label: "UI Version", icon: Sparkles },
            { id: "workspaces", label: "My Workspaces", icon: Building2 },
          ].map((tab) => {
            const Icon = tab.icon;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium transition-all flex items-center gap-2 ${
                  activeTab === tab.id
                    ? "bg-zinc-800 text-zinc-100 font-semibold border border-zinc-700 shadow-sm"
                    : "text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900"
                }`}
              >
                <Icon className="w-4 h-4" />
                <span>{tab.label}</span>
              </button>
            );
          })}
        </div>

        {/* PROFILE TAB */}
        {activeTab === "profile" && (
          <div className="space-y-6">
            <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
              <CardHeader className="p-6 pb-4">
                <div className="flex items-center gap-4">
                  <Avatar className="w-16 h-16 border-2 border-zinc-700 ring-4 ring-zinc-800">
                    {user.profile_image ? (
                      <AvatarImage src={user.profile_image} alt={user.name} />
                    ) : null}
                    <AvatarFallback className="bg-zinc-800 text-zinc-200 font-bold text-xl">
                      {user.avatar}
                    </AvatarFallback>
                  </Avatar>
                  <div>
                    <CardTitle className="text-lg font-bold text-zinc-100">{user.name}</CardTitle>
                    <CardDescription className="text-xs text-zinc-400 font-mono mt-0.5">
                      {user.email} • Joined {user.created_at}
                    </CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent className="p-6 pt-2">
                <form onSubmit={handleProfileSubmit} className="space-y-4 max-w-lg">
                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Full Name
                    </label>
                    <input
                      type="text"
                      required
                      value={profileForm.name}
                      onChange={(e) => setProfileForm({ ...profileForm, name: e.target.value })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 transition-colors"
                    />
                    {profileErrors.name && (
                      <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                        <AlertCircle className="w-3 h-3" /> {profileErrors.name}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Email Address
                    </label>
                    <input
                      type="email"
                      required
                      value={profileForm.email}
                      onChange={(e) => setProfileForm({ ...profileForm, email: e.target.value })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-xs sm:text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-emerald-500/60 transition-colors"
                    />
                    {profileErrors.email && (
                      <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                        <AlertCircle className="w-3 h-3" /> {profileErrors.email}
                      </p>
                    )}
                  </div>

                  <div className="pt-2">
                    <Button
                      type="submit"
                      size="sm"
                      disabled={isSubmittingProfile}
                      className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold px-4"
                    >
                      {isSubmittingProfile ? (
                        <span className="flex items-center gap-2">
                          <Loader2 className="w-3.5 h-3.5 animate-spin" /> Saving...
                        </span>
                      ) : (
                        "Save Profile"
                      )}
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          </div>
        )}

        {/* SECURITY TAB */}
        {activeTab === "security" && (
          <div className="space-y-6">
            <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
              <CardHeader className="p-6 pb-2">
                <CardTitle className="text-base font-bold text-zinc-100">Update Password</CardTitle>
                <CardDescription className="text-xs text-zinc-400">
                  Ensure your account is using a long, random password to stay secure.
                </CardDescription>
              </CardHeader>
              <CardContent className="p-6 pt-2">
                <form onSubmit={handlePasswordSubmit} className="space-y-4 max-w-lg">
                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Current Password
                    </label>
                    <input
                      type="password"
                      required
                      value={passwordForm.current_password}
                      onChange={(e) => setPasswordForm({ ...passwordForm, current_password: e.target.value })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-xs sm:text-sm text-zinc-100 focus:outline-none focus:border-emerald-500/60 transition-colors"
                    />
                    {passwordErrors.current_password && (
                      <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                        <AlertCircle className="w-3 h-3" /> {passwordErrors.current_password}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      New Password
                    </label>
                    <input
                      type="password"
                      required
                      value={passwordForm.password}
                      onChange={(e) => setPasswordForm({ ...passwordForm, password: e.target.value })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-xs sm:text-sm text-zinc-100 focus:outline-none focus:border-emerald-500/60 transition-colors"
                    />
                    {passwordErrors.password && (
                      <p className="text-xs text-rose-400 mt-1 flex items-center gap-1 font-mono">
                        <AlertCircle className="w-3 h-3" /> {passwordErrors.password}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className="block text-xs font-mono font-medium text-zinc-300 mb-1.5">
                      Confirm New Password
                    </label>
                    <input
                      type="password"
                      required
                      value={passwordForm.password_confirmation}
                      onChange={(e) => setPasswordForm({ ...passwordForm, password_confirmation: e.target.value })}
                      className="w-full bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-xs sm:text-sm text-zinc-100 focus:outline-none focus:border-emerald-500/60 transition-colors"
                    />
                  </div>

                  <div className="pt-2">
                    <Button
                      type="submit"
                      size="sm"
                      disabled={isSubmittingPassword}
                      className="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-bold px-4"
                    >
                      {isSubmittingPassword ? "Updating Password..." : "Update Password"}
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          </div>
        )}

        {/* NOTIFICATIONS TAB */}
        {activeTab === "notifications" && (
          <div className="space-y-6">
            <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
              <CardHeader className="p-6 pb-4">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                  <div>
                    <CardTitle className="text-base font-bold text-zinc-100 flex items-center gap-2">
                      <Mail className="w-4 h-4 text-emerald-400" /> Email Notifications Engine
                    </CardTitle>
                    <CardDescription className="text-xs text-zinc-400 mt-1">
                      Control whether you receive instant email updates for all workspace activity.
                    </CardDescription>
                  </div>
                  <Badge variant="outline" className={user.email_notifications !== false ? "border-emerald-500/30 text-emerald-400 bg-emerald-500/10 font-mono text-xs" : "border-zinc-700 text-zinc-400 bg-zinc-800 font-mono text-xs"}>
                    {user.email_notifications !== false ? "Active / Enabled" : "Disabled"}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="p-6 pt-2 space-y-6">
                <div className="p-5 rounded-2xl border border-zinc-800 bg-zinc-950/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                  <div className="space-y-1">
                    <h4 className="font-bold text-sm text-zinc-100">Send Email Alerts for All Events</h4>
                    <p className="text-xs text-zinc-400 max-w-xl">
                      When enabled, WorkHub sends automated emails to <strong className="text-zinc-200 font-mono">{user.email}</strong> whenever tasks are assigned to you, statuses change, deadlines are updated, projects are created, or membership requests arrive.
                    </p>
                  </div>
                  <button
                    type="button"
                    onClick={() => {
                      router.post("/settings/notifications", {
                        email_notifications: user.email_notifications === false,
                      }, { preserveScroll: true });
                    }}
                    className={`relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none ${
                      user.email_notifications !== false ? "bg-emerald-500" : "bg-zinc-700"
                    }`}
                  >
                    <span
                      className={`pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                        user.email_notifications !== false ? "translate-x-5" : "translate-x-0"
                      }`}
                    />
                  </button>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs text-zinc-400">
                  <div className="p-3.5 rounded-xl bg-zinc-900 border border-zinc-800 space-y-1">
                    <span className="text-emerald-400 font-semibold">✓ Tasks & Assignments</span>
                    <p className="text-[11px] text-zinc-500">Creation, assignments, status changes & deadlines</p>
                  </div>
                  <div className="p-3.5 rounded-xl bg-zinc-900 border border-zinc-800 space-y-1">
                    <span className="text-cyan-400 font-semibold">✓ Projects</span>
                    <p className="text-[11px] text-zinc-500">New team projects & organization updates</p>
                  </div>
                  <div className="p-3.5 rounded-xl bg-zinc-900 border border-zinc-800 space-y-1">
                    <span className="text-indigo-400 font-semibold">✓ Team & Workspaces</span>
                    <p className="text-[11px] text-zinc-500">Join requests, approvals & invitations</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        )}

        {/* INTERFACE TAB */}
        {activeTab === "interface" && (
          <div className="space-y-6">
            <Card className="bg-zinc-900/80 border-zinc-800 shadow-sm">
              <CardHeader className="p-6 pb-4">
                <div className="flex items-center justify-between">
                  <div>
                    <CardTitle className="text-lg font-bold text-zinc-100 flex items-center gap-2">
                      <Sparkles className="w-5 h-5 text-emerald-400" /> Interface & Theme Version
                    </CardTitle>
                    <CardDescription className="text-xs text-zinc-400 font-mono mt-1">
                      Choose between the Next-Gen Modern React SPA or the Classic Blade layout. Both use clean URLs and the same database.
                    </CardDescription>
                  </div>
                  <Badge variant="outline" className="border-emerald-500/30 text-emerald-400 bg-emerald-500/10 font-mono text-xs">
                    Current: {user.ui_version === "v2" ? "Modern V2" : "Classic V1"}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="p-6 pt-2 space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {/* Modern V2 Option */}
                  <div
                    className={`p-5 rounded-2xl border transition-all ${
                      user.ui_version === "v2"
                        ? "bg-emerald-500/5 border-emerald-500/40 ring-1 ring-emerald-500/20"
                        : "bg-zinc-950/60 border-zinc-800 hover:border-zinc-700"
                    }`}
                  >
                    <div className="flex items-center justify-between mb-3">
                      <div className="flex items-center gap-2">
                        <span className="p-2 rounded-lg bg-emerald-500/10 text-emerald-400">
                          <Sparkles className="w-4 h-4" />
                        </span>
                        <h4 className="font-bold text-zinc-100 text-sm">Modern V2 Suite</h4>
                      </div>
                      {user.ui_version === "v2" && (
                        <span className="text-xs font-mono text-emerald-400 flex items-center gap-1 font-semibold">
                          <Check className="w-3.5 h-3.5" /> Active
                        </span>
                      )}
                    </div>
                    <p className="text-xs text-zinc-400 leading-relaxed mb-4">
                      Single-Page React 18 interface with instant page transitions, interactive drawers, dark mode aesthetics, and clean URLs without any <code className="text-emerald-400 bg-zinc-900 px-1 py-0.5 rounded">/v2/</code> prefix.
                    </p>
                    <Button
                      asChild={user.ui_version !== "v2"}
                      disabled={user.ui_version === "v2"}
                      size="sm"
                      className="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs rounded-xl h-9"
                    >
                      {user.ui_version === "v2" ? (
                        <span>Currently Active</span>
                      ) : (
                        <a href="/ui/switch/v2">Switch to Modern V2</a>
                      )}
                    </Button>
                  </div>

                  {/* Classic V1 Option */}
                  <div
                    className={`p-5 rounded-2xl border transition-all ${
                      user.ui_version === "v1"
                        ? "bg-indigo-500/5 border-indigo-500/40 ring-1 ring-indigo-500/20"
                        : "bg-zinc-950/60 border-zinc-800 hover:border-zinc-700"
                    }`}
                  >
                    <div className="flex items-center justify-between mb-3">
                      <div className="flex items-center gap-2">
                        <span className="p-2 rounded-lg bg-zinc-800 text-zinc-300">
                          <ShieldCheck className="w-4 h-4" />
                        </span>
                        <h4 className="font-bold text-zinc-100 text-sm">Classic V1 View</h4>
                      </div>
                      {user.ui_version === "v1" && (
                        <span className="text-xs font-mono text-indigo-400 flex items-center gap-1 font-semibold">
                          <Check className="w-3.5 h-3.5" /> Active
                        </span>
                      )}
                    </div>
                    <p className="text-xs text-zinc-400 leading-relaxed mb-4">
                      Traditional server-rendered Laravel Blade interface with Bootstrap and SB Admin. Full backward compatibility on the exact same clean URLs.
                    </p>
                    <Button
                      asChild={user.ui_version !== "v1"}
                      disabled={user.ui_version === "v1"}
                      variant="outline"
                      size="sm"
                      className="w-full border-zinc-700 hover:bg-zinc-800 text-zinc-200 font-semibold text-xs rounded-xl h-9"
                    >
                      {user.ui_version === "v1" ? (
                        <span>Currently Active</span>
                      ) : (
                        <a href="/ui/switch/v1">Switch to Classic V1</a>
                      )}
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        )}

        {/* WORKSPACES TAB */}
        {activeTab === "workspaces" && (
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="text-sm font-bold text-zinc-200">Connected Organizations</h3>
              <Button asChild size="sm" variant="outline" className="border-zinc-800 text-xs text-zinc-300">
                <Link href="/v2/companies">
                  <Building2 className="w-3.5 h-3.5 mr-1 text-emerald-400" /> Manage All
                </Link>
              </Button>
            </div>

            {companies.length > 0 ? (
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                {companies.map((c) => (
                  <div
                    key={c.id}
                    className="p-4 rounded-xl bg-zinc-900/80 border border-zinc-800/80 flex items-center justify-between gap-3"
                  >
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center font-bold text-emerald-400">
                        {c.name.charAt(0).toUpperCase()}
                      </div>
                      <div>
                        <h4 className="font-bold text-zinc-100 text-sm">{c.name}</h4>
                        <span className="text-[11px] font-mono text-zinc-400">Code: {c.code}</span>
                      </div>
                    </div>

                    <Badge
                      variant="outline"
                      className={`text-[10px] font-semibold ${
                        c.role === "Admin"
                          ? "border-indigo-500/30 text-indigo-400 bg-indigo-500/10"
                          : "border-zinc-700 text-zinc-400"
                      }`}
                    >
                      {c.role}
                    </Badge>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-xs text-zinc-500 italic">No connected organizations.</p>
            )}
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
