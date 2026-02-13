"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { Mail, Phone, IdCard, Calendar, Plus, Pencil } from "lucide-react"
import type { components } from "@/lib/api/types"
import { fetchClient } from "@/lib/api/fetch-client"
import { usePageHeader } from "@/lib/page-header-context"
import { Badge } from "@/components/ui/badge"
import { ClienteFormModal } from "@/components/data-tables/clientes/cliente-form-modal"
import { ContratoFormModal, type ContratoData } from "@/components/data-tables/contratos/contrato-form-modal"
import { IngresoFormModal } from "@/components/data-tables/ingresos/ingreso-form-modal"
import { ContratosTable, type ContratoWithRelations } from "@/components/data-tables/contratos/contratos-table"
import { IngresosTable, type Ingreso } from "@/components/data-tables/ingresos/ingresos-table"
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import { formatDate } from "@/lib/format"

type Cliente = components["schemas"]["Cliente"]

export default function ClienteDetailPage() {
  const { id } = useParams<{ id: string }>()
  const router = useRouter()
  const [cliente, setCliente] = useState<Cliente | null>(null)
  const [contratos, setContratos] = useState<ContratoWithRelations[]>([])
  const [ingresos, setIngresos] = useState<Ingreso[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [contratoModalOpen, setContratoModalOpen] = useState(false)
  const [editingContrato, setEditingContrato] = useState<ContratoData | null>(null)
  const [ingresoModalOpen, setIngresoModalOpen] = useState(false)
  const [editingCliente, setEditingCliente] = useState(false)
  const { setHeaderContent } = usePageHeader()

  const fetchData = () => {
    setLoading(true)
    Promise.all([
      fetchClient(`/api/clientes/${id}`).then((res) => {
        if (!res.ok) throw new Error("Cliente no encontrado")
        return res.json()
      }),
      fetchClient(`/api/clientes/${id}/contratos`).then((res) =>
        res.ok ? res.json() : { data: [] }
      ),
      fetchClient(`/api/clientes/${id}/ingresos`).then((res) =>
        res.ok ? res.json() : { data: [] }
      ),
    ])
      .then(([clienteData, contratosData, ingresosData]) => {
        setCliente(clienteData)
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
    if (cliente) {
      setHeaderContent(
        <div className="flex items-center gap-2">
          <h1 className="text-base font-medium">
            {cliente.nombre} {cliente.apellidos}
          </h1>
          <Badge variant={cliente.activo ? "default" : "secondary"} className="text-[10px]">
            {cliente.activo ? "Activo" : "Inactivo"}
          </Badge>
        </div>
      )
    }
  }, [cliente])

  // Map contratoId -> trastero numero for the ingresos table
  const contratoTrasteroMap = new Map(
    contratos.map((c) => [c.id, c.trastero?.numero ?? `#${c.id}`])
  )

  if (loading) {
    return (
      <div className="flex flex-col gap-4 px-4 py-4 md:py-6 lg:px-6">
        <div className="bg-muted h-8 w-48 animate-pulse rounded" />
        <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-2">
          <div className="flex flex-col gap-4 md:gap-6">
            <div className="bg-muted h-24 animate-pulse rounded-lg" />
            <div className="bg-muted h-48 animate-pulse rounded-lg" />
          </div>
          <div className="bg-muted h-72 animate-pulse rounded-lg" />
        </div>
      </div>
    )
  }

  if (error || !cliente) {
    return (
      <div className="flex flex-col items-center gap-4 px-4 py-12 lg:px-6">
        <p className="text-muted-foreground">{error ?? "Cliente no encontrado"}</p>
        <Button variant="outline" onClick={() => router.push("/clientes")}>
          Volver a clientes
        </Button>
      </div>
    )
  }

  return (
    <div className="flex flex-col gap-4 px-4 py-4 md:gap-6 md:py-6 lg:px-6">
      <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-2">
        {/* Left column: Info + Contratos */}
        <div className="flex flex-col gap-4 md:gap-6">
          <Card>
            <div className="relative p-5">
              <Button
                variant="ghost"
                size="icon-sm"
                className="absolute right-3 top-3"
                onClick={() => setEditingCliente(true)}
              >
                <Pencil className="size-3.5" />
                <span className="sr-only">Editar cliente</span>
              </Button>
              <div className="grid grid-cols-2 gap-x-6 gap-y-3">
                <InfoField icon={IdCard} label="DNI/NIE" value={cliente.dniNie} />
                <InfoField icon={Mail} label="Email" value={cliente.email} />
                <InfoField icon={Phone} label="Teléfono" value={cliente.telefono} />
                <InfoField icon={Calendar} label="Alta" value={formatDate(cliente.createdAt)} />
              </div>
            </div>
          </Card>
          <ClienteFormModal
            open={editingCliente}
            onOpenChange={setEditingCliente}
            cliente={{
              id: cliente.id!,
              nombre: cliente.nombre,
              apellidos: cliente.apellidos,
              dniNie: cliente.dniNie,
              email: cliente.email,
              telefono: cliente.telefono,
              activo: cliente.activo,
            }}
            onSuccess={fetchData}
          />

          <ContratoFormModal
            open={contratoModalOpen}
            onOpenChange={setContratoModalOpen}
            clienteId={cliente.id!}
            onSuccess={fetchData}
          />
          <ContratoFormModal
            open={!!editingContrato}
            onOpenChange={(open) => { if (!open) setEditingContrato(null) }}
            clienteId={cliente.id!}
            contrato={editingContrato}
            onSuccess={fetchData}
          />
          <ContratosTable
            contratos={contratos}
            title="Contratos"
            showSearch={false}
            action={
              <Button size="sm" onClick={() => setContratoModalOpen(true)}>
                <Plus className="size-4" />
                Crear contrato
              </Button>
            }
            onEdit={(c) => setEditingContrato({
              id: c.id!,
              trastero: c.trastero,
              clienteId: cliente!.id!,
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
          <IngresoFormModal
            open={ingresoModalOpen}
            onOpenChange={setIngresoModalOpen}
            contratos={contratos.map((c) => ({
              id: c.id!,
              trasteroNumero: c.trastero?.numero ?? `#${c.id}`,
            }))}
            onSuccess={fetchData}
          />
          <IngresosTable
            ingresos={ingresos}
            contratoTrasteroMap={contratoTrasteroMap}
            title="Ingresos"
            showSearch={false}
            action={
              <Button size="sm" onClick={() => setIngresoModalOpen(true)}>
                <Plus className="size-4" />
                Crear ingreso
              </Button>
            }
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
