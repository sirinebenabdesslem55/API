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
check_all(29);
$id=$_SESSION['id'];
get_session_parameters();

if ( check_rights($_SESSION['id'], 24)) $section='0';
else $section=$_SESSION['SES_SECTION'];

if ( isset($_GET["from"])) $from=$_GET["from"];
else $from='default';

if ( isset($_GET["action"])) $action=$_GET["action"];
else $action='update';

if ( isset($_GET["EF_ID"])) $EF_ID=intval($_GET["EF_ID"]);
else $EF_ID=0;

writehead();
echo "<STYLE type='text/css'>
.categorie{color:$mydarkcolor; background-color:$mylightcolor; font-size:10pt;}
.type{color:$mydarkcolor; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/element_facturable.js'></script>
</head>
<body>";

//=====================================================================
// affiche la fiche
//=====================================================================

if ( $action =='update' or $action == 'duplicate') {
    $query="select s.S_ID, e.EF_ID, e.TEF_CODE, t.TEF_NAME, e.S_ID, e.EF_NAME, e.EF_PRICE, s.S_CODE
        from element_facturable e, type_element_facturable t, section s
        where s.S_ID=e.S_ID
        and e.TEF_CODE = t.TEF_CODE
        and e.EF_ID = ".$EF_ID;
    
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $EF_ID = $row["EF_ID"];
    $S_ID = $row["S_ID"];
    $TEF_CODE = $row["TEF_CODE"];
    $TEF_NAME = $row["TEF_NAME"];
    $EF_NAME = $row["EF_NAME"];
    $title = "Modification élément facturable";
    $EF_PRICE = $row["EF_PRICE"];
    $S_CODE = $row["S_CODE"];
    if ( $action == 'duplicate' ) {
        $EF_ID = 0;
        $action='insert';
        $title = "Nouvel élément facturable";
    }
}
else {
    $S_ID = $_SESSION['SES_SECTION'];
    $EF_ID = 0;
    $TEF_CODE = "";
    $TEF_NAME = "";
    $EF_NAME = "";
    $title = "Nouvel élément facturable";
    $EF_PRICE = 0;
}

// permettre les modifications si je suis habilité sur la fonctionnalité 29 au bon niveau
// ou je suis habilité sur la fonctionnalité 24 )
if (check_rights($_SESSION['id'], 29,"$S_ID")) $comptable=true;
else $comptable=false;

if ( $comptable ) $disabled=""; 
else $disabled="disabled";

//=====================================================================
// afficher fiche
//=====================================================================

echo "<div align=center class='table-responsive'><table class='noBorder'>
      <tr><td width = 60 ><i class='fa fa-euro-signfa-3x' style='color:orange;'></i></td><td>
      <font size=4><b>".$title."</b></font></td></tr></table>";

echo "<form name='ef' action='save_element_facturable.php'>";
echo "<input type='hidden' name='EF_ID' value='$EF_ID'>";
echo "<input type='hidden' name='operation' value='".$action."'>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<p><table cellspacing=0 border=0>";
echo "<tr class='TabHeader'>
            <td class=TabHeader colspan=2>informations élément</td>
      </tr>";

//=====================================================================
// ligne section
//=====================================================================

echo "<tr bgcolor=$mylightcolor >
            <td><b>Section</b> $asterisk</td>
            <td align=left>";
echo "<select id='S_ID' name='S_ID' $disabled>"; 

$mysection=$S_ID;
$mysection=get_highest_section_where_granted($id,29);
if ( $mysection == '' ) $mysection=$S_ID;
if ( ! is_children($section,$mysection)) $mysection=$section;
   
$level=get_level($mysection);
if ( $level == 0 ) $mycolor=$myothercolor;
elseif ( $level == 1 ) $mycolor=$my2darkcolor;
elseif ( $level == 2 ) $mycolor=$my2lightcolor;
elseif ( $level == 3 ) $mycolor=$mylightcolor;
else $mycolor='white';
$class="style='background: $mycolor;'";
   
if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
else $sectionorder=$defaultsectionorder;    
   
if ( check_rights($id, 24))
         display_children2(-1, 0, $S_ID, $nbmaxlevels, $sectionorder);
else {
    echo "<option value='$mysection' $class >".
              get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
       if ( $disabled == '') display_children2($mysection, $level +1, $S_ID, $nbmaxlevels);
}
echo "</select></td></tr>";  

//=====================================================================
// ligne type élément
//=====================================================================

echo "<tr bgcolor=$mylightcolor >
         <td width=150><b>Type</b> $asterisk</td>
         <td align=left><select id='TEF_CODE' name='TEF_CODE' >";

$query2=" select TEF_CODE, TEF_NAME from type_element_facturable order by TEF_NAME asc";
$result2=mysqli_query($dbc,$query2);
while ($row2=@mysqli_fetch_array($result2)) {
    $NEWTEF_CODE=$row2["TEF_CODE"];
    $NEWTEF_NAME=$row2["TEF_NAME"];
    if ($NEWTEF_CODE == $TEF_CODE ) $selected="selected ";
    else $selected ="";
    echo "<option class='conso' value='".$NEWTEF_CODE."' $selected>".$NEWTEF_NAME."</option>\n";
}
echo "</select></td>";

//=====================================================================
// ligne nom
//=====================================================================

echo "<tr bgcolor=$mylightcolor >
            <td><b>Description</b> $asterisk</td>
            <td align=left><input type='text' name='EF_NAME' id='EF_NAME' maxlength='60' size='40' value=\"$EF_NAME\" $disabled>";        
echo "</td>
      </tr>";
      
//=====================================================================
// prix unitaire
//=====================================================================
echo "<tr bgcolor=$mylightcolor >
            <td><b>Prix unitaire ".$default_money_symbol."</b> $asterisk</td>
            <td align=left><input type='text' name='EF_PRICE' id='EF_PRICE' maxlength='5' size='5' value=\"$EF_PRICE\" $disabled onchange=\"checkFloat(form.EF_PRICE,".$EF_PRICE.");\">";        
echo "</td>
      </tr>";
echo "</table>";
  
if ( $disabled == "") {
    echo " <p><input type='submit' class='btn btn-default' value='sauver'> ";
    if ( $action == 'update' ) {
        echo " <input type='button' class='btn btn-default' value='dupliquer' name='dupliquer' 
                onclick=\"javascript:bouton_redirect('upd_element_facturable.php?action=duplicate&EF_ID=".$EF_ID."');\">";
    }
    echo "</form>";
}

if ( check_rights($_SESSION['id'], 29, "$S_ID")) {
    echo "<form name='tf2' action='save_element_facturable.php'>";
    echo "<input type='hidden' name='EF_ID' value='$EF_ID'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
    echo "<input type='hidden' name='operation' value='delete'>";
    echo "<input type='submit' class='btn btn-default' value='supprimer'> ";
    echo "</form><p>";
}


if ( $from == 'export' ) {
    echo " <input type=submit class='btn btn-default' value='fermer cette page' onclick='fermerfenetre();'> ";
}
else if ( $from == 'evenement' ) {
    echo " <input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
}
else {
    echo " <input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:bouton_redirect('element_facturable.php');\">";
}

echo "</div>";
writefoot();

?>