document.addEventListener(
    'DOMContentLoaded',
    function () {

        /* ====================================================
           ESTADO
        ==================================================== */

        let carrito =
            JSON.parse(
                localStorage.getItem(
                    'don_pinguino_carrito'
                )
                || '[]'
            );


        let categoriaActual =
            0;


        /* ====================================================
           ELEMENTOS
        ==================================================== */

        const buscar =
            document.getElementById(
                'buscarProducto'
            );


        const productos =
            Array.from(
                document.querySelectorAll(
                    '.producto-catalogo'
                )
            );


        const contenidoCarrito =
            document.getElementById(
                'contenidoCarrito'
            );


        const cantidadCarrito =
            document.getElementById(
                'cantidadCarrito'
            );


        const totalCarrito =
            document.getElementById(
                'totalCarrito'
            );


        const botonesWhatsapp =
            document.querySelectorAll(
                '.btn-pedir-whatsapp'
            );


        const sinResultados =
            document.getElementById(
                'sinResultados'
            );


        const cantidadResultados =
            document.getElementById(
                'cantidadResultados'
            );


        /* ====================================================
           DINERO
        ==================================================== */

        function dinero(valor) {

            return new Intl.NumberFormat(
                'es-PE',
                {
                    style:
                        'currency',

                    currency:
                        'PEN'
                }
            ).format(
                Number(
                    valor || 0
                )
            );

        }


        /* ====================================================
           GUARDAR CARRITO
        ==================================================== */

        function guardarCarrito() {

            localStorage.setItem(
                'don_pinguino_carrito',
                JSON.stringify(
                    carrito
                )
            );

        }


        /* ====================================================
           AGREGAR
        ==================================================== */

        document
            .querySelectorAll(
                '.btn-agregar-carrito'
            )
            .forEach(
                function (btn) {

                    btn.addEventListener(
                        'click',
                        function () {

                            const id =
                                Number(
                                    this.dataset.id
                                );


                            const existente =
                                carrito.find(
                                    item =>
                                        item.id === id
                                );


                            if (existente) {

                                existente.cantidad++;

                            } else {

                                carrito.push({

                                    id:
                                        id,

                                    nombre:
                                        this.dataset.nombre,

                                    presentacion:
                                        this.dataset.presentacion,

                                    precio:
                                        Number(
                                            this.dataset.precio
                                        ),

                                    cantidad:
                                        1

                                });

                            }


                            guardarCarrito();

                            pintarCarrito();


                            const textoOriginal =
                                this.innerHTML;


                            this.innerHTML =
                                '<i class="fa-solid fa-check me-1"></i> Agregado';


                            setTimeout(
                                () => {

                                    this.innerHTML =
                                        textoOriginal;

                                },
                                700
                            );

                        }
                    );

                }
            );


        /* ====================================================
           MODIFICAR CANTIDAD
        ==================================================== */

        contenidoCarrito.addEventListener(
            'click',
            function (e) {

                const boton =
                    e.target.closest(
                        '[data-accion]'
                    );


                if (!boton) {
                    return;
                }


                const id =
                    Number(
                        boton.dataset.id
                    );


                const item =
                    carrito.find(
                        p =>
                            p.id === id
                    );


                if (!item) {
                    return;
                }


                const accion =
                    boton.dataset.accion;


                if (
                    accion === 'sumar'
                ) {

                    item.cantidad++;

                }


                if (
                    accion === 'restar'
                ) {

                    item.cantidad--;


                    if (
                        item.cantidad <= 0
                    ) {

                        carrito =
                            carrito.filter(
                                p =>
                                    p.id !== id
                            );

                    }

                }


                if (
                    accion === 'eliminar'
                ) {

                    carrito =
                        carrito.filter(
                            p =>
                                p.id !== id
                        );

                }


                guardarCarrito();

                pintarCarrito();

            }
        );


        /* ====================================================
           PINTAR CARRITO
        ==================================================== */

        function pintarCarrito() {

            contenidoCarrito.innerHTML =
                '';


            if (
                carrito.length === 0
            ) {

                contenidoCarrito.innerHTML = `

                    <div
                        class="
                            text-center
                            text-muted
                            py-5
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-cart-shopping
                                fa-3x
                                mb-3
                            "
                        ></i>

                        <div class="fw-semibold">
                            Tu carrito está vacío
                        </div>

                        <div class="small">
                            Agrega productos para comenzar.
                        </div>

                    </div>

                `;

            } else {

                carrito.forEach(
                    function (item) {

                        const subtotal =
                            item.precio
                            *
                            item.cantidad;


                        const div =
                            document.createElement(
                                'div'
                            );


                        div.className =
                            'border-bottom py-3';


                        div.innerHTML = `

                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    gap-3
                                "
                            >

                                <div class="flex-grow-1">

                                    <div class="fw-semibold">

                                        ${escaparHtml(
                                            item.nombre
                                        )}

                                    </div>


                                    <div class="small text-muted">

                                        ${escaparHtml(
                                            item.presentacion
                                            || ''
                                        )}

                                    </div>


                                    <div class="fw-bold mt-1">

                                        ${dinero(
                                            subtotal
                                        )}

                                    </div>

                                </div>


                                <button
                                    type="button"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-danger
                                        align-self-start
                                    "
                                    data-accion="eliminar"
                                    data-id="${item.id}"
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-trash
                                        "
                                    ></i>

                                </button>

                            </div>


                            <div
                                class="
                                    d-flex
                                    align-items-center
                                    gap-2
                                    mt-2
                                "
                            >

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-dark"
                                    data-accion="restar"
                                    data-id="${item.id}"
                                >
                                    -
                                </button>


                                <span
                                    class="
                                        fw-semibold
                                        text-center
                                    "
                                    style="min-width:30px;"
                                >
                                    ${item.cantidad}
                                </span>


                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-dark"
                                    data-accion="sumar"
                                    data-id="${item.id}"
                                >
                                    +
                                </button>

                            </div>

                        `;


                        contenidoCarrito.appendChild(
                            div
                        );

                    }
                );

            }


            const cantidad =
                carrito.reduce(
                    (
                        total,
                        item
                    ) =>
                        total
                        +
                        item.cantidad,
                    0
                );


            const total =
                carrito.reduce(
                    (
                        total,
                        item
                    ) =>
                        total
                        +
                        (
                            item.precio
                            *
                            item.cantidad
                        ),
                    0
                );


            cantidadCarrito.textContent =
                cantidad;


            totalCarrito.textContent =
                dinero(
                    total
                );


            botonesWhatsapp.forEach(
                function (btn) {

                    btn.disabled =
                        carrito.length === 0;

                }
            );

        }


        /* ====================================================
           FILTROS
        ==================================================== */

        function filtrarProductos() {

            const texto =
                buscar.value
                    .trim()
                    .toLowerCase();


            let visibles =
                0;


            productos.forEach(
                function (producto) {

                    const categoria =
                        Number(
                            producto.dataset.categoria
                            || 0
                        );


                    const busqueda =
                        (
                            producto.dataset.busqueda
                            || ''
                        )
                        .toLowerCase();


                    const coincideCategoria =
                        categoriaActual === 0
                        ||
                        categoria
                            === categoriaActual;


                    const coincideTexto =
                        texto === ''
                        ||
                        busqueda.includes(
                            texto
                        );


                    const visible =
                        coincideCategoria
                        &&
                        coincideTexto;


                    producto.classList.toggle(
                        'd-none',
                        !visible
                    );


                    if (visible) {
                        visibles++;
                    }

                }
            );


            sinResultados.classList.toggle(
                'd-none',
                visibles > 0
            );


            cantidadResultados.textContent =
                visibles
                +
                (
                    visibles === 1
                        ? ' producto'
                        : ' productos'
                );

        }


        buscar.addEventListener(
            'input',
            filtrarProductos
        );


        document
            .querySelectorAll(
                '.filtro-categoria'
            )
            .forEach(
                function (btn) {

                    btn.addEventListener(
                        'click',
                        function () {

                            categoriaActual =
                                Number(
                                    this.dataset.categoria
                                );


                            document
                                .querySelectorAll(
                                    '.filtro-categoria'
                                )
                                .forEach(
                                    b => {

                                        b.classList.remove(
                                            'btn-dark',
                                            'active'
                                        );


                                        b.classList.add(
                                            'btn-outline-dark'
                                        );

                                    }
                                );


                            this.classList.remove(
                                'btn-outline-dark'
                            );


                            this.classList.add(
                                'btn-dark',
                                'active'
                            );


                            filtrarProductos();

                        }
                    );

                }
            );


            /* ====================================================
            WHATSAPP
            ==================================================== */

            botonesWhatsapp.forEach(
                function (btn) {

                    btn.addEventListener(
                        'click',
                        function () {

                            if (
                                carrito.length === 0
                            ) {

                                return;
                            }


                            const opcion =
                                Number(
                                    this.dataset.whatsapp
                                );


                            let numero = '';


                            if (
                                opcion === 1
                            ) {

                                numero =
                                    window
                                        .CATALOGO_CONFIG
                                        .whatsapp1;

                            } else {

                                numero =
                                    window
                                        .CATALOGO_CONFIG
                                        .whatsapp2;

                            }


                            numero =
                                String(
                                    numero
                                    || ''
                                )
                                .replace(
                                    /\D/g,
                                    ''
                                );


                            if (!numero) {

                                alert(
                                    'El número de WhatsApp no está configurado.'
                                );

                                return;
                            }


                            /* ============================================
                            MENSAJE
                            ============================================ */

                            const lineas = [

                                'Hola Don Pingüino, quisiera realizar el siguiente pedido:',
                                ''

                            ];


                            carrito.forEach(
                                function (item) {

                                    const subtotal =
                                        item.cantidad
                                        *
                                        item.precio;


                                    let linea =
                                        '• '
                                        +
                                        item.cantidad
                                        +
                                        ' x '
                                        +
                                        item.nombre;


                                    if (
                                        item.presentacion
                                    ) {

                                        linea +=
                                            ' '
                                            +
                                            item.presentacion;

                                    }


                                    linea +=
                                        ' - '
                                        +
                                        dinero(
                                            subtotal
                                        );


                                    lineas.push(
                                        linea
                                    );

                                }
                            );


                            const total =
                                carrito.reduce(
                                    function (
                                        suma,
                                        item
                                    ) {

                                        return (
                                            suma
                                            +
                                            (
                                                item.cantidad
                                                *
                                                item.precio
                                            )
                                        );

                                    },
                                    0
                                );


                            lineas.push('');

                            lineas.push(
                                'Total referencial: '
                                +
                                dinero(
                                    total
                                )
                            );


                            lineas.push('');

                            lineas.push(
                                '¿Me confirman disponibilidad?'
                            );


                            const mensaje =
                                encodeURIComponent(
                                    lineas.join(
                                        '\n'
                                    )
                                );


                            const url =
                                'https://wa.me/'
                                +
                                numero
                                +
                                '?text='
                                +
                                mensaje;


                            window.open(
                                url,
                                '_blank',
                                'noopener'
                            );

                        }
                    );

                }
            );


        /* ====================================================
           SEGURIDAD HTML
        ==================================================== */

        function escaparHtml(
            texto
        ) {

            const div =
                document.createElement(
                    'div'
                );


            div.textContent =
                String(
                    texto ?? ''
                );


            return div.innerHTML;

        }


        /* ====================================================
           INICIAL
        ==================================================== */

        pintarCarrito();

        filtrarProductos();

    }
);