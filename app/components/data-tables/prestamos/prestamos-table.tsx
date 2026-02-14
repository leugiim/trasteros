// components/data-tables/prestamos/prestamos-table.tsx
"use client"

import { useState } from "react"
import {
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
  type ColumnDef,
  type SortingState,
} from "@tanstack/react-table"
import { ArrowUpDown, Search } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { formatCurrency, formatDate } from "@/lib/format"

export interface Prestamo {
  id: number
  localId: number
  capitalSolicitado: number
  totalADevolver: number
  fechaConcesion: string
  entidadBancaria: string
  numeroPrestamo?: string | null
  tipoInteres: number
  estado: string
  amortizado?: number
}

interface PrestamosTableProps {
  prestamos: Prestamo[]
  title?: string
  action?: React.ReactNode
  showSearch?: boolean
}

const estadoVariant: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
  activo: "default",
  pagado: "secondary",
  cancelado: "destructive",
}

const estadoLabel: Record<string, string> = {
  activo: "Activo",
  pagado: "Pagado",
  cancelado: "Cancelado",
}

const columns: ColumnDef<Prestamo>[] = [
  {
    accessorKey: "entidadBancaria",
    header: ({ column }) => (
      <Button
        variant="ghost"
        size="sm"
        className="-ml-3"
        onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
      >
        Entidad
        <ArrowUpDown className="ml-1 size-3.5" />
      </Button>
    ),
    cell: ({ row }) => (
      <span className="font-medium">{row.original.entidadBancaria}</span>
    ),
  },
  {
    accessorKey: "numeroPrestamo",
    header: "Nº Préstamo",
    cell: ({ row }) => (
      <span className="text-muted-foreground">
        {row.original.numeroPrestamo ?? "-"}
      </span>
    ),
  },
  {
    accessorKey: "capitalSolicitado",
    header: ({ column }) => (
      <Button
        variant="ghost"
        size="sm"
        className="-ml-3"
        onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
      >
        Capital
        <ArrowUpDown className="ml-1 size-3.5" />
      </Button>
    ),
    cell: ({ row }) => (
      <span className="tabular-nums">{formatCurrency(row.original.capitalSolicitado)}</span>
    ),
  },
  {
    accessorKey: "totalADevolver",
    header: "Total a devolver",
    cell: ({ row }) => (
      <span className="tabular-nums">{formatCurrency(row.original.totalADevolver)}</span>
    ),
  },
  {
    accessorKey: "tipoInteres",
    header: "Interés",
    cell: ({ row }) => (
      <span className="tabular-nums">{row.original.tipoInteres}%</span>
    ),
  },
  {
    accessorKey: "amortizado",
    header: "Amortizado",
    cell: ({ row }) => {
      const amortizado = row.original.amortizado ?? 0
      const total = row.original.totalADevolver
      const pct = total > 0 ? (amortizado / total) * 100 : 0
      return (
        <span className="tabular-nums">
          {formatCurrency(amortizado)}{" "}
          <span className="text-muted-foreground text-xs">({pct.toFixed(1)}%)</span>
        </span>
      )
    },
  },
  {
    accessorKey: "fechaConcesion",
    header: "Concesión",
    cell: ({ row }) => formatDate(row.original.fechaConcesion),
  },
  {
    accessorKey: "estado",
    header: "Estado",
    cell: ({ row }) => {
      const estado = row.original.estado ?? ""
      return (
        <Badge variant={estadoVariant[estado] ?? "outline"}>
          {estadoLabel[estado] ?? estado}
        </Badge>
      )
    },
  },
]

export function PrestamosTable({ prestamos, title, action, showSearch = true }: PrestamosTableProps) {
  const [sorting, setSorting] = useState<SortingState>([])
  const [globalFilter, setGlobalFilter] = useState("")

  const table = useReactTable({
    data: prestamos,
    columns,
    state: { sorting, globalFilter },
    onSortingChange: setSorting,
    onGlobalFilterChange: setGlobalFilter,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    initialState: { pagination: { pageSize: 10 } },
  })

  return (
    <Card className="p-6">
      <div className="flex flex-col gap-4">
        {(title || showSearch || action) && (
          <div className="flex flex-col gap-4">
            {title && (
              <div className="flex items-center justify-between">
                <h3 className="text-lg font-semibold">{title}</h3>
                {action}
              </div>
            )}
            {(showSearch || (!title && action)) && (
              <div className="flex items-center justify-between">
                {showSearch ? (
                  <div className="flex items-center gap-2">
                    <div className="relative">
                      <Search className="text-muted-foreground absolute left-2.5 top-2.5 size-4" />
                      <Input
                        placeholder="Buscar préstamos..."
                        value={globalFilter}
                        onChange={(e) => setGlobalFilter(e.target.value)}
                        className="w-64 pl-9"
                      />
                    </div>
                    <span className="text-muted-foreground text-sm">
                      {prestamos.length} préstamos
                    </span>
                  </div>
                ) : <div />}
                {!title && action}
              </div>
            )}
          </div>
        )}

        <div className="overflow-hidden rounded-lg border">
          <Table>
            <TableHeader className="bg-muted">
              {table.getHeaderGroups().map((headerGroup) => (
                <TableRow key={headerGroup.id}>
                  {headerGroup.headers.map((header) => (
                    <TableHead key={header.id}>
                      {header.isPlaceholder
                        ? null
                        : flexRender(
                            header.column.columnDef.header,
                            header.getContext()
                          )}
                    </TableHead>
                  ))}
                </TableRow>
              ))}
            </TableHeader>
            <TableBody>
              {table.getRowModel().rows.length ? (
                table.getRowModel().rows.map((row) => (
                  <TableRow key={row.id}>
                    {row.getVisibleCells().map((cell) => (
                      <TableCell key={cell.id}>
                        {flexRender(
                          cell.column.columnDef.cell,
                          cell.getContext()
                        )}
                      </TableCell>
                    ))}
                  </TableRow>
                ))
              ) : (
                <TableRow>
                  <TableCell
                    colSpan={columns.length}
                    className="h-24 text-center"
                  >
                    No se encontraron préstamos.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </div>

        {table.getPageCount() > 1 && (
          <div className="flex items-center justify-between">
            <span className="text-muted-foreground text-sm">
              Página {table.getState().pagination.pageIndex + 1} de{" "}
              {table.getPageCount()}
            </span>
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                onClick={() => table.previousPage()}
                disabled={!table.getCanPreviousPage()}
              >
                Anterior
              </Button>
              <Button
                variant="outline"
                size="sm"
                onClick={() => table.nextPage()}
                disabled={!table.getCanNextPage()}
              >
                Siguiente
              </Button>
            </div>
          </div>
        )}
      </div>
    </Card>
  )
}
