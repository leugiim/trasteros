// components/ingresos-table.tsx
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

export interface Ingreso {
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
  action?: React.ReactNode
  showSearch?: boolean
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

function getColumns(contratoTrasteroMap: Map<number | undefined, string>): ColumnDef<Ingreso>[] {
  return [
    {
      accessorKey: "fechaPago",
      header: ({ column }) => (
        <Button
          variant="ghost"
          size="sm"
          className="-ml-3"
          onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
        >
          Fecha
          <ArrowUpDown className="ml-1 size-3.5" />
        </Button>
      ),
      cell: ({ row }) => formatDate(row.original.fechaPago),
    },
    {
      accessorKey: "concepto",
      header: "Concepto",
      cell: ({ row }) => (
        <span className="max-w-37.5 truncate">{row.original.concepto}</span>
      ),
    },
    {
      accessorKey: "importe",
      header: ({ column }) => (
        <Button
          variant="ghost"
          size="sm"
          className="-ml-3"
          onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
        >
          Importe
          <ArrowUpDown className="ml-1 size-3.5" />
        </Button>
      ),
      cell: ({ row }) => (
        <span className="tabular-nums font-medium">{formatCurrency(row.original.importe)}</span>
      ),
    },
    {
      accessorKey: "categoria",
      header: "Categoría",
      cell: ({ row }) => (
        <Badge variant="outline" className="text-[10px]">
          {categoriaLabel[row.original.categoria] ?? row.original.categoria}
        </Badge>
      ),
    },
    {
      accessorKey: "metodoPago",
      header: "Método",
      cell: ({ row }) => (
        <span className="text-muted-foreground text-xs">
          {row.original.metodoPago ? (metodoPagoLabel[row.original.metodoPago] ?? row.original.metodoPago) : "-"}
        </span>
      ),
    },
    {
      id: "trastero",
      header: "Trastero",
      cell: ({ row }) => (
        <span className="text-muted-foreground text-xs">
          {contratoTrasteroMap.get(row.original.contratoId) ?? "-"}
        </span>
      ),
    },
  ]
}

export function IngresosTable({ ingresos, contratoTrasteroMap, action, showSearch = true }: IngresosTableProps) {
  const [sorting, setSorting] = useState<SortingState>([])
  const [globalFilter, setGlobalFilter] = useState("")
  const [columns] = useState(() => getColumns(contratoTrasteroMap))

  const table = useReactTable({
    data: ingresos,
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
    <div className="flex flex-col gap-4">
      {(showSearch || action) && (
        <div className="flex items-center justify-between">
          {showSearch ? (
            <div className="flex items-center gap-2">
              <div className="relative">
                <Search className="text-muted-foreground absolute left-2.5 top-2.5 size-4" />
                <Input
                  placeholder="Buscar ingresos..."
                  value={globalFilter}
                  onChange={(e) => setGlobalFilter(e.target.value)}
                  className="w-64 pl-9"
                />
              </div>
              <span className="text-muted-foreground text-sm">
                {ingresos.length} ingresos
              </span>
            </div>
          ) : <div />}
          {action}
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
                  No se encontraron ingresos.
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
  )
}
