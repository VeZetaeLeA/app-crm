# ADR 1: Introducción de Capa de Dominio y Servicios (Fase 3)

**Fecha:** 2026-02-08  
**Estado:** Aceptado  

## Contexto
La aplicación DataWyrd ha crecido hasta un punto donde la lógica de negocio se encuentra dispersa entre controladores y modelos SQL. Esto dificulta el testing y hace que el mantenimiento sea propenso a errores de flujo (ej. transiciones de estado inválidas).

## Decisión
Implementar una **Capa de Dominio** explícita basada en:
1. **Entidades Puras:** Clases en `app/domain` que no conocen la base de datos ni las sesiones. Se adopta la nomenclatura **Active Service** (anteriormente Project) para mantener paridad 1:1 con el diccionario de datos de la base de datos.
2. **Value Objects:** Especialmente para el manejo de estados (`Status`), centralizando las reglas de transición.
3. **Services:** Orquestadores que interactúan con el dominio y la persistencia.

## Consecuencias
- **Positivas:** Lógica centralizada, reducción radical de bugs de flujo, código más testeable.
- **Negativas:** Ligero aumento en la cantidad de archivos iniciales.

---

# ADR 2: Centralización de Autorización mediante Policies

**Fecha:** 2026-02-08  
**Estado:** Aceptado  

## Contexto
Anteriormente, las vistas y controladores decidían los permisos de acceso de forma ad-hoc mediante `Auth::check()`. Esto generaba duplicidad y riesgos de seguridad si se olvidaba una validación en una vista específica.

## Decisión
Adoptar el patrón **Policy**. Cada recurso de dominio (Project, Ticket, Invoice) tendrá una clase Policy en `app/policies` o bajo su carpeta de dominio que centralice los booleanos de permiso.

## Consecuencias
- **Positivas:** Fuente única de verdad para la seguridad. Cambiar una regla de negocio de acceso ahora se hace en un solo lugar.
- **Negativas:** Requiere pasar el array de usuario y el recurso a la política en cada llamada.
