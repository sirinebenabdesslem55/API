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
check_all(5);
$id=$_SESSION['id'];
$eqid=intval($_GET["eqid"]);
get_session_parameters();
writehead();

?>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/equipe.js'></script>
<script type='text/javascript' src='js/ddslick.js'></script>
<?php
// choix d'icônes pour la garde
$query="select EQ_ICON from type_garde where EQ_ID=".$eqid;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$current=$row[0];

echo "<script type='text/javascript'>
    var ddData = [";
$f = 0;
$file_arr = array();
    
$dir=opendir('images/gardes');

while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." &&  file_extension($file) == 'png' ){
        $file_arr[$f] = "images/gardes/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}
closedir($dir);
array_multisort( $file_arr, $name_arr );

for( $i=0 ; $i < count( $file_arr ); $i++ ) {
    echo "    {
        text: '".$name_arr[$i]."',
        value: '".$name_arr[$i]."',";
        if ( $current == $file_arr[$i] ) echo "selected: true,";
        else echo "selected: false,";
        echo "description: \"\",
        imageSrc: \"".$file_arr[$i]."\"
        },";
}
echo "];";
echo "</script>
</head>
<body>";

//=====================================================================
// affiche la fiche equipe
//=====================================================================
if ( $eqid > 0 ) {
    $query="select EQ_ID, EQ_JOUR, EQ_NUIT , EQ_NOM, S_ID, ASSURE_PAR1, ASSURE_PAR2, ASSURE_PAR_DATE,
        EQ_DUREE1, EQ_DUREE2, EQ_REGIME_TRAVAIL,
        TIME_FORMAT(EQ_DEBUT1, '%k:%i') EQ_DEBUT1,
        TIME_FORMAT(EQ_DEBUT2, '%k:%i') EQ_DEBUT2,
        TIME_FORMAT(EQ_FIN1, '%k:%i') EQ_FIN1,
        TIME_FORMAT(EQ_FIN2, '%k:%i') EQ_FIN2,
        EQ_PERSONNEL1,EQ_PERSONNEL2, EQ_VEHICULES, EQ_SPP, EQ_ICON, EQ_ADDRESS, EQ_LIEU
        from type_garde
        where EQ_ID=".$eqid;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $ASSURE_PAR1=intval($ASSURE_PAR1);
    $ASSURE_PAR2=intval($ASSURE_PAR2);
    $title="Type de Garde - $EQ_NOM";
    $operation='update';
}
else {
    $EQ_ID=0;
    $EQ_NOM="";
    $S_ID=$filter;
    $ASSURE_PAR_DATE="";
    $EQ_JOUR=1;
    $EQ_NUIT=0;
    $EQ_DUREE1=12;
    $EQ_DUREE2=12;
    $EQ_DEBUT1='7:30';
    $EQ_FIN1='19:30';
    $EQ_DEBUT2='19:30';
    $EQ_FIN2='7:30';
    $EQ_PERSONNEL1=4;
    $EQ_PERSONNEL2=4;
    $EQ_VEHICULES=0;
    $EQ_SPP=0;
    $EQ_ADDRESS='';
    $EQ_LIEU='';
    $EQ_ICON='images/gardes/GAR.png';
    $EQ_REGIME_TRAVAIL=0;
    $title="Ajout nouveau type de garde";
    $operation='insert';
}

echo "<div align=center class='table-responsive'><font size=4><b>".$title."</b></font>";
echo "<form name='garde1' action='save_type_garde.php'>";

echo "<p><table cellspacing=0 border=0>";
echo "<input type='hidden' name='EQ_ID' value='$EQ_ID'>";
echo "<input type='hidden' name='operation' value='$operation'>";

echo "<tr>
    <td colspan=2 class=TabHeader>infos</td>
      </tr>";

//=====================================================================
// section
//=====================================================================

