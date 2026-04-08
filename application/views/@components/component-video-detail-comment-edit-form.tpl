<!-- 详情页评论编辑弹窗表单 -->
<div id="comment-edit-form" class="modal-dialog">
    <form method="post">
        <div class="modal-content-wrapper">
            <div class="modal-content">
                <div class="modal-header">
                    <button data-fancybox-close="" type="button" class="close">
                        <svg aria-hidden="true" height="20" width="20">
                            <use xlink:href="#icon-close"></use>
                        </svg>
                    </button>
                    <h4>編輯留言</h4>
                    <p>您可以自由地發表內容，但請勿直接推廣其他線上播放網站。</p>
                    <div class="alert alert-danger w-100" role="alert"></div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <span class="emoji-picker" data-toggle="dropdown" data-display="static">
                            <svg aria-hidden="true" height="18" width="18">
                                <use xlink:href="#icon-emoji"></use>
                            </svg>
                        </span>
                        <div class="dropdown-menu dropdown-menu-right emoji-dropdown">
                            <img class="emoji" data-src="https://assets-cdn.jable.tv/assets/images/emoji/1.svg" alt=":love:" width="18" height="18">
                            <img class="emoji" data-src="https://assets-cdn.jable.tv/assets/images/emoji/2.svg" alt=":hungry:" width="18" height="18">
                            <img class="emoji" data-src="https://assets-cdn.jable.tv/assets/images/emoji/3.svg" alt=":tongue:" width="18" height="18">
                            <img class="emoji" data-src="https://assets-cdn.jable.tv/assets/images/emoji/4.svg" alt=":skr:" width="18" height="18">
                            <img class="emoji" data-src="https://assets-cdn.jable.tv/assets/images/emoji/5.svg" alt=":cool:" width="18" height="18">
                            <img class="emoji" data-src="https://assets-cdn.jable.tv/assets/images/emoji/6.svg" alt=":funny:" width="18" height="18">
                            <img class="emoji" data-src="https://assets-cdn.jable.tv/assets/images/emoji/7.svg" alt=":sad:" width="18" height="18">
                            <img class="emoji" data-src="https://assets-cdn.jable.tv/assets/images/emoji/8.svg" alt=":devil:" width="18" height="18">
                            <img class="emoji" data-src="https://assets-cdn.jable.tv/assets/images/emoji/9.svg" alt=":angry:" width="18" height="18">
                        </div>
                        <label for="comment-edit">留言內容</label>
                        <input id="comment-edit" name="comment" class="form-control" type="text">
                    </div>
                    <input type="hidden" name="action" value="edit_comment">
                    <input type="hidden" name="comment_id" value="">
                    <button class="btn btn-submit btn-block" type="submit">確定</button>
                </div>
            </div>
        </div>
    </form>
</div>
