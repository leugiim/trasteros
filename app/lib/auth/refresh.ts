import { API_URL, type SessionData } from "@/lib/auth/session"

/**
 * Exchanges a refresh token for a new JWT + refresh token.
 *
 * Refresh tokens are single use (the API rotates them), so requests that
 * refresh the same token at the same time must share one call: otherwise the
 * second one would be rejected and log the user out. Calls are grouped by
 * refresh token (never shared between users), and the result is kept for a
 * few seconds for requests that were already in flight with the old cookie.
 * Each caller then saves the result into its own session.
 */

type RefreshResult = Required<Pick<SessionData, "token" | "refreshToken" | "user">>

const RESULT_TTL_MS = 10_000
const inFlight = new Map<string, Promise<RefreshResult | null>>()

export function refreshTokens(refreshToken: string): Promise<RefreshResult | null> {
  const existing = inFlight.get(refreshToken)
  if (existing) {
    return existing
  }

  const promise = (async (): Promise<RefreshResult | null> => {
    try {
      const res = await fetch(`${API_URL}/api/auth/refresh`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ refreshToken }),
      })

      if (!res.ok) {
        return null
      }

      const data = await res.json()
      return { token: data.token, refreshToken: data.refreshToken, user: data.user }
    } catch {
      return null
    }
  })()

  inFlight.set(refreshToken, promise)
  promise.finally(() => {
    setTimeout(() => inFlight.delete(refreshToken), RESULT_TTL_MS)
  })

  return promise
}
