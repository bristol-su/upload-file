import Vue from 'vue';
import UploadFileRoot from './components/UploadFileRoot';
import BootstrapVue from 'bootstrap-vue';
import http from 'http-client';
import AWN from "awesome-notifications";

Vue.prototype.$http = http;
Vue.prototype.$notify = new AWN({position: 'top-right'});
Vue.use(BootstrapVue);
Vue.prototype.$url = portal.APP_URL + '/p/' + portal.ACTIVITY_SLUG + '/' + portal.MODULE_INSTANCE_SLUG;

let vue = new Vue({
    el: '#uploadfile-root',
    
    components: {
        UploadFileRoot
    }
});