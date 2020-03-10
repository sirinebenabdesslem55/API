<?php
require_once 'C:/wamp64/www/api/Model/Engins.php';
class Engin{

    //recuperation de la liste des engins
    static public function getAll(){
        $response = Engins::getAllEngins();
        
        //die(print_r($Role));
        return json_encode($response,true);
    }

    static public function getAllRoles(){
        $Role=array();
        $response = Engins::getAllEngins();
        foreach($response as $e){
            $Var= Engins::getRoleEngins($e['TV_CODE']);
            array_push($Role, $Var);
        }
        //die(print_r($Role));
        return json_encode($Role,true);
    }
    
    static public function getEngin($TV_LIBELLE){
        $response = Engins::getEngin($TV_LIBELLE);
        return json_encode($response,true);
    }
    
    static public function getRolesEngin($TV_CODE){
        $Role=array();
        $response = Engins::getRoleEngins($TV_CODE);
        foreach($response as $e){
            array_push($Role, $e['ROLE_NAME']);
        }
        return json_encode($Role,true);
    }
}
    //Test de verification pour verifier les objets recuperer
    //$test = Engin::getRolesEngin("CCFL");
    //echo $test;
?>