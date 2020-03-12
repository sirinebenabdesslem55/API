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
get_session_parameters();

$possibleorders= array('VI_DECEDE','VI_NUMEROTATION','VI_DETRESSE_VITALE','VI_MALAISE','VI_TRANSPORT','VI_REPOS','VI_TRAUMATISME','VI_IMPLIQUE','VI_AGE','VI_NOM','PAYS',
            'VI_SEXE','CAV_ID','CAV_ENTREE','CAV_SORTIE','CAV_REGULATED','EL_FIN','EL_DEBUT','IDENTIFICATION','VI_MEDICALISE','VI_INFORMATION','VI_SOINS');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='VI_NUMEROTATION';

if ( $type_victime == 'cav' ){
    $title="Victimes dans les centres d'accueil ";
    $picture='h-square';
    $color=$mydarkcolor;
}
else if ( $type_victime == 'intervention' ){
    $title='Victimes sur interventions';
    $picture='medkit';
    $color=$mydarkcolor;
}
else if ( intval($type_victime) > 0 ){
    $title="Victimes du centre d'accueil";
    $query = "select CAV_OUVERT from centre_accueil_victime where CAV_ID=".intval($type_victime);  
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $ouvert = $row["CAV_OUVERT"];
    $picture='h-square';
    if ( $ouvert ) $color='green';
    else  $color='red';
}
else {
    $picture = 'medkit';
    $title="Victimes de l'�v�nement";
    $color=$mydarkcolor;
}
writehead();

//=====================================================================
// check_security
//=====================================================================
$granted_update=false;
$chefs=get_chefs_evenement($evenement_victime);
$chefs_parent=get_chefs_evenement_parent($evenement_victime);
if ( intval($type_victime) > 0 ) {
    $query="select CAV_RESPONSABLE, E_CODE from centre_accueil_victime where CAV_ID=".$type_victime;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $responsable=$row[0];
    $evenement=$row[1];
    if ( $responsable == $id ) $granted_update=true;
    else if (check_rights($id, 15, (get_section_organisatrice ( $evenement )))) $granted_update=true;
}
else if ($evenement_victime > 0 ) {
    if (check_rights($id, 15, (get_section_organisatrice ( $evenement_victime )))) $granted_update=true;
}
if ( in_array($id,$chefs) ) $granted_update=true;
if ( in_array($id,$chefs_parent) ) $granted_update=true;
if ( is_operateur_pc($id,$evenement_victime)) $granted_update=true;

if ($granted_update) 
    $disabled='';
else  {
    $disabled='disabled';
    check_all(15);
    check_all(24);
}


if ( $autorefresh == 1 ) echo "<meta http-equiv='Refresh' content='20'>";
?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.typevictime{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<?php
forceReloadJS('js/liste_victimes.js');

$querycnt="select count(*) as NB";
$query1="select VI_ID,victime.EL_ID, VI_NOM, VI_PRENOM, VI_SEXE, VI_ADDRESS, date_format(VI_BIRTHDATE, '%d-%m-%Y') VI_BIRTHDATE,
        VI_DETRESSE_VITALE, VI_INFORMATION, VI_SOINS, VI_MEDICALISE, VI_TRANSPORT, VI_VETEMENT, VI_ALIMENTATION, VI_TRAUMATISME, VI_REPOS, VI_DECEDE, VI_MALAISE, p.NAME PAYS,
        victime.D_CODE,victime.T_CODE,VI_COMMENTAIRE, VI_REFUS, VI_IMPLIQUE, VI_NUMEROTATION, destination.D_NAME, transporteur.T_NAME, VI_AGE,
        victime.CAV_ID, date_format(CAV_ENTREE, '%d-%m-%Y') DATE_ENTREE, date_format(CAV_ENTREE, '%H:%i') HEURE_ENTREE,
        date_format(CAV_SORTIE, 'le %d-%m-%Y � %H:%i') DATE_SORTIE, date_format(CAV_SORTIE, '%H:%i') HEURE_SORTIE,
        CAV_REGULATED, cav.CAV_NAME, CAV_REGULATED,
        el.EL_TITLE, date_format(el.EL_DEBUT, '%d-%m-%Y') DEBUT_INTERVENTION, date_format(el.EL_DEBUT, '%H:%i') HEURE_DEBUT_INTERVENTION,
        date_format(el.EL_FIN, 'le %d-%m-%Y � %H:%i') FIN_INTERVENTION, date_format(el.EL_FIN, '%H:%i') HEURE_FIN_INTERVENTION,
        IDENTIFICATION";
        
$queryadd =" from victime left join evenement_log el on el.EL_ID = victime.EL_ID
            left join centre_accueil_victime cav on cav.CAV_ID = victime.CAV_ID
            left join pays p on p.ID = victime.VI_PAYS, destination , transporteur
        where victime.D_CODE = destination.D_CODE
        and victime.T_CODE = transporteur.T_CODE";
        
if ( $type_victime == 'intervention' )  $queryadd .= " and victime.EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement_victime.")";
else if ( $type_victime == 'cav' )  $queryadd .= " and victime.CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement_victime.")";
else if ( intval($type_victime) > 0 )  $queryadd .= " and victime.CAV_ID = ".intval($type_victime);
else  $queryadd .= " and ( victime.EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement_victime.")
                                                    or victime.CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement_victime."))";
