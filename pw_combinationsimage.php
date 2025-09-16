<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Pw_combinationsImage extends Module
{
    public function __construct()
    {
        $this->name = 'pw_combinationsimage';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Profil Web';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PW Combinations Image');
        $this->description = $this->l('Affiche les images des combinaisons.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayProductMiniatureImgVariant'); // Votre hook personnalisé
    }

    public function hookDisplayProductMiniatureImgVariant($params)
    {
        $product = $params['product'];

        // Vérification basique
        if (!$product || !is_object($product) || !isset($product->id)) {
            return '';
        }

        $id_product = (int)$params['product']['id_product'];
        $id_lang = (int)$this->context->language->id;

        // 1. Trouver les groupes d'attributs de type "color"
        $attributeGroups = AttributeGroup::getAttributesGroups($id_lang);
        $colorGroupIds = [];

        foreach ($attributeGroups as $group) {
            if ($group['is_color_group']) {
                $colorGroupIds[] = $group['id_attribute_group'];
            }
        }

        if (empty($colorGroupIds)) {
            return ''; // Aucun groupe de couleur
        }

        $combinations = [];
        $img_colors = [];

        // 2. Récupérer toutes les combinaisons du produit
        $attributes = Product::getProductAttributesIds($id_product);

        foreach($attributes as $attribute) {

            $combinations = Product::getAttributesParams($id_product, $attribute['id_product_attribute']);

            foreach ($combinations as $combination) {
                if (in_array($combination['id_attribute_group'], $colorGroupIds)) {

                    $image = $this->getCombinationImage($product, $attribute['id_product_attribute']);

                    $img_colors[$combination['id_attribute']] = [
                        'id_attribute' => $combination['id_attribute'],
                        'image' => $image,
                    ];
                }
            }
        }

        if (empty($img_colors)) {
            return ''; // Aucune couleur pour ce produit
        }

        // Passe les données au template Smarty
        $this->smarty->assign([
            'combinations' => $img_colors,
        ]);

        // Retourner VOTRE template
        return $this->display(__FILE__, 'pw_combinationsimage.tpl');
    }

    protected function getCombinationImage($product, $id_product_attribute)
    {
        global $params;
        
        $combination = new Combination($id_product_attribute);
        $images = $combination->getWsImages($this->context->language->id);


        if (!empty($images)) {

            // Prendre la première image associée à la combinaison - mais dans l'ordre défini pour les images produits
            $imgs = array();
            foreach($images as $decli_img) {
                $imgs[$decli_img['id']] = $decli_img;
            }

            $_p = new Product($product->id, false, Configuration::get('PS_LANG_DEFAULT'));
            $_p_imgs = $_p->getImages(Configuration::get('PS_LANG_DEFAULT'));

            $_get_img = false;

            foreach($_p_imgs as $_img) {
                if(!$_get_img) {
                    if(in_array((int)$_img['id_image'],array_keys($imgs))) {                      
                        $id_image = $_img['id_image'];
                        $_get_img = true;
                    }
                }
            }

            if(isset($id_image)) $image_url = $this->context->link->getImageLink(
                $_p->link_rewrite,
                $id_image,
                'medium_default'
            );

            return $image_url;
        }

        return false;
    }
}