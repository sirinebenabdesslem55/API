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
check_all(49);
get_session_parameters();
writehead();
test_permission_level(49);

$possibleorders= array('LH_STAMP','LT_DESCRIPTION','P_ID','LH_COMPLEMENT','P_NOM','COMPLEMENT_CODE','P_NOM2');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='LH_STAMP';


?>
<script language="JavaScript">
function orderfilter(p1,p2,p3,p4){
    self.location.href="history.php?ltcode="+p1+"&lcid="+p2+"&order=LH_STAMP&filter="+p3+"&lccode="+p4;
    return true
}
</script>
<?php

echo "</head>";
echo "<body>";

if ( $lccode == 'A' ) {
    check_all(14);
    $title="activités suspectes";
    $icon="bomb";
    $lcid=0;
}
else {
    $title="modifications";
    $icon = "history";
}

if ( check_rights($_SESSION['id'], 25)) $granted_for_all=true;
else $granted_for_all=false;

// fiche personnel: history.php?lccode=P&lcid=$pompier&order=LH_STAMP&ltcode=ALL
// evenement: history.php?lccode=E&lcid=$evenement&order=LH_STAMP&ltcode=ALL
// section: history.php?lccode=S&lcid=$section&order=LH_STAMP&ltcode=ALL

$query ="select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM, e.E_CODE, e.E_LIBELLE, p2.P_NOM P_NOM2, p2.P_PRENOM P_PRENOM2, p2.P_SECTION
        from log_type lt, pompier p, log_history lh
        left join evenement e on ( e.E_CODE = lh.COMPLEMENT_CODE)
        left join pompier p2 on ( p2.P_ID = lh.LH_WHAT)
        where p.P_ID = lh.P_ID
        and lh.LT_CODE=lt.LT_CODE";
if ( $ltcode <> 'ALL' )
        $query .= " and lt.LT_CODE='".$ltcode."'";

$what="";$what2="";
if ( $lcid > 0) {
    if ( $lccode == 'P' ) {
        $_SESSION["lcid2"]=$lcid;
        $query .= " and lh.LH_WHAT='".$lcid."'";
        $query .= " and lt.LC_CODE='P'";
         $what="<br>pour ".my_ucfirst(get_prenom("$lcid"))." ".strtoupper(get_nom("$lcid"));
        if ( $granted_for_all )  $what2 ="<a href=history.php?ltcode=".$ltcode."&lccode=P&lcid=0 title='historique pour tout le personnel'>Voir tout</a>";
        
        $pos=get_position($lcid);
        if ( $pos > 0 ) $mylightcolor=$mygreycolor;
    }
    if ( $lccode == 'S' ) {
        $query ="select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM,s.S_CODE P_NOM2, '' P_PRENOM2, s.S_ID P_SECTION, '' E_CODE, '' E_LIBELLE
        from log_type lt, pompier p, log_history lh, section s
        where p.P_ID = lh.P_ID
        and s.S_ID = lh.LH_WHAT
        and lh.LT_CODE=lt.LT_CODE";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
        $_SESSION["lcid2"]=$lcid;
        $query .= " and lh.LH_WHAT='".$lcid."'";
        $query .= " and lt.LC_CODE='S'";
         $what="<br>pour ".get_section_code("$lcid");
        if ( $granted_for_all )  $what2 ="<a href=history.php?ltcode=".$ltcode."&lccode=S&lcid=0 title='historique pour toutes les sections'>Voir tout</a>";
        
    }
    if ( $lccode == 'E' ) {
        $_SESSION["lcid2"]=$lcid;
        $query .= " and lh.COMPLEMENT_CODE='".$lcid."'";
        $query .= " and lt.LC_CODE='P'";
        $query .= " union select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM, e.E_CODE, e.E_LIBELLE, '' P_NOM2, '' P_PRENOM2, '' P_SECTION
        from log_type lt, pompier p, log_history lh, evenement e
        where e.E_CODE = lh.LH_WHAT
        and p.P_ID = lh.P_ID
        and lh.LT_CODE=lt.LT_CODE
        and lh.LH_WHAT='".$lcid."'
        and lt.LC_CODE='E'";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
        $what="<br>pour l'événement n°". $lcid;
        if ( $granted_for_all )  $what2 ="<a href=history.php?ltcode=".$ltcode."&lccode=E&lcid=0 title='historique pour tous les événements'>Voir tout</a>";
    }
}
else { // $lcid=0
    if (! $granted_for_all) check_all(25);
    $query .= " and lt.LC_CODE='P'";
    if ( $filter > 0  and $lccode == 'P')
        $query .= " and p2.P_SECTION in (".get_family("$filter").")";    
    if ( $lccode == 'E' ) {
        if ( $filter > 0 ) $query .= " and e.S_ID in (".get_family("$filter").")";
        $query .= " union select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM, e.E_CODE, e.E_LIBELLE, '' P_NOM2, '' P_PRENOM2, '' P_SECTION
        from log_type lt, pompier p, log_history lh, evenement e
        where e.E_CODE = lh.LH_WHAT
        and p.P_ID = lh.P_ID
        and lh.LT_CODE=lt.LT_CODE
        and lt.LC_CODE='E'";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
        if ( $filter > 0 ) $query .= " and e.S_ID in (".get_family("$filter").")";
    }
    if ( $lccode == 'S' ) {
        $query ="select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM,s.S_CODE P_NOM2, '' P_PRENOM2, s.S_ID P_SECTION, '' E_CODE, '' E_LIBELLE
        from log_type lt, pompier p, log_history lh, section s
        where p.P_ID = lh.P_ID
        and s.S_ID = lh.LH_WHAT
        and lh.LT_CODE=lt.LT_CODE
        and lt.LC_CODE='S'";
        if ( $filter > 0 ) $query .= " and s.S_ID in (".get_family("$filter").")";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
    }
    if ( $lccode == 'A' ) {
        $query ="select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM, s.S_ID, s.S_CODE, '' E_CODE, '' E_LIBELLE, p.P_NOM P_NOM2, p.P_PRENOM P_PRENOM2
        from log_type lt, pompier p, log_history lh, section s
        where p.P_ID = lh.P_ID
        and p.P_SECTION =  s.S_ID
        and lh.LT_CODE=lt.LT_CODE
        and lt.LC_CODE='A'";
        if ( $filter > 0 ) $query .= " and s.S_ID in (".get_family("$filter").")";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
    }
    if ( $lccode == 'P' )
          $what="<br>pour tout le personnel";
    if ( $lccode == 'E' )
          $what="<br>pour tous les événements";
    if ( $lccode == 'S' )
          $what="<br>pour toutes les sections";
}
$query .= " order by ".$order ;


