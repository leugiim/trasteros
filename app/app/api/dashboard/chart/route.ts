import { NextRequest, NextResponse } from "next/server"
import { API_URL } from "@/lib/auth/session"
import { authFetch } from "@/lib/auth/fetch"

export async function GET(request: NextRequest) {
  const period = request.nextUrl.searchParams.get("period") ?? "1m"

  const res = await authFetch(`${API_URL}/api/dashboard/chart?period=${period}`)
  const data = await res.json()

  return NextResponse.json(data, { status: res.status })
}
