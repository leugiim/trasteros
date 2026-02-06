
"use client"

import { useState } from "react"
import { Eye, EyeOff, LoaderCircle } from "lucide-react"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import {
  Field,
  FieldGroup,
  FieldLabel,
} from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import { useLogin } from "@/hooks/use-login"

export function LoginForm({
  className,
  ...props
}: React.ComponentProps<"div">) {
  const [showPassword, setShowPassword] = useState(false)
  const { login, error, loading } = useLogin()

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const form = new FormData(e.currentTarget)
    const email = form.get("email") as string
    const password = form.get("password") as string
    await login(email, password)
  }
  return (
    <div className={cn("flex flex-col gap-6", className)} {...props}>
      <Card className="overflow-hidden p-0">
        <CardContent className="grid p-0 md:grid-cols-2">
          <form className="p-6 md:p-8" onSubmit={handleSubmit}>
            <FieldGroup>
              <div className="flex flex-col items-center gap-2 text-center">
                <h1 className="text-2xl font-bold">Bienvenido de nuevo</h1>
                <p className="text-muted-foreground text-balance">
                  Inicia sesión en tu cuenta
                </p>
              </div>
              {error && (
                <p className="text-sm text-red-500 text-center">{error}</p>
              )}
              <Field>
                <FieldLabel htmlFor="email">Correo electrónico</FieldLabel>
                <Input
                  id="email"
                  name="email"
                  type="email"
                  placeholder="m@ejemplo.com"
                  required
                />
              </Field>
              <Field>
                <FieldLabel htmlFor="password">Contraseña</FieldLabel>
                <div className="relative">
                  <Input id="password" name="password" type={showPassword ? "text" : "password"} required />
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="absolute right-0 top-0 h-full px-3 hover:bg-transparent"
                    onClick={() => setShowPassword(!showPassword)}
                  >
                    {showPassword ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
                  </Button>
                </div>
              </Field>
              <Field>
                <Button type="submit" disabled={loading}>
                  {loading && <LoaderCircle className="size-4 animate-spin" />}
                  Iniciar sesión
                </Button>
              </Field>
            </FieldGroup>
          </form>
          <div className="bg-muted relative hidden md:block">
            <img
              src="/storage-login.jpg"
              alt="Almacén con estanterías y cajas"
              className="absolute inset-0 h-full w-full object-cover dark:brightness-[0.7]"
            />
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
