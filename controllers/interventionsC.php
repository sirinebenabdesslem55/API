<?php
require_once 'C:/wamp64/www/api/Model/Interventions.php';

//require_once '../Model/Type_Interventions.php';
//require_once 'C:/wamp64/www/api/Model/TypeInterventions.php';
class Interventions
{
    static public function getAll(){
        $response = TypeInterventions::getAllType();
         return json_encode($response,true);
    }
}
//http://localhost/api/utilisateurs.php?c=interventions&m=getAll

//$test = Interventions::getAll();
//echo (json_encode($test));
//$tests = TypeIntervention::getAll();
//echo $test;
//foreach($tests as $test){
//echo $test['TI_CODE'];}
?>


