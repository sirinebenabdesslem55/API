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
check_all(56);
$id=$_SESSION['id'];
get_session_parameters();
writehead();
test_permission_level(56);

$possibleorders= array('G_LEVEL','P_STATUT','P_NOM','P_SECTION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';


if ( $month > 12 ) {
    $month=date('n');
    $_SESSION['month'] = $month;
}

$moislettres=moislettres($month);
$nbjoursdumois=nbjoursdumois($month, $year);
$yearnext=date("Y") +1;
$yearcurrent=date("Y");
$yearprevious = date("Y") - 1;

function get_status($P_ID,$year,$month,$day) {
    global $gardes, $N, $G, $T, $D, $P, $Q, $A, $dbc, $type_evenement;
    // participations
    $status="";
    $style="";
    if ( isset($G[$day][$P_ID])) {
        $nb=$G[$day][$P_ID];
        $tot=$T[$day][$P_ID];
        $dis=$D[$day][$P_ID];
        $par=$P[$day][$P_ID];
        $eq=$Q[$day][$P_ID];
        
        if ( $nb == 2 ) {
            if ( $dis == 1 ) {
                $status=$tot;
                $style='garde1';
                $title="Garde ".$tot." heures";
            }
            else {
                $status=$tot;
                $style='garde2';
                $title=" Plusieurs activités différéntes pour un total de Garde de ".$tot." heures";
            }
        }
        else if ( $nb == 1 ) {
            if ( $par == 1 ) {
                $status=$tot.'J';
                $style='garde1j';
                $title="Garde ".$tot." heures Jour";
                if ( $eq == 1 )  $style='garde1j';
                else if ( $eq == 2 )  $style='garde2';
                else $style='garde3';
            }
            else {
                $status=$tot.'N';
                $title="Garde ".$tot." heures Nuit";
                if ( $eq == 1 )  $style='garde1j';
                else if ( $eq == 2 )  $style='garde2';
                else $style='garde3';
            }
        }
    }
    else if (isset($N[$day][$P_ID])) {
        $nb=$N[$day][$P_ID];
        $status=$nb;
        $title="Participation à ".$nb." événements";
        $style='participe';
        if (strlen($month) == 1 ) $month='0'.$month;
        if (strlen($day) == 1 ) $day='0'.$day;
        $url="participations_modal.php?pid=".$P_ID."&date=".$year."-".$month."-".$day."&type_evenement=".$type_evenement;
        $status = write_modal( $url, $P_ID, $status);
    }
    else if (isset ($A[$day][$P_ID])) {
        // indisponibilites           
        $status=$A[$day][$P_ID];
        $title='Absence '.$P[$day][$P_ID];
        $style='indispo';        
        }
    else if ( $status == "" ) {
            // disponibilites
            $query="select sum( d.PERIOD_ID * d.PERIOD_ID ) as DISPO, sum(dp.DP_DUREE) as DUREE
                from disponibilite d, disponibilite_periode dp
                where d.P_ID =".$P_ID."
                and dp.DP_ID = d.PERIOD_ID
                and d.D_DATE = '".$year."-".$month."-".$day."'";
            $result=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($result);
            $DISPO=$row["DISPO"];
            $DUREE=$row["DUREE"];
            if ( $DISPO == 0 ) {
                $status='';
                $title='';
                $style='none';
            }
            else if ( $DISPO == 30 ) {
                $status='24';
                $title='Disponible 24h';
                $style='dispo';
            }
            else if ( $DISPO == 5 ) {
                $status='J';
                $title='Disponible le jour '.$DUREE.'h';
                $style='dispo';
            }
            else if ( $DISPO == 25 ) {
                $status='N';
                $title='Disponible la nuit '.$DUREE.'h';
                $style='dispo';
            }
            else {
                $status=$DUREE;
                $title='Disponible partiellement, pendant '.$DUREE.'h';
                $style='dispo';
            }
        }
    
    $array = array();
    $array[0]=$status;
    $array[1]=$title;
    $array[2]=$style;
    return $array;
}


?>
<STYLE type="text/css">
.participe{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:8pt; font-weight:bold;}
.garde1{color:white;background-color:purple;font-size:8pt; font-weight:bold;}
.garde1j{color:white;background-color:blue;font-size:8pt; font-weight:bold;}
.garde1n{color:white;background-color:brown;font-size:8pt; font-weight:bold;}
.garde2{color:white;background-color:green;font-size:8pt; font-weight:bold;}
.garde3{color:white;background-color:orange;font-size:8pt; font-weight:bold;}
.dispo{color:green; font-size:8pt;}
.dispoweekend{color:green; background-color:yellow;font-size:8pt;}
.indispo{color:black;background-color:lightgrey; font-size:8pt;font-style:italic;}
.weekend{background-color:<?php echo $yellow; ?>;}
.planning-row { border-bottom: solid 1px ; }
tr.border_bottom td { border-bottom:1px solid <?php echo $mydarkcolor; ?>; }
td.borderleft { border-left:1px solid <?php echo $mydarkcolor; ?>; min-width:20px}
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/planning.js?version=5'></script>
<?php
echo "</head>";
echo "<body>";

$querycnt="select count(1) as NB";

$query1="select distinct p.P_ID, p.P_NOM , p.P_PRENOM, p.P_SEXE, p.P_GRADE, p.P_STATUT, p.P_SECTION, s.S_CODE ";

$queryadd1=" from pompier p, grade g , section s
     where p.P_GRADE=g.G_GRADE
     and p.P_SECTION=s.S_ID
     and p.P_NOM <> 'admin' 
     and p.P_OLD_MEMBER = 0 
     and p.P_STATUT <> 'EXT'";

$queryadd2="";
if ( $subsections == 1 ) {
      $queryadd2 = " and p.P_SECTION in (".get_family("$filter").")";
}
else {
      $queryadd2 = " and p.P_SECTION =".$filter;
}

if ( $day_planning > 0 ) {
    $queryadd2 .= " and exists (select 1 from evenement e, type_evenement te, evenement_participation ep, evenement_horaire eh
                                where ep.P_ID = p.P_ID
                                and eh.E_CODE = ep.E_CODE
                                and eh.EH_ID = ep.EH_ID
                                and e.E_CODE = eh.E_CODE
                                and e.E_CODE = ep.E_CODE
                                and te.TE_CODE = e.TE_CODE
                                and ep.E_CODE = e.E_CODE
                                and ep.EP_ABSENT=0
                                and e.TE_CODE <> 'MC'";
    if ( $type_evenement <> 'ALL' ) 
            $queryadd2 .= " and (te.TE_CODE = '".$type_evenement."' or te.CEV_CODE = '".$type_evenement."')";
    $queryadd2 .=" and eh.EH_DATE_DEBUT <= '".$year."-".$month."-".$day_planning."' 
                and eh.EH_DATE_FIN >= '".$year."-".$month."-".$day_planning."')";
}

