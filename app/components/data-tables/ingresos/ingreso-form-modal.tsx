"use client"

import { useEffect, useState } from "react"
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
import { Badge } from "@/components/ui/badge"

interface ContratoOption {
  id: number
  trasteroNumero: string
  clienteNombre?: string
  estado?: string
}

export interface IngresoData {
  id: number
  contratoId: number
  concepto: string
  importe: number
  fechaPago: string
  categoria: string
  metodoPago?: string | null
}

interface IngresoFormModalProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  contratos: ContratoOption[]
  ingreso?: IngresoData | null
  onSuccess?: () => void
}

const categorias = [
  { value: "mensualidad", label: "Mensualidad" },
  { value: "fianza", label: "Fianza" },
  { value: "penalizacion", label: "Penalización" },
  { value: "otros", label: "Otros" },
]

const metodosPago = [
  { value: "efectivo", label: "Efectivo" },
  { value: "transferencia", label: "Transferencia" },
  { value: "tarjeta", label: "Tarjeta" },
  { value: "bizum", label: "Bizum" },
]

export function IngresoFormModal({
  open,
  onOpenChange,
  contratos,
  ingreso,
  onSuccess,
}: IngresoFormModalProps) {
  const isEditing = !!ingreso
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [contratoId, setContratoId] = useState("")
  const [concepto, setConcepto] = useState("")
  const [importe, setImporte] = useState("")
  const [fechaPago, setFechaPago] = useState<Date | undefined>()
  const [categoria, setCategoria] = useState("mensualidad")
  const [metodoPago, setMetodoPago] = useState("")

  useEffect(() => {
    if (open && ingreso) {
      setContratoId(String(ingreso.contratoId))
      setConcepto(ingreso.concepto)
      setImporte(String(ingreso.importe))
      setFechaPago(new Date(ingreso.fechaPago))
      setCategoria(ingreso.categoria)
      setMetodoPago(ingreso.metodoPago ?? "")
    }
  }, [open, ingreso])

  const resetForm = () => {
    setError(null)
    setFieldErrors({})
    setContratoId("")
    setConcepto("")
    setImporte("")
    setFechaPago(undefined)
    setCategoria("mensualidad")
    setMetodoPago("")
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
      contratoId: Number(contratoId),
      concepto,
      importe: Number(importe),
      fechaPago: fechaPago ? format(fechaPago, "yyyy-MM-dd") : "",
      categoria,
      metodoPago: metodoPago || null,
    }

    try {
      const res = await fetchClient(
        isEditing ? `/api/ingresos/${ingreso.id}` : "/api/ingresos",
        {
          method: isEditing ? "PUT" : "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(body),
        }
      )

      if (!res.ok) {
        const data = await res.json()
        const details = data.error?.details as Record<string, string[]> | undefined
        if (details) {
          setFieldErrors(details)
        } else {
          setError(data.error?.message ?? `Error al ${isEditing ? "actualizar" : "crear"} ingreso`)
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
          <DialogTitle>{isEditing ? "Editar ingreso" : "Nuevo ingreso"}</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="grid gap-4">
          {error && (
            <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-600 dark:border-red-800 dark:bg-red-950 dark:text-red-400">
              {error}
            </div>
          )}

          <div className="grid gap-2">
            <Label htmlFor="contratoId">Contrato *</Label>
            <Select value={contratoId} onValueChange={setContratoId} required>
              <SelectTrigger id="contratoId" className="w-full">
                <SelectValue placeholder="Seleccionar contrato" />
              </SelectTrigger>
              <SelectContent>
                {contratos.length === 0 ? (
                  <SelectItem value="_empty" disabled>
                    No hay contratos
                  </SelectItem>
                ) : (
                  contratos.map((c) => (
                    <SelectItem key={c.id} value={String(c.id)}>
                      <span className="flex items-center gap-2">
                        #{c.id} — Trastero {c.trasteroNumero}
                        {c.clienteNombre && (
                          <span className="text-muted-foreground">({c.clienteNombre})</span>
                        )}
                        {c.estado && (
                          <Badge variant={c.estado === "activo" ? "default" : "secondary"} className="text-[10px] px-1.5 py-0">
                            {c.estado}
                          </Badge>
                        )}
                      </span>
                    </SelectItem>
                  ))
                )}
              </SelectContent>
            </Select>
            {fieldErrors.contratoId?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          <div className="grid gap-2">
            <Label>Fecha de pago *</Label>
            <DatePicker
              value={fechaPago}
              onChange={setFechaPago}
              placeholder="Seleccionar fecha"
            />
            {fieldErrors.fechaPago?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          <div className="grid gap-2">
            <Label htmlFor="concepto">Concepto *</Label>
            <Input
              id="concepto"
              value={concepto}
              onChange={(e) => setConcepto(e.target.value)}
              required
              placeholder="Ej: Mensualidad enero 2026"
            />
            {fieldErrors.concepto?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="importe">Importe *</Label>
              <Input
                id="importe"
                type="number"
                step="0.01"
                min="0.01"
                required
                value={importe}
                onChange={(e) => setImporte(e.target.value)}
                placeholder="0.00"
              />
              {fieldErrors.importe?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
            <div className="grid gap-2">
              <Label htmlFor="categoria">Categoría *</Label>
              <Select value={categoria} onValueChange={setCategoria} required>
                <SelectTrigger id="categoria" className="w-full">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {categorias.map((c) => (
                    <SelectItem key={c.value} value={c.value}>
                      {c.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              {fieldErrors.categoria?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
          </div>

          <div className="grid gap-2">
            <Label htmlFor="metodoPago">Método de pago</Label>
            <Select value={metodoPago} onValueChange={setMetodoPago}>
              <SelectTrigger id="metodoPago" className="w-full">
                <SelectValue placeholder="Seleccionar método" />
              </SelectTrigger>
              <SelectContent>
                {metodosPago.map((m) => (
                  <SelectItem key={m.value} value={m.value}>
                    {m.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            {fieldErrors.metodoPago?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
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
              disabled={saving || !contratoId || !fechaPago}
            >
              {saving ? "Guardando..." : isEditing ? "Guardar cambios" : "Crear ingreso"}
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
