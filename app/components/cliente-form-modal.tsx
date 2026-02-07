"use client"

import { useState } from "react"
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

interface ClienteFormModalProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  onSuccess?: () => void
}

export function ClienteFormModal({
  open,
  onOpenChange,
  onSuccess,
}: ClienteFormModalProps) {
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setSaving(true)
    setError(null)
    setFieldErrors({})

    const formData = new FormData(e.currentTarget)
    const body = {
      nombre: formData.get("nombre") as string,
      apellidos: formData.get("apellidos") as string,
      dniNie: (formData.get("dniNie") as string) || null,
      email: (formData.get("email") as string) || null,
      telefono: (formData.get("telefono") as string) || null,
      activo: true,
    }

    try {
      const res = await fetch("/api/clientes", {
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
          setError(data.error?.message ?? "Error al crear cliente")
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
          <DialogTitle>Nuevo cliente</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="grid gap-4">
          {error && (
            <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-600 dark:border-red-800 dark:bg-red-950 dark:text-red-400">
              {error}
            </div>
          )}

          <FormField
            label="Nombre *"
            name="nombre"
            required
            maxLength={100}
            placeholder="Nombre"
            errors={fieldErrors.nombre}
          />

          <FormField
            label="Apellidos *"
            name="apellidos"
            required
            maxLength={200}
            placeholder="Apellidos"
            errors={fieldErrors.apellidos}
          />

          <FormField
            label="DNI/NIE"
            name="dniNie"
            maxLength={20}
            placeholder="12345678A"
            errors={fieldErrors.dniNie}
          />

          <FormField
            label="Email"
            name="email"
            type="email"
            maxLength={255}
            placeholder="correo@ejemplo.com"
            errors={fieldErrors.email}
          />

          <FormField
            label="Teléfono"
            name="telefono"
            maxLength={20}
            placeholder="600 000 000"
            errors={fieldErrors.telefono}
          />

          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => onOpenChange(false)}
            >
              Cancelar
            </Button>
            <Button type="submit" disabled={saving}>
              {saving ? "Guardando..." : "Crear cliente"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  )
}

function FormField({
  label,
  name,
  errors,
  ...inputProps
}: {
  label: string
  name: string
  errors?: string[]
} & React.ComponentProps<typeof Input>) {
  return (
    <div className="grid gap-2">
      <Label htmlFor={name}>{label}</Label>
      <Input
        id={name}
        name={name}
        aria-invalid={errors ? true : undefined}
        {...inputProps}
      />
      {errors?.map((msg) => (
        <p key={msg} className="text-destructive text-sm">
          {msg}
        </p>
      ))}
    </div>
  )
}
