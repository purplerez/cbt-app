'use client'

import { useAuth } from '@/hooks/useAuth'
import { useRouter } from 'next/navigation'
import { useEffect, useState } from 'react'

interface ProtectedRouteProps {
     children: React.ReactNode
}

export default function ProtectedRoute({ children }: ProtectedRouteProps) {
     const { isAuthenticated, isLoading, isUnauthenticated } = useAuth()
     const router = useRouter()
     const [mounted, setMounted] = useState(false)

     useEffect(() => {
          setMounted(true)
     }, [])

     useEffect(() => {
          if (mounted && isUnauthenticated) {
               router.push('/')
          }
     }, [mounted, isUnauthenticated, router])

     if (!mounted) {
          return null
     }

     if (isLoading) {
          return (
               <div className="flex items-center justify-center min-h-screen">
                    <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-500"></div>
               </div>
          )
     }

     if (isUnauthenticated) {
          return null
     }

     if (isAuthenticated) {
          return <>{children}</>
     }

     return null
}
