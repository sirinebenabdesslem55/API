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
@session_start();
$dbc=connect();
if ( ! isset($_SESSION['id']) ) {
         if ( isset($_GET['counter'])) echo " ";
           else echo "<body onload=\"javascript:top.location.href='lost_session.php';\" />";
           exit;
}
$id=$_SESSION['id'];

// enregistrer un nouveau message
if (isset($_GET['msg'])){
    destroy_my_session_if_forbidden($id);
    $msg = ".";
    $msg  = isset($_GET['msg']) ? htmlspecialchars(fixcharset($_GET['msg'])) : ".";
    $chat_colors = array('#ff0000','#ff00ff','#0000ff','#00baff','#008e00',
                         '#ff6900','#7f0000','#7f007f','#00007f','#007f7f',
                         '#004200','#827f00','#000000','#333333','#4c4c4c',
                         '#dc007f','#889f00','#ff6f6f','#756f97','#00b992',
                         '#006852','#3b003b','#3b273b','#a5273b','#4141ff');
    $i=($id +date('d'))%25;
    $usercolor=$chat_colors[$i];
    
    $query="update audit set A_FIN =NOW(), A_LAST_PAGE='chat' where P_ID=".$id." and A_DEBUT >='".$_SESSION['SES_DEBUT']."'";
    $result=mysqli_query($dbc,$query);    
     
    $destid=get_global_granted(62);
    $query="insert into chat (P_ID, C_MSG, C_DATE, C_COLOR)
            values (".$id.",\"".$msg." \",NOW(),'".$usercolor."')";
    $result=mysqli_query($dbc,$query);
    $username = fixcharset(strtoupper($_SESSION['SES_NOM'])." ".ucfirst($_SESSION['SES_PRENOM']));
        
    // envoyer mail de notification
    if ( $destid <> "" ) {
        $subject=fixcharset("Nouveau commentaire de ".$username." sur la messagerie instantanée");
        $text=str_replace("\"","",$msg);
        mysendmail("$destid" , $id , $subject , $text);
    }
    specific_chat_cleanup();
    
    $query="select count(*) as NB, date_format(NOW(),'%H:%i:%s') C_DATE from chat ";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $nb=$row["NB"];
    
    echo "<br><b><font color='".$usercolor."'>".$username."</font></b><font size=1> - ".$row["C_DATE"]."</font> : ".$msg;
    
    if ( $nb > $maxchatmessages + 10 ) {
        $query="delete from chat order by C_DATE asc limit 10 ";
        $result=mysqli_query($dbc,$query);
    }
} 
// afficher les messages
else if (isset($_GET['all'])) {
    $content = "";
    $query="select P_NOM,P_PRENOM, chat.C_ID, pompier.P_ID, C_MSG, C_COLOR , date_format(C_DATE,'%d-%m-%Y - %H:%i') C_DATE 
            from chat, pompier
            where pompier.P_ID= chat.P_ID";
    $query .= " order by chat.C_ID";
    $result=mysqli_query($dbc,$query);
    $k=0;
    while ($row=mysqli_fetch_array($result)) {
        if ( $k > 0 ) $content .= "<br>";
        $k++ ;
        $content .= "<b><font color='".$row["C_COLOR"]."'>".fixcharset(strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]))."</font>
                        </b><font size=1> - ".$row["C_DATE"]."</font> : ".$row["C_MSG"];
        if ( check_rights($id, 14)) {
            $content .= " <a href=chat.php?del=".$row["C_ID"]."><i class='fa fa-trash-alt' title='supprimer ce message'></i></a>";
        }
    }
    echo $content;
}
// afficher les nombre d'utilisateurs connectés
else if ( isset($_GET['counter'])) {

    $query="select count(distinct P_ID) as NB from audit 
            where ( A_DEBUT > DATE_SUB(now(), INTERVAL 10 MINUTE)
                 or A_FIN > DATE_SUB(now(), INTERVAL 3 MINUTE))";
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $NB=$row["NB"];
    
    $query="select count(1) as m from chat
    where C_DATE > DATE_SUB(now(), INTERVAL 1 MINUTE)";
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $m=$row["m"];
    
    if ( $m > 0 ) $class='red-badge';
    else  $class='simple-badge';

    echo "<span class='".$class."' title='En ligne: $NB utilisateurs' >".$NB."</span>";

}
?>
