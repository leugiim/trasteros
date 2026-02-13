import { getIronSession } from "iron-session"
import { cookies } from "next/headers"
import { redirect } from "next/navigation"
import { AppSidebar } from "@/components/app-sidebar"
import { SiteHeader } from "@/components/site-header"
import {
  SidebarInset,
  SidebarProvider,
} from "@/components/ui/sidebar"
import { sessionOptions, type SessionData } from "@/lib/auth/session"
import { PageHeaderProvider } from "@/lib/page-header-context"

export default async function AuthenticatedLayout({
  children,
}: {
  children: React.ReactNode
}) {
  const session = await getIronSession<SessionData>(await cookies(), sessionOptions)

  if (!session.token) {
    redirect("/")
  }

  return (
    <SidebarProvider
        style={
          {
            "--sidebar-width": "calc(var(--spacing) * 72)",
            "--header-height": "calc(var(--spacing) * 12)",
          } as React.CSSProperties
        }
      >
        <PageHeaderProvider>
          <AppSidebar variant="inset" />
          <SidebarInset>
            <SiteHeader />
            <div className="flex flex-1 flex-col">
              <div className="@container/main flex flex-1 flex-col gap-2">
                {children}
              </div>
            </div>
          </SidebarInset>
        </PageHeaderProvider>
      </SidebarProvider>
  )
}
