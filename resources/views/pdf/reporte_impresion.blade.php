<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Activos - ITCA RFID</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; color: #332211; margin: 20px; }

        /* Contenedor principal del encabezado */
        .header-container { 
            display: flex; 
            align-items: center; 
            width: 100%; 
            border-bottom: 2px solid #74451f; 
            padding-bottom: 15px; 
            margin-bottom: 25px; 
        }

        /* Bloque izquierdo: Logo + Texto alineado a la izquierda */
        .header-left { 
            display: flex; 
            align-items: center; 
            flex: 1; 
            gap: 15px; 
        }
        .logo-img { max-height: 55px; width: auto; }
        .title-text { text-align: left; }
        .title-text h1 { margin: 0; color: #6b3f1e; font-size: 16px; text-transform: uppercase; }
        .title-text h2 { margin: 2px 0; color: #dc2626; font-size: 10px; text-transform: uppercase; }
        .title-text p { margin: 2px 0; color: #74451f; font-size: 10px; font-style: italic; }

        /* Bloque derecho: Botón */
        .no-print { flex: 0 0 auto; text-align: right; }
        .no-print button {
            padding: 8px 15px; background: #dc2626; color: white; border: none; 
            border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 11px;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .no-print button img { width: 14px; filter: brightness(0) invert(1); }

        /* Tabla */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #6b3f1e; color: white; padding: 10px; text-align: left; font-size: 11px; }
        td { padding: 10px; border-bottom: 1px solid #ddd; font-size: 11px; }

        /* Ajustes finales para impresión */
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <div class="header-container">
        <div class="header-left">
            <img src="{{ asset('images/logo-itca.png') }}" class="logo-img" alt="Logo">
            <div class="title-text">
                <h1>ITCA - FEPADE</h1>
                <h2>Sistema de Control de Inventario y Activos Fijos (RFID)</h2>
                <p>Reporte: {{ strtoupper($tipoReporte) }}</p>
            </div>
        </div>

        <div class="no-print">
            <button onclick="window.print()">
                <img src="{{ asset('images/impresora.png') }}" alt="Icono">
                Imprimir / Guardar PDF
            </button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código RFID</th>
                <th>Nombre del Activo</th>
                <th>Categoría</th>
                <th>Modelo</th>
                <th>Marca</th>
                <th>Ubicación / Salón</th>
                <th>Estado</th>
                <th>Responsable</th>
                <th>Valor Compra</th>
                <th>Fecha Compra</th>
                <th>Vida Util</th>
                <th>Depreciación Anual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->etiqueta->codigo ?? 'Sin Tag' }}</td>
                    <td>{{ $row->nombre_activo ?? 'N/A' }}</td>
                    <td>{{ $row->categoria->nombre_categoria ?? 'N/A' }}</td>
                    <td>{{ $row->modelo->nombre_modelo ?? 'N/A' }}</td>
                    <td>{{ $row->modelo->marca->nombre_marca ?? 'N/A' }}</td>
                    <td>{{ $row->ubicacion->laboratorio->nombre_laboratorio ?? 'N/A' }}</td>
                    <td>{{ $row->estadoActivo->nombre_estado ?? 'N/A' }}</td>
                    <td>{{ $row->responsable ? ($row->responsable->nombre . ' ' . $row->responsable->apellido) : 'No Asignado' }}</td>
                    <td>${{ number_format($row->valor_compra ?? 0, 2) }}</td>
                    <td>{{ $row->fecha_compra ?? 'N/A' }}</td>
                    <td>{{ $row->vida_util ?? 'N/A' }}</td>
                    <td>{{ $row->depreciacion_anual ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr><td colspan="11" style="text-align:center;">No hay registros encontrados.</td></tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>