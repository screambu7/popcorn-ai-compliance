<x-mail::message>
# Bienvenido a Popcorn Compliance, {{ $userName }}

Tu cuenta ha sido creada exitosamente. Ahora puedes gestionar tus obligaciones fiscales y expedientes de antilavado desde un solo lugar.

<x-mail::button :url="$dashboardUrl">
Ir a mi Dashboard
</x-mail::button>

**Con Popcorn Compliance puedes:**

- Recibir alertas automáticas de ISR, IVA, DIOT y declaración anual
- Gestionar expedientes KYC conforme a la LFPIORPI Art. 17
- Clasificar clientes con actividades vulnerables y detectar PEP
- Calcular umbrales UMA automáticamente (UMA 2026: $117.31/día)
- Mantener auditoría completa con retención de 10 años

<x-mail::panel>
**Tip:** Empieza registrando a tus clientes y el sistema calculará automáticamente su nivel de riesgo y obligaciones de reporte.
</x-mail::panel>

Si tienes alguna duda, responde a este correo y te ayudamos.

Saludos,<br>
**El equipo de Popcorn Compliance**
</x-mail::message>
