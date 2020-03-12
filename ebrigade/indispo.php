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
check_all(11);
$id=$_SESSION['id'];
if ( isset ($_GET["person"])) {
    $person=intval($_GET["person"]);
    if ($person == 'ALL' and !check_rights($id, 12)) {
        $person=$id;
    }
}
else $person=$id;

//section
if (isset ($_GET["section"])) {
   $_SESSION['sectionchoice1'] = intval($_GET["section"]);
   $section=intval($_GET["section"]);
}
else if ( isset($_SESSION['sectionchoice1']) ) {
   $section=$_SESSION['sectionchoice1'];
}
else $section=$_SESSION['SES_SECTION'];

$mysection=get_highest_section_where_granted($id,12);
if ( check_rights($id, 24) ) $mysection='0';
else if ( $mysection == '' ) $mysection=$_SESSION['SES_SECTION'];

writehead();
?>
<STYLE type='text/css'>
.categorie{color:<?php echo $mydarkcolor; ?>; background-color:<?php echo $mylightcolor; ?>; font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>

<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/indispo.js'></script>
</head>
<?php
//=====================================================================
// debut tableau
//=====================================================================


echo "<body onload='changeDisplay();'>";
echo "<div align=center><font size=4><b>Saisie absence</b></font><br>";

echo "<p><table cellspacing=0 border=0>";
echo "<tr class=TabHeader>
            <td colspan=2>Informations</td>
      </tr>";

echo "<form name=demoform action='indispo_save.php'>";

//=====================================================================
// choix section
//=====================================================================

if (check_rights($id, 12) and $syndicate == 0 ){
    
    $level=get_level($mysection);
     if ( $level == 0 ) $mycolor=$myothercolor;
    elseif ( $level == 1 ) $mycolor=$my2darkcolor;
    elseif ( $level == 2 ) $mycolor=$my2lightcolor;
    elseif ( $level == 3 ) $mycolor=$mylightcolor;
    else $mycolor='white';
    $class="style='background: $mycolor;'";
    
     echo "<tr><td bgcolor=$mylightcolor width=150><b>Section</b> $asterisk</td>";
    echo "<td bgcolor=$mylightcolor width=200 align=left>
        <select name='s1' id='s1' title='filtrer le personnel' onChange=\"redirect_liste(document.getElementById('s1').value);\" class='smallcontrol2'>";
        
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;    
        
    if ( check_rights($id, 24))
         display_children2(-1, 0, $section, $nbmaxlevels, $sectionorder);
    else { 
        echo "<option value='$mysection' $class >".get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
        display_children2($mysection, $level +1, $section, $nbmaxlevels);
    }
    echo "</select></td></tr>";

}

//=====================================================================
// choix personne
//=====================================================================


echo "<tr>
            <td bgcolor=$mylightcolor><b>Personne</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>";

//cas personnel habilités sur F 12
if ( check_rights($id, 12) ) {
   $query="select P_ID, P_PRENOM, P_NOM , S_CODE
              from pompier, section
           where P_SECTION = S_ID
           and P_OLD_MEMBER = 0
           and P_STATUT <> 'EXT'
           and P_STATUT <> 'ADH'";
    if ( $syndicate == 1 ) $query .= " and P_SECTION in (".get_family("$mysection").")";           
    else $query .= " and P_SECTION = ".$section;              
    $query .= " order by P_NOM";
    $result=mysqli_query($dbc,$query);
    
    if ( mysqli_num_rows($result) > 0 ) {
        echo "<select id='person' name='person' onChange=\"redirect_liste2(".$section.",document.getElementById('person').value);\" class='smallcontrol2'>";
        while (custom_fetch_array($result)) {
            echo "<option value='".$P_ID."'";
            if ($P_ID == $person ) echo " selected ";
            echo ">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)." (".$S_CODE.")</option>\n";
        }
        echo "</select>";
    }
    else
        echo "<i class='fa fa-warn fa-lg' style='color:orange;'></i> <small>Pas de personnel dans cette section</small>";
}
else {
    echo "<input type=hidden id='person' name='person' value=".$person.">";
    echo strtoupper($_SESSION['SES_NOM'])." ".ucfirst($_SESSION['SES_PRENOM'])."</font>";
}

echo "</td>
   </tr>";

//=====================================================================
// type indispo
//=====================================================================

echo "<tr height=20>
            <td bgcolor=$mylightcolor><b>Raison</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>";

echo "<select id='type' name='type' onchange='changedType()' class='smallcontrol2'>";
echo "<option value=''>Type d'indisponibilité </option>\n";
$query="select TI_CODE, TI_LIBELLE, TI_FLAG
        from type_indisponibilite
        where TI_CODE <> ''";

$statut = get_statut($person);
if ( in_array($statut, array('BEN','SPV','JSP','ADH')) )
    $query .= " and TI_FLAG = 0 ";

if ( $gardes == 0 ) {
    $query .= " and TI_CODE <> 'RT' ";
}
$query .= " order by TI_FLAG, TI_CODE ";
$result=mysqli_query($dbc,$query);
echo "<optgroup class='categorie' label=\"Pas de validation\" />\n";
$prev=0;
while (custom_fetch_array($result)) {
    if ( $TI_FLAG == 1 and $prev == 0) {
        echo "<optgroup class='categorie' label=\"Validation nécessaire\" />\n";
        $prev=$TI_FLAG;
    }
    echo "<option value='".$TI_CODE."' class='type'>".$TI_CODE." - ".$TI_LIBELLE."</option>\n";
}
echo "</select></td>";
echo "</tr>";

//=====================================================================
// début et fin
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor><b>Jour(s) complet(s)</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='full_day' id='full_day' value='1' checked onclick='changeDisplay();'
            title=\"cochez cette case si l'absence concerne une ou plusieurs journées complètes\"></td>";        
echo "</tr>";

$style="style='display:none'";

echo "<tr>
            <td bgcolor=$mylightcolor><b>Une demi journée </b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='morning' id='morning' value='1' onclick='changeDisplay2();'
            title=\"cochez cette case si l'absence concerne une demi-journée seulement\"> <label for='morning'>matin</label>";
echo "</td></tr>";

echo "<tr>
            <td bgcolor=$mylightcolor><b>Une demi journée</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='afternoon' id='afternoon' value='1' onclick='changeDisplay2b();'
            title=\"cochez cette case si l'absence concerne une demi-journée seulement\"> <label for='afternoon'>après-midi</label> ";    
echo "</td></tr>";

echo "<tr>
            <td bgcolor=$mylightcolor><b>Date début</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' size='10' name='dc1' id='dc1' value='' autocomplete='off' class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(document.demoform.dc1)
            style='width:100px;'>";

echo "<select id='debut' name='debut' title=\"heure de début de l'absence\" onchange=\"EvtCalcDuree(document.demoform.duree);\" $style>";
for ( $i=0; $i <= 24; $i++ ) {
    $check = $i.":00";
    if (  $i == 8 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
}
echo "</select>";
echo "</tr>";

echo "<tr id='rowdatefin'>
            <td bgcolor=$mylightcolor><b>Date Fin</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' size='10' name='dc2' id='dc2' value='' autocomplete='off' class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(document.demoform.dc2)
            style='width:100px;'>";

echo "<select id='fin' name='fin' title=\"heure de fin de l'absence\" onchange=\"EvtCalcDuree(document.demoform.duree);\" $style>";
for ( $i=0; $i <= 24; $i++ ) {
    $check = $i.":00";
    if (  $i == 19 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
}
echo "</select>";
echo "<input type='hidden' name='duree' id='duree' value='999999'>";
echo "</tr>";


//=====================================================================
// commentaire facultatif
//=====================================================================

echo "<tr height=30>
            <td bgcolor=$mylightcolor><b>Commentaire </b></td>
            <td bgcolor=$mylightcolor align=left>";
   echo "<input type='text' name='comment' id='comment' size='30' value=''>";
   echo " </tr>";


echo "</table>";

//=====================================================================
// boutons enregistrement
//=====================================================================

echo "<p><a href=indispo_choice.php><input type='button' class='btn btn-default' value='retour'></a>
<input id='save' type='submit' class='btn btn-default'  value='enregistrer' disabled>";
echo "</form></div>";
writefoot();
?>

