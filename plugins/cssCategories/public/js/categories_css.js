<!--
/*
 * DO NOT REMOVE THIS NOTICE
 *
 * PROJECT:   mygosuMenu
 * VERSION:   1.2.0b
 * COPYRIGHT: (c) 2003,2004 Cezary Tomczak
 * LINK:    http://gosu.pl/dhtml/mygosumenu.html
 * LICENSE:   BSD (revised)
 *
 * MODIFIED:  2007,2008 Cameron Clark
 * CHANGES:   1) Close all other nodes when a new node is clicked
 *            2) Remembers current open node with a cookie
 *            3) Immediately assigns class of "on" to clicked menu item, removing from all other items
 *            4) Uses blur() to remove dotted outline from clicked links
 *            5) Allows option to make parent links either just show submenus, or also open corresponding pages
 */

function TreeMenu(id, openParentPages) {

  this.init = function() {
    if (!document.getElementById(this.id)) {
      return;
      }
    document.getElementById(this.id).className = "tree-menu";  // change class name to invoke tree menu styles
    this.parse(document.getElementById(this.id).childNodes, this.tree, this.id, 0);
    this.load();
    addDOMEvent(window,'unload',function(e) { self.save(); },false);
    }

  this.parse = function(nodes, tree, id, depth) {
    var a, lastLi;
    for (var i = 0; i < nodes.length; i++) {
      if (nodes[i].nodeType != 1) {
        continue;
        }
      if (nodes[i].tagName.toLowerCase() == "li") {
        lastLi = nodes[i];
        nodes[i].id = id + "-" + tree.length;
        tree[tree.length] = new Array();
        if (a = this.getA(nodes[i].childNodes)) {
          a.id = nodes[i].id + "-a";
          if (hasClassName(a,"on")) {
            this.id_activenode = nodes[i].id;
            }
          }
        if (nodes[i].childNodes && this.hasUl(nodes[i].childNodes)) {
          nodes[i].className = (depth == 0) ? "top-section" : "section";
          if (a) {
            if (depth == 0) {
              if (this.openParentPages) // enable links on parent items (links to parent category pages work)
                eval("document.getElementById('"+a.id+"').onclick = function() {this.blur(); self.setActive(this); return self.click('"+nodes[i].id+"');}");
              else                     // disable links on parent items (so clicking them just opens submenus, not the page)
                eval("document.getElementById('"+a.id+"').onclick = function() {this.blur(); self.click('"+nodes[i].id+"'); return false;}");
              }
            else
              eval("document.getElementById('"+a.id+"').onclick = function() {this.blur(); self.setActive(this); return self.click('"+nodes[i].id+"');}");
            }
          }
        else {
          nodes[i].className = (depth == 0) ? "top-item" : "item";
          if (a) {
            if (depth == 0) {
              a.id = nodes[i].id + "-a";
              eval("document.getElementById('"+a.id+"').onclick = function() {this.blur(); self.closeAll('"+nodes[i].id+"'); self.setActive(this); return true;}");
              }
            else {
              eval("document.getElementById('"+a.id+"').onclick = function() {this.blur(); self.setActive(this); return true;}");
              }
            }
          }
        }
      if (nodes[i].tagName.toLowerCase() == "ul") {
        nodes[i].style.display = "none";
        id = id + "-" + (tree.length - 1);
        nodes[i].id = id + "-section";
        tree = tree[tree.length - 1];
        }
      if (nodes[i].childNodes) {
        this.parse(nodes[i].childNodes, tree, id, depth+1); // run recursively through menu tree
        }
      }
    if (lastLi) {
      lastLi.className = lastLi.className + "-end";
      }
    }

  this.hasUl = function(nodes) {
    for (var i = 0; i < nodes.length; i++) {
      if (nodes[i].nodeType != 1) {
        continue;
        }
      if (nodes[i].tagName.toLowerCase() == "ul") {
        return true;
        }
      if (nodes[i].childNodes) {
        if (this.hasUl(nodes[i].childNodes)) {
          return true;
          }
        }
      }
    return false;
    }

  this.getA = function(nodes) {
    for (var i = 0; i < nodes.length; i++) {
      if (nodes[i].nodeType == 1) {
        if (nodes[i].tagName.toLowerCase() == "a") {
          return nodes[i];
          }
        return false;
        }
      }
    }

  this.setActive = function(a) {
    var links = document.getElementById(this.id).getElementsByTagName("A");
    for (var i = 0; i < links.length; i++) {
      links[i].className = '';
      }
    a.className = "on";
    }
  
  this.click = function(id) {
    childList = document.getElementById(id + "-section");
    if (childList) {
      if (childList.style.display == "none") {
        this.show(id);
        this.hideOthers(document.getElementById(this.id).childNodes,id); // pass top-level menu object to start recursion
        this.id_opennode = id;                                           // set current node for saving in cookie
        return true;
        }
      else {
        this.hide(id);
        this.id_opennode = (id.split("-").length > 2) ? id.substr(0,id.lastIndexOf("-")) : ""; // set parent node, or if at top level, set to no id, for saving in cookie
        return false;
        }
      }
    }

  this.show = function(id) {
    childList  = document.getElementById(id + "-section");
    parentItem = document.getElementById(id);
    if (childList) {
      childList.style.display = "";
      parentItem.className = parentItem.className.replace(/section(-open)?/, "section-open");
      }
    }

  this.hide = function(id) {
    childList  = document.getElementById(id + "-section");
    parentItem = document.getElementById(id);
    if (childList) {
      childList.style.display = "none";
      parentItem.className = parentItem.className.replace(/section(-open)?/, "section");
      }
    }

  // runs through child nodes recursively to hide all but current node and its parents
  this.hideOthers = function(nodes,id) {
    for (var i = 0; i < nodes.length; i++) {
      if (nodes[i].nodeType == 1 && nodes[i].tagName.toLowerCase() == "li") { // find all child <li> elements
        childList = document.getElementById(nodes[i].id + "-section");
        if (childList) {
          if (id.indexOf(nodes[i].id) == -1) {  // if this is not the current node or one of its parents, hide it
            this.hide(nodes[i].id);
            }
          if (id != nodes[i].id && childList.childNodes) {  // if this is not the current node and it has child nodes, run this function recursively to hide them
            this.hideOthers(childList.childNodes,id);
            }
          }
        }
      }
    }

  this.closeAll = function(id) {
    this.hideOthers(document.getElementById(this.id).childNodes,'');
    this.id_opennode = '';
    }

  this.showParents = function(id) { // Note that this will work backwards from closest to farthest ancestor nodes
    var idPieces = id.split("-");
    var depth = idPieces.length-2;
    for (var p = 0; p < depth; p++) {
      idPieces.pop();
      this.show(idPieces.join("-"))
      }
    }

  this.save = function() {
    if (this.id_opennode) {
      this.cookie.set(this.id, this.id_opennode);
      }
    else {
      this.cookie.del(this.id);
      }
    }

  this.load = function() {
    var id_savednode = this.cookie.get(this.id);
    if (this.id_activenode) {
      this.id_opennode = this.id_activenode;
      }
    else if (id_savednode) {
      this.id_opennode = id_savednode;
      }
    if (this.id_opennode) {
      this.showParents(this.id_opennode);
      this.show(this.id_opennode);
      }
    }

  function Cookie() {
    this.get = function(name) {
      var cookies = document.cookie.split(";");
      for (var i = 0; i < cookies.length; i++) {
        var a = cookies[i].split("=");
        if (a.length == 2) {
          a[0] = a[0].trim();
          a[1] = a[1].trim();
          if (a[0] == name) {
            return unescape(a[1]);
            }
          }
        }
      return "";
      }
    this.set = function(name, value) {
      var date = new Date();
      date.setTime(date.getTime()+(24*60*60*1000)); // save for 1 day
      document.cookie = name + "=" + escape(value) + "; expires=" + date.toGMTString() + "; path=/";
      }
    this.del = function(name) {
      document.cookie = name + "=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/";
      }
    }

  var self = this;
  this.id = id;
  this.openParentPages = openParentPages;
  this.tree = new Array();
  this.cookie = new Cookie();
  this.init();
  }

