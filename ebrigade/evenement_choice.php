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
include_once ("fonctions_documents.php");
check_all(0);
$id=$_SESSION['id'];
$my_parent_section = get_section_parent($_SESSION['SES_SECTION']);
get_session_parameters();
writehead();

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/evenement_choice.js'></script>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
</HEAD>

<?php

$fixed_company = false;
// cas externe?
if ( $_SESSION['SES_STATUT'] == 'EXT' ) {
    if (! check_rights($id, 41)) {
        check_all(45);
        $company=$_SESSION['SES_COMPANY'];
        $_SESSION['company'] = $company;
        $fixed_company = true;
    }
    if ($company <= 0 ) {
        test_permission_level(41);
    }
}
else{
    test_permission_level(41);
}


$query="select E.TE_CODE, TE.TE_ICON, TE.TE_LIBELLE, E.E_LIEU, EH.EH_ID,
    DATE_FORMAT(EH.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
    EH.EH_DATE_DEBUT as PLAIN_EH_DATE_DEBUT,
    DATE_FORMAT(EH.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, 
    TIME_FORMAT(EH.EH_DEBUT, '%k:%i') as EH_DEBUT, 
    TIME_FORMAT(EH.EH_FIN, '%k:%i') as  EH_FIN, E.E_VISIBLE_INSIDE,
    E.E_NB, E.E_LIBELLE, E.E_CODE, E.E_CLOSED, E.E_OPEN_TO_EXT, E.E_CANCELED, S.S_CODE, E.S_ID,
    E.E_PARENT, E.TAV_ID, EH.EH_DESCRIPTION, tg.EQ_NOM, tg.EQ_ICON, S.S_HIDE, S.S_PARENT, SF.NIV,
    E_COLONNE_RENFORT
    from evenement E left join type_garde tg on E.E_EQUIPE = tg.EQ_ID,
    evenement_horaire EH, type_evenement TE, section S, section_flat SF
    where E.TE_CODE=TE.TE_CODE
    and E.E_CODE=EH.E_CODE
    and SF.S_ID = S.S_ID
    and E.S_ID = S.S_ID";
    
if ( $type_evenement == 'FOR' ) {
    if ( intval($competence) > 0 )
        $query .= " and E.PS_ID=".$competence;
}    
    
if ( ! check_rights($id,9))
$query .= " and E.E_VISIBLE_INSIDE=1";

// recherche par numéro?
$s=0;
$p_calendar="";
if (intval($search) > 0 and strval(intval($search)) == "$search" ) {
     $query2="select count(*) as NB from evenement where E_CODE=".intval($search);
     $result2=mysqli_query($dbc,$query2);
     $row2=@mysqli_fetch_array($result2);
     $s=$row2["NB"]; 
}
if ( $s == 1 ) $query .= "\n and E.E_CODE=".intval($search);
// sinon recherche par critères
else {
if ( $ec_mode == 'MC' ) 
    $query .= "\n and TE.TE_CODE = 'MC'";
else
    $query .= "\n and TE.TE_CODE <> 'MC'";
if ( $type_evenement == 'ALLBUTGARDE' ) 
    $query .= "\n and TE.TE_CODE <> 'GAR'";
else if ( $type_evenement <> 'ALL' and $ec_mode == 'default' ) 
    $query .= "\n and (TE.TE_CODE = '".$type_evenement."' or TE.CEV_CODE = '".$type_evenement."')";

//deb gestion calendriers mutltiples
// récupérer la liste des calendriers perso a afficher
$errCal="";
$cbcalendar="";
$ChxCalendar = (isset($_GET['btGo'])?(isset($_GET['chxCal'])?$_GET['chxCal']:array()):$chxCal);// utilise les données du formulaire ou de la session
if (count($ChxCalendar)==0){ $_SESSION['chxCal']=array(); }
// lire les calendriers persos enregistrés dans la fiche perso
$sqlcal = "select p_calendar from pompier where p_id=$id";
$rescal = mysqli_query($dbc,$sqlcal);
$row2=@mysqli_fetch_array($rescal);
$p_calendar = $row2[0];
if ($p_calendar == '') $_SESSION['chxCal']=array();

// ajouter un calendrier perso
$addCal = (isset($_GET['AddCal'])?$filter:"");
if ($addCal <> "" ){
    $updCal = "";
    if ( count(explode(",",$p_calendar)) < 20 ){ // limite le nombre de calendriers à 20
        $updCal = (in_array($filter,explode(",",$p_calendar))?"$p_calendar":"$p_calendar,$filter");
        $updCal = ((substr($updCal,0,1)==",")?substr($updCal,1):$updCal);
        if (strlen($updCal)<100){ // limite à la taille du champ à 100
            $sqlical="update pompier set p_calendar = '$updCal' where p_id=$id";
            $resical = mysqli_query($dbc,$sqlical);
        }
        else {
            $errCal = "Impossible d'ajouter cette section, contactez l'administrateur";
        }
    }
    else {
        $errCal =  "Impossible d'ajouter une section aux calendriers perso, <br>nombre maximum (20) déjà atteint";
    }
}
// supprimer la sélection des calendriers perso
if (isset($_GET['delCal'])) $delCal=intval($_GET['delCal']);
else $delCal = 0;
if ($delCal > 0 ){
    $updCal = "";
    $pcalendar = explode(",",$p_calendar);
    foreach ($pcalendar as $pcal){
        $updCal .= (in_array($pcal,$ChxCalendar)?"":",$pcal");
    }
    $updCal = ((substr($updCal,0,1)==",")?substr($updCal,1):$updCal);
    $sqlical="update pompier set p_calendar = '$updCal' where p_id=$id";
    $resical = mysqli_query($dbc,$sqlical);
}
// lire les calendriers persos enregistrés dans la fiche perso
if ($delCal > 0 or $addCal <> ""){
    $sqlcal = "select p_calendar from pompier where p_id=$id";
    $rescal = mysqli_query($dbc,$sqlcal);
    $row2=@mysqli_fetch_array($rescal);
    $p_calendar = $row2[0];
}

$cbcalendar="";
if (mysqli_num_rows($rescal)>0 or $addCal <> "") {
    $pcalendar = explode(",",$p_calendar);
    $k=0;
    foreach ($pcalendar as $pcal){
        if ( $k % 5 == 0 and $k > 1 ) $cbcalendar .= "<br>";
        $cbcalendar .= ($pcal<>""?" <input type=\"checkbox\" name=\"chxCal[]\" value=\"$pcal\" ".(in_array($pcal,$ChxCalendar)?" checked":"")."> ".get_section_code($pcal):"");
        $k++;
    }
}

//fin gestion calendriers mutltiples
foreach ($ChxCalendar as $k => $v) $ChxCalendar[$k] = intval($v, 10);

if ( $subsections == 1 )
     $query .= "\n and S.S_ID in (".get_family("$filter").(count($ChxCalendar) > 0 ? ",".implode(",",$ChxCalendar) : "").")";
else 
     $query .= "\n and S.S_ID in ($filter".(count($ChxCalendar) > 0 ? ",".implode(",",$ChxCalendar) : "").")";

if ( $canceled == 0 )
    $query .= "\n and E.E_CANCELED = 0";
    
if ( $renforts == 0 )
    $query .= "\n and ( E.E_PARENT = 0 or E.E_PARENT is null ) ";

if ( $company <> '-1' )
    $query .= "\n and E.C_ID =".$company;
    
if ( $search <> ''){
    $formatted_search="%".$search."%";
    if ( strtolower($search) == 'colonne' )
        $query .= "\n and (E.E_LIBELLE like '$formatted_search' or E.E_LIEU like '$formatted_search' or E.E_COLONNE_RENFORT=1)";
    else if ( strtolower($search) == 'renfort' ) 
        $query .= "\n and (E.E_LIBELLE like '$formatted_search' or E.E_LIEU like '$formatted_search' or E.E_PARENT is not null or E.E_COLONNE_RENFORT=1)";
    else
        $query .= "\n and (E.E_LIBELLE like '$formatted_search' or E.E_LIEU like '$formatted_search')";
}

$tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
$tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

$query .="\n and EH.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
             and EH.EH_DATE_FIN   >= '$year1-$month1-$day1'";
$query .="\n order by EH.EH_DATE_DEBUT, EH.EH_DEBUT";
}

write_debugbox($query);

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

echo "<body><div class='table-responsive'>";
echo "<form name='formf' action='evenement_choice.php'>";

if ( $ec_mode == 'MC' ) $t = "Mains Courantes";
else $t = "Liste des événements";

echo "<div align=center>
<table class='noBorder'>
<tr>
<td><font size=4><b>".$t."</b> <span class='badge' title='$number événements trouvés'>$number</span>
</td>";
if ( $ec_mode == 'default' ) {
    echo "<td>
           <a href=\"evenement_ical.php?section=$filter\"><i class='far fa-calendar-alt fa-2x' title=\"Télécharger le fichier ical de tous ces événements\" ></i></a>";
    echo " <a href='#'><i class='fa fa-print fa-2x' title=\"imprimer\" onclick=\"impression();\"></i></a>";
    echo " <a href='#'><i class='far fa-file-excel fa-2x' style='color:green' id=\"StartExcel\"  title=\"Excel\" onclick=\"window.open('evenement_list_xls.php');\" ></i></a>";
    echo " <a href=pdf_bulletin.php?date=".$dtdb."&date2=".$dtfn."&section=".$filter." target='_blank'
                title=\"Afficher le bulletin de renseignements du ".$dtdb." au ".$dtfn."\">
                <i class='far fa-file-pdf fa-2x' style='color:red;'></i></a>";
    if ( $geolocalize_enabled == 1 and check_rights($id,76)) 
        echo " <a href=gmaps_evenement.php ><i class='fa fa-map-marker-alt fa-2x' title='Localiser les événements en cours sur une carte'></i></a>";
    echo "</td></tr></table>";
}
echo "<p><table class='noBorder'><tr>";


// choix type événement
if ( $ec_mode == 'default' )  {
    echo "<td align=right> Activité </td>";
    echo "<td align=left colspan=2><select id='type' name='type' 
     onchange=\"redirect(document.formf.type.options[document.formf.type.selectedIndex].value, '$filter','$subsections', '$dtdb', '$dtfn', '$canceled','$company')\">";
   
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
}

// si formation, choix compétence
if ( $type_evenement == 'FOR' ) {
    echo "<tr id='rowforpour' >
            <td align=right>Formation pour</td>";
    echo "<td align=left colspan=2>
    <select id='ps' name='ps' title='saisir ici le type de compétence ou le diplôme obtenu grâce à cette formation' 
    style='max-width: 380px;'
    onchange=\"redirect('$type_evenement', '$filter','$subsections', '$dtdb', '$dtfn', '$canceled','$company');\">";
    if ( intval($competence) == 0 ) $selected="selected"; else $selected="";
    echo "<option value='0' $selected class='type'>toutes les compétences</option>\n";  
    $query2="select PS_ID, TYPE, DESCRIPTION from poste 
            where PS_FORMATION=1 or PS_ID =".intval($competence)."
            order by TYPE asc";
    $result2=mysqli_query($dbc,$query2);

    while ($row2=@mysqli_fetch_array($result2)) {
        $_PS_ID=$row2["PS_ID"];
        $_TYPE=$row2["TYPE"];
        $_DESCRIPTION=$row2["DESCRIPTION"];     
        if ( $competence == $_PS_ID ) $selected="selected"; else $selected="";
        // cas particulier, ne plus proposer BNIS, numero 82
        if ($_PS_ID <> 82 or $_TYPE <> 'BNIS' or $_PS_ID == $competence)
        echo "<option value=".$_PS_ID." $selected class='type'>".$_TYPE." - ".$_DESCRIPTION."</option>\n";
    }
    echo "</select></td>";       
    echo "</tr>";
}
else 
    echo "<input type='hidden' name ='ps' id='ps' value = '".intval($competence)."'>";

// choix section
 echo "<tr><td align=right >";
  
if ( $_SESSION['SES_STATUT'] == 'EXT' ) {
      echo "Organisateur";
}
else {
      echo choice_section_order('evenement_choice.php');
}
echo " </td>";

// choix section
echo "<td align=left colspan=2>
      <select id='filter' name='filter' 
     title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
     onchange=\"redirect('$type_evenement', document.formf.filter.options[document.formf.filter.selectedIndex].value, 
                 '$subsections', '$dtdb', '$dtfn', '$canceled', '-1', '$renforts')\">";

// pour personnel externe on limite géographiquement la visibilité
if ( $_SESSION['SES_STATUT'] == 'EXT' ) {
      $_level=get_level("$mysection");
      echo "<option value='$mysection' $class >".
                  get_section_code("$mysection")." - ".get_section_name("$mysection")."</option>";
    display_children2($mysection, $_level + 1, $filter, $nbmaxlevels);
    echo "</select>";
}
else  {
      display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";
    if (check_rights($id,26) and $filter > 0 ) {    
        echo " <input type='hidden' name='AddCal' value='+'>
            <a href='#' style='height:16px; padding:1px;'
            onclick='document.formf.submit();' title='Ajouter à mes calendriers favoris'>
            <span class='badge' style='background-color:green;'>+</span></a>";
    }
}
echo "</td></tr>";
if ($p_calendar <> '') {
    echo "<tr><td align=right>Favoris</td>";
    echo "<td align=left colspan=2>".$cbcalendar.(($errCal<>"")?"<div class='alert alert-danger' role='alert'>".$errCal."</div>":"");
    echo " <input type='hidden' name='delCal' id='delCal' value='0'>
            <a href='#' style='height:16px; padding:1px;'
             title='Supprimer la sélection des calendriers favoris'><span class='badge' style='background-color:red;' onclick='return DelCalConfirm();'  name='delCal'>
            <b>-</b></span></a>";
    echo "</td></tr>";
}

echo "<tr><td align=right >inclure</td>";
echo "<td align=left colspan=2>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<input type='checkbox' name='subsections' id='subsections' value='1' $checked title='cocher pour afficher aussi les événements organisés par les $sous_sections'
       onClick=\"redirect2('$type_evenement', '$filter', this , '$dtdb', '$dtfn', '$canceled', '$company','$renforts')\"/>
       <label for='subsections'>les $sous_sections</label>";
}
else echo "<input type=hidden name=subsections id=subsections value='0' >";

