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
namespace zenmagick\apps\store\toolbox;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMException;
use zenmagick\http\toolbox\ToolboxTool;

/**
 * Macro utilities.
 *
 * @author DerManoMann
 */
class ToolboxMacro extends ToolboxTool {

    /**
     * <code>phpinfo</code> wrapper.
     *
     * @param what What to display (see phpinfo manual for more); default is <code>1</code>.
     * @return string The <code>phpinfo</code> output minus a few formatting things that break validation.
     */
    public function phpinfo($what=1) {
        ob_start();
        phpinfo($what);
        $info = ob_get_clean();
        $info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info);
        $info = str_replace('width="600"', '', $info);

        return $info;
    }


    /**
     * Format an address according to the countries address format.
     *
     * <p>The following values are available for display:</p>
     *
     * <ul>
     *  <li><code>$firstname</code> - The first name</li>
     *  <li><code>$lastname</code> - The last name</li>
     *  <li><code>$company</code> - The company name</li>
     *  <li><code>$street</code> - The street address</li>
     *  <li><code>$streets</code> - Depending on availablility either <code>$street</code> or <code>$street$cr$suburb</code></li>
     *  <li><code>$suburb</code> - The subrub</li>
     *  <li><code>$city</code> - The city</li>
     *  <li><code>$state</code> - The state (either from the list of states or manually entered)</li>
     *  <li><code>$country</code> - The country name</li>
     *  <li><code>$postcode</code>/<code>$zip</code> - The post/zip code</li>
     *  <li><code>$hr</code> - A horizontal line</li>
     *  <li><code>$cr</code> - New line character</li>
     *  <li><code>$statecomma</code> - The sequence <code>$state, </code> (note the trailing space)</li>
     * </ul>
     *
     * <p>If address is <code>null</code>, the localized version of <em>N/A</em> will be returned.</p>
     *
     * @param ZMAddress address The address to format.
     * @param boolean html If <code>true</code>, format as HTML, otherwise plain text.
     * @return string A fully formatted address that, depending on the <em>html</code> flag, is either HTML or ASCII formatted.
     */
    public function formatAddress($address, $html=true) {
        if (null == $address) {
            $out = _zm("N/A");
        } else {
            if (!Toolbox::isEmpty($address->getLastName())) {
                $firstname = $address->getFirstName();
                $lastname = $address->getLastName();
            } else {
                $firstname = '';
                $lastname = '';
            }
            $company = $address->getCompanyName();
            $street = $address->getAddressLine1();
            $suburb = $address->getSuburb();
            $city = $address->getCity();
            $state = $address->getState();
            $countryService = $this->container->get('countryService');
            if (0 != $address->getCountryId()) {
                $zmcountry = $address->getCountry();
                $country = $zmcountry->getName();
                if (0 != $address->getZoneId()) {
                    $state = $countryService->getZoneCode($zmcountry->getId(), $address->getZoneId(), $state);
                }
            } else {
                $zmcountry = $countryService->getCountryForId(Runtime::getSettings()->get('storeCountry'));
                $country = '';
                $state = '';
            }
            $postcode = $address->getPostcode();

            $boln = '';
            if ($html) {
                $hr = '<hr>';
                $cr = '<br />';
            } else {
                $hr = '----------------------------------------';
                $cr = "\n";
            }

            // encode
            $vars = array('firstname', 'lastname', 'company', 'street', 'suburb', 'city', 'state', 'country', 'postcode');
            foreach ($vars as $var) {
                $$var = $this->getToolbox()->html->encode($$var);
            }

            // alias or derived
            $zip = $postcode;
            $statecomma = '';
            $streets = $street;
            if ($suburb != '') $streets = $street . $cr . $suburb;
            if ($state != '') $statecomma = $state . ', ';

            $format = $this->container->get('addressService')->getAddressFormatForId($zmcountry->getAddressFormatId());
            // $format is using all the local variables...
            eval("\$out = \"$format\";");

            $company = $address->getCompanyName();
            if (Runtime::getSettings()->get('isAccountCompany') && !empty($company) ) {
                $out = $company . $cr . $out;
            }
        }

        return $out;
    }

    /**
     * Helper to format a given <code>ZMCrumbtrail</code>.
     *
     * @param ZMCrumbtrail crumbtrail A <code>ZMCrumbtrail</code> instance.
     * @param string sep A separator string.
     * @return string A fully HTML formatted crumbtrail.
     */
    public function buildCrumbtrail($crumbtrail, $sep) {
        $toolbox = $this->getToolbox();
        $html = '';
        $first = true;
        $crumbs = $toolbox->crumbtrail->getCrumbs();
        foreach ($crumbs as $crumb) {
            if (!$first) $html .= $sep;
            $first = false;
            if (null != $crumb->getURL()) {
                $html .= '<a href="'.$crumb->getURL().'">'.$toolbox->html->encode(_zm($crumb->getName())).'</a>';
            } else {
                $html .= $toolbox->html->encode(_zm($crumb->getName()));
            }
        }
        return $html;
    }

    /**
     * Build a nested unordered list from the given categories.
     *
     * <p>Supports show category count and use category page.</p>
     *
     * <p>Links in the active path (&lt;a&gt;) will have a class named <code>act</code>,
     * empty categories will have a class <code>empty</code>. Note that both can occur
     * at the same time.</p>
     *
     * <p>Uses output buffering for increased performance.</p>
     *
     * <p>Please note that the last three parameter are used internally and should not bet set.</p>
     *
     * @param array categories An <code>array</code> of <code>ZMCategory</code> instances.
     * @param boolean showProductCount If true, show the product count per category; default is <code>false</code>.
     * @param boolean $useCategoryPage If true, create links for empty categories; default is <code>false</code>.
     * @param boolean activeParent If true, the parent category is considered in the current category path; default is <code>false</code>.
     * @param boolean root Flag to indicate the start of the recursion (not required to set, as defaults to <code>true</code>); default is <code>true</code>.
     * @param array path The active category path; default is <code>null</code>.
     * @return string The given categories as nested unordered list.
     */
    public function categoryTree($categories, $showProductCount=false, $useCategoryPage=false, $activeParent=false, $root=true, $path=null) {
        $toolbox = $this->getToolbox();
        if ($root) {
            ob_start();
            $path = (array)$this->getRequest()->attributes->get('categoryIds');
        }
        $languageId = $this->getRequest()->getSession()->getLanguageId();
        echo '<ul' . ($activeParent ? ' class="act"' : '') . '>';
        foreach ($categories as $category) {
            if (!$category->isActive()) {
                continue;
            }
            $active = in_array($category->getId(), $path);
            $noOfProductsInCat = $showProductCount ? count($this->container->get('productService')->getProductIdsForCategoryId($category->getId(), $languageId, true, false)) : 0;
            $isEmpty = 0 == $noOfProductsInCat;
            echo '<li>';
            $class = '';
            $class = $active ? 'act' : '';
            $class .= $isEmpty ? ' empty' : '';
            $class .= ($active && !$category->hasChildren()) ? ' curr' : '';
            $class = trim($class);
            $onclick = $isEmpty ? ($useCategoryPage ? '' : ' onclick="return catclick(this);"') : '';
            echo '<a' . ('' != $class ? ' class="'.$class.'"' : '') . $onclick . ' href="' .
                        $this->getRequest()->url('category', 'cPath='.implode('_', $category->getPath())) .
                        '">'.$toolbox->html->encode($category->getName()).'</a>';
            if ($showProductCount) {
                if (0 < ($noOfProductsInTree = count($this->container->get('productService')->getProductIdsForCategoryId($category->getId(), $languageId, true, true)))) {
                    echo '('.$noOfProductsInTree.')';
                }
            }
            if ($category->hasChildren()) {
                echo '&gt;';
            }
            if ($category->hasChildren()) {
                $this->categoryTree($category->getChildren(), $showProductCount, $useCategoryPage, $active, false, $path);
            }
            echo '</li>';
        }
        echo '</ul>';

        $html = $root ? ob_get_clean() : '';
        return $html;
    }


    /**
     * Format additional email content for internal copies.
     *
     * @param string name The sender name.
     * @param string email The sender email.
     * @param zenmagick\http\session\Session session The current session.
     * @return array Hash of extra information.
     */
    public function officeOnlyEmailFooter($name, $email, $session) {
        $context = array();

        $session = $this->getRequest()->getSession();
        $ipAddress = $this->getRequest()->getClientIp();
        $hostAddress = '';
        if ($this->container->get('settingsService')->get('isResolveClientIP')) {
            $hostAddress = @gethostbyaddr($ipAddress);
        }
        $officeOnly = array(
            "\n",
            _zm('Office Use Only:'),
            sprintf(_zm('From: %s'), $name),
            sprintf(_zm('Email: %s'), $email),
            sprintf(_zm('Remote: %s - %s'), $hostAddress, $ipAddress),
            sprintf(_zm('Date: %s'), date("D M j Y G:i:s T")),
            "\n\n"
        );
        $context['office_only_html'] = nl2br(implode("\n", $officeOnly));

        return $context;
    }


    /**
     * Generate HTML for product attributes.
     *
     * <p>Usage sample:</p>
     *
     * <code><pre>
     *  &lt;?php $attributes = $macro->productAttributes($product); ?&gt;
     *  &lt;?php foreach ($attributes as $attribute) { ?&gt;
     *  &nbsp;&nbsp;  &lt;?php foreach ($attribute['html'] as $option) { ?&gt;
     *  &nbsp;&nbsp;&nbsp;&nbsp;    &lt;p&gt;&lt;?php echo $option ?&gt;&lt;/p&gt;
     *  &nbsp;&nbsp;  &lt;?php } ?&gt;
     *  &lt;?php } ?&gt;
     * </pre></code>
     *
     * @param ZMProduct product A <code>ZMProduct</code> instance.
     * @return array An array containing HTML formatted attributes.
     */
    public function productAttributes($product) {
        $elements = array();
        $attributes = $product->getAttributes();
        $uploadIndex = 1;
        foreach ($attributes as $attribute) {
            switch ($attribute->getType()) {
                case PRODUCTS_OPTIONS_TYPE_RADIO:
                    $details = $this->productRadioAttribute($product, $attribute);
                    break;
                case PRODUCTS_OPTIONS_TYPE_CHECKBOX:
                    $details = $this->productCheckboxAttribute($product, $attribute);
                    break;
                case PRODUCTS_OPTIONS_TYPE_READONLY:
                    $details = $this->productReadonlyAttribute($attribute);
                    break;
                case PRODUCTS_OPTIONS_TYPE_TEXT:
                    $details = $this->productTextAttribute($product, $attribute);
                    break;
                case PRODUCTS_OPTIONS_TYPE_FILE:
                    $details = $this->productUploadAttribute($product, $attribute, $uploadIndex);
                    ++$uploadIndex;
                    break;
                case PRODUCTS_OPTIONS_TYPE_SELECT:
                    $details = $this->productSelectAttribute($product, $attribute);
                    break;
                default:
                    throw new ZMException('Unsupported attribute type: '.$attribute->getType().'/'.$attribute->getName());
            }
            $details['attribute'] = $attribute;
            $elements[] = $details;
        }

        return $elements;
    }

    /**
     * Generate HTML for a <em>RADIO</em> attribute.
     *
     * @param ZMProduct product The product.
     * @param ZMAttribute attribute The attribute.
     * @return array Attribute info plus HTML to render this attribute.
     */
    protected function productRadioAttribute($product, $attribute) {
        $element = array();
        $element['id'] = $attribute->getId();
        $element['name'] = $attribute->getName();
        $element['type'] = 'radio';
        $elements = array();
        $index = 1;
        $slash = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? '/' : '';
        foreach ($attribute->getValues() as $value) {
            $id = 'id_'.$attribute->getId().'_'.$index++;
            $name = 'id['.$attribute->getId().']';
            $checked = $value->isDefault() ? ' checked="checked"' : '';
            $radio = '<input type="radio" id="'.$id.'" name="'.$name.'" value="'.$value->getId().'"'.$checked.$slash.'>';
            $radio .= '<label for="'.$id.'">'.$this->buildAttributeValueLabel($product, $value).'</label>';
            array_push($elements, $radio);
        }
        $element['html'] = $elements;
        return $element;
    }

    /**
     * Generate HTML for a <em>CHECKBOX</em> attribute.
     *
     * @param ZMProduct product The product.
     * @param ZMAttribute attribute The attribute.
     * @return array Attribute info plus HTML to render this attribute.
     */
    protected function productCheckboxAttribute($product, $attribute) {
        $element = array();
        $element['id'] = $attribute->getId();
        $element['name'] = $attribute->getName();
        $element['type'] = 'checkbox';
        $elements = array();
        $index = 1;
        $slash = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? '/' : '';
        foreach ($attribute->getValues() as $value) {
            $id = 'id_'.$attribute->getId().'_'.$index++;
            $name = 'id['.$attribute->getId().']['.$value->getId().']';
            $checked = $value->isDefault() ? ' checked="checked"' : '';
            $checkbox = '<input type="checkbox" id="'.$id.'" name="'.$name.'" value="'.$value->getId().'"'.$checked.$slash.'>';
            $checkbox .= '<label for="'.$id.'">'.$this->buildAttributeValueLabel($product, $value).'</label>';
            array_push($elements, $checkbox);
        }
        $element['html'] = $elements;
        return $element;
    }

    /**
     * Generate HTML for a <em>TEXT</em> attribute.
     *
     * @param ZMProduct product The product.
     * @param ZMAttribute attribute The attribute.
     * @return array Attribute info plus HTML to render this attribute.
     */
    protected function productTextAttribute($product, $attribute) {
        $element = array();
        $element['id'] = $attribute->getId();
        $element['name'] = $attribute->getName();
        $element['type'] = 'text';
        $elements = array();
        $index = 1;
        $slash = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? '/' : '';
        foreach ($attribute->getValues() as $value) {
            $id = 'id_'.$attribute->getId().'_'.$index++;
            $name = 'id['.Runtime::getSettings()->get('textOptionPrefix').$attribute->getId().']';
            $text = '<label for="'.$id.'">'.$this->buildAttributeValueLabel($product, $value).'</label>';
            $text .= '<input type="text" id="'.$id.'" name="'.$name.'" value=""'.$slash.'>';
            array_push($elements, $text);
        }
        $element['html'] = $elements;
        return $element;
    }

    /**
     * Generate HTML for a <em>FILE</em> attribute.
     *
     * @param ZMProduct product The product.
     * @param ZMAttribute attribute The attribute.
     * @return array Attribute info plus HTML to render this attribute.
     */
    protected function productUploadAttribute($product, $attribute, $uploadIndex) {
        $element = array();
        $element['id'] = $attribute->getId();
        $element['name'] = $attribute->getName();
        $element['type'] = 'upload';
        $elements = array();
        $index = 1;
        $slash = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? '/' : '';
        foreach ($attribute->getValues() as $value) {
            $id = 'id_'.$attribute->getId().'_'.$index;
            $name = 'id['.Runtime::getSettings()->get('textOptionPrefix').$attribute->getId().']';
            $text = '<label for="'.$id.'">'.$this->buildAttributeValueLabel($product, $value).'</label>';
            $text .= '<input type="file" id="'.$id.'" name="'.$name.'" value=""'.$slash.'>';
            $text .= '<input type="hidden" name="'.Runtime::getSettings()->get('uploadOptionPrefix').$uploadIndex.'" value="'.$attribute->getId().'"'.$slash.'>';
            $text .= '<input type="hidden" name="'.Runtime::getSettings()->get('textOptionPrefix').Runtime::getSettings()->get('uploadOptionPrefix').$uploadIndex.'" value=""'.$slash.'>';
            array_push($elements, $text);
        }
        $element['html'] = $elements;
        return $element;
    }

    /**
     * Generate HTML for a <em>READONLY</em> attribute.
     *
     * @param ZMProduct product The product.
     * @param ZMAttribute attribute The attribute.
     * @return array Attribute info plus HTML to render this attribute.
     */
    protected function productReadonlyAttribute($attribute) {
        $element = array();
        $element['id'] = $attribute->getId();
        $element['name'] = $attribute->getName();
        $element['type'] = 'feature';
        $elements = array();
        foreach ($attribute->getValues() as $value) {
            array_push($elements, $value->getName());
        }
        $element['html'] = $elements;
        return $element;
    }

    /**
     * Generate HTML for a <em>SELECT</em> attribute.
     *
     * @param ZMProduct product The product.
     * @param ZMAttribute attribute The attribute.
     * @return array Attribute info plus HTML to render this attribute.
     */
    protected function productSelectAttribute($product, $attribute) {
        $element = array();
        $element['id'] = $attribute->getId();
        $element['name'] = $attribute->getName();
        $element['type'] = 'select';
        $elements = array();
        $html = '<select name="id['.$attribute->getId().']">';
        foreach ($attribute->getValues() as $value) {
            $selected = $value->isDefault() ? ' selected="selected"' : '';
            $html .= '<option value="'.$value->getId().'"'.$selected.'>'.$this->buildAttributeValueLabel($product, $value, false).'</option>';
        }
        $html .= '</select>';
        array_push($elements, $html);
        $element['html'] = $elements;
        return $element;
    }

    /**
     * Format an attribute value label.
     *
     * @param ZMProduct product The product.
     * @param ZMAttributeValue value The attribute value.
     * @param boolean enableImage Optional flag to enable/disable images; default is <code>true</code>.
     * @return string A fully HTML formatted attribute value label.
     */
    protected function buildAttributeValueLabel($product, $value, $enableImage=true) {
        $toolbox = $this->getToolbox();
        $slash = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? '/' : '';
        $label = '';
        if ($value->hasImage() && $enableImage) {
            // TODO: where are images coming from in the future??
            $path = realpath($this->getRequest()->getDocRoot().$this->getRequest()->getContext().DIRECTORY_SEPARATOR.'images/'.$value->getImage());
            if (file_exists($path)) {
                $label = '<img src="' . $toolbox->net->image($value->getImage()) . '" alt="'.$value->getName().'" title="'.$value->getName().'"'.$slash.'>';
            }
        }
        $label .= _zm($value->getName());

        if ($value->isFree() && $product->isFree()) {
            $label .= sprintf(_zm(' [FREE! (was: %s%s)]'), $value->getPricePrefix(), $toolbox->utils->formatMoney($value->getPrice()));
        } else if (0 != $value->getPrice()) {
            $label .= sprintf(_zm(' (%s%s)'), $value->getPricePrefix(), $toolbox->utils->formatMoney(abs($value->getPrice())));
        }
        //TODO: onetime and weight

        return $label;
    }

    /**
     * Format the product price incl. offer, special and other stuff.
     *
     * @param ZMProduct product The product.
     * @param boolean tax Optional flag to display prices with/without tax (see <code>ZMOffers</code> for details; default is <code>true</code>.
     * @return string The fully HTML formatted price.
     */
    public function productPrice($product, $tax=true) {
        $toolbox = $this->getToolbox();
        $offers = $product->getOffers();

        $html = '<span class="price">';
        if ($offers->isAttributePrice()) {
            $html .= _zm("Starting at: ");
        }
        if (!$product->isFree() && ($offers->isSpecial() || $offers->isSale())) {
            // special/sale
            $html .= '<span class="strike base">' . $toolbox->utils->formatMoney($offers->getBasePrice($tax)) . '</span> ';
            if ($offers->isSpecial())  {
                if ($offers->isSale()) {
                   $html .= '<span class="strike special">' . $toolbox->utils->formatMoney($offers->getSpecialPrice($tax)) . '</span>';
                } else {
                   $html .= $toolbox->utils->formatMoney($offers->getSpecialPrice($tax));
                }
            }
            // sale has precendence
            if ($offers->isSale()) {
               $html .= $toolbox->utils->formatMoney($offers->getSalePrice($tax));
            }
        } else {
            $html .= $toolbox->utils->formatMoney($offers->getCalculatedPrice($tax));
        }
        $html .= '</span>';

        return $html;
    }

    /**
     * Build quantity discounts details.
     *
     * @param ZMProduct product The product.
     * @param boolean tax Optional flag to display prices with/without tax (see <code>ZMOffers</code> for details; default is <code>true</code>.
     * @return array Discount details.
     * @todo The applied price logic should not be in here!
     */
    public function buildQuantityDiscounts($product, $tax=true) {
        $offers = $product->getOffers();
        $discounts = $offers->getQuantityDiscounts($tax);

        // build info map
        $details = array();
        for ($ii=0, $n=count($discounts); $ii<$n; ++$ii) {
            if (0 == $ii) {
                // regular
                $low = $product->getQtyOrderMin();
                $high = $discounts[0]->getQuantity() - 1;
                if ($low == $high) {
                    $high = $low;
                }
                if ($low != $high) {
                    $qty = sprintf(_zm("%s-%s"), $low, $high);
                } else {
                    $qty = $low;
                }
                $price = $offers->isSpecial($tax) ? $offers->getSpecialPrice($tax) : $offers->getCalculatedPrice($tax);
                $details[] = array('qty' => $qty, 'price' => $price);
            }

            if ($ii == ($n - 1)) {
                $qty = sprintf(_zm("%s+"), $discounts[$ii]->getQuantity());
            } else {
                if ($discounts[$ii]->getQuantity() == ($discounts[$ii+1]->getQuantity() - 1)) {
                    $qty = $discounts[$ii]->getQuantity();
                } else {
                    $qty = sprintf(_zm("%s-%s"), $discounts[$ii]->getQuantity(), ($discounts[$ii+1]->getQuantity() - 1));
                }
            }
            $details[] = array('qty' => $qty, 'price' => $discounts[$ii]->getPrice());
        }

        return $details;
    }

}
