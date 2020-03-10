<?php
require_once 'DB.php';
class TypeInterventions
{
    public function construct(){}
    static public function getAllType()
    {
        $stmt=DB::connect()->prepare('SELECT * FROM  type_intervention');
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->closeCursor();
        $stmt=null;
    }
}
//$test = TypeInterventions::getAllType();
//echo (json_encode($test));
?>