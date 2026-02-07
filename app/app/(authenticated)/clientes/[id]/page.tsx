"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { ArrowLeft, Mail, Phone, IdCard, Calendar, Plus } from "lucide-react"
import type { components } from "@/lib/api/types"
import { ContratoFormModal } from "@/components/contrato-form-modal"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

type Cliente = components["schemas"]["Cliente"]

interface ContratoWithRelations {
  id?: number
  trastero?: { id: number; numero: string }
  cliente?: { id: number; nombre: string }
  fechaInicio?: string
  fechaFin?: string | null
  precioMensual?: number
  fianza?: number
  fianzaPagada?: boolean
  estado?: string
  createdAt?: string
  updatedAt?: string
}

const estadoVariant: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
  activo: "default",
  pendiente: "outline",
  finalizado: "secondary",
  cancelado: "destructive",
}

function formatCurrency(amount: number | null | undefined) {
  if (amount == null) return "-"
  return new Intl.NumberFormat("es-ES", {
    style: "currency",
    currency: "EUR",
  }).format(amount)
}

function formatDate(date: string | null | undefined) {
  if (!date) return "-"
  return new Date(date).toLocaleDateString("es-ES")
}

export default function ClienteDetailPage() {
  const { id } = useParams<{ id: string }>()
  const router = useRouter()
  const [cliente, setCliente] = useState<Cliente | null>(null)
  const [contratos, setContratos] = useState<ContratoWithRelations[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [contratoModalOpen, setContratoModalOpen] = useState(false)

  const fetchData = () => {
    setLoading(true)
    Promise.all([
      fetch(`/api/clientes/${id}`).then((res) => {
        if (!res.ok) throw new Error("Cliente no encontrado")
        return res.json()
      }),
      fetch(`/api/clientes/${id}/contratos`).then((res) =>
        res.ok ? res.json() : { data: [] }
      ),
    ])
      .then(([clienteData, contratosData]) => {
        setCliente(clienteData)
        setContratos(contratosData.data ?? [])
      })
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    fetchData()
  }, [id])

  if (loading) {
    return (
      <div className="flex flex-col gap-4 px-4 py-4 md:py-6 lg:px-6">
        <div className="bg-muted h-8 w-48 animate-pulse rounded" />
        <div className="bg-muted h-24 animate-pulse rounded-lg" />
        <div className="bg-muted h-48 animate-pulse rounded-lg" />
      </div>
    )
  }

  if (error || !cliente) {
    return (
      <div className="flex flex-col items-center gap-4 px-4 py-12 lg:px-6">
        <p className="text-muted-foreground">{error ?? "Cliente no encontrado"}</p>
        <Button variant="outline" onClick={() => router.push("/clientes")}>
          <ArrowLeft className="size-4" />
          Volver a clientes
        </Button>
      </div>
    )
  }

  return (
    <div className="flex flex-col gap-4 px-4 py-4 md:gap-6 md:py-6 lg:px-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon-sm" onClick={() => router.push("/clientes")}>
          <ArrowLeft className="size-4" />
          <span className="sr-only">Volver</span>
        </Button>
        <div className="flex items-center gap-3">
          <h2 className="text-xl font-semibold">
            {cliente.nombre} {cliente.apellidos}
          </h2>
          <Badge variant={cliente.activo ? "default" : "secondary"}>
            {cliente.activo ? "Activo" : "Inactivo"}
          </Badge>
        </div>
      </div>

      <Card>
        <div className="grid grid-cols-2 gap-x-6 gap-y-3 p-5 sm:grid-cols-4">
          <InfoField icon={IdCard} label="DNI/NIE" value={cliente.dniNie} />
          <InfoField icon={Mail} label="Email" value={cliente.email} />
          <InfoField icon={Phone} label="Teléfono" value={cliente.telefono} />
          <InfoField icon={Calendar} label="Alta" value={formatDate(cliente.createdAt)} />
        </div>
      </Card>

      <Card>
        <CardHeader className="flex-row items-center justify-between">
          <CardTitle className="text-lg">
            Contratos
            {contratos.length > 0 && (
              <Badge variant="outline" className="ml-2 text-xs font-normal">
                {contratos.length}
              </Badge>
            )}
          </CardTitle>
          <Button size="sm" onClick={() => setContratoModalOpen(true)}>
            <Plus className="size-4" />
            Crear contrato
          </Button>
        </CardHeader>

        <ContratoFormModal
          open={contratoModalOpen}
          onOpenChange={setContratoModalOpen}
          clienteId={cliente.id!}
          onSuccess={fetchData}
        />
        <div className="px-6 pb-6">
          {contratos.length === 0 ? (
            <p className="text-muted-foreground py-4 text-center text-sm">
              Este cliente no tiene contratos.
            </p>
          ) : (
            <div className="overflow-hidden rounded-lg border">
              <Table>
                <TableHeader className="bg-muted">
                  <TableRow>
                    <TableHead>ID</TableHead>
                    <TableHead>Trastero</TableHead>
                    <TableHead>Inicio</TableHead>
                    <TableHead>Fin</TableHead>
                    <TableHead>Precio/mes</TableHead>
                    <TableHead>Fianza</TableHead>
                    <TableHead>Estado</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {contratos.map((c) => (
                    <TableRow key={c.id}>
                      <TableCell className="text-muted-foreground">{c.id}</TableCell>
                      <TableCell className="font-medium">
                        {c.trastero?.numero ?? "-"}
                      </TableCell>
                      <TableCell>{formatDate(c.fechaInicio)}</TableCell>
                      <TableCell>{formatDate(c.fechaFin)}</TableCell>
                      <TableCell className="tabular-nums">{formatCurrency(c.precioMensual)}</TableCell>
                      <TableCell>
                        <span className="tabular-nums">{formatCurrency(c.fianza)}</span>
                        {c.fianzaPagada === false && (
                          <Badge variant="destructive" className="ml-1.5 text-[10px]">Pendiente</Badge>
                        )}
                      </TableCell>
                      <TableCell>
                        <Badge variant={estadoVariant[c.estado ?? ""] ?? "outline"}>
                          {c.estado ?? "-"}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
          )}
        </div>
      </Card>
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
