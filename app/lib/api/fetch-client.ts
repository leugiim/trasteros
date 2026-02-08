export async function fetchClient(
  input: string | URL | Request,
  init?: RequestInit
): Promise<Response> {
  const res = await fetch(input, init)

  if (res.status === 401 && typeof window !== "undefined") {
    const url = typeof input === "string" ? input : input instanceof URL ? input.toString() : input.url
    if (!url.includes("/api/auth/")) {
      window.location.href = "/"
    }
  }

  return res
}
