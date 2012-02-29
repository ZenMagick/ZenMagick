<?php
/**
 * Fual Slimbox for Zen v0.1.5
 *
 * @author Brian Tyler (btyler@math.ucl.ac.uk)
 * @copyright Copyright 2008 Fual Ltd
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */


/*
 * @class FualSlimboxOptions
 * @brief Creates an interface between the ZC admin panel and Slimbox allowing JS options to be set dynamically
 */
class FualSlimboxOptions {
	/* @function fual_get_transition
	 * @brief Set the transition type
	 * @note Defaults to sine if unset
	 */
	function fual_get_transition() {
		if( defined( 'FUAL_SLIMBOX_TRANSITION_TYPE' ) ) {
			echo 'Fx.Transitions.' . FUAL_SLIMBOX_TRANSITION_TYPE;
		} else {
			echo 'Fx.Transitions.Sine';
		}
	}

	/* @function fual_get_amplitude
	 * @brief Set the amplitude of the transition
	 * @note Defaults to 5 if unset
	 */
	function fual_get_amplitude() {
		if( defined( 'FUAL_SLIMBOX_AMPLITUDE' ) ) {
			echo FUAL_SLIMBOX_AMPLITUDE;
		} else {
			echo 5;
		}
	}

	/* @function fual_get_fps
	 * @brief Set the frames per secon of the transition
	 * @note Defaults to 60 if unset
	 */
	function fual_get_fps() {
		if( defined( 'FUAL_SLIMBOX_FPS' ) ) {
			echo (int)FUAL_SLIMBOX_FPS;
		} else {
			echo 60;
		}
	}

	/* @function fual_get_duration
	 * @brief Set the duration the transition
	 * @note Defaults to 800 if unset. Times aer in miliseconds
	 */
	function fual_get_duration() {
		if( defined( 'FUAL_SLIMBOX_DURATION' ) ) {
			echo (int)FUAL_SLIMBOX_DURATION;
		} else {
			echo 800;
		}
	}

	/* @function fual_get_ease
	 * @brief Set the ease the transition
	 * @note Defaults to In and Out if unset.
	 */
	function fual_get_ease() {
		if( defined( 'FUAL_SLIMBOX_EASE' ) ) {
			echo FUAL_SLIMBOX_EASE;
		} else {
			echo 'easeInOut';
		}
	}

	/* @function fual_get_width
	 * @brief Set the starting width of the lightbox
	 * @note Defaults 400 if unset.
	 */
	function fual_get_width() {
		if( defined( 'FUAL_SLIMBOX_WIDTH' ) ) {
			echo (int)FUAL_SLIMBOX_WIDTH;
		} else {
			echo 400;
		}
	}

	/* @function fual_get_height
	 * @brief Set the starting height of the lightbox
	 * @note Defaults 300 if unset.
	 */
	function fual_get_height() {
		if( defined( 'FUAL_SLIMBOX_HEIGHT' ) ) {
			echo (int)FUAL_SLIMBOX_HEIGHT;
		} else {
			echo 300;
		}
	}

	/* @function fual_get_iwidth
	 * @brief Set the width of the lightbox as an iframe
	 * @note Defaults 500 if unset.
	 */
	function fual_get_iwidth() {
		if( defined( 'FUAL_SLIMBOX_IWIDTH' ) ) {
			echo (int)FUAL_SLIMBOX_IWIDTH;
		} else {
			echo 500;
		}
	}

	/* @function fual_get_iheight
	 * @brief Set the height of the lightbox as an iframe
	 * @note Defaults 300 if unset.
	 */
	function fual_get_iheight() {
		if( defined( 'FUAL_SLIMBOX_IHEIGHT' ) ) {
			echo (int)FUAL_SLIMBOX_IHEIGHT;
		} else {
			echo 300;
		}
	}

