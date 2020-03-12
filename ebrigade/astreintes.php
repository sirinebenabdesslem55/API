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
check_all(52);
$id=$_SESSION['id'];
get_session_parameters();
writehead();
test_permission_level(52);

$possibleorders= array('AS_DEBUT','AS_FIN','GP_DESCRIPTION','P_NOM', 'S_CODE', 'P_EMAIL', 'P_PHONE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='AS_DEBUT';

writehead();

$query="select distinct GP_ID, GP_DESCRIPTION
        from groupe
        where GP_ASTREINTE=1";
$result=mysqli_query($dbc,$query);
$nb=mysqli_num_rows($result);

if ( $nb == 0 ) {
    write_msgbox("paramétrage incomplet", $warning_pic, "Aucun <a href=habilitations.php?category=R>rôle</a> de l'organigramme ne supporte les astreintes",10,0);
    exit;
}

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<SCRIPT>
function redirect( section, sub, debut, fin, order, person, type_astreinte) {
     url = "astreintes.php?filter="+section+"&dtdb="+debut+"&subsections="+sub+"&dtfn="+fin+"&order="+order+"&person="+person+"&type_astreinte="+type_astreinte;
     self.location.href = url;
}
function redirect2( section, sub, debut, fin, order, person, type_astreinte) {
     if (sub.checked) s = 1;
     else s = 0;     
     url = "astreintes.php?filter="+section+"&dtdb="+debut+"&subsections="+s+"&dtfn="+fin+"&order="+order+"&person="+person+"&type_astreinte="+type_astreinte;
     self.location.href = url;
}
function bouton_redirect(cible) {
     self.location.href = cible;
}
function impression(){ 
    this.print(); 
}
</SCRIPT>

<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>

</HEAD>

<?php

$query="select a.AS_ID, a.S_ID, a.GP_ID, a.P_ID, g.GP_DESCRIPTION,
    DATE_FORMAT(a.AS_DEBUT, '%d-%m-%Y') as DATE_DEBUT,
    DATE_FORMAT(a.AS_FIN, '%d-%m-%Y') as DATE_FIN, 
    a.AS_UPDATED_BY, a.AS_UPDATE_DATE,
    p.P_NOM, p.P_PRENOM, p.P_EMAIL, p.P_PHONE,
    s.S_CODE
    from  section s, groupe g, 
    astreinte a
    left join pompier p on a.P_ID=p.P_ID
    where a.S_ID = s.S_ID
    and a.GP_ID=g.GP_ID";
     if ( $subsections == 1 ) {
          if ( $filter > 0 ) 
             $query .= "\n and a.S_ID in (".get_family("$filter").")";
     }
     else 
         $query .= "\n and a.S_ID =".$filter;
$tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
$tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

$query .="\n and a.AS_DEBUT <= '$year2-$month2-$day2' 
             and a.AS_FIN   >= '$year1-$month1-$day1'";
             
if ( intval($person) > 0 ) $query .= "\nand  a.P_ID = ".$person;    
if ( intval($type_astreinte) > 0 ) $query .= "\nand  a.GP_ID = ".$type_astreinte;
         
$query .="\n order by ".$order;
//if ( $order == 'AS_DEBUT' or $order == 'AS_FIN' ) $query .= ' desc';

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

echo "<body>";
echo "<div align=center class='table-responsive'>
<table class='noBorder'>
<tr>
<td><font size=4><b>Liste des astreintes</b><font size=2><i> ($number trouvées)</i></font>
</td>";

echo "<td><a href='#'><i class='fa fa-print fa-lg' title=\"imprimer\" onclick=\"impression();\"></i></a></td>";

echo "<form name='formf' action='astreintes.php'>";
echo "<input type=hidden name=subsections id=subsections value=\"0\" />";
echo "<input type=hidden name=canceled id=canceled value=\"0\" />";

echo "<p><table class='noBorder'><tr>
<td rowspan=7>";
if ( check_rights($_SESSION['id'], 15)) {
   echo "<input type='button' class='btn btn-default' value='Ajouter' name='add_event' 
         onclick=\"bouton_redirect('astreinte_edit.php?astreinte=0')\">";
}
echo "</td>";

// choix section
echo "<tr><td align=right >";
echo choice_section_order('astreintes.php');
echo " </td>";
 // choix section
echo "<td align=left>
      <select id='filter' name='filter' 
     title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
     onchange=\"redirect(document.formf.filter.options[document.formf.filter.selectedIndex].value,'$subsections', '$dtdb', '$dtfn', '$order', '$person', '$type_astreinte')\">"; 
      display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select></td></tr>";

echo "<tr><td align=right></td>";
echo "<td align=left>";
 if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<input type='checkbox' name='subsections' id='subsections' value='1' $checked 
       onClick=\"redirect2('$filter', this , '$dtdb', '$dtfn', '$order', '$person', '$type_astreinte')\"/>
       <label for='subsections'>inclure les $sous_sections</label>";
}
echo "</td></tr>";


