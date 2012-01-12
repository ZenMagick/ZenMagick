<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
?>
<?php
namespace zenmagick\apps\store\admin\dashboard;

use zenmagick\base\Toolbox;
use zenmagick\http\widgets\Widget;

/**
 * A dashboard widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
abstract class DashboardWidget extends Widget {
    const STATUS_DEFAULT = '';
    const STATUS_INFO = '';
    const STATUS_NOTICE = 'ui-state-highlight';
    const STATUS_WARN = 'ui-state-error';
    private $id_;
    private $minimize_;
    private $maximize_;
    private $options_;
    private $open_;
    private $status_;


    /**
     * Create new user.
     *
     * @param string title The title; default is <code>null</code> to use the id.
     */
    public function __construct($title=null) {
        parent::__construct();
        // default
        $this->id_ = get_class($this);
        $this->setTitle(null != $title ? $title : $this->id_);
        $this->minimize_ = true;
        $this->maximize_ = false;
        $this->options_ = null;
        $this->open_ = true;
        $this->status_ = self::STATUS_DEFAULT;
    }


    /**
     * Get the id.
     *
     * @return int The id.
     */
    public function getId() { return $this->id_; }

    /**
     * Set the id.
     *
     * @param int id The id.
     */
    public function setId($id) { $this->id_ = $id; }

    /**
     * Get the (HTML) contents.
     *
     * @param ZMRequest request The current request.
     * @return string The contents.
     */
    public abstract function getContents($request);

    /**
     * Get the minimize flag.
     *
     * <p>Tells the dashboard wheter this widget can be minimized or not.</p>
     *
     * @return boolean The minimize flag.
     */
    public function isMinimize() { return $this->minimize_; }

    /**
     * Set the minimize flag.
     *
     * @parm boolean minimize The new value.
     */
    public function setMinimize($minimize) { $this->minimize_ = $minimize; }

    /**
     * Get the maximize flag.
     *
     * <p>Tells the dashboard wheter this widget can be maximized or not.</p>
     *
     * @return boolean The maximize flag.
     */
    public function isMaximize() { return $this->maximize_; }

    /**
     * Set the maximize flag.
     *
     * @parm boolean maximize The new value.
     */
    public function setMaximize($maximize) { $this->maximize_ = $maximize; }

    /**
     * Set url for options dialog.
     *
     * @parm mixed options Options.
     */
    public function setOptions($options) { $this->options_ = $options; }

    /**
     * Get options.
     *
     * @return mixed The options or <code>null</code>.
     */
    public function getOptions() { return $this->options_; }

    /**
     * Check if this widget has options.
     *
     * @return boolean <code>true</code> if this widget has configurable options.
     */
    public function hasOptions() { return null != $this->options_; }

    /**
     * Get the open flag.
     *
     * <p>Tells the dashboard wheter this widget is currently open.</p>
     *
     * @return boolean The open flag.
     */
    public function isOpen() { return $this->open_; }

    /**
     * Set the open flag.
     *
     * @parm boolean open The new value.
     */
    public function setOpen($open) { $this->open_ = Toolbox::asBoolean($open); }

    /**
     * Get the status.
     *
     * @return string The status.
     */
    public function getStatus() { return $this->status_; }

    /**
     * Set the status.
     *
     * @param string id The status.
     */
    public function setStatus($status) { $this->status_ = $status; }

    /**
     * {@inheritDoc}
     */
    public function render($request, $view) {
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
