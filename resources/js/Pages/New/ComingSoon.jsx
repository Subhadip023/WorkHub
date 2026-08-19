import React from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Link } from "@inertiajs/react";
import { LayoutDashboard, CheckSquare, Clock, Terminal, Crown } from "lucide-react";
import { Button } from "@/Components/ui/button";

export default function ComingSoon({ feature = "This Feature", activeItem = "coming-soon" }) {
  return (
    <DashboardLayout title={feature} activeItem={activeItem}>
      <div className="min-h-[65vh] flex items-center justify-center py-12">
        <div className="max-w-lg w-full border border-neutral-800 bg-neutral-900/90 shadow-2xl rounded-2xl text-center p-8 sm:p-10 space-y-6">
          <div className="mx-auto h-16 w-16 rounded-2xl bg-black border border-neutral-800 flex items-center justify-center text-white shadow-xl">
            <Crown className="h-8 w-8 text-neutral-300" />
          </div>

          <div className="space-y-2.5">
            <span className="font-mono text-xs uppercase tracking-widest text-neutral-400 block font-semibold">
              WorkHub Classic Module
            </span>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
              {feature}
            </h1>
            <p className="text-neutral-300 text-sm sm:text-base font-sans max-w-sm mx-auto leading-relaxed">
              We are working on this page! The <span className="text-white font-mono font-semibold">{feature}</span> module is under active development.
            </p>
          </div>

          <div className="p-3.5 rounded-xl bg-black border border-neutral-800 text-xs sm:text-sm font-mono text-neutral-300 flex items-center justify-center gap-2">
            <Clock className="h-4 w-4 text-amber-400" />
            <span>Scheduled for upcoming release cycle</span>
          </div>

          <div className="flex items-center justify-center gap-3 pt-2">
            <Button asChild size="sm" className="bg-white text-black hover:bg-neutral-200 font-semibold text-sm gap-2 rounded-xl h-10 px-4">
              <Link href="/new/dashboard">
                <LayoutDashboard className="h-4 w-4" /> Dashboard
              </Link>
            </Button>
            <Button asChild variant="outline" size="sm" className="border-neutral-800 bg-black hover:bg-neutral-900 text-neutral-200 text-sm gap-2 rounded-xl h-10 px-4">
              <Link href="/new/tasks">
                <CheckSquare className="h-4 w-4" /> Open Tasks
              </Link>
            </Button>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}
