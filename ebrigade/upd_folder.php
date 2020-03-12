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
check_all(47);
$id=$_SESSION['id'];
$S_ID=intval($_GET["section"]);

get_session_parameters();

if (! check_rights($id, 47, "$S_ID"))
    check_all(24);

writehead();
echo "</head><body class='top30'>";

// section
echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b>".get_section_code("$S_ID")." - ".get_section_name("$S_ID")."</b></font></td></tr>
      </table>";
echo "<form action='save_folder.php' method='post'>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='S_ID' value='$S_ID'>";
echo "<input type='hidden' name='dossier_parent' value='$dossier'>";

echo "<p><table cellspacing=0 border=0>";
echo "<tr>
             <td colspan=2 class=TabHeader>Ajout de dossier</td>
      </tr>";

// dossier supérieur
if ($dossier > 0 ) {
    $parent="<b>".get_folder_name($dossier)."</b>";
    $query="select td.TD_CODE, td.TD_LIBELLE from type_document td, document_folder df 
            where df.TD_CODE = td.TD_CODE
            and df.DF_ID=".$dossier;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $parent .= " <br><font size=1>(".$row["TD_LIBELLE"].")</font>";
    echo "<input type='hidden' name='type' value='".$row["TD_CODE"]."'>";
}
else $parent="A la racine";
echo "<tr>
             <td bgcolor=$mylightcolor align=right width=120>Emplacement: </td>
           <td bgcolor=$mylightcolor align=left>".$parent."</td>
      </tr>";  
      
//type
if ($dossier == 0) {
    $query="select TD_CODE, TD_LIBELLE, TD_SYNDICATE, TD_SECURITY  from type_document where TD_SYNDICATE = ".$syndicate;
    $query .=" order by TD_LIBELLE";
    
    echo "<tr><td bgcolor=$mylightcolor align=right>Pour quel type de documents:</td>
        <td bgcolor=$mylightcolor> 
        <select id='type' name='type'>\n";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $TD_CODE=$row["TD_CODE"];
        $TD_LIBELLE=$row["TD_LIBELLE"];
        $TD_SECURITY=intval($row["TD_SECURITY"]);
        if ( check_rights($id, $TD_SECURITY)) {
            $selected='';
            if ( isset($_SESSION['td'])) {
                if ($_SESSION['td'] == $TD_CODE) $selected='selected';
            }
            echo "<option value='".$TD_CODE."' $selected>".$TD_LIBELLE."</option>\n";    
        }
    }
    echo "</select></td></tr>";
}
else {

}

// Dossier
echo "<tr><td bgcolor=$mylightcolor align=right>Nom du dossier:</td>
    <td bgcolor=$mylightcolor>
    <input type='text' name='folder' id='folder' size='30'></td></tr>";

echo "</table>";// end left table
echo "<p><input type='submit' class='btn btn-default' value='Envoyer'> 
        <input type='button' class='btn btn-default' value='Annuler' 
        onclick=\"javascript:self.location.href='documents.php';\">";
echo "</form>";
echo "</div>";
writefoot();
?>
