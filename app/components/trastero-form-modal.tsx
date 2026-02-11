"use client"

import { useEffect, useState } from "react"
import { fetchClient } from "@/lib/api/fetch-client"
import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

interface LocalOption {
  id: number
  nombre: string
}

interface TrasteroFormModalProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  onSuccess?: () => void
  defaultLocalId?: number
  defaultLocalNombre?: string
}

export function TrasteroFormModal({
  open,
  onOpenChange,
  onSuccess,
  defaultLocalId,
  defaultLocalNombre,
}: TrasteroFormModalProps) {
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [locales, setLocales] = useState<LocalOption[]>([])
  const [loadingLocales, setLoadingLocales] = useState(false)

  const [localId, setLocalId] = useState(defaultLocalId ? String(defaultLocalId) : "")
  const [numero, setNumero] = useState("")
  const [nombre, setNombre] = useState("")
  const [superficie, setSuperficie] = useState("")
  const [precioMensual, setPrecioMensual] = useState("")

  useEffect(() => {
    if (!open || defaultLocalId) return
    setLoadingLocales(true)
    fetchClient("/api/locales")
      .then((res) => (res.ok ? res.json() : { data: [] }))
      .then((data) => setLocales((data.data ?? []).map((l: { id: number; nombre: string }) => ({ id: l.id, nombre: l.nombre }))))
      .finally(() => setLoadingLocales(false))
  }, [open, defaultLocalId])

  const resetForm = () => {
    setError(null)
    setFieldErrors({})
    setLocalId(defaultLocalId ? String(defaultLocalId) : "")
    setNumero("")
    setNombre("")
    setSuperficie("")
    setPrecioMensual("")
  }

  const handleOpenChange = (value: boolean) => {
    if (!value) resetForm()
    onOpenChange(value)
  }

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setSaving(true)
    setError(null)
    setFieldErrors({})

    const body = {
      localId: Number(localId),
      numero,
      nombre: nombre || null,
      superficie: Number(superficie),
      precioMensual: Number(precioMensual),
    }

    try {
      const res = await fetchClient("/api/trasteros", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      })

      if (!res.ok) {
        const data = await res.json()
        const details = data.error?.details as Record<string, string[]> | undefined
        if (details) {
          setFieldErrors(details)
        } else {
          setError(data.error?.message ?? "Error al crear trastero")
        }
        return
      }

      handleOpenChange(false)
      onSuccess?.()
    } catch {
      setError("Error de conexión")
    } finally {
      setSaving(false)
    }
  }

  return (
    <Dialog open={open} onOpenChange={handleOpenChange}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Nuevo trastero</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="grid gap-4">
          {error && (
            <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-600 dark:border-red-800 dark:bg-red-950 dark:text-red-400">
              {error}
            </div>
          )}

          <div className="grid gap-2">
            <Label htmlFor="localId">Local *</Label>
            {defaultLocalId ? (
              <Input value={defaultLocalNombre ?? `Local #${defaultLocalId}`} disabled />
            ) : (
              <Select value={localId} onValueChange={setLocalId} required disabled={loadingLocales}>
                <SelectTrigger id="localId" className="w-full">
                  {loadingLocales ? (
                    <span className="text-muted-foreground">Cargando locales...</span>
                  ) : (
                    <SelectValue placeholder="Seleccionar local" />
                  )}
                </SelectTrigger>
                <SelectContent>
                  {locales.length === 0 ? (
                    <SelectItem value="_empty" disabled>
                      No hay locales
                    </SelectItem>
                  ) : (
                    locales.map((l) => (
                      <SelectItem key={l.id} value={String(l.id)}>
                        {l.nombre}
                      </SelectItem>
                    ))
                  )}
                </SelectContent>
              </Select>
            )}
            {fieldErrors.localId?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="numero">Número *</Label>
              <Input
                id="numero"
                value={numero}
                onChange={(e) => setNumero(e.target.value)}
                required
                placeholder="Ej: T-01"
              />
              {fieldErrors.numero?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
            <div className="grid gap-2">
              <Label htmlFor="nombre">Nombre</Label>
              <Input
                id="nombre"
                value={nombre}
                onChange={(e) => setNombre(e.target.value)}
                placeholder="Opcional"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="superficie">Superficie (m²) *</Label>
              <Input
                id="superficie"
                type="number"
                step="0.01"
                min="0.01"
                required
                value={superficie}
                onChange={(e) => setSuperficie(e.target.value)}
                placeholder="0.00"
              />
              {fieldErrors.superficie?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
            <div className="grid gap-2">
              <Label htmlFor="precioMensual">Precio mensual *</Label>
              <Input
                id="precioMensual"
                type="number"
                step="0.01"
                min="0"
                required
                value={precioMensual}
                onChange={(e) => setPrecioMensual(e.target.value)}
                placeholder="0.00"
              />
              {fieldErrors.precioMensual?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => handleOpenChange(false)}>
              Cancelar
            </Button>
            <Button type="submit" disabled={saving || !localId}>
              {saving ? "Guardando..." : "Crear trastero"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  )
}
