import { getIronSession } from "iron-session"
import { cookies } from "next/headers"
import { NextResponse } from "next/server"
import { API_URL, sessionOptions, type SessionData } from "@/lib/auth/session"

export async function POST() {
  const session = await getIronSession<SessionData>(await cookies(), sessionOptions)

  if (!session.refreshToken) {
    session.destroy()
    return NextResponse.json(
      { error: { message: "No autenticado", code: "NOT_AUTHENTICATED" } },
      { status: 401 }
    )
  }

  const res = await fetch(`${API_URL}/api/auth/refresh`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ refreshToken: session.refreshToken }),
  })

  if (!res.ok) {
    session.destroy()
    await session.save()
    return NextResponse.json(
      { error: { message: "Sesion expirada", code: "SESSION_EXPIRED" } },
      { status: 401 }
    )
  }

  const data = await res.json()
  session.token = data.token
  session.refreshToken = data.refreshToken
  session.user = data.user
  await session.save()

  return NextResponse.json({ user: data.user })
}
