import { useState } from "react"
import { useRouter } from "next/navigation"
import { apiFetch, ApiError } from "@/lib/api/client"
import type { components } from "@/lib/api/types"

type LoginResponse = components["schemas"]["LoginResponse"]

export function useLogin() {
  const router = useRouter()
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  async function login(email: string, password: string) {
    setError(null)
    setLoading(true)

    try {
      const data = await apiFetch<LoginResponse>("/api/auth/login", {
        method: "POST",
        body: JSON.stringify({ email, password }),
      })

      document.cookie = `token=${data.token}; path=/; SameSite=Lax`

      router.push("/dashboard")
    } catch (err) {
      if (err instanceof ApiError) {
        const messages: Record<string, string> = {
          INVALID_CREDENTIALS: "Correo electrónico o contraseña incorrectos",
          USER_INACTIVE: "Tu cuenta está desactivada",
        }
        setError(messages[err.code] ?? err.message)
      } else {
        setError("No se pudo conectar con el servidor")
      }
    } finally {
      setLoading(false)
    }
  }

  return { login, error, loading }
}
