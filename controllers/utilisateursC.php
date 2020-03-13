<?php
require_once 'Model/Utilisateur.php';
require_once 'ebrigade/fonctions.php';
class Utilisateurs
{
    static public function ListUtilisateur()
    {
        $response = Utilisateur::getAll();
        return json_encode($response);
        //return $response;
    }
    /*static public function SearchUtilisateur($P_CODE)
    {
            $response = Utilisateur::getByP_CODE($P_CODE);
            return  json_encode($response);
    }*/

    static public function getOneUserByCode(){
        var_dump($_GET['P_CODE']);
        if (isset($_GET['P_CODE'])){
            $employee = Utilisateur::getEmployeLogin($_GET['P_CODE']);
            $c=json_encode($employee,true);
            var_dump($c);
            return $c;
        }
        else{
            die(print_r("erreurrrrr"));
        }

    }

    static public function getOneUserByLogin(){
         //var_dump($_GET['P_CODE']);
          //var_dump($_GET['P_MDP']);
          $mcrypt = my_create_hash($_GET['P_MDP']);
          //var_dump($mcrypt);

        if(isset($_GET['P_CODE']) && !empty($_GET['P_CODE']) && isset($_GET['P_MDP']) && !empty($_GET['P_MDP'])){
                $employee = Utilisateur::getEmployeLogin($_GET['P_CODE']);

                if($mcrypt == $employee['P_MDP']){
                    //connexion reussit    
                    $c=json_encode($employee,true);
                    return $c;
                }
                else{

                    return (1);
                    //mot de passe ou nom utilisateur incorrect
                    //die(print_r("Mdp ou username incorrect"));
                }
        }
        
        //formulaire mal remplit
        else{

            return (2);
             //die(print_r("Formulaire à été mal remplit"));
        }
    }


    static public function getOne(){

        if(isset($_GET['P_ID'])){
            //$data =array('P_ID'=> $_GET['P_ID']);
            $employe= Utilisateur::getEmploye($_GET['P_ID']);
            //die(print_r($employe));
            $d=json_encode($employe,true);
            //var_dump($d);
            return $d; 
    }
    else
    {
        die(print_r("erreur de remplissage de formulaire"));
    }

    }

    /*public function auth()
    {
         if (isset($_POST['username'])) 
        {
            $password=$_POST['password'];
            $username=$_POST['username'];
            $result=Utilisateur::login($username);     
            $d=json_encode($result,true);
            return $d
        }
    else{
        var_dump ($_POST);
       // header('location:http://localhost/Interventions-Management/login2');
        
        die("errrreur eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeede login");

        
  }

    }*/
static public function update($aray)

{
        

    

    }
}


?>