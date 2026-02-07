import { getIronSession } from "iron-session"
import { cookies } from "next/headers"
import { NextResponse } from "next/server"
import { sessionOptions, type SessionData } from "@/lib/auth/session"

export async function GET() {
  const session = await getIronSession<SessionData>(await cookies(), sessionOptions)

  if (!session.user) {
    return NextResponse.json(
      { error: { message: "No autenticado", code: "NOT_AUTHENTICATED" } },
      { status: 401 }
    )
  }

  return NextResponse.json(session.user)
}
