<?php
require_once 'DB.php';

class Utilisateur
{
    public function construct(){}
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

    static public function getEmploye($data)
    {

        $id =$data['P_ID'];
        try {
            $query='SELECT `P_ID`,`P_PRENOM`,`P_PRENOM2`,`P_NOM`,`P_GRADE`,`P_STATUT`,`P_EMAIL`,`P_SEXE`,`P_PROFESSION` FROM pompier WHERE P_ID=:P_ID';
            $stmt= DB::connect()->prepare($query);
            $stmt->bindParam(':P_ID',$id);
            $stmt->execute();
            $employe = $stmt->fetch(PDO::FETCH_ASSOC);
            return $employe;
        } catch (PDOException $ex) {
            echo'erer' . $ex->getMessage();
        }

    }

    static public function up($data)
    {

$query='UPDATE `pompier` SET  `P_NOM`=:P_NOM,
                              `P_PRENOM`=:P_PRENOM,
                              `P_SEXE`=:P_SEXE,
                              `P_GRADE`=:P_GRADE,
                              `P_PROFESSION`=:P_PROFESSION,
                              `P_STATUT`=:P_STATUT,
                              `P_EMAIL`=:P_EMAIL
                            where  `P_ID`=:P_ID
                            '; 

        $stmt=DB::connect()->prepare($query);

        $stmt->bindParam(':P_NOM', $data['P_NOM']);
        $stmt->bindParam(':P_ID', $data['P_ID']);
        $stmt->bindParam(':P_PRENOM', $data['P_PRENOM']);
        $stmt->bindParam(':P_SEXE', $data['P_SEXE']);
        $stmt->bindParam(':P_GRADE', $data['P_GRADE']);
        $stmt->bindParam(':P_PROFESSION', $data['P_¨PROFESSION']);
        $stmt->bindParam(':P_STATUT', $data['P_STATUT']);
        $stmt->bindParam(':P_EMAIL', $data['P_EMAIL']);

        if($stmt->execute())
        {
            return 'ok';
        }

        else
        
        {

            return 'error';
        }

        $stmt->close;
        $stmt = null;

    }

    
}

