<!-- 举报弹窗组件 -->
<div class="report-popup-wrapper" v-show="showReportPopup">
    <div class="van-overlay" @click="showReportPopup = false"></div>
    <div class="van-popup van-popup--center report-popup-dialog">
        <div class="report-popup-container">
            <div class="report-popup-header">
                <div class="report-popup-title">
                    <h3>举报这个视频 <p class="report-subtitle">如果举报属实,奖励APP会员</p></h3>
                    
                </div>
                <div class="report-popup-close" @click="showReportPopup = false">
                    <i class="fa fa-times"></i>
                </div>
            </div>
            
            <div class="report-popup-body">
                <div class="report-options">
                    <label class="report-option-item">
                        <input type="radio" name="reportReason" v-model="reportReason" value="1">
                        <span class="report-option-text">
                            上传的视频内容涉及未成年儿童、恐怖活动、人口贩卖、极端暴力等严重违反宣传这法律或结合相关关的行为,平台将立即下架相关内容
                        </span>
                    </label>
                    
                    <label class="report-option-item">
                        <input type="radio" name="reportReason" v-model="reportReason" value="2">
                        <span class="report-option-text">
                            传播含有极端暴力、虐待、血腥等违反国家法律法规的内容。一经举报属实,平台将立即删除该视频
                        </span>
                    </label>
                    
                    <label class="report-option-item">
                        <input type="radio" name="reportReason" v-model="reportReason" value="3">
                        <span class="report-option-text">
                            视频中存在未经授权使用他人作品、肖像或泄露他人隐私信息的行为,平台在接到权利人举报并核实属实后,将第一时间下架视频内容。
                        </span>
                    </label>
                </div>
                
                <div class="report-detail-section">
                    <p class="report-tips">请您提供详细或组织帮助我们解决为什么举报认为该内容违反了我们的服务条款或令人反感,请到下方或豆类联系,为更与您取得联系</p>
                    <textarea 
                        class="report-textarea" 
                        v-model="reportDetail" 
                        placeholder="请输入举报详情"
                        maxlength="200"
                        rows="4"
                    ></textarea>
                    <div class="report-textarea-count">{{ reportDetail.length }}/200</div>
                </div>
            </div>
            
            <div class="report-popup-footer">
                <button class="report-popup-btn report-btn-cancel" @click="handleCancelReport">取消</button>
                <button class="report-popup-btn report-btn-submit" @click="handleSubmitReport">提交</button>
            </div>
        </div>
    </div>
</div>
