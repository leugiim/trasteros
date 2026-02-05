# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Next.js 16 frontend application for the storage unit management system. Built with React 19, TypeScript, and Tailwind CSS 4.

## Key Commands

```bash
# Package manager: pnpm
pnpm install          # Install dependencies
pnpm dev              # Start development server (http://localhost:3000)
pnpm build            # Build for production
pnpm start            # Start production server
pnpm lint             # Run ESLint
```

## Architecture

- **Next.js 16** with App Router and React Server Components (RSC)
- **React 19** with latest features
- **Tailwind CSS 4** for styling
- **shadcn/ui** component library (base-mira style)
- **TypeScript** for type safety

## Directory Structure

```
app/
├── app/              # Next.js App Router pages and layouts
│   ├── layout.tsx    # Root layout
│   ├── page.tsx      # Home page
│   └── globals.css   # Global styles and Tailwind
├── components/       # React components
│   └── ui/           # shadcn/ui components
├── lib/              # Utility functions
│   └── utils.ts      # cn() helper for class merging
└── public/           # Static assets
```

## Component Patterns

### shadcn/ui Configuration

- Style: `base-mira`
- Icon library: `hugeicons`
- CSS variables enabled
- Path aliases configured:
  - `@/components` → `components/`
  - `@/lib` → `lib/`
  - `@/hooks` → `hooks/`

### Adding Components

```bash
# Add shadcn/ui components
pnpm dlx shadcn@latest add button
pnpm dlx shadcn@latest add card
```

### Class Merging

Use the `cn()` utility for conditional classes:

```tsx
import { cn } from "@/lib/utils"

<div className={cn("base-class", condition && "conditional-class")} />
```

## Icons

Using HugeIcons React:

```tsx
import { Home01Icon } from "@hugeicons/react"

<Home01Icon className="size-5" />
```

## API Integration

Backend API runs at `http://localhost:8000/api/` (see `/api` project).

Key endpoints:
- `POST /api/auth/login` - Authentication
- `GET /api/dashboard/stats` - Dashboard statistics
- Full API reference in `/api/openapi.json`
