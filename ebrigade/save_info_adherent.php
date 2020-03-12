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
check_all(53);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);

verify_csrf('update_personnel');
?>
<html>
<SCRIPT language=JavaScript>
function redirect(page) {
     self.location.href=page;
}
</SCRIPT>

<?php

if ( isset ($_POST["P_ID"])) $P_ID=intval($_POST["P_ID"]);
else if ( isset ($_GET["P_ID"])) $P_ID=intval($_GET["P_ID"]);
else $P_ID=0;

if ( $P_ID == 0 ) {
    param_error_msg();
    exit;
}
if (!  check_rights($id, 53, get_section_of("$P_ID"))) check_all(24);
$_SESSION['from_cotisation']=1;

// sauver infos infos adhérent
// post parameters

$type_paiement=(isset($_POST["type_paiement"])?intval($_POST["type_paiement"]):0);
$montant_regul=(isset($_POST["montant_regul"])?(float)$_POST["montant_regul"]:0);

$query="update pompier 
        set TP_ID=".$type_paiement.",
        MONTANT_REGUL=".$montant_regul."
        where P_ID=".$P_ID;
$result=mysqli_query($dbc,$query);

if ( $bank_accounts == 1 ) {

    // permission nationale requise
    if (!  check_rights($id, 53, "0")) check_all(24);
    
    // sauver infos BIC
    if (isset ($_POST["bic"])) $bic=secure_input($dbc,$_POST["bic"]);
    else $bic="";
    
    $OLDBIC=get_BIC('P',$P_ID);
    if ( strlen($bic) > 0 and strlen($bic) <> 11 ) {
        write_msgbox("erreur", $error_pic, "Code BIC incorrect, 11 caractères requis, BIC non modifié<br><p align=center><input type=submit class='btn btn-default' value='retour' onclick='javascript:history.back(1);'> ",10,0);
        exit;
    }
    
    if (isset ($_POST["iban1"])) $iban1=secure_input($dbc,$_POST["iban1"]);
    else $iban1="";
    if (isset ($_POST["iban2"])) $iban2=secure_input($dbc,$_POST["iban2"]);
    else $iban2="";
    if (isset ($_POST["iban3"])) $iban3=secure_input($dbc,$_POST["iban3"]);
    else $iban3="";
    if (isset ($_POST["iban4"])) $iban4=secure_input($dbc,$_POST["iban4"]);
    else $iban4="";
    if (isset ($_POST["iban5"])) $iban5=secure_input($dbc,$_POST["iban5"]);
    else $iban5="";
    if (isset ($_POST["iban6"])) $iban6=secure_input($dbc,$_POST["iban6"]);
    else $iban6="";
    if (isset ($_POST["iban7"])) $iban7=secure_input($dbc,$_POST["iban7"]);
    else $iban7="";
    if (isset ($_POST["iban8"])) $iban8=secure_input($dbc,$_POST["iban8"]);
    else $iban7="";
    $iban=$iban1.$iban2.$iban3.$iban4.$iban5.$iban6.$iban7.$iban8;
     
    $OLDIBAN=get_IBAN('P',$P_ID);
    if ( strlen($iban) > 0 and (strlen($iban) < 16 or strlen($iban) > 32 )) {
        write_msgbox("erreur", $error_pic, "Code IBAN incorrect, entre 16 et 32 caractères requis, IBAN non modifié<br><p align=center><input type=submit class='btn btn-default' value='retour' onclick='javascript:history.back(1);'> ",10,0);
        exit;
    }

    if ( $OLDBIC <> $bic or $OLDIBAN <> $iban) {
        $query="delete from compte_bancaire where CB_TYPE = 'P' and CB_ID=".$P_ID;
        $result=mysqli_query($dbc,$query);
        $query="insert into compte_bancaire (CB_TYPE,CB_ID,BIC,IBAN,UPDATE_DATE)
                    values ('P', ".$P_ID.",\"".$bic."\",\"".$iban."\",NOW())";
        $result=mysqli_query($dbc,$query);

        if ( $OLDBIC <> $bic ) insert_log('UPDBIC', $P_ID, "ancien BIC: ".$OLDBIC );
        if ( $OLDIBAN <> $iban ) insert_log('UPDIBAN', $P_ID, "ancien IBAN: ".$OLDIBAN );
    }
    
}

echo "<body onload='redirect(\"upd_personnel.php?tab=8&pompier=".$P_ID."\")'>";

?>
