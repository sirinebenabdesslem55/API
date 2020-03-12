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
check_all(17);
get_session_parameters();
writehead();
test_permission_level(17);

$possibleorders= array('evenement','vehicule','dtdb');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='evenement';
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<STYLE type="text/css">
.section{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.categorie{color:black; background-color:white; font-size:9pt;}
.vehicule{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<SCRIPT>
function redirect(vehicule, section, dtdb, dtfn, order, subsections) {
    url = "evenement_vehicule.php?vehicule="+vehicule+"&dtdb="+dtdb+"&order="+order+"&dtfn="+dtfn+"&filter="+section+"&subsections="+subsections;
    self.location.href = url;
}
function redirect2(vehicule, section, dtdb, dtfn, order, sub) {
    if (sub.checked) subsections = 1;
    else subsections = 0;
    url = "evenement_vehicule.php?vehicule="+vehicule+"&dtdb="+dtdb+"&order="+order+"&dtfn="+dtfn+"&filter="+section+"&subsections="+subsections;
    self.location.href = url;
}
</SCRIPT>
<?php
echo "</head>";
echo "<body>";

$query="select distinct v.TV_CODE, v.V_ID, v.V_IMMATRICULATION, v.V_MODELE, 
        DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
        DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, e.E_CODE,
        e.TE_CODE, e.E_LIBELLE, v.S_ID, s.S_DESCRIPTION,
        vp.VP_OPERATIONNEL, vp.VP_LIBELLE,ev.EV_KM,
        DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
        DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
        DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
        e.E_CANCELED, e.E_CLOSED,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') as EH_DEBUT, 
        TIME_FORMAT(eh.EH_FIN, '%k:%i') as  EH_FIN,
        eh.EH_ID, te.TE_ICON
        from evenement e, vehicule v, evenement_vehicule ev, section s, vehicule_position vp, evenement_horaire eh, type_evenement te
        where v.V_ID=ev.V_ID
        and te.TE_CODE = e.TE_CODE
        and e.E_CODE = eh.E_CODE
        and s.S_ID=v.S_ID
        and vp.VP_ID = v.VP_ID
        and e.E_CODE=ev.E_CODE
        and eh.E_CODE=ev.E_CODE
        and eh.EH_ID=ev.EH_ID";
    
if ( $vehicule > 0 ) $query .= "\nand  v.V_ID = '".$vehicule."'";

$tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
$tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

$query .="\n and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
             and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";

if ( $subsections == 1 )
     $query .= "\n and v.S_ID in (".get_family("$filter").")";
else 
     $query .= "\n and v.S_ID =".$filter;


if ( $order == 'vehicule')     $query .="\n order by TV_CODE, V_ID";
if ( $order == 'dtdb')     $query .="\norder by eh.EH_DATE_DEBUT, e.E_CODE";
if ( $order == 'evenement') $query .="\norder by e.E_CODE, eh.EH_DATE_DEBUT";
    
//echo "$query<br>";
$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);



echo "<div align=center class='table-responsive'><font size=4><b>Engagements des véhicules </b></font> <span class='badge'>".$number." </span>";
echo " <i class='far fa-file-excel fa-lg' style='color:green;' id='StartExcel' title='Excel' onclick=\"window.open('evenement_vehicule_xls.php?vehicule=".$vehicule."&filter=".$filter."subsections=".$subsections."&dtdb=".$dtdb."&dtfn=".$dtfn."')\" align='right'></i>";
echo "<form name=formf>";
echo "<table width=400 class='noBorder' >";

//---------------------
// choix section
//---------------------
echo "<tr><td align=right width=40%>".choice_section_order('evenement_vehicule.php')."</td>";
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
// choix véhicule
//---------------------
echo "<tr><td align=right> Véhicule </td>";
echo "<td align=left>
      <select id='vehicule' name='vehicule' 
      onchange=\"redirect( this.form.vehicule.options[this.form.vehicule.selectedIndex].value ,'$filter', '$dtdb', '$dtfn', '$order', '$subsections')\">";
echo "<option value='0' selected>Tous véhicules</option>\n";
$query2="select distinct v.V_ID, v.TV_CODE, v.V_MODELE, v.V_IMMATRICULATION, s.S_DESCRIPTION, s.S_ID, s.S_CODE
        from vehicule v, section s
        where s.S_ID = v.S_ID";
if ( $subsections == 1 ) $list=get_children("$filter");
else $list='';
if ( $list == '' ) $list=$filter;
else $list=$filter.",".$list;
$query2 .= " and v.S_ID in (".$list.")
                 order by s.S_ID, v.TV_CODE";

$result2=mysqli_query($dbc,$query2);
$prevS_ID=-1;
while ($row2=@mysqli_fetch_array($result2)) {
      $V_ID=$row2["V_ID"];
      $S_ID=$row2["S_ID"];
      $S_CODE=$row2["S_CODE"];
      $TV_CODE=$row2["TV_CODE"];
      $S_DESCRIPTION=$row2["S_DESCRIPTION"];
      $V_MODELE=$row2["V_MODELE"];
      if (( $prevS_ID <> $S_ID ) and ( $nbsections == 0 )) echo "<OPTGROUP LABEL='".$S_CODE." - ".$S_DESCRIPTION."' class='section'>";
      $prevS_ID=$S_ID;
      $V_IMMATRICULATION=$row2["V_IMMATRICULATION"];
      if ( $vehicule == $V_ID ) $selected='selected';
      else $selected='';
      echo "<option value='".$V_ID."' $selected class='vehicule'>".$TV_CODE." - ".$V_MODELE." - ".$V_IMMATRICULATION."</option>\n";
}
echo "</select></td></tr>";

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
echo " <input type='submit'  class='btn btn-default' value='go'>";
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
    echo "<tr height=10 class=TabHeader>
            <td width=320 align=center>
            <a href=evenement_vehicule.php?order=evenement class=TabHeader>Evénement
          </td>
            <td width=400 align=center>
            <a href=evenement_vehicule.php?order=vehicule class=TabHeader>Véhicule
          </td>
            <td width=25 ></td>
            <td width=15 ></td>
            <td width=230 align=left><a href=evenement_vehicule.php?order=dtdb class=TabHeader>Date
          </td>
            <td width=80 align=center >Horaire</td>
            <td width=50 align=center >Km</td>
      </tr>
      ";

    $i=0;
    $k=0;
    while (custom_fetch_array($result)) {
        if ( $EH_ID <> 1 and $EV_KM <> "") $EV_KM="-";
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
            $ladate=$ladate." au ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1;
        }
        else $ladate=$ladate." ".$year1;
      
        $removelink="";
        if (( check_rights($_SESSION['id'], 15)) and ( is_children($S_ID,$mysection))) {
          $removelink="<a href=evenement_vehicule_add.php?evenement=".$E_CODE."&action=remove&V_ID=".$V_ID."&from=vehicule&dtdb=$dtdb&dtfn=$dtfn&order=$order&vehicule=$vehicule>
                    <i class='fa fa-trash' title='désengager ce véhicule' ></i></a>";
        }

        $sectioninfo="(".$S_DESCRIPTION.")";
        if ( $E_CODE <> $k ) {
            $evenementinfo="<td><table class='noBorder'><tr>
              <td><a href=evenement_display.php?evenement=".$E_CODE."&from=vehicule>
               <img src=images/evenements/".$TE_ICON." class='img-max-20'>
             </td>
             <td><a href=evenement_display.php?evenement=".$E_CODE."&from=vehicule>
                 <font size=1>".$E_LIBELLE."</a></font> ".$myimg."
             </td>
             </tr></table></td>";
            $k = $E_CODE;    
        }
        else $evenementinfo="<td ></td>";
          
        echo "<tr bgcolor=$mycolor >";
        echo $evenementinfo;

        if ( $VP_OPERATIONNEL == -1 ) $mytxtcolor="black";
        else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;      
        else if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) {
            $mytxtcolor=$red;
            $VP_LIBELLE = "assurance périmée";
        }
        else if ( my_date_diff(getnow(),$V_CT_DATE) < 0 ) {
            $mytxtcolor=$red;
            $VP_LIBELLE = "CT périmé";      
        }
        else if ( $VP_OPERATIONNEL == 2) {
            $mytxtcolor=$orange;
        }
        else if (( my_date_diff(getnow(),$V_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
            $mytxtcolor=$orange;
            $VP_LIBELLE = "révision à faire";
        }  
        else $mytxtcolor=$green;


        $nb = get_nb_engagements('V', $V_ID, $year1, $month1, $day1, $year2, $month2, $day2, $E_CODE);
        //$nb=1;
        if ( $nb > 1 ) 
               $myimg="<i class='fa fa-circle' style='color:red;' title='attention ce véhicule est parallèlement engagé sur $nb autres événements'></i>";
        else if ( $nb == 1 )
              $myimg="<i class='fa fa-circle' style='color:orange;' title='attention ce véhicule est parallèlement engagé sur 1 autre événement'></i>";
        else $myimg="";
     
        echo "<td>
              <a href=upd_vehicule.php?vid=".$V_ID.">
            <b> ".$TV_CODE."</b> - <font size=1>".$V_MODELE." - ".$V_IMMATRICULATION."</a> ".$sectioninfo."</font><font color=$mytxtcolor size=1> $VP_LIBELLE</font></td>
            <td>".$myimg."</td>
            <td>".$removelink."</td>
            <td align=left>
                <font size=1>".$ladate."</font></td>
            <td align=center>
                <font size=1>".$EH_DEBUT."-".$EH_FIN."</font></td>
          <td align=center>
                <font fsize=1>".$EV_KM."</font></td>
         </tr>";
    }
}
else {
    echo "<p><b>Aucun engagement ne correspond aux critères choisis</b>";
}

echo "</table></div>";
writefoot();
?>
