<?php

namespace App\Services;

use App\Models\AvisoSppld;
use App\Models\Client;
use App\Models\Operacion;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SppldXmlGenerator
{
    const NAMESPACE_URI = 'http://www.uif.shcp.gob.mx/recepcion/avi';
    const XSI_URI = 'http://www.w3.org/2001/XMLSchema-instance';

    /**
     * Catálogo de claves de actividad por fracción Art. 17.
     * Mapeo de fracción romana a clave numérica SPPLD.
     */
    const CLAVES_ACTIVIDAD = [
        'I' => '01',    // Juegos con apuestas
        'II' => '02',   // Tarjetas de servicios/prepago
        'III' => '03',  // Cheques de viajero
        'IV' => '04',   // Préstamos/créditos no financieros
        'V' => '05',    // Inmuebles
        'VI' => '06',   // Metales/piedras preciosas/joyería
        'VII' => '07',  // Notarías/fedatarios
        'VIII' => '08', // Agentes aduanales
        'IX' => '09',   // Derechos reales sobre inmuebles
        'X' => '10',    // Traslado/custodia de valores
        'XI' => '11',   // Arrendamiento/factoraje/outsourcing
        'XII' => '12',  // Servicios profesionales independientes
        'XIII' => '13', // Vehículos
        'XIV' => '14',  // Donativos
        'XV' => '15',   // Blindaje/custodia
        'XVI' => '16',  // Activos virtuales
    ];

    const FORMAS_PAGO = [
        'efectivo' => 'EF',
        'cheque' => 'CH',
        'transferencia' => 'TR',
        'tarjeta' => 'TC',
        'otro' => 'OT',
    ];

    const ENTIDADES_FEDERATIVAS = [
        'Aguascalientes' => '01', 'Baja California' => '02', 'Baja California Sur' => '03',
        'Campeche' => '04', 'Coahuila' => '05', 'Colima' => '06', 'Chiapas' => '07',
        'Chihuahua' => '08', 'Ciudad de México' => '09', 'CDMX' => '09', 'Durango' => '10',
        'Guanajuato' => '11', 'Guerrero' => '12', 'Hidalgo' => '13', 'Jalisco' => '14',
        'Estado de México' => '15', 'México' => '15', 'Michoacán' => '16', 'Morelos' => '17',
        'Nayarit' => '18', 'Nuevo León' => '19', 'Oaxaca' => '20', 'Puebla' => '21',
        'Querétaro' => '22', 'Quintana Roo' => '23', 'San Luis Potosí' => '24',
        'Sinaloa' => '25', 'Sonora' => '26', 'Tabasco' => '27', 'Tamaulipas' => '28',
        'Tlaxcala' => '29', 'Veracruz' => '30', 'Yucatán' => '31', 'Zacatecas' => '32',
    ];

    private DOMDocument $dom;
    private string $rfcSujetoObligado;
    private string $claveActividad;

    public function __construct(string $rfcSujetoObligado, string $claveActividad)
    {
        $this->rfcSujetoObligado = strtoupper($rfcSujetoObligado);
        $this->claveActividad = $claveActividad;
        $this->dom = new DOMDocument('1.0', 'utf-8');
        $this->dom->formatOutput = true;
    }

    /**
     * Genera un aviso normal con operaciones del periodo dado.
     */
    public function generarAvisoNormal(int $mes, int $anio, Collection $operaciones): string
    {
        $archivo = $this->crearRaiz();
        $informe = $this->crearInforme($archivo, $mes, $anio);

        $refCounter = 1;
        foreach ($operaciones as $operacion) {
            $aviso = $this->crearAviso($informe, $operacion, 'Normal', $refCounter++);
        }

        return $this->dom->saveXML();
    }

    /**
     * Genera un reporte en cero para el periodo.
     */
    public function generarAvisoCero(int $mes, int $anio): string
    {
        $archivo = $this->crearRaiz();
        $informe = $this->crearInforme($archivo, $mes, $anio);
        // Sin nodos <aviso> = reporte en cero

        return $this->dom->saveXML();
    }

    /**
     * Genera un aviso de 24 horas para una operación urgente.
     */
    public function generarAviso24Horas(Operacion $operacion): string
    {
        $archivo = $this->crearRaiz();
        $informe = $this->crearInforme(
            $archivo,
            $operacion->fecha_operacion->month,
            $operacion->fecha_operacion->year
        );
        $this->crearAviso($informe, $operacion, '24Horas', 1);

        return $this->dom->saveXML();
    }

    /**
     * Genera el aviso y lo guarda en storage. Retorna el path.
     */
    public static function generarYGuardar(AvisoSppld $aviso): string
    {
        $operaciones = Operacion::where('aviso_id', $aviso->id)
            ->with('client')
            ->get();

        // Determinar clave de actividad de la primera operación
        $primeraOp = $operaciones->first();
        $claveActividad = $primeraOp
            ? (self::CLAVES_ACTIVIDAD[$primeraOp->tipo_actividad_vulnerable] ?? '12')
            : '12';

        // RFC del sujeto obligado (configuración global)
        $rfcSujeto = config('compliance.rfc_sujeto_obligado', 'XAXX010101000');

        $generator = new self($rfcSujeto, $claveActividad);

        $xml = match ($aviso->tipo) {
            'cero' => $generator->generarAvisoCero($aviso->periodo_mes, $aviso->periodo_anio),
            '24horas' => $generator->generarAviso24Horas($operaciones->first()),
            default => $generator->generarAvisoNormal($aviso->periodo_mes, $aviso->periodo_anio, $operaciones),
        };

        $path = "avisos/{$aviso->folio}.xml";
        Storage::put($path, $xml);

        $aviso->update([
            'xml_path' => $path,
            'estado' => 'generado',
            'fecha_generacion' => now(),
        ]);

        return $path;
    }

    // =========================================================================
    // Private XML builders
    // =========================================================================

    private function crearRaiz(): DOMElement
    {
        $archivo = $this->dom->createElementNS(self::NAMESPACE_URI, 'archivo');
        $archivo->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsi',
            self::XSI_URI
        );
        $this->dom->appendChild($archivo);
        return $archivo;
    }

    private function crearInforme(DOMElement $archivo, int $mes, int $anio): DOMElement
    {
        $informe = $this->dom->createElement('informe');
        $archivo->appendChild($informe);

        $this->addElement($informe, 'mes_reportado', sprintf('%04d%02d', $anio, $mes));

        $sujeto = $this->dom->createElement('sujeto_obligado');
        $informe->appendChild($sujeto);
        $this->addElement($sujeto, 'clave_sujeto_obligado', $this->rfcSujetoObligado);
        $this->addElement($sujeto, 'clave_actividad', $this->claveActividad);

        return $informe;
    }

    private function crearAviso(DOMElement $informe, Operacion $operacion, string $prioridad, int $ref): DOMElement
    {
        $aviso = $this->dom->createElement('aviso');
        $informe->appendChild($aviso);

        $this->addElement($aviso, 'referencia_aviso', sprintf('REF-%05d', $ref));
        $this->addElement($aviso, 'prioridad', $prioridad);

        // Alerta
        $alerta = $this->dom->createElement('alerta');
        $aviso->appendChild($alerta);
        $this->addElement($alerta, 'tipo_alerta', '0');

        // Persona que realiza la operación (cliente)
        $client = $operacion->client;
        if ($client) {
            $this->crearPersonaAviso($aviso, $client);
        }

        // Detalle de la operación
        $this->crearDetalleOperacion($aviso, $operacion);

        return $aviso;
    }

    private function crearPersonaAviso(DOMElement $aviso, Client $client): void
    {
        $persona = $this->dom->createElement('persona_aviso');
        $aviso->appendChild($persona);

        $tipo = $this->dom->createElement('tipo_persona');
        $persona->appendChild($tipo);

        if ($client->tipo_persona === 'moral') {
            $this->crearPersonaMoral($tipo, $client);
        } else {
            $this->crearPersonaFisica($tipo, $client);
        }
    }

    private function crearPersonaFisica(DOMElement $tipo, Client $client): void
    {
        $pf = $this->dom->createElement('persona_fisica');
        $tipo->appendChild($pf);

        // Separar nombre (asumimos "nombre apellido_paterno apellido_materno")
        $partes = explode(' ', $client->razon_social, 3);
        $this->addElement($pf, 'nombre', $partes[0] ?? '');
        $this->addElement($pf, 'apellido_paterno', $partes[1] ?? '');
        if (isset($partes[2])) {
            $this->addElement($pf, 'apellido_materno', $partes[2]);
        }

        if ($client->fecha_nacimiento) {
            $this->addElement($pf, 'fecha_nacimiento', $client->fecha_nacimiento->format('Y-m-d'));
        }

        $this->addElement($pf, 'rfc', strtoupper($client->rfc));

        if ($client->curp) {
            $this->addElement($pf, 'curp', strtoupper($client->curp));
        }

        $this->addElement($pf, 'pais_nacionalidad', $this->codigoPais($client->nacionalidad ?? 'Mexicana'));

        if ($client->actividad_economica_scian) {
            $this->addElement($pf, 'actividad_economica', $client->actividad_economica_scian);
        }

        $this->crearDomicilio($pf, $client);
    }

    private function crearPersonaMoral(DOMElement $tipo, Client $client): void
    {
        $pm = $this->dom->createElement('persona_moral');
        $tipo->appendChild($pm);

        $this->addElement($pm, 'denominacion_razon', $client->razon_social);

        if ($client->fecha_constitucion) {
            $this->addElement($pm, 'fecha_constitucion', $client->fecha_constitucion->format('Y-m-d'));
        }

        $this->addElement($pm, 'rfc', strtoupper($client->rfc));
        $this->addElement($pm, 'pais_nacionalidad', $this->codigoPais($client->nacionalidad ?? 'Mexicana'));

        if ($client->objeto_social) {
            $this->addElement($pm, 'giro_mercantil', $client->objeto_social);
        }

        $this->crearDomicilio($pm, $client);

        // Representante legal
        if ($client->representante_legal_nombre) {
            $rep = $this->dom->createElement('representante_legal');
            $pm->appendChild($rep);

            $partesRep = explode(' ', $client->representante_legal_nombre, 3);
            $this->addElement($rep, 'nombre', $partesRep[0] ?? '');
            $this->addElement($rep, 'apellido_paterno', $partesRep[1] ?? '');
            if (isset($partesRep[2])) {
                $this->addElement($rep, 'apellido_materno', $partesRep[2]);
            }

            if ($client->representante_legal_rfc) {
                $this->addElement($rep, 'rfc', strtoupper($client->representante_legal_rfc));
            }
        }
    }

    private function crearDomicilio(DOMElement $parent, Client $client): void
    {
        if (!$client->domicilio_calle) {
            return;
        }

        $dom = $this->dom->createElement('domicilio');
        $parent->appendChild($dom);

        $this->addElement($dom, 'calle', $client->domicilio_calle);
        $this->addElement($dom, 'numero_exterior', $client->domicilio_num_ext ?? 'SN');

        if ($client->domicilio_num_int) {
            $this->addElement($dom, 'numero_interior', $client->domicilio_num_int);
        }

        $this->addElement($dom, 'colonia', $client->domicilio_colonia ?? '');
        $this->addElement($dom, 'municipio', $client->domicilio_municipio ?? '');

        $entidad = self::ENTIDADES_FEDERATIVAS[$client->domicilio_estado] ?? '09';
        $this->addElement($dom, 'entidad_federativa', $entidad);
        $this->addElement($dom, 'codigo_postal', $client->domicilio_cp ?? '00000');
        $this->addElement($dom, 'pais', 'MX');
    }

    private function crearDetalleOperacion(DOMElement $aviso, Operacion $operacion): void
    {
        $detalle = $this->dom->createElement('detalle_operacion');
        $aviso->appendChild($detalle);

        $this->addElement($detalle, 'fecha_operacion', $operacion->fecha_operacion->format('Y-m-d'));
        $this->addElement($detalle, 'descripcion_operacion', $operacion->descripcion ?? 'Operación registrada');
        $this->addElement($detalle, 'monto_operacion', number_format($operacion->monto, 2, '.', ''));
        $this->addElement($detalle, 'moneda', $operacion->moneda ?? 'MXN');

        // Forma de pago
        $formaPago = $this->dom->createElement('forma_pago');
        $detalle->appendChild($formaPago);

        $tipoPago = self::FORMAS_PAGO[$operacion->forma_pago] ?? 'OT';
        $this->addElement($formaPago, 'tipo_pago', $tipoPago);
        $this->addElement($formaPago, 'moneda_pago', $operacion->moneda ?? 'MXN');
        $this->addElement($formaPago, 'monto_pago', number_format($operacion->monto, 2, '.', ''));
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function addElement(DOMElement $parent, string $name, string $value): DOMElement
    {
        $el = $this->dom->createElement($name, htmlspecialchars($value, ENT_XML1, 'UTF-8'));
        $parent->appendChild($el);
        return $el;
    }

    private function codigoPais(string $nacionalidad): string
    {
        $map = [
            'Mexicana' => 'MX', 'México' => 'MX', 'Mexico' => 'MX',
            'Estadounidense' => 'US', 'Estados Unidos' => 'US',
            'Canadiense' => 'CA', 'Española' => 'ES', 'Colombiana' => 'CO',
        ];

        return $map[$nacionalidad] ?? 'MX';
    }
}