if ( $nbsections == 0 ) {

    $query="select count(1) as NB from evenement where S_ID=".$S_ID." and E_EQUIPE=".$eqid." and E_EQUIPE > 0 ";
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    if ( $NB > 0 ) {
        $section_disabled='disabled';
        $cmt="Il y a déjà $NB gardes crées pour ce type.";
        echo "<input type='hidden' id='groupe' name='groupe' value='".$S_ID."'>";
    }
    else {
        $section_disabled='';
        $cmt="Aucune garde créée pour ce type.";
    }
    
    // permettre les modifications si je suis habilité sur la fonctionnalité 5 au bon niveau
    // ou je suis habilité sur la fonctionnalité 24 )
    if (check_rights($id, 5,"$S_ID")) $responsable_gardes=true;
    else $responsable_gardes=false;

    if ($responsable_gardes ) $disabled=""; 
    else $disabled="disabled";

    echo "<tr>
            <td bgcolor=$mylightcolor ><b>Garde pour</b>$asterisk</td>
            <td bgcolor=$mylightcolor align=left>";
    echo "<select id='groupe' name='groupe' $disabled $section_disabled>"; 

    if ( $responsable_gardes ) {
        $mysection=get_highest_section_where_granted($id,5);
        if ( $mysection == '' ) $mysection=$S_ID;
        if ( ! is_children($filter,$mysection)) $mysection=$filter;
    }
    else $mysection=$S_ID;
   
    $level=get_level($mysection);
    if ( $level == 0 ) $mycolor=$myothercolor;
    elseif ( $level == 1 ) $mycolor=$my2darkcolor;
    elseif ( $level == 2 ) $mycolor=$my2lightcolor;
    elseif ( $level == 3 ) $mycolor=$mylightcolor;
    else $mycolor='white';
    $class="style='background: $mycolor;'";
   
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;
   
    if ( $pompiers ) $maxL = $nbmaxlevels -1 ;
    else $maxL = $nbmaxlevels;
    if ( check_rights($id, 24))
        display_children2(-1, 0, $S_ID, $maxL, $sectionorder);
    else {
        echo "<option value='$mysection' $class >".
              get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
        if ( $disabled == '') display_children2($mysection, $level +1, $S_ID, $maxL);
    }
    if ( $eqid > 0 ) $detail="<br><small>".$cmt."</small>";
    else $detail="";
    echo "</select>".$detail."</td> ";
    echo "</tr>";
}  
      
//=====================================================================
// ligne description
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td><b>Description</b> $asterisk</td>
            <td align=left >
            <input type='text' name='EQ_NOM' size='35' value=\"$EQ_NOM\">";
echo "</tr>";      
      
// select icon
echo "<tr bgcolor=$mylightcolor><td><b>Icône</td>
<td><div id='iconSelector'></div><input type=hidden name='icon' id='icon' value=\"".$EQ_ICON."\">";
    
?>
<script type="text/javascript">

$('#iconSelector').ddslick({
    data:ddData,
    width:300,
    height:400,
    selectText: "Choisir une icône pour ce type de garde",
    imagePosition:"left",
    onSelected: function(data){
        document.getElementById("icon").value = data.selectedData.imageSrc;
    }   
});
</script>
<?php
echo "</td></tr>";

$H = "<select name='EQ_REGIME_TRAVAIL' >";
if ( $EQ_REGIME_TRAVAIL == '2' ) $selected="selected"; else $selected="";
$H .= "<option value='2' $selected>2 sections </option>";
if ( $EQ_REGIME_TRAVAIL == '3' ) $selected="selected"; else $selected="";
$H .= "<option value='3' $selected>3 sections </option>";
if ( $EQ_REGIME_TRAVAIL == '4' ) $selected="selected"; else $selected="";
$H .= "<option value='4' $selected>4 sections</option>";
if ( $EQ_REGIME_TRAVAIL == '5' ) $selected="selected"; else $selected="";
$H .= "<option value='5' $selected>5 sections</option>";
if ( $EQ_REGIME_TRAVAIL == '6' ) $selected="selected"; else $selected="";
$H .= "<option value='6' $selected>6 sections</option>";
if ( $EQ_REGIME_TRAVAIL == 0 ) $selected="selected"; else $selected="";
$H .= "<option value='0' $selected>Autre cas</option>";
$H .= "</select>";

if ( $pompiers ) {
    echo "<tr bgcolor=$mylightcolor>
          <td ><b>Régime de travail</b> $asterisk</td>
          <td align=left>".$H."</td>";
    echo "</tr>";
}

