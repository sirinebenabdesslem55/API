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
check_all(0);
$id=$_SESSION['id'];
writehead();

if (isset($_GET['pompier']) and check_rights($id,56)) $pompier=intval($_GET['pompier']);
else $pompier=$_SESSION['id'];
if ( ! check_rights($id, 40 ) and $pompier <> $id) {
    $section=get_section_of($pompier);
    if ( ! check_rights($id, 56, $section )) $pompier=$id;
}
if ( $pompier == 0 ) $pompier=$id;

//============================================================
// les inscriptions 
// ===========================================================
$block1="";

$query="select te.TE_LIBELLE,e.E_CODE,e.TE_CODE,te.TE_ICON,e.E_LIBELLE,e.E_LIEU, s.S_CODE,s.S_DESCRIPTION,
        TIME_FORMAT(eh.EH_DEBUT, '%T') as EH_DEBUT, TIME_FORMAT(eh.EH_FIN, '%T') as EH_FIN,
        DATE_FORMAT(eh.EH_DATE_DEBUT, '%Y-%m-%d') as EH_DATE_DEBUT,
        DATE_FORMAT(eh.EH_DATE_FIN, '%Y-%m-%d') as EH_DATE_FIN,
        E_CANCELED, E_CLOSED,
        TIME_FORMAT(ep.EP_DEBUT, '%T') as EP_DEBUT, TIME_FORMAT(ep.EP_FIN, '%T') as EP_FIN,
        DATE_FORMAT(ep.EP_DATE_DEBUT, '%Y-%m-%d') as EP_DATE_DEBUT,
        DATE_FORMAT(ep.EP_DATE_FIN, '%Y-%m-%d') as EP_DATE_FIN,
        eq.EQ_ID, eq.EQ_NOM, eq.EQ_ICON, ep.EP_ASTREINTE,ep.EP_ABSENT
        from evenement e left join type_garde eq on eq.EQ_ID = e.E_EQUIPE,
        type_evenement te, section s, evenement_participation ep, evenement_horaire eh
        where e.TE_CODE=te.TE_CODE
        and eh.E_CODE = ep.E_CODE
        and eh.EH_ID = ep.EH_ID
        and e.TE_CODE <> 'MC'
        and e.E_CANCELED <> 1
        and ep.P_ID=".$pompier."
        and ep.E_CODE=e.E_CODE
        and e.S_ID=s.S_ID";
 
if ( (! check_rights($id,9) and $id <> $pompier ) or $gardes == 1 )
$query .= " and e.E_VISIBLE_INSIDE=1";
$query .= " order by EH_DATE_DEBUT asc";
$result=mysqli_query($dbc,$query);

while (custom_fetch_array($result)) {
    $EQ_ID=intval($EQ_ID);
    $E_LIEU=str_replace("'"," ",$E_LIEU);
    if ( $E_LIEU == '' ) $E_LIEU = '?';
    $TE_LIBELLE=str_replace("'","",$TE_LIBELLE);
    $E_LIBELLE=str_replace("'","",$E_LIBELLE);
    
    if ( $EQ_ICON == '' ) $img="../images/evenements/".$TE_ICON;
    else $img="../".$EQ_ICON;
    
    if ( $EP_ASTREINTE == 1 ) $img2="<td><i class=\'fa fa-exclamation-triangle\' style=\'color:orange;\' title=\'Astreinte (garde non remuneree)\'></i></td>";
    else $img2="";

    $S_DESCRIPTION=str_replace("'","",$S_DESCRIPTION);
      
    if ( $EP_DATE_DEBUT <> "" ) {
        $EH_DEBUT=$EP_DEBUT;
        $EH_FIN=$EP_FIN;
        $EH_DATE_DEBUT=$EP_DATE_DEBUT;
        $EH_DATE_FIN=$EP_DATE_FIN;
    }
 
    if ($EH_DATE_FIN == '' ) $EH_DATE_DEBUT;

    if ( $TE_CODE == 'GAR' ) {
        $theinfo = '';
        if ( $EP_ABSENT == 1 ) $color = 'grey';
        else if ( $EQ_ID == 1 ) $color = 'purple';
        else if ( $EQ_ID == 2 ) $color = 'green';
        else $color = 'blue';
    }
    else {
        $theinfo= $TE_LIBELLE;
        if ( $EP_ABSENT == 1 ) $color = 'grey';
        else if ( $TE_CODE == 'FOR' ) $color = 'blue';
        else if ( $TE_CODE == 'DPS' ) $color = '#ff6600';
        else if ( $TE_CODE == 'REU' ) $color = 'green';
        else if ( $TE_CODE == 'ALERT' ) $color = 'red';
        else if ( $TE_CODE == 'MLA' ) $color = 'purple';
        else if ( $TE_CODE == 'CER' ) $color = '#ff4dff';
        else if ( $TE_CODE == 'TEC' ) $color = 'black';
        else $color=$mydarkcolor;
    }

    $url = "evenement_display.php?evenement=".$E_CODE."&tab=2";
    if ( $EP_ABSENT == 1 ) {
        $theinfo = "ABSENT -".$theinfo;
        $url .= "&evenement_show_absents=1";
    }
    
    $title=$theinfo." ".$E_LIBELLE." - lieu ".$E_LIEU." - de ".substr($EH_DEBUT,0,5)." à ".substr($EH_FIN,0,5);
    if ( $nbsections == 0 ) $title .= " organisé par ".$S_DESCRIPTION;
    $title=fixcharset($title);
    
    
    $block1 .= "
    {
        title: \"".$title."\",
        start: '".$EH_DATE_DEBUT."T".$EH_DEBUT."',
        end: '".$EH_DATE_FIN."T".$EH_FIN."',
        url: '".$url."',
        color: '".$color."'
    },";
}

