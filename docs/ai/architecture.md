# Architecture

Pattern

MVC + Service Layer

Folder

app/

Controllers/

Models/

Services/

Policies/

Enums/

Requests/

Rules

Controller

Maximum responsibility:

- authorize

- call service

- return response

No business logic.

Service

Contains all business logic.

Every Create / Update / Delete

must use

DB::transaction()

Validation

Always use FormRequest.

Never validate in Controller.