<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IsolationForestService;
use App\Models\Alerta;
use Illuminate\Support\Facades\Log;

/**
 * AnalizarAnomalias
 * 
 * Comando Laravel que orquesta el análisis de anomalías
 * de inventario usando Isolation Forest.
 * 
 * Flujo:
 * 1. Llama al microservicio Python via IsolationForestService
 * 2. Recibe los laboratorios anómalos detectados por IF
 * 3. Evita duplicados — no inserta si ya existe alerta activa
 * 4. Guarda las alertas nuevas en la tabla alertas
 * 
 * Uso manual:
 *   php artisan anomalias:analizar
 * 
 * Uso automático:
 *   Configurado en routes/console.php para correr cada noche
 */
class AnalizarAnomalias extends Command
{
    // Nombre del comando para llamarlo desde artisan
    protected $signature = 'anomalias:analizar';

    // Descripción que aparece en php artisan list
    protected $description = 'Analiza anomalías de inventario usando Isolation Forest y genera alertas';

    public function __construct(
        private IsolationForestService $ifService
    ) {
        parent::__construct();
    }

    /**
     * Ejecuta el análisis de anomalías
     */
    public function handle(): void
    {
        $this->info('🤖 Iniciando análisis de anomalías con Isolation Forest...');
        Log::info('AnalizarAnomalias: Iniciando análisis IF');

        // Paso 1: Obtener anomalías del microservicio Python
        $anomalias = $this->ifService->obtenerAnomalias();

        if (empty($anomalias)) {
            $this->warn('⚠ No se obtuvieron anomalías o el microservicio no está disponible.');
            Log::warning('AnalizarAnomalias: Sin anomalías o microservicio no disponible');
            return;
        }

        $this->info("📊 Laboratorios anómalos detectados: " . count($anomalias));

        $insertadas = 0;
        $duplicadas = 0;

        // Paso 2: Procesar cada laboratorio anómalo
        foreach ($anomalias as $anomalia) {

            // Verificar si ya existe una alerta activa para este laboratorio
            // Evita duplicar notificaciones del mismo lab en el mismo día
            $existe = Alerta::where('id_laboratorio', $anomalia['id_laboratorio'])
                ->where('estado', 'A')
                ->where('detectada_por_ia', 1)
                ->whereDate('fecha_alerta', today())
                ->exists();

            if ($existe) {
                $duplicadas++;
                $this->line("  ↳ Ya existe alerta activa hoy para {$anomalia['laboratorio']} — omitiendo");
                continue;
            }

            // Paso 3: Construir y guardar la alerta
            try {
                Alerta::create([
                    // Título visible en la notificación
                    'titulo' => "{$anomalia['laboratorio']} — Atención requerida",

                    // Mensaje con el motivo específico detectado por IF
                    'mensaje' => $this->construirMensaje($anomalia),

                    // Tipo de alerta para filtrar en el sistema
                    'tipo' => 'inventario',

                    // Prioridad basada en urgencia de IF
                    // alta → danger, media → warning, baja → info
                    'prioridad' => $this->mapearPrioridad($anomalia['urgencia']),

                    // Indica que fue detectada por IA
                    'origen' => 'IA',

                    // Laboratorio afectado para el botón "Ver"
                    'id_laboratorio' => $anomalia['id_laboratorio'],

                    // Alerta sin leer por defecto
                    'leida' => 0,

                    // Estado activa hasta que sea atendida
                    'estado' => 'A',

                    // Marca que fue generada por Isolation Forest
                    'detectada_por_ia' => 1,

                    // Score IF convertido a escala 1-10
                    'nivel_riesgo' => $this->ifService->convertirScoreANivel(
                        $anomalia['score_if']
                    ),

                    // Fecha exacta de detección
                    'fecha_alerta' => now(),
                ]);

                $insertadas++;
                $this->info("  ✅ Alerta generada: {$anomalia['laboratorio']} — {$anomalia['urgencia']}");
                Log::info("AnalizarAnomalias: Alerta generada para {$anomalia['laboratorio']}", [
                    'score_if' => $anomalia['score_if'],
                    'urgencia' => $anomalia['urgencia'],
                    'motivo'   => $anomalia['motivo']
                ]);

            } catch (\Exception $e) {
                $this->error("  ❌ Error al guardar alerta para {$anomalia['laboratorio']}: {$e->getMessage()}");
                Log::error("AnalizarAnomalias: Error al guardar alerta", [
                    'laboratorio' => $anomalia['laboratorio'],
                    'error'       => $e->getMessage()
                ]);
            }
        }

        // Resumen final del análisis
        $this->info("─────────────────────────────────────");
        $this->info("✅ Alertas insertadas : {$insertadas}");
        $this->info("⏭  Duplicadas omitidas: {$duplicadas}");
        $this->info("─────────────────────────────────────");

        Log::info('AnalizarAnomalias: Análisis finalizado', [
            'insertadas' => $insertadas,
            'duplicadas' => $duplicadas
        ]);
    }

    /**
     * Construye el mensaje completo de la notificación
     * incluyendo edificio, motivo y días sin inventario
     * 
     * @param array $anomalia Datos del laboratorio anómalo
     * @return string Mensaje formateado para la notificación
     */
    private function construirMensaje(array $anomalia): string
{
    return "En el {$anomalia['edificio']}, el {$anomalia['laboratorio']} {$anomalia['motivo']}";
}

    /**
     * Mapea la urgencia de IF a la prioridad del sistema de alertas
     * 
     * @param string $urgencia Urgencia devuelta por IF: alta, media, baja
     * @return string Prioridad para la tabla alertas
     */
    private function mapearPrioridad(string $urgencia): string
    {
        return match($urgencia) {
            'alta'  => 'danger',
            'media' => 'warning',
            'baja'  => 'info',
            default => 'info'
        };
    }
}