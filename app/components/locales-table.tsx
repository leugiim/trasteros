// components/locales-table.tsx
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
import type { components } from "@/lib/api/types"
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

type Local = components["schemas"]["Local"]

interface LocalesTableProps {
  locales: Local[]
}

function formatCurrency(amount: number | null | undefined) {
  if (amount == null) return "-"
  return new Intl.NumberFormat("es-ES", {
    style: "currency",
    currency: "EUR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

const columns: ColumnDef<Local>[] = [
  {
    accessorKey: "id",
    header: "ID",
    cell: ({ row }) => (
      <span className="text-muted-foreground">{row.original.id}</span>
    ),
  },
  {
    accessorKey: "nombre",
    header: ({ column }) => (
      <Button
        variant="ghost"
        size="sm"
        className="-ml-3"
        onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
      >
        Nombre
        <ArrowUpDown className="ml-1 size-3.5" />
      </Button>
    ),
    cell: ({ row }) => (
      <span className="font-medium">{row.original.nombre}</span>
    ),
  },
  {
    accessorKey: "superficieTotal",
    header: ({ column }) => (
      <Button
        variant="ghost"
        size="sm"
        className="-ml-3"
        onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
      >
        Superficie
        <ArrowUpDown className="ml-1 size-3.5" />
      </Button>
    ),
    cell: ({ row }) => <span>{row.original.superficieTotal} m²</span>,
  },
  {
    accessorKey: "numeroTrasteros",
    header: "Trasteros",
    cell: ({ row }) => (
      <span className="tabular-nums">{row.original.numeroTrasteros}</span>
    ),
  },
  {
    accessorKey: "fechaCompra",
    header: "Fecha compra",
    cell: ({ row }) => {
      const fecha = row.original.fechaCompra
      if (!fecha) return <span className="text-muted-foreground">-</span>
      return new Date(fecha).toLocaleDateString("es-ES")
    },
  },
  {
    accessorKey: "precioCompra",
    header: "Precio compra",
    cell: ({ row }) => (
      <span className="tabular-nums">
        {formatCurrency(row.original.precioCompra)}
      </span>
    ),
  },
  {
    accessorKey: "referenciaCatastral",
    header: "Ref. catastral",
    cell: ({ row }) => (
      <span className="text-muted-foreground">
        {row.original.referenciaCatastral ?? "-"}
      </span>
    ),
  },
]

function TableSkeleton() {
  return (
    <div className="overflow-hidden rounded-lg border">
      <div className="bg-muted h-10" />
      {Array.from({ length: 5 }).map((_, i) => (
        <div key={i} className="flex items-center gap-4 border-t px-4 py-3">
          <div className="bg-muted h-4 w-8 animate-pulse rounded" />
          <div className="bg-muted h-4 w-36 animate-pulse rounded" />
          <div className="bg-muted h-4 w-20 animate-pulse rounded" />
          <div className="bg-muted h-4 w-16 animate-pulse rounded" />
          <div className="bg-muted h-4 w-24 animate-pulse rounded" />
          <div className="bg-muted h-4 w-24 animate-pulse rounded" />
          <div className="bg-muted h-4 w-32 animate-pulse rounded" />
        </div>
      ))}
    </div>
  )
}

export function LocalesTable({ locales }: LocalesTableProps) {
  const [sorting, setSorting] = useState<SortingState>([])
  const [globalFilter, setGlobalFilter] = useState("")

  const table = useReactTable({
    data: locales,
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
      <div className="flex items-center gap-2">
        <div className="relative">
          <Search className="text-muted-foreground absolute left-2.5 top-2.5 size-4" />
          <Input
            placeholder="Buscar locales..."
            value={globalFilter}
            onChange={(e) => setGlobalFilter(e.target.value)}
            className="w-64 pl-9"
          />
        </div>
        <span className="text-muted-foreground text-sm">
          {locales.length} locales
        </span>
      </div>

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
                  No se encontraron locales.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>

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
    </div>
  )
}
