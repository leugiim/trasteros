import { useEffect, useState } from "react"
import type { components } from "@/lib/api/types"
import { fetchClient } from "@/lib/api/fetch-client"

type Cliente = components["schemas"]["Cliente"]

interface UseClientesReturn {
  clientes: Cliente[]
  total: number
  loading: boolean
  error: string | null
  refetch: () => void
}

export function useClientes(): UseClientesReturn {
  const [clientes, setClientes] = useState<Cliente[]>([])
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const fetchClientes = async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await fetchClient("/api/clientes")
      if (!res.ok) throw new Error("Error al cargar clientes")
      const data = await res.json()
      setClientes(data.data ?? [])
      setTotal(data.meta?.total ?? 0)
    } catch (err) {
      setError(err instanceof Error ? err.message : "Error desconocido")
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchClientes()
  }, [])

  return { clientes, total, loading, error, refetch: fetchClientes }
}
