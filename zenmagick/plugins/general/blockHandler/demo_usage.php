<?php

    /** Two different ways of using the various interfaces... **/
  
    $useStatic = true;
  
    if ($useStatic) {
        // register straight with the manager
        if (ZMTemplateManager::instance()->isLeftColEnabled()) {
            foreach (ZMTemplateManager::instance()->getLeftColBoxNames() as $boxName) {
                // register as bean definition
                ZMBlockManager::instance()->registerBlock('leftColumn', 'SideboxBlockContents#boxName='.$boxName);
            }
        }

        if (ZMTemplateManager::instance()->isRightColEnabled()) {
            foreach (ZMTemplateManager::instance()->getRightColBoxNames() as $boxName) {
                // register as instance
                ZMBlockManager::instance()->registerBlock('rightColumn', new ZMSideboxBlockContents($boxName));
            }
        }
    } else {
        // perhaps the more interesting alternative:
        // allow providers of block contents to register themselfs to allow to manage the registered blocks...
        // currently allows plugins and bean definitions

        // register the sidebox block contents provider as bean definition; this would be done in lib/store somewhere
        ZMSettings::append('plugins.blockHandler.blockContentsProviders', 'SideboxBlockContentsProvider');

        // plugins implementing ZMBlockContentsProvider would do:
        // ZMSettings::append('plugins.blockHandler.blockContentsProviders', 'plugin:PLUGIN_NAME');

        // now do something with it; this is where the UI would mix & map things and perhaps store in the db?

        // 1) build list of provider objects
        $providers = array();
        foreach (explode(',', ZMSettings::get('plugins.blockHandler.blockContentsProviders')) as $providerId) {
            if (ZMLangUtils::startsWith('plugin:', $providerId)) {
                $pluginId = str_replace('plugin:', '', $providerId);
                $provider = ZMPlugins::instance()->getPluginForId($pluginId);
            } else {
                // bean definition
                $provider = ZMBeanUtils::getBean($providerId);
            }
            if ($provider instanceof ZMBlockContentsProvider) {
                $providers[] = $provider;
            } else {
                ZMLogging::instance()->log('invalid block contents provider: '.$providerId, ZMLogging::WARN);
            }
        }

        // 2) build list of all available block contents
        $blockList = array();
        foreach ($providers as $provider) {
            $blockList = array_merge($blockList, $provider->getBlockContentsList());
        }

        // 3) TODO: map to block ids where the user wants them to be 
        // let's simulate this by dividing all blocks into both sideboxes..
        $index = 1;
        $mappings = array('leftColumn' => array(), 'rightColumn' => array());
        foreach ($blockList as $block) {
            $key = 0 == (++$index%2) ? 'leftColumn' : 'rightColumn';
            $mappings[$key][] = $block;
        }

        // 4) now that we have mapped the available block contents onto block identifiers, tell the
        // manager about it
        ZMBlockManager::instance()->setMappings($mappings);

        ZMEvents::instance()->attach(ZMBlockManager::instance());
    }

?>
