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

$possibleorders= array('GRADE','NOM');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='NOM';

if ( isset($_GET["from"]))$from=$_GET["from"];
else $from="default";

if (check_rights($id, 4)) $granted_permissions=true;
else $granted_permissions=false;
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/qualifications.js'></script>
<?php
include_once ("config.php");

if (isset ($_GET["pompier"])) $pompier=$_GET["pompier"];
else $pompier=0;
$MYP_ID=intval($pompier);

$title="Compétences";

// ===============================================
// listes déroulantes de choix
// ===============================================
if ( $MYP_ID == 0 ) {
    $query2="select p.PS_ID, p.TYPE, p.DESCRIPTION as COMMENT
        from poste p
        where p.EQ_ID=".$typequalif."
        order by p.PS_ORDER, p.TYPE"; 
         
    $result2=mysqli_query($dbc,$query2);
    $num_postes = mysqli_num_rows($result2);
    
    $select1="select p.P_ID , p.P_NOM , p.P_PRENOM, p.P_GRADE, g.G_DESCRIPTION, p.P_STATUT, p.P_SECTION, g.G_LEVEL, s.S_CODE, s.S_DESCRIPTION";
    $queryadd = " from pompier p, grade g, section s where p.P_GRADE=g.G_GRADE and p.P_OLD_MEMBER = 0 and p.P_STATUT <> 'EXT' and s.S_ID = p.P_SECTION";
    if ( $competence > 0 and $action_comp =='default' ) $queryadd .= " and exists ( select 1 from qualification q where q.P_ID = p.P_ID and q.PS_ID=".$competence.")";

    $role = get_specific_outside_role();
    
    if ( $subsections == 1 ) {
        if ( $filter == 0 ) {
            $queryfilter1="";
            $queryfilter2="";
        }
        else {
            $list = get_family($filter);
            $queryfilter1 = " and p.P_SECTION in (".$list.")";
            $queryfilter2  = " and P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role.") and P_SECTION not in (".$list.")";
        }
    }
    else {
        $queryfilter1 = " and p.P_SECTION =".$filter;
        $queryfilter2  = " and P_ID in ( select P_ID from section_role where S_ID = ".$filter." and GP_ID=".$role.") and  P_SECTION <> ".$filter;
    }
    if ( $order=="NOM" ) $queryorder = " order by P_NOM";
    else $queryorder = " order by G_LEVEL desc";

    $queryX = $select1.$queryadd.$queryfilter1;
    if ( $filter > 0 or $subsections == 0 and $role > 0 ) $queryX .=" union ".$select1.$queryadd.$queryfilter2;
    $queryX .= $queryorder;
    write_debugbox($queryX);

    $querycnt1 = "select count(1) as NB1".$queryadd.$queryfilter1;
    $resultcnt1=mysqli_query($dbc,$querycnt1);
    $rowcnt1=custom_fetch_array($resultcnt1);
    if ( $filter > 0 or $subsections == 0 ) {
        $querycnt2 = "select count(1) as NB2".$queryadd.$queryfilter2;
        $resultcnt2=mysqli_query($dbc,$querycnt2);
        $rowcnt2=custom_fetch_array($resultcnt2);
    }
    else $NB2=0;
    $number = $NB1 + $NB2;
    
    echo "<div align=center class='table-responsive'><font size=4><b>$title du personnel</b></font> <span class='badge'>$number</span> 
        <a href='#'><i class='far fa-file-excel fa-lg' style='color:green;' id='StartExcel' title='Excel' 
        onclick=\"window.open('qualifications_xls.php?filter=$filter&typequalif=$typequalif&subsections=$subsections&competence=$competence')\" ></i></a><p>";
    echo "<table class='noBorder' >";
    echo "<tr>";
    echo "<td>".choice_section_order('qualifications.php')."</td>";
    
    // choix de la section
    echo "<td align=left><select id='filter' name='filter'  class=smallcontrol2
        onchange=\"displaymanager('0','".$order."',document.getElementById('filter').value,'".$typequalif."','".$subsections."','".$from."','".$competence."')\">";
          display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select></td> ";
    if ( get_children("$filter") <> '' ) {
          if ($subsections == 1 ) $checked='checked';
          else $checked='';
          echo "<td align=center ><input type='checkbox' name='sub' $checked 
               onClick=\"displaymanager2('0','".$order."','".$filter."',document.getElementById('typequalif').value, this,'".$from."','".$competence."')\"/>
               <font size=1>inclure les $sous_sections</td>";
    }

    if ( $competence > 0 ) {
        $query3="select TYPE, DESCRIPTION, PS_EXPIRABLE, F_ID from poste p where PS_ID=".$competence;
        $result3=mysqli_query($dbc,$query3);
        custom_fetch_array($result3);
        if ( intval($F_ID) == 0 ) $F_ID=4;
        if ( ! check_rights($id,$F_ID,$filter) ) {
            $action_comp = 'default';
            $_SESSION['action_comp'] = 'default';
        }
    }
    else 
        $action_comp = 'default';
    
    if ( $action_comp == 'update' ) {
        echo "<tr><td colspan=2><b>Modification des compétences ".$TYPE." - ".$DESCRIPTION."</b></td></tr>";
    }
    else {
        // choix type de compétence
        echo "</tr><tr><td>Type</td><td align=left><select id='typequalif' name='typequalif'  class=smallcontrol2
            onchange=\"displaymanager('0','".$order."','".$filter."',document.getElementById('typequalif').value,'".$subsections."','".$from."','0')\">";
        $query3="select EQ_ID, EQ_NOM from equipe";
        echo "<option value='0'>Tous types (Excel seulement)</option>";
        $result3=mysqli_query($dbc,$query3);
        while (custom_fetch_array($result3)) {
            if ($EQ_ID == $typequalif ) $selected='selected';
            else $selected='';
            echo "<option value='".$EQ_ID."' $selected>".$EQ_NOM."</option>\n";
        }
        echo "</select></td>";
        
        // filtre compétence
        echo "</tr><tr><td>Filtre</td><td align=left>
          <select id='competence' name='competence'   class=smallcontrol2
            title='Choisir une compétence pour montrer seulement le personnel qualifié pour cette compétence'
            onchange=\"displaymanager('0','".$order."','".$filter."','".$typequalif."','".$subsections."','".$from."',document.getElementById('competence').value)\">";
        $query3="select PS_ID, TYPE, DESCRIPTION from poste ";
        if ( $typequalif > 0 ) $query3 .=" where EQ_ID=".$typequalif;
        $query3 .=" order by TYPE";
        echo "<option value='0'>Pas de filtre</option>";

        $result3=mysqli_query($dbc,$query3);
        while (custom_fetch_array($result3)) {
            if ($PS_ID == $competence ) {
                $selected='selected';
            }
            else $selected='';
            echo "<option value='".$PS_ID."' $selected>".$TYPE." - ".$DESCRIPTION."</option>\n";
        }
        echo "</select></td>";
    }
    if ( $competence <> 0 and $action_comp =='default' ) {
        if ( check_rights ($id, $F_ID, "$filter") )
            echo "<td align=center><input type='button' class='btn btn-default' value='Modifier' 
            title=\"Modifier les qualifications du personnel pour cette compétence ".$TYPE." - ".$DESCRIPTION."\" 
            onclick=\"update_competence('".$competence."');\"></td>";
    }

    echo "</tr>";
    
    echo "<tr><td colspan=4>";
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
        $queryX .= $pages->limit;
    }
    $result=mysqli_query($dbc,$queryX);
    $numberrows=mysqli_num_rows($result);

    echo "</td></tr></table>";

}
else {
    echo "<div align=center><font size=4>
            <b>$title pour: ".strtoupper(get_nom($MYP_ID))." ".my_ucfirst(get_prenom($MYP_ID))." <br></b></font>";
}

