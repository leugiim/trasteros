import { useEffect, useState } from "react"
import type { components } from "@/lib/api/types"

type Trastero = components["schemas"]["Trastero"]

interface UseTrasterosReturn {
  trasteros: Trastero[]
  total: number
  loading: boolean
  error: string | null
  refetch: () => void
}

export function useTrasteros(): UseTrasterosReturn {
  const [trasteros, setTrasteros] = useState<Trastero[]>([])
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const fetchTrasteros = async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await fetch("/api/trasteros")
      if (!res.ok) throw new Error("Error al cargar trasteros")
      const data = await res.json()
      setTrasteros(data.data ?? [])
      setTotal(data.meta?.total ?? 0)
    } catch (err) {
      setError(err instanceof Error ? err.message : "Error desconocido")
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchTrasteros()
  }, [])

  return { trasteros, total, loading, error, refetch: fetchTrasteros }
}
