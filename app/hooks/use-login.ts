import { useState } from "react"
import { useRouter } from "next/navigation"
import { useUser } from "@/lib/stores/user-store"

export function useLogin() {
  const router = useRouter()
  const { setUser } = useUser()
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  async function login(email: string, password: string) {
    setError(null)
    setLoading(true)

    try {
      const res = await fetch("/api/auth/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
      })

      const data = await res.json()

      if (!res.ok) {
        const messages: Record<string, string> = {
          INVALID_CREDENTIALS: "Correo electrónico o contraseña incorrectos",
          USER_INACTIVE: "Tu cuenta está desactivada",
        }
        const code = data.error?.code ?? ""
        setError(messages[code] ?? data.error?.message ?? "Error desconocido")
        return
      }

      setUser(data.user)
      router.push("/dashboard")
    } catch {
      setError("No se pudo conectar con el servidor")
    } finally {
      setLoading(false)
    }
  }

  return { login, error, loading }
}
