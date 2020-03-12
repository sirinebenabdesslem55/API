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

  
function get_smaller_icon($type) {
    $type=strtolower($type);
    $l='grey';
    if ( $type =='pdf' ) {
        $c='file-pdf';
        $l='red';
    }
    else if ( $type =='zip' ) {
        $c='file-archive';
        $l='orange';
    }        
    else if ( $type =='jpg' or $type =='jpeg' or $type =='png'){
        $c='file-image';
        $l='purple';
    }
    else if ( $type =='doc' or $type =='docx') {
        $c='file-word';
        $l='blue';
    }
    else if ( $type =='xls' or $type =='xlsx') {
        $c='file-excel';
        $l='green';
    }
    else if ( $type =='ppt' or $type =='pptx' ) {
        $c='file-powerpoint';
        $l='#CC3333';
    }
    else if ( $type =='txt' ) $c='file-text-o';
    else {
        $c='file';
        $l='#191970';
    }
    return " <i class='far fa-".$c."' style='color:".$l.";'></i>";   
}

  
function upload_doc() {
    global $MAX_SIZE, $MAX_SIZE_ERROR, $supported_mimes, $supported_ext;
    global $upload_dir, $filesdir;
    $FNAME='';
    $ERROR=0;
    $MESSAGE='';
    if ( isset ($_FILES['userfile'])) {
        if ( $_FILES['userfile']['name'] == '' )
            return ";0;";
        $ERROR =  $_FILES['userfile']['error'];
        $FILE_SIZE = $_FILES['userfile']['size'];
        if ($FILE_SIZE == 0 or $FILE_SIZE > $MAX_SIZE) {
            $MESSAGE = $MAX_SIZE_ERROR;
            $ERROR = 1;
        }
        else {
            $temp_name = $_FILES['userfile']['tmp_name'];
            $FTYPE = $_FILES['userfile']['type'];
            $FNAME = $_FILES['userfile']['name'];
            $FEXT = strtolower(substr($FNAME,strrpos($FNAME,".")));
            $FNAME = str_replace("\\","",$FNAME);
            $FNAME = str_replace(" ","_",$FNAME);
            $FNAME = str_replace("°","",$FNAME);
            $FNAME = str_replace("#","",$FNAME);
            $FNAME = str_replace("'","",$FNAME);
            $FNAME = str_replace("&","",$FNAME);
            $FNAME = str_replace("+","",$FNAME);
            $FNAME = str_replace("(","",$FNAME);
            $FNAME = str_replace(")","",$FNAME);
            $FNAME = fixcharset($FNAME);
            $FPATH = $upload_dir.$FNAME;
            //File Type/Extension Check
            if (! in_array($FTYPE, $supported_mimes) or !in_array($FEXT, $supported_ext)) {
                $MESSAGE = "Attention, les fichiers du type $FEXT ($FTYPE) sont interdits.";
                $ERROR = 1;
            }
            else {
                // create upload dir
                if (! is_dir($upload_dir)) {
                    if (! mkdir($upload_dir, 0777, true)) {
                        $MESSAGE = "Le répertoire d'upload n'existe pas et sa création a échoué.";
                        $ERROR=1;
                    }
                }
                if (! move_uploaded_file($temp_name, $FPATH)) {
                    $MESSAGE ="Une erreur est apparue lors de l'upload du fichier.";
                    $ERROR=1;
                }
            }
        }
    }
    $out=$FNAME.";".$ERROR.";".$MESSAGE;
    return $out;
}

function show_auto_doc($docname, $mode, $secured, $signed=true) {
        global $granted_event, $evenement, $S_ID, $mylightcolor, $document_security;
        $out="";
        if ( $mode == -1 ) 
            $link="pdf.php?pdf=DPS&id=".$evenement;
        else if ( $mode == -2 ) 
            $link="pdf.php?page=1&pdf=DPS&id=".$evenement;
        else if ( $signed ) 
            $link="pdf_document.php?section=".$S_ID."&evenement=".$evenement."&mode=".$mode;
        else 
            $link="pdf_document.php?section=".$S_ID."&evenement=".$evenement."&mode=".$mode."&signed=0";
        $myimg="<i class='far fa-file-pdf'  style='color:red;' ></i>"; 
        $filedate="";
        $img="";
        if ( $granted_event or (! $secured)) {
            if ( $document_security == 1 ) $img="<i class='fa fa-unlock' title=\"Vous pouvez voir et imprimer ces documents\"></i>";
            $out .= "<tr bgcolor=$mylightcolor >
                  <td style='padding-left:4px'><a href=".$link.">".$myimg."</a></td>
                    <td ><a href=".$link." target=_blank><small>".$docname."</small></a></td>";
        }
        else {
            if ( $document_security == 1 ) $img="<i class='fa fa-lock'  style='color:orange;' title=\"Vous n'avez pas le droit de voir ces documents\"></i>";
            $out .= "<tr bgcolor=$mylightcolor>
                  <td>".$myimg."</td>
                    <td ><small> ".$docname."</small></a></td>";
        }
        $out .= "<td align=center>".$img."</td>
        <td align=center>-</td>
        <td align=center>-</td>
        <td></td>
        </tr>";
        return $out;

}

