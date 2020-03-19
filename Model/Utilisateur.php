<?php
require_once 'DB.php';

class Utilisateur
{
    public function construct(){}
    static public function login($username)
    {


        try {
            $query='SELECT * FROM pompier WHERE P_CODE=:P_CODE';
            $stmt= DB::connect()->prepare($query);
            $stmt->bindParam(':P_CODE',$username);
            $stmt->execute();
            $employe = $stmt->fetch(PDO::FETCH_ASSOC);
            return $employe;
        } catch (PDOException $ex) {
            echo'erer' . $ex->getMessage();
        }

    }

    static public function getAll()
    {
        
        $stmt=DB::connect()->prepare('SELECT * FROM pompier');
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->close();
        $stmt=null;
    }
    static public function getByP_CODE($P_CODE)
    {
        $stmt=DB::connect()->prepare('SELECT * FROM pompier WHERE P_CODE LIKE :P_CODE');
        $stmt->bindParam(':P_CODE',$P_CODE);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->close();
        $stmt=null;
    }


    static public function getEmploye($data)
    {

        //$id =$data['P_ID'];
        try {
            $query='SELECT *  FROM pompier WHERE P_ID="'.$data.'"';
            $stmt= DB::connect()->prepare($query);
            //$stmt->bindParam(':P_ID',$id);
            $stmt->execute();
            $employe = $stmt->fetch(PDO::FETCH_ASSOC);
            return $employe;
        } catch (PDOException $ex) {
            echo'erer' . $ex->getMessage();
        }

    }

    static public function getEmployeLogin($data)
    {
        try {
            $query='SELECT P_MDP, P_ID, P_EMAIL, P_NOM, P_PRENOM FROM pompier WHERE P_CODE="'.$data.'"';
            $stmt= DB::connect()->prepare($query);
            //$stmt->bindParam(':P_ID',$id);
            $stmt->execute();
            $employe = $stmt->fetch(PDO::FETCH_ASSOC);
            //var_dump(print_r($employe));
            return $employe;
            
        } catch (PDOException $ex) {
            echo'erer' . $ex->getMessage();
        }

    }

    /*static function getEmployeLogin2($dataCode,$dataMdp){
        try{
            $query='SELECT P_MDP, P_ID, P_EMAIL, P_NOM, P_PRENOM FROM pompier WHERE P_CODE="'.$dataCode.'"';
            $stmt= DB::connect()->prepare($query);
        }
    }*/

    static public function up($data)
    {

$query='UPDATE `pompier` SET  `P_NOM`=:P_NOM,
                              `P_NOM_NAISSANCE`=:P_NOM_NAISSANCE,
                              `P_PRENOM`=:P_PRENOM,
                              `P_PRENOM2`=:P_PRENOM2,
                              `P_BIRTHDATE`=:P_BIRTHDATE,
                              `P_BIRTHPLACE`=:P_BIRTHPLACE,
                              `P_BIRTH_DEP`=:P_BIRTH_DEP,
                              `P_SEXE`=:P_SEXE,
                              `P_EMAIL`=:P_EMAIL,
                              `P_PHONE`=:P_PHONE,
                              `P_PHONE2`=:P_PHONE2,
                              `P_ADDRESS`=:P_ADDRESS,
                              `P_CITY`=:P_CITY,
                              `P_ZIP_CODE`=:P_ZIP_CODE,
                              `P_PROFESSION`=:P_PROFESSION,
                              `P_GRADE`=:P_GRADE,
                              `P_STATUT`=:P_STATUT,
                              `P_DATE_ENGAGEMENT`=:P_DATE_ENGAGEMENT
                              
                            where  `P_ID`=:P_ID 
                            ';


        $stmt=DB::connect()->prepare($query);



        $stmt->bindParam(':P_NOM', $data['P_NOM']);
 
        $stmt->bindParam(':P_NOM_NAISSANCE', $data['P_NOM_NAISSANCE']);
               
        $stmt->bindParam(':P_PRENOM', $data['P_PRENOM']);
        $stmt->bindParam(':P_PRENOM2', $data['P_PRENOM2']);

        $stmt->bindParam(':P_BIRTHDATE', $data['P_BIRTHDATE']);
        $stmt->bindParam(':P_BIRTHPLACE', $data['P_BIRTHPLACE']);
        $stmt->bindParam(':P_BIRTH_DEP', $data['P_BIRTH_DEP']);

        $stmt->bindParam(':P_SEXE', $data['P_SEXE']);
        $stmt->bindParam(':P_EMAIL', $data['P_EMAIL']);
        $stmt->bindParam(':P_PHONE', $data['P_PHONE']);
        $stmt->bindParam(':P_PHONE2', $data['P_PHONE2']);


        $stmt->bindParam(':P_ADDRESS', $data['P_ADDRESS']);
        $stmt->bindParam(':P_CITY', $data['P_CITY']);
        $stmt->bindParam(':P_ZIP_CODE', $data['P_ZIP_CODE']);

        $stmt->bindParam(':P_PROFESSION', $data['P_PROFESSION']);
        
        $stmt->bindParam(':P_ID', $data['P_ID']);


        $stmt->bindParam(':P_GRADE', $data['P_GRADE']);
        $stmt->bindParam(':P_STATUT', $data['P_STATUT']);
        $stmt->bindParam(':P_DATE_ENGAGEMENT', $data['P_DATE_ENGAGEMENT']);


        if($stmt->execute())
        {
            return 'ok';
        }

        else
        
        {
                var_dump('errrrrrrrrrror');
            return 'error';
        }

        $stmt->close;
        $stmt = null;

    }  
}
//$test = Utilisateur::getEmployeLogin('admin');
//var_dump($test);
?>