if ( $order == 'LH_STAMP' or $order == 'COMPLEMENT_CODE') $query .= " desc";

write_debugbox($query);

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

if ( $days_log > 0 and $lccode <> 'A') $c="sur les ".$days_log." derniers jours";
else $c="";


echo "<div align=center class='table-responsive'>
<table class='noBorder'><tr>
<td align=center>
<i class='fa fa-".$icon." fa-2x'></i>
</td>
<td>
<font size=4>Historique des $title ".$c." ".$what." <span class='badge'>$number</span> ".$what2."
</td></tr>
<tr>
<td>Type d'historique</td>
<td>";

//filtre LT_CODE
echo "<select id='ltcode' name='ltcode' 
    onchange=\"orderfilter(document.getElementById('ltcode').value,'$lcid','".$filter."','".$lccode."')\">
      <option value='ALL'>tous types</option>";

if ( $lccode == 'P' )  {
    $query2="select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB
         from log_type lt, log_history lh
         left join pompier p2 on ( p2.P_ID = lh.LH_WHAT)
         where lt.LT_CODE = lh.LT_CODE
         and lt.LC_CODE='P'";
    if ($lcid > 0) 
        $query2 .= " and lh.LH_WHAT = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and p2.P_SECTION in (".get_family("$filter").")";
    $query2 .=" group by lt.LT_CODE, lt.LT_DESCRIPTION
             order by lt.LT_DESCRIPTION";
}
else if ( $lccode == 'S' )  {
    $query2="select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB
         from log_type lt, log_history lh
         left join section s on ( s.S_ID = lh.LH_WHAT)
         where lt.LT_CODE = lh.LT_CODE
         and lt.LC_CODE='S'";
    if ($lcid > 0) 
        $query2 .= " and lh.LH_WHAT = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and s.S_ID in (".get_family("$filter").")";
    $query2 .=" group by lt.LT_CODE, lt.LT_DESCRIPTION
             order by lt.LT_DESCRIPTION";
}
else if ( $lccode == 'E' ) {
    $query2="select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB
         from log_type lt, log_history lh
         left join evenement e on (e.E_CODE = lh.COMPLEMENT_CODE)
         where lt.LT_CODE = lh.LT_CODE
         and lt.LC_CODE='P'";
    if ($lcid > 0) 
        $query2 .= " and lh.COMPLEMENT_CODE = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and e.S_ID in (".get_family("$filter").")";
    $query2 .=" group by LT_CODE, LT_DESCRIPTION";
    $query2 .=" union 
            select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB
            from log_type lt, log_history lh, evenement e 
            where e.E_CODE = lh.LH_WHAT
            and lt.LT_CODE = lh.LT_CODE
            and lt.LC_CODE='E'";
    if ($lcid > 0) 
        $query2 .= " and lh.LH_WHAT = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and e.S_ID in (".get_family("$filter").")";
    $query2 .=" group by LT_CODE, LT_DESCRIPTION
             order by LT_DESCRIPTION";
}
else if ( $lccode == 'A' ) {
    $query2="select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB
         from log_type lt, log_history lh, pompier p
         where lt.LT_CODE = lh.LT_CODE
         and p.P_ID = lh.P_ID
         and lt.LC_CODE='A'";
    if ($lcid > 0) 
        $query2 .= " and lh.COMPLEMENT_CODE = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and p.P_SECTION in (".get_family("$filter").")";
    $query2 .=" group by LT_CODE, LT_DESCRIPTION
             order by LT_DESCRIPTION";
}
$result2=mysqli_query($dbc,$query2);



