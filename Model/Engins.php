<?php
require_once 'DB.php';
class Engins
{
    public function construct(){}
    //Recuperation des engins de la BDD ebrigade
    static public function getEngins()
    {
        $stmt=DB::connect()->prepare('SELECT TV_LIBELLE, TV_CODE FROM  type_vehicule');
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->closeCursor();
        $stmt=null;

    }
    //Recuperation des roles des pompiers de chaque engins depuis BDD ebrigade
    static public function getRoleEngins($i)
    {
        $stmt=DB::connect()->prepare('SELECT * FROM  type_vehicule_role WHERE TV_CODE ="'.$i.'"');
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->closeCursor();
        $stmt=null;
    }
    
    static public function getEngin($i)
    {
        $stmt=DB::connect()->prepare('SELECT TV_CODE, TV_LIBELLE FROM  type_vehicule WHERE TV_LIBELLE ="'.$i.'"');
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->closeCursor();
        $stmt=null;
    }
}
//$resp=Engins::getEngins();
//echo (json_encode($resp));