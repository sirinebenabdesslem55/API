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
  
# class taken form this location. 
# http://net.tutsplus.com/tutorials/php/how-to-paginate-data-with-php 
  
class Paginator{  
    var $items_per_page;
    var $items_total;
    var $current_page;
    var $num_pages;
    var $mid_range;
    var $page_name;
    var $low;
    var $high;
    var $limit;
    var $return;
    var $default_ipp = 20;
  
    function __construct() 
    {  
        $this->current_page = 1;
        $this->mid_range = 7;
        $this->page_name = $_SERVER["PHP_SELF"];
        
        if (!empty($_GET['ipp'])) {
             $this->items_per_page =  $_GET['ipp'];
             $_SESSION['ipp'] = $_GET['ipp'];
        }
        else if (!empty($_SESSION['ipp'])) $this->items_per_page = $_SESSION['ipp'];
        else $this->items_per_page = $this->default_ipp;
    }  
  
    function paginate()
    {  
        // get input parameters       
        if (!empty($_GET['page'])) {
             $_page =  intval($_GET['page']);
             $_SESSION['page'] = intval($_GET['page']);
        }
        else if (!empty($_SESSION['page'])) $_page = intval($_SESSION['page']);
        else $_page = 1;
        if ( $_page == 0 ) $_page = 1;
        
         if (!empty($_GET['ipp'])) {
             $_ipp =  $_GET['ipp'];
             $_SESSION['ipp'] = $_GET['ipp'];
        }
        else if (!empty($_SESSION['ipp'])) $_ipp = $_SESSION['ipp'];
        else $_ipp = $this->default_ipp;
         
        if($_ipp == 'All')
        {  
            $this->num_pages = ceil($this->items_total/$this->default_ipp);
            $this->items_per_page = $this->default_ipp;
        }  
        else  
        {  
            if(!is_numeric($this->items_per_page) OR $this->items_per_page <= 0) $this->items_per_page = $this->default_ipp;
            $this->num_pages = ceil($this->items_total/$this->items_per_page);
        }  
        $this->current_page = $_page;
        if($this->current_page < 1 Or !is_numeric($this->current_page)) $this->current_page = 1;
        if($this->current_page > $this->num_pages) $this->current_page = $this->num_pages;
        $prev_page = $this->current_page-1;
        $next_page = $this->current_page+1;
  
        if($this->num_pages > 10)
        {  
            $this->return = ($this->current_page != 1 And $this->items_total >= 10) ? "<a class=\"paginate\" href=\"$this->page_name?page=$prev_page&ipp=$this->items_per_page\">« Préc.</a> ":"<span class=\"inactive\" href=\"#\">« Préc.</span> ";
  
            $this->start_range = $this->current_page - floor($this->mid_range/2);
            $this->end_range = $this->current_page + floor($this->mid_range/2);
  
            if($this->start_range <= 0)
            {  
                $this->end_range += abs($this->start_range)+1;
                $this->start_range = 1;
            }  
            if($this->end_range > $this->num_pages)
            {  
                $this->start_range -= $this->end_range-$this->num_pages;
                $this->end_range = $this->num_pages;
            }  
            $this->range = range($this->start_range,$this->end_range);
  
            for($i=1;$i<=$this->num_pages;$i++)
            {  
                if($this->range[0] > 2 And $i == $this->range[0]) $this->return .= " ... ";
                // loop through all pages. if first, last, or in range, display  
                if($i==1 Or $i==$this->num_pages Or in_array($i,$this->range))
                {  
                    $this->return .= ($i == $this->current_page And $_page != 'All') ? "<a title=\"Go to page $i of $this->num_pages\" class=\"current\" href=\"#\">$i</a> ":"<a class=\"paginate\" title=\"Go to page $i of $this->num_pages\" href=\"$this->page_name?page=$i&ipp=$this->items_per_page\">$i</a> ";
                }  
                if($this->range[$this->mid_range-1] < $this->num_pages-1 And $i == $this->range[$this->mid_range-1]) $this->return .= " ... ";
            }  
            $this->return .= (($this->current_page != $this->num_pages And $this->items_total >= 10) And ($_page != 'All')) ? "<a class=\"paginate\" href=\"$this->page_name?page=$next_page&ipp=$this->items_per_page\">Suiv. »</a>\n":"<span class=\"inactive\" href=\"#\">» Suiv.</span>\n";
            $this->return .= ($_page == 'All') ? "<a class=\"current\" style=\"margin-left:10px\" href=\"#\">All</a> \n":"<a class=\"paginate\" style=\"margin-left:10px\" href=\"$this->page_name?page=1&ipp=All\">All</a> \n";
        }  
        else  
        {  
            for($i=1;$i<=$this->num_pages;$i++)
            {  
                $this->return .= ($i == $this->current_page) ? "<a class=\"current\" href=\"#\">$i</a> ":"<a class=\"paginate\" href=\"$this->page_name?page=$i&ipp=$this->items_per_page\">$i</a> ";
            }  
            $this->return .= "<a class=\"paginate\" href=\"$this->page_name?page=1&ipp=All\">All</a> \n";
        }  
        $this->low = ($this->current_page-1) * $this->items_per_page;
        if ( $this->low < 0 ) $this->low = 0;
        $this->high = ($_ipp == 'All') ? $this->items_total:($this->current_page * $this->items_per_page)-1;
        $this->limit = ($_ipp == 'All') ? "":" LIMIT $this->low,$this->items_per_page";
    }  
  
    function display_items_per_page()
    {  
        $items = '';
        
        if (!empty($_GET['ipp'])) {
             $this->items_per_page =  $_GET['ipp'];
             $_SESSION['ipp'] = $_GET['ipp'];
        }
        else if (!empty($_SESSION['ipp'])) $this->items_per_page = $_SESSION['ipp'];
        else $this->items_per_page = $this->default_ipp;
        
        $ipp_array = array(10,20,30,50,80,100,'All');
        foreach($ipp_array as $ipp_opt)  $items .= ($ipp_opt == $this->items_per_page) ? "<option selected value=\"$ipp_opt\">$ipp_opt</option>\n":"<option value=\"$ipp_opt\">$ipp_opt</option>\n";
        return "<span class=\"paginate\">Lignes par page </span><select class=\"paginate\" onchange=\"window.location='$this->page_name?page=1&ipp='+this[this.selectedIndex].value;return false\">$items</select>\n";
    }  
  
    function display_jump_menu()
    {  
        $option="";
        
        for($i=1;$i<=$this->num_pages;$i++)
        {  
            $option .= ($i==$this->current_page) ? "<option value=\"$i\" selected>$i</option>\n":"<option value=\"$i\">$i</option>\n";
        }  
        return "<span class=\"paginate\">Page </span><select class=\"paginate\" onchange=\"window.location='$this->page_name?page='+this[this.selectedIndex].value+'&ipp=$this->items_per_page';return false\">$option</select>\n";
    }  
  
    function display_pages()
    {  
        return $this->return;
    }  
}  
?>  
