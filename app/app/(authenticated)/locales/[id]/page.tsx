"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { ArrowLeft, MapPin, Calendar, Hash, Ruler, Plus } from "lucide-react"
import type { components } from "@/lib/api/types"
import { fetchClient } from "@/lib/api/fetch-client"
import { formatCurrency, formatDate } from "@/lib/format"
import { TrasterosTable } from "@/components/trasteros-table"
import { TrasteroFormModal } from "@/components/trastero-form-modal"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"

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
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [trasteroModalOpen, setTrasteroModalOpen] = useState(false)

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
    ])
      .then(([localData, trasterosData]) => {
        setLocal(localData)
        setTrasteros(trasterosData.data ?? [])
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
        <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-2">
          <div className="flex flex-col gap-4 md:gap-6">
            <div className="bg-muted h-28 animate-pulse rounded-lg" />
            <div className="bg-muted h-28 animate-pulse rounded-lg" />
          </div>
          <div className="bg-muted h-56 animate-pulse rounded-lg" />
        </div>
      </div>
    )
  }

  if (error || !local) {
    return (
      <div className="flex flex-col items-center gap-4 px-4 py-12 lg:px-6">
        <p className="text-muted-foreground">{error ?? "Local no encontrado"}</p>
        <Button variant="outline" onClick={() => router.push("/locales")}>
          <ArrowLeft className="size-4" />
          Volver a locales
        </Button>
      </div>
    )
  }

  const dir = local.direccion

  return (
    <div className="flex flex-col gap-4 px-4 py-4 md:gap-6 md:py-6 lg:px-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon-sm" onClick={() => router.push("/locales")}>
          <ArrowLeft className="size-4" />
          <span className="sr-only">Volver</span>
        </Button>
        <h2 className="text-xl font-semibold">{local.nombre}</h2>
      </div>

      <div className="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-2">
        {/* Left column: Info + Dirección in one card */}
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

        {/* Right column: Trasteros */}
        <Card>
          <CardHeader className="flex-row items-center justify-between">
            <CardTitle className="text-lg">
              Trasteros
              {trasteros.length > 0 && (
                <Badge variant="outline" className="ml-2 text-xs font-normal">
                  {trasteros.length}
                </Badge>
              )}
            </CardTitle>
            <Button size="sm" onClick={() => setTrasteroModalOpen(true)}>
              <Plus className="size-4" />
              Crear trastero
            </Button>
          </CardHeader>
          <div className="px-6 pb-6">
            <TrasterosTable trasteros={trasteros} />
          </div>
          <TrasteroFormModal
            open={trasteroModalOpen}
            onOpenChange={setTrasteroModalOpen}
            defaultLocalId={local.id}
            defaultLocalNombre={local.nombre}
            onSuccess={fetchData}
          />
        </Card>
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
