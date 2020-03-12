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
check_all(44);
$id = $_SESSION['id'];
get_session_parameters();
writehead();

if ( ! isset($_GET["filter"])) {
    if ( $syndicate == 1 ) $filter=1;
    else if ( $nbsections > 0 ) $filter=0;
}

if ( ! check_rights($id,40)) {
    $family_up=explode(",", get_family_up($_SESSION['SES_SECTION']));
    if ( ! in_array($filter, $family_up) )
        test_permission_level(44);
}

$possibleorders= array('date','file','security','type','author','extension');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='date';

if ( isset($_GET["page"])) $status="documents";
else if ( isset($_GET['status']) ) {
    $status=$_GET['status'];
    $_SESSION['status']=$status;
} 
else if ( isset($_SESSION['status']) ) $status=$_SESSION['status'];
else $status='infos';

if ( isset($_GET["from"]))$from=$_GET["from"];
else $from="default";

if (isset($_GET['search'])) $search=secure_input($dbc,$_GET["search"]);
else $search="";

if ( $search <> "" ) $dossier=0;

// use $yeardoc session value, but restrict to current
$defaultyear=date("Y");
if ( $yeardoc <> 'all' and $yeardoc > $defaultyear ) {
    $yeardoc = $defaultyear;
    $_SESSION['yeardoc'] = $yeardoc;
}

if (check_rights($id, 47, "$filter")) $granted_documentation=true;
else $granted_documentation=false;

?>
<style type="text/css">
textarea{
FONT-SIZE: 10pt; 
FONT-FAMILY: Arial;
width:90%;
}
</style>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/documents.js'></script>
<?php
echo "</head>";

$title= "Documents";
if ( $syndicate == 1 ) $title .= " ".get_section_code($filter);

echo "<div align=center class='table-responsive'><table class='noBorder'>
      <tr><td width = 60 ><i class='fa fa-cloud-download-alt fa-2x'></i></td><td>
      <font size=4><b>".$title."</b></font></td></tr></table>";

echo "<p><form name='formf' action='documents.php'>
        <table class='noBorder'>";      
      
//=====================================================================
// documents
//=====================================================================
    
if ( $nbsections == 0 and $syndicate == 0) {
     echo "<tr>
            <td>Section</td>
            <td align=left>";
     echo "<select id='section' name='section' onchange=\"javascript:filterdoc(this.value,'".$td."','".$yeardoc."');\">";

    $level=get_level($filter);
    if ( $level == 0 ) $mycolor=$myothercolor;
    elseif ( $level == 1 ) $mycolor=$my2darkcolor;
    elseif ( $level == 2 ) $mycolor=$my2lightcolor;
    elseif ( $level == 3 ) $mycolor=$mylightcolor;
    else $mycolor='white';
    $class="style='background: $mycolor;'";
    display_children2(-1, 0, $filter, $nbmaxlevels,$sectionorder);

    echo "</select></td> ";
    echo "</tr>";
}    
else
    echo " <input type='hidden' name='section' value='$filter'>";         

echo "<div align=center id='documents'>";

$query="select TD_CODE, TD_LIBELLE, TD_SYNDICATE, TD_SECURITY  from type_document where TD_SYNDICATE = ".$syndicate;
$query .=" order by TD_LIBELLE";
$result=mysqli_query($dbc,$query);
        
echo "<tr>
          <td><i>Années</i></td>
            <td align=left>
            <select id='yeardoc' name='yeardoc' onchange=\"javascript:filterdoc('".$filter."','".$td."',this.value);\">";
        
if ( $yeardoc == 'all') $selected='selected'; else $selected='';
echo "        <option value='all' $selected>Toutes</option>";
            for ($k=0; $k < 4; $k++){
                $y = $defaultyear - $k;
                if ( $yeardoc == $y) $selected='selected'; else $selected='';
                echo "<option value='".$y."' $selected>".$y."</option>";
            }
              
echo "    </select></td></tr><tr>
            <td> <i>Type</i></td>
            <td align=left>
          <select id='td' name='td' onchange=\"javascript:filterdoc('".$filter."',this.value,'".$yeardoc."');\">";
