/* eslint-disable import/no-unresolved */

import Iterator from 'src/helper/iterator.helper';
import Plugin from "src/plugin-system/plugin.class"

export default class IntediaDoofinder extends Plugin {

   static options = {
      layerType:               1,
      engineHash:              null,
      installationId:          null,
      searchZone:              'eu1',
      addToCartUrl:            null,
      offCanvasUrl:            null,
      doofinderRecommendation: 0,
      recommendationJs:        'https://cdn.doofinder.com/recommendations/js/doofinderRecommendation.min.js'
   }

   init() {
      this._insertScripts();
      this._addDoofinderCartAddListener();
   }

   /**
    * @private
    */
   _insertScripts() {
      this._insertScript(this._getLayerScriptUrl(), this._onScriptLoaded.bind(this));

      if (this.options.doofinderRecommendation) {
         this._insertScript(this.options.recommendationJs);
      }
   }

   /**
    * @param url
    * @param onLoad
    * @private
    */
   _insertScript(url, onLoad = null) {

      if (url) {

         const script = document.createElement('script')
         script.type = 'text/javascript'
         script.src = url

         if (onLoad) {
            script.addEventListener('load', onLoad)
         }

         document.head.appendChild(script)
      }
   }

   /**
    *
    * @param {string} requestUrl
    * @param {{}|FormData} formData
    * @private
    */
   _openOffCanvasCarts(requestUrl, formData) {

      const offCanvasCartInstances = window.PluginManager.getPluginInstances('OffCanvasCart');
      Iterator.iterate(offCanvasCartInstances, instance => this._openOffCanvasCart(instance, requestUrl, formData));
   }

   /**
    *
    * @param {OffCanvasCartPlugin} instance
    * @param {string} requestUrl
    * @param {{}|FormData} formData
    * @private
    */
   _openOffCanvasCart(instance, requestUrl, formData) {

      instance.openOffCanvas(requestUrl, formData, () => {
         this.$emitter.publish('openOffCanvasCart');
      });
   }

   /**
    *
    * @param formData
    * @param data
    * @param parentKey
    * @private
    */
   _buildFormData(formData, data, parentKey) {

      if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File)) {
         Object.keys(data).forEach(key => {
            this._buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
         });
      } else {
         const value = data == null ? '' : data;

         formData.append(parentKey, value);
      }
   }

   /**
    *
    * @param url
    * @param data
    * @private
    */
   _sendData(url, data) {

      const xmlRequest = new XMLHttpRequest();
      const formData   = new FormData();

      this._buildFormData(formData, data);

      xmlRequest.addEventListener('load', function(event) {
         if (this.options.offCanvasUrl) {
            this._openOffCanvasCarts(this.options.offCanvasUrl, null);
         }
      }.bind(this));

      xmlRequest.open('POST', url);
      xmlRequest.send(formData);
   }

   /**
    *
    * @param number
    * @private
    */
   _addToCart(number) {
      this._sendData(this.options.addToCartUrl, {
         number: number
      });
   }

   /**
    * Add listener to "doofinder.cart.add"
    * @private
    */
   _addDoofinderCartAddListener() {

      if (this.options.addToCartUrl) {

         document.addEventListener("doofinder.cart.add", function (event) {

            const product = event.detail;

            if (product) {

               const productId = product.id || product.item_id
               const parentId  = product.grouping_id
               const link      = product.link

               // no variation
               if (productId === parentId) {
                  this._addToCart(productId);
               }
               else if (link) {
                  location.href = link
               }
            }

         }.bind(this));
      }
   }

   /**
    *
    * @returns {string|null}
    * @private
    */
   _getLayerScriptUrl() {

      if (this.options.engineHash && this.options.searchZone && this.options.layerType) {

         switch (this.options.layerType.toString()) {
            case "1":
               return 'https://cdn.intedia.de/doofinder/layer/shopware6/' + this.options.engineHash + '.js'
            case "2":
               return 'https://' + this.options.searchZone + '-search.doofinder.com/5/script/' + this.options.engineHash + '.js'
            case "3":
               return 'https://cdn.doofinder.com/livelayer/1/js/loader.min.js'
         }
      }

      return null
   }

   _onScriptLoaded() {
      if (this.options.layerType.toString() === "3") {

         let layerOptions = {
            installationId: this.options.installationId,
            zone: this.options.searchZone
         }

         if (typeof window.dfLayerOptions === 'object') {
            layerOptions = Object.assign(window.dfLayerOptions, layerOptions)
         }

         doofinderLoader.load(layerOptions)
      }
   }
}