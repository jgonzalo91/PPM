<?php 

function mostrar_tabla_sector() {
    $conn = new conexion();
    $conOb = $conn->conexionMysql();
    global $post;
    $page_id = isset($post->ID) ? $post->ID : 0;
    $filtros_array = obtener_filtros_por_pagina($page_id);
    if (!$page_id) {
        return '<p>No se encontró la página actual.</p>';
    }
    if (!$filtros_array) {
        return '<p>No se encontraron filtros configurados para esta página (ID: ' . $page_id . ').</p>';
    }
    $sector_filtro = isset($filtros_array['sector']) ? $filtros_array['sector'] : '';
    $subsector_filtro = isset($filtros_array['subsector']) ? $filtros_array['subsector'] : '';
    if (!$sector_filtro || !$subsector_filtro) {
        return '<p>Error: Falta sector o subsector en los filtros.</p>';
    }
    $args = array(
        'post_type' => 'proyecto_inversion',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'categoria_macroproyecto',
                'field' => 'term_id',
                'terms' => array(563),
                'operator' => 'IN',
            )
        ),
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'tipo_de_inversion',
                'value' => array('2259', '2261'),
                'compare' => 'IN'
            ),
            array(
                'key' => 'sector_proyecto',
                'value' => $sector_filtro,
                'compare' => '='
            ),
            array(
                'key' => 'subsector_proyecto',
                'value' => $subsector_filtro,
                'compare' => '='
            )
        )
    );
    $query = new WP_Query($args);
    if (!$query->have_posts()) {
        return '<p>No hay proyectos en el sector y subsector especificado.</p>';
    }
	
	// aqui es donde se agregan los sectores.
    $tabla_operacion = '';
    $tabla_otras = '';
    $tabla_megaproyecto='';
    $etapas_validas = array('operación', 'ejecución', 'licitación', 'preinversión');
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $sector_id = get_post_meta($post_id, 'sector_proyecto', true);
        $subsector_id = get_post_meta($post_id, 'subsector_proyecto', true);
        $etapa_id = get_post_meta($post_id, 'etapa_proyecto', true);
        $sector = $sector_id ? get_the_title($sector_id) : 'Sin Sector';
        $subsector = $subsector_id ? get_the_title($subsector_id) : 'Sin Subsector';
        $etapa = $etapa_id ? get_the_title($etapa_id) : 'Sin Etapa';
        $etapa_normalizada = strtolower(trim($etapa));
        if (!in_array($etapa_normalizada, $etapas_validas)) {
            continue;
        }
        $sos = $conOb->query("SELECT * FROM tbl_fichas_sostenibilidad WHERE id_datos_proyecto = " . intval($post_id));
        $s = ($sos && $sos->num_rows > 0) ? "Sí" : "No";
        $redes = $conOb->query("SELECT * FROM tbl_procura WHERE id_proyecto = " . intval($post_id));
        $r_ = ($redes && $redes->num_rows > 0) ? "Sí" : "No";
        $fila = '<tr>';
        $fila .= '<td><a href="' . get_permalink() . '" target="_blank" class="enlace-proyecto">' . get_the_title() . '</a></td>';
        $fila .= '<td>' . esc_html($sector) . '</td>';
        $fila .= '<td>' . esc_html($subsector) . '</td>';
        $fila .= '<td>' . esc_html($etapa) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($s) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($r_) . '</td>';
        $fila .= '</tr>';
        if ($etapa_normalizada === 'operación') {
            $tabla_operacion .= $fila;
        } else {
            $tabla_otras .= $fila;
        }
    }
    wp_reset_postdata();
    wp_reset_query();
    $output = '';
    if (!$tabla_otras) {
        //$output .= '<p>No se encontraron proyectos en las etapas Ejecución, Licitación y Preinversión(Nuevos).</p>';
    } else {
        $output .= '<button class="btn-acordeon" aria-expanded="false">+ Proyectos Nuevos</button>';
        $output .= '<div class="contenido-acordeon tabla-scroll" style="display:none;">';
        $output .= '<table class="tabla-etapa"><thead><tr>
            <th class="centrado">Proyecto</th><th class="centrado">Sector</th><th class="centrado">Subsector</th>
            <th class="centrado">Etapa</th><th class="centrado">Sostenibilidad</th><th class="centrado">Redes de Alianza</th>
        </tr></thead><tbody>' . $tabla_otras . '</tbody></table>';
        $output .= '</div>';
    }
    if (!$tabla_operacion) {
        $output .= '<p>No se encontraron proyectos en la etapa Operación.</p>';
    } else {
        $output .= '<button class="btn-acordeon" aria-expanded="false">+ Proyectos en Operación</button>';
        $output .= '<div class="contenido-acordeon tabla-scroll" style="display:none;">';
        $output .= '<table class="tabla-etapa"><thead><tr>
            <th class="centrado">Proyecto</th><th class="centrado">Sector</th><th class="centrado">Subsector</th>
            <th class="centrado">Etapa</th><th class="centrado">Sostenibilidad</th><th class="centrado">Redes de Alianza</th>
        </tr></thead><tbody>' . $tabla_operacion . '</tbody></table>';
        $output .= '</div>';
    }
  
    return $output;
}
add_shortcode('tabla_sector', 'mostrar_tabla_sector'); 
function obtener_filtros_por_pagina($page_id) {
    $filtropag = array(
        9386 => array('sector' => '1428', 'subsector' => '4094'),
        88706 => array('sector' => '1428', 'subsector' => '1443'),
        9377 => array('sector' => '1428', 'subsector' => '1444'),
        9383 => array('sector' => '1423', 'subsector' => '12271'),
        94774 => array('sector' => '1426', 'subsector' => '70363'),
        9354 => array('sector' => '1428', 'subsector' => '1454'),
	14365 => array('sector' => '1428', 'subsector' => '1454'),
        // Agrega más según sea necesario
    );
    return isset($filtropag[$page_id]) ? $filtropag[$page_id] : false;
}
function estilo_tablas_etapas() {
    echo '<style>
        .tabla-scroll {
            overflow-x: auto;
            width: 100%;
        }

	table.tabla-etapa { 
	margin-top: 25px; 
	}
        table.tabla-etapa {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        table.tabla-etapa tbody tr:nth-child(odd) {
            background-color: #ffffff; color: #000000;
        }
        table.tabla-etapa tbody tr:nth-child(odd) .enlace-proyecto {
            color: #000000;
        }
        table.tabla-etapa tbody tr:nth-child(even) {
            background-color: #008B8B; color: #ffffff;
        }
        table.tabla-etapa tbody tr:nth-child(even) .enlace-proyecto {
            color: #ffffff;
        }
        table.tabla-etapa thead th {
            background-color: #008B8B !important;
            color: white !important;
        }
        table.tabla-etapa td,
        table.tabla-etapa th {
            padding: 8px;
            border: 1px solid #ddd;
        }
        button.btn-acordeon {
            background-color: #008B8B; color: white; cursor: pointer;
            padding: 10px 15px; width: 100%; text-align: left;
            font-size: 16px; border: none; outline: none;
            margin-bottom: 5px; transition: background-color 0.3s ease;
        }
        button.btn-acordeon:hover {
            background-color: #006666;
        }
        .contenido-acordeon {
            background-color: #3b3e40;
            padding: 0 15px 15px 15px;
            border: 1px solid #008B8B;
            margin-bottom: 10px;
        }

	.centrado {
    text-align: center;
}
    </style>';
}
add_action('wp_head', 'estilo_tablas_etapas'); 
function script_acordeon_tablas() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var botones = document.querySelectorAll('button.btn-acordeon');
        for (var i = 0; i < botones.length; i++) {
            botones[i].addEventListener('click', function() {
                var contenido = this.nextElementSibling;
                var estaAbierto = this.getAttribute('aria-expanded') === 'true';
                if (estaAbierto) {
                    contenido.style.display = 'none';
                    this.setAttribute('aria-expanded', 'false');
                    this.textContent = '+ ' + this.textContent.substring(2);
                } else {
                    contenido.style.display = 'block';
                    this.setAttribute('aria-expanded', 'true');
                    this.textContent = '- ' + this.textContent.substring(2);
                }
            });
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'script_acordeon_tablas');
