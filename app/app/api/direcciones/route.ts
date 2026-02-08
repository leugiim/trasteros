import { NextResponse } from "next/server"
import { API_URL } from "@/lib/auth/session"
import { authFetch } from "@/lib/auth/fetch"

export async function POST(request: Request) {
  const body = await request.json()

  const res = await authFetch(`${API_URL}/api/direcciones`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
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

  return NextResponse.json(data, { status: res.status })
}
