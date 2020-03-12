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
writehead();

if ( isset($_GET["order"])) $order=secure_input($dbc,$_GET["order"]);
else $order='TF_ID';

if ( isset ($_GET["from"])) $from=$_GET["from"];
else $from ='default';

if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else $tab = 1;
if ( intval($tab) == 0 ) $tab = 1;

// type
if (isset ($_GET["domain"])) {
   $domain= $_GET["domain"];
   if ( $domain <> -1 ) $domain=intval($domain);
   $_SESSION['domain'] = $domain;
}
else if ( isset($_SESSION['domain']) ) {
   $domain=$_SESSION['domain'];
}
else $domain=-1;
if ( $domain >= 0 ) $order='TF_ID';

// 3 possible categories: 
// - droit d'accès habilitation (GP_ID < 100 TR_CONFIG=1)
// - role organigramme ( GP_ID >= 100 and TR_CONFIG=2)
// - permission  organigramme ( GP_ID >= 100 and TR_CONFIG=3)

$help = write_help_habilitations();

?>
<script type='text/javascript' src='js/habilitations.js?version=<?php echo $version; ?>'></script>
<script type='text/javascript'>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});
</script>
</head>
<body>
<?php

echo "<body>";

echo "<div align=center class='table-responsive'><font size=4><b>Habilitations</b> ".$help."</font>";

echo "<form name='formf' action='habilitations.php'>";
echo "<table class=noBorder><tr>
          <td colspan=2> Domaine
          <select id='domain' name='domain' 
               onchange=\"redirect2(document.formf.domain.options[document.formf.domain.selectedIndex].value, '$order', '$tab', '$from')\">";
echo "<option value='-1' selected>Tous les domaines</option>\n";
$query="select TF_ID, TF_DESCRIPTION
        from type_fonctionnalite";
if ( $gardes == 0  ) $query .= " where TF_DESCRIPTION <> 'gardes'";
$query .= "        order by TF_ID";

$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    if ( $domain == $TF_ID ) {
        echo "<option value='".$TF_ID."' selected>".$TF_DESCRIPTION."</option>\n";
    }
    else {
        echo "<option value='".$TF_ID."'>".$TF_DESCRIPTION."</option>\n";
    }
}
echo "</select></td>";
echo "</tr></table>";
echo "</form>";

//=====================================================================
// tabs
//=====================================================================

$query="select TR_CONFIG, count(1) as CNT from groupe group by TR_CONFIG";
$result=mysqli_query($dbc,$query);
$NB[1]=0;$NB[2]=0;$NB[3]=0;
while (custom_fetch_array($result)){
    $NB[$TR_CONFIG]=$CNT;
}

echo  "<p><ul class='nav nav-tabs noprint'>";
if ( $tab == 1 ) $class='active';
else $class='';
echo "<li class='nav-item'>
<a class='nav-link $class' href='habilitations.php?tab=1' title=\"Droit d'accès\" role='tab' aria-controls='tab1' href='#tab1' >
Droits d'accès ".$NB[1]."</a></li>";

if ( $tab == 2 ) $class='active';
else $class='';
echo "<li class='nav-item'>
<a class='nav-link $class' href='habilitations.php?tab=2' title=\"Rôle dans l'organigramme, exemple président\" role='tab' aria-controls='tab2' href='#tab2' >
Rôles dans l'organigramme ".$NB[2]."</a></li>";

if ( $tab == 3 ) $class='active';
else $class='';
echo "<li class='nav-item'>
<a class='nav-link $class' href='habilitations.php?tab=3' title=\"Permissions dans l'organigramme, exemple responsable véhicule\" role='tab' aria-controls='tab3' href='#tab3' >
Permissions organigramme ".$NB[3]."</a></li>";
echo "</ul><br>";
// fin tabs

$query1="select distinct f.F_ID , f.F_TYPE, f.F_LIBELLE, f.F_DESCRIPTION, tf.TF_ID, tf.TF_DESCRIPTION, f.F_FLAG
         from fonctionnalite f, type_fonctionnalite tf
         where f.TF_ID = tf.TF_ID";
if ( $domain <> -1  ) $query1 .= " and tf.TF_ID = ".$domain;
else $query1 .= " and tf.TF_ID is not null";
$query1 .=" order by f.".$order.",f.F_ID";
$result1=mysqli_query($dbc,$query1);

$query2 ="select GP_ID, GP_DESCRIPTION, GP_USAGE, GP_ASTREINTE, GP_ORDER, TR_CONFIG from groupe ";
$query2 .=" where TR_CONFIG = ".$tab;
$query2 .=" order by GP_ORDER, GP_ID";
$result2=mysqli_query($dbc,$query2);
$nbg=mysqli_num_rows($result2);

