import { NextResponse } from "next/server"
import { API_URL } from "@/lib/auth/session"
import { authFetch } from "@/lib/auth/fetch"

export async function GET() {
  const res = await authFetch(`${API_URL}/api/dashboard/stats`)
  const data = await res.json()

  return NextResponse.json(data, { status: res.status })
}
