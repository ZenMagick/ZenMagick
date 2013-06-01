This is the ZenMagick plugin implementing the product_music_info product template.

Originally, this code was part of ZenMagick core, but got extracted into a 
separate plugin as it is only useful for stores selling music online.


Installation
============
1) Download (obvious ;)
   Download the latest version from http://www.zenmagick.org/
2) Extract into the ZenMagick plugins directory
3) Install the plugin via the ZenMagick plugins admin page
4) Done!


Usage
=====
The code included in this plugin can be used to display product information specific
to music like genre, publisher, etc.

Historically this code is used in a product template named 'product_music_info'. It is part
of the original zen-cart store setup, so it should be already configured in your database.
You can also create a new product type (and template) by logging in as admin and selecting
Catalog -> Product Types

Once you have determined the template name, it's probably easiest to copy the existing
product template (product_info.html.twig) to music_product_info.html.twig and use it as basis for your new music template.

For music products, or products configured with the music_product_info view template, the following additional 
view vars will be made available:
* musicManager - The manager service (usually not really needed)
* artist - Artist information
* collections - The available collections

The following code can be used to display music related information:

    {% if artist %}
        <fieldset>
            <legend>{{ 'Additional Music Info'|trans }}</legend>
            <p>{{ 'Genre: %genre%'|trans('%genre%' : artist.genre}) }}</p>
            {% if artist.hasUrl %}
                <p>
                    {{ 'Homepage:'|trans }}
                    <a href="{{ net.trackLink('url', artist.url) }}" class="new-win">
                        {{ artist.name }}
                    </a>
                </p>
            {% endif %}
        </fieldset>
    {% endif %}

    {% if 0 < collections|length %}
        <fieldset>
            <legend>{{ 'Media Collections'|trans }}</legend>
            {% for collection in collections %}
                <div class="mcol">
                    <h4>{{ collection.name }}</h4>
                    <ul>
                        {% for mediaItem in collection.items  %}
                            <li>
                                <a href="{{ asset(musicProductInfo.mediaUrl(mediaItem.filename)) }}">
                                    {{ mediaItem.filename }}
                                </a>
                                {{ mediaItem.type.name }}
                            </li>
                        {% endfor %}
                    </ul>
              </div>
            {% endfor %}
        </fieldset>
    {% endif %}

