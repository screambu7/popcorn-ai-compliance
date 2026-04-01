<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RFC del Sujeto Obligado
    |--------------------------------------------------------------------------
    |
    | RFC de la entidad registrada en el SPPLD del SAT como sujeto obligado
    | que realiza actividades vulnerables conforme al Art. 17 LFPIORPI.
    |
    */
    'rfc_sujeto_obligado' => env('COMPLIANCE_RFC_SUJETO', 'XAXX010101000'),

    /*
    |--------------------------------------------------------------------------
    | Clave de Actividad Vulnerable
    |--------------------------------------------------------------------------
    |
    | Clave de dos dígitos correspondiente a la fracción del Art. 17
    | bajo la cual está registrado el sujeto obligado en el SPPLD.
    |
    */
    'clave_actividad_default' => env('COMPLIANCE_CLAVE_ACTIVIDAD', '12'),

    /*
    |--------------------------------------------------------------------------
    | UMA 2026
    |--------------------------------------------------------------------------
    */
    'uma_diario' => env('UMA_DIARIO', 117.31),
    'uma_mensual' => env('UMA_MENSUAL', 3568.94),
    'uma_anual' => env('UMA_ANUAL', 42826.86),

    /*
    |--------------------------------------------------------------------------
    | Retención obligatoria (años)
    |--------------------------------------------------------------------------
    */
    'retencion_anios' => 10,

    /*
    |--------------------------------------------------------------------------
    | n8n Integration
    |--------------------------------------------------------------------------
    */
    'n8n_url' => env('N8N_URL', 'https://n8n.popcorn.mx'),
];
