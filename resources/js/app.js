import './bootstrap';
import Alpine from 'alpinejs';
import flashDealCountdown from './modern/flash-deal-countdown';
import productCard from './modern/product-card';

window.Alpine = Alpine;

Alpine.data('flashDealCountdown', flashDealCountdown);
Alpine.data('productCard', productCard);

Alpine.start();

import Vue from 'vue';
window.Vue = Vue;

import ExampleComponent from './components/ExampleComponent.vue';
Vue.component('example-component', ExampleComponent);

const app = new Vue({
    el: '#app'
});