echo "<option value='ALL'>Tous types</option>";
while ($row=@mysqli_fetch_array($result)) {
    $TD_CODE=$row["TD_CODE"];
    $TD_LIBELLE=$row["TD_LIBELLE"];
    $TD_SECURITY=intval($row["TD_SECURITY"]);
    if ( check_rights($id, $TD_SECURITY)) {
        if ( $td == $TD_CODE ) $selected = 'selected';
        else $selected='';
        echo "<option value='$TD_CODE' $selected>$TD_LIBELLE</option>";
    }
}
echo "</select></td></tr><tr><td>
     <i>Recherche</i></td><td>
     <input type=hidden name='filter' value='".$filter."'>
     <input type=hidden name='status' value='documents'>
     <input type=\"text\" name=\"search\" 
      value=\"".preg_replace("/\%/","",$search)."\" size=\"20\" 
      title=\"Recherche dans le nom des fichiers\"/> <input type='submit'  class='btn btn-default' value='go'>";

if ( $search <> "" ) {
      echo " <a href=documents.php?order=file&status=documents&filter=".$filter."&dossier=0 title='effacer critère de recherche'><i class='fa fa-eraser fa-lg' style='color:pink;' title='supprimer ce critère de recherche'</i></a>";
}
echo "</td></tr></table></form>";

$f = 0;
$id_arr = array();
$f_arr = array();
$fo_arr = array();
$cb_arr = array();
$d_arr = array();
$y_arr = array();
$t_arr = array();
$t_lib_arr = array();
$s_arr = array();
$s_lib_arr = array();
$ext_arr = array();
$is_folder= array();
$df_arr = array();

$mypath=$filesdir."/files_section/".$filter;
            
// les documents  
$query="select d.D_ID,d.S_ID,d.D_NAME,d.TD_CODE,d.DS_ID, td.TD_LIBELLE, td.TD_SECURITY,
        ds.DS_LIBELLE, ds.F_ID, d.D_CREATED_BY, date_format(d.D_CREATED_DATE,'%Y-%m-%d %H-%i') D_CREATED_DATE,
        YEAR(d.D_CREATED_DATE) D_YEAR, d.DF_ID
        from document d, document_security ds, type_document td
        where td.TD_CODE=d.TD_CODE
        and d.DS_ID=ds.DS_ID
        and d.S_ID=".$filter."
        and d.P_ID = 0 and d.V_ID = 0 and d.NF_ID = 0 and d.E_CODE = 0";
        if ( $search <> "" ) $query .=" and d.D_NAME like '%".$search."%'";
        else $query .=" and d.DF_ID = ".$dossier;
        if ( $td <> 'ALL' ) $query .=" and d.TD_CODE = '".$td."'";
        if ( $yeardoc <> 'all' ) $query .=" and YEAR(d.D_CREATED_DATE) = '".$yeardoc."'";
        
$result=mysqli_query($dbc,$query);

$nb=mysqli_num_rows($result);
while ( $row=@mysqli_fetch_array($result)) {
    if (($row["F_ID"] == 0
        or check_rights($id, $row["F_ID"], "$filter")
        or check_rights($id, 47, "$filter")
        or ($_SESSION['SES_PARENT'] == $filter and check_rights($id, 47, $_SESSION['SES_SECTION']))
        or ($row["F_ID"]== 52 and $_SESSION['SES_PARENT'] == $filter and check_rights($id, 52, $_SESSION['SES_SECTION']))
        or ( $syndicate == 1 and $row["F_ID"]== 52 and ($_SESSION['SES_PARENT'] = $filter or $_SESSION['SES_SECTION'] == $filter ))
        or ( $syndicate == 1 and $row["F_ID"]== 16 and check_rights($id, 16 ))
        or $row["D_CREATED_BY"] == $id)
        // Et aussi permission globale sur le type de document
        and check_rights($id, $row["TD_SECURITY"]))
    {
        $ext_arr[$f] = strtolower(file_extension($row["D_NAME"]));
        $f_arr[$f] = $row["D_NAME"];
        $y_arr[$f] = $row["D_YEAR"];
        $id_arr[$f] = $row["D_ID"];
        $t_arr[$f] = $row["TD_CODE"];
        $s_arr[$f] = $row["DS_ID"];
        $t_lib_arr[$f] = $row["TD_LIBELLE"];
        $s_lib_arr[$f] =$row["DS_LIBELLE"];
        $fo_arr[$f] = $row["F_ID"];
        $cb_arr[$f] = $row["D_CREATED_BY"];
        $d_arr[$f] = $row["D_CREATED_DATE"];
        $is_folder[$f] = 0;
        $df_arr[$f] = $row["DF_ID"];
        $f++;
    }
}

