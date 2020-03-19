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

    static public function update()
    {
             $P_ID=$_POST['post']['P_ID'];
             $P_NOM=$_POST['post']['P_NOM'];
             $P_NOM_NAISSANCE=$_POST['post']['P_NOM_NAISSANCE' ];
             $P_PRENOM=$_POST['post']['P_PRENOM'];
             $P_PRENOM2=$_POST['post']['P_PRENOM2'];
             $P_BIRTHDATE=$_POST['post']['P_BIRTHDATE'];
             $P_BIRTHPLACE=$_POST['post']['P_BIRTHPLACE'];
             $P_BIRTH_DEP=$_POST['post']['P_BIRTH_DEP'];
             $P_SEXE=$_POST['post']['P_SEXE'];
             $P_EMAIL=$_POST['post']['P_EMAIL'];
             $P_PHONE=$_POST['post']['P_PHONE' ];
             $P_PHONE2=$_POST['post']['P_PHONE2'];
             $P_ADDRESS=$_POST['post']['P_ADDRESS'];
             $P_CITY=$_POST['post']['P_CITY'];
             $P_ZIP_CODE=$_POST['post']['P_ZIP_CODE'];
             $P_PROFESSION=$_POST['post']['P_PROFESSION']; 
             $P_GRADE=$_POST['post'][ 'P_GRADE'];
             $P_STATUT=$_POST['post']['P_STATUT'];
             $P_DATE_ENGAGEMENT=$_POST['post']['P_DATE_ENGAGEMENT'];


             $data= array("P_ID"=>$P_ID,
                          "P_NOM"=>$P_NOM,
                          "P_NOM_NAISSANCE"=>$P_NOM_NAISSANCE,
                          "P_PRENOM"=>$P_PRENOM,
                          "P_PRENOM2"=>$P_PRENOM2,
                          "P_BIRTHDATE"=>$P_BIRTHDATE,
                          "P_BIRTHPLACE"=>$P_BIRTHPLACE,
                          "P_BIRTH_DEP"=>$P_BIRTH_DEP,
                          "P_SEXE"=>$P_SEXE,
                          "P_EMAIL"=>$P_EMAIL,
                          "P_PHONE"=>$P_PHONE,
                          "P_PHONE2"=>$P_PHONE2,
                          "P_ADDRESS"=>$P_ADDRESS,
                          "P_CITY"=>$P_CITY,
                          "P_ZIP_CODE"=>$P_ZIP_CODE,
                          "P_PROFESSION"=>$P_PROFESSION,
                          "P_GRADE"=>$P_GRADE,
                          "P_STATUT"=>$P_STATUT,
                          "P_DATE_ENGAGEMENT"=>$P_DATE_ENGAGEMENT
    );
    
    
    
    //die(var_dump($data));
    $result =Utilisateur::up($data);
    
    if($result == 'ok')
    {
        
    
    header('Location: localhost/Interventions-Management/liste');
    
    
    }    
       
    }
    
        
        
        }  
    


?>