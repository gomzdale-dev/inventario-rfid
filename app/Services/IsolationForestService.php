<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IsolationForestService
 * 
 * Servicio encargado de comunicarse con el microservicio Python
 * de Isolation Forest. Llama al endpoint /analizar-laboratorios
 * y devuelve los laboratorios anómalos detectados por la IA.
 */
class IsolationForestService
{
    // URL base del microservicio Python FastAPI
    private string $baseUrl;

    public function __construct()
    {
        // URL donde corre el microservicio Python
        // Cambiar si se despliega en otro servidor
        $this->baseUrl = 'http://127.0.0.1:8001';
    }

    /**
     * Llama al endpoint de Isolation Forest y devuelve
     * los laboratorios anómalos detectados por la IA.
     * 
     * @return array Lista de laboratorios anómalos o arreglo vacío si falla
     */
    public function obtenerAnomalias(): array
    {
        try {
            // Llamada HTTP GET al microservicio Python
            // timeout: 30 segundos para dar tiempo al modelo IF
            $response = Http::timeout(30)
                ->get("{$this->baseUrl}/analizar-laboratorios");

            // Verificar que la respuesta fue exitosa
            if (!$response->successful()) {
                Log::error('IsolationForestService: Error en respuesta del microservicio', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                return [];
            }

            $data = $response->json();

            // Verificar que el microservicio respondió sin errores internos
            if (!isset($data['status']) || $data['status'] !== 'ok') {
                Log::error('IsolationForestService: Microservicio reportó error', [
                    'respuesta' => $data
                ]);
                return [];
            }

            // Devolver solo el arreglo de laboratorios anómalos
            return $data['data'] ?? [];

        } catch (\Exception $e) {
            // Registrar el error sin detener la aplicación
            Log::error('IsolationForestService: No se pudo conectar al microservicio', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Convierte el score de Isolation Forest (-1 a +1)
     * a una escala de nivel de riesgo del 1 al 10
     * para guardarlo en el campo nivel_riesgo de la tabla alertas.
     * 
     * Ejemplos:
     *   score -0.5  → nivel 10 (muy anómalo)
     *   score -0.1  → nivel 6
     *   score  0.0  → nivel 5
     *   score +0.5  → nivel 1 (normal)
     * 
     * @param float $score Score de IF entre -1 y +1
     * @return int Nivel de riesgo entre 1 y 10
     */
    public function convertirScoreANivel(float $score): int
    {
        // Fórmula: invierte la escala y mapea a 1-10
        $nivel = (int) round((1 - $score) * 5);

        // Asegurar que esté dentro del rango 1-10
        return max(1, min(10, $nivel));
    }
}