"use client"

import { useCallback, useEffect, useState } from "react"
import { Area, AreaChart, CartesianGrid, XAxis, YAxis } from "recharts"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  type ChartConfig,
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
} from "@/components/ui/chart"
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group"

const chartConfig = {
  ingresos: {
    label: "Ingresos",
    color: "var(--color-emerald-500)",
  },
  gastos: {
    label: "Gastos",
    color: "var(--color-red-500)",
  },
} satisfies ChartConfig

interface ChartDataPoint {
  date: string
  ingresos: number
  gastos: number
}

interface ChartResponse {
  period: string
  data: ChartDataPoint[]
}

const periodLabels: Record<string, string> = {
  "1m": "último mes",
  "3m": "últimos 3 meses",
  "6m": "últimos 6 meses",
  "1y": "último año",
}

function formatDateLabel(date: string, period: string): string {
  if (period === "6m" || period === "1y") {
    // Format YYYY-MM as "Ene 2026"
    const [year, month] = date.split("-")
    const d = new Date(Number(year), Number(month) - 1)
    return d.toLocaleDateString("es-ES", { month: "short", year: "numeric" })
  }
  // Format YYYY-MM-DD as "15 Ene"
  const d = new Date(date)
  return d.toLocaleDateString("es-ES", { day: "numeric", month: "short" })
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat("es-ES", {
    style: "currency",
    currency: "EUR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)
}

export function ChartIngresosGastos() {
  const [period, setPeriod] = useState("1m")
  const [data, setData] = useState<ChartDataPoint[]>([])
  const [loading, setLoading] = useState(true)

  const fetchData = useCallback((p: string) => {
    setLoading(true)
    fetch(`/api/dashboard/chart?period=${p}`)
      .then((res) => (res.ok ? res.json() : null))
      .then((json: ChartResponse | null) => {
        if (json) setData(json.data)
      })
      .finally(() => setLoading(false))
  }, [])

  useEffect(() => {
    fetchData(period)
  }, [period, fetchData])

  const handlePeriodChange = (value: string) => {
    if (value) setPeriod(value)
  }

  return (
    <Card className="@container/chart">
      <CardHeader className="flex flex-row items-center justify-between">
        <div>
          <CardTitle>Ingresos vs Gastos</CardTitle>
          <CardDescription>
            Evolución del {periodLabels[period]}
          </CardDescription>
        </div>
        <ToggleGroup
          type="single"
          value={period}
          onValueChange={handlePeriodChange}
          variant="outline"
          size="sm"
        >
          <ToggleGroupItem value="1m">1M</ToggleGroupItem>
          <ToggleGroupItem value="3m">3M</ToggleGroupItem>
          <ToggleGroupItem value="6m">6M</ToggleGroupItem>
          <ToggleGroupItem value="1y">1A</ToggleGroupItem>
        </ToggleGroup>
      </CardHeader>
      <CardContent>
        {loading ? (
          <div className="bg-muted h-[300px] animate-pulse rounded" />
        ) : (
          <ChartContainer config={chartConfig} className="h-[300px] w-full">
            <AreaChart data={data} accessibilityLayer>
              <defs>
                <linearGradient id="fillIngresos" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="var(--color-ingresos)" stopOpacity={0.3} />
                  <stop offset="95%" stopColor="var(--color-ingresos)" stopOpacity={0.02} />
                </linearGradient>
                <linearGradient id="fillGastos" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="var(--color-gastos)" stopOpacity={0.3} />
                  <stop offset="95%" stopColor="var(--color-gastos)" stopOpacity={0.02} />
                </linearGradient>
              </defs>
              <CartesianGrid vertical={false} />
              <XAxis
                dataKey="date"
                tickLine={false}
                axisLine={false}
                tickMargin={8}
                tickFormatter={(value) => formatDateLabel(value, period)}
                interval="preserveStartEnd"
              />
              <YAxis
                tickLine={false}
                axisLine={false}
                tickMargin={8}
                tickFormatter={(value) => formatCurrency(value)}
                width={80}
              />
              <ChartTooltip
                content={
                  <ChartTooltipContent
                    labelFormatter={(value) => formatDateLabel(value as string, period)}
                    formatter={(value, name) => (
                      <div className="flex min-w-[130px] items-center justify-between gap-4">
                        <div className="flex items-center gap-2">
                          <div
                            className="h-2.5 w-2.5 shrink-0 rounded-[2px]"
                            style={{
                              backgroundColor:
                                name === "ingresos"
                                  ? "var(--color-ingresos)"
                                  : "var(--color-gastos)",
                            }}
                          />
                          <span className="text-muted-foreground">
                            {name === "ingresos" ? "Ingresos" : "Gastos"}
                          </span>
                        </div>
                        <span className="font-mono font-medium tabular-nums">
                          {formatCurrency(value as number)}
                        </span>
                      </div>
                    )}
                    hideIndicator
                  />
                }
              />
              <Area
                dataKey="ingresos"
                type="monotone"
                fill="url(#fillIngresos)"
                stroke="var(--color-ingresos)"
                strokeWidth={2}
              />
              <Area
                dataKey="gastos"
                type="monotone"
                fill="url(#fillGastos)"
                stroke="var(--color-gastos)"
                strokeWidth={2}
              />
            </AreaChart>
          </ChartContainer>
        )}
      </CardContent>
    </Card>
  )
}
