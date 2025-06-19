import { http } from 'msw'
import { mockCourses } from './data/courses'
import { mockUser } from './data/user'
import { mockNotifications } from './data/notifications'
import { mockFinancialStatement } from './data/financial'
import { mockPurchases } from './data/purchases'
import { mockCards } from './data/cards'

export const handlers = [
  http.get('/api/courses', () => {
    return new Response(JSON.stringify(mockCourses), { status: 200 })
  }),
  http.get('/api/user', () => {
    return new Response(JSON.stringify(mockUser), { status: 200 })
  }),
  http.get('/api/notifications', () => {
    return new Response(JSON.stringify(mockNotifications), { status: 200 })
  }),
  http.get('/api/financial', () => {
    return new Response(JSON.stringify(mockFinancialStatement), { status: 200 })
  }),
  http.get('/api/purchases', () => {
    return new Response(JSON.stringify(mockPurchases), { status: 200 })
  }),
  http.get('/api/cards', () => {
    return new Response(JSON.stringify(mockCards), { status: 200 })
  }),
]
