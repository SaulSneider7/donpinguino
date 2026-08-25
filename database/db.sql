-- ============================================================
-- DON PINGÜINO
-- Sistema de ventas, inventario, clientes y catálogo
-- Motor recomendado: MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS don_pinguino
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE don_pinguino;


-- ============================================================
-- 1. USUARIOS / ADMINISTRADORES
-- Todos los usuarios tienen acceso administrativo.
-- No existen roles.
-- ============================================================

CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(120) NOT NULL,
    usuario VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- ============================================================
-- 2. CLIENTES
-- Solo nombre es realmente necesario para una venta rápida.
-- ============================================================

CREATE TABLE clientes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NULL,
    direccion VARCHAR(255) NULL,
    observacion TEXT NULL,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_clientes_nombre (nombre),
    INDEX idx_clientes_telefono (telefono)
);


-- ============================================================
-- 3. CATEGORÍAS
-- ============================================================

CREATE TABLE categorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,

    orden_catalogo INT NOT NULL DEFAULT 0,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- ============================================================
-- 4. TIPOS DE ENVASE RETORNABLE
-- Ejemplo:
-- Pilsen 630 ml
-- Cusqueña 630 ml
-- ============================================================

CREATE TABLE tipos_envase (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(120) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- ============================================================
-- 5. PRODUCTOS
--
-- precio_regular:
--   precio referencial mostrado al cliente.
--
-- precio_venta:
--   precio normal al que vende Don Pingüino.
--
-- El precio realmente cobrado se guarda posteriormente
-- en detalle_venta.
-- ============================================================

CREATE TABLE productos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    categoria_id BIGINT UNSIGNED NULL,
    tipo_envase_id BIGINT UNSIGNED NULL,

    nombre VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NULL UNIQUE,

    descripcion TEXT NULL,
    presentacion VARCHAR(120) NULL,

    tipo_producto ENUM(
        'SIMPLE',
        'COMBO'
    ) NOT NULL DEFAULT 'SIMPLE',

    costo_referencia DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    precio_regular DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    precio_venta DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    -- Para productos físicos normales.
    -- Los combos normalmente no tienen stock propio.
    maneja_stock BOOLEAN NOT NULL DEFAULT TRUE,

    stock_actual DECIMAL(12,3) NOT NULL DEFAULT 0,
    stock_minimo DECIMAL(12,3) NOT NULL DEFAULT 0,

    -- Solo productos retornables.
    controla_envase BOOLEAN NOT NULL DEFAULT FALSE,
    envases_por_unidad DECIMAL(10,3) NOT NULL DEFAULT 0,

    imagen_url VARCHAR(500) NULL,

    publicar_catalogo BOOLEAN NOT NULL DEFAULT TRUE,
    destacado_catalogo BOOLEAN NOT NULL DEFAULT FALSE,
    orden_catalogo INT NOT NULL DEFAULT 0,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_productos_categoria
        FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_productos_tipo_envase
        FOREIGN KEY (tipo_envase_id)
        REFERENCES tipos_envase(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_producto_precios
        CHECK (
            costo_referencia >= 0
            AND precio_regular >= 0
            AND precio_venta >= 0
        ),

    CONSTRAINT chk_producto_stock
        CHECK (
            stock_actual >= 0
            AND stock_minimo >= 0
        ),

    CONSTRAINT chk_producto_envases
        CHECK (envases_por_unidad >= 0),

    INDEX idx_productos_nombre (nombre),
    INDEX idx_productos_categoria (categoria_id),
    INDEX idx_productos_catalogo (
        publicar_catalogo,
        activo
    ),
    INDEX idx_productos_stock (
        maneja_stock,
        stock_actual
    )
);


-- ============================================================
-- 6. COMBOS
--
-- El combo es también un producto.
--
-- Ejemplo:
-- Combo El Clásico
--   1 Ron Cartavio
--   1 Coca Cola
--   1 Hielo
-- ============================================================

CREATE TABLE combos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    producto_id BIGINT UNSIGNED NOT NULL UNIQUE,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_combo_producto
        FOREIGN KEY (producto_id)
        REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);


CREATE TABLE combo_componentes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    combo_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,

    cantidad DECIMAL(12,3) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_combo_componentes_combo
        FOREIGN KEY (combo_id)
        REFERENCES combos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_combo_componentes_producto
        FOREIGN KEY (producto_id)
        REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT uq_combo_componente
        UNIQUE (combo_id, producto_id),

    CONSTRAINT chk_combo_componente_cantidad
        CHECK (cantidad > 0),

    INDEX idx_combo_componentes_producto (producto_id)
);