if ( $nbg > 0 ) {
    echo "<table cellspacing=0 border=0>";
    // ===============================================
    // premiere ligne du tableau
    // ===============================================

    echo "<tr class=TabHeader align=center>
                       <td><font size=1><a href=habilitations.php?tab=".$tab."&order=F_ID class=TabHeader>N°</a></td>
                     <td><font size=1><a href=habilitations.php?tab=".$tab."&order=F_LIBELLE class=TabHeader >Fonctionnalité</a></td> 
                     <td><font size=1><a href=habilitations.php?tab=".$tab."&order=TF_ID class=TabHeader>Catégorie</a></td>
              ";
              
    while (custom_fetch_array($result2)) {
          
        if ( $GP_DESCRIPTION == "Président (e)" ) $title=$GP_DESCRIPTION." ou responsable d'antenne";
        else if ( $GP_DESCRIPTION == "Vice président (e)" ) $title=$GP_DESCRIPTION." ou responsable adjoint d'antenne";
        else $title="";
      
        if ( $GP_USAGE  == 'externes' ) $usagecolor="<font color=".$mygreencolor.">";
        else if ( $GP_USAGE  == 'all') $usagecolor="<font color=yellow>";
        else if ( $GP_ASTREINTE  == 1 and $cron_allowed == 1) {
               $usagecolor="<font color='#00FFFF'>";
               $title="Ce rôle peut être attribué pour des astreintes";
        }
        else $usagecolor="<font color=white>";
      
        echo "<td width=90 class=small>";
        if ( check_rights($_SESSION['id'], 9) ) 
        echo "<a href=upd_habilitations.php?gpid=$GP_ID class=TabHeader title=\"$title  Cliquer pour modifier les permissions\">".$usagecolor.$GP_DESCRIPTION."</td>";
        else {
               if ( $title <> '' ) $GP_DESCRIPTION="<a class=TabHeader title=\"$title\">".$GP_DESCRIPTION."</a>";
               echo $usagecolor.$GP_DESCRIPTION."</td>";
        }
    }

    echo "</tr>";
        
    // ===============================================
    // le corps du tableau
    // ===============================================

    $i=0; $prevtf=0;
    while (custom_fetch_array($result1)) {
        if (( $gardes == 1 ) or ( $F_TYPE <> 1 )) {
            $prevtype=$TF_ID;
            $i=$i+1;
            if ( $i%2 == 0 ) {
               $mycolor="$mylightcolor";
            }
            else {
               $mycolor="#FFFFFF";
            }

            if (( $prevtf <> $TF_ID) and ( $TF_ID <> 0 ) and ( $order=='TF_ID'))  {
                $nbcol=2*$nbg + 2;
                echo "<tr class=TabHeader height=1><td colspan=$nbcol style='padding-top:1px;'></td></tr>";
            }
            $prevtf=$TF_ID;
          
            if ( $F_FLAG == 1  and  $nbsections == 0 )  $cmt=" $asterisk";
            else $cmt="";
            $help_link=" <a href='#' data-toggle='popover' title=\"".$F_ID." - ".$F_LIBELLE."\" data-trigger='hover' data-content=\"".strip_tags($F_DESCRIPTION)."\">".$F_LIBELLE."</a>";
            echo "<tr bgcolor=$mycolor>";
            echo "<td align=center nowrap><font size=1>".$F_ID." 
                  <td nowrap> - <font size=1>".$help_link;
            echo $cmt." <i></td><td nowrap><font size=1>".$TF_DESCRIPTION."</i></font></td>";
            $result2=mysqli_query($dbc,$query2);

            while ($row2=@mysqli_fetch_array($result2)) {
                $GP_ID=$row2["GP_ID"];
                $query3="select count(1) as num from habilitation where GP_ID=".$GP_ID." and F_ID=".$F_ID;
                $result3=mysqli_query($dbc,$query3);
                $row3=@mysqli_fetch_array($result3);
                $num=$row3["num"];
                if ( $num >= 1 ) {
                       $mypic="<i class='fa fa-check' title='actif'></i>";
                }
                else {
                       $mypic="" ;
                }
                echo "<td align=center>".$mypic."</td>";
            }
            echo "</tr>";
        }
    }
    echo "</table>";
}

if ( check_rights($_SESSION['id'], 9)) {
    if ($tab == 1) $label="Droit d'accès";
    else if ($tab == 2) $label="Rôle";
    else $label="Permission";

    if ( intval($NB[$tab]) < $nbmaxgroupes )
        echo "<button class='btn btn-default' onclick=\"bouton_redirect('ins_groupe.php?tab=$tab');\">
                <i class='fa fa-plus'></i> ".$label."
            </button>";
    else
        echo "<font color=red ><b>Vous ne pouvez plus ajouter de groupes de cette catégorie( maximum atteint: $nbmaxgroupes)</b></font>";
}

if ( $from == 'update' )
echo "<input type=submit class='btn btn-default' value='retour' onclick=\"bouton_redirect('index_d.php');\"> ";
else
echo "<input type=submit class='btn btn-default' value='retour' onclick='javascript:history.back(1);'> ";

if ( $nbsections == 0 and $nbg > 0 ) 
    echo "<p><small>$asterisk<i> ces fonctionnalités ne sont pas accessibles aux personnes habilitées seulement au niveau antenne</i></small>";
echo "</div>";

writefoot();
?>