// filtering
if ( $in_cav == 1 ) $queryadd .= " and ((CAV_SORTIE is null and victime.CAV_ID > 0 ) or (EL_FIN is null and victime.CAV_ID is null ))";
if ( $a_reguler == 1 ) $queryadd .= " and CAV_REGULATED = 0 ";

// order
if ( $order == 'CAV_ID' ) $query1 .= $queryadd ." order by CAV_NAME,EL_TITLE";
else if ( $order == 'VI_NOM' ) $query1 .= $queryadd ." order by VI_NOM,VI_PRENOM";
else $query1 .= $queryadd ." order by ".$order;
if  (! in_array($order, array('CAV_ID','PAYS','VI_NOM','VI_NUMEROTATION','VI_AGE')))  $query1 .= ' desc';
$querycnt .= $queryadd;

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];
$cmt="";
$body="<body>";

// si victimes a r�guler par m�decin, beep
if ( $number > 0 ) {
    $querycnt2 = $querycnt." and CAV_REGULATED = 0 ";
    $resultcnt2=mysqli_query($dbc,$querycnt2);
    $rowcnt2=mysqli_fetch_array($resultcnt2);
    $number2 = $rowcnt2[0];

    if ( $number2 > 0 ) {
        $body = "<audio autoplay='false' id='beep' src='js/sound/bike-horn.mp3'></audio>
        <body onload='beep()'>";
        $v='victime';
        if ( $number2 > 1 ) $v .='s';
        $cmt = "<div class='alert alert-danger' role='alert' align='center'>Dont ".$number2." � r�guler par le m�decin ou le PC</div>";
    }
}


$html =  $body."<div align=center><table class='noBorder'>
      <tr><td width =60><i class='fa fa-".$picture." fa-3x' style='color:".$color.";' title=\"".$title."\"></i></td><td>
      <font size=4><b> ".$title."</b></font> <span class='badge'>".$number."  victimes</span></td></tr></table>".$cmt;

$html .= "<table class='noBorder' >";

// permissions
$granted_update=false;
if ($evenement_victime > 0 ) {
    if (check_rights($id, 15, get_section_organisatrice ( $evenement_victime ))) $granted_update=true;
}
if ( ! $granted_update ) {
    if ( is_chef_evenement($id, $evenement_victime) ) $granted_update=true;
    else if ( is_operateur_pc($id,$evenement_victime)) $granted_update=true;
}

//filtre type_victime
$html .= "<tr>
        <td align=left colspan=4><select id='type_victime' name='type_victime' 
        onchange=\"orderfilter('".$order."',this.value,'".$in_cav."','".$a_reguler."')\">";

if ( $type_victime =='ALL' ) $selected="selected";
else $selected="";
$html .= "<option value='ALL' $selected>Tous les types de victimes</option>\n";
if ( $type_victime =='intervention' ) $selected="selected";
else $selected="";
$html .= "<option value='intervention' $selected>Sur Interventions</option>\n";

$query2="select c.CAV_ID, c.CAV_NAME, c.CAV_OUVERT, c.CAV_RESPONSABLE
                from centre_accueil_victime c
                where c.E_CODE=".$evenement_victime."
                order by c.CAV_NAME";
