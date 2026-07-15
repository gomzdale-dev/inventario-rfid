<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ActivosFijosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ubicación del archivo SQL con tus datos de ITCA-FEPADE
        $path = database_path('seeders/insert.sql');

        if (File::exists($path)) {
            $sql = File::get($path);

            $this->command->info('Iniciando la carga del catálogo de activos fijos e historial...');

            // Desactivar temporalmente restricciones para permitir TRUNCATE de tablas relacionadas
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Ejecuta el script SQL plano
            DB::unprepared($sql);

            // Reactivar restricciones de integridad estructural
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->command->info('¡Estructura de prueba e historial cargados exitosamente en la BD!');
        } else {
            $this->command->error('Error Crítico: No se encontró el archivo database/seeders/inserts.sql');
        }
    }
}