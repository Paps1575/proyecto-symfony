{% extends 'base.html.twig' %}

{% block body %}
{# Estilo para la animación de entrada suave y pulido visual #}
<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .btn-paginacion:hover { background-color: #f0f0f0 !important; }
    input:focus { border-color: #007bff !important; box-shadow: 0 0 0 2px rgba(0,123,255,0.1); }
</style>

<div style="max-width: 900px; margin: 20px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; font-family: sans-serif;">

    {# Encabezado minimalista #}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 style="color: #333; margin: 0; font-weight: 600;">Gestión de Usuarios</h2>
        <button id="btn-abrir-form" style="background: #007bff; color: white; border: none; padding: 10px 22px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s;">
            + Nueva Persona
        </button>
    </div>

    {# VISTA 1: TABLA (Consumida por API, ID oculto) #}
    <div id="vista-tabla" style="animation: fadeIn 0.4s;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                <tr style="border-bottom: 2px solid #f8f9fa; text-align: left; color: #999; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                    <th style="padding: 15px;">Nombre Completo</th>
                    <th style="padding: 15px;">Correo Electrónico</th>
                    <th style="padding: 15px; text-align: center;">Acciones</th>
                </tr>
                </thead>
                <tbody id="cuerpo-tabla" style="color: #555; font-size: 14px;">
                {# JS inyectará filas aquí #}
                </tbody>
            </table>
        </div>

        {# Contenedor de Paginación #}
        <div id="paginacion" style="display: flex; gap: 8px; justify-content: center; margin-top: 20px;"></div>
    </div>

    {# VISTA 2: FORMULARIO (Organizado y sin amontonar) #}
    <div id="vista-formulario" style="display: none; animation: fadeIn 0.4s;">
        <h3 style="color: #444; margin-bottom: 25px; border-left: 4px solid #007bff; padding-left: 15px;">Registrar Nueva Persona</h3>

        <form id="form-registro">
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 8px; color: #666; font-weight: bold; font-size: 13px;">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej. Cesar Cabrera"
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: 0.3s;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 8px; color: #666; font-weight: bold; font-size: 13px;">Email:</label>
                    <input type="email" id="email" name="email" required placeholder="usuario@correo.com"
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                </div>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 8px; color: #666; font-weight: bold; font-size: 13px;">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" required maxlength="10" placeholder="10 dígitos"
                           style="width: 48%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                <button type="submit" style="background: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s;">
                    Guardar Datos
                </button>
                <button type="button" id="btn-cancelar" style="background: #f8f9fa; color: #666; border: 1px solid #ddd; padding: 12px 30px; border-radius: 8px; cursor: pointer; transition: 0.3s;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const cuerpoTabla = document.getElementById('cuerpo-tabla');
    const contenedorPaginacion = document.getElementById('paginacion');
    const vistaTabla = document.getElementById('vista-tabla');
    const vistaFormulario = document.getElementById('vista-formulario');
    const btnAbrirForm = document.getElementById('btn-abrir-form');
    const btnCancelar = document.getElementById('btn-cancelar');
    const formRegistro = document.getElementById('form-registro');

    // --- INTERCAMBIO DE VISTAS (SPA Style) ---
    btnAbrirForm.onclick = () => {
        vistaTabla.style.display = 'none';
        btnAbrirForm.style.display = 'none';
        vistaFormulario.style.display = 'block';
    };

    const regresarATabla = () => {
        vistaFormulario.style.display = 'none';
        vistaTabla.style.display = 'block';
        btnAbrirForm.style.display = 'block';
        formRegistro.reset();
    };

    btnCancelar.onclick = regresarATabla;

    // --- CARGAR DATOS (GET) ---
    async function cargarPersonas(pagina = 1) {
        try {
            const response = await fetch(`/api/personas?page=${pagina}`);
            const data = await response.json();

            cuerpoTabla.innerHTML = '';

            data.items.forEach(persona => {
                const fila = document.createElement('tr');
                fila.style.borderBottom = '1px solid #f8f9fa';

                fila.innerHTML = `
                    <td style="padding: 15px; font-weight: 500;">${persona.nombre}</td>
                    <td style="padding: 15px; color: #777;">${persona.email}</td>
                    <td style="padding: 15px; text-align: center;">
                        <button onclick="eliminar(${persona.id})" style="background:none; border:none; color:#dc3545; cursor:pointer; font-weight:bold; font-size: 13px; transition: 0.2s;">Eliminar</button>
                    </td>
                `;
                cuerpoTabla.appendChild(fila);
            });

            actualizarPaginador(data.pages, data.current_page);
        } catch (error) {
            console.error("Error al cargar:", error);
        }
    }

    // --- GUARDAR DATOS (POST) ---
    formRegistro.onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(formRegistro);
        const datos = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/api/personas/nuevo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const result = await response.json();

            if (response.ok) {
                alert("¡Persona guardada con éxito!");
                regresarATabla();
                cargarPersonas();
            } else {
                alert("Error: " + (result.error || "No se pudo guardar"));
            }
        } catch (error) {
            console.error("Error en el POST:", error);
        }
    };

    // --- ELIMINAR REGISTRO (DELETE) ---
    async function eliminar(id) {
        if (!confirm("¿Seguro que quieres borrar este registro?")) return;

        try {
            const response = await fetch(`/api/personas/${id}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                cargarPersonas();
            } else {
                alert("Hubo un error al intentar borrar.");
            }
        } catch (error) {
            console.error("Error en el DELETE:", error);
        }
    }

    // --- LÓGICA DE PAGINACIÓN ---
    function actualizarPaginador(totalPaginas, paginaActual) {
        contenedorPaginacion.innerHTML = '';
        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.innerText = i;
            btn.className = 'btn-paginacion';
            btn.style.cssText = `
                padding: 8px 14px;
                border: 1px solid #ddd;
                cursor: pointer;
                border-radius: 6px;
                font-weight: bold;
                transition: 0.2s;
                background: ${i === paginaActual ? '#007bff' : 'white'};
                color: ${i === paginaActual ? 'white' : '#555'};
            `;
            btn.onclick = () => cargarPersonas(i);
            contenedorPaginacion.appendChild(btn);
        }
    }

    // Carga inicial al entrar
    cargarPersonas();
</script>
{% endblock %}