$querycnt .= $queryadd1.$queryadd2;
$query1 .= $queryadd1.$queryadd2." order by ". $order;
if ( $order == "G_LEVEL" )  $query1 .=" desc";

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

echo "<div align=center><font size=4><b>Planning du personnel $moislettres $year</b></font> <span class='badge'>$number</span> personnes";
// choix mois année

echo "<form>";
echo "<table  class='noBorder'><tr height=40><td>";
echo "année 
<select name='menu1' onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2)\">";
if ($year > $yearprevious) echo "<option value='$yearprevious'>".$yearprevious."</option>";
else echo "<option value='$yearprevious' selected>".$yearprevious."</option>";
if ($year <> $yearcurrent) echo "<option value='$yearcurrent' >".$yearcurrent."</option>";
else echo "<option value='$yearcurrent' selected>".$yearcurrent."</option>";
if ($year < $yearnext)  echo "<option value='$yearnext' >".$yearnext."</option>";
else echo "<option value='$yearnext' selected>".$yearnext."</option>";
echo  "</select></td>";

echo "<td>mois <select name='menu2' onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2)\">";
$m=1;
while ($m <=12) {
      $monmois = $mois[$m - 1 ];
      if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
      else echo  "<option value= $m >".$monmois."</option>\n";
      $m=$m+1;
}
echo  "</select>";
echo "</td></tr></table></form>";

echo "<table  class='noBorder'>";


// type activité
echo "<tr height=40>";
echo "<td align=right> Activité </td>";
echo "<td align=left colspan=2><select id='type' name='type' 
     onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$subsections."',document.getElementById('type').value,document.getElementById('day_planning').value)\">";

if ( $type_evenement == 'ALL' ) $selected = 'selected';
else $selected = '';

echo "<option value='ALL' $selected>Toutes activités </option>\n";
if ( $gardes == 1 ) {
    if ( $type_evenement == 'ALLBUTGARDE' ) $selected = 'selected';
    else $selected = '';
    echo "<option value='ALLBUTGARDE' $selected>Toutes activités sauf gardes</option>\n";
}
$query2="select distinct te.CEV_CODE, ce.CEV_DESCRIPTION, te.TE_CODE, te.TE_LIBELLE
    from type_evenement te, categorie_evenement ce
    where te.CEV_CODE=ce.CEV_CODE
    and te.TE_CODE <> 'MC'
    order by te.CEV_CODE desc, te.TE_LIBELLE asc";
