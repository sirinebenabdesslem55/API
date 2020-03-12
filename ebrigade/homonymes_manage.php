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
$mysection=$_SESSION['SES_SECTION'];
destroy_my_session_if_forbidden($id);
writehead();
print "<script type='text/javascript' src='js/personnel.js'></script>";
print "</head>";

//====================================================================
// Parameters
//====================================================================

if ( isset ($_GET["pid"])) $pid=intval($_GET["pid"]);
else if ( isset ($_POST["pid"])) $pid=intval($_POST["pid"]);
else $pid=0;

if ( isset ($_GET["doublon_id"])) $doublon_id=intval($_GET["doublon_id"]);
else if ( isset ($_POST["doublon_id"])) $doublon_id=intval($_POST["doublon_id"]);
else $doublon_id=0;

if ( $pid == 0 or $doublon_id == 0) {
    param_error_msg();
    exit;
}

//====================================================================
// Permissions
//====================================================================
$pid_section=get_section_of($pid);
$P_STATUT=get_statut ($pid);
if ((check_rights($id, 37,"$pid_section") or (check_rights($id, 37) and check_rights($id, 24))) and $P_STATUT == 'EXT') $update_allowed=true;
else if (( check_rights($id, 2,"$pid_section") or (check_rights($id, 2) and check_rights($id, 24))) and $P_STATUT <> 'EXT') $update_allowed=true;
else $update_allowed=false;

$doublon_section=get_section_of($doublon_id);
$P_STATUT=get_statut ($doublon_id);
if ((check_rights($id, 37,"$doublon_section") or (check_rights($id, 37) and check_rights($id, 24))) and $P_STATUT == 'EXT') $update_doublon_allowed=true;
else if (( check_rights($id, 2,"$doublon_section") or (check_rights($id, 2) and check_rights($id, 24))) and $P_STATUT <> 'EXT') $update_doublon_allowed=true;
else $update_doublon_allowed=false;

if ( $update_allowed and $update_doublon_allowed ) $update_possible = true;
else $update_possible = false;

//====================================================================
// get data
//====================================================================

$query="select P_ID, P_NOM P_NOM0, P_NOM_NAISSANCE, P_PRENOM P_PRENOM0, P_PRENOM2, date_format(P_BIRTHDATE, '%d-%m-%Y') 'P_BIRTHDATE',
        P_BIRTHPLACE, P_SEXE, P_SECTION, P_STATUT, date_format(P_CREATE_DATE, '%d-%m-%Y') 'P_CREATE_DATE' from pompier where P_ID=".$pid;
$result=mysqli_query($dbc,$query);
if (mysqli_num_rows($result) == 0) {
    param_error_msg();
    exit;
}
custom_fetch_array($result);

if ( $P_PRENOM2 == "none" ) $P_PRENOM2="";
$born = "";$nom1="";
if ( $P_BIRTHDATE <> "" ) {
    if ( $P_SEXE == 'M' ) $born = "<br>n� le ".$P_BIRTHDATE;
    else $born = "<br>n�e le ".$P_BIRTHDATE;
    if ( $P_BIRTHPLACE <> '' ) $born .= " � ".$P_BIRTHPLACE;
}
if ( $P_NOM_NAISSANCE <> '' ) {
    if ( $P_SEXE == 'M' ) $nom1 = "<br>n� ".$P_NOM_NAISSANCE;
    else $nom1 = "<br>n�e ".$P_NOM_NAISSANCE;
}
if ( $P_CREATE_DATE <> "" ) 
    $created= "<br>fich�e cr��e le ".$P_CREATE_DATE;
else 
    $created="";

$out = "<font size=3><b>Homonyme de ".my_ucfirst($P_PRENOM0)." ".my_ucfirst($P_PRENOM2)." ".strtoupper($P_NOM0)." (N�".$pid.")</b></font>".$nom1.$born.$created;

$query=" select p.P_ID, p.P_NOM, p.P_NOM_NAISSANCE, p.P_PRENOM, p.P_PRENOM2, p.P_SEXE, p.P_SECTION,
            date_format(p.P_BIRTHDATE, '%d-%m-%Y') P_BIRTHDATE1, p.P_BIRTHPLACE P_BIRTHPLACE1,
            date_format(P_CREATE_DATE, '%d-%m-%Y') 'P_CREATE_DATE',
            s.S_CODE, s.S_DESCRIPTION, p.P_STATUT,
            t.S_DESCRIPTION 'STATUT'
            from pompier p, section s, statut t
            where p.P_SECTION = s.S_ID
            and p.P_STATUT = t.S_STATUT
            and p.P_ID = ".$doublon_id;
