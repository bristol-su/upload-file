<template>
    <div>
        <p-tabs ref="tabs">
            <p-tab title="New File" v-if="canUpload">
                <upload-tab-content :allowed-extensions="allowedExtensions"
                                    :default-document-title="defaultDocumentTitle"
                                    :multiple-files="multipleFiles"
                                    @file-uploaded="pushFiles">

                </upload-tab-content>
            </p-tab>
            <p-tab title="Saved Files" v-if="canView">
                <view-tab-content :loading="$isLoading('loading-files')" :can-delete="canDelete" :can-download="canDownload" :can-see-comments="canSeeComments"
                                  :can-update="canUpdate" :files="files"
                                  :can-delete-comments="canDeleteComments" :can-update-comments="canUpdateComments"
                                  :can-add-comments="canAddComments"
                                  @file-updated="replaceFile" @fileDeleted="popFile"
                                  :is-old-files="false"></view-tab-content>
            </p-tab>
            <p-tab title="Old Files" v-if="oldFiles.length > 0">
                <view-tab-content :loading="$isLoading('loading-old-files')" :can-delete="false" :can-download="true" :can-see-comments="true"
                                  :can-update="false" :files="oldFiles"
                                  :can-delete-comments="false"
                                  :can-update-comments="false" :can-add-comments="false" :is-old-files="true"/>
            </p-tab>
        </p-tabs>
    </div>
</template>

<script>
import UploadTabContent from './Upload/UploadTabContent';
import ViewTabContent from './View/ViewTabContent';

export default {
    name: "UploadFile",

    components: {
        UploadTabContent,
        ViewTabContent,
    },

    props: {
        showOldFiles: {
            type: Boolean,
            default: false
        },
        canUpload: {
            type: Boolean,
            required: true,
            default: false
        },
        canView: {
            type: Boolean,
            required: true,
            default: false
        },
        canDownload: {
            type: Boolean,
            required: true,
            default: false
        },
        canUpdate: {
            type: Boolean,
            required: true,
            default: false
        },
        canDelete: {
            type: Boolean,
            required: true,
            default: false
        },
        canSeeComments: {
            type: Boolean,
            required: true,
            default: false
        },
        canAddComments: {
            type: Boolean,
            required: true,
            default: false
        },
        canDeleteComments: {
            type: Boolean,
            required: true,
            default: false
        },
        canUpdateComments: {
            type: Boolean,
            required: true,
            default: false
        },
        defaultDocumentTitle: {
            type: String,
            default: ''
        },
        multipleFiles: {
            required: false,
            default: true,
            type: Boolean
        },
        allowedExtensions: {
            required: false,
            type: Array,
            default: function () {
                return [];
            }
        }
    },

    data() {
        return {
            files: [],
            oldFiles: []
        }
    },

    created() {
        this.loadFiles();
        this.loadOldFiles();
    },

    methods: {
        pushFiles(files) {
            files.forEach(file => this.pushFile(file));
        },
        pushFile(file) {
            this.files.push(file);
            this.$refs.tabs.selectTab(1);
        },

        loadFiles() {
            this.$http.get('file', {name: 'loading-files'})
                .then(response => this.files = response.data)
                .catch(error => this.$notify.alert('Sorry, something went wrong retrieving files: ' + error.message))
        },

        loadOldFiles() {
            this.$http.get('file/old', {name: 'loading-old-files'})
                .then(response => this.oldFiles = response.data)
                .catch(error => this.$notify.alert('Sorry, something went wrong retrieving the old files: ' + error.message))
                .finally(() => this.$refs.tabs.loadTabs());
        },

        popFile(id) {
            this.files = this.files.filter(file => file.id !== id);
        },

        replaceFile(file) {
            let index = this.files.map(data => data.id).indexOf(file.id);
            this.files[index] = file;
        }
    }
}
</script>

<style scoped>

</style>
