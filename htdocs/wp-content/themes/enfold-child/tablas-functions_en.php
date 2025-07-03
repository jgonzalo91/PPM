<?php 

function mostrar_tabla_sector_en() {
    $conn = new conexion();
    $conOb = $conn->conexionMysql();
	
    global $post;
	error_log("🧪 Post global después del reset: " . ($post ? $post->ID : 'No hay post'));
    $page_id = isset($post->ID) ? $post->ID : 0;
    $filtros_array = obtener_filtros_por_pagina_en($page_id);
	
	 // 🧪 DEBUG LOGS
    error_log("🧪 Página actual ID (EN): " . $page_id);
    error_log("🧪 Filtros encontrados (EN): " . print_r($filtros_array, true));
	
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
            /*array(
                'key' => 'subsector_proyecto',
                'value' => $subsector_filtro,
                'compare' => '='
            )*/
			array(
			'key' => 'subsector_proyecto',
			'value' => is_array($subsector_filtro) ? $subsector_filtro : array($subsector_filtro),
			'compare' => 'IN'
)
        )
    );
    $query = new WP_Query($args);
	error_log("🧪 Proyectos encontrados (EN): " . $query->found_posts);
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
		$etapa = trim($etapa);
       // $etapa_normalizada = strtolower(trim($etapa));
		$etapa_normalizada = mb_strtolower(trim($etapa), 'UTF-8');

		
		
		// Aquí agregas el log para ver qué trae cada proyecto
   error_log("🧪 Proyecto ID $post_id - Etapa original: '$etapa' - Etapa normalizada: '$etapa_normalizada'");

        if (!in_array($etapa_normalizada, $etapas_validas)) {
            continue;
        }
        $sos = $conOb->query("SELECT * FROM tbl_fichas_sostenibilidad WHERE id_datos_proyecto = " . intval($post_id));
        $s = ($sos && $sos->num_rows > 0) ? "Sí" : "No";
        $redes = $conOb->query("SELECT * FROM tbl_procura WHERE id_proyecto = " . intval($post_id));
        $r_ = ($redes && $redes->num_rows > 0) ? "Sí" : "No";
        $fila = '<tr>';
    
