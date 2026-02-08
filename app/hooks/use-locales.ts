import { useEffect, useState } from "react"
import type { components } from "@/lib/api/types"
import { fetchClient } from "@/lib/api/fetch-client"

type Local = components["schemas"]["Local"]

interface UseLocalesReturn {
  locales: Local[]
  total: number
  loading: boolean
  error: string | null
  refetch: () => void
}

export function useLocales(): UseLocalesReturn {
  const [locales, setLocales] = useState<Local[]>([])
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const fetchLocales = async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await fetchClient("/api/locales")
      if (!res.ok) throw new Error("Error al cargar locales")
      const data = await res.json()
      setLocales(data.data ?? [])
      setTotal(data.meta?.total ?? 0)
    } catch (err) {
      setError(err instanceof Error ? err.message : "Error desconocido")
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchLocales()
  }, [])

  return { locales, total, loading, error, refetch: fetchLocales }
}
