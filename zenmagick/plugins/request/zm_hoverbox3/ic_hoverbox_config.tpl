<script type="text/javascript">
var Hoverbox = {
  Version: '3.0_rc2',
  options: {
    backgroundColor: '<?php if(defined('HOVERBOX_BACKGROUND_COLOR')){echo HOVERBOX_BACKGROUND_COLOR;}else{echo '#ffffff';}?>',
    border: <?php if(defined('HOVERBOX_BORDER_SIZE')){echo HOVERBOX_BORDER_SIZE;}else{echo '12';}?>,
    buttons: { opacity: { normal: <?php if(defined('HOVERBOX_CLOSE_NORMAL')){echo HOVERBOX_CLOSE_NORMAL;}else{echo '0.65';}?>, hover: <?php if(defined('HOVERBOX_CLOSE_HOVER')){echo HOVERBOX_CLOSE_HOVER;}else{echo '1';}?> } },
    cyclic: <?php if(defined('HOVERBOX_END_BEG')){echo HOVERBOX_END_BEG;}else{echo 'false';}?>,
    images: '<?php Runtime::getTheme()->themeURL('hover3/images');?>/',
    imgNumberTemplate: '<?php if(defined('HOVERBOX_IMG_NUMBER')){echo HOVERBOX_IMG_NUMBER;}else{echo 'Image #{position} of #{total}';}?>',
    overlay: {                                            
      background: '<?php if(defined('HOVERBOX_OVERLAY_BACKGROUND')){echo HOVERBOX_OVERLAY_BACKGROUND;}else{echo '#000000';}?>',                                  
      opacity: <?php if(defined('HOVERBOX_OVERLAY_OPACITY')){echo HOVERBOX_OVERLAY_OPACITY;}else{echo '0.85';}?>,
      display: <?php if(defined('HOVERBOX_OVERLAY_ENABLE')){echo HOVERBOX_OVERLAY_ENABLE;}else{echo 'true';}?>
    },
    preloadHover: true,                                    
    radius: <?php if(defined('HOVERBOX_CORNER_RADIUS')){echo HOVERBOX_CORNER_RADIUS;}else{echo '5';}?>,
    resizeDuration: <?php if(defined('HOVERBOX_RESIZE_DURATION')){echo HOVERBOX_RESIZE_DURATION;}else{echo '1';}?>,
    slideshow: { delay: <?php if(defined('HOVERBOX_SLIDE_DELAY')){echo HOVERBOX_SLIDE_DELAY;}else{echo '5';}?>, display: <?php if(defined('HOVERBOX_SHOW_SLIDE')){echo HOVERBOX_SHOW_SLIDE;}else{echo 'true';}?> },
    titleSplit: '::',
    transition: function(pos) {                            
      return ((pos/=0.5) < 1 ? 0.5 * Math.pow(pos, 4) :
        -0.5 * ((pos-=2) * Math.pow(pos,3) - 2));
    },
    viewport: <?php if(defined('HOVERBOX_SMART_RESIZE')){echo HOVERBOX_SMART_RESIZE;}else{echo 'true';}?>,
    zIndex: <?php if(defined('HOVERBOX_ZINDEX')){echo HOVERBOX_ZINDEX;}else{echo '1';}?>,
    closeDimensions: {                                     
      large: { width: 85, height: 22 },                    
      small: { width: 32, height: 22 },
      innertop: { width: 22, height: 22 },
      topclose: { width: 22, height: 18 }                  
    },
    defaultOptions : {                                     
    },
    sideDimensions: { width: 16, height: 22 }              
  },
  typeExtensions: {
    image: 'bmp gif jpeg jpg png'
  }
};
</script>
