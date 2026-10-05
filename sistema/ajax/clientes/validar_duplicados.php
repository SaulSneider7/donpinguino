<?php

function normalizarNombreCliente(string $nombre): string
{
    return mb_strtolower(preg_replace('/[\s\p{Z}]+/u', '', $nombre), 'UTF-8');
}

function normalizarTelefonoCliente(string $telefono): string
{
    return preg_replace('/[\s\p{Z}()+.\-]+/u', '', $telefono);
}

function validarDuplicadosCliente(
    mysqli $conn,
    string $nombre,
    string $telefono,
    ?int $id = null
): void {
    $nombreNormalizado = normalizarNombreCliente($nombre);
    $telefonoNormalizado = normalizarTelefonoCliente($telefono);

    if ($nombreNormalizado === '') {
        responder(false, 'Ingrese el nombre del cliente.');
    }

    // Comparar también los registros antiguos con la misma normalización,
    // incluidos los inactivos, sin modificar cómo se muestran sus datos.
    $stmt = $conn->prepare('SELECT id, nombre, telefono FROM clientes WHERE id <> ?');
    if (!$stmt) {
        responder(false, 'No se pudo validar si el cliente ya existe.');
    }

    $idExcluir = $id ?? 0;
    $stmt->bind_param('i', $idExcluir);
    if (!$stmt->execute()) {
        $stmt->close();
        responder(false, 'No se pudo validar si el cliente ya existe.');
    }

    $resultado = $stmt->get_result();
    while ($cliente = $resultado->fetch_assoc()) {
        $mismoNombre = normalizarNombreCliente($cliente['nombre']) === $nombreNormalizado;
        $mismoTelefono = $telefonoNormalizado !== ''
            && normalizarTelefonoCliente($cliente['telefono'] ?? '') === $telefonoNormalizado;

        if ($mismoNombre || $mismoTelefono) {
            $resultado->free();
            $stmt->close();
            responder(false, $mismoNombre
                ? 'Ya existe un cliente con ese nombre.'
                : 'Ya existe un cliente con ese teléfono.', [
                    'cliente_existente' => [
                        'id' => (int) $cliente['id'],
                        'nombre' => $cliente['nombre'],
                        'telefono' => $cliente['telefono']
                    ]
                ]);
        }
    }

    $resultado->free();
    $stmt->close();
}
