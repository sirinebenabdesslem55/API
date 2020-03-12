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
$nomenu=1;
writehead();
destroy_my_session_if_forbidden($id);

if (isset($_POST["evenement"])) $evenement=intval($_POST["evenement"]);
else if (isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;
if (isset($_POST["save"])) $save=intval($_POST["save"]);
else $save=0;
if (isset($_POST["responsable"])) $responsable=intval($_POST["responsable"]);
else if ( $save == 0 )  $responsable=1;
else $responsable=0;
if (isset($_POST["nombres"])) $nombres=intval($_POST["nombres"]);
else if ( $save == 0 )  $nombres=1;
else $nombres=0;
if (isset($_POST["statistiques"])) $statistiques=intval($_POST["statistiques"]);
else if ( $save == 0 )  $statistiques=1;
else $statistiques=0;
if (isset($_POST["show_vehicules"])) $show_vehicules=intval($_POST["show_vehicules"]);
else if ( $save == 0 )  $show_vehicules=1;
else $show_vehicules=0;
if (isset($_POST["show_materiel"])) $show_materiel=intval($_POST["show_materiel"]);
else if ( $save == 0 )  $show_materiel=1;
else $show_materiel=0;
if (isset($_POST["show_cav"])) $show_cav=intval($_POST["show_cav"]);
else if ( $save == 0 )  $show_cav=1;
else $show_cav=0;

?>
<SCRIPT>
function redirect(evenement) {
    url="evenement_display.php?tab=8&evenement="+evenement;
    self.location.href=url;
}

function print_report(e,s,r,n,s,v,m,c) {
    url="pdf_document.php?evenement="+e+"&section="+s+"&show_responsable="+r+"&show_nombres="+n+"&show_statistiques="+s+"&show_vehicules="+v+"&show_materiel="+m+"&show_cav="+c+"&mode=11";
    self.location.href=url;
}

function CheckAll(checkValue) {
    var dForm = document.rapport;
    // Parcours des sous sections et mise à jour des cases à cocher
    for (i=0;i<dForm.length;i++) {
        var element = dForm[i];
        if (element.type=='checkbox') {
            if (element.name.substring(0,5)=='check'){
                element.checked = ((checkValue!=true)?false:true);
            }
            if (element.name == 'yesall' && checkValue==false ) {
                element.checked = false;
            }
            if (element.name == 'noall' && checkValue==true ) {
                element.checked = false;
            }  
        }
    }
}
</SCRIPT>
</HEAD>
<?php
$html = "<body align=center>";

$query1 = "select e.S_ID, e.E_LIBELLE, e.TE_CODE, te.TE_VICTIMES, te.TE_VEHICULES, te.TE_MATERIEL 
        from evenement e, type_evenement te 
        where e.TE_CODE = te.TE_CODE
        and e.E_CODE=".$evenement;
$result1=mysqli_query($dbc,$query1);
custom_fetch_array($result1);

if (check_rights($id, 15, $S_ID)) $granted=true;
else if ( is_chef_evenement($id, $evenement) ) $granted=true;
else if(is_operateur_pc($id,$evenement)) $granted=true;
else $granted=false;

if ( ! $granted ) {
    write_msgbox("ERREUR", $error_pic,"Vous n'avez pas la permission d'imprimer ce rapport. <p align=center><a href='javascript:history.back(1);'><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
    exit;
}


// ---------------------------------------
//  Sauver
// ---------------------------------------
if ( $save == 1 ) {
    $msgs_to_print="0";
    $query="update evenement_log set EL_IMPRIMER=0 where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    
    foreach ($_POST as $key => $val) {
        if ( substr($key,0,5) == 'check' ) {
            $msgid=intval(substr($key,6,100));
            if ( $msgid > 0 ) 
                $msgs_to_print .= ",".$msgid;
        }
    }
    $query="update evenement_log set EL_IMPRIMER=1 where E_CODE=".$evenement." and EL_ID in (".$msgs_to_print.")";
    $result=mysqli_query($dbc,$query);

    echo "<body  class='top15' onload=\"print_report('".$evenement."','".$S_ID."','".$responsable."','".$nombres."','".$statistiques."','".$show_vehicules."','".$show_materiel."','".$show_cav."');\">";
    exit;
}
else  
    echo "<body  class='top15'>";

// ---------------------------------------
//  Form
// ---------------------------------------

$html .= "<div align=center><font size=4><b>".$E_LIBELLE."</b></font><br>";
$html .= "<form name=rapport action='evenement_rapport.php' method='POST'>
        <input type='hidden' name='save' value='1'>
        <input type='hidden' name='evenement' value='".$evenement."'>";
$html .= "<p><table cellspacing=0 border=0>";
$html .= "<tr><td CLASS='MenuRub' colspan=3>Choix des informations à afficher</td></tr>";
if ( $responsable == 1 ) $checked='checked'; else $checked='';
$html .= "<tr bgcolor=$mylightcolor><td width=30 align=center><input type='checkbox' name='responsable' value=1 $checked></td>
              <td colspan=2 width=530 align=left >Responsable événement</td>
          </tr>";
if ( $nombres == 1 ) $checked='checked'; else $checked='';
$html .= "<tr bgcolor=$mylightcolor><td width=30 align=center><input type='checkbox' name='nombres' value=1 $checked></td>
              <td colspan=2 width=530 align=left >Principaux chiffres <span class=small> - nombre de messages, d'intervenants, stats globales</span></td>
          </tr>";
if ( $TE_VICTIMES == 1 ) {
    if ( $statistiques == 1 ) $checked='checked'; else $checked='';
    $html .= "<tr bgcolor=$mylightcolor><td align=center><input type='checkbox' name='statistiques' value=1 $checked></td>
                  <td colspan=2 align=left >Statistiques interventions<span class=small> - détail nombre d'interventions et de victimes, nombre de malaises ...</span></td>
              </tr>";
    if ( $show_cav == 1 ) $checked='checked'; else $checked='';
    $html .= "<tr bgcolor=$mylightcolor><td align=center><input type='checkbox' name='show_cav' value=1 $checked></td>
        <td colspan=2 align=left >Centres d'accueils des victimes</td>
        </tr>";
}
if ( $TE_VEHICULES == 1 ) {
    if ( $show_vehicules == 1 ) $checked='checked'; else $checked='';
    $html .= "<tr bgcolor=$mylightcolor><td align=center><input type='checkbox' name='show_vehicules' value=1 $checked></td>
                  <td colspan=2 align=left >Véhicules engagés</td>
              </tr>";
}
if ( $TE_MATERIEL == 1 ) {
    if ( $show_materiel == 1 ) $checked='checked'; else $checked='';
    $html .= "<tr bgcolor=$mylightcolor><td align=center><input type='checkbox' name='show_materiel' value=1 $checked></td>
                  <td colspan=2 align=left >Matériel engagé</td>
              </tr>";
}

$query="select e.EL_ID, e.E_CODE, e.TEL_CODE ,date_format(e.EL_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(e.EL_DEBUT,'%H:%i') HEURE_DEBUT,
    e.EL_IMPORTANT,e.EL_IMPRIMER,e.EL_TITLE, 
    date_format(e.EL_DEBUT, '%Y-%m-%d') as M_DATE_DEBUT, date_format(e.EL_FIN, '%Y-%m-%d') as M_DATE_FIN
    from evenement_log e
    where e.E_CODE=".$evenement."
    order by EL_DEBUT desc, HEURE_DEBUT desc";
$result=mysqli_query($dbc,$query);
write_debugbox( $query );

$html .= "<tr><td CLASS='MenuRub' colspan=3>Interventions et messages 
    <input type='checkbox' name='yesall' id='yesall' onclick='CheckAll(true)' title=\"tout cocher\" > <label for='yesall'>tout cocher</label>
    <input type='checkbox' name='noall' id='noall' onclick='CheckAll(false)' title=\"tout décocher\"> <label for='noall'>tout décocher </label>
    </td></tr>";

if ( @mysqli_num_rows($result) > 0 ) {
    while ( custom_fetch_array($result)) {
        if ( $TEL_CODE == 'I' ) {
            if ( $EL_IMPORTANT == 1 ) {
                $img="class='fa fa-medkit' style='color:red' title='intervention importante'";
            }
            else $img="class='fa fa-medkit' title='intervention'";
        }
        else {
            if ( $EL_IMPORTANT == 1 ) {
                $img="class='far fa-file-text' style='color:red' title='message important'";
            }
            else $img="class='far fa-file-text' title='message'";
        } 
        if ( $EL_IMPRIMER == 1 ) $checked = 'checked';
        else $checked = '';
            
        $html .= "<tr bgcolor=$mylightcolor>
              <td width=30 align=center><input type='checkbox' name='check_".$EL_ID."' value=1 $checked></td>
              <td width=130 align=left ><i ".$img."></i> <span class=small>".$DATE_DEBUT." ".$HEURE_DEBUT."</span></td>
              <td width=400> ".$EL_TITLE."</td>
              </tr>";
    }
}
else {
    $html .= "<tr bgcolor=$mylightcolor><td colspan=3 class=small>Aucun message enregistré</td></tr>";
}
$html .= "</table><p>";
$html .= "<a class='btn btn-default' href='#' onclick='javascript:rapport.submit();'
        title='afficher le rapport PDF et sauvegarder les choix'><i class='far fa-file-pdf fa-lg' style='color:red;'></i> Afficher</a>";
$html .= " <input type=button  class='btn btn-default' value='Fermer' onclick='window.close();'> </form>";
print $html;
writefoot();

?>
