<?php

    /** Two different ways of using the various interfaces... **/
  
    $useStatic = false;
  
    if ($useStatic) {
        // register straight with the manager
        if (ZMTemplateManager::instance()->isLeftColEnabled()) {
            $index = 0;
            foreach (ZMTemplateManager::instance()->getLeftColBoxNames() as $boxName) {
                // register as bean definition
                ZMBlockManager::instance()->registerBlock('leftColumn', 'SideboxBlockContents#boxName='.$boxName.'&sortOrder='.$index++);
            }
        }

        if (ZMTemplateManager::instance()->isRightColEnabled()) {
            $index = 0;
            foreach (ZMTemplateManager::instance()->getRightColBoxNames() as $boxName) {
                // register as instance
                ZMBlockManager::instance()->registerBlock('rightColumn', new ZMSideboxBlockContents($boxName, $index++));
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


        // 1) build custom mappings (this could be done via UI and be only a subset of the available blocks/ids)
        foreach (ZMBlockManager::instance()->getProviders() as $provider) {
            $mappings = array('leftColumn' => array(), 'rightColumn' => array());
            foreach ($mappings as $blockId => $blocks) {
                $mappings[$blockId] = array_merge($mappings[$blockId], $provider->getBlockContentsList($blockId));
            }
        }

        // 2) now that we have mapped the available block contents onto block identifiers, tell the
        // manager about it and force sorting (as we do not have a UI)
        ZMBlockManager::instance()->setMappings($mappings, true);

        ZMEvents::instance()->attach(ZMBlockManager::instance());
    }

?>
