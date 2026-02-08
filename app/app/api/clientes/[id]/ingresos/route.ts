import { NextResponse } from "next/server"
import { API_URL } from "@/lib/auth/session"
import { authFetch } from "@/lib/auth/fetch"

export async function GET(
  _request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params

  // First get the client's contracts
  const contratosRes = await authFetch(`${API_URL}/api/contratos/cliente/${id}`)
  if (!contratosRes.ok) {
    return NextResponse.json({ data: [] }, { status: contratosRes.status })
  }

  const contratosData = await contratosRes.json()
  const contratos: { id: number }[] = contratosData.data ?? []

  if (contratos.length === 0) {
    return NextResponse.json({ data: [], meta: { total: 0 } })
  }

  // Fetch ingresos for each contract in parallel
  const results = await Promise.all(
    contratos.map((c) =>
      authFetch(`${API_URL}/api/ingresos/contrato/${c.id}`)
        .then((res) => (res.ok ? res.json() : { data: [] }))
        .then((json) => json.data ?? [])
    )
  )

  const allIngresos = results.flat()
  // Sort by fechaPago descending
  allIngresos.sort((a: { fechaPago: string }, b: { fechaPago: string }) =>
    b.fechaPago.localeCompare(a.fechaPago)
  )

  return NextResponse.json({
    data: allIngresos,
    meta: { total: allIngresos.length },
  })
}
