<?php

//* Capability específica (no la damos por rol) === */
/*if (!defined('PMX_CAP_PROY_NUEVOS')) {
  define('PMX_CAP_PROY_NUEVOS', 'pmx_manage_proyectos_nuevos');
}*/

/* Define aquí los usuarios permitidos (IDs o correos) ===
   Ejemplos: 1 (ID), 'admin@tu-dominio.mx' (email)
*/
/*if (!function_exists('pmx_allowed_user_ids')) {
  function pmx_allowed_user_ids(){
    $raw = array(
      28, //valeria
	  33, //ramiro
	
    );

    $ids = array();
    foreach ($raw as $v) {
      if (is_numeric($v)) {
        $ids[] = (int)$v;
      } elseif (is_string($v) && strpos($v, '@') !== false) {
        $u = get_user_by('email', $v);
        if ($u) $ids[] = (int)$u->ID;
      }
    }
    $ids = array_values(array_unique(array_filter($ids)));
    return apply_filters('pmx_allowed_user_ids', $ids);
  }
}*/

/* Sincroniza la capability SOLO para el usuario actual ===
   (antes de admin_menu, para que WP oculte/permita el menú correctamente)
*/
/*add_action('admin_init', function () {
  if (!is_user_logged_in()) return;
  $uid     = get_current_user_id();
  $allowed = in_array($uid, pmx_allowed_user_ids(), true);
  $u       = wp_get_current_user();

  if ($allowed) {
    if (!$u->has_cap(PMX_CAP_PROY_NUEVOS)) $u->add_cap(PMX_CAP_PROY_NUEVOS);
  } else {
    if ($u->has_cap(PMX_CAP_PROY_NUEVOS)) $u->remove_cap(PMX_CAP_PROY_NUEVOS);
  }
});*/



function users_notification_setup_menu()
{
    /*add_menu_page( 'Users Notification Page', 'Notificaciones de Usuarios', 'manage_options', 'notification-back-mail-plugin', 'users_notification_init', 'dashicons-admin-users'); */
    add_menu_page('Users Notification Page', 'Notificaciones de Usuarios', 'manage_options', 'notification-back-mail-plugin', 'users_notification_init', 'dashicons-admin-users');
    add_submenu_page('notification-back-mail-plugin', 'Users Notification Page', 'Registros de Follow', 'manage_options', 'notification-back-mail-plugin');
    add_submenu_page('notification-back-mail-plugin', 'My Custom Submenu Page', 'Bitacora de Notificaciones', 'manage_options', 'update-acf-dates', 'change_acf_dates_init');
	
	//add_submenu_page('notification-back-mail-plugin', 'New Project Page', 'Bitacora de Proyectos Nuevos', 'manage_options');
	

		add_submenu_page(
	  'notification-back-mail-plugin', //padre plugin
	  'Proceso Proyectos Nuevos', // titulo de la pagina
	  'Proceso Proyectos Nuevos', // titulo del menu
	  'manage_options', // permisos
	  'pmx-proceso-proyectos-nuevos', //slug de la pagina del submenu
	  'pmx_render_pagina_proceso_proyectos_nuevos' //callback
	); 
	
	
	 /*Submenú: Proceso Proyectos Nuevos
  add_submenu_page(
    'notification-back-mail-plugin',            // Padre plugin
    'Proceso Proyectos Nuevos',                 // Título página
    'Proceso Proyectos Nuevos',                 // Titulo  menú
    PMX_CAP_PROY_NUEVOS,                        // Capability
    'pmx-proceso-proyectos-nuevos',             // Slug submenú
    'pmx_render_pagina_proceso_proyectos_nuevos'// Callback
  );*/


  
	
    //add_submenu_page( 'notification-back-mail-plugin', 'Procesos', 'Procesos','manage_options', 'update-acf-procesos','change_acf_procesos_init');
}
function users_notification_init()
{
    include 'list-users.php';
    if (is_admin()) {
        wp_enqueue_script('jquery-ui-datepicker');
        //wp_enqueue_script( 'datatable-script-init-ui', 'http://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.0/jquery-ui.js', array( 'jquery' ), '1.0.0', true );
        wp_enqueue_script('datatable-script', plugin_dir_url(__FILE__) . 'assets/DataTable/datatables.min.js', array('jquery'), '1.0.0', true);
        wp_enqueue_script('datatable-script-init', plugin_dir_url(__FILE__) . 'assets/init-table.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('datatable-style', plugin_dir_url(__FILE__) . 'assets/DataTable/datatables.min.css', array());
        wp_enqueue_style('datatable-style-new', plugin_dir_url(__FILE__) . 'assets/table.css', array());
    }
}
function change_acf_dates_init()
{
    include 'list-users-reports.php';
    if (is_admin()) {
        wp_enqueue_script('jquery-ui-datepicker');
        //wp_enqueue_script( 'datatable-script-init-ui', 'http://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.0/jquery-ui.js', array( 'jquery' ), '1.0.0', true );
        wp_enqueue_script('datatable-script', plugin_dir_url(__FILE__) . 'assets/DataTable/datatables.min.js', array('jquery'), '1.0.0', true);
        wp_enqueue_script('datatable-script-init', plugin_dir_url(__FILE__) . 'assets/init-table.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('datatable-style', plugin_dir_url(__FILE__) . 'assets/DataTable/datatables.min.css', array());
        wp_enqueue_style('datatable-style-new', plugin_dir_url(__FILE__) . 'assets/table.css', array());
    }
}
function change_acf_procesos_init()
{
    include 'procesos.php';
}


// politica para filtrar proyectos nuevos
if ( ! has_filter('pmx_conflict_policy_sector_subsector') ) {
  add_filter('pmx_conflict_policy_sector_subsector', function(){ return 'or'; });
}



// Lee el atributo `default` del shortcode [bmxt_nuevos_ttl] en una página dada
if (!function_exists('pmx_get_ttl_default_from_shortcode')) {
  function pmx_get_ttl_default_from_shortcode($page_slug = 'proceso-ttl-proyectos-nuevos', $shortcode_tag = 'bmxt_nuevos_ttl') {
    $page = get_page_by_path($page_slug);
    if ($page) {
      $content = get_post_field('post_content', $page->ID);
      if (is_string($content) && $content !== '') {
        $regex = get_shortcode_regex(); // busca cualquier shortcode
        if (preg_match_all('/'.$regex.'/s', $content, $m)) {
          foreach ($m[2] as $i => $tag) {
            if ($tag === $shortcode_tag) {
              $atts = shortcode_parse_atts($m[3][$i]);
              if (isset($atts['default']) && $atts['default'] !== '') {
                return trim((string)$atts['default']); // ej. "5m", "10h", "3600s"
              }
            }
          }
        }
      }
    }
    return null; // no encontrado
  }
}

// Convierte segundos a una forma corta agradable (d/h/m/s)
if (!function_exists('pmx_secs_to_short')) {
  function pmx_secs_to_short($secs) {
    $s = (int)$secs;
    if ($s % 86400 === 0) return (int)($s/86400) . 'd';
    if ($s % 3600  === 0) return (int)($s/3600)  . 'h';
    if ($s % 60    === 0) return (int)($s/60)    . 'm';
    return $s . 's';
  }
}


//call back de proyectos nuevos 

/** Vista del submenú */
//call back de proyectos nuevos 

/** Vista del submenú */
function pmx_render_pagina_proceso_proyectos_nuevos(){
	
	// Intenta traer el TTL desde el shortcode de la página runner
$pmx_ui_ttl = pmx_get_ttl_default_from_shortcode('proceso-ttl-proyectos-nuevos'); // cambia el slug si es otro
if ($pmx_ui_ttl === null || $pmx_ui_ttl === '') {
  // Fallback: constante o 30 min
  $pmx_ui_ttl = defined('PMX_NUEVO_TTL') ? pmx_secs_to_short((int)PMX_NUEVO_TTL) : '30m';
}

	
    $nonce = wp_create_nonce('pmx_proc_nuevos');
    ?>
    <div class="wrap">
      <h1>Proceso Proyectos Nuevos</h1>
      <p>Los resultados se muestran abajo.</p>

      <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
        <button id="pmx_btn_notif" class="button button-primary">
          Ejecutar Notificación
        </button>
        <label for="pmx_value" style="margin-left:4px;">value:</label>
        <input type="number" id="pmx_value" value="3" min="1" style="width:80px;" readonly>
        <button id="pmx_btn_ttl" class="button">
          Ejecutar TTL(Desmarcado de Proyectos)
        </button>
        <span id="pmx_spinner" class="spinner" style="float:none;"></span>
      </div>

      <pre id="pmx_output" style="max-height:460px;overflow:auto;background:#0b1220;color:#d2e1ff;padding:12px;border-radius:6px;border:1px solid #263238;">
(Esperando acciones…)
      </pre>
	  
	  <hr style="margin:18px 0;">
    <h2>Proyectos marcados como “Nuevos”</h2>
    <p>
      <button id="pmx_btn_refresh_tabla" class="button">Actualizar tabla</button>
	    <label for="pmx_ttl_view" style="margin-left:8px;">TTL tabla:</label>
<input type="text" id="pmx_ttl_view" value="<?php echo esc_attr($pmx_ui_ttl); ?>" placeholder="p.ej. 10h, 30m, 3600s" style="width:140px;" readonly>
      <span id="pmx_spinner_tbl" class="spinner" style="float:none;"></span>
    </p>
	
    <div id="pmx_tabla_nuevos" class="pmx-table-wrap">
      <em>Cargando…</em>
    </div>
	  
    </div>


<script>
jQuery(function($){
  const nonce = '<?php echo esc_js($nonce); ?>';
  const $out  = $('#pmx_output');
  const $sp   = $('#pmx_spinner');

  // ---- NUEVO: control de bloqueo de botones de ejecución ----
  const $btnNotify = $('#pmx_btn_notif');
  const $btnTTL    = $('#pmx_btn_ttl');
  const $btnExec   = $btnNotify.add($btnTTL);
  let pmxBusy = false;

  function setBusy(state){
    pmxBusy = !!state;
    $btnExec.prop('disabled', state).attr('aria-busy', state);
    $btnExec.each(function(){
      const $b = $(this);
      if (state) {
        if (!$b.data('orig-text')) $b.data('orig-text', $.trim($b.text()));
        // conserva ancho para evitar “salto”
        $b.css('min-width', $b.outerWidth());
        $b.text('Ejecutando…').addClass('disabled');
      } else {
        const t = $b.data('orig-text') || $b.text();
        $b.text(t).removeClass('disabled');
        $b.css('min-width','');
      }
    });
  }

  function log(line){ $out.prepend(String(line)+'\n'); }

  // ---- ENVOLTORIO AJAX con bloqueo/desbloqueo ----
  function run(action, data){
    if (pmxBusy) { log('• Ya hay un proceso en ejecución. Espera a que termine.'); return; }
    setBusy(true);
    $sp.addClass('is-active');

    $.post(ajaxurl, $.extend({ action, _ajax_nonce: nonce }, data||{}))
      .done(function(resp){
        // LOG MÍNIMO: HTTP <código> · descripción
        var code = (resp && resp.data && typeof resp.data.code !== 'undefined') ? resp.data.code : '—';
        var desc = (resp && resp.data && (resp.data.desc || resp.data.title)) ? (resp.data.desc || resp.data.title) : (resp && resp.success ? 'OK' : 'Error');
        $out.prepend('HTTP ' + code + ' · ' + desc + '\n');
      })
      .fail(function(xhr){
        log('✘ HTTP '+xhr.status+': '+xhr.statusText);
      })
      .always(function(){
        $sp.removeClass('is-active');
        setBusy(false);
      });
  }

  // ---- Handlers ----
  $('#pmx_btn_notif').on('click', function(e){
    e.preventDefault();
    run('pmx_run_proceso_nuevos', { value: $('#pmx_value').val() });
  });

  $('#pmx_btn_ttl').on('click', function(e){
    e.preventDefault();
    run('pmx_run_ttl_nuevos', {});

    // refrescos extra (no dependen del bloqueo)
    setTimeout(loadTabla, 700); // sube a 1200ms si tu endpoint tarda en persistir
    setTimeout(function(){
      $.post(ajaxurl, { action:'pmx_count_proyectos_nuevos', _ajax_nonce: nonce })
        .done(function(r){
          if(r && r.success){
            var n = r.data.count;
            jQuery('.awaiting-mod .pending-count').text(n);
          }
        });
    }, 800);
  });

  // ------- TABLA "Nuevos" (sin cambios funcionales) -------
  const $spTbl = $('#pmx_spinner_tbl');
  function loadTabla(){
    $spTbl.addClass('is-active');
    $.post(ajaxurl, {
      action: 'pmx_list_proyectos_nuevos',
      _ajax_nonce: nonce,
      ttl_value: $('#pmx_ttl_view').val() // <-- PASA TTL de la tabla (10h, 30m, 3600s)
    })
      .done(function(resp){
        if(resp && resp.success){
          $('#pmx_tabla_nuevos').html(resp.data.html);
        }else{
          $('#pmx_tabla_nuevos').html('<div class="notice notice-error"><p>Error al cargar la tabla.</p></div>');
        }
      })
      .fail(function(xhr){
        $('#pmx_tabla_nuevos').html('<div class="notice notice-error"><p>HTTP '+xhr.status+': '+xhr.statusText+'</p></div>');
      })
      .always(function(){ $spTbl.removeClass('is-active'); });
  }
  // carga inicial y botón
  loadTabla();
  $('#pmx_btn_refresh_tabla').on('click', function(e){ e.preventDefault(); loadTabla(); });

  // --- AUTOREFRESCO DEL GLOBO ---
  pmxRefreshBubbles(); // primer fetch inmediato
  if (!window.pmxBubbleTimer) {              // evita crear múltiples timers
    window.pmxBubbleTimer = setInterval(pmxRefreshBubbles, 30000); // cada 30s
  }
  // --- AUTOREFRESCO DE LA TABLA (en “tiempo real”) ---
  if (!window.pmxTableTimer) {
    window.pmxTableTimer = setInterval(loadTabla, 30000); // refresca cada 30s
  }

  /* --- Fallback por si ajaxurl no está definido --- */
  if (typeof window.ajaxurl === 'undefined') {
    window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
  }

  /* --- Helpers para actualizar los globos del menú --- */
  function pmxSetBubble($anchor, n){
    var $b = $anchor.find('.awaiting-mod');
    if (n > 0) {
      if ($b.length) {
        $b.removeClass(function(i, cls){ return (cls.match(/count-\d+/g)||[]).join(' '); })
          .addClass('count-'+n)
          .find('.pending-count').text(n);
      } else {
        $anchor.append(' <span class="awaiting-mod count-'+n+'"><span class="pending-count">'+n+'</span></span>');
      }
    } else {
      $b.remove();
    }
  }

  function pmxRefreshBubbles(){
    $.post(ajaxurl, { action:'pmx_count_proyectos_nuevos', _ajax_nonce: nonce })
      .done(function(r){
        if (r && r.success){
          var n = parseInt(r.data.count, 10) || 0;
          // Padre: Notificaciones de Usuarios
          var $parentA  = $('#toplevel_page_notification-back-mail-plugin > a .wp-menu-name');
          pmxSetBubble($parentA, n);
          // Submenú: Proceso Proyectos Nuevos
          var $subA = $('#toplevel_page_notification-back-mail-plugin')
                        .find('.wp-submenu a[href$="page=pmx-proceso-proyectos-nuevos"]');
          pmxSetBubble($subA, n);
        }
      });
  }
});
</script>

    <?php
}


/** AJAX: Ejecuta proceso de notificación de proyectos nuevos */
add_action('wp_ajax_pmx_run_proceso_nuevos', function(){
    check_ajax_referer('pmx_proc_nuevos');
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error('Sin permisos', 403);
    }

    // value= ventana de tiempo (tu flujo actual usa 3)
    $value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '3';

    $url = add_query_arg('value', $value, home_url('/proceso-notificacion-diario-de-proyectos-nuevos/'));
	
	// Dentro de pmx_run_proceso_nuevos, después de construir $url:
$url = add_query_arg('pmx_token', pmx_proc_issue_token(90), $url);


    $resp = wp_remote_get($url, array(
        'timeout'     => 90,
        'redirection' => 10,
        'sslverify'   => false, // si tuvieras certificado auto-firmado (como en 172.27.x.x)
    ));

    if (is_wp_error($resp)) {
        wp_send_json_error($resp->get_error_message(), 500);
    }

    $code = (int) wp_remote_retrieve_response_code($resp);
    $body = wp_remote_retrieve_body($resp);
    $snip = trim( wp_strip_all_tags($body) );
    if (strlen($snip) > 1400) { $snip = substr($snip, 0, 1400).'…'; }

    wp_send_json_success(array(
        'title' => 'Notificación ejecutada: value=' . $value,
        'code'  => $code,
        'body'  => $snip,
        'url'   => esc_url_raw($url),
		'desc' => 'Notificación ejecutada (value='.$value.')', // ADD
    ));
});


/** AJAX: Ejecuta proceso TTL de proyectos nuevos */
add_action('wp_ajax_pmx_run_ttl_nuevos', function(){
    check_ajax_referer('pmx_proc_nuevos');
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error('Sin permisos', 403);
    }

    $url = home_url('/proceso-ttl-proyectos-nuevos/');
	
	// Dentro de pmx_run_proceso_nuevos, después de construir $url:
$url = add_query_arg('pmx_token', pmx_proc_issue_token(90), $url);


    $resp = wp_remote_get($url, array(
        'timeout'     => 90,
        'redirection' => 10,
        'sslverify'   => false,
    ));

    if (is_wp_error($resp)) {
        wp_send_json_error($resp->get_error_message(), 500);
    }

    $code = (int) wp_remote_retrieve_response_code($resp);
    $body = wp_remote_retrieve_body($resp);
    $snip = trim( wp_strip_all_tags($body) );
    if (strlen($snip) > 1400) { $snip = substr($snip, 0, 1400).'…'; }

    wp_send_json_success(array(
        'title' => 'TTL ejecutado',
        'code'  => $code,
        'body'  => $snip,
        'url'   => esc_url_raw($url),
		'desc' => 'TTL recalculado', // ADD
    ));
});

//ajax tabla 

// zona horaria para la tabla 

// Formatea timestamps en la zona "México Centro"
// Formatea timestamps en "Ciudad de México", corrigiendo si el tzdata aplica DST por error derivado del servidor.
if (!function_exists('pmx_fmt_mx')) {
  function pmx_fmt_mx($ts, $format = 'Y-m-d H:i') {
    $ts = (int)$ts;
    if ($ts <= 0) return '—';

    $tzMx = new DateTimeZone('America/Mexico_City');
    $dt   = new DateTime('@'.$ts);   // '@' => epoch UTC
    $dt->setTimezone($tzMx);

    // Si el offset es -18000 (UTC-5), el tzdata del sistema está desactualizado.
    // Fallback a offset fijo UTC-6 sin DST.
    if ($dt->getOffset() === -18000) { // -5h
      $tzFixed = new DateTimeZone('Etc/GMT+6'); // ¡ojo! GMT+6 ≡ UTC-6
      $dt->setTimezone($tzFixed);
    }

    return $dt->format($format);
  }
}



/**
 * Devuelve tabla HTML con proyectos marcados como "Nuevos".
 * Soporta varias claves meta (flag) y/o TTL (fecha/hora de expiración).
 */
add_action('wp_ajax_pmx_list_proyectos_nuevos', function () {
    check_ajax_referer('pmx_proc_nuevos');
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error('Sin permisos', 403);
    }

    // ADD: helper para parsear "10h", "30m", "3600s" → segundos
    if (!function_exists('pmx_parse_ttl_like')) {
      function pmx_parse_ttl_like($raw) {
        $raw = trim((string)$raw);
        if ($raw === '') return null;
        if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*([smhd]?)\s*$/i', $raw, $m)) {
          $num = (float)$m[1];
          $u   = strtolower(isset($m[2]) ? $m[2] : '');
          $factor = ($u==='s') ? 1 : (($u==='h') ? 3600 : (($u==='d') ? 86400 : 60)); // default minutos
          return (int) round($num * $factor);
        }
        return null;
      }
    }

    // ADD: TTL para la tabla (prioridad: ttl_value UI -> constante -> 1800)
    $ttl_from_ui = isset($_POST['ttl_value']) ? sanitize_text_field($_POST['ttl_value']) : '';
    $__pmx_ttl_table = pmx_parse_ttl_like($ttl_from_ui);
    if (! $__pmx_ttl_table || $__pmx_ttl_table <= 0) {
      $__pmx_ttl_table = defined('PMX_NUEVO_TTL') ? (int) PMX_NUEVO_TTL : 1800;
    }

    // Ajusta el CPT si el tuyo se llama distinto
    $post_type = apply_filters('pmx_new_projects_post_type', 'proyecto_inversion');

    // Claves meta que significan "nuevo = true"
    $flag_keys = apply_filters('pmx_new_project_flag_keys', array(
        'proyecto_nuevo', 'nuevo', 'pmx_nuevo', 'pmx_proyecto_nuevo',
    ));

    // Claves meta que guardan "nuevo hasta" (timestamp o fecha parseable)
    $until_keys = apply_filters('pmx_new_project_until_keys', array(
        'pmx_nuevo_until', '_pmx_nuevo_until', 'nuevo_hasta', 'pmx_ttl_nuevo', 'pmx_nuevo_expira',
    ));

    // Construye meta_query (OR): o está el flag en true, o el TTL aún no vence
    $meta_or = array('relation' => 'OR');

    // Cualquier flag "truthy"
    foreach ($flag_keys as $k) {
        $meta_or[] = array(
            'key'     => $k,
            'value'   => array('1', 1, true, 'true', 'on', 'yes'),
            'compare' => 'IN',
        );
    }

    // TTL mayor o igual a "ahora"
    $now = time();
    foreach ($until_keys as $k) {
        $meta_or[] = array(
            'key'     => $k,
            'value'   => $now,
            'compare' => '>=',
            'type'    => 'NUMERIC', // si guardas timestamp numérico
        );
    }
	
	//foreach ($until_keys as $k) {
    //$meta_or[] = array(
     //   'key'     => $k,
     //   'compare' => 'EXISTS',
    //);
//}

    $q = new WP_Query(array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 200,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => $meta_or,
        'no_found_rows'  => true,
    ));

    $total = (int) $q->post_count;

    // --- estilos y leyenda para colores ---
    $html  = '<style>
      .pmx-badge{display:inline-block;padding:2px 8px;border-radius:999px;color:#fff;font-weight:600;font-size:12px;line-height:1;}
      .pmx-ok{background:#1a7f37;}      /* verde */
      .pmx-warn{background:#b58100;}    /* amarillo/ámbar */
      .pmx-exp{background:#c62828;}     /* rojo */
      .pmx-row-expired td{background:#fff4f4;}
    </style>';
    $html .= '<p style="margin:6px 0 10px 0;">
      <span class="pmx-badge pmx-ok">Vigente</span>
      <span class="pmx-badge pmx-warn" style="margin-left:6px;">Casi vence</span>
      <span class="pmx-badge pmx-exp" style="margin-left:6px;">Vencido</span>
    </p>';

    // Render tabla
    $html .= '<table class="widefat fixed striped">';
    $html .= '<thead><tr>';
    $html .= '<th width="70">ID</th>';
    $html .= '<th>Título</th>';
    $html .= '<th width="110">Estado</th>';
    $html .= '<th width="130">Flag nuevo</th>';
    $html .= '<th width="110">Vigencia</th>'; // NUEVA col con color
    $html .= '<th width="170">Marcado</th>';
    $html .= '<th width="170">Expira</th>';
    $html .= '<th width="80">Ver</th>';
    $html .= '<th width="80">Editar</th>';
    $html .= '</tr></thead><tbody>';
	$html .= '<p><strong>Total:</strong> '.esc_html($total).'</p>';

    if ($total === 0) {
        $html .= '<tr><td colspan="9"><em>No hay proyectos marcados como “Nuevos”.</em></td></tr>';
    } else {
        while ($q->have_posts()) { $q->the_post();
            $pid   = get_the_ID();
            $title = get_the_title();
            $st    = get_post_status($pid);

            // Lee la primera clave flag "verdadera" para mostrar cuál activó
            $flag_show = '—';
            foreach ($flag_keys as $k) {
                $v = get_post_meta($pid, $k, true);
                if (!empty($v) && $v !== '0' && $v !== 0) { $flag_show = esc_html($k); break; }
            }

            // Obtén "nuevo hasta" (el primer meta válido)
            $nuevo_hasta_ts = null; $nuevo_hasta_key = null;
            foreach ($until_keys as $k) {
                $v = get_post_meta($pid, $k, true);
                if ($v === '' || $v === null) continue;
                if (is_numeric($v))       { $t = (int)$v; }
                else                      { $t = strtotime((string)$v); }
                if ($t && $t > 0) { $nuevo_hasta_ts = $t; $nuevo_hasta_key = $k; break; }
            }

            // leer 'nuevo_marked_at' y normalizar a epoch (10s/13ms/ISO)
            $marked_ts = null;
            $mv = get_post_meta($pid, 'nuevo_marked_at', true);
            if ($mv !== '' && $mv !== null) {
              if (is_numeric($mv)) {
                $s = (string)$mv;
                $marked_ts = (strlen($s) >= 13) ? (int) floor(((int)$mv)/1000) : (int)$mv;
              } else {
                $tt = strtotime((string)$mv);
                if ($tt) $marked_ts = $tt;
              }
            }

            // expira = meta “hasta” (si existe)  ó  (marked_at + TTL tabla)
            $exp_ts = $nuevo_hasta_ts ? $nuevo_hasta_ts : (($marked_ts && $__pmx_ttl_table) ? $marked_ts + $__pmx_ttl_table : null);

            // textos en zona horaria del sitio
            //$marked_txt = $marked_ts ? ( function_exists('wp_date') ? wp_date('Y-m-d H:i', $marked_ts) : date_i18n('Y-m-d H:i', $marked_ts, true) ) : '—';
            //$exp_txt = $exp_ts ? ( function_exists('wp_date') ? wp_date('Y-m-d H:i', $exp_ts) : date_i18n('Y-m-d H:i', $exp_ts, true) ) : '—';
			
			$marked_txt = pmx_fmt_mx($marked_ts);
			$exp_txt    = pmx_fmt_mx($exp_ts);


            // --- Cálculo de vigencia y color ---
            $badge = '<span class="pmx-badge pmx-exp">Vencido</span>';
            $rowcls = '';
            if ($exp_ts && $exp_ts > time()) {
              $remaining = $exp_ts - time();
              // umbral “casi vencido”: último 10% del TTL (mínimo 60s)
              $warn_threshold = max(60, (int)round($__pmx_ttl_table * 0.10));
              if ($remaining <= $warn_threshold) {
                $badge = '<span class="pmx-badge pmx-warn">Casi vence</span>';
              } else {
                $badge = '<span class="pmx-badge pmx-ok">Vigente</span>';
              }
            } else {
              $rowcls = 'pmx-row-expired';
            }

            $html .= '<tr class="'.esc_attr($rowcls).'">';
            $html .= '<td>'.esc_html($pid).'</td>';
            $html .= '<td>'.esc_html($title).'</td>';
            $html .= '<td>'.esc_html($st).'</td>';
            $html .= '<td>'.esc_html($flag_show).'</td>';
            $html .= '<td>'.$badge.'</td>'; // badge de color
            $html .= '<td>'.esc_html($marked_txt).'</td>';
            $html .= '<td>'.esc_html($exp_txt).'</td>';
            $html .= '<td><a href="'.esc_url(get_permalink($pid)).'" target="_blank">Ver</a></td>';
            $html .= '<td><a href="'.esc_url(get_edit_post_link($pid)).'" target="_blank">Editar</a></td>';
            $html .= '</tr>';
        }
        wp_reset_postdata();
    }

    $html .= '</tbody></table>';
    

    wp_send_json_success(array('html' => $html));
});


//globo de Notificacion

/* ================== BURBUJAS DE CONTADOR EN MENÚ ================== */

/** Cuenta proyectos marcados como “Nuevos” (flag o TTL numérico activo). */
if (!function_exists('pmx_count_proyectos_nuevos')) {
  function pmx_count_proyectos_nuevos(){
    $post_type = apply_filters('pmx_new_projects_post_type', 'proyecto_inversion');

    $flag_keys = apply_filters('pmx_new_project_flag_keys', array(
      'proyecto_nuevo','nuevo','pmx_nuevo','pmx_proyecto_nuevo',
    ));
    $until_keys = apply_filters('pmx_new_project_until_keys', array(
      'pmx_nuevo_until','_pmx_nuevo_until','nuevo_hasta','pmx_ttl_nuevo','pmx_nuevo_expira',
    ));

    $now     = time();
    $meta_or = array('relation' => 'OR');

    foreach ($flag_keys as $k) {
      $meta_or[] = array(
        'key'     => $k,
        'value'   => array('1',1,true,'true','on','yes'),
        'compare' => 'IN',
      );
    }
    foreach ($until_keys as $k) {
      $meta_or[] = array(
        'key'     => $k,
        'value'   => $now,
        'compare' => '>=',
        'type'    => 'NUMERIC',
      );
    }

    // Usamos found_posts sin traer todos los posts
    $q = new WP_Query(array(
      'post_type'      => $post_type,
      'post_status'    => 'publish',
      'fields'         => 'ids',
      'posts_per_page' => 1,       // sólo 1, pero contamos con found_posts
      'no_found_rows'  => false,   // IMPORTANTE para obtener found_posts
      'meta_query'     => $meta_or,
    ));

    return (int) $q->found_posts;
  }
}

/** Inserta burbujas en el menú padre y en el submenú "Proceso Proyectos Nuevos". */
add_action('admin_menu', function(){
  global $menu, $submenu;

  // Slugs de tu menú
  $PARENT = 'notification-back-mail-plugin';
  $SUB    = 'pmx-proceso-proyectos-nuevos';

  $count = pmx_count_proyectos_nuevos();
  if ($count <= 0) return;

  // Burbuja tipo "comentarios" (roja)
  $bubble = ' <span class="awaiting-mod count-'.intval($count).'"><span class="pending-count">'. number_format_i18n($count) .'</span></span>';

  // 1) Menú padre
  if (is_array($menu)) {
    foreach ($menu as $i => $m) {
      if (isset($m[2]) && $m[2] === $PARENT) {
        $menu[$i][0] .= $bubble;
        break;
      }
    }
  }

  // 2) Submenú "Proceso Proyectos Nuevos"
  if (isset($submenu[$PARENT]) && is_array($submenu[$PARENT])) {
    foreach ($submenu[$PARENT] as $j => $sm) {
      if (isset($sm[2]) && $sm[2] === $SUB) {
        $submenu[$PARENT][$j][0] .= $bubble;
        break;
      }
    }
  }
}, 999);


// actualiza globo
add_action('wp_ajax_pmx_count_proyectos_nuevos', function(){
  check_ajax_referer('pmx_proc_nuevos');
  if (!current_user_can('manage_options')) wp_send_json_error('Sin permisos', 403);
  wp_send_json_success(array('count' => pmx_count_proyectos_nuevos()));
});



//restrinccion de las paginas como publicadas
//codigo que genera un token para los botones de notificaciones y ttl a travez de ajax  para usuarios logueados con permisos admin  para permirir la ejecucion de los procesos
//si no se tiene token y si no esta logueado como admin y se intenta ejecutar con url, manda un mensaje de permiso denegado

// Genera un token temporal (1 uso) para disparar los procesos desde AJAX de los botones
if (!function_exists('pmx_proc_issue_token')) {
  function pmx_proc_issue_token($ttl = 90){ // segundos de validez del token
    $tok = wp_generate_password(20, false, false); //crear token , longitud del password, caracteres especiales , otracaracteres especiales
    set_transient('pmx_proc_token_'.$tok, 1, $ttl); // token + solo se usara una vez +cadcidad del token
    return $tok;
  }
}

// Restringe acceso a las páginas de procesos: sólo admin logueado o requests con token válido
add_action('template_redirect', function () {
  if (!is_page()) return;

  $slugs_protegidos = array( // colocamos los slug de las paginas a bloquear
    'proceso-notificacion-diario-de-proyectos-nuevos',
    'proceso-ttl-proyectos-nuevos',
  );


// código de bloqueo cuando sí estamos justo en una de las páginas de proceso que estan protegidas. Así no afectas al resto del sitio.
  $obj = get_queried_object();
  if (empty($obj) || empty($obj->post_name) || !in_array($obj->post_name, $slugs_protegidos, true)) {
    return;
  }

  // no indexar
  header('X-Robots-Tag: noindex, nofollow', true);
  nocache_headers();

  // 1) Admin logueado con permisos: pasa
  if (is_user_logged_in() && current_user_can('manage_options')) { // son los permisos que se dio igual al crear el submenu
    return;
  }

  // 2) Petición con token válido (desde  botones AJAX): pasa y quita el token (one-shot)
  $tok = isset($_GET['pmx_token']) ? sanitize_text_field($_GET['pmx_token']) : '';
  if ($tok && get_transient('pmx_proc_token_'.$tok)) {
    delete_transient('pmx_proc_token_'.$tok);
    return;
  }

  // Si no cumple, bloquear
  status_header(403);
  exit('403 Sin Permisos, esto es una prueba de bloque por token');
}, 0);

// ---- LOG de tokens (emisión/consumo/llamada) ----
if (!function_exists('pmx_token_log')) {
  function pmx_token_log($stage, $tok, array $extra = []) {
    $uid = function_exists('get_current_user_id') ? get_current_user_id() : 0;
    $ip  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-';
    $mask = substr($tok, 0, 4).'…'.substr($tok, -4); // enmascarado seguro

    // Construye línea
    $line = sprintf(
      '[PMX][TOKEN][%s] uid=%s ip=%s token=%s %s',
      strtoupper($stage),
      $uid,
      $ip,
      $mask,
      $extra ? json_encode($extra, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) : ''
    );

    // Opción 1: a wp-content/debug.log si WP_DEBUG_LOG=true
    if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
      error_log($line);
    } else {
      // Opción 2 (fallback): log PHP del servidor
      error_log($line);
    }

    // Opción 3: log dedicado (descomenta si quieres tu archivo propio)
    // error_log($line.PHP_EOL, 3, WP_CONTENT_DIR.'/pmx-tokens.log');
  }
}





/////////////////////// ADMIN SECTION

//first notification shortcode Notificacion Diaria de Proyectos con edicion
function send_mails_shortcode($attr, $content = null)
{
    $mail     = $_GET['mail'];
    $language = $_GET['language'];
    extract(shortcode_atts(array(
        'mail' => $mail,
    ), $attr));
    global $wpdb;
    $table_name = $wpdb->prefix . 'bancomext_users_reports';
    $time       = current_time('mysql');
    if ($mail) {
        $mail_template = construct_mails($mail, $language);        
        if (!empty($mail_template)) {
        	$H1 = '';
        	$H2 = get_value_notifications('', '', '', 'cambio_follow_0_cuerpo_0_espaniol');
        	if($H2 == '')
            	$H2 = 'Le notificamos que el siguiente proyecto ha sido actualizado: ';            
            $perz = get_value_notifications('', '', '', 'cambio_follow_0_personalizado_0_espaniol');
            if($language == 'en_US' or $idioma == 'en'){
            	$Header1 = 'Mexico Project Hubs';
            	$H2 = get_value_notifications('', '', '', 'cambio_follow_0_cuerpo_0_ingles');
        		if($H2 == '')
                	$H2 = 'We notify you that the following project has been updated:';                
                $perz = get_value_notifications('', '', '', 'cambio_follow_0_personalizado_0_ingles');
            }
            //$header = get_header_notification();
            $header = Header_Preferences_Shortcode(0,$H1, $H2, '', $language, $perz);
            $tr = '';

            foreach ($mail_template as $keys => $values) {
                $This_id = $mail_template[$keys]->ID;
                $id      = get_field('ID_PROYECTO', $This_id);
                $title_proyect = $mail_template[$keys]->post_title;                

                // Record Save
                $wpdb->insert($table_name, array('idioma' => $language, 'follow' => $This_id, 'email' => $mail, 'report' => $time, 'proceso' => 'Follow Diario', 'status' => 'Generado'));
                // Title Project

                // Campos
                $cambios = 0;
                $post_original  = get_post($This_id);
                $post = $post_original;
                $post_revision = '';
                $post_date = get_field('fecha_ultima_actualizacion_proyecto', $This_id);
                $id_revision_usar = 0;
                

                $revisions = wp_get_post_revisions($This_id);

                if ($revisions) {
                    foreach ($revisions as $key => $item) {
                        $review_date = get_field('fecha_ultima_actualizacion_proyecto', $item->ID);

                        if ((strpos($item->post_name, 'autosave') == false) && ($post_date != $review_date)) {
                            $post_revision = $item;
                            $revision = $item;
                            $id_revision_usar = $item->ID;
                            break;
                        }
                    }
                }

                $post_titulo = get_the_title($post->ID);
                $revision_titulo = get_the_title($revision->ID);
                $post_titulo_en = get_field('nombre_oficial_ingles', $post->ID);
                $revision_titulo_en = get_field('nombre_oficial_ingles', $revision->ID);
		
		$link_ppm = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                if ($language == 'en_US'){
                    $txt_btn = 'Unsubscribe';
                    $btn_unsubscribe = $link_ppm."/wp-content/uploads/2019/02/unsubscribe.png";
                    $btn = '
                    <a href="'.$link_ppm. '/notificacion-baja-en/?mail=' . $mail . '&id=' . $id.'&language=en" target="_blank"><img src="'.$btn_unsubscribe.'" alt="'.$txt_btn.'" class="imgr96" style="outline: none;border: 0;text-decoration: none;height:55px!important;width:170px!important;" width="35%"/></a>';
                }
                else{
                    $txt_btn = 'Dar de Baja';
                    $btn_unsubscribe = $link_ppm."/wp-content/uploads/2019/02/baja.png";
                    $btn = '
                    <a href="'.$link_ppm. '/notificacion-baja/?mail=' . $mail . '&id=' . $id.'" target="_blank"><img src="'.$btn_unsubscribe.'" alt="'.$txt_btn.'" class="imgr96" style="outline: none;border: 0;text-decoration: none;height:55px!important;width:170px!important;" width="35%"/></a>';
                }

                $tr .=  Title_Proyect_Preferences_Shortcode($language, '', $This_id, $post_titulo, $post_titulo_en, $btn);

                // Sector
                $post_sector = isset($post_original->sector_proyecto) ? get_the_title($post_original->sector_proyecto) : '';
                $revision_sector = isset($post_revision->sector_proyecto) ? get_the_title($post_revision->sector_proyecto) : '';
                $post_sector_en = pll_get_post($post_original->sector_proyecto, "en") != NULL ? get_the_title(pll_get_post($post_original->sector_proyecto, "en")) : '';
                $revision_sector_en = pll_get_post($post_revision->sector_proyecto, "en") != NULL ? get_the_title(pll_get_post($post_revision->sector_proyecto, "en")) : '';

                // Subsector
                $post_subsector = isset($post_original->subsector_proyecto) ? get_the_title($post_original->subsector_proyecto) : '';
                $revision_subsector = isset($post_revision->subsector_proyecto) ? get_the_title($post_revision->subsector_proyecto) : '';
                $post_subsector_en = pll_get_post($post_original->subsector_proyecto, "en") != NULL ? get_the_title(pll_get_post($post_original->subsector_proyecto, "en")) : '';
                $revision_subsector_en = pll_get_post($post_revision->subsector_proyecto, "en") != NULL ? get_the_title(pll_get_post($post_revision->subsector_proyecto, "en")) : '';

                // Alias
                $post_alias = $post_original->alias_proyecto;
                $revision_alias = $post_revision->alias_proyecto;
                $post_alias_en = $post_original->alias_proyecto_en;
                $revision_alias_en = $post_revision->alias_proyecto_en;

                // Fecha de ulltima actualizacion
                $post_fecha_ultima_actualizacion = $post->fecha_ultima_actualizacion_proyecto;
                $revision_fecha_ultima_actualizacion = $revision->fecha_ultima_actualizacion_proyecto;

                // Proyecto verde cambio a participacion banobras
                $post_proyecto_verde = $post->proyecto_verde;
                $revision_proyecto_verde = $revision->proyecto_verde;

                // Tipo de inverseion
                $post_tipo_inversion = isset($post->tipo_de_inversion) ? get_the_title($post->tipo_de_inversion) : '';
                $revision_tipo_inversion = isset($revision->tipo_de_inversion) ? get_the_title($revision->tipo_de_inversion) : '';
                $post_tipo_inversion_en = pll_get_post($post->tipo_de_inversion, "en") != NULL ? get_the_title(pll_get_post($post->tipo_de_inversion, "en")) : '';
                $revision_tipo_inversion_en = pll_get_post($revision->tipo_de_inversion, "en") != NULL ? get_the_title(pll_get_post($revision->tipo_de_inversion, "en")) : '';

                // Activos
                $activos_original = "&nbsp;";
                if (have_rows('activos', $post->ID)) {
                    $i = 0;
                    while (have_rows('activos', $post->ID)): the_row();
                        $k = $i - 1; //Para corregir el problema de que se empieza a contar de 0
                        if (get_sub_field('activo_proyecto')) {$activos_original = $activos_original . get_the_title(get_sub_field('activo_proyecto'));}
                        if (get_sub_field('cantidad_activo')) {$activos_original = $activos_original . ' ' . str_replace('.00', '', number_format(get_sub_field('cantidad_activo'), 2));}
                        if (get_sub_field('medida_activo')) {$activos_original = $activos_original . ' ' . get_the_title(get_sub_field('medida_activo'));}
                        if (get_sub_field('calidad')) {$activos_original = $activos_original . '-' . get_the_title(get_sub_field('calidad'));}
                        $activos_original = $activos_original . ", ";
                        $i++;
                    endwhile;
                    $activos_original = substr($activos_original, 0, -2);
                }

                $activos_revision = "&nbsp;";
                if (have_rows('activos', $revision->ID)) {
                    $i = 0;
                    while (have_rows('activos', $revision->ID)): the_row();
                        $k = $i - 1; //Para corregir el problema de que se empieza a contar de 0
                        if (get_sub_field('activo_proyecto')) {$activos_revision = $activos_revision . get_the_title(get_sub_field('activo_proyecto'));}
                        if (get_sub_field('cantidad_activo')) {$activos_revision = $activos_revision . ' ' . str_replace('.00', '', number_format(get_sub_field('cantidad_activo'), 2));}
                        if (get_sub_field('medida_activo')) {$activos_revision = $activos_revision . ' ' . get_the_title(get_sub_field('medida_activo'));}
                        if (get_sub_field('calidad')) {$activos_revision = $activos_revision . '-' . get_the_title(get_sub_field('calidad'));}
                        $activos_revision = $activos_revision . ", ";
                        $i++;
                    endwhile;
                    $activos_revision = substr($activos_revision, 0, -2);
                }

                $activos_original_en = "&nbsp;";
                if (have_rows('activos', $post->ID)) {
                    $i = 0;
                    while (have_rows('activos', $post->ID)): the_row();
                        $k = $i - 1; //Para corregir el problema de que se empieza a contar de 0
                        if (pll_get_post(get_sub_field('activo_proyecto'), "en")) {$activos_original_en = $activos_original_en . get_the_title(pll_get_post(get_sub_field('activo_proyecto'), "en"));}
                        if (get_sub_field('cantidad_activo')) {$activos_original_en = $activos_original_en . ' ' . str_replace('.00', '', number_format(get_sub_field('cantidad_activo'), 2));}
                        if (pll_get_post(get_sub_field('medida_activo'), "en")) {$activos_original_en = $activos_original_en . ' ' . get_the_title(pll_get_post(get_sub_field('medida_activo'), "en"));}
                        if (pll_get_post(get_sub_field('calidad'), "en")) {$activos_original_en = $activos_original_en . '-' . get_the_title(pll_get_post(get_sub_field('calidad'), "en"));}
                        $activos_original_en = $activos_original_en . ", ";
                        $i++;
                    endwhile;
                    $activos_original_en = substr($activos_original_en, 0, -2);
                }
                $activos_revision_en = "&nbsp;";
                if (have_rows('activos', $revision->ID)) {
                    $i = 0;
                    while (have_rows('activos', $revision->ID)): the_row();
                        $k = $i - 1; //Para corregir el problema de que se empieza a contar de 0
                        if (pll_get_post(get_sub_field('activo_proyecto'), "en")) {$activos_revision_en = $activos_revision_en . get_the_title(pll_get_post(get_sub_field('activo_proyecto'), "en"));}
                        if (get_sub_field('cantidad_activo')) {$activos_revision_en = $activos_revision_en . ' ' . str_replace('.00', '', number_format(get_sub_field('cantidad_activo'), 2));}
                        if (pll_get_post(get_sub_field('medida_activo'), "en")) {$activos_revision_en = $activos_revision_en . ' ' . get_the_title(pll_get_post(get_sub_field('medida_activo'), "en"));}
                        if (pll_get_post(get_sub_field('calidad'), "en")) {$activos_revision_en = $activos_revision_en . '-' . get_the_title(pll_get_post(get_sub_field('calidad'), "en"));}
                        $activos_revision_en = $activos_revision_en . ", ";
                        $i++;
                    endwhile;
                    $activos_revision_en = substr($activos_revision_en, 0, -2);
                }

                // Moneda del contrato.
                $post_moneda = '';
                $revision_moneda = '';
                if (get_field('contrato_asignado_en', $post_original->ID) != '') {
                    $post_moneda = get_the_title(get_field('contrato_asignado_en', $post_original->ID));
                }
                if (get_field('contrato_asignado_en', $post_revision->ID) != '') {
                    $revision_moneda = get_the_title(get_field('contrato_asignado_en', $post_revision->ID));
                }

                // Monto de inversion
                $post_monto_inversion = $post->monto_inversion;
                $revision_monto_inversion = $revision->monto_inversion;

                if ( $post_monto_inversion === "" || !isset($post_monto_inversion) || $post_monto_inversion === "0" ) {

                    if($language == 'en_US' or $idioma == 'en'){
                        $post_monto_inversion = "N.A.";
                    }else{
                        $post_monto_inversion = "N.D.";
                    }

                }else{

                    // Convertir la cadena a un número.
                    $numero = (int)$post_monto_inversion;
                    // Formatear el número con comas.
                    $formateado = number_format($numero);
                    // Concatenar el símbolo de moneda y la moneda.
                    $post_monto_inversion = "$post_moneda $$formateado";

                }

                
                if ( $revision_monto_inversion === "" || !isset($revision_monto_inversion) || $revision_monto_inversion === "0" ) {

                        if($language == 'en_US' or $idioma == 'en'){
                            $revision_monto_inversion = "N.A.";
                        }else{
                            $revision_monto_inversion = "N.D.";
                        }

                }else{

                    // Convertir la cadena a un número.
                    $numero = (int)$revision_monto_inversion;
                    // Formatear el número con comas.
                    $formateado = number_format($numero);
                    // Concatenar el símbolo de moneda y la moneda.
                    $revision_monto_inversion = "$post_moneda $$formateado";
                   
                }
                

                //Alcance del contrato
                $post_values = get_field('alcances_del_contrato', $post_original->ID);
                $revision_values = get_field('alcances_del_contrato', $post_revision->ID);
                $post_alcance_contrato = "";
                $revision_alcance_contrato = "";
                $post_alcance_contrato_en = "";
                $revision_alcance_contrato_en = "";

                if($post_values) {
                    foreach($post_values as $v) {
                        $post_alcance_contrato .= $v.", ";
                    }
                }

                $post_alcance_contrato = substr($post_alcance_contrato,0,-2);

                $translate_en = ABSPATH . "/wp-content/themes/enfold-child/lang/en_US.po";
                $content_translate_en = file_get_contents($translate_en);
                $arr_content_translate_en = explode("msgid",$content_translate_en);
                $alcances_dic_en = array();

                foreach ($arr_content_translate_en as $a) {
                    $b = explode("msgstr",$a);

                    $b[0] = trim($b[0]);
                    $b[1] = trim($b[1]);

                    $b[0] = str_replace('"','',$b[0]);
                    $b[1] = str_replace('"','',$b[1]);

                    $alcances_dic_en[$b[0]] = $b[1];
                }

                foreach ($post_values as $v) {
                    $post_alcance_contrato_en = $post_alcance_contrato_en . $alcances_dic_en[$v].", ";
                }

                $post_alcance_contrato_en = substr($post_alcance_contrato_en,0,-2);

                if($revision_values) {
                    foreach($revision_values as $v) {
                        $revision_alcance_contrato .= $v.", ";
                    }
                }

                $revision_alcance_contrato = substr($revision_alcance_contrato,0,-2);

                foreach ($revision_values as $v) {
                    $revision_alcance_contrato_en = $revision_alcance_contrato_en . $alcances_dic_en[$v].", ";
                }

                $revision_alcance_contrato_en = substr($revision_alcance_contrato_en,0,-2);

                // Descripcion amplia
                $post_descripcion_amplia = $post->descripcion_amplia_es;
                $revision_descripcion_amplia = $revision->descripcion_amplia_es;
                $post_descripcion_amplia_en = $post->descripcion_amplia_en;
                $revision_descripcion_amplia_en = $revision->descripcion_amplia_en;

                // Pizarra o iD vehiculo
                $post_pizarra = $post->pizarra_o_id_vehiculo_inversion;
                $revision_pizarra = $revision->pizarra_o_id_vehiculo_inversion;

                // Tipo de proyecto
                $post_tipo_proyecto = isset($post->tipo_de_proyecto) ? get_the_title($post->tipo_de_proyecto) : '';
                $revision_tipo_proyecto = isset($revision->tipo_de_proyecto) ? get_the_title($revision->tipo_de_proyecto) : '';
                $post_tipo_proyecto_en = pll_get_post($post->tipo_de_proyecto, "en") != NULL ? get_the_title(pll_get_post($post->tipo_de_proyecto, "en")) : '';
                $revision_tipo_proyecto_en = pll_get_post($revision->tipo_de_proyecto, "en") != NULL ? get_the_title(pll_get_post($revision->tipo_de_proyecto, "en")) : '';

                // Tipo de contrato
                $post_tipo_contrato = isset($post->tipo_contrato) ? get_the_title($post->tipo_contrato) : '';
                $revision_tipo_contrato = isset($revision->tipo_contrato) ? get_the_title($revision->tipo_contrato) : '';
                $post_tipo_contrato_en = pll_get_post($post->tipo_contrato, "en") != NULL ? get_the_title(pll_get_post($post->tipo_contrato, "en")) : '';
                $revision_tipo_contrato_en = pll_get_post($revision->tipo_contrato, "en") != NULL ? get_the_title(pll_get_post($revision->tipo_contrato, "en")) : '';

                // Plazo del contrato
                $post_plazo_contrato = $post->plazo_contrato_proyecto;
                $revision_plazo_contrato = $revision->plazo_contrato_proyecto;

                if ($post_plazo_contrato == 1) {
                    if ($language == 'en_US' || $idioma == 'en') {
                        $post_plazo_contrato = $post_plazo_contrato . ' year';
                    } else {
                        $post_plazo_contrato = $post_plazo_contrato . ' año';
                    }
                }

                if ($post_plazo_contrato > 1) {
                    if ($language == 'en_US' || $idioma == 'en') {
                        $post_plazo_contrato = $post_plazo_contrato . ' years';
                    } else {
                        $post_plazo_contrato = $post_plazo_contrato . ' años';
                    }
                }

                if ($revision_plazo_contrato == 1) {
                    if ($language == 'en_US' || $idioma == 'en') {
                        $revision_plazo_contrato = $revision_plazo_contrato . ' year';
                    } else {
                        $revision_plazo_contrato = $revision_plazo_contrato . ' año';
                    }
                }

                if ($revision_plazo_contrato > 1) {
                    if ($language == 'en_US' || $idioma == 'en') {
                        $revision_plazo_contrato = $revision_plazo_contrato . ' years';
                    } else {
                        $revision_plazo_contrato = $revision_plazo_contrato . ' años';
                    }
                }

                // Proceso de seleccion
                $post_proceso_seleccion = isset($post->proceso_de_seleccion) ? get_the_title($post->proceso_de_seleccion) : '';
                $revision_proceso_seleccion = isset($revision->proceso_de_seleccion) ? get_the_title($revision->proceso_de_seleccion) : '';
                $post_proceso_seleccion_en = pll_get_post($post->proceso_de_seleccion, "en") != NULL ? get_the_title(pll_get_post($post->proceso_de_seleccion, "en")) : '';
                $revision_proceso_seleccion_en = pll_get_post($revision->proceso_de_seleccion, "en") != NULL ? get_the_title(pll_get_post($revision->proceso_de_seleccion, "en")) : '';

                // Fuente de pago
                $post_fuente_pago = isset($post->fuente_pago_proyecto) ? get_the_title($post->fuente_pago_proyecto) : '';
                $revision_fuente_pago = isset($revision->fuente_pago_proyecto) ? get_the_title($revision->fuente_pago_proyecto) : '';
                $post_fuente_pago_en = pll_get_post($post->fuente_pago_proyecto, "en") != NULL ? get_the_title(pll_get_post($post->fuente_pago_proyecto, "en")) : '';
                $revision_fuente_pago_en = pll_get_post($revision->fuente_pago_proyecto, "en") != NULL ? get_the_title(pll_get_post($revision->fuente_pago_proyecto, "en")) : '';

                // Descripcion de la fuente de pago
                $post_descripcion_fuente_pago = $post->descripcion_fuente_de_pago;
                $revision_descripcion_fuente_pago = $revision->descripcion_fuente_de_pago;
                $post_descripcion_fuente_pago_en = $post->descripcion_fuente_de_pago_en;
                $revision_descripcion_fuente_pago_en = $revision->descripcion_fuente_de_pago_en;

                // Etapa
                $post_etapa = isset($post->etapa_proyecto) ? get_the_title($post->etapa_proyecto) : '';
                $revision_etapa = isset($revision->etapa_proyecto) ? get_the_title($revision->etapa_proyecto) : '';
                $post_etapa_en = pll_get_post($post->etapa_proyecto, "en") != NULL ? get_the_title(pll_get_post($post->etapa_proyecto, "en")) : '';
                $revision_etapa_en = pll_get_post($revision->etapa_proyecto, "en") != NULL ? get_the_title(pll_get_post($revision->etapa_proyecto, "en")) : '';

                // Subetapa
                $post_subetapa = isset($post->subetapa_proyecto) ? get_the_title($post->subetapa_proyecto) : '';
                $revision_subetapa = isset($revision->subetapa_proyecto) ? get_the_title($revision->subetapa_proyecto) : '';
                $post_subetapa_en = pll_get_post($post->subetapa_proyecto, "en") != NULL ? get_the_title(pll_get_post($post->subetapa_proyecto, "en")) : '';
                $revision_subetapa_en = pll_get_post($revision->subetapa_proyecto, "en") != NULL ?  get_the_title(pll_get_post($revision->subetapa_proyecto, "en")) : '';

                // Estados
                $post_estados = "";
                $revision_estados = "";

                if( have_rows('estados_ubicacion', $post->ID)):
                    while( have_rows('estados_ubicacion', $post->ID) ) : the_row();
                        $post_estados = $post_estados . get_the_title(get_sub_field('estado_ubicacion')).", ";
                    endwhile;

                    $post_estados = substr($post_estados, 0, -2);
                endif;

                if( have_rows('estados_ubicacion', $revision->ID)):
                    while( have_rows('estados_ubicacion', $revision->ID) ) : the_row();
                        $revision_estados = $revision_estados . get_the_title(get_sub_field('estado_ubicacion')).", ";
                    endwhile;

                    $revision_estados = substr($revision_estados, 0, -2);
                endif;

                // Geolocalizacion
                $post_geolocalizacion = $post->geolocalizacion_proyecto;
                $revision_geolocalizacion = $revision->geolocalizacion_proyecto;

                // Entidad regulatorio
                $post_entidad = isset($post->entidad_reguladora_sectorial) ? get_the_title($post->entidad_reguladora_sectorial) : '';
                $revision_entidad = isset($revision->entidad_reguladora_sectorial) ? get_the_title($revision->entidad_reguladora_sectorial) : '';
                $post_entidad_en = pll_get_post($post->entidad_reguladora_sectorial,"en") != NULL ? get_the_title(pll_get_post($post->entidad_reguladora_sectorial,"en")) : '';
                $revision_entidad_en = pll_get_post($revision->entidad_reguladora_sectorial,"en") != NULL ? get_the_title(pll_get_post($revision->entidad_reguladora_sectorial,"en")) : '';

                // Area responsable
                $post_area_responsable = get_field('institucion_y_área_responsable', $post->ID);
                $revision_area_responsable = get_field('institucion_y_área_responsable', $revision->ID);

                // Contacto
                $post_contacto = $post->contacto_proyecto;
                $revision_contacto = $revision->contacto_proyecto;

                // Correo electronico
                $post_correo = $post->coreo_contacto_proyecto;
                $revision_correo = $revision->coreo_contacto_proyecto;

                // Fuentes
                $post_fuentes = $post->fuentes_proyecto;
                $revision_fuentes = $revision->fuentes_proyecto;

                // Imagenes
                $post_imagenes = $post->galeria_imagenes_proyecto;
                $revision_imagenes = $revision->galeria_imagenes_proyecto;

                // Videos
                $post_videos = $post->videos_proyecto;
                $revision_videos = $revision->videos_proyecto;

                // Observaciones
                $post_observaciones = $post->observaciones_proyecto;
                $revision_observaciones = $revision->observaciones_proyecto;
                $post_observaciones_en = $post->observaciones_en;
                $revision_observaciones_en = $revision->observaciones_en;

                // Pospuesto
                $post_pospuesto = $post->pospuesto;
                $revision_pospuesto = $revision->pospuesto;

                // Cancelado
                $post_cancelado = $post->cancelacion;
                $revision_cancelado = $revision->cancelacion;

                // Descripcion ejecutiva
                $post_descripcion_ejecutiva = $post->descripcion_ejecutiva_es;
                $revision_descripcion_ejecutiva = $revision->descripcion_ejecutiva_es;
                $post_descripcion_ejecutiva_en = $post->descripcion_ejecutiva_en;
                $revision_descripcion_ejecutiva_en = $revision->descripcion_ejecutiva_en;             
                if ($id_revision_usar != 0) {
                    if ($language == 'en_US' || $idioma == 'en') {
                        if ($post_sector_en != $revision_sector_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Sector', $revision_sector_en, $post_sector_en);

                            $cambios++;
                        }

                        if ($post_subsector_en != $revision_subsector_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Subsector', $revision_subsector_en, $post_subsector_en);

                            $cambios++;
                        }

                        if ($post_alias_en != $revision_alias_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Alias', strtoupper($revision_alias_en), strtoupper($post_alias_en));
                            $cambios++;
                        }

                        /*if ($post_fecha_ultima_actualizacion != $revision_fecha_ultima_actualizacion) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Last Update', $revision_fecha_ultima_actualizacion, $post_fecha_ultima_actualizacion);
                            $cambios++;
                        }*/

                        if ($post_titulo_en != $revision_titulo_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Project Name', $revision_titulo_en, $post_titulo_en);
                            $cambios++;
                        }

                        if ($post_proyecto_verde != $revision_proyecto_verde) {
                            $revision_proyecto_verde_respuesta = '';
                            $post_proyecto_verde_respuesta = '';

                            if ($revision_proyecto_verde == 1) {
                                $revision_proyecto_verde_respuesta = 'Yes';
                            }

                            if ($revision_proyecto_verde == 0) {
                                $revision_proyecto_verde_respuesta = 'No';
                            }

                            if ($post_proyecto_verde == 1) {
                                $post_proyecto_verde_respuesta = 'Yes';
                            }

                            if ($post_proyecto_verde == 0) {
                                $post_proyecto_verde_respuesta = 'No';
                            }

                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'BANOBRAS/FONADIN INVOLVEMENT', $revision_proyecto_verde_respuesta, $post_proyecto_verde_respuesta);
                            $cambios++;
                        }

                        if ($post_tipo_inversion_en != $revision_tipo_inversion_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Type of Investment', $revision_tipo_inversion_en, $post_tipo_inversion_en);
                            $cambios++;
                        }

                        if ($activos_original_en != $activos_revision_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Asset(s)', $activos_revision_en, $activos_original_en);
                            $cambios++;
                        }

                        if ($post_moneda != $revision_moneda) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contract Currency', $revision_moneda, $post_moneda);
                            $cambios++;
                        }

                        if ($post_monto_inversion != $revision_monto_inversion) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Estimated Investment', $revision_monto_inversion, $post_monto_inversion);
                            $cambios++;
                        }

                        if ($post_alcance_contrato_en != $revision_alcance_contrato_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contract Scope', $revision_alcance_contrato_en, $post_alcance_contrato_en);
                            $cambios++;
                        }

                        if ($post_descripcion_amplia_en != $revision_descripcion_amplia_en) {
                            $tr .=Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Change in description');
                            $cambios++;
                        }

                        if ($post_pizarra != $revision_pizarra) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Ticker symbol', $revision_pizarra, $post_pizarra);
                            $cambios++;
                        }

                        if ($post_tipo_proyecto_en != $revision_tipo_proyecto_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Type of Project', $revision_tipo_proyecto_en, $post_tipo_proyecto_en);
                            $cambios++;
                        }

                        if ($post_tipo_contrato_en != $revision_tipo_contrato_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Type of Contract', $revision_tipo_contrato_en, $post_tipo_contrato_en);
                            $cambios++;
                        }

                        if ($post_plazo_contrato != $revision_plazo_contrato) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Term', $revision_plazo_contrato, $post_plazo_contrato);
                            $cambios++;
                        }

                        
                        if ($post_proceso_seleccion_en != $revision_proceso_seleccion_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Selection Process', $revision_proceso_seleccion_en, $post_proceso_seleccion_en);
                            $cambios++;
                        }

                        if ($post_fuente_pago_en != $revision_fuente_pago_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Payment source', $revision_fuente_pago_en, $post_fuente_pago_en);
                            $cambios++;
                        }

                        if ($post_descripcion_fuente_pago_en != $revision_descripcion_fuente_pago_en) {
                            $tr .=Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Change in description payment source');
                            $cambios++;
                        }

                        if ($post_etapa_en != $revision_etapa_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Stage', $revision_etapa_en, $post_etapa_en);
                            $cambios++;
                        }

                        if ($post_subetapa_en != $revision_subetapa_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Substage', $revision_subetapa_en, $post_subetapa_en);
                            $cambios++;
                        }

                        if ($post_estados != $revision_estados) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'State(s)', $revision_estados, $post_estados);
                            $cambios++;
                        }

                        if ($post_geolocalizacion != $revision_geolocalizacion) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Geolocation', $revision_geolocalizacion, $post_geolocalizacion);
                            $cambios++;
                        }

                        if ($post_entidad_en != $revision_entidad_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Entity', $revision_entidad_en, $post_entidad_en);
                            $cambios++;
                        }

                        if ($post_area_responsable != $revision_area_responsable) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Department', $revision_area_responsable, $post_area_responsable);
                            $cambios++;
                        }

                        if ($post_contacto != $revision_contacto) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'E-mail', $revision_contacto, $post_contacto);
                            $cambios++;
                        }

                        if ($post_correo != $revision_correo) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contact', $revision_correo, $post_correo);
                            $cambios++;
                        }

                        if ($post_imagenes != $revision_imagenes) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Images', $revision_imagenes, $post_imagenes);
                            $cambios++;
                        }

                        if ($post_videos != $revision_videos) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Videos', $revision_videos, $post_videos);
                        }

                        if ($post_observaciones_en != $revision_observaciones_en) {
                            $tr .=Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Change in remarks');
                            $cambios++;
                        }

                        if ($post_descripcion_ejecutiva_en != $revision_descripcion_ejecutiva_en) {
                            $tr .=Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Change in executive description');
                            $cambios++;
                        }
                    } else {
                        if ($post_sector != $revision_sector) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Sector', $revision_sector, $post_sector);
                            $cambios++;
                        }

                        if ($post_subsector != $revision_subsector) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Subsector', $revision_subsector, $post_subsector);
                            $cambios++;
                        }

                        if ($post_alias != $revision_alias) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Alias', strtoupper($revision_alias), strtoupper($post_alias));
                            $cambios++;
                        }

                        /*if ($post_fecha_ultima_actualizacion != $revision_fecha_ultima_actualizacion) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Fecha de actualizacion', $revision_fecha_ultima_actualizacion, $post_fecha_ultima_actualizacion);
                            $cambios++;
                        }*/

                        if ($post_titulo != $revision_titulo) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Nombre del Proyecto', $revision_titulo, $post_titulo);
                            $cambios++;
                        }

                        if ($post_proyecto_verde != $revision_proyecto_verde) {
                            $revision_proyecto_verde_respuesta = '';
                            $post_proyecto_verde_respuesta = '';

                            if ($revision_proyecto_verde == 1) {
                                $revision_proyecto_verde_respuesta = 'Sí';
                            }

                            if ($revision_proyecto_verde == 0) {
                                $revision_proyecto_verde_respuesta = 'No';
                            }

                            if ($post_proyecto_verde == 1) {
                                $post_proyecto_verde_respuesta = 'Sí';
                            }

                            if ($post_proyecto_verde == 0) {
                                $post_proyecto_verde_respuesta = 'No';
                            }

                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'PARTICIPACI&#211;N BANOBRAS/FONADIN', $revision_proyecto_verde_respuesta, $post_proyecto_verde_respuesta);
                            $cambios++;
                        }

                        if ($post_tipo_inversion != $revision_tipo_inversion) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Tipo de inversi&#211;n', $revision_tipo_inversion, $post_tipo_inversion);
                            $cambios++;
                        }

                        if ($activos_original != $activos_revision) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Activo', $activos_revision, $activos_original);
                            $cambios++;
                        }

                        if ($post_moneda != $revision_moneda) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Moneda del contrato', $revision_moneda, $post_moneda);
                            $cambios++;
                        }

                        if ($post_monto_inversion != $revision_monto_inversion) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Inversi&#211;n estimada', $revision_monto_inversion, $post_monto_inversion);
                            $cambios++;
                        }

                        if ($post_alcance_contrato != $revision_alcance_contrato) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Alcances del contrato', $revision_alcance_contrato, $post_alcance_contrato);
                            $cambios++;
                        }

                        if ($post_descripcion_amplia != $revision_descripcion_amplia) {
                            $tr .=Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Cambio de descripci&#211;n', $revision_descripcion_amplia);
                            $cambios++;
                        }

                        if ($post_pizarra != $revision_pizarra) {
                            $tr .=  Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Pizarra', $revision_pizarra, $post_pizarra);
                            $cambios++;
                        }

                        if ($post_tipo_proyecto != $revision_tipo_proyecto) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Tipo de proyecto', $revision_tipo_proyecto, $post_tipo_proyecto);
                            $cambios++;
                        }

                        if ($post_tipo_contrato != $revision_tipo_contrato) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Tipo de contrato', $revision_tipo_contrato, $post_tipo_contrato);
                            $cambios++;
                        }

                        if ($post_plazo_contrato != $revision_plazo_contrato) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Plazo de contrato', $revision_plazo_contrato, $post_plazo_contrato);
                            $cambios++;
                        }

                        if ($post_proceso_seleccion != $revision_proceso_seleccion) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Proceso de selecci&#211;n', $revision_proceso_seleccion, $post_proceso_seleccion);
                            $cambios++;
                        }

                        if ($post_fuente_pago != $revision_fuente_pago) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Fuente de pago', $revision_fuente_pago, $post_fuente_pago);
                            $cambios++;
                        }

                        if ($post_descripcion_fuente_pago != $revision_descripcion_fuente_pago) {
                            $tr .=Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Cambio en descripci&#211;n fuente de pago');
                            $cambios++;
                        }

                        if ($post_etapa != $revision_etapa) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Etapa', $revision_etapa, $post_etapa);
                            $cambios++;
                        }

                        if ($post_subetapa != $revision_subetapa) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Subetapa', $revision_subetapa, $post_subetapa);
                            $cambios++;
                        }

                        if ($post_estados != $revision_estados) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Estados', $revision_estados, $post_estados);
                            $cambios++;
                        }

                        if ($post_geolocalizacion != $revision_geolocalizacion) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Geolocalizaci&#211;n', $revision_geolocalizacion, $post_geolocalizacion);
                            $cambios++;
                        }

                        if ($post_entidad != $revision_entidad) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Entidad', $revision_entidad, $post_entidad);
                            $cambios++;
                        }

                        if ($post_area_responsable != $revision_area_responsable) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, '&#193;rea responsable', $revision_area_responsable, $post_area_responsable);
                            $cambios++;
                        }

                        if ($post_contacto != $revision_contacto) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contacto', $revision_contacto, $post_contacto);
                            $cambios++;
                        }

                        if ($post_correo != $revision_correo) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Correo', $revision_correo, $post_correo);
                            $cambios++;
                        }

                        if ($post_imagenes != $revision_imagenes) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Im&#193;genes', $revision_imagenes, $post_imagenes);
                            $cambios++;
                        }

                        if ($post_videos != $revision_videos) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Videos', $revision_videos, $post_videos);
                            $cambios++;
                        }

                        if ($post_observaciones != $revision_observaciones) {
                            $tr .=Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Cambio en observaciones');
                            $cambios++;
                        }

                        if ($post_descripcion_ejecutiva != $revision_descripcion_ejecutiva) {
                            $tr .=Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Cambio en descripci&#211;n ejecutiva');
                            $cambios++;
                        }
                    }
                }

                //REVISION EN CAMBIO DE FECHA DE REGISTRO UI   ---PARTE 3---
                $content2_fecha_ui = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    
                    if (have_rows('registro_ui_proyecto', $This_id)):
                        while (have_rows('registro_ui_proyecto', $This_id)): the_row();
                    if (get_sub_field('fecha_registroui_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_registroui_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_registroui_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_registroui_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_registroui_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_registroui_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_registroui_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_registroui_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_registro_ui    = $fecha_tmp;
                    $fecha_registro_ui_en = $fecha_tpm_en;

                    $fecha_tmp2    = "";
                    $fecha_tpm_en2 = "";
                    if (have_rows('registro_ui_proyecto', $id_revision_usar)):
                        while (have_rows('registro_ui_proyecto', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_registroui_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_registroui_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp2    = $date->format('d/m/Y');
                        $fecha_tpm_en2 = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_registroui_proyecto')):
                                    $fecha_tmp2    = get_the_title(get_sub_field('plazo_registroui_proyecto')) . " ";
                        $fecha_tpm_en2 = get_the_title(pll_get_post(get_sub_field('plazo_registroui_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_registroui_proyecto')):
                                    $fecha_tmp2    = $fecha_tmp2 . get_sub_field('año_plazo_registroui_proyecto') . " ";
                        $fecha_tpm_en2 = $fecha_tpm_en2 . get_sub_field('año_plazo_registroui_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_registro_ui_revision    = $fecha_tmp2;
                    $fecha_registro_ui_revision_en = $fecha_tpm_en2;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_registro_ui_en != $fecha_registro_ui_revision_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Ministry of Finance Registration or Equivalent', $fecha_registro_ui_revision_en, $fecha_registro_ui_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_registro_ui != $fecha_registro_ui_revision) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Registro de Unidad de Inversiones o equivalente', $fecha_registro_ui_revision, $fecha_registro_ui);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE REGISTRO UI ---PARTE 3 ---

                //REVISION EN CAMBIO DE FECHA DE CONVOCATORIA   ---PARTE 4---
                $content2_fecha_convocatoria = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('convocatoria_proyecto', $This_id)):
                        while (have_rows('convocatoria_proyecto', $This_id)): the_row();
                    if (get_sub_field('fecha_convocatoria_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_convocatoria_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_convocatoria_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_convocatoria_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_convocatoria_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_convocatoria_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_convocatoria_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_convocatoria_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_convocatoria    = $fecha_tmp;
                    $fecha_convocatoria_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('convocatoria_proyecto', $id_revision_usar)):
                        while (have_rows('convocatoria_proyecto', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_convocatoria_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_convocatoria_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_convocatoria_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_convocatoria_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_convocatoria_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_convocatoria_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_convocatoria_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_convocatoria_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_convocatoria_revision    = $fecha_tmp;
                    $fecha_convocatoria_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_convocatoria_en != $fecha_convocatoria_revision_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Request for Proposals/Announcement', $fecha_convocatoria_revision_en, $fecha_convocatoria_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_convocatoria != $fecha_convocatoria_revision) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Anuncio/Convocatoria', $fecha_convocatoria_revision, $fecha_convocatoria);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE CONVOCATORIA ---PARTE 4 ---

                //REVISION EN CAMBIO DE RECEPCIÃ“N DE PROPUESTAS   ---PARTE 5---
                $content2_fecha_fallo = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('fallo_proyecto', $This_id)):
                        while (have_rows('fallo_proyecto', $This_id)): the_row();
                    if (get_sub_field('fecha_fallo_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_fallo_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_fallo_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_fallo_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_fallo_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_fallo_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_fallo_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_fallo_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_fallo    = $fecha_tmp;
                    $fecha_fallo_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('fallo_proyecto', $id_revision_usar)):
                        while (have_rows('fallo_proyecto', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_fallo_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_fallo_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_fallo_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_fallo_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_fallo_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_fallo_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_fallo_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_fallo_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_fallo_revision    = $fecha_tmp;
                    $fecha_fallo_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_fallo_en != $fecha_fallo_revision_en) {
                            $tr .=  Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Ruling', $fecha_fallo_revision_en, $fecha_fallo_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_fallo != $fecha_fallo_revision) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Fallo', $fecha_fallo_revision, $fecha_fallo);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE RECEPCIÃ“N DE PROPUESTAS ---PARTE 5 ---

                //REVISION EN FECHA DE FALLO   ---PARTE 6---
                $content2_fecha_propuestas = "";
                if ($post_revision->ID != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('propuestas_proyecto', $This_id)):
                        while (have_rows('propuestas_proyecto', $This_id)): the_row();
                    if (get_sub_field('fecha_propuestas_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_propuestas_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_propuestas_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_propuestas_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_propuestas_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_propuestas_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_propuestas_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_propuestas_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_recepcion    = $fecha_tmp;
                    $fecha_recepcion_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('propuestas_proyecto', $post_revision->ID)):
                        while (have_rows('propuestas_proyecto', $post_revision->ID)): the_row();
                    if (get_sub_field('fecha_propuestas_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_propuestas_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_propuestas_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_propuestas_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_propuestas_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_propuestas_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_propuestas_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_propuestas_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_recepcion_revision    = $fecha_tmp;
                    $fecha_recepcion_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_recepcion_en != $fecha_recepcion_revision_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Proposals Reception', $fecha_recepcion_revision_en, $fecha_recepcion_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_recepcion != $fecha_recepcion_revision) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Recepci&#211;n de propuestas', $fecha_recepcion_revision, $fecha_recepcion);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE FALLO ---PARTE 6 ---

                //REVISION EN FECHA DE FIRMA DE CONTRATO   ---PARTE 7---
                $content2_fecha_firma = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('firma_contrato_proyecto', $This_id)):
                        while (have_rows('firma_contrato_proyecto', $This_id)): the_row();
                    if (get_sub_field('fecha_firma_contrato_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_firma_contrato_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_firma_contrato_proyecto')):
                            $fecha_tmp    = get_the_title(get_sub_field('plazo_firma_contrato_proyecto')) . " ";
                            $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_firma_contrato_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_firma_contrato_proyecto')):
                            $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_firma_contrato_proyecto') . " ";
                            $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_firma_contrato_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_firma    = $fecha_tmp;
                    $fecha_firma_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('firma_contrato_proyecto', $id_revision_usar)):
                        while (have_rows('firma_contrato_proyecto', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_firma_contrato_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_firma_contrato_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_firma_contrato_proyecto')):
                            $fecha_tmp    = get_the_title(get_sub_field('plazo_firma_contrato_proyecto')) . " ";
                            $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_firma_contrato_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_firma_contrato_proyecto')):
                            $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_firma_contrato_proyecto') . " ";
                            $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_firma_contrato_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_firma_revision    = $fecha_tmp;
                    $fecha_firma_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_firma_en != $fecha_firma_revision_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contract Signing', $fecha_firma_revision_en, $fecha_firma_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_firma != $fecha_firma_revision) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Firma contrato', $fecha_firma_revision, $fecha_firma);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE FIRMA DE CONTRATO   ---PARTE 7---

                //REVISION EN FECHA DE INICIO DE EJECUCION   ---PARTE 8---
                $content2_fecha_ejecucion = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('inicio_ejecucion_proyecto', $This_id)):
                        while (have_rows('inicio_ejecucion_proyecto', $This_id)): the_row();
                    if (get_sub_field('fecha_inicio_ejecucion_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_inicio_ejecucion_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_inicio_ejecucion_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_inicio_ejecucion_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_inicio_ejecucion_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_inicio_ejecucion__proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_inicio_ejecucion__proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_inicio_ejecucion__proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_ejecucion    = $fecha_tmp;
                    $fecha_ejecucion_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('inicio_ejecucion_proyecto', $id_revision_usar)):
                        while (have_rows('inicio_ejecucion_proyecto', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_inicio_ejecucion_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_inicio_ejecucion_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_inicio_ejecucion_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_inicio_ejecucion_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_inicio_ejecucion_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_inicio_ejecucion__proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_inicio_ejecucion__proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_inicio_ejecucion__proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_ejecucion_revision    = $fecha_tmp;
                    $fecha_ejecucion_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_ejecucion_en != $fecha_ejecucion_revision_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Execution/Construction Starting Date', $fecha_ejecucion_revision_en, $fecha_ejecucion_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_ejecucion != $fecha_ejecucion_revision) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Inicio de ejecuci&#211;n/Construcci&#211;n', $fecha_ejecucion_revision, $fecha_ejecucion);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE INICIO DE EJECUCION  ---PARTE 8---

                //REVISION EN FECHA DE INICIO DE OPERACION   ---PARTE 9---
                $content2_fecha_operacion = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('inicio_de_operacion', $This_id)):
                        while (have_rows('inicio_de_operacion', $This_id)): the_row();
                    if (get_sub_field('fecha_inicio_operacion_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_inicio_operacion_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_inicio_operacion_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_inicio_operacion_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_inicio_operacion_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_inicio_operacion__proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_inicio_operacion__proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_inicio_operacion__proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_operacion    = $fecha_tmp;
                    $fecha_operacion_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('inicio_de_operacion', $id_revision_usar)):
                        while (have_rows('inicio_de_operacion', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_inicio_operacion_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_inicio_operacion_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_inicio_operacion_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_inicio_operacion_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_inicio_operacion_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_inicio_operacion__proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_inicio_operacion__proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_inicio_operacion__proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_operacion_revision    = $fecha_tmp;
                    $fecha_operacion_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_operacion_en != $fecha_operacion_revision_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Operation Starting Date', $fecha_operacion_revision_en, $fecha_operacion_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_operacion != $fecha_operacion_revision) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Inicio de Operaci&#211;n', $fecha_operacion_revision, $fecha_operacion);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE INICIO DE OPERACION  ---PARTE 9---

                //REVISION EN FECHA DE CONCLUSION   ---PARTE 10---
                $content2_fecha_conclusion = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('termino_vigencia_del_contrato', $This_id)):
                        while (have_rows('termino_vigencia_del_contrato', $This_id)): the_row();
                    if (get_sub_field('fecha_vigencia_contrato_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_vigencia_contrato_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_vigencia_contrato_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_vigencia_contrato_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_vigencia_contrato_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_vigencia_contrato_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_vigencia_contrato_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_vigencia_contrato_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_conclusion    = $fecha_tmp;
                    $fecha_conclusion_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('termino_vigencia_del_contrato', $id_revision_usar)):
                        while (have_rows('termino_vigencia_del_contrato', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_vigencia_contrato_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_vigencia_contrato_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_vigencia_contrato_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_vigencia_contrato_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_vigencia_contrato_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_vigencia_contrato_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_vigencia_contrato_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_vigencia_contrato_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_conclusion_revision    = $fecha_tmp;
                    $fecha_conclusion_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_conclusion_en != $fecha_conclusion_revision_en) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Term of the Contract', $fecha_conclusion_revision_en, $fecha_conclusion_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_conclusion != $fecha_conclusion_revision) {
                            $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Término de la vigencia del contrato', $fecha_conclusion_revision, $fecha_conclusion);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE CONCLUSION  ---PARTE 10---

                //REVISION EN CAMBIO DE ADJUDICATARIO   ---PARTE 11---
                $content2_adjudicatario = "";
                $adjudicatario_original = get_field('nombre_adjudicatario_contrato_proyecto', $This_id);
                $adjudicatario_anterior = get_field('nombre_adjudicatario_contrato_proyecto', $post_revision->ID);
                if ($language == 'en_US' or $idioma == 'en') {
                    if ($adjudicatario_original != $adjudicatario_anterior) {
                        $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contractor', $adjudicatario_anterior, $adjudicatario_original);
                        $cambios++;
                    }
                } else {
                    if ($adjudicatario_original != $adjudicatario_anterior) {
                        $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Nombre del adjudicatario del contrato', $adjudicatario_anterior, $adjudicatario_original);
                        $cambios++;
                    }
                }
                //FIN DE REVISION EN ADJUDICATARIO ---PARTE 11 ---

                //REVISION EN CAMBIO DE EMPRESAS ASOCIADAS AL ADJUDICATARIO   ---PARTE 12---
                $content2_empresas = "";
                $empresas_original = "";
                while (have_rows('empresa_a_las_que_pertenece', $This_id)): the_row();
                $empresas_original .= get_sub_field('nombre_empresa_proyecto', $id_post) . ", ";
                endwhile;
                $empresas_original = substr($empresas_original, 0, -2);

                $empresas_revision = "";
                while (have_rows('empresa_a_las_que_pertenece', $post_revision->ID)): the_row();
                $empresas_revision .= get_sub_field('nombre_empresa_proyecto', $id_revision_usar) . ", ";
                endwhile;
                $empresas_revision = substr($empresas_revision, 0, -2);

                if ($language == 'en_US' or $idioma == 'en') {
                    if ($empresas_original != $empresas_revision) {
                        $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Major Contractor’s Shareholders', $empresas_revision, $empresas_original);
                        $cambios++;
                    }
                } else {
                    if ($empresas_original != $empresas_revision) {
                        $tr .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Empresas asociadas al adjudicatario', $empresas_revision, $empresas_original);
                        $cambios++;
                    }
                }
                //FIN DE REVISION EN CAMBIO DE EMPRESAS ASOCIADAS AL ADJUDICATARIO ---PARTE 12 ---

                //$content2 .= $tr;
                $tr .= '
                <div style="text-align:center !important;">
                    <!--<a href="'.network_site_url() . '/notificacion-baja/?mail=' . $mail . '&id=' . $id.'" target="_blank">                        
                        <img src="'.$btn_unsubscribe.'" class="imgr96" style="outline: none;border: 0;text-decoration: none;height:55px!important;width:170px!important;" width="35%">
                    </a>-->
                    '.$btn.'
                </div>';            
            }

            $footer = Footer_references_Shortcode($language, '', $mail, false,'',false,'');

            return $header . $tr . $footer;
        } else {
            return 'SIN REGISTROS';
        }
    }
}

//second notification shortcode
function get_mail_preferences_shortcode($attr, $content = null)
{
    $mail     = $_GET['mail'];
    $date     = $_GET['date'];
    $language = $_GET['language'];
    extract(shortcode_atts(array(
        'mail'     => $mail,
        'language' => $language,
        'date'     => $date,
    ), $atts));
    global $wpdb;
    $table_name   = $wpdb->prefix . 'bancomext_users_reports';
    $time         = current_time('mysql');
    $dias_options = get_field('rango_en_dias_follow', 'option');
    //ECHO 'aquiii '.date('Ymd', strtotime('-'.$dias_options.' days'));
    if ($mail && $date == '') {
        $mail_template = construct_custom_post($mail, $date);
        //var_dump($mail_template);
        $this_query_mail_direct = $wpdb->get_results("SELECT wp_posts.ID
              FROM wp_posts
                INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )
                WHERE wp_postmeta.meta_key = 'correo_registro_reg_inversionista'
                AND wp_postmeta.meta_value = '$mail'
                AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'pending' OR wp_posts.post_status = 'draft')
                AND wp_posts.post_type = 'reg_inversionistas' ");
        foreach ($this_query_mail_direct as $items_mail) {
            $myid_mail = $items_mail->ID;
        }

        //$idioma = get_field('idioma_de_preferencia_reg_inversionista', $myid_mail);

        $the_lang = $wpdb->get_results("
            SELECT wp_postmeta.meta_value AS lang
            FROM wp_postmeta
            WHERE wp_postmeta.meta_key='idioma_de_preferencia_reg_inversionista'
            AND wp_postmeta.post_id = ".$myid_mail."
        ");

        foreach ($the_lang as $user) {
            $idioma = $user->lang;
        }

        //$interes = get_field('interes_en_proyectos_reg_inversionista', $myid);
        //var_dump($mail_template);
        //RETURN FOR TESTING
        //return '---'.$mail_template;
        if (!empty($mail_template)) {
            $values              = array();
            $ids                 = join("','", $mail_template);
            $args_general_direct = $wpdb->get_results("SELECT ID
                FROM wp_posts
                WHERE post_type = 'proyecto_inversion'
                AND ID IN ('$ids')");
            $rowcount = $wpdb->num_rows;
            if ($language == 'en_US' || $idioma == 'en') {
                $content1 = '<table border="0" style="border:none">
                            <thead>
                              <tr  border="0" style="border:none">
                                <th  border="0" style="border:none">Name of Project: (Total: ' . $rowcount . ')</th>
                              </tr>
                            </thead>
                            <tbody>';
            } else {
                $content1 = '<table border="0" style="border:none">
                            <thead>
                              <tr  border="0" style="border:none">
                                <th  border="0" style="border:none">' . __("Nombre de Proyecto", 'postcontact') . ' (Total: ' . $rowcount . ')</th>
                              </tr>
                            </thead>
                            <tbody>';
            }
            //var_dump($args_general_direct);
            foreach ($args_general_direct as $items) {
                $id_post = $items->ID;
                //$folio = get_field('ID_PROYECTO',$id_post);
                error_log('------------------ SI HAY REGISTROS ' . $language);
                $wpdb->insert($table_name, array('idioma' => $language, 'follow' => $id_post, 'email' => $mail, 'report' => $time, 'proceso' => 'Preferencias Inicial', 'status' => 'Generado'));
                if ($language == 'en_US' or $idioma == 'en') {
                    $content2 .= '<tr border="0" style="border:none">
                                <td border="0" style="border:none"><a href="' . get_post_permalink($id_post) . '?language=en" target="_blank">' . get_the_title($id_post) . '</a></td>
                              </tr>';
                } else {
                    $content2 .= '<tr border="0" style="border:none">
                                <td border="0" style="border:none"><a href="' . get_post_permalink($id_post) . '" target="_blank">' . get_the_title($id_post) . '</a></td>
                              </tr>';
                }
            }
            $content3 = '</tbody>
              </table>';
            /*}*/
            return $content1 . $content2 . $content3;
        } else {
            return 'SIN REGISTROS';
        }
    }
    if ($mail && $date) {
        $mail_template = construct_custom_post($mail, $date);
        $this_query_id = $wpdb->get_results("SELECT wp_posts.ID
              FROM wp_posts
                INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )
                WHERE wp_postmeta.meta_key = 'correo_registro_reg_inversionista'
                AND wp_postmeta.meta_value = '$mail'
                AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'pending' OR wp_posts.post_status = 'draft')
                AND wp_posts.post_type = 'reg_inversionistas' ");
        foreach ($this_query_id as $items_mail) {
            $myid_mail = $items_mail->ID;
        }

        //$idioma = get_field('idioma_de_preferencia_reg_inversionista', $myid_mail);

        $the_lang = $wpdb->get_results("
            SELECT wp_postmeta.meta_value AS lang
            FROM wp_postmeta
            WHERE wp_postmeta.meta_key='idioma_de_preferencia_reg_inversionista'
            AND wp_postmeta.post_id = ".$myid_mail."
        ");

        foreach ($the_lang as $user) {
            $idioma = $user->lang;
        }

        $the_notification = $wpdb->get_results("
            SELECT wp_postmeta.meta_value AS notify
            FROM wp_postmeta
            WHERE wp_postmeta.meta_key='recibir_notificaciones_reg_inversionista'
            AND wp_postmeta.post_id = ".$myid_mail."
        ");
        
        foreach ($the_notification as $notification) {
            $notify1 = $notification->notify;            
        }

        if (!empty($mail_template) && strpos($notify1, "Si") !== false) {
            $values              = array();
            $ids                 = join("','", $mail_template);
            $args_general_direct = $wpdb->get_results("SELECT ID
                FROM wp_posts
                WHERE post_type = 'proyecto_inversion'
                AND ID IN ('$ids')");
            $rowcount = $wpdb->num_rows;
            
            $Header1 = 'Proyectos México';
            $Header2 = get_value_notifications('', '', '', 'cambio_registro_0_cuerpo_0_espaniol');
            if($Header2 =='')
            	$Header2 = 'Notificación de proyectos actualizados, según los criterios en su registro.';
            $Total = 'Total de proyectos modificados';
            $perz = get_value_notifications('', '', '', 'cambio_registro_0_personalizado_0_espaniol');
            if($language == 'en_US' or $idioma == 'en'){
                $Header1 = 'Mexico Project Hubs';
                $Header2 = get_value_notifications('', '', '', 'cambio_registro_0_cuerpo_0_ingles');
                if($Header2 == '')
                	$Header2 = 'Notification of updated projects, according to your registration criteria.';
                $Total = 'Total of modified projects';
                $perz = get_value_notifications('', '', '', 'cambio_registro_0_personalizado_0_ingles');
            }
            $content1 = Header_Preferences_Shortcode($rowcount, $Header1,$Header2,$Total, $idioma, $perz);
            //$content2 = Change_Of_Projects(true,$args_general_direct,'', $idioma, $language, 'Actualizacion de Preferencias', 'Generado update');
            
            foreach ($args_general_direct as $items) {
                $id_post = $items->ID;
                $wpdb->insert($table_name, array('idioma' => $idioma, 'follow' => $id_post, 'email' => $mail, 'report' => $time, 'proceso' => 'Actualización de Preferencias', 'status' => 'Generado update'));
                // Titulo de proyecto

                $cambios = 0;
                $post_original  = get_post($id_post);
                $post = $post_original;
                $post_revision = '';
                $post_date = get_field('fecha_ultima_actualizacion_proyecto', $id_post);
                $id_revision_usar = 0;
                $tr_sector = '';
                $tr_subsector = '';
                $tr_alias = '';
                $tr_fecha_ultima_actualizacion = '';
                $tr_titulo = '';
                $tr_proyecto_verde = '';
                $tr_tipo_inversion = '';
                $tr_activos = '';
                $tr_moneda = '';
                $tr_monto_inversion = '';
                $tr_pizarra = '';
                $tr_tipo_proyecto = '';
                $tr_tipo_contrato = '';
                $tr_plazo_contrato = '';
                $tr_proceso_seleccion = '';
                $tr_fuente_pago = '';
                $tr_etapa = '';
                $tr_subetapa = '';
                $tr_estados = '';
                $tr_geolocalizacion = '';
                $tr_entidad = '';
                $tr_area_responsable = '';
                $tr_contacto = '';
                $tr_correo = '';
                $tr_fuentes = '';
                $tr_imagenes = '';
                $tr_videos = '';
                $tr_pospuesto = '';
                $tr_cancelado = '';
                $tr_descripcion_amplia = '';
                $tr_observaciones = '';
                $tr_descripcion_fuente_pago = '';
                $tr_descripcion_ejecutiva = '';
                $tr_alcance_contrato = '';

                $revisions = wp_get_post_revisions($id_post);

                if ($revisions) {
                    foreach ($revisions as $key => $item) {
                        $review_date = get_field('fecha_ultima_actualizacion_proyecto', $item->ID);

                        if ((strpos($item->post_name, 'autosave') == false) && ($post_date != $review_date)) {
                            $post_revision = $item;
                            $revision = $item;
                            $id_revision_usar = $item->ID;
                            break;
                        }
                    }
                }

                $post_titulo = get_the_title($post->ID);
                $revision_titulo = get_the_title($revision->ID);
                $post_titulo_en = get_field('nombre_oficial_ingles', $post->ID);
                $revision_titulo_en = get_field('nombre_oficial_ingles', $revision->ID);

                $content2 .= Title_Proyect_Preferences_Shortcode($language, $idioma, $id_post, $post_titulo, $post_titulo_en);

                // Sector
                $post_sector = isset($post_original->sector_proyecto) ? get_the_title($post_original->sector_proyecto) : '';
                $revision_sector = isset($post_revision->sector_proyecto) ? get_the_title($post_revision->sector_proyecto) : '';
                $post_sector_en = pll_get_post($post_original->sector_proyecto, "en") != NULL ? get_the_title(pll_get_post($post_original->sector_proyecto, "en")) : '';
                $revision_sector_en = pll_get_post($post_revision->sector_proyecto, "en") != NULL ? get_the_title(pll_get_post($post_revision->sector_proyecto, "en")) : '';

                // Subsector
                $post_subsector = isset($post_original->subsector_proyecto) ? get_the_title($post_original->subsector_proyecto) : '';
                $revision_subsector = isset($post_revision->subsector_proyecto) ? get_the_title($post_revision->subsector_proyecto) : '';
                $post_subsector_en = pll_get_post($post_original->subsector_proyecto, "en") != NULL ? get_the_title(pll_get_post($post_original->subsector_proyecto, "en")) : '';
                $revision_subsector_en = pll_get_post($post_revision->subsector_proyecto, "en") != NULL ? get_the_title(pll_get_post($post_revision->subsector_proyecto, "en")) : '';

                // Alias
                $post_alias = $post_original->alias_proyecto;
                $revision_alias = $post_revision->alias_proyecto;
                $post_alias_en = $post_original->alias_proyecto_en;
                $revision_alias_en = $post_revision->alias_proyecto_en;

                // Fecha de ulltima actualizacion
                $post_fecha_ultima_actualizacion = $post->fecha_ultima_actualizacion_proyecto;
                $revision_fecha_ultima_actualizacion = $revision->fecha_ultima_actualizacion_proyecto;

                // Proyecto verde cambio a participacion banobras
                $post_proyecto_verde = $post->proyecto_verde;
                $revision_proyecto_verde = $revision->proyecto_verde;

                // Tipo de inverseion
                $post_tipo_inversion = isset($post->tipo_de_inversion) ? get_the_title($post->tipo_de_inversion) : '';
                $revision_tipo_inversion = isset($revision->tipo_de_inversion) ? get_the_title($revision->tipo_de_inversion) : '';
                $post_tipo_inversion_en = pll_get_post($post->tipo_de_inversion, "en") != NULL ? get_the_title(pll_get_post($post->tipo_de_inversion, "en")) : '';
                $revision_tipo_inversion_en = pll_get_post($revision->tipo_de_inversion, "en") != NULL ? get_the_title(pll_get_post($revision->tipo_de_inversion, "en")) : '';

                // Activos
                $activos_original = "&nbsp;";
                if (have_rows('activos', $post->ID)) {
                    $i = 0;
                    while (have_rows('activos', $post->ID)): the_row();
                        $k = $i - 1; //Para corregir el problema de que se empieza a contar de 0
                        if (get_sub_field('activo_proyecto')) {$activos_original = $activos_original . get_the_title(get_sub_field('activo_proyecto'));}
                        if (get_sub_field('cantidad_activo')) {$activos_original = $activos_original . ' ' . str_replace('.00', '', number_format(get_sub_field('cantidad_activo'), 2));}
                        if (get_sub_field('medida_activo')) {$activos_original = $activos_original . ' ' . get_the_title(get_sub_field('medida_activo'));}
                        if (get_sub_field('calidad')) {$activos_original = $activos_original . '-' . get_the_title(get_sub_field('calidad'));}
                        $activos_original = $activos_original . ", ";
                        $i++;
                    endwhile;
                    $activos_original = substr($activos_original, 0, -2);
                }

                $activos_revision = "&nbsp;";
                if (have_rows('activos', $revision->ID)) {
                    $i = 0;
                    while (have_rows('activos', $revision->ID)): the_row();
                        $k = $i - 1; //Para corregir el problema de que se empieza a contar de 0
                        if (get_sub_field('activo_proyecto')) {$activos_revision = $activos_revision . get_the_title(get_sub_field('activo_proyecto'));}
                        if (get_sub_field('cantidad_activo')) {$activos_revision = $activos_revision . ' ' . str_replace('.00', '', number_format(get_sub_field('cantidad_activo'), 2));}
                        if (get_sub_field('medida_activo')) {$activos_revision = $activos_revision . ' ' . get_the_title(get_sub_field('medida_activo'));}
                        if (get_sub_field('calidad')) {$activos_revision = $activos_revision . '-' . get_the_title(get_sub_field('calidad'));}
                        $activos_revision = $activos_revision . ", ";
                        $i++;
                    endwhile;
                    $activos_revision = substr($activos_revision, 0, -2);
                }

                $activos_original_en = "&nbsp;";
                if (have_rows('activos', $post->ID)) {
                    $i = 0;
                    while (have_rows('activos', $post->ID)): the_row();
                        $k = $i - 1; //Para corregir el problema de que se empieza a contar de 0
                        if (pll_get_post(get_sub_field('activo_proyecto'), "en")) {$activos_original_en = $activos_original_en . get_the_title(pll_get_post(get_sub_field('activo_proyecto'), "en"));}
                        if (get_sub_field('cantidad_activo')) {$activos_original_en = $activos_original_en . ' ' . str_replace('.00', '', number_format(get_sub_field('cantidad_activo'), 2));}
                        if (pll_get_post(get_sub_field('medida_activo'), "en")) {$activos_original_en = $activos_original_en . ' ' . get_the_title(pll_get_post(get_sub_field('medida_activo'), "en"));}
                        if (pll_get_post(get_sub_field('calidad'), "en")) {$activos_original_en = $activos_original_en . '-' . get_the_title(pll_get_post(get_sub_field('calidad'), "en"));}
                        $activos_original_en = $activos_original_en . ", ";
                        $i++;
                    endwhile;
                    $activos_original_en = substr($activos_original_en, 0, -2);
                }
                $activos_revision_en = "&nbsp;";
                if (have_rows('activos', $revision->ID)) {
                    $i = 0;
                    while (have_rows('activos', $revision->ID)): the_row();
                        $k = $i - 1; //Para corregir el problema de que se empieza a contar de 0
                        if (pll_get_post(get_sub_field('activo_proyecto'), "en")) {$activos_revision_en = $activos_revision_en . get_the_title(pll_get_post(get_sub_field('activo_proyecto'), "en"));}
                        if (get_sub_field('cantidad_activo')) {$activos_revision_en = $activos_revision_en . ' ' . str_replace('.00', '', number_format(get_sub_field('cantidad_activo'), 2));}
                        if (pll_get_post(get_sub_field('medida_activo'), "en")) {$activos_revision_en = $activos_revision_en . ' ' . get_the_title(pll_get_post(get_sub_field('medida_activo'), "en"));}
                        if (pll_get_post(get_sub_field('calidad'), "en")) {$activos_revision_en = $activos_revision_en . '-' . get_the_title(pll_get_post(get_sub_field('calidad'), "en"));}
                        $activos_revision_en = $activos_revision_en . ", ";
                        $i++;
                    endwhile;
                    $activos_revision_en = substr($activos_revision_en, 0, -2);
                }

                // Moneda del contrato
                $post_moneda = '';
                $revision_moneda = '';
                if (get_field('contrato_asignado_en', $post_original->ID) != '') {
                    $post_moneda = get_the_title(get_field('contrato_asignado_en', $post_original->ID));
                }
                if (get_field('contrato_asignado_en', $post_revision->ID) != '') {
                    $revision_moneda = get_the_title(get_field('contrato_asignado_en', $post_revision->ID));
                }

                // Monto de inversion
                $post_monto_inversion = $post->monto_inversion;
                $revision_monto_inversion = $revision->monto_inversion;

                if ( $post_monto_inversion === "" || !isset($post_monto_inversion) || $post_monto_inversion === "0" ) {

                    if($language == 'en_US' or $idioma == 'en'){
                        $post_monto_inversion = "N.A.";
                    }else{
                        $post_monto_inversion = "N.D.";
                    }

                }else{

                    // Convertir la cadena a un número.
                    $numero = (int)$post_monto_inversion;
                    // Formatear el número con comas.
                    $formateado = number_format($numero);
                    // Concatenar el símbolo de moneda y la moneda.
                    $post_monto_inversion = "$post_moneda $$formateado";

                }


                if ( $revision_monto_inversion === "" || !isset($revision_monto_inversion) || $revision_monto_inversion === "0" ) {

                        if($language == 'en_US' or $idioma == 'en'){
                            $revision_monto_inversion = "N.A.";
                        }else{
                            $revision_monto_inversion = "N.D.";
                        }

                }else{

                    // Convertir la cadena a un número.
                    $numero = (int)$revision_monto_inversion;
                    // Formatear el número con comas.
                    $formateado = number_format($numero);
                    // Concatenar el símbolo de moneda y la moneda.
                    $revision_monto_inversion = "$post_moneda $$formateado";
                
                }

                //Alcance del contrato
                $post_values = get_field('alcances_del_contrato', $post_original->ID);
                $revision_values = get_field('alcances_del_contrato', $post_revision->ID);
                $post_alcance_contrato = "";
                $revision_alcance_contrato = "";
                $post_alcance_contrato_en = "";
                $revision_alcance_contrato_en = "";

                if($post_values) {
                    foreach($post_values as $v) {
                        $post_alcance_contrato .= $v.", ";
                    }
                }

                $post_alcance_contrato = substr($post_alcance_contrato,0,-2);

                $translate_en = ABSPATH . "/wp-content/themes/enfold-child/lang/en_US.po";
                $content_translate_en = file_get_contents($translate_en);
                $arr_content_translate_en = explode("msgid",$content_translate_en);
                $alcances_dic_en = array();

                foreach ($arr_content_translate_en as $a) {
                    $b = explode("msgstr",$a);

                    $b[0] = trim($b[0]);
                    $b[1] = trim($b[1]);

                    $b[0] = str_replace('"','',$b[0]);
                    $b[1] = str_replace('"','',$b[1]);

                    $alcances_dic_en[$b[0]] = $b[1];
                }

                foreach ($post_values as $v) {
                    $post_alcance_contrato_en = $post_alcance_contrato_en . $alcances_dic_en[$v].", ";
                }

                $post_alcance_contrato_en = substr($post_alcance_contrato_en,0,-2);

                if($revision_values) {
                    foreach($revision_values as $v) {
                        $revision_alcance_contrato .= $v.", ";
                    }
                }

                $revision_alcance_contrato = substr($revision_alcance_contrato,0,-2);

                foreach ($revision_values as $v) {
                    $revision_alcance_contrato_en = $revision_alcance_contrato_en . $alcances_dic_en[$v].", ";
                }

                $revision_alcance_contrato_en = substr($revision_alcance_contrato_en,0,-2);

                // Descripcion amplia
                $post_descripcion_amplia = $post->descripcion_amplia_es;
                $revision_descripcion_amplia = $revision->descripcion_amplia_es;
                $post_descripcion_amplia_en = $post->descripcion_amplia_en;
                $revision_descripcion_amplia_en = $revision->descripcion_amplia_en;

                // Pizarra o iD vehiculo
                $post_pizarra = $post->pizarra_o_id_vehiculo_inversion;
                $revision_pizarra = $revision->pizarra_o_id_vehiculo_inversion;

                // Tipo de proyecto
                $post_tipo_proyecto = isset($post->tipo_de_proyecto) ? get_the_title($post->tipo_de_proyecto) : '';
                $revision_tipo_proyecto = isset($revision->tipo_de_proyecto) ? get_the_title($revision->tipo_de_proyecto) : '';
                $post_tipo_proyecto_en = pll_get_post($post->tipo_de_proyecto, "en") != NULL ? get_the_title(pll_get_post($post->tipo_de_proyecto, "en")) : '';
                $revision_tipo_proyecto_en = pll_get_post($revision->tipo_de_proyecto, "en") != NULL ? get_the_title(pll_get_post($revision->tipo_de_proyecto, "en")) : '';

                // Tipo de contrato
                $post_tipo_contrato = isset($post->tipo_contrato) ? get_the_title($post->tipo_contrato) : '';
                $revision_tipo_contrato = isset($revision->tipo_contrato) ? get_the_title($revision->tipo_contrato) : '';
                $post_tipo_contrato_en = pll_get_post($post->tipo_contrato, "en") != NULL ? get_the_title(pll_get_post($post->tipo_contrato, "en")) : '';
                $revision_tipo_contrato_en = pll_get_post($revision->tipo_contrato, "en") != NULL ? get_the_title(pll_get_post($revision->tipo_contrato, "en")) : '';

                // Plazo del contrato
                $post_plazo_contrato = $post->plazo_contrato_proyecto;
                $revision_plazo_contrato = $revision->plazo_contrato_proyecto;

                if ($post_plazo_contrato == 1) {
                    if ($language == 'en_US' || $idioma == 'en') {
                        $post_plazo_contrato = $post_plazo_contrato . ' year';
                    } else {
                        $post_plazo_contrato = $post_plazo_contrato . ' año';
                    }
                }

                if ($post_plazo_contrato > 1) {
                    if ($language == 'en_US' || $idioma == 'en') {
                        $post_plazo_contrato = $post_plazo_contrato . ' years';
                    } else {
                        $post_plazo_contrato = $post_plazo_contrato . ' años';
                    }
                }

                if ($revision_plazo_contrato == 1) {
                    if ($language == 'en_US' || $idioma == 'en') {
                        $revision_plazo_contrato = $revision_plazo_contrato . ' year';
                    } else {
                        $revision_plazo_contrato = $revision_plazo_contrato . ' año';
                    }
                }

                if ($revision_plazo_contrato > 1) {
                    if ($language == 'en_US' || $idioma == 'en') {
                        $revision_plazo_contrato = $revision_plazo_contrato . ' years';
                    } else {
                        $revision_plazo_contrato = $revision_plazo_contrato . ' años';
                    }
                }

                // Proceso de seleccion
                $post_proceso_seleccion = isset($post->proceso_de_seleccion) ? get_the_title($post->proceso_de_seleccion) : '';
                $revision_proceso_seleccion = isset($revision->proceso_de_seleccion) ? get_the_title($revision->proceso_de_seleccion) : '';
                $post_proceso_seleccion_en = pll_get_post($post->proceso_de_seleccion, "en") != NULL ? get_the_title(pll_get_post($post->proceso_de_seleccion, "en")) : '';
                $revision_proceso_seleccion_en = pll_get_post($revision->proceso_de_seleccion, "en") != NULL ? get_the_title(pll_get_post($revision->proceso_de_seleccion, "en")) : '';

                // Fuente de pago
                $post_fuente_pago = isset($post->fuente_pago_proyecto) ? get_the_title($post->fuente_pago_proyecto) : '';
                $revision_fuente_pago = isset($revision->fuente_pago_proyecto) ? get_the_title($revision->fuente_pago_proyecto) : '';
                $post_fuente_pago_en = pll_get_post($post->fuente_pago_proyecto, "en") != NULL ? get_the_title(pll_get_post($post->fuente_pago_proyecto, "en")) : '';
                $revision_fuente_pago_en = pll_get_post($revision->fuente_pago_proyecto, "en") != NULL ? get_the_title(pll_get_post($revision->fuente_pago_proyecto, "en")) : '';

                // Descripcion de la fuente de pago
                $post_descripcion_fuente_pago = $post->descripcion_fuente_de_pago;
                $revision_descripcion_fuente_pago = $revision->descripcion_fuente_de_pago;
                $post_descripcion_fuente_pago_en = $post->descripcion_fuente_de_pago_en;
                $revision_descripcion_fuente_pago_en = $revision->descripcion_fuente_de_pago_en;

                // Etapa
                $post_etapa = isset($post->etapa_proyecto) ? get_the_title($post->etapa_proyecto) : '';
                $revision_etapa = isset($revision->etapa_proyecto) ? get_the_title($revision->etapa_proyecto) : '';
                $post_etapa_en = pll_get_post($post->etapa_proyecto, "en") != NULL ? get_the_title(pll_get_post($post->etapa_proyecto, "en")) : '';
                $revision_etapa_en = pll_get_post($revision->etapa_proyecto, "en") != NULL ? get_the_title(pll_get_post($revision->etapa_proyecto, "en")) : '';

                // Subetapa
                $post_subetapa = isset($post->subetapa_proyecto) ? get_the_title($post->subetapa_proyecto) : '';
                $revision_subetapa = isset($revision->subetapa_proyecto) ? get_the_title($revision->subetapa_proyecto) : '';
                $post_subetapa_en = pll_get_post($post->subetapa_proyecto, "en") != NULL ? get_the_title(pll_get_post($post->subetapa_proyecto, "en")) : '';
                $revision_subetapa_en = pll_get_post($revision->subetapa_proyecto, "en") != NULL ?  get_the_title(pll_get_post($revision->subetapa_proyecto, "en")) : '';

                // Estados
                $post_estados = "";
                $revision_estados = "";

                if( have_rows('estados_ubicacion', $post->ID)):
                    while( have_rows('estados_ubicacion', $post->ID) ) : the_row();
                        $post_estados = $post_estados . get_the_title(get_sub_field('estado_ubicacion')).", ";
                    endwhile;

                    $post_estados = substr($post_estados, 0, -2);
                endif;

                if( have_rows('estados_ubicacion', $revision->ID)):
                    while( have_rows('estados_ubicacion', $revision->ID) ) : the_row();
                        $revision_estados = $revision_estados . get_the_title(get_sub_field('estado_ubicacion')).", ";
                    endwhile;

                    $revision_estados = substr($revision_estados, 0, -2);
                endif;

                // Geolocalizacion
                $post_geolocalizacion = $post->geolocalizacion_proyecto;
                $revision_geolocalizacion = $revision->geolocalizacion_proyecto;

                // Entidad regulatorio
                $post_entidad = isset($post->entidad_reguladora_sectorial) ? get_the_title($post->entidad_reguladora_sectorial) : '';
                $revision_entidad = isset($revision->entidad_reguladora_sectorial) ? get_the_title($revision->entidad_reguladora_sectorial) : '';
                $post_entidad_en = pll_get_post($post->entidad_reguladora_sectorial,"en") != NULL ? get_the_title(pll_get_post($post->entidad_reguladora_sectorial,"en")) : '';
                $revision_entidad_en = pll_get_post($revision->entidad_reguladora_sectorial,"en") != NULL ? get_the_title(pll_get_post($revision->entidad_reguladora_sectorial,"en")) : '';

                // Area responsable
                $post_area_responsable = get_field('institucion_y_área_responsable', $post->ID);
                $revision_area_responsable = get_field('institucion_y_área_responsable', $revision->ID);

                // Contacto
                $post_contacto = $post->contacto_proyecto;
                $revision_contacto = $revision->contacto_proyecto;

                // Correo electronico
                $post_correo = $post->coreo_contacto_proyecto;
                $revision_correo = $revision->coreo_contacto_proyecto;

                // Fuentes
                $post_fuentes = $post->fuentes_proyecto;
                $revision_fuentes = $revision->fuentes_proyecto;

                // Imagenes
                $post_imagenes = $post->galeria_imagenes_proyecto;
                $revision_imagenes = $revision->galeria_imagenes_proyecto;

                // Videos
                $post_videos = $post->videos_proyecto;
                $revision_videos = $revision->videos_proyecto;

                // Observaciones
                $post_observaciones = $post->observaciones_proyecto;
                $revision_observaciones = $revision->observaciones_proyecto;
                $post_observaciones_en = $post->observaciones_en;
                $revision_observaciones_en = $revision->observaciones_en;

                // Pospuesto
                $post_pospuesto = $post->pospuesto;
                $revision_pospuesto = $revision->pospuesto;

                // Cancelado
                $post_cancelado = $post->cancelacion;
                $revision_cancelado = $revision->cancelacion;

                // Descripcion ejecutiva
                $post_descripcion_ejecutiva = $post->descripcion_ejecutiva_es;
                $revision_descripcion_ejecutiva = $revision->descripcion_ejecutiva_es;
                $post_descripcion_ejecutiva_en = $post->descripcion_ejecutiva_en;
                $revision_descripcion_ejecutiva_en = $revision->descripcion_ejecutiva_en;

                if ($id_revision_usar != 0) {
                    if ($language == 'en_US' || $idioma == 'en') {
                        if ($post_sector_en != $revision_sector_en) {
                            $tr_sector = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Sector', $revision_sector_en, $post_sector_en);

                            $cambios++;
                        }

                        if ($post_subsector_en != $revision_subsector_en) {
                            $tr_subsector = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Subsector', $revision_subsector_en, $post_subsector_en);

                            $cambios++;
                        }

                        if ($post_alias_en != $revision_alias_en) {
                            $tr_alias = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Alias', strtoupper($revision_alias_en), strtoupper($post_alias_en));
                            $cambios++;
                        }

                        /*if ($post_fecha_ultima_actualizacion != $revision_fecha_ultima_actualizacion) {
                            $tr_fecha_ultima_actualizacion = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Last Update', $revision_fecha_ultima_actualizacion, $post_fecha_ultima_actualizacion);
                            $cambios++;
                        }*/

                        if ($post_titulo_en != $revision_titulo_en) {
                            $tr_titulo = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Project Name', $revision_titulo_en, $post_titulo_en);
                            $cambios++;
                        }

                        if ($post_proyecto_verde != $revision_proyecto_verde) {
                            $revision_proyecto_verde_respuesta = '';
                            $post_proyecto_verde_respuesta = '';

                            if ($revision_proyecto_verde == 1) {
                                $revision_proyecto_verde_respuesta = 'Yes';
                            }

                            if ($revision_proyecto_verde == 0) {
                                $revision_proyecto_verde_respuesta = 'No';
                            }

                            if ($post_proyecto_verde == 1) {
                                $post_proyecto_verde_respuesta = 'Yes';
                            }

                            if ($post_proyecto_verde == 0) {
                                $post_proyecto_verde_respuesta = 'No';
                            }

                            $tr_proyecto_verde = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'BANOBRAS/FONADIN INVOLVEMENT', $revision_proyecto_verde_respuesta, $post_proyecto_verde_respuesta);
                            $cambios++;
                        }

                        if ($post_tipo_inversion_en != $revision_tipo_inversion_en) {
                            $tr_tipo_inversion = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Type of Investment', $revision_tipo_inversion_en, $post_tipo_inversion_en);
                            $cambios++;
                        }

                        if ($activos_original_en != $activos_revision_en) {
                            $tr_activos = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Asset(s)', $activos_revision_en, $activos_original_en);
                            $cambios++;
                        }

                        if ($post_moneda != $revision_moneda) {
                            $tr_moneda = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contract Currency', $revision_moneda, $post_moneda);
                            $cambios++;
                        }

                        if ($post_monto_inversion != $revision_monto_inversion) {
                            $tr_monto_inversion = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Estimated investment', $revision_monto_inversion, $post_monto_inversion);
                            $cambios++;
                        }

                        if ($post_alcance_contrato_en != $revision_alcance_contrato_en) {
                            $tr_alcance_contrato = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contract Scope', $revision_alcance_contrato_en, $post_alcance_contrato_en);
                            $cambios++;
                        }

                        if ($post_descripcion_amplia_en != $revision_descripcion_amplia_en) {
                            $tr_descripcion_amplia =Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Change in description');
                            $cambios++;
                        }

                        if ($post_pizarra != $revision_pizarra) {
                            $tr_pizarra = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Ticker symbol', $revision_pizarra, $post_pizarra);
                            $cambios++;
                        }

                        if ($post_tipo_proyecto_en != $revision_tipo_proyecto_en) {
                            $tr_tipo_proyecto = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Type of Project', $revision_tipo_proyecto_en, $post_tipo_proyecto_en);
                            $cambios++;
                        }

                        if ($post_tipo_contrato_en != $revision_tipo_contrato_en) {
                            $tr_tipo_contrato = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Type of Contract', $revision_tipo_contrato_en, $post_tipo_contrato_en);
                            $cambios++;
                        }

                        if ($post_plazo_contrato != $revision_plazo_contrato) {
                            $tr_plazo_contrato = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Term', $revision_plazo_contrato, $post_plazo_contrato);
                            $cambios++;
                        }

                        
                        if ($post_proceso_seleccion_en != $revision_proceso_seleccion_en) {
                            $tr_proceso_seleccion = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Selection Process', $revision_proceso_seleccion_en, $post_proceso_seleccion_en);
                            $cambios++;
                        }

                        if ($post_fuente_pago_en != $revision_fuente_pago_en) {
                            $tr_fuente_pago = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Payment source', $revision_fuente_pago_en, $post_fuente_pago_en);
                            $cambios++;
                        }

                        if ($post_descripcion_fuente_pago_en != $revision_descripcion_fuente_pago_en) {
                            $tr_descripcion_fuente_pago =Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Change in description payment source');
                            $cambios++;
                        }

                        if ($post_etapa_en != $revision_etapa_en) {
                            $tr_etapa = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Stage', $revision_etapa_en, $post_etapa_en);
                            $cambios++;
                        }

                        if ($post_subetapa_en != $revision_subetapa_en) {
                            $tr_subetapa = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Substage', $revision_subetapa_en, $post_subetapa_en);
                            $cambios++;
                        }

                        if ($post_estados != $revision_estados) {
                            $tr_estados = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'State(s)', $revision_estados, $post_estados);
                            $cambios++;
                        }

                        if ($post_geolocalizacion != $revision_geolocalizacion) {
                            $tr_geolocalizacion = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Geolocation', $revision_geolocalizacion, $post_geolocalizacion);
                            $cambios++;
                        }

                        if ($post_entidad_en != $revision_entidad_en) {
                            $tr_entidad = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Entity', $revision_entidad_en, $post_entidad_en);
                            $cambios++;
                        }

                        if ($post_area_responsable != $revision_area_responsable) {
                            $tr_area_responsable = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Department', $revision_area_responsable, $post_area_responsable);
                            $cambios++;
                        }

                        if ($post_contacto != $revision_contacto) {
                            $tr_contacto = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'E-mail', $revision_contacto, $post_contacto);
                            $cambios++;
                        }

                        if ($post_correo != $revision_correo) {
                            $tr_correo = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contact', $revision_correo, $post_correo);
                            $cambios++;
                        }

                        if ($post_imagenes != $revision_imagenes) {
                            $tr_imagenes = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Images', $revision_imagenes, $post_imagenes);
                            $cambios++;
                        }

                        if ($post_videos != $revision_videos) {
                            $tr_videos = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Videos', $revision_videos, $post_videos);
                        }

                        if ($post_observaciones_en != $revision_observaciones_en) {
                            $tr_observaciones =Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Change in remarks');
                            $cambios++;
                        }

                        if ($post_descripcion_ejecutiva_en != $revision_descripcion_ejecutiva_en) {
                            $tr_descripcion_ejecutiva =Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Change in executive description');
                            $cambios++;
                        }
                    } else {
                        if ($post_sector != $revision_sector) {
                            $tr_sector = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Sector', $revision_sector, $post_sector);
                            $cambios++;
                        }

                        if ($post_subsector != $revision_subsector) {
                            $tr_subsector = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Subsector', $revision_subsector, $post_subsector);
                            $cambios++;
                        }

                        if ($post_alias != $revision_alias) {
                            $tr_alias = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Alias', strtoupper($revision_alias), strtoupper($post_alias));
                            $cambios++;
                        }

                        /*if ($post_fecha_ultima_actualizacion != $revision_fecha_ultima_actualizacion) {
                            $tr_alitr_fecha_ultima_actualizacionas = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Fecha de actualizacion', $revision_fecha_ultima_actualizacion, $post_fecha_ultima_actualizacion);
                            $cambios++;
                        }*/

                        if ($post_titulo != $revision_titulo) {
                            $tr_titulo = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Nombre del Proyecto', $revision_titulo, $post_titulo);
                            $cambios++;
                        }

                        if ($post_proyecto_verde != $revision_proyecto_verde) {
                            $revision_proyecto_verde_respuesta = '';
                            $post_proyecto_verde_respuesta = '';

                            if ($revision_proyecto_verde == 1) {
                                $revision_proyecto_verde_respuesta = 'Sí';
                            }

                            if ($revision_proyecto_verde == 0) {
                                $revision_proyecto_verde_respuesta = 'No';
                            }

                            if ($post_proyecto_verde == 1) {
                                $post_proyecto_verde_respuesta = 'Sí';
                            }

                            if ($post_proyecto_verde == 0) {
                                $post_proyecto_verde_respuesta = 'No';
                            }

                            $tr_proyecto_verde = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'PARTICIPACI&#211;N BANOBRAS/FONADIN', $revision_proyecto_verde_respuesta, $post_proyecto_verde_respuesta);
                            $cambios++;
                        }

                        if ($post_tipo_inversion != $revision_tipo_inversion) {
                            $tr_tipo_inversion = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Tipo de inversi&#211;n', $revision_tipo_inversion, $post_tipo_inversion);
                            $cambios++;
                        }

                        if ($activos_original != $activos_revision) {
                            $tr_activos = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Activo', $activos_revision, $activos_original);
                            $cambios++;
                        }

                        if ($post_moneda != $revision_moneda) {
                            $tr_moneda = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Moneda del contrato', $revision_moneda, $post_moneda);
                            $cambios++;
                        }

                        if ($post_monto_inversion != $revision_monto_inversion) {
                            $tr_monto_inversion = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Inversi&#211;n estimada', $revision_monto_inversion, $post_monto_inversion);
                            $cambios++;
                        }

                        if ($post_alcance_contrato != $revision_alcance_contrato) {
                            $tr_alcance_contrato = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Alcances del contrato', $revision_alcance_contrato, $post_alcance_contrato);
                            $cambios++;
                        }

                        if ($post_descripcion_amplia != $revision_descripcion_amplia) {
                            $tr_descripcion_amplia =Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Cambio de descripci&#211;n');
                            $cambios++;
                        }

                        if ($post_pizarra != $revision_pizarra) {
                            $tr_pizarra =  Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Pizarra', $revision_pizarra, $post_pizarra);
                            $cambios++;
                        }

                        if ($post_tipo_proyecto != $revision_tipo_proyecto) {
                            $tr_tipo_proyecto = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Tipo de proyecto', $revision_tipo_proyecto, $post_tipo_proyecto);
                            $cambios++;
                        }

                        if ($post_tipo_contrato != $revision_tipo_contrato) {
                            $tr_tipo_contrato = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Tipo de contrato', $revision_tipo_contrato, $post_tipo_contrato);
                            $cambios++;
                        }

                        if ($post_plazo_contrato != $revision_plazo_contrato) {
                            $tr_plazo_contrato = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Plazo de contrato', $revision_plazo_contrato, $post_plazo_contrato);
                            $cambios++;
                        }

                        if ($post_proceso_seleccion != $revision_proceso_seleccion) {
                            $tr_proceso_seleccion = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Proceso de selecci&#211;n', $revision_proceso_seleccion, $post_proceso_seleccion);
                            $cambios++;
                        }

                        if ($post_fuente_pago != $revision_fuente_pago) {
                            $tr_fuente_pago = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Fuente de pago', $revision_fuente_pago, $post_fuente_pago);
                            $cambios++;
                        }

                        if ($post_descripcion_fuente_pago != $revision_descripcion_fuente_pago) {
                            $tr_descripcion_fuente_pago =Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Cambio en descripci&#211;n fuente de pago');
                            $cambios++;
                        }

                        if ($post_etapa != $revision_etapa) {
                            $tr_etapa = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Etapa', $revision_etapa, $post_etapa);
                            $cambios++;
                        }

                        if ($post_subetapa != $revision_subetapa) {
                            $tr_subetapa = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Subetapa', $revision_subetapa, $post_subetapa);
                            $cambios++;
                        }

                        if ($post_estados != $revision_estados) {
                            $tr_estados = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Estados', $revision_estados, $post_estados);
                            $cambios++;
                        }

                        if ($post_geolocalizacion != $revision_geolocalizacion) {
                            $tr_geolocalizacion = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Geolocalizaci&#211;n', $revision_geolocalizacion, $post_geolocalizacion);
                            $cambios++;
                        }

                        if ($post_entidad != $revision_entidad) {
                            $tr_entidad = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Entidad', $revision_entidad, $post_entidad);
                            $cambios++;
                        }

                        if ($post_area_responsable != $revision_area_responsable) {
                            $tr_area_responsable = Tr_Proyecto__Preferences_Shortcode($language, $idioma, '&#193;rea responsable', $revision_area_responsable, $post_area_responsable);
                            $cambios++;
                        }

                        if ($post_contacto != $revision_contacto) {
                            $tr_contacto = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contacto', $revision_contacto, $post_contacto);
                            $cambios++;
                        }

                        if ($post_correo != $revision_correo) {
                            $tr_correo = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Correo', $revision_correo, $post_correo);
                            $cambios++;
                        }

                        if ($post_imagenes != $revision_imagenes) {
                            $tr_imagenes = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Im&#193;genes', $revision_imagenes, $post_imagenes);
                            $cambios++;
                        }

                        if ($post_videos != $revision_videos) {
                            $tr_videos = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Videos', $revision_videos, $post_videos);
                            $cambios++;
                        }

                        if ($post_observaciones != $revision_observaciones) {
                            $tr_observaciones =Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Cambio en observaciones');
                            $cambios++;
                        }

                        if ($post_descripcion_ejecutiva != $revision_descripcion_ejecutiva) {
                            $tr_descripcion_ejecutiva =Tr_Colspan_Proyecto__Preferences_Shortcode($language, $idioma, 'Cambio en descripci&#211;n ejecutiva');
                            $cambios++;
                        }
                    }
                }

                //REVISION EN CAMBIO DE FECHA DE REGISTRO UI   ---PARTE 3---
                $content2_fecha_ui = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('registro_ui_proyecto', $id_post)):
                        while (have_rows('registro_ui_proyecto', $id_post)): the_row();
                    if (get_sub_field('fecha_registroui_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_registroui_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_registroui_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_registroui_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_registroui_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_registroui_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_registroui_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_registroui_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_registro_ui    = $fecha_tmp;
                    $fecha_registro_ui_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('registro_ui_proyecto', $id_revision_usar)):
                        while (have_rows('registro_ui_proyecto', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_registroui_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_registroui_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_registroui_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_registroui_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_registroui_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_registroui_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_registroui_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_registroui_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_registro_ui_revision    = $fecha_tmp;
                    $fecha_registro_ui_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_registro_ui_en != $fecha_registro_ui_revision_en) {
                            $content2_fecha_ui = Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Ministry of Finance Registration or Equivalent', $fecha_registro_ui_revision_en, $fecha_registro_ui_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_registro_ui_revision != $fecha_registro_ui) {
                            $content2_fecha_ui .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Registro de Unidad de Inversiones o equivalente', $fecha_registro_ui_revision, $fecha_registro_ui);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE REGISTRO UI ---PARTE 3 ---

                //REVISION EN CAMBIO DE FECHA DE CONVOCATORIA   ---PARTE 4---
                $content2_fecha_convocatoria = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('convocatoria_proyecto', $id_post)):
                        while (have_rows('convocatoria_proyecto', $id_post)): the_row();
                    if (get_sub_field('fecha_convocatoria_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_convocatoria_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_convocatoria_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_convocatoria_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_convocatoria_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_convocatoria_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_convocatoria_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_convocatoria_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_convocatoria    = $fecha_tmp;
                    $fecha_convocatoria_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('convocatoria_proyecto', $id_revision_usar)):
                        while (have_rows('convocatoria_proyecto', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_convocatoria_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_convocatoria_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_convocatoria_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_convocatoria_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_convocatoria_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_convocatoria_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_convocatoria_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_convocatoria_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_convocatoria_revision    = $fecha_tmp;
                    $fecha_convocatoria_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_convocatoria_en != $fecha_convocatoria_revision_en) {
                            $content2_fecha_convocatoria .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Request for Proposals/Announcement', $fecha_convocatoria_revision_en, $fecha_convocatoria_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_convocatoria != $fecha_convocatoria_revision) {
                            $content2_fecha_convocatoria .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Anuncio/Convocatoria', $fecha_convocatoria_revision, $fecha_convocatoria);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE CONVOCATORIA ---PARTE 4 ---

                //REVISION EN CAMBIO DE RECEPCIÃ“N DE PROPUESTAS   ---PARTE 5---
                $content2_fecha_fallo = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('fallo_proyecto', $id_post)):
                        while (have_rows('fallo_proyecto', $id_post)): the_row();
                    if (get_sub_field('fecha_fallo_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_fallo_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_fallo_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_fallo_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_fallo_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_fallo_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_fallo_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_fallo_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_fallo    = $fecha_tmp;
                    $fecha_fallo_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('fallo_proyecto', $id_revision_usar)):
                        while (have_rows('fallo_proyecto', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_fallo_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_fallo_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_fallo_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_fallo_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_fallo_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_fallo_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_fallo_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_fallo_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_fallo_revision    = $fecha_tmp;
                    $fecha_fallo_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_fallo_en != $fecha_fallo_revision_en) {
                            $content2_fecha_fallo .=  Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Ruling', $fecha_fallo_revision_en, $fecha_fallo_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_fallo != $fecha_fallo_revision) {
                            $content2_fecha_fallo .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Fallo', $fecha_fallo_revision, $fecha_fallo);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE RECEPCIÃ“N DE PROPUESTAS ---PARTE 5 ---

                //REVISION EN FECHA DE FALLO   ---PARTE 6---
                $content2_fecha_propuestas = "";
                if ($post_revision->ID != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('propuestas_proyecto', $id_post)):
                        while (have_rows('propuestas_proyecto', $id_post)): the_row();
                    if (get_sub_field('fecha_propuestas_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_propuestas_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_propuestas_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_propuestas_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_propuestas_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_propuestas_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_propuestas_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_propuestas_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_recepcion    = $fecha_tmp;
                    $fecha_recepcion_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('propuestas_proyecto', $post_revision->ID)):
                        while (have_rows('propuestas_proyecto', $post_revision->ID)): the_row();
                    if (get_sub_field('fecha_propuestas_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_propuestas_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_propuestas_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_propuestas_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_propuestas_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_propuestas_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_propuestas_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_propuestas_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_recepcion_revision    = $fecha_tmp;
                    $fecha_recepcion_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_recepcion_en != $fecha_recepcion_revision_en) {
                            $content2_fecha_propuestas .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Proposals Reception', $fecha_recepcion_revision_en, $fecha_recepcion_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_recepcion != $fecha_recepcion_revision) {
                            $content2_fecha_propuestas .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Recepci&#211;n de propuestas', $fecha_recepcion_revision, $fecha_recepcion);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE FALLO ---PARTE 6 ---

                //REVISION EN FECHA DE FIRMA DE CONTRATO   ---PARTE 7---
                $content2_fecha_firma = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('firma_contrato_proyecto', $id_post)):
                        while (have_rows('firma_contrato_proyecto', $id_post)): the_row();
                    if (get_sub_field('fecha_firma_contrato_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_firma_contrato_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_firma_contrato_proyecto')):
                            $fecha_tmp    = get_the_title(get_sub_field('plazo_firma_contrato_proyecto')) . " ";
                            $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_firma_contrato_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_firma_contrato_proyecto')):
                            $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_firma_contrato_proyecto') . " ";
                            $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_firma_contrato_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_firma    = $fecha_tmp;
                    $fecha_firma_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('firma_contrato_proyecto', $id_revision_usar)):
                        while (have_rows('firma_contrato_proyecto', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_firma_contrato_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_firma_contrato_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_firma_contrato_proyecto')):
                            $fecha_tmp    = get_the_title(get_sub_field('plazo_firma_contrato_proyecto')) . " ";
                            $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_firma_contrato_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_firma_contrato_proyecto')):
                            $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_firma_contrato_proyecto') . " ";
                            $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_firma_contrato_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_firma_revision    = $fecha_tmp;
                    $fecha_firma_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_firma_en != $fecha_firma_revision_en) {
                            $content2_fecha_firma .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contract Signing', $fecha_firma_revision_en, $fecha_firma_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_firma != $fecha_firma_revision) {
                            $content2_fecha_firma .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Firma contrato', $fecha_firma_revision, $fecha_firma);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE FIRMA DE CONTRATO   ---PARTE 7---

                //REVISION EN FECHA DE INICIO DE EJECUCION   ---PARTE 8---
                $content2_fecha_ejecucion = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('inicio_ejecucion_proyecto', $id_post)):
                        while (have_rows('inicio_ejecucion_proyecto', $id_post)): the_row();
                    if (get_sub_field('fecha_inicio_ejecucion_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_inicio_ejecucion_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_inicio_ejecucion_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_inicio_ejecucion_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_inicio_ejecucion_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_inicio_ejecucion__proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_inicio_ejecucion__proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_inicio_ejecucion__proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_ejecucion    = $fecha_tmp;
                    $fecha_ejecucion_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('inicio_ejecucion_proyecto', $id_revision_usar)):
                        while (have_rows('inicio_ejecucion_proyecto', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_inicio_ejecucion_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_inicio_ejecucion_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_inicio_ejecucion_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_inicio_ejecucion_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_inicio_ejecucion_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_inicio_ejecucion__proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_inicio_ejecucion__proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_inicio_ejecucion__proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_ejecucion_revision    = $fecha_tmp;
                    $fecha_ejecucion_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_ejecucion_en != $fecha_ejecucion_revision_en) {
                            $content2_fecha_ejecucion .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Execution/Construction Starting Date', $fecha_ejecucion_revision_en, $fecha_ejecucion_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_ejecucion != $fecha_ejecucion_revision) {
                            $content2_fecha_ejecucion .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Inicio de ejecuci&#211;n/Construcci&#211;n', $fecha_ejecucion_revision, $fecha_ejecucion);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE INICIO DE EJECUCION  ---PARTE 8---

                //REVISION EN FECHA DE INICIO DE OPERACION   ---PARTE 9---
                $content2_fecha_operacion = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('inicio_de_operacion', $id_post)):
                        while (have_rows('inicio_de_operacion', $id_post)): the_row();
                    if (get_sub_field('fecha_inicio_operacion_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_inicio_operacion_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_inicio_operacion_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_inicio_operacion_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_inicio_operacion_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_inicio_operacion__proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_inicio_operacion__proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_inicio_operacion__proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_operacion    = $fecha_tmp;
                    $fecha_operacion_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('inicio_de_operacion', $id_revision_usar)):
                        while (have_rows('inicio_de_operacion', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_inicio_operacion_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_inicio_operacion_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_inicio_operacion_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_inicio_operacion_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_inicio_operacion_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_inicio_operacion__proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_inicio_operacion__proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_inicio_operacion__proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_operacion_revision    = $fecha_tmp;
                    $fecha_operacion_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_operacion_en != $fecha_operacion_revision_en) {
                            $content2_fecha_operacion .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Operation Starting Date', $fecha_operacion_revision_en, $fecha_operacion_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_operacion != $fecha_operacion_revision) {
                            $content2_fecha_operacion .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Inicio de Operaci&#211;n', $fecha_operacion_revision, $fecha_operacion);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE INICIO DE OPERACION  ---PARTE 9---

                //REVISION EN FECHA DE CONCLUSION   ---PARTE 10---
                $content2_fecha_conclusion = "";
                if ($id_revision_usar != 0) {
                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('termino_vigencia_del_contrato', $id_post)):
                        while (have_rows('termino_vigencia_del_contrato', $id_post)): the_row();
                    if (get_sub_field('fecha_vigencia_contrato_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_vigencia_contrato_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_vigencia_contrato_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_vigencia_contrato_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_vigencia_contrato_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_vigencia_contrato_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_vigencia_contrato_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_vigencia_contrato_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_conclusion    = $fecha_tmp;
                    $fecha_conclusion_en = $fecha_tpm_en;

                    $fecha_tmp    = "";
                    $fecha_tpm_en = "";
                    if (have_rows('termino_vigencia_del_contrato', $id_revision_usar)):
                        while (have_rows('termino_vigencia_del_contrato', $id_revision_usar)): the_row();
                    if (get_sub_field('fecha_vigencia_contrato_proyecto', false, false)) {
                        $date         = get_sub_field('fecha_vigencia_contrato_proyecto', false, false);
                        $date         = new DateTime($date);
                        $fecha_tmp    = $date->format('d/m/Y');
                        $fecha_tpm_en = $date->format('F d, Y');
                    } else {
                        if (get_sub_field('plazo_vigencia_contrato_proyecto')):
                                    $fecha_tmp    = get_the_title(get_sub_field('plazo_vigencia_contrato_proyecto')) . " ";
                        $fecha_tpm_en = get_the_title(pll_get_post(get_sub_field('plazo_vigencia_contrato_proyecto'), "en")) . " ";
                        endif;
                        if (get_sub_field('año_plazo_vigencia_contrato_proyecto')):
                                    $fecha_tmp    = $fecha_tmp . get_sub_field('año_plazo_vigencia_contrato_proyecto') . " ";
                        $fecha_tpm_en = $fecha_tpm_en . get_sub_field('año_plazo_vigencia_contrato_proyecto') . " ";
                        endif;
                    }
                    endwhile;
                    endif;
                    $fecha_conclusion_revision    = $fecha_tmp;
                    $fecha_conclusion_revision_en = $fecha_tpm_en;

                    if ($language == 'en_US' or $idioma == 'en') {
                        if ($fecha_conclusion_en != $fecha_conclusion_revision_en) {
                            $content2_fecha_conclusion .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Term of the Contract', $fecha_conclusion_revision_en, $fecha_conclusion_en);
                            $cambios++;
                        }
                    } else {
                        if ($fecha_conclusion != $fecha_conclusion_revision) {
                            $content2_fecha_conclusion .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Termino de vigencia del contrato', $fecha_conclusion_revision, $fecha_conclusion);
                            $cambios++;
                        }
                    }
                }
                //FIN DE REVISION EN FECHA DE CONCLUSION  ---PARTE 10---

                //REVISION EN CAMBIO DE ADJUDICATARIO   ---PARTE 11---
                $content2_adjudicatario = "";
                $adjudicatario_original = get_field('nombre_adjudicatario_contrato_proyecto', $id_post);
                $adjudicatario_anterior = get_field('nombre_adjudicatario_contrato_proyecto', $post_revision->ID);
                if ($language == 'en_US' or $idioma == 'en') {
                    if ($adjudicatario_original != $adjudicatario_anterior) {
                        $content2_adjudicatario .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Contractor', $adjudicatario_anterior, $adjudicatario_original);
                        $cambios++;
                    }
                } else {
                    if ($adjudicatario_original != $adjudicatario_anterior) {
                        $content2_adjudicatario .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Nombre del adjudicatario del contrato', $adjudicatario_anterior, $adjudicatario_original);
                        $cambios++;
                    }
                }
                //FIN DE REVISION EN ADJUDICATARIO ---PARTE 11 ---

                //REVISION EN CAMBIO DE EMPRESAS ASOCIADAS AL ADJUDICATARIO   ---PARTE 12---
                $content2_empresas = "";
                $empresas_original = "";
                while (have_rows('empresa_a_las_que_pertenece', $id_post)): the_row();
                $empresas_original .= get_sub_field('nombre_empresa_proyecto', $id_post) . ", ";
                endwhile;
                $empresas_original = substr($empresas_original, 0, -2);

                $empresas_revision = "";
                while (have_rows('empresa_a_las_que_pertenece', $post_revision->ID)): the_row();
                $empresas_revision .= get_sub_field('nombre_empresa_proyecto', $id_revision_usar) . ", ";
                endwhile;
                $empresas_revision = substr($empresas_revision, 0, -2);

                if ($language == 'en_US' or $idioma == 'en') {
                    if ($empresas_original != $empresas_revision) {
                        $content2_empresas .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Major Contractor’s Shareholders', $empresas_revision, $empresas_original);
                        $cambios++;
                    }
                } else {
                    if ($empresas_original != $empresas_revision) {
                        $content2_empresas .= Tr_Proyecto__Preferences_Shortcode($language, $idioma, 'Empresas asociadas al adjudicatario', $empresas_revision, $empresas_original);
                        $cambios++;
                    }
                }
                //FIN DE REVISION EN CAMBIO DE EMPRESAS ASOCIADAS AL ADJUDICATARIO ---PARTE 12 ---

                $content2 .= $tr_sector.
                             $tr_subsector.
                             $tr_alias.
                             $tr_titulo.
                             $tr_proyecto_verde.
                             $tr_tipo_inversion.
                             $tr_activos.
                             $tr_moneda.
                             $tr_monto_inversion.
                             $tr_alcance_contrato.
                             $tr_pizarra.
                             $tr_tipo_proyecto.
                             $tr_tipo_contrato.
                             $tr_plazo_contrato.
                             $tr_proceso_seleccion.
                             $tr_fuente_pago.
                             $tr_etapa.
                             $tr_subetapa.
                             $content2_fecha_ui.
                             $content2_fecha_convocatoria.
                             $content2_fecha_propuestas.
                             $content2_fecha_fallo.
                             $content2_fecha_firma.
                             $content2_fecha_ejecucion.
                             $content2_fecha_operacion.
                             $content2_fecha_conclusion.
                             $content2_adjudicatario.
                             $content2_empresas.
                             $tr_estados.
                             $tr_geolocalizacion.
                             $tr_entidad.
                             $tr_area_responsable.
                             $tr_contacto.
                             $tr_correo.
                             $tr_imagenes.
                             $tr_videos.
                             $tr_descripcion_amplia.
                             $tr_descripcion_fuente_pago.
                             $tr_observaciones.
                             $tr_descripcion_ejecutiva;
            }
            //Fin de revisar si hubo cambios para poner encabezados
            
            $content1 .= '';
            $content3 = '';

            
            $footer = Footer_references_Shortcode($language, $idioma, $mail);

            return $content1 . $content2 . $content3 . $footer;
        } else {
            return 'SIN REGISTROS';
        }
    }
}

function construct_mails($str, $language)
{
    global $wpdb;
    //$str='dplaneacion068@banobras.gob.mx';
    $dias_options = get_field('rango_en_dias_follow', 'option');
    //$check = $wpdb->get_results("SELECT email FROM $table_name WHERE email = '$email' AND follow = '$id_post'");
    /*$check_mail = $wpdb->get_results("SELECT c.email, a.post_title, a.ID, a.post_date FROM wp_posts a
    join wp_postmeta b on a.ID = b.post_id
    join wp_bancomext_users c on b.meta_value = c.follow
    where b.meta_key = 'ID_PROYECTO'
    and a.post_date > DATE_SUB(CURDATE(),INTERVAL ".$dias_options." DAY)
    and a.post_status = 'publish'
    and c.email = '$str'");*/
    $check_mail = $wpdb->get_results("SELECT c.email, a.post_title, a.ID, d.meta_key, d.meta_value, c.lang as lang FROM wp_posts a
            inner JOIN wp_postmeta as b on (a.ID = b.post_id)
            inner join wp_postmeta as d on (a.ID = d.post_id)
            inner join wp_bancomext_users as c on (b.meta_value = c.follow)

            where b.meta_key = 'ID_PROYECTO'
            and d.meta_key = 'fecha_ultima_actualizacion_proyecto'
             and d.meta_value > CURDATE() -INTERVAL '$dias_options' DAY
            and a.post_status = 'publish'
            and c.email = '$str'
            and c.lang = '$language'
            AND a.ID NOT IN (select follow from wp_bancomext_users_reports WHERE email = '" . $str . "' AND d.meta_value < report AND proceso = 'Follow Diario') "
        /*AND a.ID IN (select p.ID from wp_posts as p
    join wp_postmeta as pm on p.ID = pm.post_id
    join wp_postmeta as pm2 on p.ID = pm2.post_id
    join wp_bancomext_users as u on pm.meta_value = u.follow
    where pm.meta_key = 'ID_PROYECTO'
    and pm.meta_value = u.follow
    and u.email = '$str'
    and p.post_status = 'publish'
    and p.post_type = 'proyecto_inversion'
    and pm2.meta_key = 'fecha_ultima_actualizacion_proyecto'
    and pm2.meta_value > u.time)
    group by c.email, substr(c.email,locate('@',c.email)), c.lang*/);

    return $check_mail;
}

function construct_custom_post($str, $date)
{
    //BRING PREFERENCES ACCORDING TO MAIL
    global $wpdb;
    $item_query = $wpdb->get_results("SELECT wp_posts.ID
              FROM wp_posts
                INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )
                WHERE wp_postmeta.meta_key = 'correo_registro_reg_inversionista'
                AND wp_postmeta.meta_value = '$str'
                AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'pending' OR wp_posts.post_status = 'draft')
                AND wp_posts.post_type = 'reg_inversionistas' ");
    //error_log('------------ llega aqui tambien ----------');
    //error_log('------------- y crea el query '.var_export($item_query, true));
    //foreach ( $this_query as $posts ) {
    foreach ($item_query as $posts) {
        $myid = $posts->ID;
    }
    //error_log('------------- asigan el ID '.var_export($myid, true));
    $interes = get_field('interes_en_proyectos_reg_inversionista', $myid); //catalogo
    if ($interes == 1) {
        $interes_id = '';
    } else {
        $interes_id = find_current_post_id($interes, 'cat_tipo_inversion');
    }

    $tipos = get_field('tipos_de_proyecto_reg_inversionista', $myid); //catalogo
    if ($tipos == 1) {
        $tipos_id = '';
    } else {
        $tipos_id = find_current_tipo_proyecto($tipos, 'cat_tipo_proyecto');
    }

    $monto_min_coma = get_field('monto_minimo_reg_inversionista', $myid);
    $monto_min      = str_replace(',', '', $monto_min_coma);
    $monto_max_coma = get_field('monto_maximo_reg_inversionista', $myid);
    $monto_max      = str_replace(',', '', $monto_max_coma);

    $etapa_ciclo = get_field('etapa_del_ciclo_de_inversion_reg_inversionista', $myid); //catalogo
    //var_dump($etapa_ciclo);
    $etapa_ciclo_ids = find_current_post_ids($etapa_ciclo, 'catalogo_etapas');
    //var_dump($etapa_ciclo_ids);
    ///// VALORES ABIERTOS ///////
    $elec_id = get_field('electricidad_reg_inversionista', $myid);
    if ($elec_id == 1) {
        $sector[] = get_field('electricidad_id_sector', 'option');
    }

    //subsector
    //$subsector = array();
    $elec_sub_id = get_field('electricidad_sub_reg_inversionista', $myid);
    $subsector1  = find_current_post_ids2($elec_sub_id, 'cat_subsector_inv');

    $elec_item = $elec_id['label'];
    $hid_id    = get_field('hidrocarburos_reg_inversionista', $myid);
    if ($hid_id == 1) {
        $sector[] = get_field('hidrocarburos_id_sector', 'option');
    }
    //subsector
    $hid_sub_id = get_field('hidrocarburos_sub-sectores', $myid);
    $subsector2 = find_current_post_ids2($hid_sub_id, 'cat_subsector_inv');

    $tran_id = get_field('transporte_reg_inversionista', $myid);
    if ($tran_id == 1) {
        $sector[] = get_field('transporte_id_sector', 'option');
    }

    //subsector
    $tran_sub_id = get_field('transporte_sub-sectores', $myid);
    $subsector3  = find_current_post_ids2($tran_sub_id, 'cat_subsector_inv');

    $inf_id = get_field('infraestructura_social_reg_inversionista', $myid);
    if ($inf_id == 1) {
        $sector[] = get_field('infraestructura_id_sector', 'option');
    }
    //subsector
    $inf_sub_id = get_field('infraestructura_sub-sectores', $myid);
    $subsector4 = find_current_post_ids2($inf_sub_id, 'cat_subsector_inv');

    $aguaym_sub_id = get_field('agua_y_medio_ambiente_reg_inversionista', $myid);
    if ($aguaym_sub_id == 1) {
        $sector[] = get_field('agua_y_medio_ambiente_id_sector', 'option');
    }
    //subsector
    $aguaym_sub_id = get_field('agua_sub-sectores', $myid);
    $subsector5    = find_current_post_ids2($aguaym_sub_id, 'cat_subsector_inv');

    $ind_id = get_field('industria_reg_inversionista', $myid);
    if ($ind_id == 1) {
        //$ind_id_item = get_field_object('industria_reg_inversionista', $myid);
        //$sector[] = $ind_id_item['label'];
        $sector[] = get_field('industria_id_sector', 'option');
    }
    //subsector
    $ind_sub_id = get_field('industria_sub-sectores', $myid);
    $subsector6 = find_current_post_ids2($ind_sub_id, 'cat_subsector_inv');

    $mine_sub_id = get_field('mineria_reg_inversionista', $myid);
    if ($mine_sub_id == 1) {
        $sector[] = get_field('mineria_id_sector', 'option');
    }
    //subsector
    $mine_sub_id = get_field('mineria_sub-sectores', $myid);
    $subsector7  = find_current_post_ids2($mine_sub_id, 'cat_subsector_inv');

    $inmo_id = get_field('inmobiliario_y_turismo_reg_inversionista', $myid);
    if ($inmo_id == 1) {
        $sector[] = get_field('inmobiliario_y_turismo_id_sector', 'option');
    }
    //subsector
    $inmo_sub_id = get_field('inmobiliario_sub-sectores', $myid);
    $subsector8  = find_current_post_ids2($inmo_sub_id, 'cat_subsector_inv');

    $telec_sub_id = get_field('telecomunicaciones_reg_inversionista', $myid);
    if ($telec_sub_id == 1) {
        $sector[] = get_field('telecomunicaciones_id_sector', 'option');
    }
    //subsector
    $telec_sub_id = get_field('telecom_sub-sectores', $myid);
    $subsector9   = find_current_post_ids2($telec_sub_id, 'cat_subsector_inv');

    //error_log('------------- Tenemos valores ');
    //}
    //implode para obtener valores separados por comas
    $array_sectores    = $sector;
    $array_subsectores = array_merge((array) $subsector1, (array) $subsector2, (array) $subsector3, (array) $subsector4, (array) $subsector5, (array) $subsector6, (array) $subsector7, (array) $subsector8, (array) $subsector9);

    // STARTS QUERY ARGS
    $values = array();

    $tipo_cambio_usa = get_field('tipo_de_cambio', 1981); // NO BORRAR ESTA LINEA
    $tipo_cambio = $tipo_cambio_usa;
    $dias_options    = get_field('rango_en_dias_proceso_diario_de_preferencias', 'option'); // NO BORRAR ESTA LINEA

    // FIRST PART SELECT
    $select = " SELECT wp_posts.ID FROM wp_posts ";
    // ONLY WHERE
    $where = " WHERE 1=1 ";
    // JOINS & CONDITIONS AND
    $and .= "AND wp_posts.post_type = 'proyecto_inversion' AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'private')";
    if (!empty($interes_id)) {
        $join .= " INNER JOIN wp_postmeta AS c ON ( wp_posts.ID = c.post_id AND c.meta_key = 'tipo_de_inversion') ";
        $and .= " AND c.meta_value = " . $interes_id;
    }
    if (!empty($tipos_id)) {
        $join .= " INNER JOIN wp_postmeta AS d ON ( wp_posts.ID = d.post_id AND d.meta_key = 'tipo_de_proyecto') ";
        $and .= " AND d.meta_value = " . $tipos_id;
    }
    if (!empty($etapa_ciclo_ids) && !in_array(1782, $etapa_ciclo_ids)) {
        $myids_etapa = implode(", ", array_values($etapa_ciclo_ids));
        $join .= " INNER JOIN wp_postmeta AS e ON ( wp_posts.ID = e.post_id AND e.meta_key = 'etapa_proyecto') ";
        $and .= " AND e.meta_value IN (" . $myids_etapa . ")";
    }
    // SECTORES
    if (!empty($array_sectores) && !empty($array_subsectores)) {
        $mykeys_sectores = implode(", ", array_values($array_sectores));
        $join .= " INNER JOIN wp_postmeta AS f ON ( wp_posts.ID = f.post_id AND f.meta_key = 'sector_proyecto') ";
        $and .= " AND (f.meta_value IN (" . $mykeys_sectores . ")";

        $mysubs_subsectores = implode(", ", array_values($array_subsectores));
        $join .= " INNER JOIN wp_postmeta AS gg ON ( wp_posts.ID = gg.post_id AND gg.meta_key = 'sector_proyecto') INNER JOIN wp_postmeta AS g ON ( wp_posts.ID = g.post_id AND g.meta_key = 'subsector_proyecto') ";
        $and .= " OR (gg.meta_value  IN (1425,4037,1428) AND g.meta_value IN (" . $mysubs_subsectores . ")))";
    } else {
        if (!empty($array_sectores)) {
            $mykeys_sectores = implode(", ", array_values($array_sectores));
            $join .= " INNER JOIN wp_postmeta AS f ON ( wp_posts.ID = f.post_id AND f.meta_key = 'sector_proyecto') ";
            $and .= " AND f.meta_value IN (" . $mykeys_sectores . ")";
        } else {
            if (!empty($array_subsectores)) {
                $mysubs_subsectores = implode(", ", array_values($array_subsectores));
                $join .= " INNER JOIN wp_postmeta AS g ON ( wp_posts.ID = g.post_id AND g.meta_key = 'subsector_proyecto') ";
                $and .= " AND g.meta_value IN (" . $mysubs_subsectores . ")";
            }
        }
    }
    if ($date) {
        $mydate = date('Ymd', strtotime('-' . $dias_options . ' days'));
        //$mydate = date('Ymd', strtotime('-1 days'));
        $join .= " INNER JOIN wp_postmeta AS h ON ( wp_posts.ID = h.post_id AND h.meta_key = 'fecha_ultima_actualizacion_proyecto') ";
        $and .= " AND h.meta_value >= " . $mydate;
        //error_log('------------- aqui vemos el and1 ----------'.var_export($and, true));

        // CHECK FOR PROJECTS WITH CHANGES ON FECHA DE ULTIMA ACTUALIZACION AGAINST THE LAST NOTIFICATION
        $and .= " AND wp_posts.ID NOT IN (select follow from wp_bancomext_users_reports WHERE email = '" . $str . "' AND h.meta_value < report AND proceso = 'Actualización de Preferencias')";
        //error_log('------------- aqui vemos el and2 ----------'.var_export($and, true));
    }
    if (!empty($monto_min) && !empty($monto_max)) {
        $join .= " INNER JOIN wp_postmeta AS monto_in ON (wp_posts.ID = monto_in.post_id AND monto_in.meta_key = 'monto_inversion')  ";
        $join .= " INNER JOIN wp_postmeta AS moneda ON (wp_posts.ID = moneda.post_id AND moneda.meta_key = 'contrato_asignado_en') ";
        $monto_min = $monto_min * 1000000;
        $monto_max = $monto_max * 1000000;

        $monto_min_mxn = $monto_min * $tipo_cambio;
        $monto_max_mxn = $monto_max * $tipo_cambio;

        $monto_min_usa = $monto_min / 1;
        $monto_max_usa = $monto_max / 1;
        $and .= " AND (monto_in.meta_value = '' OR monto_in.meta_value REGEXP '[^a-zA-Z0-9]' OR (moneda.meta_value = 1872 AND replace(monto_in.meta_value,',','')+1-1 BETWEEN $monto_min_mxn AND $monto_max_mxn) OR (moneda.meta_value = 1981 AND replace(monto_in.meta_value,',','')+1-1 BETWEEN $monto_min_usa AND $monto_max_usa)) ";
    }
    
    // //
    // Consideramos solo los proyectos modificados evitandos los nuevos
    // viejo $join .= " INNER JOIN wp_postmeta AS n ON (wp_posts.ID = n.post_id AND n.meta_key='nuevo') ";
    // viejo $where .= " AND n.meta_value=0 OR (n.meta_value=1 AND wp_posts.ID NOT IN (select follow from wp_bancomext_users_reports WHERE email = '$str' AND proceso = 'proyectos_nuevos'))";
	
	// Proyectos Nuevos
    // Consideramos solo los proyectos modificados evitandos los nuevos
    // v1.1 $join .= " INNER JOIN wp_postmeta AS n ON (wp_posts.ID = n.post_id AND n.meta_key='nuevo') ";
    // v1.1 $and .= " AND (n.meta_value=0 OR (n.meta_value=1 AND wp_posts.ID NOT IN (select follow from wp_bancomext_users_reports WHERE email = '$str' AND proceso = '".PMX_PROCESO_NUEVOS."' AND status='ENVIADO')))";
    
	
	// Proyectos Nuevos
    // Consideramos solo los proyectos modificados que no sean nuevos
    $join .= " INNER JOIN wp_postmeta AS n ON (wp_posts.ID = n.post_id AND n.meta_key='nuevo') ";
    $and  .= " AND n.meta_value=0 ";
	
    //error_log('------------- llegamos a los montos ----------'.var_export($and, true));
    // GROUP AND ORDER
    $group  = ' GROUP BY wp_posts.ID ORDER BY wp_posts.post_title, wp_posts.post_date DESC ';
    $result = $select . $join . $where . $and . $group;
    //error_log('------------- tenemos un query ----------'.var_export($result, true));
    $loop2 = $wpdb->get_results($result);
    //error_log('---------------- TERMINA EL QUERY');
    foreach ($loop2 as $item) {
        $values[] = $item->ID;
    }

    //wp_reset_postdata();
    return $values;
}

/////////////////////// EXTRA FUNCTIONS //////////////////////////////
function find_current_post_id($id, $type)
{
    $args_interes = array('post_type' => $type,
        'meta_key'                        => 'id',
        'meta_value'                      => $id,
    );
    $query_interes = new WP_Query($args_interes);
    foreach ($query_interes as $posts) {
        $current_id = $posts->ID;
    }
    return $current_id;
}

function find_current_tipo_proyecto($id, $type)
{
    $args_interes = array('post_type' => $type,
        'meta_key'                        => 'id',
        'meta_value'                      => $id,
    );
    $query_interes = new WP_Query($args_interes);
    foreach ($query_interes as $posts) {
        $current_id = $posts->ID;
    }
    return $current_id;
}

function find_current_post_ids($ids, $type)
{
    $values = array();
    if (!empty($ids)) {
        $ids_comma = implode(",", $ids);
    }
    $args = array('post_type' => 'catalogo_etapas',
        'posts_per_page'          => -1,
        'lang'                    => 'es', // NO TRAE RESULTADOS EN ESPAÃ‘OL.... LOS TRAIA EN INGLES CON OTROS
        'meta_query'              => array(
            array(
                'key'     => 'id',
                'value'   => $ids_comma,
                'compare' => 'IN',
            ),
        ),
    );
    $this_query = new WP_Query($args);
    /*foreach ( $this_query as $posts ) {
    $values[] = $posts->ID;
    }*/
    //var_dump($this_query);
    while ($this_query->have_posts()): $this_query->the_post();
    $values[] = get_the_ID();
    endwhile;
    //var_dump($values);
    return $values;
}

function find_current_post_ids2($ids, $type)
{
    //$values = array();
    $ids_comma = '';

    if (!empty($ids)) {
        $ids_comma = implode(",", $ids);
    }

    $args = array('post_type' => 'catalogo_subsectores',
        'posts_per_page'          => -1,
        'meta_query'              => array(
            array(
                'key'     => 'padre_subsector_inversionista',
                'value'   => $ids_comma,
                'compare' => 'IN',
            ),
        ),
    );
    $this_query = new WP_Query($args);
    //$values = array();
    while ($this_query->have_posts()): $this_query->the_post();
    $values[] = get_the_ID();
    endwhile;
    //error_log('&&&&&&&&&&&&&'.var_export($values, true));
    return $values;
}

/* Funciones para contruir el email de Preference shorcode */
function Header_Preferences_Shortcode($rowcount=0, $tit = '', $sub_tit='', $tot='', $language, $perzonalizado='' ){
    $Header1 = $tit;
    $Header2 = $sub_tit;
    $Total = $tot;
    $html_tot = '';
    $txt = get_field('texto_personalizado_en_altas', 57797);
    if($rowcount>0){
        $html_tot = '<h4 class="mb_xxs mte" style="color: #3e484d;margin-left: 0;margin-right: 0;margin-top: 32px;margin-bottom: 4px;padding: 0;font-weight: bold;font-size: 19px;line-height: 25px;">'.$Total.' (' . $rowcount . ')</h4>';
    }
    
    $header = '
    <table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
        <tbody>
            <tr>
                <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #ffffff;font-size: 0 !important;">
                    <!--[if (mso)|(IE)]><table width="632" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:632px;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                    <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
                        <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                                <tr>
                                    <td class="content_cell" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #ffffff;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;line-height: inherit;min-width: 0 !important;">
                                        <div class="email_row" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: center;clear: both;line-height: inherit;min-width: 0 !important;max-width: 600px !important;">
                                        <!--[if (mso)|(IE)]><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:600px;Margin:0 auto 0 0;"><tbody><tr><td width="600" style="width:600px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                                            <div class="col_6" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 600px;line-height: inherit;min-width: 0 !important;">
                                                <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td class="column_cell px pte tc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 16px;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: center;padding-left: 16px;padding-right: 16px;">
                                                                <h1 class="mb_xxs" style="color: #3e484d;margin-left: 0;margin-right: 0;margin-top: 20px;margin-bottom: 4px;padding: 0;font-weight: bold;font-size: 32px;line-height: 42px;">'.$Header1.'</h1>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
                </td>
            </tr>
        </tbody>
    </table>';
    if($perzonalizado!=''){
    	$header .= '
    	<table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
        <tbody>
            <tr>
                <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;background-color: #ffffff;font-size: 0 !important;">
                    <!--[if (mso)|(IE)]><table width="632" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:632px;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                    <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;line-height: inherit;min-width: 0 !important;">
                        <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                                <tr>
                                    <td class="content_cell" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #ffffff;font-size: 0;padding-left: 16px;padding-right: 16px;line-height: inherit;min-width: 0 !important;">
                                        <div class="email_row" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: center;clear: both;line-height: inherit;min-width: 0 !important;max-width: 600px !important;">
                                        <!--[if (mso)|(IE)]><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:600px;Margin:0 auto 0 0;"><tbody><tr><td width="600" style="width:600px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                                            <div class="col_6" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 600px;line-height: inherit;min-width: 0 !important;">
                                                <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                                                    <tbody>
                                                        <tr>
	                                                            <td class="column_cell px pte tc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 16px;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: left;padding-left: 16px;padding-right: 16px;">
	                                                            	<small>'.$perzonalizado.'</small>
	                                                            </td>
	                                                        </tr>
	                                                    </tbody>
	                                                </table>
	                                            </div>
	                                        </div>
	                                    </td>
	                                </tr>
	                            </tbody>
	                        </table>
	                    </div>
	                </td>
	            </tr>
	        </tbody>
	    </table>';
	}
	$header .= '
    <table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
        <tbody>
            <tr>
                <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #ffffff;font-size: 0 !important;">
                    <!--[if (mso)|(IE)]><table width="632" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:632px;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                    <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
                        <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                                <tr>
                                    <td class="content_cell" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #ffffff;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;line-height: inherit;min-width: 0 !important;">
                                        <div class="email_row" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: center;clear: both;line-height: inherit;min-width: 0 !important;max-width: 600px !important;">
                                        <!--[if (mso)|(IE)]><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:600px;Margin:0 auto 0 0;"><tbody><tr><td width="600" style="width:600px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                                            <div class="col_6" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 600px;line-height: inherit;min-width: 0 !important;">
                                                <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td class="column_cell px pte tc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 16px;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: center;padding-left: 16px;padding-right: 16px;">          
                                                                <p class="lead" style="font-family: Helvetica, Arial, sans-serif;font-size: 19px;line-height: 27px;color: #a7b1b6;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 16px;">'.$Header2.'</p>
                                                                <div>'.$html_tot.'</div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
                </td>
            </tr>
        </tbody>
    </table>';
    return $header;
}

function Title_Proyect_Preferences_Shortcode($language='es', $idioma='es', $id_post, $post_titulo, $post_titulo_en, $btn_unsubscribe=''){
    $link = '';
    $post_titulo = $post_titulo;
    $c1 = 'Campo modificado';
    $c2 = 'Informaci&oacute;n anterior';
    $c3 = 'Informaci&oacute;n  actualizada';
    if($language == 'en_US' or $idioma == 'en'){
        $link = '?language=en';
        $post_titulo = $post_titulo_en;
        $c1 = 'Modified field';
        $c2 = 'Previous information';
        $c3 = 'Updated information';
    }

    $proyecto = '
    <table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%">
      <tbody>
        <tr>
          <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #ffffff;font-size: 0 !important;">
            <!--[if (mso)|(IE)]><table width="632" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:632px;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
            <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
              <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                <tbody>
                  <tr>
                    <td class="content_cell active_b" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #35bec5;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;line-height: inherit;min-width: 0 !important;">
                      <!-- col-2-4 -->
                      <div class="email_row" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: center;clear: both;line-height: inherit;min-width: 0 !important;max-width: 6000px !important;">
                      <!--[if (mso)|(IE)]><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:600px;Margin:0 auto;"><tbody><tr><td width="400" style="width:400px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                        <div class="col_6" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 600px;line-height: inherit;min-width: 0 !important;">
                          <table class="column" align="center" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                              <tr>
                                <td class="column_cell px tl sc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 16px;padding-bottom: 16px;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #ffffff;mso-line-height-rule: exactly;text-align: center;padding-left: 16px;padding-right: 16px;">
                                  <h1 class="mt_0 mb_0" style="color: #ffffff;margin-left: 0;margin-right: 0;margin-top: 0;margin-bottom: 0;padding: 0;font-weight: bold;font-size: 20px;line-height: 32px;"><a href="'.get_post_permalink($id_post).$link.'" style="text-decoration: underline;line-height: inherit;color: #ffffff;"><span style="text-decoration: underline;line-height: inherit;color: #ffffff;">'.$post_titulo.'</span></a></h1>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
          </td>
        </tr>
      </tbody>
    </table>    
    <table class="email_table hide" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
      <tbody>
        <tr>
          <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #ffffff;font-size: 0 !important;">
            <!--[if (mso)|(IE)]><table width="632" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:632px;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
            <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
              <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                <tbody>
                  <tr>
                    <td class="content_cell pb" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #ffffff;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;padding-bottom: 16px;line-height: inherit;min-width: 0 !important;padding-top: 16px">
                      <div class="email_row tl" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: left;clear: both;line-height: inherit;min-width: 0 !important;max-width: 950px !important;">
                      <!--[if (mso)|(IE)]><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:600px;Margin:0 auto 0 0;"><tbody><tr><td width="100" style="width:100px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                        <div class="col_2" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 300px;line-height: inherit;min-width: 0 !important;">
                          <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                              <tr>
                                <td class="column_cell px pt_0 pb_0 tl" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 0;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: left;padding-left: 16px;padding-right: 16px;">
                                  <p class="small mb_0 tm" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #a7b1b6;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 0;">'.$c1.'</p>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      <!--[if (mso)|(IE)]></td><td width="250" style="width:250px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                        <div class="col_1" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 300px;line-height: inherit;min-width: 0 !important;">
                          <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                              <tr>
                                <td class="column_cell px pt_0 pb_0 tc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 0;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: left;padding-left: 16px;padding-right: 16px;">
                                  <p class="small mb_0 tm" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #a7b1b6;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 0;">'.$c2.'</p>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      <!--[if (mso)|(IE)]></td><td width="250" style="width:250px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                        <div class="col_1" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 300px;line-height: inherit;min-width: 0 !important;">
                          <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                              <tr>
                                <td class="column_cell px pt_0 pb_0 tl" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 0;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: left;padding-left: 16px;padding-right: 16px;">
                                  <p class="small mb_0 tm tr" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #a7b1b6;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 0;text-align: left;">'.$c3.'</p>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
                      </div>
                    </td>
                    <td></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
          </td>
        </tr>
      </tbody>
    </table>';
    return $proyecto;
}

function Tr_Proyecto__Preferences_Shortcode($language='es', $idioma='es', $str_title, $str_ant, $str_act){
    $pre_info = 'Informaci&oacute;n anterior';
    $update_info = 'Información actualizada';
    if($language == 'en_US' or $idioma == 'en'){
        $pre_info = 'Previous information';
        $update_info = 'Updated information';
    }
    $tr ='
    <table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
      <tbody>
        <tr>
          <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #ffffff;font-size: 0 !important;">
            <!--[if (mso)|(IE)]><table width="632" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:632px;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
            <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
              <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                <tbody>
                  <tr>
                    <td class="content_cell pb" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #ffffff;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;padding-bottom: 16px;line-height: inherit;min-width: 0 !important;">
                      <table class="hr_rl" align="center" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;font-size: 0;line-height: 1px;mso-line-height-rule: exactly;min-height: 1px;overflow: hidden;height: 2px;background-color: transparent !important;">
                        <tbody>
                          <tr>
                            <td class="hr_ep pb bt" style="box-sizing: border-box;vertical-align: top;font-size: 0;line-height: inherit;mso-line-height-rule: exactly;min-height: 1px;overflow: hidden;height: 2px;padding-bottom: 16px;border-top: 1px solid #e4e9eb;background-color: transparent !important;">&nbsp; </td>
                          </tr>
                        </tbody>
                      </table>
                      <div class="email_row tl" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: left;clear: both;line-height: inherit;min-width: 0 !important;max-width: 950px !important;">
                      <!--[if (mso)|(IE)]><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:600px;Margin:0 auto;"><tbody><tr><td width="100" style="width:100px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                        <div class="col_4" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 300px;line-height: inherit;min-width: 0 !important;">
                          <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                              <tr>
                                <td class="column_cell px pt_0 pb_0" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 0;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;padding-left: 16px;padding-right: 16px;">
                                  <h5 class="mt_0 mb_xxs" style="color: #3e484d;margin-left: 0;margin-right: 0;margin-top: 0;margin-bottom: 4px;padding: 0;font-weight: bold;font-size: 16px;line-height: 21px;">'. strtoupper($str_title).'</h5>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      <!--[if (mso)|(IE)]></td><td width="250" style="width:250px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                        <div class="col_1" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 300px;line-height: inherit;min-width: 0 !important;">
                          <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                              <tr>
                                <td class="column_cell px pt_0 pb_0 tc invoice_qty" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 0;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: left;padding-left: 16px;padding-right: 16px;">
                                  <p class="small mb_0 tm show_on_mobile" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #a7b1b6;mso-line-height-rule: exactly;display: none;margin-top: 0;margin-bottom: 0;">'.$pre_info.'</p>
                                  <p class="small mb_0" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #616161;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 0;">'.$str_ant.'</p>                                  
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      <!--[if (mso)|(IE)]></td><td width="250" style="width:250px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                        <div class="col_1" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 300px;line-height: inherit;min-width: 0 !important;">
                          <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                              <tr>
                                <td class="column_cell px pt_0 pb_0 tr invoice_price" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 0;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: left;padding-left: 16px;padding-right: 16px;">
                                  <p class="small mb_0 tm show_on_mobile" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #a7b1b6;mso-line-height-rule: exactly;display: none;margin-top: 0;margin-bottom: 0;">'.$update_info.'</p>
                                  <p class="mb_0" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #616161;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 0;">'.$str_act.'</p>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>                     
                      <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
          </td>
        </tr>
      </tbody>
    </table>';
    return $tr;
}

function Tr_Colspan_Proyecto__Preferences_Shortcode($language='es', $idioma='es', $str){
    $tr_colspan = '
    <table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
      <tbody>
        <tr>
          <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #ffffff;font-size: 0 !important;">
            <!--[if (mso)|(IE)]><table width="632" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:632px;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
            <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
              <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                <tbody>
                  <tr>
                    <td class="content_cell pb" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #ffffff;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;padding-bottom: 16px;line-height: inherit;min-width: 0 !important;">
                      <table class="hr_rl" align="center" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;font-size: 0;line-height: 1px;mso-line-height-rule: exactly;min-height: 1px;overflow: hidden;height: 2px;background-color: transparent !important;">
                        <tbody>
                          <tr>
                            <td class="hr_ep pb bt" style="box-sizing: border-box;vertical-align: top;font-size: 0;line-height: inherit;mso-line-height-rule: exactly;min-height: 1px;overflow: hidden;height: 2px;padding-bottom: 16px;border-top: 1px solid #e4e9eb;background-color: transparent !important;">&nbsp; </td>
                          </tr>
                        </tbody>
                      </table>
                      <div class="email_row tl" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: left;clear: both;line-height: inherit;min-width: 0 !important;max-width: 950px !important;">
                      <!--[if (mso)|(IE)]><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:600px;Margin:0 auto;"><tbody><tr><td width="600" style="width:600px;line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                        <div class="col_4" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 9500px;line-height: inherit;min-width: 0 !important;">
                          <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                              <tr>
                                <td class="column_cell px pt_0 pb_0" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 0;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;padding-left: 16px;padding-right: 16px;">
                                  <h5 class="mt_0 mb_xxs" style="color: #3e484d;margin-left: 0;margin-right: 0;margin-top: 0;margin-bottom: 4px;padding: 0;font-weight: bold;font-size: 16px;line-height: 21px;">'.strtoupper($str).'</h5>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
          </td>
        </tr>
      </tbody>
    </table>';
    return $tr_colspan;
}

function Footer_references_Shortcode($language='es', $idioma='es', $mail, $update_prefe=true, $url_prefe='', $unsubscribe=true, $url_unsubscribe=''){
    $url = 'https://www.proyectosmexico.gob.mx/';
    $pm = 'Proyectos México';
    if($url_prefe == ''){
        $url2 = 'mailto:proyectosmexico@banobras.gob.mx?subject=Modificaci%C3%B3n%20de%20preferencias&amp;body=Modificaci%C3%B3n%20de%20preferencias.%0A%0ACorreo%20electr%C3%B3nico%20de%20registro%3A%20'.urlencode($mail).'%0A%20%0AInter%C3%A9s%20en%20proyectos%3A%0ATodos%20(%20)%0ABrownfield%20(%20)%20%0AGreenfield%20(%20)%0A%0ATipos%20de%20proyecto%3A%20%0ATodos%20(%20)%0APrivados%20(%20)%0AP%C3%BAblicos%20(%20)%0AP%C3%BAblicos%2FPrivados%20(%20)%0A%0AMonto%20m%C3%ADnimo%20(millones%20USD)%3A%20%20%0AMonto%20m%C3%A1ximo%20(millones%20USD)%3A%20%0A%0AEtapas%20del%20ciclo%20de%20inversi%C3%B3n%3A%0ATodas%20las%20etapas%20(%20)%0APreinversi%C3%B3n%20(%20)%0ALicitaci%C3%B3n%20(%20)%0AEjecuci%C3%B3n%20(%20)%0AOperaci%C3%B3n%20(%20)%0A%0ASectores%3A%20%0AElectricidad%20(%20)%0AHidrocarburos%20(%20)%0ATransporte%20(%20)%0AInfraestructura%20Social%20(%20)%0AAgua%20y%20medio%20ambiente%20(%20)%0AMineria%20(%20)%0AInmobiliario%20y%20Turismo%20(%20)%0AIndustria%20(%20)%0ATelecomunicaciones%20(%20)';
    }
    else{
        $url2 = $url_prefe;
    }
    if($url_unsubscribe == '')
        $url3 = 'mailto:proyectosmexico@banobras.gob.mx?subject=Dejar%20de%20recibir%20notificaciones&amp;body='.urlencode($mail);
    else
        $url3 = $url_unsubscribe;
    $txt1 = 'Modificar preferencias.';
    $txt2 = 'Dejar de recibir notificaciones';
    if($language == 'en_US' or $idioma == 'en'){
        $url = 'https://www.proyectosmexico.gob.mx/en/home/';
        $pm = 'Mexico Projects Hub';
        if($url_prefe == ''){
            $url2 ='mailto:proyectosmexico@banobras.gob.mx?subject=Update%20preferences&amp;body=Update%20preferences.%0A%0ARegistered%20e-mail%3A%20'.urlencode($mail).'%0A%0AInterest%20in%20Projects%3A%20%20%20%0AAll%20(%20)%20%0ABrownfield%20(%20)%0AGreenfield%20(%20)%0A%0AType%20of%20Projects%3A%0AAll%20(%20)%0APrivate%20(%20)%0APublic%20(%20)%0APublic%2FPrivate%20(%20)%20%0A%0AMinimum%20Amount%20(USD%20million)%3A%20%20%0AMaximum%20Amount%20(USD%20million)%3A%20%0A%0AInvestment%20Cycle%20Stage%3A%0AAll%20stages%20(%20)%20%0APreinvestment%20(%20)%0ABidding%20(%20)%0AExecution%20(%20)%0AOperation%20(%20)%0A%0ASectors%3A%20%0AElectricity%20%20(%20)%0AHydrocarbons%20(%20)%20%0ATransport%20(%20)%0ASocial%20Infrastucture%20(%20)%20%0AWater%20%26%20Environment%20(%20)%20%0AMining%20(%20)%20%0AReal%20Estate%20%26%20Tourism%20(%20)%0AIndustry%20(%20)%0ATelecom%20(%20)';
        }
        else{
            $url2 = $url_prefe;
        }
        if($url_unsubscribe == '')
            $url3 = 'mailto:proyectosmexico@banobras.gob.mx?subject=Unsubscribe&amp;body='.urlencode($mail);
        else
            $url3 = $url_unsubscribe;
        $txt1 = 'Modify preferences.';
        $txt2 = 'Unsubscribe';
    }

    $footer = '
    <table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
        <tbody>
            <tr>
                <td class="email_body email_end tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #ffffff;font-size: 0 !important;">
                    <!--[if (mso)|(IE)]><table width="632" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:632px;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                    <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
                        <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                            <tbody>
                                <tr>
                                    <td class="content_cell footer_c default_b pt pb" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #475359;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;padding-top: 16px;padding-bottom: 16px;line-height: inherit;min-width: 0 !important;">
                                        <div class="email_row" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: center;clear: both;line-height: inherit;min-width: 0 !important;max-width: 600px !important;">
                                        <!--[if (mso)|(IE)]><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:600px;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                                            <div class="col_6" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 600px;line-height: inherit;min-width: 0 !important;">
                                                <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td class="column_cell px pt_xs pb_0 tc sc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 8px;padding-bottom: 0;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #ffffff;mso-line-height-rule: exactly;text-align: center;padding-left: 16px;padding-right: 16px;">
                                                                <p class="imgr imgr44 mb_xs" style="font-family: Helvetica, Arial, sans-serif;font-size: 0;line-height: 100%;color: #ffffff;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 8px;clear: both;"><a href="'.$url.'" style="text-decoration: underline;line-height: 1;color: #ffffff;"><img src="https://www.proyectosmexico.gob.mx/wp-content/uploads/2018/04/logo-dark.png" width="44" height="44" alt="'.$pm.'" style="outline: none;border: 0;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;line-height: 100%;max-width: 44px;margin-left: auto;margin-right: auto;width: 100% !important;height: auto !important;"/></a>
                                                                </p>';
                                                                if($update_prefe==true){
                                                                    $footer .=
                                                                    '<p class="small mb_xxs" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #ffffff;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 4px;">
                                                                    <a href="'.$url2.'" style="text-decoration: underline;line-height: inherit;color: #ffffff;">
                                                                        <span style="line-height: inherit;color: #6dd6db;">'.$txt1.'</span>
                                                                    </a>';
                                                                }
                                                                if($update_prefe==true && $unsubscribe==true)
                                                                    $footer .= '<span style="line-height: inherit;">&nbsp; | &nbsp;</span>';
                                                                if($unsubscribe==true){
                                                                    $footer .=
                                                                    '<a href="'.$url3.'" style="text-decoration: underline;line-height: inherit;color: #ffffff;">
                                                                        <span style="text-decoration: underline;line-height: inherit;color: #6dd6db;">'.$txt2.'</span>
                                                                    </a> <span style="line-height: inherit;"></span>
                                                                    </p>';
                                                                }
                                                                $footer .=
                                                                '<p class="small mb_xxs" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #ffffff;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 4px;"><span style="line-height: inherit;color: #ffffff;">® BANOBRAS '.date('Y').'</span> <span style="line-height: inherit;">&nbsp; • &nbsp;</span> <a href="'.$url.'" style="text-decoration: underline;line-height: inherit;color: #ffffff;"><span style="text-decoration: underline;line-height: inherit;color: #6dd6db;">'.$pm.'</span></a> <span style="line-height: inherit;"></span></p>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
                </td>
            </tr>
        </tbody>
    </table>';
    return $footer;
}


//correos proyectos nuevos

/* ====================== LOG BÁSICO ====================== */
if (!function_exists('pmx_log')) {
  function pmx_log($msg){ if(is_array($msg)||is_object($msg)){$msg=print_r($msg,true);} error_log('[PMX] '.$msg); }
}
if (!function_exists('pmx_join')) {
  function pmx_join($arr){ if(!is_array($arr)){$arr=$arr!==''?array($arr):array();} $o=array(); foreach($arr as $v){ $o[]=is_scalar($v)?$v:json_encode($v); } return implode(' | ',$o); }
}

/* ================= DEPURACIÓN ACF/TAX (DEBUG) ================= */
if (!function_exists('pmx_dbg_taxmap')) {
  function pmx_dbg_taxmap($acf_key) {
    $tax = pmx_key_to_tax($acf_key);
    $exists = ($tax && taxonomy_exists($tax)) ? 'sí' : 'no';
    pmx_log('[DBG] TAXMAP key='.$acf_key.' => tax="'.$tax.'" (existe='.$exists.')');
  }
}

if (!function_exists('pmx_dbg_field')) {
  function pmx_dbg_field($post_or_user_id, $acf_key) {
    $fo = function_exists('get_field_object') ? get_field_object($acf_key, $post_or_user_id) : null;
    pmx_log('[DBG] ACF key="'.$acf_key.'" @'.$post_or_user_id.' -> has_field_object='.(is_array($fo)?'sí':'no'));
    if (is_array($fo)) {
      $type = isset($fo['type']) ? $fo['type'] : '';
      $ret  = isset($fo['return_format']) ? $fo['return_format'] : '';
      $lab  = isset($fo['label']) ? $fo['label'] : '';
      $hasChoices = (isset($fo['choices']) && is_array($fo['choices'])) ? 'sí' : 'no';
      $choicesCnt = ($hasChoices==='sí') ? count($fo['choices']) : 0;
      pmx_log('[DBG]   label="'.$lab.'" type="'.$type.'" return_format="'.$ret.'" choices='.$hasChoices.' (n='.$choicesCnt.')');
      if ($choicesCnt > 0) {
        // muestra 5 choices para ver formato
        $sample = array();
        $i=0; foreach ($fo['choices'] as $k=>$v) { $sample[] = $k.'=>'.(is_scalar($v)?$v:'[arr]'); if(++$i>=5) break; }
        pmx_log('[DBG]   choices_sample: '.implode(' | ', $sample));
      }
    }

    $raw = function_exists('get_field') ? get_field($acf_key, $post_or_user_id) : null;
    // aplanamos para log amigable
    if (!function_exists('pmx_flatten_scalars')) {
      function pmx_flatten_scalars($v) {
        $out=array();
        foreach ((array)$v as $x) {
          if (is_scalar($x)) $out[]=(string)$x;
          elseif (is_object($x) && isset($x->ID)) $out[]=(string)intval($x->ID);
          elseif (is_array($x) && isset($x['ID'])) $out[]=(string)intval($x['ID']);
        }
        return $out;
      }
    }
    pmx_log('[DBG]   get_field raw: '.pmx_join(pmx_flatten_scalars($raw)));

    $tokens = pmx_get_acf_tokens($post_or_user_id, $acf_key);
    pmx_log('[DBG]   tokens: ['.pmx_join($tokens).']');
    // Normalizados
    if (!function_exists('pmx_norm_arr')) {
      function pmx_norm_arr($arr) { $o=array(); foreach ((array)$arr as $x){ $n=pmx_norm($x); if($n!=='') $o[$n]=1; } return array_keys($o); }
    }
    pmx_log('[DBG]   tokens_norm: ['.pmx_join(pmx_norm_arr($tokens)).']');
  }
}

/* ============================================================
 * Normaliza idioma para visualización en BD -> es_MX | en_US
 * ============================================================ */

if (!function_exists('pmx_locale_to_lang')) {
  // Devuelve 'es' | 'en' a partir de 'es', 'es_MX', 'en_US', etc.
  function pmx_locale_to_lang($locale){
    $s = strtolower(trim((string)$locale));
    return ($s === 'en' || strpos($s, 'en_') === 0) ? 'en' : 'es';
  }
}

if (!function_exists('pmx_to_display_locale')) {
  function pmx_to_display_locale($lang){
    $s = strtolower(trim((string)$lang));
    if ($s === 'en' || strpos($s, 'en_') === 0) return 'en_US';
    if ($s === 'es' || strpos($s, 'es_') === 0) return 'es_MX';
    // Fallback
    return 'es_MX';
  }
}

// helper para insertar titulo de proyecto wp_bancomext_users_reports
// Construye "12345 - <a href='...'>Título</a>" respetando idioma (Polylang y/o meta EN)
// Construye "12345 - <a href='...'>Título</a>" respetando idioma (Polylang y/o meta EN)
// y fuerza ?languaje=en cuando el idioma sea inglés.
if (!function_exists('pmx_report_follow_text')) {
  function pmx_report_follow_text($pid, $idioma='es_MX'){
    $pid = (int)$pid;
    if ($pid <= 0) return (string)$pid;

    // es_MX | en_US -> es | en
    $lang = (stripos((string)$idioma, 'en') !== false) ? 'en' : 'es';

    // Si hay Polylang, mapa al post del idioma solicitado
    $post_id = $pid;
    if (function_exists('pll_get_post')) {
      $tr = pll_get_post($pid, $lang);
      if ($tr) { $post_id = (int)$tr; }
    }

    // Título: intenta el del post del idioma, luego el original
    $title = get_the_title($post_id);
    if (!$title) { $title = get_the_title($pid); }

    // Fallback EN por meta
    if ($lang === 'en') {
      $meta_en = get_post_meta($post_id, 'nombre_oficial_ingles', true);
      if (!empty($meta_en)) { $title = $meta_en; }
    }

    // URL del post (del idioma si existe)
    $url = get_permalink($post_id);
    if (!$url) { $url = get_permalink($pid); }

    // --- Forzar query ?languaje=en cuando esté en inglés --- correccion ya que en el reporte de notificaciones no se visualizaba la url en el idioma correcto con ?language=en
    // (Eliminamos posibles params previos similares para no duplicar)
    if ($lang === 'en') {
      $url = remove_query_arg(array('languaje','language','lang'), $url);
      $url = add_query_arg('languaje', 'en', $url);
    } else {
      // En español dejamos la URL limpia (sin param). Si quieres forzar ?languaje=es,
      // descomenta las dos líneas de abajo:
      $url = remove_query_arg(array('languaje','language','lang'), $url);
      $url = add_query_arg('languaje', 'es', $url);
    }

    // Escapes mínimos
    $url   = esc_url($url);
    $title = esc_html($title);

    return $pid.' - <a href="'.$url.'" target="_blank" rel="noopener">'.$title.'</a>';
  }
}




/* ============================================================
 * Helper: guardar reporte en wp_bancomext_users_reports
 *  - Inserta una fila por cada ID detectado en $follow
 *  - Columna 'follow' guarda SOLO el ID (como string)
 *  - SOLO acepta IDs provenientes de:
 *      a) ['ids' => [..]]
 *      b) Array plano de números [..]
 *      c) String "id,id,id" (solo dígitos y comas)
 * ============================================================ */
/* ============================================================
 * Helper: guardar reporte en wp_bancomext_users_reports
 *  - Inserta una fila por cada ID detectado en $follow
 *  - Columna 'follow' guarda SOLO el ID (como string)
 *  - Si NO hay IDs, **NO inserta nada** (salta silenciosamente)
 * ============================================================ */
if (!function_exists('pmx_log_db_report')) {
  function pmx_log_db_report($email, $follow, $proceso, $status, $idioma) {
    global $wpdb;

    $table   = $wpdb->prefix . 'bancomext_users_reports';
    $email   = substr((string)$email, 0, 50);
    $idioma  = pmx_to_display_locale($idioma);  // es_MX | en_US
    $proceso = (string)$proceso;
    $status  = (string)$status;

    /* ---------- 1) Extraer SOLO IDs permitidos ---------- */
    $ids = array();
    $src = $follow;

    $take_ids_from_array = function($arr) {
      if (is_array($arr) && isset($arr['ids']) && is_array($arr['ids'])) {
        $arr = $arr['ids'];
      }
      if (is_array($arr) && array_values($arr) === $arr) {
        $out = array();
        foreach ($arr as $v) {
          if (is_scalar($v) && preg_match('/^\d+$/', (string)$v)) {
            $n = (int)$v;
            if ($n > 0) { $out[$n] = true; }
          }
        }
        return array_keys($out);
      }
      return array();
    };

    if (is_object($src)) {
      $src = get_object_vars($src);
    }

    if (is_array($src)) {
      $ids = $take_ids_from_array($src);
    } elseif (is_string($src)) {
      $s = trim($src);
      $decoded = json_decode($s, true);
      if (json_last_error() === JSON_ERROR_NONE) {
        $ids = $take_ids_from_array($decoded);
        $src = $decoded; // conservar por si trae inv_id
      } else {
        if (preg_match('/^\s*\d+(?:\s*,\s*\d+)*\s*$/', $s)) {
          $parts = preg_split('/\s*,\s*/', $s);
          $tmp = array();
          foreach ($parts as $p) {
            $n = (int)$p;
            if ($n > 0) { $tmp[$n] = true; }
          }
          $ids = array_keys($tmp);
        } else {
          $ids = array();
        }
      }
    } elseif (is_scalar($src) && preg_match('/^\d+$/', (string)$src)) {
      $n = (int)$src;
      if ($n > 0) $ids = array($n);
    }

    /* ---------- 1.b) Fallback: si no hay IDs, derivarlos por inv_id ---------- */
    if (empty($ids)) {
      $inv_id = 0;
      if (is_array($src) && isset($src['inv_id'])) {
        $inv_id = (int)$src['inv_id'];
      }
      if ($inv_id > 0 && function_exists('obtener_proyectos_nuevos')) {
        $ttl = defined('PMX_NUEVO_TTL') ? PMX_NUEVO_TTL : null;
        $posts_match = obtener_proyectos_nuevos($inv_id, $ttl);

        $tmp = array();
        foreach ((array)$posts_match as $p) {
          $pid = is_object($p) ? (int)$p->ID : (int)$p['ID'];
          if ($pid > 0) { $tmp[$pid] = true; }
        }

        // Excluye ya enviados (ACF)
        $enviados_acf = function_exists('get_field')
          ? get_field('proyectos_nuevos_enviados', $inv_id)
          : get_post_meta($inv_id, 'proyectos_nuevos_enviados', true);
        if (!is_array($enviados_acf)) $enviados_acf = array();

        $ids = array();
        foreach (array_keys($tmp) as $pid) {
          if (!in_array($pid, $enviados_acf, true)) $ids[] = $pid;
        }
      }
    }

    /* ---------- 2) Fechas ---------- */
    $fecha_legible = function_exists('date_i18n')
      ? date_i18n('Y-m-d H:i:s', current_time('timestamp'))
      : date('Y-m-d H:i:s');

    $report_dt = function_exists('current_time')
      ? current_time('mysql')
      : date('Y-m-d H:i:s');

    $base = array(
      'fecha'   => $fecha_legible,
      'email'   => $email,
      'report'  => $report_dt,
      'proceso' => $proceso,
      'status'  => $status,
      'idioma'  => $idioma,
    );
    // Mantenemos 7 formatos porque incluimos 'follow' al insertar/actualizar
    $format = array('%s','%s','%s','%s','%s','%s','%s');

    /* ---------- Utilidad: follow por ID o snippet ---------- */
    $build_follow_text = function($ids_list, $idi) use ($follow) {
      if (!empty($ids_list)) {
        $chunks = array();
        foreach ($ids_list as $one_id) {
          $chunks[] = pmx_report_follow_text($one_id, $idi);
        }
        return implode(' | ', $chunks);
      }
      $follow_str = '';
      if (is_string($follow)) {
        $tmp = trim($follow);
        if ($tmp !== '') {
          $tmp = function_exists('wp_strip_all_tags') ? wp_strip_all_tags($tmp) : strip_tags($tmp);
          $follow_str = substr($tmp, 0, 255);
        }
      }
      return $follow_str;
    };

    /* =========================================================
     * 3) Upsert por PROYECTO cuando EMAIL_INVALIDO o ENVIADO
     *    - EMAIL_INVALIDO: una fila por cada proyecto (sin duplicar)
     *    - ENVIADO: actualiza esas filas a ENVIADO (misma clave por ID)
     * ========================================================= */
    if (($status === 'EMAIL_INVALIDO' || $status === 'ENVIADO') && !empty($ids)) {
      $processed = 0;
      foreach ($ids as $one_id) {
        $follow_txt = pmx_report_follow_text($one_id, $idioma);

        // localizar fila EMAIL_INVALIDO previa por el ID en 'follow'
        $re = '(^|[^0-9])' . (int)$one_id . '([^0-9]|$)';
        $existing = $wpdb->get_row(
          $wpdb->prepare(
            "SELECT ID FROM {$table}
             WHERE proceso=%s AND status='EMAIL_INVALIDO' AND follow REGEXP %s
             ORDER BY ID DESC LIMIT 1",
            $proceso, $re
          )
        );

        if ($status === 'EMAIL_INVALIDO') {
          if ($existing) {
            $upd = array(
              'fecha'  => $fecha_legible,
              'report' => $report_dt,
              'idioma' => $idioma,
              'follow' => $follow_txt,
              'email'  => $email,
            );
            $wpdb->update($table, $upd, array('ID' => (int)$existing->ID), array('%s','%s','%s','%s','%s'), array('%d'));
            $processed++;
          } else {
            $data = $base;
            $data['follow'] = $follow_txt;
            $ok = $wpdb->insert($table, $data, $format);
            if ($ok !== false) $processed++;
          }
        } else { // ENVIADO
          if ($existing) {
            $upd = array(
              'fecha'  => $fecha_legible,
              'report' => $report_dt,
              'idioma' => $idioma,
              'status' => 'ENVIADO',
              'follow' => $follow_txt,
              'email'  => $email,
            );
            $wpdb->update($table, $upd, array('ID' => (int)$existing->ID), array('%s','%s','%s','%s','%s','%s'), array('%d'));
            $processed++;
          } else {
            // no existía EMAIL_INVALIDO previo para ese proyecto -> inserta ENVIADO
            $data = $base;
            $data['status'] = 'ENVIADO';
            $data['follow'] = $follow_txt;
            $ok = $wpdb->insert($table, $data, $format);
            if ($ok !== false) $processed++;
          }
        }
      }
      return $processed;
    }

    /* ---------- 4) Flujo normal: insertar por cada ID ---------- */
    if (!empty($ids)) {
      $inserted = 0;
      foreach ($ids as $one_id) {
        $data = $base;
        $data['follow'] = pmx_report_follow_text($one_id, $idioma);
        $ok = $wpdb->insert($table, $data, $format);
        if ($ok !== false) $inserted++;
        else if (function_exists('pmx_log')) pmx_log('DB_LOG_FAIL table='.$table.' id='.$one_id.' err='.$wpdb->last_error);
      }
      return $inserted;
    }

    /* ---------- 5) SIN IDs: registrar una sola fila ---------- */
    $data = $base;
    $data['follow'] = $build_follow_text(array(), $idioma);
    $ok = $wpdb->insert($table, $data, $format);
    if ($ok === false) {
      if (function_exists('pmx_log')) pmx_log('DB_LOG_FAIL table='.$table.' (sin IDs) err='.$wpdb->last_error);
      return 0;
    }
    return 1;
  }
}







/* ============================================================
 * LOG a wp_bancomext_users
 * - follow: "PID - <a href='...?languaje=xx'>Título</a>"
 * - lang:   es_MX | en_US (normalizado SOLO para la columna)
 * - el link usa el idioma crudo es|en
 * ============================================================ */
/*if (!function_exists('pmx_log_db_follow')) {
  function pmx_log_db_follow($email, $pid, $lang='es') {
    global $wpdb;
    $table = $wpdb->prefix . 'bancomext_users';

    $pid = intval($pid);
    if ($pid <= 0 || !is_email($email)) {
      if (function_exists('pmx_log')) pmx_log('DB_FOLLOW skip pid='.$pid.' email='.$email.' (datos inválidos)');
      return false;
    }

    // Idiomas
    $lang_raw  = pmx_locale_to_lang($lang);     // 'es' | 'en' -> para URL y traducción
    $lang_disp = pmx_to_display_locale($lang);  // 'es_MX' | 'en_US' -> para columna lang

    // Post en idioma del usuario (si hay traducción)
    $post_id_lang = function_exists('pmx_get_post_in_lang')
      ? pmx_get_post_in_lang($pid, $lang_raw)
      : $pid;

    // Título y URL en ese idioma
    if (pmx_locale_to_lang($lang) === 'en') {
  $title_en_meta = get_post_meta($post_id_lang, 'nombre_oficial_ingles', true);
  $title_lang = is_string($title_en_meta) && $title_en_meta !== '' ? $title_en_meta : get_the_title($post_id_lang);
} else {
  $title_lang = get_the_title($post_id_lang);
}

    if (!is_string($title_lang)) $title_lang = '';

    $url = get_permalink($post_id_lang);
    if ($url) {
      // Usa el parámetro que tu frontend espera (si es 'language' o 'lang', cámbialo aquí)
      $url = add_query_arg('language', $lang_raw, $url);
    }

    // Guardar "ID - <a>título</a>" (o solo título si no hay URL)
    $follow_value = $pid . ' - ' . (
      $url
        ? '<a href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer">'.esc_html($title_lang).'</a>'
        : esc_html($title_lang)
    );

    $row = array(
      'time'   => current_time('mysql'),
      'email'  => $email,
      'follow' => $follow_value,
      'lang'   => $lang_disp,  // visual: es_MX | en_US
    );

    $ok = $wpdb->insert($table, $row, array('%s','%s','%s','%s'));
    if ($ok === false) {
      if (function_exists('pmx_log')) pmx_log('DB_FOLLOW ❌ insert fail pid='.$pid.' email='.$email.' err='.$wpdb->last_error);
      return false;
    }
    if (function_exists('pmx_log')) pmx_log('DB_FOLLOW ✅ insert pid='.$pid.' email='.$email.' lang='.$lang_disp.' (raw='.$lang_raw.')');
    return true;
  }
}*/





/* ====================== NORMALIZADORES ====================== */
/* ==================== HELPERS DE NORMALIZACIÓN (por si faltan) ==================== */
if (!function_exists('pmx_to_array')) {
  function pmx_to_array($v) {
    if (is_array($v)) return $v;
    if ($v === null || $v === '') return array();
    return array($v);
  }
}
if (!function_exists('pmx_trim_strs')) {
  function pmx_trim_strs($arr) {
    $out = array();
    foreach (pmx_to_array($arr) as $v) {
      if (is_scalar($v)) $out[] = trim((string)$v);
    }
    return $out;
  }
}
if (!function_exists('pmx_join')) {
  function pmx_join($arr) {
    if (!is_array($arr)) { $arr = $arr !== '' ? array($arr) : array(); }
    $out = array();
    foreach ($arr as $v) { $out[] = is_scalar($v) ? $v : json_encode($v); }
    return implode(' | ', $out);
  }
}
if (!function_exists('pmx_log')) {
  function pmx_log($msg) {
    if (is_array($msg) || is_object($msg)) { $msg = print_r($msg, true); }
    error_log('[PMX] ' . $msg);
  }
}
if (!function_exists('pmx_norm')) {
  function pmx_norm($s) {
    $s = is_null($s) ? '' : (string)$s;
    $s = trim($s);
    $s = strtolower($s);
    $from = array('á','é','í','ó','ú','ñ','ä','ë','ï','ö','ü');
    $to   = array('a','e','i','o','u','n','a','e','i','o','u');
    $s = str_replace($from, $to, $s);
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
    $s = trim($s, '-');
    return $s;
  }
}
if (!function_exists('pmx_norm_arr')) {
  function pmx_norm_arr($arr) {
    $out = array();
    foreach (pmx_to_array($arr) as $x) {
      $n = pmx_norm($x);
      if ($n !== '') $out[$n] = true;
    }
    return array_keys($out);
  }
}

/* ============== MAPEADOR DE ACF KEYS -> TAXONOMÍAS (ajusta si cambia) ============== */
if (!function_exists('pmx_key_to_tax')) {
  function pmx_key_to_tax($acf_key) {
    // Slugs de taxonomías si existen en tu sitio
    $map = array(
      'sector_proyecto' => 'sector_proyecto',
      'tipo_de_proyecto' => 'tipo_de_proyecto',
      'etapa_proyecto'   => 'etapa_proyecto',
      'tipo_de_inversion'=> 'tipo_de_inversion',
    );
    return isset($map[$acf_key]) ? $map[$acf_key] : '';
  }
}

/* ===== Helper: detectar si en un ACF de preferencias fue seleccionado "Todos/Todas/All" ===== */
if (!function_exists('pmx_pref_axis_is_all')) {
    function pmx_pref_axis_is_all($post_or_user_id, $acf_key) {
        // Lee valor crudo del campo
        $raw = function_exists('get_field') ? get_field($acf_key, $post_or_user_id) : get_post_meta($post_or_user_id, $acf_key, true);
        $vals = is_array($raw) ? $raw : ($raw !== '' && $raw !== null ? array($raw) : array());

        // 1) Inspección directa de los valores (incluye "id|label" o "id;label")
        foreach ($vals as $v) {
            $s = trim((string)$v);
            if ($s === '') continue;

            // a) por valor directo
            $n = function_exists('pmx_norm') ? pmx_norm($s) : strtolower($s);
            if (in_array($n, array('todos','todas','all'), true)) return true;

            // b) "id|label" o "id;label" -> mira el label
            if (strpos($s, '|') !== false || strpos($s, ';') !== false) {
                $sep   = (strpos($s, '|') !== false) ? '|' : ';';
                $parts = explode($sep, $s, 2);
                if (count($parts) === 2) {
                    $lab = trim($parts[1]);
                    if ($lab !== '') {
                        $nl = function_exists('pmx_norm') ? pmx_norm($lab) : strtolower($lab);
                        if (in_array($nl, array('todos','todas','all'), true)) return true;
                    }
                }
            }
        }

        // 2) Si ACF tiene choices, resuelve label desde choices para cada valor
        if (function_exists('get_field_object')) {
            $fo = get_field_object($acf_key, $post_or_user_id);
            if (is_array($fo) && isset($fo['choices']) && is_array($fo['choices'])) {
                // Usa tu helper si existe
                $resolver = function_exists('pmx_find_in_choices')
                    ? 'pmx_find_in_choices'
                    : null;

                foreach ($vals as $v) {
                    $label = $resolver ? $resolver($fo['choices'], $v) : null;
                    if (is_string($label) && $label !== '') {
                        $nl = function_exists('pmx_norm') ? pmx_norm($label) : strtolower($label);
                        if (in_array($nl, array('todos','todas','all'), true)) return true;
                    }
                }
            }
        }

        return false;
    }
}

/* === Detecta si una preferencia significa "TODOS" === */
if (!function_exists('pmx_pref_means_all')) {
  function pmx_pref_means_all($tokens){
    $flat = array();
    foreach ((array)$tokens as $v) {
      if (is_array($v)) { foreach ($v as $x) $flat[] = (string)$x; }
      else { $flat[] = (string)$v; }
    }
    // "1" suele ser "Todos"
    foreach ($flat as $t) { if (trim($t) === '1') return true; }
    // por si viniera como texto
    $norm = function_exists('pmx_norm_arr') ? pmx_norm_arr($flat) : $flat;
    foreach (array('todos','todas','all') as $k) if (in_array($k, $norm, true)) return true;
    return false;
  }
}


/* ============== HELPER: obtener valores y labels de un ACF (IDs/labels/términos/posts) ============== */
if (!function_exists('pmx_flatten_scalars')) {
  function pmx_flatten_scalars($v) {
    $out = array();
    foreach (pmx_to_array($v) as $x) {
      if (is_scalar($x)) $out[] = trim((string)$x);
      else if (is_object($x) && isset($x->ID)) $out[] = (string)intval($x->ID);
      else if (is_array($x) && isset($x['ID'])) $out[] = (string)intval($x['ID']);
    }
    return array_values(array_filter($out, 'strlen'));
  }
}
if (!function_exists('pmx_find_in_choices')) {
  function pmx_find_in_choices($choices, $val) {
    // $choices puede venir como ['id'=>'Label'] o lista de labels
    if (is_array($choices)) {
      if (isset($choices[$val])) return $choices[$val];
      // Busca por valor exacto en values o labels
      foreach ($choices as $k=>$lab) {
        if ((string)$k === (string)$val) return $lab;
        if ((string)$lab === (string)$val) return $lab;
      }
    }
    return '';
  }
}
if (!function_exists('pmx_get_acf_labelled')) {
  // Devuelve ambos: 'vals' (valores crudos) y 'labels' (resueltos a nombres/labels)
  function pmx_get_acf_labelled($post_or_user_id, $acf_key) {
    $vals = array(); $labels = array(); $choices = null;

    // 1) Field object (si ACF lo expone)
    if (function_exists('get_field_object')) {
      $fo = get_field_object($acf_key, $post_or_user_id);
      if (is_array($fo)) {
        if (isset($fo['value']))   $vals = pmx_flatten_scalars($fo['value']);
        if (isset($fo['choices'])) $choices = $fo['choices'];
      }
    }
    // 2) Fallback directo
    if (empty($vals) && function_exists('get_field')) {
      $vals = pmx_flatten_scalars(get_field($acf_key, $post_or_user_id));
    }
    // 3) Labels desde 'choices'
    if ($choices) {
      foreach ($vals as $v) {
        $lab = pmx_find_in_choices($choices, $v);
        if (is_string($lab) && $lab !== '') $labels[] = $lab;
      }
    }
    // 4) Si vienen como "id|label" o "id;label", toma label
    foreach ($vals as $v) {
      if (strpos($v, '|') !== false || strpos($v, ';') !== false) {
        $sep = (strpos($v, '|') !== false) ? '|' : ';';
        $parts = explode($sep, $v, 2);
        if (count($parts) === 2) {
          $lab = trim($parts[1]);
          if ($lab !== '') $labels[] = $lab;
        }
      }
    }
    // 5) Resolución por taxonomía o como post
    $tax = pmx_key_to_tax($acf_key);
    foreach ($vals as $v) {
      if (!ctype_digit($v)) continue;
      $id = intval($v);
      $resolved = false;
      if ($tax && taxonomy_exists($tax)) {
        $term = get_term_by('id', $id, $tax);
        if ($term && !is_wp_error($term) && isset($term->name) && $term->name !== '') {
          $labels[] = $term->name;
          $resolved = true;
        }
      }
      if (!$resolved) {
        $po = get_post($id);
        if ($po && !is_wp_error($po) && isset($po->post_title) && $po->post_title !== '') {
          $labels[] = $po->post_title;
        }
      }
    }
    // 6) Limpia duplicados
    $vals   = array_values(array_unique(pmx_trim_strs($vals)));
    $labels = array_values(array_unique(pmx_trim_strs($labels)));

    return array('vals' => $vals, 'labels' => $labels);
  }
}



/* ============================================================
   pmx_get_acf_labels
   Lee SIEMPRE las ETIQUETAS (labels) reales de un campo ACF.
   Soporta Select/Checkbox/Radio (via ->choices),
   Relationship/Objeto (usa ->name / ->post_title),
   y valores numéricos (intenta resolver como término o post).
   ============================================================ */
/* ====================== APLANADORES / BUSCADORES ====================== */
if (!function_exists('pmx_flatten_scalars')) {
  // Recoge valores y labels desde cualquier estructura (array/objeto)
  function pmx_flatten_scalars($val){
    $out=array();
    if (is_array($val)) {
      foreach($val as $k=>$v){ $out=array_merge($out, pmx_flatten_scalars($v)); }
    } elseif (is_object($val)) {
      if (isset($val->value)) $out=array_merge($out, pmx_flatten_scalars($val->value));
      if (isset($val->label)) $out=array_merge($out, pmx_flatten_scalars($val->label));
      if (isset($val->ID))    $out[]= (string)$val->ID;
    } elseif (is_scalar($val)) {
      $s=trim((string)$val); if($s!=='') $out[]=$s;
    }
    return $out;
  }
}
if (!function_exists('pmx_find_in_choices')) {
  // Busca recursivamente un label en 'choices' de ACF
  function pmx_find_in_choices($choices, $needle){
    if (!is_array($choices)) return null;
    foreach($choices as $k=>$v){
      if ((string)$k === (string)$needle && is_scalar($v)) return (string)$v;
      if (is_array($v)){
        // formatos posibles: [['value'=>'xx','label'=>'Nombre'], ...] o choices anidados
        if (isset($v['value']) && (string)$v['value']===(string)$needle && isset($v['label']) && is_scalar($v['label'])){
          return (string)$v['label'];
        }
        $found = pmx_find_in_choices($v, $needle);
        if ($found!==null) return $found;
      } elseif (is_object($v)) {
        if (isset($v->value) && (string)$v->value===(string)$needle && isset($v->label)) return (string)$v->label;
      }
    }
    return null;
  }
}
/* ============== MAPEADOR DE ACF KEYS -> TAXONOMÍAS (AJUSTADO) ============== */
if (!function_exists('pmx_key_to_tax')) {
  function pmx_key_to_tax($acf_key) {
    $map = array(
      // PROYECTO
      'sector_proyecto'   => 'sector_proyecto',
      'tipo_de_proyecto'  => 'tipo_de_proyecto',
      'etapa_proyecto'    => 'etapa_proyecto',
      'tipo_de_inversion' => 'tipo_de_inversion',

      // PREFERENCIAS (Ficha Registro Contacto/Inversionista)
      'tipos_de_proyecto_reg_inversionista'            => 'tipo_de_proyecto',
      'etapa_del_ciclo_de_inversion_reg_inversionista' => 'etapa_proyecto',
      'interes_en_proyectos_reg_inversionista'         => 'tipo_de_inversion',
    );

    return (isset($map[$acf_key]) && taxonomy_exists($map[$acf_key])) ? $map[$acf_key] : '';
  }
}

if (!function_exists('pmx_filter_stopwords')) {
  function pmx_filter_stopwords($tokens){
    $stop = array('','menu-principal','seleccionar','seleccione','seleccione-','proyectos-infraestructura','todos','todas','na','n-a');
    $out=array(); foreach(pmx_to_array($tokens) as $t){ $n=pmx_norm($t); if(!in_array($n,$stop,true)) $out[]=$t; }
    return $out;
  }
}

/* ====================== TOKENS ACF (VALORES + LABELS) ====================== */
if (!function_exists('pmx_get_acf_tokens')) {
  // Devuelve tokens comparables (valor + label) para un ACF:
  // - Usa field_object->choices si existe
  // - Si el valor es numérico e identifica un término (cuando sabemos la tax), añade el nombre del término
  // - Si no hay taxonomía, intenta resolverlo como post y usa post_title (muchos Nested Select guardan IDs de posts)
  // - Si el valor viene como "id|label", añade ambos
  function pmx_get_acf_tokens($post_or_user_id, $acf_key) {
    $tokens = array();
    $vals   = array();
    $choices = null;

    // 1) Intento con field object (cuando ACF lo expone)
    if (function_exists('get_field_object')) {
      $fo = get_field_object($acf_key, $post_or_user_id);
      if (is_array($fo)) {
        if (isset($fo['value']))   $vals = pmx_flatten_scalars($fo['value']);
        if (isset($fo['choices'])) $choices = $fo['choices'];
      }
    }

    // 2) Fallback: get_field directo
    if (empty($vals) && function_exists('get_field')) {
      $vals = pmx_flatten_scalars(get_field($acf_key, $post_or_user_id));
    }

    // 3) Siempre añadimos los valores crudos
    foreach ($vals as $v) {
      $v = trim((string)$v);
      if ($v === '') continue;
      $tokens[] = $v;

      // 3a) Si viene como "id|label" o "id;label", separa y añade el label
      if (strpos($v, '|') !== false || strpos($v, ';') !== false) {
        $sep = (strpos($v, '|') !== false) ? '|' : ';';
        $parts = explode($sep, $v, 2);
        if (count($parts) === 2) {
          $label = trim($parts[1]);
          if ($label !== '') $tokens[] = $label;
        }
      }

      // 3b) Choices de ACF (label desde choices si existe)
      if ($choices !== null) {
        $lab = pmx_find_in_choices($choices, $v);
        if (is_string($lab) && $lab !== '') $tokens[] = $lab;
      }
    }

    // 4) Polylang strings (pll_xxxxx)
    if (function_exists('pll__')) {
      $extra = array();
      foreach ($tokens as $t) {
        if (is_string($t) && strpos($t, 'pll_') === 0) {
          $lab = pll__($t);
          if (is_string($lab) && trim($lab) !== '') $extra[] = $lab;
        }
      }
      $tokens = array_merge($tokens, $extra);
    }

    // 5) Si el valor es numérico, intenta resolverlo como TÉRMINO o como POST
    //    a) término: sólo si conocemos la taxonomía asociada a este ACF
    $tax = pmx_key_to_tax($acf_key);
    $extra = array();
    foreach ($tokens as $t) {
      $st = trim((string)$t);
      if ($st === '' || !ctype_digit($st)) continue;
      $id = intval($st);

      // a) taxonomía conocida (si existe)
      if ($tax && taxonomy_exists($tax)) {
        $term = get_term_by('id', $id, $tax);
        if ($term && !is_wp_error($term) && isset($term->name) && $term->name !== '') {
          $extra[] = $term->name;
          continue; // no intentes post si ya resolviste por tax
        }
      }

      // b) como POST (muy usado por Nested Select: opciones como CPT)
      $po = get_post($id);
      if ($po && !is_wp_error($po) && isset($po->post_title) && $po->post_title !== '') {
        $extra[] = $po->post_title;
      }
    }
    if (!empty($extra)) $tokens = array_merge($tokens, $extra);

    // 6) Limpia “stopwords” y duplica
    $tokens = pmx_filter_stopwords($tokens);
    $clean = array();
    foreach ($tokens as $t) {
      $t = trim((string)$t);
      if ($t !== '') $clean[$t] = true;
    }
    return array_keys($clean);
  }
}


/* ====================== SECTORES (PREFERENCIAS) ====================== */
if (!function_exists('pmx_get_pref_sector_tokens')) {
  function pmx_get_pref_sector_tokens($inv_id){
    // Si existe un ACF agregador de sectores, úsalo:
    $t = pmx_get_acf_tokens($inv_id, 'sectores_reg_inversionista');
    if (!empty($t)) return $t;

    // Si no, construye desde checkboxes individuales:
    $flags = array(
      'electricidad_reg_inversionista'           => 'Electricidad',
      'hidrocarburos_reg_inversionista'          => 'Hidrocarburos',
      'transporte_reg_inversionista'             => 'Transporte',
      'infraestructura_social_reg_inversionista' => 'Infraestructura Social',
      'agua_y_medio_ambiente_reg_inversionista'  => 'Agua y Medio Ambiente',
      'industria_reg_inversionista'              => 'Industria',
      'mineria_reg_inversionista'                => 'Minería',
      'inmobiliario_y_turismo_reg_inversionista' => 'Inmobiliario y Turismo',
      'telecomunicaciones_reg_inversionista'     => 'Telecomunicaciones',
    );
    $out=array();
    foreach($flags as $k=>$label){
      $v=get_field($k,$inv_id);
      if ($v===1||$v==='1'||$v===true||$v==='Si'||$v==='sí'||$v==='on') $out[]=$label;
    }
    return $out;
  }
}

/* ====================== SUBSECTOR (PREFERENCIAS: TODOS LOS SECTORES) ====================== */
if (!function_exists('pmx_get_pref_subsector_ids')) {
  function pmx_get_pref_subsector_ids($inv_id) {
    // TODOS los campos “Select Sector” que listaste en Registros Inversionistas
    $keys = array(
      'electricidad_sub_reg_inversionista',
      'hidrocarburos_sub-sectores',
      'transporte_sub-sectores',
      'infraestructura_sub-sectores',
      'agua_sub-sectores',
      'industria_sub-sectores',
      'mineria_sub-sectores',
      'inmobiliario_sub-sectores',
      'telecom_sub-sectores',
    );

    // Función interna: agrega IDs numéricos al set $bag
    $collect = function($val, array &$bag) {
      // a) JSON string -> array
      if (is_string($val)) {
        $s = trim($val);
        if ($s === '') return;
        $j = json_decode($s, true);
        if (json_last_error() === JSON_ERROR_NONE) $val = $j;
      }

      // b) Array/Objeto
      if (is_array($val)) {
        foreach ($val as $e) {
          // objetos/arrays con value/ID/id/term_id
          if (is_array($e)) {
            foreach (array('value','ID','id','term_id') as $k) {
              if (isset($e[$k]) && ctype_digit((string)$e[$k])) $bag[(int)$e[$k]] = true;
            }
          } elseif (is_object($e)) {
            foreach (array('value','ID','id','term_id') as $k) {
              if (isset($e->$k) && ctype_digit((string)$e->$k)) $bag[(int)$e->$k] = true;
            }
          } elseif (is_scalar($e)) {
            $str = trim((string)$e);
            if ($str === '') continue;
            // “1,2,3”
            if (preg_match('/^\d+(?:\s*,\s*\d+)*$/', $str)) {
              foreach (preg_split('/\s*,\s*/', $str) as $tok) $bag[(int)$tok] = true;
            } else {
              // “123|Label”, “123;Label”, etc. -> toma el número inicial
              if (preg_match('/^\s*(\d+)/', $str, $m)) $bag[(int)$m[1]] = true;
            }
          }
        }
        return;
      }

      // c) Escalar simple
      if (is_scalar($val)) {
        $str = trim((string)$val);
        if ($str === '') return;
        if (preg_match('/^\d+(?:\s*,\s*\d+)*$/', $str)) {
          foreach (preg_split('/\s*,\s*/', $str) as $tok) $bag[(int)$tok] = true;
        } elseif (preg_match('/^\s*(\d+)/', $str, $m)) {
          $bag[(int)$m[1]] = true;
        }
      }
    };

    $bag = array();
    foreach ($keys as $k) {
      $v = function_exists('get_field') ? get_field($k, $inv_id) : get_post_meta($inv_id, $k, true);
      if ($v !== null && $v !== false && $v !== '') $collect($v, $bag);
    }

    $out = array_keys($bag);
    sort($out, SORT_NUMERIC);

    if (function_exists('pmx_log')) pmx_log('[PREF_SUBSEC] inv='.$inv_id.' ids=['.implode(',', $out).']');
    return $out;
  }
}

/* ====================== SUBSECTOR(ES) DEL PROYECTO ====================== */
/* Devuelve SIEMPRE un arreglo de IDs (int) del ACF 'subsector_proyecto'.
   Soporta: número simple, array/objeto, o strings tipo "123|Label". */
if (!function_exists('pmx_get_project_subsector_ids')) {
  function pmx_get_project_subsector_ids($pid) {
    $val = function_exists('get_field')
      ? get_field('subsector_proyecto', $pid)
      : get_post_meta($pid, 'subsector_proyecto', true);

    $bag = array(); // set de IDs
    $collect = function($v) use (&$bag, &$collect) {
      if ($v === null || $v === false || $v === '') return;

      // array
      if (is_array($v)) { foreach ($v as $e) $collect($e); return; }

      // objeto con ID/value
      if (is_object($v)) {
        foreach (array('ID','id','value','term_id') as $k) {
          if (isset($v->$k) && ctype_digit((string)$v->$k)) { $bag[(int)$v->$k] = true; }
        }
        return;
      }

      // número suelto
      if (is_numeric($v)) { $bag[(int)$v] = true; return; }

      // string "123|Label" o "123,456"
      $s = trim((string)$v);
      if ($s === '') return;
      if (preg_match('/^\d+(?:\s*,\s*\d+)*$/', $s)) {
        foreach (preg_split('/\s*,\s*/', $s) as $tok) $bag[(int)$tok] = true;
        return;
      }
      if (preg_match('/^\s*(\d+)/', $s, $m)) { $bag[(int)$m[1]] = true; }
    };

    $collect($val);
    $out = array_keys($bag);
    sort($out, SORT_NUMERIC);
    return $out;
  }
}



/* =====================================================================
 * SOLO DOS CAMPOS DE PREFERENCIAS (INV): TIPOS y ETAPAS
 * Convierte los códigos numéricos que guarda ACF en etiquetas legibles.
 * Reemplaza cualquier versión previa de estas funciones.
 * ===================================================================== */

/* Helper: aplica una tabla de conversión código->etiqueta a una lista */
if (!function_exists('pmx_map_tokens_by_table')) {
  function pmx_map_tokens_by_table($tokens, $map) {
    $tokens = is_array($tokens) ? $tokens : ($tokens!=='' ? array($tokens) : array());
    $out = array();
    $mapped_any = false;
    foreach ($tokens as $t) {
      $k = (string)$t;
      if (isset($map[$k])) {
        $out[] = $map[$k];
        $mapped_any = true;
      } else {
        $out[] = $t; // deja pasar si no existe en el mapa
      }
    }
    // dedup y trim
    $clean = array();
    foreach ($out as $x) {
      $x = trim((string)$x);
      if ($x !== '') $clean[$x] = true;
    }
    return array_keys($clean);
  }
}

/* Tablas de conversión (puedes sobreescribir con filtros si quieres) */
if (!function_exists('pmx_get_pref_tipo_map')) {
    function pmx_get_pref_tipo_map() {
        // Códigos reales del ACF:
        // 1 = Todos (no filtra), 2 = Público, 3 = Privado, 4 = Público / Privado
        // Ajusta si tu ACF usa otros valores.
        $map = array(
            '2' => 'Público',
            '4' => 'Privado',
            '3' => 'Público / Privado',
        );
        if (function_exists('apply_filters')) $map = apply_filters('pmx_map_pref_tipo_codes', $map);
        return $map;
    }
}


if (!function_exists('pmx_get_pref_etapa_map')) {
  function pmx_get_pref_etapa_map() {
    // Códigos que devuelve ACF en etapa_del_ciclo_de_inversion_reg_inversionista
    $map = array(
	  '2' => 'preinversion',
      '7' => 'Licitación',
      '6' => 'Ejecución',
      '5' => 'Operación',
      // añade más si aparecen en logs
    );
    if (function_exists('apply_filters')) $map = apply_filters('pmx_map_pref_etapa_codes', $map);
    return $map;
  }
}


/* ====================== ETAPAS (PREFERENCIAS) ====================== */
/* Usa ÚNICAMENTE el campo: etapa_del_ciclo_de_inversion_reg_inversionista */
if (!function_exists('pmx_get_pref_etapa_tokens')) {
  function pmx_get_pref_etapa_tokens($inv_id){
    // tokens crudos desde ACF (normalmente números como "5","6","7")
    $raw = pmx_get_acf_tokens($inv_id, 'etapa_del_ciclo_de_inversion_reg_inversionista');
    // mapear a etiquetas legibles
    $mapped = pmx_map_tokens_by_table($raw, pmx_get_pref_etapa_map());
    if (function_exists('pmx_log')) pmx_log('[DBG] Pref ETAPAS raw=['.pmx_join($raw).'] mapped=['.pmx_join($mapped).']');
    return $mapped;
  }
}


/* ====================== TIPOS (PREFERENCIAS) ====================== */
/* Usa ÚNICAMENTE el campo: tipos_de_proyecto_reg_inversionista */
if (!function_exists('pmx_get_pref_tipo_tokens')) {
  function pmx_get_pref_tipo_tokens($inv_id){
    // tokens crudos desde ACF (normalmente números como "2" o "4")
    $raw = pmx_get_acf_tokens($inv_id, 'tipos_de_proyecto_reg_inversionista');
    // mapear a etiquetas legibles
    $mapped = pmx_map_tokens_by_table($raw, pmx_get_pref_tipo_map());
    if (function_exists('pmx_log')) pmx_log('[DBG] Pref TIPOS raw=['.pmx_join($raw).'] mapped=['.pmx_join($mapped).']');
    return $mapped;
  }
}


/* ====================== INTERÉS (PREFERENCIAS) ====================== */
/* Mapa por defecto de códigos (id_nested_select) a etiquetas legibles.
   Puedes sobreescribir con el filtro 'pmx_map_pref_interes_codes' si cambian. */
if (!function_exists('pmx_map_pref_interes_codes')) {
  function pmx_map_pref_interes_codes($codes){
    $map = array(
      '2' => 'Brownfield',
      '3' => 'Greenfield',
      // agrega aquí otros códigos si aparecen en tu ACF
    );
    if (function_exists('apply_filters')) {
      $map = apply_filters('pmx_map_pref_interes_codes', $map, $codes);
    }
    $out = array();
    foreach ((array)$codes as $c){
      $k = (string)$c;
      if (isset($map[$k])) $out[] = $map[$k];
    }
    return $out;
  }
}

/* Devuelve SIEMPRE etiquetas para el interés del inversionista.
   Lee SOLO el ACF: interes_en_proyectos_reg_inversionista */
if (!function_exists('pmx_get_pref_interes_tokens')) {
  function pmx_get_pref_interes_tokens($inv_id){
    // Lee los tokens crudos del ACF (pueden venir como "2", "3" o "2|Greenfield")
    $t = pmx_get_acf_tokens($inv_id, 'interes_en_proyectos_reg_inversionista');

    // Si ya vinieran etiquetas en el token (por ejemplo "2|Greenfield"), pmx_get_acf_tokens
    // las habría añadido; en ese caso simplemente regresamos $t.
    // Si son solo números, mapeamos a etiquetas:
    $solo_numeros = !empty($t);
    foreach ($t as $x){ if (!ctype_digit((string)$x)) { $solo_numeros = false; break; } }

    if ($solo_numeros) {
      $labels = pmx_map_pref_interes_codes($t);
      if (!empty($labels)) return $labels;
      // Si por alguna razón no mapeó, regresa lo crudo (pero no debería pasar si el mapa está bien)
    }

    return $t;
  }
}



/* ====================== MONTO PROYECTO (mejores intentos) ====================== */
if (!function_exists('pmx_get_monto_usd')) {
  function pmx_get_monto_usd($pid){
    // 1) Si usan “montos personalizados” en USD:
    $use_custom = get_field('utilizar_montos_personalizados', $pid);
    $m_usd = get_field('monto_personalizado_usd', $pid);
    if ($use_custom && is_numeric($m_usd)) return floatval($m_usd);

    // 2) Si existe un total en USD previo:
    $mt = get_field('monto_total_usd', $pid);
    if (is_numeric($mt)) return floatval($mt);

    // 3) Fallback: “monto_inversion” (moneda original). Sin conversión por no tener tasa aquí.
    $m = get_field('monto_inversion', $pid);
    if (is_numeric($m)) return floatval($m);

    return null;
  }
}




/* ====== CONFIG ====== */
if (!defined('PMX_NUEVO_TTL')) {
  // 30 minutos
  // define('PMX_NUEVO_TTL', 1800); //36000 10 horas
   // Fallback SOLO si la página no pasa TTL (shortcode/URL)
  define('PMX_NUEVO_TTL', 518400); // 6 dias 
}
if (!defined('PMX_NUEVO_META'))        define('PMX_NUEVO_META', 'nuevo');
if (!defined('PMX_NUEVO_MARKED_META')) define('PMX_NUEVO_MARKED_META', 'nuevo_marked_at'); // epoch UTC


if (!function_exists('pmx_stamp_nuevo_on_save')) {
  function pmx_stamp_nuevo_on_save($post_id, $post, $update){
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_type !== 'proyecto_inversion') return;

    $val   = get_post_meta($post_id, PMX_NUEVO_META, true);
    $is_on = ($val==='1'||$val===1||$val===true||$val==='true');

    if ($is_on) {
      $ts = (int) get_post_meta($post_id, PMX_NUEVO_MARKED_META, true);
      if ($ts <= 0) pmx_set_stamp_guarded($post_id, current_time('timestamp', true)); // ✅
    } else {
      pmx_del_stamp_guarded($post_id);
    }
  }
  add_action('save_post_proyecto_inversion','pmx_stamp_nuevo_on_save',20,3);
}

/* ====== STAMP en guardado (UTC) ====== */
/*if (!function_exists('pmx_stamp_nuevo_on_save')) {
  function pmx_stamp_nuevo_on_save($post_id, $post, $update){
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_type !== 'proyecto_inversion') return;

    $val   = get_post_meta($post_id, PMX_NUEVO_META, true);
    $is_on = ($val==='1' || $val===1 || $val===true || $val==='true');

   if ($is_on) {
  $ts = (int) get_post_meta($post_id, PMX_NUEVO_MARKED_META, true);
  if ($ts <= 0) {
    update_post_meta($post_id, PMX_NUEVO_MARKED_META, current_time('timestamp', true));
  }
} else {
  delete_post_meta($post_id, PMX_NUEVO_MARKED_META);
}

  }
  add_action('save_post_proyecto_inversion','pmx_stamp_nuevo_on_save',20,3);
}*/

if (!function_exists('pmx_boolish')) {
  function pmx_boolish($v){ if (is_string($v)) $v = strtolower(trim($v));
    return in_array($v, array('1',1,true,'true','on','yes','si','sí'), true);
  }
}

add_filter('acf/update_value/name=nuevo', function($value, $post_id, $field){
  $now_true = pmx_boolish($value);
  $old_raw  = function_exists('get_field') ? get_field('nuevo', $post_id, false) : get_post_meta($post_id, 'nuevo', true);
  $was_true = pmx_boolish($old_raw);

  if ($now_true && !$was_true) {
    // OFF -> ON: sellar SOLO si no hay stamp
    $ts = (int) get_post_meta($post_id, PMX_NUEVO_MARKED_META, true);
    if ($ts <= 0) pmx_set_stamp_guarded($post_id, current_time('timestamp', true)); // UTC
  } elseif (!$now_true && $was_true) {
    // ON -> OFF: limpiar
    pmx_del_stamp_guarded($post_id);
  }
  // ON->ON u OFF->OFF: no tocar
  return $value;
}, 20, 3);




/* ====== BACKFILL (UTC) ====== */
if (!function_exists('pmx_backfill_marked_at')) {
  function pmx_backfill_marked_at(){
    $q = new WP_Query(array(
      'post_type'      => 'proyecto_inversion',
      'post_status'    => 'any',
      'posts_per_page' => -1,
      'fields'         => 'ids',
      'meta_query'     => array(
        'relation' => 'AND',
        array('key'=>PMX_NUEVO_META,'value'=>'1','compare'=>'='),
        array('key'=>PMX_NUEVO_MARKED_META,'compare'=>'NOT EXISTS'),
      ),
    ));
    $n=0;
    foreach ($q->posts as $pid) {
      // usa fecha de modificación como aproximación; si no, ahora (UTC)
      $ts = (int) get_post_modified_time('U', true, $pid);
      if ($ts <= 0) $ts = current_time('timestamp', true);
      update_post_meta($pid, PMX_NUEVO_MARKED_META, $ts);
      $n++;
      if (function_exists('pmx_log')) pmx_log('BACKFILL '.$pid.' nuevo_marked_at='.gmdate('Y-m-d H:i:s',$ts).' UTC');
    }
    if (function_exists('pmx_log')) pmx_log('BACKFILL total='.$n);
    return $n;
  }
}

/* ====== AUTO-UNMARK (UTC) — DEVUELVE INT ====== */
if (!function_exists('pmx_auto_unmark_expired_nuevos')) {
  function pmx_auto_unmark_expired_nuevos($ttl = PMX_NUEVO_TTL){
    $now = current_time('timestamp', true); // UTC
    $cut = $now - (int)$ttl;

    $q = new WP_Query(array(
      'post_type'      => 'proyecto_inversion',
      'post_status'    => 'any',
      'posts_per_page' => -1,
      'fields'         => 'ids',
      'meta_query'     => array(
        'relation' => 'AND',
        array('key'=>PMX_NUEVO_META,        'value'=>'1', 'compare'=>'='),
        array('key'=>PMX_NUEVO_MARKED_META, 'value'=>$cut, 'type'=>'NUMERIC', 'compare'=>'<=')
      ),
    ));

    $cnt = 0;
    foreach ($q->posts as $pid){
      update_post_meta($pid, PMX_NUEVO_META, '0');
      delete_post_meta($pid, PMX_NUEVO_MARKED_META);
      $cnt++;
      if (function_exists('pmx_log')) pmx_log('AUTO_UNMARK pid='.$pid.' expired ttl='.$ttl.'s at '.gmdate('Y-m-d H:i:s',$now).' UTC');
    }
    if (function_exists('pmx_log')) pmx_log('AUTO_UNMARK total='.$cnt);
    return $cnt;
  }
}


/* ============================================================
   OBTENER PROYECTOS NUEVOS
   Lee “nuevo = 1” + categoría 563, hace snapshot con labels,
   y opcionalmente aplica filtro por preferencias ($inv_id).
   ============================================================ */
/* ====== OBTENER PROYECTOS NUEVOS (solo dentro de la ventana de 30 min) ======
   - Backfill + Auto-unmark antes de consultar
   - Filtra por nuevo=1 y nuevo_marked_at >= corte */
if (!function_exists('obtener_proyectos_nuevos')) {
  function obtener_proyectos_nuevos($inv_id = 0, $ttl_seconds = PMX_NUEVO_TTL) {
    pmx_backfill_marked_at();
    pmx_auto_unmark_expired_nuevos($ttl_seconds);

    $cut = current_time('timestamp') - (int)$ttl_seconds;

    $args = array(
      'post_type'      => 'proyecto_inversion',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => array('date' => 'DESC', 'title' => 'ASC'),
      'tax_query'      => array(
        array(
          'taxonomy' => 'categoria_macroproyecto',
          'field'    => 'term_id',
          'terms'    => array(563),
          'operator' => 'IN',
        ),
      ),
      'meta_query'     => array(
        'relation' => 'AND',
        array('key'=>'nuevo','value'=>'1','compare'=>'='),
        array('key'=>'nuevo_marked_at','value'=>$cut,'compare'=>'>=','type'=>'NUMERIC'),
      ),
    );

    $q = new WP_Query($args);
    pmx_log('Proyectos NUEVOS encontrados (<= '.intval($ttl_seconds).'s): ' . intval($q->found_posts));

    // (logs opcionales)
    foreach ($q->posts as $p) {
      $pid=$p->ID; $tit=get_the_title($pid);
      $sec=pmx_get_acf_tokens($pid,'sector_proyecto');
	  $sub=pmx_get_acf_tokens($pid,'subsector_proyecto');
      $tip=pmx_get_acf_tokens($pid,'tipo_de_proyecto');
      $eta=pmx_get_acf_tokens($pid,'etapa_proyecto');
      $inv=pmx_get_acf_tokens($pid,'tipo_de_inversion');
      $mon=pmx_get_monto_usd($pid);
      //pmx_log('  PID '.$pid.' | "'.$tit.'" | sector=['.pmx_join($sec).'] subsector=['.pmx_join($sub).'] tipo=['.pmx_join($tip).'] etapa=['.pmx_join($eta).'] interes=['.pmx_join($inv).'] monto='.($mon===null?'(n/a)':$mon));
	  
	  pmx_log('  PID '.$pid.' | "'.$tit.'" | sector=['.pmx_join($sec).'] subsector=['.pmx_join($sub).'] tipo=['.pmx_join($tip).'] etapa=['.pmx_join($eta).'] interes=['.pmx_join($inv).'] monto='.((trim((string)$mon)==='' || strcasecmp((string)$mon,'null')===0 || (is_numeric($mon) && (float)$mon==0.0))? '0': number_format((float)$mon, 0, '.', ',')));

    }

    $posts = $q->posts;
    if ($inv_id && function_exists('pmx_filtra_proyectos_por_preferencias')) {
      $posts = pmx_filtra_proyectos_por_preferencias($posts, $inv_id);
    }
    return $posts;
  }
}

/* ============================================================
   OBTENER INVERSIONISTAS SUSCRITOS A “PROYECTOS NUEVOS”
   ============================================================ */
if (!function_exists('obtener_inversionistas_para_nuevos_proyectos')) {
  function obtener_inversionistas_para_nuevos_proyectos() {
    $args = array(
      'post_type'      => 'reg_inversionistas',
      'post_status'    => array('draft'),
      'posts_per_page' => -1,
      'meta_query'     => array(
        'relation' => 'OR',
        array('key' => 'proyectos_nuevos_fin', 'value' => '1',  'compare' => '='),
        array('key' => 'proyectos_nuevos_fin', 'value' => 1,    'compare' => '='),
        array('key' => 'proyectos_nuevos_fin', 'value' => true, 'compare' => '='),
      ),
    );
    $q = new WP_Query($args);
    return $q->posts;
  }
}

/* ====================== PREFERENCIAS: resolutores específicos ====================== */
/* Intenta resolver labels “ocultos” que algunos Nested Select guardan como meta paralela */
if (!function_exists('pmx_try_meta_side_labels')) {
  function pmx_try_meta_side_labels($post_or_user_id, $acf_key) {
    $candidates = array(
      $acf_key.'_label',
      $acf_key.'_labels',
      $acf_key.'_text',
      '_'.$acf_key.'_label',
      '_'.$acf_key.'_labels',
      '_'.$acf_key.'_text',
    );
    $out = array();
    foreach ($candidates as $k) {
      $v = get_post_meta($post_or_user_id, $k, true);
      if (!$v) continue;
      if (is_array($v)) {
        foreach ($v as $x) { $s = trim((string)$x); if ($s!=='') $out[] = $s; }
      } else {
        // puede venir “label1, label2” o “id|label”
        $s = trim((string)$v);
        if ($s==='') continue;
        // separadores comunes
        $parts = preg_split('~\s*,\s*~', $s);
        foreach ($parts as $p) {
          $p = trim($p);
          if ($p==='') continue;
          // si viene "id|label", "id;label", "id:label", "id=>label"
          if (preg_match('~^[^|;:=>]+[|;:=>]+(.+)$~', $p, $m)) {
            $lab = trim($m[1]);
            if ($lab!=='') $out[] = $lab;
          } else {
            $out[] = $p;
          }
        }
      }
    }
    return array_values(array_unique($out));
  }
}

/* Normaliza conjunto (valores + labels) para comparar */
if (!function_exists('pmx_tokens_with_best_effort_labels')) {
  function pmx_tokens_with_best_effort_labels($post_or_user_id, $acf_key) {
    $tokens = pmx_get_acf_tokens($post_or_user_id, $acf_key); // ya mete value, labels por choices/tax/post/PLL si existen
    // si sólo quedaron dígitos, intenta meta side-labels
    $all_num = true;
    foreach ($tokens as $t) { if (!ctype_digit((string)$t)) { $all_num=false; break; } }
    if ($all_num) {
      $side = pmx_try_meta_side_labels($post_or_user_id, $acf_key);
      if (!empty($side)) $tokens = array_merge($tokens, $side);
    }
    // si aún son puros códigos, aplica filtro para mapeo externo
    $still_num = true;
    foreach ($tokens as $t) { if (!ctype_digit((string)$t)) { $still_num=false; break; } }
    if ($still_num) {
      $codes = array_values(array_unique($tokens));
      $mapped = array();
      $hook = '';
      if ($acf_key === 'tipos_de_proyecto_reg_inversionista')            $hook = 'pmx_map_pref_tipo_codes';
      if ($acf_key === 'etapa_del_ciclo_de_inversion_reg_inversionista') $hook = 'pmx_map_pref_etapa_codes';
      if ($acf_key === 'interes_en_proyectos_reg_inversionista')         $hook = 'pmx_map_pref_interes_codes';
      if ($hook) {
        $map = apply_filters($hook, array()); // ej. en theme: add_filter('pmx_map_pref_interes_codes', fn($m)=> $m + array('2'=>'Greenfield','3'=>'Brownfield'));
        foreach ($codes as $c) {
          $mapped[] = isset($map[$c]) ? $map[$c] : $c;
        }
        $tokens = array_values(array_unique(array_merge($codes, $mapped)));
      }
    }
    return $tokens;
  }
}

/* Preferencias específicas usando el resolutor anterior */
if (!function_exists('pmx_get_pref_tipo_tokens')) {
  function pmx_get_pref_tipo_tokens($inv_id) {
    return pmx_tokens_with_best_effort_labels($inv_id, 'tipos_de_proyecto_reg_inversionista');
  }
}
if (!function_exists('pmx_get_pref_etapa_tokens')) {
  function pmx_get_pref_etapa_tokens($inv_id) {
    return pmx_tokens_with_best_effort_labels($inv_id, 'etapa_del_ciclo_de_inversion_reg_inversionista');
  }
}
if (!function_exists('pmx_get_pref_interes_tokens')) {
  function pmx_get_pref_interes_tokens($inv_id) {
    return pmx_tokens_with_best_effort_labels($inv_id, 'interes_en_proyectos_reg_inversionista');
  }
}

/* ====================== RESOLVER LABELS PARA PREFS (Nested Select) ====================== */
/* Busca labels guardados en metas paralelas del ACF (algunos plugins almacenan *_label|*_labels|*_text) */
if (!function_exists('pmx_try_meta_side_labels')) {
  function pmx_try_meta_side_labels($post_or_user_id, $acf_key) {
    $candidates = array(
      $acf_key.'_label', $acf_key.'_labels', $acf_key.'_text',
      '_'.$acf_key.'_label', '_'.$acf_key.'_labels', '_'.$acf_key.'_text',
    );
    $out = array();
    foreach ($candidates as $k) {
      $v = get_post_meta($post_or_user_id, $k, true);
      if (!$v) continue;
      if (is_array($v)) {
        foreach ($v as $x) { $s = trim((string)$x); if ($s!=='') $out[] = $s; }
      } else {
        $s = trim((string)$v); if ($s==='') continue;
        // separar "label1, label2" o "id|label"
        $parts = preg_split('~\s*,\s*~', $s);
        foreach ($parts as $p) {
          $p = trim($p); if ($p==='') continue;
          if (preg_match('~^[^|;:=>]+[|;:=>]+(.+)$~', $p, $m)) {
            $lab = trim($m[1]); if ($lab!=='') $out[] = $lab;
          } else { $out[] = $p; }
        }
      }
    }
    return array_values(array_unique($out));
  }
}




//revisando
/* Mapeo por filtros con fallback por defecto (puedes sobreescribir en el theme) */
if (!function_exists('pmx_map_pref_codes')) {
  function pmx_map_pref_codes($acf_key, $codes) {
    $default = array(); $filter = '';
    if ($acf_key === 'tipos_de_proyecto_reg_inversionista') {
      $filter  = 'pmx_map_pref_tipo_codes';
      // Fallbacks típicos (ajusta en tu theme vía filtros)
      $default = array('4'=>'Privado','2'=>'Público','3'=>'Público / Privado');
    } elseif ($acf_key === 'etapa_del_ciclo_de_inversion_reg_inversionista') {
      $filter  = 'pmx_map_pref_etapa_codes';
      $default = array('2'=>'Preinverción','7'=>'Licitación','6'=>'Ejecución','5'=>'Operación');
    } elseif ($acf_key === 'interes_en_proyectos_reg_inversionista') {
      $filter  = 'pmx_map_pref_interes_codes';
      $default = array('2'=>'Greenfield','3'=>'Brownfield');
    }
    // Permite sobrescribir desde el theme/mu-plugin:
    if ($filter) $default = apply_filters($filter, $default, $codes);

    $labels = array();
    foreach ($codes as $c) {
      $k = (string)$c;
      $labels[] = isset($default[$k]) ? $default[$k] : $k;
    }
    return array_values(array_unique($labels));
  }
}

/* Tokens “best-effort” para preferencias: valores + labels desde meta/filters */
if (!function_exists('pmx_tokens_with_best_effort_labels')) {
  function pmx_tokens_with_best_effort_labels($post_or_user_id, $acf_key) {
    // 1) Tokens básicos (usa tu pmx_get_acf_tokens existente)
    $tokens = pmx_get_acf_tokens($post_or_user_id, $acf_key);

    // 2) Si TODO son números, intenta metas paralelas *_label|*_labels|*_text
    $all_num = true; foreach ($tokens as $t) { if (!ctype_digit((string)$t)) { $all_num=false; break; } }
    if ($all_num) {
      $side = pmx_try_meta_side_labels($post_or_user_id, $acf_key);
      if (!empty($side)) $tokens = array_merge($tokens, $side);
    }

    // 3) Si aún son números, mapea por filtros (y/o fallback por defecto)
    $still_num = true; foreach ($tokens as $t) { if (!ctype_digit((string)$t)) { $still_num=false; break; } }
    if ($still_num) {
      $labels = pmx_map_pref_codes($acf_key, $tokens);
      $tokens = array_merge($tokens, $labels);
    }

    // 4) Limpia duplicados/vacíos
    $clean = array();
    foreach ($tokens as $t) { $s = trim((string)$t); if ($s!=='') $clean[$s] = true; }
    return array_keys($clean);
  }
}

/* Wrappers específicos para cada preferencia */
if (!function_exists('pmx_get_pref_tipo_tokens')) {
    function pmx_get_pref_tipo_tokens($inv_id){
        // Lee lo que guardó ACF (normalmente números como "1","2","3","4")
        $raw = pmx_get_acf_tokens($inv_id, 'tipos_de_proyecto_reg_inversionista');

        // Si el usuario eligió "Todos" -> ACF suele guardar "1".
        // También cubrimos texto "todos" por si viene como label.
        $raw_norm = pmx_norm_arr($raw);
        if (in_array('1', (array)$raw, true) || in_array('todos', $raw_norm, true)) {
            if (function_exists('pmx_log')) pmx_log('[DBG] Pref TIPOS => TODOS (sin filtro)');
            return array(); // vacío = NO filtra por tipo
        }

        // Si NO es "Todos", mapea códigos -> etiquetas legibles
        $mapped = pmx_map_tokens_by_table($raw, pmx_get_pref_tipo_map());
        if (function_exists('pmx_log')) pmx_log('[DBG] Pref TIPOS raw=['.pmx_join($raw).'] mapped=['.pmx_join($mapped).']');

        return $mapped;
    }
}
if (!function_exists('pmx_get_pref_etapa_tokens')) {
  function pmx_get_pref_etapa_tokens($inv_id) {
    return pmx_tokens_with_best_effort_labels($inv_id, 'etapa_del_ciclo_de_inversion_reg_inversionista');
  }
}
if (!function_exists('pmx_get_pref_interes_tokens')) {
  function pmx_get_pref_interes_tokens($inv_id) {
    return pmx_tokens_with_best_effort_labels($inv_id, 'interes_en_proyectos_reg_inversionista');
  }
}



/* ====== CATÁLOGO DE SECTORES (CPT: catalogo_sectores) ====== */
if (!function_exists('pmx_catalogo_sectores_index')) {
    function pmx_catalogo_sectores_index($force=false){
        static $cache=null; if($cache!==null && !$force) return $cache;

        $ids = get_posts(array(
            'post_type'      => 'catalogo_sectores',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => array('title'=>'ASC'),
            'fields'         => 'ids',
        ));

        $id_by_norm    = array();
        $norm_by_id    = array();
        $label_by_norm = array();

        foreach ((array)$ids as $id){
            $t = get_post_field('post_title', $id);
            $n = function_exists('pmx_norm') ? pmx_norm($t) : sanitize_title($t);
            if (in_array($n, array('na','n-a','nd','n-d'), true)) continue; // descarta “- NA -” y “- ND -”
            $norm_by_id[$id] = $n;
            if (!isset($label_by_norm[$n])) $label_by_norm[$n] = $t;
            $id_by_norm[$n] = $id; // último gana (nos basta un canónico)
        }

        /* Aliases EN -> ES para unificar idioma (ajústalos si tu catálogo cambia) */
        $alias = array(
            'electricity'             => 'electricidad',
            'hydrocarbons'            => 'hidrocarburos',
            'transport'               => 'transporte',
            'social-infrastructure'   => 'infraestructura-social',
            'water-and-environment'   => 'agua-y-medio-ambiente',
            'industry'                => 'industria',
            'mining'                  => 'mineria',
            'real-estate-and-tourism' => 'inmobiliario-y-turismo',
            'telecommunications'      => 'telecomunicaciones',
        );

        // Si el catálogo solo tuviera ES o EN, crea alias cruzados
        foreach ($alias as $from=>$to){
            if (isset($id_by_norm[$to]) && !isset($id_by_norm[$from])) {
                $id_by_norm[$from]    = $id_by_norm[$to];
                $label_by_norm[$from] = $label_by_norm[$to];
            } elseif (isset($id_by_norm[$from]) && !isset($id_by_norm[$to])) {
                $id_by_norm[$to]      = $id_by_norm[$from];
                $label_by_norm[$to]   = $label_by_norm[$from];
            }
        }

        return $cache = array(
            'id_by_norm'    => $id_by_norm,
            'norm_by_id'    => $norm_by_id,
            'label_by_norm' => $label_by_norm,
            'alias'         => $alias,
        );
    }
}

/* ====== Canonizador: de IDs/labels varios -> claves canónicas del catálogo ====== */
if (!function_exists('pmx_sector_canonize')) {
    function pmx_sector_canonize($vals){
        $idx = pmx_catalogo_sectores_index();
        $out = array();

        foreach ((array)$vals as $v) {
            if ($v === '' || $v === null) continue;

            // 1) Si viene ID
            if (is_numeric($v)) {
                $id = intval($v);
                $n  = isset($idx['norm_by_id'][$id]) ? $idx['norm_by_id'][$id] : '';
                if ($n==='') { $t=get_post_field('post_title',$id); $n=function_exists('pmx_norm')?pmx_norm($t):sanitize_title($t); }
                if (isset($idx['alias'][$n])) $n=$idx['alias'][$n];
                if ($n!=='' && !in_array($n,array('na','n-a','nd','n-d'),true)) $out[$n]=1;
                continue;
            }

            // 2) Si viene "id|label"
            $s = (string)$v;
            if (preg_match('~^\s*(\d+)\s*[|;:]~',$s,$m)) {
                $id=intval($m[1]);
                if (isset($idx['norm_by_id'][$id])) {
                    $n=$idx['norm_by_id'][$id];
                    if (isset($idx['alias'][$n])) $n=$idx['alias'][$n];
                    if ($n!=='') $out[$n]=1;
                    continue;
                }
            }

            // 3) Como label suelto
            $n = function_exists('pmx_norm') ? pmx_norm($s) : sanitize_title($s);
            if (isset($idx['alias'][$n])) $n=$idx['alias'][$n];
            if ($n!=='' && !in_array($n,array('na','n-a','nd','n-d'),true)) $out[$n]=1;
        }

        return array_keys($out); // devuelvo claves canónicas (normalizadas)
    }
}


/* ====================== SECTORES (PREFERENCIAS) — VÍA CATÁLOGO ====================== */
if (!function_exists('pmx_get_pref_sector_tokens')) {
    function pmx_get_pref_sector_tokens($inv_id){
        // 1) ¿Tienes un Relationship/Select que apunte al CPT del catálogo?
        $raw = array();
        foreach (array('sectores_reg_inversionista','sector_reg_inversionista','sector_inversionista') as $k) {
            $v = function_exists('get_field') ? get_field($k, $inv_id) : get_post_meta($inv_id,$k,true);
            if (!empty($v)) { $raw = (array)$v; break; }
        }

        // 2) Si no hay, construye desde tus checkboxes (ES) y canoniza
        if (empty($raw)) {
            $flags = array(
                'electricidad_reg_inversionista'           => 'Electricidad',
                'hidrocarburos_reg_inversionista'          => 'Hidrocarburos',
                'transporte_reg_inversionista'             => 'Transporte',
                'infraestructura_social_reg_inversionista' => 'Infraestructura Social',
                'agua_y_medio_ambiente_reg_inversionista'  => 'Agua y Medio Ambiente',
                'industria_reg_inversionista'              => 'Industria',
                'mineria_reg_inversionista'                => 'Minería',
                'inmobiliario_y_turismo_reg_inversionista' => 'Inmobiliario y Turismo',
                'telecomunicaciones_reg_inversionista'     => 'Telecomunicaciones',
            );
            foreach ($flags as $k=>$label){
                $v = function_exists('get_field') ? get_field($k,$inv_id) : get_post_meta($inv_id,$k,true);
                if ($v===1||$v==='1'||$v===true||$v==='Si'||$v==='sí'||$v==='on') $raw[]=$label;
            }
        }

        // 3) Canonizar contra el catálogo (ES/EN unificados)
        $canon = pmx_sector_canonize($raw);
        pmx_log('[PREF_SECT] inv='.$inv_id.' raw=['.pmx_join($raw).'] canon=['.pmx_join($canon).']');

        // regresamos claves canónicas (ya normalizadas)
        return $canon;
    }
}


/* ====== CATÁLOGO DE INTERÉS (CPT: cat_tipo_inversion) ====== */
if (!function_exists('pmx_catalogo_interes_index')) {
    function pmx_catalogo_interes_index($force=false){
        static $cache=null; if($cache!==null && !$force) return $cache;

        $ids = get_posts(array(
            'post_type'      => 'cat_tipo_inversion',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => array('title'=>'ASC'),
            'fields'         => 'ids',
        ));

        $norm_by_id = array();
        $id_by_norm = array();
        $label_by_norm = array();

        foreach ((array)$ids as $id){
            $t = get_post_field('post_title', $id);
            $n = function_exists('pmx_norm') ? pmx_norm($t) : sanitize_title($t);

            // Ignorar NA/ND/Histórico/Archived
            if (in_array($n, array('na','n-a','nd','n-d','historico','archived-project','archivado','proyecto-archivado'), true)) continue;

            // Solo nos quedamos con estas dos
            if ($n === 'greenfield' || $n === 'brownfield') {
                $norm_by_id[$id] = $n;
                $id_by_norm[$n]  = $id;
                if (!isset($label_by_norm[$n])) $label_by_norm[$n] = $t;
            }
        }

        // Aliases “suaves” por si llegan variaciones
        $alias = array(
            'greenfield'          => 'greenfield',
            'proyecto-greenfield' => 'greenfield',
            'brownfield'          => 'brownfield',
            'proyecto-brownfield' => 'brownfield',
        );

        return $cache = array(
            'norm_by_id'    => $norm_by_id,
            'id_by_norm'    => $id_by_norm,
            'label_by_norm' => $label_by_norm,
            'alias'         => $alias,
        );
    }
}

/* Canoniza valores de “interés” a: ['greenfield'] | ['brownfield'] (o vacío) */
if (!function_exists('pmx_interes_canonize')) {
    function pmx_interes_canonize($vals){
        $idx = pmx_catalogo_interes_index();
        $out = array();

        foreach ((array)$vals as $v){
            if ($v === '' || $v === null) continue;

            // a) ID numérico
            if (is_numeric($v)){
                $id = (int)$v;
                if (isset($idx['norm_by_id'][$id])) { $out[$idx['norm_by_id'][$id]] = 1; continue; }
                // Compatibilidad con legacy: 2=Brownfield, 3=Greenfield
                if ($id === 2) { $out['brownfield'] = 1; continue; }
                if ($id === 3) { $out['greenfield'] = 1; continue; }
            }

            $s = (string)$v;

            // b) "id|label" o "id;label"
            if (preg_match('~^\s*(\d+)\s*[|;:]~', $s, $m)) {
                $id = (int)$m[1];
                if (isset($idx['norm_by_id'][$id])) { $out[$idx['norm_by_id'][$id]] = 1; continue; }
            }

            // c) Label suelto
            $n = function_exists('pmx_norm') ? pmx_norm($s) : sanitize_title($s);
            if (isset($idx['alias'][$n])) $n = $idx['alias'][$n];

            if ($n === 'greenfield' || $n === 'brownfield') {
                $out[$n] = 1; // solo estas dos pasan
            }
            // El resto (NA/ND/Histórico/Archived) se ignoran por diseño
        }

        $keys = array_keys($out);
        sort($keys);
        return $keys;
    }
}

/* Preferencia de interés del inversionista desde catálogo */
if (!function_exists('pmx_get_pref_interes_catalog_tokens')) {
    function pmx_get_pref_interes_catalog_tokens($inv_id){
        $raw   = pmx_get_acf_tokens($inv_id, 'interes_en_proyectos_reg_inversionista');
        $canon = pmx_interes_canonize($raw);
        if (function_exists('pmx_log')) pmx_log('[PREF_INTERES] inv='.$inv_id.' raw=['.pmx_join($raw).'] canon=['.pmx_join($canon).']');
        return $canon;
    }
}


/* ====== CATÁLOGO DE ETAPAS (CPT: catalogo_etapas) ====== */
if (!function_exists('pmx_catalogo_etapas_index')) {
    function pmx_catalogo_etapas_index($force=false){
        static $cache=null; if($cache!==null && !$force) return $cache;

        $ids = get_posts(array(
            'post_type'      => 'catalogo_etapas',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => array('title'=>'ASC'),
            'fields'         => 'ids',
        ));

        $norm_by_id = array();
        $id_by_norm = array();
        $label_by_norm = array();

        foreach ((array)$ids as $id){
            $t = get_post_field('post_title', $id);
            $n = function_exists('pmx_norm') ? pmx_norm($t) : sanitize_title($t);

            // ignora NA/ND y estados que no se usan para matching
            if (in_array($n, array('na','n-a','nd','n-d','cancelado','cancelled','pospuesto','postpuesto','postponed','bidding-nd'), true)) continue;

            $norm_by_id[$id] = $n;
            $id_by_norm[$n]  = $id;
            if (!isset($label_by_norm[$n])) $label_by_norm[$n] = $t;
        }

        /* Aliases EN -> ES para unificar */
        $alias = array(
            'preinvestment'   => 'preinversion',
            'pre-investment'  => 'preinversion',
            'bidding'         => 'licitacion',
            'execution'       => 'ejecucion',
            'operation'       => 'operacion',
        );

        return $cache = array(
            'norm_by_id'    => $norm_by_id,
            'id_by_norm'    => $id_by_norm,
            'label_by_norm' => $label_by_norm,
            'alias'         => $alias,
        );
    }
}

/* Canoniza valores de etapa a: ['preinversion'|'licitacion'|'ejecucion'|'operacion'] */
if (!function_exists('pmx_etapas_canonize')) {
    function pmx_etapas_canonize($vals){
        $idx = pmx_catalogo_etapas_index();
        $out = array();
        $allow = array('preinversion','licitacion','ejecucion','operacion');

        // compat: códigos de ACF históricos (4,5,6,7)
        $num_map = array(
            '2' => 'preinversion', //OK
            '7' => 'licitacion', // NO OK
            '6' => 'ejecucion',
            '5' => 'operacion', // OK
        );

        foreach ((array)$vals as $v){
            if ($v === '' || $v === null) continue;

            // a) ID numérico (post del catálogo)
            if (is_numeric($v)) {
                $id = (int)$v;
                if (isset($idx['norm_by_id'][$id])) {
                    $n = $idx['norm_by_id'][$id];
                    if (isset($idx['alias'][$n])) $n = $idx['alias'][$n];
                    if (in_array($n, $allow, true)) $out[$n] = 1;
                    continue;
                }
                // b) códigos numéricos legacy (4/5/6/7)
                $k = (string)$v;
                if (isset($num_map[$k])) { $out[$num_map[$k]] = 1; continue; }
            }

            $s = (string)$v;

            // c) "id|label" o "id;label"
            if (preg_match('~^\s*(\d+)\s*[|;:]~', $s, $m)) {
                $id = (int)$m[1];
                if (isset($idx['norm_by_id'][$id])) {
                    $n = $idx['norm_by_id'][$id];
                    if (isset($idx['alias'][$n])) $n = $idx['alias'][$n];
                    if (in_array($n, $allow, true)) $out[$n] = 1;
                    continue;
                }
            }

            // d) como label suelto (ES/EN)
            $n = function_exists('pmx_norm') ? pmx_norm($s) : sanitize_title($s);
            if (isset($idx['alias'][$n])) $n = $idx['alias'][$n];
            if (in_array($n, $allow, true)) $out[$n] = 1;
        }

        $keys = array_keys($out);
        sort($keys);
        return $keys;
    }
}

/* Preferencia de ETAPA del inversionista desde catálogo */
if (!function_exists('pmx_get_pref_etapa_catalog_tokens')) {
    function pmx_get_pref_etapa_catalog_tokens($inv_id){
        $raw   = pmx_get_acf_tokens($inv_id, 'etapa_del_ciclo_de_inversion_reg_inversionista');
        $canon = pmx_etapas_canonize($raw);
        if (function_exists('pmx_log')) pmx_log('[PREF_ETAPA] inv='.$inv_id.' raw=['.pmx_join($raw).'] canon=['.pmx_join($canon).']');
        return $canon; // devuelve slugs: preinversion|licitacion|ejecucion|operacion
    }
}

/* Pretty ES para mostrar en logs */
if (!function_exists('pmx_pretty_etapas_es')) {
    function pmx_pretty_etapas_es($canon){
        $map = array(
            'preinversion' => 'Preinversión',
            'licitacion'   => 'Licitación',
            'ejecucion'    => 'Ejecución',
            'operacion'    => 'Operación',
        );
        $out = array();
        foreach ((array)$canon as $k) { $out[] = isset($map[$k]) ? $map[$k] : $k; }
        return $out;
    }
}

/* ====== CATÁLOGO DE TIPOS DE PROYECTO (CPT: cat_tipo_proyecto) ====== */
if (!function_exists('pmx_catalogo_tipos_proyecto_index')) {
    function pmx_catalogo_tipos_proyecto_index($force=false){
        static $cache=null; if($cache!==null && !$force) return $cache;

        $ids = get_posts(array(
            'post_type'      => 'cat_tipo_proyecto',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => array('title'=>'ASC'),
            'fields'         => 'ids',
        ));

        $norm_by_id = array();
        $id_by_norm = array();
        $label_by_norm = array();

        foreach ((array)$ids as $id){
            $t = get_post_field('post_title', $id);
            $n = function_exists('pmx_norm') ? pmx_norm($t) : sanitize_title($t);

            // fuera NA/ND
            if (in_array($n, array('na','n-a','nd','n-d','-na-','-nd-'), true)) continue;

            $norm_by_id[$id] = $n;
            $id_by_norm[$n]  = $id;
            if (!isset($label_by_norm[$n])) $label_by_norm[$n] = $t;
        }

        // Canónicas permitidas (ES)
        $allowed = array('publico','privado','publico-privado');

        // Aliases y variaciones comunes (EN/ES)
        $alias = array(
            'público'                => 'publico',
            'public'                 => 'publico',
            'privado'                => 'privado',
            'private'                => 'privado',
            'publico-/-privado'      => 'publico-privado',
            'public-private'         => 'publico-privado',
            'public-private-partnership' => 'publico-privado',
            'publico-privado'        => 'publico-privado',
            'publico-privada'        => 'publico-privado',
            'publico--privado'       => 'publico-privado',
            'public-/-private'       => 'publico-privado',
            'public-private-'        => 'publico-privado',
        );

        // Si el catálogo solo tiene una variante, propaga labels a los alias
        foreach ($allowed as $canon){
            if (isset($id_by_norm[$canon])) {
                foreach ($alias as $from=>$to) {
                    if ($to===$canon && !isset($id_by_norm[$from])) {
                        $id_by_norm[$from]    = $id_by_norm[$canon];
                        $label_by_norm[$from] = $label_by_norm[$canon];
                    }
                }
            }
        }

        return $cache = array(
            'norm_by_id'    => $norm_by_id,
            'id_by_norm'    => $id_by_norm,
            'label_by_norm' => $label_by_norm,
            'alias'         => $alias,
            'allowed'       => $allowed,
        );
    }
}

/* ====== Canonizador: entradas variadas -> ['publico'|'privado'|'publico-privado'] ====== */
if (!function_exists('pmx_tipo_proyecto_canonize')) {
    function pmx_tipo_proyecto_canonize($vals){
        $idx = pmx_catalogo_tipos_proyecto_index();
        $out = array();

        foreach ((array)$vals as $v){
            if ($v === '' || $v === null) continue;

            // a) ID numérico del CPT
            if (is_numeric($v)){
                $id = (int)$v;
                if (isset($idx['norm_by_id'][$id])) {
                    $n = $idx['norm_by_id'][$id];
                    if (isset($idx['alias'][$n])) $n = $idx['alias'][$n];
                    if (in_array($n, $idx['allowed'], true)) $out[$n] = 1;
                }
                continue;
            }

            $s = (string)$v;

            // b) “id|label” / “id;label”
            if (preg_match('~^\s*(\d+)\s*[|;:]~', $s, $m)) {
                $id = (int)$m[1];
                if (isset($idx['norm_by_id'][$id])) {
                    $n = $idx['norm_by_id'][$id];
                    if (isset($idx['alias'][$n])) $n = $idx['alias'][$n];
                    if (in_array($n, $idx['allowed'], true)) $out[$n] = 1;
                }
                continue;
            }

            // c) Label suelto (ES/EN) -> normaliza + alias
            $n = function_exists('pmx_norm') ? pmx_norm($s) : sanitize_title($s);
            if ($n === 'public-private') $n = 'publico-privado';
            if (isset($idx['alias'][$n])) $n = $idx['alias'][$n];

            if (in_array($n, $idx['allowed'], true)) $out[$n] = 1;
        }

        $keys = array_keys($out);
        sort($keys);
        return $keys;
    }
}

/* Pretty ES para mostrar en logs */
if (!function_exists('pmx_pretty_tipo_proyecto_es')) {
    function pmx_pretty_tipo_proyecto_es($canon){
        $map = array(
            'publico'         => 'Público',
            'privado'         => 'Privado',
            'publico-privado' => 'Público / Privado',
        );
        $out = array();
        foreach ((array)$canon as $k) $out[] = isset($map[$k]) ? $map[$k] : $k;
        return $out;
    }
}

/* Preferencias: TIPOS usando el catálogo (no códigos) */
if (!function_exists('pmx_get_pref_tipo_catalog_tokens')) {
    function pmx_get_pref_tipo_catalog_tokens($inv_id){
        $raw = pmx_get_acf_tokens($inv_id, 'tipos_de_proyecto_reg_inversionista');
        return pmx_tipo_proyecto_canonize($raw); // devuelve slugs canon: publico|privado|publico-privado
    }
}

/* Pretty para etapas (ES) desde los slugs canónicos */
if (!function_exists('pmx_pretty_etapas_es')) {
    function pmx_pretty_etapas_es($canon){
        $map = array(
            'preinversion' => 'Preinversión',
            'licitacion'   => 'Licitación',
            'ejecucion'    => 'Ejecución',
            'operacion'    => 'Operación',
        );
        $out = array();
        foreach ((array)$canon as $k) { $out[] = isset($map[$k]) ? $map[$k] : $k; }
        return $out;
    }
}

// ligar Subsectores

// Mapea códigos (1,2,12,...) a IDs reales del CPT 'catalogo_subsectores' buscando por título.
add_filter('pmx_map_pref_subsectors_to_project_ids', function($codes, $inv_id){
  // Tabla código -> título exacto del subsector (según tu CSV)
  $code2title = array(
    1  => 'Generación',
    2  => 'Transmisión y Subestaciones',
    12 => 'Comercialización',
    3  => 'Exploración/Producción',
    7  => 'Transporte / Almacenamiento / Distribución',
    8  => 'Refinación / Tratamiento',
    4  => 'Aeropuertos',
    5  => 'Puertos',
    6  => 'Ferrocarriles',
    9  => 'Transporte Urbano',
    10 => 'Mixto o Multimodal',
    11 => 'Carreteras',
  );

  $ids = array();
  foreach ((array)$codes as $c) {
    $c = (int)$c;
    if (!isset($code2title[$c])) continue;
    $title = $code2title[$c];

    // Busca por título dentro del CPT 'catalogo_subsectores'
    $p = get_page_by_title($title, OBJECT, 'catalogo_subsectores');
    if ($p && !is_wp_error($p)) {
      $ids[$p->ID] = true;
    } else {
      // Fallback: búsqueda "like" por si hay pequeñas variaciones
      $q = new WP_Query(array(
        'post_type'      => 'catalogo_subsectores',
        'posts_per_page' => 1,
        's'              => $title,
      ));
      if ($q->have_posts()) {
        $ids[$q->posts[0]->ID] = true;
      }
      wp_reset_postdata();
    }
  }

  $out = array_keys($ids);
  sort($out, SORT_NUMERIC);
  if (function_exists('pmx_log')) pmx_log('[PREF_SUBSEC_MAP] codes=['.implode(',',$codes).'] -> ids=['.implode(',',$out).']');
  return $out;
}, 10, 2);


/* ============================================================
   FILTRO por PREFERENCIAS (todo con labels)
   ============================================================ */
/* ====================== FILTRO PRINCIPAL ====================== */
/* ====================== FILTRO PRINCIPAL ====================== */
if (!function_exists('pmx_filtra_proyectos_por_preferencias')) {
    function pmx_filtra_proyectos_por_preferencias($proyectos, $inv_id){
        pmx_log('--- Preferencias INV '.$inv_id.' ('.get_the_title($inv_id).') ---');

        // Preferencias (tokens “best effort” ya existentes en tu código)
        $pref_sect = pmx_get_pref_sector_tokens($inv_id);
        $pref_tip  = pmx_get_pref_tipo_tokens($inv_id);
        $pref_eta  = pmx_get_pref_etapa_catalog_tokens($inv_id);
        $pref_int  = pmx_get_pref_interes_catalog_tokens($inv_id);
		
		    // --- Normaliza "Todos" (1/texto) para NO filtrar ese eje ---
    $pref_tip_all = pmx_pref_means_all($pref_tip);
    $pref_eta_all = pmx_pref_means_all($pref_eta);
    $pref_int_all = pmx_pref_means_all($pref_int);

    if ($pref_tip_all) $pref_tip = array();
    if ($pref_eta_all) $pref_eta = array();
    if ($pref_int_all) $pref_int = array();

    // Logs legibles
    pmx_log('  Sectores: ['.pmx_join($pref_sect).']');
    pmx_log('  Tipos:    '.($pref_tip_all ? '(TODOS)'  : '['.pmx_join($pref_tip).']'));
    pmx_log('  Etapas:   '.($pref_eta_all ? '(TODAS)'  : '['.pmx_join($pref_eta).']'));
    pmx_log('  Interés:  '.($pref_int_all ? '(TODOS)'  : '['.pmx_join($pref_int).']'));
	


        // Subsectores
        $pref_subsec_ids = pmx_get_pref_subsector_ids($inv_id);
        //pmx_log('  Subsectors IDs: ['.implode(',', $pref_subsec_ids).']');
        //pmx_log('  Subsectores: (OMITIDO EN FILTRO) IDs=['.implode(',', $pref_subsec_ids).']');


        // --- DEBUG ACF (opcional, lo dejas como lo tenías)
        pmx_dbg_taxmap('tipos_de_proyecto_reg_inversionista');
        pmx_dbg_taxmap('etapa_del_ciclo_de_inversion_reg_inversionista');
        pmx_dbg_taxmap('interes_en_proyectos_reg_inversionista');

        pmx_dbg_field($inv_id, 'tipos_de_proyecto_reg_inversionista');
        pmx_dbg_field($inv_id, 'etapa_del_ciclo_de_inversion_reg_inversionista');
        pmx_dbg_field($inv_id, 'interes_en_proyectos_reg_inversionista');

        // ====== “TODOS/TODAS/ALL” => eje vacío (no filtra) ======
        $tip_all = pmx_pref_axis_is_all($inv_id, 'tipos_de_proyecto_reg_inversionista');
        $eta_all = pmx_pref_axis_is_all($inv_id, 'etapa_del_ciclo_de_inversion_reg_inversionista');
        $int_all = pmx_pref_axis_is_all($inv_id, 'interes_en_proyectos_reg_inversionista');

        if ($tip_all) { pmx_log('[PREF][TIPO] "Todos" detectado -> no filtra tipos');   $pref_tip = array(); }
        if ($eta_all) { pmx_log('[PREF][ETAPA] "Todas" detectado -> no filtra etapas');  $pref_eta = array(); }
        if ($int_all) { pmx_log('[PREF][INTERES] "Todos" detectado -> no filtra interés'); $pref_int = array(); }

        // Monto pref
        $min_raw = get_field('monto_minimo_reg_inversionista', $inv_id);
        $max_raw = get_field('monto_maximo_reg_inversionista', $inv_id);
        $min = is_numeric($min_raw) ? floatval($min_raw) : null;
        $max = is_numeric($max_raw) ? floatval($max_raw) : null;

        /*pmx_log('  Sectores: ['.pmx_join($pref_sect).']');
        pmx_log('  Tipos:    ['.pmx_join($pref_tip).']');
        pmx_log('  Etapas:   ['.pmx_join($pref_eta).']');
        pmx_log('  Interés:  ['.pmx_join($pref_int).']');
        pmx_log('  Monto:    min='.($min===null?'(n/a)':$min).' max='.($max===null?'(n/a)':$max));*/

        // Normalizados
        $n_pref_sect = pmx_norm_arr($pref_sect);
        $n_pref_tip  = pmx_norm_arr($pref_tip);
        $n_pref_eta  = pmx_norm_arr($pref_eta);
        $n_pref_int  = pmx_norm_arr($pref_int);

        $out = array(); $pas = 0; $sal = 0;

        foreach ($proyectos as $p){
            $pid = is_object($p) ? $p->ID : intval($p['ID']);
            $tit = get_the_title($pid);

            // PROYECTO: tokens/canónicos (como ya tenías)
            $sec = pmx_sector_canonize( pmx_get_acf_tokens($pid,'sector_proyecto') );
            $tip = pmx_get_acf_tokens($pid,'tipo_de_proyecto');
            $eta = pmx_etapas_canonize( pmx_get_acf_tokens($pid,'etapa_proyecto') );
            $inv = pmx_get_acf_tokens($pid,'tipo_de_inversion');
            $mon = pmx_get_monto_usd($pid); //arreglo

            // Subsector del proyecto (meta) y match con preferencias
            // temporal $proj_subsec_id = intval(get_post_meta($pid, 'subsector_proyecto', true));
            // temporal $okSub = empty($pref_subsec_ids) || in_array($proj_subsec_id, $pref_subsec_ids, true);

            /* === PARCHE TEMPORAL: OMITIR SUBSECTOR EN EL MATCH === */
            //hoy $proj_subsec_id = intval(get_post_meta($pid, 'subsector_proyecto', true));
            //hoy $okSub = true; // <— subsector NO filtra por ahora
            //hoy pmx_log('    @@ SUBSEC(omitido): proj_id='.$proj_subsec_id.' => okSub=1');
            /* === FIN PARCHE === */
			
			/* === SUBSECTOR: match real === */
$proj_subsec_ids     = pmx_get_project_subsector_ids($pid);               // IDs del proyecto
$pref_subsec_codes   = pmx_get_pref_subsector_ids($inv_id);               // CÓDIGOS elegidos por el invers.
$pref_subsec_ids     = apply_filters('pmx_map_pref_subsectors_to_project_ids', $pref_subsec_codes, $inv_id); // IDs mapeados

// Reglas: si el inversionista NO seleccionó subsectores -> no filtra por este eje.
// Si sí seleccionó, el proyecto debe tener AL MENOS uno de esos subsectores.
$okSub = empty($pref_subsec_ids) || count(array_intersect($proj_subsec_ids, $pref_subsec_ids)) > 0;

pmx_log(
  '    SUBSEC: proj=['.implode(',', $proj_subsec_ids).']'
  .' pref_codes=['.implode(',', $pref_subsec_codes).'] pref_ids=['.implode(',', $pref_subsec_ids).']'
  .' ok='.($okSub?1:0)
);
/* === FIN SUBSECTOR === */




            $tip_pretty    = pmx_pretty_tipo_proyecto_es($tip);
            $etapas_pretty = pmx_pretty_etapas_es($eta);
			
			$subsec_ids_str    = implode(',', array_map('intval', (array)$proj_subsec_ids));
$subsec_titles_arr = array_map(function($id){ return get_the_title($id); }, (array)$proj_subsec_ids);
$subsec_titles_str = pmx_join($subsec_titles_arr);
			

            pmx_log(
    'PID '.$pid
    .' | "'.$tit.'"'
    .' | sector=['.pmx_join($sec).']'
    .' | tipo=['.pmx_join($tip_pretty).'] canon=['.pmx_join($tip).']'
    .' | etapa=['.pmx_join($etapas_pretty).'] canon=['.pmx_join($eta).']'
    .' | interes=['.pmx_join($inv).']'
    .' | monto='.( (is_null($mon) || trim((string)$mon)==='' || $mon==='0' || (is_numeric($mon) && (float)$mon==0.0)) ? '0' : number_format((float)$mon, 0, '.', ','))
    .' | subsector_ids=['.$subsec_ids_str.'] ('.$subsec_titles_str.')'
    .' | okSub='.($okSub ? '1' : '0')
);

            $n_sec = pmx_norm_arr($sec);
            $n_tip = pmx_norm_arr($tip);
            $n_eta = pmx_norm_arr($eta);
            $n_inv = pmx_norm_arr($inv);
			$n_inv = pmx_norm_arr($mon);

            // Reglas: eje vacío => no filtra; OR dentro del eje; AND entre ejes + monto + subsector
            $okSec = empty($n_pref_sect) || count(array_intersect($n_pref_sect, $n_sec)) > 0;
            $okTip = empty($n_pref_tip)  || count(array_intersect($n_pref_tip,  $n_tip)) > 0;
            $okEta = empty($n_pref_eta)  || count(array_intersect($n_pref_eta,  $n_eta)) > 0;
            $okInt = empty($n_pref_int)  || count(array_intersect($n_pref_int,  $n_inv)) > 0;

            $okMon = true;
            if ($mon !== null && $min !== null && $mon < $min) $okMon = false;
            if ($mon !== null && $max !== null && $mon > $max) $okMon = false;
			

// === Política de coincidencia Sector/Subsector =====================
// ¿El usuario seleccionó algo en cada eje?
$usuario_sel_sector = !empty($n_pref_sect);
$usuario_sel_subsec = !empty($pref_subsec_codes);

// Política configurable: 'strict' (AND), 'or', 'subsector', 'sector'
$policy = apply_filters('pmx_conflict_policy_sector_subsector', 'or');

switch ($policy) {
  case 'subsector':
    // Si eligió subsector, manda subsector; si no, manda sector.
    $okSectorSub = $usuario_sel_subsec ? $okSub : $okSec;
    break;

  case 'sector':
    // Si eligió sector, manda sector; si no, manda subsector.
    $okSectorSub = $usuario_sel_sector ? $okSec : $okSub;
    break;

  case 'or':
    // Pasa si coincide sector O subsector (cuando seleccionó alguno de los dos)
    $okSectorSub = ($usuario_sel_sector || $usuario_sel_subsec)
      ? ($okSec || $okSub)
      : true; // no seleccionó nada => no filtra
    break;

  case 'strict':
  default:
    // AND estricto: si seleccionó ambos, deben coincidir ambos;
    // si solo seleccionó uno, manda ese.
    if ($usuario_sel_sector && $usuario_sel_subsec) {
      $okSectorSub = ($okSec && $okSub);
    } elseif ($usuario_sel_sector) {
      $okSectorSub = $okSec;
    } elseif ($usuario_sel_subsec) {
      $okSectorSub = $okSub;
    } else {
      $okSectorSub = true; // no seleccionó nada => no filtra
    }
    break;
}

// Log opcional
pmx_log('[SECTOR_SUBSEC_POLICY] policy='.$policy.' | okSec='.(int)$okSec.' okSub='.(int)$okSub.' => okSectorSub='.(int)$okSectorSub);

	//fin de politicas de filtrado		

            // ⬇️ AÑADIMOS Subsector al AND principal
            if ($okSectorSub && $okTip && $okEta && $okInt && $okMon){
                $out[] = $p; $pas++;
            } else {
                $why = array();
                if (!$okSec) $why[]='sector';
                if (!$okTip) $why[]='tipo';
                if (!$okEta) $why[]='etapa';
                if (!$okInt) $why[]='interes';
                if (!$okMon) $why[]='monto';
                if (!$okSub) $why[]='subsector';
                pmx_log('  ✗ NO MATCH PID '.$pid.' ('.implode(',', $why).')');
                $sal++;
            }
        }

        pmx_log('Resumen filtro inv '.$inv_id.': pasaron='.$pas.' / saltados='.$sal);
        return $out;
    }
}

// politicas

// AND estricto (lo actual por defecto)
//add_filter('pmx_conflict_policy_sector_subsector', function(){ return 'strict'; });

// OR: basta que coincida sector O subsector
// add_filter('pmx_conflict_policy_sector_subsector', function(){ return 'or'; });

// Prioriza subsector sobre sector
// add_filter('pmx_conflict_policy_sector_subsector', function(){ return 'subsector'; });

// Prioriza sector sobre subsector
// add_filter('pmx_conflict_policy_sector_subsector', function(){ return 'sector'; });



/* ===== MAIL HELPERS MÍNIMOS ===== */
if (!function_exists('pmx_domain_from_home')) {
  function pmx_domain_from_home() {
    $h = parse_url(home_url(), PHP_URL_HOST);
    if (!$h && !empty($_SERVER['HTTP_HOST'])) $h = $_SERVER['HTTP_HOST'];
    $h = preg_replace('~^www\.~i', '', (string)$h);
    return $h ?: 'localhost:7080';
  }
}
if (!function_exists('pmx_html_to_text')) {
  function pmx_html_to_text($html) {
    $html = preg_replace('~<\s*br\s*/?>~i', "\n", $html);
    $html = preg_replace('~</\s*p\s*>~i', "\n\n", $html);
    return trim( wp_strip_all_tags((string)$html) );
  }
}
if (!function_exists('pmx_join')) {
  function pmx_join($arr){ if(!is_array($arr)){$arr=$arr!==''?array($arr):array();} $o=array(); foreach($arr as $v){ $o[]=is_scalar($v)?$v:json_encode($v); } return implode(' | ',$o); }
}
if (!function_exists('pmx_log')) {
  function pmx_log($msg){ if(is_array($msg)||is_object($msg)){$msg=print_r($msg,true);} error_log('[PMX] '.$msg); }
}

/* ============================================================
 * DETECCIÓN DE IDIOMA DEL INVERSIONISTA (es|en)
 * - Intenta Polylang, WPML y varias metas comunes.
 * - Fallback: 'es'
 * ============================================================ */
/* LOCALE -> 'es'|'en' */
if (!function_exists('pmx_locale_to_lang')) {
  function pmx_locale_to_lang($locale) {
    $locale = strtolower((string)$locale);
    if ($locale === 'en' || strpos($locale, 'en_') === 0) return 'en';
    return 'es';
  }
}

/* DETECCIÓN DE IDIOMA DEL INVERSIONISTA ('es'|'en') CON LOGS DETALLADOS */
if (!function_exists('pmx_get_inversionista_lang')) {
  function pmx_get_inversionista_lang($inv_id){
    // 0) ACF preferencia directa
    $acf = function_exists('get_field') ? get_field('idioma_de_preferencia_reg_inversionista', $inv_id) : '';
    $acf_meta = get_post_meta($inv_id, 'idioma_de_preferencia_reg_inversionista', true);

    $acf_all = trim((string)($acf !== '' ? $acf : $acf_meta));
    if ($acf_all !== '') {
      $n = pmx_locale_to_lang($acf_all);
      pmx_log('LANG_DET inv='.$inv_id.' step=acf value="'.$acf_all.'" -> '.$n);
      if ($n === 'es' || $n === 'en') return $n;
    } else {
      pmx_log('LANG_DET inv='.$inv_id.' step=acf value=(vacío)');
    }

    // 1) Polylang
    if (function_exists('pll_get_post_language')) {
      $pll = pll_get_post_language($inv_id);
      pmx_log('LANG_DET inv='.$inv_id.' step=pll value="'.($pll?:'').'"');
      if ($pll && in_array($pll, array('es','en'), true)) return $pll;
    }

    // 2) WPML
    if (function_exists('apply_filters')) {
      $wpml = apply_filters('wpml_element_language_code', null, array(
        'element_id'   => $inv_id,
        'element_type' => 'post_reg_inversionistas'
      ));
      pmx_log('LANG_DET inv='.$inv_id.' step=wpml value="'.($wpml?:'').'"');
      if ($wpml && in_array($wpml, array('es','en'), true)) return $wpml;
    }

    // 3) Metas alternas
    $keys = array('idioma','idioma_registro','language','language_registro','registro_idioma','lang','user_lang');
    foreach ($keys as $k){
      $v = get_post_meta($inv_id, $k, true);
      if (!$v && function_exists('get_field')) $v = get_field($k, $inv_id);
      $v = trim((string)$v);
      if ($v==='') continue;
      $n = pmx_locale_to_lang($v);
      pmx_log('LANG_DET inv='.$inv_id.' step=meta key='.$k.' value="'.$v.'" -> '.$n);
      if ($n === 'es' || $n === 'en') return $n;
    }

    // 4) Fallback por locale del sitio
    $fallback = pmx_locale_to_lang(get_locale());
    pmx_log('LANG_DET inv='.$inv_id.' step=fallback site_locale="'.get_locale().'" -> '.$fallback);
    return $fallback;
  }
}


/* ============================================================
 * STRINGS POR IDIOMA DE CORREO PROYECTOS NUEVOS
 * ============================================================ */



// campos acf

/* ================== LOG BÁSICO ================== */
if (!function_exists('pmx_mail_dbg')) {
  function pmx_mail_dbg($msg, $ctx=null){
    if (is_array($ctx) || is_object($ctx)) { $ctx = print_r($ctx, true); }
    error_log('[PMXMAIL] '.$msg.($ctx!==null ? ' | '.$ctx : ''));
  }
}

/* Utilidad: recorta para log */
if (!function_exists('pmx_mail_snip')) {
  function pmx_mail_snip($s, $n=120){
    $s = (string)$s; $s = trim($s);
    if ($s === '') return '(vacío)';
    return (strlen($s) > $n) ? substr($s, 0, $n).'…' : $s;
  }
}

/* (opcional) fija el ID explícito (usa tu ID real) */
//update_option('pmx_mail_conf_page_id', 147141);

/* ============ RESOLVER PÁGINA DE CONFIGURACIÓN (soporta CPT) ============ */
if (!function_exists('pmx_mail_conf_page_id')) {
  function pmx_mail_conf_page_id(){
    static $cached = null; if ($cached !== null) return $cached;

    // Tipos permitidos para la página de configuración
    $allowed_types = apply_filters('pmx_mail_conf_allowed_types', array('page','conf_proyectos_nuevo'));

    // 1) Opción guardada
    $opt = intval(get_option('pmx_mail_conf_page_id'));
    if ($opt > 0) {
      $pt  = get_post_type($opt);
      $ttl = get_the_title($opt);
      $url = get_permalink($opt);
      pmx_mail_dbg('Conf page via option', "id=$opt | type=$pt | title=".($ttl?:'(sin título)')." | url=".($url?:'(sin url)'));
      if (in_array($pt, $allowed_types, true) && get_post_status($opt)) {
        pmx_mail_dbg('Usando ID de configuración', $opt);
        return $cached = $opt;
      } else {
        pmx_mail_dbg('Opción apunta a un post NO permitido; se intentará por slug');
      }
    }

    // 2) Búsqueda por slug (en page y en conf_proyectos_nuevo)
    $paths = array(
      'conf_proyectos_nuevo/configuracion-de-correos-proyectos-nuevos',
      'configuracion-de-correos-proyectos-nuevos'
    );

    foreach ($paths as $p){
      if (function_exists('get_page_by_path')) {
        $pg = get_page_by_path($p, OBJECT, $allowed_types);
        if ($pg && isset($pg->ID)) {
          pmx_mail_dbg('Conf page via slug', $p.' -> '.$pg->ID);
          pmx_mail_dbg('Conf page resolved', "ID=".$pg->ID." | title=".get_the_title($pg->ID)." | url=".get_permalink($pg->ID));
          return $cached = intval($pg->ID);
        }
      }
      $name = basename($p);
      $q = get_posts(array(
        'name'           => $name,
        'post_type'      => $allowed_types,
        'posts_per_page' => 1,
        'post_status'    => array('publish','draft','pending','private')
      ));
      if (!empty($q) && isset($q[0]->ID)) {
        pmx_mail_dbg('Conf page via get_posts(name)', $name.' -> '.$q[0]->ID);
        pmx_mail_dbg('Conf page resolved', "ID=".$q[0]->ID." | title=".get_the_title($q[0]->ID)." | url=".get_permalink($q[0]->ID));
        return $cached = intval($q[0]->ID);
      }
    }

    pmx_mail_dbg('Página de configuración NO encontrada');
    return $cached = 0;
  }
}

/* ============ LECTURA DE REPEATER (PRIMERA FILA) ============ */
if (!function_exists('pmx_acf_rep_row')) {
  function pmx_acf_rep_row($field_names, $post_id){
    if (!function_exists('get_field')) { pmx_mail_dbg('ACF no disponible: get_field() ausente'); return array(); }
    foreach ((array)$field_names as $f){
      $raw = get_field($f, $post_id);
      if (is_array($raw) && !empty($raw)) {
        $row = (isset($raw[0]) && is_array($raw[0])) ? $raw[0] : $raw;
        pmx_mail_dbg('Repeater OK', $f.' | keys='.implode(',', array_keys($row)));
        return $row;
      } else {
        pmx_mail_dbg('Repeater vacío', $f);
      }
    }
    pmx_mail_dbg('Ningún repeater con datos', implode('|',(array)$field_names).' | post_id='.$post_id);
    return array();
  }
}

/* ============ HELPERS DE IDIOMA PARA SUBCAMPOS ============ */
if (!function_exists('pmx_norm_key')) {
  function pmx_norm_key($s){
    $s = strtolower((string)$s);
    $s = strtr($s, array('á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n'));
    return preg_replace('~[^a-z0-9_]+~','',$s);
  }
}

if (!function_exists('pmx_lang_from_row')) {
  function pmx_lang_from_row($row){
    $out = array('es'=>'','en'=>'');
    if (!is_array($row) || empty($row)) { pmx_mail_dbg('Fila vacía'); return $out; }

    foreach ($row as $k=>$v){
      if (is_array($v) || is_object($v)) continue;
      $nk = pmx_norm_key($k);
      if (strpos($nk,'espanol') !== false || preg_match('~(^|_)es($|_)~',$nk))      { $out['es'] = (string)$v; }
      elseif (strpos($nk,'ingles') !== false || preg_match('~(^|_)en($|_)~',$nk)) { $out['en'] = (string)$v; }
    }

    if ($out['es']==='' || $out['en']===''){
      $vals = array();
      foreach ($row as $k=>$v){ if (!is_array($v) && !is_object($v)) $vals[] = (string)$v; }
      if ($out['es']==='' && isset($vals[0])) $out['es'] = $vals[0];
      if ($out['en']==='' && isset($vals[1])) $out['en'] = $vals[1];
    }

    pmx_mail_dbg('Mapeado ES/EN', 'ES='.pmx_mail_snip($out['es']).' | EN='.pmx_mail_snip($out['en']));
    return $out;
  }
}

/* Primer valor escalar que aparezca en una fila (para from_email) */
if (!function_exists('pmx_first_scalar')) {
  function pmx_first_scalar($row){
    if (!is_array($row)) return '';
    foreach ($row as $k=>$v){
      if (!is_array($v) && !is_object($v) && $v!=='') return (string)$v;
    }
    return '';
  }
}


/* ============ CARGA DE CONFIG DESDE ACF (SIN FALLBACKS) ============ */
if (!function_exists('pmx_mail_conf_load')) {
  function pmx_mail_conf_load(){
    $pid = pmx_mail_conf_page_id();
    $cfg = array(
      'subject_es' => '', 'subject_en' => '',
      'body_es'    => '', 'body_en'    => '',
      'count_es'   => '', 'count_en'   => '',
      'pers_es'    => '', 'pers_en'    => '',
	  // NUEVOS:
      'from_email'   => '',
      'from_name_es' => '', 'from_name_en' => '',
    );
    pmx_mail_dbg('Cargando config', 'pageID='.$pid);
    if ($pid <= 0) return $cfg;

    // 1) Asunto
    $p = pmx_lang_from_row( pmx_acf_rep_row(array('asunto_proyectos_nuevos'), $pid) );
    $cfg['subject_es'] = $p['es']; $cfg['subject_en'] = $p['en'];

    // 2) Cuerpo (campo con typo "..._uevos")
    $p = pmx_lang_from_row( pmx_acf_rep_row(array('cuerpo_de_correo_proyectos_uevos','cuerpo_de_correo_proyectos_nuevos'), $pid) );
    $cfg['body_es'] = $p['es']; $cfg['body_en'] = $p['en'];

    // 3) Conteo
    $p = pmx_lang_from_row( pmx_acf_rep_row(array('conteo_proyectos_nuevos'), $pid) );
    $cfg['count_es'] = $p['es']; $cfg['count_en'] = $p['en'];

    // 4) Personalizado
    $p = pmx_lang_from_row( pmx_acf_rep_row(array('notificacion_personalizada_proyectos_nuevos'), $pid) );
    $cfg['pers_es'] = $p['es']; $cfg['pers_en'] = $p['en'];
	
	// 5) Dirección de correo para notificaciones
    //    (primero intentamos el repeater histórico; si viene vacío, usamos el NUEVO campo email) este ya no existe 
    $r = pmx_acf_rep_row(array('direccion_de_correo_para_notificaciones'), $pid);
    $cfg['from_email'] = pmx_first_scalar($r);
    pmx_mail_dbg('from_email(repeater)', pmx_mail_snip($cfg['from_email']));

    if ($cfg['from_email'] === '') {
      // NUEVO: ACF tipo "email" -> configuracion_de_correo_para_notificaciones
      if (function_exists('get_field')) {
        $em_val = get_field('configuracion_de_correo_para_notificaciones', $pid);
        // Puede venir como string o como array('email'=>...)
        if (is_array($em_val)) {
          $em_val = isset($em_val['email']) ? $em_val['email'] : (isset($em_val['value']) ? $em_val['value'] : '');
        }
        $em_val = (string)$em_val;
        pmx_mail_dbg('from_email(acf email)', pmx_mail_snip($em_val));
        if ($em_val !== '') $cfg['from_email'] = $em_val;
      }
      // Meta crudo por si ACF devolviera vacío pero el meta existe
      if ($cfg['from_email'] === '') {
        $em_meta = get_post_meta($pid, 'configuracion_de_correo_para_notificaciones', true);
        pmx_mail_dbg('from_email(meta email)', $em_meta === '' ? '(vacío)' : pmx_mail_snip($em_meta));
        if ($em_meta !== '') $cfg['from_email'] = (string)$em_meta;
      }
    }

    // 6) Display Name ES/EN (acepta typo en field name "dislpay...")
    $p = pmx_lang_from_row( pmx_acf_rep_row(array('display_de_correo_de_notificaciones','dislpay_de_correo_de_notificaciones'), $pid) );
    $cfg['from_name_es'] = $p['es']; $cfg['from_name_en'] = $p['en'];

    pmx_mail_dbg('Config final',
      'subject_es='.pmx_mail_snip($cfg['subject_es']).' | subject_en='.pmx_mail_snip($cfg['subject_en'])
      .' || body_es='.pmx_mail_snip($cfg['body_es']).' | body_en='.pmx_mail_snip($cfg['body_en'])
      .' || count_es='.pmx_mail_snip($cfg['count_es']).' | count_en='.pmx_mail_snip($cfg['count_en'])
      .' || pers_es='.pmx_mail_snip($cfg['pers_es']).' | pers_en='.pmx_mail_snip($cfg['pers_en'])
	  .' || from_email='.pmx_mail_snip($cfg['from_email'])
      .' || from_name_es='.pmx_mail_snip($cfg['from_name_es']).' | from_name_en='.pmx_mail_snip($cfg['from_name_en'])
    );
    return $cfg;
  }
}

/* ============ FROM único (email para ES/EN) + logs ============ */
if (!function_exists('pmx_mail_from_conf')) {
  function pmx_mail_from_conf($lang = null){
    $cfg   = pmx_mail_conf_load();

    // EMAIL: único para ambos idiomas
    $email_raw = isset($cfg['from_email']) ? $cfg['from_email'] : '';
    $email     = sanitize_email($email_raw);

    // NAME: depende del idioma (si no hay idioma, usamos ES)
    $name_raw = ($lang === 'en')
      ? (isset($cfg['from_name_en']) ? $cfg['from_name_en'] : '')
      : (isset($cfg['from_name_es']) ? $cfg['from_name_es'] : '');
    $name = sanitize_text_field($name_raw);

    // Logs forenses
    pmx_mail_dbg('FROM seleccionado (antes de validación)',
      'lang='.(is_null($lang)?'null':$lang).' | name='.pmx_mail_snip($name).' | email='.$email
    );

    return array('email' => $email, 'name' => $name);
  }
}

/* ============ INYECTAR NOMBRE SI FALTA EN LA LEYENDA ============ */
if (!function_exists('pmx_inject_name_if_missing')) {
  function pmx_inject_name_if_missing($txt, $nombre, $is_en){
    $txt = (string)$txt; $nombre = (string)$nombre;
    if ($txt === '' || $nombre === '') return $txt;

    // Si ya trae placeholder, no tocamos
    if (stripos($txt, '{NOMBRE}') !== false || stripos($txt, '{NAME}') !== false) return $txt;

    if (!$is_en) {
      // ES: "Estimado(a) usuario" -> "Estimado(a) Nombre"
      $out = preg_replace('/(Estimado\(a\))\s+usuario\b/i', 'Estimado(a) <strong>'.esc_html($nombre).'</strong>', $txt, 1, $count);
      if ($count > 0) return $out;
      // "Estimado(a)" a secas -> insertamos nombre
      $out = preg_replace('/Estimado\(a\)/i', 'Estimado(a) <strong>'.esc_html($nombre).'</strong>', $txt, 1, $count);
      if ($count > 0) return $out;
      // Reemplazar primera aparición de "usuario"
      $out = preg_replace('/\busuario\b/i', '<strong>'.esc_html($nombre).'</strong>', $txt, 1, $count);
      if ($count > 0) return $out;
      // Si no hay saludo ni palabra "usuario", lo anteponemos
      return 'Estimado(a) <strong>'.esc_html($nombre).'</strong>, '.ltrim($txt);
    }

    // EN
    $out = preg_replace('/(Dear)\s+user\b/i', 'Dear <strong>'.esc_html($nombre).'</strong>', $txt, 1, $count);
    if ($count > 0) return $out;
    $out = preg_replace('/\bDear\b/i', 'Dear <strong>'.esc_html($nombre).'</strong>', $txt, 1, $count);
    if ($count > 0) return $out;
    $out = preg_replace('/\buser\b/i', '<strong>'.esc_html($nombre).'</strong>', $txt, 1, $count);
    if ($count > 0) return $out;
    return 'Dear <strong>'.esc_html($nombre).'</strong>, '.ltrim($txt);
  }
}

/* ============ API PRINCIPAL (SIN FALLBACKS) ============ */
if (!function_exists('pmx_mail_strings')) {
  function pmx_mail_strings($lang, $nombre, $rendered, $mail){
    $is_en = ($lang === 'en');
    pmx_mail_dbg('pmx_mail_strings()', 'lang='.$lang.' | rendered='.$rendered.' | mail='.pmx_mail_snip($mail, 60));
    $cfg   = pmx_mail_conf_load();

    $rep = array(
      '{NOMBRE}' => esc_html($nombre),
      '{NAME}'   => esc_html($nombre),
      '{NUM}'    => strval(intval($rendered)),
      '{COUNT}'  => strval(intval($rendered)),
    );

    // Tomamos ACF
    $subject      = (string)($is_en ? $cfg['subject_en'] : $cfg['subject_es']);
    $tot_label    = (string)($is_en ? $cfg['count_en']   : $cfg['count_es']);
    $sub_titulo   = (string)($is_en ? $cfg['body_en']    : $cfg['body_es']);
    $personal_raw = (string)($is_en ? $cfg['pers_en']    : $cfg['pers_es']);

    // Reemplazo + inyección de nombre si falta
    $personalizado = strtr($personal_raw, $rep);
    $personalizado = pmx_inject_name_if_missing($personalizado, $nombre, $is_en);

    // Sanitiza
    $subject      = sanitize_text_field($subject);
    $tot_label    = sanitize_text_field($tot_label);
    $sub_titulo   = wp_kses_post($sub_titulo);
    $personalizado= wp_kses_post($personalizado);

    pmx_mail_dbg('Strings resultantes',
      'subject='.pmx_mail_snip($subject)
      .' | tot_label='.pmx_mail_snip($tot_label)
      .' | sub_titulo='.pmx_mail_snip($sub_titulo)
      .' | personalizado='.pmx_mail_snip($personalizado)
    );

    return array(
      'subject'       => $subject,
      'tot_label'     => $tot_label,
      'sub_titulo'    => $sub_titulo,
      'personalizado' => $personalizado,
      'no_items'      => $is_en ? 'There are no new projects at the moment.' : 'No hay proyectos nuevos por el momento.', //opcional
      'btn_unsub'     => $is_en ? 'Unsubscribe' : 'Darse de Baja', // opcional
    );
  }
}

// campos acf

/* ========= CONFIG TABLA REPORTS (gatillo por BD) ========= */
if (!defined('PMX_REPORTS_TABLE')) {
  global $wpdb; define('PMX_REPORTS_TABLE', $wpdb->prefix.'bancomext_users_reports');
}
if (!defined('PMX_PROCESO_NUEVOS')) define('PMX_PROCESO_NUEVOS','proyectos_nuevos');
if (!defined('PMX_TRIGGER_STATUS')) define('PMX_TRIGGER_STATUS','PENDIENTE'); // cambia si lo deseas

if (!function_exists('pmx_reports_update_status_by_ids')) {
  function pmx_reports_update_status_by_ids(array $ids, $status, $idioma){
    if (empty($ids)) return;
    global $wpdb;
    foreach ($ids as &$x) { $x = (int)$x; } unset($x);
    $place  = implode(',', array_fill(0, count($ids), '%d'));
    $idioma_disp = function_exists('pmx_to_display_locale') ? pmx_to_display_locale($idioma) : (string)$idioma;
    $sql = "UPDATE ".PMX_REPORTS_TABLE." SET status=%s, report=%s, idioma=%s WHERE ID IN ($place)";
    $params = array_merge(array($status, current_time('mysql'), $idioma_disp), $ids);
    $wpdb->query(call_user_func_array(array($wpdb,'prepare'), array_merge(array($sql), $params)));
  }
}

if (!function_exists('pmx_reports_pick_for_email')) {
  // Obtiene filas gatillo y las bloquea a PROCESANDO
  function pmx_reports_pick_for_email($email){
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare(
      "SELECT ID, follow, idioma
         FROM ".PMX_REPORTS_TABLE."
        WHERE proceso=%s AND email=%s AND status=%s",
      PMX_PROCESO_NUEVOS, $email, PMX_TRIGGER_STATUS
    ));
    if (empty($rows)) return array();
    $ids = array_map(function($r){ return (int)$r->ID; }, $rows);
    $idioma = $rows[0]->idioma ?: 'es';
    pmx_reports_update_status_by_ids($ids, 'PROCESANDO', $idioma);
    return $rows;
  }
}

/* ============================================================
 * ENVÍO DE NOTIFICACIONES (con logging a BD y gate por reports)
 * ============================================================ */
if (!function_exists('enviar_notificaciones_proyectos_nuevos')) {

  if (!function_exists('pmx_pre_envio_expira_nuevos')) {
    function pmx_pre_envio_expira_nuevos(){
      pmx_backfill_marked_at();
      pmx_auto_unmark_expired_nuevos(PMX_NUEVO_TTL);
    }
  }

  function enviar_notificaciones_proyectos_nuevos() {
    $inversionistas = obtener_inversionistas_para_nuevos_proyectos();
    pmx_log('Inversionistas con check proyectos_nuevos_fin: '.count($inversionistas));
    if (empty($inversionistas)) { pmx_log('No hay inversionistas elegibles.'); return; }

    foreach ($inversionistas as $inv) {
      $inv_id = $inv->ID;
      $email  = get_field('correo_registro_reg_inversionista', $inv_id);
      $nombre = get_the_title($inv_id);
      $lang   = pmx_get_inversionista_lang($inv_id); // 'es' | 'en'

      // Override por ACF directo si existe
      $pref_raw = function_exists('get_field')
        ? get_field('idioma_de_preferencia_reg_inversionista', $inv_id)
        : get_post_meta($inv_id, 'idioma_de_preferencia_reg_inversionista', true);
      if ($pref_raw !== '' && $pref_raw !== null) {
        if (!function_exists('pmx_locale_to_lang')) {
          function pmx_locale_to_lang($locale){ $locale=strtolower((string)$locale); return ($locale==='en'||strpos($locale,'en_')===0)?'en':'es'; }
        }
        $pref_norm = pmx_locale_to_lang($pref_raw);
        if (in_array($pref_norm, array('es','en'), true) && $pref_norm !== $lang) {
          pmx_log('LANG_OVERRIDE inv='.$inv_id.' from='.$lang.' by_pref='.$pref_norm.' (raw="'.$pref_raw.'")');
          $lang = $pref_norm;
        }
      }

      // CORREO INVALIDO
      // CORREO INVALIDO (formato + dominio con DNS)
$em = trim((string)$email);
$ok = false;

if ($em !== '' && is_email($em)) {
  // extrae dominio
  $at = strrpos($em, '@');
  $dom = $at !== false ? strtolower(substr($em, $at + 1)) : '';

  static $dns_cache = array(); // evita repetir consultas DNS por el mismo dominio

  if ($dom !== '') {
    if (isset($dns_cache[$dom])) {
      $ok = $dns_cache[$dom];
    } else {
      // MX preferente; si no hay MX, acepta A (muchos servidores reciben por A)
      $mx_ok = function_exists('checkdnsrr') ? @checkdnsrr($dom, 'MX') : true;
      $a_ok  = $mx_ok ? true : (function_exists('checkdnsrr') ? @checkdnsrr($dom, 'A') : true);
      $ok = ($mx_ok || $a_ok);
      $dns_cache[$dom] = $ok;
    }
  }
}

// si falla cualquiera (formato o DNS), lo tratamos como inválido
if (!$ok) {
  pmx_log('INV '.$inv_id.' '.$nombre.' ❌ email inválido o dominio sin DNS: '.$email);
  pmx_log_db_report($email, array('inv_id'=>$inv_id,'nombre'=>$nombre,'motivo'=>'email_invalido'), 'proyectos_nuevos','EMAIL_INVALIDO',$lang);
  continue;
}


      /* =========================================================
       * PREPARAR COLA PENDIENTE (sin crear funciones nuevas)
       * - Toma proyectos NUEVOS que hagan match con el inversionista
       * - Excluye los ya enviados (ACF) y los que en BD ya están:
       *     ENVIADO / PENDIENTE / PROCESANDO (evita duplicados)
       * - Inserta filas PENDIENTE en wp_bancomext_users_reports
       * ========================================================= */
      pmx_pre_envio_expira_nuevos(); // asegura ventana vigente
      $posts_match = obtener_proyectos_nuevos($inv_id, PMX_NUEVO_TTL); // ya filtra por preferencias si aplica
      $ids_match = array();
      foreach ((array)$posts_match as $p) {
        $pid = is_object($p) ? (int)$p->ID : (int)$p['ID'];
        if ($pid > 0) $ids_match[$pid] = true;
      }
      $ids_match = array_keys($ids_match);

      // Excluir los que ya están en ACF "proyectos_nuevos_enviados"
      $enviados_acf = get_field('proyectos_nuevos_enviados', $inv_id);
      if (!is_array($enviados_acf)) $enviados_acf = array();
      $enviados_set = array();
      foreach ($enviados_acf as $e) { $enviados_set[(int)$e] = 1; }

      // Excluir los que ya tengan fila ENVIADO/PENDIENTE/PROCESANDO para este email
      $ids_queue = array();
      if (!empty($ids_match)) {
        global $wpdb;
        $ids_sql = implode(',', array_map('intval', $ids_match));
        // Nota: follow guarda el ID como texto, pero comparamos por IN(num) sin problema
        $rows = $wpdb->get_results($wpdb->prepare(
          "SELECT follow, status
             FROM ".PMX_REPORTS_TABLE."
            WHERE proceso=%s AND email=%s AND follow IN ($ids_sql)",
          PMX_PROCESO_NUEVOS, $email
        ));
        $status_by_follow = array();
        foreach ((array)$rows as $r) {
          $f = (int)$r->follow;
          $status_by_follow[$f] = strtoupper((string)$r->status);
        }

        foreach ($ids_match as $pid) {
          if (isset($enviados_set[$pid])) continue; // ya enviado por ACF
          $st = isset($status_by_follow[$pid]) ? $status_by_follow[$pid] : '';
          if ($st === 'ENVIADO' || $st === 'PENDIENTE' || $st === 'PROCESANDO') continue; // evita duplicados/reenvíos
          $ids_queue[] = (int)$pid;
        }
      }

      if (!empty($ids_queue)) {
        pmx_log('INV '.$inv_id.' '.$nombre.' ➕ preparando PENDIENTE ids=['.implode(',', $ids_queue).']');
        pmx_log_db_report($email, array('ids'=>$ids_queue), PMX_PROCESO_NUEVOS, PMX_TRIGGER_STATUS, $lang);
      } else {
        pmx_log('INV '.$inv_id.' '.$nombre.' (sin nuevos para preparar en PENDIENTE)');
      }
      /* ======= FIN PREPARACIÓN COLA PENDIENTE ======= */

      /* ---- GATE POR BD: sólo si hay reports PENDIENTE ---- */
      $gate_rows = pmx_reports_pick_for_email($email);
      if (empty($gate_rows)) {
        pmx_log('INV '.$inv_id.' '.$nombre.' ⏭ sin trigger en '.PMX_REPORTS_TABLE.' ('.PMX_PROCESO_NUEVOS.'/'.PMX_TRIGGER_STATUS.')');
        continue;
      }
      $ids_gate = array_map(function($r){ return (int)$r->ID; }, $gate_rows);

      // Reconstruir lista desde follow (en reports guardas SOLO el ID)
      $lista_posts = array();
      $seen = array();
      foreach ($gate_rows as $gr) {
        $pid = (int)$gr->follow;
        if ($pid > 0 && empty($seen[$pid])) {
          $post = get_post($pid);
          if ($post && $post->post_status === 'publish') { $lista_posts[] = $post; $seen[$pid] = true; }
        }
      }

      // Evitar reenvíos previos por ACF
      $enviados = get_field('proyectos_nuevos_enviados', $inv_id);
      if (!is_array($enviados)) $enviados = array();
      $lista = array();
      foreach ($lista_posts as $p) { if (!in_array($p->ID, $enviados, true)) $lista[] = $p; }

      // Si no hay nada que mandar con esos IDs, marcar SIN_PROYECTOS
      if (empty($lista)) {
        pmx_reports_update_status_by_ids($ids_gate, 'SIN_PROYECTOS', $lang);
        pmx_log('INV '.$inv_id.' '.$nombre.' ✅ SIN_PROYECTOS (IDs en gate filtrados o no publicables).');
        pmx_log_db_report($email, array('ids'=>array()), 'proyectos_nuevos', 'SIN_PROYECTOS', $lang);
        continue;
      }

      // Strings por idioma
      $S = pmx_mail_strings($lang, $nombre, count($lista), $email);
      $asunto = $S['subject'];

      // Render del correo
      $html = pmx_render_email_proyectos_nuevos_html($nombre, $email, $lista, $lang, $lang);
      if (function_exists('pmx_hide_changes_blocks')) { $html = pmx_hide_changes_blocks($html); }

      // Forzar HTML vía filtro
      $set_html = function(){ return 'text/html; charset=UTF-8'; };
      add_filter('wp_mail_content_type', $set_html);

         // --- FROM personalizado desde ACF ---
$from_conf  = function_exists('pmx_mail_from_conf') ? pmx_mail_from_conf($lang) : array('email'=>'','name'=>'');

// Si en ACF hay algo, lo usamos; si no, dejamos tu valor actual
$from_name  = trim(isset($from_conf['name'])  ? $from_conf['name']  : '');
$from_email = trim(isset($from_conf['email']) ? $from_conf['email'] : '');

if ($from_name !== '' || $from_email !== '') {
  if ($from_name  === '') $from_name  = 'Proyectos México';
  if ($from_email === '') $from_email = 'no-replyyy@banobras.gob.mx';
  $from_header = 'From: ' . $from_name . ' <' . $from_email . '>';
} else {
  // fallback a lo que ya tenías
  $from_header = 'From: Proyectos México <no-reply@banobras.gob.mx>';
}

$headers = array(
  $from_header,
  'Reply-To: ' . $email,
);
	  
	  
	  

      pmx_log('ENVIAR ['.$lang.'] -> to='.$email.' | subj="'.wp_specialchars_decode($asunto, ENT_QUOTES).'" | html_len='.strlen((string)$html));
      pmx_log('MAIL_LANG inv='.$inv_id.' name="'.get_the_title($inv_id).'" lang='.$lang);

      static $pmx_ph_inited = false;
      if (!$pmx_ph_inited) {
        add_action('phpmailer_init', function($phpmailer){
          error_log('[PMX] PHPMailer init -> Mailer='.$phpmailer->Mailer.' Host='.$phpmailer->Host.' From='.$phpmailer->From);
        });
        $pmx_ph_inited = true;
      }

      $ok = wp_mail($email, wp_specialchars_decode($asunto, ENT_QUOTES), $html, $headers);
      remove_filter('wp_mail_content_type', $set_html);

      // Fallback PLAIN si falla
      if (!$ok) {
        pmx_log('❌ wp_mail HTML FALLÓ a '.$email.' -> intento fallback PLAIN');
        $plain = pmx_html_to_text($html);
        $set_txt = function(){ return 'text/plain; charset=UTF-8'; };
        add_filter('wp_mail_content_type', $set_txt);
        $ok = wp_mail($email, wp_specialchars_decode($asunto, ENT_QUOTES), $plain, $headers);
        remove_filter('wp_mail_content_type', $set_txt);
      }

      if ($ok) {
        // Marcar como enviados (ACF) y registrar follow por proyecto
        foreach ($lista as $p) { $enviados[] = $p->ID; }
        $enviados = array_values(array_unique($enviados));
        update_field('proyectos_nuevos_enviados', $enviados, $inv_id);

        if (function_exists('pmx_log_db_follow')) {
          foreach ($lista as $p) {
            $pid_base = is_object($p) ? $p->ID : intval($p['ID']);
            pmx_log_db_follow($email, $pid_base, $lang);
          }
        }

        // Actualiza MISMAS filas del gate a ENVIADO
        pmx_reports_update_status_by_ids($ids_gate, 'ENVIADO', $lang);

        pmx_log('INV '.$inv_id.' '.$nombre.' ✅ correo ENVIADO ['.$lang.'] a '.$email.' | total_enviados='.count($lista));
        /*pmx_log_db_report(
          $email,
          array('ids' => array_map(function($p){ return is_object($p)?intval($p->ID):intval($p['ID']); }, $lista)),
          'proyectos_nuevos',
          'ENVIADO',
          $lang
        );*/
      } else {
        pmx_log('INV '.$inv_id.' '.$nombre.' ❌ wp_mail falló a '.$email);
        if (class_exists('PHPMailer')) {
          global $phpmailer;
          if ($phpmailer && isset($phpmailer->ErrorInfo)) pmx_log('PHPMailer ErrorInfo: '.$phpmailer->ErrorInfo);
        }

        // Actualiza MISMAS filas del gate a FALLO_ENVIO
        pmx_reports_update_status_by_ids($ids_gate, 'FALLO_ENVIO', $lang);

        /*pmx_log_db_report(
          $email,
          array(
            'inv_id'         => $inv_id,
            'nombre'         => $nombre,
            'motivo'         => 'wp_mail_fail',
            'total_intentos' => count($lista)
          ),
          'proyectos_nuevos',
          'FALLO_ENVIO',
          $lang
        );*/
      }
    } // foreach inversionistas
  }
}


/* ================= L10N: forzar idioma de render (PLL/WPML) ================= */
if (!function_exists('pmx_begin_lang')) {
  function pmx_begin_lang($lang){
    $ctx = array('engine'=>'none','prev'=>null);
    $lang = ($lang==='en'?'en':'es'); // sanitiza
    // Polylang
    if (function_exists('pll_current_language') && function_exists('pll_switch_language')) {
      $ctx['engine'] = 'pll';
      $ctx['prev']   = pll_current_language('slug');
      if ($ctx['prev'] !== $lang) pll_switch_language($lang);
      pmx_log('L10N: PLL switch from '.$ctx['prev'].' to '.$lang);
      return $ctx;
    }
    // WPML
    if (defined('ICL_SITEPRESS_VERSION')) {
      global $sitepress;
      if ($sitepress) {
        $ctx['engine'] = 'wpml';
        $ctx['prev']   = $sitepress->get_current_language();
        if ($ctx['prev'] !== $lang) $sitepress->switch_lang($lang, true);
        pmx_log('L10N: WPML switch from '.$ctx['prev'].' to '.$lang);
      }
    }
    return $ctx;
  }
}
if (!function_exists('pmx_end_lang')) {
  function pmx_end_lang($ctx){
    if (!is_array($ctx)) return;
    if ($ctx['engine']==='pll' && isset($ctx['prev']) && function_exists('pll_switch_language')) {
      pll_switch_language($ctx['prev']);
    } elseif ($ctx['engine']==='wpml' && isset($ctx['prev']) && defined('ICL_SITEPRESS_VERSION')) {
      global $sitepress; if ($sitepress) $sitepress->switch_lang($ctx['prev'], true);
    }
  }
}
/* Obtener el ID del post en el idioma solicitado (si existe traducción) */
if (!function_exists('pmx_get_post_in_lang')) {
  function pmx_get_post_in_lang($post_id, $lang){
    $lang = ($lang==='en'?'en':'es');
    $target = $post_id;
    if (function_exists('pll_get_post')) {
      $tr = pll_get_post($post_id, $lang);
      if ($tr) $target = $tr;
    } elseif (has_filter('wpml_object_id')) {
      $tr = apply_filters('wpml_object_id', $post_id, get_post_type($post_id), true, $lang);
      if ($tr) $target = $tr;
    }
    return $target;
  }
}

/* ======================= RENDER EMAIL (bilingüe, con switch) ======================= */
/* Reemplaza el PRIMER <h1> por un bloque */
if (!function_exists('pmx_replace_first_h1_with')) {
  function pmx_replace_first_h1_with($html, $replacement) {
    return preg_replace('/<h1\b[^>]*>.*?<\/h1>/is', $replacement, $html, 1);
  }
}

/* === Helpers de título por idioma y corrección del bloque === */
if (!function_exists('pmx_get_title_in_lang')) {
  function pmx_get_title_in_lang($post_id_base, $lang){
    $lang = ($lang === 'en') ? 'en' : 'es';
    $pid  = pmx_get_post_in_lang($post_id_base, $lang);
    $po   = get_post($pid);
    // Usa post_title directo del post traducido; si no, fallback a get_the_title
    $t    = ($po && isset($po->post_title)) ? $po->post_title : get_the_title($pid);
    return is_string($t) ? $t : '';
  }
}

if (!function_exists('pmx_force_block_title')) {
  // Si el shortcode ignora los títulos pasados, fuerza el texto visible a EN
  function pmx_force_block_title($html, $title_from, $title_to){
    $title_from = trim((string)$title_from);
    $title_to   = trim((string)$title_to);
    if ($title_from !== '' && $title_to !== '' && $title_from !== $title_to) {
      // Reemplazo simple y seguro (solo texto, no etiquetas)
      $html = str_replace($title_from, $title_to, $html);
    }
    return $html;
  }
}

/* ===================== Helpers para forzar el título EN desde meta ===================== */

/* Normaliza string (minúsculas, sin acentos) para comparar */
if (!function_exists('pmx_fold_str')) {
  function pmx_fold_str($s){
    $s = (string)$s;
    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = mb_strtolower($s, 'UTF-8');
    $from = array('á','é','í','ó','ú','ñ','ä','ë','ï','ö','ü');
    $to   = array('a','e','i','o','u','n','a','e','i','o','u');
    $s = str_replace($from, $to, $s);
    $s = preg_replace('~\s+~u', ' ', $s);
    return trim($s);
  }
}

/* Reemplaza texto en nodos de texto usando DOM (comparación plegada) */
if (!function_exists('pmx_dom_replace_text_fold')) {
  function pmx_dom_replace_text_fold($html, $needle, $replacement){
    $needle_f = pmx_fold_str($needle);
    if ($needle_f === '' || $replacement === '' || $html === '' || !class_exists('DOMDocument')) return $html;

    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $ok  = $dom->loadHTML('<?xml encoding="utf-8" ?><div id="pmxwrap">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    if (!$ok) { libxml_clear_errors(); return $html; }

    $wrap = $dom->getElementById('pmxwrap'); if (!$wrap) { libxml_clear_errors(); return $html; }

    $changed = false;
    $walker = function($node) use (&$walker, $needle_f, $replacement, &$changed){
      if ($node->nodeType === XML_TEXT_NODE) {
        if (pmx_fold_str($node->nodeValue) === $needle_f) {
          $node->nodeValue = $replacement;
          $changed = true;
        }
      } elseif ($node->hasChildNodes()) {
        foreach (iterator_to_array($node->childNodes) as $child) $walker($child);
      }
    };
    $walker($wrap);

    $out = '';
    foreach ($wrap->childNodes as $child) { $out .= $dom->saveHTML($child); }
    libxml_clear_errors();
    return $changed ? $out : $html;
  }
}

/* Cambia el texto del <a> cuyo href coincide con la URL del post */
if (!function_exists('pmx_force_block_title_by_url')) {
  function pmx_force_block_title_by_url($html, $target_url, $new_title){
    $new_title = (string)$new_title;
    if ($new_title === '' || !is_string($html) || $html === '') return $html;

    if (class_exists('DOMDocument')) {
      libxml_use_internal_errors(true);
      $dom = new DOMDocument('1.0', 'UTF-8');
      $ok  = $dom->loadHTML('<?xml encoding="utf-8" ?><div id="pmxwrap">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
      if ($ok) {
        $path = (string)parse_url($target_url, PHP_URL_PATH);
        if ($path === '') { $path = $target_url; }

        $xpath = new DOMXPath($dom);
        $anchors = $xpath->query('//a');
        $changed = false;
        foreach ($anchors as $a) {
          /** @var DOMElement $a */
          $href = $a->getAttribute('href');
          if ($href && strpos($href, $path) !== false) {
            while ($a->firstChild) $a->removeChild($a->firstChild);
            $a->appendChild($dom->createTextNode($new_title));
            if (!$a->hasAttribute('title')) $a->setAttribute('title', $new_title);
            $changed = true;
            break;
          }
        }
        $wrap = $dom->getElementById('pmxwrap');
        if ($changed && $wrap) {
          $out  = '';
          foreach ($wrap->childNodes as $child) { $out .= $dom->saveHTML($child); }
          libxml_clear_errors();
          return $out;
        }
      }
      libxml_clear_errors();
    }
    return $html;
  }
}

/* Devuelve títulos (ES del post, EN desde meta nombre_oficial_ingles) */
if (!function_exists('pmx_get_titles_from_meta')) {
  function pmx_get_titles_from_meta($post_id){
    $t_es = get_the_title($post_id);
    $t_es = is_string($t_es) ? html_entity_decode($t_es, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';

    $t_en = get_post_meta($post_id, 'nombre_oficial_ingles', true);
    $t_en = is_string($t_en) ? html_entity_decode($t_en, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';

    return array($t_es, $t_en);
  }
}

/* ======================= Helpers para 100% ancho (full-bleed) ======================= */
if (!function_exists('pmx_full_bleed_wrap')) {
  function pmx_full_bleed_wrap($inner_html, $bg = '#ffffff') {
    return '
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
       style="width:100%!important;min-width:100%!important;table-layout:fixed;border-collapse:collapse;background:'.$bg.';mso-table-lspace:0pt;mso-table-rspace:0pt;">
  <tr>
    <td align="center" style="padding:0;margin:0;">
      '.$inner_html.'
    </td>
  </tr>
</table>';
  }
}

if (!function_exists('pmx_email_fullwidth_filters')) {
  function pmx_email_fullwidth_filters($html) {
    // 1) Elimina max-width en px y fuerza width:100%
    $html = preg_replace('/max-width\s*:\s*\d+\s*px\s*;?/i', '', $html);

    // 2) Tablas con width en px -> 100%
    $html = preg_replace('/(<table\b[^>]*?)\bwidth="(?:[2-9]\d{2,3})"([^>]*>)/i', '$1 width="100%"$2', $html);

    // 3) Fuerza 100% a contenedores típicos de la plantilla
    $override = 'width:100%!important;max-width:100%!important;margin:0!important;padding:0!important;';
    $targets  = array('email_container','email_row','content_section','email_table','email_body','column','content_cell','col_6','col_12','wrapper','container');

    foreach ($targets as $cls) {
      $html = preg_replace_callback(
        '/<([a-z]+)\b([^>]*\bclass="[^"]*\b'.$cls.'\b[^"]*"[^>]*)>/i',
        function($m) use($override){
          $tag = $m[1];
          $attrs = $m[2];
          if (preg_match('/\bstyle="([^"]*)"/i', $attrs)) {
            $attrs = preg_replace('/\bstyle="([^"]*)"/i', 'style="$1 '.$override.'"', $attrs);
          } else {
            $attrs .= ' style="'.$override.'"';
          }
          return '<'.$tag.$attrs.'>';
        },
        $html
      );
    }

    // 4) En imágenes del logo (y similares) permite responsivo, pero respeta su max-width si ya existe
    $html = preg_replace('/(<img\b[^>]*\bstyle="[^"]*)\bwidth\s*:\s*\d+\s*px;?/i', '$1', $html);
    // No tocamos height:auto de imágenes

    return $html;
  }
}

/* ======================= RENDER EMAIL (todo el correo full-width) ======================= */
// lista de proyectos en correo
if (!function_exists('pmx_render_email_proyectos_nuevos_html')) {
  function pmx_render_email_proyectos_nuevos_html($nombre, $mail, $proyectos, $language='es', $idioma='es') {
    // El switch de idioma SOLO es para textos de UI. Los títulos no dependen de Polylang.
    $language = ($language==='en'?'en':'es');
    $ctx = pmx_begin_lang($language);

    $items_html = '';
    $rendered   = 0;

    if (is_array($proyectos) && !empty($proyectos)) {
  foreach ($proyectos as $p) {

    // ID del proyecto (acepta numérico, objeto o array)
    $post_id = is_numeric($p) ? (int)$p
             : (is_object($p) ? (int)$p->ID
             : (isset($p['ID']) ? (int)$p['ID'] : 0));

    if ($post_id <= 0) {
      if (function_exists('pmx_log')) pmx_log('MAIL_ITEM skip: post_id inválido para p='.substr(@json_encode($p),0,120));
      continue;
    }

    // Títulos: ES (post_title), EN SIEMPRE desde meta nombre_oficial_ingles
    list($titulo_es, $titulo_en) = pmx_get_titles_from_meta($post_id);

    // Render del bloque del item
    $block = Title_Proyect_Preferences_NewProject(
      $language,         // UI
      $language,         // datos
      $post_id,          // usamos el post base
      $titulo_es,        // ES
      $titulo_en,        // EN (meta)
      ''                 // sin botón extra
    );

    // URL que usa el bloque
    $url_used = get_permalink($post_id);
    if ($language === 'en' && $url_used) {
      $url_used = add_query_arg('language', 'en', $url_used);
    }

    // Forzar visual del título EN si corresponde
    if ($language === 'en') {
      $from = $titulo_es;
      $to   = ($titulo_en !== '' ? $titulo_en : $titulo_es); // fallback si faltara meta

      $before = $block;

      // 1) reemplazo directo (texto y entidades)
      if ($from !== '' && $to !== '' && pmx_fold_str($from) !== pmx_fold_str($to)) {
        foreach (array($from, htmlentities($from, ENT_QUOTES | ENT_HTML5, 'UTF-8'), htmlspecialchars($from, ENT_QUOTES | ENT_HTML5, 'UTF-8')) as $cand) {
          if ($cand !== '') $block = str_replace($cand, $to, $block);
        }
      }

      // 2) reemplazo del texto del <a> por URL
      $block = pmx_force_block_title_by_url($block, $url_used, $to);

      // 3) refuerzo DOM por nodos de texto (comparación plegada)
      if (pmx_fold_str($before) === pmx_fold_str($block) && $from !== '' && $to !== '') {
        $block = pmx_dom_replace_text_fold($block, $from, $to);
      }

      if (function_exists('pmx_log')) pmx_log('MAIL_EN_TITLE pid='.$post_id.' | meta_en='.($titulo_en!==''?'YES':'NO').' | url='.$url_used);
    }

    if (trim($block) !== '') {
      $items_html .= $block;

      // === Alias debajo del proyecto (usa $post_id, NO $pid) + logging ===
      if (function_exists('pmx_log')) pmx_log('ALIAS_CALL pid='.$post_id.' lang='.$language);
      $alias_html = pmx_get_alias_proyecto_html($post_id, $language);
      if ($alias_html !== '') {
        $items_html .= $alias_html;
        if (function_exists('pmx_log')) pmx_log('ALIAS_APPEND pid='.$post_id.' | Alias agregado al correo');
      } else {
        if (function_exists('pmx_log')) pmx_log('ALIAS_SKIP pid='.$post_id.' | Alias vacío o no encontrado');
      }

      $rendered++;
    } else {
      if (function_exists('pmx_log')) pmx_log('MAIL_ITEM empty-block pid='.$post_id);
    }

    // Log de contexto (auditoría)
    if (function_exists('pmx_log')) pmx_log('RENDER item pid='.$post_id.' lang='.$language.' | ES="'.$titulo_es.'" | EN(meta)="'.$titulo_en.'"');
  }
}


    // Strings
    $S = pmx_mail_strings($language, $nombre, $rendered, $mail);

    // Logo + header (elige assets por idioma)
    switch ($language) {
      case 'es':
        $logo_url  = 'https://www.proyectosmexico.gob.mx/wp-content/uploads/2021/08/PM_Esp_2021_300x100.png';
        $logo_link = 'https://www.proyectosmexico.gob.mx/';
        $alt_proyectos = 'Proyectos México';
        break;
      case 'en':
        $logo_url  = 'https://www.proyectosmexico.gob.mx/wp-content/uploads/2017/03/LogoMPH_Alta-e1657506734182.png';
        $logo_link = 'https://www.proyectosmexico.gob.mx/en/';
        $alt_proyectos = 'Projects México';
        break;
      default:
        $logo_url  = 'https://www.proyectosmexico.gob.mx/wp-content/uploads/2021/08/PM_Esp_2021_300x100.png';
        $logo_link = 'https://www.proyectosmexico.gob.mx/';
        $alt_proyectos = 'Proyectos México';
        break;
    }

    $w = 210; 
    $h = 70; 
    $divider_color = '#e4e9eb';
	
	// =========================================
// PREHEADER (debe ir ANTES de cualquier <a>)
// =========================================
$lang = ($language === 'en') ? 'en' : 'es';
$preheader_text = ($lang === 'en')
  ? 'New projects available on Projects Mexico.'
  : 'Nuevos proyectos disponibles en Proyectos México.';

// Relleno invisible para que la vista previa no agarre el siguiente contenido
$pre_pad = str_repeat('&#8203;&#160;', 35);

$preheader_block = '
  <!-- PREHEADER OCULTO -->
  <div style="display:none!important;opacity:0;visibility:hidden;mso-hide:all;
              font-size:1px;line-height:1px;color:#ffffff;max-height:0;max-width:0;
              overflow:hidden;">
    '.$preheader_text.' '.$pre_pad.'
  </div>
  <!--[if mso]>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
      <tr><td style="mso-hide:all;font-size:1px;line-height:1px;color:#ffffff;">
        '.$preheader_text.' '.$pre_pad.'
      </td></tr>
    </table>
  <![endif]-->
';

	

    // Logo block (responsive)
    $logo_block = '
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;">
        <tr><td height="14" style="height:14px;line-height:14px;font-size:0;">&nbsp;</td></tr>
        <tr>
          <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td>
              <a href="'.$logo_link.'" target="_blank" rel="noopener" style="display:inline-block;text-decoration:none;">
                <img src="'.$logo_url.'" width="'.$w.'" height="'.$h.'" alt="'.$alt_proyectos.'"
                     style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;width:100%;max-width:'.$w.'px;height:auto;">
              </a>
            </td></tr></table>
          </td>
        </tr>
        <tr><td style="padding:12px 0 0 0;"><table role="presentation" width="100%"><tr>
          <td style="height:1px;line-height:1px;font-size:0;background:'.$divider_color.';">&nbsp;</td>
        </tr></table></td></tr>
        <tr><td height="10" style="height:10px;line-height:10px;font-size:0;">&nbsp;</td></tr>
      </table>';

   $html  = '';
$html .= $preheader_block;   // <-- primero
$html .= $logo_block;        // <-- después ya puedes poner enlaces
    $header_html = Header_Preferences_Shortcode($rendered, $titulo_hdr, $S['sub_titulo'], $S['tot_label'], $language, $S['personalizado']);
    $header_html = pmx_replace_first_h1_with($header_html, $logo_block);

    // HEADER: quitar topes y envolver en banda full-bleed
    $header_html = pmx_email_fullwidth_filters($header_html);
    $header_html = pmx_full_bleed_wrap($header_html, '#ffffff');

    // CUERPO: items o mensaje vacío (también a 100%)
    if ($rendered > 0) {
      $items_html = pmx_email_fullwidth_filters($items_html);
      // Puedes dar un color de fondo distinto al cuerpo si lo necesitas (ej. #ffffff)
      $body_inner = pmx_full_bleed_wrap($items_html, '#ffffff');
      $html = $header_html . $body_inner;
    } else {
      $empty_html  = '<table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="email_body tc"><div class="email_container"><table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="content_cell" style="padding:16px;"><div class="email_row" style="max-width:600px;margin:0 auto;"><div class="col_6"><table class="column" width="100%"><tbody><tr><td class="column_cell px" style="text-align:center;font-family:Helvetica, Arial, sans-serif;color:#616161;"><p class="small" style="font-size:14px;line-height:20px;color:#a7b1b6;margin:0;">'.esc_html($S['no_items']).'</p></td></tr></tbody></table></div></div></td></tr></tbody></table></div></td></tr></tbody></table>';
      $empty_html  = pmx_email_fullwidth_filters($empty_html);
      $empty_html  = pmx_full_bleed_wrap($empty_html, '#ffffff');
      $html = $header_html . $empty_html;
    }

    // Espacio en blanco (sustituye botón de baja)
    $gap = '
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
      <tr><td align="center" style="padding:14px 28px 10px 28px;">
        <div style="height:48px; line-height:48px; font-size:0;">&nbsp;</div>
      </td></tr>
    </table>';
    $gap = pmx_email_fullwidth_filters($gap);
    $gap = pmx_full_bleed_wrap($gap, '#ffffff');
    $html .= $gap;

    // FOOTER interno (textos) + banda full-bleed gris
    $footer_inner  = Footer_references_Shortcode($language, $language, $mail, false, '', false, '');
    $footer_inner .= '
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center" style="padding:10px 28px 0 28px;">
          <div style="font:13px/1.6 Helvetica, Arial, sans-serif;color:#7e8a90;">
            '.esc_html($S['privacy_line']).'<br>
            <a href="'.esc_url($S['privacy_url']).'" target="_blank" rel="noopener"
               style="color:#2b82c6;text-decoration:underline;">'.esc_html($S['privacy_url']).'</a>
          </div>
        </td>
      </tr>
    </table>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"> 
      <tr><td style="padding:12px 28px 0 28px;"><div style="height:1px;background:#ffffff;line-height:1px;font-size:0;">&nbsp;</div></td></tr>
    </table>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
      <tr><td align="center" style="padding:10px 28px 24px 28px;">
        <div style="font:italic 12px/1.55 Helvetica, Arial, sans-serif;color:#8a8f95;">'.esc_html($S['disclaimer']).'</div>
      </td></tr>
    </table>';
    $footer_inner = pmx_email_fullwidth_filters($footer_inner);
    $html .= pmx_full_bleed_wrap($footer_inner, '#ffffff'); // fondo del aviso de privacidad
	//$html .= pmx_full_bleed_wrap($footer_inner, '#f4f6f8');
	//<tr><td style="padding:12px 28px 0 28px;"><div style="height:1px;background:#d9dee2;line-height:1px;font-size:0;">&nbsp;</div></td></tr> color de la liena divisoria

    // === PASADA FINAL: asegura que TODO el HTML está full-width y envuelve con una última banda ===
    $html = pmx_email_fullwidth_filters($html);
    $html = pmx_full_bleed_wrap($html, '#ffffff');
	
	
	

    pmx_end_lang($ctx);
    return $html;
  }
}


/* ========== Helpers para ocultar tabla de “cambios” (opcional) ========== */
if (!function_exists('pmx_strip_tables_by_class')) {
  function pmx_strip_tables_by_class($html, $classA = 'email_table', $classB = 'hide') {
    $pat = '/<table\b[^>]*class=(["\'])[^\1>]*\b' . preg_quote($classA, '/') . '\b[^\1>]*\b' . preg_quote($classB,'/') . '\b[^\1>]*\1[^>]*>/i';
    while (preg_match($pat, $html, $m, PREG_OFFSET_CAPTURE)) {
      $openTag = $m[0][0];
      $start   = $m[0][1];
      $pos     = $start + strlen($openTag);
      $depth   = 1;
      while ($depth > 0 && preg_match('/<(\/?)table\b[^>]*>/i', $html, $m2, PREG_OFFSET_CAPTURE, $pos)) {
        $isClose = ($m2[1][0] === '/');
        $pos     = $m2[0][1] + strlen($m2[0][0]);
        $depth  += $isClose ? -1 : 1;
      }
      $html = substr_replace($html, '', $start, $pos - $start);
    }
    return $html;
  }
}
if (!function_exists('pmx_remove_table_containing_text')) {
  function pmx_remove_table_containing_text($html, $regexTexto) {
    if (!preg_match($regexTexto.'u', $html, $m, PREG_OFFSET_CAPTURE)) return $html;
    $posText = $m[0][1];
    $start = strripos(substr($html, 0, $posText), '<table');
    if ($start === false) return $html;
    if (!preg_match('/<table\b[^>]*>/i', $html, $mOpen, PREG_OFFSET_CAPTURE, $start)) return $html;
    $start = $mOpen[0][1];
    $pos   = $start + strlen($mOpen[0][0]);
    $depth = 1;
    while ($depth > 0 && preg_match('/<(\/?)table\b[^>]*>/i', $html, $m2, PREG_OFFSET_CAPTURE, $pos)) {
      $isClose = ($m2[1][0] === '/');
      $pos     = $m2[0][1] + strlen($m2[0][0]);
      $depth  += $isClose ? -1 : 1;
    }
    return substr_replace($html, '', $start, $pos - $start);
  }
}
if (!function_exists('pmx_hide_changes_blocks')) {
  function pmx_hide_changes_blocks($html) {
    $html = pmx_strip_tables_by_class($html, 'email_table', 'hide');
    $patText = '/Campo\s+modificado|Informaci(?:&oacute;|ó)n\s+anterior|Informaci(?:&oacute;|ó)n\s+actualizada/i';
    while (preg_match($patText.'u', $html)) $html = pmx_remove_table_containing_text($html, $patText);
    $css = '<style type="text/css">.hide, table.hide, .email_table.hide { display:none !important; mso-hide:all; visibility:hidden; max-height:0 !important; line-height:0 !important; font-size:0 !important; height:0 !important; overflow:hidden !important; }</style>';
    if (stripos($html, '<body') !== false) $html = preg_replace('~(<body[^>]*>)~i', '$1'.$css, $html, 1);
    else $html = $css.$html;
    return $html;
  }
}

/* ========== Extractor del primer bloque de tabla para cada proyecto ========== */
if (!function_exists('pmx_extract_first_table_block')) {
  function pmx_extract_first_table_block($html) {
    $start = stripos($html, '<table');
    if ($start === false) return $html;
    $chunk = substr($html, $start);
    if (!preg_match_all('/<(\/?)table\b[^>]*>/i', $chunk, $m, PREG_OFFSET_CAPTURE)) return $html;
    $depth = 0; $end = null;
    foreach ($m[1] as $i => $tag) {
      $isClose = ($tag[0] === '/');
      if (!$isClose) $depth++; else $depth--;
      if ($depth === 0) { $end = $m[0][$i][1] + strlen($m[0][$i][0]); break; }
    }
    if ($end !== null) return substr($chunk, 0, $end);
    return $html;
  }
}
if (!function_exists('Title_Proyect_Preferences_NewProject')) {
  function Title_Proyect_Preferences_NewProject($language, $idioma, $id_post, $post_titulo, $post_titulo_en, $btn_unsubscribe='') {
    $full = Title_Proyect_Preferences_Shortcode($language, $idioma, $id_post, $post_titulo, $post_titulo_en, $btn_unsubscribe);
    return pmx_extract_first_table_block($full);
  }
}

/* ==== Helpers preferencia 'proyectos nuevos' (fallback) ==== */
if (!function_exists('pmx_pref_nuevos_sig')) {
  function pmx_pref_nuevos_sig($inv_id, $email, $want){
    $key = wp_salt('auth');
    return hash_hmac('sha256', $inv_id.'|'.$email.'|'.$want.'|pref_nuevos', $key);
  }
}
if (!function_exists('pmx_pref_nuevos_link')) {
  function pmx_pref_nuevos_link($inv_id, $email, $want='1'){
    $want = ($want==='1' ? '1' : '0');
    $args = array(
      'pmx_pref_nuevos' => $want,
      'inv'  => intval($inv_id),
      // Pasa el mail en crudo; WP lo encodea en el URL
      'mail' => (string)$email,
      // La firma SIEMPRE usa el mail sin encode
      'sig'  => pmx_pref_nuevos_sig($inv_id, (string)$email, $want),
    );
    return add_query_arg($args, home_url('/'));
  }
}

/* ========= Handler del link ========= */
add_action('init', function(){
  if (!isset($_GET['pmx_pref_nuevos'])) return;

  $want = ($_GET['pmx_pref_nuevos']==='1') ? '1' : '0';
  $inv  = isset($_GET['inv'])  ? intval($_GET['inv']) : 0;
  // Ya viene decodificado por PHP
  $mail = isset($_GET['mail']) ? sanitize_email((string)$_GET['mail']) : '';
  $sig  = isset($_GET['sig'])  ? (string)$_GET['sig']  : '';

  $expected = pmx_pref_nuevos_sig($inv, $mail, $want);
  if ((function_exists('hash_equals') && !hash_equals($expected, $sig)) || (!function_exists('hash_equals') && $expected !== $sig)) {
    wp_die('Link inválido.', 403);
  }

  if ($inv>0 && get_post_type($inv)==='reg_inversionistas' && is_email($mail)) {
    update_post_meta($inv, 'proyectos_nuevos_fin', $want);
    update_post_meta($inv, 'proyectos_nuevos_fin_updated', current_time('mysql'));
    if (function_exists('pmx_log_db_report')) {
      pmx_log_db_report($mail, array('inv_id'=>$inv,'want'=>$want), 'proyectos_nuevos', $want==='1'?'OPTIN_OK':'OPTOUT_OK', 'es');
    }
    wp_die($want==='1'
      ? '✅ Has activado las notificaciones de proyectos nuevos.'
      : '🛑 Has desactivado las notificaciones de proyectos nuevos.'
    );
  }
  wp_die('Solicitud inválida.', 400);
});

add_action('admin_init', function () {
  if (!current_user_can('manage_options') || !isset($_GET['pmx_demo_link'])) return;
  $url = pmx_pref_nuevos_link(146575, sanitize_email('epriegoaz@gmail.com'), '1');
  wp_die('<p><a href="'.esc_url($url).'">Click para activar</a><br><code>'.$url.'</code></p>');
});

/* ======================= ENVIAR CORREO: Preferencia Proyectos Nuevos ======================= */
if (!function_exists('pmx_send_pref_nuevos_email')) {
  function pmx_send_pref_nuevos_email($inv_id){
    // Validaciones básicas
    if ($inv_id <= 0 || get_post_type($inv_id) !== 'reg_inversionistas') return false;

    $email  = sanitize_email( function_exists('get_field') ? get_field('correo_registro_reg_inversionista', $inv_id) : get_post_meta($inv_id, 'correo_registro_reg_inversionista', true) );
    $nombre = get_the_title($inv_id);
    if (!is_email($email)) return false;

    // Idioma preferido (si tu helper existe)
    $lang = function_exists('pmx_get_inversionista_lang') ? pmx_get_inversionista_lang($inv_id) : 'es';
    $is_en = ($lang === 'en');

    // Links firmados (ON/OFF)
    $link_on  = pmx_pref_nuevos_link($inv_id, $email, '1');
    $link_off = pmx_pref_nuevos_link($inv_id, $email, '0');

    // Asunto y textos
    $subject = $is_en ? 'Activate new project notifications' : 'Activa tus notificaciones de proyectos nuevos';
    $greet   = $is_en ? 'Dear' : 'Estimado(a)';
    $intro   = $is_en
      ? 'We have added the option to receive email notifications when new projects are published on Proyectos México.'
      : 'Hemos agregado la opción de recibir notificaciones por correo cuando se publiquen nuevos proyectos en Proyectos México.';
    $cta     = $is_en ? 'I want to receive new projects' : 'Quiero recibir proyectos nuevos';
    $alt     = $is_en ? 'If you don’t wish to receive them' : 'Si no deseas recibirlas';
    $alt2    = $is_en ? 'click here to unsubscribe' : 'haz clic aquí para no recibir';
    $bye     = $is_en ? 'Best regards,' : 'Saludos,';

    // HTML sencillo (puedes cambiar estilos/branding)
    $html = '
      <div style="font:15px/1.6 Helvetica,Arial,sans-serif;color:#222;padding:12px">
        <p>'.$greet.' <strong>'.esc_html($nombre).'</strong>,</p>
        <p>'.$intro.'</p>
        <p style="margin:18px 0">
          <a href="'.esc_url($link_on).'"
             style="display:inline-block;padding:12px 16px;border-radius:6px;background:#0b6;color:#fff;text-decoration:none;">
             '.$cta.'
          </a>
        </p>
        <p style="font-size:13px;color:#666">'.$alt.': <a href="'.esc_url($link_off).'">'.$alt2.'</a>.</p>
        <p style="margin-top:22px;color:#555">'.$bye.'<br>Proyectos México</p>
      </div>';

    // Enviar como HTML
    $set_html = function(){ return 'text/html; charset=UTF-8'; };
    add_filter('wp_mail_content_type', $set_html);

    $headers = array(
      'From: Proyectos México <no-reply@'.(function_exists('pmx_domain_from_home')? pmx_domain_from_home() : parse_url(home_url(),PHP_URL_HOST)).'>'
    );

    $ok = wp_mail($email, $subject, $html, $headers);
    remove_filter('wp_mail_content_type', $set_html);

    // Log opcional
    if (function_exists('pmx_log_db_report')) {
      pmx_log_db_report(
        $email,
        array('inv_id'=>$inv_id, 'sent'=> $ok ? 1 : 0),
        'proyectos_nuevos',
        $ok ? 'ONBOARDING_ENVIADO' : 'ONBOARDING_FALLO',
        $is_en ? 'en' : 'es'
      );
    }

    return (bool)$ok;
  }
}

/* ========= DISPARO MANUAL DESDE EL ADMIN (URL): /wp-admin/?pmx_send_pref_mail=1&inv=123 ========= */
add_action('admin_init', function(){
  if (!isset($_GET['pmx_send_pref_mail'])) return;
  if (!current_user_can('manage_options')) wp_die('No autorizado', 403);

  $inv_id = isset($_GET['inv']) ? absint($_GET['inv']) : 0;
  $ok = pmx_send_pref_nuevos_email($inv_id);
  wp_die($ok ? '✅ Correo enviado.' : '❌ No se pudo enviar (verifica inv_id/email).');
});

/* ========= OPCIONAL: WP-CLI -> wp pmx send-pref-mail 123 ========= */
if (defined('WP_CLI') && WP_CLI) {
  WP_CLI::add_command('pmx send-pref-mail', function($args){
    if (count($args) < 1) WP_CLI::error('Uso: wp pmx send-pref-mail <inv_id>');
    $inv_id = absint($args[0]);
    $ok = pmx_send_pref_nuevos_email($inv_id);
    $ok ? WP_CLI::success('Correo enviado.') : WP_CLI::error('Fallo al enviar.');
  });
}


/* ========== Helper: extraer post_id con logging ========== */
if (!function_exists('pmx_post_id_from_any')) {
  function pmx_post_id_from_any($p) {
    $log = function($m){ if (function_exists('pmx_log')) pmx_log($m); else error_log('[PMX] '.$m); };

    $type = gettype($p);
    $sample = $type==='object' ? ('obj:'.get_class($p)) : substr(@json_encode($p), 0, 120);
    $log('PID_FROM_ANY begin type='.$type.' sample='.$sample);

    if (is_numeric($p)) { $id = (int)$p; $log('PID_FROM_ANY numeric id='.$id); return $id; }

    if (is_object($p)) {
      if (isset($p->ID)) { $id=(int)$p->ID; $log('PID_FROM_ANY object->ID id='.$id); return $id; }
      if ($p instanceof WP_Post) { $id=(int)$p->ID; $log('PID_FROM_ANY WP_Post id='.$id); return $id; }
    }

    if (is_array($p)) {
      foreach (['ID','id','post_id'] as $k) {
        if (isset($p[$k]) && is_numeric($p[$k])) { $id=(int)$p[$k]; $log('PID_FROM_ANY array['.$k.'] id='.$id); return $id; }
      }
      // ACF relación/repeater: puede venir 'proyecto' o 'post' con ID u objeto dentro
      foreach (['proyecto','post','item','value'] as $k) {
        if (isset($p[$k])) {
          $cand = $p[$k];
          if (is_numeric($cand)) { $id=(int)$cand; $log('PID_FROM_ANY array['.$k.'] numeric id='.$id); return $id; }
          if (is_object($cand) && isset($cand->ID)) { $id=(int)$cand->ID; $log('PID_FROM_ANY array['.$k.']->ID id='.$id); return $id; }
          if (is_array($cand) && isset($cand['ID']) && is_numeric($cand['ID'])) { $id=(int)$cand['ID']; $log('PID_FROM_ANY array['.$k.'][ID] id='.$id); return $id; }
        }
      }
    }

    $log('PID_FROM_ANY fail: no pude resolver ID');
    return 0;
  }
}

/* ========== Helper: encontrar alias_proyecto incluso si está en un grupo, con logging ========== */
/* ===== Alias del proyecto según idioma, usando solo 'alias_proyecto' (Polylang si hay traducción) ===== */
/* ===== Alias del proyecto (ES/EN) siguiendo la lógica de títulos EN + logging ===== */
if (!function_exists('pmx_get_alias_proyecto_html')) {
  function pmx_get_alias_proyecto_html($post_id, $language='es') {

    // Logger seguro
    $log = function($msg){
      if (function_exists('pmx_log')) pmx_log($msg); else error_log('[PMX] '.$msg);
    };

    // --- Helpers internos ---
    $trim_nonempty = function($s){ $s = trim((string)$s); return ($s === '') ? '' : $s; };

    // Busca un alias por un conjunto de claves (ACF -> meta -> grupos ACF)
    $resolve_alias_by_keys = function($pid, $keys) use ($trim_nonempty, $log) {
      $found = '';
      if (function_exists('get_field')) {
        foreach ($keys as $k) {
          $v = $trim_nonempty(get_field($k, $pid));
          if ($v !== '') { $found = $v; $log('ALIAS_KEYS ACF pid='.$pid.' key='.$k.' -> found'); return $found; }
        }
      }
      foreach ($keys as $k) {
        $v = $trim_nonempty(get_post_meta($pid, $k, true));
        if ($v !== '') { $found = $v; $log('ALIAS_KEYS META pid='.$pid.' key='.$k.' -> found'); return $found; }
      }
      if (function_exists('get_fields')) {
        $all = get_fields($pid);
        if (is_array($all) && !empty($all)) {
          $set = array(); foreach ($keys as $k) $set[$k] = true;
          $scan = function($arr) use (&$scan, &$set) {
            foreach ($arr as $k=>$v) {
              if (isset($set[$k])) { $vv = trim((string)$v); if ($vv !== '') return $vv; }
              if (is_array($v)) { $res = $scan($v); if ($res !== null && $res !== '') return $res; }
            }
            return '';
          };
          $res = $trim_nonempty($scan($all));
          if ($res !== '') { $log('ALIAS_KEYS GROUP pid='.$pid.' -> found'); return $res; }
        }
      }
      return '';
    };

    // Validación ID
    if (!is_numeric($post_id) || (int)$post_id <= 0) {
      $log('ALIAS_BEGIN pid='.print_r($post_id,true).' | ERROR: post_id inválido');
      return '';
    }
    $post_id = (int)$post_id;

    // Claves a probar (misma lógica que títulos EN)
    $keys_es = array('alias_proyecto');
    $keys_en_samepost = array('alias_proyecto_en','alias_proyecto_ingles','alias_en','alias_ingles');

    $log('ALIAS_BEGIN pid='.$post_id.' lang='.$language.' has_acf='.(function_exists('get_field')?'YES':'NO'));

    // ES base
    $alias_es = $resolve_alias_by_keys($post_id, $keys_es);
    if ($alias_es === '') { $log('ALIAS_ES_EMPTY pid='.$post_id.' | No se encontró alias_proyecto'); }

    // EN (mismo post -> traducción Polylang -> fallback ES)
    $alias_en = '';
    if ($language === 'en') {
      $alias_en = $resolve_alias_by_keys($post_id, $keys_en_samepost);
      if ($alias_en !== '') {
        $log('ALIAS_EN_SAMEPOST pid='.$post_id.' | found in same-post meta');
      } else if (function_exists('pll_get_post')) {
        $en_id = pll_get_post($post_id, 'en');
        if ($en_id && (int)$en_id > 0) {
          $alias_en = $resolve_alias_by_keys((int)$en_id, $keys_es);
          if ($alias_en !== '') $log('ALIAS_EN_POLY pid='.$post_id.' -> en_id='.$en_id.' | found in translation');
        } else {
          $log('ALIAS_EN_POLY pid='.$post_id.' | sin traducción EN, fallback');
        }
      } else {
        $log('ALIAS_EN_POLY pid='.$post_id.' | Polylang no disponible, fallback');
      }
    }

    // Qué pintar
    $alias_raw = ($language === 'en' && $alias_en !== '') ? $alias_en : $alias_es;
    if ($alias_raw === '') {
      $log('ALIAS_EMPTY_FINAL pid='.$post_id.' lang='.$language.' | No hay alias para render');
      return '';
    }

    // URL del proyecto en el idioma solicitado
    $btn_href = get_permalink($post_id);
    if ($language === 'en') {
      if (function_exists('pll_get_post')) {
        $en_id = pll_get_post($post_id, 'en');
        if ($en_id && (int)$en_id > 0) {
          $btn_href = get_permalink((int)$en_id);
          $log('ALIAS_URL pid='.$post_id.' using EN translation url='.$btn_href);
        } else if ($btn_href) {
          $btn_href = add_query_arg('language','en',$btn_href);
          $log('ALIAS_URL pid='.$post_id.' using ?language=en url='.$btn_href);
        }
      } else if ($btn_href) {
        $btn_href = add_query_arg('language','en',$btn_href);
        $log('ALIAS_URL pid='.$post_id.' (no PLL) using ?language=en url='.$btn_href);
      }
    } else {
      $log('ALIAS_URL pid='.$post_id.' using ES url='.$btn_href);
    }

    // Preview logs
    $preview = preg_replace('/\s+/u', ' ', $alias_raw);
    if (function_exists('mb_substr')) {
      $preview = mb_substr($preview, 0, 80).(mb_strlen($preview) > 80 ? '…' : '');
    } else {
      $preview = substr($preview, 0, 80).(strlen($preview) > 80 ? '…' : '');
    }

    // Sanitiza + saltos de línea
    $alias_safe = nl2br(esc_html($alias_raw));

    // ======== OUTLOOK-SAFE MARKUP (tablas) + fondo gris + botón ========
    $label    = ($language === 'en') ? 'PROJECT ALIAS' : 'ALIAS DEL PROYECTO';
    $accent   = '#18AEB3';
    // Botón por idioma:
    $btn_src  = ($language === 'en')
                ? 'https://www.proyectosmexico.gob.mx/wp-content/uploads/2019/02/read_more.png'
                : 'https://www.proyectosmexico.gob.mx/wp-content/uploads/2019/02/leer.png';
    $btn_alt  = ($language === 'en') ? 'Read more' : 'Leer más';

    $html =
      // Espaciador arriba
      '<tr><td height="16" style="line-height:16px;font-size:0;mso-line-height-rule:exactly;">&nbsp;</td></tr>'.

      // Badge centrado
      '<tr>
         <td align="center" style="padding:0 24px;">
           <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;">
             <tr>
               <td bgcolor="'.$accent.'" style="background:'.$accent.';color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:14px;mso-line-height-rule:exactly;text-transform:uppercase;font-weight:bold;padding:6px 12px;">
                 '.$label.'
               </td>
             </tr>
           </table>
         </td>
       </tr>'.

      // Bloque con marco, fondo gris y alias + botón centrados
      '<tr>
         <td align="center" style="padding:8px 24px 0 24px;">
           <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;border:2px solid '.$accent.';">
             <tr>
               <td align="center" bgcolor="#F3F4F6" style="background:#FFFFFF;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;mso-line-height-rule:exactly;color:#243b53;padding:12px 16px;word-wrap:break-word;">
                 '.$alias_safe.'

                 <!-- Espacio antes del botón -->
                 <div style="height:12px;line-height:12px;font-size:0;">&nbsp;</div>

                 <!-- Botón imagen con URL del idioma -->
                 <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;">
                   <tr>
                     <td align="center">
                       <a href="'.esc_url($btn_href).'" target="_blank" style="text-decoration:none;display:inline-block;">
                         <img src="'.$btn_src.'" width="170" height="55" border="0" alt="'.$btn_alt.'" style="display:block;outline:none;text-decoration:none;border:0;height:55px;width:170px;">
                       </a>
                     </td>
                   </tr>
                 </table>

               </td>
             </tr>
           </table>
         </td>
       </tr>'.

      // Espaciador abajo
      '<tr><td height="16" style="line-height:16px;font-size:0;mso-line-height-rule:exactly;">&nbsp;</td></tr>';

    $log('ALIAS_RENDER pid='.$post_id.' lang='.$language.' len='.strlen($alias_raw).' preview="'.$preview.'"');
    return $html;
  }
}


function enviar_desde_pendientes_bd() {
  global $wpdb;
  $tabla = $wpdb->prefix.'bancomext_users_reports'; // AJUSTA
  // Toma pendientes del tipo 'proyectos_nuevos'
  $pend = $wpdb->get_results($wpdb->prepare(
    "SELECT id, email, payload_json
     FROM $tabla
     WHERE tipo = %s AND status = %s",
     'proyectos_nuevos', 'PENDIENTE'
  ));

  foreach ($pend as $row) {
    $email = $row->email;
    // arma $asunto y $html según tu lógica…
    $ok = wp_mail($email, $asunto, $html, $headers);
    if ($ok) {
      $wpdb->update($tabla, ['status'=>'ENVIADO','updated_at'=>current_time('mysql')], ['id'=>$row->id]);
    } else {
      $wpdb->update($tabla, ['status'=>'FALLO_ENVIO','updated_at'=>current_time('mysql')], ['id'=>$row->id]);
    }
  }
}


// ocultar paginas 
// Excluir de resultados de búsqueda estas páginas por slug
add_action('pre_get_posts', function($q){
  if (is_admin() || !$q->is_main_query() || !$q->is_search()) return;

  $slugs = array(
    'proceso-ttl-proyectos-nuevos',
    'proceso-notificacion-diario-de-proyectos-nuevos',
  );

  // Obtiene IDs por slug y también sus traducciones (si usas Polylang)
  $ids = array();
  foreach ($slugs as $slug){
    $p = get_page_by_path($slug, OBJECT, 'page');
    if ($p && !empty($p->ID)) {
      $ids[] = (int)$p->ID;

      // Captura traducciones si existen
      if (function_exists('pll_get_post')) {
        $langs = function_exists('pll_languages_list') ? pll_languages_list() : array();
        foreach ($langs as $l) {
          $tr = pll_get_post($p->ID, $l);
          if ($tr) $ids[] = (int)$tr;
        }
      }
    }
  }

  $ids = array_values(array_unique(array_filter($ids)));
  if ($ids) {
    $not_in = (array) $q->get('post__not_in');
    $q->set('post__not_in', array_unique(array_merge($not_in, $ids)));
  }
});


// marcado de inversionitas
 
/* ================================================
 * Submenú: Preferencia Proyectos Nuevos (Inversionistas)
 * Padre: notification-back-mail-plugin
 * ================================================ */

/* ====== Utilidad central: marcar preferencia faltantes o todos ====== */
if (!function_exists('pmx_backfill_pref_nuevos')) {
  function pmx_backfill_pref_nuevos($force_all=false){
    $added=0; $skipped=0; $updated=0; $acf_key=null;

    if (function_exists('acf_get_field')) {
      $f = acf_get_field('proyectos_nuevos_fin');
      if ($f && !empty($f['key'])) $acf_key = $f['key']; // ej. field_64ab...
    }

    $paged = 1;
    do {
      $q = new WP_Query(array(
        'post_type'      => 'reg_inversionistas',
        'post_status'    => array('draft'),
        'posts_per_page' => 500,
        'fields'         => 'ids',
        'paged'          => $paged,
        'no_found_rows'  => true,
      ));
      if (!$q->have_posts()) break;

      foreach ($q->posts as $id){
        $v = get_post_meta($id, 'proyectos_nuevos_fin', true);
        if ($force_all) {
          update_post_meta($id, 'proyectos_nuevos_fin', '1');
          if ($acf_key) update_post_meta($id, '_proyectos_nuevos_fin', $acf_key);
          $updated++;
        } else {
          if ($v === '' || $v === null){
            update_post_meta($id, 'proyectos_nuevos_fin', '1');
            if ($acf_key) update_post_meta($id, '_proyectos_nuevos_fin', $acf_key);
            $added++;
          } else {
            $skipped++;
          }
        }
      }
      $paged++;
      wp_reset_postdata();
    } while (true);

    return array($added, $skipped, $updated);
  }
}

/* ====== Utilidad: poner TODOS en OFF (o valor dado) ====== */
if (!function_exists('pmx_set_pref_nuevos_all')) {
  function pmx_set_pref_nuevos_all($value='0'){
    $value = ($value === '1') ? '1' : '0';
    $updated = 0; $acf_key = null;

    if (function_exists('acf_get_field')) {
      $f = acf_get_field('proyectos_nuevos_fin');
      if ($f && !empty($f['key'])) $acf_key = $f['key'];
    }

    $paged = 1;
    do {
      $q = new WP_Query(array(
        'post_type'      => 'reg_inversionistas',
        'post_status'    => array('draft'),
        'posts_per_page' => 500,
        'fields'         => 'ids',
        'paged'          => $paged,
        'no_found_rows'  => true,
      ));
      if (!$q->have_posts()) break;

      foreach ($q->posts as $id){
        update_post_meta($id, 'proyectos_nuevos_fin', $value);
        if ($acf_key) update_post_meta($id, '_proyectos_nuevos_fin', $acf_key);
        $updated++;
      }
      $paged++;
      wp_reset_postdata();
    } while (true);

    return $updated;
  }
}

/* ====== Helpers UI ====== */
if (!function_exists('pmx_inv_get_email')) {
  function pmx_inv_get_email($post_id){
    static $cache = array();
    if (isset($cache[$post_id])) return $cache[$post_id];

    // 1) Claves comunes
    $keys = array(
      'email','correo','mail',
      'email_inversionista','correo_inversionista',
      'correo_electronico','correo_registro_reg_inversionista',
      'email_reg_inversionista','mail_reg_inversionista'
    );
    foreach ($keys as $k){
      $v = get_post_meta($post_id, $k, true);
      if (is_string($v) && filter_var($v, FILTER_VALIDATE_EMAIL)) {
        return $cache[$post_id] = $v;
      }
    }
    // 2) Escaneo completo de meta
    $all = get_post_meta($post_id);
    foreach ($all as $k => $vals){
      foreach ((array)$vals as $v){
        if (is_scalar($v)) {
          $s = (string)$v;
          if ($s && filter_var($s, FILTER_VALIDATE_EMAIL)) {
            return $cache[$post_id] = $s;
          }
        } elseif (is_array($v)) {
          foreach ($v as $vv){
            if (is_scalar($vv)) {
              $s = (string)$vv;
              if ($s && filter_var($s, FILTER_VALIDATE_EMAIL)) {
                return $cache[$post_id] = $s;
              }
            }
          }
        }
      }
    }
    // 3) Último recurso: email del autor
    $author_id = (int) get_post_field('post_author', $post_id);
    if ($author_id){
      $u = get_userdata($author_id);
      if ($u && !empty($u->user_email)) {
        return $cache[$post_id] = $u->user_email;
      }
    }
    return $cache[$post_id] = '';
  }
}

if (!function_exists('pmx_nuevos_status')) {
  function pmx_nuevos_status($post_id){
    $v = get_post_meta($post_id, 'proyectos_nuevos_fin', true);
    if ($v === '1' || $v === 1 || $v === true) return '1';     // ON
    if ($v === '0' || $v === 0 || $v === false) return '0';    // OFF
    return ''; // FALTA
  }
}

if (!function_exists('pmx_status_badge')) {
  function pmx_status_badge($status){
    if ($status === '1') {
      return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:#d1fae5;color:#065f46;font-weight:600;">ON</span>';
    } elseif ($status === '0') {
      return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:#fee2e2;color:#991b1b;font-weight:600;">OFF</span>';
    }
    return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:#e5e7eb;color:#374151;font-weight:600;">FALTA</span>';
  }
}

/* ====== AJAX: alternar preferencia con clic en el badge ====== */
add_action('wp_ajax_pmx_toggle_pref_nuevos', 'pmx_ajax_toggle_pref_nuevos');
function pmx_ajax_toggle_pref_nuevos(){
  if ( ! current_user_can('manage_options') ) {
    wp_send_json_error('Permisos insuficientes', 403);
  }
  check_ajax_referer('pmx_inv_nuevos_toggle');

  $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
  if ($post_id <= 0) wp_send_json_error('ID inválido');
  if (get_post_type($post_id) !== 'reg_inversionistas') {
    wp_send_json_error('Tipo inválido');
  }

  $set = isset($_POST['set']) ? (string)$_POST['set'] : '';
  $set = ($set === '1') ? '1' : '0';

  update_post_meta($post_id, 'proyectos_nuevos_fin', $set);

  if (function_exists('acf_get_field')) {
    $f = acf_get_field('proyectos_nuevos_fin');
    if ($f && !empty($f['key'])) {
      update_post_meta($post_id, '_proyectos_nuevos_fin', $f['key']);
    }
  }

  wp_send_json_success(array(
    'status' => $set,
    'badge'  => pmx_status_badge($set),
  ));
}

/* ====== Submenú bajo el mismo padre ====== */
add_action('admin_menu', function(){
  add_submenu_page(
    'notification-back-mail-plugin',     // padre: mismo que tu "Proceso Proyectos Nuevos"
    'Preferencia Proyectos Nuevos',      // título de la página
    'Pref. Proyectos Nuevos',            // título en el menú
    'manage_options',                    // capacidad
    'pmx-inversionistas-nuevos',         // slug
    'pmx_render_pagina_inversionistas_nuevos' // callback
  );
}, 20);

/* ====== Render de la página ====== */
if (!function_exists('pmx_render_pagina_inversionistas_nuevos')) {
  function pmx_render_pagina_inversionistas_nuevos(){
    if (!current_user_can('manage_options')) return;
    global $wpdb;

    $msg = '';

    // Procesar acciones (con nonce)
    if (isset($_POST['pmx_inv_nuevos_nonce']) && wp_verify_nonce($_POST['pmx_inv_nuevos_nonce'], 'pmx_inv_nuevos')) {
      if (isset($_POST['mark_missing'])) {
        list($added, $skipped, $updated) = pmx_backfill_pref_nuevos(false);
        $msg = "Marcados (sin meta previo): <strong>{$added}</strong>. Omitidos (ya tenían valor): <strong>{$skipped}</strong>.";
      } elseif (isset($_POST['mark_all'])) {
        list($added, $skipped, $updated) = pmx_backfill_pref_nuevos(true);
        $msg = "Actualizados a '1' (todos): <strong>{$updated}</strong>.";
      } elseif (isset($_POST['unmark_all'])) {
        $updated = pmx_set_pref_nuevos_all('0');
        $msg = "Actualizados a '0' (todos): <strong>{$updated}</strong>.";
      }
      echo '<div class="updated notice"><p>'.$msg.'</p></div>';
    }

    // Nonce para el toggle AJAX
    $nonce_toggle = wp_create_nonce('pmx_inv_nuevos_toggle');

    // Búsqueda y paginación
    $s      = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $paged  = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $pp     = 50;

    $args = array(
      'post_type'      => 'reg_inversionistas',
      'post_status'    => array('draft'),
      'posts_per_page' => $pp,
      'paged'          => $paged,
      's'              => $s,
      'orderby'        => 'date',
      'order'          => 'DESC',
    );
    $q = new WP_Query($args);

    // Resumen de conteos
    $table_posts = $wpdb->posts;
    $table_meta  = $wpdb->postmeta;
    // Resumen de conteos (sin duplicados y con FALTAN cuando no hay meta o no es '0'/'1')
$sql = "
  SELECT
    SUM(CASE WHEN pm.has1 = 1 THEN 1 ELSE 0 END) AS si,
    SUM(CASE WHEN pm.has1 = 0 AND pm.has0 = 1 THEN 1 ELSE 0 END) AS no,
    SUM(CASE WHEN (IFNULL(pm.has1,0)=0 AND IFNULL(pm.has0,0)=0) THEN 1 ELSE 0 END) AS faltante
  FROM {$table_posts} p
  LEFT JOIN (
    SELECT post_id,
           MAX(CASE WHEN meta_value='1' THEN 1 ELSE 0 END) AS has1,
           MAX(CASE WHEN meta_value='0' THEN 1 ELSE 0 END) AS has0
    FROM {$table_meta}
    WHERE meta_key = 'proyectos_nuevos_fin'
    GROUP BY post_id
  ) pm ON pm.post_id = p.ID
  WHERE p.post_type = 'reg_inversionistas'
    AND p.post_status IN ('draft')
";

    $tot = $wpdb->get_row($sql, ARRAY_A);
    $si  = intval($tot['si']);
    $no  = intval($tot['no']);
    $fa  = intval($tot['faltante']);
    $total_rows = $si + $no + $fa;

    $base_url = remove_query_arg(array('paged'), $_SERVER['REQUEST_URI']);
    ?>
    <div id="pmx-inv-nuevos" class="wrap">
      <h1 style="margin-bottom:10px;">Preferencia “Proyectos nuevos” — Inversionistas</h1>

      <p style="margin:8px 0 16px 0;color:#374151;">
        Total: <strong><?php echo number_format_i18n($total_rows); ?></strong> &nbsp;·&nbsp;
        ON: <strong style="color:#065f46;"><?php echo number_format_i18n($si); ?></strong> &nbsp;·&nbsp;
        OFF: <strong style="color:#991b1b;"><?php echo number_format_i18n($no); ?></strong> &nbsp;·&nbsp;
        FALTAN: <strong style="color:#374151;"><?php echo number_format_i18n($fa); ?></strong>
      </p>

      <form method="get" style="margin-bottom:12px;">
        <input type="hidden" name="page" value="pmx-inversionistas-nuevos">
        <input type="text" name="s" value="<?php echo esc_attr($s); ?>" placeholder="Buscar por título..." class="regular-text">
        <button class="button">Buscar</button>
      </form>

     
	  
	  <form method="post" style="margin:10px 0 18px 0;">
  <?php wp_nonce_field('pmx_inv_nuevos','pmx_inv_nuevos_nonce'); ?>
  <button name="mark_missing" id="pmx-mark-missing" class="button button-primary" style="margin-right:8px;">
    Marcar solo los que faltan
  </button>
  <button name="mark_all" id="pmx-mark-all" class="button" style="margin-right:8px;">
    Marcar TODOS (forzar ON)
  </button>
  <button name="unmark_all" id="pmx-unmark-all" class="button">
    Desmarcar TODOS (forzar OFF)
  </button>
</form>


      <div class="pmx-card">
        <style>
          /* Quitar límites de ancho y usar todo el espacio */
          #pmx-inv-nuevos .pmx-card{ width:100%; max-width:none; background:#fff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
          /* Contenedor con scroll horizontal si hiciera falta */
          #pmx-inv-nuevos .pmx-inv-wrap{ width:100%; overflow-x:auto; }
          /* Tabla a 100% y sin table-layout:fixed (evita apilar letras) */
          #pmx-inv-nuevos .pmx-inv-table{ width:100% !important; table-layout:auto !important; margin:0; }
          #pmx-inv-nuevos .pmx-inv-table th, 
          #pmx-inv-nuevos .pmx-inv-table td{
            white-space:normal !important; word-break:normal !important; overflow-wrap:anywhere; vertical-align:middle;
          }
          /* Anchos sugeridos */
          #pmx-inv-nuevos .pmx-inv-table .col-id{ width:90px; }
          #pmx-inv-nuevos .pmx-inv-table .col-email{ width:28%; }
          #pmx-inv-nuevos .pmx-inv-table .col-status{ width:160px; text-align:center; }
          #pmx-inv-nuevos .pmx-inv-table .col-actions{ width:160px; text-align:center; }
          #pmx-inv-nuevos .pmx-inv-table .col-nombre{ width:auto; }
          #pmx-inv-nuevos{ max-width:none; }
          .pmx-toggle-nuevos { text-decoration:none; cursor:pointer; display:inline-block; }
          .pmx-toggle-nuevos.is-loading { opacity:.6; pointer-events:none; }
        </style>

        <div class="pmx-inv-wrap">
          <table class="wp-list-table widefat striped table-view-list pmx-inv-table">
            <thead>
              <tr>
                <th class="col-id">ID</th>
                <th class="col-nombre">Nombre / Título</th>
                <th class="col-email">Email</th>
                <th class="col-status">Preferencia</th>
                <th class="col-actions">Acceso</th>
              </tr>
            </thead>
            <tbody>
            <?php
            if ($q->have_posts()):
              while ($q->have_posts()): $q->the_post();
                $post_id = get_the_ID();
                $title   = get_the_title();
                $email   = pmx_inv_get_email($post_id);
                $st      = pmx_nuevos_status($post_id);
                ?>
                <tr>
                  <td class="col-id"><?php echo intval($post_id); ?></td>
                  <td class="col-nombre">
                    <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>">
                      <?php echo esc_html($title ?: '(sin título)'); ?>
                    </a>
                  </td>
                  <td class="col-email"><?php echo esc_html($email); ?></td>
                  <td class="col-status">
                    <a href="#"
                       class="pmx-toggle-nuevos"
                       data-post="<?php echo esc_attr($post_id); ?>"
                       data-status="<?php echo esc_attr($st); ?>"
                       data-nonce="<?php echo esc_attr($nonce_toggle); ?>"
                       title="Alternar ON/OFF">
                      <?php echo pmx_status_badge($st); ?>
                    </a>
                  </td>
                  <td class="col-actions">
                    <a class="button button-small" href="<?php echo esc_url(get_edit_post_link($post_id)); ?>">Editar</a>
                    <!-- <a class="button button-small" target="_blank" href="<?php echo esc_url(get_permalink($post_id)); ?>">Ver</a> -->
                  </td>
                </tr>
                <?php
              endwhile;
              wp_reset_postdata();
            else:
              echo '<tr><td colspan="5">Sin resultados.</td></tr>';
            endif;
            ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php if ($q->max_num_pages > 1): ?>
        <div class="tablenav" style="margin-top:10px;">
          <div class="tablenav-pages">
            <?php
            echo paginate_links( array(
              'base'      => add_query_arg('paged','%#%', $base_url),
              'format'    => '',
              'prev_text' => '&laquo;',
              'next_text' => '&raquo;',
              'total'     => $q->max_num_pages,
              'current'   => $paged,
            ) );
            ?>
          </div>
        </div>
      <?php endif; ?>

      <script>
  // Confirmación: "Marcar solo los que faltan"
  document.addEventListener('click', function(e){
    var btn = e.target.closest('#pmx-mark-missing');
    if (!btn) return;
    var msg = 'Esto marcará con ON a todos los inversionistas que NO tienen la preferencia guardada (FALTA).\n' +
              'No cambiará a quienes ya estén ON u OFF.\n\n¿Deseas continuar?';
    if (!confirm(msg)) { e.preventDefault(); }
  });

  // Confirmación: "Marcar TODOS (forzar ON)"
  document.addEventListener('click', function(e){
    var btn = e.target.closest('#pmx-mark-all');
    if (!btn) return;
    var msg = 'Esto pondrá en ON a TODOS los inversionistas (acción masiva),\n' +
              'incluyendo a quienes actualmente están OFF o FALTA.\n\n¿Confirmas?';
    if (!confirm(msg)) { e.preventDefault(); }
  });

  // (ya lo tenías) Confirmación: "Desmarcar TODOS (forzar OFF)"
  document.addEventListener('click', function(e){
    var btn = e.target.closest('#pmx-unmark-all');
    if (!btn) return;
    var ok = confirm('¿Seguro que deseas poner en OFF sin importar el estatus de la preferencia a TODOS los inversionistas? Esta acción afectará a todos.');
    if (!ok) { e.preventDefault(); }
  });

  // (ya lo tenía) Toggle ON/OFF con clic en el badge
  document.addEventListener('click', function(e){
    var a = e.target.closest('.pmx-toggle-nuevos');
    if (!a) return;
    e.preventDefault();

    if (a.classList.contains('is-loading')) return;
    var id    = a.dataset.post;
    var curr  = a.dataset.status || '';
    var nonce = a.dataset.nonce || '';
    var next  = (curr === '1') ? '0' : '1'; // FALTA -> 1

    a.classList.add('is-loading');

    fetch(ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        action: 'pmx_toggle_pref_nuevos',
        post_id: id,
        set: next,
        _ajax_nonce: nonce
      })
    })
    .then(function(r){ return r.json(); })
    .then(function(resp){
      if (resp && resp.success) {
        a.dataset.status = resp.data.status;
        a.innerHTML = resp.data.badge;
      } else {
        alert((resp && resp.data) ? resp.data : 'No se pudo actualizar.');
      }
    })
    .catch(function(){
      alert('Error de red.');
    })
    .finally(function(){
      a.classList.remove('is-loading');
    });
  });
</script>


    </div>
    <?php
  }
}

// ======== BLOQUEO GLOBAL DEL STAMP (solo transiciones válidas lo pueden tocar) ========
if (!function_exists('pmx_protect_stamp_hooks')) {
  function pmx_protect_stamp_hooks(){
    static $on = false; if ($on) return; $on = true;
    $GLOBALS['pmx_allow_stamp'] = array();

    // Bloquea UPDATE si no está autorizado
    add_filter('update_post_metadata', function($check, $object_id, $meta_key, $meta_value, $prev_value){
      if ($meta_key !== PMX_NUEVO_MARKED_META) return $check;
      if (!empty($GLOBALS['pmx_allow_stamp'][$object_id])) return $check; // permitido
      if (function_exists('pmx_log')) pmx_log('BLOCK update nuevo_marked_at pid='.$object_id.' (update no autorizado)');
      return true; // short-circuit: no escribe en DB pero reporta "éxito"
    }, 1, 5);

    // Bloquea DELETE si no está autorizado
    add_filter('delete_post_metadata', function($check, $object_id, $meta_key, $meta_value, $delete_all){
      if ($meta_key !== PMX_NUEVO_MARKED_META) return $check;
      if (!empty($GLOBALS['pmx_allow_stamp'][$object_id])) return $check; // permitido
      if (function_exists('pmx_log')) pmx_log('BLOCK delete nuevo_marked_at pid='.$object_id.' (delete no autorizado)');
      return true; // short-circuit
    }, 1, 5);
  }
  pmx_protect_stamp_hooks();
}

// Helpers para escribir/borrar el stamp con “pase” temporal
if (!function_exists('pmx_set_stamp_guarded')) {
  function pmx_set_stamp_guarded($post_id, $ts){
    $GLOBALS['pmx_allow_stamp'][$post_id] = 1;
    $r = update_post_meta($post_id, PMX_NUEVO_MARKED_META, (int)$ts);
    unset($GLOBALS['pmx_allow_stamp'][$post_id]);
    return $r;
  }
}
if (!function_exists('pmx_del_stamp_guarded')) {
  function pmx_del_stamp_guarded($post_id){
    $GLOBALS['pmx_allow_stamp'][$post_id] = 1;
    $r = delete_post_meta($post_id, PMX_NUEVO_MARKED_META);
    unset($GLOBALS['pmx_allow_stamp'][$post_id]);
    return $r;
  }
}
