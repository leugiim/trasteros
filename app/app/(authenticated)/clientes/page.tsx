"use client"

import { useState } from "react"
import { Plus } from "lucide-react"
import { useClientes } from "@/hooks/use-clientes"
import { ClienteFormModal } from "@/components/cliente-form-modal"
import { ClientesTable } from "@/components/clientes-table"
import { Button } from "@/components/ui/button"

export default function ClientesPage() {
  const { clientes, loading, error, refetch } = useClientes()
  const [modalOpen, setModalOpen] = useState(false)

  return (
    <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6">
      <div className="flex items-center justify-end px-4 lg:px-6">
        <Button onClick={() => setModalOpen(true)}>
          <Plus className="size-4" />
          Crear cliente
        </Button>
      </div>

      <ClienteFormModal
        open={modalOpen}
        onOpenChange={setModalOpen}
        onSuccess={refetch}
      />

      <div className="px-4 lg:px-6">
        {error && (
          <div className="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-600 dark:border-red-800 dark:bg-red-950 dark:text-red-400">
            {error}
          </div>
        )}

        {loading ? (
          <ClientesTable.Skeleton />
        ) : (
          <ClientesTable clientes={clientes} />
        )}
      </div>
    </div>
  )
}
