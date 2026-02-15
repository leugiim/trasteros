// components/data-tables/gastos/gastos-table.tsx
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
import { ArrowUpDown, Pencil, Search } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card } from "@/components/ui/card"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { formatCurrency, formatDate } from "@/lib/format"

export interface Gasto {
  id: number
  localId: number
  concepto: string
  importe: number
  fecha: string
  categoria: string
  descripcion?: string | null
  metodoPago?: string | null
}

interface GastosTableProps {
  gastos: Gasto[]
  title?: string
  action?: React.ReactNode
  showSearch?: boolean
  onEdit?: (gasto: Gasto) => void
}

const categoriaLabel: Record<string, string> = {
  suministros: "Suministros",
  mantenimiento: "Mantenimiento",
  seguros: "Seguros",
  impuestos: "Impuestos",
  comunidad: "Comunidad",
  otros: "Otros",
}

const metodoPagoLabel: Record<string, string> = {
  efectivo: "Efectivo",
  transferencia: "Transferencia",
  tarjeta: "Tarjeta",
  bizum: "Bizum",
}

function getColumns(onEdit?: (gasto: Gasto) => void): ColumnDef<Gasto>[] {
  return [
    {
      accessorKey: "fecha",
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
      cell: ({ row }) => formatDate(row.original.fecha),
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
    ...(onEdit
      ? [
          {
            id: "actions",
            header: "",
            cell: ({ row }: { row: { original: Gasto } }) => (
              <Button
                variant="ghost"
                size="icon-sm"
                onClick={() => onEdit(row.original)}
              >
                <Pencil className="size-3.5" />
              </Button>
            ),
          } satisfies ColumnDef<Gasto>,
        ]
      : []),
  ]
}

export function GastosTable({ gastos, title, action, showSearch = true, onEdit }: GastosTableProps) {
  const [sorting, setSorting] = useState<SortingState>([])
  const [globalFilter, setGlobalFilter] = useState("")
  const [columns] = useState(() => getColumns(onEdit))

  const table = useReactTable({
    data: gastos,
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
                      placeholder="Buscar gastos..."
                      value={globalFilter}
                      onChange={(e) => setGlobalFilter(e.target.value)}
                      className="w-64 pl-9"
                    />
                  </div>
                  <span className="text-muted-foreground text-sm">
                    {gastos.length} gastos
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
                  No se encontraron gastos.
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
