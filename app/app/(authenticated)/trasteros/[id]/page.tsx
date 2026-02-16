"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import Link from "next/link"
import { Hash, Ruler, Euro, Building2, Pencil } from "lucide-react"
import type { components } from "@/lib/api/types"
import { fetchClient } from "@/lib/api/fetch-client"
import { usePageHeader } from "@/lib/page-header-context"
import { formatCurrency } from "@/lib/format"
import { ContratosTable, type ContratoWithRelations } from "@/components/data-tables/contratos/contratos-table"
import { ContratoFormModal, type ContratoData } from "@/components/data-tables/contratos/contrato-form-modal"
import { IngresosTable, type Ingreso } from "@/components/data-tables/ingresos/ingresos-table"
import { IngresoFormModal, type IngresoData } from "@/components/data-tables/ingresos/ingreso-form-modal"
import { TrasteroFormModal } from "@/components/data-tables/trasteros/trastero-form-modal"
import { Badge } from "@/components/ui/badge"
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from "@/components/ui/breadcrumb"
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"

type Trastero = components["schemas"]["Trastero"]

const estadoVariant: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
  disponible: "default",
  ocupado: "secondary",
  reservado: "outline",
  mantenimiento: "destructive",
}

const estadoLabel: Record<string, string> = {
  disponible: "Disponible",
  ocupado: "Ocupado",
  reservado: "Reservado",
  mantenimiento: "Mantenimiento",
}

