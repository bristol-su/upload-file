import Vue from 'vue';
import UploadFileRoot from './components/participant/UploadFile';
import AdminUploadFile from './components/admin/AdminUploadFile';
import AdminFiles from './components/admin/AdminFiles';
import Toolkit from '@bristol-su/frontend-toolkit';

Vue.use(Toolkit);

let vue = new Vue({
    el: '#uploadfile-root',

    components: {
        UploadFileRoot,
        AdminUploadFile,
        AdminFiles
    }
});
