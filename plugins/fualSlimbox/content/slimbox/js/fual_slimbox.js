/**
 * Fual Slimbox for Zen v1.0
 *
 * @author Brian Tyler (btyler@math.ucl.ac.uk)
 * @license Free to use for Good or Evil.
 * @version $Id: fual_slimbox.js 2007-12-04 btyler $
 */
 
function fualShowImage() {
	var slimWrappers = $$('#slimboxWrapper');
	switch( fualNervous ) {
		case 2:
			slimWrappers.each( function(wrapper){
				var slimSlide = new Fx.Slide( wrapper, {duration:300} );
				slimSlide.hide();
				wrapper.setStyle('visibility', 'visible');
				wrapper.setStyle('display', 'block');
				slimSlide.slideIn();
			});
			break;
		default:
			slimWrappers.each( function(wrapper){
				wrapper.setStyle('visibility', 'visible');
				wrapper.setStyle('display', 'block');
			});
	}
};
	
window.addEvent('domready', fualShowImage);
