/*
* @author Tatvic Interactive
* Email : info@liftsuggest.com
* URL : http://www.liftsuggest.com
* Description : LiftSuggest Recommendations is the module that helps you show recommendations for your products to users/visitors on product pages and/or shopping cart page. This will help in increasing the average order value and conversion rate of your site.
* File : lift.js
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see license.txt
* LiftSuggest Recommendations is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*/

// Strip leading and trailing white-space
String.prototype.trim = function() { return this.replace(/^\s*|\s*$/g, ''); }

// Check if string is empty
String.prototype.empty = function() {
    if (this.length == 0)
        return true;
    else if (this.length > 0)
        return /^\s*$/.test(this);
}

// Breaks cookie into an object of keypair cookie values
function crumbleCookie(c)
{
    var cookie_array = document.cookie.split(';');
    var keyvaluepair = {};
    for (var cookie = 0; cookie < cookie_array.length; cookie++)
    {
        var key = cookie_array[cookie].substring(0, cookie_array[cookie].indexOf('=')).trim();
        var value = cookie_array[cookie].substring(cookie_array[cookie].indexOf('=')+1, cookie_array[cookie].length).trim();
        keyvaluepair[key] = value;
    }

    if (c)
        return keyvaluepair[c] ? keyvaluepair[c] : null;

    return keyvaluepair;
}

/**
 *  For GA cookie explanation, see http://services.google.com/analytics/breeze/en/ga_cookies/index.html.
 *
 *  @return             -   <void>
 *
 *  @pre-condition      -   pageTracker initialised properly
 *  @post-condition     -   provides 'get' methods to access specific values in the Google Analytics cookies
 */
function gaCookies()
{
    // Cookie syntax: domain-hash.unique-id.ftime.ltime.stime.session-counter
    var utma = function() {
        var utma_array;

        if (crumbleCookie('__utma'))
            utma_array =  crumbleCookie('__utma').split('.');
        else
            return null;

        var domainhash = utma_array[0];
        var uniqueid = utma_array[1];
        var ftime = utma_array[2];
        var ltime = utma_array[3];
        var stime = utma_array[4];
        var sessions = utma_array[5];

        return {
            'cookie': utma_array,
            'domainhash': domainhash,
            'uniqueid': uniqueid,
            'ftime': ftime,
            'ltime': ltime,
            'stime': stime,
            'sessions': sessions
        };
    };

    // Cookie syntax: domain-hash.gif-requests.10.stime
    var utmb = function() {
        var utmb_array;

        if (crumbleCookie('__utmb'))
            utmb_array = crumbleCookie('__utmb').split('.');
        else
            return null;
        var gifrequest = utmb_array[1];

        return {
            'cookie': utmb_array,
            'gifrequest': gifrequest
        };
    };

    // Cookie syntax: domain-hash.value
    var utmv = function() {
        var utmv_array;

        if (crumbleCookie('__utmv'))
            utmv_array = crumbleCookie('__utmv').split('.');
        else
            return null;

        var value = utmv_array[1];

        return {
            'cookie': utmv_array,
            'value': value
        };
    };

    // Cookie syntax: domain-hash.ftime.?.?.utmcsr=X|utmccn=X|utmcmd=X|utmctr=X
    var utmz = function() {
        var utmz_array, source, medium, name, term, content, gclid;

        if (crumbleCookie('__utmz'))
            utmz_array = crumbleCookie('__utmz').split('.');
        else
            return null;

        var utms = utmz_array[4].split('|');
        for (var i = 0; i < utms.length; i++) {
            var key = utms[i].substring(0, utms[i].indexOf('='));
            var val = decodeURIComponent(utms[i].substring(utms[i].indexOf('=')+1, utms[i].length));
            val = val.replace(/^\(|\)$/g, '');  // strip () brackets
            switch(key)
            {
                case 'utmcsr':
                    source = val;
                    break;
                case 'utmcmd':
                    medium = val;
                    break;
                case 'utmccn':
                    name = val;
                    break;
                case 'utmctr':
                    term = val;
                    break;
                case 'utmcct':
                    content = val;
                    break;
                case 'utmgclid':
                    gclid = val;
                    break;
            }
        }

        return {
            'cookie': utmz_array,
            'source': source,
            'medium': medium,
            'name': name,
            'term': term,
            'content': content,
            'gclid': gclid
        };
    };

    // Establish public methods

    // utma cookies
    this.getDomainHash = function() { return (utma() && utma().domainhash) ? utma().domainhash : null };
    this.getUniqueId = function() { return (utma() && utma().uniqueid) ? utma().uniqueid : null };

    this.getInitialVisitTime = function() { return (utma() && utma().ftime) ? utma().ftime : null };
    this.getPreviousVisitTime = function() { return (utma() && utma().ltime) ? utma().ltime : null };
    this.getCurrentVisitTime = function() { return (utma() && utma().stime) ? utma().stime : null };
    this.getSessionCounter = function() { return (utma() && utma().sessions) ? utma().sessions : null };

    // utmb cookies
    this.getGifRequests = function() { return (utmb() && utmb().gifrequest) ? utmb().gifrequest : null };

    // utmv cookies
    this.getUserDefinedValue = function () { return (utmv() && utmv().value) ? decodeURIComponent(utmv().value) : null };

    // utmz cookies
    this.getCampaignSource = function () { return (utmz() && utmz().source) ? utmz().source : null };
    this.getCampaignMedium = function () { return (utmz() && utmz().medium) ? utmz().medium : null };
    this.getCampaignName = function () { return (utmz() && utmz().name) ? utmz().name : null };
    this.getCampaignTerm = function () { return (utmz() && utmz().term) ? utmz().term : null};
    this.getCampaignContent = function () { return (utmz() && utmz().content) ? utmz().content : null };
    this.getGclid = function () { return (utmz() && utmz().gclid) ? utmz().gclid : null };
}