-- ============================================================
-- 7. PROMOCIONES
--
-- Ejemplos:
-- Jueves de Patas
-- Día del Padre
-- Fiestas Patrias
-- Aniversario
-- ============================================================

CREATE TABLE promociones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,

    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,

    prioridad INT NOT NULL DEFAULT 0,

    -- Normalmente promociones NO acumulables.
    acumulable BOOLEAN NOT NULL DEFAULT FALSE,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT chk_promocion_fechas
        CHECK (fecha_fin >= fecha_inicio),

    INDEX idx_promociones_fechas (
        fecha_inicio,
        fecha_fin,
        activo
    )
);


-- ============================================================
-- 8. DÍAS EN LOS QUE APLICA UNA PROMOCIÓN
--
-- Convención ISO:
-- 1 = lunes
-- 2 = martes
-- 3 = miércoles
-- 4 = jueves
-- 5 = viernes
-- 6 = sábado
-- 7 = domingo
--
-- Si una promoción no tiene registros aquí,
-- puede considerarse válida todos los días.
-- ============================================================

CREATE TABLE promocion_dias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    promocion_id BIGINT UNSIGNED NOT NULL,

    dia_semana TINYINT UNSIGNED NOT NULL,

    CONSTRAINT fk_promocion_dias_promocion
        FOREIGN KEY (promocion_id)
        REFERENCES promociones(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT uq_promocion_dia
        UNIQUE (promocion_id, dia_semana),

    CONSTRAINT chk_dia_semana
        CHECK (dia_semana BETWEEN 1 AND 7)
);


-- ============================================================
-- 9. REGLAS PROMOCIONALES POR PRODUCTO
--
-- Soporta:
-- - Precio especial
-- - Descuento porcentual
-- - Descuento fijo
-- - Segunda/N-ésima unidad a precio especial
-- - Segunda/N-ésima unidad con porcentaje
-- ============================================================

CREATE TABLE promocion_productos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    promocion_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,

    tipo_beneficio ENUM(
        'PRECIO_ESPECIAL',
        'PORCENTAJE',
        'DESCUENTO_FIJO',
        'UNIDAD_N_PRECIO_ESPECIAL',
        'UNIDAD_N_PORCENTAJE'
    ) NOT NULL,

    -- Cantidad mínima para activar.
    cantidad_minima DECIMAL(12,3) NOT NULL DEFAULT 1,

    -- Por ejemplo:
    -- segunda unidad => 2
    unidad_beneficiada INT NULL,

    precio_promocional DECIMAL(12,2) NULL,
    porcentaje_descuento DECIMAL(7,4) NULL,
    monto_descuento DECIMAL(12,2) NULL,

    -- NULL = sin límite.
    max_aplicaciones_por_venta INT NULL,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_promoproducto_promocion
        FOREIGN KEY (promocion_id)
        REFERENCES promociones(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_promoproducto_producto
        FOREIGN KEY (producto_id)
        REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_promo_cantidad_minima
        CHECK (cantidad_minima > 0),

    CONSTRAINT chk_promo_precio
        CHECK (
            precio_promocional IS NULL
            OR precio_promocional >= 0
        ),

    CONSTRAINT chk_promo_porcentaje
        CHECK (
            porcentaje_descuento IS NULL
            OR (
                porcentaje_descuento >= 0
                AND porcentaje_descuento <= 100
            )
        ),

    CONSTRAINT chk_promo_descuento
        CHECK (
            monto_descuento IS NULL
            OR monto_descuento >= 0
        ),

    INDEX idx_promoproducto_promocion (promocion_id),
    INDEX idx_promoproducto_producto (producto_id)
);


-- ============================================================
-- 10. PRODUCTOS REGALADOS POR PROMOCIÓN
--
-- Ejemplo:
-- Compra 2 Pilsen Six Pack
-- y recibe 1 Mike's gratis.
-- ============================================================