$result2=mysqli_query($dbc,$query2);
$prevCat='';
while (custom_fetch_array($result2)) {
    if ( $prevCat <> $CEV_CODE ){
        echo "<option class='categorie' value='".$CEV_CODE."' label='".$CEV_DESCRIPTION."'";
        if ($CEV_CODE == $type_evenement ) echo " selected ";
        echo ">".$CEV_DESCRIPTION."</option>\n";
    }
    $prevCat=$CEV_CODE;
    echo "<option class='type' value='".$TE_CODE."' title=\"".$TE_LIBELLE."\"";
    if ($TE_CODE == $type_evenement ) echo " selected ";
    echo ">".$TE_LIBELLE."</option>\n";
}
echo "</select></td></tr>";
// section
echo "<tr >";
echo "<td>".choice_section_order('planning.php')."</td>";
echo "<td><select id='filter' name='filter' title='filtre par section'
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$subsections."','".$type_evenement."',document.getElementById('day_planning').value)\">";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";
    
echo "</td>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<td align=center width=120 ><input type='checkbox' name='sub' id='sub' $checked
       onClick=\"orderfilter2('".$order."',document.getElementById('filter').value, this,'".$type_evenement."')\"/>
       <label for='sub'>inclure les<br>sous sections</label></td>";
}

// jour de participations

echo "<tr height=40>";
echo "<td>Filtre jour</td>";
echo "<td><select id='day_planning' name='day_planning' 
        title='filtre par jour, seules les personnes ayant une participation ce jour sont affichées'
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$subsections."','".$type_evenement."',document.getElementById('day_planning').value);\">";
for ( $i = 0 ; $i <= $nbjoursdumois ; $i++ ) {
    if ( $i == $day_planning ) $selected = 'selected';
    else $selected = '';
    if ( $i == 0 ) $d = 'Pas de filtre';
    else $d = 'participe le '.$i;
    echo "<option value=".$i." $selected>".$d."</option>";
}
echo "</select>";


echo "</tr><tr><td colspan=3>";
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
$numberrows=mysqli_num_rows($result1);

// optimisation, mettre dans un tableau le nombre de participations par jour et par personne
$N = array();
$G = array();
$T = array();
$D = array();
$Q = array();
$A = array();
$P = array();
$lst="";

while ($row1=mysqli_fetch_array($result1)) {
         $lst .= $row1["P_ID"].",";
}
$lst .= '0';

$result1=mysqli_query($dbc,$query1);
for ( $i = 1; $i <= $nbjoursdumois; $i++ ) {
    if ( $gardes ) {
        // les gardes SP
        $query2="select ep.P_ID, count(1) as NB, sum(ep.EP_DUREE) as TOT, count(distinct ep.E_CODE) DIS, sum(ep.EH_ID) PAR, sum(e.E_EQUIPE) EQ
              from evenement_horaire eh, evenement_participation ep , evenement e
            where ep.P_ID in (".$lst.")
            and e.E_CODE = ep.E_CODE
            and eh.E_CODE= ep.E_CODE
            and eh.E_CODE= ep.E_CODE
            and eh.EH_ID = ep.EH_ID
            and eh.EH_DATE_DEBUT = '".$year."-".$month."-".$i."'
            and ep.EP_ABSENT = 0
            and e.TE_CODE = 'GAR'" ;
        if (! check_rights($id,6)) {
            $query2.= " and e.E_VISIBLE_INSIDE=1 ";
        }
        $query2.=" group by ep.P_ID";
        $result2=mysqli_query($dbc,$query2);
        while ($row2=@mysqli_fetch_array($result2)) {
            $G[$i][$row2["P_ID"]]=$row2["NB"];
            $T[$i][$row2["P_ID"]]=$row2["TOT"];
            $D[$i][$row2["P_ID"]]=$row2["DIS"];
            $P[$i][$row2["P_ID"]]=$row2["PAR"];
            $Q[$i][$row2["P_ID"]]=$row2["EQ"]; 
        }
        //if ( $i == 9 ) echo $query2;
    }
    // autres que gardes SP
    $query2="select ep.P_ID, count(1) as NB
            from evenement_horaire eh, evenement_participation ep , evenement e, type_evenement te
            where ep.P_ID in (".$lst.")
            and e.E_CODE = eh.E_CODE
            and e.E_CODE = ep.E_CODE
            and eh.E_CODE= ep.E_CODE
            and eh.EH_ID = ep.EH_ID
            and ep.EP_ABSENT = 0
            and e.TE_CODE = te.TE_CODE
            and e.TE_CODE <> 'MC' ";
    if ( $gardes ) 
        $query2 .=" and e.TE_CODE <> 'GAR'";
    if ( $type_evenement <> 'ALL' ) 
        $query2 .= " and (te.TE_CODE = '".$type_evenement."' or te.CEV_CODE = '".$type_evenement."')";
    $query2 .=" and eh.EH_DATE_DEBUT <= '".$year."-".$month."-".$i."' 
                and eh.EH_DATE_FIN >= '".$year."-".$month."-".$i."'";
    $query2 .=" group by ep.P_ID";
    $result2=mysqli_query($dbc,$query2);
    while ($row2=@mysqli_fetch_array($result2)) {
         $N[$i][$row2["P_ID"]]=$row2["NB"];
    }
   $query2="select i.TI_CODE, ti.TI_LIBELLE, i.P_ID
            from indisponibilite i, type_indisponibilite ti
           where i.P_ID in (".$lst.")
           and i.TI_CODE = ti.TI_CODE
           and i.I_STATUS='VAL'
           and i.I_DEBUT <='".$year."-".$month."-".$i."'
           and i.I_FIN >='".$year."-".$month."-".$i."'";
    $result2=@mysqli_query($dbc,$query2);
    while ($row2=@mysqli_fetch_array($result2)) {
    $A[$i][$row2["P_ID"]]=$row2["TI_CODE"];
    $P[$i][$row2["P_ID"]]=$row2["TI_LIBELLE"];
    } 
    
}

