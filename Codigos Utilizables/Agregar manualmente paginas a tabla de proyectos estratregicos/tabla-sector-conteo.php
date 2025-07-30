<?php
/**
 * Plugin Name: Tabla Sector + Conteo Procura (Multiidioma)
 * Description: Muestra tablas y conteos de proyectos por sector/subsector con soporte para español e inglés.
 * Version: 1.0
 * Author: JESUS GONZALEZ LOPEZ
 */

defined('ABSPATH') or die('¡Sin acceso directo, por favor!');

// Función que se ejecuta al activar el plugin
function ocultar_tablas_manuales_activacion() {
    update_option('tabla_sector_conteo_activo', true);
}
register_activation_hook(__FILE__, 'ocultar_tablas_manuales_activacion');

// Función que se ejecuta al desactivar el plugin
function mostrar_tablas_manuales_desactivacion() {
    delete_option('tabla_sector_conteo_activo');
}
register_deactivation_hook(__FILE__, 'mostrar_tablas_manuales_desactivacion');

// Filtro para ocultar los shortcodes cuando el plugin está desactivado
function filtrar_contenido_shortcodes($content) {
    if (!get_option('tabla_sector_conteo_activo')) {
        // Array de patrones de shortcodes a buscar
        $shortcodes = array(
            '/\[tabla_sector_idioma[^\]]*\]/',
            '/\[conteo_procura_auto[^\]]*\]/'
        );
        
        // Reemplazar cada shortcode con una cadena vacía
        foreach ($shortcodes as $shortcode) {
            $content = preg_replace($shortcode, '', $content);
        }
    }
    return $content;
}
add_filter('the_content', 'filtrar_contenido_shortcodes', 999);
add_filter('widget_text_content', 'filtrar_contenido_shortcodes', 999);
add_filter('widget_text', 'filtrar_contenido_shortcodes', 999);
add_filter('widget_block_content', 'filtrar_contenido_shortcodes', 999);

// Agregar estilos CSS para ocultar las tablas manuales
function agregar_estilos_ocultar_tablas() {
    if (!get_option('tabla_sector_conteo_activo')) {
        return;
    }

    // Obtener el ID de la página actual
    $current_page_id = get_the_ID();
    if (!$current_page_id) {
        return;
    }

    // Detectar el idioma actual usando Polylang si está disponible
    $idioma = function_exists('pll_current_language') ? pll_current_language() : 'es';

    // Obtener los IDs de páginas según el idioma
    $paginas_permitidas = array();
    if ($idioma === 'en') {
        // IDs de páginas en inglés
        $paginas_permitidas = array(
            14369, 14395, 14382, 14365, 14410, 
            14374, 14412, 14378, 94795, 14390
        );
    } else {
        // IDs de páginas en español
        $paginas_permitidas = array(
            9386, 88706, 9377, 9383, 9367, 
            9354, 9357, 9363, 9370, 94774
        );
    }

    // Verificar si la página actual está en la lista de páginas permitidas
    if (in_array($current_page_id, $paginas_permitidas)) {
        echo '<style>
            .togglecontainer.el_after_av_textblock.el_before_av_textblock.enable_toggles {
                display: none !important;
            }
            
            /* Ocultar el párrafo introductorio y la lista de conteos manuales */
            .avia_textblock p:contains("pueden consultar sobre el sector"),
            .avia_textblock p:contains("can consult about the sector"),
            .avia_textblock ul:not(.conteo-automatico-lista):has(li:contains("proyectos")),
            .avia_textblock ul:not(.conteo-automatico-lista):has(li:contains("empresas")),
            .avia_textblock ul:not(.conteo-automatico-lista):has(li:contains("consorcios")),
            .avia_textblock ul:not(.conteo-automatico-lista):has(li:contains("Projects")),
            .avia_textblock ul:not(.conteo-automatico-lista):has(li:contains("Companies")),
            .avia_textblock ul:not(.conteo-automatico-lista):has(li:contains("Consortiums")) {
                display: none !important;
            }
            
            /* Estilos para el conteo automático */
            .resumen-conteo {
                display: block !important;
                margin-top: 20px !important;
            }
            .resumen-conteo .conteo-automatico-lista {
                display: block !important;
                list-style-type: disc !important;
                padding-left: 20px !important;
                margin: 0 !important;
            }
            .resumen-conteo .conteo-automatico-lista li {
                margin-bottom: 10px !important;
                color: black !important;
            }
        </style>
        <script>
        jQuery(document).ready(function($) {
            function ocultarConteosManuales() {
                // Ocultar la lista que contiene los conteos manuales
                $(".avia_textblock ul").each(function() {
                    var $ul = $(this);
                    var contieneConteos = false;
                    
                    // Verificar si la lista contiene elementos de conteo
                    $ul.find("li").each(function() {
                        var texto = $(this).text().trim();
                        if (texto.match(/\d+\s*(proyectos|empresas|consorcios|Projects|Companies|Consortiums)/i)) {
                            contieneConteos = true;
                            return false; // Salir del bucle each
                        }
                    });
                    
                    if (contieneConteos) {
                        // Ocultar la lista y el párrafo introductorio anterior
                        $ul.prev("p").hide();
                        $ul.hide();
                    }
                });
            }
            
            // Ejecutar cuando el documento esté listo
            ocultarConteosManuales();
            
            // Ejecutar después de cualquier actualización de AJAX
            $(document).ajaxComplete(function() {
                ocultarConteosManuales();
            });
        });
        </script>';
    }
}
add_action('wp_head', 'agregar_estilos_ocultar_tablas', 999);


