import { NextResponse } from "next/server"
import { API_URL } from "@/lib/auth/session"
import { authFetch } from "@/lib/auth/fetch"

export async function GET(
  _request: Request,
  { params }: { params: Promise<{ trasteroId: string }> }
) {
  const { trasteroId } = await params

  const res = await authFetch(`${API_URL}/api/ingresos/trastero/${trasteroId}`)
  const data = await res.json()

  return NextResponse.json(data, { status: res.status })
}
