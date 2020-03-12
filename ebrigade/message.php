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
check_all(44);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
get_session_parameters();
writehead();
test_permission_level(44);

$maxlen=1200;

if (isset($_POST['TM_ID'])) $TM_ID=intval($_POST['TM_ID']);
else $TM_ID=0;

if (isset($_GET['search'])) $search="%".secure_input($dbc,$_GET["search"])."%";
else $search="%";

if (isset($_GET['write'])) $write=true;
else $write=false;

if ( isset($catmessage) or isset($_POST["catmessage"])) {
    if (isset($_POST['catmessage'])) $catmessage=secure_input($dbc,$_POST['catmessage']);
    if ( $catmessage <> 'amicale' ) $catmessage='consigne';
    $error=0;
}
else { 
    write_msgbox("ERREUR", $error_pic, "Une erreur est apparue<br>Veuillez recommencer.<br><p align=center><a href='index_d.php' target='_self'><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
    exit;
}
$iphone=is_iphone();
if ($write and ! $iphone) {
     echo "<script type='text/javascript' src='js/tinymce/tiny_mce.js'></script>
            <script type='text/javascript' src='js/tinymce/ebrigade.js'></script>";
}
?>
<script language="JavaScript">
function displaymanager(p1,p2,p3){
     self.location.href="message.php?filter="+p1+"&catmessage="+p2+"&search="+p3;
     return true
}

function redirectwrite(p1){
     self.location.href="message.php?catmessage="+p1+"&write=1";
     return true
}
</script>
<?php

//============================================================
//   Upload and test message length
//============================================================

$query="select max(M_ID)+1 as NB from message";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB=$row["NB"];
if ( $NB == '') $NB = 1;

if (isset($_POST['message'] )) {
    if ( strlen($_POST['message']) > $maxlen + 7 ) {
        $msgstring= "Le message est trop long, la taille maximum permise est ".$maxlen;
        write_msgbox("ERREUR", $error_pic, "$msgstring<br><a href='javascript:history.back();' target='_self'><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
        exit;
    }
}

include_once ($basedir."/fonctions_documents.php");
$upload_dir =$filesdir."/files_message/".$NB."/";
$upload_result = upload_doc();
list($file_name, $error, $msgstring ) = explode(";", $upload_result);


//============================================================
//   Save message
//============================================================

if ( $error == 0 ) {
  if (isset($_POST['objet']) OR isset($_POST['message'])) // Si les variables existent
  {
        verify_csrf('message');
        $objet=strip_tags(secure_input($dbc,$_POST['objet']));
        if ( $objet == '' ) $objet = 'sans objet';
        $message=mysqli_real_escape_string($dbc,$_POST['message']);
        if ( $message == '' )  $message = 'sans texte';
        $message = str_replace("\\r\\n"," ",$message);
        $message = str_replace("\"","'",$message);
        $message = str_replace("\\","",$message);
        $objet = str_replace(";","",$objet);
        $objet = str_replace("\"","",$objet);
        $objet = str_replace("\\","",$objet);
        $duree = secure_input($dbc,$_POST['duree']);
        // enlever le <p> du début et </p> de fin si besoin 
        if (substr($message,0,3) == '<p>' ) $message = substr($message,3);
        if (substr($message,-4) == '</p>' ) $message = substr($message,0,-4);
        // remplacer les autres <p> par <br>
        $message=str_replace('<p>','',$message);
        $message=str_replace('</p>','<br>',$message);
        if (isset($_POST['mail'])) $mail = intval($_POST['mail']);
        else $mail=0;
        
        if ( $nbsections > 0 ) $filter=0;

        // Ensuite on enregistre le message
        $query="INSERT INTO message (M_ID,S_ID,M_TYPE, M_DATE, P_ID, M_TEXTE, M_OBJET, M_DUREE, M_FILE, TM_ID)
           values ( $NB,'$filter', '$catmessage', NOW() , $id, \"$message\", \"$objet\", $duree, \"$file_name\", $TM_ID)" ;
        $result=mysqli_query($dbc,$query);
        
        
        // Et notification par mail
        if ( $mail == 1 ) {
            $author = my_ucfirst($_SESSION['SES_PRENOM'])." ".strtoupper($_SESSION['SES_NOM']);
            if ( $filter > 0 ) 
                $niveau =" au niveau ".get_section_code("$filter")." - ".get_section_name("$filter")." ";
            else 
                $niveau = "";
            $message = "<span style='background-color: #ffff0;'><strong>Le message d’information suivant vient d’être enregistré ".$niveau."par ".$author."</strong></span><p>".$message;
            if ( $file_name <> "" ) $attachment = $upload_dir.$file_name;
            else  $attachment="";
            if ( $cron_allowed == 0 ) {
                $destid = $id.",".get_granted(58,"$filter",'tree','yes');
                $nb = mysendmail("$destid" , $id , "Nouvelle information: $objet" , "$message" , "$attachment");
            }
            else {
                $senderName = fixcharset($author);
                $query="insert into mailer(MAILDATE, MAILTO, SENDERNAME, SENDERMAIL, SUBJECT, MESSAGE, ATTACHMENT)
                    select NOW(), P_EMAIL, \"".$senderName."\",\"".$_SESSION['SES_EMAIL']."\",
                    \"".$objet."\", \"".$message."\", \"".$attachment."\"
                    from pompier 
                    where P_OLD_MEMBER = 0 
                    and P_STATUT <> 'EXT'
                    and P_EMAIL <> ''
                    and P_ID in (".$id.",".get_granted(58,"$filter",'tree','yes').")";
                $result=mysqli_query($dbc,$query);
            }
        }
    }
}
else {
    if (( $gardes == 1 ) and ( check_rights($id, 8) )) $mycatmessage='consigne';
    else $mycatmessage='amicale';
    write_msgbox("ERREUR", $error_pic, "$msgstring<br><a href='message.php?type=$mycatmessage' target='_self'><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
    exit;
}

echo "<body>";

if ( $catmessage == 'amicale' or $nbsections == 0 )  {
    $numfonction=16;
    $mytxt="Ajouter une information";
}
else {
    $numfonction=8;
    $mytxt="Ajouter une consigne pour la garde";
}


echo "<div class='table-responsive'>";
//============================================================
//   formulaire
//============================================================

if ( $write ) {
 
 
if ( check_rights($id, $numfonction) ) {
echo "<div align=center><table class='noBorder'>
      <tr><td><font size=4><b>$mytxt</b></font><br>";
      
echo "</td></tr></table>"; 
 
echo "<form action='message.php' method='POST' enctype='multipart/form-data'>";
echo "<input type='hidden' name='catmessage' value='$catmessage' size='20'>";
print insert_csrf('message');
echo "<table cellspacing=0 border=0 >";

echo "<tr class='TabHeader'>
      <td colspan=2 align=right>Nouvelle information</td>
      </tr>";
echo "<tr>
           <td bgcolor=$mylightcolor align=center ></td>
          <td bgcolor=$mylightcolor >
          <table class='noBorder'>";

//=====================================================================
// choix section
//=====================================================================

$highestsection=get_highest_section_where_granted($id,$numfonction);
if ( $highestsection == '' ) $highestsection=$mysection;
if (( $highestsection <> '' ) and  check_rights($id, 24 )) $highestsection=0;

if ($nbsections == 0  and check_rights($id, $numfonction)) {
     echo "<tr>
            <td bgcolor=$mylightcolor align=right><i class='fa fa-at fa-lg' title='destinataires'></i></td>
            <td bgcolor=$mylightcolor align=left>";
     echo "<select id='section' name='filter'>"; 
   
    $level=get_level("$highestsection");
       if ( $level == 0 ) $mycolor=$myothercolor;
       elseif ( $level == 1 ) $mycolor=$my2darkcolor;
       elseif ( $level == 2 ) $mycolor=$my2lightcolor;
       elseif ( $level == 3 ) $mycolor=$mylightcolor;
       else $mycolor='white';
       $class="style='background: $mycolor;'";
       echo "<option value='$highestsection' $class >".
              get_section_code("$highestsection")." - ".get_section_name("$highestsection")."</option>";
               display_children2("$highestsection", $level +1, $mysection, $nbmaxlevels);
    echo "</select></td> ";
    echo "</tr>";
}    
else
    echo "<input type='hidden' name='section' value='$mysection'>";

//=====================================================================
// écrire le message
//=====================================================================
$query="select TM_ID, TM_LIBELLE, TM_COLOR, TM_ICON from type_message order by TM_ID";
$result=mysqli_query($dbc,$query);

echo         "<tr>
                  <td bgcolor=$mylightcolor align=right>Objet</td>
                 <td bgcolor=$mylightcolor><input type='text' name='objet' style='width:80%;font-size:12pt;' ></td>
             </tr>";
             
echo         "<tr>
                  <td bgcolor=$mylightcolor align=right>Message<br><span class='small'>Max $maxlen<br>caractères</span></td>
                  <td bgcolor=$mylightcolor >
                  <textarea name='message' id='message' cols='70' rows='12' style='width:80%;font-size:12pt;'></textarea>
                  </td>
                   </tr>
                   <tr>
                      <td bgcolor=$mylightcolor align=right><i class='fa fa-paperclip fa-lg' title='Ajouter un Attachement'></i></td>
                      <td bgcolor=$mylightcolor ><input type='file' id='userfile' name='userfile'><small>max ".$MAX_FILE_SIZE_MB."M</small></td>
                     </tr>";

echo         "<tr><td bgcolor=$mylightcolor align=right>Type</td>
                <td bgcolor=$mylightcolor><select name='TM_ID'>";
while ($row = mysqli_fetch_array($result) ) {
        if ($row["TM_ID"] == 0) $selected='selected';
        else $selected='';
        echo "<option value=".$row["TM_ID"]." $selected>".$row["TM_LIBELLE"]."</option>";    
        
}
echo "</select></td></tr>";

echo   "<tr>
            <td bgcolor=$mylightcolor align=right><i class='fa fa-envelope fa-lg' title='Notifier par mail'></i></td>
            <td bgcolor=$mylightcolor><input type='checkbox' name='mail' title='Cocher pour que le personnel concerné reçoive aussi le message par mail' value=1 checked> <i>Notifier par mail</i></td>
        </tr>";
                     
echo     "<tr>
              <td bgcolor=$mylightcolor align=right>Durée</td>
            <td bgcolor=$mylightcolor><select name='duree'>
                    <option value=1>1 jours</option>
                    <option value=1>2 jours</option>
                    <option value=3>3 jours</option>
                    <option value=4>4 jours</option>
                    <option value=5>5 jours</option>
                    <option value=6>6 jours</option>
                    <option value=7 selected >7 jours</option>
                    <option value=10>10 jours</option>
                    <option value=15>15 jours</option>
                    <option value=20>20 jours</option>
                    <option value=30>30 jours</option>
                    <option value=60>60 jours</option>
                    <option value=0>Sans limitation</option>
                   </select> <input type='submit' class='btn btn-default' value='Publier'></td>
                 </tr>";
                 
echo " </table>
      </td>
     </tr></table>
    </form>
    <p><input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">
    </div>";
}
} // fin mode write

//============================================================
//   messages en cours
//============================================================
else {
$csrf = generate_csrf('delmessage');
$query="SELECT p.P_ID, P_NOM, P_PRENOM, P_GRADE, M_DUREE, M_ID, s.S_DESCRIPTION, s.S_ID,
        DATE_FORMAT(M_DATE, '%m%d%Y%T') as FORMDATE2,
        DATE_FORMAT(M_DATE,'%d-%m-%Y') as FORMDATE3,
        p.P_ID, m.M_TEXTE, m.M_OBJET, m.M_FILE,
        tm.TM_COLOR, tm.TM_ICON, tm.TM_LIBELLE
        FROM message m, pompier p, section s, type_message tm
        where m.P_ID=p.P_ID
        and m.TM_ID = tm.TM_ID
        and s.S_ID = m.S_ID";
if ( $nbsections == 0 )         
    $query .= " and s.S_ID in (".get_family_up("$filter").")";
$query .= " and m.M_TYPE='".$catmessage."'";

if (! check_rights($id, $numfonction, $filter)) {
    $query .= " and (datediff('".date("Y-m-d")."', m.M_DATE ) <= M_DUREE or M_DUREE = 0 )"; 
}

if( $search <> '' and $search <> '%'){
    $query .= " and (m.M_TEXTE like '".$search."' or m.M_OBJET like '".$search."')";
}

$query .= " order by M_DATE desc";

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

echo "<body >";

echo "<form name='formf' action='message.php'>"; 
echo "<input type='hidden' name='catmessage' value='".$catmessage."'>";
echo "<div align=center><table class='noBorder'>
      <tr>
      <td rowspan=3 align=left width=100>";
if ( check_rights($id, $numfonction) )
    echo "<input type='button' class='btn btn-default' name='add' value='Ajouter' title='Cliquer ici pour ajouter un message' onclick=\"redirectwrite('".$catmessage."');\">";
echo "</td><td colspan=2><font size=4><b>Historique des informations </b></font><span class='badge'>".$number." </span></td></tr>
      <tr><td>";
     
if (  $nbsections <> 0 or ! check_rights($id,52)) {
    echo "<input type='hidden' name='filter' value='$filter'></td><td>";
}
else {
     echo "<i class='fa fa-at fa-lg' title='messages destinés aux membres de'></i></td><td><select id='filter' name='filter'
     onchange=\"displaymanager(document.getElementById('filter').value,'".$catmessage."','".$search."')\">"; 
   
   $level=get_level($filter);
   if ( $level == 0 ) $mycolor=$myothercolor;
   elseif ( $level == 1 ) $mycolor=$my2darkcolor;
   elseif ( $level == 2 ) $mycolor=$my2lightcolor;
   elseif ( $level == 3 ) $mycolor=$mylightcolor;
   else $mycolor='white';
   $class="style='background: $mycolor;'";
   display_children2(-1, 0, $filter, $nbmaxlevels,$sectionorder);
   echo "</select>";      
}                   
echo "</td></tr>";
echo "<tr><td><i class='fa fa-search fa-lg' title='recherche dans les messages' ></td><td>
      <input type=\"text\" name=\"search\" 
      value=\"".preg_replace("/\%/","",$search)."\" size=\"20\" 
      title=\"Utilisez le signe % pour remplacer des caractères\"/> <input type='submit' class='btn btn-default' value='chercher'></td></tr>"; 
      
echo "</tr></table>";
echo "</form>";

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

if ( $number > 0 ) {
echo "<p><table width=950 class='noBorder'>";
while ($row = mysqli_fetch_array($result) ) {
    $duree=$row["M_DUREE"];
    $date3=$row["FORMDATE3"];
    $S_ID=$row["S_ID"];
    $grade=$row["P_GRADE"];
    $nom=$row["P_NOM"];
    $prenom=$row["P_PRENOM"];
    $objet=$row["M_OBJET"];
    $mid=$row["M_ID"];
    $file=$row["M_FILE"];
    $color=$row["TM_COLOR"];
    $icon=$row["TM_ICON"];
    $category=$row["TM_LIBELLE"];
    if ( $duree == 0 ) {
        $mycolor=$textcolor;
        $perim_info=" ";
    }
    else {
        $MYDATEDIFF = $duree - my_date_diff($date3, date('d-m-Y'));
        if ( $MYDATEDIFF  < 0 ) {
            $mycolor=$mydarkcolor;
            $perim_info=$duree."j - Message périmé";
        }
        else {
            $mycolor=$textcolor;
            $perim_info=$duree."j - encore $MYDATEDIFF j";
        }
    }
    if ($grades == 1) $mygrade=$grade;
    else $mygrade="";
    
    echo "<tr><td><i class='fa fa-".$icon." fa-lg' style='color:".$color.";' title=\"message ".$category."\" ></i>
           <font size=3 color=".$color."><b>".$objet." </font></b> -<i> 
           <font color=".$color.">".$mygrade." ".ucfirst($prenom)." ".strtoupper($nom)."</i>";
 
    if ( check_rights($id, $numfonction, $S_ID) ) {
        echo "<i> - ".$date3." - $perim_info</i>";
        echo " <a href=delete_message.php?catmessage=".$catmessage."&M_ID=".$mid."&csrf_token_delmessage=".$csrf.">
                   <i class='fa fa-trash' title='supprimer ce message' ></i></a>";
    }
    else 
        echo "<i> - ".$date3." </i>";
 
    echo "<br>".force_blank_target($row["M_TEXTE"])."<br>";
    if ( $row["M_FILE"] <> "") echo " <i> fichier joint - 
        <a href=showfile.php?section=".$S_ID."&evenement=0&message=".$mid."&file=".$file.">".$file."</a></i>";
    echo "<p>";
    echo "</font></td></tr>";
}
echo "</table>";
}
} // fin mode liste

echo "</div>";
writefoot();
?>
