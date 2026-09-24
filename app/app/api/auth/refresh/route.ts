import { getIronSession } from "iron-session"
import { cookies } from "next/headers"
import { NextResponse } from "next/server"
import { refreshTokens } from "@/lib/auth/refresh"
import { sessionOptions, type SessionData } from "@/lib/auth/session"

export async function POST() {
  const session = await getIronSession<SessionData>(await cookies(), sessionOptions)

  if (!session.refreshToken) {
    session.destroy()
    return NextResponse.json(
      { error: { message: "No autenticado", code: "NOT_AUTHENTICATED" } },
      { status: 401 }
    )
  }

  const data = await refreshTokens(session.refreshToken)

  if (!data) {
    session.destroy()
    await session.save()
    return NextResponse.json(
      { error: { message: "Sesion expirada", code: "SESSION_EXPIRED" } },
      { status: 401 }
    )
  }

  session.token = data.token
  session.refreshToken = data.refreshToken
  session.user = data.user
  await session.save()

  return NextResponse.json({ user: data.user })
}
