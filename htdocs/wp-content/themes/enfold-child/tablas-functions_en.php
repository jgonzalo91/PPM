<?php 

function mostrar_tabla_sector_en() {
    $conn = new conexion();
    $conOb = $conn->conexionMysql();
	
    global $post;
	error_log("Post global después del reset: " . ($post ? $post->ID : 'No hay post'));
    $page_id = isset($post->ID) ? $post->ID : 0;
    $filtros_array = obtener_filtros_por_pagina_en($page_id);
	
	 //  DEBUG LOGS
    error_log("Página actual ID (EN): " . $page_id);
    error_log("Filtros encontrados (EN): " . print_r($filtros_array, true));
	
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
                'terms' => array(563,562),
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
	error_log("Proyectos encontrados (EN): " . $query->found_posts);
    if (!$query->have_posts()) {
        return '<p>No hay proyectos en el sector y subsector especificado.</p>';
    }
	
	// aqui es donde se agregan los sectores.
    $tabla_operacion = '';
    $tabla_otras = '';
    $tabla_megaproyecto='';
    // Etapas válidas según idioma
	//$idioma_actual='';
$etapas_validas = array('operation', 'execution', 'bidding', 'preinvestment');
	$nombres_ya_mostrados = array(); // Evitar duplicados por nombre
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
		
        $sector_id = get_post_meta($post_id, 'sector_proyecto', true);
        $subsector_id = get_post_meta($post_id, 'subsector_proyecto', true);
        $etapa_id = get_post_meta($post_id, 'etapa_proyecto', true);
        
		$idioma_actual = function_exists('pll_current_language') ? pll_current_language() : 'es';
		
		// Traducir sector
if ($idioma_actual === 'en' && function_exists('pll_get_post')) {
    $sector_traducido = pll_get_post($sector_id, 'en');
    $subsector_traducido = pll_get_post($subsector_id, 'en');
    $etapa_traducida = pll_get_post($etapa_id, 'en');
} else {
    $sector_traducido = $sector_id;
    $subsector_traducido = $subsector_id;
    $etapa_traducida = $etapa_id;
}
		
		/*$sector = $sector_id ? get_the_title($sector_id) : 'No Sector';
        $subsector = $subsector_id ? get_the_title($subsector_id) : 'No Subsector';
        $etapa = $etapa_id ? get_the_title($etapa_id) : 'No Stage';*/
		
		$sector = $sector_traducido ? get_the_title($sector_traducido) : 'No sector';
		$subsector = $subsector_traducido ? get_the_title($subsector_traducido) : 'No subsector';
		$etapa = $etapa_traducida ? get_the_title($etapa_traducida) : 'No stage';
		
		$etapa = trim($etapa);
       // $etapa_normalizada = strtolower(trim($etapa));
		$etapa_normalizada = mb_strtolower(trim($etapa), 'UTF-8');

		
		
		// Aqu agregas el log para ver qu trae cada proyecto
  // error_log("Proyecto ID $post_id - Etapa original: '$etapa' - Etapa normalizada: '$etapa_normalizada' - Sector: '$sector' - Subsector '$subsector'");
  

//$titulo_idioma = ($idioma_actual === 'en' && function_exists('pll_get_post')) ? pll_get_post($post_id, 'en') : $post_id;
//$titulo_proyecto = get_the_title($titulo_idioma);

//error_log("Proyecto ID $post_id - Nombre: '$titulo_proyecto' - Etapa original: '$etapa' - Etapa normalizada: '$etapa_normalizada' - Sector: '$sector' - Subsector: '$subsector'");


$titulo_idioma = ($idioma_actual === 'en' && function_exists('pll_get_post')) ? pll_get_post($post_id, 'en') : $post_id;
$titulo_proyecto_raw = get_the_title($titulo_idioma);
$titulo_proyecto = html_entity_decode($titulo_proyecto_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

error_log("🧪 Proyecto ID $post_id - Nombre: '$titulo_proyecto' - Etapa original: '$etapa' - Etapa normalizada: '$etapa_normalizada' - Sector: '$sector' - Subsector: '$subsector'");


if ($idioma_actual === 'en' && function_exists('pll_get_post')) {
    $translated_id = pll_get_post($post_id, 'en');
    if ($translated_id) {
        $titulo_proyecto_raw = get_the_title($translated_id);
    } else {
        // No existe traducción, usar original
        $titulo_proyecto_raw = get_the_title($post_id);
    }
} else {
    $titulo_proyecto_raw = get_the_title($post_id);
}

$titulo_proyecto = html_entity_decode($titulo_proyecto_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

error_log("Proyecto ID $post_id - Nombre: '$titulo_proyecto'");













 
//   error_log("🧪 Proyecto ID $post_id - Nombre: '" . get_the_title($post_id) . "' - Etapa original: '$etapa' - Etapa normalizada: '$etapa_normalizada' - Sector: '$sector' - Subsector: '$subsector'");


        if (!in_array($etapa_normalizada, $etapas_validas)) {
            continue;
        }
    
		
		// cambio de idioma de redes de alizanza y sostenibilidad
		$idioma_actual = function_exists('pll_current_language') ? pll_current_language() : 'en'; // asumimos inglés

$sos = $conOb->query("SELECT * FROM tbl_fichas_sostenibilidad WHERE id_datos_proyecto = " . intval($post_id));
$s = ($sos && $sos->num_rows > 0) ? "Yes" : "No";

$redes = $conOb->query("SELECT * FROM tbl_procura WHERE id_proyecto = " . intval($post_id));
$r_ = ($redes && $redes->num_rows > 0) ? "Yes" : "No";
		//cambio de idioma de redes de alizan y sostenibilidad

        $fila = '<tr>';
    
$post_id_actual = get_the_ID();
//$post_id= get_the_ID();
$idioma_actual = function_exists('pll_current_language') ? pll_current_language() : 'en';
if (function_exists('pll_get_post')) {
    $post_id_traducido = pll_get_post($post_id_actual, $idioma_actual);
    if ($post_id_traducido && $post_id_traducido != $post_id_actual) {
        $url_proyecto = get_permalink($post_id_traducido);
    } else {
        // Si no hay traduccin, y el idioma NO es espaol, forzar con ?language=xx
        $url_base = get_permalink($post_id_actual);
        $url_proyecto = ($idioma_actual === 'en') ? $url_base : add_query_arg('language', $idioma_actual, $url_base);
    }
} else {
    $url_proyecto = get_permalink($post_id_actual);
}
//$fila .= '<td><a href="' . esc_url($url_proyecto) . '" target="_blank" class="enlace-proyecto">' . get_the_title($post_id_actual) . '</a></td>';
//$nombre_proyecto = get_the_title($post_id_actual);
error_log("Log nombre proyecto español  para ID $nombre_proyecto");
//$nombre_proyecto = get_the_title($post_id);


// codigo de traduccion bien
$nombre_proyecto = get_the_title($post_id_actual);

// Si estamos en idioma inglés, buscar campo personalizado en inglés
if ($idioma_actual === 'en') {
    $nombre_proyecto_en = get_post_meta($post_id_actual, 'nombre_oficial_ingles', true);
    if (!empty($nombre_proyecto_en)) {
        error_log("nombre_oficial_ingles vía get_post_meta para proyecto ID $post_id_actual: '$nombre_proyecto_en'");
        $nombre_proyecto = $nombre_proyecto_en;
    } else {
        error_log("nombre_oficial_ingles no disponible para proyecto ID $post_id_actual");
    }
}


// Si estamos en idioma inglés, buscar campo personalizado 'nombre_oficial_ingles'
/*error_log('Idioma actual: ' . $idioma_actual);
if ($idioma_actual === 'en') {
   $stmt_nombre = $conOb->prepare("SELECT meta_value FROM proyectos_prod.wp_postmeta WHERE meta_key = 'nombre_oficial_ingles' AND post_id = ?");

    if ($stmt_nombre) {
        $stmt_nombre->bind_param("i", $post_id_actual);
		//$stmt_nombre->bind_param("i", $post_id);
        $stmt_nombre->execute();
        $stmt_nombre->store_result();
        $stmt_nombre->bind_result($nombre_en);
        if ($stmt_nombre->fetch() && !empty($nombre_en)) {
            $nombre_proyecto = $nombre_en;
        }
        $stmt_nombre->close();
    }
}*/

//$fila .= '<td><a href="' . esc_url($url_proyecto) . '" target="_blank" class="enlace-proyecto">' . esc_html($nombre_proyecto) . '</a></td>';
$fila .= '<td><a href="' . esc_url($url_proyecto . '?language=en') . '" target="_blank" class="enlace-proyecto">' . esc_html($nombre_proyecto) . '</a></td>';
error_log("Proyecto tabla ID ingles $post_id_actual: nombre='$nombre_proyecto', url='$url_proyecto'");



        $fila .= '<td>' . esc_html($sector) . '</td>';
        $fila .= '<td>' . esc_html($subsector) . '</td>';
        $fila .= '<td>' . esc_html($etapa) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($s) . '</td>';
        $fila .= '<td class="centrado">' . esc_html($r_) . '</td>';
        $fila .= '</tr>';



		
		
       if ($etapa_normalizada === 'operation') {
		 
            $tabla_operacion .= $fila;
			  error_log("Fila agregada a operación para proyecto ID $post_id");
			
        } else {
            $tabla_otras .= $fila;
			 error_log("Fila agregada a otras etapas para proyecto ID $post_id");
        }
		


		
    }
    wp_reset_postdata();
	error_log("wp_reset_postdata ejecutado.");
    wp_reset_query();
	error_log("wp_reset_query ejecutado.");
    $output = '';
    if (!$tabla_otras) {
		error_log("No hay proyectos en etapas otras.");
        $output .= '<p>No se encontraron proyectos en las etapas ejecución, licitación y preinversión(Nuevos).</p>';
	
    } else {
		error_log("Agregando tabla de proyectos nuevas.");
		 //$output .= '<p>se encontraron proyectos en las etapas ejecución, licitación y preinversión(Nuevos).</p>';
        $output .= '<button class="btn-acordeon-en" aria-expanded="false">+ New Projects</button>';
        $output .= '<div class="contenido-acordeon-en tabla-scroll" style="display:none;">';
        $output .= '<table class="tabla-etapa"><thead><tr>
            <th class="centrado">Project</th><th class="centrado">Sector</th><th class="centrado">Subsector</th>
            <th class="centrado">Stage</th><th class="centrado">Sustainability</th><th class="centrado">With Ally Networks</th>
        </tr></thead><tbody>' . $tabla_otras . '</tbody></table>';
        $output .= '</div>';
    }
    if (!$tabla_operacion) {
		  error_log("No hay proyectos en etapa operación.");
        $output .= '<p>No se encontraron proyectos en la etapa operación.</p>';
		
    } else {
		   error_log("Agregando tabla de proyectos en operación.");
		//$output .= '<p>se encontraron proyectos en la etapa operación.</p>';
        $output .= '<button class="btn-acordeon-en" aria-expanded="false">+ Projects in Operation</button>';
        $output .= '<div class="contenido-acordeon-en tabla-scroll" style="display:none;">';
        $output .= '<table class="tabla-etapa"><thead><tr>
            <th class="centrado">Project</th><th class="centrado">Sector</th><th class="centrado">Subsector</th>
            <th class="centrado">Stage</th><th class="centrado">Sustainability</th><th class="centrado">With Ally Networks</th>
        </tr></thead><tbody>' . $tabla_operacion . '</tbody></table>';
        $output .= '</div>';
    }
	
	// Obtener los megaproyectos explicitos de los filtros (IDs)
$ids_megaproyectos_extra = isset($filtros_array['megaproyectos']) ? $filtros_array['megaproyectos'] : array();
error_log('ids_megaproyectos_extra: ' . print_r($ids_megaproyectos_extra, true));

if (empty($ids_megaproyectos_extra)) {
    $output .= '<p>No hay megaproyectos.</p>';
} else {
    $output .= '<button class="btn-acordeon" aria-expanded="false">+ Strategic Projects</button>';
    $output .= '<div class="toggle_content invers-color" itemprop="text" style="display:none;">';
    $output .= '<ul>';

   foreach ($ids_megaproyectos_extra as $mega_id) {
    $post_mega = get_post($mega_id);
    if ($post_mega && $post_mega->post_status === 'publish') {
        // Obtener el título del megaproyecto
        $title = get_the_title($mega_id);

        // Si estamos en idioma inglés, buscar nombre oficial en inglés
        
if ($idioma_actual === 'en') {
    $nombre_mega_en = get_post_meta($mega_id, 'nombre_oficial_ingles', true);
    if (!empty($nombre_mega_en)) {
        error_log("nombre_oficial_ingles vía get_post_meta: '$nombre_mega_en'");
        $title = $nombre_mega_en;
    } else {
        error_log("nombre_oficial_ingles no disponible vía get_post_meta para post_id: $mega_id");
    }
}


        // Obtener URL y agregar language=en si aplica
        $href = get_permalink($mega_id);
        if ($idioma_actual === 'en') {
            $href = add_query_arg('language', 'en', $href);
        }

        $output .= '<li><a href="' . esc_url($href) . '" target="_blank">' . esc_html($title) . '</a></li>';
    }
}



    $output .= '</ul>';
    $output .= '</div>';
}
	
  error_log("Contenido generado (EN): " . substr(strip_tags($output), 0, 100));
    return $output;
	
}
add_shortcode('tabla_sector_en', 'mostrar_tabla_sector_en'); 
function obtener_filtros_por_pagina_en($page_id) {
    $filtropag = array(

		
		// ingles
		14369 => array('sector' => '1428', 'subsector' => '1443'), // transporte, aeropuertos
		14395 => array('sector' => '1426', 'subsector' => array('4057', '5360','4088','4118'), 'megaproyectos'  => array('136665')), // agua y medio ambiente
		14382 => array('sector' => '1428', 'subsector' => '4094'), // transporte, movilidad urbana
		14365 => array('sector' => '1428', 'subsector' => '1454', 'megaproyectos'  => array('134226')), // transporte, carreteras y puentes 134226
		14410 => array('sector' => '1425' , 'subsector' => array('4086','13720','16559','7392','38509','7685','6931','7391'),'megaproyectos'  => array('138385')), // Electricidad
		14374 => array('sector' => '1428', 'subsector' => '1445', 'megaproyectos'  => array('129130','128799','129903','128030')), // transportes , ferrocarriles
		14412 => array('sector' => '4037', 'subsector' => array('4084','4128')), // hidricarburos - 
		14378 => array('sector' => '1428', 'subsector' => '1444'), //transporte, puertos
		94795 => array ('sector' => '1426' , 'subsector' => '70363'), // agua y medio ambiente - residuos solidos
		14390 => array('sector' => '1423', 'subsector' => '12271'),
		
		
	
		
		
        // Agrega ms segn sea necesario
		
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
	-- Residuos S贸lidos (ID: 70363)
	
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