// Función auxiliar para ordenar por número de proyecto
function ordenar_por_numero_proyecto($a, $b) {
    // Extraer números del inicio del título
    preg_match('/^(\d+)/', get_the_title($a), $matches_a);
    preg_match('/^(\d+)/', get_the_title($b), $matches_b);
    
    $num_a = isset($matches_a[1]) ? intval($matches_a[1]) : 0;
    $num_b = isset($matches_b[1]) ? intval($matches_b[1]) : 0;
    
    return $num_a - $num_b;
}

function mostrar_tabla_sector_idioma($idioma = 'es') {
	
	error_log("mostrar_tabla_sector_idioma iniciado con idioma: " . $idioma);
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
        'orderby'        => array(
            'date'       => 'DESC',
            'title'      => 'ASC'
        ),
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

    $proyectos_operacion = array();
    $proyectos_otros = array();

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

        // Almacenar los datos del proyecto en arrays separados
        $proyecto_data = array(
            'post_id' => $post_id,
            'titulo' => $obtener_titulo_proyecto($post_id),
            'sector' => $sector,
            'subsector' => $subsector,
            'etapa' => $etapa,
            'sostenibilidad' => $s,
            'redes' => $r_,
            'url' => $url_proyecto
        );
        
        if ($etapa_normalizada === ($idioma === 'en' ? 'operation' : 'operación')) {
            $proyectos_operacion[] = $proyecto_data;
        } else {
            $proyectos_otros[] = $proyecto_data;
        }
    }
    wp_reset_postdata();

    // Ordenar los arrays por el número del proyecto
    usort($proyectos_operacion, function($a, $b) {
        preg_match('/^(\d+)/', $a['titulo'], $matches_a);
        preg_match('/^(\d+)/', $b['titulo'], $matches_b);
        
        // Si solo uno tiene número, el que NO tiene número va primero
        if (!isset($matches_a[1]) && isset($matches_b[1])) return -1;
        if (isset($matches_a[1]) && !isset($matches_b[1])) return 1;
        
        // Si ambos tienen número, ordenar por número descendente
        if (isset($matches_a[1]) && isset($matches_b[1])) {
            return intval($matches_b[1]) - intval($matches_a[1]);
        }
        
        // Si ninguno tiene número, mantener el orden original
        return 0;
    });

    usort($proyectos_otros, function($a, $b) {
        preg_match('/^(\d+)/', $a['titulo'], $matches_a);
        preg_match('/^(\d+)/', $b['titulo'], $matches_b);
        
        // Si solo uno tiene número, el que NO tiene número va primero
        if (!isset($matches_a[1]) && isset($matches_b[1])) return -1;
        if (isset($matches_a[1]) && !isset($matches_b[1])) return 1;
        
        // Si ambos tienen número, ordenar por número descendente
        if (isset($matches_a[1]) && isset($matches_b[1])) {
            return intval($matches_b[1]) - intval($matches_a[1]);
        }
        
        // Si ninguno tiene número, mantener el orden original
        return 0;
    });

    // Generar las tablas HTML
    $tabla_operacion = '';
    foreach ($proyectos_operacion as $proyecto) {
        $fila = '<tr>';
        $fila .= '<td><a href="' . esc_url($proyecto['url']) . '" target="_blank" class="enlace-proyecto" style="text-decoration: underline !important;">' . esc_html($proyecto['titulo']) . '</a></td>';
        $fila .= '<td>' . esc_html($proyecto['sector']) . '</td>';
        $fila .= '<td>' . esc_html($proyecto['subsector']) . '</td>';
        $fila .= '<td>' . esc_html($proyecto['etapa']) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($proyecto['sostenibilidad']) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($proyecto['redes']) . '</td>';
        $fila .= '</tr>';
        $tabla_operacion .= $fila;
    }

    $tabla_otras = '';
    foreach ($proyectos_otros as $proyecto) {
        $fila = '<tr>';
        $fila .= '<td><a href="' . esc_url($proyecto['url']) . '" target="_blank" class="enlace-proyecto" style="text-decoration: underline !important;">' . esc_html($proyecto['titulo']) . '</a></td>';
        $fila .= '<td>' . esc_html($proyecto['sector']) . '</td>';
        $fila .= '<td>' . esc_html($proyecto['subsector']) . '</td>';
        $fila .= '<td>' . esc_html($proyecto['etapa']) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($proyecto['sostenibilidad']) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($proyecto['redes']) . '</td>';
        $fila .= '</tr>';
        $tabla_otras .= $fila;
    }

    // Construir salida HTML
    $output = '';

    // Tabla proyectos en otras etapas
	error_log("Idioma: $idioma - Contenido tabla_otras tiene " . strlen($tabla_otras) . " caracteres.");

    if (!empty($tabla_otras)) {
        if (!empty($tabla_otras)) {
            $titulo_otras = $idioma === 'en' ? 'New Projects' : 'Proyectos Nuevos';
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
            $titulo_operacion = $idioma === 'en' ? 'Projects in Operation' : 'Proyectos en Operación';
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

    // Proyectos Estratégicos (megaproyectos)
    if (!empty($query_megaproyectos->posts)) {
        $titulo_mega = $idioma === 'en' ? 'Strategic Projects' : 'Proyectos Estratégicos';
        $output .= '<button class="btn-acordeon" aria-expanded="false">' . $titulo_mega . '</button>';
        $output .= '<div class="toggle_content invers-color" itemprop="text" style="display:none; background-color: #3b3e40;">';
        $output .= '<ul style="color: ' . ($idioma === 'en' ? '#8bfbff' : '#ccc') . ';">';
        
        // Mostrar los megaproyectos
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
		
		// Obtener proyectos relacionados desde ACF (repeater)


// Obtener ID de la página actual

$page_id = get_the_ID();
error_log('DEBUG: ID de la página actual: ' . $page_id);


// Verifica si hay filas en el repeater "seleccionar_pagina" en esta página
if ($page_id && have_rows('seleccionar_pagina', $page_id)) {
    error_log('DEBUG: Sí hay filas en seleccionar_pagina para la página con ID: ' . $page_id);

    while (have_rows('seleccionar_pagina', $page_id)) {
        the_row();
        $relacionado = get_sub_field('paginas_relacionadas'); // tipo Page Link

        error_log('DEBUG: Valor bruto de paginas_relacionadas: ' . print_r($relacionado, true));

        if ($relacionado) {
            // Si Page Link devuelve una URL, conviértela a ID
            if (!is_numeric($relacionado)) {
                $relacionado_id = url_to_postid($relacionado);
                error_log('DEBUG: Convertido a ID desde URL: ' . $relacionado_id);
            } else {
                $relacionado_id = intval($relacionado);
                error_log('DEBUG: ID numérico directo: ' . $relacionado_id);
            }

            if ($relacionado_id) {
                // Obtener la traducción si estás en WPML
                if (function_exists('icl_object_id')) {
                    $relacionado_id = icl_object_id($relacionado_id, 'page', true, $idioma);
                    error_log('DEBUG: ID traducido con icl_object_id: ' . $relacionado_id);
                }

                $title = get_the_title($relacionado_id);
                $href = get_permalink($relacionado_id);
                
				
                if ($idioma === 'en') {
					$title = preg_replace('/^[^-]+-/', '', $title);
                    $href = add_query_arg('language', 'en', $href);
                }

                $output .= '<li style="list-style: disc inside; color:#8bfbff;">
                    <a href="' . esc_url($href) . '" target="_blank" style="text-decoration: underline !important; color:#8bfbff !important;">' . esc_html($title) . '</a>
                </li>';
                error_log("DEBUG: Agregado link a $title ($href)");
            } else {
                error_log('DEBUG: No se pudo obtener un ID válido de paginas_relacionadas: ' . print_r($relacionado, true));
            }
        } else {
            error_log('DEBUG: No hay valor en paginas_relacionadas');
        }
    }
} else {
    error_log('DEBUG: No hay filas en seleccionar_pagina para la página con ID: ' . $page_id);
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
            background-color: #ffffff; 
            color: #000000;
        }
        table.tabla-etapa tbody tr:nth-child(odd) .enlace-proyecto {
            color: #000000;
        }
        table.tabla-etapa tbody tr:nth-child(even) {
            background-color: #008B8B; 
            color: #ffffff;
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

        /* Estilos del acordeón cuando está cerrado */
        button.btn-acordeon {
            background-color: #FFFFFF;
            color: #008B8B;
            cursor: pointer;
            padding: 10px 15px;
            width: 100%;
            text-align: left;
            font-size: 16px;
            font-weight: bold !important;
            font-family: "Open Sans", sans-serif;
            border: 1px solid #008B8B;
            outline: none;
            margin-bottom: 0;
            transition: all 0.3s ease;
            position: relative;
            padding-left: 50px; /* Espacio para el cuadrado con el signo + */
            border-top-left-radius: 2px;
            border-top-right-radius: 2px;
        }

        /* Regla específica para un solo acordeón */
        button.btn-acordeon:only-of-type {
            border: 1px solid #008B8B !important;
        }

        /* Reglas para los bordes cuando hay dos acordeones */
        button.btn-acordeon:last-of-type:nth-last-of-type(1):nth-of-type(2) {
            border-top: none;
        }

        /* Reglas para los bordes cuando hay tres acordeones */
        button.btn-acordeon:first-of-type:nth-last-of-type(3) {
            border-bottom: none;
        }

        button.btn-acordeon:last-of-type:nth-last-of-type(1):not(:only-of-type) {
            border-top: none;
        }

        /* Pseudo-elemento para el cuadrado con el signo + */
        button.btn-acordeon::before {
            content: "+";
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            border: 1px solid #008B8B;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold !important;
            font-family: "Open Sans", sans-serif;
            background-color: #FFFFFF;
            color: #008B8B;
            transition: all 0.3s ease;
        }

        /* Cambio del signo + a - cuando está abierto */
        button.btn-acordeon[aria-expanded="true"]::before {
            content: "-";
            background-color: #008B8B;
            color: white;
            border-color: white;
            font-weight: bold !important;
        }

        /* Estilos del acordeón cuando está abierto */
        button.btn-acordeon[aria-expanded="true"] {
            background-color: #008B8B;
            color: white;
            border-color: #008B8B;
            border-radius: 4px 4px 0 0;
            font-weight: bold !important;
        }

        /* Hover del botón */
        button.btn-acordeon:hover {
            background-color: #008B8B;
            color: white;
            font-weight: bold !important;
        }

        /* Hover del cuadrado con el signo */
        button.btn-acordeon:hover::before {
            background-color: #008B8B;
            color: white;
            border-color: white;
        }

        .contenido-acordeon {
            background-color: #3b3e40;
            padding: 0 15px 15px 15px;
            border: 1px solid #008B8B;
            margin-bottom: 0;
            border-radius: 0 0 4px 4px;
            border-top: none;
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
            ? 'No filters found for this page (ID: ' . $page_id . ').' 
            : 'No se encontraron filtros configurados para esta página (ID: ' . $page_id . ').') . '</p>';
    }

    $sector = isset($filtros_array['sector']) ? intval($filtros_array['sector']) : 0;
    $subsector = isset($filtros_array['subsector']) ? $filtros_array['subsector'] : 0;

    // Construir consulta SQL según filtros
    $total_proyectos = 0;
    $total_empresas = 0;
    $total_consorcios = 0;

    if (!empty($sector) && empty($subsector)) {
        // Solo sector
        $sql_conteo = "
            SELECT
              COUNT(DISTINCT id_proyecto) AS total_proyectos,
              COUNT(DISTINCT id_empresa) AS total_empresas,
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
        $total_proyectos = intval($row['total_proyectos']);
        $total_empresas = intval($row['total_empresas']);
        $total_consorcios = intval($row['total_consorcios']);
    }

    // Textos según idioma
    $texto_proyectos = $idioma === 'en' ? 'Projects' : 'Proyectos';
    $texto_empresas = $idioma === 'en' ? 'Companies' : 'Empresas';
    $texto_consorcios = $idioma === 'en' ? 'Consortiums' : 'Consorcios';

    // Salida HTML
    $output = '<div class="resumen-conteo">';
    $output .= '<ul class="conteo-automatico-lista" style="list-style-type: disc; padding-left: 20px; margin: 0;">';
    
    // Solo mostrar los elementos que tengan un conteo mayor a 0
    if ($total_proyectos > 0) {
        $output .= "<li>" . number_format($total_proyectos) . " $texto_proyectos</li>";
    }
    if ($total_empresas > 0) {
        $output .= "<li>" . number_format($total_empresas) . " $texto_empresas</li>";
    }
    if ($total_consorcios > 0) {
        $output .= "<li>" . number_format($total_consorcios) . " $texto_consorcios</li>";
    }
    
    $output .= '</ul></div>';

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
					} else {
						contenido.style.display = 'block';
						this.setAttribute('aria-expanded', 'true');
					}
				});
			}
		});
		</script>
		<?php
	}
	add_action('wp_footer', 'script_acordeon_tablas');