// choix type d'astreinte
echo "<tr><td align=right> Type </td>";
echo "<td align=left>
<select id='type_astreinte' name='type_astreinte' 
onchange=\"redirect('$filter','$subsections', '$dtdb', '$dtfn', '$order','$person', document.formf.type_astreinte.options[document.formf.type_astreinte.selectedIndex].value);\">"; 
echo "<option value='ALL' selected>Tous les types d'astreintes </option>";
$query1 ="select distinct GP_ID, GP_DESCRIPTION from groupe where GP_ASTREINTE=1";
$query1 .=" order by GP_DESCRIPTION";
$result1=mysqli_query($dbc,$query1);
while ($row1=@mysqli_fetch_array($result1)) {
      $GP_ID=$row1["GP_ID"];
      $GP_DESCRIPTION=$row1["GP_DESCRIPTION"];
      if ( $type_astreinte == $GP_ID ) $selected='selected';
      else $selected='';
      echo "<option value='".$GP_ID."' $selected>".$GP_DESCRIPTION."</option>";
}
echo "</select>
</td></tr>";

// choix personne
echo "<tr><td align=right> Nom </td>";
echo "<td align=left>
<select id='person' name='person' 
onchange=\"redirect('$filter','$subsections', '$dtdb', '$dtfn', '$order',document.formf.person.options[document.formf.person.selectedIndex].value, '$type_astreinte');\">"; 
echo "<option value='ALL' selected>Toutes les personnes </option>";
$query1 ="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, s.S_CODE
         from pompier p, section s, astreinte a 
         where p.P_SECTION = s.S_ID 
         and a.P_ID=p.P_ID
         union select p.P_ID, p.P_NOM, p.P_PRENOM, s.S_CODE
         from pompier p, section s 
         where p.P_SECTION = s.S_ID 
         and p.P_ID=".intval($person);
$query1 .=" order by P_NOM";
$result1=mysqli_query($dbc,$query1);
while ($row1=@mysqli_fetch_array($result1)) {
      $P_ID=$row1["P_ID"];
      $P_NOM=$row1["P_NOM"];
      $S_CODE=$row1["S_CODE"];
      $P_PRENOM=$row1["P_PRENOM"];
      if ( $person == $P_ID ) $selected='selected';
      else $selected='';
      $s = " - ".$S_CODE;
      echo "<option value='".$P_ID."' $selected>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM).$s."</option>";
}
echo "</select>
</td></tr>";

// Choix Dates
echo "<tr><td align=right >Début:</td><td align=left>
        <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(document.formf.dtdb)
            style='width:100px;'>";
echo "</td></tr>";

echo "<tr><td align=right >Fin :</td><td align=left>
        <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(document.formf.dtfn)
            style='width:100px;'>";

echo " <input type='submit'  class='btn btn-default' value='go'>";
echo "</td></tr>";

echo "<tr><td colspan=3>";
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
    $query .= $pages->limit;
}
$result=mysqli_query($dbc,$query);

echo "</td></tr></table>";
echo "</form>";

