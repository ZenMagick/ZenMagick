/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
$(function() {
  $(".db-column").sortable({
    connectWith: '.db-column',
    handle: '.portlet-grip',
    cursor: 'move'
  });

  $(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
    .find(".portlet-header")
      .addClass("ui-widget-header ui-corner-all")
      .html(function(index, oldhtml) { return '<div class="portlet-grip">'+oldhtml+'</div>'; })
      // add icons
      .prepend('<span class="ui-icon ui-icon-closethick"></span><span class="ui-icon ui-icon-minusthick"></span></span><span class="ui-icon ui-icon-wrench"></span>')
      .end()
    .find(".portlet-content")
    .css('display', function(index, value) {
      // fix open/close icon depending on initial state
      $(this).parents(".portlet:first .ui-icon-minusthick").removeClass("ui-icon-minusthick").addClass("ui-icon-plusthick");
    })
    ;

  // open/close
  $(".portlet-header .ui-icon-minusthick, .portlet-header .ui-icon-plusthick").click(function() {
    $(this).toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick");
    $(this).parents(".portlet:first").find(".portlet-content").toggle();
  });
  // remove
  $(".portlet-header .ui-icon-closethick").click(function() {
    $(this).parents('.portlet').css('display', 'none');
  });

  $(".portlet-grip").hover(
    function() { $(this).css('cursor', 'move'); }, 
    function() { $(this).css('cursor', 'auto'); }
  );
  
});
