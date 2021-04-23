{*
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
*}

<div class="panel">
	 <h3><i class="fa fa-wrench"></i> Configuración del cupon</h3>
    <form method="post"  class="form-horizontal">
            <p>Cantidad para obtener un descuento</p>
            
            <div class="form-group">

                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <input class="form-control" type="number" value="{$cantidaddescuento}" name="cantidad_descuento">
                </div>
                <div style="clear: both;"></div>
                <br><br>
                <p>Cantidad de descuento</p>
                
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <input class="form-control" min="1" max="99" type="number" value="{$cantidadcupon}" name="porcentaje_descuento">
                </div>
            </div>
         
        <div class="panel-footer">
            <button type="submit" value="1" id="save" name="save" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='cupon'}
            </button>
        </div>
    </form>
</div>
<!---->

<div class="panel ">
    <h3>
        <i class="fa fa-list"></i> {l s='Listado de cupones' mod='cupon'}
    </h3>
    <p>
        {l s='Este es el listado de los cupones que han sido creados para los usuarios' mod='cupon'}
    </p>
    <br>
    <div>
        <table id="cupones" class="table table-striped table-bordered">
            <thead>
                <tr class="table-header">
                    <th class="text-center"><b>{l s='Id Usuario' mod='cupon'}</b></th>
                    <th class="text-center"><b>{l s='Nombre' mod='cupon'}</b></th>
                    <th class="text-center"><b>{l s='Apellidos' mod='cupon'}</b></th>
                    <th class="text-center"><b>{l s='Email' mod='cupon'}</b></th>
                    <th class="text-center"><b>{l s='Codigo' mod='cupon'}</b></th>
                    <th class="text-center"><b>{l s='Fecha' mod='cupon'}</b></th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$cupones item=cupon}
                  <tr>
                    <td class="text-center">{$cupon.id_usuario}</td>
                    <td class="text-center">{$cupon.nombre}</td>
                    <td class="text-center">{$cupon.apellidos}</td>
                    <td class="text-center">{$cupon.email}</td>
                    <td class="text-center">{$cupon.code}</td>
                    <td class="text-center">{$cupon.date_add}</td>
                   
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    
</div>
