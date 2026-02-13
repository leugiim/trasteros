"use client"

import { useEffect, useState } from "react"
import { fetchClient } from "@/lib/api/fetch-client"
import { format } from "date-fns"
import { es } from "date-fns/locale"
import { CalendarIcon, XIcon } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
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

interface Trastero {
  id: number
  numero: string
  nombre?: string | null
  precioMensual: number
  superficie: number
  local?: { id: number; nombre: string }
}

export interface ContratoData {
  id: number
  trastero?: { id: number; numero: string; local?: { id: number; nombre: string } }
  clienteId?: number
  fechaInicio?: string
  fechaFin?: string | null
  precioMensual?: number
  fianza?: number
  fianzaPagada?: boolean
}

interface ContratoFormModalProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  clienteId: number
  contrato?: ContratoData | null
  onSuccess?: () => void
}

export function ContratoFormModal({
  open,
  onOpenChange,
  clienteId,
  contrato,
  onSuccess,
}: ContratoFormModalProps) {
  const isEditing = !!contrato

  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [trasteros, setTrasteros] = useState<Trastero[]>([])
  const [loadingTrasteros, setLoadingTrasteros] = useState(false)
  const [selectedTrasteroId, setSelectedTrasteroId] = useState<string>("")
  const [precioMensual, setPrecioMensual] = useState("")
  const [fianza, setFianza] = useState("")
  const [fianzaPagada, setFianzaPagada] = useState(false)
  const [fechaInicio, setFechaInicio] = useState<Date | undefined>()
  const [fechaFin, setFechaFin] = useState<Date | undefined>()

  const loadTrasteros = (fecha?: Date) => {
    setLoadingTrasteros(true)
    if (!isEditing) {
      setSelectedTrasteroId("")
      setPrecioMensual("")
    }
    const fechaParam = fecha ? `?fecha=${format(fecha, "yyyy-MM-dd")}` : ""
    fetchClient(`/api/trasteros/disponibles${fechaParam}`)
      .then((res) => (res.ok ? res.json() : { data: [] }))
      .then((data) => {
        let list: Trastero[] = data.data ?? []
        // In edit mode, ensure the current trastero is always in the list
        if (isEditing && contrato?.trastero) {
          const currentId = contrato.trastero.id
          if (!list.some((t) => t.id === currentId)) {
            list = [
              {
                id: currentId,
                numero: contrato.trastero.numero,
                precioMensual: contrato.precioMensual ?? 0,
                superficie: 0,
                local: contrato.trastero.local,
              },
              ...list,
            ]
          }
        }
        setTrasteros(list)
      })
      .finally(() => setLoadingTrasteros(false))
  }

  // Populate form when opening in edit mode
  useEffect(() => {
    if (!open) return
    if (isEditing && contrato) {
      setSelectedTrasteroId(String(contrato.trastero?.id ?? ""))
      setPrecioMensual(String(contrato.precioMensual ?? ""))
      setFianza(String(contrato.fianza ?? ""))
      setFianzaPagada(contrato.fianzaPagada ?? false)
      setFechaInicio(contrato.fechaInicio ? new Date(contrato.fechaInicio) : undefined)
      setFechaFin(contrato.fechaFin ? new Date(contrato.fechaFin) : undefined)
      loadTrasteros(contrato.fechaInicio ? new Date(contrato.fechaInicio) : undefined)
    } else {
      loadTrasteros(fechaInicio)
    }
  }, [open])

  // Reset form when closing
  useEffect(() => {
    if (!open) {
      setError(null)
      setFieldErrors({})
      setSelectedTrasteroId("")
      setPrecioMensual("")
      setFianza("")
      setFianzaPagada(false)
      setFechaInicio(undefined)
      setFechaFin(undefined)
    }
  }, [open])

  const handleTrasteroChange = (value: string) => {
    setSelectedTrasteroId(value)
    const trastero = trasteros.find((t) => String(t.id) === value)
    if (trastero) {
      setPrecioMensual(String(trastero.precioMensual))
    }
  }

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setSaving(true)
    setError(null)
    setFieldErrors({})

    const body = {
      trasteroId: Number(selectedTrasteroId),
      clienteId,
      fechaInicio: fechaInicio ? format(fechaInicio, "yyyy-MM-dd") : "",
      fechaFin: fechaFin ? format(fechaFin, "yyyy-MM-dd") : null,
      precioMensual: Number(precioMensual),
      fianza: Number(fianza) || null,
      fianzaPagada,
    }

    const url = isEditing ? `/api/contratos/${contrato!.id}` : "/api/contratos"
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
          setError(data.error?.message ?? (isEditing ? "Error al actualizar contrato" : "Error al crear contrato"))
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

  const selectedTrastero = trasteros.find(
    (t) => String(t.id) === selectedTrasteroId
  )

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{isEditing ? "Editar contrato" : "Nuevo contrato"}</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="grid gap-4">
          {error && (
            <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-600 dark:border-red-800 dark:bg-red-950 dark:text-red-400">
              {error}
            </div>
          )}

          <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
              <Label>Fecha inicio *</Label>
              <DatePicker
                value={fechaInicio}
                onChange={(date) => {
                  setFechaInicio(date)
                  loadTrasteros(date)
                }}
                placeholder="Seleccionar fecha"
              />
              {fieldErrors.fechaInicio?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
            <div className="grid gap-2">
              <Label>Fecha fin</Label>
              <DatePicker
                value={fechaFin}
                onChange={setFechaFin}
                placeholder="Seleccionar fecha"
                fromDate={fechaInicio}
              />
              {fieldErrors.fechaFin?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
          </div>

          <div className="grid gap-2">
            <Label htmlFor="trasteroId">Trastero *</Label>
            <Select
              value={selectedTrasteroId}
              onValueChange={handleTrasteroChange}
              required
              disabled={loadingTrasteros}
            >
              <SelectTrigger id="trasteroId" className="w-full">
                {loadingTrasteros ? (
                  <span className="text-muted-foreground">Cargando trasteros...</span>
                ) : (
                  <SelectValue placeholder="Seleccionar trastero" />
                )}
              </SelectTrigger>
              <SelectContent>
                {trasteros.length === 0 ? (
                  <SelectItem value="_empty" disabled>
                    No hay trasteros disponibles
                  </SelectItem>
                ) : (
                  trasteros.map((t) => (
                    <SelectItem key={t.id} value={String(t.id)}>
                      {t.numero}
                      {t.local ? ` — ${t.local.nombre}` : ""}
                      {t.superficie > 0 && ` · ${t.superficie} m²`}
                    </SelectItem>
                  ))
                )}
              </SelectContent>
            </Select>
            {selectedTrastero && selectedTrastero.precioMensual > 0 && (
              <p className="text-muted-foreground text-xs">
                Precio sugerido: {selectedTrastero.precioMensual} €/mes
              </p>
            )}
            {fieldErrors.trasteroId?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="precioMensual">Precio mensual *</Label>
              <Input
                id="precioMensual"
                name="precioMensual"
                type="number"
                step="0.01"
                min="0.01"
                required
                value={precioMensual}
                onChange={(e) => setPrecioMensual(e.target.value)}
                placeholder="0.00"
              />
              {fieldErrors.precioMensual?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
            <div className="grid gap-2">
              <Label htmlFor="fianza">Fianza</Label>
              <Input
                id="fianza"
                name="fianza"
                type="number"
                step="0.01"
                min="0"
                value={fianza}
                onChange={(e) => setFianza(e.target.value)}
                placeholder="0.00"
              />
              {fieldErrors.fianza?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
          </div>

          <div className="flex items-center gap-2">
            <Checkbox
              id="fianzaPagada"
              checked={fianzaPagada}
              onCheckedChange={(checked) => setFianzaPagada(checked === true)}
            />
            <Label htmlFor="fianzaPagada" className="cursor-pointer">
              Fianza pagada
            </Label>
          </div>

          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => onOpenChange(false)}
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              disabled={saving || !selectedTrasteroId || !fechaInicio}
            >
              {saving ? "Guardando..." : isEditing ? "Guardar cambios" : "Crear contrato"}
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
  fromDate,
}: {
  value?: Date
  onChange: (date: Date | undefined) => void
  placeholder?: string
  fromDate?: Date
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
          fromDate={fromDate}
        />
      </PopoverContent>
    </Popover>
  )
}