function gaCookieRead()
{
	var gac = new gaCookies();

	var val = gac.getUniqueId();
	if (val != null)
	{
		_gaq.push(['_setCustomVar',
			5,				// This custom var is set to slot #1.  Required parameter.
			'VisitorID',	// The top-level name for your online content categories.  Required parameter.
			gac.getUniqueId(),		// Sets the value of "Section" to "Life & Style" for this particular aricle.  Required parameter.
			1				// Sets the scope to page-level.  Optional parameter.
		]);
		
		//alert(gac.getUniqueId());
		clearTimeout(mytime);
	}
	else
	{
		mytime = setTimeout('gaCookieRead()', 2000);
	}

}
/*
function liftGATracking(ga_type, ga_acc_id, product_sku, pagetracker_type){
    var gac = new gaCookies();
    var vid = gac.getUniqueId();

	if(ga_type == 1) { // Synchronous/Traditional
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		try {
			//var pageTracker=_gat._getTracker(ga_acc_id);
			//pageTracker._setCustomVar(5,pagetracker_type,vid + "_" + product_sku,3);
		//	pageTracker._trackPageview();
		}
		catch(err) {

		}
	}
	else if(ga_type == 0) { // Asynchronous
		if(typeof(_gat)!='object')document.write('<sc'+'ript src="http'+(document.location.protocol=='https:'?'s://ssl':'://www')+'.google-analytics.com/ga.js"></sc'+'ript>')
		_gaq.push(['_setAccount', ga_acc_id]);
		_gaq.push(['_setCustomVar',5,'Vid_ProdSku_Price_Recomm5',
			vid +"_"+ product_sku + "_" +product_price +"_"+ rec   ,3]);
		_gaq.push(['_setCustomVar',4,'Vid_ProdSku_Price_Recomm4',
			vid +"_"+ product_sku + "_" +product_price +"_"+ rec   ,3]);
		_gaq.push(['_setCustomVar',3,'Vid_ProdSku_Price_Recomm3',
			vid +"_"+ product_sku + "_" +product_price +"_"+ rec   ,3]);
		_gaq.push(['_setCustomVar',2,'Vid_ProdSku_Price_Recomm2',
			vid +"_"+ product_sku + "_" +product_price +"_"+ rec   ,3]);
		_gaq.push(['_setCustomVar',1,'Vid_ProdSku_Price_Recomm1',
			vid +"_"+ product_sku + "_" +product_price +"_"+ rec   ,3]);
		_gaq.push(['_trackPageview']);
		
		
		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	}
}

mytime = setTimeout('gaCookieRead()', 10000);

*/