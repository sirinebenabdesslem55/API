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
check_all(22);
$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];

if (isset($_GET["section"])) $section=intval($_GET["section"]);
else if (isset($_POST["section"])) $section=intval($_POST["section"]);
else $section=$mysection;

if (isset($_GET["sseid"])) $sseid=intval($_GET["sseid"]);
else if (isset($_POST["sseid"])) $sseid=intval($_POST["sseid"]);
else $sseid=0;

if ( ! check_rights($id, 22, $section )) check_all(24);

$nomenu=1;
writehead();


//=====================================================================
// sauver nouvelle interdiction
//=====================================================================
if (isset($_POST["section"])) {
    $action=secure_input($dbc,$_POST["action"]);
    if ( $action == "save" ) {
        $start=secure_input($dbc,$_POST["start"]);
        $tmp=explode ( "-",$start); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
        $start="'".$year.'-'.$month.'-'.$day."'";
        $end=secure_input($dbc,$_POST["end"]);
        $tmp=explode ( "-",$end); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
        $end="'".$year.'-'.$month.'-'.$day."'";
        $type=secure_input($dbc,$_POST["type"]);
        $comment=secure_input($dbc,$_POST["comment"]);
        $comment=STR_replace("\"","",$comment);
        if (isset($_POST["active"])) $active=1;
        else $active=0;
        if ( $sseid == 0 )
            $query="insert into section_stop_evenement(S_ID,TE_CODE,START_DATE,END_DATE,SSE_COMMENT, SSE_ACTIVE, SSE_BY, SSE_WHEN)
                values (".$section.",'".$type."',".$start.",".$end.",\"".$comment."\", ".$active.", ".$id.", NOW())";
        else {
            $query="select S_ID from section_stop_evenement where SSE_ID=".$sseid;
            $result=mysqli_query($dbc,$query);
            custom_fetch_array($result);
            if ( ! check_rights($id, 22, $S_ID )) check_all(24);
            
            $query="update section_stop_evenement
                    set TE_CODE='".$type."',
                    START_DATE=".$start.",
                    END_DATE=".$end.",
                    SSE_COMMENT=\"".$comment."\",
                    SSE_ACTIVE= ".$active.",
                    SSE_BY=".$id.",
                    SSE_WHEN=NOW()
                    where SSE_ID=".$sseid;
        }
        $result=mysqli_query($dbc,$query);
    }
    echo "<body onload=\"javascript:self.location.href='upd_section.php?section=".$section."&tab=6'\"/>";
    exit;
}

if (isset($_GET["sseid"])) {
    $action=secure_input($dbc,$_GET["action"]);
    if ( $action == "delete" ) {
        $query="delete from section_stop_evenement where S_ID=".$section." and SSE_ID=".$sseid;
        $result=mysqli_query($dbc,$query);
        echo "<body onload=\"javascript:self.location.href='upd_section.php?section=".$section."&tab=6'\"/>";
        exit;
    }
}

//=====================================================================
// display modal
//=====================================================================

if ( $sseid == 0 ) $t='Ajouter';
else $t='';

write_modal_header($t." Interdiction pour ".get_section_code("$section"));

?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript'>
function change(control) {
    checkDate2(control);
    save=document.getElementById("sauver");
    start=document.getElementById("start");
    end=document.getElementById("end");
    if  ( start.value == '' || end.value == '') {
        save.disabled=true;
    }
    else {
        save.disabled=false;
    }
}
</script>
</head>
<?php

//=====================================================================
// choix type événement et période
//=====================================================================

echo "<body style='padding-top:10px'>";
echo "<div align=center>";

if ( $sseid == 0 ) {
    $START_DATE=''; 
    $END_DATE='';
    $SSE_COMMENT='';
    $SSE_ACTIVE=1;
    $TE_CODE='ALL';
    $SSE_BY ='';
}
else {
    $query="select s.SSE_ID, s.S_ID, s.TE_CODE, date_format(s.START_DATE,'%d-%m-%Y') START_DATE, date_format(s.END_DATE,'%d-%m-%Y') END_DATE,
            s.SSE_COMMENT, s.SSE_ACTIVE, 
            s.SSE_BY, date_format(s.SSE_WHEN,'%d-%m-%Y %H:%i') SSE_WHEN, p.P_NOM, p.P_PRENOM
            from section_stop_evenement s
            left join pompier p on p.P_ID = s.SSE_BY
            where s.SSE_ID=".$sseid;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    
    if ( ! check_rights($id, 22, $S_ID )) check_all(24);
}
echo "<p><form name='interdiction' action='section_stop.php' method='POST'>
    <input type='hidden' name='section' value='".$section."'>
    <input type='hidden' name='action' value='save'>
    <input type='hidden' name='sseid' value='".$sseid."'>
    <table cellspacing=0 border=0>";
echo "<tr>
           <td class=TabHeader colspan=2><b>Type d'événement et période à interdire</b></td>
      </tr>";
echo "<tr bgcolor=$mylightcolor align=right width=140><td>Type</td>";
echo "<td align=left width=260><select id='type' name='type'>";
echo "<option class='type' value='ALL' title=\"Bloquer tous les types d'événements\">Tous les types d'événements</option>\n";

$query="select distinct te.CEV_CODE, ce.CEV_DESCRIPTION, te.TE_CODE _TE_CODE, te.TE_LIBELLE
    from type_evenement te, categorie_evenement ce
    where te.CEV_CODE=ce.CEV_CODE";
$query .= " and TE_CODE <> 'INS' ";
$query .= " order by te.CEV_CODE desc, te.TE_LIBELLE asc";
$result=mysqli_query($dbc,$query);
$prevCat='';

while (custom_fetch_array($result)) {
    if ( $prevCat <> $CEV_CODE ){
        echo "<optgroup class='categorie' label='".$CEV_DESCRIPTION."'";
        echo ">".$CEV_DESCRIPTION."</option>\n";
    }
    $prevCat=$CEV_CODE;
    if ( $_TE_CODE == $TE_CODE ) $selected='selected';
    else $selected='';
    echo "<option class='type' value='".$_TE_CODE."' title=\"".$TE_LIBELLE."\" $selected>".$TE_LIBELLE."</option>\n";
}
echo "</select></td></tr>";

echo "<tr bgcolor=$mylightcolor align=right><td>Début</td>";
echo "<td align=left>
        <input type='text' name='start' id='start' size='13' value='".$START_DATE."'  onchange='change(this)' 
            placeholder='JJ-MM-AAAA' autocomplete='off'
            class='datepicker' data-provide='datepicker'>
        </td>
      </tr>";
      
echo "<tr bgcolor=$mylightcolor align=right><td>Fin</td>";
echo "<td align=left>
        <input type='text' name='end' id='end' size='13' value='".$END_DATE."' onchange='change(this)' 
            placeholder='JJ-MM-AAAA' autocomplete='off'
            class='datepicker' data-provide='datepicker'>
        </td>
      </tr>";
      
echo "<tr bgcolor=$mylightcolor align=right ><td>Commentaire</td>
        <td align=left><textarea name='comment' id='comment' cols='30' rows='3'>".$SSE_COMMENT."</textarea></td>
    </tr>";
    
if ( $SSE_ACTIVE == 1 ) $checked='checked';
else $checked='';

echo "<tr bgcolor=$mylightcolor align=right ><td></td>
        <td align=left><input type='checkbox' value='1' $checked id='active' name='active'> <label for='active'><b>interdiction active?</b></label></td>
    </tr>";
    
if ( $SSE_BY <> '' ) {
    echo "<tr bgcolor=$mylightcolor align=right ><td>Fait le</td>
        <td align=left><b>".$SSE_WHEN."</b></td>
    </tr>";
    echo "<tr bgcolor=$mylightcolor align=right ><td>Par</td>
        <td align=left><b><a href='upd_personnel.php?pompier=".$SSE_BY."'>".my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)."</a></b></td>
    </tr>";
}

echo "</table>";

if ( $sseid == 0 ) $disabled='disabled';
else $disabled='';
echo "<p><input type='submit' class='btn btn-default' value='sauver' id='sauver' $disabled></form>";
echo "</div>";

writefoot();
?>