export default function TrasteroDetailPage() {
  const { id } = useParams<{ id: string }>()
  const router = useRouter()
  const [trastero, setTrastero] = useState<Trastero | null>(null)
  const [contratos, setContratos] = useState<ContratoWithRelations[]>([])
  const [ingresos, setIngresos] = useState<Ingreso[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [editingTrastero, setEditingTrastero] = useState(false)
  const [editingContrato, setEditingContrato] = useState<ContratoData | null>(null)
  const [ingresoModalOpen, setIngresoModalOpen] = useState(false)
  const [editingIngreso, setEditingIngreso] = useState<IngresoData | null>(null)
  const { setHeaderContent } = usePageHeader()

  const fetchData = () => {
    if (!trastero) setLoading(true)
    Promise.all([
      fetchClient(`/api/trasteros/${id}`).then((res) => {
        if (!res.ok) throw new Error("Trastero no encontrado")
        return res.json()
      }),
      fetchClient(`/api/contratos/trastero/${id}`).then((res) =>
        res.ok ? res.json() : { data: [] }
      ),
      fetchClient(`/api/ingresos/trastero/${id}`).then((res) =>
        res.ok ? res.json() : { data: [] }
      ),
    ])
      .then(([trasteroData, contratosData, ingresosData]) => {
        setTrastero(trasteroData)
        setContratos(contratosData.data ?? [])
        setIngresos(ingresosData.data ?? [])
      })
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    fetchData()
    return () => setHeaderContent(null)
  }, [id])

  useEffect(() => {
    if (trastero) {
      // Fetch local name for breadcrumb
      fetchClient(`/api/locales/${trastero.localId}`)
        .then((res) => (res.ok ? res.json() : null))
        .then((localData) => {
          const nombre = localData?.nombre ?? `Local #${trastero.localId}`
          const estado = trastero.estado ?? ""
          setHeaderContent(
            <Breadcrumb>
              <BreadcrumbList>
                <BreadcrumbItem>
                  <BreadcrumbLink asChild>
                    <Link href="/locales">Locales</Link>
                  </BreadcrumbLink>
                </BreadcrumbItem>
                <BreadcrumbSeparator />
                <BreadcrumbItem>
                  <BreadcrumbLink asChild>
                    <Link href={`/locales/${trastero.localId}`}>{nombre}</Link>
                  </BreadcrumbLink>
                </BreadcrumbItem>
                <BreadcrumbSeparator />
                <BreadcrumbItem>
                  <BreadcrumbPage>Trastero {trastero.numero}</BreadcrumbPage>
                </BreadcrumbItem>
                <li>
                  <Badge variant={estadoVariant[estado] ?? "outline"}>
                    {estadoLabel[estado] ?? estado}
                  </Badge>
                </li>
              </BreadcrumbList>
            </Breadcrumb>
          )
        })
    }
  }, [trastero])

  if (loading) {
    return (
      <div className="flex flex-col gap-4 px-4 py-4 md:py-6 lg:px-6">
        <div className="bg-muted h-8 w-48 animate-pulse rounded" />
        <div className="bg-muted h-28 animate-pulse rounded-lg" />
        <div className="bg-muted h-56 animate-pulse rounded-lg" />
      </div>
    )
  }

  if (error || !trastero) {
    return (
      <div className="flex flex-col items-center gap-4 px-4 py-12 lg:px-6">
        <p className="text-muted-foreground">{error ?? "Trastero no encontrado"}</p>
        <Button variant="outline" onClick={() => router.back()}>
          Volver
        </Button>
      </div>
    )
  }

  const contratoActivo = contratos.find((c) => c.estado === "activo")
  const contratoTrasteroMap = new Map(contratos.map((c) => [c.id, trastero.numero ?? `#${trastero.id}`]))

  return (
    <div className="flex flex-col gap-4 px-4 py-3 md:gap-6 md:py-3 lg:px-3">
      <TrasteroFormModal
        open={editingTrastero}
        onOpenChange={setEditingTrastero}
        defaultLocalId={trastero.localId!}
        trastero={{
          id: trastero.id!,
          localId: trastero.localId!,
          numero: trastero.numero!,
          nombre: trastero.nombre,
          superficie: trastero.superficie!,
          precioMensual: trastero.precioMensual!,
          estado: trastero.estado,
        }}
        onSuccess={fetchData}
      />
      <ContratoFormModal
        open={!!editingContrato}
        onOpenChange={(open) => { if (!open) setEditingContrato(null) }}
        clienteId={editingContrato?.clienteId ?? 0}
        contrato={editingContrato}
        onSuccess={fetchData}
      />
      <IngresoFormModal
        open={ingresoModalOpen || !!editingIngreso}
        onOpenChange={(v) => {
          if (!v) { setIngresoModalOpen(false); setEditingIngreso(null) }
        }}
        contratos={contratos.map((c) => ({
          id: c.id!,
          trasteroNumero: trastero.numero ?? `#${trastero.id}`,
          estado: c.estado,
        }))}
        ingreso={editingIngreso}
        onSuccess={fetchData}
      />

      <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-2">
        {/* Left column: Info + Contratos */}
        <div className="flex flex-col gap-4 md:gap-6">
          <Card>
            <div className="relative p-5">
              <Button
                variant="ghost"
                size="icon-sm"
                className="absolute right-3 top-3"
                onClick={() => setEditingTrastero(true)}
              >
                <Pencil className="size-3.5" />
                <span className="sr-only">Editar trastero</span>
              </Button>
              <div className="grid grid-cols-2 gap-x-6 gap-y-3">
                <InfoField icon={Hash} label="Número" value={trastero.numero} />
                <InfoField label="Nombre" value={trastero.nombre} />
                <InfoField icon={Ruler} label="Superficie" value={trastero.superficie ? `${trastero.superficie} m²` : null} />
                <InfoField icon={Euro} label="Precio/mes" value={formatCurrency(trastero.precioMensual)} />
                <InfoField icon={Building2} label="Local" value={
                  <Link href={`/locales/${trastero.localId}`} className="text-primary hover:underline">
                    Local #{trastero.localId}
                  </Link>
                } />
                {contratoActivo && (
                  <InfoField label="Cliente actual" value={
                    contratoActivo.cliente ? (
                      <Link href={`/clientes/${contratoActivo.cliente.id}`} className="text-primary hover:underline">
                        {contratoActivo.cliente.nombre}
                      </Link>
                    ) : "-"
                  } />
                )}
              </div>
            </div>
          </Card>

          <ContratosTable
            contratos={contratos}
            title="Contratos"
            showSearch={false}
            onEdit={(c) => setEditingContrato({
              id: c.id!,
              trastero: c.trastero,
              clienteId: c.cliente?.id,
              fechaInicio: c.fechaInicio,
              fechaFin: c.fechaFin,
              precioMensual: c.precioMensual,
              fianza: c.fianza,
              fianzaPagada: c.fianzaPagada,
            })}
          />
        </div>

        {/* Right column: Ingresos */}
        <div>
          <IngresosTable
            ingresos={ingresos}
            contratoTrasteroMap={contratoTrasteroMap}
            title="Ingresos"
            showSearch={false}
            onEdit={(ingreso) => setEditingIngreso(ingreso)}
          />
        </div>
      </div>
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
  value?: React.ReactNode
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
