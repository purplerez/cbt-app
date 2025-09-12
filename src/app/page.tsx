"use client"

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { authService } from "@/services/auth";
import { loginSchema, LoginFormData } from "@/lib/validations/auth";
import { useMutation } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useState, useEffect } from "react";

export default function LoginPage() {
  const router = useRouter()
  const [loginError, setLoginError] = useState<string | null>(null)
  const [mounted, setMounted] = useState(false)

  useEffect(() => {
    setMounted(true)

    const token = localStorage.getItem('api_token')
    if (token) {
      router.push('/exam')
    }
  }, [router])

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting }
  } = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema)
  })

  const loginMutation = useMutation({
    mutationFn: (data: LoginFormData) => authService.login(data.email, data.password),
    onSuccess: () => {
      setLoginError(null)
      router.push('/exam')
    },
    onError: (error: Error & { response?: { data?: { message?: string } } }) => {
      console.error('Login error:', error)
      const errorMessage = error.response?.data?.message || error.message || 'Login gagal. Silakan coba lagi.'
      setLoginError(errorMessage)
    }
  })

  const onSubmit = async (data: LoginFormData) => {
    try {
      await loginMutation.mutateAsync(data)
    } catch (error) {
      console.error('Submit error:', error)
    }
  }

  if (!mounted) {
    return null
  }

  return (
    <div className="flex flex-col items-center justify-center min-h-screen w-full bg-gray-50 p-4">
      <div className="w-full max-w-md">
        <div className="bg-white shadow-md rounded-lg p-8">
          <h1 className="text-2xl font-bold text-center mb-6 text-[#404040]">Login CBT System</h1>

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4" noValidate>
            <div>
              <Input
                {...register("email")}
                placeholder="Email"
                type="email"
                className={errors.email ? "border-red-500" : ""}
              />
              {errors.email && (
                <p className="text-red-500 text-sm mt-1">{errors.email.message}</p>
              )}
            </div>

            <div>
              <Input
                {...register("password")}
                placeholder="Password"
                type="password"
                className={errors.password ? "border-red-500" : ""}
              />
              {errors.password && (
                <p className="text-red-500 text-sm mt-1">{errors.password.message}</p>
              )}
            </div>

            {loginError && (
              <Alert variant="destructive">
                <AlertDescription>
                  {loginError}
                </AlertDescription>
              </Alert>
            )}

            <Button
              type="submit"
              disabled={isSubmitting || loginMutation.isPending}
              className="w-full font-heading text-white bg-blue-500 hover:bg-blue-600 px-8 text-sm font-medium rounded-md"
            >
              {isSubmitting || loginMutation.isPending ? 'Loading...' : 'Login'}
            </Button>
          </form>
        </div>
      </div>
    </div>
  );
}