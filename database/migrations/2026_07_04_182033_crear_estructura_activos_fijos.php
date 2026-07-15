<?php
/*-----------------------------------------------------------
PROYECTO : Desarrollo De Un Sistema Web De Gestión De Activos
           Fijos Mediante Tecnología RFID y Análisis 
           De Eventos Para El Control De Inventarios 
           En ITCA-FEPADE.
EQUIPO   : Gómez Alvarez, Eduardo Alexander,
           Granados Rodríguez, Jonatan Benjamín ,
           Cruz Martínez, Nataly Julissa.
FECHA    : Abril 2026.    
-------------------------------------------------------------*/
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
        // --------------------------------------------------------
        // 1. TABLAS MAESTRAS
        // --------------------------------------------------------
        Schema::create('edificios', function (Blueprint $table) {
            $table->integer('id_edificio', true);
            $table->string('nombre_edificio', 20);
            $table->char('estado', 1);
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->integer('id_categoria', true);
            $table->string('nombre_categoria', 25);
            $table->char('estado', 1);
        });

        Schema::create('marcas', function (Blueprint $table) {
            $table->integer('id_marca', true);
            $table->string('nombre_marca', 25);
            $table->char('estado', 1);
        });

        Schema::create('estado_activos', function (Blueprint $table) {
            $table->integer('id_estado', true);
            $table->string('nombre_estado', 20);
            $table->string('descripcion', 25);
            $table->char('estado', 1);
        });

        Schema::create('etiquetas_rfid', function (Blueprint $table) {
            $table->integer('id_etiqueta', true);
            $table->string('codigo', 30)->unique();
            $table->char('estado', 1);
        });

        Schema::create('responsables', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre', 25);
            $table->string('apellido', 25);
            $table->char('codigo_empleado', 20)->unique();
            $table->char('estado', 1);
        });

        Schema::create('tipo_movimientos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre_movimiento', 25);
            $table->char('estado', 1);
        });

        Schema::create('tipo_usuarios', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre_tipo', 25);
            $table->string('descripcion', 50)->nullable();
            $table->char('estado', 1);
            $table->dateTime('fecha_ingreso');
            $table->string('usuario_ingreso', 25);
            $table->dateTime('fecha_modifica');
            $table->string('usuario_modifica', 25);
        });

        // --------------------------------------------------------
        // 2. TABLAS CATÁLOGO (DEPENDIENTES)
        // --------------------------------------------------------
        Schema::create('laboratorios', function (Blueprint $table) {
            $table->integer('id_laboratorio', true);
            $table->string('nombre_laboratorio', 20);
            $table->integer('id_edificio');
            $table->char('estado', 1);

            $table->foreign('id_edificio')->references('id_edificio')->on('edificios')->onDelete('restrict')->onUpdate('cascade');
        });

        Schema::create('modelos', function (Blueprint $table) {
            $table->integer('id_modelo', true);
            $table->string('nombre_modelo', 25);
            $table->char('estado', 1);
            $table->integer('id_marca');

            $table->foreign('id_marca')->references('id_marca')->on('marcas')->onDelete('restrict')->onUpdate('cascade');
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->integer('id_usuario', true);
            $table->string('nombre_usuario', 25);
            $table->string('correo', 50)->unique();
            $table->string('password', 255);
            $table->char('estado', 1);
            $table->dateTime('fecha_ingreso');
            $table->string('usuario_ingreso', 25);
            $table->dateTime('fecha_modifica');
            $table->string('usuario_modifica', 25);
            $table->integer('id_tipo');

            $table->foreign('id_tipo')->references('id')->on('tipo_usuarios')->onDelete('restrict')->onUpdate('cascade');
        });

        // --------------------------------------------------------
        // 3. TABLAS DE TERCER NIVEL
        // --------------------------------------------------------
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->integer('id_ubicacion', true);
            $table->integer('id_laboratorio');
            $table->char('estado', 1);

            $table->foreign('id_laboratorio')->references('id_laboratorio')->on('laboratorios')->onDelete('restrict')->onUpdate('cascade');
        });

        Schema::create('inventarios', function (Blueprint $table) {
            $table->integer('id_inventario', true);
            $table->dateTime('fecha_inventario');
            $table->integer('id_usuario')->nullable();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('restrict')->onUpdate('cascade');
        });

        // --------------------------------------------------------
        // 4. TABLA PRINCIPAL: ACTIVOS
        // --------------------------------------------------------
        Schema::create('activos', function (Blueprint $table) {
            $table->integer('id_activo', true);
            $table->string('nombre_activo', 50);
            $table->string('serie', 50)->unique();
            $table->decimal('valor_compra', 15, 2);
            $table->date('fecha_compra');
            $table->decimal('valor_actual', 15, 2);
            $table->integer('vida_util');
            $table->decimal('depreciacion_anual', 15, 2);
            $table->integer('id_etiqueta')->unique();
            $table->integer('id_categoria');
            $table->integer('id_modelo');
            $table->integer('id_ubicacion');
            $table->integer('id_estado');
            $table->integer('id_responsable')->nullable();

            $table->foreign('id_etiqueta')->references('id_etiqueta')->on('etiquetas_rfid')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_modelo')->references('id_modelo')->on('modelos')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_ubicacion')->references('id_ubicacion')->on('ubicaciones')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_estado')->references('id_estado')->on('estado_activos')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_responsable')->references('id')->on('responsables')->onDelete('set null')->onUpdate('cascade');
        });

        // --------------------------------------------------------
        // 5. HISTORIAL Y AUDITORÍA
        // --------------------------------------------------------
        Schema::create('detalle_inventarios', function (Blueprint $table) {
            $table->integer('id_detalle', true);
            $table->string('observaciones', 50)->nullable();
            $table->integer('cantidad');
            $table->integer('id_inventario');
            $table->integer('id_activo');

            $table->unique(['id_inventario', 'id_activo']);
            $table->foreign('id_inventario')->references('id_inventario')->on('inventarios')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_activo')->references('id_activo')->on('activos')->onDelete('restrict')->onUpdate('cascade');
        });

        Schema::create('movimientos', function (Blueprint $table) {
            $table->integer('id_movimiento', true);
            $table->string('comentarios', 50)->nullable();
            $table->integer('tipo_movimiento');
            $table->dateTime('fecha_movimiento')->nullable()->useCurrent();
            $table->integer('id_usuario')->nullable();
            $table->integer('id_activo');
            $table->integer('id_ubicacion');

            $table->foreign('tipo_movimiento')->references('id')->on('tipo_movimientos')->onUpdate('cascade');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_activo')->references('id_activo')->on('activos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_ubicacion')->references('id_ubicacion')->on('ubicaciones')->onDelete('restrict')->onUpdate('cascade');
        });

        // --------------------------------------------------------
        // etiqueta_historial 
        // --------------------------------------------------------
        Schema::create('etiqueta_historial', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('activo_fijo_id');
            $table->integer('etiqueta_rfid_id');
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->timestamp('fecha_baja')->nullable();
            $table->string('motivo_baja', 25)->nullable(); // 'perdida', 'dañada', 'reemplazo'

            $table->foreign('activo_fijo_id')
                  ->references('id_activo')
                  ->on('activos')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('etiqueta_rfid_id')
                  ->references('id_etiqueta')
                  ->on('etiquetas_rfid')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });

        // --------------------------------------------------------
        // 6. ALERTAS Y SOPORTE API
        // --------------------------------------------------------
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('tipo')->default('inventario');
            $table->string('prioridad')->default('info');
            $table->string('origen')->default('IA');
            $table->integer('id_laboratorio')->nullable();
            $table->tinyInteger('leida')->default(0);
            $table->string('estado')->default('activa');
            $table->tinyInteger('detectada_por_ia')->default(1);
            $table->integer('nivel_riesgo')->nullable();
            $table->timestamp('fecha_alerta')->useCurrent();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type');
            $table->unsignedBigInteger('tokenable_id');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tokenable_type', 'tokenable_id']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('alertas');
        Schema::dropIfExists('etiqueta_historial'); // Eliminamos la tabla agregada en caso de rollback
        Schema::dropIfExists('movimientos');
        Schema::dropIfExists('detalle_inventarios');
        Schema::dropIfExists('activos');
        Schema::dropIfExists('inventarios');
        Schema::dropIfExists('ubicaciones');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('modelos');
        Schema::dropIfExists('laboratorios');
        Schema::dropIfExists('tipo_usuarios');
        Schema::dropIfExists('tipo_movimientos');
        Schema::dropIfExists('responsables');
        Schema::dropIfExists('etiquetas_rfid');
        Schema::dropIfExists('estado_activos');
        Schema::dropIfExists('marcas');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('edificios');
    }
};