<div class="post-content" style="margin: 1rem 0">
                    </div>
                    <div class="line"></div>

                    <div id="comments">
                        <div id="respond-post-<?=  $content->cid ?>" class="respond">
                            <div class="cancel-comment-reply">
                                <a id="cancel-comment-reply-link" href="/archives/{{ $content->cid }}/#respond-post-{{ $content->cid}}"
                                    rel="nofollow" style="display:none"
                                    onclick="return TypechoComment.cancelReply();">取消回复</a>
                            </div>
                            <span id="response" class="widget-title text-left">添加新评论</span>
                            <form method="post" action="/archives/{{ $content->cid }}/comment" id="comment-form">
                                <p>
                                    <textarea rows="5" name="text" id="textarea" placeholder="在这里输入你的评论..."
                                        style="resize:none;" required></textarea>
                                </p>
                                <input type="hidden" value="<?= $content->cid?>" name="cid" />
                                <input class="comment-input" type="text" name="author" id="author" placeholder="称呼 *"
                                    value="" required />
                                <p style="margin-top: 10px">
                                    <span class="OwO"></span>
                                </p>
                                <p><input type="submit" value="提交评论" data-now="刚刚" data-init="提交评论"
                                        data-posting="提交评论中..." data-posted="评论提交成功" data-empty-comment="必须填写评论内容"
                                        class="button" id="submit"></p>
                            </form>
                        </div>
                        <div id="respond-placeholder" style="display:none;"></div>
                        <script>
                            $(document).ready(function () {
                                $('#comment-form').submit(function (event) {
                                    event.preventDefault();
                                    const formData = $(this).serialize();
                                    const _url = $(this).attr('action');
                                    const _form = $(this);
                                    layer.load();
                                    $.ajax({
                                        type: "POST",
                                        url: _url,
                                        data: formData,
                                        dataType: "json",
                                        beforeSend: function () {
                                        },
                                        success: function (res) {
                                            if (res.status) {
                                                layer.msg('评论成功,等待审核');
                                                _form.trigger('reset');
                                                // 埋点触发
                                                if (res.tracking_data && typeof window.tracker === 'function') {
                                                    try {
                                                        window.tracker(res.tracking_data);
                                                    } catch(e) { console.error('Tracking Error:', e); }
                                                }
                                            } else {
                                                layer.msg('评论失败！');
                                            }
                                        },
                                        error: function (res) {
                                            layer.msg('评论失败！');
                                        },
                                        complete: function () {
                                            layer.closeAll('loading');
                                        }
                                    });
                                });
                            })
                        </script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                let page = 1;
                                const perPage = 3;
                                const mainListEl = document.querySelector('.comment-list');
                                const cid = `<?= $content->cid ?>`;
                             
                                async function fetchComments(pageNum = 1) {
                                    try {
                                        const res = await fetch(`/commentList/${cid}/page/${pageNum}`);
                                        const data = await res.json();
                                        if (data.code === 1 && data.commentList.length) {
                                            const total = data.total;
                                            $(".comment-num").html(`已有 ${total} 条评论`)
                                            renderMainComments(data.commentList);
                                            page++;

                                            if (data.commentList.length < data.limit) {
                                                $('.next_more span').html('~没有更多了~');
                                                $('.next_more').addClass('disabled');
                                                $('.next_more').removeClass('next_more');
                                            } 


                                        } else {
                                            $('.next_more span').html('~没有更多了~');
                                            $('.next_more').addClass('disabled');
                                            $('.next_more').removeClass('next_more');
                                        }
                                    } catch (e) {
                                        console.error('获取评论失败：', e);
                                        //alert('加载评论失败');
                                    }
                                }

                               
                                function renderMainComments(comments) {
                                    const fragment = document.createDocumentFragment();
                                    comments.forEach(comment => {
                                        const li = document.createElement('li');
                                        li.className = 'comment-body comment-parent';
                                        li.id = 'comment-' + comment.coid;
                                        const datetime = comment.created;
                                        const date1 = datetime.split(" ")[0];
                                       // console.log(date1);
                                        li.innerHTML = `
                                            <div class="comment-author">
                                                <span><img class="avatar" src="{!! theme()->image(options('comment_avatar') ?: options('user_avatar') ?: 'logo.png') !!}" alt="{{ register('site.app_name') }}" width="100" height="100" /></span>
                                                <cite class="fn color-main">${comment.author}</cite>
                                            </div>
                                            <div class="comment-reply">
                                                <a href="javascript:;" onclick="return TypechoComment.reply('comment-${comment.coid}', ${comment.coid});">回复</a>
                                            </div>
                                            <div class="comment-meta">
                                                <a href="javascript:;"><time datetime="${comment.created}">${date1}</time></a>
                                            </div>
                                            <div class="comment-content"><p>${comment.text}</p></div>
                                            ${renderChildComments(comment.items || [], comment.coid)}
                                        `;
                                        fragment.appendChild(li);
                                    });
                                    mainListEl.appendChild(fragment);
                                }

                               

                                //渲染子评论
                                function renderChildComments(childComments, parentId) {
                                    if (!childComments.length) return '';
                                    const displayed = childComments.slice(0, 5);
                                    const htmlChildren = displayed.map(renderSingleChildComment).join('');

                                    let html = `<div class="comment-children"><ol class="comment-list" data-parent="${parentId}">${htmlChildren}</ol>`;

                                    if (childComments.length > 5) {
                                        html += `
                                            <div class='more-child-comment-btns'>
                                                <div class='flex show-more-child-comment' data-parent="${parentId}">
                                                    <div class='border'></div>
                                                    <div class='content'>查看全部${childComments.length}条回复</div>
                                                    <div class='arrow'></div>
                                                </div>
                                                <div class='flex hide-more-child-comment' style="display:none;" data-parent="${parentId}">
                                                    <div class='border'></div>
                                                    <div class='content'>隐藏部分留言</div>
                                                    <div class='arrow top'></div>
                                                </div>
                                            </div>`;
                                    }

                                    html += `</div>`;
                                    return html;
                                }

                                // 单条子评论渲染（created 可能是时间戳或 "Y-m-d H:i:s" 字符串）
                                function renderSingleChildComment(c) {
                                    const datetime2 = c.created;
                                    const date2 = typeof datetime2 === 'number'
                                        ? (new Date(datetime2 * 1000).toISOString().slice(0, 10))
                                        : (datetime2 || '').toString().split(' ')[0];
                                    return `
                                        <li class="comment-body comment-child" id="comment-${c.coid}">
                                            <div class="comment-author">
                                                <span><img class="avatar" src="{!! theme()->image(options('comment_avatar') ?: options('user_avatar') ?: 'logo.png') !!}" alt="{{ register('site.app_name') }}" width="100" height="100" /></span>
                                                <cite class="fn color-main">${c.author}</cite>
                                            </div>
                                            <div class="comment-reply">
                                                <a href="javascript:;" onclick="return TypechoComment.reply('comment-${c.coid}', ${c.coid});">回复</a>
                                            </div>
                                            <div class="comment-meta">
                                                <a href="javascript:;"><time datetime="${c.created}">${date2}</time></a>
                                            </div>
                                            <div class="comment-content"><p>@${c.reply_author} ${c.text}</p></div>
                                        </li>`;
                                }
                               
                                // 显示全部子评论（按 cid、parentId 拉取该条评论下的全部回复）
                                async function showAllChildComments(parentId) {
                                    const ol = document.querySelector(`ol.comment-list[data-parent="${parentId}"]`);
                                    const res = await fetch(`/commentList/${cid}/replies/${parentId}`);
                                    const data = await res.json();
                                    if (data.code !== 1 || !data.commentList || !data.commentList.length) return;

                                    const fullList = data.commentList;
                                    const parentLi = document.querySelector(`#comment-${parentId}`);
                                    if (parentLi) parentLi.__dataItems = fullList;

                                    const fullHTML = fullList.map(renderSingleChildComment).join('');
                                    ol.innerHTML = fullHTML;
                                    document.querySelector(`.show-more-child-comment[data-parent="${parentId}"]`).style.display = 'none';
                                    document.querySelector(`.hide-more-child-comment[data-parent="${parentId}"]`).style.display = 'flex';
                                }

                                // 收起：只显示前 5 条子评论
                                function hideChildComments(parentId) {
                                    const ol = document.querySelector(`ol.comment-list[data-parent="${parentId}"]`);
                                    const parentLi = document.querySelector(`#comment-${parentId}`);
                                    const dataItems = (parentLi && parentLi.__dataItems) ? parentLi.__dataItems : [];
                                    const partialHTML = dataItems.slice(0, 5).map(renderSingleChildComment).join('');
                                    ol.innerHTML = partialHTML;
                                    document.querySelector(`.hide-more-child-comment[data-parent="${parentId}"]`).style.display = 'none';
                                    document.querySelector(`.show-more-child-comment[data-parent="${parentId}"]`).style.display = 'flex';
                                }

                                //绑定子评论按钮事件
                                document.addEventListener('click', function (e) {
                                    const showBtn = e.target.closest('.show-more-child-comment');
                                    const hideBtn = e.target.closest('.hide-more-child-comment');

                                    if (showBtn) {
                                        const parentId = showBtn.dataset.parent;
                                        showAllChildComments(parentId);
                                    }

                                    if (hideBtn) {
                                        const parentId = hideBtn.dataset.parent;
                                        hideChildComments(parentId);
                                    }
                                });

                                //加载更多主评论
                                window.next_more = function () {
                                    fetchComments(page);
                                };

                                //初始化加载第一页
                                fetchComments(page);
                            });
                            </script>

                            <script>
                                window.TypechoComment = {
                                    reply: function(commentId, parentId) {
                                        const comment = document.getElementById(commentId);
                                        const respond = document.getElementById('respond-post-<?= $content->cid ?>');
                                        const parentInput = document.createElement('input');
                                        parentInput.type = 'hidden';
                                        parentInput.name = 'parent';
                                        parentInput.id = 'comment-parent';
                                        parentInput.value = parentId;

                                   
                                        const respondPlaceholder = document.getElementById('respond-placeholder');
                                        if (!respondPlaceholder.nextSibling || respondPlaceholder.style.display === 'none') {
                                            respondPlaceholder.parentNode.insertBefore(respondPlaceholder, respond);
                                            respondPlaceholder.style.display = '';
                                        }

                                        comment.parentNode.insertBefore(respond, comment.nextSibling);

                                        const oldInput = document.getElementById('comment-parent');
                                        if (oldInput) oldInput.remove();

                                     
                                        document.getElementById('comment-form').appendChild(parentInput);

                                     
                                        document.getElementById('cancel-comment-reply-link').style.display = '';

                                        return false;
                                    },
                                    cancelReply: function() {
                                        const respond = document.getElementById('respond-post-<?= $content->cid ?>');
                                        const respondPlaceholder = document.getElementById('respond-placeholder');
                                        const cancelLink = document.getElementById('cancel-comment-reply-link');

                                        
                                        respondPlaceholder.parentNode.insertBefore(respond, respondPlaceholder);
                                        respondPlaceholder.style.display = 'none';

                                        const parentInput = document.getElementById('comment-parent');
                                        if (parentInput) parentInput.remove();

                                        cancelLink.style.display = 'none';

                                        return false;
                                    }
                                };
                            </script>

                        <div class="comment-separator">
                            <div class="comment-tab-current">
                                <span class="comment-num"></span>
                            </div>
                        </div>
                        <ol class="comment-list">
                        </ol>
                        <ol class="page-navigator">
                            <a class="next_more" onclick="next_more();return false;" title="" href="#">
                                <span>查看更多评论</span>
                            </a>
                        </ol>
                    </div>

                    <style>
                        .page-navigator {
                            text-align: center;
                            margin-top: 3rem !important;

                        }

                        #comments {
                            padding-bottom: 3rem !important;
                            /*border-bottom:rgb(68,68,68) .5pt solid;*/
                        }

                        .next_more {
                            font-size: 0.95rem;
                            background-color: rgb(38, 38, 38);
                            padding: .7rem 2rem;
                            border-radius: 2.5rem;
                            text-decoration: none !important;
                            color: rgb(26, 188, 156) !important;
                            border: rgb(26, 188, 156) solid 1px;
                        }

                        .disabled span {
                            color: #888 !important;
                        }

                        .next_more>span::after {
                            content: "";
                            display: inline-block;
                            width: .8rem;
                            height: .8rem;
                            background: url("{!! theme()->image('down') !!}") no-repeat center center;
                            background-size: contain;
                            margin-left: 5px;
                            vertical-align: middle;
                        }

                        /*#comments a {*/
                        /*    color: unset!important;*/
                        /*}*/
                        .next_more:hover {
                            text-decoration: none !important;
                        }

                        #comments a:after {
                            border-bottom: 0
                        }





                        .comment-content p {
                            display: inline-block;
                            margin: 0;
                            padding: 0;
                        }

                        #comments .comment-content img {
                            max-height: unset;
                            max-width: unset;
                            vertical-align: text-bottom;
                        }

                        .comment-content img {
                            display: inline-block !important;
                            height: 1.3rem;
                            margin: 0;

                        }

                        .comment-content .top {
                            margin-right: .3rem;
                        }

                        #comments .more-child-comment-btns {
                            display: none;
                        }

                        @media screen and (max-width: 600px) {
                            #comments .child-comment-more {
                                display: none;
                            }

                            #comments .more-child-comment-btns {
                                display: block;
                                color: #1abc9c;
                                margin-top: 10px;
                            }

                            #comments .more-child-comment-btns .flex {
                                display: flex;
                                align-items: center;
                            }

                            #comments .more-child-comment-btns .flex .border {
                                width: 2rem;
                                height: 1px;
                                background-color: #1abc9c;
                                margin-right: 10px;
                            }

                            #comments .more-child-comment-btns .flex .arrow {
                                border-bottom: 2px solid #1abc9c;
                                border-left: 2px solid #1abc9c;
                                width: 0.5rem;
                                height: 0.5rem;
                                background-color: transparent;
                                transform: rotate(-45deg);
                                margin-left: 5px;
                                transition: 0.5s all;
                            }

                            #comments .more-child-comment-btns .flex .top {
                                transform: rotate(135deg);
                                margin-bottom: -5px;
                            }

                            #comments .more-child-comment-btns .hide-more-child-comment {
                                display: none;
                            }
                        }
                    </style>

                </div>
            </div>
                   