<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"  lang="en" xml:lang="en">
<head>
  <link rel="stylesheet" type="text/css" href="media/style/<?php echo $theme; ?>/style.css" />
  <script src="http://yandex.st/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
  <style type="text/css">
  </style>
  <script type="text/javascript">
  var colorBoxOpt = {iframe:true, innerWidth:700, innerHeight:400, opacity:0.5};
  $.fn.tabs = function(){
    var parent = $(this);
    var tabNav = $('div.tab-row',this);
    var tabContent = $('div.tab-page',this);
    $('h2.tab',tabNav).each(function(i){
      $(this).click(function(){
        $('h2.tab',tabNav).removeClass('selected');
        $('h2.tab',tabNav).eq(i).addClass('selected');
        tabContent.hide();
        tabContent.eq(i).show();
        return false;
      });
    });
  }
  var tree = false;
  
  function postForm(action, id, value){
    document.module.action.value=action;
    if (id != null) document.module.item_id.value=id;
    if (value != null) document.module.item_val.value=value;
      document.module.submit();
  }
  </script>
</head>
<body>

<br />
<div class="sectionHeader">1C Exchange</div>
<div class="sectionBody" style="min-height:250px;">
  <ul class="actionButtons" style="float:left">
    <li><a href="#" onclick="postForm('load_attributes',null,null);return false;"><img src="media/style/<?php echo $theme; ?>/images/icons/table.gif" alt="">&nbsp; Свойства номенклатуры</a></li>
  </ul>
  <ul class="actionButtons" style="float:left">
    <li><a href="#" onclick="postForm('load_options',null,null);return false;"><img src="media/style/<?php echo $theme; ?>/images/icons/table.gif" alt="">&nbsp; Харктеристики номенклатуры</a></li>
  </ul>
  <ul class="actionButtons" style="float:left">
    <li><a href="#" onclick="postForm('',null,null);return false;"><img src="media/style/<?php echo $theme; ?>/images/icons/refresh.gif" alt="">&nbsp; Настройки</a></li>
  </ul>
  <div class="clear"></div>


