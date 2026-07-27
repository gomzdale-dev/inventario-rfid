<head>
    <meta charset="UTF-8">
    <title>Reporte - {{ ucfirst($tipoReporte) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        
        /* Botón de acción superior derecho */
        .top-action-bar {
            width: 100%;
            text-align: right;
            margin-bottom: 15px;
        }
        .print-btn { 
            background: #DC2626; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            font-weight: bold; 
            border-radius: 5px; 
            cursor: pointer; 
        }
        @media print { .print-action-bar { display: none; } }

        /* Cabecera profesional en dos columnas simétricas */
        .header { 
            width: 100%; 
            border-bottom: 2px solid #8B4513; 
            padding-bottom: 12px; 
            margin-bottom: 20px; 
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        
        /* Lado Izquierdo: Logo y Datos de la Institución */
        .header-left { width: 60%; text-align: left; }
        .logo-img {
            max-height: 50px;
            width: auto;
            vertical-align: middle;
            margin-right: 15px;
        }
        .institution-info {
            display: inline-block;
            vertical-align: middle;
        }
        .header-left h2 { 
            margin: 0 0 2px 0; 
            color: #8B4513; 
            font-size: 18px; 
            letter-spacing: 0.5px; 
        }
        .institution-info p { 
            margin: 2px 0; 
            color: #8B4513; /* Color café aplicado aquí */
            font-size: 10px; 
            text-transform: uppercase; 
            font-weight: bold;
        }

        /* Lado Derecho: Detalles del Reporte */
        .header-right { width: 40%; text-align: right; }
        .institution-info h3 { 
            margin: 1px 0; 
            color: #8B4513; /* Color café aplicado */
            font-size: 13px; /* Tamaño mayor como h3 */
            text-transform: uppercase; 
            font-weight: bold;
        }
        .institution-info h2 { 
            margin: 1px 0; 
            color:  #DC2626 ;
            font-size: 14px; /* Tamaño mayor como h3 */
            text-transform: uppercase; 
            font-weight: bold;
        }
        .header-right p {
            margin: 2px 0;
            color: #666;
            font-size: 11px;
        }

        /* Estilos de la tabla de datos principal */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
        table.data-table th { background-color: #8B4513; color: white; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        table.data-table tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>

    <div class="top-action-bar print-action-bar">
        <button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>

    <div class="header">
        <table class="header-table">
            <tr>
                <!-- Columna Izquierda: Logo y Datos de la Institución -->
                <td class="header-left">
                    @php
                        $pathLogo = public_path('images/logo-itca.png');
                        $logoBase64 = '';
                        if (file_exists($pathLogo)) {
                            $dataLogo = file_get_contents($pathLogo);
                            $logoBase64 = 'data:image/png;base64,' . base64_encode($dataLogo);
                        }
                    @endphp

                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo ITCA" class="logo-img">
                    @endif

                    <div class="institution-info">
                       <h2>SISTEMA DE CONTROL DE INVENTARIO Y ACTIVOS FIJOS (RFID)</h2>
                       <h3>Reporte: {{ strtoupper(str_replace('_', ' ', $tipoReporte)) }}</h3>
                    </div>
                </td>
                
                <!-- Columna Derecha: Información y Filtros del Reporte -->
                <!-- Columna Derecha: Información y Filtros del Reporte -->
                <td class="header-right">
                    @switch($tipoReporte)
                        @case('inventario_general')
                            <p><strong>Detalle:</strong> Inventario General de Activos</p>
                            @if(request()->filled('id_laboratorio'))
                                <p><strong>Salón:</strong> {{ \App\Models\Laboratorio::find(request('id_laboratorio'))?->nombre_laboratorio ?? 'Personalizado' }}</p>
                            @elseif(request()->filled('id_edificio'))
                                <p><strong>Edificio (Filtro):</strong> {{ \App\Models\Edificio::find(request('id_edificio'))?->nombre_edificio ?? 'Personalizado' }}</p>
                            @else
                                <p><strong>Salón:</strong> Todos los salones</p>
                            @endif
                            @break

                        @case('activos_por_ubicacion')
                            <p><strong>Detalle:</strong> Activos por ubicación</p>
                            <p><strong>Salón:</strong> 
                            {{ request()->filled('id_ubicacion') ? (App\Models\Ubicacion::with('laboratorio')->find(request('id_ubicacion'))?->laboratorio?->nombre_laboratorio ?? 'Personalizada') : 'Todas las ubicaciones' }}</p>
                            @break

                        @case('historial_por_movimiento')
                            <p><strong>Detalle:</strong> Historial de movimientos de activos</p>
                            <p><strong>Tipo Movimiento:</strong> 
                                @php
                                $movObj = request()->filled('tipo_movimiento') ? \App\Models\Tipo_Movimiento::find(request('tipo_movimiento')) : null;
                                @endphp
                            {{ $movObj ? $movObj->nombre_movimiento : 'Todos los movimientos' }}</p>
                            @break

                        @case('activos_por_estado')
                            <p><strong>Detalle:</strong> Activos por Estado</p>
                            <p><strong>Estado:</strong> 
                                {{ request()->filled('id_estado') ? (\App\Models\Estado_Activo::find(request('id_estado'))?->nombre_estado ?? 'Desconocido') : 'Todos los estados' }}</p>
                            @break
                   @endswitch

                    <p><strong>Fecha de Emisión:</strong> {{ date('d/m/Y H:i') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                @foreach($resultado['columns'] as $col)
                    <th>{{ $col['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($resultado['data'] as $row)
                <tr>
                    @foreach($resultado['columns'] as $col)
                        <td>{{ $row[$col['key']] ?? 'N/A' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($resultado['columns']) }}" style="text-align: center; color: #777;">No se encontraron registros para este reporte.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>