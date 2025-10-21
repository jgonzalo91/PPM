<?php
/**
 * BMxT_TTL_New_Projects (compat PHP 5.4/7.0)
 * Runner por shortcode para desmarcar "nuevo" (sin WP-Cron).
 * - Normal: usa TTL desde ?value=... o ?ttl=...
 * - Inmediato: ?immediate=1 (opcionalmente ?ids=1,2,3 para filtrar).
 */

/*
Ejemplos de uso

Por defecto (si usas [bmxt_nuevos_ttl default="5m"]):
https://tu-dominio/proceso-nuevos/

Sobrescribir a 3 minutos:
https://tu-dominio/proceso-nuevos/?value=3

Pasar segundos explícitos (300 = 5 min):
https://tu-dominio/proceso-nuevos/?ttl=300

Inmediato (todos los “nuevo=1”):
https://tu-dominio/proceso-nuevos/?immediate=1

Inmediato solo para ciertos IDs:
https://tu-dominio/proceso-nuevos/?immediate=1&ids=101,202,303
*/

if (!class_exists('BMxT_TTL_New_Projects')) {
  class BMxT_TTL_New_Projects {
    const SHORTCODE = 'bmxt_nuevos_ttl';

    public static function init() {
      add_shortcode(self::SHORTCODE, array(__CLASS__, 'shortcode'));
    }

    // Convierte value/ttl a segundos; límites 60..86400
    protected static function parse_ttl($value, $defaultAttr) {
      // 1) ttl en segundos tiene prioridad
      if (isset($_GET['ttl']) && is_numeric($_GET['ttl'])) {
        $ttl = (int) $_GET['ttl'];
      } else {
        $raw = trim((string)($value !== null ? $value : ''));
        if ($raw === '' && $defaultAttr) $raw = trim((string)$defaultAttr);

        $ttl = 0;
        if ($raw !== '') {
          // Soporta: 30m, 2h, 3600s, 1d, 0.5h, "45" (minutos)
          if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*([smhd]?)\s*$/i', $raw, $m)) {
            $num = (float)$m[1];
            $u   = strtolower(isset($m[2]) ? $m[2] : '');
            $factor = 60; // por defecto: minutos
            if ($u === 's') $factor = 1;
            elseif ($u === 'm' || $u === '') $factor = 60;
            elseif ($u === 'h') $factor = 3600;
            elseif ($u === 'd') $factor = 86400;
            $ttl = (int) round($num * $factor);
          }
        }
      }

      if ($ttl <= 0) {
        $ttl = defined('PMX_NUEVO_TTL') ? (int)PMX_NUEVO_TTL : 1800; // 30 min
      }
      if ($ttl < 60) $ttl = 60;
      if ($ttl > 86400) $ttl = 86400;
      return $ttl;
    }

    // Lógica principal por TTL (backfill + auto_unmark)
    protected static function run_core($ttl) {
      if (function_exists('pmx_log')) pmx_log('[UNMARK] START ttl='.$ttl);
      if (function_exists('pmx_backfill_marked_at')) pmx_backfill_marked_at();
      $expired = 0;
      if (function_exists('pmx_auto_unmark_expired_nuevos')) {
        $expired = (int) pmx_auto_unmark_expired_nuevos($ttl);
      }
      if (function_exists('pmx_log')) pmx_log('[UNMARK] END expired='.$expired);
      return array($ttl, $expired);
    }

    // Inmediato por IDs concretos (si vienen) o sobre todos (TTL=0)
    protected static function run_immediate($ids_csv) {
      $affected = 0;
      $scope = 'all';
      $details = '';

      // Si vienen IDs -> desmarca solo esos
      $ids_csv = trim((string)$ids_csv);
      if ($ids_csv !== '') {
        $ids_set = array();
        $parts = preg_split('/[\s,;]+/', $ids_csv);
        foreach ($parts as $p) {
          $n = (int)$p;
          if ($n > 0) $ids_set[$n] = 1;
        }
        $ids = array_keys($ids_set);

        if (!empty($ids)) {
          $scope = 'ids';
          foreach ($ids as $pid) {
            // Solo si actualmente está marcado como nuevo
            $cur = get_post_meta($pid, defined('PMX_NUEVO_META') ? PMX_NUEVO_META : 'nuevo', true);
            $is_on = ($cur==='1' || $cur===1 || $cur===true || $cur==='true');
            if ($is_on) {
              update_post_meta($pid, defined('PMX_NUEVO_META') ? PMX_NUEVO_META : 'nuevo', '0');
              delete_post_meta($pid, defined('PMX_NUEVO_MARKED_META') ? PMX_NUEVO_MARKED_META : 'nuevo_marked_at');
              $affected++;
              if (function_exists('pmx_log')) pmx_log('[UNMARK][NOW] pid='.$pid.' -> off');
            }
          }
          $details = ' ids='.implode(',', $ids);
        }
      }

      // Si no hubo IDs válidos, aplica sobre todos: TTL=0 => unmark inmediato
      if ($scope === 'all') {
        // TTL=0 => cut = now => desmarca todos los "nuevo=1"
        $pair = self::run_core(0);
        $affected = isset($pair[1]) ? (int)$pair[1] : 0;
      }

      return array($scope, $affected, $details);
    }

    // Shortcode handler
    public static function shortcode($atts) {
      $atts = shortcode_atts(array(
        'default' => '',   // TTL por defecto si no viene ?value ni ?ttl
      ), $atts, self::SHORTCODE);

      if (!headers_sent()) {
        nocache_headers();
        header('Content-Type: text/plain; charset=UTF-8');
        header('X-Robots-Tag: noindex, nofollow', true);
      }

      // ¿Ejecución inmediata?
      $immediate = isset($_GET['immediate']) && ($_GET['immediate'] === '1' || strtolower($_GET['immediate']) === 'true');

      if ($immediate) {
        $ids = isset($_GET['ids']) ? $_GET['ids'] : '';
        $res = self::run_immediate($ids);
        $scope    = $res[0];
        $affected = $res[1];
        $details  = $res[2];

        // Texto de salida
        if ($scope === 'ids') {
          return '<pre>[PMX] OK immediate unmark (scope=ids) affected='.$affected.$details.'</pre>';
        } else {
          return '<pre>[PMX] OK immediate unmark (scope=all) affected='.$affected.'</pre>';
        }
      }

      // Modo normal por TTL
      $value = isset($_GET['value']) ? (string)$_GET['value'] : '';
      $ttl   = self::parse_ttl($value, $atts['default']);

      try {
        list($ttlEff, $expired) = self::run_core($ttl);
        return '<pre>[PMX] OK backfill+auto_unmark  ttl='.$ttlEff.'  expired='.$expired.'</pre>';
      } catch (Exception $e) {
        if (function_exists('error_log')) error_log('[PMX] Shortcode runner error: '.$e->getMessage());
        return "<pre>[PMX] 500 Error interno en runner</pre>";
      }
    }
  }

  add_action('init', array('BMxT_TTL_New_Projects', 'init'));
}
