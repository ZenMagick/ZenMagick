<?php
/*
 * ZenMagick - Another PHP framework.
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

/**
 * ZMTemplateSelect
 *
 * @package zenmagick.store.shared.model.layout
 * @Table(name="template_select")
 * @Entity
 */
class ZMTemplateSelect extends ZMObject {
    /**
     * @var integer $id
     *
     * @Column(name="template_id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $themeId
     *
     * @Column(name="template_dir", type="string", length=64, nullable=false)
     */
    private $themeId;

    /**
     * @var string $languageId
     *
     * @Column(name="template_language", type="string", length=64, nullable=false)
     */
    private $languageId;

    /**
     * @var string $variationId
     *
     * @Column(name="variation_dir", type="string", length=64, nullable=true)
     */
    private $variationId;

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    public function getId() { return $this->id; }
    public function getThemeId() { return $this->themeId; }
    public function getLanguageId() { return $this->languageId; }
    public function getVariationId() { return $this->variationId; }

    public function setId($id) { $this->id = $id; }
    public function setThemeId($themeId) { $this->themeId = $themeId; }
    public function setLanguageId($languageId) { $this->languageId = $languageId; }
    public function setVariationId($variationId) { $this->variationId = $variationId; }
}
