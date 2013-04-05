/*
  textfill
 @name      jquery.textfill.js
 @author    Russ Painter
 @author    Yu-Jie Lin
 @version   0.3.3
 @date      2013-03-26
 @copyright (c) 2012-2013 Yu-Jie Lin
 @copyright (c) 2009 Russ Painter
 @license   MIT License
 @homepage  https://github.com/jquery-textfill/jquery-textfill
 @example   http://jquery-textfill.github.com/jquery-textfill/Example.htm
*/
(function(g){g.fn.textfill=function(n){function l(c,a,e,h,f,j){function d(a,b){var c=" / ";a>b?c=" > ":a==b&&(c=" = ");return c}b.debug&&console.debug(c+"font: "+a.css("font-size")+", H: "+a.height()+d(a.height(),e)+e+", W: "+a.width()+d(a.width(),h)+h+", minFontPixels: "+f+", maxFontPixels: "+j)}function m(b,a,e,h,f,j,d,k){for(l(b+": ",a,f,j,d,k);d<k-1;){var g=Math.floor((d+k)/2);a.css("font-size",g);if(e.call(a)<=h){if(d=g,e.call(a)==h)break}else k=g;l(b+": ",a,f,j,d,k)}a.css("font-size",k);e.call(a)<=
h&&(d=k,l(b+"* ",a,f,j,d,k));return d}var b=jQuery.extend({debug:!1,maxFontPixels:40,minFontPixels:4,innerTag:"span",widthOnly:!1,callback:null,complete:null,explicitWidth:null,explicitHeight:null},n);this.each(function(){var c=g(b.innerTag+":visible:first",this),a=b.explicitHeight||g(this).height(),e=b.explicitWidth||g(this).width(),h=c.css("font-size");b.debug&&(console.log("Opts: ",b),console.log("Vars: maxHeight: "+a+", maxWidth: "+e));var f=b.minFontPixels,j=0>=b.maxFontPixels?a:b.maxFontPixels,
d=void 0;b.widthOnly||(d=m("H",c,g.fn.height,a,a,e,f,j));f=m("W",c,g.fn.width,e,a,e,f,j);b.widthOnly?c.css("font-size",f):c.css("font-size",Math.min(d,f));b.debug&&console.debug("Final: "+c.css("font-size"));(c.width()>e||c.height()>a)&&c.css("font-size",h);b.callback&&b.callback(this)});b.complete&&b.complete(this);return this}})(jQuery);
