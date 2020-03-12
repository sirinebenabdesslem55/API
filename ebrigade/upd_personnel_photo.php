<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 5.1

  # Copyright (C) 2004, 2009 Nicolas MARCHE
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
$error=(isset($_GET['error'])?$_GET['error']:(isset($_POST['error'])?$_POST['error']:""));
$msg=(isset($_GET['msg'])?$_GET['msg']:(isset($_POST['msg'])?$_POST['msg']:""));
$a=(isset($_GET['a'])?$_GET['a']:"");
$t=(isset($_GET['t'])?$_GET['t']:"");
$P_PHOTO=(isset($_GET['photo'])?$_GET['photo']:"");

$error=secure_input($dbc,$error);
$msg=secure_input($dbc,$msg);
$a=secure_input($dbc,$a);
$t=secure_input($dbc,$t);
$P_PHOTO=secure_input($dbc,$P_PHOTO);

$P_ID=(isset($_GET['pompier'])?intval($_GET['pompier']):(isset($_POST['P_ID'])?intval($_POST['P_ID']):0));
$section=get_section_of($P_ID);
$imgtropgrande=false;
$imgtroppetite=false;

if ( $id <> $P_ID ) {
   check_all(2);
   if (! check_rights($id,2,"$section")) check_all(24);
}

$pompier=intval($P_ID);
$pompierNomPrenom=my_ucfirst(get_prenom($pompier))." ".strtoupper(get_nom($pompier));

//only assign a new timestamp if the session variable is empty
if (!isset($_SESSION['random_key']) || strlen($_SESSION['random_key'])==0) {
    $_SESSION['random_key'] = strtotime(date('Y-m-d H:i:s'));
    $_SESSION['user_file_ext']= "";
}
#========================================================================================================
# CONSTANTS
#========================================================================================================
$upload_dir = $trombidir; 
$upload_path = $upload_dir."/";

$large_image_prefix = "resize_";             // The prefix name to large image
$thumb_image_prefix = "thumbnail_";            // The prefix name to the thumb image
$large_image_name = $large_image_prefix.$_SESSION['random_key'];     // New name of the large image (append the timestamp to the filename)
$thumb_image_name = $thumb_image_prefix.$_SESSION['random_key'];     // New name of the thumbnail image (append the timestamp to the filename)
$thumb_image_name = $pompier;// JPK - spécifique ebrigade
$max_file = "1";                     // Maximum file size in MB
$min_file = "6";                     // Minimum file size in kB
$max_pixels="2";                    //2 Mega Pixels
$min_pixels="6";                    //4 Kilo Pixels
$max_width = "500";                    // Max width allowed for the large image
$min_width = "148";                    // Min width allowed for the small image
$min_height = "177";                    // Min width allowed for the small image
// Taille photo 25*30 mm
// 300 dpi - px/pouce = 295 * 354 mm
// 150 dpi - px/pouce = 148 * 177 px   <<<< choix optimum pour une retouche automatique
//  72 dpi - px/pouce = 71 * 85 px
$thumb_width = "148";                // Width of thumbnail image
$thumb_height = "177";                // Height of thumbnail image
// Only one of these image types should be allowed for upload
//$allowed_image_types = array('image/pjpeg'=>"jpg",'image/jpeg'=>"jpg",'image/jpg'=>"jpg",'image/png'=>"png",'image/x-png'=>"png",'image/gif'=>"gif");
$allowed_image_types = array('image/jpeg'=>"jpg",'image/pjpeg'=>"jpg",'image/jpg'=>"jpg",'image/png'=>"png",'image/x-png'=>"png");
$allowed_image_ext = array_unique($allowed_image_types);
$image_ext = "";
foreach ($allowed_image_ext as $mime_type => $ext) {
    $image_ext.= strtoupper($ext)." ";
}

