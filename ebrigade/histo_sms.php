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
check_all(23);
writehead();
forceReloadJS('js/histo_sms.js');
echo "</head>";

if (isset($_GET['type'])) $type=$_GET['type']; //histo,compta
else $type='histo';
if (isset($_GET['sms_account'])) $sms_account=$_GET['sms_account'];
else $sms_account='ALL';
if ( $sms_account <> 'ALL' ) $sms_account=intval($sms_account);
if (isset($_GET['order'])) $order=secure_input($dbc,$_GET['order']);
else $order='s.S_DATE';

// get dat parameters, else use default dates
if (isset($_GET['dtdb'])) {
     $dtdb = secure_input($dbc,$_GET['dtdb']);    
}
else 
    $dtdb = date("d-m-Y",mktime(0,0,0,1,1,date("Y")));

if (isset($_GET['dtfn'])) {
     $dtfn = secure_input($dbc,$_GET['dtfn']);    
}
else {
     $d =  date ("d");
     $m =  date ("m");
     $y =  date ("Y");
     if ( $d < 29 ) $d = $d + 1;
     else if ( $m < 12 ) $m = $m + 1;
     else $y = $y + 1;
    $dtfn = date("d-m-Y",mktime(0,0,0,$m,$d,$y));
}
$tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
$tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

echo "<body>";

echo "<div align=center class='table-responsive'>
<table class='noBorder'>
<tr><td align=center><font size=4><b>SMS envoyés</b></font></td>";
echo "<tr></table>";

echo "<form name='formf' action='histo_sms.php'>";
echo "<table class='noBorder'><tr><td align=center>";

// Type de rapport
if ( $nbsections == 0 ) {
    echo "<tr><td>Type de rapport </td><td><select id='type' name='type'
    onchange=\"redirect(document.formf.type.options[document.formf.type.selectedIndex].value, '$sms_account',  '$dtdb', '$dtfn', '$order');\" >";
    if ( $type == 'histo' ) $selected='selected'; else $selected='';
    echo "<option value='histo' $selected>SMS envoyés (historique)</option>";
    if ( $type == 'compta' ) $selected='selected'; else $selected='';
    echo "<option value='compta' $selected>SMS envoyés par département</option>";
    echo "</select></td></tr>";
}
// choix du compte SMS
$query="select S_ID, S_CODE, SMS_LOCAL_PROVIDER, SMS_LOCAL_USER , SMS_LOCAL_PASSWORD, SMS_LOCAL_API_ID
        from section where S_ID in (select distinct S_ID from smslog) 
        union 
        select S_ID, S_CODE, SMS_LOCAL_PROVIDER, SMS_LOCAL_USER , SMS_LOCAL_PASSWORD, SMS_LOCAL_API_ID
        from section where SMS_LOCAL_PROVIDER > 0
        order by S_CODE";

$result=mysqli_query($dbc,$query);

echo "<tr><td>Compte SMS </td><td><select id='sms_account' name='sms_account'
onchange=\"redirect('$type', document.formf.sms_account.options[document.formf.sms_account.selectedIndex].value, '$dtdb', '$dtfn', '$order');\">";
echo "<option value='ALL'>Tous les comptes SMS</option>";
while ($row=@mysqli_fetch_array($result)) {
    $S_ID=$row["S_ID"];
    $S_CODE=$row["S_CODE"];
    if ( $sms_account == $S_ID ) $selected='selected';
    else $selected='';
    echo "<option value=".$S_ID." $selected>".$S_CODE."</option>";
}
echo "</select></td></tr>";


// Choix Dates
echo "<tr>
<td align=right >Début:</td>
<td align=left>
<input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            style='width:100px;'>";
echo "</td></tr>";


echo "<tr><td align=right >Fin :</td><td align=left>
<input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            style='width:100px;'>";

echo " <input type='submit' class='btn btn-default' value='go'>";
echo "</td></tr>";
echo "</table>";

// ===============================================
// compta
// ===============================================

