<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\Http\Blocks\Widgets;

use ZenMagick\Http\Widgets\Widget;
use ZenMagick\Http\View\TemplateView;

/**
 * A block widget.
 *
 * <p>This base class will render the configured template if set, or return an empty string.</p>
 *
 * <p>In addition to rendering the template, it is possible to pass in a format string. The string is takes as
 * <code>sprintf</code> type format string with the template output as the single parameter (typically <em>%s</em>
 * would be used to position the template content).</p>
 * <p>This makes is rather simple to wrap the template output in something like a &lt;li&gt; tag or similar. For more
 * complex formatting nested blocks should be considered.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class BlockWidget extends Widget
{
    private $sortOrder_;
    private $template_;
    private $format_;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->sortOrder_ = 0;
        $this->template_ = null;
        $this->format_ = null;
    }

    /**
     * Set the sort order.
     *
     * @param int sortOrder The sort order.
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder_ = $sortOrder;
    }

    /**
     * Get the sort order.
     *
     * @return int The sort order.
     */
    public function getSortOrder()
    {
        return $this->sortOrder_;
    }

    /**
     * Set the template name.
     *
     * @param string template The template.
     */
    public function setTemplate($template)
    {
        $this->template_ = $template;
    }

    /**
     * Get the template name.
     *
     * @return string The template.
     */
    public function getTemplate()
    {
        return $this->template_;
    }

    /**
     * Set the optional format string.
     *
     * <p><strong>Note:</strong> The format, if set, will only be used if the generated content is <strong>not empty</strong>.</p>
     *
     * @param string format The format.
     */
    public function setFormat($format)
    {
        $this->format_ = $format;
    }

    /**
     * Get the format string.
     *
     * @return string The format string or <code>null</code> if not set.
     */
    public function getFormat()
    {
        return $this->format_;
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, TemplateView $templateView)
    {
        $content = '';

        if (!empty($this->template_)) {
            // hand on all custom properties
            $content = $templateView->fetch($this->template_, $this->getProperties());
        }

        if (!empty($this->format_) && !empty($content)) {
            $content = sprintf($this->format_, $content);
        }

        return $content;
    }

}
