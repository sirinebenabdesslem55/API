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
$MAX=8; // nb maxi de roles sur un vehicule
check_all(18);

if (isset ($_GET["operation"])) $operation=$_GET["operation"];
else $operation='update';
if (isset ($_GET["TV_CODE"])) $TV_CODE=secure_input($dbc,$_GET["TV_CODE"]);
else $TV_CODE='';
if (isset ($_GET["from"])) $from=$_GET["from"];
else $from=0;

writehead();

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/type_vehicule.js'></script>
<script type='text/javascript' src='js/ddslick.js'></script>
<?php
// choix d'icônes de vehicules

$query="select TV_ICON from type_vehicule where TV_CODE='".$TV_CODE."'";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$current=$row[0];

echo "<script type='text/javascript'>
    var ddData = [";
$f = 0;
$file_arr = array();
    
$dir=opendir('images/vehicules');

while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." &&  file_extension($file) == 'png' ){
        $file_arr[$f] = "images/vehicules/".$file;
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
";


//=====================================================================
// affiche la fiche type de véhicule
//=====================================================================

if ( $operation == 'insert' ) {
    $TV_NB=2;
    echo "<body onload=\"changeNbEquipage('$TV_NB', '$MAX');\">";
    echo "<div align=center class='table-responsive' ><table class='noBorder'>
      <tr><td><font size=4><b>Ajout type de véhicule</b></font></td></tr>
      </table>";

    echo "<form name='vehicule' action='save_type_vehicule.php' method='POST'>";
    echo "<input type='hidden' name='operation' value='insert'>";
    echo "<input type='hidden' name='OLD_TV_CODE' value='NULL'>";
    $TV_LIBELLE='';
    $TV_USAGE ='';
    $TV_ICON ='';
    $nombre=0;
}
else {
    $query="select TV_LIBELLE, TV_NB, TV_USAGE, TV_ICON
        from type_vehicule where TV_CODE='".$TV_CODE."'";
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $TV_NB=intval($TV_NB);

    $query="select count(1) from vehicule where TV_CODE='".$TV_CODE."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $nombre=$row[0];

    echo "<body onload=\"changeNbEquipage('$TV_NB', '$MAX');\">";
    echo "<div align=center class='table-responsive'><table class='noBorder'>
      <tr><td><font size=4><b>".$TV_CODE.' - '.$TV_LIBELLE."</b></font></td></tr>
      <tr><td>$nombre véhicules</td></tr>
      </table>";

    echo "<form name='vehicule' action='save_type_vehicule.php' method='POST'>";
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='OLD_TV_CODE' value='$TV_CODE'>";
}

//=====================================================================
// ligne 1
//=====================================================================
echo "<p><table cellspacing=0 border=0>";
echo "<tr height=10>
            <td class=TabHeader colspan=4>informations type de véhicule</td>
      </tr>";


//=====================================================================
// ligne catégorie
//=====================================================================

$categories = array('SECOURS','FEU','LOGISTIQUE','DIVERS');
$count = count($categories);

echo "<tr bgcolor=$mylightcolor>
            <td width=150><b>Catégorie</b> $asterisk</td>
            <td width=300 align=left colspan=4>
          <select name='TV_USAGE'>";
               for ($i = 0; $i < $count; $i++) {
                  if ( $categories[$i] == $TV_USAGE ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$categories[$i]."\" $selected>".$categories[$i]."</option>";
              }
 echo "</select>";
 echo "</td>
      </tr>";

//=====================================================================
// ligne code
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td ><b>Code</b> $asterisk</td>
            <td colspan=3 align=left><input type='text' name='TV_CODE' size='12' value=\"$TV_CODE\" onchange=\"isValid6(TV_CODE, '$TV_CODE');\">";        
echo " </td>
      </tr>";
      
//=====================================================================
// ligne description
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td ><b>Description</b></td>
            <td align=left colspan=3><input type='text' name='TV_LIBELLE' size='50' value=\"$TV_LIBELLE\">";        
echo " </td>
      </tr>";
      
      
//=====================================================================
// icone
//=====================================================================      

echo "<tr bgcolor=$mylightcolor><td><b>Icône</td>
    <td colspan=4><div id='iconSelector'></div><input type=hidden name='icon' id='icon' value=\"".$TV_ICON."\">";

?>
<script type="text/javascript">

$('#iconSelector').ddslick({
    data:ddData,
    width:300,
    height:400,
    selectText: "Choisir une icône pour ce type de véhicule",
    imagePosition:"left",
    onSelected: function(data){
        document.getElementById("icon").value = data.selectedData.imageSrc;
    }   
});
</script>
<?php    

//=====================================================================
// ligne nombre équipage
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
          <td ><b>Equipage</b></td>";
echo "<td colspan=3><select id='TV_NB' name='TV_NB' onchange=\"changeNbEquipage(this.value, '$MAX');\">";
for ( $i=0; $i <= $MAX; $i++ ) {
    if ( $i == $TV_NB ) $selected="selected";
    else $selected="";
    echo "<option value='".$i."' $selected>".$i."</option>\n";
}
if ( $TV_NB > $MAX ) {
    echo "<option value='".$TV_NB."' selected>".$TV_NB."</option>\n";
}
echo "</select>";
echo " <span class = small> nombre de personnes dans le véhicule</span></td>
      </tr>";
      

//=====================================================================
// rôles
//=====================================================================      

echo "<tr class=TabHeader id='row_0' >
    <td colspan=2>Rôles</td>";
if ( $competences == 1 ) echo "<td colspan=2>Compétence requise</td>";
else echo "<td colspan=2></td>";
echo "</tr>";

$query="select ROLE_ID, ROLE_NAME, PS_ID from type_vehicule_role where TV_CODE='".$TV_CODE."' order by ROLE_ID";
$result=mysqli_query($dbc,$query);
$roles=array();
$qualif = array();
while ( custom_fetch_array($result)) {
    $roles[$ROLE_ID] = $ROLE_NAME;
    $qualif[$ROLE_ID] = $PS_ID;
}

for ( $i = 1; $i <= $MAX; $i++ ) {
    if ( isset ($roles[$i])) $ROLE_NAME = $roles[$i];
    else $ROLE_NAME ="";
    
    if ( isset($qualif[$i])) $PS_ID = $qualif[$i];
    else $PS_ID ="0" ;
    
    echo "<tr bgcolor=$mylightcolor id='row_$i' >
            <td align=right><b>$i </b></td>
            <td align=left><input type='text' name='ROLE_$i' id='ROLE_$i' size='20' value=\"".$ROLE_NAME."\" title=\"Saisissez ici le nom du rôle, exemples: Conducteur, Chef d'agrès ... \" >";
    // Définition des competences requises
    if ( $competences == 1 ) {
    $query2="select p.PS_ID, p.EQ_ID, p.TYPE, p.DESCRIPTION, e.EQ_NOM
            from poste p, equipe e
            where p.EQ_ID=e.EQ_ID 
            order by e.EQ_ORDER, p.TYPE";
    echo "<td></td>
            <td align=left>";
    echo "<select id ='PS_$i' name='PS_$i' style='max-width:220px;font-size: 12px;' title='Une compétence peut être requise pour pouvoir exercer la fonction, définir laquelle'>";
    echo "<option value='0'>Aucune compétence requise</option>";
    $result2=mysqli_query($dbc,$query2);
    $prevEQ_ID=-1;
    while ($row2=mysqli_fetch_array($result2)) {
          $NEWPS_ID=$row2["PS_ID"];
          $NEWEQ_ID=$row2["EQ_ID"];
          $NEWTYPE=$row2["TYPE"];
          $NEWDESCRIPTION=$row2["DESCRIPTION"];
          $NEWEQ_NOM=$row2["EQ_NOM"];
          if ($prevEQ_ID <> $NEWEQ_ID ) echo "<OPTGROUP LABEL=\"".$NEWEQ_NOM."\" class='section'>";
          $prevEQ_ID=$NEWEQ_ID;
          if ( $PS_ID ==  $NEWPS_ID ) $selected='selected';
          else $selected='';
          echo "<option value='".$NEWPS_ID."' $selected style='max-width:220px;font-size: 12px;'>
                ".$NEWTYPE." - ".$NEWDESCRIPTION."</option>\n";
    }
    echo "</select></td>";
    }        
    echo " </td>
      </tr>";
}

echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'> ";
echo "</form><form name='vehicule2' action='save_type_vehicule.php' method=POST>";
echo "<input type='hidden' name='TV_CODE' value=\"$TV_CODE\">";
echo "<input type='hidden' name='OLD_TV_CODE' value=\"$TV_CODE\">";
echo "<input type='hidden' name='TV_USAGE' value='$TV_USAGE'>";
echo "<input type='hidden' name='TV_NB' value='$TV_NB'>";
echo "<input type='hidden' name='TV_ICON' value='$TV_ICON'>";
echo "<input type='hidden' name='TV_LIBELLE' value='$TV_LIBELLE'>";
echo "<input type='hidden' name='operation' value='delete'>";
if ( $nombre > 0 ) 
echo "<input type='submit' class='btn btn-default' value='supprimer' disabled title='Impossible de supprimer car il y a $nombre vévicules de ce type dans la base'> ";
else
echo "<input type='submit' class='btn btn-default' value='supprimer'> ";

echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"redirect('type_vehicule.php');\">";
echo "</form><p style='padding-top:400px'>";
echo "</div>";

writefoot();
?>
