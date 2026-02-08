// lib/format.ts

export function formatCurrency(amount: number | null | undefined) {
  if (amount == null) return "-"
  return new Intl.NumberFormat("es-ES", {
    style: "currency",
    currency: "EUR",
  }).format(amount)
}

export function formatDate(date: string | null | undefined) {
  if (!date) return "-"
  return new Date(date).toLocaleDateString("es-ES")
}
