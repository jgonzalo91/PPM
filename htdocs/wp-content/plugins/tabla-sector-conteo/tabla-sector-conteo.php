<?php
/**
 * Plugin Name: Tabla Sector + Conteo Procura (Multiidioma)
 * Description: Muestra tablas y conteos de proyectos por sector/subsector con soporte para español e inglés.
 * Version: 1.0
 * Author: JESUS GONZALEZ LOPEZ
 */

defined('ABSPATH') or die('¡Sin acceso directo, por favor!');


function mostrar_tabla_sector_idioma($idioma = 'es') {
	
	error_log("mostrar_tabla_sector_idioma iniciado con el idioma: " . $idioma);
    $conn = new conexion();
    $conOb = $conn->conexionMysql();

    global $post;
    $page_id = isset($post->ID) ? $post->ID : 0;
	 error_log("ID de página actual: " . $page_id);
    if (!$page_id) {
        return '<p>' . ($idioma === 'en' ? 'Page not found.' : 'No se encontró la página actual.') . '</p>';
    }

    // Obtener filtros según idioma
    if ($idioma === 'en') {
        $filtros_array = obtener_filtros_por_pagina_en($page_id);
    } else {
        $filtros_array = obtener_filtros_por_pagina($page_id);
    }
	 error_log("Filtros obtenidos: " . print_r($filtros_array, true));
    if (!$filtros_array) {
        return '<p>' . ($idioma === 'en'
            ? 'No filters found for this page (ID: ' . $page_id . ').'
            : 'No se encontraron filtros configurados para esta página (ID: ' . $page_id . ').') . '</p>';
    }

    // Cambiado para compatibilidad con PHP 5.4 (sin ??)
    $sector_filtro = isset($filtros_array['sector']) ? $filtros_array['sector'] : '';
    $subsector_filtro = isset($filtros_array['subsector']) ? $filtros_array['subsector'] : '';
    if (!$sector_filtro || !$subsector_filtro) {
        return '<p>' . ($idioma === 'en'
            ? 'Error: Missing sector or subsector filters.'
            : 'Error: Falta sector o subsector en los filtros.') . '</p>';
    }

    // Args comunes para WP_Query proyectos
    $args = array(
        'post_type'      => 'proyecto_inversion',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'categoria_macroproyecto',
                'field'    => 'term_id',
                'terms'    => array(563), // para proyectos regulares
                'operator' => 'IN',
            ),
        ),
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'tipo_de_inversion',
                'value'   => array('2259', '2261'),
                'compare' => 'IN',
            ),
            array(
                'key'     => 'sector_proyecto',
                'value'   => $sector_filtro,
                'compare' => '=',
            ),
            array(
                'key'     => 'subsector_proyecto',
                'value'   => is_array($subsector_filtro) ? $subsector_filtro : array($subsector_filtro),
                'compare' => 'IN',
            ),
        ),
    );

    // Args para megaproyectos: distinto term_id = 562 + solo IDs para optimizar
    $args_megaproyectos = $args;
    $args_megaproyectos['tax_query'][0]['terms'] = array(562);
    $args_megaproyectos['fields'] = 'ids';

    $query_proyectos = new WP_Query($args);
    $query_megaproyectos = new WP_Query($args_megaproyectos);

    if (!$query_proyectos->have_posts() && empty($query_megaproyectos->posts)) {
        return '<p>' . ($idioma === 'en' ? 'No projects found.' : 'No se encontraron proyectos.') . '</p>';
    }

    // Función auxiliar para traducir y obtener título
    $obtener_titulo_proyecto = function($post_id) use ($idioma) {
        if ($idioma === 'en' && function_exists('pll_get_post')) {
            $id_traducido = pll_get_post($post_id, 'en');
            if ($id_traducido) {
                $titulo = get_the_title($id_traducido);
            } else {
                $titulo = get_the_title($post_id);
            }
            // Intentar campo nombre oficial en inglés
            $nombre_oficial_en = get_post_meta($post_id, 'nombre_oficial_ingles', true);
            if (!empty($nombre_oficial_en)) {
                $titulo = $nombre_oficial_en;
            }
        } else {
            $titulo = get_the_title($post_id);
        }
        return html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    };

    // Valid stages según idioma
    $etapas_validas = $idioma === 'en'
        ? array('operation', 'execution', 'bidding', 'preinvestment')
        : array('operación', 'ejecución', 'licitación', 'preinversión'); // ajusta si necesario

    // Variables tablas por etapa
    $tabla_operacion = '';
    $tabla_otras = '';

    while ($query_proyectos->have_posts()) {
        $query_proyectos->the_post();
        $post_id = get_the_ID();

 error_log("Procesando proyecto ID: $post_id");

        $sector_id = get_post_meta($post_id, 'sector_proyecto', true);
        $subsector_id = get_post_meta($post_id, 'subsector_proyecto', true);
        $etapa_id = get_post_meta($post_id, 'etapa_proyecto', true);

error_log("sector_id: $sector_id, subsector_id: $subsector_id, etapa_id: $etapa_id");
        // Obtener títulos para taxonomías traducidas (si aplica)
        $sector = $sector_id;
        $subsector = $subsector_id;
        $etapa = $etapa_id;

        if ($idioma === 'en' && function_exists('pll_get_post')) {
            $sector = get_the_title(pll_get_post($sector_id, 'en')) ?: get_the_title($sector_id);
            $subsector = get_the_title(pll_get_post($subsector_id, 'en')) ?: get_the_title($subsector_id);
            $etapa = get_the_title(pll_get_post($etapa_id, 'en')) ?: get_the_title($etapa_id);
        } else {
            $sector = get_the_title($sector_id);
            $subsector = get_the_title($subsector_id);
            $etapa = get_the_title($etapa_id);
        }

        $etapa_normalizada = mb_strtolower(trim($etapa), 'UTF-8');
		
		 error_log("etapa normalizada: $etapa_normalizada");

        // Validar etapa
        if (!in_array($etapa_normalizada, $etapas_validas)) {
			error_log("Proyecto ID $post_id ignorado por etapa inválida: $etapa_normalizada");
            continue;
        }
		error_log("Proyecto ID $post_id agregado a tabla.");

        // Consultas para sostenibilidad y redes de alianza
        $sos = $conOb->query("SELECT 1 FROM tbl_fichas_sostenibilidad WHERE id_datos_proyecto = " . intval($post_id));
        $s = ($sos && $sos->num_rows > 0) ? ($idioma === 'en' ? "Yes" : "Sí") : ($idioma === 'en' ? "No" : "No");

        $redes = $conOb->query("SELECT 1 FROM tbl_procura WHERE id_proyecto = " . intval($post_id));
        $r_ = ($redes && $redes->num_rows > 0) ? ($idioma === 'en' ? "Yes" : "Sí") : ($idioma === 'en' ? "No" : "No");

        // URL proyecto con idioma
        $idioma_actual = $idioma;
        $url_proyecto = get_permalink($post_id);
        if ($idioma_actual === 'en') {
            $url_proyecto = add_query_arg('language', 'en', $url_proyecto);
        }

        $titulo_proyecto = $obtener_titulo_proyecto($post_id);

        $fila = '<tr>';
        $fila .= '<td><a href="' . esc_url($url_proyecto) . '" target="_blank" class="enlace-proyecto" style="text-decoration: underline !important;">' . esc_html($titulo_proyecto) . '</a></td>';
        $fila .= '<td>' . esc_html($sector) . '</td>';
        $fila .= '<td>' . esc_html($subsector) . '</td>';
        $fila .= '<td>' . esc_html($etapa) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($s) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($r_) . '</td>';
        $fila .= '</tr>';

        if ($etapa_normalizada === ($idioma === 'en' ? 'operation' : 'operación')) {
            $tabla_operacion .= $fila;
        } else {
            $tabla_otras .= $fila;
        }
    }
    wp_reset_postdata();

    // Construir salida HTML
    $output = '';

    // Tabla proyectos en otras etapas
	error_log("Idioma: $idioma - Contenido tabla_otras tiene " . strlen($tabla_otras) . " caracteres.");

    if (!empty($tabla_otras)) {
        if (!empty($tabla_otras)) {
    $titulo_otras = $idioma === 'en' ? '+ New Projects' : '+ Proyectos Nuevos';
    $output .= '<button class="btn-acordeon" aria-expanded="false">' . $titulo_otras . '</button>';
    $output .= '<div class="contenido-acordeon tabla-scroll" style="display:none;">';
    $output .= '<table class="tabla-etapa"><thead><tr>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'Project' : 'Proyecto') . '</th>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'Sector' : 'Sector') . '</th>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'Subsector' : 'Subsector') . '</th>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'Stage' : 'Etapa') . '</th>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'Sustainability' : 'Sostenibilidad') . '</th>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'With Ally Networks' : 'Con Redes de Alianza') . '</th>';
    $output .= '</tr></thead><tbody>' . $tabla_otras . '</tbody></table>';
    $output .= '</div>';
}

    }

    // Tabla proyectos en operación
	error_log("Idioma: $idioma - Contenido tabla_operacion tiene " . strlen($tabla_operacion) . " caracteres.");
    if (!empty($tabla_operacion)) {
        if (!empty($tabla_operacion)) {
    $titulo_operacion = $idioma === 'en' ? '+ Projects in Operation' : '+ Proyectos en Operación';
    $output .= '<button class="btn-acordeon" aria-expanded="false">' . $titulo_operacion . '</button>';
    $output .= '<div class="contenido-acordeon tabla-scroll" style="display:none;">';
    $output .= '<table class="tabla-etapa"><thead><tr>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'Project' : 'Proyecto') . '</th>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'Sector' : 'Sector') . '</th>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'Subsector' : 'Subsector') . '</th>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'Stage' : 'Etapa') . '</th>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'Sustainability' : 'Sostenibilidad') . '</th>';
    $output .= '<th class="centrado">' . ($idioma === 'en' ? 'With Ally Networks' : 'Con Redes de Alianza') . '</th>';
    $output .= '</tr></thead><tbody>' . $tabla_operacion . '</tbody></table>';
    $output .= '</div>';
}

    }

    // Megaproyectos
	error_log("Idioma: $idioma - Número de megaproyectos: " . count($query_megaproyectos->posts));
    if (!empty($query_megaproyectos->posts)) {
        $titulo_mega = $idioma === 'en' ? '+ Strategic Projects' : '+ Proyectos Estratégicos';
        //$output .= '<button class="btn-acordeon-' . $idioma . '" aria-expanded="false">' . $titulo_mega . '</button>';
		$output .= '<button class="btn-acordeon" aria-expanded="false">' . $titulo_mega . '</button>';
        //$output .= '<div class="contenido-acordeon-' . $idioma . ' invers-color tabla-scroll" style="display:none; background-color: #3b3e40;">';
		$output .= '<div class="toggle_content invers-color" itemprop="text" style="display:none; background-color: #3b3e40;">';
        $output .= '<ul style="color: ' . ($idioma === 'en' ? '#8bfbff' : '#ccc') . ';">';
        foreach ($query_megaproyectos->posts as $mega_id) {
            $title = $obtener_titulo_proyecto($mega_id);
            $href = get_permalink($mega_id);
            if ($idioma === 'en') {
                $href = add_query_arg('language', 'en', $href);
            }
            $output .= '<li style="list-style: disc inside; color:#8bfbff;">
                
				<a href="' . esc_url($href) . '" target="_blank" style="text-decoration: underline !important; color:#8bfbff !important;">' . esc_html($title) . '</a>
            </li>';
        }
        $output .= '</ul></div>';
    }

    return $output;
}