// les dossiers 
$query="select df.DF_ID, df.S_ID, df.DF_NAME, df.TD_CODE, 0 DS_ID, td.TD_LIBELLE, td.TD_SECURITY,
            '' DS_LIBELLE, 0 F_ID, df.DF_CREATED_BY, date_format(df.DF_CREATED_DATE,'%Y-%m-%d %H-%i') DF_CREATED_DATE,
            YEAR(df.DF_CREATED_DATE) DF_YEAR, df.DF_PARENT
            from document_folder df left join  type_document td on td.TD_CODE = df.TD_CODE 
            where df.S_ID=".$filter;
if ( $search <> "" ) $query .=" and df.DF_NAME like '%".$search."%'";
else $query .=" and df.DF_PARENT = ".$dossier;
if ( $td <> 'ALL' ) $query .=" and df.TD_CODE = '".$td."'";
if ( $yeardoc <> 'all' ) $query .=" and YEAR(df.DF_CREATED_DATE) = '".$yeardoc."'";    
$result=mysqli_query($dbc,$query);
$nb=mysqli_num_rows($result);

while ( $row=@mysqli_fetch_array($result)) {
    $ext_arr[$f] = '_folder';
    $id_arr[$f] = $row["DF_ID"];
    $y_arr[$f] = $row["DF_YEAR"];
    $f_arr[$f] = $row["DF_NAME"];
    $t_arr[$f] = $row["TD_CODE"];
    $s_arr[$f] = $row["DS_ID"];
    $t_lib_arr[$f] = $row["TD_LIBELLE"];
    $s_lib_arr[$f] =$row["DS_LIBELLE"];
    $fo_arr[$f] = $row["F_ID"];
    $cb_arr[$f] = $row["DF_CREATED_BY"];
    $d_arr[$f] = $row["DF_CREATED_DATE"];
    $is_folder[$f] = 1;
    $df_arr[$f] = $row["DF_PARENT"];
    $f++;
}


if ( $order == 'date' ) 
    array_multisort($is_folder, SORT_DESC, $y_arr, SORT_DESC, $d_arr, SORT_DESC, $f_arr, $t_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr,$df_arr);
else if ( $order == 'file' ) 
    array_multisort($is_folder, SORT_DESC, $f_arr, SORT_ASC, $y_arr, $d_arr, $t_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr,$df_arr);
else if ( $order == 'type' ) 
    array_multisort($is_folder, SORT_DESC, $t_arr, SORT_ASC, $f_arr, $y_arr, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr,$df_arr);
else if ( $order == 'security' ) 
    array_multisort($is_folder, SORT_DESC, $s_arr, SORT_DESC, $f_arr, $y_arr, $d_arr, $t_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr,$df_arr);
else if ( $order == 'author' ) 
    array_multisort($is_folder, SORT_DESC, $cb_arr, SORT_ASC, $f_arr, $y_arr, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$t_arr,$ext_arr,$id_arr,$df_arr);
else if ( $order == 'extension' ) 
    array_multisort($is_folder, SORT_DESC, $ext_arr,$f_arr, $cb_arr, SORT_DESC, $y_arr, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$t_arr,$id_arr,$df_arr);

    
    
