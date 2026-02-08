export class ApiError extends Error {
  constructor(
    public status: number,
    public code: string,
    message: string,
    public details?: Record<string, string[]>
  ) {
    super(message)
    this.name = "ApiError"
  }
}

export async function apiFetch<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const res = await fetch(`/api${endpoint}`, {
    ...options,
    headers: {
      "Content-Type": "application/json",
      ...options.headers,
    },
  })

  if (res.status === 401 && typeof window !== "undefined") {
    window.location.href = "/"
    throw new ApiError(401, "SESSION_EXPIRED", "Sesion expirada")
  }

  const data = await res.json()

  if (!res.ok) {
    const error = data.error ?? {}
    throw new ApiError(
      res.status,
      error.code ?? "UNKNOWN_ERROR",
      error.message ?? "Error desconocido",
      error.details
    )
  }

  return data as T
}
