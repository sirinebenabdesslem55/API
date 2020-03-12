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
check_all(18);
get_session_parameters();

$possibleorders= array('TV_CODE','TV_LIBELLE','TV_NB','TV_USAGE','NB', 'TV_ICON');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TV_USAGE';
writehead();
?>

<script language="JavaScript">
function orderfilter(p1){
    self.location.href="type_vehicule.php?order="+p1;
    return true
}
function displaymanager(p1){
    self.location.href="upd_type_vehicule.php?TV_CODE="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

</script>
<?php

$query1="select tv.TV_ICON, tv.TV_CODE,tv.TV_LIBELLE,tv.TV_NB,tv.TV_USAGE, count(*) as NB
        from type_vehicule tv left join vehicule v on v.TV_CODE = tv.TV_CODE
        group by tv.TV_CODE";
$query1 .="\n order by ". $order;
if ( $order == 'TV_NB' or $order == 'NB') $query1 .=" desc";
$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<div align=center>
      <table class='noBorder'>
      <tr height=70>
        <td rowspan=2 width=100><input type='button' class='btn btn-default' value='Ajouter' name='ajouter' onclick=\"bouton_redirect('upd_type_vehicule.php?operation=insert');\"></td>
        <td width = 50 ><i class='fa fa-truck fa-3x'></i></td>
        <td><font size=4><b>Types de véhicules</b> <span class='badge'>$number</span></font></td>
      </tr>
      <tr>
      <td colspan=2>";


// ====================================
// pagination
// ====================================
require_once('paginator.class.php');
$pages = new Paginator;  
$pages->items_total = $number;  
$pages->mid_range = 9;  
$pages->paginate();  
if ( $number > 10 ) {
    echo $pages->display_pages();
    echo $pages->display_jump_menu(); 
    echo $pages->display_items_per_page(); 
    $query1 .= $pages->limit;
}
$result1=mysqli_query($dbc,$query1);

echo "</td></tr></table>";

if ( $number > 0 ) {
    echo "<p><table cellspacing=0 border=0>";

    // ===============================================
    // premiere ligne du tableau
    // ===============================================

    echo "<tr class='TabHeader'>
        <td width=45 align=center><a href=type_vehicule.php?order=TV_ICON class=TabHeader>icône</a></td>
        <td width=70 align=center><a href=type_vehicule.php?order=TV_CODE class=TabHeader>Code</a></td>
        <td width=300 align=center><a href=type_vehicule.php?order=TV_LIBELLE class=TabHeader>Nom</a></td>
        <td width=80 align=center><a href=type_vehicule.php?order=TV_USAGE class=TabHeader>Catégorie</a></td>
        <td width=30 align=center><a href=type_vehicule.php?order=TV_NB class=TabHeader title='Nombre de personnels dans le véhicule'>Equipage</a></td>
        <td width=30 align=center><a href=type_vehicule.php?order=NB class=TabHeader title='Nombre véhicules dans la base (y compris réformés)'>Nombre</a></td>
        </tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while ($row=@mysqli_fetch_array($result1)) {
         $TV_USAGE=$row["TV_USAGE"];
        $TV_CODE=$row["TV_CODE"];
        $TV_LIBELLE=$row["TV_LIBELLE"];
        $TV_NB=$row["TV_NB"];
        $TV_ICON=$row["TV_ICON"];
        if ( $TV_ICON == '' ) $img="";
        else $img="<img src=".$TV_ICON." class='img-max-22'>";
        $NB=$row["NB"];
        
        if ( $NB == 1 ) {
            $query2="select count(1) from vehicule where TV_CODE=\"".$TV_CODE."\"";    
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $NB=$row2[0];
        }
        $i=$i+1;
        if ( $i%2 == 0 ) {
            $mycolor=$mylightcolor;
        }
        else {
            $mycolor="#FFFFFF";
        }
          
        echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" 
        onclick=\"this.bgColor='#33FF00'; displaymanager('$TV_CODE')\" >
              <td >".$img."</td>
                <td >$TV_CODE</td>
              <td >$TV_LIBELLE</td>
              <td align=center>$TV_USAGE</td>
              <td align=center>$TV_NB</td>
              <td align=center>$NB</td>
          </tr>";
          
    }
    echo "</table>";
}

echo "<p><input type='button' class='btn btn-default' value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'>";
writefoot();

?>
