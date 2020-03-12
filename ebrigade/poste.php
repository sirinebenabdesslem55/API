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
$possibleorders= array('EQ_ID','PS_ID','TYPE','DESCRIPTION','PS_EXPIRABLE',
                        'PS_AUDIT','PS_DIPLOMA','PS_NUMERO', 'PS_SECOURISME','PS_NATIONAL','PS_PRINTABLE','PS_PRINT_IMAGE',
                        'PS_RECYCLE','PS_USER_MODIFIABLE','F_LIBELLE','PH_CODE', 'PS_FORMATION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='EQ_ID';
writehead();
?>
<script type='text/javascript' src='js/competence.js'></script>
<?php

echo "<body>";

$query1="select p.PS_ID, p.EQ_ID, p.TYPE, p.DESCRIPTION,
        e.EQ_NOM,p.PS_EXPIRABLE, p.PS_AUDIT, p.PS_DIPLOMA, p.PS_NUMERO, p.F_ID,
        p.PS_RECYCLE, p.PS_USER_MODIFIABLE, p.PS_PRINTABLE, p.PS_PRINT_IMAGE, p.PS_NATIONAL, p.PS_SECOURISME,PS_FORMATION,
        case
            when f.F_ID = 4 then 'zzz'
            else f.F_LIBELLE
        end
        as F_LIBELLE,
        p.PH_CODE, p.PH_LEVEL
        from equipe e, poste p left join poste_hierarchie ph on ph.PH_CODE = p.PH_CODE,
        fonctionnalite f
        where p.EQ_ID=e.EQ_ID
        and p.F_ID = f.F_ID";

if ( $typequalif <> 'ALL' ) $query1 .= "\nand p.EQ_ID='".$typequalif."'";
if ( $order == 'PH_CODE' ) $query1 .="\norder by ph.PH_CODE desc, p.PH_LEVEL desc";
else $query1 .="\norder by ". $order;
if ( $order == 'PS_EXPIRABLE' || $order == 'PS_AUDIT' 
    || $order == 'PS_DIPLOMA' || $order == 'PS_NUMERO' || $order == 'PS_PRINT_IMAGE'
    || $order == 'PS_RECYCLE' || $order == 'PS_USER_MODIFIABLE'
    || $order == 'PS_PRINTABLE' || $order == 'PS_NATIONAL'
    || $order == 'PS_SECOURISME' || $order == 'PS_FORMATION') 
$query1 .= " desc";

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);


echo "<div align=center class='table-responsive'><font size=4><b>Comp�tences</b></font> <span class='badge'>".$number ."</span></i>";
echo "<p><table class='noBorder'>";
echo "<tr>";
echo "<td ><input type='button' class='btn btn-default' value='Hi�rarchies' title='Voir les hi�rarchies de comp�tences ' onclick=\"bouton_redirect('hierarchie_competence.php');\"></td>";
echo "<td ><input type='button' class='btn btn-default' value='Types' title='Voir les types de comp�tences ' onclick=\"bouton_redirect('equipe.php');\"></td>";
echo "<td><select id='typequalif' name='typequalif' onchange=\"orderfilter('".$order."',document.getElementById('typequalif').value)\">
      <option value='ALL'>toutes types</option>";


$query2="select distinct EQ_ID, EQ_NOM from equipe";
$result2=mysqli_query($dbc,$query2);
while (custom_fetch_array($result2)) {
    echo "<option value='".$EQ_ID."'";
    if ($EQ_ID == $typequalif ) echo " selected ";
    echo ">".$EQ_NOM."</option>\n";
}
echo "</select></td> ";
if ( $number < $nbmaxpostes )
       echo "<td><input type='button' class='btn btn-default' value='Ajouter' title='Ajouter une comp�tence' onclick=\"bouton_redirect('ins_poste.php');\"></td>";
else
       echo "<td ><font color=red><b>Vous ne pouvez plus ajouter de $title ( maximum atteint: $nbmaxpostes)</b></font></td>";

echo "</tr>
        <tr><td colspan=4 align=center>";
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
echo "<p><table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr height=10 class=TabHeader>
            <td width=200><a href=poste.php?order=EQ_ID class=TabHeader>Type</a></td>
            <td width=30><a href=poste.php?order=PS_ID class=TabHeader>N�</a></td>
            <td width=70><a href=poste.php?order=TYPE class=TabHeader>Code</a></td>
          <td width=100><a href=poste.php?order=PH_CODE class=TabHeader>Hi�rarchie</a></td>
            <td width=240><a href=poste.php?order=DESCRIPTION class=TabHeader>Description</a></td>
            ";
