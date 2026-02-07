"use client"

import { useEffect, useState } from "react"
import { TrendingUp, TrendingDown, ArrowUpRight, ArrowDownRight, CalendarClock, Landmark } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import {
  Card,
  CardAction,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"

interface DashboardStats {
  trasteros: {
    total: number
    disponibles: number
    ocupados: number
    tasaOcupacion: number
  }
  contratos: {
    activos: number
    total: number
    proximosAVencer: number
    fianzasPendientes: number
  }
  financiero: {
    ingresosMes: number
    gastosMes: number
    balanceMes: number
  }
  prestamos?: {
    pendienteTotal: number
  }
}

function formatCurrency(amount: number) {
  return new Intl.NumberFormat("es-ES", {
    style: "currency",
    currency: "EUR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

export function SectionCards() {
  const [stats, setStats] = useState<DashboardStats | null>(null)

  useEffect(() => {
    fetch("/api/dashboard/stats")
      .then((res) => (res.ok ? res.json() : null))
      .then((data) => {
        if (data) setStats(data)
      })
  }, [])

  if (!stats) {
    return (
      <div className="grid grid-cols-1 gap-4 px-4 lg:px-6 @xl/main:grid-cols-2 @5xl/main:grid-cols-4">
        {Array.from({ length: 4 }).map((_, i) => (
          <Card key={i} className="@container/card animate-pulse">
            <CardHeader>
              <div className="bg-muted h-4 w-24 rounded" />
              <div className="bg-muted mt-2 h-8 w-16 rounded" />
            </CardHeader>
            <CardFooter><div className="bg-muted h-4 w-32 rounded" /></CardFooter>
          </Card>
        ))}
      </div>
    )
  }

  const balancePositive = stats.financiero.balanceMes >= 0

  return (
    <div className="*:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card dark:*:data-[slot=card]:bg-card grid grid-cols-1 gap-4 px-4 *:data-[slot=card]:bg-gradient-to-t *:data-[slot=card]:shadow-xs lg:px-6 @xl/main:grid-cols-2 @5xl/main:grid-cols-4">
      {/* Tasa de ocupación */}
      <Card className="@container/card">
        <CardHeader>
          <CardDescription>Tasa de ocupación</CardDescription>
          <CardTitle className="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
            {stats.trasteros.tasaOcupacion.toFixed(0)}%
          </CardTitle>
          <CardAction>
            <Badge variant="outline">
              {stats.trasteros.tasaOcupacion >= 75 ? <TrendingUp /> : <TrendingDown />}
              {stats.trasteros.ocupados}/{stats.trasteros.total}
            </Badge>
          </CardAction>
        </CardHeader>
        <CardFooter className="flex-col items-start gap-1.5 text-sm">
          <div className="line-clamp-1 flex gap-2 font-medium">
            {stats.trasteros.ocupados} ocupados · {stats.trasteros.disponibles} disponibles
          </div>
          <div className="text-muted-foreground">
            De un total de {stats.trasteros.total} trasteros
          </div>
        </CardFooter>
      </Card>

      {/* Balance del mes */}
      <Card className="@container/card">
        <CardHeader>
          <CardDescription>Balance del mes</CardDescription>
          <CardTitle className="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
            {formatCurrency(stats.financiero.balanceMes)}
          </CardTitle>
          <CardAction>
            <Badge variant="outline">
              {balancePositive ? <TrendingUp /> : <TrendingDown />}
              {balancePositive ? "Positivo" : "Negativo"}
            </Badge>
          </CardAction>
        </CardHeader>
        <CardFooter className="flex-col items-start gap-1.5 text-sm">
          <div className="line-clamp-1 flex gap-2 font-medium">
            <span className="flex items-center gap-1 text-green-500">
              <ArrowUpRight className="size-4" />
              {formatCurrency(stats.financiero.ingresosMes)}
            </span>
            <span className="flex items-center gap-1 text-red-500">
              <ArrowDownRight className="size-4" />
              {formatCurrency(stats.financiero.gastosMes)}
            </span>
          </div>
          <div className="text-muted-foreground">
            Ingresos y gastos del mes actual
          </div>
        </CardFooter>
      </Card>

      {/* Contratos próximos a vencer */}
      <Card className="@container/card">
        <CardHeader>
          <CardDescription>Contratos por vencer</CardDescription>
          <CardTitle className="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
            {stats.contratos.proximosAVencer}
          </CardTitle>
          <CardAction>
            <Badge variant="outline">
              <CalendarClock className="size-3.5" />
              {stats.contratos.activos} activos
            </Badge>
          </CardAction>
        </CardHeader>
        <CardFooter className="flex-col items-start gap-1.5 text-sm">
          <div className="line-clamp-1 flex gap-2 font-medium">
            Próximos 30 días
          </div>
          <div className="text-muted-foreground">
            {stats.contratos.total} contratos en total
          </div>
        </CardFooter>
      </Card>

      {/* Préstamos pendientes */}
      <Card className="@container/card">
        <CardHeader>
          <CardDescription>Préstamos pendientes</CardDescription>
          <CardTitle className="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
            {formatCurrency(stats.prestamos?.pendienteTotal ?? 0)}
          </CardTitle>
          <CardAction>
            <Badge variant="outline">
              <Landmark className="size-3.5" />
              Deuda viva
            </Badge>
          </CardAction>
        </CardHeader>
        <CardFooter className="flex-col items-start gap-1.5 text-sm">
          <div className="line-clamp-1 flex gap-2 font-medium">
            Total pendiente de amortizar
          </div>
          <div className="text-muted-foreground">
            De préstamos activos
          </div>
        </CardFooter>
      </Card>
    </div>
  )
}
