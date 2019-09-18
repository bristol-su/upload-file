import Vue from 'vue';
import ExampleComponent from './components/ExampleComponent';
import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
Vue.prototype.$http = axios;

let vue = new Vue({
    el: '#uploadfile-root',
    
    components: {
        ExampleComponent
    }
});
