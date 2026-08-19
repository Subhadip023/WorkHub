import * as React from "react";
import { cva } from "class-variance-authority";
import { cn } from "@/lib/utils";

const badgeVariants = cva(
  "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2",
  {
    variants: {
      variant: {
        default:
          "border-transparent bg-indigo-500/15 text-indigo-300 border-indigo-500/30",
        secondary:
          "border-transparent bg-slate-800 text-slate-300 border-slate-700",
        destructive:
          "border-transparent bg-rose-500/15 text-rose-300 border-rose-500/30",
        outline: "text-slate-300 border-slate-700",
        success:
          "border-transparent bg-emerald-500/15 text-emerald-300 border-emerald-500/30",
        warning:
          "border-transparent bg-amber-500/15 text-amber-300 border-amber-500/30",
        info:
          "border-transparent bg-cyan-500/15 text-cyan-300 border-cyan-500/30",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
);

function Badge({ className, variant, ...props }) {
  return (
    <div className={cn(badgeVariants({ variant }), className)} {...props} />
  );
}

export { Badge, badgeVariants };
