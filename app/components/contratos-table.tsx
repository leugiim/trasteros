// components/contratos-table.tsx
"use client"

import { Pencil } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { formatCurrency, formatDate } from "@/lib/format"

interface ContratoWithRelations {
  id?: number
  trastero?: { id: number; numero: string; local?: { id: number; nombre: string } }
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

interface ContratosTableProps {
  contratos: ContratoWithRelations[]
  onEdit: (contrato: ContratoWithRelations) => void
}

const estadoVariant: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
  activo: "default",
  pendiente: "outline",
  finalizado: "secondary",
  cancelado: "destructive",
}

export function ContratosTable({ contratos, onEdit }: ContratosTableProps) {
  if (contratos.length === 0) {
    return (
      <p className="text-muted-foreground py-4 text-center text-sm">
        Este cliente no tiene contratos.
      </p>
    )
  }

  return (
    <div className="overflow-hidden rounded-lg border">
      <Table>
        <TableHeader className="bg-muted">
          <TableRow>
            <TableHead>Trastero</TableHead>
            <TableHead>Inicio</TableHead>
            <TableHead>Fin</TableHead>
            <TableHead>Precio/mes</TableHead>
            <TableHead>Fianza</TableHead>
            <TableHead>Estado</TableHead>
            <TableHead className="w-10" />
          </TableRow>
        </TableHeader>
        <TableBody>
          {contratos.map((c) => (
            <TableRow key={c.id}>
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
              <TableCell>
                <Button
                  variant="ghost"
                  size="icon-sm"
                  onClick={() => onEdit(c)}
                >
                  <Pencil className="size-3.5" />
                  <span className="sr-only">Editar</span>
                </Button>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
