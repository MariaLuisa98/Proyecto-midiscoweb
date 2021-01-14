<?php

define ('GESTIONUSUARIOS','1');



// Fichero donde se guardan los datos
define('FILEUSER','app/dat/Usuarios.sql');
// Ruta donde se guardan los archivos de los usuarios
// Tiene que tener permiso 777 o permitir a Apache rwx
define('RUTA_FICHEROS','app/dat');
//  Estado: (A-Activo | B-Bloqueado |I-Inactivo )
const  ESTADOS = ['A' => 'Activo','B' =>'Bloqueado', 'I' => 'Inactivo']; 
// (0-BÃ¡sico |1-Profesional |2- Premium| 3- MÃ¡ster)
const  PLANES = ['Básico','Profesional','Premium','Máster'];

const NOVACIO   = "Rellenar todos los campos";

    
// Definir otras constantes 