echo "  <td width=50 align=center>
            <a href=poste.php?order=PS_SECOURISME class=TabHeader title='Comp�tence officielle de secourisme' >Secourisme</a></td>
        <td width=50 align=center>
            <a href=poste.php?order=PS_FORMATION class=TabHeader title=\"On peut organiser des formations pour cette comp�tence\">Formation.</a></td>
        <td width=50 align=center>
            <a href=poste.php?order=PS_RECYCLE class=TabHeader title='Recyclage ou formation continue n�cessaire'>Recycl.</a></td>
        <td width=50 align=center>
            <a href=poste.php?order=PS_EXPIRABLE class=TabHeader title=\"On peut d�finir une date d'expiration sur cette comp�tence\">Exp.</a></td>
        <td width=50 align=center>
            <a href=poste.php?order=PS_DIPLOMA class=TabHeader title='Un dipl�me est d�livr� apr�s formation' >Dipl�me</a></td>
        <td width=50 align=center>
            <a href=poste.php?order=PS_NUMERO class=TabHeader title='Dipl�mes num�rot� de fa�on unique' >Num�ro</a></td>
        <td width=50 align=center>
            <a href=poste.php?order=PS_NATIONAL class=TabHeader title='Le dipl�me est d�livr� au niveau national seulement' >National</a></td>
        <td width=50 align=center>
            <a href=poste.php?order=PS_PRINTABLE class=TabHeader title=\"Possibilit� d'imprimer un dipl�me\">Print.</a></td>
        <td width=50 align=center>
            <a href=poste.php?order=PS_PRINT_IMAGE class=TabHeader title=\"L'image du dipl�me est obligatoirement imprim�e\">Image.</a></td>
        <td width=50 align=center>
            <a href=poste.php?order=PS_USER_MODIFIABLE class=TabHeader title='Modifiable par chaque utilisateur'>Modif.</a></td> 
        <td width=50 align=center>
            <a href=poste.php?order=PS_AUDIT class=TabHeader title='Un mail est envoy� au secr�tariat en cas de modification'>Audit</a></td>
        <td width=50 align=center>
            <a href=poste.php?order=F_LIBELLE class=TabHeader title='Permission sp�ciale requise pour modifier cette comp�tence'>Perm.</a></td>
";
echo "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result1)) {
    $DESCRIPTION=strip_tags($DESCRIPTION);
      
    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor=$mylightcolor;
    }
    else {
          $mycolor="#FFFFFF";
    }
    if ( $PS_FORMATION == 1 ) $formation="<i class='fa fa-check '
    title = 'Possibilit� d''organiser des formations pour cette comp�tence'></i>";
    else $formation="";
    if ( $PS_EXPIRABLE == 1 ) $expirable="<i class='fa fa-check' 
    title = 'Expiration possible'></i>";
    else $expirable="";
    if ( $PS_AUDIT == 1 ) $audit="<i class='fa fa-check'
    title = 'Alerter si modifications'></i>";
    else $audit="";
    if ( $PS_DIPLOMA == 1 ) $diploma="<i class='fa fa-check '
    title = 'Dipl�me d�livr� apr�s une formation'></i>";
    else $diploma="";
    if ( $PS_NUMERO == 1 ) $numero="<i class='fa fa-check '
    title = 'Dipl�me num�rot� de fa�on unique'></i>";
    else $numero="";
    if ( $PS_SECOURISME == 1 ) $secourisme="<i class='fa fa-check '
    title = 'Comp�tence officielle de secourisme'></i>";
    else $secourisme="";
    if ( $PS_NATIONAL == 1 ) $national="<i class='fa fa-check '
    title = 'Dipl�me d�livr� au niveau national seulement'></i>";
    else $national="";
    if ( $PS_RECYCLE == 1 ) $recycle="<i class='fa fa-check' 
    title = 'Un recyclage p�riodique est n�cessaire'></i>";
    else $recycle="";
    if ( $PS_USER_MODIFIABLE == 1 ) $modifiable="<i class='fa fa-check' 
    title = 'Modifiable par chaque utilisateur'></i>";
    else $modifiable="";
    if ( $PS_PRINTABLE == 1 ) $printable="<i class='fa fa-check '
    title = 'Possibilit� d''imprimer un dipl�me'></i>";
    else $printable="";
    if ( $PS_PRINT_IMAGE == 1 ) $print_image="<i class='fa fa-check '
    title = 'L'image du dipl�me est obligatoirement imprim�e'></i>";
    else $print_image="";
    if ( $F_ID <> 4 ) $permission="<i class='fa fa-check' 
    title = \"Permission '$F_ID - $F_LIBELLE' requise pour modifier cette comp�tence\"></i> $F_ID";
    else $permission="";
    if ( $PH_CODE <> "" ) $hierarchy=$PH_CODE." niveau ".$PH_LEVEL;
    else $hierarchy="";
      
    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; displaymanager($PS_ID)\" >
            <td>$EQ_NOM</td>
            <td align=center>$PS_ID</td>
            <td>$TYPE</td>
            <td class=small>$hierarchy</td>
          <td class=small>$DESCRIPTION</td>
          <td align=center>$secourisme</td>
          <td align=center>$formation</td>
          <td align=center>$recycle</td>
          <td align=center>$expirable</td>
          <td align=center>$diploma</td>
          <td align=center>$numero</td>
          <td align=center>$national</td>
          <td align=center>$printable</td>
          <td align=center>$print_image</td>
          <td align=center>$modifiable</td>
          <td align=center>$audit</td>
          <td align=center>$permission</td>
        </tr>";
 
}

echo "</table>";

echo "<p><input type='button' class='btn btn-default' value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'></div>";
writefoot();
?>