// y compris les annulés
if ($canceled == 1 ) $checked='checked';
else $checked='';
echo " <input type='checkbox' name='canceled' id='canceled' value='1' $checked title='cocher pour afficher aussi les annulés'
       onClick=\"redirect3('$type_evenement', '$filter', '$subsections' , '$dtdb', '$dtfn', this, '$company','$renforts')\" />
       <label for='canceled'>les annulés</label> ";

// y compris les renforts
if ( $ec_mode == 'default' ) {
    if ($renforts == 1 ) $checked='checked';
    else $checked='';
    echo " <input type='checkbox' name='renforts' id='renforts' value='1' $checked title='cocher pour afficher aussi les ".$renfort_label."s'
       onClick=\"redirect4('$type_evenement', '$filter', '$subsections' , '$dtdb', '$dtfn', '$canceled', '$company', this)\" />
       <label for='renforts'>les ".$renfort_label."s</label>";
}
echo "</td></tr>";

// filtre entreprise
if ( $externes == 1 and $ec_mode == 'default' ) {
    if ( $fixed_company ) $disabled='disabled';
    else $disabled='';
    echo "<tr><td align=right>Pour </td>";
    echo "<td align=left colspan=2>
      <select id='company' name='company' $disabled style='max-width:320px;font-size:12px;'
     title=\"Evénements organisés pour le compte d'une entreprise\"
     onchange=\"redirect('$type_evenement', '$filter', '$subsections', '$dtdb', '$dtfn', '$canceled',
                 document.formf.company.options[document.formf.company.selectedIndex].value ,'$renforts')\">";
                
    if ( $company == -1 ) $selected ='selected'; else $selected='';
    echo "<option value='-1' $selected>... Pas de filtre par entreprise ...</option>";
    echo companychoice($filter,$company,$includeparticulier=false,$category='EXT');
    echo "</select></td></tr>";
}
else echo "<input type='hidden' id='company' name='company' value='-1'>";