function show_hardcoded_doc($number, $docname, $file, $type) {
    // $number = 1 : SST or 2: PSC1
    // $type pdf, or xls
    global $evenement, $S_ID, $mylightcolor;
    $myimg=get_smaller_icon($type);
    $img="<i class='fa fa-unlock' title=\"Vous pouvez voir et imprimer ces documents\"></i>";
    $out = "<tr bgcolor=$mylightcolor ><td style='padding-left:4px'>
        <a href=showfile.php?sst=$number&section=".$S_ID."&evenement=".$evenement."&file=".$file.">".$myimg."</a></td>
        <td><a href=showfile.php?sst=$number&section=".$S_ID."&evenement=".$evenement."&file=".$file.">
        <small>".$docname."</small></a></td>
        <td align=center>".$img."</td>
        <td align=center>-</td>
        <td align=center>-</td>
        <td ></td>
        </tr>";
    return $out;
}

function show_documents_in_folder($folder){
    global $supported_ext, $mylightcolor, $userdocdir;
    $mypath=$userdocdir."/".$folder;
    $out="";
    if (is_dir($mypath)) {
           $handle=opendir($mypath);
           $out .=  "<tr bgcolor=$mylightcolor ><td colspan=6 align=left><b>Documents ".$folder."</b></td></tr>";
        $files=array();
        while ($file = readdir($handle)) {
            $files[$file] = $file;
        }
        closedir($handle);
        sort($files);
        
        foreach($files as $file) {
             if ($file != "." && $file != ".." and $file <> "index.html" and $file <> ".svn") {
                  if (is_dir($mypath."/".$file)) {
                       $out .= show_documents_in_folder($folder."/".$file);
                  }
                 else {
                      $file_ext = strtolower(substr($file,strrpos($file,".")));
                    $myimg=get_smaller_icon(file_extension($file));
              
                     $out .=  "<tr bgcolor=$mylightcolor ><td style='padding-left:4px'>
                    <a href=".$mypath."/".rawurlencode("$file")." target=_blank>".$myimg."</a></td>
                    <td><a href=".$mypath."/".rawurlencode("$file")." target=_blank><small>".$file."</small></a></td>
                    <td colspan=4></td>
                    </tr>";
                }
             }
         }
     }
    return $out;
}

function count_documents_in_folder($folder){
    global $supported_ext, $mylightcolor, $userdocdir;
    $count=0;
    $mypath=$userdocdir."/".$folder;
    if (is_dir($mypath)) {
           $dir=opendir($mypath);
        while ($file = readdir ($dir)) {
             if ($file != "." && $file != ".." and $file <> "index.html" and $file <> ".svn") {
                  if (is_dir($mypath."/".$file)) {
                       $count = $count + count_documents_in_folder($folder."/".$file);
                  }
                 else $count++;
             }
         }
     }
     return $count;
}