#========================================================================================================
# IMAGE FUNCTIONS                                                                                                                                                
#========================================================================================================
function resizeImage($image,$width,$height,$scale) {
    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
    $imageType = image_type_to_mime_type($imageType);
    $newImageWidth = ceil($width * $scale);
    $newImageHeight = ceil($height * $scale);
    $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
    switch($imageType) {
        case "image/gif":
            $source=imagecreatefromgif ( $image); 
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            $source=imagecreatefromjpeg($image); 
            break;
        case "image/png":
        case "image/x-png":
            $source=imagecreatefrompng($image); 
            break;
      }
    imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);
    
    switch($imageType) {
        case "image/gif":
              imagegif ( $newImage,$image); 
            break;
          case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
              imagejpeg($newImage,$image,90); 
            break;
        case "image/png":
        case "image/x-png":
            imagepng($newImage,$image);  
            break;
    }
    chmod($image, 0777);
    return $image;
}

function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale) {
    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
    $imageType = image_type_to_mime_type($imageType);
    
    $newImageWidth = ceil($width * $scale);
    $newImageHeight = ceil($height * $scale);
    $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
    switch($imageType) {
        case "image/gif":
            $source=imagecreatefromgif ( $image); 
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            $source=imagecreatefromjpeg($image); 
            break;
        case "image/png":
        case "image/x-png":
            $source=imagecreatefrompng($image); 
            break;
      }
    imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
    switch($imageType) {
        case "image/gif":
              imagegif ( $newImage,$thumb_image_name); 
            break;
          case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
              imagejpeg($newImage,$thumb_image_name,90); 
            break;
        case "image/png":
        case "image/x-png":
            imagepng($newImage,$thumb_image_name);  
            break;
    }
    chmod($thumb_image_name, 0777);
    return $thumb_image_name;
}

function getHeight($image) {
    $size = getimagesize($image);
    $height = $size[1];
    return $height;
}

function getWidth($image) {
    $size = getimagesize($image);
    $width = $size[0];
    return $width;
}

#========================================================================================================
# SAVE IMAGE                                                                                                                                                
#========================================================================================================

$large_image_location = $upload_path.$large_image_name.$_SESSION['user_file_ext'];
$thumb_image_location = $upload_path.$thumb_image_name.$_SESSION['user_file_ext'];

if ( !is_dir($upload_dir)) {
    mkdir($upload_dir, 0777);
    chmod($upload_dir, 0777);
}

//Check to see if any images with the same name already exist
if (file_exists($large_image_location)) {
    if ( file_exists($thumb_image_location)) {
        $thumb_photo_exists = "<img src=\"".$upload_path.$thumb_image_name.$_SESSION['user_file_ext']."\" alt=\"Thumbnail Image\"/>";
    } else {
        $thumb_photo_exists = "";
    }
       $large_photo_exists = "<img src=\"".$upload_path.$large_image_name.$_SESSION['user_file_ext']."\" alt=\"Large Image\"/>";
} else {
       $large_photo_exists = "";
    $thumb_photo_exists = "";
}

