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
			return '<p>No se encontraron filtros configurados para esta página(ID: ' . $page_id . ').</p>';
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
				'value' => is_array($subsector_filtro) ? $subsector_filtro : array($subsector_filtro),
				'compare' => 'IN'
	)
			)
		);
		
		//Megaproyectos
		
		$args_megaproyectos = array(
    'post_type' => 'proyecto_inversion',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'fields' => 'ids',
    'tax_query' => array(
        array(
            'taxonomy' => 'categoria_macroproyecto',
            'field' => 'term_id',
            'terms' => array(562),
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
            'value' => is_array($subsector_filtro) ? $subsector_filtro : array($subsector_filtro),
            'compare' => 'IN'
        )
    )
);
		
		//megaproyectos
		
		$query = new WP_Query($args);
		$megaproyectos_query = new WP_Query($args_megaproyectos);
		
		if (!$query->have_posts()) {
			return '<p>No hay proyectos en el sector y subsector especificado.</p>';
			
		}
		
		
		
		// aqui es donde se agregan los sectores.
		$tabla_operacion = '';
		$tabla_otras = '';
		
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
		
			$etapa_normalizada = mb_strtolower(trim($etapa), 'UTF-8');
			
			if (!in_array($etapa_normalizada, $etapas_validas)) {
				continue;
			}
			$sos = $conOb->query("SELECT * FROM tbl_fichas_sostenibilidad WHERE id_datos_proyecto = " . intval($post_id));
			$s = ($sos && $sos->num_rows > 0) ? "Si" : "No";
			$redes = $conOb->query("SELECT * FROM tbl_procura WHERE id_proyecto = " . intval($post_id));
			$r_ = ($redes && $redes->num_rows > 0) ? "Si" : "No";
			$fila = '<tr>';
			$fila .= '<td><a href="' . get_permalink() . '" target="_blank" class="enlace-proyecto" style="text-decoration: underline !important;">' . get_the_title() . '</a></td>';
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
			//$output .= '<p>No se encontraron proyectos en la etapa Operación.</p>';
		} else {
		
			$output .= '<button class="btn-acordeon" aria-expanded="false">+ Proyectos en Operación</button>';
			$output .= '<div class="contenido-acordeon tabla-scroll" style="display:none;">';
			$output .= '<table class="tabla-etapa"><thead><tr>
				<th class="centrado">Proyecto</th><th class="centrado">Sector</th><th class="centrado">Subsector</th>
				<th class="centrado">Etapa</th><th class="centrado">Sostenibilidad</th><th class="centrado">Redes de Alianza</th>
			</tr></thead><tbody>' . $tabla_operacion . '</tbody></table>';
			$output .= '</div>';
		}

// Obtener los megaproyect de los filtros (IDs)
//bien $ids_megaproyectos_extra = isset($filtros_array['megaproyectos']) ? $filtros_array['megaproyectos'] : array();


// Obtener megaproyectos desde ACF (manual)
/*$repetidor = get_field('proyectos_por_pagina', $page_id);
$ids_megaproyectos_extra = array();

if ($repetidor && is_array($repetidor)) {
    foreach ($repetidor as $fila) {
        if (isset($fila['megaproyecto']) && is_object($fila['megaproyecto'])) {
            $ids_megaproyectos_extra[] = $fila['megaproyecto']->ID;
        }
    }
}*/
//$ids_megaproyectos_extra = $megaproyectos_query->posts;
$ids_megaproyectos_extra = $megaproyectos_query->posts;

if (empty($ids_megaproyectos_extra)) {
    //$output .= '<p>No hay megaproyectos.</p>';
} else {
	
	//$output .= '<p>Hay Megaproyectos.</p>';
    $output .= '<button class="btn-acordeon" aria-expanded="false">+ Proyectos Estratégicos</button>';
    $output .= '<div class="toggle_content invers-color" itemprop="text" style="display:none;">';
    $output .= '<ul>';

    foreach ($ids_megaproyectos_extra as $mega_id) {
        $post_mega = get_post($mega_id);
        if ($post_mega && $post_mega->post_status === 'publish') {
            $href = get_permalink($mega_id);
            $title = get_the_title($mega_id);
    


	
	$output .= "<li><a href=\"$href\" target=\"_blank\" style=\"text-decoration: underline !important;\">$title</a></li>";




        }
    }

    $output .= '</ul>';
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
