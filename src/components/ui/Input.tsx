"use client";

import React from "react";
import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
}

const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, label, error, ...props }, ref) => {
    return (
      <div className="w-full space-y-2">
        {label && (
          <label className="text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">
            {label}
          </label>
        )}
        <input
          ref={ref}
          className={cn(
            "w-full px-5 py-3 rounded-2xl bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-primary focus:bg-white dark:focus:bg-vibe-black focus:outline-none transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-400",
            error && "border-red-500 focus:border-red-500",
            className
          )}
          {...props}
        />
        {error && <p className="text-xs text-red-500 ml-1">{error}</p>}
      </div>
    );
  }
);

Input.displayName = "Input";

export { Input };
