<?php

define(
    'CATALOGO_NOMBRE',
    'Don Pingüino'
);


/*
 * WhatsApp para pedidos.
 * Formato:
 * 51 + número peruano
 *
 * Sin:
 * +
 * espacios
 * guiones
 */

define(
    'WHATSAPP_PEDIDOS_1',
    '519XXXXXXXX'
);

define(
    'WHATSAPP_PEDIDOS_2',
    '519YYYYYYYY'
);


$host =
    $_SERVER['HTTP_HOST']
    ?? '';


$esLocal =
    str_starts_with(
        $host,
        'localhost'
    );


if ($esLocal) {

    /*
     * LOCAL
     *
     * http://localhost/web/DonPinguino/
     */
    define(
        'CATALOGO_BASE_URL',
        '/web/DonPinguino/'
    );


    /*
     * http://localhost/web/DonPinguino/Sistema/
     */
    define(
        'SISTEMA_URL',
        '/web/DonPinguino/Sistema/'
    );

} else {

    /*
     * PRODUCCIÓN
     *
     * https://donpinguino.com/
     */
    define(
        'CATALOGO_BASE_URL',
        '/'
    );


    /*
     * https://donpinguino.com/sistema/
     */
    define(
        'SISTEMA_URL',
        '/sistema/'
    );
}