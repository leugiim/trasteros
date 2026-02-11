"use client"

import { useState } from "react"
import { Plus } from "lucide-react"
import { useLocales } from "@/hooks/use-locales"
import { LocalFormModal } from "@/components/local-form-modal"
import { LocalesTable } from "@/components/locales-table"
import { Button } from "@/components/ui/button"

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

export default function LocalesPage() {
  const { locales, loading, error, refetch } = useLocales()
  const [modalOpen, setModalOpen] = useState(false)

  return (
    <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6">
      <LocalFormModal
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
          <TableSkeleton />
        ) : (
          <LocalesTable
            locales={locales}
            action={
              <Button size="sm" onClick={() => setModalOpen(true)}>
                <Plus className="size-4" />
                Crear local
              </Button>
            }
          />
        )}
      </div>
    </div>
  )
}