$result=mysqli_query($dbc,$query);
if (mysqli_num_rows($result) == 0) {
    param_error_msg();
    exit;
}
custom_fetch_array($result);

if ( $P_PRENOM2 == "none" ) $P_PRENOM2="";

$born = "";$nom1="";
if ( $P_BIRTHDATE1 <> "" ) {
    if ( $P_SEXE == 'M' ) $born = "n� le ".$P_BIRTHDATE1;
    else $born = "n�e le ".$P_BIRTHDATE1;
    if ( $P_BIRTHPLACE1 <> '' ) $born .= " � ".$P_BIRTHPLACE1;
}
if ( $P_NOM_NAISSANCE <> '' ) {
    if ( $P_SEXE == 'M' ) $nom1 = " n� ".$P_NOM_NAISSANCE;
    else $nom1 = " n�e ".$P_NOM_NAISSANCE;
}

if ( check_rights($id,3,"$P_SECTION") 
    or ( check_rights($id,37,"$P_SECTION") and $P_STATUT == 'EXT' )) 
    $delete_possible = true;
else
    $delete_possible = false;

if ( fixcharset(rtrim($P_NOM0)) <> fixcharset(rtrim($P_NOM)) or fixcharset(rtrim($P_PRENOM0)) <> fixcharset(rtrim($P_PRENOM)) ) {
    write_msgbox("Fusion impossible",$error_pic,"Les 2 fiches ont des noms et pr�noms diff�rents, fusion interdite<p>
    <input type='button' class='btn btn-default' value='Annuler' onclick=\"bouton_redirect('upd_personnel.php?pompier=".$pid."');\">",30,30);
    exit;
}

//====================================================================
// Save
//====================================================================
if (isset($_POST["pid"])) {
    if ( ! $update_possible ) {
        write_msgbox("Permission insuffisante",$error_pic,"LVous n'avez pas les permissions requises pour effectuer cette action<p>
        <input type='button' class='btn btn-default' value='Annuler' onclick=\"bouton_redirect('upd_personnel.php?pompier=".$pid."');\">",30,30);
        exit;
    }
    verify_csrf('fusion');
    if ( isset($_POST["formations"]) ) {
        $query="update personnel_formation set P_ID=".$pid." where P_ID=".$doublon_id;
        mysqli_query($dbc,$query);
    }
    if ( isset($_POST["competences"]) ) {
        $query="update qualification set P_ID=".$pid." where P_ID=".$doublon_id;
        mysqli_query($dbc,$query);
    }
    if ( isset($_POST["participations"]) ) {
        $query="update evenement_participation set P_ID=".$pid." where P_ID=".$doublon_id;
        mysqli_query($dbc,$query);
    }
    if ( isset($_POST["radier"]) ) {
        $query="update pompier set P_OLD_MEMBER='1' where P_ID=".$doublon_id;
        mysqli_query($dbc,$query);
        insert_log('UPDSTP', $doublon_id, "actif => ancien pour cause de doublon avec ".$pid);
    }
    if ( isset($_POST["supprimer"]) and $delete_possible ) {
        delete_personnel($doublon_id);
    }
    echo "<body onload=\"self.location.href='upd_personnel.php?pompier=".$pid."';\" >";
    exit;
}

//====================================================================
// Afficher formulaire
//====================================================================

if ( $P_BIRTHDATE1 == $P_BIRTHDATE and $P_BIRTHDATE <> '') 
    $alert = "<div class='alert alert-success' role='alert'> Les 2 fiches homonymes ont la m�me date de naissance, il s'agit certainement de doublons.</div>";
else if ( $P_BIRTHDATE <> '' and $P_BIRTHDATE1 <> '' ) 
    $alert = "<div class='alert alert-danger' role='alert'> Les 2 fiches ont des dates de naissance diff�rentes, ce sont des homonymes pas des doublons.</div>";
else 
    $alert="<div class='alert alert-warning' role='alert'> Attention, les dates de naissance ne sont pas renseign�es pour les 2 fiches, on ne peut pas confirmer que ce sont des doublons.</div>";

$title="ouvrir cette fiche - ".my_ucfirst($P_PRENOM)." ".$P_PRENOM2." ".strtoupper($P_NOM);
if ( ! $update_doublon_allowed ) $alert2 = "<div class='alert alert-warning' role='alert'> Vous n'avez pas les permissions de modifier la fiche 
<a href=upd_personnel.php?pompier=".$doublon_id."  title=\"".$title."\">".$doublon_id."</a>. Demandez � un responsable national de faire la fusion des fiches pour vous.</div>";
else $alert2="";
    
