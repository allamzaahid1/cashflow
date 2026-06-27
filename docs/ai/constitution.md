# AI Constitution

## Purpose

This document defines the mandatory rules for all AI agents working on this project.

Its purpose is to ensure consistent architecture, stable implementation, predictable development workflow, and high-quality code throughout the entire project lifecycle.

These rules have higher priority than implementation preferences.

---

# AI Roles

## ChatGPT

Responsibilities:

- Software Architect
- Tech Lead
- AI Prompt Engineer
- Code Reviewer
- Workflow Designer
- Debugger
- Architecture Decision Maker

ChatGPT is NOT responsible for implementing the application.

---

## Gemini Agent

Responsibilities:

- Laravel implementation
- CRUD implementation
- Refactoring
- Unit Testing
- Feature Testing
- File Generation
- Bug Fixing

Gemini Agent MUST NOT redesign architecture without instruction.

---

# Source of Truth

Every implementation MUST follow this order.

Priority 1

System Analysis Document

```
docs/analysis/Analisis dan Desain Sistem.pdf
```

Contains:

- Functional Requirement
- Business Process
- Database Design
- Activity Diagram
- Use Case

Priority 2

UI Prototype

```
docs/ui/app.tsx
```

Contains:

- Layout
- Components
- UX
- UI
- Navigation
- Typography

Never redesign.

Priority 3

AI Documentation

```
docs/ai/
```

---

# Architecture Policy

Always preserve project architecture.

Current architecture:

MVC

↓

Form Request

↓

Service Layer

↓

Model

↓

Blade

↓

Pest Test

Never bypass Service Layer.

Never move business logic into Controller.

Never query database inside Blade.

Never redesign architecture without approval.

---

# Development Policy

Implement only the requested feature.

Do not modify unrelated modules.

Do not perform unnecessary refactoring.

Do not rename files without approval.

Do not create new architecture patterns.

Keep changes as small as possible.

---

# UI Policy

React Prototype is the visual source of truth.

Laravel Blade must reproduce the same appearance.

Allowed:

- Blade conversion
- Tailwind implementation
- Component extraction

Not allowed:

- redesign
- changing layout
- changing spacing
- changing typography
- changing color palette

unless explicitly requested.

---

# Business Rule Policy

Business rules always come from:

System Analysis Document.

If implementation conflicts with assumptions,

the analysis document wins.

Never invent business rules.

---

# Database Policy

Database schema follows:

Analysis Document

and

database.md

Do not change schema unless required.

Do not add unnecessary tables.

---

# Prompt Policy

Before implementing any feature:

1. Read Analysis Document
2. Read AI Documentation
3. Read UI Prototype
4. Inspect Current Laravel Code
5. Understand Existing Architecture
6. Implement Requested Feature Only

---

# Review Policy

After implementation:

- check architecture consistency
- check business rules
- check naming consistency
- run Pint
- run Pest

Only finish when every verification passes.

---

# Decision Policy

If multiple implementations are possible,

choose the one that:

1. follows analysis document
2. preserves architecture
3. minimizes changes
4. keeps implementation simple
5. maximizes maintainability

---

# Scope Policy

Gemini Agent is NOT allowed to:

- redesign architecture
- redesign UI
- invent business rules
- change unrelated files
- optimize code outside requested scope

unless explicitly instructed.

---

# Completion Policy

A feature is considered complete only if:

✓ Analysis is followed

✓ UI matches prototype

✓ Business rules are satisfied

✓ Architecture remains consistent

✓ Tests pass

✓ Pint passes

✓ No unrelated files are modified