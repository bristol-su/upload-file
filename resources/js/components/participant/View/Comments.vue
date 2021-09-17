<template>
    <div>
        <div v-if="$isLoading('loading-comments')">
            Loading comments
        </div>
        <div v-else class="pt-3">
            <ul class="commentList" v-if="comments.length > 0">
                <li v-for="comment in comments" v-if="comments.length > 0">
                    <comment :comment="comment" :can-delete-comments="canDeleteComments" :can-update-comments="canUpdateComments"
                             @updated="updateComment(comment, $event)"
                            @deleted="deleteComment(comment)"></comment>
                    <hr/>
                </li>
            </ul>
            <div v-else>
                No comments have been left.
            </div>
            <p-api-form ref="apiForm" :schema="form" @submit="postComment" v-if="canAddComments" button-text="Post Comment" :initial-data="{comment: ''}"
                        :busy="$isLoading('posting-comment')" busy-text="Posting comment">

            </p-api-form>
        </div>

    </div>
</template>

<script>
import Comment from './Comment';
export default {
    name: "Comments",
    components: {Comment},
    props: {
        file: {
            required: true,
            type: Object
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
        }
    },

    created() {
        this.loadComments();
    },

    methods: {
        updateComment(comment, updates) {
            this.comments.splice(
                this.comments.indexOf(comment),
                1,
                updates
            );
        },
        deleteComment(comment) {
            this.comments.splice(
                this.comments.indexOf(comment),
                1
            );
        },
        loadComments() {
            this.$http.get('/file/' + this.file.id + '/comment', {name: 'loading-comments'})
                .then(response => {
                    this.comments = response.data;
                    this.$emit('commentUpdated', response.data);
                })
                .catch(error => this.$notify.alert('Could not load comments'));
        },

        postComment(data) {
            this.$http.post('/file/' + this.file.id + '/comment', {comment: data.comment}, {name: 'posting-comment'})
                .then(response => {
                    this.comments.push(response.data);
                    this.$refs.apiForm.reset();
                    this.$emit('commentUpdated', this.comments);
                })
                .catch(error => this.$notify.alert('Could not post the comment'));
        }
    },

    computed: {
        form() {
            return this.$tools.generator.form.newForm()
                .withGroup(this.$tools.generator.group.newGroup()
                    .withField(
                        this.$tools.generator.field.textArea('comment')
                            .label('Your comment')
                            .required(true)
                    )
                )
                .generate()
                .asJson();
        }
    }
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
