<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\AdminBundle\Installation;

use ZenMagick\Base\ZMObject;

/**
 * Single installation patch.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class InstallationPatch extends ZMObject
{
    public $messages;
    protected $id;
    protected $label;

    /**
     * Create new patch.
     *
     * @param string id Id of the patch.
     */
    public function __construct($id)
    {
        parent::__construct();

        $this->container = \ZenMagick\Base\Runtime::getContainer();
        $this->id = $id;
        $this->label = $id. ' Patch';
        $this->messages = array();
    }

    /**
     * Get the patch id.
     *
     * @return string The id of the patch.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the patch label.
     *
     * @return string The label of the patch.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns a list of other patches it depends on.
     *
     * @return array List of patch names.
     */
    public function dependsOn()
    {
        return array();
    }

    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    public function isOpen()
    {
        return false;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    public function isReady()
    {
        return true;
    }

    /**
     * Get the patch group id.
     *
     * @return string The patch group id.
     */
    public function getGroupId()
    {
        return '';
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    public function getPreconditionsMessage()
    {
        return "";
    }

    /**
     * Get optional installation messages.
     *
     * @return array List of <code>Message</code> instances.
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    public function patch($force=false)
    {
        return true;
    }

    /**
     * Check if this patch supports undo.
     *
     * @return boolean <code>true</code> if undo is supported, <code>false</code> if not.
     */
    public function canUndo()
    {
        return true;
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    public function undo()
    {
        return true;
    }

}
