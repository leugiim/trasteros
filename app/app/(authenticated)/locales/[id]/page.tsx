"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { MapPin, Calendar, Hash, Ruler, Plus } from "lucide-react"
import type { components } from "@/lib/api/types"
import { fetchClient } from "@/lib/api/fetch-client"
import { usePageHeader } from "@/lib/page-header-context"
import { formatCurrency, formatDate } from "@/lib/format"
import { TrasterosTable } from "@/components/data-tables/trasteros/trasteros-table"
import { TrasteroFormModal } from "@/components/data-tables/trasteros/trastero-form-modal"
import { IngresosTable, type Ingreso } from "@/components/data-tables/ingresos/ingresos-table"
import { GastosTable, type Gasto } from "@/components/data-tables/gastos/gastos-table"
import { GastoFormModal } from "@/components/data-tables/gastos/gasto-form-modal"
import { IngresoFormModal } from "@/components/data-tables/ingresos/ingreso-form-modal"
import { PrestamosTable, type Prestamo } from "@/components/data-tables/prestamos/prestamos-table"
import { PrestamoFormModal } from "@/components/data-tables/prestamos/prestamo-form-modal"
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import {
  Tabs,
  TabsList,
  TabsTrigger,
  TabsContent,
} from "@/components/ui/tabs"

interface Direccion {
  id: number
  tipoVia?: string | null
  nombreVia: string
  numero?: string | null
  piso?: string | null
  puerta?: string | null
  codigoPostal: string
  ciudad: string
  provincia: string
  pais: string
  direccionCompleta?: string
}

interface Local {
  id: number
  nombre: string
  direccion: Direccion
  superficieTotal?: number | null
  numeroTrasteros?: number | null
  fechaCompra?: string | null
  precioCompra?: number | null
  referenciaCatastral?: string | null
  valorCatastral?: number | null
  createdAt?: string
}

type Trastero = components["schemas"]["Trastero"]

