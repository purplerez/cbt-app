"use client"

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { useRouter } from "next/navigation";

export default function LoginPage() {
  const router = useRouter()

  const handleLogin = () => {
    router.push("/dashboard");
  }

  return (
    <div className="flex flex-col items-center justify-center h-screen w-full">
      <form action="" className="flex items-center justify-center flex-col gap-5">
        <div className="flex flex-col items-center gap-3 w-full">
          <Input placeholder="Email" />
          <Input placeholder="Password" type="password" />
        </div>
        <div>
          <Button onClick={handleLogin} className="font-heading text-white bg-blue-500 hover:bg-blue-600 px-8 text-sm font-medium rounded-md">Masuk</Button>
        </div>
      </form>
    </div>
  );
}