// Define trim function
if (typeof String.prototype.trim == "undefined") {
  String.prototype.trim = function() {
    var s = this.replace(/^\s*/, "");
    return s.replace(/\s*$/, "");
    }
  }

// Load menu immediately
new TreeMenu('siteMenu',0);

// Iterates through all class names for an object and returns true if specified class name is found
function hasClassName(obj, className) {
  if (obj && obj.className) {
    var objClass = obj.className.trim();
    arrClasses = objClass.split(" ");
    for (var c=0; c<arrClasses.length; c++) {
      if (className == arrClasses[c])
        return true;
      }
    }
  return false;
  }

// For adding new events to any object (like preloads in tpl_categories_css.php)
function addDOMEvent(elm, evType, fn, useCapture) {
  if (elm.addEventListener) {
    elm.addEventListener(evType, fn, useCapture);
    return true;
    }
  else if (elm.attachEvent) {
    var r = elm.attachEvent('on' + evType, fn);
    return r;
    }
  else {
    elm['on' + evType] = fn;
    }
  }

// Accept any number of image sources for preloadings (separate with commas)
function preloadImages() {
  if (document.images) {
    if (!document.preloads)
      document.preloads = new Array();
    var i, j, p = document.preloads.length, a = preloadImages.arguments;
    // Check to see if the preload already exists in the array, and if so, exit function
    for (i=0; i<p; i++) {
      for (j=0; j<a.length; j++) {
        if (document.preloads[i].src == a[j])
          return;
        }
      }
    // Add preload to array
    for (j=0; j<a.length; j++) {
      document.preloads[p]       = new Image;
      document.preloads[p++].src = a[j];
      }
    }
  }
// -->
