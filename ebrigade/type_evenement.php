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

$possibleorders= array('TE_CODE','TE_LIBELLE','CEV_DESCRIPTION','TE_MAIN_COURANTE', 'TE_VICTIMES', 'TE_MULTI_DUPLI', 'ACCES_RESTREINT', 
                        'TE_PERSONNEL','TE_VEHICULES','TE_MATERIEL','TE_CONSOMMABLES', 'COLONNE_RENFORT');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TE_CODE';
writehead();
?>
<script type='text/javascript' src='js/type_evenement.js'></script>
<?php


$query1="select te.TE_CODE, te.TE_LIBELLE, te.CEV_CODE, cev.CEV_DESCRIPTION,
    te.TE_MAIN_COURANTE, te.TE_VICTIMES, te.TE_MULTI_DUPLI, te.ACCES_RESTREINT, te.TE_ICON,
    te.TE_PERSONNEL, te.TE_VEHICULES, te.TE_MATERIEL, te.TE_CONSOMMABLES, te.COLONNE_RENFORT
    from type_evenement te,
    categorie_evenement cev
    where cev.CEV_CODE = te.CEV_CODE";
$query1 .="\n order by ". $order;
if ( $order == 'TE_VICTIMES' or $order == 'TE_MULTI_DUPLI' or $order == 'TE_MAIN_COURANTE' or $order == 'ACCES_RESTREINT'
    or  $order == 'TE_PERSONNEL' or $order == 'TE_VEHICULES' or $order == 'TE_MATERIEL' or $order == 'TE_CONSOMMABLES' or $order == 'COLONNE_RENFORT') $query1 .=" desc";
$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<div align=center class='table-responsive'>
      <table class='noBorder'>
      <tr height=70>
        <td rowspan=2 width=100><input type='button' class='btn btn-default' value='Ajouter' name='ajouter' onclick=\"bouton_redirect('upd_type_evenement.php?operation=insert');\"></td>
        <td width = 60 ><i class='fa fa-info-circle fa-3x' style='color:blue;'></i></td>
        <td><font size=4>Types d'événements </font> <span class='badge'>$number</span></td>
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

echo "<tr class='TabHeader' bgcolor=$mydarkcolor>
    <td width=30></td>
    <td width=40 align=center><a href=type_evenement.php?order=TE_CODE class=TabHeader>Code</a></td>
    <td width=230 align=center><a href=type_evenement.php?order=TE_LIBELLE class=TabHeader>Nom</a></td>
    <td width=220 align=center><a href=type_evenement.php?order=CEV_DESCRIPTION class=TabHeader>Catégorie</a></td>
    <td width=60 align=center><a href=type_evenement.php?order=TE_PERSONNEL class=TabHeader>Personnel</a></td>";
if ( $vehicules == 1 ) 
echo "<td width=60 align=center><a href=type_evenement.php?order=TE_VEHICULES class=TabHeader>Véhicules</a></td>";
if ( $materiel == 1 ) 
echo "<td width=60 align=center><a href=type_evenement.php?order=TE_MATERIEL class=TabHeader>Matériel</a></td>";
if ( $consommables == 1 )
echo "<td width=60 align=center><a href=type_evenement.php?order=TE_CONSOMMABLES class=TabHeader>Consommables</a></td>";
echo "<td width=60 align=center><a href=type_evenement.php?order=TE_MAIN_COURANTE class=TabHeader>Rapport</a></td>
    <td width=60 align=center><a href=type_evenement.php?order=TE_VICTIMES class=TabHeader>Victimes</a></td>
    <td width=60 align=center><a href=type_evenement.php?order=TE_MULTI_DUPLI class=TabHeader>Duplication Multiple</a></td>
    <td width=60 align=center><a href=type_evenement.php?order=ACCES_RESTREINT class=TabHeader>Accès restreint</a></td>";
if ( $syndicate == 0 ) 
echo "<td width=60 align=center><a href=type_evenement.php?order=COLONNE_RENFORT class=TabHeader>Colonne renfort</a></td>";
echo "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result1)) {
    
    if ( $TE_MAIN_COURANTE == 1 ) $TE_MAIN_COURANTE = "<i class='fa fa-check' title=\"Il est possible d'écrire une main courante pour ce type d'événement\"></i>";
    else $TE_MAIN_COURANTE ="";
    
    if ( $TE_VICTIMES == 1 ) $TE_VICTIMES = "<i class='fa fa-check'  title=\"Il est possible d'enregistrer des victimes sur ce type d'événement\"></i>";
    else $TE_VICTIMES ="";
    
    if ( $TE_MULTI_DUPLI == 1 ) $TE_MULTI_DUPLI = "<i class='fa fa-check'  title=\"Il est possible de faire des duplications multiples pour ce type d'événement\"></i>";
    else $TE_MULTI_DUPLI ="";
    
    if ( $ACCES_RESTREINT == 1 ) $ACCES_RESTREINT = "<i class='fa fa-check'  title=\"Les événements de ce type ne sont visibles que par les inscrits et les responsables\"></i>";
    else $ACCES_RESTREINT ="";
    
    if ( $TE_PERSONNEL == 1 ) $TE_PERSONNEL = "<i class='fa fa-check'  title=\"On peut inscrire du personnel sur ce type d'événement\"></i>";
    else $TE_PERSONNEL ="";
    
    if ( $TE_VEHICULES == 1 ) $TE_VEHICULES = "<i class='fa fa-check'  title=\"Les véhicules peuvent être engagés sur ce type d'événement\"></i>";
    else $TE_VEHICULES ="";
    
    if ( $TE_MATERIEL == 1 ) $TE_MATERIEL = "<i class='fa fa-check'  title=\"Du matériel peut êtree engagé sur ce type d'événements\"></i>";
    else $TE_MATERIEL ="";
    
    if ( $TE_CONSOMMABLES == 1 ) $TE_CONSOMMABLES = "<i class='fa fa-check'  title=\"Des consommations de produits peuvent être enregistrées sur ce type d'événement\"></i>";
    else $TE_CONSOMMABLES ="";
    
    if ( $COLONNE_RENFORT == 1 ) $COLONNE_RENFORT = "<i class='fa fa-check'  title=\"Les événements de ce type peuvent avoir la propriété colonne de renfort activée.\"></i>";
    else $COLONNE_RENFORT ="";
    

    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor=$mylightcolor;
    }
    else {
        $mycolor="#FFFFFF";
    }
      
    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" 
    onclick=\"this.bgColor='#33FF00'; displaymanager('$TE_CODE')\" >
          <td ><img src=images/evenements/".$TE_ICON." class='img-max-20'></td>
            <td >$TE_CODE</td>
          <td >$TE_LIBELLE</td>
          <td align=center>$CEV_DESCRIPTION</td>
          <td align=center>$TE_PERSONNEL</td>";
    if ( $vehicules == 1 ) 
    echo "<td align=center>$TE_VEHICULES</td>";
         
    if ( $materiel == 1 ) 
    echo "<td align=center>$TE_MATERIEL</td>";
          
    if ( $consommables == 1 ) 
    echo "<td align=center>$TE_CONSOMMABLES</td>";
    echo "<td align=center>$TE_MAIN_COURANTE</td>
          <td align=center>$TE_VICTIMES</td>
          <td align=center>$TE_MULTI_DUPLI</td>
          <td align=center>$ACCES_RESTREINT</td>";
    if ( $syndicate == 0 ) 
    echo "<td align=center>$COLONNE_RENFORT</td>";
    echo "</tr>";
      
}
echo "</table>";
}

echo "<p><input type='button' class='btn btn-default' value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'></div>";
writefoot();
?>