$result2=mysqli_query($dbc,$query2);
$nb_cav = mysqli_num_rows($result2);
$cav_ouvert_found=false;
if ( $nb_cav > 0 ) {
    $html .= "<optgroup class='categorie' label=\"Dans un Centre d'Accueil des Victimes\">";
    if ( $type_victime =='cav' ) $selected="selected";
    else $selected="";
    $html .= "<option class='typevictime' value='cav' $selected>Tous les centres d'accueil</option>";
    
    while (custom_fetch_array($result2)) {
        if ( $CAV_RESPONSABLE == $id ) $granted_update=true;
        if ( $CAV_OUVERT == 0 ) $CAV_NAME .= " - ferm�";
        else $cav_ouvert_found = true;
        if ( intval($type_victime) == $CAV_ID ) $selected="selected";
        else $selected="";
        $html .= "<option class='typevictime' value='".$CAV_ID."' $selected>".$CAV_NAME."</option>";
    }

}
$html .= "</select></td>";

if ( $granted_update and $cav_ouvert_found) {
    $url="victimes.php?from=list&action=insert&evenement=".$evenement_victime;
    if ( $type_victime <> 'intervention' and $nb_cav > 0) {
        $numcav = intval($type_victime);
        $url .= "&numcav=".$numcav;
        $html .= "<td>";
        $html .= "<input type='button' class='btn btn-default' value='Ajouter' name='ajouter' 
                title=\"Ajouter une victime dans un centre d'accueil\"  onclick=\"redirect('".$url."');\">";
        $html .= "<td><a class='btn btn-default' href='scan_victime.php?evenement=".$evenement_victime."&numcav=".$numcav."' 
                    title='Scanner QR Code pour cr�er la fiche victime' ><i class='fa fa-qrcode fa-lg' style='color:purple;'></i> Scan</a></td>";       
        $html .= "</td>";
    }
}
else $html .= "<td></td>";
$html .= "</tr>";

// seulement les pr�sents dans un CAV
if ($in_cav == 1 ) $checked='checked';
else $checked='';
$in_cav_checkbox= "<td align=right class=small2><label for='in_cav'>En cours seulement</label></td><td width=30 align=left>
        <input type='checkbox' name='in_cav' id='in_cav' value='1' $checked 
        title=\"cocher pour afficher seulement les victimes en cours de traitement dans un centre d'accueil des victimes\"
        onClick=\"orderfilter2('".$order."','".$type_victime."',this, document.getElementById('a_reguler'));\"/></td>";
if ( $a_reguler == 1 ) $checked='checked';
else $checked='';
$a_reguler_checkbox= "<td align=right class=small2><label for='a_reguler'>A r�guler seulement</label></td><td width=30 align=left>
        <input type='checkbox' name='a_reguler' id='a_reguler' value='1' $checked 
        title=\"cocher pour afficher seulement les victimes devant �tre r�gul�es par le m�decin\"
        onClick=\"orderfilter2('".$order."','".$type_victime."',document.getElementById('in_cav'),this);\"/></td>";

// autorefresh
if ( $autorefresh == 1 ) $checked='checked';
 else $checked='';
$a_reguler_checkbox .= "<td align=right class=small2><label for='autorefresh' class=small>rafra�chissement auto.</label></td><td width=30 align=left>
        <input type='checkbox' id='autorefresh' name='autorefresh' value='1'
        title='cocher pour activer le rafraichissement automatique toutes les 20 secondes'
        onclick=\"autorefresh_victimes();\" $checked></td>";

$html .= "<tr>".$in_cav_checkbox.$a_reguler_checkbox."</tr>";

 

$html .= "<tr><td colspan=6>";
// ====================================
// pagination
// ====================================
require_once('paginator.class.php');
$pages = new Paginator;  
$pages->items_total = $number;  
$pages->mid_range = 9;  
$pages->paginate();  
if ( $number > 10 ) {
    $html .= $pages->display_pages();
    $html .= $pages->display_jump_menu(); 
    $html .= $pages->display_items_per_page(); 
    $query1 .= $pages->limit;
}

//$html .= $query1;

$result1=mysqli_query($dbc,$query1);
$numberrows=mysqli_num_rows($result1);

$html .= "</td></tr></table>";

