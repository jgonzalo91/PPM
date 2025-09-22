<?php
if ( ! class_exists( 'BMXT_Main_New_Projects' ) ) {

  class BMXT_Main_New_Projects {

    protected $async_process_new;

    public function __construct() {
      $this->async_process_new = new BMXT_Async_New_Projects();
      // Shortcode a usar en la página:
      // [notification_process_new_projects value="3"]
      add_shortcode( 'notification_process_new_projects', array( $this, 'start_process_new' ) );
    }

    public function start_process_new() {
      $value = isset($_GET['value']) ? $_GET['value'] : null;

      if ( $value ) {
        if ( function_exists('pmx_log') ) pmx_log('[NUEVOS][SC] dispatch value='.$value);
        $this->async_process_new->dispatch();
        return '<h1>Proceso Iniciado Proyectos Nuevos</h1>';
      }

      // Mantén el mismo comportamiento que “preferences” (sin value no hace nada)
      return '';
    }
  }

  // Bootstrap como en bmxt_main_preferences()
  function bmxt_main_new_projects() {
    global $bmxt_main_new_projects;
    if ( ! isset( $bmxt_main_new_projects ) ) {
      $bmxt_main_new_projects = new BMXT_Main_New_Projects();
    }
    return $bmxt_main_new_projects;
  }

  bmxt_main_new_projects();
}