if (isset($_POST["upload"])) {
    echo $userfile_size;
    $userfile_name = $_FILES['image']['name'];
    $userfile_tmp = $_FILES['image']['tmp_name'];
    $userfile_size = $_FILES['image']['size'];
    $userfile_type = $_FILES['image']['type'];
    if ($userfile_type == 'image/pjpeg' ) $userfile_type = 'image/jpeg';
    $filename = basename($_FILES['image']['name']);
    $file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));

    $taille = filesize($_FILES['image']['tmp_name']);
    $img_info = @getimagesize($_FILES['image']['tmp_name']);
    
    //Only process if the file is a JPG, PNG or GIF and below the allowed limit
    if ( (!empty($_FILES["image"])) && ($_FILES['image']['error'] == 0)) {
        $error = "<br>Votre image est un <strong>".$userfile_type." extension ".$file_ext."</strong>
                <br>Mais seules les images suivantes sont acceptées <strong>".$image_ext."</strong> sont acceptées<br>";
        foreach ($allowed_image_types as $mime_type => $ext) {
            //loop through the specified image types and if they match the extension then break out
            if ( $file_ext==$ext and $userfile_type==$mime_type) {
                $error = "";
                break;
            }
        }
        
        //check if the file size is above the allowed limit
        if ($userfile_size > ($max_file*1048576)) {
            $error.= "<br />L'image doit faire moins de ".$max_file." MB ";
        }
        if ($userfile_size < (($min_file -1) *1024)) {
            $error.= "<br />L'image doit faire au moins ".$min_file." kB ";
        }    
    
    } else {
        $error .= "<br />S&eacute;lectionnez une image a t&eacute;l&eacute;charger";
    }

    if ( ($img_info[0]*$img_info[1])>($max_pixels*1000000)) {
        $imgtropgrande=true;
        $error .= "<br />Diminuez les dimensions de l'image d'origine<br />MAXI = $max_pixels Mega pixels et $max_file MB";
    }
    if ( ($img_info[0]*$img_info[1])<(($min_pixels -1)*1000)) {
        $imgtroppetite=true;
        $error .= "<br />Augmentez les dimensions de l'image d'origine<br />MINI = $min_pixels kilo pixels et $min_file kB";
        
    }
    //Everything is ok, so we can upload the image.
    if ( strlen ( $error ) == 0) {
        if (isset($_FILES['image']['name'])) {
            //this file could now has an unknown file extension (we hope it's one of the ones set above!)
            if ( strtolower(file_extension($large_image_location)) <> 'jpg' ) {
                $large_image_location = $large_image_location.".".$file_ext;
                $thumb_image_location = $thumb_image_location.".".$file_ext;
            }
            //put the file ext in the session so we know what file to look for once its uploaded
            $_SESSION['user_file_ext']=".".$file_ext;
            
            move_uploaded_file($userfile_tmp, $large_image_location);
            chmod($large_image_location, 0777);
            
            $width = getWidth($large_image_location);
            $height = getHeight($large_image_location);
            //Scale the image if it is greater than the width set above
            if ($width > $max_width) {
                $scale = $max_width/$width;
                $uploaded = resizeImage($large_image_location,$width,$height,$scale);
            }
            elseif ($width < $min_width or $height < $min_height) {
                $scale = $min_width/$width;
                if ( $scale < 1 ) $scale = $min_height/$height;
                $uploaded = resizeImage($large_image_location,$width,$height,$scale);
            }
            else {
                $scale = 1;
                $uploaded = resizeImage($large_image_location,$width,$height,$scale);
            }
            //Delete the thumbnail file so the user can create a new one
            if (file_exists($thumb_image_location)) {
                unlink($thumb_image_location);
            }
        }
        //Refresh the page to show the new uploaded image
        header("location:upd_personnel_photo.php?pompier=$pompier");
        exit();
    }
    else {
        $error .= "<br><a href='upd_personnel_photo.php?pompier=".$pompier."' class ='btn btn-danger'>Retour</a>";
    
    }
}

if (isset($_POST["upload_thumbnail"]) && strlen($large_photo_exists)>0) {
    //Get the new coordinates to crop the image.
    $x1 = $_POST["x1"];
    $y1 = $_POST["y1"];
    $x2 = $_POST["x2"];
    $y2 = $_POST["y2"];
    $w = $_POST["w"];
    $h = $_POST["h"];
    //Scale the image to the thumb_width set above
    $scale = $thumb_width/$w;
    $cropped = resizeThumbnailImage($thumb_image_location, $large_image_location,$w,$h,$x1,$y1,$scale);
    $sql = "UPDATE pompier SET
            p_photo = '".str_replace('/','',str_replace($trombidir,"",$thumb_image_location))."'
            WHERE p_id = ".$pompier;
    $result=mysqli_query($dbc,$sql);
    insert_log('UPDPHOTO', $pompier);    
    $large_image_location = $upload_path.$large_image_prefix.$t;
    if (file_exists($large_image_location)) {
        unlink($large_image_location);
    }    
    //Reload the page again to view the thumbnail
    header("location:upd_personnel_photo.php?pompier=$pompier&msg=$msg");
    exit();
}


if ($a=="delete" && strlen($t)>0) {
//get the file locations 
    $large_image_location = $upload_path.$large_image_prefix.$t;
    $thumb_image_location = $upload_path.$thumb_image_prefix.$t;
    if (file_exists($large_image_location)) {
        unlink($large_image_location);
    }
    if (file_exists($thumb_image_location)) {
        unlink($thumb_image_location);
    }
    header("location:".$_SERVER["PHP_SELF"]."?pompier=$pompier");
    exit(); 
}

