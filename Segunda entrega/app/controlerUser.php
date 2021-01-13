<?php
// ------------------------------------------------
// Controlador que realiza la gestión de usuarios
// ------------------------------------------------

include_once 'config.php';
include_once 'modeloUserDB.php';


/*
 * Inicio Muestra o procesa el formulario (POST)
 */

function  ctlUserInicio(){
    $msg = "";
    $user ="";
    $clave ="";
    if ( $_SERVER['REQUEST_METHOD'] == "POST"){
        if (isset($_POST['user']) && isset($_POST['clave'])){
            $user =$_POST['user'];
            $clave=$_POST['clave'];
            if ( modeloUserDB::modeloOkUser($user,$clave)){
                $_SESSION['user'] = $user;
                $_SESSION['tipouser'] = modeloUserDB::modeloObtenerTipo($user);
                if ( $_SESSION['tipouser'] == "M�ster"){
                    $_SESSION['modo'] = GESTIONUSUARIOS;
                    header('Location:index.php?orden=VerUsuarios');
                    return;
                }
                else {
                  // Usuario normal;
                  // PRIMERA VERSI�N SOLO USUARIOS ADMISTRADORES
                    $msg="Error: Acceso solo permitido a usuarios Administradores.";
                  unset( $_SESSION['user']);
                  // SEGUNDA VERSION PERMITE LOS USUARIOS GESTION SUS FICHEROS
                  // Cambio de modo y redireccion a verficheros
                  //$_SESSION['modo'] = GESTIONFICHEROS;     
                  // header('Location:index.php?operacion=VerFicheros');
                }
            }
            else {
                $msg="Error: usuario y contrase�a no v�lidos.";
           }  
        }
    }
    
    include_once 'plantilla/formAcceso.php';
}

/*
 *  Muestra y procesa el formulario de alta (ADMINISTRADOR)
 */
function ctlUserAlta (){
    $user="";
    $nombre="";
    $clave1="";
    $clave2="";
    $email="";
    $plan="";
    $estado="";
    
    // Si hay que procesar el formulario
    if ( $_SERVER['REQUEST_METHOD'] == "POST"){
        //var_dump($_POST);
        
        if (empty($_POST['user'])  || empty($_POST['nombre']) ||
            empty($_POST['clave1'])|| empty($_POST['clave2']) || 
            empty($_POST['email']) || !isset($_POST['plan'])   ||  !isset($_POST['estado']) ){
                $msg ='NOVACIO';    
        } else {
           
        $user =   $_POST['user'];
        $nombre = $_POST['nombre'];
        $clave1 = $_POST['clave1'];
        $clave2 = $_POST['clave2'];
        $email  = $_POST['email'];
        $plan   = $_POST['plan'];
        $estado = $_POST['estado'];
        $msg = modeloUserDB::modeloErrorValoresAlta($user, $clave1, $clave2, $nombre, $email, $plan, $estado);
        if ( !$msg ){
            if ( modeloUserDB::modeloUserAdd($user, [$clave1,$nombre,$email,$plan,$estado]) ){
            // OJO EL NOMBRE DE $user
            mkdir("./app/dat/".$user);
            $msg=" Nuevo Usuario almacenado.";  
            header('Location:index.php?orden=VerUsuarios&msg='.urlencode($msg));
            return;
            }
            else {
                $msg="Error: No se puede a�adir el usuario";
            }
       }   
     }
    }
    include_once 'plantilla/fnuevo.php';
}
/*
 *  Muestra y procesa el formulario de Modificaci�n (ADMINISTRADOR)
 */