$map="";
if ( $EQ_ADDRESS <> "" and $geolocalize_enabled==1) {
    $querym="select count(1) as NB from geolocalisation where TYPE='G' and CODE=".$EQ_ID;
    $resultm=mysqli_query($dbc,$querym);
    custom_fetch_array($resultm);
    if ( $NB == 0 ) gelocalize($EQ_ID, 'G');
    $resultm=mysqli_query($dbc,$querym);
        custom_fetch_array($resultm);
    if ( $NB == 1 ) $map=" <a href=map.php?type=G&code=".$EQ_ID." target=_blank><i class='fa fa-map noprint' style='color:green' title='Voir la carte Google Maps' class='noprint'></i></a>";
}

echo "<tr bgcolor=$mylightcolor>
      <td ><b>Lieu</b></td>
      <td align=left>
        <input type='text' name='EQ_LIEU' id='EQ_LIEU' size='35' value=\"".$EQ_LIEU."\" title=\"saisir le lieu de la garde (exemple caserne)\">";
echo "</tr>";

echo "<tr bgcolor=$mylightcolor>
      <td ><b>Adresse garde</b></td>
      <td align=left>
        <input type='text' name='EQ_ADDRESS' id='EQ_ADDRESS' size='35' value=\"".$EQ_ADDRESS."\" title=\"saisir l'adresse exacte du lieu où se situe la garde (exemple: adresse de la caserne)\">";
if ( $geolocalize_enabled == 1)
    echo "$map<br><small> Utilisée pour la géolocalisation</small2>";
echo "</tr>";

// garde
echo "<input type='hidden' name='date1' id='date1' value='01-01-".date('Y')."'>"; //used by javascript EvtCalcDuree only
echo "<input type='hidden' name='date2' id='date2' value='02-01-".date('Y')."'>"; //used by javascript EvtCalcDuree only

//--------------------
// JOUR
//--------------------
if ( $EQ_JOUR == 1 ) {
    $checked="checked";
    $style='';
}
else {
    $checked="";
    $style="style='display:none'";
}
echo "<tr bgcolor=$mylightcolor>
      <td ><label for='EQ_JOUR'>Actif le jour</label>  <i class='fa fa-sun fa-lg' style='color:yellow;' title='jour'></i></td>
      <td align=left>
        <input type='checkbox' name='EQ_JOUR' id='EQ_JOUR' value='1' $checked onchange=\"garde_JN();\" title='cocher si la garde est active le jour'>";
echo "</tr>";

echo "<tr id='row_debut1' $style bgcolor=$mylightcolor><td align=right><i>Heure de début</i></td>
    <td><select id='debut1' name='debut1' title=\"Heure de début de la garde\"
    onchange=\"EvtCalcDuree(date1,date1,debut1,fin1,duree1);\">";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i.":00" == $EQ_DEBUT1 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
    if ( $i.":30" == $EQ_DEBUT1 ) $selected="selected";
    else $selected="";
    if ( $i < 24 )
    echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
}
echo "</select></td></tr>";

echo  "<tr id='row_fin1' $style bgcolor=$mylightcolor><td align=right><i>Heure de fin</i></td>
    <td><select id='fin1' name='fin1' title=\"Heure de fin de journée\"
    onchange=\"EvtCalcDuree(date1,date1,debut1,fin1,duree1);\">";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i.":00" == $EQ_FIN1 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
    if ( $i.":30" == $EQ_FIN1 ) $selected="selected";
    else $selected="";
    if ( $i < 24 )
    echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
}
echo "</select></td></tr>";

echo "<tr bgcolor=$mylightcolor id='row_duree1' $style>
      <td align=right><i>Durée</i></td>
      <td align=left>";
echo "<select id='duree1' name='duree1' title='duree en heures de présence pour le jour'>";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i == $EQ_DUREE1 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i." $selected>".$i."</option>\n";
    if ( $i < 24 ) {
        $j=$i+0.5;
        if ( $j == $EQ_DUREE2 ) $selected="selected";
        else $selected="";
        echo "<option value=".$j." $selected>".$j."</option>\n";
    }
}
echo "</select> <i>heures</i></td> ";
echo "</tr>";