function show_attached_docs($evenement){
 
    global $filesdir, $documentation, $S_ID, $mylightcolor, $mydarkcolor, $supported_ext, $document_security;
    global $dbc;
    $out="";
    $i=0;
    $_txt = array();
    $_dt = array();
    $mypath=$filesdir."/files/".$evenement;
    $querys="select DS_ID, DS_LIBELLE,F_ID from document_security";
    if (is_dir($mypath)) {
        $dir=opendir($mypath);
        while ($file = readdir ($dir)) {
            $securityid = "1";
            $securitylabel ="Public";
            $fonctionnalite = "0";
            $author = "";
            $fileid = 0;
            $author = "";
            $fileid = 0;
            $_out="";
            if ($file != "." && $file != ".." and (file_extension($file) <> "db")) {
                $query="select distinct d.D_ID,d.S_ID,d.D_NAME,d.TD_CODE,d.DS_ID, td.TD_LIBELLE, 
                        ds.DS_LIBELLE, ds.F_ID, d.D_CREATED_BY, date_format(d.D_CREATED_DATE,'%Y-%m-%d %H-%i') D_CREATED_DATE, TIMESTAMPDIFF(MINUTE,d.D_CREATED_DATE,NOW()) AGE
                        from document d, document_security ds, type_document td
                        where td.TD_CODE=d.TD_CODE
                        and d.DS_ID=ds.DS_ID
                        and d.E_CODE=".$evenement."
                        and d.D_NAME=\"".$file."\"";
                        
                $result=mysqli_query($dbc,$query);
                $nb=mysqli_num_rows($result);
                $row=@mysqli_fetch_array($result);
                
                if ($row["F_ID"] == 0 
                    or check_rights($_SESSION['id'], $row["F_ID"], "$S_ID")
                    or $documentation
                    or $row["D_CREATED_BY"] == $_SESSION['id']) {
                    $visible=true;            
                }
                else $visible=false;
                
                $file_ext = strtolower(substr($file,strrpos($file,".")));
                $myimg=get_smaller_icon(file_extension($file));     
                $filedate = date("Y-m-d H:i",filemtime($mypath."/".$file));
                        
                if ( $nb > 0 ) {
                    $fileid = $row["D_ID"];
                    $securityid = $row["DS_ID"];
                    $securitylabel =$row["DS_LIBELLE"];
                    $fonctionnalite = $row["F_ID"];
                    $author = $row["D_CREATED_BY"];
                    $filedate = $row["D_CREATED_DATE"];
                    $age = abs(intval($row["AGE"]));
                    if ( $age < 1440 ) $new="<i class='fa fa-star' style='color:yellow;' title=\"Ce document a été ajouté il y a moins de 24 heures\" ></i>";
                    else $new  ='';
                }
                else {
                    $new  ='';
                    $age = '';
                }
                
                if ( $file_ext == '.pdf' ) $target="target=_blank";
                else $target="";
                
                if ( $visible ) 
                    $_out .= "<tr bgcolor=$mylightcolor ><td style='padding-left:4px'>
                        <a href=showfile.php?section=".$S_ID."&evenement=".$evenement."&file=".$file." $target>".$myimg."</a></td>
                        <td ><a href=showfile.php?section=".$S_ID."&evenement=".$evenement."&file=".$file." $target>
                            <small>".$file." ".$new."</small></a></td>";
                else
                    $_out .= "<tr bgcolor=$mylightcolor ><td>".$myimg."</td>
                          <td><small> ".$file." ".$new."</small></td>";
                    
                $_out .= "<td align=center>";
                
                if ( $document_security == 0 ) $img="";
                else if ( $securityid > 1 ) $img="<i class='fa fa-lock' style='color:orange;' title=\"".$securitylabel."\" ></i>";
                else $img="<i class='fa fa-unlock' title=\"".$securitylabel."\" ></i>";
                
                $url="document_modal.php?evenement=".$evenement."&sid=".$S_ID."&docid=".$fileid;
                if ($documentation) 
                     $_out .= write_modal( $url, "doc_".$fileid, $img);
                else $_out .= $img;
                $_out .= "</td>";
                
                if ( $author <> "" ) $author = "<a href=upd_personnel.php?pompier=".$author.">".
                      my_ucfirst(get_prenom($author))." ".strtoupper(get_nom($author))."</a>";

                $_out .= "<td align=center><small>".$author."</a></small></td>";
                    
                $_out .= "<td align=center >
                    <small>".$filedate."</small></td>";
                    
                if ( $documentation)
                      $_out .= "<td align=center><a href=\"javascript:deletefile('".$evenement."',".$fileid.",'".str_replace("'","",$file)."')\">
                      <i class='fa fa-trash' title='supprimer'></i></a></td>";
                else $_out .= "<td></td>";
                $_out .= "</tr>";
                
                $_txt[$i] = $_out;
                $_dt[$i] = $age;
                $i++;
            }
        }
    }
    array_multisort($_dt, SORT_NUMERIC, SORT_ASC, $_txt);
    if ( $i > 0 ) {
        for ($j=0; $j < $i ; $j++) {
            $out .= $_txt[$j];
        }
    }
    return $out;
}



?>
