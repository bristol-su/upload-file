<template>
    <div>
        <div v-if="comments.length > 0">
            <ul class="commentList">
                <li v-for="comment in comments" v-if="comments.length > 0">
                    <comment :comment="comment" :can-delete-comments="canDeleteComments"
                             :can-update-comments="canUpdateComments" @updated="loadComments" v-on:commentDeleted="commentDeleted"></comment>
                    <hr/>
                </li>
            </ul>
        </div>
        <div v-else>
            No comments have been left.
            <hr>
        </div>
        <b-container>
            <b-form @submit.prevent="postComment" v-if="canAddComments">
                <b-row class="mt-2">
                    <b-col sm="2">
                        <label for="comment">Comment:</label>
                    </b-col>
                    <b-col sm="10">
                        <b-textarea
                                id="comment"
                                class="mb-2 mr-sm-2 mb-sm-0"
                                placeholder="Leave a comment..."
                                v-model="newComment"
                        ></b-textarea>
                    </b-col>
                </b-row>
                <br>
                <b-button type="submit" variant="outline-success">Post Comment</b-button>
            </b-form>
        </b-container>
    </div>
</template>

<script>
    import Comment from './Comment';

    export default {
        name: "Comments",
        components: {Comment},
        props: {
            fileId: {
                required: false
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
        },

        data() {
            return {
                comments: [],
                newComment: ''
            }
        },

        created() {
            this.loadComments();
        },

        methods: {
            loadComments() {
                this.$http.get('/file/' + this.fileId + '/comment')
                    .then(response => this.comments = response.data)
                    .catch(error => this.$notify.alert('Could not load comments'));
            },

            postComment() {
                this.$http.post('/file/' + this.fileId + '/comment', {comment: this.newComment})
                    .then(response => {
                        this.comments.push(response.data);
                        this.newComment = '';
                        this.$emit('updateCommentCount', {file: this.fileId, comment: response.data, action: 'Added'});
                    })
                    .catch(error => this.$notify.alert('Could not post the comment'));
            },

            commentDeleted(e) {
                // Refresh Comments:
                this.loadComments();
                this.$emit('updateCommentCount', {...e, file: this.fileId});
            }
        },


        computed: {}
    }
</script>

<style scoped>

    .commentList {
        padding: 0;
        list-style: none;
        overflow: auto;
    }

    .commentList li {
        margin: 0;
        margin-top: 10px;
    }


</style>