// ligne section assurant ce type de garde aujourd'hui
if ( $pompiers ) {
    if ( $EQ_ID == 0 ) $section_today=0;
    else $section_today=get_section_pro_jour($EQ_ID,date("Y"), date("n"), date("d"),'J');
    echo "<tr bgcolor=$mylightcolor id='row_eq1' $style>
        <td align=right><i>Assurée aujourd'hui par </i></td>
        <td align=left>";

    echo "<select id='section_jour' name='section_jour'>";
    $query2="select S_ID, S_CODE, S_DESCRIPTION
    from section
    where ( S_ID = ".$S_ID." or S_PARENT = ".$S_ID.")
    order by S_CODE";

    $result2=mysqli_query($dbc,$query2);
    while ($row=@mysqli_fetch_array($result2)) {
        $NEWS_ID=$row["S_ID"];
        $S_DESCRIPTION=$row["S_DESCRIPTION"];
        $S_CODE=$row["S_CODE"];
        if ( $S_DESCRIPTION <> '' ) $S_CODE .= " - "; 
        echo "<option value='".$NEWS_ID."'";
        if ($NEWS_ID == $section_today ) echo " selected ";
        echo ">".$S_CODE.$S_DESCRIPTION."</option>\n";
    }
    echo "</select></td></tr>";
}
else {
    echo "<tr id='row_eq1'><td colspan=2 style='display:none'></td></tr>";
}

echo "<tr bgcolor=$mylightcolor id='row_personnel1' $style>
      <td align=right><i>Nombre de personnes </i></td>
      <td align=left>
        <input type='text' name='EQ_PERSONNEL1'  size='3' maxlength='3' value='".$EQ_PERSONNEL1."' onchange=\"checkNumber(form.EQ_PERSONNEL1,'".$EQ_PERSONNEL1."');\">";        
echo "</tr>";

if ( $eqid > 0 ) {
    echo " <tr bgcolor=$mylightcolor id='row_comp1' $style>
            <td align=right><i>Compétences </i></td>
            <td>";

    print show_competences($eqid, "1"); 
    echo " <a href='evenement_competences.php?garde=".$eqid."&partie=1'><i class='fa fa-edit fa-lg' title='Modifier les compétences demandées' 
                onclick=\"modifier_competences('".$eqid."','1')\"></i></a>
            </td></tr>";
}
else {
    echo "<tr id='row_comp1'><td colspan=2 style='display:none'></td></tr>";
}
//--------------------
// NUIT
//--------------------
if ( $EQ_NUIT == 1 ) {
    $checked="checked";
    $style='';
}
else {
    $checked="";
    $style="style='display:none'";
}
echo "<tr bgcolor=$mylightcolor>
      <td ><label for='EQ_NUIT'>Actif la nuit</label> <i class='fa fa-moon fa-lg' style='color:black;' title='nuit'></i></td>
      <td align=left>
        <input type='checkbox' name='EQ_NUIT' id='EQ_NUIT' value='1' $checked onchange=\"garde_JN();\" title='cocher si la garde est active la nuit'>";
echo "</tr>";

echo "<tr id='row_debut2' $style bgcolor=$mylightcolor><td align=right><i>Heure de début</i></td>
    <td><select id='debut2' name='debut2' title=\"Heure de début de la garde\"
    onchange=\"EvtCalcDuree(date1,date2,debut2,fin2,duree2);\">";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i.":00" == $EQ_DEBUT2 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
    if ( $i.":30" == $EQ_DEBUT2 ) $selected="selected";
    else $selected="";
    if ( $i < 24 )
    echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
}
echo "</select> </td></tr>";

echo  "<tr id='row_fin2' $style bgcolor=$mylightcolor><td align=right><i>Heure de fin </i></td>
    <td><select id='fin2' name='fin2' title=\"Heure de fin de journée\"
    onchange=\"EvtCalcDuree(date1,date2,debut2,fin2,duree2);\">";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i.":00" == $EQ_FIN2 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
    if ( $i.":30" == $EQ_FIN2 ) $selected="selected";
    else $selected="";
    if ( $i < 24 )
    echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
}
echo "</select></td></tr>";

echo "<tr bgcolor=$mylightcolor id='row_duree2' $style>
      <td align=right><i>Durée</i></td>
      <td align=left>";
echo "<select id='dure2' name='duree2' title='duree en heures de présence pour la nuit'>";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i == $EQ_DUREE2 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i." $selected>".$i."</option>\n";
    if ( $i < 24 ) {
        $j=$i+0.5;
        if ( $j == $EQ_DUREE2 ) $selected="selected";
        else $selected="";
        echo "<option value=".$j." $selected>".$j."</option>\n";
    }
}
echo "</select><i>heures</i></td> ";
echo "</tr>";

