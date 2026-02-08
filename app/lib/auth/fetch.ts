import { getIronSession } from "iron-session"
import { cookies } from "next/headers"
import { API_URL, sessionOptions, type SessionData } from "@/lib/auth/session"

let refreshPromise: Promise<boolean> | null = null

async function tryRefresh(session: SessionData & { save: () => Promise<void>, destroy: () => void }): Promise<boolean> {
  if (!session.refreshToken) {
    return false
  }

  try {
    const res = await fetch(`${API_URL}/api/auth/refresh`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ refreshToken: session.refreshToken }),
    })

    if (!res.ok) {
      session.destroy()
      return false
    }

    const data = await res.json()
    session.token = data.token
    session.refreshToken = data.refreshToken
    session.user = data.user
    await session.save()
    return true
  } catch {
    session.destroy()
    return false
  }
}

export async function authFetch(url: string, options: RequestInit = {}): Promise<Response> {
  const session = await getIronSession<SessionData>(await cookies(), sessionOptions)

  if (!session.token) {
    return new Response(
      JSON.stringify({ error: { message: "No autenticado", code: "NOT_AUTHENTICATED" } }),
      { status: 401, headers: { "Content-Type": "application/json" } }
    )
  }

  const res = await fetch(url, {
    ...options,
    headers: {
      ...options.headers,
      Authorization: `Bearer ${session.token}`,
    },
  })

  if (res.status === 401) {
    if (!refreshPromise) {
      refreshPromise = tryRefresh(session).finally(() => {
        refreshPromise = null
      })
    }

    const refreshed = await refreshPromise

    if (refreshed) {
      const freshSession = await getIronSession<SessionData>(await cookies(), sessionOptions)
      return fetch(url, {
        ...options,
        headers: {
          ...options.headers,
          Authorization: `Bearer ${freshSession.token}`,
        },
      })
    }

    return new Response(
      JSON.stringify({ error: { message: "Sesion expirada", code: "SESSION_EXPIRED" } }),
      { status: 401, headers: { "Content-Type": "application/json" } }
    )
  }

  return res
}
