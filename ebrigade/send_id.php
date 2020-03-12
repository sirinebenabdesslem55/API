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

include("config.php");
check_all(0);

$id=$_SESSION['id'];
$pid=intval($_GET["pid"]);
writehead();
?>

<SCRIPT>
function redirect(pid,mode,action) {
         url = "send_id.php?pid="+pid+"&mode="+mode+"&action="+action;
         self.location.href = url;
}
</SCRIPT>
<?php

$query="select p.P_ID, p.P_NOM, p.P_CODE, p.P_PRENOM, p.P_EMAIL, p.P_SECTION, p.P_PHONE, p.P_STATUT, p.P_CREATE_DATE, DATEDIFF(NOW(),p.P_CREATE_DATE ) NBDAYS
        from pompier p
        where p.P_ID = ".$pid;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

if ( isset($_GET["mode"])) $mode=$_GET["mode"];
else $mode='unknown';
if ( $P_EMAIL == '' ) $mode = 'manual';

if ( isset($_GET["action"])) $action=$_GET["action"];
else $action="update";

if ( $action == 'create') {
    if ( $NBDAYS > 0 or $P_CREATE_DATE == '' ) check_all(9);
    check_all(1);
    if (! check_rights($id, 1, "$P_SECTION")) check_all(24);
}
else {
    check_all(25);
    if (! check_rights($id, 25,"$P_SECTION")) check_all(9);
}
// select mode
if ( $mode == 'unknown' ) {
    $subject="Choisir mode d'envoi du mot de passe";
    $texte = "<input type='button' class='btn btn-default' value='manuel' onclick=\"javascript:redirect('".$pid."','manual','".$action."')\";><br>
    Vous devrez communiquer le mot de passe par t�l�phone ou en l'envoyant vous m�me par mail.";
    $texte .="<p><p><input type='button' class='btn btn-default' value='automatique' onclick=\"javascript:redirect('".$pid."','auto','".$action."')\";>
    <br>Le nouveau mot de passe sera envoy� automatiquement par mail 
    <br><i class='fa fa-exclamation-triangle' style='color:orange'></i><small>
    Attention risque d'�tre consid�r� comme un spam par certains serveurs de messagerie.</small>";
    if ( $action == 'update' ) 
        $ret = "<a href='javascript:history.back(1)'><input type='submit' class='btn btn-default' value='Annuler'></a>";
    else
        $ret = "";
    write_msgbox($subject, $star_pic, $texte."<p align=center>".$ret,10,0);
}
else {
    if ($password_length == 0) $password_length=8;
    $newpass = generatePassword($password_length);
    $hash = my_create_hash($newpass);
    $query="update pompier set P_MDP=\"".$hash."\", P_PASSWORD_FAILURE=null where P_ID=".$P_ID;
    $result=mysqli_query($dbc,$query);

    if ( $mode =='auto' ) {
        $Mailcontent = "Bonjour ".ucfirst($P_PRENOM).",\n\n";
        $Mailcontent .= "Voici vos informations de connexion $application_title.\n\n";
        $Mailcontent .= "Identifiant: $P_CODE\n\n";
        $Mailcontent .= "Mot de passe: $newpass\n\n";
        $Mailcontent .= "Vous pourrez les changer une fois connect�(e).\n";
        if ( $assoc == 1 ) $Mailcontent .= "\nAide en ligne: ".$wikiurl."\n";
        $Subject = "identifiants $application_title pour ".fixcharset(ucfirst($P_PRENOM)." ".strtoupper($P_NOM));
        $SenderName = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
        $SenderMail = $_SESSION['SES_EMAIL'];
        $title="identifiants renvoy�s";
        mysendmail2("$P_EMAIL","$Subject","$Mailcontent","$SenderName","$SenderMail");  
        $texte = "Un email contenant l'identifiant et un mot de passe a �t� envoy� � ".ucfirst($P_PRENOM)." ".strtoupper($P_NOM);
        $comment = "Envoi automatique � ".$P_EMAIL;
    }
    else {
        $title="Envoi manuel du mot de passe";
        if ( $action == 'update' ) {
            $a = 'mis � jour';
            $b = 'nouveau';
            $c = 'toujours';
        }
        else {
            $a = 'cr��';
            $b = '';
            $c='';
        }
        $texte = "Le mot de passe de ".ucfirst($P_PRENOM)." ".strtoupper($P_NOM).", a �t� ".$a.". <br>Ce ".$b." mot de passe est <b>".$newpass."</b>";
        $url = "http://".get_plain_url($cisurl);
        $texte .="<br>Son identifiant pour se connecter � <a href='".$url."' target='_blank'>".$url."</a> est ".$c." <b>".$P_CODE."</b>";
        $texte .= "<br>Veuillez lui communiquer ces informations: ";
        if ( $P_EMAIL <> '' ) $texte .="<br>- par mail � <a href='mailto:".$P_EMAIL."'>".$P_EMAIL."</a>.";
        if ( $P_PHONE <> '' )$texte .="<br>- ou par t�l�phone au ".$P_PHONE.".";
        $texte .= "<br>Il devra id�alement changer son mot de passe � la premi�re connexion.";
        $comment = "Envoi manuel";
    }

    if ( $action == 'update' ) 
        insert_log('REGENMDP', $P_ID, $comment);
    
    $ret = "<a href='upd_personnel.php?pompier=".$pid."&tab=1'><input type='submit' class='btn btn-default' value='Retour'></a>";
    if ( $action == 'create' and $P_STATUT  <> 'EXT' ) $another = " <a class='btn btn-default'  href='ins_personnel.php' title='Ajouter une autre fiche'><i class='fa fa-plus' ></i> Ajouter</a>";
    else  $another ="";
    write_msgbox($title, $star_pic, $texte."<p align=center>".$ret.$another,10,0); 
}
writefoot();
?>
