import { getIronSession } from "iron-session"
import { cookies } from "next/headers"
import { NextResponse } from "next/server"
import { API_URL, sessionOptions, type SessionData } from "@/lib/auth/session"

export async function POST(request: Request) {
  const body = await request.json()

  const res = await fetch(`${API_URL}/api/auth/login`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  })

  const data = await res.json()

  if (!res.ok) {
    return NextResponse.json(data, { status: res.status })
  }

  const session = await getIronSession<SessionData>(await cookies(), sessionOptions)
  session.token = data.token
  session.user = data.user
  await session.save()

  return NextResponse.json({ user: data.user })
}
