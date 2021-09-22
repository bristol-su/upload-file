import Vue from 'vue';
import UploadFileRoot from './components/participant/UploadFile';
import Admin from './components/admin/Admin';
import Toolkit from '@bristol-su/frontend-toolkit';

Vue.use(Toolkit);

let vue = new Vue({
    el: '#uploadfile-root',

    components: {
        UploadFileRoot,
        Admin
    }
});