$queryt="select TD_CODE, TD_LIBELLE, TD_SECURITY from type_document where TD_SYNDICATE=".$syndicate." order by TD_LIBELLE";
$querys="select DS_ID, DS_LIBELLE,F_ID from document_security";
$queryr="select df.DF_ID, df.DF_NAME, df.DF_PARENT, dfp.DF_NAME DFP_NAME, td.TD_SECURITY, td.TD_LIBELLE
        from type_document td,
        document_folder df left join document_folder dfp on dfp.DF_ID = df.DF_PARENT
        where td.TD_CODE = df.TD_CODE
        and td.TD_SYNDICATE = ".$syndicate."
        and df.S_ID=".$filter."
        order by DFP_NAME, DF_NAME";
       
$number = count( $f_arr );
// ------------------------------------
// pagination
// ------------------------------------
$parent=0;

if ( $number  > 0 ) {

require_once('paginator.class.php');
$pages = new Paginator;
$pages->items_total = $number;
$pages->mid_range = 9;
$pages->paginate();
if ( $number > 10 ) {
    echo $pages->display_pages();
    echo $pages->display_jump_menu(); 
    echo $pages->display_items_per_page(); 
}
if ( $pages->items_per_page == 'All' ) $pages->items_per_page = 1000;
    
if ( $dossier > 0 ) {
    $dn = " <span class=TabHeader> / </span>
            <a href=documents.php?order=file&status=documents&filter=".$filter."&dossier=".$dossier."  class=TabHeader
            title='Vous êtes dans ce dossier qui contient $number documents ou dossiers'>".get_folder_name($dossier)."</a>";
    $parent=get_parent_folder($dossier, 1);
    if ( $parent > 0 ) {
        $dn = "<span class=TabHeader> / </span>
            <a href=documents.php?order=file&status=documents&filter=".$filter."&dossier=".$parent."  class=TabHeader
            title='ouvrir ce dossier'>".get_folder_name($parent)."</a>".$dn;
    }
}
else $dn="";

echo "<p><table cellspacing=0 border=0>";
echo "<tr class=TabHeader>
           <td width=20>
                 <a href=documents.php?order=extension&status=documents&filter=".$filter." class=TabHeader
                 title='trier par extension'>Ext</a></td>
             <td width=380 class=TabHeader>
                 <a href=documents.php?order=file&status=documents&filter=".$filter."&dossier=0  class=TabHeader
                title='Ouvrir le dossier racine'> Doc </a>".$dn." ($number)</span></td>
           ";
if ( $document_security == 1 ) 
    echo "<td width=30 align=left><a href=documents.php?order=security&status=documents&filter=".$filter." class=TabHeader>
            <i class='fa fa-lock' title='trier par sécurité'></i></a></td>";
echo "<td width=170>
            <a href=documents.php?order=type&status=documents&filter=".$filter." class=TabHeader>Type</a></td>
         <td width=180>
             <a href=documents.php?order=author&status=documents&filter=".$filter." class=TabHeader>Auteur</a></td>
         <td width=100>
             <a href=documents.php?order=date&status=documents&filter=".$filter." class=TabHeader>Date</a></td>
         <td width=20 class=TabHeader>Suppr.</td>
      </tr>";
           
$low=$pages->low;
$high= $pages->items_per_page +  $low;
if ( $high > $number ) $high=$number;
for( $i=$low ; $i < $high  ; $i++ ) {
    // extension
    if ( $is_folder[$i] ) {
        $myimg="<i class='far fa-folder-open fa-lg' title='Ouvrir ce dossier' style='padding-left:2px;'></i>";
        echo "<td bgcolor=$mylightcolor align=left>
        <a href=documents.php?status=documents&filter=".$filter."&dossier=".$id_arr[$i].">".$myimg." </a>
        </td>";
    }
    else {
        $file_ext = strtolower(substr($f_arr[$i],strrpos($f_arr[$i],".")));
        if ( $file_ext == '.pdf' ) $target="target='_blank'";
        else $target="";
        $myimg=get_smaller_icon(file_extension($f_arr[$i]));

        echo "<td bgcolor=$mylightcolor align=left style='padding-left:4px'>
                <a href=showfile.php?section=".$filter."&dossier=".$df_arr[$i]."&file=".$f_arr[$i]." $target>".$myimg." </a>
                    </td>";
    }
    // document or folder name
    if ( $is_folder[$i] ) {
        $nb=count_files_in_folder_tree($id_arr[$i]);
        echo "<td bgcolor=$mylightcolor ><a href=documents.php?status=documents&filter=".$filter."&dossier=".$id_arr[$i]."
                        title='Ouvrir ce dossier, il contient $nb document(s)'><b>".$f_arr[$i]." (".$nb.")</b></a></td>";
    }
    else
        echo "<td bgcolor=$mylightcolor >
            <a href=showfile.php?section=".$filter."&dossier=".$df_arr[$i]."&file=".$f_arr[$i]." $target> 
            <font size=1>".$f_arr[$i]."</font></a></td>";
                
    $url="document_modal.php?sid=".$filter."&docid=".$id_arr[$i]."&isfolder=".$is_folder[$i];
    // security
    if ( $document_security == 1 ) {
        echo "<td bgcolor=$mylightcolor align=left>";
        if ( $s_arr[$i] > 1 ) $img="<i class='fa fa-lock' style='color:orange;' title=\"".$s_lib_arr[$i]."\" ></i>";
        else $img="<i class='fa fa-unlock' title=\"".$s_lib_arr[$i]."\"></i>";
        if (! $is_folder[$i] ) {
            if ($granted_documentation)
                print write_modal( $url, "doc_".$is_folder[$i]."_".$id_arr[$i], $img);
            else
                echo $img;
        }
        echo "</td>";
    }          
    // type document
    echo "<td bgcolor=$mylightcolor >";
    
    if ($granted_documentation)
        print write_modal( $url, "doc_".$is_folder[$i]."_".$id_arr[$i], "<font size=1>".$t_lib_arr[$i]."</font>");
    else if ( $t_lib_arr[$i] <> 'choisir') 
        echo "<font size=1>".$t_lib_arr[$i]."</font>";

    if ( $cb_arr[$i] <> "" and ! $is_folder[$i]) {
        if ( check_rights($id, 40))
            $author = "<a href=upd_personnel.php?pompier=".$cb_arr[$i].">".my_ucfirst(get_prenom($cb_arr[$i]))." ".strtoupper(get_nom($cb_arr[$i]))."</a>";
        else 
            $author = my_ucfirst(get_prenom($cb_arr[$i]))." ".strtoupper(get_nom($cb_arr[$i]));
    }
    else $author="";
    echo "<td bgcolor=$mylightcolor ><font size=1>".$author."</a></font></td>";
                
    echo "<td bgcolor=$mylightcolor >
                <font size=1>".$d_arr[$i]."</td>";
                
    if ($granted_documentation)
        echo "<td bgcolor=$mylightcolor align=center><a href='javascript:deletefile(\"".$filter."\",".$id_arr[$i].",\"".str_replace("'","",$f_arr[$i])."\",\"".$is_folder[$i]."\")'>
                  <i class='fa fa-trash' title='supprimer'></i></a></td>";
    else echo "<td bgcolor=$mylightcolor width=10></td>";
    echo "</tr>";
}

if ( $document_security == 1 ) $colspan=7;
else $colspan=6;
echo "<tr><td colspan=$colspan bgcolor=$mylightcolor align=left >";


echo "</td>";
echo " </tr>";  
    
echo "</table><p>";
}
else echo "<p><i>Aucun document trouvé</i>";

if ( $dossier > 0 ) 
echo " <input type='button'  class='btn btn-default' id='goup' name='goup' value='Niveau supérieur' onclick=\"goUp('".$filter."','".$parent."');\" >";

if ($granted_documentation) {
    echo "<p><b>Ajouter ici un nouveau</b> ";
    echo "<input type='button'  class='btn btn-default' id='userfile' name='userfile' value='Fichier'
            onclick=\"openNewDocument('".$filter."','D');\" >";
    if ( $parent == 0 )
        echo "<b> ou </b>    
          <input type='button'  class='btn btn-default' id='userfile' name='userfile' value='Dossier'
            onclick=\"openNewDocument('".$filter."','F');\" >";
} 

echo "</div>";
writefoot();
?>
