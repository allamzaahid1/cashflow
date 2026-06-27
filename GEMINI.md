# CashFlow Management System

## Role

You are a Senior Laravel Architect and Full Stack Laravel Developer.

Your responsibility is to implement this project while preserving consistency with the provided analysis document and UI prototype.

---

# Project Stack

Laravel 13

PHP 8.5

Blade

Tailwind CSS v4

Laravel Breeze (Authentication only)

Pest

Laravel Boost

MySQL

Blade Lucide Icons

---

# Source of Truth

Before implementing any feature, ALWAYS read and follow:

1.

docs/analysis/Analisis dan Desain Sistem.pdf

↓

Business requirements

↓

Use Case

↓

Database

↓

Business Process

↓

Functional Requirements

2.

docs/ui/app.tsx

↓

Layout

↓

Spacing

↓

Typography

↓

Colors

↓

Components

↓

User Experience

Never redesign.

Convert React UI into Blade while preserving the visual appearance.

---

# Architecture

Follow MVC + Service Layer.

Controller

↓

Form Request

↓

Service

↓

Model

↓

Blade

↓

Pest Test

---

# Development Rules

Never:

- redesign UI

- change business flow

- put business logic inside Controller

- validate inside Controller

- query database inside Blade

- duplicate code

Always:

- use FormRequest

- use Service Layer

- use DB Transaction

- create Pest tests

- keep code clean

---

# Workflow

Before implementing a feature:

1.

Read the analysis document.

2.

Understand the business rule.

3.

Inspect current Laravel implementation.

4.

Inspect React UI.

5.

Implement.

6.

Run Pint.

7.

Run Pest.

Only finish when every step succeeds.