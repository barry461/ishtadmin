<!-- 评论组件 -->
<div class="slf-component-video-comments">
    <div class="comment-card" v-if="!replyId">
        <div class="comment-card">
            <textarea
                name="comment"
                id="comment-main"
                maxlength="200"
                v-model="replyText"
                placeholder="有什么想说的，来分享吧~"
            ></textarea>
            <div class="comment-btn" @click="handleCommitComment">发表评论</div>
        </div>
    </div>
    <div class="comment-list">
        <div class="comment-item" v-for="(comment, index) in commentList" :key="comment.id">
            <div class="comment-header">
                <div class="comment-avatar">
                    <img :x-image-loader-url="comment.avatar" alt="头像" />
                </div>
                <div class="comment-username">{{index}}{{ comment.username }}</div>
                <div class="comment-date">{{ comment.date }}</div>
            </div>
            <div class="comment-content">
                {{ comment.content }}
                <a href="javascript:;" class="reply-btn" @click="handleReply(comment)">回复</a>
                &nbsp; &nbsp;
                <a v-if="replyId === comment.id" href="javascript:;" class="reply-btn" @click="cancelReply">取消回复</a>
            </div>
            <div class="comment-card" v-if="replyId === comment.id">
                <div class="comment-card">
                    <textarea
                        name="comment"
                        :id="'comment-reply-' + comment.id"
                        maxlength="200"
                        v-model="replyText"
                        :placeholder="`回复 ${comment.username}：`"
                    ></textarea>
                    <div class="comment-btn" @click="handleCommitComment">发表评论</div>
                </div>
            </div>
            <div class="comment-children">
                <div
                    class="comment-child-item"
                    v-for="(childComment, childIndex) in comment.children"
                    :key="childComment.id"
                >
                    <div class="comment-header">
                        <div class="comment-avatar">
                            <img :x-image-loader-url="childComment.avatar" alt="头像" />
                        </div>
                        <div class="comment-username">{{ childComment.username }}</div>
                        <div class="comment-date">{{ childComment.date }}</div>
                    </div>
                    <div class="comment-content">{{ childComment.content }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="comment-placeholder" id="comment-placeholder" style="height: 1px"></div>
<div class="comment-load-tip">暂无更多评论</div>