if ($a == "suppr" && strlen($t) > 0) {
//get the file locations 
    $pompier=$_GET['P_ID'];
    $image_location = $upload_path.$t.$_SESSION['user_file_ext'];
    if (file_exists($image_location)) {
        unlink($image_location);
        $sql = "UPDATE pompier SET
        p_photo = NULL
        WHERE p_id = ".$pompier;
        $result=mysqli_query($dbc,$sql);
        insert_log('DELPHOTO', $pompier);    
    }
    header("location:".$_SERVER["PHP_SELF"]."?pompier=$pompier&msg=$msg");
    exit(); 
}

#========================================================================================================
# FORM                                                                                                                                                
#========================================================================================================

writehead();
?>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.imgareaselect.min.js"></script>
<script>
function changeStatus() { 
    what=document.getElementById('image');
    if  ( what.value == '' ) {
        document.getElementById("upload").style.display = "none";
    }
    else {
        document.getElementById("upload").style.display = "block";
    }
    document.getElementById("photo").submit();
}
function redirect(pid) {
    self.location.href="upd_personnel.php?pompier="+pid;
}
</script>
<?php
//Only display the javacript if an image has been uploaded
if ( strlen($large_photo_exists) > 0 ) {
    $current_large_image_width = getWidth($large_image_location);
    $current_large_image_height = getHeight($large_image_location);
?>

<script type="text/javascript">
function preview(img, selection) { 
    var scaleX = <?php echo $thumb_width;?> / selection.width; 
    var scaleY = <?php echo $thumb_height;?> / selection.height; 
    
    $('#thumbnail + div > img').css({ 
        width: Math.round(scaleX * <?php echo $current_large_image_width;?>) + 'px', 
        height: Math.round(scaleY * <?php echo $current_large_image_height;?>) + 'px',
        marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px', 
        marginTop: '-' + Math.round(scaleY * selection.y1) + 'px' 
    });
    $('#x1').val(selection.x1);
    $('#y1').val(selection.y1);
    $('#x2').val(selection.x2);
    $('#y2').val(selection.y2);
    $('#w').val(selection.width);
    $('#h').val(selection.height);
}

$(document).ready(function () { 
    $('#save_thumb').click(function() {
        var x1 = $('#x1').val();
        var y1 = $('#y1').val();
        var x2 = $('#x2').val();
        var y2 = $('#y2').val();
        var w = $('#w').val();
        var h = $('#h').val();
        if ( x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h=="") {
            alert("Vous devez faire une sélection");
            return false;
        } else {
            return true;
        }
    });
}); 

$(window).load(function () { 
    $('#thumbnail').imgAreaSelect({ x1: 0, y1: 0, x2: 148, y2: 177, handle: true, aspectRatio: '1:<?php echo $thumb_height/$thumb_width;?>', onSelectChange: preview }); 
});

</script>
<?php 
} // FIN large_photo_exists 

echo "</head>
<body class='top50'>
<div align=center><p>";

echo  "<table cellpading=0 cellspacing=0 border=0 >
            <tr class='MenuRub'><td colspan=2 width=400>Photo de ".$pompierNomPrenom."</td></tr>
            <tr bgcolor=$mylightcolor><td>";

//Display error message if there are any
if ( strlen( $error ) > 0) {
    echo  "<div id='msgError' class='alert alert-danger' role='alert'><strong>Erreur! </strong>".$error."</div>";
}
if ( strlen( $msg ) > 0) {
    echo  "<div id='msgInfo' class='alert alert-info' role='alert'><strong>Info </strong>".$msg."</div>";
}