if ( $number > 0 ) {
$html .= "<table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

if ( $type_victime == 'intervention' ) {
    $d = "<a href=liste_victimes.php?order=EL_DEBUT class=TabHeader title='Date intervention'>Date</a>";
    $e = "<a href=liste_victimes.php?order=EL_DEBUT class=TabHeader title='D�but intervention'>D�but</a>";
    $s = "<a href=liste_victimes.php?order=EL_FIN class=TabHeader  title='Fin intervention'>Fin</a>";
}
else {
    $d = "<a href=liste_victimes.php?order=CAV_ENTREE class=TabHeader>Date</a>";
    $e = "<a href=liste_victimes.php?order=CAV_ENTREE class=TabHeader>Entr�e</a>";
    $s = "<a href=liste_victimes.php?order=CAV_SORTIE class=TabHeader>Sortie</a>";
}
$html .= "<tr class='TabHeader'>";
$html .= "<td width=30 align=center>
    <a href=liste_victimes.php?order=VI_NUMEROTATION class=TabHeader>Num.</a></td>";
$html .= "<td width=30 align=center>
    <a href=liste_victimes.php?order=IDENTIFICATION class=TabHeader>Id</a></td>";
$html .= "<td width=250 align=left>
    <a href=liste_victimes.php?order=CAV_ID class=TabHeader>Localisation actuelle</a></td>
    <td width=22></td>
    <td width=83 align=center>
    ".$d."
    <td width=40 align=center>
    ".$e."
    <td width=40 align=center>
    ".$s;
$html .= "<td width=130 align=center>
    <a href=liste_victimes.php?order=VI_NOM class=TabHeader>Identit�</a>"; 
$html .= "<td width=30 align=center>
    <a href=liste_victimes.php?order=VI_AGE class=TabHeader>Age</a></td>";
$html .= "<td width=35 align=center>
    <a href=liste_victimes.php?order=VI_SEXE class=TabHeader >Sexe</a></td>";
$html .= "<td width=75 align=center>
    <a href=liste_victimes.php?order=PAYS class=TabHeader>Nat.</a></td>";            
$html .= "<td width=35 align=center><a href=liste_victimes.php?order=VI_DECEDE class=TabHeader>DCD</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_DETRESSE_VITALE class=TabHeader title='D�tresse vitale'>D�tresse</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_MALAISE class=TabHeader>Malaise</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_TRAUMATISME class=TabHeader>Trauma</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_SOINS class=TabHeader>Soins</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_TRANSPORT class=TabHeader>Transport</a></td>  
      <td width=35 align=center><a href=liste_victimes.php?order=VI_REPOS class=TabHeader title='Repos sous surveillance'>Repos</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_INFORMATION class=TabHeader title='Personne assist�e'>Assist�</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_IMPLIQUE class=TabHeader title='Impliqu� indemne'>Impliqu�</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_MEDICALISE class=TabHeader title='Victime m�dicalis�e'>M�dic.</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=REGULATED class=TabHeader title='R�gulation faite par le m�decin'>R�gul.</a></td>";
$html .= "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result1)) {
    $EL_ID = intval($EL_ID);
    if ( $VI_DETRESSE_VITALE == 1 ) $VI_DETRESSE_VITALE = "<i class='fa fa-exclamation-circle fa-lg' style='color:red;' title='d�tresse vitale'></i>";
    else $VI_DETRESSE_VITALE = "";
    if ( $VI_TRAUMATISME == 1 ) $VI_TRAUMATISME = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;' title='traumatisme'></i>";
    else $VI_TRAUMATISME = "";
    if ( $VI_SOINS == 1 ) $VI_SOINS = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;' title='soins donn�s � la victime'></i>";
    else $VI_SOINS = "";
    if ( $VI_DECEDE == 1 ) $VI_DECEDE = "<i class='fa fa-exclamation-circle fa-lg' style='color:red;'  title='d�c�d�'></i>";
    else $VI_DECEDE = "";
    if ( $VI_MALAISE == 1 ) $VI_MALAISE = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;' title='malaise'></i>";
    else $VI_MALAISE = "";
    if ( $VI_TRANSPORT == 1 ) $VI_TRANSPORT = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;'  title=\"transport vers ".$D_NAME." par ".$T_NAME."\"></i>";
    else $VI_TRANSPORT = "";
    if ( $VI_REPOS == 1 ) $VI_REPOS = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;'  title=\"Repos sous surveillance\"></i>";
    else $VI_REPOS = "";
    if ( $VI_IMPLIQUE == 1 ) $VI_IMPLIQUE = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;'  title=\"Impliqu� indemne\"></i>";
    else $VI_IMPLIQUE = "";
    if ( $VI_INFORMATION == 1 ) $VI_INFORMATION = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;'  title=\"Personne assist�e\"></i>";
    else $VI_INFORMATION = "";
    if ( $VI_MEDICALISE == 1 ) $VI_MEDICALISE = "<i class='fa fa-exclamation-circle fa-lg' style='color:red;'  title=\"Victime m�dicalis�e\"></i>";
    else $VI_MEDICALISE = "";
    
    if ( $IDENTIFICATION <> "" ) 
        $IDENTIFICATION="<i class ='fa fa-hashtag fa-lg' title=\"".$IDENTIFICATION."\"></i>";
    if ( $CAV_ID > 0 ) {
        $e = "<span title=\"entr�e au centre d'accueil ".$DATE_ENTREE."\">".$HEURE_ENTREE."</span>";
        $s = "<span title=\"sortie du centre d'accueil ".$DATE_SORTIE."\">".$HEURE_SORTIE."</span>";          
    }
    else {
        $DATE_ENTREE=$DEBUT_INTERVENTION;
        $DATE_SORTIE=$FIN_INTERVENTION;
        $HEURE_ENTREE=$HEURE_DEBUT_INTERVENTION;
        $HEURE_SORTIE=$HEURE_FIN_INTERVENTION;
        if ( $HEURE_SORTIE == '00:00' ) $HEURE_SORTIE= "";
        $e = "<span title=\"d�but intervention ".$DATE_ENTREE."\">".$HEURE_ENTREE."</span>";
        $s = "<span title=\"fin intervention ".$DATE_SORTIE."\">".$HEURE_SORTIE."</span>";
    }
    if ( $CAV_NAME <> "") $localisation = $CAV_NAME;
    else $localisation = "Sur intervention";
    
    if ( $EL_ID > 0 ) $inter_icon="<i class = 'fa fa-medkit' title='Victime initialement prise en compte sur intervention'></i>";
    else $inter_icon="";
    
    $REGULATED=intval($CAV_REGULATED);
    if ( $REGULATED == 1 ) $REGUL_ICON="<i class='fa fa-check-square fa-lg' style='color:green;' title='r�gulation faite par le m�decin ou le PC'></i>";
    else if ( $REGULATED == 0 ) $REGUL_ICON="<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='� r�guler par le m�decin ou le PC'></i>";
    else $REGUL_ICON="";
    
    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor=$mylightcolor;
    }
    else {
        $mycolor="#FFFFFF";
    }
    
    if ( $REGULATED == 0 and $CAV_ID > 0 ) $span = "<span class ='red12' title='� r�guler par m�decin ou le PC'>";
    else if ( $HEURE_SORTIE == '' ) $span = "<span class ='green12' title='en cours de traitement'>";
    else $span="<span>";
      
    $html .= "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; displaymanager($VI_ID)\" >";
    $html .= "<td align=center >".$span."V".$VI_NUMEROTATION."<span></td>
          <td align=center class=small>".$span.$IDENTIFICATION."</span></td>
          <td align=left ><small><B>".$span.$localisation."</small></span></td>
          <td align=center >".$inter_icon."</td>
          <td align=center >".$span.$DATE_ENTREE."</span></td>
          <td align=center >".$span.$e."</span></td>
          <td align=center >".$span.$s."</span></td>
          <td align=center >".my_ucfirst($VI_NOM." ".$VI_PRENOM)."</td>
          <td align=center>".$VI_AGE."</td>
          <td align=center class=small2>".$VI_SEXE."</td>
          <td align=center class=small2>".$PAYS."</td>
          <td align=center class=small2>".$VI_DECEDE."</td>
          <td align=center class=small2>".$VI_DETRESSE_VITALE."</td>
          <td align=center class=small2>".$VI_MALAISE."</td>
          <td align=center class=small2>".$VI_TRAUMATISME."</td>
          <td align=center class=small2>".$VI_SOINS."</td>
          <td align=center class=small2>".$VI_TRANSPORT."</td>
          <td align=center class=small2>".$VI_REPOS."</td>
          <td align=center class=small2>".$VI_INFORMATION."</td>
          <td align=center class=small2>".$VI_IMPLIQUE."</td>
          <td align=center class=small2>".$VI_MEDICALISE."</td>
          <td align=center class=small2>".$REGUL_ICON."</td>
    </tr>"; 
}
$html .= "</table>";
} // if $number > 0
else {
    $html .= "<span class=small>Pas de victimes.</span>";
}

$html .= " <p><table class='noBorder'><tr><td><input type='button' class='btn btn-default' value='retour �v�nement' onclick=\"redirect('evenement_display.php?evenement=".$evenement_victime."&from=interventions');\">";
if ( intval($type_victime) > 0 ) 
    $html .= " <input type='button' class='btn btn-default' value='voir centre accueil' onclick=\"redirect('cav_edit.php?numcav=".intval($type_victime)."');\">";

$html .= "</td></tr></table></body></html>";

print $html;
writefoot();
?>
