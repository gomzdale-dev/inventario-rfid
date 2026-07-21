<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventarios', function (Blueprint $table) {
            if (!Schema::hasColumn('inventarios', 'id_laboratorio')) {
                $table->unsignedBigInteger('id_laboratorio')
                    ->nullable()
                    ->after('id_usuario');

                $table->index('id_laboratorio');
            }
        });

        Schema::table('detalle_inventarios', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_inventarios', 'encontrado')) {
                $table->boolean('encontrado')
                    ->default(false)
                    ->after('cantidad');
            }

            if (!Schema::hasColumn('detalle_inventarios', 'fecha_lectura')) {
                $table->dateTime('fecha_lectura')
                    ->nullable()
                    ->after('encontrado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_inventarios', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_inventarios', 'fecha_lectura')) {
                $table->dropColumn('fecha_lectura');
            }

            if (Schema::hasColumn('detalle_inventarios', 'encontrado')) {
                $table->dropColumn('encontrado');
            }
        });

        Schema::table('inventarios', function (Blueprint $table) {
            if (Schema::hasColumn('inventarios', 'id_laboratorio')) {
                $table->dropIndex(['id_laboratorio']);
                $table->dropColumn('id_laboratorio');
            }
        });
    }
};
