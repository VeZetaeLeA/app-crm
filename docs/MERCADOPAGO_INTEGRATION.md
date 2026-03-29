# Guía de Integración: MercadoPago y Transferencias Bancarias

Esta guía detalla cómo habilitar los pagos automatizados usando MercadoPago y configurar dinámicamente las instrucciones de transferencia bancaria directamente desde el archivo `.env`.

## 1. Configuración de Transferencia Bancaria (Manual / FinOps)
El sistema **Data Wyrd OS** ya no utiliza textos forzados en el código. Para actualizar la información del banco que se muestra en las facturas de tus clientes, debes ajustar las siguientes variables en tu archivo `.env`:

```env
# ===========================================
# INSTRUCCIONES BANCARIAS (TRANSFERENCIAS)
# ===========================================
BANK_NAME="Ecosistema Digital Bank"
BANK_ACCOUNT_NAME="Data Wyrd Services LLC"
BANK_ACCOUNT_NUMBER="1234-5678-9012"
BANK_CBU_ALIAS="vezetaelea.usd"
```
Cualquier cambio a estas variables impactará inversamente y en tiempo real a la vista web de **todas** las facturas `unpaid` o `partial`.

---

## 2. Integración de MercadoPago Global

### Paso 2.1: Obtener las Credenciales
1. Accede a tu cuenta de MercadoPago y dirígete al panel de **[Desarrolladores -> Tus Integraciones](https://www.mercadopago.com/developers/panel/app)**.
2. Crea una **Nueva Aplicación**.
3. Ingresa a la sección **Credenciales de Producción** (o Credenciales de Prueba si estás en un entorno local / sandbox).
4. Copia el **Access Token** y la **Public Key**.

### Paso 2.2: Configurar tu Sistema Data Wyrd OS
Copia las credenciales obtenidas y colócalas en el archivo `.env` en la raíz de tu proyecto:

```env
# ===========================================
# PASARELAS DE PAGO (MERCADOPAGO)
# ===========================================
MP_ACCESS_TOKEN=APP_USR-8984920...
MP_PUBLIC_KEY=APP_USR-3...
MP_CURRENCY_ID=ARS
MP_EXCHANGE_RATE=1050.00
```

> [!TIP]
> **Finanzas Cross-Border (Multi-divisa):** VeZetaeLeA OS factura a los clientes en la moneda original (`USD`, por ejemplo), pero si tu cuenta de MercadoPago acepta solo moneda local (ej. Colombia `COP` o Argentina `ARS`), la variable `MP_EXCHANGE_RATE` multiplicará el valor en USD por tu tasa y enviará silenciosamente a MercadoPago la cifra en la divisa declarada en `MP_CURRENCY_ID`. La deuda en VeZetaeLeA bajará normalmente.

**Atención:** El panel de `Pagar Online` solo será visible para el cliente si la variable `MP_ACCESS_TOKEN` no está vacía.

### Paso 2.3: Configurar el Webhook en MercadoPago
Para que los pagos cambien de estado automáticamente y el servicio se active sin espera humana (FinOps asíncrono):

1. En el panel de tu Aplicación de MercadoPago, navega a **Webhooks**.
2. Añade la siguiente **URL de Producción**: 
   `https://tu-dominio.com/webhook/mercadopago`
   _(Reemplaza `tu-dominio.com` por el dominio real establecido en `APP_URL`)_
3. En la sección de Eventos a monitorear, selecciona **Pagos (Payments)**.
4. Guarda los cambios.

### ¿Qué hace el sistema bajo el capó?
Una vez un cliente efectúa un pago a través del Checkout, MercadoPago contacta a `WebhookController::mercadopago()`. El servidor consulta nativamente utilizando cURL usando el `MP_ACCESS_TOKEN` para evitar suplantaciones, si el resultado realza transaccionado y cobrado, genera un comprobante virtual automático y ejecuta `InvoiceService::confirmPayment()`, cambiando el estado de la factura a `Paid`, abriendo o actualizando el `Workspace` para el cliente y cerrando el ticket origen.
