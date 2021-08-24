import Vue from 'vue';
import UploadFileRoot from './components/participant/UploadFile';
import AdminUploadFile from './components/admin/AdminUploadFile';
import AdminFiles from './components/admin/UploadFile';
import BootstrapVue from 'bootstrap-vue';
import http from '@bristol-su/http-client';
import AWN from "awesome-notifications";
import Toolkit from '@bristol-su/frontend-toolkit';

Vue.prototype.$http = http;
Vue.prototype.$notify = new AWN({position: 'top-right'});
Vue.use(BootstrapVue);
Vue.use(Toolkit);

let vue = new Vue({
    el: '#uploadfile-root',

    components: {
        UploadFileRoot,
        AdminUploadFile,
        AdminFiles
    }
});
