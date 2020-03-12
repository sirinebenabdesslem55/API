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
check_all(70);
get_session_parameters();
writehead();
test_permission_level(70);
$possibleorders= array('evenement','matos','dtdb');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='evenement';

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<STYLE type="text/css">
.section{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.categorie{color:black; background-color:white; font-size:9pt;}
.materiel{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<SCRIPT>
function redirect(matos, section, dtdb, dtfn, order, subsections) {
    var mad = document.getElementById('mad');
    if (mad.checked) m = 1;
    else m=0;
    url = "evenement_materiel.php?matos="+matos+"&dtdb="+dtdb+"&dtfn="+dtfn+"&order="+order+"&filter="+section+"&subsections="+subsections+"&mad="+m;
    self.location.href = url;
}
function redirect2(matos, section, dtdb, dtfn, order, sub) {
    if (sub.checked) subsections = 1;
    else subsections = 0;
    var mad = document.getElementById('mad');
    if (mad.checked) m = 1;
    else m=0;
    url = "evenement_materiel.php?matos="+matos+"&dtdb="+dtdb+"&dtfn="+dtfn+"&order="+order+"&filter="+section+"&subsections="+subsections+"&mad="+m;
    self.location.href = url;
}
</SCRIPT>
<?php
echo "</head>";
include_once ("config.php");
echo "<body>";

$query="select tm.TM_CODE, m.MA_ID, m.MA_MODELE, m.MA_NUMERO_SERIE,
        DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
        DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, e.E_CODE,
        e.TE_CODE, e.E_LIBELLE, m.S_ID, s.S_DESCRIPTION,
        vp.VP_OPERATIONNEL, vp.VP_LIBELLE, em.EM_NB, m.MA_NB,
        e.E_CANCELED, e.E_CLOSED,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') as EH_DEBUT, 
        TIME_FORMAT(eh.EH_FIN, '%k:%i') as  EH_FIN,
        eh.EH_ID,
        cm.PICTURE, te.TE_ICON, m.MA_EXTERNE
        from evenement e, materiel m, evenement_materiel em, section s,
        vehicule_position vp, type_materiel tm, categorie_materiel cm, evenement_horaire eh, type_evenement te
        where m.MA_ID=em.MA_ID
        and e.TE_CODE = te.TE_CODE
        and e.E_CODE = eh.E_CODE
        and cm.TM_USAGE=tm.TM_USAGE
        and tm.TM_ID=m.TM_ID
        and s.S_ID=m.S_ID
        and vp.VP_ID = m.VP_ID
        and e.E_CODE=em.E_CODE
        and eh.E_CODE=em.E_CODE
        and eh.EH_ID=1";
    
if ( $matos > 0 ) $query .= "\nand  m.MA_ID = '".$matos."'";
$tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
$tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

$query .="\n and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
             and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";

if ( $subsections == 1 )
     $query .= "\n and m.S_ID in (".get_family("$filter").")";
else 
     $query .= "\n and m.S_ID =".$filter;

if ( $mad == 1 ) {
    $query .="\n and m.MA_EXTERNE = 1 ";
}
if ( $order == 'matos')     $query .="\n order by tm.TM_USAGE, tm.TM_CODE, m.MA_ID, eh.EH_DATE_DEBUT";
if ( $order == 'dtdb')     $query .="\norder by eh.EH_DATE_DEBUT, e.E_CODE";
if ( $order == 'evenement') $query .="\norder by e.E_CODE";

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

echo "<div align=center class='table-responsive'><font size=4><b>Engagements du matériel </b></font> <span class='badge'>".$number."</span><br>";
echo "<form name=formf>";
echo "<table width=400 class='noBorder'>";

//---------------------
// choix section
//---------------------
echo "<tr><td align=right width=40%>".choice_section_order('evenement_materiel.php')."</td>";
echo "<td align=left width=60% >
        <select id='filter' name='filter' 
     title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
     onchange=\"redirect('0', this.form.filter.options[this.form.filter.selectedIndex].value,'$dtdb', '$dtfn', '$order', '$subsections')\">";
     display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";
      
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<br><input type='checkbox' name='subsections' id='subsections' value='1' $checked 
       onClick=\"redirect2('0', '$filter','$dtdb', '$dtfn', '$order', this)\"/>
       <label for='subsections'>inclure les $sous_sections</label>";
}
echo "</td></tr>";

//---------------------
// choix matériel
//---------------------
echo "<tr><td width=40% align=right> Matériel </td>";
echo "<td width=60% align=left><select id='menu1' name='menu1' onchange=\"redirect(this.form.menu1.options[this.form.menu1.selectedIndex].value , '$filter', '$dtdb', '$dtfn', '$order', '$subsections')\">";
echo "<option value='ALL' selected>Tout le matériel</option>\n";
$query2="select distinct tm.TM_USAGE, m.MA_ID, m.TM_ID, tm.TM_CODE, m.MA_NUMERO_SERIE,
        m.MA_MODELE, m.MA_NB, s.S_DESCRIPTION, s.S_ID, s.S_CODE,tm.TM_USAGE
        from materiel m, section s, type_materiel tm
        where s.S_ID = m.S_ID
        and tm.TM_ID = m.TM_ID";

if ( $subsections == 1 ) $list=get_children("$filter");
else $list='';
if ( $list == '' ) $list=$filter;
else $list=$filter.",".$list;
$query2 .= " and m.S_ID in (".$list.")
                 order by s.S_ID, tm.TM_USAGE, tm.TM_CODE";

$result2=mysqli_query($dbc,$query2);
$prevS_ID=-1; $prevTM_USAGE="";
while (custom_fetch_array($result2)) {
    if (( $prevS_ID <> $S_ID ) and ( $nbsections == 0 )) echo "<OPTGROUP LABEL='".$S_CODE." - ".$S_DESCRIPTION."' class='section'>";
    $prevS_ID=$S_ID;
    if ( $prevTM_USAGE <> $TM_USAGE ) echo "<OPTGROUP LABEL='...".$TM_USAGE."' class='categorie'>";
    $prevTM_USAGE=$TM_USAGE;
    if ( $matos == $MA_ID ) $selected='selected';
    else $selected='';
    echo "<option value='".$MA_ID."' $selected class='materiel'>".$TM_CODE." - ".$MA_MODELE."</option>\n";
}
echo "</select>";

// filtre seulement mis à disposition
if ( $assoc == 1 ) {
    if ( $mad == 1 ) $checked='checked';
    else $checked='';
    echo " <input type='checkbox' name='mad' id='mad'  value='1' $checked title='cocher pour afficher seulement le matériel mis à disposition'
     onClick=\"redirect('0', '$filter','$dtdb', '$dtfn', '$order', '$subsections')\"/>
        <label for='mad'>seulement le matériel mis à disposition</label>";
}
else echo "<input type='hidden' name='mad' id='mad' value='0'>";

echo "</td></tr>";

//---------------------
// choix date
//---------------------
echo "<tr><td align=right >Début:</td><td align=left>
        <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtdb)
            style='width:100px;'>";