function shortcode_tabla_sector_idioma($atts) {
    $atts = shortcode_atts(
        ['idioma' => 'es'], // valor por defecto
        $atts,
        'tabla_sector_idioma'
    );

    return mostrar_tabla_sector_idioma($atts['idioma']);
}
add_shortcode('tabla_sector_idioma', 'shortcode_tabla_sector_idioma');

// Funciones para obtener filtros según página e idioma

function obtener_filtros_por_pagina_en($page_id) {
    $filtropag = array(
        // ingles
        14369 => array('sector' => '1428', 'subsector' => '1443'), // transporte, aeropuertos
        14395 => array('sector' => '1426', 'subsector' => array('4057', '5360','4088','4118')), // agua y medio ambiente
        14382 => array('sector' => '1428', 'subsector' => '4094'), // transporte, movilidad urbana
        14365 => array('sector' => '1428', 'subsector' => '1454'), // transporte, carreteras y puentes
        14410 => array('sector' => '1425' , 'subsector' => array('4086','13720','16559','7392','38509','7685','6931','7391')), // electricidad
        14374 => array('sector' => '1428', 'subsector' => '1445'), // transportes , ferrocarriles
        14412 => array('sector' => '4037', 'subsector' => array('4084','4128')), // hidrocarburos
        14378 => array('sector' => '1428', 'subsector' => '1444'), // transporte, puertos
        94795 => array ('sector' => '1426' , 'subsector' => '70363'), // agua y medio ambiente - residuos sólidos
        14390 => array('sector' => '1423', 'subsector' => '12271'),
    );
    return isset($filtropag[$page_id]) ? $filtropag[$page_id] : false;
}

