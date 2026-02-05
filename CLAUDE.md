# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Monorepo containing a full-stack application for storage unit (trasteros) management.

| Directory | Technology | Description |
|-----------|------------|-------------|
| `/api` | Symfony 7.4 | REST API with JWT authentication |
| `/app` | Next.js 16 | Frontend application |

## Quick Start

```bash
# API (from /api directory)
symfony server:start
php bin/console doctrine:migrations:migrate
php bin/console app:database:seeds

# Frontend (from /app directory)
pnpm install
pnpm dev
```

## Subproject Documentation

Each subproject has its own CLAUDE.md with detailed instructions:
- [API Documentation](api/CLAUDE.md) - Symfony commands, entity patterns, testing
- [App Documentation](app/CLAUDE.md) - Next.js structure, components, styling
