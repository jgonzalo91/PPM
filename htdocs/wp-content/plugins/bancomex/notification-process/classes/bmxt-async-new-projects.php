<?php
if ( ! class_exists( 'BMXT_Async_New_Projects' ) ) {

  // Igual que BMXT_Async_Preferences, pero para “proyectos nuevos”
  class BMXT_Async_New_Projects extends BMXT_Async_Task {

    protected $prefix = 'bmxt';       // mismo prefijo que el resto
    protected $action = 'async_new_projects';

    protected function handle() {

      // Evita solapes (3 min)
      $lock_key = 'pmx_notify_lock';
      if ( get_transient( $lock_key ) ) {
        if ( function_exists('pmx_log') ) pmx_log('[NUEVOS][ASYNC] LOCK activo, omitiendo');
        return;
      }
      set_transient( $lock_key, 1, 180 );

      try {
        if ( function_exists('pmx_log') ) pmx_log('[NUEVOS][ASYNC] iniciar');

        if ( function_exists('preparar_notificaciones_proyectos_nuevos') ) {
          preparar_notificaciones_proyectos_nuevos('append');
        }
        if ( function_exists('enviar_notificaciones_proyectos_nuevos') ) {
          enviar_notificaciones_proyectos_nuevos();
        }

        if ( function_exists('pmx_log') ) pmx_log('[NUEVOS][ASYNC] OK');
      } catch ( Exception $e ) {
        error_log('[BMXT][NUEVOS][ASYNC] '.$e->getMessage());
        if ( function_exists('pmx_log') ) pmx_log('[NUEVOS][ASYNC][ERR] '.$e->getMessage());
      }

      delete_transient( $lock_key );
    }
  }
}