CREATE TABLE promocion_regalos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    promocion_id BIGINT UNSIGNED NOT NULL,

    producto_condicion_id BIGINT UNSIGNED NOT NULL,
    cantidad_condicion DECIMAL(12,3) NOT NULL,

    producto_regalo_id BIGINT UNSIGNED NOT NULL,
    cantidad_regalo DECIMAL(12,3) NOT NULL,

    max_aplicaciones_por_venta INT NULL,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_promoregalo_promocion
        FOREIGN KEY (promocion_id)
        REFERENCES promociones(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_promoregalo_condicion
        FOREIGN KEY (producto_condicion_id)
        REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_promoregalo_regalo
        FOREIGN KEY (producto_regalo_id)
        REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_promoregalo_cantidades
        CHECK (
            cantidad_condicion > 0
            AND cantidad_regalo > 0
        ),

    INDEX idx_promoregalo_promocion (promocion_id)
);


-- ============================================================
-- 11. VENTAS
-- ============================================================

CREATE TABLE ventas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    cliente_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    descuento_promociones DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    descuento_manual DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    total_pagado DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    saldo_pendiente DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    estado_pago ENUM(
        'PAGADO',
        'PARCIAL',
        'PENDIENTE'
    ) NOT NULL DEFAULT 'PENDIENTE',

    estado ENUM(
        'ACTIVA',
        'ANULADA'
    ) NOT NULL DEFAULT 'ACTIVA',

    observacion TEXT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_ventas_cliente
        FOREIGN KEY (cliente_id)
        REFERENCES clientes(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_ventas_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_venta_importes
        CHECK (
            subtotal >= 0
            AND descuento_promociones >= 0
            AND descuento_manual >= 0
            AND total >= 0
            AND total_pagado >= 0
            AND saldo_pendiente >= 0
        ),

    INDEX idx_ventas_fecha (fecha),
    INDEX idx_ventas_cliente (cliente_id),
    INDEX idx_ventas_estado_pago (estado_pago),
    INDEX idx_ventas_estado (estado)
);


-- ============================================================
-- 12. DETALLE DE VENTA
--
-- Los precios se COPIAN aquí para mantener histórico.
--
-- Si el producto cambia de precio después,
-- esta venta NO cambia.
-- ============================================================

