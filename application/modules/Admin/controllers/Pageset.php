<?php

/**
 * Class ContentsController
 * @author xiongba
 * @date 2022-11-03 09:30:57
 */
class PagesetController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
     
       
        $optionser = new service\OptionService();
        $maxNavbarMenuNum = $optionser->getSubKey('theme:Mirages', 'maxNavbarMenuNum');
        $headNav = $optionser->getSubKey('theme:Mirages', 'toolbarItems');
        $footMenu = $optionser->getSubKey('plugin:FootMenu', 'foot_menu');
        $foot_link = $optionser->getSubKey('plugin:FootMenu', 'foot_link');
        $contact_link = $optionser->getSubKey('plugin:FootMenu', 'contact_link');
        $footDesc = $optionser->getSubKey('plugin:FootMenu', 'foot_desc');
        
        // 获取底部版权内容
        $footerCopyright = $optionser->getSubKey('plugin:FootMenu', 'footer_copyright');
        
        // 获取文章详情页底部追加内容
        $articleBottomContent = $optionser->get('article_bottom_content');
        
        $this->assign('footDesc',$footDesc);
        $this->assign('headNav',$headNav);
        $this->assign('footMenu',$footMenu);
        $this->assign('footLink',$foot_link);
        $this->assign('contactLink',$contact_link);
        $this->assign('maxNavbarMenuNum',$maxNavbarMenuNum);
        $this->assign('footerCopyright',$footerCopyright);
        $this->assign('articleBottomContent',$articleBottomContent);
        
        $this->display();
    }

    public function menuAction()
    {
        $optionser = new service\OptionService();
        $maxNavbarMenuNum = $optionser->getSubKey('theme:Mirages', 'maxNavbarMenuNum');
       
        $headNav = $optionser->getSubKey('theme:Mirages', 'toolbarItems');
        $footMenu = $optionser->getSubKey('plugin:FootMenu', 'foot_menu');
        $foot_link = $optionser->getSubKey('plugin:FootMenu', 'foot_link');
        $contact_link = $optionser->getSubKey('plugin:FootMenu', 'contact_link');
        $legalLinks = $optionser->getSubKey('plugin:FootMenu', 'legal_links');
        $friendLinks = $optionser->getSubKey('plugin:FootMenu', 'friend_links');
        $footDesc = $optionser->getSubKey('plugin:FootMenu', 'foot_desc');
       
        $this->assign('headNav',$headNav);
        $this->assign('footMenu',$footMenu);
        $this->assign('footLink',$foot_link);
        $this->assign('contactLink',$contact_link);
        $this->assign('legalLinks',$legalLinks);
        $this->assign('friendLinks',$friendLinks);
        $this->assign('footDesc',$footDesc);
        $this->assign('maxNavbarMenuNum',$maxNavbarMenuNum);
        
        $this->display();
    }

    public function photoAction()
    {
        $optionser = new service\OptionService();

        $tech_ios = $optionser->getSubKey('plugin:TechPhoto', 'ios') ?? OptionsModel::getPhototech();
        $tech_and = $optionser->getSubKey('plugin:TechPhoto', 'android')?? OptionsModel::getPhototech();
        $tech_ios_txt = $optionser->getSubKey('plugin:TechPhoto', 'ios_txt') ?? OptionsModel::getPhototech();
        $tech_and_txt = $optionser->getSubKey('plugin:TechPhoto', 'android_txt')?? OptionsModel::getPhototech();
        $tech_ios_prompt = $optionser->getSubKey('plugin:TechPhoto', 'ios_prompt') ?? array_fill(0, count($tech_ios), '');
        $tech_and_prompt = $optionser->getSubKey('plugin:TechPhoto', 'android_prompt') ?? array_fill(0, count($tech_and), '');

        $this->assign('techIos',$tech_ios);
        $this->assign('techAnd',$tech_and);
        $this->assign('techIosTxt',$tech_ios_txt);
        $this->assign('techAndTxt',$tech_and_txt);
        $this->assign('techIosPrompt',$tech_ios_prompt);
        $this->assign('techAndPrompt',$tech_and_prompt);

        $this->display();
    }

    public function set_navAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }

        try {
            $params = $this->postArray();
            test_assert($params, "未传入任何修改参数");

            transaction(function () use ($params) {
                $optionser = new service\OptionService();
                if (isset($params['maxNavbarMenuNum'])) {
                    $optionser->setSubKey('theme:Mirages', 'maxNavbarMenuNum', $params['maxNavbarMenuNum']);
                }
                if (isset($params['footMenu'])) {
                    $optionser->setSubKey('plugin:FootMenu', 'foot_menu', $params['footMenu']);
                }
                if (isset($params['footLink'])) {
                    $optionser->setSubKey('plugin:FootMenu', 'foot_link', $params['footLink']);
                }
                if (isset($params['contactLink'])) {
                    $optionser->setSubKey('plugin:FootMenu', 'contact_link', $params['contactLink']);
                }
                if (isset($params['legalLinks'])) {
                    $optionser->setSubKey('plugin:FootMenu', 'legal_links', $params['legalLinks']);
                }
                if (isset($params['friendLinks'])) {
                    $optionser->setSubKey('plugin:FootMenu', 'friend_links', $params['friendLinks']);
                }
                if (isset($params['footDesc'])) {
                    $optionser->setSubKey('plugin:FootMenu', 'foot_desc', $params['footDesc']);
                }
                if (isset($params['headNav'])) {
                   $optionser->setSubKey('theme:Mirages', 'toolbarItems', $params['headNav']);
                }

                if (isset($params['img_techios'])) {
                    $optionser->setSubKey('plugin:TechPhoto', 'ios', $params['img_techios']);
                }
                if (isset($params['img_techand'])) {
                    $optionser->setSubKey('plugin:TechPhoto', 'android', $params['img_techand']);
                }
                if (isset($params['txt_techios'])) {
                    $optionser->setSubKey('plugin:TechPhoto', 'ios_txt', $params['txt_techios']);
                }
                if (isset($params['txt_techand'])) {
                    $optionser->setSubKey('plugin:TechPhoto', 'android_txt', $params['txt_techand']);
                }
                if (isset($params['prompt_techios'])) {
                    $optionser->setSubKey('plugin:TechPhoto', 'ios_prompt', $params['prompt_techios']);
                }
                if (isset($params['prompt_techand'])) {
                    $optionser->setSubKey('plugin:TechPhoto', 'android_prompt', $params['prompt_techand']);
                }
            });

            yac()->delete("options");
            yac()->delete("options:all");
            return $this->ajaxSuccessMsg('修改成功');

        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    
    public function baseAction()
    {
        
        $this->display();
    }

     public function set_optionAction(){

     
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
   
        try{
        
            $params = $this->postArray();

            test_assert($params,"未传入任何修改参数");

            transaction(function () use($params){
                $optionser = new service\OptionService();

                if (isset($params['footDesc'])) {
                    $optionser->setSubKey('plugin:FootMenu', 'foot_desc', $params['footDesc']);
                    unset($params['footDesc']); 
                }
                
                if (isset($params['footer_copyright'])) {
                    $optionser->setSubKey('plugin:FootMenu', 'footer_copyright', $params['footer_copyright']);
                    unset($params['footer_copyright']); 
                }
                
                foreach ($params as $key=>$val){
                   $ret =  $optionser->set( $key, $val);
                   switch ($key){
                       case "siteUrl": // 网站主域名
                           $file = APP_PATH.'/application/config.php';
                           $site = @include($file);
                           if(!is_array($site)) $site = [];
                           $site['site_url'] = $val;
                           $write_data = "<?php


return ".var_export($site,true).";";
                           safe_write($file, $write_data);
                           break;
                   }

//                    test_assert($ret !== false,"{$key}的值修改失败");
                }
            });
            yac()->delete("options");
            yac()->delete("options:all");
            yac()->delete("site:copyright"); // 清除版权缓存
            redis()->del("options");
            return $this->ajaxSuccessMsg('修改成功');

        }catch (Throwable $e){
               return $this->ajaxError($e->getMessage());
        }

    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return ContentsModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'cid';
    }

    /**
     * 定义数据操作日志
     * @return string
     * @author xiongba
     */
    protected function getLogDesc(): string {
        return '';
    }
}