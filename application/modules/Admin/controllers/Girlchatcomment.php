<?php

/**
 * Class GirlchatcommentController
 * @date 2025-04-10 07:06:44
 */
class GirlchatcommentController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (GirlChatCommentModel $item) {
            $item->setHidden([]);

            $item->status_str = GirlChatCommentModel::STATUS[$item->status];
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $this->display();
    }


    public function acceptAction()
    {
        $id = $this->postArray();
        $id = (int) $id['_pk'];

        $comment = GirlChatCommentModel::find($id);
        $comment->status = GirlChatCommentModel::STATUS_PASS;
        $comment->save();
        $infovip = InfoVipModel::find($comment->girl_chat_id);
        $infovip->comment_ct += 1;
        $infovip->save();
        return $this->ajaxSuccessMsg('操作成功');
    }


    public function rejectAction()
    {
        $id = $this->postArray();
        $id = (int) $id['_pk'];

        $comment = GirlChatCommentModel::find($id);
        $comment->status = GirlChatCommentModel::STATUS_FAILURE;
        $comment->save();
        return $this->ajaxSuccessMsg('操作成功');
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return GirlChatCommentModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'id';
    }

    /**
     * 定义数据操作日志
     * @return string
     */
    protected function getLogDesc(): string {
        return '';
    }
}