$NB1=count_entities("qualification", "P_ID=".$doublon_id);
$NB2=count_entities("personnel_formation", "P_ID=".$doublon_id);
$NB3=count_entities("evenement_participation ep, evenement e, evenement_horaire eh", "ep.P_ID=".$doublon_id." and eh.e_code = e.e_code and ep.eh_id=eh.eh_id and ep.E_CODE=e.E_CODE and e.E_CANCELED = 0");

if ( $update_possible ) {
    $out .= "<form action='homonymes_manage.php' method='POST'><input type='hidden' name='pid' value='".$pid."'><input type='hidden' name='doublon_id' value='".$doublon_id."'>";
    $out .= insert_csrf('fusion');
}
$out .=  $alert.$alert2."<div align=center ><table cellspacing=0>";
$out .= "<tr class=TabHeader><td colspan=2>Informations homonyme</td></tr>";
$out .= "<tr bgcolor=$mylightcolor><td ><b>N� membre</b></td>
<td width=250 ><a href=upd_personnel.php?pompier=".$doublon_id."  title=\"".$title."\">".$doublon_id."</a></td></tr>";
$out .= "<tr bgcolor=$mylightcolor><td width=120><b>Nom</b></td><td>".my_ucfirst($P_PRENOM)." ".my_ucfirst($P_PRENOM2)." ".strtoupper($P_NOM).$nom1."</td></tr>";
$out .= "<tr bgcolor=$mylightcolor><td><b>Naissance</b></td><td>".$born."</td></tr>";
$out .= "<tr bgcolor=$mylightcolor><td><b>Statut</b></td><td>".$STATUT."</td></tr>";
$out .= "<tr bgcolor=$mylightcolor><td><b>Section</b></td><td>".$S_CODE." - ".$S_DESCRIPTION."</td></tr>";
$out .= "<tr bgcolor=$mylightcolor><td><b>Fiche cr�� le</b></td><td>".$P_CREATE_DATE."</td></tr>";

if ( $NB1 > 0 and $update_possible) $move=" <label for='competences'>d�placer vers N�$pid</label> <input type='checkbox' id='competences' name='competences' checked value=1
title='cocher pour d�placer les comp�tences sur la fiche principale' > ";
else $move ="";
$out .= "<tr bgcolor=$mylightcolor><td><b>Comp�tences</b></td>
<td><a href=upd_personnel.php?pompier=".$doublon_id."&tab=2 title=\"".$title."\"><span class='badge' title='Voir les comp�tences'>".$NB1."</span></a>$move</td>
</tr>";

if ( $NB2 > 0 and $update_possible) $move=" <label for='formations'>d�placer vers N�$pid</label> <input type='checkbox' id='formations' name='formations' checked value=1
title='cocher pour d�placer les formations sur la fiche principale'> ";
else $move ="";
$out .= "<tr bgcolor=$mylightcolor><td><b>Formations</b></td>
<td><a href=upd_personnel.php?pompier=".$doublon_id."&tab=3 title=\"".$title."\"><span class='badge' title='Voir les formations'>".$NB2."</span></a>$move</td>
</tr>";

if ( $NB3 > 0 and $update_possible) $move=" <label for='participations'>d�placer vers N�$pid</label> <input type='checkbox' id='participations' name='participations' checked value=1
title='cocher pour d�placer les participations sur la fiche principale'> ";
else $move ="";
$out .= "<tr bgcolor=$mylightcolor><td><b>Participations</b></td>
<td><a href=upd_personnel.php?pompier=".$doublon_id."&tab=4 title=\"".$title."\"><span class='badge' title='Voir les participations'>".$NB3."</span></a>
$move</td>
</tr>";

if ( $update_possible )
$out .= "<tr bgcolor=$mylightcolor><td><b>Changer le statut </b></td>
<td><label for='radier'>radier cette fiche N�$doublon_id</label> <input type='checkbox' id='radier' name='radier' checked value=1 title='cocher pour mettre la fiche � ancien membre'> </td>
</tr>";

if ( $delete_possible)
$out .= "<tr bgcolor=$mylightcolor><td><b>Supprimer</b></td>
<td><label for='supprimer'>supprimer cette fiche N�$doublon_id</label> <input type='checkbox' id='supprimer' name='supprimer' value=1 checked title='cocher pour mettre la fiche � ancien membre'></td></tr>";

$out .= "</table>";

$out .=" <p> <input type='button' class='btn btn-default' value='Annuler' onclick=\"bouton_redirect('upd_personnel.php?pompier=".$pid."');\">";
if ( $update_possible ) $out .=" <input type='submit' class='btn btn-default' value='Sauver'></form>";
$out .="</div>";

print $out;
writefoot();

?>