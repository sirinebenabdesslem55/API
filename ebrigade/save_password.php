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
check_all(0);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
writehead();

$new1=secure_input($dbc,$_POST["new1"]);
$new2=secure_input($dbc,$_POST["new2"]);
$pid=intval($_POST["pid"]);
$section=get_section_of($pid);
$matricule=get_matricule($pid);
$url = "change_password.php?pid=".$pid;

?>
<html>
<SCRIPT language=JavaScript>
function redirect(url) {
     self.location.href=url;
}
</SCRIPT>

<?php
verify_csrf('change_password');

if ( $pid <> $id ) {
   if (! check_rights($id, 25,"$section"))
      check_all(9);
}
if ($new1 =="" ) {
    write_msgbox("erreur mot de passe",$error_pic,"le nouveau mot de passe doit être renseigné <br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\">",30,30);
    exit;
}

//======================
// check duplicate
//======================

elseif ($new1 <> $new2) {
    write_msgbox("erreur mot de passe",$error_pic,"les 2 valeurs saisies pour le nouveau mot de passe sont différentes<br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\">",30,30);
    exit;
}

//======================
// check quality
//======================
$pos = strpos($new1, $matricule);

if (($pos == true ) or ( substr($new1,0,2) == substr($matricule,0,2)))  { 
    write_msgbox("erreur mot de passe",$error_pic,"le mot de passe ne doit pas être basé sur votre identifiant.<br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\">",30,30);
    exit;
}

if ( $password_quality > 0 ){
  if (! preg_match("/.*[0-9].*/","$new1" )){
        write_msgbox("erreur mot de passe",$error_pic,"le mot de passe doit aussi contenir des chiffres.<br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\">",30,30);
        exit;
  }
  if (! preg_match("/.*[a-zA-Z].*/","$new1" )){
        write_msgbox("erreur mot de passe",$error_pic,"le mot de passe doit aussi contenir des lettres.<br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\">",30,30);
        exit;
  }
  if ($password_quality > 1 and ! preg_match("/\W/","$new1" )){
        write_msgbox("erreur mot de passe",$error_pic,"le mot de passe doit aussi contenir au moins un caractère spécial, <br>parmi ceux-ci par exemple:<p><b>!,@,#,$,%,^,&,*,?,_,~,£,µ,§,=,<br>é,è,ç,à,ù,>,<,€,\.,\;,\,+,-,¤,|</b><br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\">",30,30);
        exit;
  }
}

if ( preg_match("/\"|\'/","$new1" )){
    write_msgbox("erreur mot de passe",$error_pic,"le mot de passe ne doit pas contenir d'apostrophes ou guillemets.<br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\">",30,30);
    exit;
}

//======================
// check length
//======================

if ( $password_length > 0 ){
    if (strlen("$new1") < $password_length ) {
        write_msgbox("erreur mot de passe",$error_pic,"le mot de passe est trop court. Il doit avoir au moins $password_length caractères.<br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\">",30,30);
        exit;
    }
}

$hash = my_create_hash($new1);
$query="update pompier set P_MDP=\"".$hash."\", P_PASSWORD_FAILURE=null
         where P_ID=convert('".$pid."',UNSIGNED)";
$result=mysqli_query($dbc,$query);

insert_log('UPDMDP', $pid);

write_msgbox("changement réussi",$star_pic,"le mot de passe a été modifié avec succès<br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('upd_personnel.php?pompier=".$pid."');\">",30,30);

writefoot();
?>
