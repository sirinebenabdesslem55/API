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
get_session_parameters();

$possibleorders= array('G_LEVEL','P_PHOTO','P_STATUT','P_NOM','P_PRENOM','S_CODE','P_DATE_ENGAGEMENT','P_FIN','C_NAME', 'P_PROFESSION', 'SERVICE', 'P_CODE', 'P_REGIME');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

$fixed_company = false;
if ( $category == 'EXT' ) {
    if ( check_rights($id, 37))
        test_permission_level(37);
    else {
        check_all(45);
        $company=$_SESSION['SES_COMPANY'];
        $_SESSION['company'] = $company;
        $fixed_company = true;
    }
} 
else {
    test_permission_level(56);
}

$ischef=is_chef($id,$filter);

$disabled="disabled";
$hide_phone=true;
$envoisEmail=false;
if ($position == 'actif'){
    if ( check_rights($id, 43) )
    $envoisEmail=true;
}

?>
<script type='text/javascript' src='js/personnel_liste.js'></script>
<?php
echo "</head>";

$query1="select distinct pompier.P_ID, P_CODE , P_NOM , P_PRENOM, P_HIDE, P_SEXE, pompier.C_ID, C_NAME, SERVICE, G_LEVEL,
        P_GRADE, G_DESCRIPTION, G_CATEGORY, P_STATUT, P_REGIME, DATE_FORMAT(P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT1, P_DATE_ENGAGEMENT, DATE_FORMAT(P_FIN,'%d-%m-%Y') 'P_END', P_FIN,
        P_SECTION, ".phone_display_mask('P_PHONE')." as P_PHONE,".phone_display_mask('P_PHONE2')." as P_PHONE2, S_CODE, P_EMAIL, P_PHOTO, P_PROFESSION";

$queryadd=" from section, pompier left join company on pompier.C_ID = company.C_ID
    left join grade on grade.G_GRADE =  pompier.P_GRADE
     where pompier.P_SECTION=section.S_ID";

if ( $company == 0 ) $queryadd .= " and ( company.C_ID = 0 or company.C_ID is null )";
else if ( $company > 0 ) $queryadd .= " and company.C_ID = $company";

if ( $syndicate == 1 ) { $p="des adhérents"; $r="radiés"; $a="actifs"; }
else { $p="du personnel"; $r="anciens"; $a="actif"; }


if ( $category == 'EXT' ) {
    $queryadd .= " and P_STATUT = 'EXT'";
    $title="Liste ".$p." <span style='background-color:#00ff00;'>externe</span>";
}
else {
    $queryadd .= " and P_STATUT <> 'EXT'";
    $title="Liste ".$p;
}

if ( $position == 'actif' ) {
    $queryadd .= " and P_OLD_MEMBER = 0";
    $title .=" ".$a;
}
else {
    $queryadd .= " and P_OLD_MEMBER > 0";
    $title .=" ".$r;
    $mylightcolor="#b3b3b3";
}

$role = get_specific_outside_role();

if ( $subsections == 1 ) {
    if ( $filter == 0 ) {
            $queryfilter1="";
            $queryfilter2="";
    }
    else {
        $list = get_family($filter);
        $queryfilter1  = " and P_SECTION in (".$list.")";
        $queryfilter2  = " and P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role.") and P_SECTION not in (".$list.")";
    }
}
else {
    $queryfilter1  = " and P_SECTION =".$filter;
    $queryfilter2  = " and P_ID in ( select P_ID from section_role where S_ID = ".$filter." and GP_ID=".$role.") and  P_SECTION <> ".$filter;
}

if ( $order == "P_REGIME" ) $queryorder = " order by P_STATUT, P_REGIME desc";
else if ( $order == "G_LEVEL" ) $queryorder = " order by G_CATEGORY desc, G_LEVEL desc, G_DESCRIPTION, P_NOM asc, P_PRENOM asc";
else { 
    $queryorder = " order by ". $order;
    if ( $order == "P_PHOTO" or $order == "P_FIN" or $order == "P_DATE_ENGAGEMENT" )  $queryorder .=" desc";
}

$queryX = $query1.$queryadd.$queryfilter1;
if ( $filter > 0 or $subsections == 0 and $role > 0 ) $queryX .=" union ".$query1.$queryadd.$queryfilter2;
$queryX .= $queryorder;
write_debugbox($queryX);

$querycnt1 = "select count(1) as NB1 ".$queryadd.$queryfilter1;
$resultcnt1=mysqli_query($dbc,$querycnt1);
$rowcnt1=custom_fetch_array($resultcnt1);
if ( $filter > 0 or $subsections == 0 ) {
    $querycnt2 = "select count(1) as NB2".$queryadd.$queryfilter2;
    $resultcnt2=mysqli_query($dbc,$querycnt2);
    $rowcnt2=custom_fetch_array($resultcnt2);
}
else $NB2=0;
$number = $NB1 + $NB2;