CREATE TABLE detalle_venta (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    venta_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NULL,

    -- Snapshot histórico.
    nombre_producto VARCHAR(180) NOT NULL,
    presentacion_producto VARCHAR(120) NULL,

    cantidad DECIMAL(12,3) NOT NULL,

    costo_unitario DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    precio_regular DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    precio_venta_base DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    subtotal_base DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    descuento_promocion DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    descuento_manual DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    subtotal_final DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    -- Promoción aplicada a esta línea.
    promocion_id BIGINT UNSIGNED NULL,

    -- Snapshot para que aunque luego se edite/elimine
    -- la promoción sepamos cómo se llamó.
    promocion_nombre VARCHAR(150) NULL,

    -- Permite guardar casos como:
    -- [
    --   {"cantidad":1,"precio":28},
    --   {"cantidad":1,"precio":27}
    -- ]
    detalle_precio_json JSON NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_detalleventa_venta
        FOREIGN KEY (venta_id)
        REFERENCES ventas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_detalleventa_producto
        FOREIGN KEY (producto_id)
        REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_detalleventa_promocion
        FOREIGN KEY (promocion_id)
        REFERENCES promociones(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_detalleventa_cantidad
        CHECK (cantidad > 0),

    CONSTRAINT chk_detalleventa_importes
        CHECK (
            costo_unitario >= 0
            AND precio_regular >= 0
            AND precio_venta_base >= 0
            AND subtotal_base >= 0
            AND descuento_promocion >= 0
            AND descuento_manual >= 0
            AND subtotal_final >= 0
        ),

    INDEX idx_detalleventa_venta (venta_id),
    INDEX idx_detalleventa_producto (producto_id),
    INDEX idx_detalleventa_promocion (promocion_id)
);


-- ============================================================
-- 13. PAGOS
--
-- Cada pago queda registrado por separado.
--
-- Ejemplo:
-- Venta S/100
-- Día 1: Yape S/40
-- Día 2: Efectivo S/30
-- Día 3: Yape S/30
-- ============================================================

CREATE TABLE pagos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    venta_id BIGINT UNSIGNED NOT NULL,
    cliente_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,

    monto DECIMAL(12,2) NOT NULL,

    metodo_pago ENUM(
        'EFECTIVO',
        'YAPE',
        'PLIN',
        'OTRO'
    ) NOT NULL,

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    observacion VARCHAR(500) NULL,

    estado ENUM(
        'ACTIVO',
        'ANULADO'
    ) NOT NULL DEFAULT 'ACTIVO',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pagos_venta
        FOREIGN KEY (venta_id)
        REFERENCES ventas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_pagos_cliente
        FOREIGN KEY (cliente_id)
        REFERENCES clientes(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_pagos_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_pago_monto
        CHECK (monto > 0),

    INDEX idx_pagos_venta (venta_id),
    INDEX idx_pagos_cliente (cliente_id),
    INDEX idx_pagos_fecha (fecha)
);


-- ============================================================
-- 14. ENVASES DE UNA VENTA
--
-- Guarda lo ocurrido exactamente al momento de vender.
--
-- Ejemplo:
-- requeridos: 12
-- entregados: 8
-- pendientes: 4
-- ============================================================

CREATE TABLE venta_envases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    venta_id BIGINT UNSIGNED NOT NULL,
    tipo_envase_id BIGINT UNSIGNED NOT NULL,

    cantidad_requerida DECIMAL(12,3)
        NOT NULL DEFAULT 0,

    cantidad_entregada DECIMAL(12,3)
        NOT NULL DEFAULT 0,

    cantidad_pendiente DECIMAL(12,3)
        NOT NULL DEFAULT 0,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_ventaenvase_venta
        FOREIGN KEY (venta_id)
        REFERENCES ventas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_ventaenvase_tipo
        FOREIGN KEY (tipo_envase_id)
        REFERENCES tipos_envase(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT uq_venta_tipo_envase
        UNIQUE (venta_id, tipo_envase_id),

    CONSTRAINT chk_ventaenvase_cantidades
        CHECK (
            cantidad_requerida >= 0
            AND cantidad_entregada >= 0
            AND cantidad_pendiente >= 0
        )
);


-- ============================================================
-- 15. MOVIMIENTOS DE ENVASES
--
-- Historial completo de deuda y devolución.
--
-- DEUDA      => aumenta saldo
-- DEVOLUCION => disminuye saldo
-- AJUSTE     => corrección manual
-- ============================================================

CREATE TABLE movimientos_envases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    cliente_id BIGINT UNSIGNED NOT NULL,
    tipo_envase_id BIGINT UNSIGNED NOT NULL,

    venta_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,

    tipo_movimiento ENUM(
        'DEUDA',
        'DEVOLUCION',
        'AJUSTE_ENTRADA',
        'AJUSTE_SALIDA',
        'ANULACION'
    ) NOT NULL,

    cantidad DECIMAL(12,3) NOT NULL,

    saldo_anterior DECIMAL(12,3) NOT NULL,
    saldo_nuevo DECIMAL(12,3) NOT NULL,

    descripcion VARCHAR(500) NULL,

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_movenvase_cliente
        FOREIGN KEY (cliente_id)
        REFERENCES clientes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_movenvase_tipo
        FOREIGN KEY (tipo_envase_id)
        REFERENCES tipos_envase(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_movenvase_venta
        FOREIGN KEY (venta_id)
        REFERENCES ventas(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_movenvase_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_movenvase_cantidad
        CHECK (cantidad > 0),

    INDEX idx_movenvase_cliente (
        cliente_id,
        tipo_envase_id
    ),

    INDEX idx_movenvase_fecha (fecha)
);


-- ============================================================
-- 16. PROVEEDORES
-- ============================================================

CREATE TABLE proveedores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(150) NOT NULL,

    ruc VARCHAR(20) NULL,
    telefono VARCHAR(30) NULL,
    direccion VARCHAR(255) NULL,

    observacion TEXT NULL,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_proveedores_nombre (nombre),
    INDEX idx_proveedores_ruc (ruc)
);


-- ============================================================
-- 17. COMPRAS / ABASTECIMIENTO
-- ============================================================

CREATE TABLE compras (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    proveedor_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    descuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    observacion TEXT NULL,

    estado ENUM(
        'ACTIVA',
        'ANULADA'
    ) NOT NULL DEFAULT 'ACTIVA',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_compras_proveedor
        FOREIGN KEY (proveedor_id)
        REFERENCES proveedores(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_compras_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_compra_importes
        CHECK (
            subtotal >= 0
            AND descuento >= 0
            AND total >= 0
        ),

    INDEX idx_compras_fecha (fecha),
    INDEX idx_compras_proveedor (proveedor_id)
);


-- ============================================================
-- 18. DETALLE DE COMPRA
-- ============================================================

CREATE TABLE detalle_compra (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    compra_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,

    cantidad DECIMAL(12,3) NOT NULL,
    costo_unitario DECIMAL(12,2) NOT NULL,

    subtotal DECIMAL(12,2) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_detallecompra_compra
        FOREIGN KEY (compra_id)
        REFERENCES compras(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_detallecompra_producto
        FOREIGN KEY (producto_id)
        REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_detallecompra_cantidad
        CHECK (cantidad > 0),

    CONSTRAINT chk_detallecompra_costo
        CHECK (
            costo_unitario >= 0
            AND subtotal >= 0
        ),

    INDEX idx_detallecompra_compra (compra_id),
    INDEX idx_detallecompra_producto (producto_id)
);


-- ============================================================
-- 19. KARDEX / MOVIMIENTOS DE STOCK
--
-- productos.stock_actual da el stock rápido.
-- movimientos_stock explica cómo se llegó a ese stock.
-- ============================================================

CREATE TABLE movimientos_stock (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    producto_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,

    tipo_movimiento ENUM(
        'COMPRA',
        'VENTA',
        'REGALO',
        'REGALO_PROMOCIONAL',
        'AJUSTE_ENTRADA',
        'AJUSTE_SALIDA',
        'ANULACION_COMPRA',
        'ANULACION_VENTA',
        'ANULACION_REGALO'
    ) NOT NULL,

    -- Ejemplo:
    -- COMPRA / VENTA / REGALO
    referencia_tipo VARCHAR(40) NULL,
    referencia_id BIGINT UNSIGNED NULL,

    -- Positivo para entrada.
    -- Negativo para salida.
    cantidad DECIMAL(12,3) NOT NULL,

    stock_anterior DECIMAL(12,3) NOT NULL,
    stock_nuevo DECIMAL(12,3) NOT NULL,

    costo_unitario DECIMAL(12,2) NULL,

    descripcion VARCHAR(500) NULL,

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_movstock_producto
        FOREIGN KEY (producto_id)
        REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_movstock_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_movstock_cantidad
        CHECK (cantidad <> 0),

    INDEX idx_movstock_producto_fecha (
        producto_id,
        fecha
    ),

    INDEX idx_movstock_referencia (
        referencia_tipo,
        referencia_id
    )
);


-- ============================================================
-- 20. REGALOS / PREMIOS / CORTESÍAS
--
-- NO son ventas a S/0.
--
-- Afectan inventario y costo,
-- pero no los ingresos por ventas.
-- ============================================================

CREATE TABLE regalos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id BIGINT UNSIGNED NOT NULL,
    cliente_id BIGINT UNSIGNED NULL,

    tipo ENUM(
        'REGALO',
        'PREMIO',
        'CORTESIA',
        'OTRO'
    ) NOT NULL DEFAULT 'REGALO',

    descripcion TEXT NOT NULL,

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    estado ENUM(
        'ACTIVO',
        'ANULADO'
    ) NOT NULL DEFAULT 'ACTIVO',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_regalos_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_regalos_cliente
        FOREIGN KEY (cliente_id)
        REFERENCES clientes(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    INDEX idx_regalos_fecha (fecha),
    INDEX idx_regalos_cliente (cliente_id)
);


-- ============================================================
-- 21. DETALLE DE REGALOS
-- ============================================================

CREATE TABLE detalle_regalo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    regalo_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,

    cantidad DECIMAL(12,3) NOT NULL,

    costo_unitario DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    costo_total DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_detalleregalo_regalo
        FOREIGN KEY (regalo_id)
        REFERENCES regalos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_detalleregalo_producto
        FOREIGN KEY (producto_id)
        REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_detalleregalo_cantidad
        CHECK (cantidad > 0),

    CONSTRAINT chk_detalleregalo_costo
        CHECK (
            costo_unitario >= 0
            AND costo_total >= 0
        ),

    INDEX idx_detalleregalo_producto (producto_id)
);


-- ============================================================
-- 22. REGALOS GENERADOS DENTRO DE UNA VENTA POR PROMOCIÓN
--
-- Ejemplo:
-- Compra 2 six packs y recibe un Mike's.
-- ============================================================

CREATE TABLE detalle_venta_regalos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    venta_id BIGINT UNSIGNED NOT NULL,
    promocion_id BIGINT UNSIGNED NULL,
    producto_id BIGINT UNSIGNED NOT NULL,

    cantidad DECIMAL(12,3) NOT NULL,

    costo_unitario DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    costo_total DECIMAL(12,2)
        NOT NULL DEFAULT 0.00,

    promocion_nombre VARCHAR(150) NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_ventaregalo_venta
        FOREIGN KEY (venta_id)
        REFERENCES ventas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_ventaregalo_promocion
        FOREIGN KEY (promocion_id)
        REFERENCES promociones(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_ventaregalo_producto
        FOREIGN KEY (producto_id)
        REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_ventaregalo_cantidad
        CHECK (cantidad > 0),

    CONSTRAINT chk_ventaregalo_costo
        CHECK (
            costo_unitario >= 0
            AND costo_total >= 0
        ),

    INDEX idx_ventaregalo_venta (venta_id)
);


-- ============================================================
-- 23. ÍNDICES EXTRA PARA REPORTES
-- ============================================================

CREATE INDEX idx_detalleventa_producto_venta
ON detalle_venta (producto_id, venta_id);

CREATE INDEX idx_productos_activos_nombre
ON productos (activo, nombre);

CREATE INDEX idx_clientes_activos_nombre
ON clientes (activo, nombre);


-- ============================================================
-- 24. CATEGORÍAS INICIALES BASADAS EN EL CATÁLOGO
-- Pueden modificarse posteriormente.
-- ============================================================

INSERT INTO categorias (nombre, orden_catalogo)
VALUES
    ('Combos', 1),
    ('Cervezas', 2),
    ('Ron', 3),
    ('Whisky', 4),
    ('Vodka', 5),
    ('Vinos', 6),
    ('Piscos', 7),
    ('Rehidratantes', 8),
    ('Latas y RTD', 9),
    ('Gaseosas', 10),
    ('Hielos', 11),
    ('Snacks', 12),
    ('Cigarros', 13),
    ('Otros', 99);


-- ============================================================
-- 25. VISTAS ÚTILES
-- ============================================================


-- ------------------------------------------------------------
-- Deuda monetaria actual por cliente
-- ------------------------------------------------------------

CREATE OR REPLACE VIEW vw_deudas_clientes AS
SELECT
    c.id AS cliente_id,
    c.nombre AS cliente,
    COUNT(v.id) AS ventas_con_deuda,
    ROUND(SUM(v.saldo_pendiente), 2) AS deuda_total
FROM clientes c
INNER JOIN ventas v
    ON v.cliente_id = c.id
WHERE
    v.estado = 'ACTIVA'
    AND v.saldo_pendiente > 0
GROUP BY
    c.id,
    c.nombre;


-- ------------------------------------------------------------
-- Saldo actual de envases por cliente y tipo
--
-- Se obtiene del último movimiento registrado para cada tipo.
-- ------------------------------------------------------------

CREATE OR REPLACE VIEW vw_saldos_envases AS
SELECT
    me.cliente_id,
    c.nombre AS cliente,

    me.tipo_envase_id,
    te.nombre AS tipo_envase,

    me.saldo_nuevo AS saldo_pendiente,

    me.fecha AS ultima_actualizacion

FROM movimientos_envases me

INNER JOIN clientes c
    ON c.id = me.cliente_id

INNER JOIN tipos_envase te
    ON te.id = me.tipo_envase_id

INNER JOIN (
    SELECT
        cliente_id,
        tipo_envase_id,
        MAX(id) AS ultimo_id
    FROM movimientos_envases
    GROUP BY
        cliente_id,
        tipo_envase_id
) ult
    ON ult.ultimo_id = me.id

WHERE me.saldo_nuevo > 0;


-- ------------------------------------------------------------
-- Stock actual
-- ------------------------------------------------------------

CREATE OR REPLACE VIEW vw_stock_productos AS
SELECT
    p.id,
    p.nombre,
    p.presentacion,
    c.nombre AS categoria,

    p.stock_actual,
    p.stock_minimo,

    CASE
        WHEN p.maneja_stock = FALSE
            THEN 'NO_APLICA'

        WHEN p.stock_actual <= 0
            THEN 'AGOTADO'

        WHEN p.stock_actual <= p.stock_minimo
            THEN 'STOCK_BAJO'

        ELSE 'DISPONIBLE'
    END AS estado_stock

FROM productos p

LEFT JOIN categorias c
    ON c.id = p.categoria_id

WHERE p.activo = TRUE;


-- ------------------------------------------------------------
-- Historial de ventas resumido
-- ------------------------------------------------------------

CREATE OR REPLACE VIEW vw_ventas_resumen AS
SELECT
    v.id AS venta_id,
    v.fecha,

    c.id AS cliente_id,
    COALESCE(c.nombre, 'Cliente ocasional') AS cliente,

    v.subtotal,
    v.descuento_promociones,
    v.descuento_manual,
    v.total,

    v.total_pagado,
    v.saldo_pendiente,

    v.estado_pago,
    v.estado,

    u.nombre AS registrado_por

FROM ventas v

LEFT JOIN clientes c
    ON c.id = v.cliente_id

INNER JOIN usuarios u
    ON u.id = v.usuario_id;


-- ------------------------------------------------------------
-- Consumo acumulado por cliente
-- ------------------------------------------------------------

CREATE OR REPLACE VIEW vw_clientes_consumo AS
SELECT
    c.id AS cliente_id,
    c.nombre,

    COUNT(v.id) AS cantidad_ventas,

    ROUND(
        COALESCE(SUM(v.total), 0),
        2
    ) AS total_consumido,

    ROUND(
        COALESCE(AVG(v.total), 0),
        2
    ) AS ticket_promedio,

    ROUND(
        COALESCE(SUM(v.saldo_pendiente), 0),
        2
    ) AS deuda_actual,

    MAX(v.fecha) AS ultima_compra

FROM clientes c

LEFT JOIN ventas v
    ON v.cliente_id = c.id
    AND v.estado = 'ACTIVA'

GROUP BY
    c.id,
    c.nombre;


-- ------------------------------------------------------------
-- Rentabilidad de las ventas
-- ------------------------------------------------------------

CREATE OR REPLACE VIEW vw_rentabilidad_ventas AS
SELECT
    v.id AS venta_id,
    v.fecha,

    ROUND(
        SUM(dv.costo_unitario * dv.cantidad),
        2
    ) AS costo_productos,

    v.total AS ingreso_venta,

    ROUND(
        v.total
        -
        SUM(dv.costo_unitario * dv.cantidad),
        2
    ) AS utilidad_bruta

FROM ventas v

INNER JOIN detalle_venta dv
    ON dv.venta_id = v.id

WHERE v.estado = 'ACTIVA'

GROUP BY
    v.id,
    v.fecha,
    v.total;


-- ------------------------------------------------------------
-- Costos de regalos / premios
-- ------------------------------------------------------------

CREATE OR REPLACE VIEW vw_costos_regalos AS
SELECT
    r.id AS regalo_id,
    r.fecha,
    r.tipo,
    r.descripcion,

    c.nombre AS cliente,

    ROUND(
        SUM(dr.costo_total),
        2
    ) AS costo_total

FROM regalos r

LEFT JOIN clientes c
    ON c.id = r.cliente_id

INNER JOIN detalle_regalo dr
    ON dr.regalo_id = r.id

WHERE r.estado = 'ACTIVO'

GROUP BY
    r.id,
    r.fecha,
    r.tipo,
    r.descripcion,
    c.nombre;


-- ============================================================
-- FIN DE LA ESTRUCTURA
-- ============================================================




-- DELIVERY
ALTER TABLE ventas
ADD COLUMN delivery DECIMAL(12,2) NOT NULL DEFAULT 0.00
AFTER descuento_manual,
ADD COLUMN total_manual DECIMAL(12,2) NULL
AFTER delivery;


CREATE TABLE gastos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id BIGINT UNSIGNED NOT NULL,

    tipo ENUM(
        'AGUA',
        'LUZ',
        'COMIDA',
        'INSUMOS',
        'DELIVERY',
        'ALQUILER',
        'OTRO'
    ) NOT NULL DEFAULT 'OTRO',

    descripcion VARCHAR(255) NOT NULL,

    monto DECIMAL(12,2) NOT NULL,

    fecha DATE NOT NULL,

    observacion TEXT NULL,

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_gastos_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_gastos_monto
        CHECK (monto > 0),

    INDEX idx_gastos_fecha (fecha),
    INDEX idx_gastos_tipo (tipo),
    INDEX idx_gastos_activo_fecha (activo, fecha)
);