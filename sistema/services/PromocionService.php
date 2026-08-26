<?php

class PromocionService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }


    /**
     * Calcula el precio de una línea de venta.
     *
     * Retorna:
     * - precio_regular
     * - precio_venta_base
     * - subtotal_base
     * - descuento_promocion
     * - subtotal_final
     * - promocion_id
     * - promocion_nombre
     * - detalle_precio
     */
    public function calcularProducto(
        array $producto,
        float $cantidad,
        ?string $fecha = null
    ): array {

        $fecha = $fecha ?: date('Y-m-d');

        $precioRegular =
            (float) $producto['precio_regular'];

        $precioVenta =
            (float) $producto['precio_venta'];

        $subtotalBase =
            round($precioVenta * $cantidad, 2);


        $resultadoBase = [
            'precio_regular' => $precioRegular,
            'precio_venta_base' => $precioVenta,

            'subtotal_base' => $subtotalBase,

            'descuento_promocion' => 0,
            'subtotal_final' => $subtotalBase,

            'promocion_id' => null,
            'promocion_nombre' => null,

            'detalle_precio' => [
                [
                    'cantidad' => $cantidad,
                    'precio' => $precioVenta
                ]
            ]
        ];


        $promociones =
            $this->obtenerPromocionesValidas(
                (int) $producto['id'],
                $fecha
            );


        if (!$promociones) {
            return $resultadoBase;
        }


        /*
         * Evaluamos todas y nos quedamos con
         * la que deje el menor subtotal.
         *
         * Por ahora las promociones no acumulables
         * se resuelven de esta forma.
         */
        $mejorResultado = $resultadoBase;


        foreach ($promociones as $promo) {

            $calculado =
                $this->aplicarRegla(
                    $promo,
                    $precioVenta,
                    $cantidad
                );


            if ($calculado === null) {
                continue;
            }


            if (
                $calculado['subtotal_final']
                < $mejorResultado['subtotal_final']
            ) {

                $mejorResultado = [
                    'precio_regular' =>
                        $precioRegular,

                    'precio_venta_base' =>
                        $precioVenta,

                    'subtotal_base' =>
                        $subtotalBase,

                    'descuento_promocion' =>
                        round(
                            $subtotalBase
                            - $calculado['subtotal_final'],
                            2
                        ),

                    'subtotal_final' =>
                        round(
                            $calculado['subtotal_final'],
                            2
                        ),

                    'promocion_id' =>
                        (int) $promo['promocion_id'],

                    'promocion_nombre' =>
                        $promo['promocion_nombre'],

                    'detalle_precio' =>
                        $calculado['detalle_precio']
                ];
            }
        }


        return $mejorResultado;
    }


    /**
     * Obtiene promociones:
     *
     * - activas
     * - dentro de vigencia
     * - aplicables al producto
     * - válidas para el día de semana
     */
    private function obtenerPromocionesValidas(
        int $productoId,
        string $fecha
    ): array {

        /*
         * date('N'):
         *
         * 1 lunes
         * ...
         * 7 domingo
         */
        $diaSemana =
            (int) date(
                'N',
                strtotime($fecha)
            );


        $sql = "
            SELECT
                p.id AS promocion_id,
                p.nombre AS promocion_nombre,
                p.prioridad,
                p.acumulable,

                pp.id AS regla_id,
                pp.tipo_beneficio,
                pp.cantidad_minima,
                pp.unidad_beneficiada,
                pp.precio_promocional,
                pp.porcentaje_descuento,
                pp.monto_descuento,
                pp.max_aplicaciones_por_venta

            FROM promociones p

            INNER JOIN promocion_productos pp
                ON pp.promocion_id = p.id

            WHERE
                pp.producto_id = ?

                AND p.activo = 1
                AND pp.activo = 1

                AND ? BETWEEN
                    p.fecha_inicio
                    AND p.fecha_fin

                AND (
                    NOT EXISTS (
                        SELECT 1
                        FROM promocion_dias pd0
                        WHERE pd0.promocion_id = p.id
                    )

                    OR EXISTS (
                        SELECT 1
                        FROM promocion_dias pd
                        WHERE
                            pd.promocion_id = p.id
                            AND pd.dia_semana = ?
                    )
                )

            ORDER BY
                p.id DESC
        ";


        $stmt =
            $this->conn->prepare($sql);


        $stmt->bind_param(
            'isi',
            $productoId,
            $fecha,
            $diaSemana
        );


        $stmt->execute();


        return $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);
    }


    private function aplicarRegla(
        array $promo,
        float $precioVenta,
        float $cantidad
    ): ?array {

        $cantidadMinima =
            (float) $promo['cantidad_minima'];


        if ($cantidad < $cantidadMinima) {
            return null;
        }


        $tipo =
            $promo['tipo_beneficio'];


        switch ($tipo) {

            // =================================================
            // PRECIO ESPECIAL PARA TODAS LAS UNIDADES
            // =================================================

            case 'PRECIO_ESPECIAL':

                if (
                    $promo['precio_promocional']
                    === null
                ) {
                    return null;
                }


                $precioPromo =
                    (float)
                    $promo['precio_promocional'];


                return [
                    'subtotal_final' =>
                        round(
                            $cantidad * $precioPromo,
                            2
                        ),

                    'detalle_precio' => [
                        [
                            'cantidad' => $cantidad,
                            'precio' => $precioPromo
                        ]
                    ]
                ];


            // =================================================
            // PORCENTAJE SOBRE TODO
            // =================================================

            case 'PORCENTAJE':

                if (
                    $promo['porcentaje_descuento']
                    === null
                ) {
                    return null;
                }


                $porcentaje =
                    (float)
                    $promo['porcentaje_descuento'];


                $precioFinal =
                    $precioVenta
                    * (1 - ($porcentaje / 100));


                return [
                    'subtotal_final' =>
                        round(
                            $cantidad * $precioFinal,
                            2
                        ),

                    'detalle_precio' => [
                        [
                            'cantidad' => $cantidad,
                            'precio' =>
                                round($precioFinal, 2)
                        ]
                    ]
                ];


            // =================================================
            // DESCUENTO FIJO
            // =================================================

            case 'DESCUENTO_FIJO':

                if (
                    $promo['monto_descuento']
                    === null
                ) {
                    return null;
                }


                $subtotal =
                    $cantidad * $precioVenta;


                $descuento =
                    (float)
                    $promo['monto_descuento'];


                return [
                    'subtotal_final' =>
                        max(
                            0,
                            round(
                                $subtotal - $descuento,
                                2
                            )
                        ),

                    'detalle_precio' => [
                        [
                            'cantidad' => $cantidad,
                            'precio' => $precioVenta
                        ]
                    ]
                ];


            // =================================================
            // CADA N-ÉSIMA UNIDAD TIENE PRECIO ESPECIAL
            //
            // Ejemplo:
            // N = 2
            // Segunda unidad S/27
            //
            // 4 unidades:
            // 28 + 27 + 28 + 27
            // =================================================

            case 'UNIDAD_N_PRECIO_ESPECIAL':

                return $this->calcularUnidadNPrecio(
                    $promo,
                    $precioVenta,
                    $cantidad
                );


            // =================================================
            // CADA N-ÉSIMA UNIDAD TIENE DESCUENTO %
            // =================================================

            case 'UNIDAD_N_PORCENTAJE':

                return $this->calcularUnidadNPorcentaje(
                    $promo,
                    $precioVenta,
                    $cantidad
                );
            



            
            // =================================================
            // JUEVES DE PATAS
            // =================================================
            case 'CANTIDAD_POR_PRECIO':

                return $this->calcularCantidadPorPrecio(
                    $promo,
                    $precioVenta,
                    $cantidad
                );
        }


        return null;
    }


    private function calcularUnidadNPrecio(
        array $promo,
        float $precioVenta,
        float $cantidad
    ): ?array {

        $unidadN =
            (int) (
                $promo['unidad_beneficiada']
                ?? 0
            );


        $precioPromo =
            $promo['precio_promocional'];


        if (
            $unidadN <= 0
            || $precioPromo === null
        ) {
            return null;
        }


        /*
         * Para productos de venta por unidad
         * este tipo promocional requiere
         * cantidad entera.
         */
        $cantidadEntera =
            (int) floor($cantidad);


        $aplicaciones =
            intdiv(
                $cantidadEntera,
                $unidadN
            );


        $limite =
            $promo['max_aplicaciones_por_venta'];


        if ($limite !== null) {

            $aplicaciones =
                min(
                    $aplicaciones,
                    (int) $limite
                );
        }


        if ($aplicaciones <= 0) {
            return null;
        }


        $cantidadPromo =
            $aplicaciones;


        $cantidadNormal =
            $cantidad - $cantidadPromo;


        $precioPromo =
            (float) $precioPromo;


        $subtotal =
            ($cantidadNormal * $precioVenta)
            +
            ($cantidadPromo * $precioPromo);


        $detalle = [];


        if ($cantidadNormal > 0) {

            $detalle[] = [
                'cantidad' => $cantidadNormal,
                'precio' => $precioVenta
            ];
        }


        if ($cantidadPromo > 0) {

            $detalle[] = [
                'cantidad' => $cantidadPromo,
                'precio' => $precioPromo
            ];
        }


        return [
            'subtotal_final' =>
                round($subtotal, 2),

            'detalle_precio' =>
                $detalle
        ];
    }


    private function calcularUnidadNPorcentaje(
        array $promo,
        float $precioVenta,
        float $cantidad
    ): ?array {

        $unidadN =
            (int) (
                $promo['unidad_beneficiada']
                ?? 0
            );


        $porcentaje =
            $promo['porcentaje_descuento'];


        if (
            $unidadN <= 0
            || $porcentaje === null
        ) {
            return null;
        }


        $cantidadEntera =
            (int) floor($cantidad);


        $aplicaciones =
            intdiv(
                $cantidadEntera,
                $unidadN
            );


        $limite =
            $promo['max_aplicaciones_por_venta'];


        if ($limite !== null) {

            $aplicaciones =
                min(
                    $aplicaciones,
                    (int) $limite
                );
        }


        if ($aplicaciones <= 0) {
            return null;
        }


        $cantidadPromo =
            $aplicaciones;


        $cantidadNormal =
            $cantidad - $cantidadPromo;


        $precioPromo =
            $precioVenta
            * (
                1
                -
                (
                    ((float) $porcentaje)
                    / 100
                )
            );


        $subtotal =
            ($cantidadNormal * $precioVenta)
            +
            ($cantidadPromo * $precioPromo);


        return [
            'subtotal_final' =>
                round($subtotal, 2),

            'detalle_precio' => [
                [
                    'cantidad' =>
                        $cantidadNormal,

                    'precio' =>
                        $precioVenta
                ],

                [
                    'cantidad' =>
                        $cantidadPromo,

                    'precio' =>
                        round($precioPromo, 2)
                ]
            ]
        ];
    }

    private function calcularCantidadPorPrecio(
        array $promo,
        float $precioVenta,
        float $cantidad
    ): ?array {

        $cantidadGrupo =
            (int) ($promo['unidad_beneficiada'] ?? 0);

        $precioGrupo =
            $promo['precio_promocional'];


        if (
            $cantidadGrupo <= 0
            || $precioGrupo === null
        ) {
            return null;
        }


        /*
        * Esta promoción está pensada para unidades enteras.
        */
        $cantidadEntera =
            (int) floor($cantidad);


        /*
        * Cuántos grupos completos existen.
        *
        * Ejemplo:
        * 7 unidades / grupo de 3
        * = 2 promociones.
        */
        $aplicaciones =
            intdiv(
                $cantidadEntera,
                $cantidadGrupo
            );


        $limite =
            $promo['max_aplicaciones_por_venta'];


        if ($limite !== null) {

            $aplicaciones =
                min(
                    $aplicaciones,
                    (int) $limite
                );
        }


        if ($aplicaciones <= 0) {
            return null;
        }


        $cantidadPromocionada =
            $aplicaciones
            * $cantidadGrupo;


        $cantidadNormal =
            $cantidad
            - $cantidadPromocionada;


        $precioGrupo =
            (float) $precioGrupo;


        $subtotalPromociones =
            $aplicaciones
            * $precioGrupo;


        $subtotalNormal =
            $cantidadNormal
            * $precioVenta;


        $subtotalFinal =
            $subtotalPromociones
            + $subtotalNormal;


        $detalle = [];


        if ($cantidadPromocionada > 0) {

            $detalle[] = [
                'cantidad' =>
                    $cantidadPromocionada,

                'precio_grupo' =>
                    $precioGrupo,

                'cantidad_por_grupo' =>
                    $cantidadGrupo,

                'aplicaciones' =>
                    $aplicaciones
            ];
        }


        if ($cantidadNormal > 0) {

            $detalle[] = [
                'cantidad' =>
                    $cantidadNormal,

                'precio' =>
                    $precioVenta
            ];
        }


        return [
            'subtotal_final' =>
                round(
                    $subtotalFinal,
                    2
                ),

            'detalle_precio' =>
                $detalle
        ];
    }
}