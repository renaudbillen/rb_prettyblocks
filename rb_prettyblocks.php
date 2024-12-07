<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Rb_prettyblocks extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'rb_prettyblocks';
        $this->tab = 'content_management';
        $this->version = '1.0.0';
        $this->author = 'Renaud Billen';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        $this->dependencies = [
            'prettyblocks',
        ];

        parent::__construct();

        $this->displayName = $this->l('RB PrettyBlocks');
        $this->description = $this->l('Blocks Library for PB');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install(): bool
    {
        //include(dirname(__FILE__).'/sql/install.php');

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('actionRegisterBlock');
    }

    public function uninstall(): bool
    {
        //include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall()
            && $this->unregisterHook('header')
            && $this->unregisterHook('displayBackOfficeHeader')
            && $this->unregisterHook('actionRegisterBlock');
    }

    /**
     * @return bool
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (Tools::isSubmit('submitRb_prettyblocksModule')) {
            $this->postProcess();
        }

        try {
            $this->context->smarty->assign('module_dir', $this->_path);

            $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        } catch (SmartyException|Exception $e) {
            return '';
        }

        return $output . $this->renderForm();
    }

    /**
     * @return string
     */
    protected function renderForm(): string
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRb_prettyblocksModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * @return array[]
     */
    protected function getConfigForm(): array
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getConfigFormValues(): array
    {
        return [];
    }

    /**
     * @return void
     */
    protected function postProcess(): void
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader(): void
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader(): void
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function registerBlocks(): array
    {
        $blocks = [];
        $blocks[] = [
            'name' => 'RB Pretty Blocks',
            'description' => 'Description of your block',
            'code' => 'rb_images_block',
            'icon' => 'StarIcon',
            'need_reload' => false,
            'nameFrom' => 'title',
            'templates' => [
                'default' => 'module:' . $this->name . '/views/templates/block/default.tpl'
            ],
            'config' => [
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                        'default' => 'Images Block',
                    ],
                    'columns' => [
                        'type' => 'select',
                        'label' => $this->l('Maximum number of columns'),
                        'choices' => [
                            1, 2, 3, 4
                        ],
                        'default' => 0,
                    ],
                    'rearrange' => [
                        'type' => 'select',
                        'label' => $this->l('Rearrange last column'),
                        'choices' => [
                            'yes',
                            'no',
                        ],
                        'default' => 0,
                    ],
                ],
            ],
            'repeater' => [
                'name' => 'Image Infos',
                'nameFrom' => 'name',
                'groups' => [
                    'name' => [
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'default' => 'John Doe',
                    ],
                    'function' => [
                        'type' => 'text',
                        'label' => $this->l('Function'),
                        'default' => 'Director',
                    ],
                    'phone' => [
                        'type' => 'text',
                        'label' => $this->l('Phone'),
                        'default' => '+32 471 23 45 67',
                    ],
                    'email' => [
                        'type' => 'text',
                        'label' => $this->l('Email'),
                        'default' => 'johndoe@me.com',
                    ],
                    'image' => [
                        'type' => 'fileupload',
                        'force_default_value' => true,
                        'label' => $this->l('Image'),
                        'path' => '$/modules/' . $this->name . '/views/img/',
                        'default' => [
                            'url' => 'https://placehold.co/600x400',
                        ],
                    ],
                ],
            ],
        ];

        return $blocks;
    }

    /**
     * @param array $params
     * @return array
     */
    public function hookActionRegisterBlock(array $params): array
    {
        return $this->registerBlocks();
    }

    public function renderWidget($hookName, array $configuration)
    {
        // TODO: Implement renderWidget() method.
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        // TODO: Implement getWidgetVariables() method.
    }
}
