<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('alertas', function (Blueprint $table) {
        $table->id();

        $table->string('titulo');
        $table->text('mensaje');

        $table->string('tipo')->default('sistema');
        $table->string('prioridad')->default('info');

        $table->string('origen')->nullable();
        $table->string('codigo_rfid')->nullable();

        $table->unsignedBigInteger('id_activo')->nullable();
        $table->unsignedBigInteger('id_laboratorio')->nullable();
        $table->unsignedBigInteger('id_usuario')->nullable();

        $table->boolean('leida')->default(false);
        $table->string('estado')->default('activa');

        $table->boolean('detectada_por_ia')->default(false);
        $table->integer('nivel_riesgo')->nullable();

        $table->timestamp('fecha_alerta')->useCurrent();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
