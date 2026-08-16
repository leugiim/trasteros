"use client"

import { useEffect, useState } from "react"
import {
  Scale,
  Warehouse, Percent,
  Landmark, Wallet,
  FileText, ChartNoAxesCombined,
  CircleDollarSign, Users, Building2, ShoppingCart,
} from "lucide-react"
import type { components } from "@/lib/api/types"
import { fetchClient } from "@/lib/api/fetch-client"
import { formatCurrency } from "@/lib/format"
import type { Ingreso } from "@/components/data-tables/ingresos/ingresos-table"
import type { Gasto } from "@/components/data-tables/gastos/gastos-table"
import type { Prestamo } from "@/components/data-tables/prestamos/prestamos-table"
import type { ContratoWithRelations } from "@/components/data-tables/contratos/contratos-table"
import { ChartIngresosGastos } from "@/components/chart-ingresos-gastos"
import { Card } from "@/components/ui/card"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

type Trastero = components["schemas"]["Trastero"]
type Local = components["schemas"]["Local"]
type DashboardStats = components["schemas"]["DashboardStats"]
type RentabilidadLocalItem = {
  localId?: number
  financiero?: { ingresosMensualesPotenciales?: number; ingresosMensualesActuales?: number }
}

export default function DashboardPage() {
  const [loading, setLoading] = useState(true)
  const [year, setYear] = useState(String(new Date().getFullYear()))
  const [locales, setLocales] = useState<Local[]>([])
  const [trasteros, setTrasteros] = useState<Trastero[]>([])
  const [clientes, setClientes] = useState<{ id: number }[]>([])
  const [contratos, setContratos] = useState<ContratoWithRelations[]>([])
  const [ingresos, setIngresos] = useState<Ingreso[]>([])
  const [gastos, setGastos] = useState<Gasto[]>([])
  const [prestamos, setPrestamos] = useState<Prestamo[]>([])
  const [stats, setStats] = useState<DashboardStats | null>(null)
  const [rentabilidadLocales, setRentabilidadLocales] = useState<RentabilidadLocalItem[]>([])

  useEffect(() => {
    setLoading(true)
    Promise.all([
      fetchClient("/api/locales").then((r) => r.ok ? r.json() : { data: [] }),
      fetchClient("/api/trasteros").then((r) => r.ok ? r.json() : { data: [] }),
      fetchClient("/api/clientes").then((r) => r.ok ? r.json() : { data: [] }),
      fetchClient("/api/contratos").then((r) => r.ok ? r.json() : { data: [] }),
      fetchClient("/api/ingresos").then((r) => r.ok ? r.json() : { data: [] }),
      fetchClient("/api/gastos").then((r) => r.ok ? r.json() : { data: [] }),
      fetchClient("/api/prestamos").then((r) => r.ok ? r.json() : { data: [] }),
      fetchClient("/api/dashboard/stats").then((r) => r.ok ? r.json() : null),
      fetchClient("/api/dashboard/rentabilidad").then((r) => r.ok ? r.json() : { locales: [] }),
    ])
      .then(([loc, tra, cli, con, ing, gas, pre, statsData, rentData]) => {
        setLocales(loc.data ?? [])
        setTrasteros(tra.data ?? [])
        setClientes(cli.data ?? [])
        setContratos(con.data ?? [])
        setIngresos(ing.data ?? [])
        setGastos(gas.data ?? [])
        setPrestamos(pre.data ?? [])
        setStats(statsData)
        setRentabilidadLocales(rentData?.locales ?? [])
      })
      .finally(() => setLoading(false))
  }, [])

  // Derive year range
  const currentYear = new Date().getFullYear()
  const dataYears = [
    ...ingresos.map((i) => Number(i.fechaPago.substring(0, 4))),
    ...gastos.map((g) => Number(g.fecha.substring(0, 4))),
  ]
  const minYear = dataYears.length > 0 ? Math.min(...dataYears, currentYear) : currentYear
  const availableYears: string[] = []
  for (let y = currentYear; y >= minYear; y--) {
    availableYears.push(String(y))
  }
  while (availableYears.length < 3) {
    const lowest = Number(availableYears[availableYears.length - 1]) - 1
    availableYears.push(String(lowest))
  }

  // Year-filtered data (still local, no backend endpoint for year-filtered totals)
  const filteredIngresos = ingresos.filter((i) => i.fechaPago.startsWith(year))
  const filteredGastos = gastos.filter((g) => g.fecha.startsWith(year))

  // Entidades: prefer backend stats (occupancy based on active contracts, not estado field)
  const totalTrasteros = stats?.trasteros?.total ?? trasteros.length
  const ocupados = stats?.trasteros?.ocupados ?? trasteros.filter((t) => t.estado === "ocupado").length
  const disponibles = stats?.trasteros?.disponibles ?? trasteros.filter((t) => t.estado === "disponible").length
  const tasaOcupacion = stats?.trasteros?.tasaOcupacion ?? (totalTrasteros > 0 ? (ocupados / totalTrasteros) * 100 : 0)
  const contratosActivos = stats?.contratos?.activos ?? contratos.filter((c) => c.estado === "activo").length
  const fianzasPendientes = stats?.contratos?.fianzasPendientes ?? contratos.filter((c) => c.estado === "activo" && !c.fianzaPagada).length
  const totalContratos = stats?.contratos?.total ?? contratos.length

  // Financiero (filtered by year, local computation)
  const totalIngresosYear = filteredIngresos.reduce((sum, i) => sum + i.importe, 0)
  const totalGastosYear = filteredGastos.reduce((sum, i) => sum + i.importe, 0)
  const balanceYear = totalIngresosYear - totalGastosYear

  // Préstamos
  const totalADevolver = prestamos.reduce((sum, p) => sum + p.totalADevolver, 0)
  const totalAmortizado = prestamos.reduce((sum, p) => sum + (p.amortizado ?? 0), 0)
  const pendienteAmortizar = totalADevolver - totalAmortizado
  const pctAmortizado = totalADevolver > 0 ? (totalAmortizado / totalADevolver) * 100 : 0
  const prestamosActivos = prestamos.filter((p) => p.estado === "activo").length

  // Ingresos mensuales: sum across all locals from rentabilidad endpoint
  const ingresoMensualPotencial = rentabilidadLocales.length > 0
    ? rentabilidadLocales.reduce((sum, l) => sum + (l.financiero?.ingresosMensualesPotenciales ?? 0), 0)
    : trasteros.reduce((sum, t) => sum + (t.precioMensual ?? 0), 0)
  const ingresoMensualActual = rentabilidadLocales.reduce(
    (sum, l) => sum + (l.financiero?.ingresosMensualesActuales ?? 0), 0
  )

  const inversionTotal = locales.reduce((sum, l) => sum + (l.precioCompra ?? 0), 0)
  const rentabilidad =
    inversionTotal > 0 ? ((totalIngresosYear - totalGastosYear) / inversionTotal) * 100 : null

  if (loading) {
    return (
      <div className="flex flex-col gap-4 px-4 py-4 md:gap-6 md:py-6 lg:px-6">
        <div className="bg-muted h-8 w-48 animate-pulse rounded" />
        {Array.from({ length: 3 }).map((_, i) => (
          <div key={i} className="bg-muted h-20 animate-pulse rounded-lg" />
        ))}
      </div>
    )
  }

  return (
    <div className="flex flex-col gap-4 px-4 py-3 md:gap-4 md:py-2 lg:px-3">
      {/* Header */}
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">Métricas del {year}</h2>
        <Select value={year} onValueChange={setYear}>
          <SelectTrigger className="w-28">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            {availableYears.map((y) => (
              <SelectItem key={y} value={y}>{y}</SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      {/* Entidades */}
      <div>
        <p className="text-muted-foreground mb-2 text-xs font-medium uppercase tracking-wide">General</p>
        <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
          <StatCard label="Locales" value={String(locales.length)} icon={Building2} iconClassName="text-blue-500" />
          <StatCard
            label="Trasteros"
            value={String(totalTrasteros)}
            detail={`${ocupados} ocupados · ${disponibles} disponibles`}
            icon={Warehouse}
            iconClassName="text-indigo-500"
          />
          <StatCard label="Clientes" value={String(clientes.length)} icon={Users} iconClassName="text-violet-500" />
          <StatCard
            label="Contratos"
            value={String(totalContratos)}
            detail={`${contratosActivos} activos · ${fianzasPendientes} fianzas pend.`}
            icon={FileText}
            iconClassName="text-amber-500"
          />
        </div>
      </div>

      {/* Ocupación + Financiero */}
      <div>
        <p className="text-muted-foreground mb-2 text-xs font-medium uppercase tracking-wide">Financiero ({year})</p>
        <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
          <StatCard
            label="Ocupación"
            value={`${tasaOcupacion.toFixed(0)}%`}
            icon={Percent}
            iconClassName="text-violet-500"
          />
          <StatCard
            label="Balance"
            value={formatCurrency(balanceYear)}
            detail={`↑ ${formatCurrency(totalIngresosYear)} · ↓ ${formatCurrency(totalGastosYear)}`}
            icon={Scale}
            iconClassName={balanceYear >= 0 ? "text-emerald-500" : "text-red-500"}
            valueClassName={balanceYear >= 0 ? "text-emerald-600 dark:text-emerald-400" : "text-red-600 dark:text-red-400"}
          />
          <StatCard
            label="Ingreso actual/mes"
            value={formatCurrency(ingresoMensualActual)}
            detail={`Potencial: ${formatCurrency(ingresoMensualPotencial)}`}
            icon={CircleDollarSign}
            iconClassName="text-emerald-500"
          />
          {rentabilidad !== null ? (
            <StatCard
              label="Rentabilidad s/ inversión"
              value={`${rentabilidad.toFixed(2)}%`}
              detail={`Inversión: ${formatCurrency(inversionTotal)}`}
              icon={ChartNoAxesCombined}
              iconClassName={rentabilidad >= 0 ? "text-emerald-500" : "text-red-500"}
              valueClassName={rentabilidad >= 0 ? "text-emerald-600 dark:text-emerald-400" : "text-red-600 dark:text-red-400"}
            />
          ) : (
            <StatCard
              label="Inversión total"
              value={formatCurrency(inversionTotal)}
              icon={ShoppingCart}
              iconClassName="text-slate-500"
            />
          )}
        </div>
      </div>

      {/* Préstamos */}
      {prestamos.length > 0 && (
        <div>
          <p className="text-muted-foreground mb-2 text-xs font-medium uppercase tracking-wide">Préstamos</p>
          <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
            <StatCard
              label="Deuda total"
              value={formatCurrency(totalADevolver)}
              detail={`Amortizado: ${formatCurrency(totalAmortizado)} (${pctAmortizado.toFixed(1)}%)`}
              icon={Landmark}
              iconClassName="text-orange-500"
            />
            <StatCard
              label="Pendiente"
              value={formatCurrency(pendienteAmortizar)}
              detail={`${prestamosActivos} préstamos activos`}
              icon={Wallet}
              iconClassName="text-amber-500"
            />
          </div>
        </div>
      )}

      {/* Chart */}
      <ChartIngresosGastos />
    </div>
  )
}

function StatCard({
  label,
  value,
  sub,
  detail,
  icon: Icon,
  iconClassName,
  valueClassName,
}: {
  label: string
  value: string
  sub?: string
  detail?: string
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
        <div className="min-w-0">
          <div className="flex items-baseline gap-1.5">
            <span className={`text-sm font-semibold tabular-nums leading-none ${valueClassName ?? ""}`}>
              {value}
              {sub && <span className="text-muted-foreground text-[10px] font-normal"> ({sub})</span>}
            </span>
            <span className="text-muted-foreground text-[11px] leading-none">{label}</span>
          </div>
          {detail && (
            <p className="text-muted-foreground mt-1 truncate text-[10px] leading-none">{detail}</p>
          )}
        </div>
      </div>
    </Card>
  )
}