echo "</td></tr>";

echo "<tr><td align=right >Fin :</td><td align=left>
        <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtfn)
            style='width:100px;'>";
echo " <input type='submit' class='btn btn-default' value='go'>";
echo "</td></tr>";

echo "</table></form>";


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

if ( $number > 0 ) {
    echo "<p><table cellspacing=0 border=0>";
    echo "<tr class='TabHeader' >
            <td width=280 align=center>
            <a href=evenement_materiel.php?order=evenement class=TabHeader>Evénement
          </td>
            <td width=460 align=center>
            <a href=evenement_materiel.php?order=matos class=TabHeader>Matériel
          </td>
            <td width=15 class=TabHeader></td>
            <td width=15 class=TabHeader></td>
            <td width=140 align=center><a href=evenement_materiel.php?order=dtdb class=TabHeader>Date
          </td>
            <td width=80 align=center class=TabHeader>Horaire</td>
            <td width=70 align=center class=TabHeader>Nombre</td>";
    if ( $assoc == 1 )
          echo "<td width=70 align=center class=TabHeader>MàD</td>";
    echo "</tr>";

    $i=0;
    $k=0;
    while (custom_fetch_array($result)) {
        if ( $EH_DATE_FIN == '') $EH_DATE_FIN = $EH_DATE_DEBUT;
        $i=$i+1;
        if ( $i%2 == 0 ) {
            $mycolor="$mylightcolor";
        }
        else {
            $mycolor="#FFFFFF";
        }
        if ( $E_CANCELED == 1 ) $myimg="<i class='fa fa-circle' style='color:red;' title='événement annulé'></i>";
        elseif ( $E_CLOSED == 1 ) $myimg="<i class='fa fa-circle' style='color:orange;' title='inscriptions fermées'></i>";
        else $myimg="<i class='fa fa-circle' style='color:green;' title='inscriptions ouvertes'></i>";
      
        $tmp=explode ( "-",$EH_DATE_DEBUT); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
        $date1=mktime(0,0,0,$month1,$day1,$year1);
        $ladate=date_fran($month1, $day1 ,$year1)." ".moislettres($month1);
    
        $year2=$year1;
        $month2=$month1;
        $day2=$day1;
      
        if ( $EH_DATE_FIN <> '' and $EH_DATE_FIN <> $EH_DATE_DEBUT) {
            $tmp=explode ( "-",$EH_DATE_FIN); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
            $date1=mktime(0,0,0,$month1,$day1,$year1);
            $ladate=$ladate." au<br> ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1;
        }
        else $ladate=$ladate." ".$year1;
      
        $removelink="";
        if (( check_rights($_SESSION['id'], 15)) and ( is_children($S_ID,$mysection))) {
            $removelink="<a href=evenement_materiel_add.php?evenement=".$E_CODE."&action=remove&MA_ID=".$MA_ID."&from=materiel&dtdb=$dtdb&order=$order&filtermateriel=$matos>
                    <i class='fa fa-trash' title='désengager ce matériel' ></i></a>";
        }

        if ( $nbsections == 0 ) $sectioninfo="(".$S_DESCRIPTION.")";
        else $sectioninfo="";
        if ( $E_CODE <> $k ) {
            $evenementinfo="<td ><table class='noBorder'><tr>
              <td><a href=evenement_display.php?evenement=".$E_CODE."&from=materiel>
               <img src=images/evenements/".$TE_ICON." class='img-max-20'>
             </td>
             <td><a href=evenement_display.php?evenement=".$E_CODE."&from=materiel>
                 <font size=1>".$E_LIBELLE."</a></font>  ".$myimg."
             </td>
             </tr></table></td>";
             $k = $E_CODE;    
        }
        else $evenementinfo="<td ></td>
                           ";
          
        echo "<tr bgcolor=$mycolor >";
        echo $evenementinfo;

        if ( $VP_OPERATIONNEL == -1 ) $mytxtcolor="black";
        else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;      
        else if ( $VP_OPERATIONNEL == 2) $mytxtcolor=$orange;
        else $mytxtcolor=$green;


        $nb = get_nb_engagements('M', $MA_ID, $year1, $month1, $day1, $year2, $month2, $day2, $E_CODE) ;
        if ( $nb > $MA_NB ) 
               $myimg="<i class='fa fa-circle' style='color:orange;' title='attention ce matériel est parallèlement engagé sur 1 autre événement'></i>";
        else $myimg="";
        if ( $MA_EXTERNE == 1 ) $img3="<i class='fa fa-check' title=\"matériel mis à disposition par $cisname\"></i>";
        else $img3=''; 
     
        echo "<td ><a href=upd_materiel.php?mid=".$MA_ID.">
              <i class='fa fa-".$PICTURE."' style='color:purple;'></i>
            <b> ".$TM_CODE."</b> - <font size=1>".$MA_MODELE." - ".$MA_NUMERO_SERIE."</a> ".$sectioninfo."</font><font color=$mytxtcolor size=1> $VP_LIBELLE</font></td>
            <td >".$myimg."</td>
            <td >".$removelink."</td>
            <td align=center>
                <font size=1>".$ladate."</font></td>
            <td align=center>
                <font size=1>".$EH_DEBUT."-".$EH_FIN."</font></td>
            <td align=center>
                <font fsize=1>".$EM_NB." / ".$MA_NB."</font></td>";
            if ( $assoc == 1 )
                echo "<td align=center>".$img3."</td>";
        echo "</tr>";
    }
}
else {
    echo "<p><b>Aucun engagement ne correspond aux critères choisis</b>";
}

echo "</table></div>";
writefoot();
?>

