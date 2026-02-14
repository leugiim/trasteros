"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import {
  MapPin, Calendar, Hash, Ruler, Plus, Pencil,
  TrendingUp, TrendingDown, Wallet, Scale,
  Warehouse, DoorOpen, DoorClosed, Percent,
  Landmark, CircleCheckBig, CircleDollarSign, Clock,
  FileText, BadgeDollarSign, ShoppingCart, ChartNoAxesCombined,
} from "lucide-react"
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
import { LocalFormModal } from "@/components/data-tables/locales/local-form-modal"
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
  const [contratos, setContratos] = useState<{ id: number; trastero?: { id: number; numero: string }; cliente?: { nombre: string; apellidos?: string }; estado?: string }[]>([])
  const [trasteroModalOpen, setTrasteroModalOpen] = useState(false)
  const [prestamoModalOpen, setPrestamoModalOpen] = useState(false)
  const [ingresoModalOpen, setIngresoModalOpen] = useState(false)
  const [gastoModalOpen, setGastoModalOpen] = useState(false)
  const [editingLocal, setEditingLocal] = useState(false)
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
      fetchClient(`/api/contratos`).then((res) =>
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
          (contratosData.data ?? []).filter((c: { trastero?: { id: number } }) => trasteroIds.has(c.trastero?.id))
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
    <div className="flex flex-col gap-4 px-4 py-3 md:gap-6 md:py-3 lg:px-3">
      {/* Info card */}
      <Card>
        <div className="relative p-5">
          <Button
            variant="ghost"
            size="icon-sm"
            className="absolute right-3 top-3"
            onClick={() => setEditingLocal(true)}
          >
            <Pencil className="size-3.5" />
            <span className="sr-only">Editar local</span>
          </Button>
          <div className="grid grid-cols-3 gap-x-6 gap-y-3">
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
        </div>
      </Card>
      <LocalFormModal
        open={editingLocal}
        onOpenChange={setEditingLocal}
        local={{
          id: local.id,
          nombre: local.nombre,
          direccionId: dir.id,
          superficieTotal: local.superficieTotal,
          numeroTrasteros: local.numeroTrasteros,
          fechaCompra: local.fechaCompra,
          precioCompra: local.precioCompra,
          referenciaCatastral: local.referenciaCatastral,
          valorCatastral: local.valorCatastral,
          direccion: dir,
        }}
        onSuccess={fetchData}
      />

      {/* Tabs */}
      <Tabs defaultValue="metricas">
        <TabsList variant="line">
          <TabsTrigger value="metricas">Métricas</TabsTrigger>
          <TabsTrigger value="trasteros">Trasteros</TabsTrigger>
          <TabsTrigger value="finanzas">Finanzas</TabsTrigger>
          <TabsTrigger value="prestamos">Préstamos</TabsTrigger>
        </TabsList>

        <TabsContent value="metricas">
          <MetricasPanel
            trasteros={trasteros}
            ingresos={ingresos}
            gastos={gastos}
            prestamos={prestamos}
            contratos={contratos}
            local={local}
          />
        </TabsContent>

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
              clienteNombre: [c.cliente?.nombre, c.cliente?.apellidos].filter(Boolean).join(" ") || undefined,
              estado: c.estado,
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

function MetricasPanel({
  trasteros,
  ingresos,
  gastos,
  prestamos,
  contratos,
  local,
}: {
  trasteros: Trastero[]
  ingresos: Ingreso[]
  gastos: Gasto[]
  prestamos: Prestamo[]
  contratos: { estado?: string }[]
  local: Local
}) {
  const totalTrasteros = trasteros.length
  const ocupados = trasteros.filter((t) => t.estado === "ocupado").length
  const disponibles = trasteros.filter((t) => t.estado === "disponible").length
  const tasaOcupacion = totalTrasteros > 0 ? (ocupados / totalTrasteros) * 100 : 0

  const totalIngresos = ingresos.reduce((sum, i) => sum + i.importe, 0)
  const totalGastos = gastos.reduce((sum, g) => sum + g.importe, 0)
  const balance = totalIngresos - totalGastos

  const totalCapitalPrestamos = prestamos.reduce((sum, p) => sum + p.totalADevolver, 0)
  const totalAmortizado = prestamos.reduce((sum, p) => sum + (p.amortizado ?? 0), 0)
  const pendienteAmortizar = totalCapitalPrestamos - totalAmortizado
  const pctAmortizado = totalCapitalPrestamos > 0 ? (totalAmortizado / totalCapitalPrestamos) * 100 : 0

  const contratosActivos = contratos.filter((c) => c.estado === "activo").length

  const ingresoMensualPotencial = trasteros
    .filter((t) => t.estado === "ocupado")
    .reduce((sum, t) => sum + (t.precioMensual ?? 0), 0)

  const rentabilidad =
    local.precioCompra && local.precioCompra > 0
      ? (totalIngresos - totalGastos) / local.precioCompra * 100
      : null

  return (
    <div className="flex flex-col gap-3">
      {/* Trasteros */}
      <div>
        <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wide">Trasteros</p>
        <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
          <StatCard label="Total" value={String(totalTrasteros)} icon={Warehouse} iconClassName="text-blue-500" />
          <StatCard label="Ocupados" value={String(ocupados)} icon={DoorClosed} iconClassName="text-amber-500" />
          <StatCard label="Disponibles" value={String(disponibles)} icon={DoorOpen} iconClassName="text-emerald-500" />
          <StatCard label="Tasa ocupación" value={`${tasaOcupacion.toFixed(0)}%`} icon={Percent} iconClassName="text-violet-500" />
        </div>
      </div>

      {/* Financiero */}
      <div>
        <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wide">Financiero</p>
        <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
          <StatCard
            label="Total ingresos"
            value={formatCurrency(totalIngresos)}
            icon={TrendingUp}
            iconClassName="text-emerald-500"
          />
          <StatCard
            label="Total gastos"
            value={formatCurrency(totalGastos)}
            icon={TrendingDown}
            iconClassName="text-red-500"
          />
          <StatCard
            label="Balance"
            value={formatCurrency(balance)}
            icon={Scale}
            iconClassName={balance >= 0 ? "text-emerald-500" : "text-red-500"}
            valueClassName={balance >= 0 ? "text-emerald-600 dark:text-emerald-400" : "text-red-600 dark:text-red-400"}
          />
          <StatCard label="Contratos activos" value={String(contratosActivos)} icon={FileText} iconClassName="text-blue-500" />
        </div>
      </div>

      {/* Préstamos */}
      {prestamos.length > 0 && (
        <div>
          <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wide">Préstamos</p>
          <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
            <StatCard label="Total a devolver" value={formatCurrency(totalCapitalPrestamos)} icon={Landmark} iconClassName="text-orange-500" />
            <StatCard label="Amortizado" value={formatCurrency(totalAmortizado)} sub={`${pctAmortizado.toFixed(1)}%`} icon={CircleCheckBig} iconClassName="text-emerald-500" />
            <StatCard label="Pendiente" value={formatCurrency(pendienteAmortizar)} icon={Clock} iconClassName="text-amber-500" />
            <StatCard label="Préstamos activos" value={String(prestamos.filter((p) => p.estado === "activo").length)} icon={Wallet} iconClassName="text-violet-500" />
          </div>
        </div>
      )}

      {/* Rentabilidad */}
      <div>
        <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wide">Rentabilidad</p>
        <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
          <StatCard label="Ingreso mensual potencial" value={formatCurrency(ingresoMensualPotencial)} icon={CircleDollarSign} iconClassName="text-emerald-500" />
          <StatCard label="Precio compra" value={formatCurrency(local.precioCompra)} icon={ShoppingCart} iconClassName="text-slate-500" />
          {rentabilidad !== null && (
            <StatCard
              label="Rentabilidad s/ compra"
              value={`${rentabilidad.toFixed(2)}%`}
              icon={ChartNoAxesCombined}
              iconClassName={rentabilidad >= 0 ? "text-emerald-500" : "text-red-500"}
              valueClassName={rentabilidad >= 0 ? "text-emerald-600 dark:text-emerald-400" : "text-red-600 dark:text-red-400"}
            />
          )}
        </div>
      </div>
    </div>
  )
}

function StatCard({
  label,
  value,
  sub,
  icon: Icon,
  iconClassName,
  valueClassName,
}: {
  label: string
  value: string
  sub?: string
  icon?: React.ComponentType<{ className?: string }>
  iconClassName?: string
  valueClassName?: string
}) {
  return (
    <Card>
      <div className="flex items-center gap-2.5 px-3 py-0">
        {Icon && (
          <div className="bg-muted flex size-8 shrink-0 items-center justify-center rounded-md">
            <Icon className={`size-5 ${iconClassName ?? ""}`} />
          </div>
        )}
        <div className="flex items-baseline gap-1.5">
          <span className={`text-sm font-semibold tabular-nums leading-none ${valueClassName ?? ""}`}>
            {value}
            {sub && <span className="text-muted-foreground text-[10px] font-normal">({sub})</span>}
          </span>
          <span className="text-muted-foreground text-[11px] leading-none">{label}</span>
        </div>
      </div>
    </Card>
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
