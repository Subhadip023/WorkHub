import React from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Link } from "@inertiajs/react";
import { Wrench, Sparkles, ArrowLeft, LayoutDashboard, CheckSquare, Clock } from "lucide-react";
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";

export default function ComingSoon({ feature = "This Feature", activeItem = "coming-soon" }) {
  return (
    <DashboardLayout title={feature} activeItem={activeItem}>
      <div className="min-h-[70vh] flex items-center justify-center py-12">
        <Card className="max-w-xl w-full border-slate-800/80 bg-slate-900/60 backdrop-blur-xl shadow-2xl relative overflow-hidden text-center p-8">
          {/* Ambient Glow */}
          <div className="absolute -top-24 -left-24 w-64 h-64 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
          <div className="absolute -bottom-24 -right-24 w-64 h-64 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>

          <CardContent className="space-y-6 relative z-10 pt-4">
            {/* Animated Icon Header */}
            <div className="mx-auto h-20 w-20 rounded-3xl bg-gradient-to-tr from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 flex items-center justify-center shadow-xl shadow-indigo-500/10 animate-bounce">
              <Wrench className="h-10 w-10 text-indigo-400" />
            </div>

            {/* Badge */}
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-semibold uppercase tracking-wider">
              <Sparkles className="h-3.5 w-3.5 text-indigo-400" />
              Under Construction
            </div>

            {/* Title & Message */}
            <div className="space-y-2">
              <h1 className="text-3xl font-extrabold tracking-tight text-white">
                We are working on it!
              </h1>
              <p className="text-slate-400 text-sm max-w-md mx-auto leading-relaxed">
                The <span className="text-indigo-400 font-semibold">{feature}</span> page is currently under active development as part of our new Inertia.js + React stack.
              </p>
            </div>

            {/* Timeline Note */}
            <div className="p-3 rounded-xl bg-slate-950/80 border border-slate-800 text-xs text-slate-400 flex items-center justify-center gap-2">
              <Clock className="h-4 w-4 text-amber-400" />
              <span>Expected release in upcoming version update</span>
            </div>

            {/* CTA Buttons */}
            <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4">
              <Button asChild variant="gradient" size="sm" className="gap-2 w-full sm:w-auto">
                <Link href="/new/dashboard">
                  <LayoutDashboard className="h-4 w-4" /> Go to Dashboard
                </Link>
              </Button>
              <Button asChild variant="outline" size="sm" className="gap-2 w-full sm:w-auto">
                <Link href="/new/tasks">
                  <CheckSquare className="h-4 w-4" /> View My Tasks
                </Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