echo "<body >";
echo "<div align=center class='table-responsive'><font size=4><b>$title</b></font> <span class='badge'>$number personnes</span>";
echo "<p><div><table class='noBorder'>";
echo "<tr height=40>";

echo "<td>
   <a href=trombinoscope.php?position=".$position."&category=".$category.">
    <i class='fa fa-camera fa-2x' title='voir le trombinoscope'></i></a>";
if ( $geolocalize_enabled == 1 and check_rights($id,76)) {
    echo " <a href='gmaps_personnel.php?position=".$position."&category=".$category."' ><i class='fa fa-map fa-2x' style='color:green;' title='Localiser les adresses du personnel sur une carte' ></i></a>";
    if ( $category <> 'EXT') 
        echo " <a href='gps.php'><i class='fa fa-map-marker-alt fa-2x' style='color:crimson;' title='Localiser le personnel connecté avec leur position GPS'></i></a>";
}
if ( check_rights($id,2,$filter))
echo " <a href='#'><i class='far fa-file-excel fa-2x' style='color:green;' title='Exporter la liste dans un fichier Excel' 
        onclick=\"window.open('personnel_xls.php?filter=$filter&subsections=$subsections&category=$category&position=$position');\" /></i></a>";
        
if (( check_rights($id, 1) and $category=='interne') or (check_rights($id, 37) and $category=='EXT')) {
    if ( $position == 'actif' ) {
        $querynb="select count(*) as NB from pompier";
        $resultnb=mysqli_query($dbc,$querynb);
        $rownb=@mysqli_fetch_array($resultnb);
        $nb = $rownb[0];
    
        if ( ! $block_personnel or $category=='EXT' ) {
            if ( $nb <= $nbmaxpersonnes )
                echo "<p><a class='btn btn-default' href='#' onclick=\"bouton_redirect('ins_personnel.php?category=$category&suggestedcompany=$company');\">
                    <i class='fa fa-user-plus' ></i> Ajouter</a>";
            else
                echo "<p><i class ='fa fa-exclamation-circle fa-2x' style='color:red;' title=' Vous ne pouvez plus ajouter de personnel (maximum atteint: $nbmaxpersonnes)'></i>";
        }
    }
}


echo "</td>";

// section
if ( $_SESSION['SES_STATUT'] == 'EXT' ) {
    echo "Section";
}
else {
    echo "<td>".choice_section_order('personnel.php')."</td>";
}
echo "<td><select id='filter' name='filter' title='filtre par section' class='smalldropdown'
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$subsections."','".$position."','".$category."','-1')\">";
      display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<br><input type='checkbox' name='sub' id='sub' $checked
       onClick=\"orderfilter2('".$order."',document.getElementById('filter').value, this,'".$position."','".$category."','".$company."')\"/>
       <small><label for='sub'>inclure les $sous_sections</label></small>";
}
    
if ($externes == 1  ) {
    if ( $fixed_company ) $disabled='disabled';
    else $disabled='';
    echo "<p><select id='company' name='company' title='filtre par entreprise' $disabled  class='smalldropdown'
        onchange=\"orderfilter('".$order."','".$filter."','".$subsections."','".$position."','".$category."',document.getElementById('company').value)\">";
                
    echo "<option value='-1' 'selected'>... Pas de filtre par entreprise ...</option>";
        
    $treenode=get_highest_section_where_granted($id,37);
    if ( $treenode == '' ) $treenode=$mysection;
    if ( check_rights($id, 24) ) $treenode=$filter;
    echo companychoice("$treenode","$company",true,'EXT');
    echo "</select>";
}
echo "</td>";

echo "</tr><tr height=30><td colspan=4>";
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
$result1=mysqli_query($dbc,$queryX);
$numberrows=mysqli_num_rows($result1);

echo "</td></tr></table></div>";

