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
 *
 * $Id: productPicker.js 1884 2009-01-19 01:54:24Z dermanomann $
 */


// create picker
function ProductPicker(id, selectSingle, baseUrl, handler) {
    this.id = selectSingle;
    this.selectSingle = selectSingle;
    //TODO: construct vis JS alone
    this.baseUrl = baseUrl;
    this.handler = handler;
    this.products = new Array();
    this.categoryCache = new Array();
}

// implementation
ProductPicker.prototype = {
    // picker closed
    close: function() {
        if (this.handler) {
            this.handler(this.products);
        }
        this.done();
    },

    cancel: function() {
        this.done();
    },

    done: function() {
        tb_remove();
        $('#picker-prod-list').html('');
        $('#picker-pages').html('');
        $('#picker-selected').html('');
        this.products = new Array();
    },

    // product selected
    picked: function(elem, productId) {
        if (!this.isSelected(productId)) {
            this.products.push(productId);
            $(elem).addClass('selected');
            if (this.selectSingle) {
                this.close();
            }
        } else {
            // remove
            for (var ii=0; ii<this.products.length; ++ii) {
                if (this.products[ii] == productId) {
                    this.products.splice(ii, 1);
                }
            }
            // update selected area
            $(elem).removeClass('selected');
        }
        var list = '';
        for (var ii=0; ii<this.products.length; ++ii) {
            if (0 < ii) {
                list += ', ';
            }
            list += '<span>'+this.products[ii]+'</span>';
        }
        $('#picker-selected').html(list);
    },

    // is product already selected?
    isSelected: function(productId) {
        for (var ii=0; ii<this.products.length; ++ii) {
            if (this.products[ii] == productId) {
                return true;
            }
        }
        return false;
    },

    // show/hide loading throbber
    loading: function(on) {
        $('#picker-prod-loading').css('display', on ? 'block' : 'none');
    },

    // display products
    displayResults: function(resultList, categoryId) {
        prodList = $('#picker-prod-list');
        prodList.html('');
        var html = '';
        for (var jj=0; jj < resultList.results.length; ++jj) {
            var item = resultList.results[jj];
            var attr = '';
            if (this.isSelected(item.id)) {
                attr = ' class="selected"';
            }
            html += '<a '+attr+'href="#" onclick="productPicker.picked(this, '+item.id+')">'+item.name+'</a>';
        }
        prodList.html(html);
        var pages = 'Page ' + resultList.pageNumber + ' of ' + resultList.numberOfPages+':&nbsp;&nbsp;&nbsp;';
        if (1 < resultList.numberOfPages) {
            // display/update page links
            for (var kk=1; kk <= resultList.numberOfPages; ++kk) {
                if (kk == resultList.pageNumber) {
                    pages += '<span class="current">['+kk+']</span>';
                } else {
                    // XXX: how to avoid using productPicker??
                    pages += '<a href="#" onclick="productPicker.categoryClick('+categoryId+', '+kk+');return false;" >'+kk+'</a>&nbsp;';
                }
            }
        }
        $('#picker-pages').html(pages);
    },

    // category clicked
    categoryClick: function(categoryId, page) {
        if (!page) {
            page = 1;
        }
        var cacheKey = categoryId+'-'+page;
        if (cacheKey in this.categoryCache) {
            // use cached results
            this.displayResults(this.categoryCache[cacheKey], categoryId);
            return;
        }
        var ajaxUrl = this.baseUrl+'&categoryId='+categoryId+'&pagination=20&page='+page;
        var me = this;
        this.loading(true);
        $.getJSON(ajaxUrl, function(resultList) {
            me.loading(false);
            me.displayResults(resultList, categoryId);
            me.categoryCache[cacheKey] = resultList;
        });
    },

    // init
    init: function() {
        $(document).ready(function() {
          $('#picker-tree a.tree-cat-url').each(function (i) {
            var classes = this.className.split(' ');
            for (var ii=0; ii<classes.length; ++ii) {
              if (0 == classes[ii].indexOf('c:')) {
                var token = classes[ii].split(':');
                if (0 < token[1]) {
                  $(this).click(function() {
                    // XXX: how to avoid using productPicker??
                    productPicker.categoryClick(token[1], 1);
                    return false;
                  });
                }
                break;
              }
            }
          });
        });
    }
}