// Choix Dates
echo "<tr><td align=right >Début </td><td align=left >
    <input name='dtdb' id='dtdb' placeholder='JJ-MM-AAAA' size='10' value=".$dtdb." class='datepicker' data-provide='datepicker' onchange='checkDate2(document.formf.dtdb)'>
    </td>";

if ( check_rights($id, 15)) {
    if ( $ec_mode=='MC' ) $param = "&ec_mode=MC";
    else $param="";
    echo "<td rowspan=2><a class='btn btn-default' href='#' title='Ajouter un événement' onclick=\"bouton_redirect('evenement_edit.php?action=create".$param."');\">
                <i class='far fa-calendar-plus' ></i> Ajouter</a></td>";
}
else echo "<td rowspan=2></td>";

echo "</tr>";

echo "<tr><td align=right >Fin </td><td align=left >
    <input name='dtfn' id='dtfn' placeholder='JJ-MM-AAAA' size='10' value=".$dtfn." class='datepicker' data-provide='datepicker' onchange='checkDate2(document.formf.dtfn)'>
    </td>";

echo "</tr><tr>
<td align=right >Recherche</td>
<td align=left colspan=2>";
echo "<div class='noprint'><input  type=\"text\" name=\"search\" value=\"".preg_replace("/\%/","",$search)."\" size=\"20\" alt=\"\" title=\"Saisissez un mot recherché (dans le libellé ou le lieu) ou un numéro d'événement\"/>";
echo " <input type='submit' class='btn btn-default' name='btGo' value='go'> ";