if ( strlen($large_photo_exists)>0 && strlen($thumb_photo_exists)>0) {
    echo "<div align=center><img src='".$thumb_image_location."?timestamp=".time()."' border='0' class='rounded'/>";
    echo "<p><a href='upd_personnel.php?pompier=".$pompier."' class='btn btn-success'>Confirmer</a>";
    echo " ou <a href='upd_personnel_photo.php?a=suppr&t=".$thumb_image_name.".jpg&P_ID=".$pompier."&pompier=".$pompier."' class='btn btn-danger'>Annuler</a>";
    echo "</p></div>";
    //Clear the time stamp session and user file extension
    $_SESSION['random_key']= "";
    $_SESSION['user_file_ext']= "";
}
else {
    // Affiche la photo si elle existe
    echo "<div id=\"AffichePhoto\" style=\"float:right;margin:10px;\">";
    
    $query="select P_PHOTO from pompier where P_ID=".$pompier;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    
    if ( $P_PHOTO <> "" and file_exists($trombidir."/".$P_PHOTO)) {
        $image = $trombidir."/".$P_PHOTO;
        $filedate = date("Y-m-d",filemtime($image));
        if ( $filedate == date("Y-m-d")) $image .="?timestamp=".time();
        echo "<form action='upd_personnel_photo.php' method=\"get\">";
        echo "<img src=\"".$image."\" class='rounded' border='0' />";
        echo "<input type=\"hidden\" name=\"a\" value=\"suppr\" />";
        echo "<input type=\"hidden\" name=\"t\" value=\"".$P_PHOTO."\" />";
        echo "<input type=\"hidden\" name=\"pompier\" id=\"pompier\" value=\"$pompier\" />";
        echo "<input type=\"hidden\" name=\"P_ID\" id=\"P_ID\" value=\"$pompier\" />";
        echo "<p align=center><input type=\"submit\" class='btn btn-danger' value=\" Supprimer\">";
        echo "</form>";
    }
    echo "</div>";
    
    if ( $imgtropgrande or $imgtroppetite) $st="display:none;";
    else $st="";
    
    // DEB Affiche les images pour créer la miniature
    if ( strlen($large_photo_exists)>0) {
        echo "Choisir un morceau de la photo
        <div align='center'>
            <img src=".$upload_path.$large_image_name.$_SESSION['user_file_ext']."  class='rounded'
                style='float: left; margin-right: 10px;' id='thumbnail' style=".$st."/>
            <br style='clear:both;'/>
            <form name='thumbnail' action='upd_personnel_photo.php' method='post'>
                <input type='hidden' name='x1' value='' id='x1' />
                <input type='hidden' name='y1' value='' id='y1' />
                <input type='hidden' name='x2' value='' id='x2' />
                <input type='hidden' name='y2' value='' id='y2' />
                <input type='hidden' name='w' value='' id='w' />
                <input type='hidden' name='h' value='' id='h' />
                <input type='hidden' name='P_ID' id='P_ID' value=".$pompier." />
                <p><input type='submit' class='btn btn-default' name='upload_thumbnail' value='Créer la photo' id='save_thumb' />
            </form>
        </div>";
    }
    
    echo "<small>
    <i class='far fa-circle'></i> Chercher la photo sur votre ordinateur avec le bouton <i class='fa fa-camera fa-lg' style='color:grey;'></i><br>
    <i class='far fa-circle'></i> Sélectionnez une zone sur l'image avec votre souris<br>
    <i class='far fa-circle'></i> Cliquez : Créer la photo<br>
    <i class='far fa-circle'></i> Cliquez sur le lien \"Enregistrer\"<br>
    <i class='far fa-circle'></i> Cliquer CTRL + F5 pour éviter que l'ancienne photo du cache ne s'affiche<br>
    <p>La photo générée fera 148px * 178 px. (taille d'une photo d'identité)</small>
    <form name='photo' id='photo' enctype='multipart/form-data' action='upd_personnel_photo.php' method='post'>
    
    <div align='center'>
    <label class='btn btn-success btn-file' title='Choisir une photo'>
    <i class='fa fa-camera fa-2x'></i><input type='file' id='image' name='image' style='display: none;' onchange='javascript:changeStatus();'>
    </label>
    <p><input type='hidden' id='upload' name='upload' />
    <input type='hidden' name='P_ID' id='P_ID' value=".$pompier." />
    </div>
    </form>";

}
echo "</td></tr></table>";
echo "<p><input type='button' class='btn btn-default' value='Retour' onclick=\"redirect('".$P_ID."');\">";
//writefoot();
?>
