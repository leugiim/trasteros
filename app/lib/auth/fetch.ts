import { getIronSession } from "iron-session"
import { cookies } from "next/headers"
import { refreshTokens } from "@/lib/auth/refresh"
import { sessionOptions, type SessionData } from "@/lib/auth/session"

type Session = SessionData & { save: () => Promise<void>; destroy: () => void }

async function tryRefresh(session: Session): Promise<boolean> {
  if (!session.refreshToken) {
    return false
  }

  const result = await refreshTokens(session.refreshToken)

  if (!result) {
    session.destroy()
    return false
  }

  session.token = result.token
  session.refreshToken = result.refreshToken
  session.user = result.user
  await session.save()
  return true
}

export async function authFetch(url: string, options: RequestInit = {}): Promise<Response> {
  const session = await getIronSession<SessionData>(await cookies(), sessionOptions)

  const debug = process.env.DEBUG_SENSITIVE_LOGS === "true"

  if (debug) {
    console.log("[authFetch] session:", { token: session.token, user: session.user?.email })
  } else {
    console.log("[authFetch] session token length:", session.token?.length, "user:", session.user?.email)
  }

  if (!session.token) {
    console.log("[authFetch] no token, returning 401 locally")
    return new Response(
      JSON.stringify({ error: { message: "No autenticado", code: "NOT_AUTHENTICATED" } }),
      { status: 401, headers: { "Content-Type": "application/json" } }
    )
  }

  const requestHeaders = {
    ...options.headers,
    Authorization: `Bearer ${session.token}`,
  }

  if (debug) {
    console.log("[authFetch] →", options.method ?? "GET", url, { headers: requestHeaders, body: options.body })
  } else {
    console.log("[authFetch] →", options.method ?? "GET", url)
  }

  const res = await fetch(url, { ...options, headers: requestHeaders })
  console.log("[authFetch] ←", res.status, res.url, res.redirected ? "(redirected!)" : "")

  if (res.status === 401) {
    // Refreshes this request's own session (see refreshTokens for how
    // concurrent refreshes of the same token are shared)
    const refreshed = await tryRefresh(session)

    if (refreshed) {
      // tryRefresh already put the new token in this request's session
      return fetch(url, {
        ...options,
        headers: {
          ...options.headers,
          Authorization: `Bearer ${session.token}`,
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
