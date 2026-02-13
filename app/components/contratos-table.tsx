// components/contratos-table.tsx
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
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { formatCurrency, formatDate } from "@/lib/format"

export interface ContratoWithRelations {
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
  onEdit?: (contrato: ContratoWithRelations) => void
  action?: React.ReactNode
  showSearch?: boolean
}

const estadoVariant: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
  activo: "default",
  pendiente: "outline",
  finalizado: "secondary",
  cancelado: "destructive",
}

function getColumns(onEdit?: (contrato: ContratoWithRelations) => void): ColumnDef<ContratoWithRelations>[] {
  const cols: ColumnDef<ContratoWithRelations>[] = [
    {
      accessorKey: "trastero.numero",
      header: ({ column }) => (
        <Button
          variant="ghost"
          size="sm"
          className="-ml-3"
          onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
        >
          Trastero
          <ArrowUpDown className="ml-1 size-3.5" />
        </Button>
      ),
      cell: ({ row }) => (
        <span className="font-medium">
          {row.original.trastero?.numero ?? "-"}
        </span>
      ),
    },
    {
      accessorKey: "fechaInicio",
      header: "Inicio",
      cell: ({ row }) => formatDate(row.original.fechaInicio),
    },
    {
      accessorKey: "fechaFin",
      header: "Fin",
      cell: ({ row }) => formatDate(row.original.fechaFin),
    },
    {
      accessorKey: "precioMensual",
      header: ({ column }) => (
        <Button
          variant="ghost"
          size="sm"
          className="-ml-3"
          onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
        >
          Precio/mes
          <ArrowUpDown className="ml-1 size-3.5" />
        </Button>
      ),
      cell: ({ row }) => (
        <span className="tabular-nums">{formatCurrency(row.original.precioMensual)}</span>
      ),
    },
    {
      accessorKey: "fianza",
      header: "Fianza",
      cell: ({ row }) => (
        <>
          <span className="tabular-nums">{formatCurrency(row.original.fianza)}</span>
          {row.original.fianzaPagada === false && (
            <Badge variant="destructive" className="ml-1.5 text-[10px]">Pendiente</Badge>
          )}
        </>
      ),
    },
    {
      accessorKey: "estado",
      header: "Estado",
      cell: ({ row }) => {
        const estado = row.original.estado ?? ""
        return (
          <Badge variant={estadoVariant[estado] ?? "outline"}>
            {estado || "-"}
          </Badge>
        )
      },
    },
  ]

  if (onEdit) {
    cols.push({
      id: "actions",
      header: "",
      cell: ({ row }) => (
        <Button
          variant="ghost"
          size="icon-sm"
          onClick={() => onEdit(row.original)}
        >
          <Pencil className="size-3.5" />
          <span className="sr-only">Editar</span>
        </Button>
      ),
    })
  }

  return cols
}

export function ContratosTable({ contratos, onEdit, action, showSearch = true }: ContratosTableProps) {
  const [sorting, setSorting] = useState<SortingState>([])
  const [globalFilter, setGlobalFilter] = useState("")
  const [columns] = useState(() => getColumns(onEdit))

  const table = useReactTable({
    data: contratos,
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
                  placeholder="Buscar contratos..."
                  value={globalFilter}
                  onChange={(e) => setGlobalFilter(e.target.value)}
                  className="w-64 pl-9"
                />
              </div>
              <span className="text-muted-foreground text-sm">
                {contratos.length} contratos
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
                  No se encontraron contratos.
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
