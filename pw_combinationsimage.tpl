{**
 * PW Combinations image: module for PrestaShop.
 *
 * @author    profilweb. <manu@profil-web.fr>
 * @copyright 2025 profil Web.
 * @link      https://github.com/profilweb/pw_homecategories The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

{foreach $combinations as $combination}
    {if $combination.image}
        <figure class="thumbnail product-thumbnail variant-thumbnail" data-variant-img="{$combination.id_attribute}">
            <img src="{$combination.image}" alt="" loading="lazy">
        </figure>
    {/if}
{/foreach}