if ( $search <> "" ) {
      echo " <a href=evenement_choice.php?search= title='effacer le critère de recherche'><i class='fa fa-eraser fa-lg' style='color:pink;' ></i></a>";
}
echo "</div></td>";
echo "</tr></table>";

echo "<tr><td colspan=3>";
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

echo "</td></tr></table>";
echo "</form>";

if ( $number > 0 ) {
    $organisateur="<font size=1> (organisateur)</font>";

    if ( $ec_mode == 'MC' )  {
        $tw=320;
        $dtw=250;
        $br="";
    }
    else {
        $tw=420;
        $dtw=220;
        $br=" ";
    }
    echo "<p><table cellspacing=0 border=0>";
    echo "<tr class=TabHeader>
            <td width=".$tw." align=center>Activité".$organisateur."</td>
          ";
    if ($type_evenement == 'DPS')
        echo "<td width=60 align=center class='hide_mobile'>DPS</td>";
    echo "<td width=260 align=left>Lieu</td>
            <td width=".$dtw." align=left>Date</td>
            <td width=80 align=left>Horaire</td>
          <td width=50 align=left class='hide_mobile'>N° Code</td>";
    if ( $ec_mode == 'MC' ) 
        echo "<td width=60 align=left>Messages</td>
            <td width=100 align=left>Dernier</td>";
    else 
        echo "
          <td width=50 align=left>Ouvert</td>
            <td width=120 align=left class='hide_mobile'>Requis</td>
          <td width=60 align=left >Inscrits</td>";
    if( check_rights($id, 29) and  $ec_mode == 'default' )
         echo "
            <td width=30 align=left class='hide_mobile'>Fac.</td>";
    echo "</tr>";

    $i=0;
    while (custom_fetch_array($result)) {
        $size=strlen($renfort_label);
        if ( intval($E_PARENT) > 0 and strtolower(substr($E_LIBELLE,0,$size)) <> $renfort_label ) $E_LIBELLE = ucfirst($renfort_label).' '.$E_LIBELLE;
        if ( $E_COLONNE_RENFORT > 0 and strtolower(substr($E_LIBELLE,0,7)) <> 'colonne' ) $E_LIBELLE = 'Colonne de renfort '.$E_LIBELLE;
        if ( $E_VISIBLE_INSIDE == 0 ) $E_LIBELLE .= " <i class='fa fa-exclamation-triangle' style='color:orange;' title='ATTENTION événement caché, seules les personnes ayant la permission n°9 peuvent le voir'></i>";

        $i=$i+1;
        if ( $i%2 == 0 ) {
            $mycolor=$mylightcolor;
        }
        else {
            $mycolor="#FFFFFF";
        }

        $tmp=explode ( "-",$EH_DATE_DEBUT); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
        $date1=mktime(0,0,0,$month1,$day1,$year1);
        $ladate=date_fran($month1, $day1 ,$year1)." ".moislettres($month1);

        if ( $EH_DATE_FIN <> '' and $EH_DATE_FIN <> $EH_DATE_DEBUT) {
            $tmp=explode ( "-",$EH_DATE_FIN); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
            $date1=mktime(0,0,0,$month1,$day1,$year1);
            $ladate=$ladate." au $br".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1;
        }
        else $ladate=$ladate." ".$year1;
        //$timenow = time();
        //if ($timenow > $date1) $E_CLOSED =1;

        $attached="";
        $f_arr = array(); $f = 0;
        $mypath=$filesdir."/files/".$E_CODE;
        if (is_dir($mypath)) {
            $dir=opendir($mypath); 
            while ($file = readdir ($dir)) { 
                if ($file != "." && $file != ".." and (file_extension($file) <> "db")) {
                    $f_arr[$f++] = $file;
                }
            } 
            closedir($dir);

            if (count( $f_arr ) > 0) {
                sort( $f_arr ); reset( $f_arr );
                for( $p=0; $p < count( $f_arr ); $p++ ) {
                    if ( $p == 5 ) $attached .= "...";
                    else if ( $p < 5 ) {
                        $myimg=get_smaller_icon(file_extension($f_arr[$p]));
                        $attached=" <span title='".$f_arr[$p]."' >".$myimg."</span> ".$attached;     
                    }
                }
            }
        }

        $S_DESCRIPTION=get_section_name($S_ID);
        $organisateur="<font size=1 >(".$S_CODE.")</font>";

        if ( $E_CANCELED == 1 ) {
            $color='red';
            $tt='événement annulé';
        }
        elseif ( $E_CLOSED == 1 ) {
            $color='orange';
            $tt='inscriptions fermées';
        }
        else {
            $color='green';
            $tt='inscriptions ouvertes';
        }
        // si inscription interdite pour les externes alors on vérifie si l'agent fait partie d'une sous section 
        //ou d'un niveau plusélevé : auquel cas on l'autorise.
          if ($E_OPEN_TO_EXT == 0  and  $mysection <> $S_ID ) {
               if ( get_section_parent("$mysection") <> get_section_parent("$S_ID")) {
                   $list = preg_split('/,/' , get_family_up("$S_ID"));
                   if (! in_array($mysection,$list)) {
                       $list = preg_split('/,/' , get_family("$S_ID"));  
                       if (! in_array($mysection,$list)){
                        $color='orange';
                        $tt='inscriptions interdites pour personnes extérieures';
                    }
                }
              }
              else {// je peux inscrire sur les antennes voisines mais pas les départements voisins
                if ( get_level("$mysection") + 2 <= $nbmaxlevels ){
                    $color='orange';
                    $tt='inscriptions interdites pour personnes extérieures';
                }
            }
        }
        $query2="select count(1) as NB from evenement_horaire where E_CODE=".$E_CODE;
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $nbsessions=$row2["NB"];      
      
        // cas où on a les permissions de voir l'événement
        if (     $S_HIDE == 0
            or $E_OPEN_TO_EXT == 1 
            or check_rights($id,41, $S_ID) 
            or ($S_PARENT == $my_parent_section and $NIV == $nbmaxlevels - 1 ) 
            or $S_ID == $my_parent_section ) {
            $query2="select count(1) as NP from evenement_participation ep, evenement e
               where e.E_CODE=".$E_CODE."
              and ep.E_CODE=e.E_CODE
              and ep.EP_ABSENT=0
              and e.E_CANCELED=0
              and ep.EH_ID=".$EH_ID;
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $NP=$row2["NP"];
      
            $query2="select count(distinct P_ID) as NP from evenement_participation ep, evenement e, evenement_horaire eh
               where e.E_PARENT=".$E_CODE."
              and ep.E_CODE=e.E_CODE
              and ep.EP_ABSENT=0
              and e.E_CANCELED=0
              and e.E_CODE=eh.E_CODE
              and ep.E_CODE=eh.E_CODE
              and ep.EH_ID=eh.EH_ID";
            if ( $nbsessions > 1 ) 
                $query2 .= " and eh.EH_DATE_DEBUT='".$PLAIN_EH_DATE_DEBUT."' 
                         and eh.EH_DEBUT = '".$EH_DEBUT."'";
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $NP=$row2["NP"] + $NP;
      
            // compétences requises
            $querym="select ec.PS_ID, ec.NB, p.TYPE, p.DESCRIPTION, p.EQ_ID 
            from evenement_competences ec
            left join poste p on ec.PS_ID = p.PS_ID
            where ec.E_CODE=".$E_CODE." and ec.EH_ID in(0,".$EH_ID.") and ec.PS_ID > 0
            order by ec.EH_ID, p.EQ_ID, p.PS_ORDER";
            $resultm=mysqli_query($dbc,$querym);
            $nbm=mysqli_num_rows($resultm);
            $requis="";
            while ( $rowm=mysqli_fetch_array($resultm) ) {
                $poste=$rowm["PS_ID"];
                $type=$rowm["TYPE"];
                $nb=$rowm["NB"];
                $desc=$nb." ".$rowm["DESCRIPTION"]." requis, ";
                $inscrits=get_nb_competences($E_CODE, $EH_ID, $poste);
                if ($inscrits >= $nb ) $col=$blue;
                else $col=$red;
                if ( $inscrits < 2 ) $desc .= "\n".$inscrits." inscrit ayant cette compétence valide.";
                else $desc .= "\n".$inscrits." inscrits ayant cette compétence valide.";
                $requis .= " <font size=1 color=$col>".$nb."</font> <a title=\"".$desc."\"><font size=1 color=$col>".$type."</font></a>,";
            }
            $requis = rtrim($requis,',');
            if ( $E_NB == 0 ) {
                if ( intval($NP) == 0 )  $color1 ='red';
                else $color1 ='green';
                $cmt = "<span class='badge' style='background-color:$color1;' >".$NP."</span>";
            }
            else {
                if ( intval($NP) == 0 )  $color1 ='red';
                else if ( intval($NP) == intval($E_NB)) $color1 ='green';
                else if ( intval($NP) > intval($E_NB))  $color1 ='blue';
                else $color1 ='orange';
                $cmt = "<span class='badge' style='background-color:$color1;'>".$NP." / ".$E_NB."</span>";
            }
        }
        // cas où on n'a pas les permissions de voir l'événement
        else {
            $requis="";
            $NP="?";
            $cmt = "<span class='badge' style='background-color:red;' title=\"Vous n'avez pas les permissions pour voir le détail de cet événement\">".$NP."</span>";
        }
      
        if ( $EQ_ICON <> "" ) $b1="<img  src=".$EQ_ICON." height=16 title=\"".$EQ_NOM."\"  class='img-max-20'>";
        else $b1="<img src=images/evenements/".$TE_ICON." height=16  title='".$TE_LIBELLE."' class='img-max-20'>";
        $query2="select count(distinct e.E_CODE) as NR from evenement e, evenement_horaire eh
                where e.E_PARENT=".$E_CODE." 
                and e.E_CODE = eh.E_CODE";
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $NR=$row2["NR"];

        $b2="";
        if ( $NR > 0 ) $b2 .= "<i class='fa fa-plus-circle' style='color:green;' title='$NR ".$renfort_label."s' ></i>";
        if ( $NR > 1 ) $b2 .= " <b><font color=green>x$NR</font></b>";

        if ( $nbsessions > 1 )  {
            if ( $EH_DESCRIPTION <> "" ) $dp = " - <i>".$EH_DESCRIPTION."</i>";
            else $dp="";
            $session="<small> partie n°".$EH_ID."/".$nbsessions.$dp."</small>";
        }
        else $session="";
      
        $E_LIBELLE=str_replace('Colonne de renfort','<font color='.$purple.'>Colonne de renfort</font>',$E_LIBELLE);
        $E_LIBELLE=str_replace('Participation','<font color=green>Participation</font>',$E_LIBELLE);
        $E_LIBELLE=str_replace('Renfort','<font color=green>Renfort</font>',$E_LIBELLE);
        
        echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; bouton_redirect('evenement_display.php?evenement=".$E_CODE."&from=choice');\" >
            <td align=left>".$b1."<b> ".$E_LIBELLE.$session."</b> ".$organisateur." ".$b2." ".$attached."</td>";
        if ( $type_evenement =='DPS' and $TAV_ID <> "") {
            $query2="select TA_SHORT from type_agrement_valeur 
                    where TA_CODE = 'D' and TAV_ID=".$TAV_ID;
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $TA_SHORT=$row2["TA_SHORT"];
            echo "<td align=left class='hide_mobile'>".$TA_SHORT."</td>";
        }
        echo "<td align=left><small>".$E_LIEU."</small></td>
            <td align=left><small>".$ladate."</small></td>
            <td align=left><small>".$EH_DEBUT."-".$EH_FIN."</small></td>
            <td align=left class='hide_mobile'><small><a href=evenement_display.php?evenement=".$E_CODE." title='ceci est le N° code $application_title de cet événement'>".$E_CODE."</a></small></td>";
        if ( $ec_mode == 'MC' ) {
            $query2="select count(1), date_format(max(EL_DATE_ADD),'%d-%m-%Y %H:%i'), date_format(max(EL_DATE_ADD),'%d-%m-%Y') 
                    from evenement_log where E_CODE=".$E_CODE;
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $nb=intval($row2[0]);
            if ( $row2[2] == date('d-m-Y')) $new=" <i class='fa fa-star' style='color:orange'  title=\"Dernier message ajouté aujourd'hui\" ></i>";
            else $new="";
            if ( $nb > 0 ) $latest=$row2[1];
            else $latest ='';
            echo "<td align=left>".$nb."</td>
                <td align=left class=small>".$latest.$new."</td>";
        }
        else {
            echo " <td align=center><i class='fa fa-circle' style='color:".$color.";' title=\"".$tt."\"></i></td>
                <td align=left class='hide_mobile'>".$requis."</td>
                <td align=left>".$cmt."</td>";
        }
        if( check_rights($id, 29) and  $ec_mode == 'default'  ) {
            if (check_rights($id, 29, "$S_ID")) 
                $myfact=get_etat_facturation($E_CODE, "ico");
            else 
                $myfact="";
            echo "<td align=center class='hide_mobile'><a href=evenement_facturation.php?evenement=".$E_CODE.">".$myfact."</a></td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}
else {
     echo "<p><b>Aucun élément trouvé ne correspond aux critères choisis</b>";
}
echo "<p></div>";
writefoot();

?>
