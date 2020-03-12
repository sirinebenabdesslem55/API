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
$eqid=intval($_GET["eqid"]);
if (isset($_GET["filter"]))  $filter=intval($_GET["filter"]);
writehead();

?>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/equipe.js'></script>
<?php

echo "</head>
<body>";

//=====================================================================
// affiche la fiche equipe
//=====================================================================
if ( $eqid > 0 ) {
    $query="select EQ_ID, EQ_NOM, EQ_ORDER from equipe where EQ_ID=".$eqid;    
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $title="Type de Compétence: $EQ_NOM";
    $operation='update';
}
else {
    $EQ_ID=0;
    $EQ_NOM="";
    $EQ_ORDER="";
    $title="Ajout nouveau type de compétence";
    $operation='insert';
}
check_all(18);

echo "<div align=center class='table-responsive'><font size=4><b>".$title."</b></font>";
echo "<form name='equipe' action='save_equipe.php'>";

echo "<p><table cellspacing=0 border=0>";
echo "<input type='hidden' name='EQ_ID' value='$EQ_ID'>";
echo "<input type='hidden' name='operation' value='$operation'>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<tr>
    <td colspan=2 class=TabHeader>infos</td>
      </tr>";

//=====================================================================
// ligne description
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td width=200><b>Description</b> $asterisk</td>
            <td align=left>
            <input type='text' name='EQ_NOM' size='35' value=\"$EQ_NOM\">";        
echo "</tr>";      
      
//=====================================================================
// type competence
//=====================================================================


// competences affichables sur evenements
$query2="select distinct ce.CEV_CODE, ce.CEV_DESCRIPTION, cea.FLAG1 
        from categorie_evenement ce left join categorie_evenement_affichage cea on ( ce.CEV_CODE=cea.CEV_CODE and cea.EQ_ID=".$EQ_ID.")";
$result2=mysqli_query($dbc,$query2);
echo "<tr bgcolor=$mylightcolor><td colspan=2 align=left><b>Affichage sur les événements:</b></td></tr>";
while (custom_fetch_array($result2)) {
    if ( $FLAG1 == 1 ) $checked="checked";
    else $checked="";
    echo "<tr bgcolor=$mylightcolor>
      <td align=right ></td>
      <td align=left>
        <input type='checkbox' name='".$CEV_CODE."'  value='1' $checked
        title=\"cocher si ces compéténces de ce type doivent être affichées sur les événements de cette catégorie\" >
        <span class=small>".$CEV_DESCRIPTION."</span>
      </td>";        
    echo "</tr>";
}

echo "<tr bgcolor=$mylightcolor>
      <td ><b>Ordre affichage</b> $asterisk</td>
      <td align=left>
      <select name='EQ_ORDER' >";
         for ($i=1 ; $i<=30 ; $i++) {
            if ($i == $EQ_ORDER) $selected="selected";
            else $selected="";
            echo "<option value='$i' $selected>$i</option>";
        }
        echo "</select>";
echo "</tr>";

// afficher les compétences de ce type
$queryp="select PS_ID, TYPE, DESCRIPTION
    from  poste p
    where EQ_ID=$EQ_ID";
$resultp=mysqli_query($dbc,$queryp);

if ( @mysqli_num_rows($resultp) > 0 )
    echo "<tr>
      <td colspan=2 class=TabHeader>
        <a href=poste.php?typequalif=$EQ_ID&order=PS_ID class=TabHeader>Compétences de ce type</a></td>
    </tr>";
    
while ($rowp=@mysqli_fetch_array($resultp)) {
    $PS_ID=$rowp["PS_ID"];
    $TYPE=$rowp["TYPE"];
    $DESCRIPTION=strip_tags($rowp["DESCRIPTION"]);
    echo "<tr bgcolor=$mylightcolor>
      <td ><b> $PS_ID</b></td>
      <td align=left><a href=upd_poste.php?pid=$PS_ID>$DESCRIPTION</a>";        
    echo "</tr>";
}

//=====================================================================
// bas de tableau
//=====================================================================
echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'> <input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
echo "</form>";
if ( $EQ_ID > 0 ) {
    echo "<form name='equipe2' action='save_equipe.php'>";
    echo "<input type='hidden' name='EQ_ID' value='$EQ_ID'>";
    echo "<input type='hidden' name='operation' value='delete'>";
    echo "<input type='submit' class='btn btn-default' value='supprimer'> ";
    echo "</form>";
}

echo "<p style='padding-top:150px'></div>";
writefoot();
?>
