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
namespace ZenMagick\AdminBundle\Dashboard;

use ZenMagick\Base\Toolbox;
use ZenMagick\Http\View\TemplateView;
use ZenMagick\Http\Widgets\Widget;

/**
 * A dashboard widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
abstract class DashboardWidget extends Widget
{
    const STATUS_DEFAULT = '';
    const STATUS_INFO = '';
    const STATUS_NOTICE = 'ui-state-highlight';
    const STATUS_WARN = 'ui-state-error';
    private $id;
    private $minimize;
    private $maximize;
    private $options;
    private $open;
    private $status;

    /**
     * Create new user.
     *
     * @param string title The title; default is <code>null</code> to use the id.
     */
    public function __construct($title=null)
    {
        parent::__construct();
        // default
        $this->id = get_class($this);
        $this->setTitle(null != $title ? $title : $this->id);
        $this->minimize = true;
        $this->maximize = false;
        $this->options = null;
        $this->open = true;
        $this->status = self::STATUS_DEFAULT;
    }

    /**
     * Get the id.
     *
     * @return int The id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id.
     *
     * @param int id The id.
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the (HTML) contents.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @return string The contents.
     */
    abstract public function getContents($request);

    /**
     * Get the minimize flag.
     *
     * <p>Tells the dashboard wheter this widget can be minimized or not.</p>
     *
     * @return boolean The minimize flag.
     */
    public function isMinimize()
    {
        return $this->minimize;
    }

    /**
     * Set the minimize flag.
     *
     * @parm boolean minimize The new value.
     */
    public function setMinimize($minimize)
    {
        $this->minimize = $minimize;
    }

    /**
     * Get the maximize flag.
     *
     * <p>Tells the dashboard wheter this widget can be maximized or not.</p>
     *
     * @return boolean The maximize flag.
     */
    public function isMaximize()
    {
        return $this->maximize;
    }

    /**
     * Set the maximize flag.
     *
     * @parm boolean maximize The new value.
     */
    public function setMaximize($maximize)
    {
        $this->maximize = $maximize;
    }

    /**
     * Set url for options dialog.
     *
     * @parm mixed options Options.
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Get options.
     *
     * @return mixed The options or <code>null</code>.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Check if this widget has options.
     *
     * @return boolean <code>true</code> if this widget has configurable options.
     */
    public function hasOptions()
    {
        return null != $this->options;
    }

    /**
     * Get the open flag.
     *
     * <p>Tells the dashboard wheter this widget is currently open.</p>
     *
     * @return boolean The open flag.
     */
    public function isOpen()
    {
        return $this->open;
    }

    /**
     * Set the open flag.
     *
     * @parm boolean open The new value.
     */
    public function setOpen($open)
    {
        $this->open = Toolbox::asBoolean($open);
    }

    /**
     * Get the status.
     *
     * @return string The status.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the status.
     *
     * @param string id The status.
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, TemplateView $templateView)
    {
        // request first, as this might trigger a status change
        $contents = $this->getContents($request);
        $lines = array(
            '<div class="portlet'.($this->hasOptions() ? ' gear' : '').'" id="portlet-'.$this->getId().'">',
            '  <div class="portlet-header'.($this->isOpen() ? ' open' : ' closed"').' '.$this->getStatus().'">'.$this->getTitle().'</div>',
            '  <div class="portlet-content"'.($this->isOpen() ? '' : ' style="display:none;"').'>',
            '    '.$contents,
            '  </div>',
            '</div>'
        );

        return implode("\n", $lines);
    }

}
