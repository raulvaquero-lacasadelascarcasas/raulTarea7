/**
* 2007-2020 PrestaShop
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
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

(function(){

  'use strict';

  var canvas = document.getElementById('scratch'),
      context = canvas.getContext('2d');

  // default value
  context.globalCompositeOperation = 'source-over';

  //----------------------------------------------------------------------------

  var x, y, radius;

  x = y = radius = 150 / 2;

  // fill circle
  context.beginPath();
  context.fillStyle = '#515151';
  context.rect(0, 0, 300, 60);
  context.fill();

  //----------------------------------------------------------------------------

  var isDrag = false;

  function clearArc(x, y) {
    context.globalCompositeOperation = 'destination-out';
    context.beginPath();
    context.arc(x, y, 10, 0, Math.PI * 2, false);
    context.fill();
  }

  canvas.addEventListener('mousedown', function(event) {
    isDrag = true;

    clearArc(event.offsetX, event.offsetY);
    judgeVisible();
  }, false);

  canvas.addEventListener('mousemove', function(event) {
    if (!isDrag) {
      return;
    }

    clearArc(event.offsetX, event.offsetY);
    judgeVisible();
  }, false);

  canvas.addEventListener('mouseup', function(event) {
    isDrag = false;
  }, false);

  canvas.addEventListener('mouseleave', function(event) {
    isDrag = false;
  }, false);

  //----------------------------------------------------------------------------

  canvas.addEventListener('touchstart', function(event) {
    if (event.targetTouches.length !== 1) {
      return;
    }

    event.preventDefault();

    isDrag = true;

    clearArc(event.touches[0].offsetX, event.touches[0].offsetY);
    judgeVisible();
  }, false);

  canvas.addEventListener('touchmove', function(event) {
    if (!isDrag || event.targetTouches.length !== 1) {
      return;
    }

    event.preventDefault();

    clearArc(event.touches[0].offsetX, event.touches[0].offsetY);
    judgeVisible();
  }, false);

  canvas.addEventListener('touchend', function(event) {
    isDrag = false;
  }, false);

  //----------------------------------------------------------------------------

  function judgeVisible() {
    var imageData = context.getImageData(0, 0, 150, 150),
        pixels = imageData.data,
        result = {},
        i, len;

    // count alpha values
    for (i = 3, len = pixels.length; i < len; i += 4) {
      result[pixels[i]] || (result[pixels[i]] = 0);
      result[pixels[i]]++;
    }

    document.getElementById('gray-count').innerHTML = result[255];
    document.getElementById('erase-count').innerHTML = result[0];
  }

  document.addEventListener('DOMContentLoaded', judgeVisible, false);

}());