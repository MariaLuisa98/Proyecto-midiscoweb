<?php

include_once 'config.php';
include_once 'util.php';
include_once 'Usuario.php';

class modeloUserDB {
    
    private static $db=null;
    private static $consulta_user="select * from Usuarios where user=?";
    private static $consulta_email="select email from Usuarios where user=?";
    private static $delete="delete from Usuarios where id=?";
    private static $insert="insert into Usuarios (user,clave,nombre,email,plan,estado) values (:user,:clave,:nombre,:email,:plan,:estado)";
    private static $update="update Usuarios set clave=?,nombre=?,email=?,plan=?,estado=? where user=?";

public static function init() {
    
    if(self::$db==null) {
        try {
            $dsn="msql:host=192.168.1.132;dbname=midiscoweb;charset=utf8";
            self::$db=new PDO($dsn,'root','root');
            self::$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $m) {
            echo "Error de conexión ".$m->getMessage();
            exit();
        }
    }
}

// Comprueba usuario y contraseña son correctos (boolean)
public static function modeloOkUser($user,$clave){
   $stmt=self::$db->prepare(self::$consulta_user);
   $stmt->bindValue(1,$user);
   $stmt->execute();
   if ($stmt->rowCount()>0) {
       $stmt->setFetchMode(PDO::FETCH_ASSOC);
           return true;
   }
   return false;
}


public static function modeloExisteID(String $user):bool{
    $stmt=self::$db->prepare(self::$consulta_user);
    $stmt->bindValue(1,$user);
    $stmt->execute();
    if ($stmt->rowCount()>0) {
        return true;
    }
    return false;
}

public static function modeloGetEmail(String $user){
    $stmt=self::$db->prepare(self::$consulta_user);
    $stmt->bindValue(1,$user);
    $stmt->execute();
    if ($stmt->rowCount()>0) {
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $fila=$stmt->fetch();
        return $fila["email"];
    }
    return "Consulta errónea";
}


/*
 * Chequea si hay error en el datos antes de guardarlos
 */
public static function modeloErrorValoresAlta ($user,$clave1, $clave2, $nombre, $email, $plan, $estado){
    if ( self::modeloExisteID($user))                         return "El ID ya existe";
    if ( preg_match("/^[a-zA-Z0-9]+$/", $user) == 0)          return "El ID debe contener letras y números";
    if ( $clave1 != $clave2 )                                 return "Las contraseñas no son iguales";
    if ( !self::modeloEsClaveSegura($clave1) )                return "Contraseña no segura";
    if ( !filter_var($email, FILTER_VALIDATE_EMAIL))          return "Email incorrecto";
    if ( self::modeloExisteEmail($email))                     return "Email repetido";
    return false;
}

public static function modeloErrorValoresModificar($user, $clave1, $clave2, $nombre, $email, $plan, $estado){
    
    if ( $clave1 != $clave2 )                                 return "Las contraseñas no son iguales";
    if ( !self::modeloEsClaveSegura($clave1) )                return "Contraseña no segura";
    if ( !filter_var($email, FILTER_VALIDATE_EMAIL))          return "Email incorrecto";
    // SI se cambia el email
    $emailantiguo = self::modeloGetEmail($user);
    if ( $email != $emailantiguo && self::modeloExisteEmail($email))   return "Email repetido";
    return false;
}

/*
 * Comprueba que la contraseña es segura
 */

public static function modeloEsClaveSegura (String $clave):bool {
    if ( empty($clave))         return false;
    if (  strlen($clave) < 8 )  return false;
    if ( !hayMayusculas($clave) || !hayMinusculas($clave)) return false;
    if ( !hayDigito($clave))         return false;
    if ( !hayNoAlfanumerico($clave)) return false;
    
    return true;
}


/*
 * Comprueba si un correo existe
 */
public static function modeloExisteEmail( String $email):bool{
    $stmt=self::$db->prepare(self::consulta_email);
    $stmt->bindValue(1,$email);
    $stmt->execute();
    modeloUserDB::Init();
}

// Devuelve el plan de usuario (String)
public static function modeloObtenerTipo($user){
    $stmt=self::$db->prepare(self::$consulta_user);
    $stmt->bindValue(1,$user);
    $stmt->execute();
    if ($stmt->rowCount()>0) {
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $fila=$stmt->fetch();
        $plancod=$fila["plan"];
        return PLANES[$plancod];
    }
    return "Consulta fallida";
}

// Borrar un usuario (boolean)
function modeloUserDel($userid){
    $stmt=self::$db->prepare(self::$delete);
    $stmt->bindValue(1,$userid);
    $stmt->execute();
    if ($stmt->rowCount()>0) {
         return true;
    }
    return false;
}
// AÃ±adir un nuevo usuario (boolean)
public static function modeloUserAdd($userid, $userdat){
    $user=new Usuario();
    $user->id=$userid;
    $user->clave=$userdat[0];
    $user->nombre=$userdat[1];
    $user->email=$userdat[2];
    $user->plan=$userdat[3];
    $user->estado=$userdat[4];
    $user=self::$db->prepare(self::$insert);
    $user->bindValue(":user",$user0->user);
    $user->bindValue(":clave",$user0->clave);
    $user->bindValue(":nombre",$user0->nombre);
    $user->bindValue(":email",$user0->email);
    $user->bindValue(":plan",$user0->plan);
    $user->bindValue(":estado",$user0->estado);
    
    if ($stmt->execute()) {
        return true;
    }
    
}

// Actualizar un nuevo usuario (boolean)
public static function modeloUserUpdate ($userid, $userdat){
    $stmt=self::$db->prepare(self::$update);
    
    $stmt->bindValue(1,$userdat[0]);
    $stmt->bindValue(2,$userdat[1]);
    $stmt->bindValue(3,$userdat[2]);
    $stmt->bindValue(4,$userdat[3]);
    $stmt->bindValue(5,$userdat[4]);
    $stmt->bindValue(6,$userid);
    if($stmt->execute()) {
        return true;
    }
    return false;
}


// Tabla de todos los usuarios para visualizar
public static function modeloUserGetAll ():array {
    // Genero lo datos para la vista que no muestra la contraseÃ±a ni los cÃ³digos de estado o plan
    // sino su traducción a texto
    $stmt=self::$db->query("select * from Usuarios");
    $usuarios=[];
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    while ($cod=$stmt->fetch()) {
        $datosuser= [
            $cod["nombre"],
            $cod["email"],
            PLANES[$cod["plan"]],
            ESTADOS[$cod["estados"]]
        ];
        $usuarios[$cod["user"]]=$datosuser;
    }
    return $usuarios;
}



// Datos de un usuario para visualizar
public static function modeloUserGet ($userid){
   $datosuser=[];
   $stmt=self::$db->prepare(self::$consulta_user);
   $stmt->bindValue(1,$userid);
   $stmt->execute();
   if ($stmt->rowCount()>0) {
       $stmt->setFetchMode(PDO::FETCH_CLASS,"Usuario");
       $cod=$stmt->fetch();
       $datosuser=[
           $cod->clave,
           $cod->nombre,
           $cod->email,
           $cod->plan,
           $cod->estado
       ];
       return $datosuser;
   }
   return null;
}

public static function closeDB() {
    self::$db=null;
}
}