if ( $type == 'compta' ) {
    $SID = array();
    $SCODE = array();
    $SDESCRIPTION = array();
    $SNB = array();
    $i=0; 
    $total=0;

    $query="select distinct S_ID, S_CODE, S_DESCRIPTION 
        from section_flat where NIV=3";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $S_ID=$row["S_ID"];
        $query2="select sum(sm.S_NB) as S_NB 
            from smslog sm, pompier p
            where p.P_ID = sm.P_ID
            and sm.S_DATE <= '$year2-$month2-$day2' 
            and sm.S_DATE   >= '$year1-$month1-$day1'
            and p.P_SECTION in (".get_family("$S_ID").")";
        if ( $sms_account <> 'ALL') $query2 .=" and sm.S_ID = ".$sms_account;
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        if ( $row2["S_NB"] > 0 && $row2["S_NB"] <> "" ) {
            $SID[$i]=$S_ID;
            $SCODE[$i]=$row["S_CODE"];
            $SDESCRIPTION[$i]=$row["S_DESCRIPTION"];
            $SNB[$i]=$row2["S_NB"];
            $total++;
            $i++;
        }
    
    }
    array_multisort($SNB, SORT_DESC,
                $SCODE,
                $SID,
                $SDESCRIPTION
    );

    if ( $total > 0 ) {
        echo "<p><table cellspacing=0 border=0>";

        echo "<tr>
            <td class=TabHeader align=center width=250>Section</font></td>
            <td class=TabHeader align=center width=150>Nombre SMS</font></td>
        </tr>
        ";

        for ($i=0; $i < $total; $i++) {
            if ( $i%2 == 0 ) {
                $mycolor="$mylightcolor";
            }
            else {
            $mycolor="#FFFFFF";
            }
      
            echo "<tr bgcolor=$mycolor >
            <td align=left>
                <a href=upd_section.php?S_ID=".$SID[$i].">".$SCODE[$i]." ".$SDESCRIPTION[$i]."</a></td>
            <td align=center><font size=1>".$SNB[$i]."</font></td>      
            </tr>"; 
        }
        echo "</table>";
        echo "</td></tr></table>";
    }
}
else {

// ===============================================
// historique
// ===============================================

$query="select p.P_ID, p.P_NOM, p.P_PRENOM, s.S_DATE, s.S_NB, s.S_TEXTE , se.S_CODE, sm.S_CODE SMS_SECTION_CODE , sm.SMS_LOCAL_PROVIDER, s.S_PROVIDER
         from pompier p, smslog s left join section sm on sm.S_ID = s.S_ID,
         section se
         where s.P_ID=p.P_ID
         and p.P_SECTION=se.S_ID
         and s.S_DATE <= '$year2-$month2-$day2' 
         and s.S_DATE   >= '$year1-$month1-$day1'";
if ( $sms_account <> 'ALL') $query .=" and s.S_ID = ".$sms_account;

$query .=" order by ".$order;
if ( $order == 's.S_NB' || $order == 's.S_DATE' ) $query .=" desc";

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

echo "<p><table cellspacing=0 border=0>";

echo "<tr class='TabHeader'>
            <td width=150 align=center>
                <a href=histo_sms.php?order=p.P_NOM&sms_account=".$sms_account."&type=histo&dtdb=".$dtdb."&dtfn=".$dtfn." class=TabHeader >
            Nom</a></font></td>
            <td width=100 class=TabHeader align=center>
            <a href=histo_sms.php?order=se.S_CODE&sms_account=".$sms_account."&type=histo&dtdb=".$dtdb."&dtfn=".$dtfn." class=TabHeader >
            Section</a></font></td>
            <td width=120 class=TabHeader align=center>
              <a href=histo_sms.php?order=s.S_DATE&sms_account=".$sms_account."&type=histo&dtdb=".$dtdb."&dtfn=".$dtfn." class=TabHeader >
              Date</a></font></td>
            <td width=20 class=TabHeader align=center>
            <a href=histo_sms.php?order=s.S_NB&sms_account=".$sms_account."&type=histo&dtdb=".$dtdb."&dtfn=".$dtfn." class=TabHeader >
            Nb</a></font></td>
            <td width=150 class=TabHeader align=center>
            <a href=histo_sms.php?order=sm.S_CODE&sms_account=".$sms_account."&type=histo&dtdb=".$dtdb."&dtfn=".$dtfn." class=TabHeader >
            Compte</a></font></td>
            <td width=550 class=TabHeader align=left>Texte du message</font></td>
      </tr>";

$i=0;
while ($row=@mysqli_fetch_array($result)) {
    $P_ID=$row["P_ID"];
    $P_NOM=$row["P_NOM"];
    $P_PRENOM=$row["P_PRENOM"];
    $S_DATE=$row["S_DATE"];
    $S_NB=$row["S_NB"];
    $S_TEXTE=$row["S_TEXTE"];
    $S_CODE=$row["S_CODE"];
    $SMS_SECTION_CODE=$row["SMS_SECTION_CODE"];
    $S_PROVIDER=$row["S_PROVIDER"];
  
    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor="$mylightcolor";
    }
    else {
        $mycolor="#FFFFFF";
    }
  
    echo "<tr bgcolor=$mycolor >
        <td align=center>
        <a href=upd_personnel.php?pompier=".$P_ID.">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</a></td>
        <td align=center><font size=1>".$S_CODE."</font></td>
        <td align=center><font size=1>".$S_DATE."</font></td>
        <td align=center><font size=1>".$S_NB."</font></td>
        <td align=center><font size=1>".$SMS_SECTION_CODE." - ".$S_PROVIDER."</font></td>
        <td width=450 align=left><font size=1>".$S_TEXTE."</font></td>      
    </tr>"; 
}
echo "</table>"; 
}

echo "<p><input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\"></div>";
writefoot();
?>
