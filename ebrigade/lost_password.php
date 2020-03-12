<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 5.1

  # Copyright (C) 2004, 2020 Nicolas MARCHE
  # This program is free software; you can redistribute it and/or modify
  # it under the terms of the GNU General Public License as published by
  # the Free Software Foundation; either version 2 of the License, or
  # (at your option) any later version.
  #
  # This program is distributed in the hope that it will be useful,
  # but WITHOUT ANY WARRANTY; without even the implied warranty of
  # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  # GNU General Public License for more details.
  # You should have received a copy of the GNU General Public License
  # along with this program; if not, write to the Free Software
  # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

include_once ("config.php");
$nomenu=1;
writehead();
echo  "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
include_once ("css/css.php");

$url=$identpage;

if (isset($_POST["matricule"])) $matricule = $_POST["matricule"];
else $matricule = "";

if (isset($_POST["email"])) $email = $_POST["email"];
else $email = "";

if (isset($_GET["session"])) $session = $_GET["session"];
else $session = "";

// =====================================
// Verify parameters 
// =====================================

if ( $mail_allowed == 0 ) {
    write_msgbox("mail d�sactiv�s", $warning_pic, "Les mails sont d�sactiv�s, cette fonction ne peut pas �tre utilis�e.<p align=center>
       <a href=$url><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
    exit;
}

if ( ($matricule <> "") and ( $email <> "") ) {
 
     // check input parameters
    $matricule=secure_input($dbc,$matricule);
     $email=secure_input($dbc,$email);
 
    $query="select P_ID, P_NOM, P_PRENOM
        from pompier 
        where P_CODE='".$matricule."'
         and P_EMAIL =  '".$email."'
        and GP_ID >= 0 
        and GP_ID2 >= 0";
    $result=mysqli_query($dbc,$query);
     
    if ( mysqli_num_rows($result) == 1 ) {
        $row=mysqli_fetch_array($result);
        $P_ID=$row['P_ID'];
        $P_PRENOM=$row['P_PRENOM'];
        $P_NOM=$row['P_NOM'];
        $secret = generateSecretString();
        $query="delete from demande
                  where P_ID = '".$P_ID."'
                  and D_TYPE = 'password'";
        $result=mysqli_query($dbc,$query);    
       
        $query="insert into demande ( P_ID, D_TYPE, D_SECRET , D_DATE )
                  values ( '".$P_ID."' , 'password', '".$secret."', NOW() )";
        $result=mysqli_query($dbc,$query);    
    
        $Mailcontent = "Bonjour ".ucfirst($P_PRENOM).",\n\n";    
        $Mailcontent .= "Vous avez demand� un renouvellement de votre mot de passe.\n";
        $Mailcontent .= "Veuillez confirmer cette demande en cliquant sur le lien suivant:\n";
        $Mailcontent .= "http://".$cisurl."/lost_password.php?session=".$secret;
        $Subject = "Confirmation $application_title pour ".ucfirst($P_PRENOM)." ".strtoupper($P_NOM);
       
        mysendmail2("$email","$Subject","$Mailcontent","Admin $cisname","$email");
       
        write_msgbox("demande prise en compte", $star_pic, "Vous allez recevoir un email contenant un lien URL (V�rifiez le dossier Spam). En cliquant dessus vous confirmerez la demande de renouvellement de mot de passe.<p align=center><a href=$url><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0); 

    }
    else {
        write_msgbox("erreur de param�tres", $error_pic, $error_7." Ou encore le compte est interdit d'acc�s.<p align=center>
        <a href=$url><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
    }
}

// =====================================
// confirmation
// =====================================

else if ($session <> "") {
    $session=secure_input($dbc,$session);
    $query="select d.P_ID, p.P_PRENOM, p.P_NOM, p.P_EMAIL
        from demande d, pompier p
        where d.D_TYPE='password'
        and p.P_ID = d.P_ID 
         and d.D_SECRET =  '".$session."'";
    $result=mysqli_query($dbc,$query);
     
    if ( mysqli_num_rows($result) > 0 ) {
        $row=mysqli_fetch_array($result);
        $P_ID=$row['P_ID'];
        $P_PRENOM=$row['P_PRENOM'];
        $P_NOM=$row['P_NOM'];
        $email=$row['P_EMAIL'];
        if ($password_length == 0) $password_length=6;
        $newpass = generatePassword($password_length);
       
        $hash = my_create_hash($newpass);
        $query="update pompier set P_MDP=\"".$hash."\", P_PASSWORD_FAILURE=null
              where P_ID=".$P_ID;
        $result=mysqli_query($dbc,$query);
       
        $query="delete from demande
                  where P_ID = '".$P_ID."'
                  and D_TYPE = 'password'";
        $result=mysqli_query($dbc,$query);

        $Mailcontent = "Bonjour ".ucfirst($P_PRENOM).",\n\n";       
        $Mailcontent .= "Votre mot de passe a bien �t� chang�.\n\n";
        $Mailcontent .= "$newpass\n\n";
        $Mailcontent .= "Vous pourrez le changer une fois connect�(e).\n";
        $Subject = "Nouveau mot de passe $application_title pour ".ucfirst($P_PRENOM)." ".strtoupper($P_NOM);       
       
        mysendmail2("$email","$Subject","$Mailcontent","Admin $cisname","$email");
       
        write_msgbox("nouveau mot de passe g�n�r�", $star_pic, "Votre nouveau mot de passe est:<p> ".$newpass."<p>Vous pourrez le modifier lors de votre prochaine connexion.<p align=center><a href=$url><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0); 
       
    }
    else {
    
        write_msgbox("erreur de param�tres", $error_pic, "Aucune demande de renouvellement de mot de passe correspondant � votre session n'a �t� enregistr�e aujourd'hui<p align=center>
               <a href=$url><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
    
    }
}

// =====================================
// Demande 
// =====================================
else {

if ( $syndicate == 1 ) $msg="au secr�tariat."; 
else $msg="� votre responsable.";

echo "<body align='center'>
<div class='container' align='center'>
    <div class='row'>
        <div class='col-md-6 col-lg-4 offset-md-4'>
            <div class='card' style='background-color:".$mydarkcolor.";'>
                <div class='card-header'> <strong>".$cisname."  - mot de passe perdu</strong>
                </div>
                <div class='card-body'>
                    <form role='form' name='form' action='lost_password.php' method='POST'>
                        <fieldset>
                            <div class='row'>
                            <small>
                                Vous pouvez demander un nouveau mot de passe, en indiquant votre Identifiant et votre adresse email
                                (qui doit d&#xE9;j&#xE0; &#xEA;tre enregistr&#xE9;e). Par contre si votre identifiant est perdu, alors vous devrez vous adresser &#xE0; votre responsable.
                            </small><br>
                            </div>
                            <div class='row'>
                                <div class='col-md-12 col-lg-10  offset-md-1 '>
                                
                                    <div class='input-group mb-3'>
                                      <div class='input-group-prepend'>
                                        <span class='input-group-text' id='basic-addon1'><i class='fa fa-user'></i></span>
                                      </div>
                                      <input type='text' name='matricule' class='form-control no-round-left' placeholder='Identifiant' aria-describedby='basic-addon1'>
                                    </div>
                                
                                    <div class='input-group mb-3'>
                                      <div class='input-group-prepend'>
                                        <span class='input-group-text' id='basic-addon1'><i class='far fa-envelope'></i></span>
                                      </div>
                                      <input type='text' name='email' class='form-control no-round-left' placeholder='email' >
                                    </div>
                                
                                    <div class='form-group'> 
                                        <a class='btn btn-ebrigade btn-lg' href='#' onclick='this.disabled=true;this.value=&apos;attendez&apos;;document.form.submit();'>
                                            <i class='fa fa-sign-in fa-lg'></i> Envoyer
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
                <div class='card-footer '> <a href='login.php'>Retour login </a>
                </div>
            </div>
        </div>
    </div>
</div>";

}

writefoot($loadjs=false);
?>
    
