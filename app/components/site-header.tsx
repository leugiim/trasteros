"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import { ArrowLeft } from "lucide-react"
import { usePageHeader } from "@/lib/page-header-context"
import { Button } from "@/components/ui/button"
import { Separator } from "@/components/ui/separator"
import { SidebarTrigger } from "@/components/ui/sidebar"

const titles: Record<string, string> = {
  "/dashboard": "Dashboard",
  "/clientes": "Clientes",
  "/locales": "Locales",
  "/trasteros": "Trasteros",
  "/finanzas": "Finanzas",
}

const detailBackHref: Record<string, string> = {
  "/clientes/": "/clientes",
  "/locales/": "/locales",
}

export function SiteHeader() {
  const pathname = usePathname()
  const { headerContent } = usePageHeader()

  const detailPrefix = Object.keys(detailBackHref).find((p) => pathname.startsWith(p))
  const backHref = detailPrefix ? detailBackHref[detailPrefix] : null

  const title = titles[pathname] ?? ""

  return (
    <header className="flex h-(--header-height) shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-(--header-height)">
      <div className="flex w-full items-center gap-1 px-4 lg:gap-2 lg:px-6">
        <SidebarTrigger className="-ml-1" />
        <Separator
          orientation="vertical"
          className="mx-2 data-[orientation=vertical]:h-4"
        />
        {backHref ? (
          <div className="flex items-center gap-2">
            <Button variant="ghost" size="icon-sm" asChild>
              <Link href={backHref}>
                <ArrowLeft className="size-4" />
                <span className="sr-only">Volver</span>
              </Link>
            </Button>
            {headerContent ?? <h1 className="text-base font-medium">Cargando...</h1>}
          </div>
        ) : (
          <h1 className="text-base font-medium">{title}</h1>
        )}
      </div>
    </header>
  )
}
