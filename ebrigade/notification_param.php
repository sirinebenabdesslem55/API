<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade
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

if (isset($_GET["person"])) $person=intval($_GET["person"]);
else if (isset($_POST["person"])) $person=intval($_POST["person"]);
else $person=$id;

if ( $id <> $person ) {
    check_all(2);
    if ( ! check_rights($id,2, get_section_of("$person"))) check_all(24);
}

function insert_block($field, $pid) {
    global $dbc, $A;
    if (isset ($_POST[$field])) {
        $fid=str_replace("U","",$field);
        $persofield="F".$fid;
        if (! isset($_POST[$persofield]) ) {
            $query="insert into notification_block(P_ID, F_ID) values (".$pid.",".$fid.")";
            mysqli_query($dbc,$query);
        }
        else $A .= $fid."+";
    }
}

$query = "select f.F_ID, f.F_LIBELLE, f.F_DESCRIPTION, nb.F_ID as BLOCKED
        from fonctionnalite f left join notification_block nb on ( nb.P_ID = ".$person." and nb.F_ID = f.F_ID)
        where ( f.TF_ID = 10 or f.F_ID in (13,48,73,74) )
        order by f.F_LIBELLE";
if ( $gardes == 0 ) $query .= " and f.F_ID <> 60";
$result=mysqli_query($dbc,$query);

// -------------------------------------------
// Update
// -------------------------------------------
if (isset($_POST["person"])) {

    $query="delete from notification_block where P_ID =".$person;
    mysqli_query($dbc,$query);
    $A="";

    while ( $row=mysqli_fetch_array($result)) {
        $F_ID=intval($row["F_ID"]);
        if ( $F_ID > 0 ) {
            $UID="U".$F_ID;
            insert_block($UID, $person);
        }
    }
    
    insert_log('UPDP15', $person,rtrim($A,'+'));
    echo "<body onload=\"javascript:self.location.href='upd_personnel.php?pompier=".$person."'\";>";
    exit;
}

// -------------------------------------------
// Display
// -------------------------------------------

$modal=true;
$nomenu=1;
writehead();

$helper="<a href='#' data-toggle='popover' title=\"Notifications par mail\" data-trigger='hover' data-placement='bottom'
            data-content=\"Si les permissions permettent de les recevoir, cochez les cases pour accepter 
        ces notifications facultatives. Ou décochez les cases pour éviter le spam\" ><i class='fas fa-info-circle'></i></a>";
        
write_modal_header(ucfirst(get_prenom($person))." ".strtoupper(get_nom($person))." ".$helper);

echo "<script>
$(document).ready(function(){
    $('[data-toggle=\"popover\"]').popover();
});
</SCRIPT>
</HEAD>";

$html=  "<body><div align=center><form name='notifications' action='notification_param.php' method='POST'>
<input type='hidden' name='person' value='".$person."'>
<table class='noBorder' >
    <tr height=10 style='background-color:white;'>
      <td width='200' align=center><b>Notification</b></td>
      <td width='50' align=center><b>Recevoir</b></td>
    </tr>";

while ( $row=mysqli_fetch_array($result)) {
    $F_ID=intval($row["F_ID"]); 
    $F_LIBELLE=str_replace("Notifications ","",$row["F_LIBELLE"]);
    $F_LIBELLE=str_replace("Notification ","",$F_LIBELLE);
    $F_DESCRIPTION=str_replace("<br>","",$row["F_DESCRIPTION"]);
    $F_DESCRIPTION=str_replace("<b>","",$F_DESCRIPTION);
    $F_DESCRIPTION=str_replace("</b>","",$F_DESCRIPTION);
    $BLOCKED=intval($row["BLOCKED"]);
    
    if ( $BLOCKED == 0 ) $checked ='checked';
    else $checked ='';
    
    if ( check_rights($person,$F_ID) == 1) $checkbox="<input type='hidden' name='U".$F_ID."' value='1'>
                                                      <input type='checkbox' name='F".$F_ID."' title='cocher pour recevoir' value=1 $checked>";
    else $checkbox="<i class='fa fa-ban fa-lg' style='color:red' title=\"Vous n'avez pas la permission suffisante pour recevoir cette notification.\"></i>"; 
    
    if ( $F_ID == 48 and ! check_rights($id,48,"0")) // a le droit d'imprimer les diplomes nationaux?
        $html.="";
    else
        $html.="
        <tr style='background-color:white;'>
        <td><a href='#' data-toggle='popover' title=\"".ucfirst($F_LIBELLE)."\" data-trigger='hover' 
            data-content=\"".$F_DESCRIPTION."\" ><b>".ucfirst($F_LIBELLE)."</b></a></td>
        <td align=center>".$checkbox."</td>
        </tr>";
}    

$html.= "</table>";
$html.= "<p><div><input type='submit' class='btn btn-default' value='enregistrer'><p></div></form>";
$html.= "</div></body></html>";

print $html;
writefoot($loadjs=false);
?>