function ctlUserModificar (){
    
    if ( $_SERVER['REQUEST_METHOD'] == "GET"){
        $datosuser = modeloUserDB::modeloUserGet($_GET['userid']);
        //var_dump($datosuser);
        $user=$_GET['userid'];
        $clave1=$datosuser[0];
        $clave2=$datosuser[0];
        $nombre=$datosuser[1];
        $email =$datosuser[2];
        $plan  =$datosuser[3];
        $estado=$datosuser[4];
    }
    // Si hay que procesar el formulario
    if ( $_SERVER['REQUEST_METHOD'] == "POST"){
        
        if (empty($_POST['user'])  || empty($_POST['nombre']) ||
            empty($_POST['clave1'])|| empty($_POST['clave2']) ||
            empty($_POST['email']) || !isset($_POST['plan'])   ||  !isset($_POST['estado']) ){
                $msg ='NOVACIO';
        } else {
            
            $user =   $_POST['user'];
            $nombre = $_POST['nombre'];
            $clave1 = $_POST['clave1'];
            $clave2 = $_POST['clave2'];
            $email  = $_POST['email'];
            $plan   = $_POST['plan'];
            $estado = $_POST['estado'];

            // ERROR al fallo correo electronico
            $msg = modeloUserDB::modeloErrorValoresModificar($user, $clave1, $clave2, $nombre, $email, $plan, $estado);
            if ( !$msg ){
                if ( modeloUserDB::modeloUserUpdate($user, [$clave1,$nombre,$email,$plan,$estado]) ){
                $msg=" Se han modificado los datos del Usuario $user";
                header('Location:index.php?orden=VerUsuarios&msg='.urlencode($msg));
                return;
            }
            else {
                $msg="Error al modificar el usuario $user ";
            }
          }
        }
    }
    include_once 'plantilla/fmodifica.php';
}



function ctlUserRegistroUsuario(){
    $user="";
    $nombre="";
    $clave1="";
    $clave2="";
    $email="";
    $plan="";
    $estado="I"; // Estado Inactivo
    
    // Si hay que procesar el formulario
    if ( $_SERVER['REQUEST_METHOD'] == "POST"){
        //var_dump($_POST);
        
        if (empty($_POST['user'])  || empty($_POST['nombre']) ||
            empty($_POST['clave1'])|| empty($_POST['clave2']) ||
            empty($_POST['email']) || !isset($_POST['plan'])  ){
                $msg ='NOVACIO';
        } else {
            
            $user =   $_POST['user'];
            $nombre = $_POST['nombre'];
            $clave1 = $_POST['clave1'];
            $clave2 = $_POST['clave2'];
            $email  = $_POST['email'];
            $plan   = $_POST['plan'];
  
            $msg = modeloUserDB::modeloErrorValoresAlta($user, $clave1, $clave2, $nombre, $email, $plan, $estado);
            if ( !$msg ){
                if ( modeloUserDB::modeloUserAdd($user, [$clave1,$nombre,$email,$plan,$estado]) ){
                    $msg="Usuario registrado. Introduzca sus datos";
                    header('Location:index.php?orden=Inicio&msg='.urlencode($msg));
                    return;
                }
                else {
                    $msg= "Error: No se puede a�adir el usuario";
                }
            }
        }
    }
    include_once 'plantilla/fregistro.php';
}




function ctlUserDetalles(){
    $datosuser = modeloUserDB::modeloUserGet($_GET['userid']);
    $userid=$_GET['userid'];
    $nombre=$datosuser[1];
    $email =$datosuser[2];
    $plancod =$datosuser[3];
    $estadocod=$datosuser[4];
    //Datos de prueba 
    $plan  =PLANES[$plancod];
    $estado=ESTADOS[$estadocod];
    
    include_once 'plantilla/detalle.php';
    
}
/*
 * Borrar usuarios
 */

function ctlUserBorrar(){
    $userid=$_GET['userid'];
    // Chequear error si no existe GET
    if ( modeloUserDel($userid)){
        $msg=" Usuario eliminado.";   
    }
    else {
        $msg="Error no se puede eliminar el usuario.";
    }
    header('Location:index.php?orden=VerUsuarios&msg='.urlencode($msg));
}

/*
 * Cierra la sesi�n y vuelca los datos
 */
function ctlUserCerrar(){
    session_destroy();
    modeloUserDB::modeloUserSave();
    header('Location:index.php');
}

/*
 * Muestro la tabla con los usuario 
 */ 
function ctlUserVerUsuarios (){
    // Obtengo los datos del modelo
    $usuarios = modeloUserDB::modeloUserGetAll(); 
    // Invoco la vista 
    include_once 'plantilla/verusuarios.php';
   
}