if ($envoisEmail) {

// permettre les modifications si je suis habilité sur la fonctionnalité 2 ( ou 37 pour externes)
// (et si la personne fait partie de mes sections filles ou alors je suis habilité sur la fonctionnalité 24 )

if ((is_children($filter,$mysection)) or (check_rights($id, 24))) {
    if ( check_rights($id, 2) and $category=='interne' ) { $disabled="";$hide_phone=false; }
    if ( check_rights($id, 37) and $category=='EXT' ) { $disabled="";$hide_phone=false; }
    if ( check_rights($id, 12) and $category=='interne' ) { $disabled="";$hide_phone=false; }
}
if ( $ischef ) {
    $disabled="";
    $hide_phone=false;
}

echo "<form name=\"frmPersonnel\" id=\"frmPersonnel\" method=\"post\" action=\"mail_create.php\">";
if ( $number > 0 ) {
     if ( $category <> 'EXT' ) 
    echo "<input type=\"button\" class='btn btn-default' onclick=\"SendMailTo('frmPersonnel','SendMail','Vous devez sélectionner au moins un destinataire !','mail');\" value=\"Message\" title=\"envoyer un message à partir de cette application\">";
    if ( check_rights($id, 2) or check_rights($id, 26)) {
        echo " <input type=\"button\" class='btn btn-default' onclick=\"DirectMailTo('frmPersonnel','SendMail','Vous devez sélectionner au moins un destinataire !','mail');\" value=\"Mailto\" title=\"envoyer un message avec votre logiciel de messagerie\">";    
        echo " <input type=\"button\" class='btn btn-default' onclick=\"SendMailTo('frmPersonnel','SendMail','Vous devez sélectionner au moins un destinataire !','listemails');\" value=\"Listemails\" title=\"Récupérer la liste des adresses email\">";    
    }
    if ( check_rights($id, 30) and $nbsections == 0 ) {
        echo " <input type=\"button\" class='btn btn-default' onclick=\"SendMailTo('frmPersonnel','SendMail','Vous devez sélectionner au moins une personne !','badge');\" value=\"Editer les badges\" title=\"imprimer des badges\">";
    }
}
echo "<input type=\"hidden\" name=\"SelectionMail\" id=\"SelectionMail\">";
}

if ( $number == 0 ) {
    echo "<span class=small>Aucune personne.</span>";
    exit;
}

echo "<p><div ><table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class=TabHeader>";
if ($envoisEmail) {
    echo "      <td width=40 align=center>";
    if ( $numberrows > 0 )
        echo "<input type=checkbox name=CheckAll id=CheckAll onclick=checkAll(document.frmPersonnel.SendMail,this.checked); title='sélectionner/désélectionner tous'>";
}
if ( $syndicate == 1 ) {
    echo "<td width=60 align=center ><a href=personnel.php?order=P_PROFESSION class=TabHeader>Profession</a></td>";
}
if ( $grades == 1 ) {
    echo "<td width=45 align=center ><a href=personnel.php?order=G_LEVEL class=TabHeader >Grade</a></td>";
}
if ( $nbsections <> 0 ) 
    echo "<td width=45 align=center ><a href=personnel.php?order=P_CODE class=TabHeader  >Matricule</a></td>";
echo "<td width=15 align=center ><a href=personnel.php?order=P_PHOTO class=TabHeader><i class='fa fa-user' title='personnel avec une photo'></i></a></td>";
echo "<td align=left width=140><a href=personnel.php?order=P_NOM class=TabHeader >Nom</a></td>
      <td align=left width=100><a href=personnel.php?order=P_PRENOM class=TabHeader >Prénom</a></td>";
echo "<td align=left width=45 class='hide_mobile'><a href=personnel.php?order=P_STATUT class='TabHeader'>Statut</a></td>";
if ($pompiers == 1 ) {
    echo "<td align=left width=30 ><a href=personnel.php?order=P_REGIME class=TabHeader >Régime</a></td>";
}
echo "<td align=left width=210><a href=personnel.php?order=S_CODE class=TabHeader >Section</a></td>";

if ($syndicate == 1 ) {
    echo "<td align=left width=150 class='hide_mobile'><a href=personnel.php?order=SERVICE class=TabHeader >Service</a></td>";
}
echo "<td align=center width=100 ><a href=personnel.php?order=P_DATE_ENGAGEMENT class=TabHeader>Entrée</a></td>";
if ( $position <> 'actif')
echo "<td align=center width=80 class='hide_mobile'><a href=personnel.php?order=P_FIN class=TabHeader>Sortie</a></td>";

if ($externes == 1 ) { 
    echo "<td align=center width=250 class='hide_mobile'><a href=personnel.php?order=C_NAME class=TabHeader>Entreprise</a></td>";
}
echo "<td align=center width=80 class='hide_mobile'>Téléphone</td>";
echo "<td align=center width=80 class='hide_mobile'>Téléphone 2</td>";
if ( check_rights($id,2,$filter)) echo "<td class='hide_mobile'>carte</td>";
echo "</tr>";