// affichage
$result1=mysqli_query($dbc,$query1);
echo "</td></tr></table>";

echo "<table cellspacing=0 >";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class=TabHeader>";
if ( $grades == 1 ) {
    echo "<td width=40 align=center ><a href=planning.php?order=G_LEVEL class=TabHeader>Grade</a></td>";
}
echo "      <td align=center>
            <a href=planning.php?order=P_NOM class=TabHeader>Nom</td>";

echo "<td class=TabHeader align=center width=120><a href=planning.php?order=P_SECTION class=TabHeader>Section</a></td>";

for ( $i = 1 ; $i <= $nbjoursdumois ; $i++ ) {
    $jj=date("w", mktime(0, 0, 0, $month, $i, $year));
    if ( $jj == 0 or $jj == 6 ) $color=$yellow;
    else $color=$white;
    if ( $day_planning == $i ) $d="<span class='badge badge-pill badge3' title='Seules les personnes ayant une activité ce jour sont prises en compte, cliquer pour désactiver ce filtre'>
                    <a href=planning.php?day_planning=0 style='color:white;'>".$i."</a></span>";
    else $d="<a title='cliquer ici pour filtrer sur les personnes participant à une activité ce jour' href=planning.php?day_planning=".$i." style='color:white;'>".$i."</a>";
    echo "    <td class=tabheader align=center width=25><font color=".$color.">".$d."</td>";
}

echo " </tr>";


// ===============================================
// le corps du tableau
// ===============================================
$r=0;
$nbr=mysqli_num_rows($result1);
while (custom_fetch_array($result1)) {
    $r++;

    if ( $P_SEXE == 'F' ) $prcolor='purple';
    else $prcolor=$mydarkcolor;
    
    if ( $r < $nbr )  $class='border_bottom';
    else $class='';
    
    echo "<tr bgcolor='white' class=".$class."
          onMouseover=\"this.bgColor='yellow'\"
          onMouseout=\"this.bgColor='white'\" >";

    if ( $grades == 1 ) {
        echo "      <td align=center>$P_GRADE</td>";
    }
    echo "    <td nowrap><small><span color=$prcolor><a href=upd_personnel.php?pompier=".$P_ID.">".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a></span></small></td>";

    echo "<td align=center class='borderleft' nowrap><small>".$S_CODE."</small></td>";
    
    for ( $i = 1; $i <= $nbjoursdumois; $i++ ) {
        list ($status, $title, $style ) = get_status($P_ID,$year,$month,$i);
        if ( $style=='none' || $style=='dispo'){
            $jj=date("w", mktime(0, 0, 0, $month, $i, $year));
             if ( $jj == 0 or $jj == 6 ) {
                if ( $style=='none' ) $style='weekend';
                else if ( $style=='dispo' ) $style='dispoweekend';
            }
        }
        
        echo "<td 
                align=center
                title=\"".$title."\" 
                class='".$style." borderleft'>
                <small>".$status."</small>
              </td>";
    }
    
}
echo "</table>";
writefoot();
//show_total_time();
?>
