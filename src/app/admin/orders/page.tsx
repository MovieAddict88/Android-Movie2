"use client";

import React, { useState } from "react";
import { Card } from "@/components/ui/Card";
import {
  Filter,
  MoreVertical,
  CheckCircle,
  Clock,
  XCircle
} from "lucide-react";
import { Order, OrderStatus } from "@/types";

const initialOrders: Order[] = [
  { id: "ORD-7281", customer: "John Doe", items: "2x Classic Vibe Burger", total: 25.98, status: "Delivered", time: "10:24 AM" },
  { id: "ORD-7282", customer: "Jane Smith", items: "1x Truffle Pizza", total: 18.50, status: "Preparing", time: "11:05 AM" },
  { id: "ORD-7283", customer: "Mike Ross", items: "3x Salmon Zen Roll", total: 45.00, status: "Pending", time: "11:15 AM" },
  { id: "ORD-7284", customer: "Sarah Connor", items: "1x Quinoa Bowl, 1x Cake", total: 23.24, status: "Delivered", time: "09:45 AM" },
  { id: "ORD-7285", customer: "Harvey Specter", items: "4x Vibe Burger", total: 51.96, status: "Cancelled", time: "10:50 AM" },
];

const StatusBadge = ({ status }: { status: OrderStatus }) => {
  const styles: Record<OrderStatus, string> = {
    Delivered: "bg-green-100 text-green-600",
    Preparing: "bg-blue-100 text-blue-600",
    Pending: "bg-yellow-100 text-yellow-600",
    Cancelled: "bg-red-100 text-red-600",
  };

  const icons: Record<OrderStatus, React.ReactNode> = {
    Delivered: <CheckCircle className="w-3 h-3" />,
    Preparing: <Clock className="w-3 h-3" />,
    Pending: <Clock className="w-3 h-3" />,
    Cancelled: <XCircle className="w-3 h-3" />,
  };

  return (
    <span className={`px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 w-fit ${styles[status]}`}>
      {icons[status]}
      {status}
    </span>
  );
};

export default function OrdersPage() {
  const [orders] = useState<Order[]>(initialOrders);

  return (
    <div className="space-y-8">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-black mb-2">Order Management</h1>
          <p className="text-gray-500">View and manage all customer orders in real-time.</p>
        </div>
        <div className="flex gap-4">
          <button className="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-xl font-medium">
            <Filter className="w-4 h-4" /> Filter
          </button>
          <button className="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-medium shadow-lg shadow-primary/20">
            Export Data
          </button>
        </div>
      </div>

      <Card className="p-0 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50">
                <th className="px-6 py-4 font-bold text-sm">Order ID</th>
                <th className="px-6 py-4 font-bold text-sm">Customer</th>
                <th className="px-6 py-4 font-bold text-sm">Items</th>
                <th className="px-6 py-4 font-bold text-sm">Total</th>
                <th className="px-6 py-4 font-bold text-sm">Status</th>
                <th className="px-6 py-4 font-bold text-sm">Time</th>
                <th className="px-6 py-4 font-bold text-sm text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              {orders.map((order) => (
                <tr key={order.id} className="border-b dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                  <td className="px-6 py-4 font-medium">{order.id}</td>
                  <td className="px-6 py-4">{order.customer}</td>
                  <td className="px-6 py-4 text-sm text-gray-500 truncate max-w-[200px]">{order.items}</td>
                  <td className="px-6 py-4 font-bold">${order.total.toFixed(2)}</td>
                  <td className="px-6 py-4">
                    <StatusBadge status={order.status} />
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-500">{order.time}</td>
                  <td className="px-6 py-4 text-right">
                    <button className="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg">
                      <MoreVertical className="w-4 h-4 text-gray-400" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        <div className="p-6 border-t dark:border-gray-800 flex justify-between items-center text-sm text-gray-500">
          <p>Showing 5 of 1,240 orders</p>
          <div className="flex gap-2">
            <button className="px-4 py-2 border dark:border-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900">Previous</button>
            <button className="px-4 py-2 border dark:border-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900">Next</button>
          </div>
        </div>
      </Card>
    </div>
  );
}