// ===============================================
// tout le personnel
// ===============================================
if ( $MYP_ID == 0 ) {
    if ($typequalif == 0 ) {
        write_msgbox("Erreur", $warning_pic, "Le nombre de compétences est trop élevé. Seul la page excel peut être affichée.<br>Ou choisissez un type de compétences.",10,0);
        exit;
    }
    
    echo "<tr ><td colspan=2 align=center> Compétence
        <i class='fa fa-circle' style='color:green;' title='Compétence principale'></i> principale 
        <i class='fa fa-circle' style='color:blue;' title='Compétence secondaire'></i> secondaire
        <i class='fa fa-circle' style='color:red;' title='Compétence expirée'></i> expirée
        <i class='fa fa-circle' style='color:orange;' title='Compétence bientôt expirée'></i> bientôt expirée
        </td></tr></table><p>";
    
    
    // ===============================================
    // tout le personnel - modification une compétence 
    // ===============================================
    if ( $competence > 0 and $action_comp == 'update' ) {
        echo "<form name = 'chqualif2' id='chqualif2' action='save_qualif2.php' method='POST'>";
        print insert_csrf('qualif2');
        echo "<table cellspacing=0>
                <tr class=TabHeader>
                <td width=30></td>
                <td width = 300 class=TabHeader><a href=qualifications.php?pompier=0&order=NOM&filter=$filter&typequalif=$typequalif class=TabHeader>Nom</a></td>";
        if ( $grades == 1 )echo "<td >Grade</td>";
        else echo "<td></td>";
        echo "  <td width=60 align=center >principale</td>
                <td width=60 align=center >secondaire</td>
                <td width=60 align=center >non</td>  
                <td width=100 align=center >expiration</td>
               </tr>";
        while (custom_fetch_array($result)) {
            $query3="select p.P_ID, q.Q_VAL, DATE_FORMAT(Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION, DATEDIFF(q.Q_EXPIRATION,NOW()) as NB 
                        from pompier p left join qualification q on (q.P_ID = p.P_ID and PS_ID=".$competence.") 
                        where p.P_ID=".$P_ID;
            $result3=mysqli_query($dbc,$query3);
            $checked1='';$checked2='';$checked0='';
            custom_fetch_array($result3);
            $Q_VAL=intval($Q_VAL);
            echo "<tr bgcolor=$mylightcolor id='row_".$P_ID."'>";
            if ($Q_VAL == 1 ) {
                $checked1='checked';
                $myimg="<i class='fa fa-circle' style='color:green;'  title='compétence principale'></i>";
            }
            else if ($Q_VAL == 2 ) {
                $checked2='checked';
                $myimg="<i class='fa fa-circle' style='color:blue;'  title='compétence secondaire'></i>";
            }
            else {
                $checked0='checked';
                $myimg="";
            }
            echo "<td align=center>$myimg</td>";
            echo "<td align=left><b><a href=upd_personnel.php?tab=2&pompier=".$P_ID.">".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a></b> <span class=small title=\"".$S_DESCRIPTION."\">- ".$S_CODE."</small></td>";
            if ( $grades == 1 ) { 
                $img=$grades_imgdir."/".$P_GRADE.".png";
                if (! file_exists($img)) $img=$grades_imgdir."/-.png";
                echo "<td width=70><img src='".$img."' title=\"".$G_DESCRIPTION."\"  class='img-max-20'/></td>"; 
            }
            else 
                echo "<td></td>";

            if ( $Q_EXPIRATION == '00-00-0000' ) $Q_EXPIRATION='';
            if ( $Q_EXPIRATION <> '') {
                if ($NB < 61) $myimg="<i class='fa fa-circle' style='color:orange;'  title='expiration dans moins de 2 mois'></i>";
                if ($NB <= 0) $myimg="<i class='fa fa-circle' style='color:red;' title='date expiration dépassée' ></i>";
            }
            if ($Q_VAL >= 1 ) $style="<b>";
            else $style="";
            
            echo "<input type='hidden' name='competence' value=".$competence.">";
            
            echo "<td align=center>
                <input type='radio' id='".$P_ID."_1' name='$P_ID' value='1' $checked1
                    onclick=\"change_competence('".$P_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";></td>";
            
            echo " <td align=center>
                <input type='radio' id='".$P_ID."_2' name='$P_ID' value='2' $checked2
                    onclick=\"change_competence('".$P_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";></td>";

            echo " <td align=center>
                <input type='radio' id='".$P_ID."_0' name='$P_ID' value='0' $checked0
                    onchange=\"change_competence('".$P_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";></td>";
                
            echo " <td align=center>";
            if ( $PS_EXPIRABLE == 1 ) {
                $placeholder="placeholder='JJ-MM-AAAA'";

                echo " <input type=text size=10 maxlength=10 name='exp_".$P_ID."' id='exp_".$P_ID."'  $placeholder class='datepicker' data-provide='datepicker'
                    value='".$Q_EXPIRATION."' title='JJ-MM-AAAA' autocomplete='off'
                    onchange=\"change_competence('".$P_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";>";
            }
            else {
                echo "<input type='hidden' name='exp_".$P_ID."' value=''>";
            }
            echo "<input type='hidden' id='updated_".$P_ID."' name='updated_".$P_ID."' value='0'>";
            echo " </td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p>
                <input type='submit' class='btn btn-default' value='sauver' 
                title=\"Sauver les qualifications saisies pour cette compétence ".":\n".$TYPE." - ".$DESCRIPTION."\">
                <input type='button' class='btn btn-default' value='Retour' 
                    title=\"Annuler et retour à la page précédente \" onclick=\"redirect3();\">";
    }

    // ===============================================
    // tout le personnel - read only
    // ===============================================

    else  {
        $query_k="select e.EQ_ID, e.EQ_NOM, count(1) as EQNB from poste p, equipe e
            where e.EQ_ID=p.EQ_ID";
        if ($typequalif <> 0 ) $query_k .= " and e.EQ_ID=".$typequalif;
        $query_k .= " group by e.EQ_ID, e.EQ_NOM order by p.PS_ORDER";
        $result_k=mysqli_query($dbc,$query_k);
        if ( $number == 0 ) {
            echo "<small><i>Aucune personne trouvée</i></small>";
        }
        else {
            echo "<table cellspacing=0 border=0>";
            // ===============================================
            // premiere ligne du tableau
            // ===============================================

            echo "\n<tr class=TabHeader>";
            if ( $grades == 1 )echo "<td colspan=2 ></td>";
            else echo "<td></td>";

            while (custom_fetch_array($result_k)) {
                $cs=$EQNB * 2;
                echo "<td colspan=".$EQNB." align=center>".$EQ_NOM."</td>";
            }
            echo "</tr>";

            echo "<tr>
              <td bgcolor=$mydarkcolor width=260 >
                  <a href=qualifications.php?pompier=0&order=NOM&filter=$filter&typequalif=$typequalif class=TabHeader>Nom</a></td>";

            if ( $grades == 1 ) {         
            echo "<td bgcolor=$mydarkcolor width=70>
                <a href=qualifications.php?pompier=0&order=GRADE&filter=$filter&typequalif=$typequalif class=TabHeader>Grade</a></td>";
            }

            while (custom_fetch_array($result2)) {
                if ( $PS_ID == $competence ) $TYPE="<span class='badge' style='background-color:yellow; color:$mydarkcolor'>".$TYPE."</span>";
                $COMMENT=strip_tags($COMMENT);
                echo "<td bgcolor=$mydarkcolor width=50 align=center ><a href=qualifications.php?pompier=0&competence=$PS_ID&filter=$filter&typequalif=$typequalif title=\"$COMMENT\" class=TabHeader>$TYPE</a></td>";
            }
            echo "</tr>";

            // ===============================================
            // le corps du tableau
            // ===============================================
            $i=0;
            while (custom_fetch_array($result)) {
                $i=$i+1;
                if ( $i%2 == 0 ) {
                   $mycolor="$mylightcolor";
                }
                else {
                    $mycolor="#FFFFFF";
                }
                if ( check_rights($id, 4, "$P_SECTION")) {
                    // ligne avec lien pour modifier
                    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" 
                        onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; 
                        displaymanager($P_ID,'".$order."','".$filter."','".$typequalif."','".$subsections."','".$from."')\">";
                }
                else {
                    // ligne sans lien pour modifier
                    echo "<tr bgcolor=$mycolor>";
                }
                  
                echo "<td width=180><b><a href=upd_personnel.php?tab=2&pompier=".$P_ID.">".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a></b> <span class=small title=\"".$S_DESCRIPTION."\">- ".$S_CODE."</small>
                       </td>";
                if ( $grades == 1 ) {
                    $img=$grades_imgdir."/".$P_GRADE.".png";
                    if (! file_exists($img)) $img=$grades_imgdir."/-.png";
                    echo "<td width=70><img src='".$img."' title=\"".$G_DESCRIPTION."\"  class='img-max-20'/></td>"; 
                }
                $result2=mysqli_query($dbc,$query2);
                  
                  // optimiser ici, faire un seul acces base par personne
                while (custom_fetch_array($result2)) {
                    $query3="select Q_VAL, Q_EXPIRATION,  DATEDIFF(Q_EXPIRATION,NOW()) as NB 
                        from qualification where PS_ID=".$PS_ID." and P_ID=".$P_ID;
                    $result3=mysqli_query($dbc,$query3);
                    if (mysqli_num_rows($result3) > 0) {
                        custom_fetch_array($result3);
                        if ( $Q_VAL == 1 ) {
                            $mypic="<i class='fa fa-circle' style='color:green;' title='compétence principale'></i>";
                            $selected1="selected";
                            $selected2="";
                        }
                        if ( $Q_VAL == 2 ) {
                            $mypic="<i class='fa fa-circle' style='color:blue;' title='compétence secondaire'></i>";
                            $selected1="";
                            $selected2="selected";
                        }
                        $selected0="";
                        if ( $Q_EXPIRATION <> '') {
                            if ($NB < 61) $mypic="<i class='fa fa-circle' style='color:orange;' title='expiration dans moins de 2 mois'></i>";
                            if ($NB <= 0) $mypic="<i class='fa fa-circle' style='color:red;' title='date expiration dépassée' ></i>";
                        }
                    }
                    else {
                        $mypic="" ;
                        $selected0="selected";
                        $selected1="";
                        $selected2="";
                    }
                    echo "<td width=40 align=center>".$mypic."</td>";
                }
                if ($MYP_ID <> 0) echo "</form>";
                echo "</tr>";
            }


            // ===============================================
            // le bas du tableau
            // ===============================================


            echo "<tr class=TabHeader>
              <td bgcolor=$mydarkcolor width=130 >Total </td>";
            if ( $grades == 1 ) {  
                echo "<td bgcolor=$mydarkcolor width=70></td>";
            }
            $result2=mysqli_query($dbc,$query2);
            while (custom_fetch_array($result2)) {
                $query="select count(1) as NB 
                     from qualification q, pompier p 
                     where q.PS_ID=".$PS_ID." 
                     and p.P_ID=q.P_ID
                     and P_OLD_MEMBER = 0
                     and P_STATUT <> 'EXT'";
                if ( $subsections == 1 ) 
                    $query .= " and P_SECTION in (".get_family("$filter").")";
                else 
                    $query .= " and P_SECTION =".$filter;
                $result=mysqli_query($dbc,$query);
                $row=@mysqli_fetch_array($result);
                $NB=$row["NB"];
                echo "<td bgcolor=$mydarkcolor width=40 align=center>".$NB."</td>";
            } 
            echo "</tr>";
            echo "</table><p>";
        }
    }
}
// ===============================================
// une personne - modification
// ===============================================

else { // mode update one
    $THE_SECTION=get_section_of("$MYP_ID");
    // permission de modifier les compétences?
    $competence_allowed=false;
    $query="select distinct F_ID from poste order by F_ID";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        if (check_rights($_SESSION['id'], $F_ID, "$THE_SECTION") ) {
            $competence_allowed=true;
            break;
        }
    }
    if ( $competence_allowed )  $disabled_base='';
    else $disabled_base='disabled';

    echo "<form name = 'chqualif' id='chqualif' action='save_qualif.php' method='POST'>";
    print insert_csrf('qualif');
    // choix type compétence
    echo "<p> Type de compétences <select id='filter_one' name='filter_one' 
            onchange=\"displaymanager3('".$MYP_ID."', document.getElementById('filter_one').value,'".$from."')\">";
    $query3="select EQ_ID, EQ_NOM from equipe";

    echo "<option value='0'>Tous types</option>";
    $result3=mysqli_query($dbc,$query3);
    while (custom_fetch_array($result3)) {
        if ($EQ_ID == $typequalif ) $selected='selected';
        else $selected='';
        echo "<option value='".$EQ_ID."' $selected>".$EQ_NOM."</option>\n";
    }
    echo "</select><p>";
    if ( $disabled_base == 'disabled' ) echo "<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i> <font size=1><i>Attention seules les compétences que vous avez le droit de modifier apparaissent</i></font><p>";
    echo "<input name='typequalif' type='hidden' value=".$typequalif.">";
    echo "<input name='pompier' type='hidden' value=".$MYP_ID.">";
    echo "<input name='order' type='hidden' value=".$order.">";
    echo "<input name='filter' type='hidden' value=".$filter.">";
    echo "<input name='from' type='hidden' value=".$from.">";

    $queryn="select count(1) as NB from poste where PS_USER_MODIFIABLE = 1";
    $resultn=mysqli_query($dbc,$queryn);
    $rown=@mysqli_fetch_array($resultn);
    $n=$rown["NB"];

    $OLDEQ_NOM="NULL";
    $query2="select e.EQ_ID, e.EQ_NOM, p.PS_ID, TYPE, p.DESCRIPTION, p.PS_EXPIRABLE, p.F_ID,
             p.PS_USER_MODIFIABLE, p.PH_LEVEL, p.PH_CODE,
             Q_VAL, DATE_FORMAT(Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION,  
             DATEDIFF(Q_EXPIRATION,NOW()) as NB
             from equipe e, poste p left join qualification q on ( q.P_ID=".$MYP_ID." and q.PS_ID=p.PS_ID)
             where e.EQ_ID=p.EQ_ID";
    if (($disabled_base == 'disabled') and ($n > 0))
        $query2 .=" and p.PS_USER_MODIFIABLE = 1";
    if ( $typequalif > 0 ) $query2 .=" and e.EQ_ID=".$typequalif;    
    $query2 .=" order by e.EQ_ID, p.PH_CODE desc, p.PH_LEVEL desc, p.PS_ORDER";

    $result2=mysqli_query($dbc,$query2);
    
    if ( mysqli_num_rows($result2) > 0 ) {
        echo "<table cellspacing=0 border=0>";
        while (custom_fetch_array($result2)) {
            $DESCRIPTION=strip_tags($DESCRIPTION);
            if ( $PH_CODE <> "" ) $hierarchie=" <span class=small2>(".$PH_CODE." niveau ".$PH_LEVEL.")</span>";
            else $hierarchie="";
            $checked1='';$checked2='';$checked0='';
            $Q_VAL=intval($Q_VAL);
            if ($Q_VAL == 1 ) {
                $checked1='checked';
                $myimg="<i class='fa fa-circle' style='color:green;'  title='compétence principale'></i>";
            }
            else if ($Q_VAL == 2 ) {
                $checked2='checked';
                $myimg="<i class='fa fa-circle' style='color:blue;'  title='compétence secondaire'></i>";
            }
            else {
                $checked0='checked';
                $myimg="";
            }
            if ( $Q_EXPIRATION == '00-00-0000' ) $Q_EXPIRATION='';
            if ( $Q_EXPIRATION <> '') {
                if ($NB < 61) $myimg="<i class='fa fa-circle' style='color:orange;'  title='expiration dans moins de 2 mois'></i>";
                if ($NB <= 0) $myimg="<i class='fa fa-circle' style='color:red;' title='date expiration dépassée' ></i>";
            }
            if ( $EQ_NOM <> $OLDEQ_NOM) {
                $OLDEQ_NOM =  $EQ_NOM;
                echo "<tr>
                    <td width=360 align=center class=TabHeader colspan=3 align=left>$EQ_NOM</td>
                    <td width=60 align=center class=TabHeader >principale</td>
                    <td width=60 align=center class=TabHeader >secondaire</td>
                    <td width=60 align=center class=TabHeader >non</td>  
                    <td width=100 align=center class=TabHeader >expiration</td>";
                echo "</tr>";    
            }
            
            $disabled3='disabled';
            if ( check_rights($id,$F_ID)) $disabled3='';
            
            if ($Q_VAL >= 1 ) $style="style='font-weight: bold;'";
            else $style="";
            
            if ( $PS_USER_MODIFIABLE == 1  and  $MYP_ID == $id ) {
                $disabled = ''; 
                $disabled3= '';
            }
            else $disabled=$disabled_base;
            
            echo "<tr bgcolor=$mylightcolor  id='row_".$PS_ID."'>
                 <td width=25 align=center>$myimg</td> 
                 <td width=60 align=left $style>".$TYPE."</td>
                 <td width=350 align=left $style>".$DESCRIPTION.$hierarchie."</font></td>";
                   
            echo "<td align=center>";
            echo "<input type='radio' id='".$PS_ID."_1' name='$PS_ID' value='1' $checked1 $disabled $disabled3
                    onclick=\"change_competence('".$PS_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";>";
            echo "</td>";
            
            echo " <td align=center>";
            echo "<input type='radio' id='".$PS_ID."_2' name='$PS_ID' value='2' $checked2 $disabled $disabled3
                     onclick=\"change_competence('".$PS_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";>";
            echo "</td>";

            echo " <td align=center>";
            echo "<input type='radio' id='".$PS_ID."_0' name='$PS_ID' value='0' $checked0 $disabled $disabled3
                        onchange=\"change_competence('".$PS_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";>";
            echo "</td>";
            
            if ( $disabled3 == 'disabled' )
                echo "<input type=hidden name='".$PS_ID."' value='".$Q_VAL."'>";
                
            echo " <td align=center>";
            if ( $PS_EXPIRABLE == 1 ) {
                $disabled2='disabled';
                if ( $disabled == '' ) {
                    if ( $checked0 == '' ) $disabled2='';
                }
                $placeholder="placeholder='JJ-MM-AAAA'";

                echo " <input type=text size=10 maxlength=10 name='exp_".$PS_ID."' id='exp_".$PS_ID."'  $placeholder class='datepicker' data-provide='datepicker'
                    value='".$Q_EXPIRATION."' title='JJ-MM-AAAA' autocomplete='off'
                    onchange=\"change_competence('".$PS_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";
                    $disabled2 $disabled3>";
            }
            else {
                echo "<input type=hidden name='exp_".$PS_ID." value=''>";
            }
            echo "<input type='hidden' id='updated_".$PS_ID."' name='updated_".$PS_ID."' value='0'>";
            echo " </td>";
            echo "</tr>";
        }
        echo "</table><p>";
    }
    if ( $disabled_base == ''  or $n > 0) echo "<input type='submit' class='btn btn-default' value='sauver'>";
    if ( $from == 'personnel' )
        echo " <input type='button' class='btn btn-default' value='Retour' name='Retour' onclick=\"javascript:redirect1(".$MYP_ID.");\">";
    else
        echo " <input type='button' class='btn btn-default' value='Retour' name='Retour' onclick=\"javascript:redirect2();\">";
}
echo "</div>";
writefoot();
?>