export default function LocalDetailPage() {
  const { id } = useParams<{ id: string }>()
  const router = useRouter()
  const [local, setLocal] = useState<Local | null>(null)
  const [trasteros, setTrasteros] = useState<Trastero[]>([])
  const [ingresos, setIngresos] = useState<Ingreso[]>([])
  const [gastos, setGastos] = useState<Gasto[]>([])
  const [prestamos, setPrestamos] = useState<Prestamo[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [contratos, setContratos] = useState<{ id: number; trastero?: { numero: string } }[]>([])
  const [trasteroModalOpen, setTrasteroModalOpen] = useState(false)
  const [prestamoModalOpen, setPrestamoModalOpen] = useState(false)
  const [ingresoModalOpen, setIngresoModalOpen] = useState(false)
  const [gastoModalOpen, setGastoModalOpen] = useState(false)
  const { setHeaderContent } = usePageHeader()

  const fetchData = () => {
    setLoading(true)
    Promise.all([
      fetchClient(`/api/locales/${id}`).then((res) => {
        if (!res.ok) throw new Error("Local no encontrado")
        return res.json()
      }),
      fetchClient(`/api/locales/${id}/trasteros`).then((res) =>
        res.ok ? res.json() : { data: [] }
      ),
      fetchClient(`/api/locales/${id}/ingresos`).then((res) =>
        res.ok ? res.json() : { data: [] }
      ),
      fetchClient(`/api/locales/${id}/gastos`).then((res) =>
        res.ok ? res.json() : { data: [] }
      ),
      fetchClient(`/api/prestamos/local/${id}`).then((res) =>
        res.ok ? res.json() : { data: [] }
      ),
      fetchClient(`/api/contratos?estado=activo`).then((res) =>
        res.ok ? res.json() : { data: [] }
      ),
    ])
      .then(([localData, trasterosData, ingresosData, gastosData, prestamosData, contratosData]) => {
        setLocal(localData)
        setTrasteros(trasterosData.data ?? [])
        setIngresos(ingresosData.data ?? [])
        setGastos(gastosData.data ?? [])
        setPrestamos(prestamosData.data ?? [])
        // Filter contratos belonging to this local's trasteros
        const trasteroIds = new Set((trasterosData.data ?? []).map((t: Trastero) => t.id))
        setContratos(
          (contratosData.data ?? []).filter((c: { trasteroId?: number }) => trasteroIds.has(c.trasteroId))
        )
      })
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    fetchData()
    return () => setHeaderContent(null)
  }, [id])

  useEffect(() => {
    if (local) {
      setHeaderContent(
        <h1 className="text-base font-medium">{local.nombre}</h1>
      )
    }
  }, [local])

  if (loading) {
    return (
      <div className="flex flex-col gap-4 px-4 py-4 md:py-6 lg:px-6">
        <div className="bg-muted h-8 w-48 animate-pulse rounded" />
        <div className="bg-muted h-28 animate-pulse rounded-lg" />
        <div className="bg-muted h-56 animate-pulse rounded-lg" />
      </div>
    )
  }

  if (error || !local) {
    return (
      <div className="flex flex-col items-center gap-4 px-4 py-12 lg:px-6">
        <p className="text-muted-foreground">{error ?? "Local no encontrado"}</p>
        <Button variant="outline" onClick={() => router.push("/locales")}>
          Volver a locales
        </Button>
      </div>
    )
  }

  const dir = local.direccion

  return (
    <div className="flex flex-col gap-4 px-4 py-4 md:gap-6 md:py-6 lg:px-6">
      {/* Info card */}
      <Card>
        <div className="grid grid-cols-3 gap-x-6 gap-y-3 p-5">
          <InfoField icon={MapPin} label="Dirección" value={dir.direccionCompleta} />
          <InfoField icon={Ruler} label="Superficie" value={local.superficieTotal ? `${local.superficieTotal} m²` : null} />
          <InfoField icon={Hash} label="Nº trasteros" value={local.numeroTrasteros != null ? String(local.numeroTrasteros) : null} />
          <InfoField icon={Calendar} label="Fecha compra" value={formatDate(local.fechaCompra)} />
          <InfoField label="Precio compra" value={formatCurrency(local.precioCompra)} />
          <InfoField label="Ref. catastral" value={local.referenciaCatastral} />
          <InfoField label="Valor catastral" value={formatCurrency(local.valorCatastral)} />
          <InfoField label="C.P." value={dir.codigoPostal} />
          <InfoField label="Ciudad" value={`${dir.ciudad}, ${dir.provincia}`} />
        </div>
      </Card>

      {/* Tabs */}
      <Tabs defaultValue="trasteros">
        <TabsList variant="line">
          <TabsTrigger value="trasteros">Trasteros</TabsTrigger>
          <TabsTrigger value="finanzas">Finanzas</TabsTrigger>
          <TabsTrigger value="prestamos">Préstamos</TabsTrigger>
        </TabsList>

        <TabsContent value="trasteros">
          <TrasteroFormModal
            open={trasteroModalOpen}
            onOpenChange={setTrasteroModalOpen}
            defaultLocalId={local.id}
            defaultLocalNombre={local.nombre}
            onSuccess={fetchData}
          />
          <TrasterosTable
            trasteros={trasteros}
            title="Trasteros"
            showSearch={false}
            action={
              <Button size="sm" onClick={() => setTrasteroModalOpen(true)}>
                <Plus className="size-4" />
                Crear trastero
              </Button>
            }
          />
        </TabsContent>

        <TabsContent value="finanzas">
          <IngresoFormModal
            open={ingresoModalOpen}
            onOpenChange={setIngresoModalOpen}
            contratos={contratos.map((c) => ({
              id: c.id,
              trasteroNumero: c.trastero?.numero ?? `#${c.id}`,
            }))}
            onSuccess={fetchData}
          />
          <GastoFormModal
            open={gastoModalOpen}
            onOpenChange={setGastoModalOpen}
            localId={local.id}
            prestamos={prestamos.map((p) => ({
              id: p.id,
              entidadBancaria: p.entidadBancaria,
              numeroPrestamo: p.numeroPrestamo,
            }))}
            onSuccess={fetchData}
          />
          <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-2">
            <IngresosTable
              ingresos={ingresos}
              contratoTrasteroMap={new Map()}
              title="Ingresos"
              showSearch={false}
              action={
                <Button size="sm" onClick={() => setIngresoModalOpen(true)}>
                  <Plus className="size-4" />
                  Crear ingreso
                </Button>
              }
            />
            <GastosTable
              gastos={gastos}
              title="Gastos"
              showSearch={false}
              action={
                <Button size="sm" onClick={() => setGastoModalOpen(true)}>
                  <Plus className="size-4" />
                  Crear gasto
                </Button>
              }
            />
          </div>
        </TabsContent>

        <TabsContent value="prestamos">
          <PrestamoFormModal
            open={prestamoModalOpen}
            onOpenChange={setPrestamoModalOpen}
            localId={local.id}
            onSuccess={fetchData}
          />
          <PrestamosTable
            prestamos={prestamos}
            title="Préstamos"
            showSearch={false}
            action={
              <Button size="sm" onClick={() => setPrestamoModalOpen(true)}>
                <Plus className="size-4" />
                Crear préstamo
              </Button>
            }
          />
        </TabsContent>
      </Tabs>
    </div>
  )
}

function InfoField({
  icon: Icon,
  label,
  value,
}: {
  icon?: React.ComponentType<{ className?: string }>
  label: string
  value?: string | null
}) {
  return (
    <div className="flex flex-col gap-0.5">
      <span className="text-muted-foreground flex items-center gap-1 text-xs">
        {Icon && <Icon className="size-3" />}
        {label}
      </span>
      <span className="text-sm">{value || "-"}</span>
    </div>
  )
}
