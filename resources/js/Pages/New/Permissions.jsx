import React, { useState } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
  ShieldCheck,
  Crown,
  Users,
  Check,
  X,
  Lock,
  Building2,
  FolderKanban,
  CheckSquare,
  KeyRound,
  FileText,
} from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";

export default function Permissions({
  roles = [],
  matrix = [],
  user_memberships = [],
  current_workspace_name = "All Workspaces",
}) {
  const [selectedRole, setSelectedRole] = useState("all");

  return (
    <DashboardLayout title="System Permissions & RBAC" activeItem="permissions">
      <div className="space-y-6 pb-12">
        {/* Top Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-zinc-800">
          <div>
            <div className="flex items-center gap-2.5">
              <div className="p-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                <ShieldCheck className="w-5 h-5" />
              </div>
              <h1 className="text-2xl font-bold tracking-tight text-zinc-100">
                System Permissions & Roles (RBAC)
              </h1>
              <Badge variant="outline" className="border-indigo-500/30 text-indigo-400 bg-indigo-500/5 font-mono text-xs">
                RBAC Hierarchy
              </Badge>
            </div>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1">
              Review workspace privilege boundaries, capabilities, and role definitions across WorkHub.
            </p>
          </div>
        </div>

        {/* Roles Breakdown Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {roles.map((role, idx) => (
            <Card key={idx} className="bg-zinc-900/80 border-zinc-800 shadow-sm flex flex-col justify-between">
              <CardHeader className="p-5 pb-3">
                <div className="flex items-center justify-between mb-2">
                  <div className="p-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-300">
                    <Crown className="w-4 h-4 text-amber-400" />
                  </div>
                  <Badge variant="outline" className="text-[10px] font-mono border-zinc-700 text-zinc-400">
                    {role.badge}
                  </Badge>
                </div>
                <CardTitle className="text-base font-bold text-zinc-100">
                  {role.name}
                </CardTitle>
                <p className="text-xs text-zinc-400 mt-1 leading-relaxed">
                  {role.description}
                </p>
              </CardHeader>
              <CardContent className="p-5 pt-0">
                <div className="text-[11px] text-zinc-500 font-mono border-t border-zinc-800/80 pt-3 flex items-center justify-between">
                  <span>Authorized Workspaces</span>
                  <span className="font-bold text-zinc-200">{role.users_count}</span>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* User's Active Memberships */}
        {user_memberships.length > 0 && (
          <div className="p-4 rounded-2xl bg-zinc-900/60 border border-zinc-800">
            <h3 className="text-xs font-mono font-bold text-zinc-400 uppercase tracking-wider mb-3">
              Your Current Role Assignments
            </h3>
            <div className="flex flex-wrap gap-2.5">
              {user_memberships.map((m, idx) => (
                <div
                  key={idx}
                  className="px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center gap-2 text-xs"
                >
                  <Building2 className="w-3.5 h-3.5 text-zinc-500" />
                  <span className="font-semibold text-zinc-200">{m.company_name}</span>
                  <Badge
                    variant="outline"
                    className={`text-[10px] font-semibold ${
                      m.is_admin
                        ? "border-indigo-500/30 text-indigo-400 bg-indigo-500/10"
                        : "border-zinc-700 text-zinc-400"
                    }`}
                  >
                    {m.role}
                  </Badge>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Capabilities Matrix Table */}
        <div className="bg-zinc-900/80 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
          <div className="p-4 bg-zinc-950/70 border-b border-zinc-800 flex items-center justify-between">
            <h3 className="font-bold text-sm text-zinc-100 flex items-center gap-2">
              <KeyRound className="w-4 h-4 text-emerald-400" />
              <span>Permission Capabilities Matrix</span>
            </h3>
            <span className="text-xs font-mono text-zinc-500">Super Admin • Org Admin • Manager • Member</span>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs sm:text-sm">
              <thead className="bg-zinc-950/40 text-zinc-400 font-mono text-[11px] uppercase border-b border-zinc-800">
                <tr>
                  <th className="py-3 px-4">Capability Action</th>
                  <th className="py-3 px-4 text-center">Super Admin</th>
                  <th className="py-3 px-4 text-center">Org Admin</th>
                  <th className="py-3 px-4 text-center">Project Lead</th>
                  <th className="py-3 px-4 text-center">Member</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-zinc-800/60">
                {matrix.map((section, sIdx) => (
                  <React.Fragment key={sIdx}>
                    <tr className="bg-zinc-950/90 font-mono font-bold text-[11px] text-emerald-400/90 tracking-wider">
                      <td colSpan={5} className="py-2.5 px-4 uppercase">
                        {section.category}
                      </td>
                    </tr>
                    {section.permissions.map((perm, pIdx) => (
                      <tr key={pIdx} className="hover:bg-zinc-800/30 transition-colors">
                        <td className="py-3 px-4 font-medium text-zinc-200">
                          {perm.name}
                        </td>
                        <td className="py-3 px-4 text-center">
                          {perm.super_admin ? (
                            <Check className="w-4 h-4 text-emerald-400 mx-auto" />
                          ) : (
                            <X className="w-4 h-4 text-zinc-600 mx-auto" />
                          )}
                        </td>
                        <td className="py-3 px-4 text-center">
                          {perm.org_admin ? (
                            <Check className="w-4 h-4 text-emerald-400 mx-auto" />
                          ) : (
                            <X className="w-4 h-4 text-zinc-600 mx-auto" />
                          )}
                        </td>
                        <td className="py-3 px-4 text-center">
                          {perm.manager ? (
                            <Check className="w-4 h-4 text-emerald-400 mx-auto" />
                          ) : (
                            <X className="w-4 h-4 text-zinc-600 mx-auto" />
                          )}
                        </td>
                        <td className="py-3 px-4 text-center">
                          {perm.member ? (
                            <Check className="w-4 h-4 text-emerald-400 mx-auto" />
                          ) : (
                            <X className="w-4 h-4 text-zinc-600 mx-auto" />
                          )}
                        </td>
                      </tr>
                    ))}
                  </React.Fragment>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}
