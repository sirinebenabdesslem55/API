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
include_once ("fonctions_sms.php");
check_all(43);
$id=$_SESSION['id'];
$mysection = $_SESSION['SES_SECTION'];
$SMS_CONFIG=get_sms_config($mysection);
writehead();
?>
<script type="text/javascript" src="js/tokeninput/src/jquery.tokeninput.js"></script>
<script type="text/javascript" src="js/checkForm.js"></script>
<link rel="stylesheet" href="js/tokeninput/styles/token-input.css" type="text/css" />
<link rel="stylesheet" href="js/tokeninput/styles/token-input-facebook.css" type="text/css" />
<script type="text/javascript">
var MaxSMS = 1;
function SendSMS(l1) {
    var dests = l1.value;
    var nbdest;
    if ( dests.length == 0 ) nbdest = 0;
    else nbdest = dests.split(",").length;
    if (dests.length == 0) {
        alert("Aucun destinataire");
        return;
    }
    if (nbdest > MaxSMS) {
        alert("Vous avez choisi d'envoyer un SMS à "+ nbdest +" personnes. \n Le maximum autorisé est "+ MaxSMS);
        return;
    }
    url="localize_send.php?pid="+dests;
    self.location.href=url;
}
function redirect() {
     self.location.href="gps.php";
}
</script>
</HEAD>
<?php

if ( isset($_GET['tab']) ) $tab=$_GET['tab']; 
else $tab='1';

$html = "<body><div align=center class='table-responsive'>";

$NO_SMS=true;
if ( check_rights($id, 23) and $SMS_CONFIG[1] <> 0) {
    $credits = get_sms_credits($mysection);
    if ( intval($credits) > 0  or $credits == "Solde illimité" or $credits == "OK" ) $NO_SMS=false;
}
if ( $NO_SMS ) $html .=   "<div id='msgError' class='alert alert-danger' role='alert'><strong>Erreur! </strong>Pas d'envoi de SMS possibles.</div>";

$prepopulate="";

if (isset($_POST['SelectionMail'])) {
    $ids = $_POST['SelectionMail'];
    $ids = str_replace(",,",",",$ids);
    $ids = trim($ids, ',');
    $query="select P_ID, upper(P_NOM), P_PRENOM 
            from pompier 
            where P_ID in (".$ids.")
            and P_EMAIL <>'' 
            and P_OLD_MEMBER = 0
            order by P_NOM, P_PRENOM ";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $prepopulate .= "{id: ".$row[0].", name: \"".$row[1]." ".ucfirst($row[2])."\"},";
    }
    $prepopulate="prePopulate: [ ".rtrim($prepopulate,',')." ],";
}

$html .=  "<font size=4><b> Localiser une personne</b></font>
<br><small><i>Un SMS va être envoyé à la personne sélectionnée.<br>En cliquant sur le lien reçu, il activera sa géolocalisation et vous pourrez le voir sur la carte.</i></small>";

$html .=  "<ul class='nav nav-tabs  noprint' id='myTab' role='tablist'>";
if ( $tab == '1' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='localize.php?tab=1' title='Recherche Numéro de téléphone' role='tab' aria-controls='tab1' href='#tab1' >
    <span>Recherche Numéro de téléphone</span></a></li>";
    
if ( $tab == '2' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='localize.php?tab=2' title='Recherche Nom' role='tab' aria-controls='tab2' href='#tab2' >
    <span>Recherche Nom</span></a></li>";

$html .= "</ul>";
$html .=  "<FORM name='formulaire' id='formulaire'>";
$html .=  "<p><TABLE cellspacing=0 border=0 >";

if ( $tab == '1' ) {
    $html .=  " <tr bgcolor=$mylightcolor>
                <td align=center width='300'><b>Numéro de téléphone à localiser</b>
                <p align=center><input type='text' id='phone' name='phone'  maxlength=14  size='30' onchange='checkPhone(form.phone,\"\",\"".$min_numbers_in_phone."\")' autofocus='autofocus'/>
                <p align=center><input type='button' class='btn btn-primary' value='Envoyer' onclick='SendSMS(this.form.phone)'>
                </td></tr>";
}
if ( $tab == '2' ) {
    
    $html .=  " <tr bgcolor=$mylightcolor><td align=center ><b>Personne à localiser 
            <i class='far fa-lightbulb fa-lg' title='Saisissez les premières lettres du nom de chaque personne dans le champ ci-dessous'></i></b>
            <input type='text' id='input-facebook-theme' name='liste2' autofocus='autofocus'/>
            <script type='text/javascript'>
            $(document).ready(function() {
                $(\"#input-facebook-theme\").tokenInput(\"mail_create_input.php\", {
                    theme: \"facebook\",
                    $prepopulate
                    preventDuplicates: true,
                    hintText: \"Saisissez les premières lettres du nom\",
                    noResultsText: \"Aucun résultat\",
                    searchingText: \"Recherche en cours\"
                    
                });
            });
            </script>
     </td></tr>";

    $html .=  "<tr bgcolor=$mylightcolor>
            <td>
            <table class='noBorder'>
            <tr>
                <td bgcolor=$mylightcolor align=center >
                <input type='button' class='btn btn-primary' value='Envoyer' onclick='SendSMS(this.form.liste2)'>
                </td>
            </tr></table>
            </td>
            </tr>";
}

$html .= "</TABLE>";
$html .=  "</FORM>";
$html .=  "<p><input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"redirect();\">";
$html .=  "</div>";
print $html;
writefoot();
?>
