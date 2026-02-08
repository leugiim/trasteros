// components/ingresos-table.tsx
"use client"

import { Badge } from "@/components/ui/badge"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { formatCurrency, formatDate } from "@/lib/format"

interface Ingreso {
  id: number
  contratoId: number
  concepto: string
  importe: number
  fechaPago: string
  metodoPago?: string | null
  categoria: string
}

interface IngresosTableProps {
  ingresos: Ingreso[]
  contratoTrasteroMap: Map<number | undefined, string>
}

const categoriaLabel: Record<string, string> = {
  mensualidad: "Mensualidad",
  fianza: "Fianza",
  penalizacion: "Penalización",
  otros: "Otros",
}

const metodoPagoLabel: Record<string, string> = {
  efectivo: "Efectivo",
  transferencia: "Transferencia",
  tarjeta: "Tarjeta",
  bizum: "Bizum",
}

export function IngresosTable({ ingresos, contratoTrasteroMap }: IngresosTableProps) {
  if (ingresos.length === 0) {
    return (
      <p className="text-muted-foreground py-4 text-center text-sm">
        Este cliente no tiene ingresos registrados.
      </p>
    )
  }

  return (
    <div className="overflow-hidden rounded-lg border">
      <Table>
        <TableHeader className="bg-muted">
          <TableRow>
            <TableHead>Fecha</TableHead>
            <TableHead>Concepto</TableHead>
            <TableHead>Importe</TableHead>
            <TableHead>Categoría</TableHead>
            <TableHead>Método</TableHead>
            <TableHead>Trastero</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {ingresos.map((ing) => (
            <TableRow key={ing.id}>
              <TableCell>{formatDate(ing.fechaPago)}</TableCell>
              <TableCell className="max-w-37.5 truncate">{ing.concepto}</TableCell>
              <TableCell className="tabular-nums font-medium">{formatCurrency(ing.importe)}</TableCell>
              <TableCell>
                <Badge variant="outline" className="text-[10px]">
                  {categoriaLabel[ing.categoria] ?? ing.categoria}
                </Badge>
              </TableCell>
              <TableCell className="text-muted-foreground text-xs">
                {ing.metodoPago ? (metodoPagoLabel[ing.metodoPago] ?? ing.metodoPago) : "-"}
              </TableCell>
              <TableCell className="text-muted-foreground text-xs">
                {contratoTrasteroMap.get(ing.contratoId) ?? "-"}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
