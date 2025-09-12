import { useQuery } from '@tanstack/react-query'
import { authService } from '@/services/auth'
import { DashboardExam } from '@/types'
import { useEffect, useState } from 'react'

export function useAuth() {
     const [hasToken, setHasToken] = useState(false)
     const [mounted, setMounted] = useState(false)

     useEffect(() => {
          setMounted(true)
          if (typeof window !== 'undefined') {
               setHasToken(!!localStorage.getItem('api_token'))
          }
     }, [])

     const {
          data: dashboardData,
          isLoading,
          error,
          refetch
     } = useQuery<DashboardExam, Error>({
          queryKey: ['auth', 'user'],
          queryFn: authService.getCurrentUser,
          retry: false,
          enabled: mounted && hasToken
     })

     const user = dashboardData?.student
     const isAuthenticated = !!user && !error
     const isUnauthenticated = mounted && !user && !isLoading && !hasToken

     return {
          user,
          dashboardData,
          isLoading: !mounted || isLoading,
          error,
          isAuthenticated,
          isUnauthenticated,
          refetch
     }
}
