import Vue from 'vue';
import UploadFileRoot from './components/participant/UploadFile';
import UploadFileAdmin from './components/admin/UploadFile';
import BootstrapVue from 'bootstrap-vue';
import http from '@bristol-su/http-client';
import AWN from "awesome-notifications";

Vue.prototype.$http = http;
Vue.prototype.$notify = new AWN({position: 'top-right'});
Vue.use(BootstrapVue);
Vue.prototype.$url = portal.APP_URL + '/' + portal.A_OR_P + '/' + portal.ACTIVITY_SLUG + '/' + portal.MODULE_INSTANCE_SLUG + '/' + portal.ALIAS;

let vue = new Vue({
    el: '#uploadfile-root',
    
    components: {
        UploadFileRoot,
        UploadFileAdmin
    }
});