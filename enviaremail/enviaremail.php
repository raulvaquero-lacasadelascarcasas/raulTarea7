<?php

/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Enviaremail extends Module {
    protected $config_form = false;

    public function __construct() {
        $this->name = 'enviaremail';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Raul Vaquero';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Enviar email');
        $this->description = $this->l('Envia al comprador un email informándole del total de dinero que lleva gastado en la tienda gastado y la creación de cupón descuento');
        $this->confirmUninstall = $this->l('');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install() {
        Configuration::updateValue('ENVIAREMAIL_LIVE_MODE', false);
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('displayOrderConfirmation');
    }

    public function uninstall() {
        Configuration::deleteByName('ENVIAREMAIL_LIVE_MODE');
        return parent::uninstall();
    }

    public function getContent() {
        return $this->postProcess() . $this->getForm();
    }

    public function getForm() {
        $this->context->smarty->assign([
            'cupones' => $this->getCupones(),
            'cantidaddescuento' => Configuration::get('CANTIDAD_CUPON'),
            'cantidadcupon' => Configuration::get('DESCUENTO_CUPON'),
        ]);

        return $this->output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    protected function postProcess() {
        if (Tools::isSubmit('save')) {
            $cantidad_descuento = Tools::getValue('cantidad_descuento');
            $porcentaje_descuento = Tools::getValue('porcentaje_descuento');
            Configuration::updateValue('CANTIDAD_CUPON', $cantidad_descuento);
            Configuration::updateValue('DESCUENTO_CUPON', $porcentaje_descuento);
            return $this->displayConfirmation($this->l('Se ha actualizado correctamente'));
        }
    }

    public function hookActionFrontControllerSetMedia($params) {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/estilo.css');
    }

    public function hookdisplayOrderConfirmation($params) {
        $idcupon = Db::getInstance()->getValue('SELECT id_cart_rule FROM ps_cart_rule  WHERE id_customer="' . $params['cart']->id_customer . '"');
        $existe = Db::getInstance()->getValue('SELECT id_cart_rule FROM ps_order_cart_rule  WHERE id_cart_rule="' . $idcupon . '"');
        $descuento = (Configuration::get('DESCUENTO_CUPON'));

        if ($idcupon == $existe) {
        } else {
            $codigocupon = Db::getInstance()->getValue('SELECT code FROM ps_cart_rule  WHERE id_customer="' . $params['cart']->id_customer . '"');
            $this->context->smarty->assign(array('texto_variable' => $codigocupon, 'descuento' => $descuento));
            return $this->context->smarty->fetch($this->local_path . 'views/templates/hook/confirmacion.tpl');
        }
    }

    public function hookactionValidateOrder($params) {
        $user_email = $params['customer']->email;
        $id_usuario = $params['customer']->id;
        $nombre = $params['customer']->firstname;
        $apellido = $params['customer']->lastname;
        $cantidad_gastada = Db::getInstance()->getValue('SELECT ROUND(sum(po.total_paid),2) as totalpaid FROM `ps_orders` po where id_customer="' . pSQL($id_usuario) . '"');
        $cantidad_configurada = Configuration::get('CANTIDAD_CUPON');
        $descuento = (Configuration::get('DESCUENTO_CUPON'));
        $numerocupones = (Db::getInstance()->getValue('SELECT count(id_cart_rule) FROM  ps_cart_rule WHERE id_customer=' . $id_usuario));
        $cupon = new CartRule();

        if ($cantidad_gastada > $cantidad_configurada) {
            if ($numerocupones < 1) {
                $cupon->code = $this->generarCodigo(5);
                $cupon->id_customer = (int) ($id_usuario);
                $cupon->reduction_percent = $descuento;
                $cart_rule_name = $this->l('Cupon descuento ') . $descuento . '% - Ref: ' . (int) ($cupon->id_customer) . ' - ' . date('Y');
                array('1' => $cart_rule_name, '2' => $cart_rule_name);
                $languages = Language::getLanguages();
                $array_name = array();

                foreach ($languages as $language) {
                    $array_name[$language['id_lang']] = $cart_rule_name;
                }
                $cupon->name = $array_name;
                $cupon->description = '¡Cupón descuento!';
                $cupon->cart_rule_restriction = false;
                $cupon->date_from = date('Y-m-d');
                $cupon->date_to = strftime('%Y-%m-%d', strtotime('+2 year'));
                $cupon->active = true;
                $cupon->add();
                $this->enviarEmail("concupon", $user_email, $nombre, $apellido, $descuento, $cupon->code, $cantidad_gastada, "¡tienes un cupon de descuento!");
            } else {

                $this->enviarEmail("sincupon", $user_email, $nombre, $apellido, $descuento, $cupon->code, $cantidad_gastada, "¡Tu gasto total en la tienda!");
            }
        } else {

            $this->enviarEmail("sincupon", $user_email, $nombre, $apellido, $descuento, $cupon->code, $cantidad_gastada, "¡Tu gasto total en la tienda!");
        }
    }

    public function enviarEmail($plantilla, $user_email, $nombre, $apellido, $descuento, $cupon_code, $cantidad_gastada, $asunto) {
        $email_data = array('{nombre}' => $nombre, '{apellidos}' => $apellido, '{cantidad_gastada}' => $cantidad_gastada, '{descuento}' => $descuento, '{codigo}' => $cupon_code,);
        Mail::Send(
            (int) Configuration::get('PS_LANG_DEFAULT'),
            $plantilla,
            $asunto,
            $email_data,
            $user_email,
            null,
            (string) Configuration::get('PS_SHOP_EMAIL'),
            (string) Configuration::get('PS_SHOP_NAME'),
            null,
            null,
            dirname(__FILE__) . '/mails/'
        );
    }

    function generarCodigo($longitud) {
        $codigo = "";
        $caracteres = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $max = strlen($caracteres) - 1;
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[rand(0, $max)];
        }
        return $codigo;
    }

    public static function getCupones() {
        $cupones = Db::getInstance()->executeS('SELECT * FROM ps_cart_rule INNER JOIN ps_customer ON ps_cart_rule.id_customer = ps_customer.id_customer');
        if (empty($cupones)) {
            return [];
        }
        $resultado = [];
        foreach ($cupones as $cupon) {
            $resultado[] = [
                'id_cupon' => $cupon['id_cart_rule'],
                'id_usuario' => $cupon['id_customer'],
                'nombre' => $cupon['firstname'],
                'apellidos' => $cupon['lastname'],
                'email' => $cupon['email'],
                'code' => $cupon['code'],
                'date_add' => $cupon['date_add'],
                'date_upd' => $cupon['date_upd'],
            ];
        }
        return $resultado;
    }
}
