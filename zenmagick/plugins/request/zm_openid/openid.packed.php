<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/AX.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//AX.php */ ?>
<?php

/**
 * Implements the OpenID attribute exchange specification, version 1.0
 * as of svn revision 370 from openid.net svn.
 *
 * @package OpenID
 */

/**
 * Require utility classes and functions for the consumer.
 */
//require_once "Auth/OpenID/Extension.php";
//require_once "Auth/OpenID/Message.php";
//require_once "Auth/OpenID/TrustRoot.php";

define('Auth_OpenID_AX_NS_URI',
       'http://openid.net/srv/ax/1.0');

// Use this as the 'count' value for an attribute in a FetchRequest to
// ask for as many values as the OP can provide.
define('Auth_OpenID_AX_UNLIMITED_VALUES', 'unlimited');

// Minimum supported alias length in characters.  Here for
// completeness.
define('Auth_OpenID_AX_MINIMUM_SUPPORTED_ALIAS_LENGTH', 32);

/**
 * AX utility class.
 *
 * @package OpenID
 */
class Auth_OpenID_AX {
    /**
     * @param mixed $thing Any object which may be an
     * Auth_OpenID_AX_Error object.
     *
     * @return bool true if $thing is an Auth_OpenID_AX_Error; false
     * if not.
     */
    function isError($thing)
    {
        return is_a($thing, 'Auth_OpenID_AX_Error');
    }
}

/**
 * Check an alias for invalid characters; raise AXError if any are
 * found.  Return None if the alias is valid.
 */
function Auth_OpenID_AX_checkAlias($alias)
{
  if (strpos($alias, ',') !== false) {
      return new Auth_OpenID_AX_Error(sprintf(
                   "Alias %s must not contain comma", $alias));
  }
  if (strpos($alias, '.') !== false) {
      return new Auth_OpenID_AX_Error(sprintf(
                   "Alias %s must not contain period", $alias));
  }

  return true;
}

/**
 * Results from data that does not meet the attribute exchange 1.0
 * specification
 *
 * @package OpenID
 */
class Auth_OpenID_AX_Error {
    function Auth_OpenID_AX_Error($message=null)
    {
        $this->message = $message;
    }
}

/**
 * Abstract class containing common code for attribute exchange
 * messages.
 *
 * @package OpenID
 */
class Auth_OpenID_AX_Message extends Auth_OpenID_Extension {
    /**
     * ns_alias: The preferred namespace alias for attribute exchange
     * messages
     */
    var $ns_alias = 'ax';

    /**
     * mode: The type of this attribute exchange message. This must be
     * overridden in subclasses.
     */
    var $mode = null;

    var $ns_uri = Auth_OpenID_AX_NS_URI;

    /**
     * Return Auth_OpenID_AX_Error if the mode in the attribute
     * exchange arguments does not match what is expected for this
     * class; true otherwise.
     *
     * @access private
     */
    function _checkMode($ax_args)
    {
        $mode = Auth_OpenID::arrayGet($ax_args, 'mode');
        if ($mode != $this->mode) {
            return new Auth_OpenID_AX_Error(
                            sprintf(
                                    "Expected mode '%s'; got '%s'",
                                    $this->mode, $mode));
        }

        return true;
    }

    /**
     * Return a set of attribute exchange arguments containing the
     * basic information that must be in every attribute exchange
     * message.
     *
     * @access private
     */
    function _newArgs()
    {
        return array('mode' => $this->mode);
    }
}

/**
 * Represents a single attribute in an attribute exchange
 * request. This should be added to an AXRequest object in order to
 * request the attribute.
 *
 * @package OpenID
 */
class Auth_OpenID_AX_AttrInfo {
    /**
     * Construct an attribute information object.  Do not call this
     * directly; call make(...) instead.
     *
     * @param string $type_uri The type URI for this attribute.
     *
     * @param int $count The number of values of this type to request.
     *
     * @param bool $required Whether the attribute will be marked as
     * required in the request.
     *
     * @param string $alias The name that should be given to this
     * attribute in the request.
     */
    function Auth_OpenID_AX_AttrInfo($type_uri, $count, $required,
                                     $alias)
    {
        /**
         * required: Whether the attribute will be marked as required
         * when presented to the subject of the attribute exchange
         * request.
         */
        $this->required = $required;

        /**
         * count: How many values of this type to request from the
         * subject. Defaults to one.
         */
        $this->count = $count;

        /**
         * type_uri: The identifier that determines what the attribute
         * represents and how it is serialized. For example, one type
         * URI representing dates could represent a Unix timestamp in
         * base 10 and another could represent a human-readable
         * string.
         */
        $this->type_uri = $type_uri;

        /**
         * alias: The name that should be given to this attribute in
         * the request. If it is not supplied, a generic name will be
         * assigned. For example, if you want to call a Unix timestamp
         * value 'tstamp', set its alias to that value. If two
         * attributes in the same message request to use the same
         * alias, the request will fail to be generated.
         */
        $this->alias = $alias;
    }

    /**
     * Construct an attribute information object.  For parameter
     * details, see the constructor.
     */
    function make($type_uri, $count=1, $required=false,
                  $alias=null)
    {
        if ($alias !== null) {
            $result = Auth_OpenID_AX_checkAlias($alias);

            if (Auth_OpenID_AX::isError($result)) {
                return $result;
            }
        }

        return new Auth_OpenID_AX_AttrInfo($type_uri, $count, $required,
                                           $alias);
    }

    /**
     * When processing a request for this attribute, the OP should
     * call this method to determine whether all available attribute
     * values were requested.  If self.count == UNLIMITED_VALUES, this
     * returns True.  Otherwise this returns False, in which case
     * self.count is an integer.
    */
    function wantsUnlimitedValues()
    {
        return $this->count === Auth_OpenID_AX_UNLIMITED_VALUES;
    }
}

/**
 * Given a namespace mapping and a string containing a comma-separated
 * list of namespace aliases, return a list of type URIs that
 * correspond to those aliases.
 *
 * @param $namespace_map The mapping from namespace URI to alias
 * @param $alias_list_s The string containing the comma-separated
 * list of aliases. May also be None for convenience.
 *
 * @return $seq The list of namespace URIs that corresponds to the
 * supplied list of aliases. If the string was zero-length or None, an
 * empty list will be returned.
 *
 * return null If an alias is present in the list of aliases but
 * is not present in the namespace map.
 */
function Auth_OpenID_AX_toTypeURIs(&$namespace_map, $alias_list_s)
{
    $uris = array();

    if ($alias_list_s) {
        foreach (explode(',', $alias_list_s) as $alias) {
            $type_uri = $namespace_map->getNamespaceURI($alias);
            if ($type_uri === null) {
                // raise KeyError(
                // 'No type is defined for attribute name %r' % (alias,))
                return new Auth_OpenID_AX_Error(
                  sprintf('No type is defined for attribute name %s',
                          $alias)
                  );
            } else {
                $uris[] = $type_uri;
            }
        }
    }

    return $uris;
}

/**
 * An attribute exchange 'fetch_request' message. This message is sent
 * by a relying party when it wishes to obtain attributes about the
 * subject of an OpenID authentication request.
 *
 * @package OpenID
 */
class Auth_OpenID_AX_FetchRequest extends Auth_OpenID_AX_Message {

    var $mode = 'fetch_request';

    function Auth_OpenID_AX_FetchRequest($update_url=null)
    {
        /**
         * requested_attributes: The attributes that have been
         * requested thus far, indexed by the type URI.
         */
        $this->requested_attributes = array();

        /**
         * update_url: A URL that will accept responses for this
         * attribute exchange request, even in the absence of the user
         * who made this request.
        */
        $this->update_url = $update_url;
    }

    /**
     * Add an attribute to this attribute exchange request.
     *
     * @param attribute: The attribute that is being requested
     * @return true on success, false when the requested attribute is
     * already present in this fetch request.
     */
    function add($attribute)
    {
        if ($this->contains($attribute->type_uri)) {
            return new Auth_OpenID_AX_Error(
              sprintf("The attribute %s has already been requested",
                      $attribute->type_uri));
        }

        $this->requested_attributes[$attribute->type_uri] = $attribute;

        return true;
    }

    /**
     * Get the serialized form of this attribute fetch request.
     *
     * @returns Auth_OpenID_AX_FetchRequest The fetch request message parameters
     */
    function getExtensionArgs()
    {
        $aliases = new Auth_OpenID_NamespaceMap();

        $required = array();
        $if_available = array();

        $ax_args = $this->_newArgs();

        foreach ($this->requested_attributes as $type_uri => $attribute) {
            if ($attribute->alias === null) {
                $alias = $aliases->add($type_uri);
            } else {
                $alias = $aliases->addAlias($type_uri, $attribute->alias);

                if ($alias === null) {
                    return new Auth_OpenID_AX_Error(
                      sprintf("Could not add alias %s for URI %s",
                              $attribute->alias, $type_uri
                      ));
                }
            }

            if ($attribute->required) {
                $required[] = $alias;
            } else {
                $if_available[] = $alias;
            }

            if ($attribute->count != 1) {
                $ax_args['count.' . $alias] = strval($attribute->count);
            }

            $ax_args['type.' . $alias] = $type_uri;
        }

        if ($required) {
            $ax_args['required'] = implode(',', $required);
        }

        if ($if_available) {
            $ax_args['if_available'] = implode(',', $if_available);
        }

        return $ax_args;
    }

    /**
     * Get the type URIs for all attributes that have been marked as
     * required.
     *
     * @return A list of the type URIs for attributes that have been
     * marked as required.
     */
    function getRequiredAttrs()
    {
        $required = array();
        foreach ($this->requested_attributes as $type_uri => $attribute) {
            if ($attribute->required) {
                $required[] = $type_uri;
            }
        }

        return $required;
    }

    /**
     * Extract a FetchRequest from an OpenID message
     *
     * @param request: The OpenID request containing the attribute
     * fetch request
     *
     * @returns mixed An Auth_OpenID_AX_Error or the
     * Auth_OpenID_AX_FetchRequest extracted from the request message if
     * successful
     */
    function &fromOpenIDRequest($request)
    {
        $m = $request->message;
        $obj = new Auth_OpenID_AX_FetchRequest();
        $ax_args = $m->getArgs($obj->ns_uri);

        $result = $obj->parseExtensionArgs($ax_args);

        if (Auth_OpenID_AX::isError($result)) {
            return $result;
        }

        if ($obj->update_url) {
            // Update URL must match the openid.realm of the
            // underlying OpenID 2 message.
            $realm = $m->getArg(Auth_OpenID_OPENID_NS, 'realm',
                        $m->getArg(
                                  Auth_OpenID_OPENID_NS,
                                  'return_to'));

            if (!$realm) {
                $obj = new Auth_OpenID_AX_Error(
                  sprintf("Cannot validate update_url %s " .
                          "against absent realm", $obj->update_url));
            } else if (!Auth_OpenID_TrustRoot::match($realm,
                                                     $obj->update_url)) {
                $obj = new Auth_OpenID_AX_Error(
                  sprintf("Update URL %s failed validation against realm %s",
                          $obj->update_url, $realm));
            }
        }

        return $obj;
    }

    /**
     * Given attribute exchange arguments, populate this FetchRequest.
     *
     * @return $result Auth_OpenID_AX_Error if the data to be parsed
     * does not follow the attribute exchange specification. At least
     * when 'if_available' or 'required' is not specified for a
     * particular attribute type.  Returns true otherwise.
    */
    function parseExtensionArgs($ax_args)
    {
        $result = $this->_checkMode($ax_args);
        if (Auth_OpenID_AX::isError($result)) {
            return $result;
        }

        $aliases = new Auth_OpenID_NamespaceMap();

        foreach ($ax_args as $key => $value) {
            if (strpos($key, 'type.') === 0) {
                $alias = substr($key, 5);
                $type_uri = $value;

                $alias = $aliases->addAlias($type_uri, $alias);

                if ($alias === null) {
                    return new Auth_OpenID_AX_Error(
                      sprintf("Could not add alias %s for URI %s",
                              $alias, $type_uri)
                      );
                }

                $count_s = Auth_OpenID::arrayGet($ax_args, 'count.' . $alias);
                if ($count_s) {
                    $count = Auth_OpenID::intval($count_s);
                    if (($count === false) &&
                        ($count_s === Auth_OpenID_AX_UNLIMITED_VALUES)) {
                        $count = $count_s;
                    }
                } else {
                    $count = 1;
                }

                if ($count === false) {
                    return new Auth_OpenID_AX_Error(
                      sprintf("Integer value expected for %s, got %s",
                              'count.' . $alias, $count_s));
                }

                $attrinfo = Auth_OpenID_AX_AttrInfo::make($type_uri, $count,
                                                          false, $alias);

                if (Auth_OpenID_AX::isError($attrinfo)) {
                    return $attrinfo;
                }

                $this->add($attrinfo);
            }
        }

        $required = Auth_OpenID_AX_toTypeURIs($aliases,
                         Auth_OpenID::arrayGet($ax_args, 'required'));

        foreach ($required as $type_uri) {
            $attrib =& $this->requested_attributes[$type_uri];
            $attrib->required = true;
        }

        $if_available = Auth_OpenID_AX_toTypeURIs($aliases,
                             Auth_OpenID::arrayGet($ax_args, 'if_available'));

        $all_type_uris = array_merge($required, $if_available);

        foreach ($aliases->iterNamespaceURIs() as $type_uri) {
            if (!in_array($type_uri, $all_type_uris)) {
                return new Auth_OpenID_AX_Error(
                  sprintf('Type URI %s was in the request but not ' .
                          'present in "required" or "if_available"',
                          $type_uri));

            }
        }

        $this->update_url = Auth_OpenID::arrayGet($ax_args, 'update_url');

        return true;
    }

    /**
     * Iterate over the AttrInfo objects that are contained in this
     * fetch_request.
     */
    function iterAttrs()
    {
        return array_values($this->requested_attributes);
    }

    function iterTypes()
    {
        return array_keys($this->requested_attributes);
    }

    /**
     * Is the given type URI present in this fetch_request?
     */
    function contains($type_uri)
    {
        return in_array($type_uri, $this->iterTypes());
    }
}

/**
 * An abstract class that implements a message that has attribute keys
 * and values. It contains the common code between fetch_response and
 * store_request.
 *
 * @package OpenID
 */
class Auth_OpenID_AX_KeyValueMessage extends Auth_OpenID_AX_Message {

    function Auth_OpenID_AX_KeyValueMessage()
    {
        $this->data = array();
    }

    /**
     * Add a single value for the given attribute type to the
     * message. If there are already values specified for this type,
     * this value will be sent in addition to the values already
     * specified.
     *
     * @param type_uri: The URI for the attribute
     * @param value: The value to add to the response to the relying
     * party for this attribute
     * @return null
     */
    function addValue($type_uri, $value)
    {
        if (!array_key_exists($type_uri, $this->data)) {
            $this->data[$type_uri] = array();
        }

        $values =& $this->data[$type_uri];
        $values[] = $value;
    }

    /**
     * Set the values for the given attribute type. This replaces any
     * values that have already been set for this attribute.
     *
     * @param type_uri: The URI for the attribute
     * @param values: A list of values to send for this attribute.
     */
    function setValues($type_uri, &$values)
    {
        $this->data[$type_uri] =& $values;
    }

    /**
     * Get the extension arguments for the key/value pairs contained
     * in this message.
     *
     * @param aliases: An alias mapping. Set to None if you don't care
     * about the aliases for this request.
     *
     * @access private
     */
    function _getExtensionKVArgs(&$aliases)
    {
        if ($aliases === null) {
            $aliases = new Auth_OpenID_NamespaceMap();
        }

        $ax_args = array();

        foreach ($this->data as $type_uri => $values) {
            $alias = $aliases->add($type_uri);

            $ax_args['type.' . $alias] = $type_uri;
            $ax_args['count.' . $alias] = strval(count($values));

            foreach ($values as $i => $value) {
              $key = sprintf('value.%s.%d', $alias, $i + 1);
              $ax_args[$key] = $value;
            }
        }

        return $ax_args;
    }

    /**
     * Parse attribute exchange key/value arguments into this object.
     *
     * @param ax_args: The attribute exchange fetch_response
     * arguments, with namespacing removed.
     *
     * @return Auth_OpenID_AX_Error or true
     */
    function parseExtensionArgs($ax_args)
    {
        $result = $this->_checkMode($ax_args);
        if (Auth_OpenID_AX::isError($result)) {
            return $result;
        }

        $aliases = new Auth_OpenID_NamespaceMap();

        foreach ($ax_args as $key => $value) {
            if (strpos($key, 'type.') === 0) {
                $type_uri = $value;
                $alias = substr($key, 5);

                $result = Auth_OpenID_AX_checkAlias($alias);

                if (Auth_OpenID_AX::isError($result)) {
                    return $result;
                }

                $alias = $aliases->addAlias($type_uri, $alias);

                if ($alias === null) {
                    return new Auth_OpenID_AX_Error(
                      sprintf("Could not add alias %s for URI %s",
                              $alias, $type_uri)
                      );
                }
            }
        }

        foreach ($aliases->iteritems() as $pair) {
            list($type_uri, $alias) = $pair;

            if (array_key_exists('count.' . $alias, $ax_args)) {

                $count_key = 'count.' . $alias;
                $count_s = $ax_args[$count_key];

                $count = Auth_OpenID::intval($count_s);

                if ($count === false) {
                    return new Auth_OpenID_AX_Error(
                      sprintf("Integer value expected for %s, got %s",
                              'count. %s' . $alias, $count_s,
                              Auth_OpenID_AX_UNLIMITED_VALUES)
                                                    );
                }

                $values = array();
                for ($i = 1; $i < $count + 1; $i++) {
                    $value_key = sprintf('value.%s.%d', $alias, $i);

                    if (!array_key_exists($value_key, $ax_args)) {
                      return new Auth_OpenID_AX_Error(
                        sprintf(
                                "No value found for key %s",
                                $value_key));
                    }

                    $value = $ax_args[$value_key];
                    $values[] = $value;
                }
            } else {
                $key = 'value.' . $alias;

                if (!array_key_exists($key, $ax_args)) {
                  return new Auth_OpenID_AX_Error(
                    sprintf(
                            "No value found for key %s",
                            $key));
                }

                $value = $ax_args['value.' . $alias];

                if ($value == '') {
                    $values = array();
                } else {
                    $values = array($value);
                }
            }

            $this->data[$type_uri] = $values;
        }

        return true;
    }

    /**
     * Get a single value for an attribute. If no value was sent for
     * this attribute, use the supplied default. If there is more than
     * one value for this attribute, this method will fail.
     *
     * @param type_uri: The URI for the attribute
     * @param default: The value to return if the attribute was not
     * sent in the fetch_response.
     *
     * @return $value Auth_OpenID_AX_Error on failure or the value of
     * the attribute in the fetch_response message, or the default
     * supplied
     */
    function getSingle($type_uri, $default=null)
    {
        $values = Auth_OpenID::arrayGet($this->data, $type_uri);
        if (!$values) {
            return $default;
        } else if (count($values) == 1) {
            return $values[0];
        } else {
            return new Auth_OpenID_AX_Error(
              sprintf('More than one value present for %s',
                      $type_uri)
              );
        }
    }

    /**
     * Get the list of values for this attribute in the
     * fetch_response.
     *
     * XXX: what to do if the values are not present? default
     * parameter? this is funny because it's always supposed to return
     * a list, so the default may break that, though it's provided by
     * the user's code, so it might be okay. If no default is
     * supplied, should the return be None or []?
     *
     * @param type_uri: The URI of the attribute
     *
     * @return $values The list of values for this attribute in the
     * response. May be an empty list.  If the attribute was not sent
     * in the response, returns Auth_OpenID_AX_Error.
     */
    function get($type_uri)
    {
        if (array_key_exists($type_uri, $this->data)) {
            return $this->data[$type_uri];
        } else {
            return new Auth_OpenID_AX_Error(
              sprintf("Type URI %s not found in response",
                      $type_uri)
              );
        }
    }

    /**
     * Get the number of responses for a particular attribute in this
     * fetch_response message.
     *
     * @param type_uri: The URI of the attribute
     *
     * @returns int The number of values sent for this attribute.  If
     * the attribute was not sent in the response, returns
     * Auth_OpenID_AX_Error.
     */
    function count($type_uri)
    {
        if (array_key_exists($type_uri, $this->data)) {
            return count($this->get($type_uri));
        } else {
            return new Auth_OpenID_AX_Error(
              sprintf("Type URI %s not found in response",
                      $type_uri)
              );
        }
    }
}

/**
 * A fetch_response attribute exchange message.
 *
 * @package OpenID
 */
class Auth_OpenID_AX_FetchResponse extends Auth_OpenID_AX_KeyValueMessage {
    var $mode = 'fetch_response';

    function Auth_OpenID_AX_FetchResponse($update_url=null)
    {
        $this->Auth_OpenID_AX_KeyValueMessage();
        $this->update_url = $update_url;
    }

    /**
     * Serialize this object into arguments in the attribute exchange
     * namespace
     *
     * @return $args The dictionary of unqualified attribute exchange
     * arguments that represent this fetch_response, or
     * Auth_OpenID_AX_Error on error.
     */
    function getExtensionArgs($request=null)
    {
        $aliases = new Auth_OpenID_NamespaceMap();

        $zero_value_types = array();

        if ($request !== null) {
            // Validate the data in the context of the request (the
            // same attributes should be present in each, and the
            // counts in the response must be no more than the counts
            // in the request)

            foreach ($this->data as $type_uri => $unused) {
                if (!$request->contains($type_uri)) {
                    return new Auth_OpenID_AX_Error(
                      sprintf("Response attribute not present in request: %s",
                              $type_uri)
                      );
                }
            }

            foreach ($request->iterAttrs() as $attr_info) {
                // Copy the aliases from the request so that reading
                // the response in light of the request is easier
                if ($attr_info->alias === null) {
                    $aliases->add($attr_info->type_uri);
                } else {
                    $alias = $aliases->addAlias($attr_info->type_uri,
                                                $attr_info->alias);

                    if ($alias === null) {
                        return new Auth_OpenID_AX_Error(
                          sprintf("Could not add alias %s for URI %s",
                                  $attr_info->alias, $attr_info->type_uri)
                          );
                    }
                }

                if (array_key_exists($attr_info->type_uri, $this->data)) {
                    $values = $this->data[$attr_info->type_uri];
                } else {
                    $values = array();
                    $zero_value_types[] = $attr_info;
                }

                if (($attr_info->count != Auth_OpenID_AX_UNLIMITED_VALUES) &&
                    ($attr_info->count < count($values))) {
                    return new Auth_OpenID_AX_Error(
                      sprintf("More than the number of requested values " .
                              "were specified for %s",
                              $attr_info->type_uri)
                      );
                }
            }
        }

        $kv_args = $this->_getExtensionKVArgs($aliases);

        // Add the KV args into the response with the args that are
        // unique to the fetch_response
        $ax_args = $this->_newArgs();

        // For each requested attribute, put its type/alias and count
        // into the response even if no data were returned.
        foreach ($zero_value_types as $attr_info) {
            $alias = $aliases->getAlias($attr_info->type_uri);
            $kv_args['type.' . $alias] = $attr_info->type_uri;
            $kv_args['count.' . $alias] = '0';
        }

        $update_url = null;
        if ($request) {
            $update_url = $request->update_url;
        } else {
            $update_url = $this->update_url;
        }

        if ($update_url) {
            $ax_args['update_url'] = $update_url;
        }

        Auth_OpenID::update(&$ax_args, $kv_args);

        return $ax_args;
    }

    /**
     * @return $result Auth_OpenID_AX_Error on failure or true on
     * success.
     */
    function parseExtensionArgs($ax_args)
    {
        $result = parent::parseExtensionArgs($ax_args);

        if (Auth_OpenID_AX::isError($result)) {
            return $result;
        }

        $this->update_url = Auth_OpenID::arrayGet($ax_args, 'update_url');

        return true;
    }

    /**
     * Construct a FetchResponse object from an OpenID library
     * SuccessResponse object.
     *
     * @param success_response: A successful id_res response object
     *
     * @param signed: Whether non-signed args should be processsed. If
     * True (the default), only signed arguments will be processsed.
     *
     * @return $response A FetchResponse containing the data from the
     * OpenID message
     */
    function fromSuccessResponse($success_response, $signed=true)
    {
        $obj = new Auth_OpenID_AX_FetchResponse();
        if ($signed) {
            $ax_args = $success_response->getSignedNS($obj->ns_uri);
        } else {
            $ax_args = $success_response->message->getArgs($obj->ns_uri);
        }
        if ($ax_args === null || Auth_OpenID::isFailure($ax_args) ||
              sizeof($ax_args) == 0) {
            return null;
        }

        $result = $obj->parseExtensionArgs($ax_args);
        if (Auth_OpenID_AX::isError($result)) {
            #XXX log me
            return null;
        }
        return $obj;
    }
}

/**
 * A store request attribute exchange message representation.
 *
 * @package OpenID
 */
class Auth_OpenID_AX_StoreRequest extends Auth_OpenID_AX_KeyValueMessage {
    var $mode = 'store_request';

    /**
     * @param array $aliases The namespace aliases to use when making
     * this store response. Leave as None to use defaults.
     */
    function getExtensionArgs($aliases=null)
    {
        $ax_args = $this->_newArgs();
        $kv_args = $this->_getExtensionKVArgs($aliases);
        Auth_OpenID::update(&$ax_args, $kv_args);
        return $ax_args;
    }
}

/**
 * An indication that the store request was processed along with this
 * OpenID transaction.  Use make(), NOT the constructor, to create
 * response objects.
 *
 * @package OpenID
 */
class Auth_OpenID_AX_StoreResponse extends Auth_OpenID_AX_Message {
    var $SUCCESS_MODE = 'store_response_success';
    var $FAILURE_MODE = 'store_response_failure';

    /**
     * Returns Auth_OpenID_AX_Error on error or an
     * Auth_OpenID_AX_StoreResponse object on success.
     */
    function &make($succeeded=true, $error_message=null)
    {
        if (($succeeded) && ($error_message !== null)) {
            return new Auth_OpenID_AX_Error('An error message may only be '.
                                    'included in a failing fetch response');
        }

        return new Auth_OpenID_AX_StoreResponse($succeeded, $error_message);
    }

    function Auth_OpenID_AX_StoreResponse($succeeded=true, $error_message=null)
    {
        if ($succeeded) {
            $this->mode = $this->SUCCESS_MODE;
        } else {
            $this->mode = $this->FAILURE_MODE;
        }

        $this->error_message = $error_message;
    }

    /**
     * Was this response a success response?
     */
    function succeeded()
    {
        return $this->mode == $this->SUCCESS_MODE;
    }

    function getExtensionArgs()
    {
        $ax_args = $this->_newArgs();
        if ((!$this->succeeded()) && $this->error_message) {
            $ax_args['error'] = $this->error_message;
        }

        return $ax_args;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/CryptUtil.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//CryptUtil.php */ ?>
<?php

/**
 * CryptUtil: A suite of wrapper utility functions for the OpenID
 * library.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @access private
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

if (!defined('Auth_OpenID_RAND_SOURCE')) {
    /**
     * The filename for a source of random bytes. Define this yourself
     * if you have a different source of randomness.
     */
    //define('Auth_OpenID_RAND_SOURCE', '/dev/urandom');
    define('Auth_OpenID_RAND_SOURCE', null);
}

class Auth_OpenID_CryptUtil {
    /**
     * Get the specified number of random bytes.
     *
     * Attempts to use a cryptographically secure (not predictable)
     * source of randomness if available. If there is no high-entropy
     * randomness source available, it will fail. As a last resort,
     * for non-critical systems, define
     * <code>Auth_OpenID_RAND_SOURCE</code> as <code>null</code>, and
     * the code will fall back on a pseudo-random number generator.
     *
     * @param int $num_bytes The length of the return value
     * @return string $bytes random bytes
     */
    function getBytes($num_bytes)
    {
        static $f = null;
        $bytes = '';
        if ($f === null) {
            if (Auth_OpenID_RAND_SOURCE === null) {
                $f = false;
            } else {
                $f = @fopen(Auth_OpenID_RAND_SOURCE, "r");
                if ($f === false) {
                    $msg = 'Define Auth_OpenID_RAND_SOURCE as null to ' .
                        ' continue with an insecure random number generator.';
                    trigger_error($msg, E_USER_ERROR);
                }
            }
        }
        if ($f === false) {
            // pseudorandom used
            $bytes = '';
            for ($i = 0; $i < $num_bytes; $i += 4) {
                $bytes .= pack('L', mt_rand());
            }
            $bytes = substr($bytes, 0, $num_bytes);
        } else {
            $bytes = fread($f, $num_bytes);
        }
        return $bytes;
    }

    /**
     * Produce a string of length random bytes, chosen from chrs.  If
     * $chrs is null, the resulting string may contain any characters.
     *
     * @param integer $length The length of the resulting
     * randomly-generated string
     * @param string $chrs A string of characters from which to choose
     * to build the new string
     * @return string $result A string of randomly-chosen characters
     * from $chrs
     */
    function randomString($length, $population = null)
    {
        if ($population === null) {
            return Auth_OpenID_CryptUtil::getBytes($length);
        }

        $popsize = strlen($population);

        if ($popsize > 256) {
            $msg = 'More than 256 characters supplied to ' . __FUNCTION__;
            trigger_error($msg, E_USER_ERROR);
        }

        $duplicate = 256 % $popsize;

        $str = "";
        for ($i = 0; $i < $length; $i++) {
            do {
                $n = ord(Auth_OpenID_CryptUtil::getBytes(1));
            } while ($n < $duplicate);

            $n %= $popsize;
            $str .= $population[$n];
        }

        return $str;
    }
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/DatabaseConnection.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//DatabaseConnection.php */ ?>
<?php

/**
 * The Auth_OpenID_DatabaseConnection class, which is used to emulate
 * a PEAR database connection.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * An empty base class intended to emulate PEAR connection
 * functionality in applications that supply their own database
 * abstraction mechanisms.  See {@link Auth_OpenID_SQLStore} for more
 * information.  You should subclass this class if you need to create
 * an SQL store that needs to access its database using an
 * application's database abstraction layer instead of a PEAR database
 * connection.  Any subclass of Auth_OpenID_DatabaseConnection MUST
 * adhere to the interface specified here.
 *
 * @package OpenID
 */
class Auth_OpenID_DatabaseConnection {
    /**
     * Sets auto-commit mode on this database connection.
     *
     * @param bool $mode True if auto-commit is to be used; false if
     * not.
     */
    function autoCommit($mode)
    {
    }

    /**
     * Run an SQL query with the specified parameters, if any.
     *
     * @param string $sql An SQL string with placeholders.  The
     * placeholders are assumed to be specific to the database engine
     * for this connection.
     *
     * @param array $params An array of parameters to insert into the
     * SQL string using this connection's escaping mechanism.
     *
     * @return mixed $result The result of calling this connection's
     * internal query function.  The type of result depends on the
     * underlying database engine.  This method is usually used when
     * the result of a query is not important, like a DDL query.
     */
    function query($sql, $params = array())
    {
    }

    /**
     * Starts a transaction on this connection, if supported.
     */
    function begin()
    {
    }

    /**
     * Commits a transaction on this connection, if supported.
     */
    function commit()
    {
    }

    /**
     * Performs a rollback on this connection, if supported.
     */
    function rollback()
    {
    }

    /**
     * Run an SQL query and return the first column of the first row
     * of the result set, if any.
     *
     * @param string $sql An SQL string with placeholders.  The
     * placeholders are assumed to be specific to the database engine
     * for this connection.
     *
     * @param array $params An array of parameters to insert into the
     * SQL string using this connection's escaping mechanism.
     *
     * @return mixed $result The value of the first column of the
     * first row of the result set.  False if no such result was
     * found.
     */
    function getOne($sql, $params = array())
    {
    }

    /**
     * Run an SQL query and return the first row of the result set, if
     * any.
     *
     * @param string $sql An SQL string with placeholders.  The
     * placeholders are assumed to be specific to the database engine
     * for this connection.
     *
     * @param array $params An array of parameters to insert into the
     * SQL string using this connection's escaping mechanism.
     *
     * @return array $result The first row of the result set, if any,
     * keyed on column name.  False if no such result was found.
     */
    function getRow($sql, $params = array())
    {
    }

    /**
     * Run an SQL query with the specified parameters, if any.
     *
     * @param string $sql An SQL string with placeholders.  The
     * placeholders are assumed to be specific to the database engine
     * for this connection.
     *
     * @param array $params An array of parameters to insert into the
     * SQL string using this connection's escaping mechanism.
     *
     * @return array $result An array of arrays representing the
     * result of the query; each array is keyed on column name.
     */
    function getAll($sql, $params = array())
    {
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/Discover.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//Discover.php */ ?>
<?php

/**
 * The OpenID and Yadis discovery implementation for OpenID 1.2.
 */

//require_once "Auth/OpenID.php";
//require_once "Auth/OpenID/Parse.php";
//require_once "Auth/OpenID/Message.php";
//require_once "Auth/Yadis/XRIRes.php";
//require_once "Auth/Yadis/Yadis.php";

// XML namespace value
define('Auth_OpenID_XMLNS_1_0', 'http://openid.net/xmlns/1.0');

// Yadis service types
define('Auth_OpenID_TYPE_1_2', 'http://openid.net/signon/1.2');
define('Auth_OpenID_TYPE_1_1', 'http://openid.net/signon/1.1');
define('Auth_OpenID_TYPE_1_0', 'http://openid.net/signon/1.0');
define('Auth_OpenID_TYPE_2_0_IDP', 'http://specs.openid.net/auth/2.0/server');
define('Auth_OpenID_TYPE_2_0', 'http://specs.openid.net/auth/2.0/signon');
define('Auth_OpenID_RP_RETURN_TO_URL_TYPE',
       'http://specs.openid.net/auth/2.0/return_to');

function Auth_OpenID_getOpenIDTypeURIs()
{
    return array(Auth_OpenID_TYPE_2_0_IDP,
                 Auth_OpenID_TYPE_2_0,
                 Auth_OpenID_TYPE_1_2,
                 Auth_OpenID_TYPE_1_1,
                 Auth_OpenID_TYPE_1_0,
                 Auth_OpenID_RP_RETURN_TO_URL_TYPE);
}

/**
 * Object representing an OpenID service endpoint.
 */
class Auth_OpenID_ServiceEndpoint {
    function Auth_OpenID_ServiceEndpoint()
    {
        $this->claimed_id = null;
        $this->server_url = null;
        $this->type_uris = array();
        $this->local_id = null;
        $this->canonicalID = null;
        $this->used_yadis = false; // whether this came from an XRDS
        $this->display_identifier = null;
    }

    function getDisplayIdentifier()
    {
        if ($this->display_identifier) {
            return $this->display_identifier;
        }
        if (! $this->claimed_id) {
          return $this->claimed_id;
        }
        $parsed = parse_url($this->claimed_id);
        $scheme = $parsed['scheme'];
        $host = $parsed['host'];
        $path = $parsed['path'];
        if (array_key_exists('query', $parsed)) {
            $query = $parsed['query'];
            $no_frag = "$scheme://$host$path?$query";
        } else {
            $no_frag = "$scheme://$host$path";
        }
        return $no_frag;
    }

    function usesExtension($extension_uri)
    {
        return in_array($extension_uri, $this->type_uris);
    }

    function preferredNamespace()
    {
        if (in_array(Auth_OpenID_TYPE_2_0_IDP, $this->type_uris) ||
            in_array(Auth_OpenID_TYPE_2_0, $this->type_uris)) {
            return Auth_OpenID_OPENID2_NS;
        } else {
            return Auth_OpenID_OPENID1_NS;
        }
    }

    /*
     * Query this endpoint to see if it has any of the given type
     * URIs. This is useful for implementing other endpoint classes
     * that e.g. need to check for the presence of multiple versions
     * of a single protocol.
     *
     * @param $type_uris The URIs that you wish to check
     *
     * @return all types that are in both in type_uris and
     * $this->type_uris
     */
    function matchTypes($type_uris)
    {
        $result = array();
        foreach ($type_uris as $test_uri) {
            if ($this->supportsType($test_uri)) {
                $result[] = $test_uri;
            }
        }

        return $result;
    }

    function supportsType($type_uri)
    {
        // Does this endpoint support this type?
        return ((in_array($type_uri, $this->type_uris)) ||
                (($type_uri == Auth_OpenID_TYPE_2_0) &&
                 $this->isOPIdentifier()));
    }

    function compatibilityMode()
    {
        return $this->preferredNamespace() != Auth_OpenID_OPENID2_NS;
    }

    function isOPIdentifier()
    {
        return in_array(Auth_OpenID_TYPE_2_0_IDP, $this->type_uris);
    }

    function fromOPEndpointURL($op_endpoint_url)
    {
        // Construct an OP-Identifier OpenIDServiceEndpoint object for
        // a given OP Endpoint URL
        $obj = new Auth_OpenID_ServiceEndpoint();
        $obj->server_url = $op_endpoint_url;
        $obj->type_uris = array(Auth_OpenID_TYPE_2_0_IDP);
        return $obj;
    }

    function parseService($yadis_url, $uri, $type_uris, $service_element)
    {
        // Set the state of this object based on the contents of the
        // service element.  Return true if successful, false if not
        // (if findOPLocalIdentifier returns false).
        $this->type_uris = $type_uris;
        $this->server_url = $uri;
        $this->used_yadis = true;

        if (!$this->isOPIdentifier()) {
            $this->claimed_id = $yadis_url;
            $this->local_id = Auth_OpenID_findOPLocalIdentifier(
                                                    $service_element,
                                                    $this->type_uris);
            if ($this->local_id === false) {
                return false;
            }
        }

        return true;
    }

    function getLocalID()
    {
        // Return the identifier that should be sent as the
        // openid.identity_url parameter to the server.
        if ($this->local_id === null && $this->canonicalID === null) {
            return $this->claimed_id;
        } else {
            if ($this->local_id) {
                return $this->local_id;
            } else {
                return $this->canonicalID;
            }
        }
    }

    /*
     * Parse the given document as XRDS looking for OpenID services.
     *
     * @return array of Auth_OpenID_ServiceEndpoint or null if the
     * document cannot be parsed.
     */
    function fromXRDS($uri, $xrds_text)
    {
        $xrds =& Auth_Yadis_XRDS::parseXRDS($xrds_text);

        if ($xrds) {
            $yadis_services =
              $xrds->services(array('filter_MatchesAnyOpenIDType'));
            return Auth_OpenID_makeOpenIDEndpoints($uri, $yadis_services);
        }

        return null;
    }

    /*
     * Create endpoints from a DiscoveryResult.
     *
     * @param discoveryResult Auth_Yadis_DiscoveryResult
     * @return array of Auth_OpenID_ServiceEndpoint or null if
     * endpoints cannot be created.
     */
    function fromDiscoveryResult($discoveryResult)
    {
        if ($discoveryResult->isXRDS()) {
            return Auth_OpenID_ServiceEndpoint::fromXRDS(
                                     $discoveryResult->normalized_uri,
                                     $discoveryResult->response_text);
        } else {
            return Auth_OpenID_ServiceEndpoint::fromHTML(
                                     $discoveryResult->normalized_uri,
                                     $discoveryResult->response_text);
        }
    }

    function fromHTML($uri, $html)
    {
        $discovery_types = array(
                                 array(Auth_OpenID_TYPE_2_0,
                                       'openid2.provider', 'openid2.local_id'),
                                 array(Auth_OpenID_TYPE_1_1,
                                       'openid.server', 'openid.delegate')
                                 );

        $services = array();

        foreach ($discovery_types as $triple) {
            list($type_uri, $server_rel, $delegate_rel) = $triple;

            $urls = Auth_OpenID_legacy_discover($html, $server_rel,
                                                $delegate_rel);

            if ($urls === false) {
                continue;
            }

            list($delegate_url, $server_url) = $urls;

            $service = new Auth_OpenID_ServiceEndpoint();
            $service->claimed_id = $uri;
            $service->local_id = $delegate_url;
            $service->server_url = $server_url;
            $service->type_uris = array($type_uri);

            $services[] = $service;
        }

        return $services;
    }

    function copy()
    {
        $x = new Auth_OpenID_ServiceEndpoint();

        $x->claimed_id = $this->claimed_id;
        $x->server_url = $this->server_url;
        $x->type_uris = $this->type_uris;
        $x->local_id = $this->local_id;
        $x->canonicalID = $this->canonicalID;
        $x->used_yadis = $this->used_yadis;

        return $x;
    }
}

function Auth_OpenID_findOPLocalIdentifier($service, $type_uris)
{
    // Extract a openid:Delegate value from a Yadis Service element.
    // If no delegate is found, returns null.  Returns false on
    // discovery failure (when multiple delegate/localID tags have
    // different values).

    $service->parser->registerNamespace('openid',
                                        Auth_OpenID_XMLNS_1_0);

    $service->parser->registerNamespace('xrd',
                                        Auth_Yadis_XMLNS_XRD_2_0);

    $parser =& $service->parser;

    $permitted_tags = array();

    if (in_array(Auth_OpenID_TYPE_1_1, $type_uris) ||
        in_array(Auth_OpenID_TYPE_1_0, $type_uris)) {
        $permitted_tags[] = 'openid:Delegate';
    }

    if (in_array(Auth_OpenID_TYPE_2_0, $type_uris)) {
        $permitted_tags[] = 'xrd:LocalID';
    }

    $local_id = null;

    foreach ($permitted_tags as $tag_name) {
        $tags = $service->getElements($tag_name);

        foreach ($tags as $tag) {
            $content = $parser->content($tag);

            if ($local_id === null) {
                $local_id = $content;
            } else if ($local_id != $content) {
                return false;
            }
        }
    }

    return $local_id;
}

function filter_MatchesAnyOpenIDType(&$service)
{
    $uris = $service->getTypes();

    foreach ($uris as $uri) {
        if (in_array($uri, Auth_OpenID_getOpenIDTypeURIs())) {
            return true;
        }
    }

    return false;
}

function Auth_OpenID_bestMatchingService($service, $preferred_types)
{
    // Return the index of the first matching type, or something
    // higher if no type matches.
    //
    // This provides an ordering in which service elements that
    // contain a type that comes earlier in the preferred types list
    // come before service elements that come later. If a service
    // element has more than one type, the most preferred one wins.

    foreach ($preferred_types as $index => $typ) {
        if (in_array($typ, $service->type_uris)) {
            return $index;
        }
    }

    return count($preferred_types);
}

function Auth_OpenID_arrangeByType($service_list, $preferred_types)
{
    // Rearrange service_list in a new list so services are ordered by
    // types listed in preferred_types.  Return the new list.

    // Build a list with the service elements in tuples whose
    // comparison will prefer the one with the best matching service
    $prio_services = array();
    foreach ($service_list as $index => $service) {
        $prio_services[] = array(Auth_OpenID_bestMatchingService($service,
                                                        $preferred_types),
                                 $index, $service);
    }

    sort($prio_services);

    // Now that the services are sorted by priority, remove the sort
    // keys from the list.
    foreach ($prio_services as $index => $s) {
        $prio_services[$index] = $prio_services[$index][2];
    }

    return $prio_services;
}

// Extract OP Identifier services.  If none found, return the rest,
// sorted with most preferred first according to
// OpenIDServiceEndpoint.openid_type_uris.
//
// openid_services is a list of OpenIDServiceEndpoint objects.
//
// Returns a list of OpenIDServiceEndpoint objects."""
function Auth_OpenID_getOPOrUserServices($openid_services)
{
    $op_services = Auth_OpenID_arrangeByType($openid_services,
                                     array(Auth_OpenID_TYPE_2_0_IDP));

    $openid_services = Auth_OpenID_arrangeByType($openid_services,
                                     Auth_OpenID_getOpenIDTypeURIs());

    if ($op_services) {
        return $op_services;
    } else {
        return $openid_services;
    }
}

function Auth_OpenID_makeOpenIDEndpoints($uri, $yadis_services)
{
    $s = array();

    if (!$yadis_services) {
        return $s;
    }

    foreach ($yadis_services as $service) {
        $type_uris = $service->getTypes();
        $uris = $service->getURIs();

        // If any Type URIs match and there is an endpoint URI
        // specified, then this is an OpenID endpoint
        if ($type_uris &&
            $uris) {
            foreach ($uris as $service_uri) {
                $openid_endpoint = new Auth_OpenID_ServiceEndpoint();
                if ($openid_endpoint->parseService($uri,
                                                   $service_uri,
                                                   $type_uris,
                                                   $service)) {
                    $s[] = $openid_endpoint;
                }
            }
        }
    }

    return $s;
}

function Auth_OpenID_discoverWithYadis($uri, &$fetcher,
              $endpoint_filter='Auth_OpenID_getOPOrUserServices',
              $discover_function=null)
{
    // Discover OpenID services for a URI. Tries Yadis and falls back
    // on old-style <link rel='...'> discovery if Yadis fails.

    // Might raise a yadis.discover.DiscoveryFailure if no document
    // came back for that URI at all.  I don't think falling back to
    // OpenID 1.0 discovery on the same URL will help, so don't bother
    // to catch it.
    if ($discover_function === null) {
        $discover_function = array('Auth_Yadis_Yadis', 'discover');
    }

    $openid_services = array();

    $response = call_user_func_array($discover_function,
                                     array($uri, &$fetcher));

    $yadis_url = $response->normalized_uri;
    $yadis_services = array();

    if ($response->isFailure()) {
        return array($uri, array());
    }

    $openid_services = Auth_OpenID_ServiceEndpoint::fromXRDS(
                                         $yadis_url,
                                         $response->response_text);

    if (!$openid_services) {
        if ($response->isXRDS()) {
            return Auth_OpenID_discoverWithoutYadis($uri,
                                                    $fetcher);
        }

        // Try to parse the response as HTML to get OpenID 1.0/1.1
        // <link rel="...">
        $openid_services = Auth_OpenID_ServiceEndpoint::fromHTML(
                                        $yadis_url,
                                        $response->response_text);
    }

    $openid_services = call_user_func_array($endpoint_filter,
                                            array(&$openid_services));

    return array($yadis_url, $openid_services);
}

function Auth_OpenID_discoverURI($uri, &$fetcher)
{
    $uri = Auth_OpenID::normalizeUrl($uri);
    return Auth_OpenID_discoverWithYadis($uri, $fetcher);
}

function Auth_OpenID_discoverWithoutYadis($uri, &$fetcher)
{
    $http_resp = @$fetcher->get($uri);

    if ($http_resp->status != 200) {
        return array($uri, array());
    }

    $identity_url = $http_resp->final_url;

    // Try to parse the response as HTML to get OpenID 1.0/1.1 <link
    // rel="...">
    $openid_services = Auth_OpenID_ServiceEndpoint::fromHTML(
                                           $identity_url,
                                           $http_resp->body);

    return array($identity_url, $openid_services);
}

function Auth_OpenID_discoverXRI($iname, &$fetcher)
{
    $resolver = new Auth_Yadis_ProxyResolver($fetcher);
    list($canonicalID, $yadis_services) =
        $resolver->query($iname,
                         Auth_OpenID_getOpenIDTypeURIs(),
                         array('filter_MatchesAnyOpenIDType'));

    $openid_services = Auth_OpenID_makeOpenIDEndpoints($iname,
                                                       $yadis_services);

    $openid_services = Auth_OpenID_getOPOrUserServices($openid_services);

    for ($i = 0; $i < count($openid_services); $i++) {
        $openid_services[$i]->canonicalID = $canonicalID;
        $openid_services[$i]->claimed_id = $canonicalID;
        $openid_services[$i]->display_identifier = $iname;
    }

    // FIXME: returned xri should probably be in some normal form
    return array($iname, $openid_services);
}

function Auth_OpenID_discover($uri, &$fetcher)
{
    // If the fetcher (i.e., PHP) doesn't support SSL, we can't do
    // discovery on an HTTPS URL.
    if ($fetcher->isHTTPS($uri) && !$fetcher->supportsSSL()) {
        return array($uri, array());
    }

    if (Auth_Yadis_identifierScheme($uri) == 'XRI') {
        $result = Auth_OpenID_discoverXRI($uri, $fetcher);
    } else {
        $result = Auth_OpenID_discoverURI($uri, $fetcher);
    }

    // If the fetcher doesn't support SSL, we can't interact with
    // HTTPS server URLs; remove those endpoints from the list.
    if (!$fetcher->supportsSSL()) {
        $http_endpoints = array();
        list($new_uri, $endpoints) = $result;

        foreach ($endpoints as $e) {
            if (!$fetcher->isHTTPS($e->server_url)) {
                $http_endpoints[] = $e;
            }
        }

        $result = array($new_uri, $http_endpoints);
    }

    return $result;
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/HMACSHA1.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//HMACSHA1.php */ ?>
<?php

/**
 * This is the HMACSHA1 implementation for the OpenID library.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @access private
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

//require_once 'Auth/OpenID.php';

/**
 * SHA1_BLOCKSIZE is this module's SHA1 blocksize used by the fallback
 * implementation.
 */
define('Auth_OpenID_SHA1_BLOCKSIZE', 64);

function Auth_OpenID_SHA1($text)
{
    if (function_exists('hash') &&
        function_exists('hash_algos') &&
        (in_array('sha1', hash_algos()))) {
        // PHP 5 case (sometimes): 'hash' available and 'sha1' algo
        // supported.
        return hash('sha1', $text, true);
    } else if (function_exists('sha1')) {
        // PHP 4 case: 'sha1' available.
        $hex = sha1($text);
        $raw = '';
        for ($i = 0; $i < 40; $i += 2) {
            $hexcode = substr($hex, $i, 2);
            $charcode = (int)base_convert($hexcode, 16, 10);
            $raw .= chr($charcode);
        }
        return $raw;
    } else {
        // Explode.
        trigger_error('No SHA1 function found', E_USER_ERROR);
    }
}

/**
 * Compute an HMAC/SHA1 hash.
 *
 * @access private
 * @param string $key The HMAC key
 * @param string $text The message text to hash
 * @return string $mac The MAC
 */
function Auth_OpenID_HMACSHA1($key, $text)
{
    if (Auth_OpenID::bytes($key) > Auth_OpenID_SHA1_BLOCKSIZE) {
        $key = Auth_OpenID_SHA1($key, true);
    }

    $key = str_pad($key, Auth_OpenID_SHA1_BLOCKSIZE, chr(0x00));
    $ipad = str_repeat(chr(0x36), Auth_OpenID_SHA1_BLOCKSIZE);
    $opad = str_repeat(chr(0x5c), Auth_OpenID_SHA1_BLOCKSIZE);
    $hash1 = Auth_OpenID_SHA1(($key ^ $ipad) . $text, true);
    $hmac = Auth_OpenID_SHA1(($key ^ $opad) . $hash1, true);
    return $hmac;
}

if (function_exists('hash') &&
    function_exists('hash_algos') &&
    (in_array('sha256', hash_algos()))) {
    function Auth_OpenID_SHA256($text)
    {
        // PHP 5 case: 'hash' available and 'sha256' algo supported.
        return hash('sha256', $text, true);
    }
    define('Auth_OpenID_SHA256_SUPPORTED', true);
} else {
    define('Auth_OpenID_SHA256_SUPPORTED', false);
}

if (function_exists('hash_hmac') &&
    function_exists('hash_algos') &&
    (in_array('sha256', hash_algos()))) {

    function Auth_OpenID_HMACSHA256($key, $text)
    {
        // Return raw MAC (not hex string).
        return hash_hmac('sha256', $key, $text, true);
    }

    define('Auth_OpenID_HMACSHA256_SUPPORTED', true);
} else {
    define('Auth_OpenID_HMACSHA256_SUPPORTED', false);
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/HTTPFetcher.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//HTTPFetcher.php */ ?>
<?php

/**
 * This module contains the HTTP fetcher interface
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Require logging functionality
 */
//require_once "Auth/OpenID.php";

define('Auth_OpenID_FETCHER_MAX_RESPONSE_KB', 1024);
define('Auth_OpenID_USER_AGENT',
       'php-openid/'.Auth_OpenID_VERSION.' (php/'.phpversion().')');

class Auth_Yadis_HTTPResponse {
    function Auth_Yadis_HTTPResponse($final_url = null, $status = null,
                                         $headers = null, $body = null)
    {
        $this->final_url = $final_url;
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }
}

/**
 * This class is the interface for HTTP fetchers the Yadis library
 * uses.  This interface is only important if you need to write a new
 * fetcher for some reason.
 *
 * @access private
 * @package OpenID
 */
class Auth_Yadis_HTTPFetcher {

    var $timeout = 20; // timeout in seconds.

    /**
     * Return whether a URL can be fetched.  Returns false if the URL
     * scheme is not allowed or is not supported by this fetcher
     * implementation; returns true otherwise.
     *
     * @return bool
     */
    function canFetchURL($url)
    {
        if ($this->isHTTPS($url) && !$this->supportsSSL()) {
            Auth_OpenID::log("HTTPS URL unsupported fetching %s",
                             $url);
            return false;
        }

        if (!$this->allowedURL($url)) {
            Auth_OpenID::log("URL fetching not allowed for '%s'",
                             $url);
            return false;
        }

        return true;
    }

    /**
     * Return whether a URL should be allowed. Override this method to
     * conform to your local policy.
     *
     * By default, will attempt to fetch any http or https URL.
     */
    function allowedURL($url)
    {
        return $this->URLHasAllowedScheme($url);
    }

    /**
     * Does this fetcher implementation (and runtime) support fetching
     * HTTPS URLs?  May inspect the runtime environment.
     *
     * @return bool $support True if this fetcher supports HTTPS
     * fetching; false if not.
     */
    function supportsSSL()
    {
        trigger_error("not implemented", E_USER_ERROR);
    }

    /**
     * Is this an https URL?
     *
     * @access private
     */
    function isHTTPS($url)
    {
        return (bool)preg_match('/^https:\/\//i', $url);
    }

    /**
     * Is this an http or https URL?
     *
     * @access private
     */
    function URLHasAllowedScheme($url)
    {
        return (bool)preg_match('/^https?:\/\//i', $url);
    }

    /**
     * @access private
     */
    function _findRedirect($headers)
    {
        foreach ($headers as $line) {
            if (strpos(strtolower($line), "location: ") === 0) {
                $parts = explode(" ", $line, 2);
                return $parts[1];
            }
        }
        return null;
    }

    /**
     * Fetches the specified URL using optional extra headers and
     * returns the server's response.
     *
     * @param string $url The URL to be fetched.
     * @param array $extra_headers An array of header strings
     * (e.g. "Accept: text/html").
     * @return mixed $result An array of ($code, $url, $headers,
     * $body) if the URL could be fetched; null if the URL does not
     * pass the URLHasAllowedScheme check or if the server's response
     * is malformed.
     */
    function get($url, $headers)
    {
        trigger_error("not implemented", E_USER_ERROR);
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/Interface.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//Interface.php */ ?>
<?php

/**
 * This file specifies the interface for PHP OpenID store implementations.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * This is the interface for the store objects the OpenID library
 * uses. It is a single class that provides all of the persistence
 * mechanisms that the OpenID library needs, for both servers and
 * consumers.  If you want to create an SQL-driven store, please see
 * then {@link Auth_OpenID_SQLStore} class.
 *
 * Change: Version 2.0 removed the storeNonce, getAuthKey, and isDumb
 * methods, and changed the behavior of the useNonce method to support
 * one-way nonces.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 */
class Auth_OpenID_OpenIDStore {
    /**
     * This method puts an Association object into storage,
     * retrievable by server URL and handle.
     *
     * @param string $server_url The URL of the identity server that
     * this association is with. Because of the way the server portion
     * of the library uses this interface, don't assume there are any
     * limitations on the character set of the input string. In
     * particular, expect to see unescaped non-url-safe characters in
     * the server_url field.
     *
     * @param Association $association The Association to store.
     */
    function storeAssociation($server_url, $association)
    {
        trigger_error("Auth_OpenID_OpenIDStore::storeAssociation ".
                      "not implemented", E_USER_ERROR);
    }

    /*
     * Remove expired nonces from the store.
     *
     * Discards any nonce from storage that is old enough that its
     * timestamp would not pass useNonce().
     *
     * This method is not called in the normal operation of the
     * library.  It provides a way for store admins to keep their
     * storage from filling up with expired data.
     *
     * @return the number of nonces expired
     */
    function cleanupNonces()
    {
        trigger_error("Auth_OpenID_OpenIDStore::cleanupNonces ".
                      "not implemented", E_USER_ERROR);
    }

    /*
     * Remove expired associations from the store.
     *
     * This method is not called in the normal operation of the
     * library.  It provides a way for store admins to keep their
     * storage from filling up with expired data.
     *
     * @return the number of associations expired.
     */
    function cleanupAssociations()
    {
        trigger_error("Auth_OpenID_OpenIDStore::cleanupAssociations ".
                      "not implemented", E_USER_ERROR);
    }

    /*
     * Shortcut for cleanupNonces(), cleanupAssociations().
     *
     * This method is not called in the normal operation of the
     * library.  It provides a way for store admins to keep their
     * storage from filling up with expired data.
     */
    function cleanup()
    {
        return array($this->cleanupNonces(),
                     $this->cleanupAssociations());
    }

    /**
     * Report whether this storage supports cleanup
     */
    function supportsCleanup()
    {
        return true;
    }

    /**
     * This method returns an Association object from storage that
     * matches the server URL and, if specified, handle. It returns
     * null if no such association is found or if the matching
     * association is expired.
     *
     * If no handle is specified, the store may return any association
     * which matches the server URL. If multiple associations are
     * valid, the recommended return value for this method is the one
     * most recently issued.
     *
     * This method is allowed (and encouraged) to garbage collect
     * expired associations when found. This method must not return
     * expired associations.
     *
     * @param string $server_url The URL of the identity server to get
     * the association for. Because of the way the server portion of
     * the library uses this interface, don't assume there are any
     * limitations on the character set of the input string.  In
     * particular, expect to see unescaped non-url-safe characters in
     * the server_url field.
     *
     * @param mixed $handle This optional parameter is the handle of
     * the specific association to get. If no specific handle is
     * provided, any valid association matching the server URL is
     * returned.
     *
     * @return Association The Association for the given identity
     * server.
     */
    function getAssociation($server_url, $handle = null)
    {
        trigger_error("Auth_OpenID_OpenIDStore::getAssociation ".
                      "not implemented", E_USER_ERROR);
    }

    /**
     * This method removes the matching association if it's found, and
     * returns whether the association was removed or not.
     *
     * @param string $server_url The URL of the identity server the
     * association to remove belongs to. Because of the way the server
     * portion of the library uses this interface, don't assume there
     * are any limitations on the character set of the input
     * string. In particular, expect to see unescaped non-url-safe
     * characters in the server_url field.
     *
     * @param string $handle This is the handle of the association to
     * remove. If there isn't an association found that matches both
     * the given URL and handle, then there was no matching handle
     * found.
     *
     * @return mixed Returns whether or not the given association existed.
     */
    function removeAssociation($server_url, $handle)
    {
        trigger_error("Auth_OpenID_OpenIDStore::removeAssociation ".
                      "not implemented", E_USER_ERROR);
    }

    /**
     * Called when using a nonce.
     *
     * This method should return C{True} if the nonce has not been
     * used before, and store it for a while to make sure nobody
     * tries to use the same value again.  If the nonce has already
     * been used, return C{False}.
     *
     * Change: In earlier versions, round-trip nonces were used and a
     * nonce was only valid if it had been previously stored with
     * storeNonce.  Version 2.0 uses one-way nonces, requiring a
     * different implementation here that does not depend on a
     * storeNonce call.  (storeNonce is no longer part of the
     * interface.
     *
     * @param string $nonce The nonce to use.
     *
     * @return bool Whether or not the nonce was valid.
     */
    function useNonce($server_url, $timestamp, $salt)
    {
        trigger_error("Auth_OpenID_OpenIDStore::useNonce ".
                      "not implemented", E_USER_ERROR);
    }

    /**
     * Removes all entries from the store; implementation is optional.
     */
    function reset()
    {
    }

}
 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/KVForm.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//KVForm.php */ ?>
<?php

/**
 * OpenID protocol key-value/comma-newline format parsing and
 * serialization
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @access private
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Container for key-value/comma-newline OpenID format and parsing
 */
class Auth_OpenID_KVForm {
    /**
     * Convert an OpenID colon/newline separated string into an
     * associative array
     *
     * @static
     * @access private
     */
    function toArray($kvs, $strict=false)
    {
        $lines = explode("\n", $kvs);

        $last = array_pop($lines);
        if ($last !== '') {
            array_push($lines, $last);
            if ($strict) {
                return false;
            }
        }

        $values = array();

        for ($lineno = 0; $lineno < count($lines); $lineno++) {
            $line = $lines[$lineno];
            $kv = explode(':', $line, 2);
            if (count($kv) != 2) {
                if ($strict) {
                    return false;
                }
                continue;
            }

            $key = $kv[0];
            $tkey = trim($key);
            if ($tkey != $key) {
                if ($strict) {
                    return false;
                }
            }

            $value = $kv[1];
            $tval = trim($value);
            if ($tval != $value) {
                if ($strict) {
                    return false;
                }
            }

            $values[$tkey] = $tval;
        }

        return $values;
    }

    /**
     * Convert an array into an OpenID colon/newline separated string
     *
     * @static
     * @access private
     */
    function fromArray($values)
    {
        if ($values === null) {
            return null;
        }

        ksort($values);

        $serialized = '';
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                list($key, $value) = array($value[0], $value[1]);
            }

            if (strpos($key, ':') !== false) {
                return null;
            }

            if (strpos($key, "\n") !== false) {
                return null;
            }

            if (strpos($value, "\n") !== false) {
                return null;
            }
            $serialized .= "$key:$value\n";
        }
        return $serialized;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/Manager.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//Manager.php */ ?>
<?php

/**
 * Yadis service manager to be used during yadis-driven authentication
 * attempts.
 *
 * @package OpenID
 */

/**
 * The base session class used by the Auth_Yadis_Manager.  This
 * class wraps the default PHP session machinery and should be
 * subclassed if your application doesn't use PHP sessioning.
 *
 * @package OpenID
 */
class Auth_Yadis_PHPSession {
    /**
     * Set a session key/value pair.
     *
     * @param string $name The name of the session key to add.
     * @param string $value The value to add to the session.
     */
    function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Get a key's value from the session.
     *
     * @param string $name The name of the key to retrieve.
     * @param string $default The optional value to return if the key
     * is not found in the session.
     * @return string $result The key's value in the session or
     * $default if it isn't found.
     */
    function get($name, $default=null)
    {
        if (array_key_exists($name, $_SESSION)) {
            return $_SESSION[$name];
        } else {
            return $default;
        }
    }

    /**
     * Remove a key/value pair from the session.
     *
     * @param string $name The name of the key to remove.
     */
    function del($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * Return the contents of the session in array form.
     */
    function contents()
    {
        return $_SESSION;
    }
}

/**
 * A session helper class designed to translate between arrays and
 * objects.  Note that the class used must have a constructor that
 * takes no parameters.  This is not a general solution, but it works
 * for dumb objects that just need to have attributes set.  The idea
 * is that you'll subclass this and override $this->check($data) ->
 * bool to implement your own session data validation.
 *
 * @package OpenID
 */
class Auth_Yadis_SessionLoader {
    /**
     * Override this.
     *
     * @access private
     */
    function check($data)
    {
        return true;
    }

    /**
     * Given a session data value (an array), this creates an object
     * (returned by $this->newObject()) whose attributes and values
     * are those in $data.  Returns null if $data lacks keys found in
     * $this->requiredKeys().  Returns null if $this->check($data)
     * evaluates to false.  Returns null if $this->newObject()
     * evaluates to false.
     *
     * @access private
     */
    function fromSession($data)
    {
        if (!$data) {
            return null;
        }

        $required = $this->requiredKeys();

        foreach ($required as $k) {
            if (!array_key_exists($k, $data)) {
                return null;
            }
        }

        if (!$this->check($data)) {
            return null;
        }

        $data = array_merge($data, $this->prepareForLoad($data));
        $obj = $this->newObject($data);

        if (!$obj) {
            return null;
        }

        foreach ($required as $k) {
            $obj->$k = $data[$k];
        }

        return $obj;
    }

    /**
     * Prepares the data array by making any necessary changes.
     * Returns an array whose keys and values will be used to update
     * the original data array before calling $this->newObject($data).
     *
     * @access private
     */
    function prepareForLoad($data)
    {
        return array();
    }

    /**
     * Returns a new instance of this loader's class, using the
     * session data to construct it if necessary.  The object need
     * only be created; $this->fromSession() will take care of setting
     * the object's attributes.
     *
     * @access private
     */
    function newObject($data)
    {
        return null;
    }

    /**
     * Returns an array of keys and values built from the attributes
     * of $obj.  If $this->prepareForSave($obj) returns an array, its keys
     * and values are used to update the $data array of attributes
     * from $obj.
     *
     * @access private
     */
    function toSession($obj)
    {
        $data = array();
        foreach ($obj as $k => $v) {
            $data[$k] = $v;
        }

        $extra = $this->prepareForSave($obj);

        if ($extra && is_array($extra)) {
            foreach ($extra as $k => $v) {
                $data[$k] = $v;
            }
        }

        return $data;
    }

    /**
     * Override this.
     *
     * @access private
     */
    function prepareForSave($obj)
    {
        return array();
    }
}

/**
 * A concrete loader implementation for Auth_OpenID_ServiceEndpoints.
 *
 * @package OpenID
 */
class Auth_OpenID_ServiceEndpointLoader extends Auth_Yadis_SessionLoader {
    function newObject($data)
    {
        return new Auth_OpenID_ServiceEndpoint();
    }

    function requiredKeys()
    {
        $obj = new Auth_OpenID_ServiceEndpoint();
        $data = array();
        foreach ($obj as $k => $v) {
            $data[] = $k;
        }
        return $data;
    }

    function check($data)
    {
        return is_array($data['type_uris']);
    }
}

/**
 * A concrete loader implementation for Auth_Yadis_Managers.
 *
 * @package OpenID
 */
class Auth_Yadis_ManagerLoader extends Auth_Yadis_SessionLoader {
    function requiredKeys()
    {
        return array('starting_url',
                     'yadis_url',
                     'services',
                     'session_key',
                     '_current',
                     'stale');
    }

    function newObject($data)
    {
        return new Auth_Yadis_Manager($data['starting_url'],
                                          $data['yadis_url'],
                                          $data['services'],
                                          $data['session_key']);
    }

    function check($data)
    {
        return is_array($data['services']);
    }

    function prepareForLoad($data)
    {
        $loader = new Auth_OpenID_ServiceEndpointLoader();
        $services = array();
        foreach ($data['services'] as $s) {
            $services[] = $loader->fromSession($s);
        }
        return array('services' => $services);
    }

    function prepareForSave($obj)
    {
        $loader = new Auth_OpenID_ServiceEndpointLoader();
        $services = array();
        foreach ($obj->services as $s) {
            $services[] = $loader->toSession($s);
        }
        return array('services' => $services);
    }
}

/**
 * The Yadis service manager which stores state in a session and
 * iterates over <Service> elements in a Yadis XRDS document and lets
 * a caller attempt to use each one.  This is used by the Yadis
 * library internally.
 *
 * @package OpenID
 */
class Auth_Yadis_Manager {

    /**
     * Intialize a new yadis service manager.
     *
     * @access private
     */
    function Auth_Yadis_Manager($starting_url, $yadis_url,
                                    $services, $session_key)
    {
        // The URL that was used to initiate the Yadis protocol
        $this->starting_url = $starting_url;

        // The URL after following redirects (the identifier)
        $this->yadis_url = $yadis_url;

        // List of service elements
        $this->services = $services;

        $this->session_key = $session_key;

        // Reference to the current service object
        $this->_current = null;

        // Stale flag for cleanup if PHP lib has trouble.
        $this->stale = false;
    }

    /**
     * @access private
     */
    function length()
    {
        // How many untried services remain?
        return count($this->services);
    }

    /**
     * Return the next service
     *
     * $this->current() will continue to return that service until the
     * next call to this method.
     */
    function nextService()
    {

        if ($this->services) {
            $this->_current = array_shift($this->services);
        } else {
            $this->_current = null;
        }

        return $this->_current;
    }

    /**
     * @access private
     */
    function current()
    {
        // Return the current service.
        // Returns None if there are no services left.
        return $this->_current;
    }

    /**
     * @access private
     */
    function forURL($url)
    {
        return in_array($url, array($this->starting_url, $this->yadis_url));
    }

    /**
     * @access private
     */
    function started()
    {
        // Has the first service been returned?
        return $this->_current !== null;
    }
}

/**
 * State management for discovery.
 *
 * High-level usage pattern is to call .getNextService(discover) in
 * order to find the next available service for this user for this
 * session. Once a request completes, call .cleanup() to clean up the
 * session state.
 *
 * @package OpenID
 */
class Auth_Yadis_Discovery {

    /**
     * @access private
     */
    var $DEFAULT_SUFFIX = 'auth';

    /**
     * @access private
     */
    var $PREFIX = '_yadis_services_';

    /**
     * Initialize a discovery object.
     *
     * @param Auth_Yadis_PHPSession $session An object which
     * implements the Auth_Yadis_PHPSession API.
     * @param string $url The URL on which to attempt discovery.
     * @param string $session_key_suffix The optional session key
     * suffix override.
     */
    function Auth_Yadis_Discovery(&$session, $url,
                                      $session_key_suffix = null)
    {
        /// Initialize a discovery object
        $this->session =& $session;
        $this->url = $url;
        if ($session_key_suffix === null) {
            $session_key_suffix = $this->DEFAULT_SUFFIX;
        }

        $this->session_key_suffix = $session_key_suffix;
        $this->session_key = $this->PREFIX . $this->session_key_suffix;
    }

    /**
     * Return the next authentication service for the pair of
     * user_input and session. This function handles fallback.
     */
    function getNextService($discover_cb, &$fetcher)
    {
        $manager = $this->getManager();
        if (!$manager || (!$manager->services)) {
            $this->destroyManager();

            list($yadis_url, $services) = call_user_func($discover_cb,
                                                         $this->url,
                                                         $fetcher);

            $manager = $this->createManager($services, $yadis_url);
        }

        if ($manager) {
            $loader = new Auth_Yadis_ManagerLoader();
            $service = $manager->nextService();
            $this->session->set($this->session_key,
                                serialize($loader->toSession($manager)));
        } else {
            $service = null;
        }

        return $service;
    }

    /**
     * Clean up Yadis-related services in the session and return the
     * most-recently-attempted service from the manager, if one
     * exists.
     *
     * @param $force True if the manager should be deleted regardless
     * of whether it's a manager for $this->url.
     */
    function cleanup($force=false)
    {
        $manager = $this->getManager($force);
        if ($manager) {
            $service = $manager->current();
            $this->destroyManager($force);
        } else {
            $service = null;
        }

        return $service;
    }

    /**
     * @access private
     */
    function getSessionKey()
    {
        // Get the session key for this starting URL and suffix
        return $this->PREFIX . $this->session_key_suffix;
    }

    /**
     * @access private
     *
     * @param $force True if the manager should be returned regardless
     * of whether it's a manager for $this->url.
     */
    function &getManager($force=false)
    {
        // Extract the YadisServiceManager for this object's URL and
        // suffix from the session.

        $manager_str = $this->session->get($this->getSessionKey());
        $manager = null;

        if ($manager_str !== null) {
            $loader = new Auth_Yadis_ManagerLoader();
            $manager = $loader->fromSession(unserialize($manager_str));
        }

        if ($manager && ($manager->forURL($this->url) || $force)) {
            return $manager;
        } else {
            $unused = null;
            return $unused;
        }
    }

    /**
     * @access private
     */
    function &createManager($services, $yadis_url = null)
    {
        $key = $this->getSessionKey();
        if ($this->getManager()) {
            return $this->getManager();
        }

        if ($services) {
            $loader = new Auth_Yadis_ManagerLoader();
            $manager = new Auth_Yadis_Manager($this->url, $yadis_url,
                                              $services, $key);
            $this->session->set($this->session_key,
                                serialize($loader->toSession($manager)));
            return $manager;
        } else {
            // Oh, PHP.
            $unused = null;
            return $unused;
        }
    }

    /**
     * @access private
     *
     * @param $force True if the manager should be deleted regardless
     * of whether it's a manager for $this->url.
     */
    function destroyManager($force=false)
    {
        if ($this->getManager($force) !== null) {
            $key = $this->getSessionKey();
            $this->session->del($key);
        }
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/Message.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//Message.php */ ?>
<?php

/**
 * Extension argument processing code
 *
 * @package OpenID
 */

/**
 * Import tools needed to deal with messages.
 */
//require_once 'Auth/OpenID.php';
//require_once 'Auth/OpenID/KVForm.php';
//require_once 'Auth/Yadis/XML.php';

// This doesn't REALLY belong here, but where is better?
define('Auth_OpenID_IDENTIFIER_SELECT',
       "http://specs.openid.net/auth/2.0/identifier_select");

// URI for Simple Registration extension, the only commonly deployed
// OpenID 1.x extension, and so a special case
define('Auth_OpenID_SREG_URI', 'http://openid.net/sreg/1.0');

// The OpenID 1.X namespace URI
define('Auth_OpenID_OPENID1_NS', 'http://openid.net/signon/1.0');

// The OpenID 2.0 namespace URI
define('Auth_OpenID_OPENID2_NS', 'http://specs.openid.net/auth/2.0');

// The namespace consisting of pairs with keys that are prefixed with
// "openid."  but not in another namespace.
define('Auth_OpenID_NULL_NAMESPACE', 'Null namespace');

// The null namespace, when it is an allowed OpenID namespace
define('Auth_OpenID_OPENID_NS', 'OpenID namespace');

// The top-level namespace, excluding all pairs with keys that start
// with "openid."
define('Auth_OpenID_BARE_NS', 'Bare namespace');

// Sentinel for Message implementation to indicate that getArg should
// return null instead of returning a default.
define('Auth_OpenID_NO_DEFAULT', 'NO DEFAULT ALLOWED');

// Limit, in bytes, of identity provider and return_to URLs, including
// response payload.  See OpenID 1.1 specification, Appendix D.
define('Auth_OpenID_OPENID1_URL_LIMIT', 2047);

// All OpenID protocol fields.  Used to check namespace aliases.
global $Auth_OpenID_OPENID_PROTOCOL_FIELDS;
$Auth_OpenID_OPENID_PROTOCOL_FIELDS = array(
    'ns', 'mode', 'error', 'return_to', 'contact', 'reference',
    'signed', 'assoc_type', 'session_type', 'dh_modulus', 'dh_gen',
    'dh_consumer_public', 'claimed_id', 'identity', 'realm',
    'invalidate_handle', 'op_endpoint', 'response_nonce', 'sig',
    'assoc_handle', 'trust_root', 'openid');

// Global namespace / alias registration map.  See
// Auth_OpenID_registerNamespaceAlias.
global $Auth_OpenID_registered_aliases;
$Auth_OpenID_registered_aliases = array();

/**
 * Registers a (namespace URI, alias) mapping in a global namespace
 * alias map.  Raises NamespaceAliasRegistrationError if either the
 * namespace URI or alias has already been registered with a different
 * value.  This function is required if you want to use a namespace
 * with an OpenID 1 message.
 */
function Auth_OpenID_registerNamespaceAlias($namespace_uri, $alias)
{
    global $Auth_OpenID_registered_aliases;

    if (Auth_OpenID::arrayGet($Auth_OpenID_registered_aliases,
                              $alias) == $namespace_uri) {
        return true;
    }

    if (in_array($namespace_uri,
                 array_values($Auth_OpenID_registered_aliases))) {
        return false;
    }

    if (in_array($alias, array_keys($Auth_OpenID_registered_aliases))) {
        return false;
    }

    $Auth_OpenID_registered_aliases[$alias] = $namespace_uri;
    return true;
}

/**
 * Removes a (namespace_uri, alias) registration from the global
 * namespace alias map.  Returns true if the removal succeeded; false
 * if not (if the mapping did not exist).
 */
function Auth_OpenID_removeNamespaceAlias($namespace_uri, $alias)
{
    global $Auth_OpenID_registered_aliases;

    if (Auth_OpenID::arrayGet($Auth_OpenID_registered_aliases,
                              $alias) === $namespace_uri) {
        unset($Auth_OpenID_registered_aliases[$alias]);
        return true;
    }

    return false;
}

/**
 * An Auth_OpenID_Mapping maintains a mapping from arbitrary keys to
 * arbitrary values.  (This is unlike an ordinary PHP array, whose
 * keys may be only simple scalars.)
 *
 * @package OpenID
 */
class Auth_OpenID_Mapping {
    /**
     * Initialize a mapping.  If $classic_array is specified, its keys
     * and values are used to populate the mapping.
     */
    function Auth_OpenID_Mapping($classic_array = null)
    {
        $this->keys = array();
        $this->values = array();

        if (is_array($classic_array)) {
            foreach ($classic_array as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Returns true if $thing is an Auth_OpenID_Mapping object; false
     * if not.
     */
    function isA($thing)
    {
        return (is_object($thing) &&
                strtolower(get_class($thing)) == 'auth_openid_mapping');
    }

    /**
     * Returns an array of the keys in the mapping.
     */
    function keys()
    {
        return $this->keys;
    }

    /**
     * Returns an array of values in the mapping.
     */
    function values()
    {
        return $this->values;
    }

    /**
     * Returns an array of (key, value) pairs in the mapping.
     */
    function items()
    {
        $temp = array();

        for ($i = 0; $i < count($this->keys); $i++) {
            $temp[] = array($this->keys[$i],
                            $this->values[$i]);
        }
        return $temp;
    }

    /**
     * Returns the "length" of the mapping, or the number of keys.
     */
    function len()
    {
        return count($this->keys);
    }

    /**
     * Sets a key-value pair in the mapping.  If the key already
     * exists, its value is replaced with the new value.
     */
    function set($key, $value)
    {
        $index = array_search($key, $this->keys);

        if ($index !== false) {
            $this->values[$index] = $value;
        } else {
            $this->keys[] = $key;
            $this->values[] = $value;
        }
    }

    /**
     * Gets a specified value from the mapping, associated with the
     * specified key.  If the key does not exist in the mapping,
     * $default is returned instead.
     */
    function get($key, $default = null)
    {
        $index = array_search($key, $this->keys);

        if ($index !== false) {
            return $this->values[$index];
        } else {
            return $default;
        }
    }

    /**
     * @access private
     */
    function _reflow()
    {
        // PHP is broken yet again.  Sort the arrays to remove the
        // hole in the numeric indexes that make up the array.
        $old_keys = $this->keys;
        $old_values = $this->values;

        $this->keys = array();
        $this->values = array();

        foreach ($old_keys as $k) {
            $this->keys[] = $k;
        }

        foreach ($old_values as $v) {
            $this->values[] = $v;
        }
    }

    /**
     * Deletes a key-value pair from the mapping with the specified
     * key.
     */
    function del($key)
    {
        $index = array_search($key, $this->keys);

        if ($index !== false) {
            unset($this->keys[$index]);
            unset($this->values[$index]);
            $this->_reflow();
            return true;
        }
        return false;
    }

    /**
     * Returns true if the specified value has a key in the mapping;
     * false if not.
     */
    function contains($value)
    {
        return (array_search($value, $this->keys) !== false);
    }
}

/**
 * Maintains a bijective map between namespace uris and aliases.
 *
 * @package OpenID
 */
class Auth_OpenID_NamespaceMap {
    function Auth_OpenID_NamespaceMap()
    {
        $this->alias_to_namespace = new Auth_OpenID_Mapping();
        $this->namespace_to_alias = new Auth_OpenID_Mapping();
        $this->implicit_namespaces = array();
    }

    function getAlias($namespace_uri)
    {
        return $this->namespace_to_alias->get($namespace_uri);
    }

    function getNamespaceURI($alias)
    {
        return $this->alias_to_namespace->get($alias);
    }

    function iterNamespaceURIs()
    {
        // Return an iterator over the namespace URIs
        return $this->namespace_to_alias->keys();
    }

    function iterAliases()
    {
        // Return an iterator over the aliases"""
        return $this->alias_to_namespace->keys();
    }

    function iteritems()
    {
        return $this->namespace_to_alias->items();
    }

    function isImplicit($namespace_uri)
    {
        return in_array($namespace_uri, $this->implicit_namespaces);
    }

    function addAlias($namespace_uri, $desired_alias, $implicit=false)
    {
        // Add an alias from this namespace URI to the desired alias
        global $Auth_OpenID_OPENID_PROTOCOL_FIELDS;

        // Check that desired_alias is not an openid protocol field as
        // per the spec.
        if (in_array($desired_alias, $Auth_OpenID_OPENID_PROTOCOL_FIELDS)) {
            // "%r is not an allowed namespace alias" % (desired_alias,);
            return null;
        }

        // Check that desired_alias does not contain a period as per
        // the spec.
        if (strpos($desired_alias, '.') !== false) {
            // "%r must not contain a dot" % (desired_alias,)
            return null;
        }

        // Check that there is not a namespace already defined for the
        // desired alias
        $current_namespace_uri =
            $this->alias_to_namespace->get($desired_alias);

        if (($current_namespace_uri !== null) &&
            ($current_namespace_uri != $namespace_uri)) {
            // Cannot map because previous mapping exists
            return null;
        }

        // Check that there is not already a (different) alias for
        // this namespace URI
        $alias = $this->namespace_to_alias->get($namespace_uri);

        if (($alias !== null) && ($alias != $desired_alias)) {
            // fmt = ('Cannot map %r to alias %r. '
            //        'It is already mapped to alias %r')
            // raise KeyError(fmt % (namespace_uri, desired_alias, alias))
            return null;
        }

        assert((Auth_OpenID_NULL_NAMESPACE === $desired_alias) ||
               is_string($desired_alias));

        $this->alias_to_namespace->set($desired_alias, $namespace_uri);
        $this->namespace_to_alias->set($namespace_uri, $desired_alias);
        if ($implicit) {
            array_push($this->implicit_namespaces, $namespace_uri);
        }

        return $desired_alias;
    }

    function add($namespace_uri)
    {
        // Add this namespace URI to the mapping, without caring what
        // alias it ends up with

        // See if this namespace is already mapped to an alias
        $alias = $this->namespace_to_alias->get($namespace_uri);

        if ($alias !== null) {
            return $alias;
        }

        // Fall back to generating a numerical alias
        $i = 0;
        while (1) {
            $alias = 'ext' . strval($i);
            if ($this->addAlias($namespace_uri, $alias) === null) {
                $i += 1;
            } else {
                return $alias;
            }
        }

        // Should NEVER be reached!
        return null;
    }

    function contains($namespace_uri)
    {
        return $this->isDefined($namespace_uri);
    }

    function isDefined($namespace_uri)
    {
        return $this->namespace_to_alias->contains($namespace_uri);
    }
}

/**
 * In the implementation of this object, null represents the global
 * namespace as well as a namespace with no key.
 *
 * @package OpenID
 */
class Auth_OpenID_Message {

    function Auth_OpenID_Message($openid_namespace = null)
    {
        // Create an empty Message
        $this->allowed_openid_namespaces = array(
                               Auth_OpenID_OPENID1_NS,
                               Auth_OpenID_OPENID2_NS);

        $this->args = new Auth_OpenID_Mapping();
        $this->namespaces = new Auth_OpenID_NamespaceMap();
        if ($openid_namespace === null) {
            $this->_openid_ns_uri = null;
        } else {
            $this->setOpenIDNamespace($openid_namespace);
        }
    }

    function isOpenID1()
    {
        return $this->getOpenIDNamespace() == Auth_OpenID_OPENID1_NS;
    }

    function isOpenID2()
    {
        return $this->getOpenIDNamespace() == Auth_OpenID_OPENID2_NS;
    }

    function fromPostArgs($args)
    {
        // Construct a Message containing a set of POST arguments
        $obj = new Auth_OpenID_Message();

        // Partition into "openid." args and bare args
        $openid_args = array();
        foreach ($args as $key => $value) {

            if (is_array($value)) {
                return null;
            }

            $parts = explode('.', $key, 2);

            if (count($parts) == 2) {
                list($prefix, $rest) = $parts;
            } else {
                $prefix = null;
            }

            if ($prefix != 'openid') {
                $obj->args->set(array(Auth_OpenID_BARE_NS, $key), $value);
            } else {
                $openid_args[$rest] = $value;
            }
        }

        if ($obj->_fromOpenIDArgs($openid_args)) {
            return $obj;
        } else {
            return null;
        }
    }

    function fromOpenIDArgs($openid_args)
    {
        // Takes an array.

        // Construct a Message from a parsed KVForm message
        $obj = new Auth_OpenID_Message();
        if ($obj->_fromOpenIDArgs($openid_args)) {
            return $obj;
        } else {
            return null;
        }
    }

    /**
     * @access private
     */
    function _fromOpenIDArgs($openid_args)
    {
        global $Auth_OpenID_registered_aliases;

        // Takes an Auth_OpenID_Mapping instance OR an array.

        if (!Auth_OpenID_Mapping::isA($openid_args)) {
            $openid_args = new Auth_OpenID_Mapping($openid_args);
        }

        $ns_args = array();

        // Resolve namespaces
        foreach ($openid_args->items() as $pair) {
            list($rest, $value) = $pair;

            $parts = explode('.', $rest, 2);

            if (count($parts) == 2) {
                list($ns_alias, $ns_key) = $parts;
            } else {
                $ns_alias = Auth_OpenID_NULL_NAMESPACE;
                $ns_key = $rest;
            }

            if ($ns_alias == 'ns') {
                if ($this->namespaces->addAlias($value, $ns_key) === null) {
                    return false;
                }
            } else if (($ns_alias == Auth_OpenID_NULL_NAMESPACE) &&
                       ($ns_key == 'ns')) {
                // null namespace
                if ($this->namespaces->addAlias($value,
                                     Auth_OpenID_NULL_NAMESPACE) === null) {
                    return false;
                }
            } else {
                $ns_args[] = array($ns_alias, $ns_key, $value);
            }
        }

        // Ensure that there is an OpenID namespace definition
        $openid_ns_uri =
            $this->namespaces->getNamespaceURI(Auth_OpenID_NULL_NAMESPACE);

        $this->setOpenIDNamespace($openid_ns_uri);

        // Actually put the pairs into the appropriate namespaces
        foreach ($ns_args as $triple) {
            list($ns_alias, $ns_key, $value) = $triple;
            $ns_uri = $this->namespaces->getNamespaceURI($ns_alias);
            if ($ns_uri === null) {
                $ns_uri = $this->_getDefaultNamespace($ns_alias);
                if ($ns_uri === null) {
                    $ns_uri = Auth_OpenID_OPENID_NS;
                    $ns_key = sprintf('%s.%s', $ns_alias, $ns_key);
                } else {
                    $this->namespaces->addAlias($ns_uri, $ns_alias, true);
                }
            }

            $this->setArg($ns_uri, $ns_key, $value);
        }

        return true;
    }

    function _getDefaultNamespace($mystery_alias)
    {
        global $Auth_OpenID_registered_aliases;
        if ($this->isOpenID1()) {
            return @$Auth_OpenID_registered_aliases[$mystery_alias];
        }
        return null;
    }

    function setOpenIDNamespace($openid_ns_uri=null)
    {
        if ($openid_ns_uri === null) {
            $openid_ns_uri = Auth_OpenID_OPENID1_NS;
            $implicit = true;
        } else {
            $implicit = false;
        }

        if (!in_array($openid_ns_uri, $this->allowed_openid_namespaces)) {
            // raise ValueError('Invalid null namespace: %r' % (openid_ns_uri,))
            return false;
        }

        $this->namespaces->addAlias($openid_ns_uri,
                                    Auth_OpenID_NULL_NAMESPACE,
                                    $implicit);
        $this->_openid_ns_uri = $openid_ns_uri;
    }

    function getOpenIDNamespace()
    {
        return $this->_openid_ns_uri;
    }

    function fromKVForm($kvform_string)
    {
        // Create a Message from a KVForm string
        return Auth_OpenID_Message::fromOpenIDArgs(
                     Auth_OpenID_KVForm::toArray($kvform_string));
    }

    function copy()
    {
        return $this;
    }

    function toPostArgs()
    {
        // Return all arguments with openid. in front of namespaced
        // arguments.

        $args = array();

        // Add namespace definitions to the output
        foreach ($this->namespaces->iteritems() as $pair) {
            list($ns_uri, $alias) = $pair;
            if ($this->namespaces->isImplicit($ns_uri)) {
                continue;
            }
            if ($alias == Auth_OpenID_NULL_NAMESPACE) {
                $ns_key = 'openid.ns';
            } else {
                $ns_key = 'openid.ns.' . $alias;
            }
            $args[$ns_key] = $ns_uri;
        }

        foreach ($this->args->items() as $pair) {
            list($ns_parts, $value) = $pair;
            list($ns_uri, $ns_key) = $ns_parts;
            $key = $this->getKey($ns_uri, $ns_key);
            $args[$key] = $value;
        }

        return $args;
    }

    function toArgs()
    {
        // Return all namespaced arguments, failing if any
        // non-namespaced arguments exist.
        $post_args = $this->toPostArgs();
        $kvargs = array();
        foreach ($post_args as $k => $v) {
            if (strpos($k, 'openid.') !== 0) {
                // raise ValueError(
                //   'This message can only be encoded as a POST, because it '
                //   'contains arguments that are not prefixed with "openid."')
                return null;
            } else {
                $kvargs[substr($k, 7)] = $v;
            }
        }

        return $kvargs;
    }

    function toFormMarkup($action_url, $form_tag_attrs = null,
                          $submit_text = "Continue")
    {
        $form = "<form accept-charset=\"UTF-8\" ".
            "enctype=\"application/x-www-form-urlencoded\"";

        if (!$form_tag_attrs) {
            $form_tag_attrs = array();
        }

        $form_tag_attrs['action'] = $action_url;
        $form_tag_attrs['method'] = 'post';

        unset($form_tag_attrs['enctype']);
        unset($form_tag_attrs['accept-charset']);

        if ($form_tag_attrs) {
            foreach ($form_tag_attrs as $name => $attr) {
                $form .= sprintf(" %s=\"%s\"", $name, $attr);
            }
        }

        $form .= ">\n";

        foreach ($this->toPostArgs() as $name => $value) {
            $form .= sprintf(
                        "<input type=\"hidden\" name=\"%s\" value=\"%s\" />\n",
                        $name, $value);
        }

        $form .= sprintf("<input type=\"submit\" value=\"%s\" />\n",
                         $submit_text);

        $form .= "</form>\n";

        return $form;
    }

    function toURL($base_url)
    {
        // Generate a GET URL with the parameters in this message
        // attached as query parameters.
        return Auth_OpenID::appendArgs($base_url, $this->toPostArgs());
    }

    function toKVForm()
    {
        // Generate a KVForm string that contains the parameters in
        // this message. This will fail if the message contains
        // arguments outside of the 'openid.' prefix.
        return Auth_OpenID_KVForm::fromArray($this->toArgs());
    }

    function toURLEncoded()
    {
        // Generate an x-www-urlencoded string
        $args = array();

        foreach ($this->toPostArgs() as $k => $v) {
            $args[] = array($k, $v);
        }

        sort($args);
        return Auth_OpenID::httpBuildQuery($args);
    }

    /**
     * @access private
     */
    function _fixNS($namespace)
    {
        // Convert an input value into the internally used values of
        // this object

        if ($namespace == Auth_OpenID_OPENID_NS) {
            if ($this->_openid_ns_uri === null) {
                return new Auth_OpenID_FailureResponse(null,
                    'OpenID namespace not set');
            } else {
                $namespace = $this->_openid_ns_uri;
            }
        }

        if (($namespace != Auth_OpenID_BARE_NS) &&
              (!is_string($namespace))) {
            //TypeError
            $err_msg = sprintf("Namespace must be Auth_OpenID_BARE_NS, ".
                              "Auth_OpenID_OPENID_NS or a string. got %s",
                              print_r($namespace, true));
            return new Auth_OpenID_FailureResponse(null, $err_msg);
        }

        if (($namespace != Auth_OpenID_BARE_NS) &&
            (strpos($namespace, ':') === false)) {
            // fmt = 'OpenID 2.0 namespace identifiers SHOULD be URIs. Got %r'
            // warnings.warn(fmt % (namespace,), DeprecationWarning)

            if ($namespace == 'sreg') {
                // fmt = 'Using %r instead of "sreg" as namespace'
                // warnings.warn(fmt % (SREG_URI,), DeprecationWarning,)
                return Auth_OpenID_SREG_URI;
            }
        }

        return $namespace;
    }

    function hasKey($namespace, $ns_key)
    {
        $namespace = $this->_fixNS($namespace);
        if (Auth_OpenID::isFailure($namespace)) {
            // XXX log me
            return false;
        } else {
            return $this->args->contains(array($namespace, $ns_key));
        }
    }

    function getKey($namespace, $ns_key)
    {
        // Get the key for a particular namespaced argument
        $namespace = $this->_fixNS($namespace);
        if (Auth_OpenID::isFailure($namespace)) {
            return $namespace;
        }
        if ($namespace == Auth_OpenID_BARE_NS) {
            return $ns_key;
        }

        $ns_alias = $this->namespaces->getAlias($namespace);

        // No alias is defined, so no key can exist
        if ($ns_alias === null) {
            return null;
        }

        if ($ns_alias == Auth_OpenID_NULL_NAMESPACE) {
            $tail = $ns_key;
        } else {
            $tail = sprintf('%s.%s', $ns_alias, $ns_key);
        }

        return 'openid.' . $tail;
    }

    function getArg($namespace, $key, $default = null)
    {
        // Get a value for a namespaced key.
        $namespace = $this->_fixNS($namespace);

        if (Auth_OpenID::isFailure($namespace)) {
            return $namespace;
        } else {
            if ((!$this->args->contains(array($namespace, $key))) &&
              ($default == Auth_OpenID_NO_DEFAULT)) {
                $err_msg = sprintf("Namespace %s missing required field %s",
                                   $namespace, $key);
                return new Auth_OpenID_FailureResponse(null, $err_msg);
            } else {
                return $this->args->get(array($namespace, $key), $default);
            }
        }
    }

    function getArgs($namespace)
    {
        // Get the arguments that are defined for this namespace URI

        $namespace = $this->_fixNS($namespace);
        if (Auth_OpenID::isFailure($namespace)) {
            return $namespace;
        } else {
            $stuff = array();
            foreach ($this->args->items() as $pair) {
                list($key, $value) = $pair;
                list($pair_ns, $ns_key) = $key;
                if ($pair_ns == $namespace) {
                    $stuff[$ns_key] = $value;
                }
            }

            return $stuff;
        }
    }

    function updateArgs($namespace, $updates)
    {
        // Set multiple key/value pairs in one call

        $namespace = $this->_fixNS($namespace);

        if (Auth_OpenID::isFailure($namespace)) {
            return $namespace;
        } else {
            foreach ($updates as $k => $v) {
                $this->setArg($namespace, $k, $v);
            }
            return true;
        }
    }

    function setArg($namespace, $key, $value)
    {
        // Set a single argument in this namespace
        $namespace = $this->_fixNS($namespace);

        if (Auth_OpenID::isFailure($namespace)) {
            return $namespace;
        } else {
            $this->args->set(array($namespace, $key), $value);
            if ($namespace !== Auth_OpenID_BARE_NS) {
                $this->namespaces->add($namespace);
            }
            return true;
        }
    }

    function delArg($namespace, $key)
    {
        $namespace = $this->_fixNS($namespace);

        if (Auth_OpenID::isFailure($namespace)) {
            return $namespace;
        } else {
            return $this->args->del(array($namespace, $key));
        }
    }

    function getAliasedArg($aliased_key, $default = null)
    {
        $parts = explode('.', $aliased_key, 2);

        if (count($parts) != 2) {
            $ns = null;
        } else {
            list($alias, $key) = $parts;

            if ($alias == 'ns') {
              // Return the namespace URI for a namespace alias
              // parameter.
              return $this->namespaces->getNamespaceURI($key);
            } else {
              $ns = $this->namespaces->getNamespaceURI($alias);
            }
        }

        if ($ns === null) {
            $key = $aliased_key;
            $ns = $this->getOpenIDNamespace();
        }

        return $this->getArg($ns, $key, $default);
    }
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/Misc.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//Misc.php */ ?>
<?php

/**
 * Miscellaneous utility values and functions for OpenID and Yadis.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

function Auth_Yadis_getUCSChars()
{
    return array(
                 array(0xA0, 0xD7FF),
                 array(0xF900, 0xFDCF),
                 array(0xFDF0, 0xFFEF),
                 array(0x10000, 0x1FFFD),
                 array(0x20000, 0x2FFFD),
                 array(0x30000, 0x3FFFD),
                 array(0x40000, 0x4FFFD),
                 array(0x50000, 0x5FFFD),
                 array(0x60000, 0x6FFFD),
                 array(0x70000, 0x7FFFD),
                 array(0x80000, 0x8FFFD),
                 array(0x90000, 0x9FFFD),
                 array(0xA0000, 0xAFFFD),
                 array(0xB0000, 0xBFFFD),
                 array(0xC0000, 0xCFFFD),
                 array(0xD0000, 0xDFFFD),
                 array(0xE1000, 0xEFFFD)
                 );
}

function Auth_Yadis_getIPrivateChars()
{
    return array(
                 array(0xE000, 0xF8FF),
                 array(0xF0000, 0xFFFFD),
                 array(0x100000, 0x10FFFD)
                 );
}

function Auth_Yadis_pct_escape_unicode($char_match)
{
    $c = $char_match[0];
    $result = "";
    for ($i = 0; $i < strlen($c); $i++) {
        $result .= "%".sprintf("%X", ord($c[$i]));
    }
    return $result;
}

function Auth_Yadis_startswith($s, $stuff)
{
    return strpos($s, $stuff) === 0;
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/Nonce.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//Nonce.php */ ?>
<?php

/**
 * Nonce-related functionality.
 *
 * @package OpenID
 */

/**
 * Need CryptUtil to generate random strings.
 */
//require_once 'Auth/OpenID/CryptUtil.php';

/**
 * This is the characters that the nonces are made from.
 */
define('Auth_OpenID_Nonce_CHRS',"abcdefghijklmnopqrstuvwxyz" .
       "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789");

// Keep nonces for five hours (allow five hours for the combination of
// request time and clock skew). This is probably way more than is
// necessary, but there is not much overhead in storing nonces.
global $Auth_OpenID_SKEW;
$Auth_OpenID_SKEW = 60 * 60 * 5;

define('Auth_OpenID_Nonce_REGEX',
       '/(\d{4})-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d)Z(.*)/');

define('Auth_OpenID_Nonce_TIME_FMT',
       '%Y-%m-%dT%H:%M:%SZ');

function Auth_OpenID_splitNonce($nonce_string)
{
    // Extract a timestamp from the given nonce string
    $result = preg_match(Auth_OpenID_Nonce_REGEX, $nonce_string, $matches);
    if ($result != 1 || count($matches) != 8) {
        return null;
    }

    list($unused,
         $tm_year,
         $tm_mon,
         $tm_mday,
         $tm_hour,
         $tm_min,
         $tm_sec,
         $uniquifier) = $matches;

    $timestamp =
        @gmmktime($tm_hour, $tm_min, $tm_sec, $tm_mon, $tm_mday, $tm_year);

    if ($timestamp === false || $timestamp < 0) {
        return null;
    }

    return array($timestamp, $uniquifier);
}

function Auth_OpenID_checkTimestamp($nonce_string,
                                    $allowed_skew = null,
                                    $now = null)
{
    // Is the timestamp that is part of the specified nonce string
    // within the allowed clock-skew of the current time?
    global $Auth_OpenID_SKEW;

    if ($allowed_skew === null) {
        $allowed_skew = $Auth_OpenID_SKEW;
    }

    $parts = Auth_OpenID_splitNonce($nonce_string);
    if ($parts == null) {
        return false;
    }

    if ($now === null) {
        $now = time();
    }

    $stamp = $parts[0];

    // Time after which we should not use the nonce
    $past = $now - $allowed_skew;

    // Time that is too far in the future for us to allow
    $future = $now + $allowed_skew;

    // the stamp is not too far in the future and is not too far
    // in the past
    return (($past <= $stamp) && ($stamp <= $future));
}

function Auth_OpenID_mkNonce($when = null)
{
    // Generate a nonce with the current timestamp
    $salt = Auth_OpenID_CryptUtil::randomString(
        6, Auth_OpenID_Nonce_CHRS);
    if ($when === null) {
        // It's safe to call time() with no arguments; it returns a
        // GMT unix timestamp on PHP 4 and PHP 5.  gmmktime() with no
        // args returns a local unix timestamp on PHP 4, so don't use
        // that.
        $when = time();
    }
    $time_str = gmstrftime(Auth_OpenID_Nonce_TIME_FMT, $when);
    return $time_str . $salt;
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/Parse.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//Parse.php */ ?>
<?php

/**
 * This module implements a VERY limited parser that finds <link> tags
 * in the head of HTML or XHTML documents and parses out their
 * attributes according to the OpenID spec. It is a liberal parser,
 * but it requires these things from the data in order to work:
 *
 * - There must be an open <html> tag
 *
 * - There must be an open <head> tag inside of the <html> tag
 *
 * - Only <link>s that are found inside of the <head> tag are parsed
 *   (this is by design)
 *
 * - The parser follows the OpenID specification in resolving the
 *   attributes of the link tags. This means that the attributes DO
 *   NOT get resolved as they would by an XML or HTML parser. In
 *   particular, only certain entities get replaced, and href
 *   attributes do not get resolved relative to a base URL.
 *
 * From http://openid.net/specs.bml:
 *
 * - The openid.server URL MUST be an absolute URL. OpenID consumers
 *   MUST NOT attempt to resolve relative URLs.
 *
 * - The openid.server URL MUST NOT include entities other than &amp;,
 *   &lt;, &gt;, and &quot;.
 *
 * The parser ignores SGML comments and <![CDATA[blocks]]>. Both kinds
 * of quoting are allowed for attributes.
 *
 * The parser deals with invalid markup in these ways:
 *
 * - Tag names are not case-sensitive
 *
 * - The <html> tag is accepted even when it is not at the top level
 *
 * - The <head> tag is accepted even when it is not a direct child of
 *   the <html> tag, but a <html> tag must be an ancestor of the
 *   <head> tag
 *
 * - <link> tags are accepted even when they are not direct children
 *   of the <head> tag, but a <head> tag must be an ancestor of the
 *   <link> tag
 *
 * - If there is no closing tag for an open <html> or <head> tag, the
 *   remainder of the document is viewed as being inside of the
 *   tag. If there is no closing tag for a <link> tag, the link tag is
 *   treated as a short tag. Exceptions to this rule are that <html>
 *   closes <html> and <body> or <head> closes <head>
 *
 * - Attributes of the <link> tag are not required to be quoted.
 *
 * - In the case of duplicated attribute names, the attribute coming
 *   last in the tag will be the value returned.
 *
 * - Any text that does not parse as an attribute within a link tag
 *   will be ignored. (e.g. <link pumpkin rel='openid.server' /> will
 *   ignore pumpkin)
 *
 * - If there are more than one <html> or <head> tag, the parser only
 *   looks inside of the first one.
 *
 * - The contents of <script> tags are ignored entirely, except
 *   unclosed <script> tags. Unclosed <script> tags are ignored.
 *
 * - Any other invalid markup is ignored, including unclosed SGML
 *   comments and unclosed <![CDATA[blocks.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @access private
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Require Auth_OpenID::arrayGet().
 */
//require_once "Auth/OpenID.php";

class Auth_OpenID_Parse {

    /**
     * Specify some flags for use with regex matching.
     */
    var $_re_flags = "si";

    /**
     * Stuff to remove before we start looking for tags
     */
    var $_removed_re =
           "<!--.*?-->|<!\[CDATA\[.*?\]\]>|<script\b(?!:)[^>]*>.*?<\/script>";

    /**
     * Starts with the tag name at a word boundary, where the tag name
     * is not a namespace
     */
    var $_tag_expr = "<%s\b(?!:)([^>]*?)(?:\/>|>(.*?)(?:<\/?%s\s*>|\Z))";

    var $_attr_find = '\b(\w+)=("[^"]*"|\'[^\']*\'|[^\'"\s\/<>]+)';

    var $_open_tag_expr = "<%s\b";
    var $_close_tag_expr = "<((\/%s\b)|(%s[^>\/]*\/))>";

    function Auth_OpenID_Parse()
    {
        $this->_link_find = sprintf("/<link\b(?!:)([^>]*)(?!<)>/%s",
                                    $this->_re_flags);

        $this->_entity_replacements = array(
                                            'amp' => '&',
                                            'lt' => '<',
                                            'gt' => '>',
                                            'quot' => '"'
                                            );

        $this->_attr_find = sprintf("/%s/%s",
                                    $this->_attr_find,
                                    $this->_re_flags);

        $this->_removed_re = sprintf("/%s/%s",
                                     $this->_removed_re,
                                     $this->_re_flags);

        $this->_ent_replace =
            sprintf("&(%s);", implode("|",
                                      $this->_entity_replacements));
    }

    /**
     * Returns a regular expression that will match a given tag in an
     * SGML string.
     */
    function tagMatcher($tag_name, $close_tags = null)
    {
        $expr = $this->_tag_expr;

        if ($close_tags) {
            $options = implode("|", array_merge(array($tag_name), $close_tags));
            $closer = sprintf("(?:%s)", $options);
        } else {
            $closer = $tag_name;
        }

        $expr = sprintf($expr, $tag_name, $closer);
        return sprintf("/%s/%s", $expr, $this->_re_flags);
    }

    function openTag($tag_name)
    {
        $expr = sprintf($this->_open_tag_expr, $tag_name);
        return sprintf("/%s/%s", $expr, $this->_re_flags);
    }

    function closeTag($tag_name)
    {
        $expr = sprintf($this->_close_tag_expr, $tag_name, $tag_name);
        return sprintf("/%s/%s", $expr, $this->_re_flags);
    }

    function htmlBegin($s)
    {
        $matches = array();
        $result = preg_match($this->openTag('html'), $s,
                             $matches, PREG_OFFSET_CAPTURE);
        if ($result === false || !$matches) {
            return false;
        }
        // Return the offset of the first match.
        return $matches[0][1];
    }

    function htmlEnd($s)
    {
        $matches = array();
        $result = preg_match($this->closeTag('html'), $s,
                             $matches, PREG_OFFSET_CAPTURE);
        if ($result === false || !$matches) {
            return false;
        }
        // Return the offset of the first match.
        return $matches[count($matches) - 1][1];
    }

    function headFind()
    {
        return $this->tagMatcher('head', array('body', 'html'));
    }

    function replaceEntities($str)
    {
        foreach ($this->_entity_replacements as $old => $new) {
            $str = preg_replace(sprintf("/&%s;/", $old), $new, $str);
        }
        return $str;
    }

    function removeQuotes($str)
    {
        $matches = array();
        $double = '/^"(.*)"$/';
        $single = "/^\'(.*)\'$/";

        if (preg_match($double, $str, $matches)) {
            return $matches[1];
        } else if (preg_match($single, $str, $matches)) {
            return $matches[1];
        } else {
            return $str;
        }
    }

    /**
     * Find all link tags in a string representing a HTML document and
     * return a list of their attributes.
     *
     * @param string $html The text to parse
     * @return array $list An array of arrays of attributes, one for each
     * link tag
     */
    function parseLinkAttrs($html)
    {
        $stripped = preg_replace($this->_removed_re,
                                 "",
                                 $html);

        $html_begin = $this->htmlBegin($stripped);
        $html_end = $this->htmlEnd($stripped);

        if ($html_begin === false) {
            return array();
        }

        if ($html_end === false) {
            $html_end = strlen($stripped);
        }

        $stripped = substr($stripped, $html_begin,
                           $html_end - $html_begin);

        // Try to find the <HEAD> tag.
        $head_re = $this->headFind();
        $head_matches = array();
        if (!preg_match($head_re, $stripped, $head_matches)) {
            return array();
        }

        $link_data = array();
        $link_matches = array();

        if (!preg_match_all($this->_link_find, $head_matches[0],
                            $link_matches)) {
            return array();
        }

        foreach ($link_matches[0] as $link) {
            $attr_matches = array();
            preg_match_all($this->_attr_find, $link, $attr_matches);
            $link_attrs = array();
            foreach ($attr_matches[0] as $index => $full_match) {
                $name = $attr_matches[1][$index];
                $value = $this->replaceEntities(
                              $this->removeQuotes($attr_matches[2][$index]));

                $link_attrs[strtolower($name)] = $value;
            }
            $link_data[] = $link_attrs;
        }

        return $link_data;
    }

    function relMatches($rel_attr, $target_rel)
    {
        // Does this target_rel appear in the rel_str?
        // XXX: TESTME
        $rels = preg_split("/\s+/", trim($rel_attr));
        foreach ($rels as $rel) {
            $rel = strtolower($rel);
            if ($rel == $target_rel) {
                return 1;
            }
        }

        return 0;
    }

    function linkHasRel($link_attrs, $target_rel)
    {
        // Does this link have target_rel as a relationship?
        // XXX: TESTME
        $rel_attr = Auth_OpeniD::arrayGet($link_attrs, 'rel', null);
        return ($rel_attr && $this->relMatches($rel_attr,
                                               $target_rel));
    }

    function findLinksRel($link_attrs_list, $target_rel)
    {
        // Filter the list of link attributes on whether it has
        // target_rel as a relationship.
        // XXX: TESTME
        $result = array();
        foreach ($link_attrs_list as $attr) {
            if ($this->linkHasRel($attr, $target_rel)) {
                $result[] = $attr;
            }
        }

        return $result;
    }

    function findFirstHref($link_attrs_list, $target_rel)
    {
        // Return the value of the href attribute for the first link
        // tag in the list that has target_rel as a relationship.
        // XXX: TESTME
        $matches = $this->findLinksRel($link_attrs_list,
                                       $target_rel);
        if (!$matches) {
            return null;
        }
        $first = $matches[0];
        return Auth_OpenID::arrayGet($first, 'href', null);
    }
}

function Auth_OpenID_legacy_discover($html_text, $server_rel,
                                     $delegate_rel)
{
    $p = new Auth_OpenID_Parse();

    $link_attrs = $p->parseLinkAttrs($html_text);

    $server_url = $p->findFirstHref($link_attrs,
                                    $server_rel);

    if ($server_url === null) {
        return false;
    } else {
        $delegate_url = $p->findFirstHref($link_attrs,
                                          $delegate_rel);
        return array($delegate_url, $server_url);
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/ParseHTML.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//ParseHTML.php */ ?>
<?php

/**
 * This is the HTML pseudo-parser for the Yadis library.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * This class is responsible for scanning an HTML string to find META
 * tags and their attributes.  This is used by the Yadis discovery
 * process.  This class must be instantiated to be used.
 *
 * @package OpenID
 */
class Auth_Yadis_ParseHTML {

    /**
     * @access private
     */
    var $_re_flags = "si";

    /**
     * @access private
     */
    var $_removed_re =
           "<!--.*?-->|<!\[CDATA\[.*?\]\]>|<script\b(?!:)[^>]*>.*?<\/script>";

    /**
     * @access private
     */
    var $_tag_expr = "<%s%s(?:\s.*?)?%s>";

    /**
     * @access private
     */
    var $_attr_find = '\b([-\w]+)=(".*?"|\'.*?\'|.+?)[\/\s>]';

    function Auth_Yadis_ParseHTML()
    {
        $this->_attr_find = sprintf("/%s/%s",
                                    $this->_attr_find,
                                    $this->_re_flags);

        $this->_removed_re = sprintf("/%s/%s",
                                     $this->_removed_re,
                                     $this->_re_flags);

        $this->_entity_replacements = array(
                                            'amp' => '&',
                                            'lt' => '<',
                                            'gt' => '>',
                                            'quot' => '"'
                                            );

        $this->_ent_replace =
            sprintf("&(%s);", implode("|",
                                      $this->_entity_replacements));
    }

    /**
     * Replace HTML entities (amp, lt, gt, and quot) as well as
     * numeric entities (e.g. #x9f;) with their actual values and
     * return the new string.
     *
     * @access private
     * @param string $str The string in which to look for entities
     * @return string $new_str The new string entities decoded
     */
    function replaceEntities($str)
    {
        foreach ($this->_entity_replacements as $old => $new) {
            $str = preg_replace(sprintf("/&%s;/", $old), $new, $str);
        }

        // Replace numeric entities because html_entity_decode doesn't
        // do it for us.
        $str = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $str);
        $str = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $str);

        return $str;
    }

    /**
     * Strip single and double quotes off of a string, if they are
     * present.
     *
     * @access private
     * @param string $str The original string
     * @return string $new_str The new string with leading and
     * trailing quotes removed
     */
    function removeQuotes($str)
    {
        $matches = array();
        $double = '/^"(.*)"$/';
        $single = "/^\'(.*)\'$/";

        if (preg_match($double, $str, $matches)) {
            return $matches[1];
        } else if (preg_match($single, $str, $matches)) {
            return $matches[1];
        } else {
            return $str;
        }
    }

    /**
     * Create a regular expression that will match an opening
     * or closing tag from a set of names.
     *
     * @access private
     * @param mixed $tag_names Tag names to match
     * @param mixed $close false/0 = no, true/1 = yes, other = maybe
     * @param mixed $self_close false/0 = no, true/1 = yes, other = maybe
     * @return string $regex A regular expression string to be used
     * in, say, preg_match.
     */
    function tagPattern($tag_names, $close, $self_close)
    {
        if (is_array($tag_names)) {
            $tag_names = '(?:'.implode('|',$tag_names).')';
        }
        if ($close) {
            $close = '\/' . (($close == 1)? '' : '?');
        } else {
            $close = '';
        }
        if ($self_close) {
            $self_close = '(?:\/\s*)' . (($self_close == 1)? '' : '?');
        } else {
            $self_close = '';
        }
        $expr = sprintf($this->_tag_expr, $close, $tag_names, $self_close);

        return sprintf("/%s/%s", $expr, $this->_re_flags);
    }

    /**
     * Given an HTML document string, this finds all the META tags in
     * the document, provided they are found in the
     * <HTML><HEAD>...</HEAD> section of the document.  The <HTML> tag
     * may be missing.
     *
     * @access private
     * @param string $html_string An HTMl document string
     * @return array $tag_list Array of tags; each tag is an array of
     * attribute -> value.
     */
    function getMetaTags($html_string)
    {
        $html_string = preg_replace($this->_removed_re,
                                    "",
                                    $html_string);

        $key_tags = array($this->tagPattern('html', false, false),
                          $this->tagPattern('head', false, false),
                          $this->tagPattern('head', true, false),
                          $this->tagPattern('html', true, false),
                          $this->tagPattern(array(
                          'body', 'frameset', 'frame', 'p', 'div',
                          'table','span','a'), 'maybe', 'maybe'));
        $key_tags_pos = array();
        foreach ($key_tags as $pat) {
            $matches = array();
            preg_match($pat, $html_string, $matches, PREG_OFFSET_CAPTURE);
            if($matches) {
                $key_tags_pos[] = $matches[0][1];
            } else {
                $key_tags_pos[] = null;
            }
        }
        // no opening head tag
        if (is_null($key_tags_pos[1])) {
            return array();
        }
        // the effective </head> is the min of the following
        if (is_null($key_tags_pos[2])) {
            $key_tags_pos[2] = strlen($html_string);
        }
        foreach (array($key_tags_pos[3], $key_tags_pos[4]) as $pos) {
            if (!is_null($pos) && $pos < $key_tags_pos[2]) {
                $key_tags_pos[2] = $pos;
            }
        }
        // closing head tag comes before opening head tag
        if ($key_tags_pos[1] > $key_tags_pos[2]) {
            return array();
        }
        // if there is an opening html tag, make sure the opening head tag
        // comes after it
        if (!is_null($key_tags_pos[0]) && $key_tags_pos[1] < $key_tags_pos[0]) {
            return array();
        }
        $html_string = substr($html_string, $key_tags_pos[1],
                              ($key_tags_pos[2]-$key_tags_pos[1]));

        $link_data = array();
        $link_matches = array();

        if (!preg_match_all($this->tagPattern('meta', false, 'maybe'),
                            $html_string, $link_matches)) {
            return array();
        }

        foreach ($link_matches[0] as $link) {
            $attr_matches = array();
            preg_match_all($this->_attr_find, $link, $attr_matches);
            $link_attrs = array();
            foreach ($attr_matches[0] as $index => $full_match) {
                $name = $attr_matches[1][$index];
                $value = $this->replaceEntities(
                              $this->removeQuotes($attr_matches[2][$index]));

                $link_attrs[strtolower($name)] = $value;
            }
            $link_data[] = $link_attrs;
        }

        return $link_data;
    }

    /**
     * Looks for a META tag with an "http-equiv" attribute whose value
     * is one of ("x-xrds-location", "x-yadis-location"), ignoring
     * case.  If such a META tag is found, its "content" attribute
     * value is returned.
     *
     * @param string $html_string An HTML document in string format
     * @return mixed $content The "content" attribute value of the
     * META tag, if found, or null if no such tag was found.
     */
    function getHTTPEquiv($html_string)
    {
        $meta_tags = $this->getMetaTags($html_string);

        if ($meta_tags) {
            foreach ($meta_tags as $tag) {
                if (array_key_exists('http-equiv', $tag) &&
                    (in_array(strtolower($tag['http-equiv']),
                              array('x-xrds-location', 'x-yadis-location'))) &&
                    array_key_exists('content', $tag)) {
                    return $tag['content'];
                }
            }
        }

        return null;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/ServerRequest.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//ServerRequest.php */ ?>
<?php
/**
 * OpenID Server Request
 *
 * @see Auth_OpenID_Server
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Imports
 */
//require_once "Auth/OpenID.php";

/**
 * Object that holds the state of a request to the OpenID server
 *
 * With accessor functions to get at the internal request data.
 *
 * @see Auth_OpenID_Server
 * @package OpenID
 */
class Auth_OpenID_ServerRequest {
    function Auth_OpenID_ServerRequest()
    {
        $this->mode = null;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/SReg.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//SReg.php */ ?>
<?php

/**
 * Simple registration request and response parsing and object
 * representation.
 *
 * This module contains objects representing simple registration
 * requests and responses that can be used with both OpenID relying
 * parties and OpenID providers.
 *
 * 1. The relying party creates a request object and adds it to the
 * {@link Auth_OpenID_AuthRequest} object before making the
 * checkid request to the OpenID provider:
 *
 *   $sreg_req = Auth_OpenID_SRegRequest::build(array('email'));
 *   $auth_request->addExtension($sreg_req);
 *
 * 2. The OpenID provider extracts the simple registration request
 * from the OpenID request using {@link
 * Auth_OpenID_SRegRequest::fromOpenIDRequest}, gets the user's
 * approval and data, creates an {@link Auth_OpenID_SRegResponse}
 * object and adds it to the id_res response:
 *
 *   $sreg_req = Auth_OpenID_SRegRequest::fromOpenIDRequest(
 *                                  $checkid_request);
 *   // [ get the user's approval and data, informing the user that
 *   //   the fields in sreg_response were requested ]
 *   $sreg_resp = Auth_OpenID_SRegResponse::extractResponse(
 *                                  $sreg_req, $user_data);
 *   $sreg_resp->toMessage($openid_response->fields);
 *
 * 3. The relying party uses {@link
 * Auth_OpenID_SRegResponse::fromSuccessResponse} to extract the data
 * from the OpenID response:
 *
 *   $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse(
 *                                  $success_response);
 *
 * @package OpenID
 */

/**
 * Import message and extension internals.
 */
//require_once 'Auth/OpenID/Message.php';
//require_once 'Auth/OpenID/Extension.php';

// The data fields that are listed in the sreg spec
global $Auth_OpenID_sreg_data_fields;
$Auth_OpenID_sreg_data_fields = array(
                                      'fullname' => 'Full Name',
                                      'nickname' => 'Nickname',
                                      'dob' => 'Date of Birth',
                                      'email' => 'E-mail Address',
                                      'gender' => 'Gender',
                                      'postcode' => 'Postal Code',
                                      'country' => 'Country',
                                      'language' => 'Language',
                                      'timezone' => 'Time Zone');

/**
 * Check to see that the given value is a valid simple registration
 * data field name.  Return true if so, false if not.
 */
function Auth_OpenID_checkFieldName($field_name)
{
    global $Auth_OpenID_sreg_data_fields;

    if (!in_array($field_name, array_keys($Auth_OpenID_sreg_data_fields))) {
        return false;
    }
    return true;
}

// URI used in the wild for Yadis documents advertising simple
// registration support
define('Auth_OpenID_SREG_NS_URI_1_0', 'http://openid.net/sreg/1.0');

// URI in the draft specification for simple registration 1.1
// <http://openid.net/specs/openid-simple-registration-extension-1_1-01.html>
define('Auth_OpenID_SREG_NS_URI_1_1', 'http://openid.net/extensions/sreg/1.1');

// This attribute will always hold the preferred URI to use when
// adding sreg support to an XRDS file or in an OpenID namespace
// declaration.
define('Auth_OpenID_SREG_NS_URI', Auth_OpenID_SREG_NS_URI_1_1);

Auth_OpenID_registerNamespaceAlias(Auth_OpenID_SREG_NS_URI_1_1, 'sreg');

/**
 * Does the given endpoint advertise support for simple
 * registration?
 *
 * $endpoint: The endpoint object as returned by OpenID discovery.
 * returns whether an sreg type was advertised by the endpoint
 */
function Auth_OpenID_supportsSReg(&$endpoint)
{
    return ($endpoint->usesExtension(Auth_OpenID_SREG_NS_URI_1_1) ||
            $endpoint->usesExtension(Auth_OpenID_SREG_NS_URI_1_0));
}

/**
 * A base class for classes dealing with Simple Registration protocol
 * messages.
 *
 * @package OpenID
 */
class Auth_OpenID_SRegBase extends Auth_OpenID_Extension {
    /**
     * Extract the simple registration namespace URI from the given
     * OpenID message. Handles OpenID 1 and 2, as well as both sreg
     * namespace URIs found in the wild, as well as missing namespace
     * definitions (for OpenID 1)
     *
     * $message: The OpenID message from which to parse simple
     * registration fields. This may be a request or response message.
     *
     * Returns the sreg namespace URI for the supplied message. The
     * message may be modified to define a simple registration
     * namespace.
     *
     * @access private
     */
    function _getSRegNS(&$message)
    {
        $alias = null;
        $found_ns_uri = null;

        // See if there exists an alias for one of the two defined
        // simple registration types.
        foreach (array(Auth_OpenID_SREG_NS_URI_1_1,
                       Auth_OpenID_SREG_NS_URI_1_0) as $sreg_ns_uri) {
            $alias = $message->namespaces->getAlias($sreg_ns_uri);
            if ($alias !== null) {
                $found_ns_uri = $sreg_ns_uri;
                break;
            }
        }

        if ($alias === null) {
            // There is no alias for either of the types, so try to
            // add one. We default to using the modern value (1.1)
            $found_ns_uri = Auth_OpenID_SREG_NS_URI_1_1;
            if ($message->namespaces->addAlias(Auth_OpenID_SREG_NS_URI_1_1,
                                               'sreg') === null) {
                // An alias for the string 'sreg' already exists, but
                // it's defined for something other than simple
                // registration
                return null;
            }
        }

        return $found_ns_uri;
    }
}

/**
 * An object to hold the state of a simple registration request.
 *
 * required: A list of the required fields in this simple registration
 * request
 *
 * optional: A list of the optional fields in this simple registration
 * request
 *
 * @package OpenID
 */
class Auth_OpenID_SRegRequest extends Auth_OpenID_SRegBase {

    var $ns_alias = 'sreg';

    /**
     * Initialize an empty simple registration request.
     */
    function build($required=null, $optional=null,
                   $policy_url=null,
                   $sreg_ns_uri=Auth_OpenID_SREG_NS_URI,
                   $cls='Auth_OpenID_SRegRequest')
    {
        $obj = new $cls();

        $obj->required = array();
        $obj->optional = array();
        $obj->policy_url = $policy_url;
        $obj->ns_uri = $sreg_ns_uri;

        if ($required) {
            if (!$obj->requestFields($required, true, true)) {
                return null;
            }
        }

        if ($optional) {
            if (!$obj->requestFields($optional, false, true)) {
                return null;
            }
        }

        return $obj;
    }

    /**
     * Create a simple registration request that contains the fields
     * that were requested in the OpenID request with the given
     * arguments
     *
     * $request: The OpenID authentication request from which to
     * extract an sreg request.
     *
     * $cls: name of class to use when creating sreg request object.
     * Used for testing.
     *
     * Returns the newly created simple registration request
     */
    function fromOpenIDRequest($request, $cls='Auth_OpenID_SRegRequest')
    {

        $obj = call_user_func_array(array($cls, 'build'),
                 array(null, null, null, Auth_OpenID_SREG_NS_URI, $cls));

        // Since we're going to mess with namespace URI mapping, don't
        // mutate the object that was passed in.
        $m = $request->message;

        $obj->ns_uri = $obj->_getSRegNS($m);
        $args = $m->getArgs($obj->ns_uri);

        if ($args === null || Auth_OpenID::isFailure($args)) {
            return null;
        }

        $obj->parseExtensionArgs($args);

        return $obj;
    }

    /**
     * Parse the unqualified simple registration request parameters
     * and add them to this object.
     *
     * This method is essentially the inverse of
     * getExtensionArgs. This method restores the serialized simple
     * registration request fields.
     *
     * If you are extracting arguments from a standard OpenID
     * checkid_* request, you probably want to use fromOpenIDRequest,
     * which will extract the sreg namespace and arguments from the
     * OpenID request. This method is intended for cases where the
     * OpenID server needs more control over how the arguments are
     * parsed than that method provides.
     *
     * $args == $message->getArgs($ns_uri);
     * $request->parseExtensionArgs($args);
     *
     * $args: The unqualified simple registration arguments
     *
     * strict: Whether requests with fields that are not defined in
     * the simple registration specification should be tolerated (and
     * ignored)
     */
    function parseExtensionArgs($args, $strict=false)
    {
        foreach (array('required', 'optional') as $list_name) {
            $required = ($list_name == 'required');
            $items = Auth_OpenID::arrayGet($args, $list_name);
            if ($items) {
                foreach (explode(',', $items) as $field_name) {
                    if (!$this->requestField($field_name, $required, $strict)) {
                        if ($strict) {
                            return false;
                        }
                    }
                }
            }
        }

        $this->policy_url = Auth_OpenID::arrayGet($args, 'policy_url');

        return true;
    }

    /**
     * A list of all of the simple registration fields that were
     * requested, whether they were required or optional.
     */
    function allRequestedFields()
    {
        return array_merge($this->required, $this->optional);
    }

    /**
     * Have any simple registration fields been requested?
     */
    function wereFieldsRequested()
    {
        return count($this->allRequestedFields());
    }

    /**
     * Was this field in the request?
     */
    function contains($field_name)
    {
        return (in_array($field_name, $this->required) ||
                in_array($field_name, $this->optional));
    }

    /**
     * Request the specified field from the OpenID user
     *
     * $field_name: the unqualified simple registration field name
     *
     * required: whether the given field should be presented to the
     * user as being a required to successfully complete the request
     *
     * strict: whether to raise an exception when a field is added to
     * a request more than once
     */
    function requestField($field_name,
                          $required=false, $strict=false)
    {
        if (!Auth_OpenID_checkFieldName($field_name)) {
            return false;
        }

        if ($strict) {
            if ($this->contains($field_name)) {
                return false;
            }
        } else {
            if (in_array($field_name, $this->required)) {
                return true;
            }

            if (in_array($field_name, $this->optional)) {
                if ($required) {
                    unset($this->optional[array_search($field_name,
                                                       $this->optional)]);
                } else {
                    return true;
                }
            }
        }

        if ($required) {
            $this->required[] = $field_name;
        } else {
            $this->optional[] = $field_name;
        }

        return true;
    }

    /**
     * Add the given list of fields to the request
     *
     * field_names: The simple registration data fields to request
     *
     * required: Whether these values should be presented to the user
     * as required
     *
     * strict: whether to raise an exception when a field is added to
     * a request more than once
     */
    function requestFields($field_names, $required=false, $strict=false)
    {
        if (!is_array($field_names)) {
            return false;
        }

        foreach ($field_names as $field_name) {
            if (!$this->requestField($field_name, $required, $strict=$strict)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get a dictionary of unqualified simple registration arguments
     * representing this request.
     *
     * This method is essentially the inverse of
     * C{L{parseExtensionArgs}}. This method serializes the simple
     * registration request fields.
     */
    function getExtensionArgs()
    {
        $args = array();

        if ($this->required) {
            $args['required'] = implode(',', $this->required);
        }

        if ($this->optional) {
            $args['optional'] = implode(',', $this->optional);
        }

        if ($this->policy_url) {
            $args['policy_url'] = $this->policy_url;
        }

        return $args;
    }
}

/**
 * Represents the data returned in a simple registration response
 * inside of an OpenID C{id_res} response. This object will be created
 * by the OpenID server, added to the C{id_res} response object, and
 * then extracted from the C{id_res} message by the Consumer.
 *
 * @package OpenID
 */
class Auth_OpenID_SRegResponse extends Auth_OpenID_SRegBase {

    var $ns_alias = 'sreg';

    function Auth_OpenID_SRegResponse($data=null,
                                      $sreg_ns_uri=Auth_OpenID_SREG_NS_URI)
    {
        if ($data === null) {
            $this->data = array();
        } else {
            $this->data = $data;
        }

        $this->ns_uri = $sreg_ns_uri;
    }

    /**
     * Take a C{L{SRegRequest}} and a dictionary of simple
     * registration values and create a C{L{SRegResponse}} object
     * containing that data.
     *
     * request: The simple registration request object
     *
     * data: The simple registration data for this response, as a
     * dictionary from unqualified simple registration field name to
     * string (unicode) value. For instance, the nickname should be
     * stored under the key 'nickname'.
     */
    function extractResponse($request, $data)
    {
        $obj = new Auth_OpenID_SRegResponse();
        $obj->ns_uri = $request->ns_uri;

        foreach ($request->allRequestedFields() as $field) {
            $value = Auth_OpenID::arrayGet($data, $field);
            if ($value !== null) {
                $obj->data[$field] = $value;
            }
        }

        return $obj;
    }

    /**
     * Create a C{L{SRegResponse}} object from a successful OpenID
     * library response
     * (C{L{openid.consumer.consumer.SuccessResponse}}) response
     * message
     *
     * success_response: A SuccessResponse from consumer.complete()
     *
     * signed_only: Whether to process only data that was
     * signed in the id_res message from the server.
     *
     * Returns a simple registration response containing the data that
     * was supplied with the C{id_res} response.
     */
    function fromSuccessResponse(&$success_response, $signed_only=true)
    {
        global $Auth_OpenID_sreg_data_fields;

        $obj = new Auth_OpenID_SRegResponse();
        $obj->ns_uri = $obj->_getSRegNS($success_response->message);

        if ($signed_only) {
            $args = $success_response->getSignedNS($obj->ns_uri);
        } else {
            $args = $success_response->message->getArgs($obj->ns_uri);
        }

        if ($args === null || Auth_OpenID::isFailure($args)) {
            return null;
        }

        foreach ($Auth_OpenID_sreg_data_fields as $field_name => $desc) {
            if (in_array($field_name, array_keys($args))) {
                $obj->data[$field_name] = $args[$field_name];
            }
        }

        return $obj;
    }

    function getExtensionArgs()
    {
        return $this->data;
    }

    // Read-only dictionary interface
    function get($field_name, $default=null)
    {
        if (!Auth_OpenID_checkFieldName($field_name)) {
            return null;
        }

        return Auth_OpenID::arrayGet($this->data, $field_name, $default);
    }

    function contents()
    {
        return $this->data;
    }
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/URINorm.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//URINorm.php */ ?>
<?php

/**
 * URI normalization routines.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

//require_once 'Auth/Yadis/Misc.php';

// from appendix B of rfc 3986 (http://www.ietf.org/rfc/rfc3986.txt)
function Auth_OpenID_getURIPattern()
{
    return '&^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?&';
}

function Auth_OpenID_getAuthorityPattern()
{
    return '/^([^@]*@)?([^:]*)(:.*)?/';
}

function Auth_OpenID_getEncodedPattern()
{
    return '/%([0-9A-Fa-f]{2})/';
}

function Auth_OpenID_getUnreserved()
{
    $_unreserved = array();
    for ($i = 0; $i < 256; $i++) {
        $_unreserved[$i] = false;
    }

    for ($i = ord('A'); $i <= ord('Z'); $i++) {
        $_unreserved[$i] = true;
    }

    for ($i = ord('0'); $i <= ord('9'); $i++) {
        $_unreserved[$i] = true;
    }

    for ($i = ord('a'); $i <= ord('z'); $i++) {
        $_unreserved[$i] = true;
    }

    $_unreserved[ord('-')] = true;
    $_unreserved[ord('.')] = true;
    $_unreserved[ord('_')] = true;
    $_unreserved[ord('~')] = true;

    return $_unreserved;
}

function Auth_OpenID_getEscapeRE()
{
    $parts = array();
    foreach (array_merge(Auth_Yadis_getUCSChars(),
                         Auth_Yadis_getIPrivateChars()) as $pair) {
        list($m, $n) = $pair;
        $parts[] = sprintf("%s-%s", chr($m), chr($n));
    }

    return sprintf('[%s]', implode('', $parts));
}

function Auth_OpenID_pct_encoded_replace_unreserved($mo)
{
    $_unreserved = Auth_OpenID_getUnreserved();

    $i = intval($mo[1], 16);
    if ($_unreserved[$i]) {
        return chr($i);
    } else {
        return strtoupper($mo[0]);
    }

    return $mo[0];
}

function Auth_OpenID_pct_encoded_replace($mo)
{
    return chr(intval($mo[1], 16));
}

function Auth_OpenID_remove_dot_segments($path)
{
    $result_segments = array();

    while ($path) {
        if (Auth_Yadis_startswith($path, '../')) {
            $path = substr($path, 3);
        } else if (Auth_Yadis_startswith($path, './')) {
            $path = substr($path, 2);
        } else if (Auth_Yadis_startswith($path, '/./')) {
            $path = substr($path, 2);
        } else if ($path == '/.') {
            $path = '/';
        } else if (Auth_Yadis_startswith($path, '/../')) {
            $path = substr($path, 3);
            if ($result_segments) {
                array_pop($result_segments);
            }
        } else if ($path == '/..') {
            $path = '/';
            if ($result_segments) {
                array_pop($result_segments);
            }
        } else if (($path == '..') ||
                   ($path == '.')) {
            $path = '';
        } else {
            $i = 0;
            if ($path[0] == '/') {
                $i = 1;
            }
            $i = strpos($path, '/', $i);
            if ($i === false) {
                $i = strlen($path);
            }
            $result_segments[] = substr($path, 0, $i);
            $path = substr($path, $i);
        }
    }

    return implode('', $result_segments);
}

function Auth_OpenID_urinorm($uri)
{
    $uri_matches = array();
    preg_match(Auth_OpenID_getURIPattern(), $uri, $uri_matches);

    if (count($uri_matches) < 9) {
        for ($i = count($uri_matches); $i <= 9; $i++) {
            $uri_matches[] = '';
        }
    }

    $scheme = $uri_matches[2];
    if ($scheme) {
        $scheme = strtolower($scheme);
    }

    $scheme = $uri_matches[2];
    if ($scheme === '') {
        // No scheme specified
        return null;
    }

    $scheme = strtolower($scheme);
    if (!in_array($scheme, array('http', 'https'))) {
        // Not an absolute HTTP or HTTPS URI
        return null;
    }

    $authority = $uri_matches[4];
    if ($authority === '') {
        // Not an absolute URI
        return null;
    }

    $authority_matches = array();
    preg_match(Auth_OpenID_getAuthorityPattern(),
               $authority, $authority_matches);
    if (count($authority_matches) === 0) {
        // URI does not have a valid authority
        return null;
    }

    if (count($authority_matches) < 4) {
        for ($i = count($authority_matches); $i <= 4; $i++) {
            $authority_matches[] = '';
        }
    }

    list($_whole, $userinfo, $host, $port) = $authority_matches;

    if ($userinfo === null) {
        $userinfo = '';
    }

    if (strpos($host, '%') !== -1) {
        $host = strtolower($host);
        $host = preg_replace_callback(
                  Auth_OpenID_getEncodedPattern(),
                  'Auth_OpenID_pct_encoded_replace', $host);
        // NO IDNA.
        // $host = unicode($host, 'utf-8').encode('idna');
    } else {
        $host = strtolower($host);
    }

    if ($port) {
        if (($port == ':') ||
            ($scheme == 'http' && $port == ':80') ||
            ($scheme == 'https' && $port == ':443')) {
            $port = '';
        }
    } else {
        $port = '';
    }

    $authority = $userinfo . $host . $port;

    $path = $uri_matches[5];
    $path = preg_replace_callback(
               Auth_OpenID_getEncodedPattern(),
               'Auth_OpenID_pct_encoded_replace_unreserved', $path);

    $path = Auth_OpenID_remove_dot_segments($path);
    if (!$path) {
        $path = '/';
    }

    $query = $uri_matches[6];
    if ($query === null) {
        $query = '';
    }

    $fragment = $uri_matches[8];
    if ($fragment === null) {
        $fragment = '';
    }

    return $scheme . '://' . $authority . $path . $query . $fragment;
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/XML.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//XML.php */ ?>
<?php

/**
 * XML-parsing classes to wrap the domxml and DOM extensions for PHP 4
 * and 5, respectively.
 *
 * @package OpenID
 */

/**
 * The base class for wrappers for available PHP XML-parsing
 * extensions.  To work with this Yadis library, subclasses of this
 * class MUST implement the API as defined in the remarks for this
 * class.  Subclasses of Auth_Yadis_XMLParser are used to wrap
 * particular PHP XML extensions such as 'domxml'.  These are used
 * internally by the library depending on the availability of
 * supported PHP XML extensions.
 *
 * @package OpenID
 */
class Auth_Yadis_XMLParser {
    /**
     * Initialize an instance of Auth_Yadis_XMLParser with some
     * XML and namespaces.  This SHOULD NOT be overridden by
     * subclasses.
     *
     * @param string $xml_string A string of XML to be parsed.
     * @param array $namespace_map An array of ($ns_name => $ns_uri)
     * to be registered with the XML parser.  May be empty.
     * @return boolean $result True if the initialization and
     * namespace registration(s) succeeded; false otherwise.
     */
    function init($xml_string, $namespace_map)
    {
        if (!$this->setXML($xml_string)) {
            return false;
        }

        foreach ($namespace_map as $prefix => $uri) {
            if (!$this->registerNamespace($prefix, $uri)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Register a namespace with the XML parser.  This should be
     * overridden by subclasses.
     *
     * @param string $prefix The namespace prefix to appear in XML tag
     * names.
     *
     * @param string $uri The namespace URI to be used to identify the
     * namespace in the XML.
     *
     * @return boolean $result True if the registration succeeded;
     * false otherwise.
     */
    function registerNamespace($prefix, $uri)
    {
        // Not implemented.
    }

    /**
     * Set this parser object's XML payload.  This should be
     * overridden by subclasses.
     *
     * @param string $xml_string The XML string to pass to this
     * object's XML parser.
     *
     * @return boolean $result True if the initialization succeeded;
     * false otherwise.
     */
    function setXML($xml_string)
    {
        // Not implemented.
    }

    /**
     * Evaluate an XPath expression and return the resulting node
     * list.  This should be overridden by subclasses.
     *
     * @param string $xpath The XPath expression to be evaluated.
     *
     * @param mixed $node A node object resulting from a previous
     * evalXPath call.  This node, if specified, provides the context
     * for the evaluation of this xpath expression.
     *
     * @return array $node_list An array of matching opaque node
     * objects to be used with other methods of this parser class.
     */
    function evalXPath($xpath, $node = null)
    {
        // Not implemented.
    }

    /**
     * Return the textual content of a specified node.
     *
     * @param mixed $node A node object from a previous call to
     * $this->evalXPath().
     *
     * @return string $content The content of this node.
     */
    function content($node)
    {
        // Not implemented.
    }

    /**
     * Return the attributes of a specified node.
     *
     * @param mixed $node A node object from a previous call to
     * $this->evalXPath().
     *
     * @return array $attrs An array mapping attribute names to
     * values.
     */
    function attributes($node)
    {
        // Not implemented.
    }
}

/**
 * This concrete implementation of Auth_Yadis_XMLParser implements
 * the appropriate API for the 'domxml' extension which is typically
 * packaged with PHP 4.  This class will be used whenever the 'domxml'
 * extension is detected.  See the Auth_Yadis_XMLParser class for
 * details on this class's methods.
 *
 * @package OpenID
 */
class Auth_Yadis_domxml extends Auth_Yadis_XMLParser {
    function Auth_Yadis_domxml()
    {
        $this->xml = null;
        $this->doc = null;
        $this->xpath = null;
        $this->errors = array();
    }

    function setXML($xml_string)
    {
        $this->xml = $xml_string;
        $this->doc = @domxml_open_mem($xml_string, DOMXML_LOAD_PARSING,
                                      $this->errors);

        if (!$this->doc) {
            return false;
        }

        $this->xpath = $this->doc->xpath_new_context();

        return true;
    }

    function registerNamespace($prefix, $uri)
    {
        return xpath_register_ns($this->xpath, $prefix, $uri);
    }

    function &evalXPath($xpath, $node = null)
    {
        if ($node) {
            $result = @$this->xpath->xpath_eval($xpath, $node);
        } else {
            $result = @$this->xpath->xpath_eval($xpath);
        }

        if (!$result) {
            $n = array();
            return $n;
        }

        if (!$result->nodeset) {
            $n = array();
            return $n;
        }

        return $result->nodeset;
    }

    function content($node)
    {
        if ($node) {
            return $node->get_content();
        }
    }

    function attributes($node)
    {
        if ($node) {
            $arr = $node->attributes();
            $result = array();

            if ($arr) {
                foreach ($arr as $attrnode) {
                    $result[$attrnode->name] = $attrnode->value;
                }
            }

            return $result;
        }
    }
}

/**
 * This concrete implementation of Auth_Yadis_XMLParser implements
 * the appropriate API for the 'dom' extension which is typically
 * packaged with PHP 5.  This class will be used whenever the 'dom'
 * extension is detected.  See the Auth_Yadis_XMLParser class for
 * details on this class's methods.
 *
 * @package OpenID
 */
class Auth_Yadis_dom extends Auth_Yadis_XMLParser {
    function Auth_Yadis_dom()
    {
        $this->xml = null;
        $this->doc = null;
        $this->xpath = null;
        $this->errors = array();
    }

    function setXML($xml_string)
    {
        $this->xml = $xml_string;
        $this->doc = new DOMDocument;

        if (!$this->doc) {
            return false;
        }

        if (!@$this->doc->loadXML($xml_string)) {
            return false;
        }

        $this->xpath = new DOMXPath($this->doc);

        if ($this->xpath) {
            return true;
        } else {
            return false;
        }
    }

    function registerNamespace($prefix, $uri)
    {
        return $this->xpath->registerNamespace($prefix, $uri);
    }

    function &evalXPath($xpath, $node = null)
    {
        if ($node) {
            $result = @$this->xpath->query($xpath, $node);
        } else {
            $result = @$this->xpath->query($xpath);
        }

        $n = array();

        if (!$result) {
            return $n;
        }

        for ($i = 0; $i < $result->length; $i++) {
            $n[] = $result->item($i);
        }

        return $n;
    }

    function content($node)
    {
        if ($node) {
            return $node->textContent;
        }
    }

    function attributes($node)
    {
        if ($node) {
            $arr = $node->attributes;
            $result = array();

            if ($arr) {
                for ($i = 0; $i < $arr->length; $i++) {
                    $node = $arr->item($i);
                    $result[$node->nodeName] = $node->nodeValue;
                }
            }

            return $result;
        }
    }
}

global $__Auth_Yadis_defaultParser;
$__Auth_Yadis_defaultParser = null;

/**
 * Set a default parser to override the extension-driven selection of
 * available parser classes.  This is helpful in a test environment or
 * one in which multiple parsers can be used but one is more
 * desirable.
 *
 * @param Auth_Yadis_XMLParser $parser An instance of a
 * Auth_Yadis_XMLParser subclass.
 */
function Auth_Yadis_setDefaultParser(&$parser)
{
    global $__Auth_Yadis_defaultParser;
    $__Auth_Yadis_defaultParser =& $parser;
}

function Auth_Yadis_getSupportedExtensions()
{
    return array(
                 'dom' => array('classname' => 'Auth_Yadis_dom',
                       'libname' => array('dom.so', 'dom.dll')),
                 'domxml' => array('classname' => 'Auth_Yadis_domxml',
                       'libname' => array('domxml.so', 'php_domxml.dll')),
                 );
}

/**
 * Returns an instance of a Auth_Yadis_XMLParser subclass based on
 * the availability of PHP extensions for XML parsing.  If
 * Auth_Yadis_setDefaultParser has been called, the parser used in
 * that call will be returned instead.
 */
function &Auth_Yadis_getXMLParser()
{
    global $__Auth_Yadis_defaultParser;

    if (isset($__Auth_Yadis_defaultParser)) {
        return $__Auth_Yadis_defaultParser;
    }

    $p = null;
    $classname = null;

    $extensions = Auth_Yadis_getSupportedExtensions();

    // Return a wrapper for the resident implementation, if any.
    foreach ($extensions as $name => $params) {
        if (!extension_loaded($name)) {
            foreach ($params['libname'] as $libname) {
                if (@dl($libname)) {
                    $classname = $params['classname'];
                }
            }
        } else {
            $classname = $params['classname'];
        }
        if (isset($classname)) {
            $p = new $classname();
            return $p;
        }
    }

    if (!isset($p)) {
        trigger_error('No XML parser was found', E_USER_ERROR);
    } else {
        Auth_Yadis_setDefaultParser($p);
    }

    return $p;
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/XRI.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//XRI.php */ ?>
<?php

/**
 * Routines for XRI resolution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

//require_once 'Auth/Yadis/Misc.php';
//require_once 'Auth/Yadis/Yadis.php';
//require_once 'Auth/OpenID.php';

function Auth_Yadis_getDefaultProxy()
{
    return 'http://proxy.xri.net/';
}

function Auth_Yadis_getXRIAuthorities()
{
    return array('!', '=', '@', '+', '$', '(');
}

function Auth_Yadis_getEscapeRE()
{
    $parts = array();
    foreach (array_merge(Auth_Yadis_getUCSChars(),
                         Auth_Yadis_getIPrivateChars()) as $pair) {
        list($m, $n) = $pair;
        $parts[] = sprintf("%s-%s", chr($m), chr($n));
    }

    return sprintf('/[%s]/', implode('', $parts));
}

function Auth_Yadis_getXrefRE()
{
    return '/\((.*?)\)/';
}

function Auth_Yadis_identifierScheme($identifier)
{
    if (Auth_Yadis_startswith($identifier, 'xri://') ||
        ($identifier &&
          in_array($identifier[0], Auth_Yadis_getXRIAuthorities()))) {
        return "XRI";
    } else {
        return "URI";
    }
}

function Auth_Yadis_toIRINormal($xri)
{
    if (!Auth_Yadis_startswith($xri, 'xri://')) {
        $xri = 'xri://' . $xri;
    }

    return Auth_Yadis_escapeForIRI($xri);
}

function _escape_xref($xref_match)
{
    $xref = $xref_match[0];
    $xref = str_replace('/', '%2F', $xref);
    $xref = str_replace('?', '%3F', $xref);
    $xref = str_replace('#', '%23', $xref);
    return $xref;
}

function Auth_Yadis_escapeForIRI($xri)
{
    $xri = str_replace('%', '%25', $xri);
    $xri = preg_replace_callback(Auth_Yadis_getXrefRE(),
                                 '_escape_xref', $xri);
    return $xri;
}

function Auth_Yadis_toURINormal($xri)
{
    return Auth_Yadis_iriToURI(Auth_Yadis_toIRINormal($xri));
}

function Auth_Yadis_iriToURI($iri)
{
    if (1) {
        return $iri;
    } else {
        // According to RFC 3987, section 3.1, "Mapping of IRIs to URIs"
        return preg_replace_callback(Auth_Yadis_getEscapeRE(),
                                     'Auth_Yadis_pct_escape_unicode', $iri);
    }
}


function Auth_Yadis_XRIAppendArgs($url, $args)
{
    // Append some arguments to an HTTP query.  Yes, this is just like
    // OpenID's appendArgs, but with special seasoning for XRI
    // queries.

    if (count($args) == 0) {
        return $url;
    }

    // Non-empty array; if it is an array of arrays, use multisort;
    // otherwise use sort.
    if (array_key_exists(0, $args) &&
        is_array($args[0])) {
        // Do nothing here.
    } else {
        $keys = array_keys($args);
        sort($keys);
        $new_args = array();
        foreach ($keys as $key) {
            $new_args[] = array($key, $args[$key]);
        }
        $args = $new_args;
    }

    // According to XRI Resolution section "QXRI query parameters":
    //
    // "If the original QXRI had a null query component (only a
    //  leading question mark), or a query component consisting of
    //  only question marks, one additional leading question mark MUST
    //  be added when adding any XRI resolution parameters."
    if (strpos(rtrim($url, '?'), '?') !== false) {
        $sep = '&';
    } else {
        $sep = '?';
    }

    return $url . $sep . Auth_OpenID::httpBuildQuery($args);
}

function Auth_Yadis_providerIsAuthoritative($providerID, $canonicalID)
{
    $lastbang = strrpos($canonicalID, '!');
    $p = substr($canonicalID, 0, $lastbang);
    return $p == $providerID;
}

function Auth_Yadis_rootAuthority($xri)
{
    // Return the root authority for an XRI.

    $root = null;

    if (Auth_Yadis_startswith($xri, 'xri://')) {
        $xri = substr($xri, 6);
    }

    $authority = explode('/', $xri, 2);
    $authority = $authority[0];
    if ($authority[0] == '(') {
        // Cross-reference.
        // XXX: This is incorrect if someone nests cross-references so
        //   there is another close-paren in there.  Hopefully nobody
        //   does that before we have a real xriparse function.
        //   Hopefully nobody does that *ever*.
        $root = substr($authority, 0, strpos($authority, ')') + 1);
    } else if (in_array($authority[0], Auth_Yadis_getXRIAuthorities())) {
        // Other XRI reference.
        $root = $authority[0];
    } else {
        // IRI reference.
        $_segments = explode("!", $authority);
        $segments = array();
        foreach ($_segments as $s) {
            $segments = array_merge($segments, explode("*", $s));
        }
        $root = $segments[0];
    }

    return Auth_Yadis_XRI($root);
}

function Auth_Yadis_XRI($xri)
{
    if (!Auth_Yadis_startswith($xri, 'xri://')) {
        $xri = 'xri://' . $xri;
    }
    return $xri;
}

function Auth_Yadis_getCanonicalID($iname, $xrds)
{
    // Returns false or a canonical ID value.

    // Now nodes are in reverse order.
    $xrd_list = array_reverse($xrds->allXrdNodes);
    $parser =& $xrds->parser;
    $node = $xrd_list[0];

    $canonicalID_nodes = $parser->evalXPath('xrd:CanonicalID', $node);

    if (!$canonicalID_nodes) {
        return false;
    }

    $canonicalID = $canonicalID_nodes[count($canonicalID_nodes) - 1];
    $canonicalID = Auth_Yadis_XRI($parser->content($canonicalID));

    $childID = $canonicalID;

    for ($i = 1; $i < count($xrd_list); $i++) {
        $xrd = $xrd_list[$i];

        $parent_sought = substr($childID, 0, strrpos($childID, '!'));
        $parent_list = array();

        foreach ($parser->evalXPath('xrd:CanonicalID', $xrd) as $c) {
            $parent_list[] = Auth_Yadis_XRI($parser->content($c));
        }

        if (!in_array($parent_sought, $parent_list)) {
            // raise XRDSFraud.
            return false;
        }

        $childID = $parent_sought;
    }

    $root = Auth_Yadis_rootAuthority($iname);
    if (!Auth_Yadis_providerIsAuthoritative($root, $childID)) {
        // raise XRDSFraud.
        return false;
    }

    return $canonicalID;
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/Association.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/Association.php */ ?>
<?php

/**
 * This module contains code for dealing with associations between
 * consumers and servers.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * @access private
 */
//require_once 'Auth/OpenID/CryptUtil.php';

/**
 * @access private
 */
//require_once 'Auth/OpenID/KVForm.php';

/**
 * @access private
 */
//require_once 'Auth/OpenID/HMACSHA1.php';

/**
 * This class represents an association between a server and a
 * consumer.  In general, users of this library will never see
 * instances of this object.  The only exception is if you implement a
 * custom {@link Auth_OpenID_OpenIDStore}.
 *
 * If you do implement such a store, it will need to store the values
 * of the handle, secret, issued, lifetime, and assoc_type instance
 * variables.
 *
 * @package OpenID
 */
class Auth_OpenID_Association {

    /**
     * This is a HMAC-SHA1 specific value.
     *
     * @access private
     */
    var $SIG_LENGTH = 20;

    /**
     * The ordering and name of keys as stored by serialize.
     *
     * @access private
     */
    var $assoc_keys = array(
                            'version',
                            'handle',
                            'secret',
                            'issued',
                            'lifetime',
                            'assoc_type'
                            );

    var $_macs = array(
                       'HMAC-SHA1' => 'Auth_OpenID_HMACSHA1',
                       'HMAC-SHA256' => 'Auth_OpenID_HMACSHA256'
                       );

    /**
     * This is an alternate constructor (factory method) used by the
     * OpenID consumer library to create associations.  OpenID store
     * implementations shouldn't use this constructor.
     *
     * @access private
     *
     * @param integer $expires_in This is the amount of time this
     * association is good for, measured in seconds since the
     * association was issued.
     *
     * @param string $handle This is the handle the server gave this
     * association.
     *
     * @param string secret This is the shared secret the server
     * generated for this association.
     *
     * @param assoc_type This is the type of association this
     * instance represents.  The only valid values of this field at
     * this time is 'HMAC-SHA1' and 'HMAC-SHA256', but new types may
     * be defined in the future.
     *
     * @return association An {@link Auth_OpenID_Association}
     * instance.
     */
    function fromExpiresIn($expires_in, $handle, $secret, $assoc_type)
    {
        $issued = time();
        $lifetime = $expires_in;
        return new Auth_OpenID_Association($handle, $secret,
                                           $issued, $lifetime, $assoc_type);
    }

    /**
     * This is the standard constructor for creating an association.
     * The library should create all of the necessary associations, so
     * this constructor is not part of the external API.
     *
     * @access private
     *
     * @param string $handle This is the handle the server gave this
     * association.
     *
     * @param string $secret This is the shared secret the server
     * generated for this association.
     *
     * @param integer $issued This is the time this association was
     * issued, in seconds since 00:00 GMT, January 1, 1970.  (ie, a
     * unix timestamp)
     *
     * @param integer $lifetime This is the amount of time this
     * association is good for, measured in seconds since the
     * association was issued.
     *
     * @param string $assoc_type This is the type of association this
     * instance represents.  The only valid values of this field at
     * this time is 'HMAC-SHA1' and 'HMAC-SHA256', but new types may
     * be defined in the future.
     */
    function Auth_OpenID_Association(
        $handle, $secret, $issued, $lifetime, $assoc_type)
    {
        if (!in_array($assoc_type,
                      Auth_OpenID_getSupportedAssociationTypes())) {
            $fmt = 'Unsupported association type (%s)';
            trigger_error(sprintf($fmt, $assoc_type), E_USER_ERROR);
        }

        $this->handle = $handle;
        $this->secret = $secret;
        $this->issued = $issued;
        $this->lifetime = $lifetime;
        $this->assoc_type = $assoc_type;
    }

    /**
     * This returns the number of seconds this association is still
     * valid for, or 0 if the association is no longer valid.
     *
     * @return integer $seconds The number of seconds this association
     * is still valid for, or 0 if the association is no longer valid.
     */
    function getExpiresIn($now = null)
    {
        if ($now == null) {
            $now = time();
        }

        return max(0, $this->issued + $this->lifetime - $now);
    }

    /**
     * This checks to see if two {@link Auth_OpenID_Association}
     * instances represent the same association.
     *
     * @return bool $result true if the two instances represent the
     * same association, false otherwise.
     */
    function equal($other)
    {
        return ((gettype($this) == gettype($other))
                && ($this->handle == $other->handle)
                && ($this->secret == $other->secret)
                && ($this->issued == $other->issued)
                && ($this->lifetime == $other->lifetime)
                && ($this->assoc_type == $other->assoc_type));
    }

    /**
     * Convert an association to KV form.
     *
     * @return string $result String in KV form suitable for
     * deserialization by deserialize.
     */
    function serialize()
    {
        $data = array(
                     'version' => '2',
                     'handle' => $this->handle,
                     'secret' => base64_encode($this->secret),
                     'issued' => strval(intval($this->issued)),
                     'lifetime' => strval(intval($this->lifetime)),
                     'assoc_type' => $this->assoc_type
                     );

        assert(array_keys($data) == $this->assoc_keys);

        return Auth_OpenID_KVForm::fromArray($data, $strict = true);
    }

    /**
     * Parse an association as stored by serialize().  This is the
     * inverse of serialize.
     *
     * @param string $assoc_s Association as serialized by serialize()
     * @return Auth_OpenID_Association $result instance of this class
     */
    function deserialize($class_name, $assoc_s)
    {
        $pairs = Auth_OpenID_KVForm::toArray($assoc_s, $strict = true);
        $keys = array();
        $values = array();
        foreach ($pairs as $key => $value) {
            if (is_array($value)) {
                list($key, $value) = $value;
            }
            $keys[] = $key;
            $values[] = $value;
        }

        $class_vars = get_class_vars($class_name);
        $class_assoc_keys = $class_vars['assoc_keys'];

        sort($keys);
        sort($class_assoc_keys);

        if ($keys != $class_assoc_keys) {
            trigger_error('Unexpected key values: ' . var_export($keys, true),
                          E_USER_WARNING);
            return null;
        }

        $version = $pairs['version'];
        $handle = $pairs['handle'];
        $secret = $pairs['secret'];
        $issued = $pairs['issued'];
        $lifetime = $pairs['lifetime'];
        $assoc_type = $pairs['assoc_type'];

        if ($version != '2') {
            trigger_error('Unknown version: ' . $version, E_USER_WARNING);
            return null;
        }

        $issued = intval($issued);
        $lifetime = intval($lifetime);
        $secret = base64_decode($secret);

        return new $class_name(
            $handle, $secret, $issued, $lifetime, $assoc_type);
    }

    /**
     * Generate a signature for a sequence of (key, value) pairs
     *
     * @access private
     * @param array $pairs The pairs to sign, in order.  This is an
     * array of two-tuples.
     * @return string $signature The binary signature of this sequence
     * of pairs
     */
    function sign($pairs)
    {
        $kv = Auth_OpenID_KVForm::fromArray($pairs);

        /* Invalid association types should be caught at constructor */
        $callback = $this->_macs[$this->assoc_type];

        return call_user_func_array($callback, array($this->secret, $kv));
    }

    /**
     * Generate a signature for some fields in a dictionary
     *
     * @access private
     * @param array $fields The fields to sign, in order; this is an
     * array of strings.
     * @param array $data Dictionary of values to sign (an array of
     * string => string pairs).
     * @return string $signature The signature, base64 encoded
     */
    function signMessage($message)
    {
        if ($message->hasKey(Auth_OpenID_OPENID_NS, 'sig') ||
            $message->hasKey(Auth_OpenID_OPENID_NS, 'signed')) {
            // Already has a sig
            return null;
        }

        $extant_handle = $message->getArg(Auth_OpenID_OPENID_NS,
                                          'assoc_handle');

        if ($extant_handle && ($extant_handle != $this->handle)) {
            // raise ValueError("Message has a different association handle")
            return null;
        }

        $signed_message = $message;
        $signed_message->setArg(Auth_OpenID_OPENID_NS, 'assoc_handle',
                                $this->handle);

        $message_keys = array_keys($signed_message->toPostArgs());
        $signed_list = array();
        $signed_prefix = 'openid.';

        foreach ($message_keys as $k) {
            if (strpos($k, $signed_prefix) === 0) {
                $signed_list[] = substr($k, strlen($signed_prefix));
            }
        }

        $signed_list[] = 'signed';
        sort($signed_list);

        $signed_message->setArg(Auth_OpenID_OPENID_NS, 'signed',
                                implode(',', $signed_list));
        $sig = $this->getMessageSignature($signed_message);
        $signed_message->setArg(Auth_OpenID_OPENID_NS, 'sig', $sig);
        return $signed_message;
    }

    /**
     * Given a {@link Auth_OpenID_Message}, return the key/value pairs
     * to be signed according to the signed list in the message.  If
     * the message lacks a signed list, return null.
     *
     * @access private
     */
    function _makePairs(&$message)
    {
        $signed = $message->getArg(Auth_OpenID_OPENID_NS, 'signed');
        if (!$signed || Auth_OpenID::isFailure($signed)) {
            // raise ValueError('Message has no signed list: %s' % (message,))
            return null;
        }

        $signed_list = explode(',', $signed);
        $pairs = array();
        $data = $message->toPostArgs();
        foreach ($signed_list as $field) {
            $pairs[] = array($field, Auth_OpenID::arrayGet($data,
                                                           'openid.' .
                                                           $field, ''));
        }
        return $pairs;
    }

    /**
     * Given an {@link Auth_OpenID_Message}, return the signature for
     * the signed list in the message.
     *
     * @access private
     */
    function getMessageSignature(&$message)
    {
        $pairs = $this->_makePairs($message);
        return base64_encode($this->sign($pairs));
    }

    /**
     * Confirm that the signature of these fields matches the
     * signature contained in the data.
     *
     * @access private
     */
    function checkMessageSignature(&$message)
    {
        $sig = $message->getArg(Auth_OpenID_OPENID_NS,
                                'sig');

        if (!$sig || Auth_OpenID::isFailure($sig)) {
            return false;
        }

        $calculated_sig = $this->getMessageSignature($message);
        return $calculated_sig == $sig;
    }
}

function Auth_OpenID_getSecretSize($assoc_type)
{
    if ($assoc_type == 'HMAC-SHA1') {
        return 20;
    } else if ($assoc_type == 'HMAC-SHA256') {
        return 32;
    } else {
        return null;
    }
}

function Auth_OpenID_getAllAssociationTypes()
{
    return array('HMAC-SHA1', 'HMAC-SHA256');
}

function Auth_OpenID_getSupportedAssociationTypes()
{
    $a = array('HMAC-SHA1');

    if (Auth_OpenID_HMACSHA256_SUPPORTED) {
        $a[] = 'HMAC-SHA256';
    }

    return $a;
}

function Auth_OpenID_getSessionTypes($assoc_type)
{
    $assoc_to_session = array(
       'HMAC-SHA1' => array('DH-SHA1', 'no-encryption'));

    if (Auth_OpenID_HMACSHA256_SUPPORTED) {
        $assoc_to_session['HMAC-SHA256'] =
            array('DH-SHA256', 'no-encryption');
    }

    return Auth_OpenID::arrayGet($assoc_to_session, $assoc_type, array());
}

function Auth_OpenID_checkSessionType($assoc_type, $session_type)
{
    if (!in_array($session_type,
                  Auth_OpenID_getSessionTypes($assoc_type))) {
        return false;
    }

    return true;
}

function Auth_OpenID_getDefaultAssociationOrder()
{
    $order = array();

    if (!Auth_OpenID_noMathSupport()) {
        $order[] = array('HMAC-SHA1', 'DH-SHA1');

        if (Auth_OpenID_HMACSHA256_SUPPORTED) {
            $order[] = array('HMAC-SHA256', 'DH-SHA256');
        }
    }

    $order[] = array('HMAC-SHA1', 'no-encryption');

    if (Auth_OpenID_HMACSHA256_SUPPORTED) {
        $order[] = array('HMAC-SHA256', 'no-encryption');
    }

    return $order;
}

function Auth_OpenID_getOnlyEncryptedOrder()
{
    $result = array();

    foreach (Auth_OpenID_getDefaultAssociationOrder() as $pair) {
        list($assoc, $session) = $pair;

        if ($session != 'no-encryption') {
            if (Auth_OpenID_HMACSHA256_SUPPORTED &&
                ($assoc == 'HMAC-SHA256')) {
                $result[] = $pair;
            } else if ($assoc != 'HMAC-SHA256') {
                $result[] = $pair;
            }
        }
    }

    return $result;
}

function &Auth_OpenID_getDefaultNegotiator()
{
    $x = new Auth_OpenID_SessionNegotiator(
                 Auth_OpenID_getDefaultAssociationOrder());
    return $x;
}

function &Auth_OpenID_getEncryptedNegotiator()
{
    $x = new Auth_OpenID_SessionNegotiator(
                 Auth_OpenID_getOnlyEncryptedOrder());
    return $x;
}

/**
 * A session negotiator controls the allowed and preferred association
 * types and association session types. Both the {@link
 * Auth_OpenID_Consumer} and {@link Auth_OpenID_Server} use
 * negotiators when creating associations.
 *
 * You can create and use negotiators if you:

 * - Do not want to do Diffie-Hellman key exchange because you use
 * transport-layer encryption (e.g. SSL)
 *
 * - Want to use only SHA-256 associations
 *
 * - Do not want to support plain-text associations over a non-secure
 * channel
 *
 * It is up to you to set a policy for what kinds of associations to
 * accept. By default, the library will make any kind of association
 * that is allowed in the OpenID 2.0 specification.
 *
 * Use of negotiators in the library
 * =================================
 *
 * When a consumer makes an association request, it calls {@link
 * getAllowedType} to get the preferred association type and
 * association session type.
 *
 * The server gets a request for a particular association/session type
 * and calls {@link isAllowed} to determine if it should create an
 * association. If it is supported, negotiation is complete. If it is
 * not, the server calls {@link getAllowedType} to get an allowed
 * association type to return to the consumer.
 *
 * If the consumer gets an error response indicating that the
 * requested association/session type is not supported by the server
 * that contains an assocation/session type to try, it calls {@link
 * isAllowed} to determine if it should try again with the given
 * combination of association/session type.
 *
 * @package OpenID
 */
class Auth_OpenID_SessionNegotiator {
    function Auth_OpenID_SessionNegotiator($allowed_types)
    {
        $this->allowed_types = array();
        $this->setAllowedTypes($allowed_types);
    }

    /**
     * Set the allowed association types, checking to make sure each
     * combination is valid.
     *
     * @access private
     */
    function setAllowedTypes($allowed_types)
    {
        foreach ($allowed_types as $pair) {
            list($assoc_type, $session_type) = $pair;
            if (!Auth_OpenID_checkSessionType($assoc_type, $session_type)) {
                return false;
            }
        }

        $this->allowed_types = $allowed_types;
        return true;
    }

    /**
     * Add an association type and session type to the allowed types
     * list. The assocation/session pairs are tried in the order that
     * they are added.
     *
     * @access private
     */
    function addAllowedType($assoc_type, $session_type = null)
    {
        if ($this->allowed_types === null) {
            $this->allowed_types = array();
        }

        if ($session_type === null) {
            $available = Auth_OpenID_getSessionTypes($assoc_type);

            if (!$available) {
                return false;
            }

            foreach ($available as $session_type) {
                $this->addAllowedType($assoc_type, $session_type);
            }
        } else {
            if (Auth_OpenID_checkSessionType($assoc_type, $session_type)) {
                $this->allowed_types[] = array($assoc_type, $session_type);
            } else {
                return false;
            }
        }

        return true;
    }

    // Is this combination of association type and session type allowed?
    function isAllowed($assoc_type, $session_type)
    {
        $assoc_good = in_array(array($assoc_type, $session_type),
                               $this->allowed_types);

        $matches = in_array($session_type,
                            Auth_OpenID_getSessionTypes($assoc_type));

        return ($assoc_good && $matches);
    }

    /**
     * Get a pair of assocation type and session type that are
     * supported.
     */
    function getAllowedType()
    {
        if (!$this->allowed_types) {
            return array(null, null);
        }

        return $this->allowed_types[0];
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/BigMath.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/BigMath.php */ ?>
<?php

/**
 * BigMath: A math library wrapper that abstracts out the underlying
 * long integer library.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @access private
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Needed for random number generation
 */
//require_once 'Auth/OpenID/CryptUtil.php';

/**
 * Need Auth_OpenID::bytes().
 */
//require_once 'Auth/OpenID.php';

/**
 * The superclass of all big-integer math implementations
 * @access private
 * @package OpenID
 */
class Auth_OpenID_MathLibrary {
    /**
     * Given a long integer, returns the number converted to a binary
     * string.  This function accepts long integer values of arbitrary
     * magnitude and uses the local large-number math library when
     * available.
     *
     * @param integer $long The long number (can be a normal PHP
     * integer or a number created by one of the available long number
     * libraries)
     * @return string $binary The binary version of $long
     */
    function longToBinary($long)
    {
        $cmp = $this->cmp($long, 0);
        if ($cmp < 0) {
            $msg = __FUNCTION__ . " takes only positive integers.";
            trigger_error($msg, E_USER_ERROR);
            return null;
        }

        if ($cmp == 0) {
            return "\x00";
        }

        $bytes = array();

        while ($this->cmp($long, 0) > 0) {
            array_unshift($bytes, $this->mod($long, 256));
            $long = $this->div($long, pow(2, 8));
        }

        if ($bytes && ($bytes[0] > 127)) {
            array_unshift($bytes, 0);
        }

        $string = '';
        foreach ($bytes as $byte) {
            $string .= pack('C', $byte);
        }

        return $string;
    }

    /**
     * Given a binary string, returns the binary string converted to a
     * long number.
     *
     * @param string $binary The binary version of a long number,
     * probably as a result of calling longToBinary
     * @return integer $long The long number equivalent of the binary
     * string $str
     */
    function binaryToLong($str)
    {
        if ($str === null) {
            return null;
        }

        // Use array_merge to return a zero-indexed array instead of a
        // one-indexed array.
        $bytes = array_merge(unpack('C*', $str));

        $n = $this->init(0);

        if ($bytes && ($bytes[0] > 127)) {
            trigger_error("bytesToNum works only for positive integers.",
                          E_USER_WARNING);
            return null;
        }

        foreach ($bytes as $byte) {
            $n = $this->mul($n, pow(2, 8));
            $n = $this->add($n, $byte);
        }

        return $n;
    }

    function base64ToLong($str)
    {
        $b64 = base64_decode($str);

        if ($b64 === false) {
            return false;
        }

        return $this->binaryToLong($b64);
    }

    function longToBase64($str)
    {
        return base64_encode($this->longToBinary($str));
    }

    /**
     * Returns a random number in the specified range.  This function
     * accepts $start, $stop, and $step values of arbitrary magnitude
     * and will utilize the local large-number math library when
     * available.
     *
     * @param integer $start The start of the range, or the minimum
     * random number to return
     * @param integer $stop The end of the range, or the maximum
     * random number to return
     * @param integer $step The step size, such that $result - ($step
     * * N) = $start for some N
     * @return integer $result The resulting randomly-generated number
     */
    function rand($stop)
    {
        static $duplicate_cache = array();

        // Used as the key for the duplicate cache
        $rbytes = $this->longToBinary($stop);

        if (array_key_exists($rbytes, $duplicate_cache)) {
            list($duplicate, $nbytes) = $duplicate_cache[$rbytes];
        } else {
            if ($rbytes[0] == "\x00") {
                $nbytes = Auth_OpenID::bytes($rbytes) - 1;
            } else {
                $nbytes = Auth_OpenID::bytes($rbytes);
            }

            $mxrand = $this->pow(256, $nbytes);

            // If we get a number less than this, then it is in the
            // duplicated range.
            $duplicate = $this->mod($mxrand, $stop);

            if (count($duplicate_cache) > 10) {
                $duplicate_cache = array();
            }

            $duplicate_cache[$rbytes] = array($duplicate, $nbytes);
        }

        do {
            $bytes = "\x00" . Auth_OpenID_CryptUtil::getBytes($nbytes);
            $n = $this->binaryToLong($bytes);
            // Keep looping if this value is in the low duplicated range
        } while ($this->cmp($n, $duplicate) < 0);

        return $this->mod($n, $stop);
    }
}

/**
 * Exposes BCmath math library functionality.
 *
 * {@link Auth_OpenID_BcMathWrapper} wraps the functionality provided
 * by the BCMath extension.
 *
 * @access private
 * @package OpenID
 */
class Auth_OpenID_BcMathWrapper extends Auth_OpenID_MathLibrary{
    var $type = 'bcmath';

    function add($x, $y)
    {
        return bcadd($x, $y);
    }

    function sub($x, $y)
    {
        return bcsub($x, $y);
    }

    function pow($base, $exponent)
    {
        return bcpow($base, $exponent);
    }

    function cmp($x, $y)
    {
        return bccomp($x, $y);
    }

    function init($number, $base = 10)
    {
        return $number;
    }

    function mod($base, $modulus)
    {
        return bcmod($base, $modulus);
    }

    function mul($x, $y)
    {
        return bcmul($x, $y);
    }

    function div($x, $y)
    {
        return bcdiv($x, $y);
    }

    /**
     * Same as bcpowmod when bcpowmod is missing
     *
     * @access private
     */
    function _powmod($base, $exponent, $modulus)
    {
        $square = $this->mod($base, $modulus);
        $result = 1;
        while($this->cmp($exponent, 0) > 0) {
            if ($this->mod($exponent, 2)) {
                $result = $this->mod($this->mul($result, $square), $modulus);
            }
            $square = $this->mod($this->mul($square, $square), $modulus);
            $exponent = $this->div($exponent, 2);
        }
        return $result;
    }

    function powmod($base, $exponent, $modulus)
    {
        if (function_exists('bcpowmod')) {
            return bcpowmod($base, $exponent, $modulus);
        } else {
            return $this->_powmod($base, $exponent, $modulus);
        }
    }

    function toString($num)
    {
        return $num;
    }
}

/**
 * Exposes GMP math library functionality.
 *
 * {@link Auth_OpenID_GmpMathWrapper} wraps the functionality provided
 * by the GMP extension.
 *
 * @access private
 * @package OpenID
 */
class Auth_OpenID_GmpMathWrapper extends Auth_OpenID_MathLibrary{
    var $type = 'gmp';

    function add($x, $y)
    {
        return gmp_add($x, $y);
    }

    function sub($x, $y)
    {
        return gmp_sub($x, $y);
    }

    function pow($base, $exponent)
    {
        return gmp_pow($base, $exponent);
    }

    function cmp($x, $y)
    {
        return gmp_cmp($x, $y);
    }

    function init($number, $base = 10)
    {
        return gmp_init($number, $base);
    }

    function mod($base, $modulus)
    {
        return gmp_mod($base, $modulus);
    }

    function mul($x, $y)
    {
        return gmp_mul($x, $y);
    }

    function div($x, $y)
    {
        return gmp_div_q($x, $y);
    }

    function powmod($base, $exponent, $modulus)
    {
        return gmp_powm($base, $exponent, $modulus);
    }

    function toString($num)
    {
        return gmp_strval($num);
    }
}

/**
 * Define the supported extensions.  An extension array has keys
 * 'modules', 'extension', and 'class'.  'modules' is an array of PHP
 * module names which the loading code will attempt to load.  These
 * values will be suffixed with a library file extension (e.g. ".so").
 * 'extension' is the name of a PHP extension which will be tested
 * before 'modules' are loaded.  'class' is the string name of a
 * {@link Auth_OpenID_MathWrapper} subclass which should be
 * instantiated if a given extension is present.
 *
 * You can define new math library implementations and add them to
 * this array.
 */
function Auth_OpenID_math_extensions()
{
    $result = array();

    if (!defined('Auth_OpenID_BUGGY_GMP')) {
        $result[] =
            array('modules' => array('gmp', 'php_gmp'),
                  'extension' => 'gmp',
                  'class' => 'Auth_OpenID_GmpMathWrapper');
    }

    $result[] = array(
                      'modules' => array('bcmath', 'php_bcmath'),
                      'extension' => 'bcmath',
                      'class' => 'Auth_OpenID_BcMathWrapper');

    return $result;
}

/**
 * Detect which (if any) math library is available
 */
function Auth_OpenID_detectMathLibrary($exts)
{
    $loaded = false;

    foreach ($exts as $extension) {
        // See if the extension specified is already loaded.
        if ($extension['extension'] &&
            extension_loaded($extension['extension'])) {
            $loaded = true;
        }

        // Try to load dynamic modules.
        if (!$loaded) {
            foreach ($extension['modules'] as $module) {
                if (@dl($module . "." . PHP_SHLIB_SUFFIX)) {
                    $loaded = true;
                    break;
                }
            }
        }

        // If the load succeeded, supply an instance of
        // Auth_OpenID_MathWrapper which wraps the specified
        // module's functionality.
        if ($loaded) {
            return $extension;
        }
    }

    return false;
}

/**
 * {@link Auth_OpenID_getMathLib} checks for the presence of long
 * number extension modules and returns an instance of
 * {@link Auth_OpenID_MathWrapper} which exposes the module's
 * functionality.
 *
 * Checks for the existence of an extension module described by the
 * result of {@link Auth_OpenID_math_extensions()} and returns an
 * instance of a wrapper for that extension module.  If no extension
 * module is found, an instance of {@link Auth_OpenID_MathWrapper} is
 * returned, which wraps the native PHP integer implementation.  The
 * proper calling convention for this method is $lib =&
 * Auth_OpenID_getMathLib().
 *
 * This function checks for the existence of specific long number
 * implementations in the following order: GMP followed by BCmath.
 *
 * @return Auth_OpenID_MathWrapper $instance An instance of
 * {@link Auth_OpenID_MathWrapper} or one of its subclasses
 *
 * @package OpenID
 */
function &Auth_OpenID_getMathLib()
{
    // The instance of Auth_OpenID_MathWrapper that we choose to
    // supply will be stored here, so that subseqent calls to this
    // method will return a reference to the same object.
    static $lib = null;

    if (isset($lib)) {
        return $lib;
    }

    if (Auth_OpenID_noMathSupport()) {
        $null = null;
        return $null;
    }

    // If this method has not been called before, look at
    // Auth_OpenID_math_extensions and try to find an extension that
    // works.
    $ext = Auth_OpenID_detectMathLibrary(Auth_OpenID_math_extensions());
    if ($ext === false) {
        $tried = array();
        foreach (Auth_OpenID_math_extensions() as $extinfo) {
            $tried[] = $extinfo['extension'];
        }
        $triedstr = implode(", ", $tried);

        Auth_OpenID_setNoMathSupport();

        $result = null;
        return $result;
    }

    // Instantiate a new wrapper
    $class = $ext['class'];
    $lib = new $class();

    return $lib;
}

function Auth_OpenID_setNoMathSupport()
{
    if (!defined('Auth_OpenID_NO_MATH_SUPPORT')) {
        define('Auth_OpenID_NO_MATH_SUPPORT', true);
    }
}

function Auth_OpenID_noMathSupport()
{
    return defined('Auth_OpenID_NO_MATH_SUPPORT');
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/DumbStore.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/DumbStore.php */ ?>
<?php

/**
 * This file supplies a dumb store backend for OpenID servers and
 * consumers.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Import the interface for creating a new store class.
 */
//require_once 'Auth/OpenID/Interface.php';
//require_once 'Auth/OpenID/HMACSHA1.php';

/**
 * This is a store for use in the worst case, when you have no way of
 * saving state on the consumer site. Using this store makes the
 * consumer vulnerable to replay attacks, as it's unable to use
 * nonces. Avoid using this store if it is at all possible.
 *
 * Most of the methods of this class are implementation details.
 * Users of this class need to worry only about the constructor.
 *
 * @package OpenID
 */
class Auth_OpenID_DumbStore extends Auth_OpenID_OpenIDStore {

    /**
     * Creates a new {@link Auth_OpenID_DumbStore} instance. For the security
     * of the tokens generated by the library, this class attempts to
     * at least have a secure implementation of getAuthKey.
     *
     * When you create an instance of this class, pass in a secret
     * phrase. The phrase is hashed with sha1 to make it the correct
     * length and form for an auth key. That allows you to use a long
     * string as the secret phrase, which means you can make it very
     * difficult to guess.
     *
     * Each {@link Auth_OpenID_DumbStore} instance that is created for use by
     * your consumer site needs to use the same $secret_phrase.
     *
     * @param string secret_phrase The phrase used to create the auth
     * key returned by getAuthKey
     */
    function Auth_OpenID_DumbStore($secret_phrase)
    {
        $this->auth_key = Auth_OpenID_SHA1($secret_phrase);
    }

    /**
     * This implementation does nothing.
     */
    function storeAssociation($server_url, $association)
    {
    }

    /**
     * This implementation always returns null.
     */
    function getAssociation($server_url, $handle = null)
    {
        return null;
    }

    /**
     * This implementation always returns false.
     */
    function removeAssociation($server_url, $handle)
    {
        return false;
    }

    /**
     * In a system truly limited to dumb mode, nonces must all be
     * accepted. This therefore always returns true, which makes
     * replay attacks feasible.
     */
    function useNonce($server_url, $timestamp, $salt)
    {
        return true;
    }

    /**
     * This method returns the auth key generated by the constructor.
     */
    function getAuthKey()
    {
        return $this->auth_key;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/Extension.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/Extension.php */ ?>
<?php

/**
 * An interface for OpenID extensions.
 *
 * @package OpenID
 */

/**
 * Require the Message implementation.
 */
//require_once 'Auth/OpenID/Message.php';

/**
 * A base class for accessing extension request and response data for
 * the OpenID 2 protocol.
 *
 * @package OpenID
 */
class Auth_OpenID_Extension {
    /**
     * ns_uri: The namespace to which to add the arguments for this
     * extension
     */
    var $ns_uri = null;
    var $ns_alias = null;

    /**
     * Get the string arguments that should be added to an OpenID
     * message for this extension.
     */
    function getExtensionArgs()
    {
        return null;
    }

    /**
     * Add the arguments from this extension to the provided message.
     *
     * Returns the message with the extension arguments added.
     */
    function toMessage(&$message)
    {
        $implicit = $message->isOpenID1();
        $added = $message->namespaces->addAlias($this->ns_uri,
                                                $this->ns_alias,
                                                $implicit);

        if ($added === null) {
            if ($message->namespaces->getAlias($this->ns_uri) !=
                $this->ns_alias) {
                return null;
            }
        }

        $message->updateArgs($this->ns_uri,
                             $this->getExtensionArgs());
        return $message;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/FileStore.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/FileStore.php */ ?>
<?php

/**
 * This file supplies a Memcached store backend for OpenID servers and
 * consumers.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Require base class for creating a new interface.
 */
//require_once 'Auth/OpenID.php';
//require_once 'Auth/OpenID/Interface.php';
//require_once 'Auth/OpenID/HMACSHA1.php';
//require_once 'Auth/OpenID/Nonce.php';

/**
 * This is a filesystem-based store for OpenID associations and
 * nonces.  This store should be safe for use in concurrent systems on
 * both windows and unix (excluding NFS filesystems).  There are a
 * couple race conditions in the system, but those failure cases have
 * been set up in such a way that the worst-case behavior is someone
 * having to try to log in a second time.
 *
 * Most of the methods of this class are implementation details.
 * People wishing to just use this store need only pay attention to
 * the constructor.
 *
 * @package OpenID
 */
class Auth_OpenID_FileStore extends Auth_OpenID_OpenIDStore {

    /**
     * Initializes a new {@link Auth_OpenID_FileStore}.  This
     * initializes the nonce and association directories, which are
     * subdirectories of the directory passed in.
     *
     * @param string $directory This is the directory to put the store
     * directories in.
     */
    function Auth_OpenID_FileStore($directory)
    {
        if (!Auth_OpenID::ensureDir($directory)) {
            trigger_error('Not a directory and failed to create: '
                          . $directory, E_USER_ERROR);
        }
        $directory = realpath($directory);

        $this->directory = $directory;
        $this->active = true;

        $this->nonce_dir = $directory . DIRECTORY_SEPARATOR . 'nonces';

        $this->association_dir = $directory . DIRECTORY_SEPARATOR .
            'associations';

        // Temp dir must be on the same filesystem as the assciations
        // $directory.
        $this->temp_dir = $directory . DIRECTORY_SEPARATOR . 'temp';

        $this->max_nonce_age = 6 * 60 * 60; // Six hours, in seconds

        if (!$this->_setup()) {
            trigger_error('Failed to initialize OpenID file store in ' .
                          $directory, E_USER_ERROR);
        }
    }

    function destroy()
    {
        Auth_OpenID_FileStore::_rmtree($this->directory);
        $this->active = false;
    }

    /**
     * Make sure that the directories in which we store our data
     * exist.
     *
     * @access private
     */
    function _setup()
    {
        return (Auth_OpenID::ensureDir($this->nonce_dir) &&
                Auth_OpenID::ensureDir($this->association_dir) &&
                Auth_OpenID::ensureDir($this->temp_dir));
    }

    /**
     * Create a temporary file on the same filesystem as
     * $this->association_dir.
     *
     * The temporary directory should not be cleaned if there are any
     * processes using the store. If there is no active process using
     * the store, it is safe to remove all of the files in the
     * temporary directory.
     *
     * @return array ($fd, $filename)
     * @access private
     */
    function _mktemp()
    {
        $name = Auth_OpenID_FileStore::_mkstemp($dir = $this->temp_dir);
        $file_obj = @fopen($name, 'wb');
        if ($file_obj !== false) {
            return array($file_obj, $name);
        } else {
            Auth_OpenID_FileStore::_removeIfPresent($name);
        }
    }

    function cleanupNonces()
    {
        global $Auth_OpenID_SKEW;

        $nonces = Auth_OpenID_FileStore::_listdir($this->nonce_dir);
        $now = time();

        $removed = 0;
        // Check all nonces for expiry
        foreach ($nonces as $nonce_fname) {
            $base = basename($nonce_fname);
            $parts = explode('-', $base, 2);
            $timestamp = $parts[0];
            $timestamp = intval($timestamp, 16);
            if (abs($timestamp - $now) > $Auth_OpenID_SKEW) {
                Auth_OpenID_FileStore::_removeIfPresent($nonce_fname);
                $removed += 1;
            }
        }
        return $removed;
    }

    /**
     * Create a unique filename for a given server url and
     * handle. This implementation does not assume anything about the
     * format of the handle. The filename that is returned will
     * contain the domain name from the server URL for ease of human
     * inspection of the data directory.
     *
     * @return string $filename
     */
    function getAssociationFilename($server_url, $handle)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        if (strpos($server_url, '://') === false) {
            trigger_error(sprintf("Bad server URL: %s", $server_url),
                          E_USER_WARNING);
            return null;
        }

        list($proto, $rest) = explode('://', $server_url, 2);
        $parts = explode('/', $rest);
        $domain = Auth_OpenID_FileStore::_filenameEscape($parts[0]);
        $url_hash = Auth_OpenID_FileStore::_safe64($server_url);
        if ($handle) {
            $handle_hash = Auth_OpenID_FileStore::_safe64($handle);
        } else {
            $handle_hash = '';
        }

        $filename = sprintf('%s-%s-%s-%s', $proto, $domain, $url_hash,
                            $handle_hash);

        return $this->association_dir. DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Store an association in the association directory.
     */
    function storeAssociation($server_url, $association)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return false;
        }

        $association_s = $association->serialize();
        $filename = $this->getAssociationFilename($server_url,
                                                  $association->handle);
        list($tmp_file, $tmp) = $this->_mktemp();

        if (!$tmp_file) {
            trigger_error("_mktemp didn't return a valid file descriptor",
                          E_USER_WARNING);
            return false;
        }

        fwrite($tmp_file, $association_s);

        fflush($tmp_file);

        fclose($tmp_file);

        if (@rename($tmp, $filename)) {
            return true;
        } else {
            // In case we are running on Windows, try unlinking the
            // file in case it exists.
            @unlink($filename);

            // Now the target should not exist. Try renaming again,
            // giving up if it fails.
            if (@rename($tmp, $filename)) {
                return true;
            }
        }

        // If there was an error, don't leave the temporary file
        // around.
        Auth_OpenID_FileStore::_removeIfPresent($tmp);
        return false;
    }

    /**
     * Retrieve an association. If no handle is specified, return the
     * association with the most recent issue time.
     *
     * @return mixed $association
     */
    function getAssociation($server_url, $handle = null)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        if ($handle === null) {
            $handle = '';
        }

        // The filename with the empty handle is a prefix of all other
        // associations for the given server URL.
        $filename = $this->getAssociationFilename($server_url, $handle);

        if ($handle) {
            return $this->_getAssociation($filename);
        } else {
            $association_files =
                Auth_OpenID_FileStore::_listdir($this->association_dir);
            $matching_files = array();

            // strip off the path to do the comparison
            $name = basename($filename);
            foreach ($association_files as $association_file) {
                $base = basename($association_file);
                if (strpos($base, $name) === 0) {
                    $matching_files[] = $association_file;
                }
            }

            $matching_associations = array();
            // read the matching files and sort by time issued
            foreach ($matching_files as $full_name) {
                $association = $this->_getAssociation($full_name);
                if ($association !== null) {
                    $matching_associations[] = array($association->issued,
                                                     $association);
                }
            }

            $issued = array();
            $assocs = array();
            foreach ($matching_associations as $key => $assoc) {
                $issued[$key] = $assoc[0];
                $assocs[$key] = $assoc[1];
            }

            array_multisort($issued, SORT_DESC, $assocs, SORT_DESC,
                            $matching_associations);

            // return the most recently issued one.
            if ($matching_associations) {
                list($issued, $assoc) = $matching_associations[0];
                return $assoc;
            } else {
                return null;
            }
        }
    }

    /**
     * @access private
     */
    function _getAssociation($filename)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $assoc_file = @fopen($filename, 'rb');

        if ($assoc_file === false) {
            return null;
        }

        $assoc_s = fread($assoc_file, filesize($filename));
        fclose($assoc_file);

        if (!$assoc_s) {
            return null;
        }

        $association =
            Auth_OpenID_Association::deserialize('Auth_OpenID_Association',
                                                $assoc_s);

        if (!$association) {
            Auth_OpenID_FileStore::_removeIfPresent($filename);
            return null;
        }

        if ($association->getExpiresIn() == 0) {
            Auth_OpenID_FileStore::_removeIfPresent($filename);
            return null;
        } else {
            return $association;
        }
    }

    /**
     * Remove an association if it exists. Do nothing if it does not.
     *
     * @return bool $success
     */
    function removeAssociation($server_url, $handle)
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $assoc = $this->getAssociation($server_url, $handle);
        if ($assoc === null) {
            return false;
        } else {
            $filename = $this->getAssociationFilename($server_url, $handle);
            return Auth_OpenID_FileStore::_removeIfPresent($filename);
        }
    }

    /**
     * Return whether this nonce is present. As a side effect, mark it
     * as no longer present.
     *
     * @return bool $present
     */
    function useNonce($server_url, $timestamp, $salt)
    {
        global $Auth_OpenID_SKEW;

        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        if ( abs($timestamp - time()) > $Auth_OpenID_SKEW ) {
            return False;
        }

        if ($server_url) {
            list($proto, $rest) = explode('://', $server_url, 2);
        } else {
            $proto = '';
            $rest = '';
        }

        $parts = explode('/', $rest, 2);
        $domain = $this->_filenameEscape($parts[0]);
        $url_hash = $this->_safe64($server_url);
        $salt_hash = $this->_safe64($salt);

        $filename = sprintf('%08x-%s-%s-%s-%s', $timestamp, $proto,
                            $domain, $url_hash, $salt_hash);
        $filename = $this->nonce_dir . DIRECTORY_SEPARATOR . $filename;

        $result = @fopen($filename, 'x');

        if ($result === false) {
            return false;
        } else {
            fclose($result);
            return true;
        }
    }

    /**
     * Remove expired entries from the database. This is potentially
     * expensive, so only run when it is acceptable to take time.
     *
     * @access private
     */
    function _allAssocs()
    {
        $all_associations = array();

        $association_filenames =
            Auth_OpenID_FileStore::_listdir($this->association_dir);

        foreach ($association_filenames as $association_filename) {
            $association_file = fopen($association_filename, 'rb');

            if ($association_file !== false) {
                $assoc_s = fread($association_file,
                                 filesize($association_filename));
                fclose($association_file);

                // Remove expired or corrupted associations
                $association =
                  Auth_OpenID_Association::deserialize(
                         'Auth_OpenID_Association', $assoc_s);

                if ($association === null) {
                    Auth_OpenID_FileStore::_removeIfPresent(
                                                 $association_filename);
                } else {
                    if ($association->getExpiresIn() == 0) {
                        $all_associations[] = array($association_filename,
                                                    $association);
                    }
                }
            }
        }

        return $all_associations;
    }

    function clean()
    {
        if (!$this->active) {
            trigger_error("FileStore no longer active", E_USER_ERROR);
            return null;
        }

        $nonces = Auth_OpenID_FileStore::_listdir($this->nonce_dir);
        $now = time();

        // Check all nonces for expiry
        foreach ($nonces as $nonce) {
            if (!Auth_OpenID_checkTimestamp($nonce, $now)) {
                $filename = $this->nonce_dir . DIRECTORY_SEPARATOR . $nonce;
                Auth_OpenID_FileStore::_removeIfPresent($filename);
            }
        }

        foreach ($this->_allAssocs() as $pair) {
            list($assoc_filename, $assoc) = $pair;
            if ($assoc->getExpiresIn() == 0) {
                Auth_OpenID_FileStore::_removeIfPresent($assoc_filename);
            }
        }
    }

    /**
     * @access private
     */
    function _rmtree($dir)
    {
        if ($dir[strlen($dir) - 1] != DIRECTORY_SEPARATOR) {
            $dir .= DIRECTORY_SEPARATOR;
        }

        if ($handle = opendir($dir)) {
            while ($item = readdir($handle)) {
                if (!in_array($item, array('.', '..'))) {
                    if (is_dir($dir . $item)) {

                        if (!Auth_OpenID_FileStore::_rmtree($dir . $item)) {
                            return false;
                        }
                    } else if (is_file($dir . $item)) {
                        if (!unlink($dir . $item)) {
                            return false;
                        }
                    }
                }
            }

            closedir($handle);

            if (!@rmdir($dir)) {
                return false;
            }

            return true;
        } else {
            // Couldn't open directory.
            return false;
        }
    }

    /**
     * @access private
     */
    function _mkstemp($dir)
    {
        foreach (range(0, 4) as $i) {
            $name = tempnam($dir, "php_openid_filestore_");

            if ($name !== false) {
                return $name;
            }
        }
        return false;
    }

    /**
     * @access private
     */
    function _mkdtemp($dir)
    {
        foreach (range(0, 4) as $i) {
            $name = $dir . strval(DIRECTORY_SEPARATOR) . strval(getmypid()) .
                "-" . strval(rand(1, time()));
            if (!mkdir($name, 0700)) {
                return false;
            } else {
                return $name;
            }
        }
        return false;
    }

    /**
     * @access private
     */
    function _listdir($dir)
    {
        $handle = opendir($dir);
        $files = array();
        while (false !== ($filename = readdir($handle))) {
            if (!in_array($filename, array('.', '..'))) {
                $files[] = $dir . DIRECTORY_SEPARATOR . $filename;
            }
        }
        return $files;
    }

    /**
     * @access private
     */
    function _isFilenameSafe($char)
    {
        $_Auth_OpenID_filename_allowed = Auth_OpenID_letters .
            Auth_OpenID_digits . ".";
        return (strpos($_Auth_OpenID_filename_allowed, $char) !== false);
    }

    /**
     * @access private
     */
    function _safe64($str)
    {
        $h64 = base64_encode(Auth_OpenID_SHA1($str));
        $h64 = str_replace('+', '_', $h64);
        $h64 = str_replace('/', '.', $h64);
        $h64 = str_replace('=', '', $h64);
        return $h64;
    }

    /**
     * @access private
     */
    function _filenameEscape($str)
    {
        $filename = "";
        $b = Auth_OpenID::toBytes($str);

        for ($i = 0; $i < count($b); $i++) {
            $c = $b[$i];
            if (Auth_OpenID_FileStore::_isFilenameSafe($c)) {
                $filename .= $c;
            } else {
                $filename .= sprintf("_%02X", ord($c));
            }
        }
        return $filename;
    }

    /**
     * Attempt to remove a file, returning whether the file existed at
     * the time of the call.
     *
     * @access private
     * @return bool $result True if the file was present, false if not.
     */
    function _removeIfPresent($filename)
    {
        return @unlink($filename);
    }

    function cleanupAssociations()
    {
        $removed = 0;
        foreach ($this->_allAssocs() as $pair) {
            list($assoc_filename, $assoc) = $pair;
            if ($assoc->getExpiresIn() == 0) {
                $this->_removeIfPresent($assoc_filename);
                $removed += 1;
            }
        }
        return $removed;
    }
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/MemcachedStore.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/MemcachedStore.php */ ?>
<?php

/**
 * This file supplies a memcached store backend for OpenID servers and
 * consumers.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author Artemy Tregubenko <me@arty.name>
 * @copyright 2008 JanRain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 * Contributed by Open Web Technologies <http://openwebtech.ru/>
 */

/**
 * Import the interface for creating a new store class.
 */
//require_once 'Auth/OpenID/Interface.php';

/**
 * This is a memcached-based store for OpenID associations and
 * nonces.
 *
 * As memcache has limit of 250 chars for key length,
 * server_url, handle and salt are hashed with sha1().
 *
 * Most of the methods of this class are implementation details.
 * People wishing to just use this store need only pay attention to
 * the constructor.
 *
 * @package OpenID
 */
class Auth_OpenID_MemcachedStore extends Auth_OpenID_OpenIDStore {

    /**
     * Initializes a new {@link Auth_OpenID_MemcachedStore} instance.
     * Just saves memcached object as property.
     *
     * @param resource connection Memcache connection resourse
     */
    function Auth_OpenID_MemcachedStore($connection, $compress = false)
    {
        $this->connection = $connection;
        $this->compress = $compress ? MEMCACHE_COMPRESSED : 0;
    }

    /**
     * Store association until its expiration time in memcached.
     * Overwrites any existing association with same server_url and
     * handle. Handles list of associations for every server.
     */
    function storeAssociation($server_url, $association)
    {
        // create memcached keys for association itself
        // and list of associations for this server
        $associationKey = $this->associationKey($server_url,
            $association->handle);
        $serverKey = $this->associationServerKey($server_url);

        // get list of associations
        $serverAssociations = $this->connection->get($serverKey);

        // if no such list, initialize it with empty array
        if (!$serverAssociations) {
            $serverAssociations = array();
        }
        // and store given association key in it
        $serverAssociations[$association->issued] = $associationKey;

        // save associations' keys list
        $this->connection->set(
            $serverKey,
            $serverAssociations,
            $this->compress
        );
        // save association itself
        $this->connection->set(
            $associationKey,
            $association,
            $this->compress,
            $association->issued + $association->lifetime);
    }

    /**
     * Read association from memcached. If no handle given
     * and multiple associations found, returns latest issued
     */
    function getAssociation($server_url, $handle = null)
    {
        // simple case: handle given
        if ($handle !== null) {
            // get association, return null if failed
            $association = $this->connection->get(
                $this->associationKey($server_url, $handle));
            return $association ? $association : null;
        }

        // no handle given, working with list
        // create key for list of associations
        $serverKey = $this->associationServerKey($server_url);

        // get list of associations
        $serverAssociations = $this->connection->get($serverKey);
        // return null if failed or got empty list
        if (!$serverAssociations) {
            return null;
        }

        // get key of most recently issued association
        $keys = array_keys($serverAssociations);
        sort($keys);
        $lastKey = $serverAssociations[array_pop($keys)];

        // get association, return null if failed
        $association = $this->connection->get($lastKey);
        return $association ? $association : null;
    }

    /**
     * Immediately delete association from memcache.
     */
    function removeAssociation($server_url, $handle)
    {
        // create memcached keys for association itself
        // and list of associations for this server
        $serverKey = $this->associationServerKey($server_url);
        $associationKey = $this->associationKey($server_url,
            $handle);

        // get list of associations
        $serverAssociations = $this->connection->get($serverKey);
        // return null if failed or got empty list
        if (!$serverAssociations) {
            return false;
        }

        // ensure that given association key exists in list
        $serverAssociations = array_flip($serverAssociations);
        if (!array_key_exists($associationKey, $serverAssociations)) {
            return false;
        }

        // remove given association key from list
        unset($serverAssociations[$associationKey]);
        $serverAssociations = array_flip($serverAssociations);

        // save updated list
        $this->connection->set(
            $serverKey,
            $serverAssociations,
            $this->compress
        );

        // delete association
        return $this->connection->delete($associationKey);
    }

    /**
     * Create nonce for server and salt, expiring after
     * $Auth_OpenID_SKEW seconds.
     */
    function useNonce($server_url, $timestamp, $salt)
    {
        global $Auth_OpenID_SKEW;

        // save one request to memcache when nonce obviously expired
        if (abs($timestamp - time()) > $Auth_OpenID_SKEW) {
            return false;
        }

        // returns false when nonce already exists
        // otherwise adds nonce
        return $this->connection->add(
            'openid_nonce_' . sha1($server_url) . '_' . sha1($salt),
            1, // any value here
            $this->compress,
            $Auth_OpenID_SKEW);
    }

    /**
     * Memcache key is prefixed with 'openid_association_' string.
     */
    function associationKey($server_url, $handle = null)
    {
        return 'openid_association_' . sha1($server_url) . '_' . sha1($handle);
    }

    /**
     * Memcache key is prefixed with 'openid_association_' string.
     */
    function associationServerKey($server_url)
    {
        return 'openid_association_server_' . sha1($server_url);
    }

    /**
     * Report that this storage doesn't support cleanup
     */
    function supportsCleanup()
    {
        return false;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/ParanoidHTTPFetcher.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/ParanoidHTTPFetcher.php */ ?>
<?php

/**
 * This module contains the CURL-based HTTP fetcher implementation.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Interface import
 */
//require_once "Auth/Yadis/HTTPFetcher.php";

//require_once "Auth/OpenID.php";

/**
 * A paranoid {@link Auth_Yadis_HTTPFetcher} class which uses CURL
 * for fetching.
 *
 * @package OpenID
 */
class Auth_Yadis_ParanoidHTTPFetcher extends Auth_Yadis_HTTPFetcher {
    function Auth_Yadis_ParanoidHTTPFetcher()
    {
        $this->reset();
    }

    function reset()
    {
        $this->headers = array();
        $this->data = "";
    }

    /**
     * @access private
     */
    function _writeHeader($ch, $header)
    {
        array_push($this->headers, rtrim($header));
        return strlen($header);
    }

    /**
     * @access private
     */
    function _writeData($ch, $data)
    {
        if (strlen($this->data) > 1024*Auth_OpenID_FETCHER_MAX_RESPONSE_KB) {
            return 0;
        } else {
            $this->data .= $data;
            return strlen($data);
        }
    }

    /**
     * Does this fetcher support SSL URLs?
     */
    function supportsSSL()
    {
        $v = curl_version();
        if(is_array($v)) {
            return in_array('https', $v['protocols']);
        } elseif (is_string($v)) {
            return preg_match('/OpenSSL/i', $v);
        } else {
            return 0;
        }
    }

    function get($url, $extra_headers = null)
    {
        if (!$this->canFetchURL($url)) {
            return null;
        }

        $stop = time() + $this->timeout;
        $off = $this->timeout;

        $redir = true;

        while ($redir && ($off > 0)) {
            $this->reset();

            $c = curl_init();

            if ($c === false) {
                Auth_OpenID::log(
                    "curl_init returned false; could not " .
                    "initialize for URL '%s'", $url);
                return null;
            }

            if (defined('CURLOPT_NOSIGNAL')) {
                curl_setopt($c, CURLOPT_NOSIGNAL, true);
            }

            if (!$this->allowedURL($url)) {
                Auth_OpenID::log("Fetching URL not allowed: %s",
                                 $url);
                return null;
            }

            curl_setopt($c, CURLOPT_WRITEFUNCTION,
                        array(&$this, "_writeData"));
            curl_setopt($c, CURLOPT_HEADERFUNCTION,
                        array(&$this, "_writeHeader"));

            if ($extra_headers) {
                curl_setopt($c, CURLOPT_HTTPHEADER, $extra_headers);
            }

            $cv = curl_version();
            if(is_array($cv)) {
              $curl_user_agent = 'curl/'.$cv['version'];
            } else {
              $curl_user_agent = $cv;
            }
            curl_setopt($c, CURLOPT_USERAGENT,
                        Auth_OpenID_USER_AGENT.' '.$curl_user_agent);
            curl_setopt($c, CURLOPT_TIMEOUT, $off);
            curl_setopt($c, CURLOPT_URL, $url);
            curl_setopt($c, CURLOPT_RANGE,
                        "0-".(1024 * Auth_OpenID_FETCHER_MAX_RESPONSE_KB));

            curl_exec($c);

            $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
            $body = $this->data;
            $headers = $this->headers;

            if (!$code) {
                Auth_OpenID::log("Got no response code when fetching %s", $url);
                Auth_OpenID::log("CURL error (%s): %s",
                                 curl_errno($c), curl_error($c));
                return null;
            }

            if (in_array($code, array(301, 302, 303, 307))) {
                $url = $this->_findRedirect($headers);
                $redir = true;
            } else {
                $redir = false;
                curl_close($c);

                $new_headers = array();

                foreach ($headers as $header) {
                    if (preg_match("/:/", $header)) {
                        $parts = explode(": ", $header, 2);

                        if (count($parts) == 2) {
                            list($name, $value) = $parts;
                            $new_headers[$name] = $value;
                        }
                    }
                }

                Auth_OpenID::log(
                    "Successfully fetched '%s': GET response code %s",
                    $url, $code);

                return new Auth_Yadis_HTTPResponse($url, $code,
                                                    $new_headers, $body);
            }

            $off = $stop - time();
        }

        return null;
    }

    function post($url, $body, $extra_headers = null)
    {
        if (!$this->canFetchURL($url)) {
            return null;
        }

        $this->reset();

        $c = curl_init();

        if (defined('CURLOPT_NOSIGNAL')) {
            curl_setopt($c, CURLOPT_NOSIGNAL, true);
        }

        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $body);
        curl_setopt($c, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_WRITEFUNCTION,
                    array(&$this, "_writeData"));

        curl_exec($c);

        $code = curl_getinfo($c, CURLINFO_HTTP_CODE);

        if (!$code) {
            Auth_OpenID::log("Got no response code when fetching %s", $url);
            return null;
        }

        $body = $this->data;

        curl_close($c);

        if ($extra_headers === null) {
            $new_headers = null;
        } else {
            $new_headers = $extra_headers;
        }

        foreach ($this->headers as $header) {
            if (preg_match("/:/", $header)) {
                list($name, $value) = explode(": ", $header, 2);
                $new_headers[$name] = $value;
            }

        }

        Auth_OpenID::log("Successfully fetched '%s': POST response code %s",
                         $url, $code);

        return new Auth_Yadis_HTTPResponse($url, $code,
                                           $new_headers, $body);
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/PlainHTTPFetcher.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/PlainHTTPFetcher.php */ ?>
<?php

/**
 * This module contains the plain non-curl HTTP fetcher
 * implementation.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Interface import
 */
//require_once "Auth/Yadis/HTTPFetcher.php";

/**
 * This class implements a plain, hand-built socket-based fetcher
 * which will be used in the event that CURL is unavailable.
 *
 * @package OpenID
 */
class Auth_Yadis_PlainHTTPFetcher extends Auth_Yadis_HTTPFetcher {
    /**
     * Does this fetcher support SSL URLs?
     */
    function supportsSSL()
    {
        return function_exists('openssl_open');
    }

    function get($url, $extra_headers = null)
    {
        if (!$this->canFetchURL($url)) {
            return null;
        }

        $redir = true;

        $stop = time() + $this->timeout;
        $off = $this->timeout;

        while ($redir && ($off > 0)) {

            $parts = parse_url($url);

            $specify_port = true;

            // Set a default port.
            if (!array_key_exists('port', $parts)) {
                $specify_port = false;
                if ($parts['scheme'] == 'http') {
                    $parts['port'] = 80;
                } elseif ($parts['scheme'] == 'https') {
                    $parts['port'] = 443;
                } else {
                    return null;
                }
            }

            if (!array_key_exists('path', $parts)) {
                $parts['path'] = '/';
            }

            $host = $parts['host'];

            if ($parts['scheme'] == 'https') {
                $host = 'ssl://' . $host;
            }

            $user_agent = Auth_OpenID_USER_AGENT;

            $headers = array(
                             "GET ".$parts['path'].
                             (array_key_exists('query', $parts) ?
                              "?".$parts['query'] : "").
                                 " HTTP/1.0",
                             "User-Agent: $user_agent",
                             "Host: ".$parts['host'].
                                ($specify_port ? ":".$parts['port'] : ""),
                             "Range: 0-".
                                (1024*Auth_OpenID_FETCHER_MAX_RESPONSE_KB),
                             "Port: ".$parts['port']);

            $errno = 0;
            $errstr = '';

            if ($extra_headers) {
                foreach ($extra_headers as $h) {
                    $headers[] = $h;
                }
            }

            @$sock = fsockopen($host, $parts['port'], $errno, $errstr,
                               $this->timeout);
            if ($sock === false) {
                return false;
            }

            stream_set_timeout($sock, $this->timeout);

            fputs($sock, implode("\r\n", $headers) . "\r\n\r\n");

            $data = "";
            $kilobytes = 0;
            while (!feof($sock) &&
                   $kilobytes < Auth_OpenID_FETCHER_MAX_RESPONSE_KB ) {
                $data .= fgets($sock, 1024);
                $kilobytes += 1;
            }

            fclose($sock);

            // Split response into header and body sections
            list($headers, $body) = explode("\r\n\r\n", $data, 2);
            $headers = explode("\r\n", $headers);

            $http_code = explode(" ", $headers[0]);
            $code = $http_code[1];

            if (in_array($code, array('301', '302'))) {
                $url = $this->_findRedirect($headers);
                $redir = true;
            } else {
                $redir = false;
            }

            $off = $stop - time();
        }

        $new_headers = array();

        foreach ($headers as $header) {
            if (preg_match("/:/", $header)) {
                $parts = explode(": ", $header, 2);

                if (count($parts) == 2) {
                    list($name, $value) = $parts;
                    $new_headers[$name] = $value;
                }
            }

        }

        return new Auth_Yadis_HTTPResponse($url, $code, $new_headers, $body);
    }

    function post($url, $body, $extra_headers = null)
    {
        if (!$this->canFetchURL($url)) {
            return null;
        }

        $parts = parse_url($url);

        $headers = array();

        $post_path = $parts['path'];
        if (isset($parts['query'])) {
            $post_path .= '?' . $parts['query'];
        }

        $headers[] = "POST ".$post_path." HTTP/1.0";
        $headers[] = "Host: " . $parts['host'];
        $headers[] = "Content-type: application/x-www-form-urlencoded";
        $headers[] = "Content-length: " . strval(strlen($body));

        if ($extra_headers &&
            is_array($extra_headers)) {
            $headers = array_merge($headers, $extra_headers);
        }

        // Join all headers together.
        $all_headers = implode("\r\n", $headers);

        // Add headers, two newlines, and request body.
        $request = $all_headers . "\r\n\r\n" . $body;

        // Set a default port.
        if (!array_key_exists('port', $parts)) {
            if ($parts['scheme'] == 'http') {
                $parts['port'] = 80;
            } elseif ($parts['scheme'] == 'https') {
                $parts['port'] = 443;
            } else {
                return null;
            }
        }

        if ($parts['scheme'] == 'https') {
            $parts['host'] = sprintf("ssl://%s", $parts['host']);
        }

        // Connect to the remote server.
        $errno = 0;
        $errstr = '';

        $sock = fsockopen($parts['host'], $parts['port'], $errno, $errstr,
                          $this->timeout);

        if ($sock === false) {
            return null;
        }

        stream_set_timeout($sock, $this->timeout);

        // Write the POST request.
        fputs($sock, $request);

        // Get the response from the server.
        $response = "";
        while (!feof($sock)) {
            if ($data = fgets($sock, 128)) {
                $response .= $data;
            } else {
                break;
            }
        }

        // Split the request into headers and body.
        list($headers, $response_body) = explode("\r\n\r\n", $response, 2);

        $headers = explode("\r\n", $headers);

        // Expect the first line of the headers data to be something
        // like HTTP/1.1 200 OK.  Split the line on spaces and take
        // the second token, which should be the return code.
        $http_code = explode(" ", $headers[0]);
        $code = $http_code[1];

        $new_headers = array();

        foreach ($headers as $header) {
            if (preg_match("/:/", $header)) {
                list($name, $value) = explode(": ", $header, 2);
                $new_headers[$name] = $value;
            }

        }

        return new Auth_Yadis_HTTPResponse($url, $code,
                                           $new_headers, $response_body);
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/SQLStore.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/SQLStore.php */ ?>
<?php

/**
 * SQL-backed OpenID stores.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Require the PEAR DB module because we'll need it for the SQL-based
 * stores implemented here.  We silence any errors from the inclusion
 * because it might not be present, and a user of the SQL stores may
 * supply an Auth_OpenID_DatabaseConnection instance that implements
 * its own storage.
 */
global $__Auth_OpenID_PEAR_AVAILABLE;
$__Auth_OpenID_PEAR_AVAILABLE = @include_once 'DB.php';

/**
 * @access private
 */
//require_once 'Auth/OpenID/Interface.php';
//require_once 'Auth/OpenID/Nonce.php';

/**
 * @access private
 */
//require_once 'Auth/OpenID.php';

/**
 * @access private
 */
//require_once 'Auth/OpenID/Nonce.php';

/**
 * This is the parent class for the SQL stores, which contains the
 * logic common to all of the SQL stores.
 *
 * The table names used are determined by the class variables
 * associations_table_name and nonces_table_name.  To change the name
 * of the tables used, pass new table names into the constructor.
 *
 * To create the tables with the proper schema, see the createTables
 * method.
 *
 * This class shouldn't be used directly.  Use one of its subclasses
 * instead, as those contain the code necessary to use a specific
 * database.  If you're an OpenID integrator and you'd like to create
 * an SQL-driven store that wraps an application's database
 * abstraction, be sure to create a subclass of
 * {@link Auth_OpenID_DatabaseConnection} that calls the application's
 * database abstraction calls.  Then, pass an instance of your new
 * database connection class to your SQLStore subclass constructor.
 *
 * All methods other than the constructor and createTables should be
 * considered implementation details.
 *
 * @package OpenID
 */
class Auth_OpenID_SQLStore extends Auth_OpenID_OpenIDStore {

    /**
     * This creates a new SQLStore instance.  It requires an
     * established database connection be given to it, and it allows
     * overriding the default table names.
     *
     * @param connection $connection This must be an established
     * connection to a database of the correct type for the SQLStore
     * subclass you're using.  This must either be an PEAR DB
     * connection handle or an instance of a subclass of
     * Auth_OpenID_DatabaseConnection.
     *
     * @param associations_table: This is an optional parameter to
     * specify the name of the table used for storing associations.
     * The default value is 'oid_associations'.
     *
     * @param nonces_table: This is an optional parameter to specify
     * the name of the table used for storing nonces.  The default
     * value is 'oid_nonces'.
     */
    function Auth_OpenID_SQLStore($connection,
                                  $associations_table = null,
                                  $nonces_table = null)
    {
        global $__Auth_OpenID_PEAR_AVAILABLE;

        $this->associations_table_name = "oid_associations";
        $this->nonces_table_name = "oid_nonces";

        // Check the connection object type to be sure it's a PEAR
        // database connection.
        if (!(is_object($connection) &&
              (is_subclass_of($connection, 'db_common') ||
               is_subclass_of($connection,
                              'auth_openid_databaseconnection')))) {
            trigger_error("Auth_OpenID_SQLStore expected PEAR connection " .
                          "object (got ".get_class($connection).")",
                          E_USER_ERROR);
            return;
        }

        $this->connection = $connection;

        // Be sure to set the fetch mode so the results are keyed on
        // column name instead of column index.  This is a PEAR
        // constant, so only try to use it if PEAR is present.  Note
        // that Auth_Openid_Databaseconnection instances need not
        // implement ::setFetchMode for this reason.
        if ($__Auth_OpenID_PEAR_AVAILABLE) {
            $this->connection->setFetchMode(DB_FETCHMODE_ASSOC);
        }

        if ($associations_table) {
            $this->associations_table_name = $associations_table;
        }

        if ($nonces_table) {
            $this->nonces_table_name = $nonces_table;
        }

        $this->max_nonce_age = 6 * 60 * 60;

        // Be sure to run the database queries with auto-commit mode
        // turned OFF, because we want every function to run in a
        // transaction, implicitly.  As a rule, methods named with a
        // leading underscore will NOT control transaction behavior.
        // Callers of these methods will worry about transactions.
        $this->connection->autoCommit(false);

        // Create an empty SQL strings array.
        $this->sql = array();

        // Call this method (which should be overridden by subclasses)
        // to populate the $this->sql array with SQL strings.
        $this->setSQL();

        // Verify that all required SQL statements have been set, and
        // raise an error if any expected SQL strings were either
        // absent or empty.
        list($missing, $empty) = $this->_verifySQL();

        if ($missing) {
            trigger_error("Expected keys in SQL query list: " .
                          implode(", ", $missing),
                          E_USER_ERROR);
            return;
        }

        if ($empty) {
            trigger_error("SQL list keys have no SQL strings: " .
                          implode(", ", $empty),
                          E_USER_ERROR);
            return;
        }

        // Add table names to queries.
        $this->_fixSQL();
    }

    function tableExists($table_name)
    {
        return !$this->isError(
                      $this->connection->query(
                          sprintf("SELECT * FROM %s LIMIT 0",
                                  $table_name)));
    }

    /**
     * Returns true if $value constitutes a database error; returns
     * false otherwise.
     */
    function isError($value)
    {
        return PEAR::isError($value);
    }

    /**
     * Converts a query result to a boolean.  If the result is a
     * database error according to $this->isError(), this returns
     * false; otherwise, this returns true.
     */
    function resultToBool($obj)
    {
        if ($this->isError($obj)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * This method should be overridden by subclasses.  This method is
     * called by the constructor to set values in $this->sql, which is
     * an array keyed on sql name.
     */
    function setSQL()
    {
    }

    /**
     * Resets the store by removing all records from the store's
     * tables.
     */
    function reset()
    {
        $this->connection->query(sprintf("DELETE FROM %s",
                                         $this->associations_table_name));

        $this->connection->query(sprintf("DELETE FROM %s",
                                         $this->nonces_table_name));
    }

    /**
     * @access private
     */
    function _verifySQL()
    {
        $missing = array();
        $empty = array();

        $required_sql_keys = array(
                                   'nonce_table',
                                   'assoc_table',
                                   'set_assoc',
                                   'get_assoc',
                                   'get_assocs',
                                   'remove_assoc'
                                   );

        foreach ($required_sql_keys as $key) {
            if (!array_key_exists($key, $this->sql)) {
                $missing[] = $key;
            } else if (!$this->sql[$key]) {
                $empty[] = $key;
            }
        }

        return array($missing, $empty);
    }

    /**
     * @access private
     */
    function _fixSQL()
    {
        $replacements = array(
                              array(
                                    'value' => $this->nonces_table_name,
                                    'keys' => array('nonce_table',
                                                    'add_nonce',
                                                    'clean_nonce')
                                    ),
                              array(
                                    'value' => $this->associations_table_name,
                                    'keys' => array('assoc_table',
                                                    'set_assoc',
                                                    'get_assoc',
                                                    'get_assocs',
                                                    'remove_assoc',
                                                    'clean_assoc')
                                    )
                              );

        foreach ($replacements as $item) {
            $value = $item['value'];
            $keys = $item['keys'];

            foreach ($keys as $k) {
                if (is_array($this->sql[$k])) {
                    foreach ($this->sql[$k] as $part_key => $part_value) {
                        $this->sql[$k][$part_key] = sprintf($part_value,
                                                            $value);
                    }
                } else {
                    $this->sql[$k] = sprintf($this->sql[$k], $value);
                }
            }
        }
    }

    function blobDecode($blob)
    {
        return $blob;
    }

    function blobEncode($str)
    {
        return $str;
    }

    function createTables()
    {
        $this->connection->autoCommit(true);
        $n = $this->create_nonce_table();
        $a = $this->create_assoc_table();
        $this->connection->autoCommit(false);

        if ($n && $a) {
            return true;
        } else {
            return false;
        }
    }

    function create_nonce_table()
    {
        if (!$this->tableExists($this->nonces_table_name)) {
            $r = $this->connection->query($this->sql['nonce_table']);
            return $this->resultToBool($r);
        }
        return true;
    }

    function create_assoc_table()
    {
        if (!$this->tableExists($this->associations_table_name)) {
            $r = $this->connection->query($this->sql['assoc_table']);
            return $this->resultToBool($r);
        }
        return true;
    }

    /**
     * @access private
     */
    function _set_assoc($server_url, $handle, $secret, $issued,
                        $lifetime, $assoc_type)
    {
        return $this->connection->query($this->sql['set_assoc'],
                                        array(
                                              $server_url,
                                              $handle,
                                              $secret,
                                              $issued,
                                              $lifetime,
                                              $assoc_type));
    }

    function storeAssociation($server_url, $association)
    {
        if ($this->resultToBool($this->_set_assoc(
                                            $server_url,
                                            $association->handle,
                                            $this->blobEncode(
                                                  $association->secret),
                                            $association->issued,
                                            $association->lifetime,
                                            $association->assoc_type
                                            ))) {
            $this->connection->commit();
        } else {
            $this->connection->rollback();
        }
    }

    /**
     * @access private
     */
    function _get_assoc($server_url, $handle)
    {
        $result = $this->connection->getRow($this->sql['get_assoc'],
                                            array($server_url, $handle));
        if ($this->isError($result)) {
            return null;
        } else {
            return $result;
        }
    }

    /**
     * @access private
     */
    function _get_assocs($server_url)
    {
        $result = $this->connection->getAll($this->sql['get_assocs'],
                                            array($server_url));

        if ($this->isError($result)) {
            return array();
        } else {
            return $result;
        }
    }

    function removeAssociation($server_url, $handle)
    {
        if ($this->_get_assoc($server_url, $handle) == null) {
            return false;
        }

        if ($this->resultToBool($this->connection->query(
                              $this->sql['remove_assoc'],
                              array($server_url, $handle)))) {
            $this->connection->commit();
        } else {
            $this->connection->rollback();
        }

        return true;
    }

    function getAssociation($server_url, $handle = null)
    {
        if ($handle !== null) {
            $assoc = $this->_get_assoc($server_url, $handle);

            $assocs = array();
            if ($assoc) {
                $assocs[] = $assoc;
            }
        } else {
            $assocs = $this->_get_assocs($server_url);
        }

        if (!$assocs || (count($assocs) == 0)) {
            return null;
        } else {
            $associations = array();

            foreach ($assocs as $assoc_row) {
                $assoc = new Auth_OpenID_Association($assoc_row['handle'],
                                                     $assoc_row['secret'],
                                                     $assoc_row['issued'],
                                                     $assoc_row['lifetime'],
                                                     $assoc_row['assoc_type']);

                $assoc->secret = $this->blobDecode($assoc->secret);

                if ($assoc->getExpiresIn() == 0) {
                    $this->removeAssociation($server_url, $assoc->handle);
                } else {
                    $associations[] = array($assoc->issued, $assoc);
                }
            }

            if ($associations) {
                $issued = array();
                $assocs = array();
                foreach ($associations as $key => $assoc) {
                    $issued[$key] = $assoc[0];
                    $assocs[$key] = $assoc[1];
                }

                array_multisort($issued, SORT_DESC, $assocs, SORT_DESC,
                                $associations);

                // return the most recently issued one.
                list($issued, $assoc) = $associations[0];
                return $assoc;
            } else {
                return null;
            }
        }
    }

    /**
     * @access private
     */
    function _add_nonce($server_url, $timestamp, $salt)
    {
        $sql = $this->sql['add_nonce'];
        $result = $this->connection->query($sql, array($server_url,
                                                       $timestamp,
                                                       $salt));
        if ($this->isError($result)) {
            $this->connection->rollback();
        } else {
            $this->connection->commit();
        }
        return $this->resultToBool($result);
    }

    function useNonce($server_url, $timestamp, $salt)
    {
        global $Auth_OpenID_SKEW;

        if ( abs($timestamp - time()) > $Auth_OpenID_SKEW ) {
            return False;
        }

        return $this->_add_nonce($server_url, $timestamp, $salt);
    }

    /**
     * "Octifies" a binary string by returning a string with escaped
     * octal bytes.  This is used for preparing binary data for
     * PostgreSQL BYTEA fields.
     *
     * @access private
     */
    function _octify($str)
    {
        $result = "";
        for ($i = 0; $i < Auth_OpenID::bytes($str); $i++) {
            $ch = substr($str, $i, 1);
            if ($ch == "\\") {
                $result .= "\\\\\\\\";
            } else if (ord($ch) == 0) {
                $result .= "\\\\000";
            } else {
                $result .= "\\" . strval(decoct(ord($ch)));
            }
        }
        return $result;
    }

    /**
     * "Unoctifies" octal-escaped data from PostgreSQL and returns the
     * resulting ASCII (possibly binary) string.
     *
     * @access private
     */
    function _unoctify($str)
    {
        $result = "";
        $i = 0;
        while ($i < strlen($str)) {
            $char = $str[$i];
            if ($char == "\\") {
                // Look to see if the next char is a backslash and
                // append it.
                if ($str[$i + 1] != "\\") {
                    $octal_digits = substr($str, $i + 1, 3);
                    $dec = octdec($octal_digits);
                    $char = chr($dec);
                    $i += 4;
                } else {
                    $char = "\\";
                    $i += 2;
                }
            } else {
                $i += 1;
            }

            $result .= $char;
        }

        return $result;
    }

    function cleanupNonces()
    {
        global $Auth_OpenID_SKEW;
        $v = time() - $Auth_OpenID_SKEW;

        $this->connection->query($this->sql['clean_nonce'], array($v));
        $num = $this->connection->affectedRows();
        $this->connection->commit();
        return $num;
    }

    function cleanupAssociations()
    {
        $this->connection->query($this->sql['clean_assoc'],
                                 array(time()));
        $num = $this->connection->affectedRows();
        $this->connection->commit();
        return $num;
    }
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/TrustRoot.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/TrustRoot.php */ ?>
<?php
/**
 * Functions for dealing with OpenID trust roots
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

//require_once 'Auth/OpenID/Discover.php';

/**
 * A regular expression that matches a domain ending in a top-level domains.
 * Used in checking trust roots for sanity.
 *
 * @access private
 */
define('Auth_OpenID___TLDs',
       '/\.(com|edu|gov|int|mil|net|org|biz|info|name|museum|coop|aero|ac|' .
       'ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|az|ba|bb|bd|be|bf|bg|' .
       'bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|' .
       'cm|cn|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|ee|eg|eh|er|es|et|eu|' .
       'fi|fj|fk|fm|fo|fr|ga|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|' .
       'gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|' .
       'ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|' .
       'ma|mc|md|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|' .
       'nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|' .
       'ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|' .
       'so|sr|st|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|' .
       'ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)' .
       '\.?$/');

define('Auth_OpenID___HostSegmentRe',
       "/^(?:[-a-zA-Z0-9!$&'\\(\\)\\*+,;=._~]|%[a-zA-Z0-9]{2})*$/");

/**
 * A wrapper for trust-root related functions
 */
class Auth_OpenID_TrustRoot {
    /*
     * Return a discovery URL for this realm.
     *
     * Return null if the realm could not be parsed or was not valid.
     *
     * @param return_to The relying party return URL of the OpenID
     * authentication request
     *
     * @return The URL upon which relying party discovery should be
     * run in order to verify the return_to URL
     */
    function buildDiscoveryURL($realm)
    {
        $parsed = Auth_OpenID_TrustRoot::_parse($realm);

        if ($parsed === false) {
            return false;
        }

        if ($parsed['wildcard']) {
            // Use "www." in place of the star
            if ($parsed['host'][0] != '.') {
                return false;
            }

            $www_domain = 'www' . $parsed['host'];

            return sprintf('%s://%s%s', $parsed['scheme'],
                           $www_domain, $parsed['path']);
        } else {
            return $parsed['unparsed'];
        }
    }

    /**
     * Parse a URL into its trust_root parts.
     *
     * @static
     *
     * @access private
     *
     * @param string $trust_root The url to parse
     *
     * @return mixed $parsed Either an associative array of trust root
     * parts or false if parsing failed.
     */
    function _parse($trust_root)
    {
        $trust_root = Auth_OpenID_urinorm($trust_root);
        if ($trust_root === null) {
            return false;
        }

        if (preg_match("/:\/\/[^:]+(:\d+){2,}(\/|$)/", $trust_root)) {
            return false;
        }

        $parts = @parse_url($trust_root);
        if ($parts === false) {
            return false;
        }

        $required_parts = array('scheme', 'host');
        $forbidden_parts = array('user', 'pass', 'fragment');
        $keys = array_keys($parts);
        if (array_intersect($keys, $required_parts) != $required_parts) {
            return false;
        }

        if (array_intersect($keys, $forbidden_parts) != array()) {
            return false;
        }

        if (!preg_match(Auth_OpenID___HostSegmentRe, $parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        $allowed_schemes = array('http', 'https');
        if (!in_array($scheme, $allowed_schemes)) {
            return false;
        }
        $parts['scheme'] = $scheme;

        $host = strtolower($parts['host']);
        $hostparts = explode('*', $host);
        switch (count($hostparts)) {
        case 1:
            $parts['wildcard'] = false;
            break;
        case 2:
            if ($hostparts[0] ||
                ($hostparts[1] && substr($hostparts[1], 0, 1) != '.')) {
                return false;
            }
            $host = $hostparts[1];
            $parts['wildcard'] = true;
            break;
        default:
            return false;
        }
        if (strpos($host, ':') !== false) {
            return false;
        }

        $parts['host'] = $host;

        if (isset($parts['path'])) {
            $path = strtolower($parts['path']);
            if (substr($path, 0, 1) != '/') {
                return false;
            }
        } else {
            $path = '/';
        }

        $parts['path'] = $path;
        if (!isset($parts['port'])) {
            $parts['port'] = false;
        }


        $parts['unparsed'] = $trust_root;

        return $parts;
    }

    /**
     * Is this trust root sane?
     *
     * A trust root is sane if it is syntactically valid and it has a
     * reasonable domain name. Specifically, the domain name must be
     * more than one level below a standard TLD or more than two
     * levels below a two-letter tld.
     *
     * For example, '*.com' is not a sane trust root, but '*.foo.com'
     * is.  '*.co.uk' is not sane, but '*.bbc.co.uk' is.
     *
     * This check is not always correct, but it attempts to err on the
     * side of marking sane trust roots insane instead of marking
     * insane trust roots sane. For example, 'kink.fm' is marked as
     * insane even though it "should" (for some meaning of should) be
     * marked sane.
     *
     * This function should be used when creating OpenID servers to
     * alert the users of the server when a consumer attempts to get
     * the user to accept a suspicious trust root.
     *
     * @static
     * @param string $trust_root The trust root to check
     * @return bool $sanity Whether the trust root looks OK
     */
    function isSane($trust_root)
    {
        $parts = Auth_OpenID_TrustRoot::_parse($trust_root);
        if ($parts === false) {
            return false;
        }

        // Localhost is a special case
        if ($parts['host'] == 'localhost') {
            return true;
        }

        $host_parts = explode('.', $parts['host']);
        if ($parts['wildcard']) {
            // Remove the empty string from the beginning of the array
            array_shift($host_parts);
        }

        if ($host_parts && !$host_parts[count($host_parts) - 1]) {
            array_pop($host_parts);
        }

        if (!$host_parts) {
            return false;
        }

        // Don't allow adjacent dots
        if (in_array('', $host_parts, true)) {
            return false;
        }

        // Get the top-level domain of the host. If it is not a valid TLD,
        // it's not sane.
        preg_match(Auth_OpenID___TLDs, $parts['host'], $matches);
        if (!$matches) {
            return false;
        }
        $tld = $matches[1];

        if (count($host_parts) == 1) {
            return false;
        }

        if ($parts['wildcard']) {
            // It's a 2-letter tld with a short second to last segment
            // so there needs to be more than two segments specified
            // (e.g. *.co.uk is insane)
            $second_level = $host_parts[count($host_parts) - 2];
            if (strlen($tld) == 2 && strlen($second_level) <= 3) {
                return count($host_parts) > 2;
            }
        }

        return true;
    }

    /**
     * Does this URL match the given trust root?
     *
     * Return whether the URL falls under the given trust root. This
     * does not check whether the trust root is sane. If the URL or
     * trust root do not parse, this function will return false.
     *
     * @param string $trust_root The trust root to match against
     *
     * @param string $url The URL to check
     *
     * @return bool $matches Whether the URL matches against the
     * trust root
     */
    function match($trust_root, $url)
    {
        $trust_root_parsed = Auth_OpenID_TrustRoot::_parse($trust_root);
        $url_parsed = Auth_OpenID_TrustRoot::_parse($url);
        if (!$trust_root_parsed || !$url_parsed) {
            return false;
        }

        // Check hosts matching
        if ($url_parsed['wildcard']) {
            return false;
        }
        if ($trust_root_parsed['wildcard']) {
            $host_tail = $trust_root_parsed['host'];
            $host = $url_parsed['host'];
            if ($host_tail &&
                substr($host, -(strlen($host_tail))) != $host_tail &&
                substr($host_tail, 1) != $host) {
                return false;
            }
        } else {
            if ($trust_root_parsed['host'] != $url_parsed['host']) {
                return false;
            }
        }

        // Check path and query matching
        $base_path = $trust_root_parsed['path'];
        $path = $url_parsed['path'];
        if (!isset($trust_root_parsed['query'])) {
            if ($base_path != $path) {
                if (substr($path, 0, strlen($base_path)) != $base_path) {
                    return false;
                }
                if (substr($base_path, strlen($base_path) - 1, 1) != '/' &&
                    substr($path, strlen($base_path), 1) != '/') {
                    return false;
                }
            }
        } else {
            $base_query = $trust_root_parsed['query'];
            $query = @$url_parsed['query'];
            $qplus = substr($query, 0, strlen($base_query) + 1);
            $bqplus = $base_query . '&';
            if ($base_path != $path ||
                ($base_query != $query && $qplus != $bqplus)) {
                return false;
            }
        }

        // The port and scheme need to match exactly
        return ($trust_root_parsed['scheme'] == $url_parsed['scheme'] &&
                $url_parsed['port'] === $trust_root_parsed['port']);
    }
}

/*
 * If the endpoint is a relying party OpenID return_to endpoint,
 * return the endpoint URL. Otherwise, return None.
 *
 * This function is intended to be used as a filter for the Yadis
 * filtering interface.
 *
 * @see: C{L{openid.yadis.services}}
 * @see: C{L{openid.yadis.filters}}
 *
 * @param endpoint: An XRDS BasicServiceEndpoint, as returned by
 * performing Yadis dicovery.
 *
 * @returns: The endpoint URL or None if the endpoint is not a
 * relying party endpoint.
 */
function filter_extractReturnURL(&$endpoint)
{
    if ($endpoint->matchTypes(array(Auth_OpenID_RP_RETURN_TO_URL_TYPE))) {
        return $endpoint;
    } else {
        return null;
    }
}

function &Auth_OpenID_extractReturnURL(&$endpoint_list)
{
    $result = array();

    foreach ($endpoint_list as $endpoint) {
        if (filter_extractReturnURL($endpoint)) {
            $result[] = $endpoint;
        }
    }

    return $result;
}

/*
 * Is the return_to URL under one of the supplied allowed return_to
 * URLs?
 */
function Auth_OpenID_returnToMatches($allowed_return_to_urls, $return_to)
{
    foreach ($allowed_return_to_urls as $allowed_return_to) {
        // A return_to pattern works the same as a realm, except that
        // it's not allowed to use a wildcard. We'll model this by
        // parsing it as a realm, and not trying to match it if it has
        // a wildcard.

        $return_realm = Auth_OpenID_TrustRoot::_parse($allowed_return_to);
        if (// Parses as a trust root
            ($return_realm !== false) &&
            // Does not have a wildcard
            (!$return_realm['wildcard']) &&
            // Matches the return_to that we passed in with it
            (Auth_OpenID_TrustRoot::match($allowed_return_to, $return_to))) {
            return true;
        }
    }

    // No URL in the list matched
    return false;
}

/*
 * Given a relying party discovery URL return a list of return_to
 * URLs.
 */
function Auth_OpenID_getAllowedReturnURLs($relying_party_url, &$fetcher,
              $discover_function=null)
{
    if ($discover_function === null) {
        $discover_function = array('Auth_Yadis_Yadis', 'discover');
    }

    $xrds_parse_cb = array('Auth_OpenID_ServiceEndpoint', 'fromXRDS');

    list($rp_url_after_redirects, $endpoints) =
        Auth_Yadis_getServiceEndpoints($relying_party_url, $xrds_parse_cb,
                                       $discover_function, $fetcher);

    if ($rp_url_after_redirects != $relying_party_url) {
        // Verification caused a redirect
        return false;
    }

    call_user_func_array($discover_function,
                         array($relying_party_url, $fetcher));

    $return_to_urls = array();
    $matching_endpoints = Auth_OpenID_extractReturnURL($endpoints);

    foreach ($matching_endpoints as $e) {
        $return_to_urls[] = $e->server_url;
    }

    return $return_to_urls;
}

/*
 * Verify that a return_to URL is valid for the given realm.
 *
 * This function builds a discovery URL, performs Yadis discovery on
 * it, makes sure that the URL does not redirect, parses out the
 * return_to URLs, and finally checks to see if the current return_to
 * URL matches the return_to.
 *
 * @return true if the return_to URL is valid for the realm
 */
function Auth_OpenID_verifyReturnTo($realm_str, $return_to, &$fetcher,
              $_vrfy='Auth_OpenID_getAllowedReturnURLs')
{
    $disco_url = Auth_OpenID_TrustRoot::buildDiscoveryURL($realm_str);

    if ($disco_url === false) {
        return false;
    }

    $allowable_urls = call_user_func_array($_vrfy,
                           array($disco_url, &$fetcher));

    // The realm_str could not be parsed.
    if ($allowable_urls === false) {
        return false;
    }

    if (Auth_OpenID_returnToMatches($allowable_urls, $return_to)) {
        return true;
    } else {
        return false;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/XRDS.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/XRDS.php */ ?>
<?php

/**
 * This module contains the XRDS parsing code.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Require the XPath implementation.
 */
//require_once 'Auth/Yadis/XML.php';

/**
 * This match mode means a given service must match ALL filters passed
 * to the Auth_Yadis_XRDS::services() call.
 */
define('SERVICES_YADIS_MATCH_ALL', 101);

/**
 * This match mode means a given service must match ANY filters (at
 * least one) passed to the Auth_Yadis_XRDS::services() call.
 */
define('SERVICES_YADIS_MATCH_ANY', 102);

/**
 * The priority value used for service elements with no priority
 * specified.
 */
define('SERVICES_YADIS_MAX_PRIORITY', pow(2, 30));

/**
 * XRD XML namespace
 */
define('Auth_Yadis_XMLNS_XRD_2_0', 'xri://$xrd*($v*2.0)');

/**
 * XRDS XML namespace
 */
define('Auth_Yadis_XMLNS_XRDS', 'xri://$xrds');

function Auth_Yadis_getNSMap()
{
    return array('xrds' => Auth_Yadis_XMLNS_XRDS,
                 'xrd' => Auth_Yadis_XMLNS_XRD_2_0);
}

/**
 * @access private
 */
function Auth_Yadis_array_scramble($arr)
{
    $result = array();

    while (count($arr)) {
        $index = array_rand($arr, 1);
        $result[] = $arr[$index];
        unset($arr[$index]);
    }

    return $result;
}

/**
 * This class represents a <Service> element in an XRDS document.
 * Objects of this type are returned by
 * Auth_Yadis_XRDS::services() and
 * Auth_Yadis_Yadis::services().  Each object corresponds directly
 * to a <Service> element in the XRDS and supplies a
 * getElements($name) method which you should use to inspect the
 * element's contents.  See {@link Auth_Yadis_Yadis} for more
 * information on the role this class plays in Yadis discovery.
 *
 * @package OpenID
 */
class Auth_Yadis_Service {

    /**
     * Creates an empty service object.
     */
    function Auth_Yadis_Service()
    {
        $this->element = null;
        $this->parser = null;
    }

    /**
     * Return the URIs in the "Type" elements, if any, of this Service
     * element.
     *
     * @return array $type_uris An array of Type URI strings.
     */
    function getTypes()
    {
        $t = array();
        foreach ($this->getElements('xrd:Type') as $elem) {
            $c = $this->parser->content($elem);
            if ($c) {
                $t[] = $c;
            }
        }
        return $t;
    }

    function matchTypes($type_uris)
    {
        $result = array();

        foreach ($this->getTypes() as $typ) {
            if (in_array($typ, $type_uris)) {
                $result[] = $typ;
            }
        }

        return $result;
    }

    /**
     * Return the URIs in the "URI" elements, if any, of this Service
     * element.  The URIs are returned sorted in priority order.
     *
     * @return array $uris An array of URI strings.
     */
    function getURIs()
    {
        $uris = array();
        $last = array();

        foreach ($this->getElements('xrd:URI') as $elem) {
            $uri_string = $this->parser->content($elem);
            $attrs = $this->parser->attributes($elem);
            if ($attrs &&
                array_key_exists('priority', $attrs)) {
                $priority = intval($attrs['priority']);
                if (!array_key_exists($priority, $uris)) {
                    $uris[$priority] = array();
                }

                $uris[$priority][] = $uri_string;
            } else {
                $last[] = $uri_string;
            }
        }

        $keys = array_keys($uris);
        sort($keys);

        // Rebuild array of URIs.
        $result = array();
        foreach ($keys as $k) {
            $new_uris = Auth_Yadis_array_scramble($uris[$k]);
            $result = array_merge($result, $new_uris);
        }

        $result = array_merge($result,
                              Auth_Yadis_array_scramble($last));

        return $result;
    }

    /**
     * Returns the "priority" attribute value of this <Service>
     * element, if the attribute is present.  Returns null if not.
     *
     * @return mixed $result Null or integer, depending on whether
     * this Service element has a 'priority' attribute.
     */
    function getPriority()
    {
        $attributes = $this->parser->attributes($this->element);

        if (array_key_exists('priority', $attributes)) {
            return intval($attributes['priority']);
        }

        return null;
    }

    /**
     * Used to get XML elements from this object's <Service> element.
     *
     * This is what you should use to get all custom information out
     * of this element. This is used by service filter functions to
     * determine whether a service element contains specific tags,
     * etc.  NOTE: this only considers elements which are direct
     * children of the <Service> element for this object.
     *
     * @param string $name The name of the element to look for
     * @return array $list An array of elements with the specified
     * name which are direct children of the <Service> element.  The
     * nodes returned by this function can be passed to $this->parser
     * methods (see {@link Auth_Yadis_XMLParser}).
     */
    function getElements($name)
    {
        return $this->parser->evalXPath($name, $this->element);
    }
}

/*
 * Return the expiration date of this XRD element, or None if no
 * expiration was specified.
 *
 * @param $default The value to use as the expiration if no expiration
 * was specified in the XRD.
 */
function Auth_Yadis_getXRDExpiration($xrd_element, $default=null)
{
    $expires_element = $xrd_element->$parser->evalXPath('/xrd:Expires');
    if ($expires_element === null) {
        return $default;
    } else {
        $expires_string = $expires_element->text;

        // Will raise ValueError if the string is not the expected
        // format
        $t = strptime($expires_string, "%Y-%m-%dT%H:%M:%SZ");

        if ($t === false) {
            return false;
        }

        // [int $hour [, int $minute [, int $second [,
        //  int $month [, int $day [, int $year ]]]]]]
        return mktime($t['tm_hour'], $t['tm_min'], $t['tm_sec'],
                      $t['tm_mon'], $t['tm_day'], $t['tm_year']);
    }
}

/**
 * This class performs parsing of XRDS documents.
 *
 * You should not instantiate this class directly; rather, call
 * parseXRDS statically:
 *
 * <pre>  $xrds = Auth_Yadis_XRDS::parseXRDS($xml_string);</pre>
 *
 * If the XRDS can be parsed and is valid, an instance of
 * Auth_Yadis_XRDS will be returned.  Otherwise, null will be
 * returned.  This class is used by the Auth_Yadis_Yadis::discover
 * method.
 *
 * @package OpenID
 */
class Auth_Yadis_XRDS {

    /**
     * Instantiate a Auth_Yadis_XRDS object.  Requires an XPath
     * instance which has been used to parse a valid XRDS document.
     */
    function Auth_Yadis_XRDS(&$xmlParser, &$xrdNodes)
    {
        $this->parser =& $xmlParser;
        $this->xrdNode = $xrdNodes[count($xrdNodes) - 1];
        $this->allXrdNodes =& $xrdNodes;
        $this->serviceList = array();
        $this->_parse();
    }

    /**
     * Parse an XML string (XRDS document) and return either a
     * Auth_Yadis_XRDS object or null, depending on whether the
     * XRDS XML is valid.
     *
     * @param string $xml_string An XRDS XML string.
     * @return mixed $xrds An instance of Auth_Yadis_XRDS or null,
     * depending on the validity of $xml_string
     */
    function &parseXRDS($xml_string, $extra_ns_map = null)
    {
        $_null = null;

        if (!$xml_string) {
            return $_null;
        }

        $parser = Auth_Yadis_getXMLParser();

        $ns_map = Auth_Yadis_getNSMap();

        if ($extra_ns_map && is_array($extra_ns_map)) {
            $ns_map = array_merge($ns_map, $extra_ns_map);
        }

        if (!($parser && $parser->init($xml_string, $ns_map))) {
            return $_null;
        }

        // Try to get root element.
        $root = $parser->evalXPath('/xrds:XRDS[1]');
        if (!$root) {
            return $_null;
        }

        if (is_array($root)) {
            $root = $root[0];
        }

        $attrs = $parser->attributes($root);

        if (array_key_exists('xmlns:xrd', $attrs) &&
            $attrs['xmlns:xrd'] != Auth_Yadis_XMLNS_XRDS) {
            return $_null;
        } else if (array_key_exists('xmlns', $attrs) &&
                   preg_match('/xri/', $attrs['xmlns']) &&
                   $attrs['xmlns'] != Auth_Yadis_XMLNS_XRD_2_0) {
            return $_null;
        }

        // Get the last XRD node.
        $xrd_nodes = $parser->evalXPath('/xrds:XRDS[1]/xrd:XRD');

        if (!$xrd_nodes) {
            return $_null;
        }

        $xrds = new Auth_Yadis_XRDS($parser, $xrd_nodes);
        return $xrds;
    }

    /**
     * @access private
     */
    function _addService($priority, $service)
    {
        $priority = intval($priority);

        if (!array_key_exists($priority, $this->serviceList)) {
            $this->serviceList[$priority] = array();
        }

        $this->serviceList[$priority][] = $service;
    }

    /**
     * Creates the service list using nodes from the XRDS XML
     * document.
     *
     * @access private
     */
    function _parse()
    {
        $this->serviceList = array();

        $services = $this->parser->evalXPath('xrd:Service', $this->xrdNode);

        foreach ($services as $node) {
            $s =& new Auth_Yadis_Service();
            $s->element = $node;
            $s->parser =& $this->parser;

            $priority = $s->getPriority();

            if ($priority === null) {
                $priority = SERVICES_YADIS_MAX_PRIORITY;
            }

            $this->_addService($priority, $s);
        }
    }

    /**
     * Returns a list of service objects which correspond to <Service>
     * elements in the XRDS XML document for this object.
     *
     * Optionally, an array of filter callbacks may be given to limit
     * the list of returned service objects.  Furthermore, the default
     * mode is to return all service objects which match ANY of the
     * specified filters, but $filter_mode may be
     * SERVICES_YADIS_MATCH_ALL if you want to be sure that the
     * returned services match all the given filters.  See {@link
     * Auth_Yadis_Yadis} for detailed usage information on filter
     * functions.
     *
     * @param mixed $filters An array of callbacks to filter the
     * returned services, or null if all services are to be returned.
     * @param integer $filter_mode SERVICES_YADIS_MATCH_ALL or
     * SERVICES_YADIS_MATCH_ANY, depending on whether the returned
     * services should match ALL or ANY of the specified filters,
     * respectively.
     * @return mixed $services An array of {@link
     * Auth_Yadis_Service} objects if $filter_mode is a valid
     * mode; null if $filter_mode is an invalid mode (i.e., not
     * SERVICES_YADIS_MATCH_ANY or SERVICES_YADIS_MATCH_ALL).
     */
    function services($filters = null,
                      $filter_mode = SERVICES_YADIS_MATCH_ANY)
    {

        $pri_keys = array_keys($this->serviceList);
        sort($pri_keys, SORT_NUMERIC);

        // If no filters are specified, return the entire service
        // list, ordered by priority.
        if (!$filters ||
            (!is_array($filters))) {

            $result = array();
            foreach ($pri_keys as $pri) {
                $result = array_merge($result, $this->serviceList[$pri]);
            }

            return $result;
        }

        // If a bad filter mode is specified, return null.
        if (!in_array($filter_mode, array(SERVICES_YADIS_MATCH_ANY,
                                          SERVICES_YADIS_MATCH_ALL))) {
            return null;
        }

        // Otherwise, use the callbacks in the filter list to
        // determine which services are returned.
        $filtered = array();

        foreach ($pri_keys as $priority_value) {
            $service_obj_list = $this->serviceList[$priority_value];

            foreach ($service_obj_list as $service) {

                $matches = 0;

                foreach ($filters as $filter) {
                    if (call_user_func_array($filter, array($service))) {
                        $matches++;

                        if ($filter_mode == SERVICES_YADIS_MATCH_ANY) {
                            $pri = $service->getPriority();
                            if ($pri === null) {
                                $pri = SERVICES_YADIS_MAX_PRIORITY;
                            }

                            if (!array_key_exists($pri, $filtered)) {
                                $filtered[$pri] = array();
                            }

                            $filtered[$pri][] = $service;
                            break;
                        }
                    }
                }

                if (($filter_mode == SERVICES_YADIS_MATCH_ALL) &&
                    ($matches == count($filters))) {

                    $pri = $service->getPriority();
                    if ($pri === null) {
                        $pri = SERVICES_YADIS_MAX_PRIORITY;
                    }

                    if (!array_key_exists($pri, $filtered)) {
                        $filtered[$pri] = array();
                    }
                    $filtered[$pri][] = $service;
                }
            }
        }

        $pri_keys = array_keys($filtered);
        sort($pri_keys, SORT_NUMERIC);

        $result = array();
        foreach ($pri_keys as $pri) {
            $result = array_merge($result, $filtered[$pri]);
        }

        return $result;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/2/DiffieHellman.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/2/DiffieHellman.php */ ?>
<?php

/**
 * The OpenID library's Diffie-Hellman implementation.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @access private
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

//require_once 'Auth/OpenID.php';
//require_once 'Auth/OpenID/BigMath.php';
//require_once 'Auth/OpenID/HMACSHA1.php';

function Auth_OpenID_getDefaultMod()
{
    return '155172898181473697471232257763715539915724801'.
        '966915404479707795314057629378541917580651227423'.
        '698188993727816152646631438561595825688188889951'.
        '272158842675419950341258706556549803580104870537'.
        '681476726513255747040765857479291291572334510643'.
        '245094715007229621094194349783925984760375594985'.
        '848253359305585439638443';
}

function Auth_OpenID_getDefaultGen()
{
    return '2';
}

/**
 * The Diffie-Hellman key exchange class.  This class relies on
 * {@link Auth_OpenID_MathLibrary} to perform large number operations.
 *
 * @access private
 * @package OpenID
 */
class Auth_OpenID_DiffieHellman {

    var $mod;
    var $gen;
    var $private;
    var $lib = null;

    function Auth_OpenID_DiffieHellman($mod = null, $gen = null,
                                       $private = null, $lib = null)
    {
        if ($lib === null) {
            $this->lib =& Auth_OpenID_getMathLib();
        } else {
            $this->lib =& $lib;
        }

        if ($mod === null) {
            $this->mod = $this->lib->init(Auth_OpenID_getDefaultMod());
        } else {
            $this->mod = $mod;
        }

        if ($gen === null) {
            $this->gen = $this->lib->init(Auth_OpenID_getDefaultGen());
        } else {
            $this->gen = $gen;
        }

        if ($private === null) {
            $r = $this->lib->rand($this->mod);
            $this->private = $this->lib->add($r, 1);
        } else {
            $this->private = $private;
        }

        $this->public = $this->lib->powmod($this->gen, $this->private,
                                           $this->mod);
    }

    function getSharedSecret($composite)
    {
        return $this->lib->powmod($composite, $this->private, $this->mod);
    }

    function getPublicKey()
    {
        return $this->public;
    }

    function usingDefaultValues()
    {
        return ($this->mod == Auth_OpenID_getDefaultMod() &&
                $this->gen == Auth_OpenID_getDefaultGen());
    }

    function xorSecret($composite, $secret, $hash_func)
    {
        $dh_shared = $this->getSharedSecret($composite);
        $dh_shared_str = $this->lib->longToBinary($dh_shared);
        $hash_dh_shared = $hash_func($dh_shared_str);

        $xsecret = "";
        for ($i = 0; $i < Auth_OpenID::bytes($secret); $i++) {
            $xsecret .= chr(ord($secret[$i]) ^ ord($hash_dh_shared[$i]));
        }

        return $xsecret;
    }
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/2/MySQLStore.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/2/MySQLStore.php */ ?>
<?php

/**
 * A MySQL store.
 *
 * @package OpenID
 */

/**
 * Require the base class file.
 */
//require_once "Auth/OpenID/SQLStore.php";

/**
 * An SQL store that uses MySQL as its backend.
 *
 * @package OpenID
 */
class Auth_OpenID_MySQLStore extends Auth_OpenID_SQLStore {
    /**
     * @access private
     */
    function setSQL()
    {
        $this->sql['nonce_table'] =
            "CREATE TABLE %s (\n".
            "  server_url VARCHAR(2047) NOT NULL,\n".
            "  timestamp INTEGER NOT NULL,\n".
            "  salt CHAR(40) NOT NULL,\n".
            "  UNIQUE (server_url(255), timestamp, salt)\n".
            ") ENGINE=InnoDB";

        $this->sql['assoc_table'] =
            "CREATE TABLE %s (\n".
            "  server_url BLOB NOT NULL,\n".
            "  handle VARCHAR(255) NOT NULL,\n".
            "  secret BLOB NOT NULL,\n".
            "  issued INTEGER NOT NULL,\n".
            "  lifetime INTEGER NOT NULL,\n".
            "  assoc_type VARCHAR(64) NOT NULL,\n".
            "  PRIMARY KEY (server_url(255), handle)\n".
            ") ENGINE=InnoDB";

        $this->sql['set_assoc'] =
            "REPLACE INTO %s (server_url, handle, secret, issued,\n".
            "  lifetime, assoc_type) VALUES (?, ?, !, ?, ?, ?)";

        $this->sql['get_assocs'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = ?";

        $this->sql['get_assoc'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = ? AND handle = ?";

        $this->sql['remove_assoc'] =
            "DELETE FROM %s WHERE server_url = ? AND handle = ?";

        $this->sql['add_nonce'] =
            "INSERT INTO %s (server_url, timestamp, salt) VALUES (?, ?, ?)";

        $this->sql['clean_nonce'] =
            "DELETE FROM %s WHERE timestamp < ?";

        $this->sql['clean_assoc'] =
            "DELETE FROM %s WHERE issued + lifetime < ?";
    }

    /**
     * @access private
     */
    function blobEncode($blob)
    {
        return "0x" . bin2hex($blob);
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/2/OpenID.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/2/OpenID.php */ ?>
<?php

/**
 * This is the PHP OpenID library by JanRain, Inc.
 *
 * This module contains core utility functionality used by the
 * library.  See Consumer.php and Server.php for the consumer and
 * server implementations.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * The library version string
 */
define('Auth_OpenID_VERSION', '2.1.0');

/**
 * Require the fetcher code.
 */
//require_once "Auth/Yadis/PlainHTTPFetcher.php";
//require_once "Auth/Yadis/ParanoidHTTPFetcher.php";
//require_once "Auth/OpenID/BigMath.php";
//require_once "Auth/OpenID/URINorm.php";

/**
 * Status code returned by the server when the only option is to show
 * an error page, since we do not have enough information to redirect
 * back to the consumer. The associated value is an error message that
 * should be displayed on an HTML error page.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_LOCAL_ERROR', 'local_error');

/**
 * Status code returned when there is an error to return in key-value
 * form to the consumer. The caller should return a 400 Bad Request
 * response with content-type text/plain and the value as the body.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_REMOTE_ERROR', 'remote_error');

/**
 * Status code returned when there is a key-value form OK response to
 * the consumer. The value associated with this code is the
 * response. The caller should return a 200 OK response with
 * content-type text/plain and the value as the body.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_REMOTE_OK', 'remote_ok');

/**
 * Status code returned when there is a redirect back to the
 * consumer. The value is the URL to redirect back to. The caller
 * should return a 302 Found redirect with a Location: header
 * containing the URL.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_REDIRECT', 'redirect');

/**
 * Status code returned when the caller needs to authenticate the
 * user. The associated value is a {@link Auth_OpenID_ServerRequest}
 * object that can be used to complete the authentication. If the user
 * has taken some authentication action, use the retry() method of the
 * {@link Auth_OpenID_ServerRequest} object to complete the request.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_DO_AUTH', 'do_auth');

/**
 * Status code returned when there were no OpenID arguments
 * passed. This code indicates that the caller should return a 200 OK
 * response and display an HTML page that says that this is an OpenID
 * server endpoint.
 *
 * @see Auth_OpenID_Server
 */
define('Auth_OpenID_DO_ABOUT', 'do_about');

/**
 * Defines for regexes and format checking.
 */
define('Auth_OpenID_letters',
       "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");

define('Auth_OpenID_digits',
       "0123456789");

define('Auth_OpenID_punct',
       "!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~");

if (Auth_OpenID_getMathLib() === null) {
    Auth_OpenID_setNoMathSupport();
}

/**
 * The OpenID utility function class.
 *
 * @package OpenID
 * @access private
 */
class Auth_OpenID {

    /**
     * Return true if $thing is an Auth_OpenID_FailureResponse object;
     * false if not.
     *
     * @access private
     */
    function isFailure($thing)
    {
        return is_a($thing, 'Auth_OpenID_FailureResponse');
    }

    /**
     * Gets the query data from the server environment based on the
     * request method used.  If GET was used, this looks at
     * $_SERVER['QUERY_STRING'] directly.  If POST was used, this
     * fetches data from the special php://input file stream.
     *
     * Returns an associative array of the query arguments.
     *
     * Skips invalid key/value pairs (i.e. keys with no '=value'
     * portion).
     *
     * Returns an empty array if neither GET nor POST was used, or if
     * POST was used but php://input cannot be opened.
     *
     * @access private
     */
    function getQuery($query_str=null)
    {
        $data = array();

        if ($query_str !== null) {
            $data = Auth_OpenID::params_from_string($query_str);
        } else if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
            // Do nothing.
        } else {
          // XXX HACK FIXME HORRIBLE.
          //
          // POSTing to a URL with query parameters is acceptable, but
          // we don't have a clean way to distinguish those parameters
          // when we need to do things like return_to verification
          // which only want to look at one kind of parameter.  We're
          // going to emulate the behavior of some other environments
          // by defaulting to GET and overwriting with POST if POST
          // data is available.
          $data = Auth_OpenID::params_from_string($_SERVER['QUERY_STRING']);

          if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $str = file_get_contents('php://input');

            if ($str === false) {
              $post = array();
            } else {
              $post = Auth_OpenID::params_from_string($str);
            }

            $data = array_merge($data, $post);
          }
        }

        return $data;
    }

    function params_from_string($str)
    {
        $chunks = explode("&", $str);

        $data = array();
        foreach ($chunks as $chunk) {
            $parts = explode("=", $chunk, 2);

            if (count($parts) != 2) {
                continue;
            }

            list($k, $v) = $parts;
            $data[$k] = urldecode($v);
        }

        return $data;
    }

    /**
     * Create dir_name as a directory if it does not exist. If it
     * exists, make sure that it is, in fact, a directory.  Returns
     * true if the operation succeeded; false if not.
     *
     * @access private
     */
    function ensureDir($dir_name)
    {
        if (is_dir($dir_name) || @mkdir($dir_name)) {
            return true;
        } else {
            $parent_dir = dirname($dir_name);

            // Terminal case; there is no parent directory to create.
            if ($parent_dir == $dir_name) {
                return true;
            }

            return (Auth_OpenID::ensureDir($parent_dir) && @mkdir($dir_name));
        }
    }

    /**
     * Adds a string prefix to all values of an array.  Returns a new
     * array containing the prefixed values.
     *
     * @access private
     */
    function addPrefix($values, $prefix)
    {
        $new_values = array();
        foreach ($values as $s) {
            $new_values[] = $prefix . $s;
        }
        return $new_values;
    }

    /**
     * Convenience function for getting array values.  Given an array
     * $arr and a key $key, get the corresponding value from the array
     * or return $default if the key is absent.
     *
     * @access private
     */
    function arrayGet($arr, $key, $fallback = null)
    {
        if (is_array($arr)) {
            if (array_key_exists($key, $arr)) {
                return $arr[$key];
            } else {
                return $fallback;
            }
        } else {
            trigger_error("Auth_OpenID::arrayGet (key = ".$key.") expected " .
                          "array as first parameter, got " .
                          gettype($arr), E_USER_WARNING);

            return false;
        }
    }

    /**
     * Replacement for PHP's broken parse_str.
     */
    function parse_str($query)
    {
        if ($query === null) {
            return null;
        }

        $parts = explode('&', $query);

        $new_parts = array();
        for ($i = 0; $i < count($parts); $i++) {
            $pair = explode('=', $parts[$i]);

            if (count($pair) != 2) {
                continue;
            }

            list($key, $value) = $pair;
            $new_parts[$key] = urldecode($value);
        }

        return $new_parts;
    }

    /**
     * Implements the PHP 5 'http_build_query' functionality.
     *
     * @access private
     * @param array $data Either an array key/value pairs or an array
     * of arrays, each of which holding two values: a key and a value,
     * sequentially.
     * @return string $result The result of url-encoding the key/value
     * pairs from $data into a URL query string
     * (e.g. "username=bob&id=56").
     */
    function httpBuildQuery($data)
    {
        $pairs = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $pairs[] = urlencode($value[0])."=".urlencode($value[1]);
            } else {
                $pairs[] = urlencode($key)."=".urlencode($value);
            }
        }
        return implode("&", $pairs);
    }

    /**
     * "Appends" query arguments onto a URL.  The URL may or may not
     * already have arguments (following a question mark).
     *
     * @access private
     * @param string $url A URL, which may or may not already have
     * arguments.
     * @param array $args Either an array key/value pairs or an array of
     * arrays, each of which holding two values: a key and a value,
     * sequentially.  If $args is an ordinary key/value array, the
     * parameters will be added to the URL in sorted alphabetical order;
     * if $args is an array of arrays, their order will be preserved.
     * @return string $url The original URL with the new parameters added.
     *
     */
    function appendArgs($url, $args)
    {
        if (count($args) == 0) {
            return $url;
        }

        // Non-empty array; if it is an array of arrays, use
        // multisort; otherwise use sort.
        if (array_key_exists(0, $args) &&
            is_array($args[0])) {
            // Do nothing here.
        } else {
            $keys = array_keys($args);
            sort($keys);
            $new_args = array();
            foreach ($keys as $key) {
                $new_args[] = array($key, $args[$key]);
            }
            $args = $new_args;
        }

        $sep = '?';
        if (strpos($url, '?') !== false) {
            $sep = '&';
        }

        return $url . $sep . Auth_OpenID::httpBuildQuery($args);
    }

    /**
     * Turn a string into an ASCII string.
     *
     * Replace non-ascii characters with a %-encoded, UTF-8
     * encoding. This function will fail if the input is a string and
     * there are non-7-bit-safe characters. It is assumed that the
     * caller will have already translated the input into a Unicode
     * character sequence, according to the encoding of the HTTP POST
     * or GET.
     *
     * Do not escape anything that is already 7-bit safe, so we do the
     * minimal transform on the identity URL
     *
     * @access private
     */
    function quoteMinimal($s)
    {
        $res = array();
        for ($i = 0; $i < strlen($s); $i++) {
            $c = $s[$i];
            if ($c >= "\x80") {
                for ($j = 0; $j < count(utf8_encode($c)); $j++) {
                    array_push($res, sprintf("%02X", ord($c[$j])));
                }
            } else {
                array_push($res, $c);
            }
        }

        return implode('', $res);
    }

    /**
     * Implements python's urlunparse, which is not available in PHP.
     * Given the specified components of a URL, this function rebuilds
     * and returns the URL.
     *
     * @access private
     * @param string $scheme The scheme (e.g. 'http').  Defaults to 'http'.
     * @param string $host The host.  Required.
     * @param string $port The port.
     * @param string $path The path.
     * @param string $query The query.
     * @param string $fragment The fragment.
     * @return string $url The URL resulting from assembling the
     * specified components.
     */
    function urlunparse($scheme, $host, $port = null, $path = '/',
                        $query = '', $fragment = '')
    {

        if (!$scheme) {
            $scheme = 'http';
        }

        if (!$host) {
            return false;
        }

        if (!$path) {
            $path = '';
        }

        $result = $scheme . "://" . $host;

        if ($port) {
            $result .= ":" . $port;
        }

        $result .= $path;

        if ($query) {
            $result .= "?" . $query;
        }

        if ($fragment) {
            $result .= "#" . $fragment;
        }

        return $result;
    }

    /**
     * Given a URL, this "normalizes" it by adding a trailing slash
     * and / or a leading http:// scheme where necessary.  Returns
     * null if the original URL is malformed and cannot be normalized.
     *
     * @access private
     * @param string $url The URL to be normalized.
     * @return mixed $new_url The URL after normalization, or null if
     * $url was malformed.
     */
    function normalizeUrl($url)
    {
        @$parsed = parse_url($url);

        if (!$parsed) {
            return null;
        }

        if (isset($parsed['scheme']) &&
            isset($parsed['host'])) {
            $scheme = strtolower($parsed['scheme']);
            if (!in_array($scheme, array('http', 'https'))) {
                return null;
            }
        } else {
            $url = 'http://' . $url;
        }

        $normalized = Auth_OpenID_urinorm($url);
        if ($normalized === null) {
            return null;
        }
        list($defragged, $frag) = Auth_OpenID::urldefrag($normalized);
        return $defragged;
    }

    /**
     * Replacement (wrapper) for PHP's intval() because it's broken.
     *
     * @access private
     */
    function intval($value)
    {
        $re = "/^\\d+$/";

        if (!preg_match($re, $value)) {
            return false;
        }

        return intval($value);
    }

    /**
     * Count the number of bytes in a string independently of
     * multibyte support conditions.
     *
     * @param string $str The string of bytes to count.
     * @return int The number of bytes in $str.
     */
    function bytes($str)
    {
        return strlen(bin2hex($str)) / 2;
    }

    /**
     * Get the bytes in a string independently of multibyte support
     * conditions.
     */
    function toBytes($str)
    {
        $hex = bin2hex($str);

        if (!$hex) {
            return array();
        }

        $b = array();
        for ($i = 0; $i < strlen($hex); $i += 2) {
            $b[] = chr(base_convert(substr($hex, $i, 2), 16, 10));
        }

        return $b;
    }

    function urldefrag($url)
    {
        $parts = explode("#", $url, 2);

        if (count($parts) == 1) {
            return array($parts[0], "");
        } else {
            return $parts;
        }
    }

    function filter($callback, &$sequence)
    {
        $result = array();

        foreach ($sequence as $item) {
            if (call_user_func_array($callback, array($item))) {
                $result[] = $item;
            }
        }

        return $result;
    }

    function update(&$dest, &$src)
    {
        foreach ($src as $k => $v) {
            $dest[$k] = $v;
        }
    }

    /**
     * Wrap PHP's standard error_log functionality.  Use this to
     * perform all logging. It will interpolate any additional
     * arguments into the format string before logging.
     *
     * @param string $format_string The sprintf format for the message
     */
    function log($format_string)
    {
        $args = func_get_args();
        $message = call_user_func_array('sprintf', $args);
        error_log($message);
    }

    function autoSubmitHTML($form, $title="OpenId transaction in progress")
    {
        return("<html>".
               "<head><title>".
               $title .
               "</title></head>".
               "<body onload='document.forms[0].submit();'>".
               $form .
               "<script>".
               "var elements = document.forms[0].elements;".
               "for (var i = 0; i < elements.length; i++) {".
               "  elements[i].style.display = \"none\";".
               "}".
               "</script>".
               "</body>".
               "</html>");
    }
}
?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/2/PAPE.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/2/PAPE.php */ ?>
<?php

/**
 * An implementation of the OpenID Provider Authentication Policy
 *  Extension 1.0
 *
 * See:
 * http://openid.net/developers/specs/
 */

//require_once "Auth/OpenID/Extension.php";

define('Auth_OpenID_PAPE_NS_URI',
       "http://specs.openid.net/extensions/pape/1.0");

define('PAPE_AUTH_MULTI_FACTOR_PHYSICAL',
       'http://schemas.openid.net/pape/policies/2007/06/multi-factor-physical');
define('PAPE_AUTH_MULTI_FACTOR',
       'http://schemas.openid.net/pape/policies/2007/06/multi-factor');
define('PAPE_AUTH_PHISHING_RESISTANT',
       'http://schemas.openid.net/pape/policies/2007/06/phishing-resistant');

define('PAPE_TIME_VALIDATOR',
       '^[0-9]{4,4}-[0-9][0-9]-[0-9][0-9]T[0-9][0-9]:[0-9][0-9]:[0-9][0-9]Z$');
/**
 * A Provider Authentication Policy request, sent from a relying party
 * to a provider
 *
 * preferred_auth_policies: The authentication policies that
 * the relying party prefers
 *
 * max_auth_age: The maximum time, in seconds, that the relying party
 * wants to allow to have elapsed before the user must re-authenticate
 */
class Auth_OpenID_PAPE_Request extends Auth_OpenID_Extension {

    var $ns_alias = 'pape';
    var $ns_uri = Auth_OpenID_PAPE_NS_URI;

    function Auth_OpenID_PAPE_Request($preferred_auth_policies=null,
                                      $max_auth_age=null)
    {
        if ($preferred_auth_policies === null) {
            $preferred_auth_policies = array();
        }

        $this->preferred_auth_policies = $preferred_auth_policies;
        $this->max_auth_age = $max_auth_age;
    }

    /**
     * Add an acceptable authentication policy URI to this request
     *
     * This method is intended to be used by the relying party to add
     * acceptable authentication types to the request.
     *
     * policy_uri: The identifier for the preferred type of
     * authentication.
     */
    function addPolicyURI($policy_uri)
    {
        if (!in_array($policy_uri, $this->preferred_auth_policies)) {
            $this->preferred_auth_policies[] = $policy_uri;
        }
    }

    function getExtensionArgs()
    {
        $ns_args = array(
                         'preferred_auth_policies' =>
                           implode(' ', $this->preferred_auth_policies)
                         );

        if ($this->max_auth_age !== null) {
            $ns_args['max_auth_age'] = strval($this->max_auth_age);
        }

        return $ns_args;
    }

    /**
     * Instantiate a Request object from the arguments in a checkid_*
     * OpenID message
     */
    function fromOpenIDRequest($request)
    {
        $obj = new Auth_OpenID_PAPE_Request();
        $args = $request->message->getArgs(Auth_OpenID_PAPE_NS_URI);

        if ($args === null || $args === array()) {
            return null;
        }

        $obj->parseExtensionArgs($args);
        return $obj;
    }

    /**
     * Set the state of this request to be that expressed in these
     * PAPE arguments
     *
     * @param args: The PAPE arguments without a namespace
     */
    function parseExtensionArgs($args)
    {
        // preferred_auth_policies is a space-separated list of policy
        // URIs
        $this->preferred_auth_policies = array();

        $policies_str = Auth_OpenID::arrayGet($args, 'preferred_auth_policies');
        if ($policies_str) {
            foreach (explode(' ', $policies_str) as $uri) {
                if (!in_array($uri, $this->preferred_auth_policies)) {
                    $this->preferred_auth_policies[] = $uri;
                }
            }
        }

        // max_auth_age is base-10 integer number of seconds
        $max_auth_age_str = Auth_OpenID::arrayGet($args, 'max_auth_age');
        if ($max_auth_age_str) {
            $this->max_auth_age = Auth_OpenID::intval($max_auth_age_str);
        } else {
            $this->max_auth_age = null;
        }
    }

    /**
     * Given a list of authentication policy URIs that a provider
     * supports, this method returns the subsequence of those types
     * that are preferred by the relying party.
     *
     * @param supported_types: A sequence of authentication policy
     * type URIs that are supported by a provider
     *
     * @return array The sub-sequence of the supported types that are
     * preferred by the relying party. This list will be ordered in
     * the order that the types appear in the supported_types
     * sequence, and may be empty if the provider does not prefer any
     * of the supported authentication types.
     */
    function preferredTypes($supported_types)
    {
        $result = array();

        foreach ($supported_types as $st) {
            if (in_array($st, $this->preferred_auth_policies)) {
                $result[] = $st;
            }
        }
        return $result;
    }
}

/**
 * A Provider Authentication Policy response, sent from a provider to
 * a relying party
 */
class Auth_OpenID_PAPE_Response extends Auth_OpenID_Extension {

    var $ns_alias = 'pape';
    var $ns_uri = Auth_OpenID_PAPE_NS_URI;

    function Auth_OpenID_PAPE_Response($auth_policies=null, $auth_time=null,
                                       $nist_auth_level=null)
    {
        if ($auth_policies) {
            $this->auth_policies = $auth_policies;
        } else {
            $this->auth_policies = array();
        }

        $this->auth_time = $auth_time;
        $this->nist_auth_level = $nist_auth_level;
    }

    /**
     * Add a authentication policy to this response
     *
     * This method is intended to be used by the provider to add a
     * policy that the provider conformed to when authenticating the
     * user.
     *
     * @param policy_uri: The identifier for the preferred type of
     * authentication.
     */
    function addPolicyURI($policy_uri)
    {
        if (!in_array($policy_uri, $this->auth_policies)) {
            $this->auth_policies[] = $policy_uri;
        }
    }

    /**
     * Create an Auth_OpenID_PAPE_Response object from a successful
     * OpenID library response.
     *
     * @param success_response $success_response A SuccessResponse
     * from Auth_OpenID_Consumer::complete()
     *
     * @returns: A provider authentication policy response from the
     * data that was supplied with the id_res response.
     */
    function fromSuccessResponse($success_response)
    {
        $obj = new Auth_OpenID_PAPE_Response();

        // PAPE requires that the args be signed.
        $args = $success_response->getSignedNS(Auth_OpenID_PAPE_NS_URI);

        if ($args === null || $args === array()) {
            return null;
        }

        $result = $obj->parseExtensionArgs($args);

        if ($result === false) {
            return null;
        } else {
            return $obj;
        }
    }

    /**
     * Parse the provider authentication policy arguments into the
     *  internal state of this object
     *
     * @param args: unqualified provider authentication policy
     * arguments
     *
     * @param strict: Whether to return false when bad data is
     * encountered
     *
     * @return null The data is parsed into the internal fields of
     * this object.
    */
    function parseExtensionArgs($args, $strict=false)
    {
        $policies_str = Auth_OpenID::arrayGet($args, 'auth_policies');
        if ($policies_str && $policies_str != "none") {
            $this->auth_policies = explode(" ", $policies_str);
        }

        $nist_level_str = Auth_OpenID::arrayGet($args, 'nist_auth_level');
        if ($nist_level_str !== null) {
            $nist_level = Auth_OpenID::intval($nist_level_str);

            if ($nist_level === false) {
                if ($strict) {
                    return false;
                } else {
                    $nist_level = null;
                }
            }

            if (0 <= $nist_level && $nist_level < 5) {
                $this->nist_auth_level = $nist_level;
            } else if ($strict) {
                return false;
            }
        }

        $auth_time = Auth_OpenID::arrayGet($args, 'auth_time');
        if ($auth_time !== null) {
            if (ereg(PAPE_TIME_VALIDATOR, $auth_time)) {
                $this->auth_time = $auth_time;
            } else if ($strict) {
                return false;
            }
        }
    }

    function getExtensionArgs()
    {
        $ns_args = array();
        if (count($this->auth_policies) > 0) {
            $ns_args['auth_policies'] = implode(' ', $this->auth_policies);
        } else {
            $ns_args['auth_policies'] = 'none';
        }

        if ($this->nist_auth_level !== null) {
            if (!in_array($this->nist_auth_level, range(0, 4), true)) {
                return false;
            }
            $ns_args['nist_auth_level'] = strval($this->nist_auth_level);
        }

        if ($this->auth_time !== null) {
            if (!ereg(PAPE_TIME_VALIDATOR, $this->auth_time)) {
                return false;
            }

            $ns_args['auth_time'] = $this->auth_time;
        }

        return $ns_args;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/2/PostgreSQLStore.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/2/PostgreSQLStore.php */ ?>
<?php

/**
 * A PostgreSQL store.
 *
 * @package OpenID
 */

/**
 * Require the base class file.
 */
//require_once "Auth/OpenID/SQLStore.php";

/**
 * An SQL store that uses PostgreSQL as its backend.
 *
 * @package OpenID
 */
class Auth_OpenID_PostgreSQLStore extends Auth_OpenID_SQLStore {
    /**
     * @access private
     */
    function setSQL()
    {
        $this->sql['nonce_table'] =
            "CREATE TABLE %s (server_url VARCHAR(2047) NOT NULL, ".
                             "timestamp INTEGER NOT NULL, ".
                             "salt CHAR(40) NOT NULL, ".
                "UNIQUE (server_url, timestamp, salt))";

        $this->sql['assoc_table'] =
            "CREATE TABLE %s (server_url VARCHAR(2047) NOT NULL, ".
                             "handle VARCHAR(255) NOT NULL, ".
                             "secret BYTEA NOT NULL, ".
                             "issued INTEGER NOT NULL, ".
                             "lifetime INTEGER NOT NULL, ".
                             "assoc_type VARCHAR(64) NOT NULL, ".
            "PRIMARY KEY (server_url, handle), ".
            "CONSTRAINT secret_length_constraint CHECK ".
            "(LENGTH(secret) <= 128))";

        $this->sql['set_assoc'] =
            array(
                  'insert_assoc' => "INSERT INTO %s (server_url, handle, ".
                  "secret, issued, lifetime, assoc_type) VALUES ".
                  "(?, ?, '!', ?, ?, ?)",
                  'update_assoc' => "UPDATE %s SET secret = '!', issued = ?, ".
                  "lifetime = ?, assoc_type = ? WHERE server_url = ? AND ".
                  "handle = ?"
                  );

        $this->sql['get_assocs'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = ?";

        $this->sql['get_assoc'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = ? AND handle = ?";

        $this->sql['remove_assoc'] =
            "DELETE FROM %s WHERE server_url = ? AND handle = ?";

        $this->sql['add_nonce'] =
                  "INSERT INTO %s (server_url, timestamp, salt) VALUES ".
                  "(?, ?, ?)"
                  ;

        $this->sql['clean_nonce'] =
            "DELETE FROM %s WHERE timestamp < ?";

        $this->sql['clean_assoc'] =
            "DELETE FROM %s WHERE issued + lifetime < ?";
    }

    /**
     * @access private
     */
    function _set_assoc($server_url, $handle, $secret, $issued, $lifetime,
                        $assoc_type)
    {
        $result = $this->_get_assoc($server_url, $handle);
        if ($result) {
            // Update the table since this associations already exists.
            $this->connection->query($this->sql['set_assoc']['update_assoc'],
                                     array($secret, $issued, $lifetime,
                                           $assoc_type, $server_url, $handle));
        } else {
            // Insert a new record because this association wasn't
            // found.
            $this->connection->query($this->sql['set_assoc']['insert_assoc'],
                                     array($server_url, $handle, $secret,
                                           $issued, $lifetime, $assoc_type));
        }
    }

    /**
     * @access private
     */
    function blobEncode($blob)
    {
        return $this->_octify($blob);
    }

    /**
     * @access private
     */
    function blobDecode($blob)
    {
        return $this->_unoctify($blob);
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/2/SQLiteStore.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/2/SQLiteStore.php */ ?>
<?php

/**
 * An SQLite store.
 *
 * @package OpenID
 */

/**
 * Require the base class file.
 */
//require_once "Auth/OpenID/SQLStore.php";

/**
 * An SQL store that uses SQLite as its backend.
 *
 * @package OpenID
 */
class Auth_OpenID_SQLiteStore extends Auth_OpenID_SQLStore {
    function setSQL()
    {
        $this->sql['nonce_table'] =
            "CREATE TABLE %s (server_url VARCHAR(2047), timestamp INTEGER, ".
            "salt CHAR(40), UNIQUE (server_url, timestamp, salt))";

        $this->sql['assoc_table'] =
            "CREATE TABLE %s (server_url VARCHAR(2047), handle VARCHAR(255), ".
            "secret BLOB(128), issued INTEGER, lifetime INTEGER, ".
            "assoc_type VARCHAR(64), PRIMARY KEY (server_url, handle))";

        $this->sql['set_assoc'] =
            "INSERT OR REPLACE INTO %s VALUES (?, ?, ?, ?, ?, ?)";

        $this->sql['get_assocs'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = ?";

        $this->sql['get_assoc'] =
            "SELECT handle, secret, issued, lifetime, assoc_type FROM %s ".
            "WHERE server_url = ? AND handle = ?";

        $this->sql['remove_assoc'] =
            "DELETE FROM %s WHERE server_url = ? AND handle = ?";

        $this->sql['add_nonce'] =
            "INSERT INTO %s (server_url, timestamp, salt) VALUES (?, ?, ?)";

        $this->sql['clean_nonce'] =
            "DELETE FROM %s WHERE timestamp < ?";

        $this->sql['clean_assoc'] =
            "DELETE FROM %s WHERE issued + lifetime < ?";
    }

    /**
     * @access private
     */
    function _add_nonce($server_url, $timestamp, $salt)
    {
        // PECL SQLite extensions 1.0.3 and older (1.0.3 is the
        // current release at the time of this writing) have a broken
        // sqlite_escape_string function that breaks when passed the
        // empty string. Prefixing all strings with one character
        // keeps them unique and avoids this bug. The nonce table is
        // write-only, so we don't have to worry about updating other
        // functions with this same bad hack.
        return parent::_add_nonce('x' . $server_url, $timestamp, $salt);
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/2/XRIRes.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/2/XRIRes.php */ ?>
<?php

/**
 * Code for using a proxy XRI resolver.
 */

//require_once 'Auth/Yadis/XRDS.php';
//require_once 'Auth/Yadis/XRI.php';

class Auth_Yadis_ProxyResolver {
    function Auth_Yadis_ProxyResolver(&$fetcher, $proxy_url = null)
    {
        $this->fetcher =& $fetcher;
        $this->proxy_url = $proxy_url;
        if (!$this->proxy_url) {
            $this->proxy_url = Auth_Yadis_getDefaultProxy();
        }
    }

    function queryURL($xri, $service_type = null)
    {
        // trim off the xri:// prefix
        $qxri = substr(Auth_Yadis_toURINormal($xri), 6);
        $hxri = $this->proxy_url . $qxri;
        $args = array(
                      '_xrd_r' => 'application/xrds+xml'
                      );

        if ($service_type) {
            $args['_xrd_t'] = $service_type;
        } else {
            // Don't perform service endpoint selection.
            $args['_xrd_r'] .= ';sep=false';
        }

        $query = Auth_Yadis_XRIAppendArgs($hxri, $args);
        return $query;
    }

    function query($xri, $service_types, $filters = array())
    {
        $services = array();
        $canonicalID = null;
        foreach ($service_types as $service_type) {
            $url = $this->queryURL($xri, $service_type);
            $response = $this->fetcher->get($url);
            if ($response->status != 200) {
                continue;
            }
            $xrds = Auth_Yadis_XRDS::parseXRDS($response->body);
            if (!$xrds) {
                continue;
            }
            $canonicalID = Auth_Yadis_getCanonicalID($xri,
                                                         $xrds);

            if ($canonicalID === false) {
                return null;
            }

            $some_services = $xrds->services($filters);
            $services = array_merge($services, $some_services);
            // TODO:
            //  * If we do get hits for multiple service_types, we're
            //    almost certainly going to have duplicated service
            //    entries and broken priority ordering.
        }
        return array($canonicalID, $services);
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/2/Yadis.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/2/Yadis.php */ ?>
<?php

/**
 * The core PHP Yadis implementation.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Need both fetcher types so we can use the right one based on the
 * presence or absence of CURL.
 */
//require_once "Auth/Yadis/PlainHTTPFetcher.php";
//require_once "Auth/Yadis/ParanoidHTTPFetcher.php";

/**
 * Need this for parsing HTML (looking for META tags).
 */
//require_once "Auth/Yadis/ParseHTML.php";

/**
 * Need this to parse the XRDS document during Yadis discovery.
 */
//require_once "Auth/Yadis/XRDS.php";

/**
 * XRDS (yadis) content type
 */
define('Auth_Yadis_CONTENT_TYPE', 'application/xrds+xml');

/**
 * Yadis header
 */
define('Auth_Yadis_HEADER_NAME', 'X-XRDS-Location');

/**
 * Contains the result of performing Yadis discovery on a URI.
 *
 * @package OpenID
 */
class Auth_Yadis_DiscoveryResult {

    // The URI that was passed to the fetcher
    var $request_uri = null;

    // The result of following redirects from the request_uri
    var $normalized_uri = null;

    // The URI from which the response text was returned (set to
    // None if there was no XRDS document found)
    var $xrds_uri = null;

    var $xrds = null;

    // The content-type returned with the response_text
    var $content_type = null;

    // The document returned from the xrds_uri
    var $response_text = null;

    // Did the discovery fail miserably?
    var $failed = false;

    function Auth_Yadis_DiscoveryResult($request_uri)
    {
        // Initialize the state of the object
        // sets all attributes to None except the request_uri
        $this->request_uri = $request_uri;
    }

    function fail()
    {
        $this->failed = true;
    }

    function isFailure()
    {
        return $this->failed;
    }

    /**
     * Returns the list of service objects as described by the XRDS
     * document, if this yadis object represents a successful Yadis
     * discovery.
     *
     * @return array $services An array of {@link Auth_Yadis_Service}
     * objects
     */
    function services()
    {
        if ($this->xrds) {
            return $this->xrds->services();
        }

        return null;
    }

    function usedYadisLocation()
    {
        // Was the Yadis protocol's indirection used?
        return $this->normalized_uri != $this->xrds_uri;
    }

    function isXRDS()
    {
        // Is the response text supposed to be an XRDS document?
        return ($this->usedYadisLocation() ||
                $this->content_type == Auth_Yadis_CONTENT_TYPE);
    }
}

/**
 *
 * Perform the Yadis protocol on the input URL and return an iterable
 * of resulting endpoint objects.
 *
 * input_url: The URL on which to perform the Yadis protocol
 *
 * @return: The normalized identity URL and an iterable of endpoint
 * objects generated by the filter function.
 *
 * xrds_parse_func: a callback which will take (uri, xrds_text) and
 * return an array of service endpoint objects or null.  Usually
 * array('Auth_OpenID_ServiceEndpoint', 'fromXRDS').
 *
 * discover_func: if not null, a callback which should take (uri) and
 * return an Auth_Yadis_Yadis object or null.
 */
function Auth_Yadis_getServiceEndpoints($input_url, $xrds_parse_func,
                                        $discover_func=null, $fetcher=null)
{
    if ($discover_func === null) {
        $discover_function = array('Auth_Yadis_Yadis', 'discover');
    }

    $yadis_result = call_user_func_array($discover_func,
                                         array($input_url, $fetcher));

    if ($yadis_result === null) {
        return array($input_url, array());
    }

    $endpoints = call_user_func_array($xrds_parse_func,
                      array($yadis_result->normalized_uri,
                            $yadis_result->response_text));

    if ($endpoints === null) {
        $endpoints = array();
    }

    return array($yadis_result->normalized_uri, $endpoints);
}

/**
 * This is the core of the PHP Yadis library.  This is the only class
 * a user needs to use to perform Yadis discovery.  This class
 * performs the discovery AND stores the result of the discovery.
 *
 * First, require this library into your program source:
 *
 * <pre>  require_once "Auth/Yadis/Yadis.php";</pre>
 *
 * To perform Yadis discovery, first call the "discover" method
 * statically with a URI parameter:
 *
 * <pre>  $http_response = array();
 *  $fetcher = Auth_Yadis_Yadis::getHTTPFetcher();
 *  $yadis_object = Auth_Yadis_Yadis::discover($uri,
 *                                    $http_response, $fetcher);</pre>
 *
 * If the discovery succeeds, $yadis_object will be an instance of
 * {@link Auth_Yadis_Yadis}.  If not, it will be null.  The XRDS
 * document found during discovery should have service descriptions,
 * which can be accessed by calling
 *
 * <pre>  $service_list = $yadis_object->services();</pre>
 *
 * which returns an array of objects which describe each service.
 * These objects are instances of Auth_Yadis_Service.  Each object
 * describes exactly one whole Service element, complete with all of
 * its Types and URIs (no expansion is performed).  The common use
 * case for using the service objects returned by services() is to
 * write one or more filter functions and pass those to services():
 *
 * <pre>  $service_list = $yadis_object->services(
 *                               array("filterByURI",
 *                                     "filterByExtension"));</pre>
 *
 * The filter functions (whose names appear in the array passed to
 * services()) take the following form:
 *
 * <pre>  function myFilter(&$service) {
 *       // Query $service object here.  Return true if the service
 *       // matches your query; false if not.
 *  }</pre>
 *
 * This is an example of a filter which uses a regular expression to
 * match the content of URI tags (note that the Auth_Yadis_Service
 * class provides a getURIs() method which you should use instead of
 * this contrived example):
 *
 * <pre>
 *  function URIMatcher(&$service) {
 *      foreach ($service->getElements('xrd:URI') as $uri) {
 *          if (preg_match("/some_pattern/",
 *                         $service->parser->content($uri))) {
 *              return true;
 *          }
 *      }
 *      return false;
 *  }</pre>
 *
 * The filter functions you pass will be called for each service
 * object to determine which ones match the criteria your filters
 * specify.  The default behavior is that if a given service object
 * matches ANY of the filters specified in the services() call, it
 * will be returned.  You can specify that a given service object will
 * be returned ONLY if it matches ALL specified filters by changing
 * the match mode of services():
 *
 * <pre>  $yadis_object->services(array("filter1", "filter2"),
 *                          SERVICES_YADIS_MATCH_ALL);</pre>
 *
 * See {@link SERVICES_YADIS_MATCH_ALL} and {@link
 * SERVICES_YADIS_MATCH_ANY}.
 *
 * Services described in an XRDS should have a library which you'll
 * probably be using.  Those libraries are responsible for defining
 * filters that can be used with the "services()" call.  If you need
 * to write your own filter, see the documentation for {@link
 * Auth_Yadis_Service}.
 *
 * @package OpenID
 */
class Auth_Yadis_Yadis {

    /**
     * Returns an HTTP fetcher object.  If the CURL extension is
     * present, an instance of {@link Auth_Yadis_ParanoidHTTPFetcher}
     * is returned.  If not, an instance of
     * {@link Auth_Yadis_PlainHTTPFetcher} is returned.
     *
     * If Auth_Yadis_CURL_OVERRIDE is defined, this method will always
     * return a {@link Auth_Yadis_PlainHTTPFetcher}.
     */
    function getHTTPFetcher($timeout = 20)
    {
        if (Auth_Yadis_Yadis::curlPresent() &&
            (!defined('Auth_Yadis_CURL_OVERRIDE'))) {
            $fetcher = new Auth_Yadis_ParanoidHTTPFetcher($timeout);
        } else {
            $fetcher = new Auth_Yadis_PlainHTTPFetcher($timeout);
        }
        return $fetcher;
    }

    function curlPresent()
    {
        return function_exists('curl_init');
    }

    /**
     * @access private
     */
    function _getHeader($header_list, $names)
    {
        foreach ($header_list as $name => $value) {
            foreach ($names as $n) {
                if (strtolower($name) == strtolower($n)) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @access private
     */
    function _getContentType($content_type_header)
    {
        if ($content_type_header) {
            $parts = explode(";", $content_type_header);
            return strtolower($parts[0]);
        }
    }

    /**
     * This should be called statically and will build a Yadis
     * instance if the discovery process succeeds.  This implements
     * Yadis discovery as specified in the Yadis specification.
     *
     * @param string $uri The URI on which to perform Yadis discovery.
     *
     * @param array $http_response An array reference where the HTTP
     * response object will be stored (see {@link
     * Auth_Yadis_HTTPResponse}.
     *
     * @param Auth_Yadis_HTTPFetcher $fetcher An instance of a
     * Auth_Yadis_HTTPFetcher subclass.
     *
     * @param array $extra_ns_map An array which maps namespace names
     * to namespace URIs to be used when parsing the Yadis XRDS
     * document.
     *
     * @param integer $timeout An optional fetcher timeout, in seconds.
     *
     * @return mixed $obj Either null or an instance of
     * Auth_Yadis_Yadis, depending on whether the discovery
     * succeeded.
     */
    function discover($uri, &$fetcher,
                      $extra_ns_map = null, $timeout = 20)
    {
        $result = new Auth_Yadis_DiscoveryResult($uri);

        $request_uri = $uri;
        $headers = array("Accept: " . Auth_Yadis_CONTENT_TYPE .
                         ', text/html; q=0.3, application/xhtml+xml; 0.5');

        if ($fetcher === null) {
            $fetcher = Auth_Yadis_Yadis::getHTTPFetcher($timeout);
        }

        $response = $fetcher->get($uri, $headers);

        if (!$response || ($response->status != 200)) {
            $result->fail();
            return $result;
        }

        $result->normalized_uri = $response->final_url;
        $result->content_type = Auth_Yadis_Yadis::_getHeader(
                                       $response->headers,
                                       array('content-type'));

        if ($result->content_type &&
            (Auth_Yadis_Yadis::_getContentType($result->content_type) ==
             Auth_Yadis_CONTENT_TYPE)) {
            $result->xrds_uri = $result->normalized_uri;
        } else {
            $yadis_location = Auth_Yadis_Yadis::_getHeader(
                                                 $response->headers,
                                                 array(Auth_Yadis_HEADER_NAME));

            if (!$yadis_location) {
                $parser = new Auth_Yadis_ParseHTML();
                $yadis_location = $parser->getHTTPEquiv($response->body);
            }

            if ($yadis_location) {
                $result->xrds_uri = $yadis_location;

                $response = $fetcher->get($yadis_location);

                if ((!$response) || ($response->status != 200)) {
                    $result->fail();
                    return $result;
                }

                $result->content_type = Auth_Yadis_Yadis::_getHeader(
                                                         $response->headers,
                                                         array('content-type'));
            }
        }

        $result->response_text = $response->body;
        return $result;
    }
}

 /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/2/3/Consumer.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/2/3/Consumer.php */ ?>
<?php

/**
 * This module documents the main interface with the OpenID consumer
 * library.  The only part of the library which has to be used and
 * isn't documented in full here is the store required to create an
 * Auth_OpenID_Consumer instance.  More on the abstract store type and
 * concrete implementations of it that are provided in the
 * documentation for the Auth_OpenID_Consumer constructor.
 *
 * OVERVIEW
 *
 * The OpenID identity verification process most commonly uses the
 * following steps, as visible to the user of this library:
 *
 *   1. The user enters their OpenID into a field on the consumer's
 *      site, and hits a login button.
 *   2. The consumer site discovers the user's OpenID server using the
 *      YADIS protocol.
 *   3. The consumer site sends the browser a redirect to the identity
 *      server.  This is the authentication request as described in
 *      the OpenID specification.
 *   4. The identity server's site sends the browser a redirect back
 *      to the consumer site.  This redirect contains the server's
 *      response to the authentication request.
 *
 * The most important part of the flow to note is the consumer's site
 * must handle two separate HTTP requests in order to perform the full
 * identity check.
 *
 * LIBRARY DESIGN
 *
 * This consumer library is designed with that flow in mind.  The goal
 * is to make it as easy as possible to perform the above steps
 * securely.
 *
 * At a high level, there are two important parts in the consumer
 * library.  The first important part is this module, which contains
 * the interface to actually use this library.  The second is the
 * Auth_OpenID_Interface class, which describes the interface to use
 * if you need to create a custom method for storing the state this
 * library needs to maintain between requests.
 *
 * In general, the second part is less important for users of the
 * library to know about, as several implementations are provided
 * which cover a wide variety of situations in which consumers may use
 * the library.
 *
 * This module contains a class, Auth_OpenID_Consumer, with methods
 * corresponding to the actions necessary in each of steps 2, 3, and 4
 * described in the overview.  Use of this library should be as easy
 * as creating an Auth_OpenID_Consumer instance and calling the
 * methods appropriate for the action the site wants to take.
 *
 * STORES AND DUMB MODE
 *
 * OpenID is a protocol that works best when the consumer site is able
 * to store some state.  This is the normal mode of operation for the
 * protocol, and is sometimes referred to as smart mode.  There is
 * also a fallback mode, known as dumb mode, which is available when
 * the consumer site is not able to store state.  This mode should be
 * avoided when possible, as it leaves the implementation more
 * vulnerable to replay attacks.
 *
 * The mode the library works in for normal operation is determined by
 * the store that it is given.  The store is an abstraction that
 * handles the data that the consumer needs to manage between http
 * requests in order to operate efficiently and securely.
 *
 * Several store implementation are provided, and the interface is
 * fully documented so that custom stores can be used as well.  See
 * the documentation for the Auth_OpenID_Consumer class for more
 * information on the interface for stores.  The implementations that
 * are provided allow the consumer site to store the necessary data in
 * several different ways, including several SQL databases and normal
 * files on disk.
 *
 * There is an additional concrete store provided that puts the system
 * in dumb mode.  This is not recommended, as it removes the library's
 * ability to stop replay attacks reliably.  It still uses time-based
 * checking to make replay attacks only possible within a small
 * window, but they remain possible within that window.  This store
 * should only be used if the consumer site has no way to retain data
 * between requests at all.
 *
 * IMMEDIATE MODE
 *
 * In the flow described above, the user may need to confirm to the
 * lidentity server that it's ok to authorize his or her identity.
 * The server may draw pages asking for information from the user
 * before it redirects the browser back to the consumer's site.  This
 * is generally transparent to the consumer site, so it is typically
 * ignored as an implementation detail.
 *
 * There can be times, however, where the consumer site wants to get a
 * response immediately.  When this is the case, the consumer can put
 * the library in immediate mode.  In immediate mode, there is an
 * extra response possible from the server, which is essentially the
 * server reporting that it doesn't have enough information to answer
 * the question yet.
 *
 * USING THIS LIBRARY
 *
 * Integrating this library into an application is usually a
 * relatively straightforward process.  The process should basically
 * follow this plan:
 *
 * Add an OpenID login field somewhere on your site.  When an OpenID
 * is entered in that field and the form is submitted, it should make
 * a request to the your site which includes that OpenID URL.
 *
 * First, the application should instantiate the Auth_OpenID_Consumer
 * class using the store of choice (Auth_OpenID_FileStore or one of
 * the SQL-based stores).  If the application has a custom
 * session-management implementation, an object implementing the
 * {@link Auth_Yadis_PHPSession} interface should be passed as the
 * second parameter.  Otherwise, the default uses $_SESSION.
 *
 * Next, the application should call the Auth_OpenID_Consumer object's
 * 'begin' method.  This method takes the OpenID URL.  The 'begin'
 * method returns an Auth_OpenID_AuthRequest object.
 *
 * Next, the application should call the 'redirectURL' method of the
 * Auth_OpenID_AuthRequest object.  The 'return_to' URL parameter is
 * the URL that the OpenID server will send the user back to after
 * attempting to verify his or her identity.  The 'trust_root' is the
 * URL (or URL pattern) that identifies your web site to the user when
 * he or she is authorizing it.  Send a redirect to the resulting URL
 * to the user's browser.
 *
 * That's the first half of the authentication process.  The second
 * half of the process is done after the user's ID server sends the
 * user's browser a redirect back to your site to complete their
 * login.
 *
 * When that happens, the user will contact your site at the URL given
 * as the 'return_to' URL to the Auth_OpenID_AuthRequest::redirectURL
 * call made above.  The request will have several query parameters
 * added to the URL by the identity server as the information
 * necessary to finish the request.
 *
 * Lastly, instantiate an Auth_OpenID_Consumer instance as above and
 * call its 'complete' method, passing in all the received query
 * arguments.
 *
 * There are multiple possible return types possible from that
 * method. These indicate the whether or not the login was successful,
 * and include any additional information appropriate for their type.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Require utility classes and functions for the consumer.
 */
//require_once "Auth/OpenID.php";
//require_once "Auth/OpenID/Message.php";
//require_once "Auth/OpenID/HMACSHA1.php";
//require_once "Auth/OpenID/Association.php";
//require_once "Auth/OpenID/CryptUtil.php";
//require_once "Auth/OpenID/DiffieHellman.php";
//require_once "Auth/OpenID/KVForm.php";
//require_once "Auth/OpenID/Nonce.php";
//require_once "Auth/OpenID/Discover.php";
//require_once "Auth/OpenID/URINorm.php";
//require_once "Auth/Yadis/Manager.php";
//require_once "Auth/Yadis/XRI.php";

/**
 * This is the status code returned when the complete method returns
 * successfully.
 */
define('Auth_OpenID_SUCCESS', 'success');

/**
 * Status to indicate cancellation of OpenID authentication.
 */
define('Auth_OpenID_CANCEL', 'cancel');

/**
 * This is the status code completeAuth returns when the value it
 * received indicated an invalid login.
 */
define('Auth_OpenID_FAILURE', 'failure');

/**
 * This is the status code completeAuth returns when the
 * {@link Auth_OpenID_Consumer} instance is in immediate mode, and the
 * identity server sends back a URL to send the user to to complete his
 * or her login.
 */
define('Auth_OpenID_SETUP_NEEDED', 'setup needed');

/**
 * This is the status code beginAuth returns when the page fetched
 * from the entered OpenID URL doesn't contain the necessary link tags
 * to function as an identity page.
 */
define('Auth_OpenID_PARSE_ERROR', 'parse error');

/**
 * An OpenID consumer implementation that performs discovery and does
 * session management.  See the Consumer.php file documentation for
 * more information.
 *
 * @package OpenID
 */
class Auth_OpenID_Consumer {

    /**
     * @access private
     */
    var $discoverMethod = 'Auth_OpenID_discover';

    /**
     * @access private
     */
    var $session_key_prefix = "_openid_consumer_";

    /**
     * @access private
     */
    var $_token_suffix = "last_token";

    /**
     * Initialize a Consumer instance.
     *
     * You should create a new instance of the Consumer object with
     * every HTTP request that handles OpenID transactions.
     *
     * @param Auth_OpenID_OpenIDStore $store This must be an object
     * that implements the interface in {@link
     * Auth_OpenID_OpenIDStore}.  Several concrete implementations are
     * provided, to cover most common use cases.  For stores backed by
     * MySQL, PostgreSQL, or SQLite, see the {@link
     * Auth_OpenID_SQLStore} class and its sublcasses.  For a
     * filesystem-backed store, see the {@link Auth_OpenID_FileStore}
     * module.  As a last resort, if it isn't possible for the server
     * to store state at all, an instance of {@link
     * Auth_OpenID_DumbStore} can be used.
     *
     * @param mixed $session An object which implements the interface
     * of the {@link Auth_Yadis_PHPSession} class.  Particularly, this
     * object is expected to have these methods: get($key), set($key),
     * $value), and del($key).  This defaults to a session object
     * which wraps PHP's native session machinery.  You should only
     * need to pass something here if you have your own sessioning
     * implementation.
     *
     * @param str $consumer_cls The name of the class to instantiate
     * when creating the internal consumer object.  This is used for
     * testing.
     */
    function Auth_OpenID_Consumer(&$store, $session = null,
                                  $consumer_cls = null)
    {
        if ($session === null) {
            $session = new Auth_Yadis_PHPSession();
        }

        $this->session =& $session;

        if ($consumer_cls !== null) {
            $this->consumer =& new $consumer_cls($store);
        } else {
            $this->consumer =& new Auth_OpenID_GenericConsumer($store);
        }

        $this->_token_key = $this->session_key_prefix . $this->_token_suffix;
    }

    /**
     * Used in testing to define the discovery mechanism.
     *
     * @access private
     */
    function getDiscoveryObject(&$session, $openid_url,
                                $session_key_prefix)
    {
        return new Auth_Yadis_Discovery($session, $openid_url,
                                        $session_key_prefix);
    }

    /**
     * Start the OpenID authentication process. See steps 1-2 in the
     * overview at the top of this file.
     *
     * @param string $user_url Identity URL given by the user. This
     * method performs a textual transformation of the URL to try and
     * make sure it is normalized. For example, a user_url of
     * example.com will be normalized to http://example.com/
     * normalizing and resolving any redirects the server might issue.
     *
     * @param bool $anonymous True if the OpenID request is to be sent
     * to the server without any identifier information.  Use this
     * when you want to transport data but don't want to do OpenID
     * authentication with identifiers.
     *
     * @return Auth_OpenID_AuthRequest $auth_request An object
     * containing the discovered information will be returned, with a
     * method for building a redirect URL to the server, as described
     * in step 3 of the overview. This object may also be used to add
     * extension arguments to the request, using its 'addExtensionArg'
     * method.
     */
    function begin($user_url, $anonymous=false)
    {
        $openid_url = $user_url;

        $disco = $this->getDiscoveryObject($this->session,
                                           $openid_url,
                                           $this->session_key_prefix);

        // Set the 'stale' attribute of the manager.  If discovery
        // fails in a fatal way, the stale flag will cause the manager
        // to be cleaned up next time discovery is attempted.

        $m = $disco->getManager();
        $loader = new Auth_Yadis_ManagerLoader();

        if ($m) {
            if ($m->stale) {
                $disco->destroyManager();
            } else {
                $m->stale = true;
                $disco->session->set($disco->session_key,
                                     serialize($loader->toSession($m)));
            }
        }

        $endpoint = $disco->getNextService($this->discoverMethod,
                                           $this->consumer->fetcher);

        // Reset the 'stale' attribute of the manager.
        $m =& $disco->getManager();
        if ($m) {
            $m->stale = false;
            $disco->session->set($disco->session_key,
                                 serialize($loader->toSession($m)));
        }

        if ($endpoint === null) {
            return null;
        } else {
            return $this->beginWithoutDiscovery($endpoint,
                                                $anonymous);
        }
    }

    /**
     * Start OpenID verification without doing OpenID server
     * discovery. This method is used internally by Consumer.begin
     * after discovery is performed, and exists to provide an
     * interface for library users needing to perform their own
     * discovery.
     *
     * @param Auth_OpenID_ServiceEndpoint $endpoint an OpenID service
     * endpoint descriptor.
     *
     * @param bool anonymous Set to true if you want to perform OpenID
     * without identifiers.
     *
     * @return Auth_OpenID_AuthRequest $auth_request An OpenID
     * authentication request object.
     */
    function &beginWithoutDiscovery($endpoint, $anonymous=false)
    {
        $loader = new Auth_OpenID_ServiceEndpointLoader();
        $auth_req = $this->consumer->begin($endpoint);
        $this->session->set($this->_token_key,
              $loader->toSession($auth_req->endpoint));
        if (!$auth_req->setAnonymous($anonymous)) {
            return new Auth_OpenID_FailureResponse(null,
              "OpenID 1 requests MUST include the identifier " .
              "in the request.");
        }
        return $auth_req;
    }

    /**
     * Called to interpret the server's response to an OpenID
     * request. It is called in step 4 of the flow described in the
     * consumer overview.
     *
     * @param string $current_url The URL used to invoke the application.
     * Extract the URL from your application's web
     * request framework and specify it here to have it checked
     * against the openid.current_url value in the response.  If
     * the current_url URL check fails, the status of the
     * completion will be FAILURE.
     *
     * @param array $query An array of the query parameters (key =>
     * value pairs) for this HTTP request.  Defaults to null.  If
     * null, the GET or POST data are automatically gotten from the
     * PHP environment.  It is only useful to override $query for
     * testing.
     *
     * @return Auth_OpenID_ConsumerResponse $response A instance of an
     * Auth_OpenID_ConsumerResponse subclass. The type of response is
     * indicated by the status attribute, which will be one of
     * SUCCESS, CANCEL, FAILURE, or SETUP_NEEDED.
     */
    function complete($current_url, $query=null)
    {
        if ($current_url && !is_string($current_url)) {
            // This is ugly, but we need to complain loudly when
            // someone uses the API incorrectly.
            trigger_error("current_url must be a string; see NEWS file " .
                          "for upgrading notes.",
                          E_USER_ERROR);
        }

        if ($query === null) {
            $query = Auth_OpenID::getQuery();
        }

        $loader = new Auth_OpenID_ServiceEndpointLoader();
        $endpoint_data = $this->session->get($this->_token_key);
        $endpoint =
            $loader->fromSession($endpoint_data);

        $message = Auth_OpenID_Message::fromPostArgs($query);
        $response = $this->consumer->complete($message, $endpoint,
                                              $current_url);
        $this->session->del($this->_token_key);

        if (in_array($response->status, array(Auth_OpenID_SUCCESS,
                                              Auth_OpenID_CANCEL))) {
            if ($response->identity_url !== null) {
                $disco = $this->getDiscoveryObject($this->session,
                                                   $response->identity_url,
                                                   $this->session_key_prefix);
                $disco->cleanup(true);
            }
        }

        return $response;
    }
}

/**
 * A class implementing HMAC/DH-SHA1 consumer sessions.
 *
 * @package OpenID
 */
class Auth_OpenID_DiffieHellmanSHA1ConsumerSession {
    var $session_type = 'DH-SHA1';
    var $hash_func = 'Auth_OpenID_SHA1';
    var $secret_size = 20;
    var $allowed_assoc_types = array('HMAC-SHA1');

    function Auth_OpenID_DiffieHellmanSHA1ConsumerSession($dh = null)
    {
        if ($dh === null) {
            $dh = new Auth_OpenID_DiffieHellman();
        }

        $this->dh = $dh;
    }

    function getRequest()
    {
        $math =& Auth_OpenID_getMathLib();

        $cpub = $math->longToBase64($this->dh->public);

        $args = array('dh_consumer_public' => $cpub);

        if (!$this->dh->usingDefaultValues()) {
            $args = array_merge($args, array(
                'dh_modulus' =>
                     $math->longToBase64($this->dh->mod),
                'dh_gen' =>
                     $math->longToBase64($this->dh->gen)));
        }

        return $args;
    }

    function extractSecret($response)
    {
        if (!$response->hasKey(Auth_OpenID_OPENID_NS,
                               'dh_server_public')) {
            return null;
        }

        if (!$response->hasKey(Auth_OpenID_OPENID_NS,
                               'enc_mac_key')) {
            return null;
        }

        $math =& Auth_OpenID_getMathLib();

        $spub = $math->base64ToLong($response->getArg(Auth_OpenID_OPENID_NS,
                                                      'dh_server_public'));
        $enc_mac_key = base64_decode($response->getArg(Auth_OpenID_OPENID_NS,
                                                       'enc_mac_key'));

        return $this->dh->xorSecret($spub, $enc_mac_key, $this->hash_func);
    }
}

/**
 * A class implementing HMAC/DH-SHA256 consumer sessions.
 *
 * @package OpenID
 */
class Auth_OpenID_DiffieHellmanSHA256ConsumerSession extends
      Auth_OpenID_DiffieHellmanSHA1ConsumerSession {
    var $session_type = 'DH-SHA256';
    var $hash_func = 'Auth_OpenID_SHA256';
    var $secret_size = 32;
    var $allowed_assoc_types = array('HMAC-SHA256');
}

/**
 * A class implementing plaintext consumer sessions.
 *
 * @package OpenID
 */
class Auth_OpenID_PlainTextConsumerSession {
    var $session_type = 'no-encryption';
    var $allowed_assoc_types =  array('HMAC-SHA1', 'HMAC-SHA256');

    function getRequest()
    {
        return array();
    }

    function extractSecret($response)
    {
        if (!$response->hasKey(Auth_OpenID_OPENID_NS, 'mac_key')) {
            return null;
        }

        return base64_decode($response->getArg(Auth_OpenID_OPENID_NS,
                                               'mac_key'));
    }
}

/**
 * Returns available session types.
 */
function Auth_OpenID_getAvailableSessionTypes()
{
    $types = array(
      'no-encryption' => 'Auth_OpenID_PlainTextConsumerSession',
      'DH-SHA1' => 'Auth_OpenID_DiffieHellmanSHA1ConsumerSession',
      'DH-SHA256' => 'Auth_OpenID_DiffieHellmanSHA256ConsumerSession');

    return $types;
}

/**
 * This class is the interface to the OpenID consumer logic.
 * Instances of it maintain no per-request state, so they can be
 * reused (or even used by multiple threads concurrently) as needed.
 *
 * @package OpenID
 */
class Auth_OpenID_GenericConsumer {
    /**
     * @access private
     */
    var $discoverMethod = 'Auth_OpenID_discover';

    /**
     * This consumer's store object.
     */
    var $store;

    /**
     * @access private
     */
    var $_use_assocs;

    /**
     * @access private
     */
    var $openid1_nonce_query_arg_name = 'janrain_nonce';

    /**
     * Another query parameter that gets added to the return_to for
     * OpenID 1; if the user's session state is lost, use this claimed
     * identifier to do discovery when verifying the response.
     */
    var $openid1_return_to_identifier_name = 'openid1_claimed_id';

    /**
     * This method initializes a new {@link Auth_OpenID_Consumer}
     * instance to access the library.
     *
     * @param Auth_OpenID_OpenIDStore $store This must be an object
     * that implements the interface in {@link Auth_OpenID_OpenIDStore}.
     * Several concrete implementations are provided, to cover most common use
     * cases.  For stores backed by MySQL, PostgreSQL, or SQLite, see
     * the {@link Auth_OpenID_SQLStore} class and its sublcasses.  For a
     * filesystem-backed store, see the {@link Auth_OpenID_FileStore} module.
     * As a last resort, if it isn't possible for the server to store
     * state at all, an instance of {@link Auth_OpenID_DumbStore} can be used.
     *
     * @param bool $immediate This is an optional boolean value.  It
     * controls whether the library uses immediate mode, as explained
     * in the module description.  The default value is False, which
     * disables immediate mode.
     */
    function Auth_OpenID_GenericConsumer(&$store)
    {
        $this->store =& $store;
        $this->negotiator =& Auth_OpenID_getDefaultNegotiator();
        $this->_use_assocs = ($this->store ? true : false);

        $this->fetcher = Auth_Yadis_Yadis::getHTTPFetcher();

        $this->session_types = Auth_OpenID_getAvailableSessionTypes();
    }

    /**
     * Called to begin OpenID authentication using the specified
     * {@link Auth_OpenID_ServiceEndpoint}.
     *
     * @access private
     */
    function begin($service_endpoint)
    {
        $assoc = $this->_getAssociation($service_endpoint);
        $r = new Auth_OpenID_AuthRequest($service_endpoint, $assoc);
        $r->return_to_args[$this->openid1_nonce_query_arg_name] =
            Auth_OpenID_mkNonce();

        if ($r->message->isOpenID1()) {
            $r->return_to_args[$this->openid1_return_to_identifier_name] =
                $r->endpoint->claimed_id;
        }

        return $r;
    }

    /**
     * Given an {@link Auth_OpenID_Message}, {@link
     * Auth_OpenID_ServiceEndpoint} and optional return_to URL,
     * complete OpenID authentication.
     *
     * @access private
     */
    function complete($message, $endpoint, $return_to)
    {
        $mode = $message->getArg(Auth_OpenID_OPENID_NS, 'mode',
                                 '<no mode set>');

        $mode_methods = array(
                              'cancel' => '_complete_cancel',
                              'error' => '_complete_error',
                              'setup_needed' => '_complete_setup_needed',
                              'id_res' => '_complete_id_res',
                              );

        $method = Auth_OpenID::arrayGet($mode_methods, $mode,
                                        '_completeInvalid');

        return call_user_func_array(array(&$this, $method),
                                    array($message, $endpoint, $return_to));
    }

    /**
     * @access private
     */
    function _completeInvalid($message, &$endpoint, $unused)
    {
        $mode = $message->getArg(Auth_OpenID_OPENID_NS, 'mode',
                                 '<No mode set>');

        return new Auth_OpenID_FailureResponse($endpoint,
                    sprintf("Invalid openid.mode '%s'", $mode));
    }

    /**
     * @access private
     */
    function _complete_cancel($message, &$endpoint, $unused)
    {
        return new Auth_OpenID_CancelResponse($endpoint);
    }

    /**
     * @access private
     */
    function _complete_error($message, &$endpoint, $unused)
    {
        $error = $message->getArg(Auth_OpenID_OPENID_NS, 'error');
        $contact = $message->getArg(Auth_OpenID_OPENID_NS, 'contact');
        $reference = $message->getArg(Auth_OpenID_OPENID_NS, 'reference');

        return new Auth_OpenID_FailureResponse($endpoint, $error,
                                               $contact, $reference);
    }

    /**
     * @access private
     */
    function _complete_setup_needed($message, &$endpoint, $unused)
    {
        if (!$message->isOpenID2()) {
            return $this->_completeInvalid($message, $endpoint);
        }

        return new Auth_OpenID_SetupNeededResponse($endpoint);
    }

    /**
     * @access private
     */
    function _complete_id_res($message, &$endpoint, $return_to)
    {
        $user_setup_url = $message->getArg(Auth_OpenID_OPENID1_NS,
                                           'user_setup_url');

        if ($this->_checkSetupNeeded($message)) {
            return SetupNeededResponse($endpoint, $user_setup_url);
        } else {
            return $this->_doIdRes($message, $endpoint, $return_to);
        }
    }

    /**
     * @access private
     */
    function _checkSetupNeeded($message)
    {
        // In OpenID 1, we check to see if this is a cancel from
        // immediate mode by the presence of the user_setup_url
        // parameter.
        if ($message->isOpenID1()) {
            $user_setup_url = $message->getArg(Auth_OpenID_OPENID1_NS,
                                               'user_setup_url');
            if ($user_setup_url !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @access private
     */
    function _doIdRes($message, $endpoint, $return_to)
    {
        // Checks for presence of appropriate fields (and checks
        // signed list fields)
        $result = $this->_idResCheckForFields($message);

        if (Auth_OpenID::isFailure($result)) {
            return $result;
        }

        if (!$this->_checkReturnTo($message, $return_to)) {
            return new Auth_OpenID_FailureResponse(null,
            sprintf("return_to does not match return URL. Expected %s, got %s",
                    $return_to,
                    $message->getArg(Auth_OpenID_OPENID_NS, 'return_to')));
        }

        // Verify discovery information:
        $result = $this->_verifyDiscoveryResults($message, $endpoint);

        if (Auth_OpenID::isFailure($result)) {
            return $result;
        }

        $endpoint = $result;

        $result = $this->_idResCheckSignature($message,
                                              $endpoint->server_url);

        if (Auth_OpenID::isFailure($result)) {
            return $result;
        }

        $result = $this->_idResCheckNonce($message, $endpoint);

        if (Auth_OpenID::isFailure($result)) {
            return $result;
        }

        $signed_list_str = $message->getArg(Auth_OpenID_OPENID_NS, 'signed',
                                            Auth_OpenID_NO_DEFAULT);
        if (Auth_OpenID::isFailure($signed_list_str)) {
            return $signed_list_str;
        }
        $signed_list = explode(',', $signed_list_str);

        $signed_fields = Auth_OpenID::addPrefix($signed_list, "openid.");

        return new Auth_OpenID_SuccessResponse($endpoint, $message,
                                               $signed_fields);

    }

    /**
     * @access private
     */
    function _checkReturnTo($message, $return_to)
    {
        // Check an OpenID message and its openid.return_to value
        // against a return_to URL from an application.  Return True
        // on success, False on failure.

        // Check the openid.return_to args against args in the
        // original message.
        $result = Auth_OpenID_GenericConsumer::_verifyReturnToArgs(
                                           $message->toPostArgs());
        if (Auth_OpenID::isFailure($result)) {
            return false;
        }

        // Check the return_to base URL against the one in the
        // message.
        $msg_return_to = $message->getArg(Auth_OpenID_OPENID_NS,
                                          'return_to');
        if (Auth_OpenID::isFailure($return_to)) {
            // XXX log me
            return false;
        }

        $return_to_parts = parse_url(Auth_OpenID_urinorm($return_to));
        $msg_return_to_parts = parse_url(Auth_OpenID_urinorm($msg_return_to));

        // If port is absent from both, add it so it's equal in the
        // check below.
        if ((!array_key_exists('port', $return_to_parts)) &&
            (!array_key_exists('port', $msg_return_to_parts))) {
            $return_to_parts['port'] = null;
            $msg_return_to_parts['port'] = null;
        }

        // If path is absent from both, add it so it's equal in the
        // check below.
        if ((!array_key_exists('path', $return_to_parts)) &&
            (!array_key_exists('path', $msg_return_to_parts))) {
            $return_to_parts['path'] = null;
            $msg_return_to_parts['path'] = null;
        }

        // The URL scheme, authority, and path MUST be the same
        // between the two URLs.
        foreach (array('scheme', 'host', 'port', 'path') as $component) {
            // If the url component is absent in either URL, fail.
            // There should always be a scheme, host, port, and path.
            if (!array_key_exists($component, $return_to_parts)) {
                return false;
            }

            if (!array_key_exists($component, $msg_return_to_parts)) {
                return false;
            }

            if (Auth_OpenID::arrayGet($return_to_parts, $component) !==
                Auth_OpenID::arrayGet($msg_return_to_parts, $component)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @access private
     */
    function _verifyReturnToArgs($query)
    {
        // Verify that the arguments in the return_to URL are present in this
        // response.

        $message = Auth_OpenID_Message::fromPostArgs($query);
        $return_to = $message->getArg(Auth_OpenID_OPENID_NS, 'return_to');

        if (Auth_OpenID::isFailure($return_to)) {
            return $return_to;
        }
        // XXX: this should be checked by _idResCheckForFields
        if (!$return_to) {
            return new Auth_OpenID_FailureResponse(null,
                           "Response has no return_to");
        }

        $parsed_url = parse_url($return_to);

        $q = array();
        if (array_key_exists('query', $parsed_url)) {
            $rt_query = $parsed_url['query'];
            $q = Auth_OpenID::parse_str($rt_query);
        }

        foreach ($q as $rt_key => $rt_value) {
            if (!array_key_exists($rt_key, $query)) {
                return new Auth_OpenID_FailureResponse(null,
                  sprintf("return_to parameter %s absent from query", $rt_key));
            } else {
                $value = $query[$rt_key];
                if ($rt_value != $value) {
                    return new Auth_OpenID_FailureResponse(null,
                      sprintf("parameter %s value %s does not match " .
                              "return_to value %s", $rt_key,
                              $value, $rt_value));
                }
            }
        }

        // Make sure all non-OpenID arguments in the response are also
        // in the signed return_to.
        $bare_args = $message->getArgs(Auth_OpenID_BARE_NS);
        foreach ($bare_args as $key => $value) {
            if (Auth_OpenID::arrayGet($q, $key) != $value) {
                return new Auth_OpenID_FailureResponse(null,
                  sprintf("Parameter %s = %s not in return_to URL",
                          $key, $value));
            }
        }

        return true;
    }

    /**
     * @access private
     */
    function _idResCheckSignature($message, $server_url)
    {
        $assoc_handle = $message->getArg(Auth_OpenID_OPENID_NS,
                                         'assoc_handle');
        if (Auth_OpenID::isFailure($assoc_handle)) {
            return $assoc_handle;
        }

        $assoc = $this->store->getAssociation($server_url, $assoc_handle);

        if ($assoc) {
            if ($assoc->getExpiresIn() <= 0) {
                // XXX: It might be a good idea sometimes to re-start
                // the authentication with a new association. Doing it
                // automatically opens the possibility for
                // denial-of-service by a server that just returns
                // expired associations (or really short-lived
                // associations)
                return new Auth_OpenID_FailureResponse(null,
                             'Association with ' . $server_url . ' expired');
            }

            if (!$assoc->checkMessageSignature($message)) {
                return new Auth_OpenID_FailureResponse(null,
                                                       "Bad signature");
            }
        } else {
            // It's not an association we know about.  Stateless mode
            // is our only possible path for recovery.  XXX - async
            // framework will not want to block on this call to
            // _checkAuth.
            if (!$this->_checkAuth($message, $server_url)) {
                return new Auth_OpenID_FailureResponse(null,
                             "Server denied check_authentication");
            }
        }

        return null;
    }

    /**
     * @access private
     */
    function _verifyDiscoveryResults($message, $endpoint=null)
    {
        if ($message->getOpenIDNamespace() == Auth_OpenID_OPENID2_NS) {
            return $this->_verifyDiscoveryResultsOpenID2($message,
                                                         $endpoint);
        } else {
            return $this->_verifyDiscoveryResultsOpenID1($message,
                                                         $endpoint);
        }
    }

    /**
     * @access private
     */
    function _verifyDiscoveryResultsOpenID1($message, $endpoint)
    {
        $claimed_id = $message->getArg(Auth_OpenID_BARE_NS,
                                $this->openid1_return_to_identifier_name);

        if (($endpoint === null) && ($claimed_id === null)) {
            return new Auth_OpenID_FailureResponse($endpoint,
              'When using OpenID 1, the claimed ID must be supplied, ' .
              'either by passing it through as a return_to parameter ' .
              'or by using a session, and supplied to the GenericConsumer ' .
              'as the argument to complete()');
        } else if (($endpoint !== null) && ($claimed_id === null)) {
            $claimed_id = $endpoint->claimed_id;
        }

        $to_match = new Auth_OpenID_ServiceEndpoint();
        $to_match->type_uris = array(Auth_OpenID_TYPE_1_1);
        $to_match->local_id = $message->getArg(Auth_OpenID_OPENID1_NS,
                                               'identity');

        // Restore delegate information from the initiation phase
        $to_match->claimed_id = $claimed_id;

        if ($to_match->local_id === null) {
            return new Auth_OpenID_FailureResponse($endpoint,
                         "Missing required field openid.identity");
        }

        $to_match_1_0 = $to_match->copy();
        $to_match_1_0->type_uris = array(Auth_OpenID_TYPE_1_0);

        if ($endpoint !== null) {
            $result = $this->_verifyDiscoverySingle($endpoint, $to_match);

            if (is_a($result, 'Auth_OpenID_TypeURIMismatch')) {
                $result = $this->_verifyDiscoverySingle($endpoint,
                                                        $to_match_1_0);
            }

            if (Auth_OpenID::isFailure($result)) {
                // oidutil.log("Error attempting to use stored
                //             discovery information: " + str(e))
                //             oidutil.log("Attempting discovery to
                //             verify endpoint")
            } else {
                return $endpoint;
            }
        }

        // Endpoint is either bad (failed verification) or None
        return $this->_discoverAndVerify($to_match->claimed_id,
                                         array($to_match, $to_match_1_0));
    }

    /**
     * @access private
     */
    function _verifyDiscoverySingle($endpoint, $to_match)
    {
        // Every type URI that's in the to_match endpoint has to be
        // present in the discovered endpoint.
        foreach ($to_match->type_uris as $type_uri) {
            if (!$endpoint->usesExtension($type_uri)) {
                return new Auth_OpenID_TypeURIMismatch($endpoint,
                             "Required type ".$type_uri." not present");
            }
        }

        // Fragments do not influence discovery, so we can't compare a
        // claimed identifier with a fragment to discovered
        // information.
        list($defragged_claimed_id, $_) =
            Auth_OpenID::urldefrag($to_match->claimed_id);

        if ($defragged_claimed_id != $endpoint->claimed_id) {
            return new Auth_OpenID_FailureResponse($endpoint,
              sprintf('Claimed ID does not match (different subjects!), ' .
                      'Expected %s, got %s', $defragged_claimed_id,
                      $endpoint->claimed_id));
        }

        if ($to_match->getLocalID() != $endpoint->getLocalID()) {
            return new Auth_OpenID_FailureResponse($endpoint,
              sprintf('local_id mismatch. Expected %s, got %s',
                      $to_match->getLocalID(), $endpoint->getLocalID()));
        }

        // If the server URL is None, this must be an OpenID 1
        // response, because op_endpoint is a required parameter in
        // OpenID 2. In that case, we don't actually care what the
        // discovered server_url is, because signature checking or
        // check_auth should take care of that check for us.
        if ($to_match->server_url === null) {
            if ($to_match->preferredNamespace() != Auth_OpenID_OPENID1_NS) {
                return new Auth_OpenID_FailureResponse($endpoint,
                             "Preferred namespace mismatch (bug)");
            }
        } else if ($to_match->server_url != $endpoint->server_url) {
            return new Auth_OpenID_FailureResponse($endpoint,
              sprintf('OP Endpoint mismatch. Expected %s, got %s',
                      $to_match->server_url, $endpoint->server_url));
        }

        return null;
    }

    /**
     * @access private
     */
    function _verifyDiscoveryResultsOpenID2($message, $endpoint)
    {
        $to_match = new Auth_OpenID_ServiceEndpoint();
        $to_match->type_uris = array(Auth_OpenID_TYPE_2_0);
        $to_match->claimed_id = $message->getArg(Auth_OpenID_OPENID2_NS,
                                                 'claimed_id');

        $to_match->local_id = $message->getArg(Auth_OpenID_OPENID2_NS,
                                                'identity');

        $to_match->server_url = $message->getArg(Auth_OpenID_OPENID2_NS,
                                                 'op_endpoint');

        if ($to_match->server_url === null) {
            return new Auth_OpenID_FailureResponse($endpoint,
                         "OP Endpoint URL missing");
        }

        // claimed_id and identifier must both be present or both be
        // absent
        if (($to_match->claimed_id === null) &&
            ($to_match->local_id !== null)) {
            return new Auth_OpenID_FailureResponse($endpoint,
              'openid.identity is present without openid.claimed_id');
        }

        if (($to_match->claimed_id !== null) &&
            ($to_match->local_id === null)) {
            return new Auth_OpenID_FailureResponse($endpoint,
              'openid.claimed_id is present without openid.identity');
        }

        if ($to_match->claimed_id === null) {
            // This is a response without identifiers, so there's
            // really no checking that we can do, so return an
            // endpoint that's for the specified `openid.op_endpoint'
            return Auth_OpenID_ServiceEndpoint::fromOPEndpointURL(
                                                $to_match->server_url);
        }

        if (!$endpoint) {
            // The claimed ID doesn't match, so we have to do
            // discovery again. This covers not using sessions, OP
            // identifier endpoints and responses that didn't match
            // the original request.
            // oidutil.log('No pre-discovered information supplied.')
            return $this->_discoverAndVerify($to_match->claimed_id,
                                             array($to_match));
        } else {

            // The claimed ID matches, so we use the endpoint that we
            // discovered in initiation. This should be the most
            // common case.
            $result = $this->_verifyDiscoverySingle($endpoint, $to_match);

            if (Auth_OpenID::isFailure($result)) {
                $endpoint = $this->_discoverAndVerify($to_match->claimed_id,
                                                      array($to_match));
                if (Auth_OpenID::isFailure($endpoint)) {
                    return $endpoint;
                }
            }
        }

        // The endpoint we return should have the claimed ID from the
        // message we just verified, fragment and all.
        if ($endpoint->claimed_id != $to_match->claimed_id) {
            $endpoint->claimed_id = $to_match->claimed_id;
        }

        return $endpoint;
    }

    /**
     * @access private
     */
    function _discoverAndVerify($claimed_id, $to_match_endpoints)
    {
        // oidutil.log('Performing discovery on %s' % (claimed_id,))
        list($unused, $services) = call_user_func($this->discoverMethod,
                                                  $claimed_id,
                                                  $this->fetcher);

        if (!$services) {
            return new Auth_OpenID_FailureResponse(null,
              sprintf("No OpenID information found at %s",
                      $claimed_id));
        }

        return $this->_verifyDiscoveryServices($claimed_id, $services,
                                               $to_match_endpoints);
    }

    /**
     * @access private
     */
    function _verifyDiscoveryServices($claimed_id,
                                      &$services, &$to_match_endpoints)
    {
        // Search the services resulting from discovery to find one
        // that matches the information from the assertion

        foreach ($services as $endpoint) {
            foreach ($to_match_endpoints as $to_match_endpoint) {
                $result = $this->_verifyDiscoverySingle($endpoint,
                                                        $to_match_endpoint);

                if (!Auth_OpenID::isFailure($result)) {
                    // It matches, so discover verification has
                    // succeeded. Return this endpoint.
                    return $endpoint;
                }
            }
        }

        return new Auth_OpenID_FailureResponse(null,
          sprintf('No matching endpoint found after discovering %s',
                  $claimed_id));
    }

    /**
     * Extract the nonce from an OpenID 1 response.  Return the nonce
     * from the BARE_NS since we independently check the return_to
     * arguments are the same as those in the response message.
     *
     * See the openid1_nonce_query_arg_name class variable
     *
     * @returns $nonce The nonce as a string or null
     *
     * @access private
     */
    function _idResGetNonceOpenID1($message, $endpoint)
    {
        return $message->getArg(Auth_OpenID_BARE_NS,
                                $this->openid1_nonce_query_arg_name);
    }

    /**
     * @access private
     */
    function _idResCheckNonce($message, $endpoint)
    {
        if ($message->isOpenID1()) {
            // This indicates that the nonce was generated by the consumer
            $nonce = $this->_idResGetNonceOpenID1($message, $endpoint);
            $server_url = '';
        } else {
            $nonce = $message->getArg(Auth_OpenID_OPENID2_NS,
                                      'response_nonce');

            $server_url = $endpoint->server_url;
        }

        if ($nonce === null) {
            return new Auth_OpenID_FailureResponse($endpoint,
                                     "Nonce missing from response");
        }

        $parts = Auth_OpenID_splitNonce($nonce);

        if ($parts === null) {
            return new Auth_OpenID_FailureResponse($endpoint,
                                     "Malformed nonce in response");
        }

        list($timestamp, $salt) = $parts;

        if (!$this->store->useNonce($server_url, $timestamp, $salt)) {
            return new Auth_OpenID_FailureResponse($endpoint,
                         "Nonce already used or out of range");
        }

        return null;
    }

    /**
     * @access private
     */
    function _idResCheckForFields($message)
    {
        $basic_fields = array('return_to', 'assoc_handle', 'sig', 'signed');
        $basic_sig_fields = array('return_to', 'identity');

        $require_fields = array(
            Auth_OpenID_OPENID2_NS => array_merge($basic_fields,
                                                  array('op_endpoint')),

            Auth_OpenID_OPENID1_NS => array_merge($basic_fields,
                                                  array('identity'))
            );

        $require_sigs = array(
            Auth_OpenID_OPENID2_NS => array_merge($basic_sig_fields,
                                                  array('response_nonce',
                                                        'claimed_id',
                                                        'assoc_handle')),
            Auth_OpenID_OPENID1_NS => array_merge($basic_sig_fields,
                                                  array('nonce'))
            );

        foreach ($require_fields[$message->getOpenIDNamespace()] as $field) {
            if (!$message->hasKey(Auth_OpenID_OPENID_NS, $field)) {
                return new Auth_OpenID_FailureResponse(null,
                             "Missing required field '".$field."'");
            }
        }

        $signed_list_str = $message->getArg(Auth_OpenID_OPENID_NS,
                                            'signed',
                                            Auth_OpenID_NO_DEFAULT);
        if (Auth_OpenID::isFailure($signed_list_str)) {
            return $signed_list_str;
        }
        $signed_list = explode(',', $signed_list_str);

        foreach ($require_sigs[$message->getOpenIDNamespace()] as $field) {
            // Field is present and not in signed list
            if ($message->hasKey(Auth_OpenID_OPENID_NS, $field) &&
                (!in_array($field, $signed_list))) {
                return new Auth_OpenID_FailureResponse(null,
                             "'".$field."' not signed");
            }
        }

        return null;
    }

    /**
     * @access private
     */
    function _checkAuth($message, $server_url)
    {
        $request = $this->_createCheckAuthRequest($message);
        if ($request === null) {
            return false;
        }

        $resp_message = $this->_makeKVPost($request, $server_url);
        if (($resp_message === null) ||
            (is_a($resp_message, 'Auth_OpenID_ServerErrorContainer'))) {
            return false;
        }

        return $this->_processCheckAuthResponse($resp_message, $server_url);
    }

    /**
     * @access private
     */
    function _createCheckAuthRequest($message)
    {
        $signed = $message->getArg(Auth_OpenID_OPENID_NS, 'signed');
        if ($signed) {
            foreach (explode(',', $signed) as $k) {
                $value = $message->getAliasedArg($k);
                if ($value === null) {
                    return null;
                }
            }
        }
        $ca_message = $message->copy();
        $ca_message->setArg(Auth_OpenID_OPENID_NS, 'mode',
                            'check_authentication');
        return $ca_message;
    }

    /**
     * @access private
     */
    function _processCheckAuthResponse($response, $server_url)
    {
        $is_valid = $response->getArg(Auth_OpenID_OPENID_NS, 'is_valid',
                                      'false');

        $invalidate_handle = $response->getArg(Auth_OpenID_OPENID_NS,
                                               'invalidate_handle');

        if ($invalidate_handle !== null) {
            $this->store->removeAssociation($server_url,
                                            $invalidate_handle);
        }

        if ($is_valid == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Adapt a POST response to a Message.
     *
     * @param $response Result of a POST to an OpenID endpoint.
     *
     * @access private
     */
    function _httpResponseToMessage($response, $server_url)
    {
        // Should this function be named Message.fromHTTPResponse instead?
        $response_message = Auth_OpenID_Message::fromKVForm($response->body);

        if ($response->status == 400) {
            return Auth_OpenID_ServerErrorContainer::fromMessage(
                        $response_message);
        } else if ($response->status != 200) {
            return null;
        }

        return $response_message;
    }

    /**
     * @access private
     */
    function _makeKVPost($message, $server_url)
    {
        $body = $message->toURLEncoded();
        $resp = $this->fetcher->post($server_url, $body);

        if ($resp === null) {
            return null;
        }

        return $this->_httpResponseToMessage($resp, $server_url);
    }

    /**
     * @access private
     */
    function _getAssociation($endpoint)
    {
        if (!$this->_use_assocs) {
            return null;
        }

        $assoc = $this->store->getAssociation($endpoint->server_url);

        if (($assoc === null) ||
            ($assoc->getExpiresIn() <= 0)) {

            $assoc = $this->_negotiateAssociation($endpoint);

            if ($assoc !== null) {
                $this->store->storeAssociation($endpoint->server_url,
                                               $assoc);
            }
        }

        return $assoc;
    }

    /**
     * Handle ServerErrors resulting from association requests.
     *
     * @return $result If server replied with an C{unsupported-type}
     * error, return a tuple of supported C{association_type},
     * C{session_type}.  Otherwise logs the error and returns null.
     *
     * @access private
     */
    function _extractSupportedAssociationType(&$server_error, &$endpoint,
                                              $assoc_type)
    {
        // Any error message whose code is not 'unsupported-type'
        // should be considered a total failure.
        if (($server_error->error_code != 'unsupported-type') ||
            ($server_error->message->isOpenID1())) {
            return null;
        }

        // The server didn't like the association/session type that we
        // sent, and it sent us back a message that might tell us how
        // to handle it.

        // Extract the session_type and assoc_type from the error
        // message
        $assoc_type = $server_error->message->getArg(Auth_OpenID_OPENID_NS,
                                                     'assoc_type');

        $session_type = $server_error->message->getArg(Auth_OpenID_OPENID_NS,
                                                       'session_type');

        if (($assoc_type === null) || ($session_type === null)) {
            return null;
        } else if (!$this->negotiator->isAllowed($assoc_type,
                                                 $session_type)) {
            return null;
        } else {
          return array($assoc_type, $session_type);
        }
    }

    /**
     * @access private
     */
    function _negotiateAssociation($endpoint)
    {
        // Get our preferred session/association type from the negotiatior.
        list($assoc_type, $session_type) = $this->negotiator->getAllowedType();

        $assoc = $this->_requestAssociation(
                           $endpoint, $assoc_type, $session_type);

        if (Auth_OpenID::isFailure($assoc)) {
            return null;
        }

        if (is_a($assoc, 'Auth_OpenID_ServerErrorContainer')) {
            $why = $assoc;

            $supportedTypes = $this->_extractSupportedAssociationType(
                                     $why, $endpoint, $assoc_type);

            if ($supportedTypes !== null) {
                list($assoc_type, $session_type) = $supportedTypes;

                // Attempt to create an association from the assoc_type
                // and session_type that the server told us it
                // supported.
                $assoc = $this->_requestAssociation(
                                   $endpoint, $assoc_type, $session_type);

                if (is_a($assoc, 'Auth_OpenID_ServerErrorContainer')) {
                    // Do not keep trying, since it rejected the
                    // association type that it told us to use.
                    // oidutil.log('Server %s refused its suggested association
                    //             'type: session_type=%s, assoc_type=%s'
                    //             % (endpoint.server_url, session_type,
                    //                assoc_type))
                    return null;
                } else {
                    return $assoc;
                }
            } else {
                return null;
            }
        } else {
            return $assoc;
        }
    }

    /**
     * @access private
     */
    function _requestAssociation($endpoint, $assoc_type, $session_type)
    {
        list($assoc_session, $args) = $this->_createAssociateRequest(
                                      $endpoint, $assoc_type, $session_type);

        $response_message = $this->_makeKVPost($args, $endpoint->server_url);

        if ($response_message === null) {
            // oidutil.log('openid.associate request failed: %s' % (why[0],))
            return null;
        } else if (is_a($response_message,
                        'Auth_OpenID_ServerErrorContainer')) {
            return $response_message;
        }

        return $this->_extractAssociation($response_message, $assoc_session);
    }

    /**
     * @access private
     */
    function _extractAssociation(&$assoc_response, &$assoc_session)
    {
        // Extract the common fields from the response, raising an
        // exception if they are not found
        $assoc_type = $assoc_response->getArg(
                         Auth_OpenID_OPENID_NS, 'assoc_type',
                         Auth_OpenID_NO_DEFAULT);

        if (Auth_OpenID::isFailure($assoc_type)) {
            return $assoc_type;
        }

        $assoc_handle = $assoc_response->getArg(
                           Auth_OpenID_OPENID_NS, 'assoc_handle',
                           Auth_OpenID_NO_DEFAULT);

        if (Auth_OpenID::isFailure($assoc_handle)) {
            return $assoc_handle;
        }

        // expires_in is a base-10 string. The Python parsing will
        // accept literals that have whitespace around them and will
        // accept negative values. Neither of these are really in-spec,
        // but we think it's OK to accept them.
        $expires_in_str = $assoc_response->getArg(
                             Auth_OpenID_OPENID_NS, 'expires_in',
                             Auth_OpenID_NO_DEFAULT);

        if (Auth_OpenID::isFailure($expires_in_str)) {
            return $expires_in_str;
        }

        $expires_in = Auth_OpenID::intval($expires_in_str);
        if ($expires_in === false) {

            $err = sprintf("Could not parse expires_in from association ".
                           "response %s", print_r($assoc_response, true));
            return new Auth_OpenID_FailureResponse(null, $err);
        }

        // OpenID 1 has funny association session behaviour.
        if ($assoc_response->isOpenID1()) {
            $session_type = $this->_getOpenID1SessionType($assoc_response);
        } else {
            $session_type = $assoc_response->getArg(
                               Auth_OpenID_OPENID2_NS, 'session_type',
                               Auth_OpenID_NO_DEFAULT);

            if (Auth_OpenID::isFailure($session_type)) {
                return $session_type;
            }
        }

        // Session type mismatch
        if ($assoc_session->session_type != $session_type) {
            if ($assoc_response->isOpenID1() &&
                ($session_type == 'no-encryption')) {
                // In OpenID 1, any association request can result in
                // a 'no-encryption' association response. Setting
                // assoc_session to a new no-encryption session should
                // make the rest of this function work properly for
                // that case.
                $assoc_session = new Auth_OpenID_PlainTextConsumerSession();
            } else {
                // Any other mismatch, regardless of protocol version
                // results in the failure of the association session
                // altogether.
                return null;
            }
        }

        // Make sure assoc_type is valid for session_type
        if (!in_array($assoc_type, $assoc_session->allowed_assoc_types)) {
            return null;
        }

        // Delegate to the association session to extract the secret
        // from the response, however is appropriate for that session
        // type.
        $secret = $assoc_session->extractSecret($assoc_response);

        if ($secret === null) {
            return null;
        }

        return Auth_OpenID_Association::fromExpiresIn(
                 $expires_in, $assoc_handle, $secret, $assoc_type);
    }

    /**
     * @access private
     */
    function _createAssociateRequest($endpoint, $assoc_type, $session_type)
    {
        if (array_key_exists($session_type, $this->session_types)) {
            $session_type_class = $this->session_types[$session_type];

            if (is_callable($session_type_class)) {
                $assoc_session = $session_type_class();
            } else {
                $assoc_session = new $session_type_class();
            }
        } else {
            return null;
        }

        $args = array(
            'mode' => 'associate',
            'assoc_type' => $assoc_type);

        if (!$endpoint->compatibilityMode()) {
            $args['ns'] = Auth_OpenID_OPENID2_NS;
        }

        // Leave out the session type if we're in compatibility mode
        // *and* it's no-encryption.
        if ((!$endpoint->compatibilityMode()) ||
            ($assoc_session->session_type != 'no-encryption')) {
            $args['session_type'] = $assoc_session->session_type;
        }

        $args = array_merge($args, $assoc_session->getRequest());
        $message = Auth_OpenID_Message::fromOpenIDArgs($args);
        return array($assoc_session, $message);
    }

    /**
     * Given an association response message, extract the OpenID 1.X
     * session type.
     *
     * This function mostly takes care of the 'no-encryption' default
     * behavior in OpenID 1.
     *
     * If the association type is plain-text, this function will
     * return 'no-encryption'
     *
     * @access private
     * @return $typ The association type for this message
     */
    function _getOpenID1SessionType($assoc_response)
    {
        // If it's an OpenID 1 message, allow session_type to default
        // to None (which signifies "no-encryption")
        $session_type = $assoc_response->getArg(Auth_OpenID_OPENID1_NS,
                                                'session_type');

        // Handle the differences between no-encryption association
        // respones in OpenID 1 and 2:

        // no-encryption is not really a valid session type for OpenID
        // 1, but we'll accept it anyway, while issuing a warning.
        if ($session_type == 'no-encryption') {
            // oidutil.log('WARNING: OpenID server sent "no-encryption"'
            //             'for OpenID 1.X')
        } else if (($session_type == '') || ($session_type === null)) {
            // Missing or empty session type is the way to flag a
            // 'no-encryption' response. Change the session type to
            // 'no-encryption' so that it can be handled in the same
            // way as OpenID 2 'no-encryption' respones.
            $session_type = 'no-encryption';
        }

        return $session_type;
    }
}

/**
 * This class represents an authentication request from a consumer to
 * an OpenID server.
 *
 * @package OpenID
 */
class Auth_OpenID_AuthRequest {

    /**
     * Initialize an authentication request with the specified token,
     * association, and endpoint.
     *
     * Users of this library should not create instances of this
     * class.  Instances of this class are created by the library when
     * needed.
     */
    function Auth_OpenID_AuthRequest(&$endpoint, $assoc)
    {
        $this->assoc = $assoc;
        $this->endpoint =& $endpoint;
        $this->return_to_args = array();
        $this->message = new Auth_OpenID_Message();
        $this->message->setOpenIDNamespace(
            $endpoint->preferredNamespace());
        $this->_anonymous = false;
    }

    /**
     * Add an extension to this checkid request.
     *
     * $extension_request: An object that implements the extension
     * request interface for adding arguments to an OpenID message.
     */
    function addExtension(&$extension_request)
    {
        $extension_request->toMessage($this->message);
    }

    /**
     * Add an extension argument to this OpenID authentication
     * request.
     *
     * Use caution when adding arguments, because they will be
     * URL-escaped and appended to the redirect URL, which can easily
     * get quite long.
     *
     * @param string $namespace The namespace for the extension. For
     * example, the simple registration extension uses the namespace
     * 'sreg'.
     *
     * @param string $key The key within the extension namespace. For
     * example, the nickname field in the simple registration
     * extension's key is 'nickname'.
     *
     * @param string $value The value to provide to the server for
     * this argument.
     */
    function addExtensionArg($namespace, $key, $value)
    {
        return $this->message->setArg($namespace, $key, $value);
    }

    /**
     * Set whether this request should be made anonymously. If a
     * request is anonymous, the identifier will not be sent in the
     * request. This is only useful if you are making another kind of
     * request with an extension in this request.
     *
     * Anonymous requests are not allowed when the request is made
     * with OpenID 1.
     */
    function setAnonymous($is_anonymous)
    {
        if ($is_anonymous && $this->message->isOpenID1()) {
            return false;
        } else {
            $this->_anonymous = $is_anonymous;
            return true;
        }
    }

    /**
     * Produce a {@link Auth_OpenID_Message} representing this
     * request.
     *
     * @param string $realm The URL (or URL pattern) that identifies
     * your web site to the user when she is authorizing it.
     *
     * @param string $return_to The URL that the OpenID provider will
     * send the user back to after attempting to verify her identity.
     *
     * Not specifying a return_to URL means that the user will not be
     * returned to the site issuing the request upon its completion.
     *
     * @param bool $immediate If true, the OpenID provider is to send
     * back a response immediately, useful for behind-the-scenes
     * authentication attempts.  Otherwise the OpenID provider may
     * engage the user before providing a response.  This is the
     * default case, as the user may need to provide credentials or
     * approve the request before a positive response can be sent.
     */
    function getMessage($realm, $return_to=null, $immediate=false)
    {
        if ($return_to) {
            $return_to = Auth_OpenID::appendArgs($return_to,
                                                 $this->return_to_args);
        } else if ($immediate) {
            // raise ValueError(
            //     '"return_to" is mandatory when
            //using "checkid_immediate"')
            return new Auth_OpenID_FailureResponse(null,
              "'return_to' is mandatory when using checkid_immediate");
        } else if ($this->message->isOpenID1()) {
            // raise ValueError('"return_to" is
            // mandatory for OpenID 1 requests')
            return new Auth_OpenID_FailureResponse(null,
              "'return_to' is mandatory for OpenID 1 requests");
        } else if ($this->return_to_args) {
            // raise ValueError('extra "return_to" arguments
            // were specified, but no return_to was specified')
            return new Auth_OpenID_FailureResponse(null,
              "extra 'return_to' arguments where specified, " .
              "but no return_to was specified");
        }

        if ($immediate) {
            $mode = 'checkid_immediate';
        } else {
            $mode = 'checkid_setup';
        }

        $message = $this->message->copy();
        if ($message->isOpenID1()) {
            $realm_key = 'trust_root';
        } else {
            $realm_key = 'realm';
        }

        $message->updateArgs(Auth_OpenID_OPENID_NS,
                             array(
                                   $realm_key => $realm,
                                   'mode' => $mode,
                                   'return_to' => $return_to));

        if (!$this->_anonymous) {
            if ($this->endpoint->isOPIdentifier()) {
                // This will never happen when we're in compatibility
                // mode, as long as isOPIdentifier() returns False
                // whenever preferredNamespace() returns OPENID1_NS.
                $claimed_id = $request_identity =
                    Auth_OpenID_IDENTIFIER_SELECT;
            } else {
                $request_identity = $this->endpoint->getLocalID();
                $claimed_id = $this->endpoint->claimed_id;
            }

            // This is true for both OpenID 1 and 2
            $message->setArg(Auth_OpenID_OPENID_NS, 'identity',
                             $request_identity);

            if ($message->isOpenID2()) {
                $message->setArg(Auth_OpenID_OPENID2_NS, 'claimed_id',
                                 $claimed_id);
            }
        }

        if ($this->assoc) {
            $message->setArg(Auth_OpenID_OPENID_NS, 'assoc_handle',
                             $this->assoc->handle);
        }

        return $message;
    }

    function redirectURL($realm, $return_to = null,
                         $immediate = false)
    {
        $message = $this->getMessage($realm, $return_to, $immediate);

        if (Auth_OpenID::isFailure($message)) {
            return $message;
        }

        return $message->toURL($this->endpoint->server_url);
    }

    /**
     * Get html for a form to submit this request to the IDP.
     *
     * form_tag_attrs: An array of attributes to be added to the form
     * tag. 'accept-charset' and 'enctype' have defaults that can be
     * overridden. If a value is supplied for 'action' or 'method', it
     * will be replaced.
     */
    function formMarkup($realm, $return_to=null, $immediate=false,
                        $form_tag_attrs=null)
    {
        $message = $this->getMessage($realm, $return_to, $immediate);

        if (Auth_OpenID::isFailure($message)) {
            return $message;
        }

        return $message->toFormMarkup($this->endpoint->server_url,
                                      $form_tag_attrs);
    }

    /**
     * Get a complete html document that will autosubmit the request
     * to the IDP.
     *
     * Wraps formMarkup.  See the documentation for that function.
     */
    function htmlMarkup($realm, $return_to=null, $immediate=false,
                        $form_tag_attrs=null)
    {
        $form = $this->formMarkup($realm, $return_to, $immediate,
                                  $form_tag_attrs);

        if (Auth_OpenID::isFailure($form)) {
            return $form;
        }
        return Auth_OpenID::autoSubmitHTML($form);
    }

    function shouldSendRedirect()
    {
        return $this->endpoint->compatibilityMode();
    }
}

/**
 * The base class for responses from the Auth_OpenID_Consumer.
 *
 * @package OpenID
 */
class Auth_OpenID_ConsumerResponse {
    var $status = null;

    function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        if ($endpoint === null) {
            $this->identity_url = null;
        } else {
            $this->identity_url = $endpoint->claimed_id;
        }
    }

    /**
     * Return the display identifier for this response.
     *
     * The display identifier is related to the Claimed Identifier, but the
     * two are not always identical.  The display identifier is something the
     * user should recognize as what they entered, whereas the response's
     * claimed identifier (in the identity_url attribute) may have extra
     * information for better persistence.
     *
     * URLs will be stripped of their fragments for display.  XRIs will
     * display the human-readable identifier (i-name) instead of the
     * persistent identifier (i-number).
     *
     * Use the display identifier in your user interface.  Use
     * identity_url for querying your database or authorization server.
     *
     */
    function getDisplayIdentifier()
    {
        if ($this->endpoint !== null) {
            return $this->endpoint->getDisplayIdentifier();
        }
        return null;
    }
}

/**
 * A response with a status of Auth_OpenID_SUCCESS. Indicates that
 * this request is a successful acknowledgement from the OpenID server
 * that the supplied URL is, indeed controlled by the requesting
 * agent.  This has three relevant attributes:
 *
 * claimed_id - The identity URL that has been authenticated
 *
 * signed_args - The arguments in the server's response that were
 * signed and verified.
 *
 * status - Auth_OpenID_SUCCESS.
 *
 * @package OpenID
 */
class Auth_OpenID_SuccessResponse extends Auth_OpenID_ConsumerResponse {
    var $status = Auth_OpenID_SUCCESS;

    /**
     * @access private
     */
    function Auth_OpenID_SuccessResponse($endpoint, $message, $signed_args=null)
    {
        $this->endpoint = $endpoint;
        $this->identity_url = $endpoint->claimed_id;
        $this->signed_args = $signed_args;
        $this->message = $message;

        if ($this->signed_args === null) {
            $this->signed_args = array();
        }
    }

    /**
     * Extract signed extension data from the server's response.
     *
     * @param string $prefix The extension namespace from which to
     * extract the extension data.
     */
    function extensionResponse($namespace_uri, $require_signed)
    {
        if ($require_signed) {
            return $this->getSignedNS($namespace_uri);
        } else {
            return $this->message->getArgs($namespace_uri);
        }
    }

    function isOpenID1()
    {
        return $this->message->isOpenID1();
    }

    function isSigned($ns_uri, $ns_key)
    {
        // Return whether a particular key is signed, regardless of
        // its namespace alias
        return in_array($this->message->getKey($ns_uri, $ns_key),
                        $this->signed_args);
    }

    function getSigned($ns_uri, $ns_key, $default = null)
    {
        // Return the specified signed field if available, otherwise
        // return default
        if ($this->isSigned($ns_uri, $ns_key)) {
            return $this->message->getArg($ns_uri, $ns_key, $default);
        } else {
            return $default;
        }
    }

    function getSignedNS($ns_uri)
    {
        $args = array();

        $msg_args = $this->message->getArgs($ns_uri);
        if (Auth_OpenID::isFailure($msg_args)) {
            return null;
        }

        foreach ($msg_args as $key => $value) {
            if (!$this->isSigned($ns_uri, $key)) {
                return null;
            }
        }

        return $msg_args;
    }

    /**
     * Get the openid.return_to argument from this response.
     *
     * This is useful for verifying that this request was initiated by
     * this consumer.
     *
     * @return string $return_to The return_to URL supplied to the
     * server on the initial request, or null if the response did not
     * contain an 'openid.return_to' argument.
    */
    function getReturnTo()
    {
        return $this->getSigned(Auth_OpenID_OPENID_NS, 'return_to');
    }
}

/**
 * A response with a status of Auth_OpenID_FAILURE. Indicates that the
 * OpenID protocol has failed. This could be locally or remotely
 * triggered.  This has three relevant attributes:
 *
 * claimed_id - The identity URL for which authentication was
 * attempted, if it can be determined.  Otherwise, null.
 *
 * message - A message indicating why the request failed, if one is
 * supplied.  Otherwise, null.
 *
 * status - Auth_OpenID_FAILURE.
 *
 * @package OpenID
 */
class Auth_OpenID_FailureResponse extends Auth_OpenID_ConsumerResponse {
    var $status = Auth_OpenID_FAILURE;

    function Auth_OpenID_FailureResponse($endpoint, $message = null,
                                         $contact = null, $reference = null)
    {
        $this->setEndpoint($endpoint);
        $this->message = $message;
        $this->contact = $contact;
        $this->reference = $reference;
    }
}

/**
 * A specific, internal failure used to detect type URI mismatch.
 *
 * @package OpenID
 */
class Auth_OpenID_TypeURIMismatch extends Auth_OpenID_FailureResponse {
}

/**
 * Exception that is raised when the server returns a 400 response
 * code to a direct request.
 *
 * @package OpenID
 */
class Auth_OpenID_ServerErrorContainer {
    function Auth_OpenID_ServerErrorContainer($error_text,
                                              $error_code,
                                              $message)
    {
        $this->error_text = $error_text;
        $this->error_code = $error_code;
        $this->message = $message;
    }

    /**
     * @access private
     */
    function fromMessage($message)
    {
        $error_text = $message->getArg(
           Auth_OpenID_OPENID_NS, 'error', '<no error message supplied>');
        $error_code = $message->getArg(Auth_OpenID_OPENID_NS, 'error_code');
        return new Auth_OpenID_ServerErrorContainer($error_text,
                                                    $error_code,
                                                    $message);
    }
}

/**
 * A response with a status of Auth_OpenID_CANCEL. Indicates that the
 * user cancelled the OpenID authentication request.  This has two
 * relevant attributes:
 *
 * claimed_id - The identity URL for which authentication was
 * attempted, if it can be determined.  Otherwise, null.
 *
 * status - Auth_OpenID_SUCCESS.
 *
 * @package OpenID
 */
class Auth_OpenID_CancelResponse extends Auth_OpenID_ConsumerResponse {
    var $status = Auth_OpenID_CANCEL;

    function Auth_OpenID_CancelResponse($endpoint)
    {
        $this->setEndpoint($endpoint);
    }
}

/**
 * A response with a status of Auth_OpenID_SETUP_NEEDED. Indicates
 * that the request was in immediate mode, and the server is unable to
 * authenticate the user without further interaction.
 *
 * claimed_id - The identity URL for which authentication was
 * attempted.
 *
 * setup_url - A URL that can be used to send the user to the server
 * to set up for authentication. The user should be redirected in to
 * the setup_url, either in the current window or in a new browser
 * window.  Null in OpenID 2.
 *
 * status - Auth_OpenID_SETUP_NEEDED.
 *
 * @package OpenID
 */
class Auth_OpenID_SetupNeededResponse extends Auth_OpenID_ConsumerResponse {
    var $status = Auth_OpenID_SETUP_NEEDED;

    function Auth_OpenID_SetupNeededResponse($endpoint,
                                             $setup_url = null)
    {
        $this->setEndpoint($endpoint);
        $this->setup_url = $setup_url;
    }
}

?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.tmp/flat/1/2/3/Server.php */ ?>
<?php /* C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.0/openid.packed.php.prep//1/2/3/Server.php */ ?>
<?php

/**
 * OpenID server protocol and logic.
 *
 * Overview
 *
 * An OpenID server must perform three tasks:
 *
 *  1. Examine the incoming request to determine its nature and validity.
 *  2. Make a decision about how to respond to this request.
 *  3. Format the response according to the protocol.
 *
 * The first and last of these tasks may performed by the {@link
 * Auth_OpenID_Server::decodeRequest()} and {@link
 * Auth_OpenID_Server::encodeResponse} methods.  Who gets to do the
 * intermediate task -- deciding how to respond to the request -- will
 * depend on what type of request it is.
 *
 * If it's a request to authenticate a user (a 'checkid_setup' or
 * 'checkid_immediate' request), you need to decide if you will assert
 * that this user may claim the identity in question.  Exactly how you
 * do that is a matter of application policy, but it generally
 * involves making sure the user has an account with your system and
 * is logged in, checking to see if that identity is hers to claim,
 * and verifying with the user that she does consent to releasing that
 * information to the party making the request.
 *
 * Examine the properties of the {@link Auth_OpenID_CheckIDRequest}
 * object, and if and when you've come to a decision, form a response
 * by calling {@link Auth_OpenID_CheckIDRequest::answer()}.
 *
 * Other types of requests relate to establishing associations between
 * client and server and verifing the authenticity of previous
 * communications.  {@link Auth_OpenID_Server} contains all the logic
 * and data necessary to respond to such requests; just pass it to
 * {@link Auth_OpenID_Server::handleRequest()}.
 *
 * OpenID Extensions
 *
 * Do you want to provide other information for your users in addition
 * to authentication?  Version 1.2 of the OpenID protocol allows
 * consumers to add extensions to their requests.  For example, with
 * sites using the Simple Registration
 * Extension
 * (http://www.openidenabled.com/openid/simple-registration-extension/),
 * a user can agree to have their nickname and e-mail address sent to
 * a site when they sign up.
 *
 * Since extensions do not change the way OpenID authentication works,
 * code to handle extension requests may be completely separate from
 * the {@link Auth_OpenID_Request} class here.  But you'll likely want
 * data sent back by your extension to be signed.  {@link
 * Auth_OpenID_ServerResponse} provides methods with which you can add
 * data to it which can be signed with the other data in the OpenID
 * signature.
 *
 * For example:
 *
 * <pre>  // when request is a checkid_* request
 *  $response = $request->answer(true);
 *  // this will a signed 'openid.sreg.timezone' parameter to the response
 *  response.addField('sreg', 'timezone', 'America/Los_Angeles')</pre>
 *
 * Stores
 *
 * The OpenID server needs to maintain state between requests in order
 * to function.  Its mechanism for doing this is called a store.  The
 * store interface is defined in Interface.php.  Additionally, several
 * concrete store implementations are provided, so that most sites
 * won't need to implement a custom store.  For a store backed by flat
 * files on disk, see {@link Auth_OpenID_FileStore}.  For stores based
 * on MySQL, SQLite, or PostgreSQL, see the {@link
 * Auth_OpenID_SQLStore} subclasses.
 *
 * Upgrading
 *
 * The keys by which a server looks up associations in its store have
 * changed in version 1.2 of this library.  If your store has entries
 * created from version 1.0 code, you should empty it.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Required imports
 */
//require_once "Auth/OpenID.php";
//require_once "Auth/OpenID/Association.php";
//require_once "Auth/OpenID/CryptUtil.php";
//require_once "Auth/OpenID/BigMath.php";
//require_once "Auth/OpenID/DiffieHellman.php";
//require_once "Auth/OpenID/KVForm.php";
//require_once "Auth/OpenID/TrustRoot.php";
//require_once "Auth/OpenID/ServerRequest.php";
//require_once "Auth/OpenID/Message.php";
//require_once "Auth/OpenID/Nonce.php";

define('AUTH_OPENID_HTTP_OK', 200);
define('AUTH_OPENID_HTTP_REDIRECT', 302);
define('AUTH_OPENID_HTTP_ERROR', 400);

/**
 * @access private
 */
global $_Auth_OpenID_Request_Modes;
$_Auth_OpenID_Request_Modes = array('checkid_setup',
                                    'checkid_immediate');

/**
 * @access private
 */
define('Auth_OpenID_ENCODE_KVFORM', 'kfvorm');

/**
 * @access private
 */
define('Auth_OpenID_ENCODE_URL', 'URL/redirect');

/**
 * @access private
 */
define('Auth_OpenID_ENCODE_HTML_FORM', 'HTML form');

/**
 * @access private
 */
function Auth_OpenID_isError($obj, $cls = 'Auth_OpenID_ServerError')
{
    return is_a($obj, $cls);
}

/**
 * An error class which gets instantiated and returned whenever an
 * OpenID protocol error occurs.  Be prepared to use this in place of
 * an ordinary server response.
 *
 * @package OpenID
 */
class Auth_OpenID_ServerError {
    /**
     * @access private
     */
    function Auth_OpenID_ServerError($message = null, $text = null,
                                     $reference = null, $contact = null)
    {
        $this->message = $message;
        $this->text = $text;
        $this->contact = $contact;
        $this->reference = $reference;
    }

    function getReturnTo()
    {
        if ($this->message &&
            $this->message->hasKey(Auth_OpenID_OPENID_NS, 'return_to')) {
            return $this->message->getArg(Auth_OpenID_OPENID_NS,
                                          'return_to');
        } else {
            return null;
        }
    }

    /**
     * Returns the return_to URL for the request which caused this
     * error.
     */
    function hasReturnTo()
    {
        return $this->getReturnTo() !== null;
    }

    /**
     * Encodes this error's response as a URL suitable for
     * redirection.  If the response has no return_to, another
     * Auth_OpenID_ServerError is returned.
     */
    function encodeToURL()
    {
        if (!$this->message) {
            return null;
        }

        $msg = $this->toMessage();
        return $msg->toURL($this->getReturnTo());
    }

    /**
     * Encodes the response to key-value form.  This is a
     * machine-readable format used to respond to messages which came
     * directly from the consumer and not through the user-agent.  See
     * the OpenID specification.
     */
    function encodeToKVForm()
    {
        return Auth_OpenID_KVForm::fromArray(
                                      array('mode' => 'error',
                                            'error' => $this->toString()));
    }

    function toFormMarkup($form_tag_attrs=null)
    {
        $msg = $this->toMessage();
        return $msg->toFormMarkup($this->getReturnTo(), $form_tag_attrs);
    }

    function toHTML($form_tag_attrs=null)
    {
        return Auth_OpenID::autoSubmitHTML(
                      $this->toFormMarkup($form_tag_attrs));
    }

    function toMessage()
    {
        // Generate a Message object for sending to the relying party,
        // after encoding.
        $namespace = $this->message->getOpenIDNamespace();
        $reply = new Auth_OpenID_Message($namespace);
        $reply->setArg(Auth_OpenID_OPENID_NS, 'mode', 'error');
        $reply->setArg(Auth_OpenID_OPENID_NS, 'error', $this->toString());

        if ($this->contact !== null) {
            $reply->setArg(Auth_OpenID_OPENID_NS, 'contact', $this->contact);
        }

        if ($this->reference !== null) {
            $reply->setArg(Auth_OpenID_OPENID_NS, 'reference',
                           $this->reference);
        }

        return $reply;
    }

    /**
     * Returns one of Auth_OpenID_ENCODE_URL,
     * Auth_OpenID_ENCODE_KVFORM, or null, depending on the type of
     * encoding expected for this error's payload.
     */
    function whichEncoding()
    {
        global $_Auth_OpenID_Request_Modes;

        if ($this->hasReturnTo()) {
            if ($this->message->isOpenID2() &&
                (strlen($this->encodeToURL()) >
                   Auth_OpenID_OPENID1_URL_LIMIT)) {
                return Auth_OpenID_ENCODE_HTML_FORM;
            } else {
                return Auth_OpenID_ENCODE_URL;
            }
        }

        if (!$this->message) {
            return null;
        }

        $mode = $this->message->getArg(Auth_OpenID_OPENID_NS,
                                       'mode');

        if ($mode) {
            if (!in_array($mode, $_Auth_OpenID_Request_Modes)) {
                return Auth_OpenID_ENCODE_KVFORM;
            }
        }
        return null;
    }

    /**
     * Returns this error message.
     */
    function toString()
    {
        if ($this->text) {
            return $this->text;
        } else {
            return get_class($this) . " error";
        }
    }
}

/**
 * Error returned by the server code when a return_to is absent from a
 * request.
 *
 * @package OpenID
 */
class Auth_OpenID_NoReturnToError extends Auth_OpenID_ServerError {
    function Auth_OpenID_NoReturnToError($message = null,
                                         $text = "No return_to URL available")
    {
        parent::Auth_OpenID_ServerError($message, $text);
    }

    function toString()
    {
        return "No return_to available";
    }
}

/**
 * An error indicating that the return_to URL is malformed.
 *
 * @package OpenID
 */
class Auth_OpenID_MalformedReturnURL extends Auth_OpenID_ServerError {
    function Auth_OpenID_MalformedReturnURL($message, $return_to)
    {
        $this->return_to = $return_to;
        parent::Auth_OpenID_ServerError($message, "malformed return_to URL");
    }
}

/**
 * This error is returned when the trust_root value is malformed.
 *
 * @package OpenID
 */
class Auth_OpenID_MalformedTrustRoot extends Auth_OpenID_ServerError {
    function Auth_OpenID_MalformedTrustRoot($message = null,
                                            $text = "Malformed trust root")
    {
        parent::Auth_OpenID_ServerError($message, $text);
    }

    function toString()
    {
        return "Malformed trust root";
    }
}

/**
 * The base class for all server request classes.
 *
 * @package OpenID
 */
class Auth_OpenID_Request {
    var $mode = null;
}

/**
 * A request to verify the validity of a previous response.
 *
 * @package OpenID
 */
class Auth_OpenID_CheckAuthRequest extends Auth_OpenID_Request {
    var $mode = "check_authentication";
    var $invalidate_handle = null;

    function Auth_OpenID_CheckAuthRequest($assoc_handle, $signed,
                                          $invalidate_handle = null)
    {
        $this->assoc_handle = $assoc_handle;
        $this->signed = $signed;
        if ($invalidate_handle !== null) {
            $this->invalidate_handle = $invalidate_handle;
        }
        $this->namespace = Auth_OpenID_OPENID2_NS;
        $this->message = null;
    }

    function fromMessage($message, $server=null)
    {
        $required_keys = array('assoc_handle', 'sig', 'signed');

        foreach ($required_keys as $k) {
            if (!$message->getArg(Auth_OpenID_OPENID_NS, $k)) {
                return new Auth_OpenID_ServerError($message,
                    sprintf("%s request missing required parameter %s from \
                            query", "check_authentication", $k));
            }
        }

        $assoc_handle = $message->getArg(Auth_OpenID_OPENID_NS, 'assoc_handle');
        $sig = $message->getArg(Auth_OpenID_OPENID_NS, 'sig');

        $signed_list = $message->getArg(Auth_OpenID_OPENID_NS, 'signed');
        $signed_list = explode(",", $signed_list);

        $signed = $message;
        if ($signed->hasKey(Auth_OpenID_OPENID_NS, 'mode')) {
            $signed->setArg(Auth_OpenID_OPENID_NS, 'mode', 'id_res');
        }

        $result = new Auth_OpenID_CheckAuthRequest($assoc_handle, $signed);
        $result->message = $message;
        $result->sig = $sig;
        $result->invalidate_handle = $message->getArg(Auth_OpenID_OPENID_NS,
                                                      'invalidate_handle');
        return $result;
    }

    function answer(&$signatory)
    {
        $is_valid = $signatory->verify($this->assoc_handle, $this->signed);

        // Now invalidate that assoc_handle so it this checkAuth
        // message cannot be replayed.
        $signatory->invalidate($this->assoc_handle, true);
        $response = new Auth_OpenID_ServerResponse($this);

        $response->fields->setArg(Auth_OpenID_OPENID_NS,
                                  'is_valid',
                                  ($is_valid ? "true" : "false"));

        if ($this->invalidate_handle) {
            $assoc = $signatory->getAssociation($this->invalidate_handle,
                                                false);
            if (!$assoc) {
                $response->fields->setArg(Auth_OpenID_OPENID_NS,
                                          'invalidate_handle',
                                          $this->invalidate_handle);
            }
        }
        return $response;
    }
}

/**
 * A class implementing plaintext server sessions.
 *
 * @package OpenID
 */
class Auth_OpenID_PlainTextServerSession {
    /**
     * An object that knows how to handle association requests with no
     * session type.
     */
    var $session_type = 'no-encryption';
    var $needs_math = false;
    var $allowed_assoc_types = array('HMAC-SHA1', 'HMAC-SHA256');

    function fromMessage($unused_request)
    {
        return new Auth_OpenID_PlainTextServerSession();
    }

    function answer($secret)
    {
        return array('mac_key' => base64_encode($secret));
    }
}

/**
 * A class implementing DH-SHA1 server sessions.
 *
 * @package OpenID
 */
class Auth_OpenID_DiffieHellmanSHA1ServerSession {
    /**
     * An object that knows how to handle association requests with
     * the Diffie-Hellman session type.
     */

    var $session_type = 'DH-SHA1';
    var $needs_math = true;
    var $allowed_assoc_types = array('HMAC-SHA1');
    var $hash_func = 'Auth_OpenID_SHA1';

    function Auth_OpenID_DiffieHellmanSHA1ServerSession($dh, $consumer_pubkey)
    {
        $this->dh = $dh;
        $this->consumer_pubkey = $consumer_pubkey;
    }

    function getDH($message)
    {
        $dh_modulus = $message->getArg(Auth_OpenID_OPENID_NS, 'dh_modulus');
        $dh_gen = $message->getArg(Auth_OpenID_OPENID_NS, 'dh_gen');

        if ((($dh_modulus === null) && ($dh_gen !== null)) ||
            (($dh_gen === null) && ($dh_modulus !== null))) {

            if ($dh_modulus === null) {
                $missing = 'modulus';
            } else {
                $missing = 'generator';
            }

            return new Auth_OpenID_ServerError($message,
                                'If non-default modulus or generator is '.
                                'supplied, both must be supplied.  Missing '.
                                $missing);
        }

        $lib =& Auth_OpenID_getMathLib();

        if ($dh_modulus || $dh_gen) {
            $dh_modulus = $lib->base64ToLong($dh_modulus);
            $dh_gen = $lib->base64ToLong($dh_gen);
            if ($lib->cmp($dh_modulus, 0) == 0 ||
                $lib->cmp($dh_gen, 0) == 0) {
                return new Auth_OpenID_ServerError(
                  $message, "Failed to parse dh_mod or dh_gen");
            }
            $dh = new Auth_OpenID_DiffieHellman($dh_modulus, $dh_gen);
        } else {
            $dh = new Auth_OpenID_DiffieHellman();
        }

        $consumer_pubkey = $message->getArg(Auth_OpenID_OPENID_NS,
                                            'dh_consumer_public');
        if ($consumer_pubkey === null) {
            return new Auth_OpenID_ServerError($message,
                                  'Public key for DH-SHA1 session '.
                                  'not found in query');
        }

        $consumer_pubkey =
            $lib->base64ToLong($consumer_pubkey);

        if ($consumer_pubkey === false) {
            return new Auth_OpenID_ServerError($message,
                                       "dh_consumer_public is not base64");
        }

        return array($dh, $consumer_pubkey);
    }

    function fromMessage($message)
    {
        $result = Auth_OpenID_DiffieHellmanSHA1ServerSession::getDH($message);

        if (is_a($result, 'Auth_OpenID_ServerError')) {
            return $result;
        } else {
            list($dh, $consumer_pubkey) = $result;
            return new Auth_OpenID_DiffieHellmanSHA1ServerSession($dh,
                                                    $consumer_pubkey);
        }
    }

    function answer($secret)
    {
        $lib =& Auth_OpenID_getMathLib();
        $mac_key = $this->dh->xorSecret($this->consumer_pubkey, $secret,
                                        $this->hash_func);
        return array(
           'dh_server_public' =>
                $lib->longToBase64($this->dh->public),
           'enc_mac_key' => base64_encode($mac_key));
    }
}

/**
 * A class implementing DH-SHA256 server sessions.
 *
 * @package OpenID
 */
class Auth_OpenID_DiffieHellmanSHA256ServerSession
      extends Auth_OpenID_DiffieHellmanSHA1ServerSession {

    var $session_type = 'DH-SHA256';
    var $hash_func = 'Auth_OpenID_SHA256';
    var $allowed_assoc_types = array('HMAC-SHA256');

    function fromMessage($message)
    {
        $result = Auth_OpenID_DiffieHellmanSHA1ServerSession::getDH($message);

        if (is_a($result, 'Auth_OpenID_ServerError')) {
            return $result;
        } else {
            list($dh, $consumer_pubkey) = $result;
            return new Auth_OpenID_DiffieHellmanSHA256ServerSession($dh,
                                                      $consumer_pubkey);
        }
    }
}

/**
 * A request to associate with the server.
 *
 * @package OpenID
 */
class Auth_OpenID_AssociateRequest extends Auth_OpenID_Request {
    var $mode = "associate";

    function getSessionClasses()
    {
        return array(
          'no-encryption' => 'Auth_OpenID_PlainTextServerSession',
          'DH-SHA1' => 'Auth_OpenID_DiffieHellmanSHA1ServerSession',
          'DH-SHA256' => 'Auth_OpenID_DiffieHellmanSHA256ServerSession');
    }

    function Auth_OpenID_AssociateRequest(&$session, $assoc_type)
    {
        $this->session =& $session;
        $this->namespace = Auth_OpenID_OPENID2_NS;
        $this->assoc_type = $assoc_type;
    }

    function fromMessage($message, $server=null)
    {
        if ($message->isOpenID1()) {
            $session_type = $message->getArg(Auth_OpenID_OPENID_NS,
                                             'session_type');

            if ($session_type == 'no-encryption') {
                // oidutil.log('Received OpenID 1 request with a no-encryption '
                //             'assocaition session type. Continuing anyway.')
            } else if (!$session_type) {
                $session_type = 'no-encryption';
            }
        } else {
            $session_type = $message->getArg(Auth_OpenID_OPENID_NS,
                                             'session_type');
            if ($session_type === null) {
                return new Auth_OpenID_ServerError($message,
                  "session_type missing from request");
            }
        }

        $session_class = Auth_OpenID::arrayGet(
           Auth_OpenID_AssociateRequest::getSessionClasses(),
           $session_type);

        if ($session_class === null) {
            return new Auth_OpenID_ServerError($message,
                                               "Unknown session type " .
                                               $session_type);
        }

        $session = call_user_func(array($session_class, 'fromMessage'),
                                  $message);
        if (is_a($session, 'Auth_OpenID_ServerError')) {
            return $session;
        }

        $assoc_type = $message->getArg(Auth_OpenID_OPENID_NS,
                                       'assoc_type', 'HMAC-SHA1');

        if (!in_array($assoc_type, $session->allowed_assoc_types)) {
            $fmt = "Session type %s does not support association type %s";
            return new Auth_OpenID_ServerError($message,
              sprintf($fmt, $session_type, $assoc_type));
        }

        $obj = new Auth_OpenID_AssociateRequest($session, $assoc_type);
        $obj->message = $message;
        $obj->namespace = $message->getOpenIDNamespace();
        return $obj;
    }

    function answer($assoc)
    {
        $response = new Auth_OpenID_ServerResponse($this);
        $response->fields->updateArgs(Auth_OpenID_OPENID_NS,
           array(
                 'expires_in' => sprintf('%d', $assoc->getExpiresIn()),
                 'assoc_type' => $this->assoc_type,
                 'assoc_handle' => $assoc->handle));

        $response->fields->updateArgs(Auth_OpenID_OPENID_NS,
           $this->session->answer($assoc->secret));

        if (! ($this->session->session_type == 'no-encryption'
               && $this->message->isOpenID1())) {
            $response->fields->setArg(Auth_OpenID_OPENID_NS,
                                      'session_type',
                                      $this->session->session_type);
        }

        return $response;
    }

    function answerUnsupported($text_message,
                               $preferred_association_type=null,
                               $preferred_session_type=null)
    {
        if ($this->message->isOpenID1()) {
            return new Auth_OpenID_ServerError($this->message);
        }

        $response = new Auth_OpenID_ServerResponse($this);
        $response->fields->setArg(Auth_OpenID_OPENID_NS,
                                  'error_code', 'unsupported-type');
        $response->fields->setArg(Auth_OpenID_OPENID_NS,
                                  'error', $text_message);

        if ($preferred_association_type) {
            $response->fields->setArg(Auth_OpenID_OPENID_NS,
                                      'assoc_type',
                                      $preferred_association_type);
        }

        if ($preferred_session_type) {
            $response->fields->setArg(Auth_OpenID_OPENID_NS,
                                      'session_type',
                                      $preferred_session_type);
        }

        return $response;
    }
}

/**
 * A request to confirm the identity of a user.
 *
 * @package OpenID
 */
class Auth_OpenID_CheckIDRequest extends Auth_OpenID_Request {
    /**
     * Return-to verification callback.  Default is
     * Auth_OpenID_verifyReturnTo from TrustRoot.php.
     */
    var $verifyReturnTo = 'Auth_OpenID_verifyReturnTo';

    /**
     * The mode of this request.
     */
    var $mode = "checkid_setup"; // or "checkid_immediate"

    /**
     * Whether this request is for immediate mode.
     */
    var $immediate = false;

    /**
     * The trust_root value for this request.
     */
    var $trust_root = null;

    /**
     * The OpenID namespace for this request.
     * deprecated since version 2.0.2
     */
    var $namespace;

    function make(&$message, $identity, $return_to, $trust_root = null,
                  $immediate = false, $assoc_handle = null, $server = null)
    {
        if ($server === null) {
            return new Auth_OpenID_ServerError($message,
                                               "server must not be null");
        }

        if ($return_to &&
            !Auth_OpenID_TrustRoot::_parse($return_to)) {
            return new Auth_OpenID_MalformedReturnURL($message, $return_to);
        }

        $r = new Auth_OpenID_CheckIDRequest($identity, $return_to,
                                            $trust_root, $immediate,
                                            $assoc_handle, $server);

        $r->namespace = $message->getOpenIDNamespace();
        $r->message =& $message;

        if (!$r->trustRootValid()) {
            return new Auth_OpenID_UntrustedReturnURL($message,
                                                      $return_to,
                                                      $trust_root);
        } else {
            return $r;
        }
    }

    function Auth_OpenID_CheckIDRequest($identity, $return_to,
                                        $trust_root = null, $immediate = false,
                                        $assoc_handle = null, $server = null)
    {
        $this->namespace = Auth_OpenID_OPENID2_NS;
        $this->assoc_handle = $assoc_handle;
        $this->identity = $identity;
        $this->claimed_id = $identity;
        $this->return_to = $return_to;
        $this->trust_root = $trust_root;
        $this->server =& $server;

        if ($immediate) {
            $this->immediate = true;
            $this->mode = "checkid_immediate";
        } else {
            $this->immediate = false;
            $this->mode = "checkid_setup";
        }
    }

    function equals($other)
    {
        return (
                (is_a($other, 'Auth_OpenID_CheckIDRequest')) &&
                ($this->namespace == $other->namespace) &&
                ($this->assoc_handle == $other->assoc_handle) &&
                ($this->identity == $other->identity) &&
                ($this->claimed_id == $other->claimed_id) &&
                ($this->return_to == $other->return_to) &&
                ($this->trust_root == $other->trust_root));
    }

    /*
     * Does the relying party publish the return_to URL for this
     * response under the realm? It is up to the provider to set a
     * policy for what kinds of realms should be allowed. This
     * return_to URL verification reduces vulnerability to data-theft
     * attacks based on open proxies, corss-site-scripting, or open
     * redirectors.
     *
     * This check should only be performed after making sure that the
     * return_to URL matches the realm.
     *
     * @return true if the realm publishes a document with the
     * return_to URL listed, false if not or if discovery fails
     */
    function returnToVerified()
    {
        return call_user_func_array($this->verifyReturnTo,
                                    array($this->trust_root, $this->return_to));
    }

    function fromMessage(&$message, $server)
    {
        $mode = $message->getArg(Auth_OpenID_OPENID_NS, 'mode');
        $immediate = null;

        if ($mode == "checkid_immediate") {
            $immediate = true;
            $mode = "checkid_immediate";
        } else {
            $immediate = false;
            $mode = "checkid_setup";
        }

        $return_to = $message->getArg(Auth_OpenID_OPENID_NS,
                                      'return_to');

        if (($message->isOpenID1()) &&
            (!$return_to)) {
            $fmt = "Missing required field 'return_to' from checkid request";
            return new Auth_OpenID_ServerError($message, $fmt);
        }

        $identity = $message->getArg(Auth_OpenID_OPENID_NS,
                                     'identity');
        $claimed_id = $message->getArg(Auth_OpenID_OPENID_NS, 'claimed_id');
        if ($message->isOpenID1()) {
            if ($identity === null) {
                $s = "OpenID 1 message did not contain openid.identity";
                return new Auth_OpenID_ServerError($message, $s);
            }
        } else {
            if ($identity && !$claimed_id) {
                $s = "OpenID 2.0 message contained openid.identity but not " .
                  "claimed_id";
                return new Auth_OpenID_ServerError($message, $s);
            } else if ($claimed_id && !$identity) {
                $s = "OpenID 2.0 message contained openid.claimed_id " .
                  "but not identity";
                return new Auth_OpenID_ServerError($message, $s);
            }
        }

        // There's a case for making self.trust_root be a TrustRoot
        // here.  But if TrustRoot isn't currently part of the
        // "public" API, I'm not sure it's worth doing.
        if ($message->isOpenID1()) {
            $trust_root_param = 'trust_root';
        } else {
            $trust_root_param = 'realm';
        }
        $trust_root = $message->getArg(Auth_OpenID_OPENID_NS,
                                       $trust_root_param);
        if (! $trust_root) {
            $trust_root = $return_to;
        }

        if (! $message->isOpenID1() &&
            ($return_to === null) &&
            ($trust_root === null)) {
            return new Auth_OpenID_ServerError($message,
              "openid.realm required when openid.return_to absent");
        }

        $assoc_handle = $message->getArg(Auth_OpenID_OPENID_NS,
                                         'assoc_handle');

        $obj = Auth_OpenID_CheckIDRequest::make($message,
                                                $identity,
                                                $return_to,
                                                $trust_root,
                                                $immediate,
                                                $assoc_handle,
                                                $server);

        if (is_a($obj, 'Auth_OpenID_ServerError')) {
            return $obj;
        }

        $obj->claimed_id = $claimed_id;

        return $obj;
    }

    function idSelect()
    {
        // Is the identifier to be selected by the IDP?
        // So IDPs don't have to import the constant
        return $this->identity == Auth_OpenID_IDENTIFIER_SELECT;
    }

    function trustRootValid()
    {
        if (!$this->trust_root) {
            return true;
        }

        $tr = Auth_OpenID_TrustRoot::_parse($this->trust_root);
        if ($tr === false) {
            return new Auth_OpenID_MalformedTrustRoot($this->message,
                                                      $this->trust_root);
        }

        if ($this->return_to !== null) {
            return Auth_OpenID_TrustRoot::match($this->trust_root,
                                                $this->return_to);
        } else {
            return true;
        }
    }

    /**
     * Respond to this request.  Return either an
     * {@link Auth_OpenID_ServerResponse} or
     * {@link Auth_OpenID_ServerError}.
     *
     * @param bool $allow Allow this user to claim this identity, and
     * allow the consumer to have this information?
     *
     * @param string $server_url DEPRECATED.  Passing $op_endpoint to
     * the {@link Auth_OpenID_Server} constructor makes this optional.
     *
     * When an OpenID 1.x immediate mode request does not succeed, it
     * gets back a URL where the request may be carried out in a
     * not-so-immediate fashion.  Pass my URL in here (the fully
     * qualified address of this server's endpoint, i.e.
     * http://example.com/server), and I will use it as a base for the
     * URL for a new request.
     *
     * Optional for requests where {@link $immediate} is false or
     * $allow is true.
     *
     * @param string $identity The OP-local identifier to answer with.
     * Only for use when the relying party requested identifier
     * selection.
     *
     * @param string $claimed_id The claimed identifier to answer
     * with, for use with identifier selection in the case where the
     * claimed identifier and the OP-local identifier differ,
     * i.e. when the claimed_id uses delegation.
     *
     * If $identity is provided but this is not, $claimed_id will
     * default to the value of $identity.  When answering requests
     * that did not ask for identifier selection, the response
     * $claimed_id will default to that of the request.
     *
     * This parameter is new in OpenID 2.0.
     *
     * @return mixed
     */
    function answer($allow, $server_url = null, $identity = null,
                    $claimed_id = null)
    {
        if (!$this->return_to) {
            return new Auth_OpenID_NoReturnToError();
        }

        if (!$server_url) {
            if ((!$this->message->isOpenID1()) &&
                (!$this->server->op_endpoint)) {
                return new Auth_OpenID_ServerError(null,
                  "server should be constructed with op_endpoint to " .
                  "respond to OpenID 2.0 messages.");
            }

            $server_url = $this->server->op_endpoint;
        }

        if ($allow) {
            $mode = 'id_res';
        } else if ($this->message->isOpenID1()) {
            if ($this->immediate) {
                $mode = 'id_res';
            } else {
                $mode = 'cancel';
            }
        } else {
            if ($this->immediate) {
                $mode = 'setup_needed';
            } else {
                $mode = 'cancel';
            }
        }

        if (!$this->trustRootValid()) {
            return new Auth_OpenID_UntrustedReturnURL(null,
                                                      $this->return_to,
                                                      $this->trust_root);
        }

        $response = new Auth_OpenID_ServerResponse($this);

        if ($claimed_id &&
            ($this->message->isOpenID1())) {
            return new Auth_OpenID_ServerError(null,
              "claimed_id is new in OpenID 2.0 and not " .
              "available for ".$this->namespace);
        }

        if ($identity && !$claimed_id) {
            $claimed_id = $identity;
        }

        if ($allow) {

            if ($this->identity == Auth_OpenID_IDENTIFIER_SELECT) {
                if (!$identity) {
                    return new Auth_OpenID_ServerError(null,
                      "This request uses IdP-driven identifier selection.  " .
                      "You must supply an identifier in the response.");
                }

                $response_identity = $identity;
                $response_claimed_id = $claimed_id;

            } else if ($this->identity) {
                if ($identity &&
                    ($this->identity != $identity)) {
                    $fmt = "Request was for %s, cannot reply with identity %s";
                    return new Auth_OpenID_ServerError(null,
                      sprintf($fmt, $this->identity, $identity));
                }

                $response_identity = $this->identity;
                $response_claimed_id = $this->claimed_id;
            } else {
                if ($identity) {
                    return new Auth_OpenID_ServerError(null,
                      "This request specified no identity and " .
                      "you supplied ".$identity);
                }

                $response_identity = null;
            }

            if (($this->message->isOpenID1()) &&
                ($response_identity === null)) {
                return new Auth_OpenID_ServerError(null,
                  "Request was an OpenID 1 request, so response must " .
                  "include an identifier.");
            }

            $response->fields->updateArgs(Auth_OpenID_OPENID_NS,
                   array('mode' => $mode,
                         'op_endpoint' => $server_url,
                         'return_to' => $this->return_to,
                         'response_nonce' => Auth_OpenID_mkNonce()));

            if ($response_identity !== null) {
                $response->fields->setArg(
                                          Auth_OpenID_OPENID_NS,
                                          'identity',
                                          $response_identity);
                if ($this->message->isOpenID2()) {
                    $response->fields->setArg(
                                              Auth_OpenID_OPENID_NS,
                                              'claimed_id',
                                              $response_claimed_id);
                }
            }

        } else {
            $response->fields->setArg(Auth_OpenID_OPENID_NS,
                                      'mode', $mode);

            if ($this->immediate) {
                if (($this->message->isOpenID1()) &&
                    (!$server_url)) {
                    return new Auth_OpenID_ServerError(null,
                                 'setup_url is required for $allow=false \
                                  in OpenID 1.x immediate mode.');
                }

                $setup_request =& new Auth_OpenID_CheckIDRequest(
                                                $this->identity,
                                                $this->return_to,
                                                $this->trust_root,
                                                false,
                                                $this->assoc_handle,
                                                $this->server);
                $setup_request->message = $this->message;

                $setup_url = $setup_request->encodeToURL($server_url);

                if ($setup_url === null) {
                    return new Auth_OpenID_NoReturnToError();
                }

                $response->fields->setArg(Auth_OpenID_OPENID_NS,
                                          'user_setup_url',
                                          $setup_url);
            }
        }

        return $response;
    }

    function encodeToURL($server_url)
    {
        if (!$this->return_to) {
            return new Auth_OpenID_NoReturnToError();
        }

        // Imported from the alternate reality where these classes are
        // used in both the client and server code, so Requests are
        // Encodable too.  That's right, code imported from alternate
        // realities all for the love of you, id_res/user_setup_url.

        $q = array('mode' => $this->mode,
                   'identity' => $this->identity,
                   'claimed_id' => $this->claimed_id,
                   'return_to' => $this->return_to);

        if ($this->trust_root) {
            if ($this->message->isOpenID1()) {
                $q['trust_root'] = $this->trust_root;
            } else {
                $q['realm'] = $this->trust_root;
            }
        }

        if ($this->assoc_handle) {
            $q['assoc_handle'] = $this->assoc_handle;
        }

        $response = new Auth_OpenID_Message(
            $this->message->getOpenIDNamespace());
        $response->updateArgs(Auth_OpenID_OPENID_NS, $q);
        return $response->toURL($server_url);
    }

    function getCancelURL()
    {
        if (!$this->return_to) {
            return new Auth_OpenID_NoReturnToError();
        }

        if ($this->immediate) {
            return new Auth_OpenID_ServerError(null,
                                               "Cancel is not an appropriate \
                                               response to immediate mode \
                                               requests.");
        }

        $response = new Auth_OpenID_Message(
            $this->message->getOpenIDNamespace());
        $response->setArg(Auth_OpenID_OPENID_NS, 'mode', 'cancel');
        return $response->toURL($this->return_to);
    }
}

/**
 * This class encapsulates the response to an OpenID server request.
 *
 * @package OpenID
 */
class Auth_OpenID_ServerResponse {

    function Auth_OpenID_ServerResponse(&$request)
    {
        $this->request =& $request;
        $this->fields = new Auth_OpenID_Message($this->request->namespace);
    }

    function whichEncoding()
    {
      global $_Auth_OpenID_Request_Modes;

        if (in_array($this->request->mode, $_Auth_OpenID_Request_Modes)) {
            if ($this->fields->isOpenID2() &&
                (strlen($this->encodeToURL()) >
                   Auth_OpenID_OPENID1_URL_LIMIT)) {
                return Auth_OpenID_ENCODE_HTML_FORM;
            } else {
                return Auth_OpenID_ENCODE_URL;
            }
        } else {
            return Auth_OpenID_ENCODE_KVFORM;
        }
    }

    /*
     * Returns the form markup for this response.
     *
     * @return str
     */
    function toFormMarkup($form_tag_attrs=null)
    {
        return $this->fields->toFormMarkup($this->request->return_to,
                                           $form_tag_attrs);
    }

    /*
     * Returns an HTML document containing the form markup for this
     * response that autosubmits with javascript.
     */
    function toHTML()
    {
        return Auth_OpenID::autoSubmitHTML($this->toFormMarkup());
    }

    /*
     * Returns True if this response's encoding is ENCODE_HTML_FORM.
     * Convenience method for server authors.
     *
     * @return bool
     */
    function renderAsForm()
    {
        return $this->whichEncoding() == Auth_OpenID_ENCODE_HTML_FORM;
    }


    function encodeToURL()
    {
        return $this->fields->toURL($this->request->return_to);
    }

    function addExtension($extension_response)
    {
        $extension_response->toMessage($this->fields);
    }

    function needsSigning()
    {
        return $this->fields->getArg(Auth_OpenID_OPENID_NS,
                                     'mode') == 'id_res';
    }

    function encodeToKVForm()
    {
        return $this->fields->toKVForm();
    }
}

/**
 * A web-capable response object which you can use to generate a
 * user-agent response.
 *
 * @package OpenID
 */
class Auth_OpenID_WebResponse {
    var $code = AUTH_OPENID_HTTP_OK;
    var $body = "";

    function Auth_OpenID_WebResponse($code = null, $headers = null,
                                     $body = null)
    {
        if ($code) {
            $this->code = $code;
        }

        if ($headers !== null) {
            $this->headers = $headers;
        } else {
            $this->headers = array();
        }

        if ($body !== null) {
            $this->body = $body;
        }
    }
}

/**
 * Responsible for the signature of query data and the verification of
 * OpenID signature values.
 *
 * @package OpenID
 */
class Auth_OpenID_Signatory {

    // = 14 * 24 * 60 * 60; # 14 days, in seconds
    var $SECRET_LIFETIME = 1209600;

    // keys have a bogus server URL in them because the filestore
    // really does expect that key to be a URL.  This seems a little
    // silly for the server store, since I expect there to be only one
    // server URL.
    var $normal_key = 'http://localhost/|normal';
    var $dumb_key = 'http://localhost/|dumb';

    /**
     * Create a new signatory using a given store.
     */
    function Auth_OpenID_Signatory(&$store)
    {
        // assert store is not None
        $this->store =& $store;
    }

    /**
     * Verify, using a given association handle, a signature with
     * signed key-value pairs from an HTTP request.
     */
    function verify($assoc_handle, $message)
    {
        $assoc = $this->getAssociation($assoc_handle, true);
        if (!$assoc) {
            // oidutil.log("failed to get assoc with handle %r to verify sig %r"
            //             % (assoc_handle, sig))
            return false;
        }

        return $assoc->checkMessageSignature($message);
    }

    /**
     * Given a response, sign the fields in the response's 'signed'
     * list, and insert the signature into the response.
     */
    function sign($response)
    {
        $signed_response = $response;
        $assoc_handle = $response->request->assoc_handle;

        if ($assoc_handle) {
            // normal mode
            $assoc = $this->getAssociation($assoc_handle, false, false);
            if (!$assoc || ($assoc->getExpiresIn() <= 0)) {
                // fall back to dumb mode
                $signed_response->fields->setArg(Auth_OpenID_OPENID_NS,
                             'invalidate_handle', $assoc_handle);
                $assoc_type = ($assoc ? $assoc->assoc_type : 'HMAC-SHA1');

                if ($assoc && ($assoc->getExpiresIn() <= 0)) {
                    $this->invalidate($assoc_handle, false);
                }

                $assoc = $this->createAssociation(true, $assoc_type);
            }
        } else {
            // dumb mode.
            $assoc = $this->createAssociation(true);
        }

        $signed_response->fields = $assoc->signMessage(
                                      $signed_response->fields);
        return $signed_response;
    }

    /**
     * Make a new association.
     */
    function createAssociation($dumb = true, $assoc_type = 'HMAC-SHA1')
    {
        $secret = Auth_OpenID_CryptUtil::getBytes(
                    Auth_OpenID_getSecretSize($assoc_type));

        $uniq = base64_encode(Auth_OpenID_CryptUtil::getBytes(4));
        $handle = sprintf('{%s}{%x}{%s}', $assoc_type, intval(time()), $uniq);

        $assoc = Auth_OpenID_Association::fromExpiresIn(
                      $this->SECRET_LIFETIME, $handle, $secret, $assoc_type);

        if ($dumb) {
            $key = $this->dumb_key;
        } else {
            $key = $this->normal_key;
        }

        $this->store->storeAssociation($key, $assoc);
        return $assoc;
    }

    /**
     * Given an association handle, get the association from the
     * store, or return a ServerError or null if something goes wrong.
     */
    function getAssociation($assoc_handle, $dumb, $check_expiration=true)
    {
        if ($assoc_handle === null) {
            return new Auth_OpenID_ServerError(null,
                                     "assoc_handle must not be null");
        }

        if ($dumb) {
            $key = $this->dumb_key;
        } else {
            $key = $this->normal_key;
        }

        $assoc = $this->store->getAssociation($key, $assoc_handle);

        if (($assoc !== null) && ($assoc->getExpiresIn() <= 0)) {
            if ($check_expiration) {
                $this->store->removeAssociation($key, $assoc_handle);
                $assoc = null;
            }
        }

        return $assoc;
    }

    /**
     * Invalidate a given association handle.
     */
    function invalidate($assoc_handle, $dumb)
    {
        if ($dumb) {
            $key = $this->dumb_key;
        } else {
            $key = $this->normal_key;
        }
        $this->store->removeAssociation($key, $assoc_handle);
    }
}

/**
 * Encode an {@link Auth_OpenID_ServerResponse} to an
 * {@link Auth_OpenID_WebResponse}.
 *
 * @package OpenID
 */
class Auth_OpenID_Encoder {

    var $responseFactory = 'Auth_OpenID_WebResponse';

    /**
     * Encode an {@link Auth_OpenID_ServerResponse} and return an
     * {@link Auth_OpenID_WebResponse}.
     */
    function encode(&$response)
    {
        $cls = $this->responseFactory;

        $encode_as = $response->whichEncoding();
        if ($encode_as == Auth_OpenID_ENCODE_KVFORM) {
            $wr = new $cls(null, null, $response->encodeToKVForm());
            if (is_a($response, 'Auth_OpenID_ServerError')) {
                $wr->code = AUTH_OPENID_HTTP_ERROR;
            }
        } else if ($encode_as == Auth_OpenID_ENCODE_URL) {
            $location = $response->encodeToURL();
            $wr = new $cls(AUTH_OPENID_HTTP_REDIRECT,
                           array('location' => $location));
        } else if ($encode_as == Auth_OpenID_ENCODE_HTML_FORM) {
          $wr = new $cls(AUTH_OPENID_HTTP_OK, array(),
                         $response->toFormMarkup());
        } else {
            return new Auth_OpenID_EncodingError($response);
        }
        return $wr;
    }
}

/**
 * An encoder which also takes care of signing fields when required.
 *
 * @package OpenID
 */
class Auth_OpenID_SigningEncoder extends Auth_OpenID_Encoder {

    function Auth_OpenID_SigningEncoder(&$signatory)
    {
        $this->signatory =& $signatory;
    }

    /**
     * Sign an {@link Auth_OpenID_ServerResponse} and return an
     * {@link Auth_OpenID_WebResponse}.
     */
    function encode(&$response)
    {
        // the isinstance is a bit of a kludge... it means there isn't
        // really an adapter to make the interfaces quite match.
        if (!is_a($response, 'Auth_OpenID_ServerError') &&
            $response->needsSigning()) {

            if (!$this->signatory) {
                return new Auth_OpenID_ServerError(null,
                                       "Must have a store to sign request");
            }

            if ($response->fields->hasKey(Auth_OpenID_OPENID_NS, 'sig')) {
                return new Auth_OpenID_AlreadySigned($response);
            }
            $response = $this->signatory->sign($response);
        }

        return parent::encode($response);
    }
}

/**
 * Decode an incoming query into an Auth_OpenID_Request.
 *
 * @package OpenID
 */
class Auth_OpenID_Decoder {

    function Auth_OpenID_Decoder(&$server)
    {
        $this->server =& $server;

        $this->handlers = array(
            'checkid_setup' => 'Auth_OpenID_CheckIDRequest',
            'checkid_immediate' => 'Auth_OpenID_CheckIDRequest',
            'check_authentication' => 'Auth_OpenID_CheckAuthRequest',
            'associate' => 'Auth_OpenID_AssociateRequest'
            );
    }

    /**
     * Given an HTTP query in an array (key-value pairs), decode it
     * into an Auth_OpenID_Request object.
     */
    function decode($query)
    {
        if (!$query) {
            return null;
        }

        $message = Auth_OpenID_Message::fromPostArgs($query);

        if ($message === null) {
            /*
             * It's useful to have a Message attached to a
             * ProtocolError, so we override the bad ns value to build
             * a Message out of it.  Kinda kludgy, since it's made of
             * lies, but the parts that aren't lies are more useful
             * than a 'None'.
             */
            $old_ns = $query['openid.ns'];

            $query['openid.ns'] = Auth_OpenID_OPENID2_NS;
            $message = Auth_OpenID_Message::fromPostArgs($query);
            return new Auth_OpenID_ServerError(
                  $message,
                  sprintf("Invalid OpenID namespace URI: %s", $old_ns));
        }

        $mode = $message->getArg(Auth_OpenID_OPENID_NS, 'mode');
        if (!$mode) {
            return new Auth_OpenID_ServerError($message,
                                               "No mode value in message");
        }

        if (Auth_OpenID::isFailure($mode)) {
            return new Auth_OpenID_ServerError($message,
                                               $mode->message);
        }

        $handlerCls = Auth_OpenID::arrayGet($this->handlers, $mode,
                                            $this->defaultDecoder($message));

        if (!is_a($handlerCls, 'Auth_OpenID_ServerError')) {
            return call_user_func_array(array($handlerCls, 'fromMessage'),
                                        array($message, $this->server));
        } else {
            return $handlerCls;
        }
    }

    function defaultDecoder($message)
    {
        $mode = $message->getArg(Auth_OpenID_OPENID_NS, 'mode');

        if (Auth_OpenID::isFailure($mode)) {
            return new Auth_OpenID_ServerError($message,
                                               $mode->message);
        }

        return new Auth_OpenID_ServerError($message,
                       sprintf("Unrecognized OpenID mode %s", $mode));
    }
}

/**
 * An error that indicates an encoding problem occurred.
 *
 * @package OpenID
 */
class Auth_OpenID_EncodingError {
    function Auth_OpenID_EncodingError(&$response)
    {
        $this->response =& $response;
    }
}

/**
 * An error that indicates that a response was already signed.
 *
 * @package OpenID
 */
class Auth_OpenID_AlreadySigned extends Auth_OpenID_EncodingError {
    // This response is already signed.
}

/**
 * An error that indicates that the given return_to is not under the
 * given trust_root.
 *
 * @package OpenID
 */
class Auth_OpenID_UntrustedReturnURL extends Auth_OpenID_ServerError {
    function Auth_OpenID_UntrustedReturnURL($message, $return_to,
                                            $trust_root)
    {
        parent::Auth_OpenID_ServerError($message, "Untrusted return_to URL");
        $this->return_to = $return_to;
        $this->trust_root = $trust_root;
    }

    function toString()
    {
        return sprintf("return_to %s not under trust_root %s",
                       $this->return_to, $this->trust_root);
    }
}

/**
 * I handle requests for an OpenID server.
 *
 * Some types of requests (those which are not checkid requests) may
 * be handed to my {@link handleRequest} method, and I will take care
 * of it and return a response.
 *
 * For your convenience, I also provide an interface to {@link
 * Auth_OpenID_Decoder::decode()} and {@link
 * Auth_OpenID_SigningEncoder::encode()} through my methods {@link
 * decodeRequest} and {@link encodeResponse}.
 *
 * All my state is encapsulated in an {@link Auth_OpenID_OpenIDStore}.
 *
 * Example:
 *
 * <pre> $oserver = new Auth_OpenID_Server(Auth_OpenID_FileStore($data_path),
 *                                   "http://example.com/op");
 * $request = $oserver->decodeRequest();
 * if (in_array($request->mode, array('checkid_immediate',
 *                                    'checkid_setup'))) {
 *     if ($app->isAuthorized($request->identity, $request->trust_root)) {
 *         $response = $request->answer(true);
 *     } else if ($request->immediate) {
 *         $response = $request->answer(false);
 *     } else {
 *         $app->showDecidePage($request);
 *         return;
 *     }
 * } else {
 *     $response = $oserver->handleRequest($request);
 * }
 *
 * $webresponse = $oserver->encode($response);</pre>
 *
 * @package OpenID
 */
class Auth_OpenID_Server {
    function Auth_OpenID_Server(&$store, $op_endpoint=null)
    {
        $this->store =& $store;
        $this->signatory =& new Auth_OpenID_Signatory($this->store);
        $this->encoder =& new Auth_OpenID_SigningEncoder($this->signatory);
        $this->decoder =& new Auth_OpenID_Decoder($this);
        $this->op_endpoint = $op_endpoint;
        $this->negotiator =& Auth_OpenID_getDefaultNegotiator();
    }

    /**
     * Handle a request.  Given an {@link Auth_OpenID_Request} object,
     * call the appropriate {@link Auth_OpenID_Server} method to
     * process the request and generate a response.
     *
     * @param Auth_OpenID_Request $request An {@link Auth_OpenID_Request}
     * returned by {@link Auth_OpenID_Server::decodeRequest()}.
     *
     * @return Auth_OpenID_ServerResponse $response A response object
     * capable of generating a user-agent reply.
     */
    function handleRequest($request)
    {
        if (method_exists($this, "openid_" . $request->mode)) {
            $handler = array($this, "openid_" . $request->mode);
            return call_user_func($handler, $request);
        }
        return null;
    }

    /**
     * The callback for 'check_authentication' messages.
     */
    function openid_check_authentication(&$request)
    {
        return $request->answer($this->signatory);
    }

    /**
     * The callback for 'associate' messages.
     */
    function openid_associate(&$request)
    {
        $assoc_type = $request->assoc_type;
        $session_type = $request->session->session_type;
        if ($this->negotiator->isAllowed($assoc_type, $session_type)) {
            $assoc = $this->signatory->createAssociation(false,
                                                         $assoc_type);
            return $request->answer($assoc);
        } else {
            $message = sprintf('Association type %s is not supported with '.
                               'session type %s', $assoc_type, $session_type);
            list($preferred_assoc_type, $preferred_session_type) =
                $this->negotiator->getAllowedType();
            return $request->answerUnsupported($message,
                                               $preferred_assoc_type,
                                               $preferred_session_type);
        }
    }

    /**
     * Encodes as response in the appropriate format suitable for
     * sending to the user agent.
     */
    function encodeResponse(&$response)
    {
        return $this->encoder->encode($response);
    }

    /**
     * Decodes a query args array into the appropriate
     * {@link Auth_OpenID_Request} object.
     */
    function decodeRequest($query=null)
    {
        if ($query === null) {
            $query = Auth_OpenID::getQuery();
        }

        return $this->decoder->decode($query);
    }
}

?>
