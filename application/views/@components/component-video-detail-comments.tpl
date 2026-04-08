<!-- 详情页评论区 -->
<section class="comments pb-3 pb-lg-4" data-block-id="video_comments_video_comments">
    <div id="video_comments_video_comments" class="comment-list">
        {notempty name="video_detail_show_comment_alert"}
        <div class="alert alert-success w-100" role="alert">
            Thank you! Your comment has been submitted for review.
        </div>
        {/notempty}
        <h3 class="sub-title pb-3">留言 ({$video_detail_comment_count|default='0'})</h3>
        <div class="comment-box comment-box-require-login" data-login-url="/login">
            <textarea data-mvid="{$video_detail_video_id|default=''}" id="bind_video_comment" name="comment" placeholder="请先登录之后再发表评论..." readonly tabindex="-1"></textarea>
            <button class="btn btn-comment-submit" id="bind_video_comment_submit">发表评论</button>
        </div>
        <div class="video_comments_item">
            {foreach name="video_detail_comment_list" item="e"}
            <div class="item" data-comment-id="{$e.comment_id|default=''}">
                <img class="avatar mt-1" src="__ROOT_PATH__/__base/images/avatar.svg" width="35" height="35">
                <div class="right">
                    <div class="title">
                        <span class="pr-2">{$e.username|default=''}</span>
                        <span class="inactive-color pr-2">{$e.time|default=''}</span>
                    </div>
                    <p class="comment-text">
                        <span class="original-text">{$e.text|default=''}</span>
                    </p>
                </div>
            </div>
            {/foreach}
        </div>
    </div>
</section>
