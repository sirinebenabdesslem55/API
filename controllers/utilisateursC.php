<?php
require_once 'Model/Utilisateur.php';
class Utilisateurs
{
    static public function ListUtilisateur()
    {
        $response = Utilisateur::getAll();
        return json_encode($response);
        //return $response;
    }
    static public function SearchUtilisateur($P_CODE)
    {
            $response = Utilisateur::getByP_CODE($P_CODE);
            return  json_encode($response);
    }
    static public function getOne()
    {
    if(isset($_GET['P_ID']))
    {
    $data =array('P_ID'=> $_GET['P_ID']);
    $employe= Utilisateur::getEmploye($data);
    $d=json_encode($employe,true);
    return $d; 
   }
    else
    {

    die(print_r("erreru de remplissage de formulaire"));

    }

    }


    public function auth()
    {

         if (isset($_POST['username'])) 
        {
            $password=$_POST['password'];

            $username=$_POST['username'];

            $result=Utilisateur::login($username);

        
            $d=json_encode($result,true);


                return $d;

        /*if ($result['P_CODE'] === $username && password_verify($password, $result['password'])) 
        
        {
            $_SESSION['logged'] = true;
            $_SESSION['username']=$result['P_CODE'];
            $_SESSION['P_ID']=$result['P_ID'];
            $d=$_SESSION;

            //Redirect::to('home');
            
            header('location:http://localhost/Interventions-Management/home');

            return $d;
        }
*/
        }
    else{
        var_dump ($_POST);
       // header('location:http://localhost/Interventions-Management/login2');
        
        die("errrreur eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeede login");

        
  }

    }
/*static public function update()
{
        
{
    $data= array(
'P_git' => $_POST['EmployeeID'],
'Title' => $_POST['Title'],
'NationalIDNumber' => $_POST['NationalIDNumber'],
'BirthDate' => $_POST['BirthDate'],
'Gender' => $_POST['Gender']

);


    $result =Employe::update2($data);



    if ($result === 'ok') {
        echo "<script type='text/javascript'>window.top.location='http://localhost/management-employee/';</script>";
        exit;
    } else {
        echo $result;
    }
}

    }
*/
}


?>