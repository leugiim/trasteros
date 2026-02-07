import { SessionOptions } from "iron-session"

if (!process.env.SESSION_SECRET) {
  throw new Error("SESSION_SECRET is not defined in environment variables")
}

if (!process.env.API_URL) {
  throw new Error("API_URL is not defined in environment variables")
}

export const API_URL = process.env.API_URL

export interface SessionData {
  token?: string
  user?: {
    id: string
    nombre: string
    email: string
    rol: string
  }
}

export const sessionOptions: SessionOptions = {
  password: process.env.SESSION_SECRET,
  cookieName: "session",
  cookieOptions: {
    secure: process.env.NODE_ENV === "production",
    httpOnly: true,
    sameSite: "lax" as const,
  },
}
