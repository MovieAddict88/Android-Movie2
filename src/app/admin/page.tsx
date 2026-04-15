"use client";

import React from "react";
import { Card } from "@/components/ui/Card";
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  AreaChart,
  Area
} from "recharts";
import { TrendingUp, ShoppingCart, Users, DollarSign } from "lucide-react";
import { StatCardProps } from "@/types";

const data = [
  { name: "Mon", sales: 4000, orders: 240 },
  { name: "Tue", sales: 3000, orders: 198 },
  { name: "Wed", sales: 2000, orders: 150 },
  { name: "Thu", sales: 2780, orders: 190 },
  { name: "Fri", sales: 1890, orders: 120 },
  { name: "Sat", sales: 2390, orders: 170 },
  { name: "Sun", sales: 3490, orders: 210 },
];

const StatsCard = ({ title, value, icon: Icon, trend }: StatCardProps) => (
  <Card className="flex flex-col gap-4">
    <div className="flex justify-between items-start">
      <div className="p-3 bg-gray-50 dark:bg-gray-800 rounded-2xl">
        <Icon className="w-6 h-6 text-primary" />
      </div>
      <span className={`text-sm font-bold ${trend > 0 ? "text-green-500" : "text-red-500"}`}>
        {trend > 0 ? "+" : ""}{trend}%
      </span>
    </div>
    <div>
      <p className="text-gray-500 text-sm font-medium">{title}</p>
      <h3 className="text-3xl font-black mt-1">{value}</h3>
    </div>
  </Card>
);

export default function AdminDashboard() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-black mb-2">Dashboard Overview</h1>
        <p className="text-gray-500">Welcome back! Here's what's happening with your store today.</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatsCard title="Total Revenue" value="$24,560" icon={DollarSign} trend={12} />
        <StatsCard title="Total Orders" value="1,240" icon={ShoppingCart} trend={8} />
        <StatsCard title="Active Users" value="842" icon={Users} trend={-3} />
        <StatsCard title="Conversion Rate" value="4.2%" icon={TrendingUp} trend={5} />
      </div>

      {/* Charts Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <Card className="p-8">
          <div className="flex justify-between items-center mb-8">
            <h3 className="text-xl font-bold">Revenue Analytics</h3>
            <select className="bg-gray-50 dark:bg-gray-800 border-none rounded-lg text-sm px-4 py-2">
              <option>Last 7 Days</option>
              <option>Last 30 Days</option>
            </select>
          </div>
          <div className="h-80 w-full">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={data}>
                <defs>
                  <linearGradient id="colorSales" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#ff6b6b" stopOpacity={0.3}/>
                    <stop offset="95%" stopColor="#ff6b6b" stopOpacity={0}/>
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#eee" />
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{fill: '#888', fontSize: 12}} />
                <YAxis axisLine={false} tickLine={false} tick={{fill: '#888', fontSize: 12}} />
                <Tooltip
                  contentStyle={{
                    borderRadius: '16px',
                    border: 'none',
                    boxShadow: '0 10px 15px -3px rgba(0,0,0,0.1)',
                    backgroundColor: '#fff'
                  }}
                />
                <Area type="monotone" dataKey="sales" stroke="#ff6b6b" strokeWidth={3} fillOpacity={1} fill="url(#colorSales)" />
              </AreaChart>
            </ResponsiveContainer>
          </div>
        </Card>

        <Card className="p-8">
          <div className="flex justify-between items-center mb-8">
            <h3 className="text-xl font-bold">Orders Monitoring</h3>
            <div className="flex gap-2">
               <div className="flex items-center gap-2 text-xs text-gray-500">
                  <div className="w-3 h-3 bg-secondary rounded-full" />
                  Orders
               </div>
            </div>
          </div>
          <div className="h-80 w-full">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={data}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#eee" />
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{fill: '#888', fontSize: 12}} />
                <YAxis axisLine={false} tickLine={false} tick={{fill: '#888', fontSize: 12}} />
                <Tooltip
                  cursor={{fill: '#f3f4f6'}}
                  contentStyle={{
                    borderRadius: '16px',
                    border: 'none',
                    boxShadow: '0 10px 15px -3px rgba(0,0,0,0.1)'
                  }}
                />
                <Bar dataKey="orders" fill="#4ecdc4" radius={[6, 6, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </Card>
      </div>
    </div>
  );
}
