"use client"

import { useState } from "react"
import { fetchClient } from "@/lib/api/fetch-client"
import { format } from "date-fns"
import { es } from "date-fns/locale"
import { CalendarIcon, XIcon } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
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
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

interface PrestamoFormModalProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  localId: number
  onSuccess?: () => void
}

const estados = [
  { value: "activo", label: "Activo" },
  { value: "cancelado", label: "Cancelado" },
  { value: "finalizado", label: "Finalizado" },
]

export function PrestamoFormModal({
  open,
  onOpenChange,
  localId,
  onSuccess,
}: PrestamoFormModalProps) {
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [entidadBancaria, setEntidadBancaria] = useState("")
  const [numeroPrestamo, setNumeroPrestamo] = useState("")
  const [capitalSolicitado, setCapitalSolicitado] = useState("")
  const [totalADevolver, setTotalADevolver] = useState("")
  const [tipoInteres, setTipoInteres] = useState("")
  const [fechaConcesion, setFechaConcesion] = useState<Date | undefined>()
  const [estado, setEstado] = useState("activo")

  const resetForm = () => {
    setError(null)
    setFieldErrors({})
    setEntidadBancaria("")
    setNumeroPrestamo("")
    setCapitalSolicitado("")
    setTotalADevolver("")
    setTipoInteres("")
    setFechaConcesion(undefined)
    setEstado("activo")
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
      localId,
      entidadBancaria,
      numeroPrestamo: numeroPrestamo || null,
      capitalSolicitado: Number(capitalSolicitado),
      totalADevolver: Number(totalADevolver),
      tipoInteres: tipoInteres ? Number(tipoInteres) : null,
      fechaConcesion: fechaConcesion ? format(fechaConcesion, "yyyy-MM-dd") : "",
      estado,
    }

    try {
      const res = await fetchClient("/api/prestamos", {
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
          setError(data.error?.message ?? "Error al crear préstamo")
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
          <DialogTitle>Nuevo préstamo</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="grid gap-4">
          {error && (
            <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-600 dark:border-red-800 dark:bg-red-950 dark:text-red-400">
              {error}
            </div>
          )}

          <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="entidadBancaria">Entidad bancaria *</Label>
              <Input
                id="entidadBancaria"
                value={entidadBancaria}
                onChange={(e) => setEntidadBancaria(e.target.value)}
                required
                placeholder="Ej: CaixaBank"
              />
              {fieldErrors.entidadBancaria?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
            <div className="grid gap-2">
              <Label htmlFor="numeroPrestamo">Nº préstamo</Label>
              <Input
                id="numeroPrestamo"
                value={numeroPrestamo}
                onChange={(e) => setNumeroPrestamo(e.target.value)}
                placeholder="Ej: PREST-2024-001"
              />
              {fieldErrors.numeroPrestamo?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="capitalSolicitado">Capital solicitado *</Label>
              <Input
                id="capitalSolicitado"
                type="number"
                step="0.01"
                min="0.01"
                required
                value={capitalSolicitado}
                onChange={(e) => setCapitalSolicitado(e.target.value)}
                placeholder="0.00"
              />
              {fieldErrors.capitalSolicitado?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
            <div className="grid gap-2">
              <Label htmlFor="totalADevolver">Total a devolver *</Label>
              <Input
                id="totalADevolver"
                type="number"
                step="0.01"
                min="0.01"
                required
                value={totalADevolver}
                onChange={(e) => setTotalADevolver(e.target.value)}
                placeholder="0.00"
              />
              {fieldErrors.totalADevolver?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="tipoInteres">Tipo de interés (%)</Label>
              <Input
                id="tipoInteres"
                type="number"
                step="0.01"
                min="0"
                value={tipoInteres}
                onChange={(e) => setTipoInteres(e.target.value)}
                placeholder="3.50"
              />
              {fieldErrors.tipoInteres?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
            <div className="grid gap-2">
              <Label>Fecha concesión *</Label>
              <DatePicker
                value={fechaConcesion}
                onChange={setFechaConcesion}
                placeholder="Seleccionar fecha"
              />
              {fieldErrors.fechaConcesion?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
          </div>

          <div className="grid gap-2">
            <Label htmlFor="estado">Estado</Label>
            <Select value={estado} onValueChange={setEstado}>
              <SelectTrigger id="estado" className="w-full">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                {estados.map((e) => (
                  <SelectItem key={e.value} value={e.value}>
                    {e.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => handleOpenChange(false)}
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              disabled={saving || !entidadBancaria || !fechaConcesion}
            >
              {saving ? "Guardando..." : "Crear préstamo"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  )
}

function DatePicker({
  value,
  onChange,
  placeholder,
}: {
  value?: Date
  onChange: (date: Date | undefined) => void
  placeholder?: string
}) {
  const [open, setOpen] = useState(false)

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <div className="relative">
        <PopoverTrigger asChild>
          <Button
            type="button"
            variant="outline"
            className={`w-full justify-start text-left font-normal ${value ? "pr-8" : ""} ${!value ? "text-muted-foreground" : ""}`}
          >
            <CalendarIcon className="mr-2 size-4" />
            {value ? format(value, "dd/MM/yyyy") : placeholder}
          </Button>
        </PopoverTrigger>
        {value && (
          <button
            type="button"
            className="text-muted-foreground hover:text-foreground absolute right-2 top-1/2 -translate-y-1/2"
            onClick={(e) => {
              e.stopPropagation()
              onChange(undefined)
            }}
          >
            <XIcon className="size-3.5" />
            <span className="sr-only">Limpiar fecha</span>
          </button>
        )}
      </div>
      <PopoverContent className="w-auto p-0" align="start">
        <Calendar
          mode="single"
          selected={value}
          onSelect={(date) => {
            onChange(date)
            setOpen(false)
          }}
          locale={es}
        />
      </PopoverContent>
    </Popover>
  )
}
