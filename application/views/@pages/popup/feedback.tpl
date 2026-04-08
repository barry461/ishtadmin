<div id="flag-form" class="modal-dialog fancybox-content">
    <div class="modal-content-wrapper">
        <div class="modal-content">
            <div class="modal-header">
                <h4>问题回报</h4>
                <p>如果您的问题与影片播放有关，请详细描述情况，以帮助我们找出及解决问题。</p>
            </div>
            <div class="modal-body">
                <div class="row radio">
                    <div class="col-6">
                        <input id="flag_id_1" name="feed_type_id" class="radio" value="1" type="radio">
                        <label for="flag_id_1">不当内容</label>
                    </div>
                    <div class="col-6">
                        <input id="flag_id_2" name="feed_type_id" class="radio" value="2" type="radio">
                        <label for="flag_id_2">影片失效</label>
                    </div>
                    <div class="col-6">
                        <input id="flag_id_3" name="feed_type_id" class="radio" value="3" type="radio">
                        <label for="flag_id_3">版权</label>
                    </div>
                    <div class="col-6">
                        <input id="flag_id_4" name="feed_type_id" class="radio" value="4" type="radio" checked>
                        <label for="flag_id_4">其他</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="feed_message">描述 (必填)</label>
                    <input id="feed_message" name="feed_message" class="form-control" type="text">
                </div>
                <input class="btn btn-submit btn-block" type="button" value="发送" id="bind_feedback_submit">
            </div>
        </div>
    </div>
</div>
