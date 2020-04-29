<template>
    <b-tabs>
        <b-tab active title="Uploaded Files" v-if="canViewFiles">
            <upload-file
                :query-string="queryString"
                :can-see-comments="canSeeComments"
                :can-add-comments="canAddComments"
                :can-change-status="canChangeStatus"
                :can-delete-comments="canDeleteComments"
                :can-update-comments="canUpdateComments"
                :can-download="canDownload"
                :can-delete-files="canDeleteFiles"
                :can-update-files="canUpdateFiles"
                :statuses="statuses"></upload-file>
        </b-tab>
        <b-tab title="Upload a File" v-if="canUploadFiles">
            <admin-upload-file
                :default-document-title="defaultDocumentTitle"
                :multiple-files="multipleFiles"
                :allowed-extensions="allowedExtensions"></admin-upload-file>
        </b-tab>
    </b-tabs>
</template>

<script>
    import UploadFile from './UploadFile';
    import AdminUploadFile from './AdminUploadFile';
    export default {
        name: "AdminUploadFilePage",
        components: {AdminUploadFile, UploadFile},
        props: {
            defaultDocumentTitle: {
                required: false,
                default: '',
                type: String
            },
            multipleFiles: {
                required: false,
                default: true,
                type: Boolean
            },
            allowedExtensions: {
                required: false,
                type: Array,
                default: function() {
                    return [];
                }
            },
            canViewFiles: {
                required: true,
                type: Boolean,
                default: false
            },
            canUploadFiles: {
                required: true,
                type: Boolean,
                default: false
            },
            canDownload: {
                required: true,
                type: Boolean,
                default: false
            },
            canChangeStatus: {
                required: true,
                type: Boolean,
                default: false
            },
            canAddComments: {
                required: true,
                type: Boolean,
                default: false
            },
            canDeleteComments: {
                required: true,
                type: Boolean,
                default: false
            },
            canUpdateComments: {
                required: true,
                type: Boolean,
                default: false
            },
            canSeeComments: {
                required: true,
                type: Boolean,
                default: false
            },
            statuses: {
                required: true,
                type: Array
            },
            queryString: {
                type: String,
                required: true
            },
            canUpdateFiles: {
                type: Boolean,
                required: false,
                default:  false
            },
            canDeleteFiles: {
                type: Boolean,
                required: false,
                default:  false
            }
        },

        data() {
            return {}
        },

        methods: {},

        computed: {}
    }
</script>

<style scoped>

</style>