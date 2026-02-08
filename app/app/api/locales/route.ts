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
