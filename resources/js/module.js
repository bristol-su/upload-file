import Vue from 'vue';
import UploadFileRoot from './components/participant/UploadFile';
import AdminUploadFilePage from './components/admin/AdminUploadFilePage';
import BootstrapVue from 'bootstrap-vue';
import http from '@bristol-su/http-client';
import AWN from "awesome-notifications";
import UiKit from '@bristol-su/portal-ui-kit';

Vue.prototype.$http = http;
Vue.prototype.$notify = new AWN({position: 'top-right'});
Vue.use(BootstrapVue);
Vue.use(UiKit);
Vue.prototype.$url = portal.APP_URL + '/' + (portal.admin ? 'a' : 'p') + '/' + portal.activity.slug + '/' + portal.module_instance.slug + '/' + portal.module_instance.alias;

let vue = new Vue({
    el: '#uploadfile-root',

    components: {
        UploadFileRoot,
        AdminUploadFilePage
    }
});
