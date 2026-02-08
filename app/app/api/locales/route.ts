import { NextResponse } from "next/server"
import { API_URL } from "@/lib/auth/session"
import { authFetch } from "@/lib/auth/fetch"

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url)
  const query = searchParams.toString()
  const url = `${API_URL}/api/locales${query ? `?${query}` : ""}`

  const res = await authFetch(url)
  const data = await res.json()

  return NextResponse.json(data, { status: res.status })
}

export async function POST(request: Request) {
  const body = await request.json()

  const res = await authFetch(`${API_URL}/api/locales`, {
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
