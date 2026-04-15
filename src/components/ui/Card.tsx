"use client";

import React from "react";
import { motion } from "framer-motion";
import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

interface CardProps {
  children: React.ReactNode;
  className?: string;
  animate?: boolean;
}

const Card = ({ children, className, animate = true }: CardProps) => {
  const Component = animate ? motion.div : "div";

  return (
    <Component
      {...(animate ? {
        initial: { opacity: 0, y: 20 },
        whileInView: { opacity: 1, y: 0 },
        viewport: { once: true },
        transition: { duration: 0.5 }
      } : {})}
      className={cn(
        "bg-white dark:bg-vibe-black rounded-3xl p-6 shadow-xl hover:shadow-2xl transition-shadow duration-300 border border-gray-100 dark:border-gray-800",
        className
      )}
    >
      {children}
    </Component>
  );
};

export { Card };
