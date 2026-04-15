import { LucideIcon } from 'lucide-react'

export interface StatCardProps {
  title: string
  value: string
  icon: LucideIcon
  trend: number
}

export type OrderStatus = 'Delivered' | 'Preparing' | 'Pending' | 'Cancelled'

export interface Order {
  id: string
  customer: string
  items: string
  total: number
  status: OrderStatus
  time: string
}