// ligne section assurant ce type de garde cette nuit
if ( $pompiers ) {
    if ( $EQ_ID == 0 ) $section_today=0;
    else $section_today=get_section_pro_jour($EQ_ID, date("Y"), date("n"), date("d"), 'N');

    echo "<tr bgcolor=$mylightcolor id='row_eq2' $style>
        <td align=right><i>Assurée aujourd'hui par </i></td>
        <td align=left>";

    echo "<select id='section_nuit' name='section_nuit'>";
    $query2="select S_ID, S_CODE, S_DESCRIPTION
    from section
    where ( S_ID = ".$S_ID." or S_PARENT = ".$S_ID.")
    order by S_CODE";

    $result2=mysqli_query($dbc,$query2);
    while ($row=@mysqli_fetch_array($result2)) {
        $NEWS_ID=$row["S_ID"];
        $S_DESCRIPTION=$row["S_DESCRIPTION"];
        $S_CODE=$row["S_CODE"];
        if ( $S_DESCRIPTION <> '' ) $S_CODE .= " - "; 
        echo "<option value='".$NEWS_ID."'";
        if ($NEWS_ID == $section_today ) echo " selected ";
        echo ">".$S_CODE.$S_DESCRIPTION."</option>\n";
    }
    echo "</select></td></tr>";
}
else {
    echo "<tr id='row_eq2'><td colspan=2 style='display:none'></td></tr>";
}

echo "<tr bgcolor=$mylightcolor $style id='row_personnel2'>
      <td align=right><i>Nombre de personnes </i></td>
      <td align=left>
        <input type='text' name='EQ_PERSONNEL2'  size='3' maxlength='3' value='".$EQ_PERSONNEL2."' onchange=\"checkNumber(form.EQ_PERSONNEL2,'".$EQ_PERSONNEL2."');\">";
echo "</tr>";

if ( $eqid > 0 ) {
    echo " <tr bgcolor=$mylightcolor id='row_comp2' $style>
            <td align=right><i>Compétences </i></td>
            <td>";
    print show_competences($eqid, "2"); 
    echo " <a href='evenement_competences.php?garde=".$eqid."&partie=2'><i class='fa fa-edit fa-lg' title='Modifier les compétences demandées'
                onclick=\"modifier_competences('".$eqid."','2')\"></i></a>
            </td></tr>";
}
else {
    echo "<tr id='row_comp2'><td colspan=2 style='display:none'></td></tr>";
}

// options de la garde
if ( $vehicules ) {
    if ( $EQ_VEHICULES == 1 )$checked="checked";
    else $checked="";

    echo "<tr bgcolor=$mylightcolor>
        <td ><b>Véhicules</b></td>
        <td >
        <input type='checkbox' name='EQ_VEHICULES'  value='1' $checked  title = \"Les véhicules sont par défaut automatiquement affichés\">";
    echo "</tr>";
}

if ( $pompiers ) {
    $NBSPP = get_number_spp();
    if ( $NBSPP > 0 ) {
        if ( $EQ_SPP == 1) $checked="checked";
        else $checked="";

        echo "<tr bgcolor=$mylightcolor> 
          <td><b>SPP</b></td>
          <td align=left>
            <input type='checkbox' name='EQ_SPP'  value='1' $checked title = \"Les sapeurs pompiers professionnels sont par défaut automatiquement engagés sur ce type de garde\">";
        echo "</tr>";
    }
}
//=====================================================================
// bas de tableau
//=====================================================================
echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'> <input type='button' class='btn btn-default' value='Retour' name='annuler' onclick='javascript:self.location.href=\"type_garde.php\";'>";
echo "</form>";
if ( $EQ_ID > 0 ) {
    echo "<form name='garde2' action='save_type_garde.php'>";
    echo "<input type='hidden' name='EQ_ID' value='$EQ_ID'>";
    echo "<input type='hidden' name='operation' value='delete'>";
    echo "<input type='submit' class='btn btn-default' value='supprimer'> ";
    echo "</form>";
}

echo "<p style='padding-top:150px'></div>";
writefoot();
?>
