"use client";

import React from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import {
  LayoutDashboard,
  ShoppingBag,
  Users,
  Settings,
  LogOut,
  Bell,
  Search
} from "lucide-react";

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const pathname = usePathname();

  const navItems = [
    { name: "Dashboard", icon: LayoutDashboard, href: "/admin" },
    { name: "Orders", icon: ShoppingBag, href: "/admin/orders" },
    { name: "Customers", icon: Users, href: "/admin/customers" },
    { name: "Settings", icon: Settings, href: "/admin/settings" },
  ];

  return (
    <div className="flex min-h-screen bg-gray-50 dark:bg-vibe-black">
      {/* Sidebar */}
      <aside className="w-64 bg-white dark:bg-gray-900 border-r dark:border-gray-800 flex flex-col">
        <div className="p-8">
          <Link href="/" className="text-2xl font-bold text-primary flex items-center gap-2">
            <div className="w-8 h-8 modern-fluid-gradient rounded-lg flex items-center justify-center text-white text-sm">V</div>
            <span>VibeAdmin</span>
          </Link>
        </div>

        <nav className="flex-grow px-4 space-y-2">
          {navItems.map((item) => {
            const isActive = pathname === item.href;
            return (
              <Link
                key={item.name}
                href={item.href}
                className={`flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 ${
                  isActive
                    ? "bg-primary text-white shadow-lg shadow-primary/20"
                    : "text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
                }`}
              >
                <item.icon className="w-5 h-5" />
                <span className="font-medium">{item.name}</span>
              </Link>
            );
          })}
        </nav>

        <div className="p-4 border-t dark:border-gray-800">
          <button className="flex items-center gap-3 px-4 py-3 w-full text-gray-500 hover:text-red-500 transition-colors">
            <LogOut className="w-5 h-5" />
            <span className="font-medium">Logout</span>
          </button>
        </div>
      </aside>

      {/* Main Content */}
      <div className="flex-grow flex flex-col">
        {/* Header */}
        <header className="h-20 bg-white dark:bg-gray-900 border-b dark:border-gray-800 px-8 flex items-center justify-between">
          <div className="relative w-96">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              type="text"
              placeholder="Search anything..."
              className="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-primary transition-all"
            />
          </div>

          <div className="flex items-center gap-4">
            <button className="p-2 bg-gray-50 dark:bg-gray-800 rounded-xl relative">
              <Bell className="w-5 h-5" />
              <span className="absolute top-2 right-2 w-2 h-2 bg-primary rounded-full border-2 border-white dark:border-gray-900" />
            </button>
            <div className="flex items-center gap-3 ml-4 border-l dark:border-gray-800 pl-4">
              <div className="text-right">
                <p className="text-sm font-bold">Alex Vibe</p>
                <p className="text-xs text-gray-500">Super Admin</p>
              </div>
              <div className="w-10 h-10 rounded-xl modern-fluid-gradient" />
            </div>
          </div>
        </header>

        <main className="p-8 overflow-y-auto">
          {children}
        </main>
      </div>
    </div>
  );
}