while ($row=@mysqli_fetch_array($result2)) {
      $_LT_CODE=$row["LT_CODE"];
      $_LT_DESCRIPTION=$row["LT_DESCRIPTION"];
      $_NB=$row["NB"];
      echo "<option value='".$_LT_CODE."' title=\"".$_LT_DESCRIPTION."\"";
      if ($_LT_CODE == $ltcode ) echo " selected ";
      echo ">".$_LT_DESCRIPTION." (".$_NB.")</option>\n";
}
echo "</select></td></tr>";

if ( $lcid == 0 ) {
    echo "<tr><td>Section</td><td><select id='filter' name='filter' title='filtre par section'
        onchange=\"orderfilter('".$ltcode."','0',document.getElementById('filter').value,'".$lccode."');\">";
      display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";
    echo "</td></tr>";
}
echo "</table><p>";

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

echo "<p><table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class='TabHeader'>
          <td width=160 ><a href=history.php?order=LH_STAMP class=TabHeader>Date</a></td>
          <td width=150 class='hide_mobile'><a href=history.php?order=P_NOM class=TabHeader>Modifié par</a></td>
          <td width=240><a href=history.php?order=LT_DESCRIPTION class=TabHeader>Action</a></td>
          <td width=180 class='hide_mobile'><a href=history.php?order=P_NOM2 class=TabHeader>Pour</a></td>";
if ( $lccode <> 'S')
    echo "<td width=200 ><a href=history.php?order=COMPLEMENT_CODE class=TabHeader>Référence</a></td>";
echo "<td width=280 ><a href=history.php?order=LH_COMPLEMENT class=TabHeader>Complément</a></td>
      </tr>
      ";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result)) {
    $P_NOM=strtoupper($P_NOM);
    $P_PRENOM=my_ucfirst($P_PRENOM);
    $P_NOM2=strtoupper($P_NOM2);
    $P_PRENOM2=my_ucfirst($P_PRENOM2);
    if ( $E_LIBELLE <> "" ) {
        $COMPLEMENT = "<a href=evenement_display.php?evenement=$E_CODE&from=history title=\"".$LH_COMPLEMENT."\">".$E_LIBELLE."</a>";
    }
    else $COMPLEMENT="";
      
    $i=$i+1;
    if ( $i%2 == 0 ) {
       $mycolor="$mylightcolor";
    }
    else {
       $mycolor="#FFFFFF";
    }
    $LH_COMPLEMENT = str_replace ("->","<i class='fas fa-arrow-right'></i>", $LH_COMPLEMENT);
      
    echo "<tr bgcolor=$mycolor class=small2>";
    echo "<td align=left>".$DATE."</td>
          <td align=left class='hide_mobile'><a href=upd_personnel.php?pompier=".$P_ID.">".$P_PRENOM." ".$P_NOM."</a></td>
            <td align=left>".$LT_DESCRIPTION."</td>";
    if ( $lccode == 'S')
        echo "<td align=left class='hide_mobile'><a href=upd_section.php?S_ID=".$LH_WHAT.">".$P_NOM2."</a></td>";
    else
        echo "<td align=left class='hide_mobile'><a href=upd_personnel.php?pompier=".$LH_WHAT.">".$P_PRENOM2." ".$P_NOM2."</a></td>";
    if ( $lccode <> 'S')
        echo "<td align=left>".$COMPLEMENT."</td>";
    echo " <td align=left>".$LH_COMPLEMENT."</td>
      </tr>"; 
}
echo "</table>";  

if ( $lccode == 'P' ) {
    if ( $lcid == 0 ) echo "<p><p><input type=button class='btn btn-default' value='retour' onclick='javascript:history.back(1);'> ";
    else echo "<p><p><input type=button class='btn btn-default' value='retour' onclick='javascript:self.location.href=\"upd_personnel.php?pompier=".$_SESSION["lcid2"]."\";'> ";
}
if ( $lccode == 'E' )
    echo "<p><p><input type=button class='btn btn-default' value='retour' onclick='javascript:self.location.href=\"evenement_display.php?evenement=".$_SESSION["lcid2"]."\";'> ";
if ( $lccode == 'S' )
    echo "<p><p><input type=button class='btn btn-default' value='retour' onclick='javascript:self.location.href=\"upd_section.php?S_ID=".$_SESSION["lcid2"]."\";'> ";
    
    
echo "</div>";
writefoot();
?>