function obtener_filtros_por_pagina($page_id) {
    $filtropag = array(
        9386 => array('sector' => '1428', 'subsector' => '4094'), 
        88706 => array('sector' => '1428', 'subsector' => '1443'),
        9377 => array('sector' => '1428', 'subsector' => '1444'), 
        9383 => array('sector' => '1423', 'subsector' => '12271'), 
        9367 => array('sector' => '1426', 'subsector' => array('4057', '5360','4088','4118')), 
        9354 => array('sector' => '1428', 'subsector' => '1454'), 
        9357 => array('sector' => '1425' , 'subsector' => array('4086','13720','16559','7392','38509','7685','6931','7391')), 
        9363 => array('sector' => '1428', 'subsector' => '1445'), 
        9370 => array('sector' => '4037', 'subsector' => array('4084','4128')),
        94774 => array ('sector' => '1426' , 'subsector' => '70363'), 
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


	function conteo_procura_auto_idioma($atts) {
    // Extraer atributos con valor por defecto 'es'
    $atts = shortcode_atts(['idioma' => 'es'], $atts);

    // Validar y limpiar idioma: si viene array o no válido, usar 'es'
    $idioma = $atts['idioma'];
    if (is_array($idioma)) {
        $idioma = 'es';
    } else {
        $idioma = sanitize_text_field($idioma);
        if (!in_array($idioma, ['es', 'en'])) {
            $idioma = 'es';
        }
    }

    $conn = new conexion();
    $conOb = $conn->conexionMysql();

    global $post;
    $page_id = isset($post->ID) ? $post->ID : 0;
    if (!$page_id) {
        return '<p>' . ($idioma === 'en' ? 'Page not found.' : 'No se encontró la página actual.') . '</p>';
    }

    // Obtener filtros según idioma
    if ($idioma === 'en') {
        $filtros_array = obtener_filtros_por_pagina_en($page_id);
    } else {
        $filtros_array = obtener_filtros_por_pagina($page_id);
    }
    if (!$filtros_array) {
        return '<p>' . ($idioma === 'en'
            ? 'No filters found for this page (ID: ' . $page_id . ').' : 'No se encontraron filtros configurados para esta página (ID: ' . $page_id . ').') . '</p>';
    }

    $sector = isset($filtros_array['sector']) ? intval($filtros_array['sector']) : 0;
    $subsector = isset($filtros_array['subsector']) ? $filtros_array['subsector'] : 0;

    // Construir consulta SQL según filtros
    $total_proyectos = 0;
    $total_empresas = 0;
    $total_consorcios = 0;
	
	error_log("🧪 Evaluando condiciones de filtros:");
error_log("➡️ Sector: " . var_export($sector, true));
error_log("➡️ Subsector: " . var_export($subsector, true));

if (!empty($sector) && empty($subsector)) {
    error_log("✅ Condición: Solo sector (subsector vacío)");
} elseif (!empty($sector) && !empty($subsector)) {
    error_log("✅ Condición: Sector y subsector");
} else {
    error_log("✅ Condición: Sin sector o sin filtros (nacional)");
}

    if (!empty($sector) && empty($subsector)) {
        // Solo sector
        $sql_conteo = "
            SELECT
              COUNT(DISTINCT id_proyecto) AS total_proyectos,
              COUNT(DISTINCT id_empresa ) AS total_empresas
              COUNT(DISTINCT CASE WHEN tipo_participante = 'Consorcio' THEN id_propuesta END) AS total_consorcios
            FROM tbl_procura
            WHERE id_sector = $sector
        ";

    } elseif (!empty($sector) && !empty($subsector)) {
        // Sector y subsector (único o múltiple)
		
        if (is_array($subsector)) {
            $ids = array_map('intval', $subsector);
            $where_subsector = "id_subsector IN (" . implode(",", $ids) . ")";
        } else {
            $where_subsector = "id_subsector = " . intval($subsector);
        }

        $sql_conteo = "
            SELECT
              COUNT(DISTINCT id_proyecto) AS total_proyectos,
              COUNT(DISTINCT id_empresa) AS total_empresas,
              COUNT(DISTINCT CASE WHEN tipo_participante = 'Consorcio' THEN id_propuesta END) AS total_consorcios
            FROM tbl_procura
            WHERE $where_subsector
        ";

    } else {
        // Nacional o sin filtros
        $sql_conteo = "
            SELECT
              COUNT(DISTINCT id_proyecto) AS total_proyectos,
              COUNT(DISTINCT id_empresa) AS total_empresas,
              COUNT(DISTINCT CASE WHEN tipo_participante = 'Consorcio' THEN id_propuesta END) AS total_consorcios
            FROM tbl_procura
        ";
    }


    $res_conteo = $conOb->query($sql_conteo);
    if ($res_conteo && $res_conteo->num_rows > 0) {
        $row = $res_conteo->fetch_assoc();
        $total_proyectos = $row['total_proyectos'];
        $total_empresas = $row['total_empresas'];
        $total_consorcios = $row['total_consorcios'];
    } else {
        $total_proyectos = $total_empresas = $total_consorcios = 0;
    }

    // Textos según idioma
    $texto_proyectos = $idioma === 'en' ? 'Projects' : 'Proyectos';
    $texto_empresas = $idioma === 'en' ? 'Companies' : 'Empresas';
    $texto_consorcios = $idioma === 'en' ? 'Consortiums' : 'Consorcios';

    // Salida HTML
    $output = '<div class="resumen-conteo">';
    $output .= "<p><strong>$texto_proyectos:</strong> " . number_format($total_proyectos) . '</p>';
    $output .= "<p><strong>$texto_empresas:</strong> " . number_format($total_empresas) . '</p>';
    $output .= "<p><strong>$texto_consorcios:</strong> " . number_format($total_consorcios) . '</p>';
    $output .= '</div>';

    return $output;
}
add_shortcode('conteo_procura_auto', 'conteo_procura_auto_idioma');


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