	/* @function fual_get_caption
	 * @brief Set the style of caption display
	 * @note Defaults true if unset.
	 */
	function fual_get_caption() {
		if( defined( 'FUAL_SLIMBOX_CAPTION' ) ) {
			echo FUAL_SLIMBOX_CAPTION;
		} else {
			echo 'true';
		}
	}

	/* @function fual_get_elhide
	 * @brief Set the class to hide when the lightbox is displayed
	 * @note Defaults zenLightboxHideMe if unset.
	 */
	function fual_get_elhide() {
		if( defined( 'FUAL_SLIMBOX_HIDE_ME' ) ) {

			echo '".' . FUAL_SLIMBOX_HIDE_ME . '"';
		} else {
			echo '.zenLightboxHideMe';
		}
	}

	/* @function fual_get_displayvar
	 * @brief Chooses whether to display the safe variation
	 * @note Defaults to the safe variation if unset.
	 */
	function fual_get_displayvar() {
		if( FUAL_SLIMBOX_DISPLAY_VAR == 'true' || !defined( 'FUAL_SLIMBOX_DISPLAY_VAR' ) ){
			echo '"Var"';
		} else {
			echo '""';
		}
	}

	/* @function fual_get_pageof
	 * @brief Sets the "Page of" Text
	 */
	function fual_get_pageof() {
		if( defined( 'FUAL_SLIMBOX_PAGEOF' ) ){
			if ( preg_match( '/^.*#1.*#2*.$/', FUAL_SLIMBOX_PAGEOF ) ){
				echo '"' . htmlentities(FUAL_SLIMBOX_PAGEOF,ENT_QUOTES) . '"';
				return;
			}
		}
		echo '"Page #1 of #2"';
	}

	/* @function jscript
	 * @brief Writes the javascript which sets the lightbox options to the page
	 */
	function jscript() {
        $theme = \zenmagick\base\Runtime::getContainer()->get('themeService')->getActiveThemeId();
        $themeDir = 'includes/templates/' . $theme;
		// There is no point in doing anything if Slimbox is turned off and if zenbox is turned on this will just cause conflicts
		if( FUAL_SLIMBOX == 'true' && ZEN_LIGHTBOX_STATUS != 'true' ) {
			echo '<script type="text/javascript" src="' . $themeDir . '/jscript/slimbox/mootools-release-1.11.slim.js"></script>' . "\n";
		?>
		<script type="text/javascript"><!--
			var FualSlimboxOptions = new Class({
				initialize: function(){
					this.transitionType = new Fx.Transition(<?php $this->fual_get_transition() ?>, <?php $this->fual_get_amplitude(); ?>);
					this.resizeFps = <?php $this->fual_get_fps(); ?>;
					this.resizeDuration = <?php $this->fual_get_duration(); ?>;
					this.resizeTransition = this.transitionType.<?php $this->fual_get_ease(); ?>;
					this.initialWidth = <?php $this->fual_get_width(); ?>;
					this.initialHeight = <?php $this->fual_get_height(); ?>;
					this.animateCaption = <?php $this->fual_get_caption(); ?>;
					this.defaultIframeWidth = <?php $this->fual_get_iwidth(); ?>;
					this.defaultIframeHeight = <?php $this->fual_get_iheight(); ?>;
					this.elHide = <?php $this->fual_get_elhide(); ?>;
					this.displayVar = <?php $this->fual_get_displayvar(); ?>;
					this.pageOf = <?php $this->fual_get_pageof(); ?>;
				}
			});
		//--></script>
		<?php
			echo '<script type="text/javascript" src="' . $themeDir . '/jscript/slimbox/slimbox_ex.compressed.js"></script>'  . "\n"; // */

			if( FUAL_SLIMBOX_NERVOUS != 0 ) {
			?>
		<script type="text/javascript"><!--
			var fualNervous = <?php echo FUAL_SLIMBOX_NERVOUS; ?>
		//--></script>
			<?php
				echo '<script type="text/javascript" src="' . $themeDir . '/jscript/slimbox/fual_slimbox.compressed.js"></script>'  . "\n";
			}
		}
	}
}

?>
