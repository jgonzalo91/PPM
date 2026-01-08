<?php
include_once('conexion/conexion.php');
include_once('blob.php');
include_once('menu.php');
include_once('usuario.php');
include_once('utilerias.php');
include_once('permiso.php');
session_start();
$idMenu = 618; //Modulo observatorio
$usuario = desencripta($_SESSION['usuario']);
$idUsuario = desencripta($_SESSION['idUsuario']);
$idArea = desencripta($_SESSION['idArea']);
$isMobile = $_SESSION['esMobil'];
$fuente = "12";
$padding = "20";
$ancho = "50";
if ($isMobile == "1") {
    $fuente = "13";
    $padding = "3";
    $ancho = "100";
}
$p = new permiso();
$per = $p->verificaPermiso($idUsuario, $idMenu);
if ($per->id_permiso == 0) {
    header("Location: index.php");
    exit();
}
$menu = new menu();
$menu->idUsuario = $idUsuario;
$arr = $menu->cargar();
$n = count($arr);
if (!$_SESSION['usuario']) {
    header("Location: index.php");
    exit();
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//SP" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="sp" lang="sp" dir="ltr">
    <head> 

        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta http-equiv="content-language" content="es" />
        <link rel="icon" href="images/favicon.ico" type="image/x-icon" />

        <style>

            .encabezado-fichas{
                background-color: #337ab7;
                color: #fff;
                border-color: #305676;
             }


            .nav-side-menu {
                overflow: auto;
                font-family: verdana;
                font-size: 12px;
                font-weight: 200;
                background-color: #2e353d;
                position: inherit;
                top: 0px;
                width: 100%;
                height: 100%;
                color: #e1ffff;
            }
            .nav-side-menu .brand {
                background-color: #23282e;
                line-height: 50px;
                display: block;
                text-align: center;
                font-size: 14px;
            }
            .nav-side-menu .toggle-btn {
                display: none;
            }
            .nav-side-menu ul,
            .nav-side-menu li {
                list-style: none;
                padding: 0px;
                margin: 0px;
                line-height: 35px;
                cursor: pointer;
            }
            .nav-side-menu ul :not(collapsed) .arrow:before,
            .nav-side-menu li :not(collapsed) .arrow:before {
                font-family: FontAwesome;
                content: "\f078";
                display: inline-block;
                padding-left: 10px; 
                padding-right: 10px;
                vertical-align: middle;
                float: right;
            }
            .nav-side-menu ul .active,
            .nav-side-menu li .active {
                border-left: 3px solid #d19b3d;
                background-color: #4f5b69;
            }
            .nav-side-menu ul .sub-menu li.active,
            .nav-side-menu li .sub-menu li.active {
                color: #d19b3d;
            }
            .nav-side-menu ul .sub-menu li.active a,
            .nav-side-menu li .sub-menu li.active a {
                color: #d19b3d;
            }
            .nav-side-menu ul .sub-menu li,
            .nav-side-menu li .sub-menu li {
                background-color: #181c20;
                border: none;
                line-height: 28px;
                border-bottom: 1px solid #23282e;
                margin-left: 0px;
            }
            .nav-side-menu ul .sub-menu li:hover,
            .nav-side-menu li .sub-menu li:hover {
                background-color: #020203;
            }
            .nav-side-menu ul .sub-menu li:before,
            .nav-side-menu li .sub-menu li:before {
                font-family: FontAwesome;
                content: "\f105";
                display: inline-block;
                padding-left: 10px;
                padding-right: 10px;
                vertical-align: middle;
            }
            .nav-side-menu li {
                padding-left: 0px;
                border-left: 3px solid #2e353d;
                border-bottom: 1px solid #23282e;
            }
            .nav-side-menu li a {
                text-decoration: none;
                color: #e1ffff;
            }
            .nav-side-menu li a i {
                padding-left: 10px;
                width: 20px;
                padding-right: 20px;
            }
            .nav-side-menu li:hover {
                border-left: 3px solid #d19b3d;
                background-color: #4f5b69;
                -webkit-transition: all 1s ease;
                -moz-transition: all 1s ease;
                -o-transition: all 1s ease;
                -ms-transition: all 1s ease;
                transition: all 1s ease;
            }
            @media (max-width: 767px) {
                .nav-side-menu {
                    position: relative;
                    width: 100%;
                    margin-bottom: 10px;
                }
                .nav-side-menu .toggle-btn {
                    display: block;
                    cursor: pointer;
                    position: absolute;
                    right: 10px;
                    top: 10px;
                    z-index: 10 !important;
                    padding: 3px;
                    background-color: #ffffff;
                    color: #000;
                    width: 40px;
                    text-align: center;
                }
                .brand {
                    text-align: left !important;
                    font-size: 22px;
                    padding-left: 20px;
                    line-height: 50px !important;
                }
            }
            @media (min-width: 767px) {
                .nav-side-menu .menu-list .menu-content {
                    display: block;
                }
            }

        </style>   
        <script src="js/jquery.min.js" type="text/javascript"></script>  
        
        <script src="./js/amchart/core.js"></script>
        <script src="./js/amchart/charts.js"></script>
        <script src="./js/amchart/themes/animated.js"></script>
        <link rel="stylesheet" id="bootstrap-css" href="/wp-content/themes/enfold-child/sostenibilidad/Chart.min.css" type="text/css">
        <script type="text/javascript" src="/wp-content/themes/enfold-child/sostenibilidad/Chart.min.js"></script>
        <script src="./fichas/fichas.js"></script> 
    </head>       
    <body onload="buscarFicha('%')" >
        <?php
        if (rtrim($isMobile == "0"))
            include_once('head.php');
        else
            include_once('headM.php');
        ?>
        <form id="formulario" name="formulario">
        <table style=" width: 100%; background: rgba(255, 255, 255, 0.5) url('images/fondoHD_10.png'); background-position: 0 100%; background-repeat: no-repeat; background-size: 100% 100%;">
            <tr>
                <td  style=" width: 15%; vertical-align: top; height: 800px; background-color: #F2F2F2; color:black;line-height:30px; ">
                    <div class="nav-side-menu">
                        <div class="brand">Menu</div>
                        <i class="fa fa-bars fa-2x toggle-btn" data-toggle="collapse" data-target="#menu-content"></i>
                        <div class="menu-list">
                            <ul id="menu-content" class="menu-content collapse out">
                                <li><a href='index3.php'><i class='icon-home'></i> Home</a></li>
                                <?php
                                // Cargar Menus del usuario
                                $seleccionado = "";
                                for ($i = 0; $i < $n; $i++) {
                                    echo "<li data-toggle='collapse' data-target='#" . $arr[$i]->descripcion . "' class='collapsed active'><a href='#'><i class='" . $arr[$i]->img . "'></i> " . $arr[$i]->descripcion . "<span class='arrow'></span></a></li>";
                                    echo"<ul class='sub-menu collapse' id='" . $arr[$i]->descripcion . "'>";
                                    $menu->idUsuario = $idUsuario;
                                    $arr_hijos = $menu->cargarHijosXusuario($arr[$i]->id);
                                    $nn = count($arr_hijos);
                                    for ($j = 0; $j < $nn; $j++) {
                                        if ($arr_hijos[$j]->id == $idMenu)
                                            $seleccionado = " class='active' ";
                                        else
                                            $seleccionado = "";
                                        if ($arr[$i]->id == 3)
                                            echo"<li " . $seleccionado . "><a href='" . $arr_hijos[$j]->url . "?je=" . encripta($arr_hijos[$j]->id) . "'><span class='" . $arr_hijos[$j]->img . "'></span> " . $arr_hijos[$j]->descripcion . " </a></li>";
                                        else
                                            echo"<li " . $seleccionado . "><a href='" . $arr_hijos[$j]->url . "'><span class='" . $arr_hijos[$j]->img . "'></span> " . $arr_hijos[$j]->descripcion . " </a></li>";
                                    }
                                    echo " </ul>";
                                }
                                ?>
                                <li><a href='exit.php'><i class='icon-exit'></i> Cerrar sesi&oacute;n</a></li>
                            </ul>      
                        </div>
                    </div>
                </td>
                <td width="85%" style=" vertical-align: top; text-align: left;">
                    <input type="hidden" name="id" id="id" value="0">
                    <table width="100%" style="background: -webkit-linear-gradient(left, rgba(0, 0, 0,.85) 0%, rgba(0, 0, 0,.5) 50%, rgba(0, 0, 0,1) 100%);">
                        <tr style="color: white;">
                            <td style="font-size: 16px;padding-left:20px; height: 40px;" >
                                <span class=" icon-database"></span>&nbsp;Fichas alineación ODS
                            </td>
                        </tr>
                    </table> 
                    <div id="contenedor" style=" width: 98%; text-align: center;"></div>     
                </td>
            </tr> 
        </table>
        <div id="ModalListaFicha" style="display: none;"style=" background-image: url('images/logo_opaco.png'); background-size: 25%; background-repeat: no-repeat; background-position: 80% 70%">
            <table width="100%;">
                <tr>
                    <td width="100%" align="center" style="font-family:Verdana, Geneva, sans-serif; font-size:10px; background-color:white; color:#466CAC;">
                        Por favor espere un momento <br /> cargando informacion ...<br /><br />
                        <img src="images/loading.gif" style="width: 30%"/>
                    </td>
                </tr>
            </table>
        </div>
        <div id="ModalComentariosEvidencia" style="display: none;"style=" background-image: url('images/logo_opaco.png'); background-size: 25%; background-repeat: no-repeat; background-position: 80% 70%">
            <table width="100%;">
                <tr>
                    <td width="100%" align="center" style="font-family:Verdana, Geneva, sans-serif; font-size:10px; background-color:white; color:#466CAC;">
                        Por favor espere un momento <br /> cargando informacion ...<br /><br />
                        <img src="images/loading.gif" style="width: 30%"/>
                    </td>
                </tr>
            </table>
        </div>
        <!-- proceso -->
        <div class="modal fade" id="proceso" tabindex="-1" role="dialog" aria-labelledby="proceso" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document" style="color:#FFF">
          <div id="proceso-contenido" style="text-align: center;">
            <i class="fa fa-spinner fa-5x fa-spin" style="font-size:150px"></i>
            <h3>Generaci&oacute;n PDF</h3>
            <h2 id="proceso-porcentaje" style="margin-top: 20px; font-size: 48px; font-weight: bold;">0%</h2>
            <div id="proceso-mensaje-paso" style="margin-top: 10px; font-size: 16px; opacity: 0.9;">Iniciando proceso...</div>
          </div>
          </div>
        </div>
        <canvas id="canvas" class="" width="600" height="300" style="display:none;background-color: rgba(0, 0, 0, 0.500);"></canvas>
        <canvas id="canvas_en" class="" width="600" height="300" style="display:none;background-color: rgba(0, 0, 0, 0.500);"></canvas>
        </form>
    <?php include_once('pie.php'); ?> 
    </body>
</html>
