import { getIronSession } from "iron-session"
import { cookies } from "next/headers"
import { NextRequest, NextResponse } from "next/server"
import { API_URL, sessionOptions, type SessionData } from "@/lib/auth/session"

export async function GET(request: NextRequest) {
  const session = await getIronSession<SessionData>(await cookies(), sessionOptions)

  if (!session.token) {
    return NextResponse.json(
      { error: { message: "No autenticado", code: "NOT_AUTHENTICATED" } },
      { status: 401 }
    )
  }

  const period = request.nextUrl.searchParams.get("period") ?? "1m"

  const res = await fetch(`${API_URL}/api/dashboard/chart?period=${period}`, {
    headers: { Authorization: `Bearer ${session.token}` },
  })

  const data = await res.json()

  if (!res.ok) {
    return NextResponse.json(data, { status: res.status })
  }

  return NextResponse.json(data)
}
