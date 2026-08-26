USE don_pinguino;

-- ============================================================
-- LIMPIEZA DE DATOS DE PRUEBA
-- Conserva:
-- usuarios
-- categorias
-- tipos_envase
-- productos
-- combos
-- combo_componentes
-- promociones y reglas
-- proveedores (si son reales)
--
-- Elimina:
-- ventas
-- pagos
-- clientes
-- envases pendientes
-- compras
-- movimientos de stock
-- regalos
-- gastos
-- ============================================================


SET FOREIGN_KEY_CHECKS = 0;


-- ============================================================
-- 1. VENTAS Y PAGOS
-- ============================================================

TRUNCATE TABLE detalle_venta_regalos;

TRUNCATE TABLE venta_envases;

TRUNCATE TABLE pagos;

TRUNCATE TABLE detalle_venta;

TRUNCATE TABLE ventas;


-- ============================================================
-- 2. ENVASES
-- ============================================================

TRUNCATE TABLE movimientos_envases;


-- ============================================================
-- 3. COMPRAS
-- ============================================================

TRUNCATE TABLE detalle_compra;

TRUNCATE TABLE compras;


-- ============================================================
-- 4. KARDEX / MOVIMIENTOS DE STOCK
-- ============================================================

TRUNCATE TABLE movimientos_stock;


-- ============================================================
-- 5. REGALOS / PREMIOS / CORTESÍAS
-- ============================================================

TRUNCATE TABLE detalle_regalo;

TRUNCATE TABLE regalos;


-- ============================================================
-- 6. GASTOS
-- ============================================================

TRUNCATE TABLE gastos;


-- ============================================================
-- 7. CLIENTES
--
-- Ejecutar solo si TODOS los clientes actuales son de prueba.
-- Si ya tienes clientes reales, comenta esta línea.
-- ============================================================

TRUNCATE TABLE clientes;


-- ============================================================
-- 8. REINICIAR STOCK
--
-- IMPORTANTE:
-- Ejecuta esto solo si stock_actual contiene cantidades
-- generadas durante las pruebas.
--
-- Los combos normalmente tienen maneja_stock = 0,
-- por eso solo modificamos productos que manejan stock.
-- ============================================================

UPDATE productos
SET stock_actual = 0
WHERE maneja_stock = 1;


-- ============================================================
-- 9. REACTIVAR CLAVES FORÁNEAS
-- ============================================================

SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================
-- 10. COMPROBACIÓN
-- ============================================================

SELECT 'ventas' AS tabla, COUNT(*) AS registros
FROM ventas

UNION ALL

SELECT 'detalle_venta', COUNT(*)
FROM detalle_venta

UNION ALL

SELECT 'pagos', COUNT(*)
FROM pagos

UNION ALL

SELECT 'clientes', COUNT(*)
FROM clientes

UNION ALL

SELECT 'movimientos_envases', COUNT(*)
FROM movimientos_envases

UNION ALL

SELECT 'compras', COUNT(*)
FROM compras

UNION ALL

SELECT 'detalle_compra', COUNT(*)
FROM detalle_compra

UNION ALL

SELECT 'movimientos_stock', COUNT(*)
FROM movimientos_stock

UNION ALL

SELECT 'regalos', COUNT(*)
FROM regalos

UNION ALL

SELECT 'detalle_regalo', COUNT(*)
FROM detalle_regalo

UNION ALL

SELECT 'gastos', COUNT(*)
FROM gastos;