$post_id_actual = get_the_ID();
$idioma_actual = function_exists('pll_current_language') ? pll_current_language() : 'es';
if (function_exists('pll_get_post')) {
    $post_id_traducido = pll_get_post($post_id_actual, $idioma_actual);
    if ($post_id_traducido && $post_id_traducido != $post_id_actual) {
        $url_proyecto = get_permalink($post_id_traducido);
    } else {
        // Si no hay traducción, y el idioma NO es español, forzar con ?language=xx
        $url_base = get_permalink($post_id_actual);
        $url_proyecto = ($idioma_actual === 'es') ? $url_base : add_query_arg('language', $idioma_actual, $url_base);
    }
} else {
    $url_proyecto = get_permalink($post_id_actual);
}
$fila .= '<td><a href="' . esc_url($url_proyecto) . '" target="_blank" class="enlace-proyecto">' . get_the_title($post_id_actual) . '</a></td>';



        $fila .= '<td>' . esc_html($sector) . '</td>';
        $fila .= '<td>' . esc_html($subsector) . '</td>';
        $fila .= '<td>' . esc_html($etapa) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($s) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($r_) . '</td>';
        $fila .= '</tr>';



		
		
       if ($etapa_normalizada === 'operación') {
		 
            $tabla_operacion .= $fila;
			  error_log("🧪 Fila agregada a operación para proyecto ID $post_id");
			
        } else {
            $tabla_otras .= $fila;
			 error_log("🧪 Fila agregada a otras etapas para proyecto ID $post_id");
        }
		


		
    }
    wp_reset_postdata();
	error_log("🧪 wp_reset_postdata ejecutado.");
    wp_reset_query();
	error_log("🧪 wp_reset_query ejecutado.");
    $output = '';
    if (!$tabla_otras) {
		error_log("🧪 No hay proyectos en etapas otras.");
        $output .= '<p>No se encontraron proyectos en las etapas Ejecución, Licitación y Preinversión(Nuevos).</p>';
	
    } else {
		error_log("🧪 Agregando tabla de proyectos nuevas.");
		 //$output .= '<p>se encontraron proyectos en las etapas Ejecución, Licitación y Preinversión(Nuevos).</p>';
        $output .= '<button class="btn-acordeon-en" aria-expanded="false">+ New Projects</button>';
        $output .= '<div class="contenido-acordeon-en tabla-scroll" style="display:none;">';
        $output .= '<table class="tabla-etapa"><thead><tr>
            <th class="centrado">Project</th><th class="centrado">Sector</th><th class="centrado">Subsector</th>
            <th class="centrado">Stage</th><th class="centrado">Sustainability</th><th class="centrado">With Ally Networks</th>
        </tr></thead><tbody>' . $tabla_otras . '</tbody></table>';
        $output .= '</div>';
    }
    if (!$tabla_operacion) {
		  error_log("🧪 No hay proyectos en etapa operación.");
        $output .= '<p>No se encontraron proyectos en la etapa Operación.</p>';
		
    } else {
		   error_log("🧪 Agregando tabla de proyectos en operación.");
		//$output .= '<p>se encontraron proyectos en la etapa Operación.</p>';
        $output .= '<button class="btn-acordeon-en" aria-expanded="false">+ Projects in Operation</button>';
        $output .= '<div class="contenido-acordeon-en tabla-scroll" style="display:none;">';
        $output .= '<table class="tabla-etapa"><thead><tr>
            <th class="centrado">Project</th><th class="centrado">Sector</th><th class="centrado">Subsector</th>
            <th class="centrado">Stage</th><th class="centrado">Sustainability</th><th class="centrado">With Ally Networks</th>
        </tr></thead><tbody>' . $tabla_operacion . '</tbody></table>';
        $output .= '</div>';
    }
  error_log("🧪 Contenido generado (EN): " . substr(strip_tags($output), 0, 100));
    return $output;
	
}
add_shortcode('tabla_sector_en', 'mostrar_tabla_sector_en'); 
function obtener_filtros_por_pagina_en($page_id) {
    $filtropag = array(

		
		// ingles
		14369 => array('sector' => '1428', 'subsector' => '1443'), // transporte, aeropuertos
		14395 => array('sector' => '1426', 'subsector' => array('4057', '5360','4088','4118')), // agua y medio ambiente
		14382 => array('sector' => '1428', 'subsector' => '4094'), // transporte, movilidad urbana
		14365 => array('sector' => '1428', 'subsector' => '1454'), // transporte, carreteras y puentes
		14410 => array('sector' => '1425' , 'subsector' => array('4086','13720','16559','7392','38509','7685','6931','7391')), // Electricidad
		
		
        // Agrega más según sea necesario
		
				/* relaicones
		
		Transporte (ID: 1428)
Ferrocarriles (ID: 1445)
Carreteras / Puentes (ID: 1454)
Puertos (ID: 1444)
Aeropuertos (ID: 1443)
Movilidad Urbana (ID: 4094)
	Agua y Medio Ambiente (ID: 1426)
Abastecimiento de Agua (ID: 4057)
Otros (ID: 5360)
Saneamiento de Agua (ID: 4118)
Gestión de Agua (ID: 4088)
	-- Residuos Sólidos (ID: 70363)
	
	Industria (ID: 16472)
Industria (ID: 53731)
	Electricidad (ID: 1425)
Generación (ID: 4086)
Transmisión / Distribución (ID: 13720)
Energía Solar (ID: 16559)
Energía Eólica (ID: 7392)
Turbogas (ID: 38509)
Energía Geotérmica (ID: 7685)
Energía Térmica (ID: 6931)
Energía Hidráulica (ID: 7391)
	Inmobiliario y Turismo (ID: 4041)
Turismo (ID: 10503)
	Infraestructura Social (ID: 1424)
Cultura y Esparcimiento (ID: 4066)
Salud (ID: 1447)
Seguridad Pública y Justicia (ID: 4122)
Educación / Ciencia y Tecnología (ID: 4072)
	Hidrocarburos (ID: 4037)
Exploración/Producción (ID: 4084)
Transporte / Almacenamiento / Distribución (ID: 4128)
	Telecomunicaciones (ID: 1423)
Red de Telecomunicaciones (ID: 12271)
		
		*/
    );
    return isset($filtropag[$page_id]) ? $filtropag[$page_id] : false;
}
function estilo_tablas_etapas_en() {
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
        button.btn-acordeon-en {
            background-color: #008B8B; color: white; cursor: pointer;
            padding: 10px 15px; width: 100%; text-align: left;
            font-size: 16px; border: none; outline: none;
            margin-bottom: 5px; transition: background-color 0.3s ease;
        }
        button.btn-acordeon-en:hover {
            background-color: #006666;
        }
        .contenido-acordeon-en {
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
add_action('wp_head', 'estilo_tablas_etapas_en'); 
function script_acordeon_tablas_en() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
    var botones = document.querySelectorAll('button.btn-acordeon-en');
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
add_action('wp_footer', 'script_acordeon_tablas_en');
