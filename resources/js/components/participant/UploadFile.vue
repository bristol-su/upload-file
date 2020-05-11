<template>
    <div>
        <b-tabs>
            <b-tab active title="New File" v-if="canUpload">
                <upload-tab-content :allowed-extensions="allowedExtensions" :default-document-title="defaultDocumentTitle"
                                    :multiple-files="multipleFiles"
                                    @file-uploaded="pushFile"></upload-tab-content>
            </b-tab>
            <b-tab title="Saved Files" v-if="canView">
                <view-tab-content :can-delete="canDelete" :can-download="canDownload" :can-see-comments="canSeeComments"
                                  :can-update="canUpdate" :files="files" :query-string="queryString"
                                  :can-delete-comments="canDeleteComments" :can-update-comments="canUpdateComments" :can-add-comments="canAddComments"
                                  @file-updated="replaceFile" @fileDeleted="popFile" :is-old-files="false"></view-tab-content>
            </b-tab>
            <b-tab title="Old Files" v-if="oldFiles.length > 0">
                <view-tab-content :can-delete="false" :can-download="true" :can-see-comments="true"
                    :can-update="false" :query-string="queryString" :files="oldFiles" :can-delete-comments="false"
                    can-update-comments="false" :can-add-comments="false" :is-old-files="true" />
            </b-tab>
        </b-tabs>
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
            },
            queryString: {
                type: String,
                required: true
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
            pushFile(file) {
                this.$http.get('file/' + file.id)
                    .then(response => this.files.push(response.data))
                    .catch(error => window.location.reload());
            },

            loadFiles() {
                this.$http.get('file')
                    .then(response => this.files = response.data)
                    .catch(error => this.$notify.alert('Sorry, something went wrong retrieving files: ' + error.message));
            },
            
            loadOldFiles() {
                this.$http.get('file/old')
                    .then(response => this.oldFiles = response.data)
                    .catch(error => this.$notify.alert('Sorry, something went wrong retrieving the old files: ' + error.message));
            },

            popFile(id) {
                this.files = this.files.filter(file => file.id !== id);
            },

            replaceFile(file) {
                this.$http.get('file/' + file.id)
                    .then(response => {
                        let index = this.files.map(data => data.id).indexOf(file.id);
                        this.files[index] = response.data;
                    })
                    .catch(error => window.location.reload());
            }
        }
    }
</script>

<style scoped>

</style>