//============================================================
// les astreintes 
//============================================================
$block2="";
$query="select a.AS_ID, a.S_ID, a.GP_ID, a.P_ID, g.GP_DESCRIPTION,
    DATE_FORMAT(a.AS_DEBUT, '%Y-%m-%d') as DEBUT,
    DATE_FORMAT(a.AS_FIN, '%Y-%m-%d') as FIN, s.S_CODE
    from astreinte a, section s, pompier p, groupe g
    where a.S_ID = s.S_ID
    and a.P_ID=p.P_ID
    and a.GP_ID=g.GP_ID
    and p.P_ID=".$pompier;
$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    $TYPE=fixcharset($GP_DESCRIPTION);
    $block2 .= "
    {
        title: '".$TYPE."',
        start: '".$DEBUT."',
        end: '".$FIN."',
        url: 'astreinte_edit.php?from=calendar&astreinte=".$AS_ID."',
        color: 'orange'
    },";
}

//============================================================
// les absences 
//============================================================

$block3="";
$query="select i.I_CODE, ti.TI_LIBELLE as TYPE, DATE_FORMAT(i.I_DEBUT, '%Y-%m-%d') as DEBUT, DATE_FORMAT(i.I_FIN, '%Y-%m-%d') as FIN
        from  indisponibilite i, type_indisponibilite ti
        where i.P_ID=".$pompier."
        and i.TI_CODE=ti.TI_CODE";
$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result) ) {
    $TYPE=fixcharset($TYPE);
    $block3 .= "
    {
        title: 'Absence ".$TYPE."',
        start: '".$DEBUT."',
        end: '".$FIN." 20:00',
        url: 'indispo_display.php?code=".$I_CODE."&from=calendar',
        color: 'grey',
    },";
}

//============================================================
// les heures travaillées 
//============================================================

$block4="";
$query="select H_DATE, H_DEBUT1, H_DEBUT2, H_FIN1,H_FIN2 from horaires
        where P_ID=".$pompier."
        order by H_DATE,H_DEBUT1";
$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result) ) {
    if ( $H_FIN1 <> '' ) 
        $block4 .= "
        {
            title: 'Pointage matin',
            start: '".$H_DATE."T".$H_DEBUT1."',
            end: '".$H_DATE."T".$H_FIN1."',
            url: 'horaires.php?from=calendar&person=".$pompier."&view=list',
            color: '#80bfff',
        },";
    if ( $H_FIN2 <> '' ) 
        $block4 .= "
        {
            title: 'Pointage après-midi',
            start: '".$H_DATE."T".$H_DEBUT2."',
            end: '".$H_DATE."T".$H_FIN2."',
            url: 'horaires.php?from=calendar&person=".$pompier."&view=list',
            color: '#80bfff',
        },";
}

//============================================================
// les jours fériés années N et N+1
//============================================================
$block5="";
$date= mktime(0,0,0,1,1, date("Y"));
$i=0;
while ( $i < 730 ) {
    if (dateCheckPublicholiday($date)) {
        $block5 .= "
        {
            title: 'jour férié',
            start: '".date("Y-m-d", $date)."',
            color: 'orange',
            rendering: 'background'
        },";
    }
    $i++;
    $date = dateAddDay($date,1);
}

?>

<link rel='stylesheet' href='js/fullcalendar/fullcalendar.css' />
<link rel='stylesheet' href='css/main.css' />
<link rel='stylesheet' href='css/calendar.css' />
<script src='js/fullcalendar/lib/moment.min.js'></script>
<script src='js/fullcalendar/fullcalendar.js'></script>
<script src='js/fullcalendar/locale/fr.js'></script>
<script type=text/javascript>
$(document).ready(function() {

    // page is now ready, initialize the calendar...

    $('#calendar').fullCalendar({
        header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            //defaultDate: '2017-12-05',
            weekNumbers: true,
            weekNumbersWithinDays : true,
            firstDay: 1,
            aspectRatio: 1,
            contentHeight: 550,
            navLinks: true, // can click day/week names to navigate views
            editable: false,
            showNonCurrentDates: false,
            //businessHours: true, // display business hours
            eventLimit: true, // allow "more" link when too many events
            events: [
                <?php 
                    print $block1;
                    print $block2;
                    print $block3;
                    print $block4;
                    print $block5;
                ?>
            ]
        });
        
    });
</script>
</head>
<?php
echo "<body class=top50>
    <table class='noBorder'>
      <tr><td >
      <font size=4> <b>Calendrier de ";
    echo ucfirst(get_prenom($pompier))." ".strtoupper(get_nom($pompier))."</b></font> 
        <a href=upd_personnel.php?pompier=".$pompier." title='Fiche personnelle'><i class ='fa fa-user fa-2x'></i></a>
        <a href=evenement_ical.php?pid=".$pompier."><i class='fa fa-file-download fa-2x' title=\"Télécharger le fichier ical correspondant aux inscriptions\" ></i> </a>
        </td></tr></table><p>
<div id='calendar'></div>";

writefoot();
?>