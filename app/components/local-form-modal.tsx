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

interface LocalFormModalProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  onSuccess?: () => void
}

export function LocalFormModal({
  open,
  onOpenChange,
  onSuccess,
}: LocalFormModalProps) {
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})

  // Local fields
  const [nombre, setNombre] = useState("")
  const [superficieTotal, setSuperficieTotal] = useState("")
  const [numeroTrasteros, setNumeroTrasteros] = useState("")
  const [fechaCompra, setFechaCompra] = useState<Date | undefined>()
  const [precioCompra, setPrecioCompra] = useState("")
  const [referenciaCatastral, setReferenciaCatastral] = useState("")

  // Direccion fields
  const [tipoVia, setTipoVia] = useState("")
  const [nombreVia, setNombreVia] = useState("")
  const [numero, setNumero] = useState("")
  const [piso, setPiso] = useState("")
  const [puerta, setPuerta] = useState("")
  const [codigoPostal, setCodigoPostal] = useState("")
  const [ciudad, setCiudad] = useState("")
  const [provincia, setProvincia] = useState("")

  const resetForm = () => {
    setError(null)
    setFieldErrors({})
    setNombre("")
    setSuperficieTotal("")
    setNumeroTrasteros("")
    setFechaCompra(undefined)
    setPrecioCompra("")
    setReferenciaCatastral("")
    setTipoVia("")
    setNombreVia("")
    setNumero("")
    setPiso("")
    setPuerta("")
    setCodigoPostal("")
    setCiudad("")
    setProvincia("")
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

    try {
      // 1. Create direccion
      const dirBody = {
        tipoVia: tipoVia || null,
        nombreVia,
        numero: numero || null,
        piso: piso || null,
        puerta: puerta || null,
        codigoPostal,
        ciudad,
        provincia,
        pais: "España",
      }

      const dirRes = await fetchClient("/api/direcciones", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dirBody),
      })

      if (!dirRes.ok) {
        const dirData = await dirRes.json()
        const details = dirData.error?.details as Record<string, string[]> | undefined
        if (details) {
          setFieldErrors(details)
        } else {
          setError(dirData.error?.message ?? "Error al crear dirección")
        }
        return
      }

      const direccion = await dirRes.json()

      // 2. Create local with direccionId
      const localBody = {
        nombre,
        direccionId: direccion.id,
        superficieTotal: superficieTotal ? Number(superficieTotal) : null,
        numeroTrasteros: numeroTrasteros ? Number(numeroTrasteros) : null,
        fechaCompra: fechaCompra ? format(fechaCompra, "yyyy-MM-dd") : null,
        precioCompra: precioCompra ? Number(precioCompra) : null,
        referenciaCatastral: referenciaCatastral || null,
      }

      const localRes = await fetchClient("/api/locales", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(localBody),
      })

      if (!localRes.ok) {
        const localData = await localRes.json()
        const details = localData.error?.details as Record<string, string[]> | undefined
        if (details) {
          setFieldErrors(details)
        } else {
          setError(localData.error?.message ?? "Error al crear local")
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
      <DialogContent className="max-w-lg">
        <DialogHeader>
          <DialogTitle>Nuevo local</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="grid gap-4">
          {error && (
            <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-600 dark:border-red-800 dark:bg-red-950 dark:text-red-400">
              {error}
            </div>
          )}

          <div className="grid gap-2">
            <Label htmlFor="nombre">Nombre del local *</Label>
            <Input
              id="nombre"
              value={nombre}
              onChange={(e) => setNombre(e.target.value)}
              required
              placeholder="Ej: Local Calle Mayor"
            />
            {fieldErrors.nombre?.map((msg) => (
              <p key={msg} className="text-destructive text-sm">{msg}</p>
            ))}
          </div>

          {/* Direccion */}
          <p className="text-muted-foreground text-xs font-medium uppercase tracking-wide">Dirección</p>

          <div className="grid grid-cols-3 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="tipoVia">Tipo vía</Label>
              <Input
                id="tipoVia"
                value={tipoVia}
                onChange={(e) => setTipoVia(e.target.value)}
                placeholder="Calle"
              />
            </div>
            <div className="col-span-2 grid gap-2">
              <Label htmlFor="nombreVia">Nombre vía *</Label>
              <Input
                id="nombreVia"
                value={nombreVia}
                onChange={(e) => setNombreVia(e.target.value)}
                required
                placeholder="Mayor"
              />
              {fieldErrors.nombreVia?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
          </div>

          <div className="grid grid-cols-3 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="numero">Número</Label>
              <Input
                id="numero"
                value={numero}
                onChange={(e) => setNumero(e.target.value)}
                placeholder="12"
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="piso">Piso</Label>
              <Input
                id="piso"
                value={piso}
                onChange={(e) => setPiso(e.target.value)}
                placeholder="Bajo"
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="puerta">Puerta</Label>
              <Input
                id="puerta"
                value={puerta}
                onChange={(e) => setPuerta(e.target.value)}
                placeholder="A"
              />
            </div>
          </div>

          <div className="grid grid-cols-3 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="codigoPostal">C.P. *</Label>
              <Input
                id="codigoPostal"
                value={codigoPostal}
                onChange={(e) => setCodigoPostal(e.target.value)}
                required
                placeholder="28001"
              />
              {fieldErrors.codigoPostal?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
            <div className="grid gap-2">
              <Label htmlFor="ciudad">Ciudad *</Label>
              <Input
                id="ciudad"
                value={ciudad}
                onChange={(e) => setCiudad(e.target.value)}
                required
                placeholder="Madrid"
              />
              {fieldErrors.ciudad?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
            <div className="grid gap-2">
              <Label htmlFor="provincia">Provincia *</Label>
              <Input
                id="provincia"
                value={provincia}
                onChange={(e) => setProvincia(e.target.value)}
                required
                placeholder="Madrid"
              />
              {fieldErrors.provincia?.map((msg) => (
                <p key={msg} className="text-destructive text-sm">{msg}</p>
              ))}
            </div>
          </div>

          {/* Datos del local */}
          <p className="text-muted-foreground text-xs font-medium uppercase tracking-wide">Datos del local</p>

          <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
              <Label htmlFor="superficieTotal">Superficie (m²)</Label>
              <Input
                id="superficieTotal"
                type="number"
                step="0.01"
                min="0.01"
                value={superficieTotal}
                onChange={(e) => setSuperficieTotal(e.target.value)}
                placeholder="0.00"
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="numeroTrasteros">Nº trasteros</Label>
              <Input
                id="numeroTrasteros"
                type="number"
                min="0"
                value={numeroTrasteros}
                onChange={(e) => setNumeroTrasteros(e.target.value)}
                placeholder="0"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
              <Label>Fecha compra</Label>
              <DatePicker
                value={fechaCompra}
                onChange={setFechaCompra}
                placeholder="Seleccionar fecha"
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="precioCompra">Precio compra</Label>
              <Input
                id="precioCompra"
                type="number"
                step="0.01"
                min="0"
                value={precioCompra}
                onChange={(e) => setPrecioCompra(e.target.value)}
                placeholder="0.00"
              />
            </div>
          </div>

          <div className="grid gap-2">
            <Label htmlFor="referenciaCatastral">Ref. catastral</Label>
            <Input
              id="referenciaCatastral"
              value={referenciaCatastral}
              onChange={(e) => setReferenciaCatastral(e.target.value)}
              placeholder="1234567AB1234C0001AB"
            />
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => handleOpenChange(false)}>
              Cancelar
            </Button>
            <Button type="submit" disabled={saving}>
              {saving ? "Guardando..." : "Crear local"}
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