if ( $number > 0 ) {
   echo "<p><table cellspacing=0 border=0>";
   echo "<tr class=TabHeader>
            <td width=150 align=center><a href=astreintes.php?order=AS_DEBUT class=TabHeader>Début</a></td>
          <td width=150 align=center><a href=astreintes.php?order=AS_FIN class=TabHeader>Fin</a></td>
          <td width=40></td>
          <td width=140 align=left><a href=astreintes.php?order=GP_DESCRIPTION class=TabHeader>Rôle</a></td>
          <td width=140 align=center><a href=astreintes.php?order=S_ID class=TabHeader>Section</a></td>
            <td width=150 align=center><a href=astreintes.php?order=P_NOM class=TabHeader>Nom</a></td>
            <td width=120 align=center><a href=astreintes.php?order=P_EMAIL class=TabHeader>email</a></td>
            <td width=120 align=center><a href=astreintes.php?order=P_PHONE class=TabHeader>téléphone</a></td>
        </tr>
      ";

   $i=0;
   while ($row=@mysqli_fetch_array($result)) {
      $AS_ID=$row["AS_ID"];
      $S_CODE=$row["S_CODE"];
      $S_ID=$row["S_ID"];
      $P_ID=$row["P_ID"];
      $GP_ID=$row["GP_ID"];
      $GP_DESCRIPTION=$row["GP_DESCRIPTION"];
      $P_NOM=strtoupper($row["P_NOM"]);
      $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
      $DATE_DEBUT=$row["DATE_DEBUT"];
      $DATE_FIN=$row["DATE_FIN"];
      $P_EMAIL=$row["P_EMAIL"];
      if ( $P_EMAIL <> "" ) $P_EMAIL="<a href=mailto:".$P_EMAIL.">".$P_EMAIL."</a>";
      $P_PHONE=$row["P_PHONE"];
      
      $i=$i+1;
      if ( $i%2 == 0 ) {
           $mycolor="$mylightcolor";
      }
      else {
           $mycolor="#FFFFFF";
      }
      
      $tmp=explode ("-",$DATE_DEBUT); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
      $date1=mktime(0,0,0,$month1,$day1,$year1);
      $ladate1=date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1;
      
      $tmp=explode ("-",$DATE_FIN); $day2=$tmp[0]; $month2=$tmp[1]; $year2=$tmp[2];
      $date2=mktime(0,0,0,$month2,$day2,$year2);
      $ladate2=date_fran($month2, $day2 ,$year2)." ".moislettres($month2)." ".$year2;
      
      
      // chercher les chevauchements
       $query2="select count(*) -1 from astreinte
        where GP_ID=".$GP_ID."
        and S_ID=".$S_ID."
        and AS_DEBUT <= '$year2-$month2-$day2' 
        and AS_FIN   >= '$year1-$month1-$day1'";
      $result2=mysqli_query($dbc,$query2);
      $row2=@mysqli_fetch_array($result2);
      $num=$row2[0];
      if ( $num > 0 ) $w="<i class='fa fa-exclamation-triangle' style='color:orange;' title=\"Attention: chevauchement avec $num autre(s) astreinte(s)\"></i>";
      else $w="";
      
      // chercher les absences
      $absences=count_absences($P_ID, $year1."-".$month1."-".$day1 , $year2."-".$month2."-".$day2);
      if ( $absences > 0 ) $z="<i class='fa fa-exclamation-circle' style='color:red;' title=\"Attention: cette personne a des absences enregistrées pendant cette asterinte\"></i>";
      else $z="";
      
      if ( check_rights($id,26))
        echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" 
            onclick=\"this.bgColor='#33FF00'; bouton_redirect('astreinte_edit.php?astreinte=".$AS_ID."');\" >";
      else echo "<tr bgcolor=$mycolor>"; 
      echo "<td align=center><font size=1>".$ladate1."</font></td>
            <td align=center><font size=1>".$ladate2."</font></td>  
          <td align=center>".$w." ".$z."</td>
          <td align=left>";
      if ( check_rights($id,9))
        echo "<a href=upd_habilitations.php?from=astreintes&gpid=".$GP_ID."><font size=1>".$GP_DESCRIPTION."</font></a></td>";
      else 
        echo $GP_DESCRIPTION."</td>";
      echo "<td align=center><a href=upd_section.php?S_ID=".$S_ID.">".$S_CODE."</a></td>      
            <td align=center>";
      if ( $P_ID == 0 ) echo "<i>personne</i>";
      else echo "<a href=upd_personnel.php?pompier=".$P_ID.">".$P_PRENOM." ".$P_NOM."</a></td>";
      echo " <td align=center><font size=1>".$P_EMAIL."</font></td>
            <td align=center></font>".$P_PHONE."</font></td>";
      echo "</tr>";
   }
   echo "</table>";
}
else {
     echo "<p><b>Aucune astreinte ne correspond aux critères choisis</b>";
}
writefoot();
?>

