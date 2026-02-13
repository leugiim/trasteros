"use client"

import { createContext, useContext, useState, type ReactNode } from "react"

interface PageHeaderContextValue {
  headerContent: ReactNode | null
  setHeaderContent: (content: ReactNode | null) => void
}

const PageHeaderContext = createContext<PageHeaderContextValue>({
  headerContent: null,
  setHeaderContent: () => {},
})

export function PageHeaderProvider({ children }: { children: ReactNode }) {
  const [headerContent, setHeaderContent] = useState<ReactNode | null>(null)
  return (
    <PageHeaderContext.Provider value={{ headerContent, setHeaderContent }}>
      {children}
    </PageHeaderContext.Provider>
  )
}

export function usePageHeader() {
  return useContext(PageHeaderContext)
}