echo "<input type=hidden name=SendMail id=SendMail value=\"0\" />";
// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result1)) {
    if ( $P_STATUT <> 'SPP' ) $P_REGIME="";
    if ( $P_STATUT == 'SPP' ) $P_STATUT="<span class=red12>".$P_STATUT."</span>";
    else if ( $P_STATUT == 'EXT' ) $P_STATUT="<span class=green12>".$P_STATUT."</span>";
    if ( $C_ID == 0 ) $C_NAME='';

    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor=$mylightcolor;
    }
    else {
        $mycolor="#FFFFFF";
    }
    if ( $P_SEXE == 'F' ) $prcolor='purple';
    else $prcolor=$mydarkcolor;

    if( ($P_PHOTO <> "") and file_exists($trombidir."/".$P_PHOTO)) $img = "<i class='fa fa-user' title='personnel avec une photo'></i>";
    else $img="";
      
    echo "<tr bgcolor=$mycolor 
          onMouseover=\"this.bgColor='yellow'\" 
          onMouseout=\"this.bgColor='$mycolor'\"   
          onclick=\"this.bgColor='#33FF00'\">";
    if ($envoisEmail) {
        echo "      <td align=center>";
        if (($P_EMAIL!='') or  check_rights($id, 30)) {
            echo "<input type=checkbox name=SendMail id=SendMail value=\"$P_ID\" />";
        }
    }
    if ( $syndicate == 1 ) {
        echo "      <td align=center onclick='displaymanager($P_ID)'>$P_PROFESSION</td>";
    }

    if ( $grades == 1 ) {
        $file=$grades_imgdir."/".$P_GRADE.".png";
        if ( file_exists($file) and $G_CATEGORY <> 'PATS' ) $t="<img class='img-max-20' src=".$grades_imgdir."/".$P_GRADE.".png title=\"".$G_DESCRIPTION."\">";
        else $t = "<span title=\"".$G_DESCRIPTION."\">".$P_GRADE."</span>";
        echo "<td align=center onclick='displaymanager($P_ID)'>".$t."</td>";
    }
    if ( $nbsections <> 0 ) 
        echo "<td align=center onclick='displaymanager($P_ID)'>".$P_CODE."</td>";

    echo "    <td align=center onclick='displaymanager($P_ID)'><b>".$img."</b></td>";
    echo "    <td onclick='displaymanager($P_ID)'><b>".strtoupper($P_NOM)."</b></td><td onclick='displaymanager($P_ID)'><font color=$prcolor>".my_ucfirst($P_PRENOM)."</font></td>";
              
    echo "      <td align=left onclick='displaymanager($P_ID)' class='hide_mobile'>$P_STATUT</td>";
    
    if ($pompiers == 1 ) {
        echo "<td align=left onclick='displaymanager($P_ID)'>$P_REGIME</td>";
    }

    echo "      <td align=left onclick='displaymanager($P_ID)'><b><a href=upd_section.php?S_ID=$P_SECTION>$S_CODE</a></b></td>";
    if ($syndicate == 1 ) {
            echo "<td align=left onclick='displaymanager($P_ID)' class='hide_mobile'><small>$SERVICE</small></td>";
    }

    echo "     <td align=center onclick='displaymanager($P_ID)'>$P_DATE_ENGAGEMENT1</td>";
    if ( $position <> 'actif')
        echo"     <td align=center onclick='displaymanager($P_ID)' class='hide_mobile'>$P_END</td>";

    if ($externes == 1 ) {
        echo"     <td align=center onclick='displaymanager($P_ID)' class='hide_mobile'>$C_NAME</td>";
    }
        
    if ( $P_PHONE <> '' ) {
            if (($P_HIDE == 1) and $hide_phone)
                    $P_PHONE="**********";
    }

    echo"     <td align=center onclick='displaymanager($P_ID)' class='small2 hide_mobile'>$P_PHONE</td>
    ";

    if ( $P_PHONE2 <> '' ) {
            if (($P_HIDE == 1) and $hide_phone)
                    $P_PHONE2="**********";
    }
    echo"     <td align=center onclick='displaymanager($P_ID)' class='small2 hide_mobile'>$P_PHONE2</td>";
    if ( check_rights($id,2,$filter))  {  
        echo " <td align=center onclick='displaymanager($P_ID)' class='hide_mobile'>";
        if ( check_rights($id,2,$P_SECTION))
            echo "<a href='vcard.php?pid=$P_ID' ><i class='far fa-address-card fa-lg noprint' title=\"Extraire la Carte de visite\" style='PADDING-LEFT:3px'></i></a>"; 
        echo "</td>";
    }
    echo "</tr>";
}
echo "</table></div>";
if ($envoisEmail) {   
    echo "</form>";
}
writefoot();
?>
