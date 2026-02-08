"use client"

import { useEffect, useState } from "react"
import { fetchClient } from "@/lib/api/fetch-client"
import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

export interface ClienteData {
  id: number
  nombre?: string
  apellidos?: string
  dniNie?: string | null
  email?: string | null
  telefono?: string | null
  activo?: boolean
}

interface ClienteFormModalProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  cliente?: ClienteData | null
  onSuccess?: () => void
}

export function ClienteFormModal({
  open,
  onOpenChange,
  cliente,
  onSuccess,
}: ClienteFormModalProps) {
  const isEditing = !!cliente

  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [nombre, setNombre] = useState("")
  const [apellidos, setApellidos] = useState("")
  const [dniNie, setDniNie] = useState("")
  const [email, setEmail] = useState("")
  const [telefono, setTelefono] = useState("")
  const [activo, setActivo] = useState(true)

  useEffect(() => {
    if (!open) {
      setError(null)
      setFieldErrors({})
      setNombre("")
      setApellidos("")
      setDniNie("")
      setEmail("")
      setTelefono("")
      setActivo(true)
      return
    }
    if (isEditing && cliente) {
      setNombre(cliente.nombre ?? "")
      setApellidos(cliente.apellidos ?? "")
      setDniNie(cliente.dniNie ?? "")
      setEmail(cliente.email ?? "")
      setTelefono(cliente.telefono ?? "")
      setActivo(cliente.activo ?? true)
    }
  }, [open])

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setSaving(true)
    setError(null)
    setFieldErrors({})

    const body = {
      nombre,
      apellidos,
      dniNie: dniNie || null,
      email: email || null,
      telefono: telefono || null,
      activo,
    }

    const url = isEditing ? `/api/clientes/${cliente!.id}` : "/api/clientes"
    const method = isEditing ? "PUT" : "POST"

    try {
      const res = await fetchClient(url, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      })

      if (!res.ok) {
        const data = await res.json()
        const details = data.error?.details as Record<string, string[]> | undefined
        if (details) {
          setFieldErrors(details)
        } else {
          setError(data.error?.message ?? (isEditing ? "Error al actualizar cliente" : "Error al crear cliente"))
        }
        return
      }

      onOpenChange(false)
      onSuccess?.()
    } catch {
      setError("Error de conexión")
    } finally {
      setSaving(false)
    }
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{isEditing ? "Editar cliente" : "Nuevo cliente"}</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="grid gap-4">
          {error && (
            <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-600 dark:border-red-800 dark:bg-red-950 dark:text-red-400">
              {error}
            </div>
          )}

          <div className="grid gap-2">
            <Label htmlFor="nombre">Nombre *</Label>
            <Input
              id="nombre"
              value={nombre}
              onChange={(e) => setNombre(e.target.value)}
              required
              maxLength={100}
              placeholder="Nombre"
            />
            {fieldErrors.nombre?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          <div className="grid gap-2">
            <Label htmlFor="apellidos">Apellidos *</Label>
            <Input
              id="apellidos"
              value={apellidos}
              onChange={(e) => setApellidos(e.target.value)}
              required
              maxLength={200}
              placeholder="Apellidos"
            />
            {fieldErrors.apellidos?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          <div className="grid gap-2">
            <Label htmlFor="dniNie">DNI/NIE</Label>
            <Input
              id="dniNie"
              value={dniNie}
              onChange={(e) => setDniNie(e.target.value)}
              maxLength={20}
              placeholder="12345678A"
            />
            {fieldErrors.dniNie?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          <div className="grid gap-2">
            <Label htmlFor="email">Email</Label>
            <Input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              maxLength={255}
              placeholder="correo@ejemplo.com"
            />
            {fieldErrors.email?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          <div className="grid gap-2">
            <Label htmlFor="telefono">Teléfono</Label>
            <Input
              id="telefono"
              value={telefono}
              onChange={(e) => setTelefono(e.target.value)}
              maxLength={20}
              placeholder="600 000 000"
            />
            {fieldErrors.telefono?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          {isEditing && (
            <div className="flex items-center gap-2">
              <Checkbox
                id="activo"
                checked={activo}
                onCheckedChange={(checked) => setActivo(checked === true)}
              />
              <Label htmlFor="activo" className="cursor-pointer">
                Cliente activo
              </Label>
            </div>
          )}

          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => onOpenChange(false)}
            >
              Cancelar
            </Button>
            <Button type="submit" disabled={saving}>
              {saving ? "Guardando..." : isEditing ? "Guardar cambios" : "Crear cliente"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  )
}
