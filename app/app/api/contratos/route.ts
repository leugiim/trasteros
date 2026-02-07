import { getIronSession } from "iron-session"
import { cookies } from "next/headers"
import { NextResponse } from "next/server"
import { API_URL, sessionOptions, type SessionData } from "@/lib/auth/session"

export async function POST(request: Request) {
  const session = await getIronSession<SessionData>(await cookies(), sessionOptions)

  if (!session.token) {
    return NextResponse.json(
      { error: { message: "No autenticado", code: "NOT_AUTHENTICATED" } },
      { status: 401 }
    )
  }

  const body = await request.json()

  const res = await fetch(`${API_URL}/api/contratos`, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${session.token}`,
      "Content-Type": "application/json",
    },
    body: JSON.stringify(body),
  })

  const text = await res.text()
  let data: unknown
  try {
    data = JSON.parse(text)
  } catch {
    return NextResponse.json(
      { error: { message: `Error del servidor (${res.status})`, code: "SERVER_ERROR" } },
      { status: 502 }
    )
  }

  if (!res.ok) {
    return NextResponse.json(data, { status: res.status })
  }

  return NextResponse.json(data, { status: 201 })
}
