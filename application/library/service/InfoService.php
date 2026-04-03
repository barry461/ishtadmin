<?php


namespace service;

use InfoModel;
use InfoPicModel;
use InfoVipModel;
use InfoVipResourcesModel;
use MemberModel;

class InfoService
{
    public $member;
    public $redis;
    function __construct(){
        // $this->MvService = new MvService($member);
        // $this->redis = \tools\RedisService::instance();

    }
    public function getInfoById($id){

        $info = InfoModel::find($id);
        if($info){
            $info = $info->toArray();
            if($info['authentication'] == InfoModel::AUTHENTICATION_FLAG){
                $info['authentication'] = true;
            }else{
                $info['authentication'] = false;
            }
            $info['pic'] = InfoModel::find($id)->photos;
            if($info['pic']->count()){
                $info['pic']->toArray();
                foreach($info['pic'] as &$v){
                    $v['url'] = url_image($v['url']);
                }
            }  
            return $info;
        }
        else{
            return null;
        }   
    }
    public function getVipInfoById($id){

        $info = InfoVipModel::find($id);
        if($info){
            $info = $info->toArray();
            $agent = MemberModel::where('aff',$info['aff'])->first();
            if($agent){
                $info['uuid'] = $agent->uuid;
                $info['nickname'] = $agent->nickname;
                $info['thumb'] = $agent->thumb;
            }else{
                $info['uuid'] = null;
                $info['nickname'] = null;
                $info['thumb'] = null;
            }
            
            $info['resources'] = $this->getInfoVipResources($info['id']);
            $info['tags']  = $this->getInfoVipTags($info['id']);       

            return $info;
        }
        else{
            return null;
        }       
    }
    public function getInfoListByWhere($where,$limit,$offset,array $whereIn=null,$whereNotEq=null,$order=['updated_at','desc'],$orderRaw=null,$whereLess=null,$whereNotIn=null){
        $infos = InfoModel::where($where)
        ->limit($limit)
        ->offset($offset);
        if($orderRaw){
            $infos = $infos->orderByRaw($orderRaw);
        }else{
            $infos = $infos->orderBy(...$order);
        }
        if($whereLess){
            $infos = $infos->where(...$whereLess);
        }
        if($whereIn){
            $infos = $infos->whereIn(array_keys($whereIn)[0],array_values($whereIn)[0]);
        }
        if($whereNotEq){
            $infos = $infos->where(array_keys($whereNotEq)[0],'!=',array_values($whereNotEq)[0]);
        }
        if($whereNotIn){
            $infos = $infos->whereIntegerNotInRaw(array_keys($whereNotIn)[0],array_values($whereNotIn)[0]);
        }
        // var_dump($whereIn);exit;
        // echo $infos->toSql();exit;
        $infos = $infos->get();
        if($infos->count()){
            $infos = $infos->toArray();
            foreach($infos as &$info){
                $info['pic'] = $this->getInfoPic($info);
                
//                if($info['authentication'] == InfoModel::AUTHENTICATION_FLAG){
//                    $info['authentication'] = true;
//                }else{
//                    $info['authentication'] = false;
//                }
                $this->unsetInfo($info);
                // $info['status'] = InfoModel::STATUS[$info['status']];

            }
            return $infos;           
        }
        else{
            return null;
        }
    }
    public function getVipInfoListByWhere($where,$limit,$offset,array $whereIn=null){

        $infos = \InfoVipModel::where($where)
        ->limit($limit)
        ->offset($offset)
        ->orderByDesc("id");
        if($whereIn){
            $keys = array_keys($whereIn);
            foreach($keys as $key){
                $infos = $infos->whereIn($key,$whereIn[$key]);
            }
        }


        $infos = $infos->get();
        if($infos->count()){
            $infos = $infos->toArray();
            foreach($infos as &$info){
                $info['resources'] = $this->getInfoVipResources($info['id']);  
                $info['tags'] = $this->getInfoVipTags($info['id']);
                if ($info['status']==6){
                    $info['status'] = 1;
                }

            }
             return $infos;
        }
        else{
            return [];
        }
    }
    public function getGoodsListByWhere($where,$limit,$offset,array $whereIn=null,$whereSpecial=null,$order=['updated_at','desc']){
        $infos = \InfoVipModel::where($where)
        ->limit($limit)
        ->offset($offset)
        ->orderByRaw('girl_age desc , appointment desc');
        if($whereIn){
            $keys = array_keys($whereIn);
            foreach($keys as $key){
                $infos = $infos->whereIn($key,$whereIn[$key]);
            }
        }
        if($whereSpecial){
            foreach($whereSpecial as $v){
                $infos = $infos->where(...$v);
            }
        }
        // var_dump($infos->toSql());exit;
        $infos = $infos->get();
        if($infos->count()){
            $infos = $infos->toArray();
            foreach($infos as &$info){
                $info['resources'] = $this->getInfoVipResources($info['id']);  
                $info['tags'] = $this->getInfoVipTags($info['id']);  
                $agent = UserService::getUserByAff($info['aff']);
                $info['thumb'] = $agent->thumb;
                $info['nickname'] = $agent->nickname;
                $info['phone'] = null;
                $info['address'] = null;
            }
            return $infos;           
        }
        else{
            return null;
        }
    }
    public function getGirlListByWhere($where,$limit,$offset,array $whereIn=null,$whereSpecial=null,$order=['updated_at','desc']){
        $infos = \InfoVipModel::where($where)
        ->limit($limit)
        ->offset($offset)
        ->orderByRaw('girl_age desc , appointment desc');
        if($whereIn){
            $keys = array_keys($whereIn);
            foreach($keys as $key){
                $infos = $infos->whereIn($key,$whereIn[$key]);
            }
        }
        if($whereSpecial){
            foreach($whereSpecial as $v){
                $infos = $infos->where(...$v);
            }
        }
        // var_dump($infos->toSql());exit;
        $infos = $infos->get();
        if($infos->count()){
            $infos = $infos->toArray();
            foreach($infos as &$info){
                $info['resources'] = $this->getInfoVipResources($info['id']);  
                $info['tags'] = $this->getInfoVipTags($info['id']);  
                $agent = UserService::getUserByAff($info['aff']);
                $info['thumb'] = $agent->thumb;
                $info['nickname'] = $agent->nickname;
                $info['address'] = null;
            }
            return $infos;           
        }
        else{
            return null;
        }
    }
    public function getVipInfoListByFiler($wheres,$whereIn,$tags,$count=null,$limit=null,$offset=null,$order=null){
        $infos = new \InfoVipModel();
        if($tags){
            $tags = is_array($tags) ? $tags : explode(",",$tags);
            $infos = $infos->select('info_vip.*')
            ->join('info_vip_tag','info_vip_tag.info_id','=','info_vip.id')
            ->whereIn('info_vip_tag.tag_id',$tags)
            ->groupBy('info_vip.id');
        }
        if($whereIn){
            $infos = $infos->whereIn(array_keys($whereIn)[0],array_values($whereIn)[0]);
        }
        foreach($wheres as $where){
            $infos = $infos->where(...$where);
        }

        if($count){
            // return $infos->toSql();
            return $infos->count();
        }
        // var_dump($infos->toSql());exit;
        $infos = $infos->limit($limit)->offset($offset);
        if($order){
            switch($order){
                /*
                case 1:
                    $infos = $infos->orderByRaw('sort asc,view/appointment desc');
                    break;
                case 2:
                    $infos = $infos->orderBy('appointment','desc');
                    break;
                case 3:
                    $infos = $infos->orderByRaw('mark/confirm desc');
                    break;*/
                case "orderDesc":
                    $infos = $infos->orderByRaw('fee_ct desc');
                    break;
                case "orderAsc":
                    $infos = $infos->orderBy('fee_ct');
                    break;
                case "payDesc":
                    $infos = $infos->orderByRaw('fee desc');
                    break;
                case "payAsc":
                    $infos = $infos->orderBy('fee');
                    break;
            }

        }
        $infos = $infos->orderByDesc("id");
        $infos = $infos->get();


        if($infos->count()){
            $infos = $infos->toArray();
            foreach($infos as &$info){
                $info['resources'] = $this->getInfoVipResources($info['id']);
                $info['tags']  = $this->getInfoVipTags($info['id']);
                $info['price']  = ($info["price"] ?? $info["price_p"])."/次";
            }
            return $infos;
        }
        else{
            return [];
        }
    }
    public function filterStringFormat($filterString,$field,$origin){
        if(!$filterString){
            return $origin;
        }
        $filterString = explode('-',$filterString);
        $where = [];
        if(count($filterString)>1){
            if($filterString[0] == 'min' || $filterString[1] == 'max'){
                if($filterString[0] == 'min'){
                    $where[] = [$field,'<',$filterString[1]];
                }else{
                    $where[] = [$field,'>=',$filterString[0]];
                }
            }
            else{
                $where[] = [$field,'>=',$filterString[0]];
                $where[] = [$field,'<',$filterString[1]];
            }
        }else{
            $where[] = [$field,'=',$filterString[0]];
        }
        $origin = $this->pushArraysToArray($origin,$where);
        return $origin;
    }
    public function pushArraysToArray($array,$arrays){
        foreach($arrays as $v){
            $array[] = $v;
        }
        return $array;
    }
    public function getInfoPic($info){
        $pic = InfoPicModel::where('info_id',$info['id'])
                        ->get()->toArray();
        foreach($pic as &$v){
            $v['url'] = url_image($v['url']);
        }
        return $pic;
    }
    public function getInfoPicById($id){
        $pic = InfoModel::find($id)
                        ->photos->toArray();
        foreach($pic as &$v){
            $v['url'] = url_image($v['url']);
        }
        return $pic;
    }
    public function getInfoVipResources($infoId){
        $resources = \InfoVipModel::select('info_vip_resources.*')
                        ->join('info_vip_resources','info_vip_resources.info_id','=','info_vip.id')
                        ->orderBy('info_vip_resources.sort','asc')
                        ->where('info_vip_resources.info_id',$infoId)
                        ->get()
                        ->toArray();
        foreach($resources as &$v){
            switch($v['type']){
                case InfoVipResourcesModel::TYPE_IMAGE:
                    $v['url'] = url_image($v['url']);
                break;
                case InfoVipResourcesModel::TYPE_VIDEO:
                    $v['url'] = url_video($v['url']);
                break;
            }
        }
        return $resources;
    }
    public function getInfoVipTags($infoId){
        $tags = \InfoVipTagModel::select('tags.name','tags.id')
                        ->join('tags','tags.id','=','info_vip_tag.tag_id')
                        ->where('info_vip_tag.info_id',$infoId)
                        ->get()
                        ->toArray();
        shuffle($tags);
        return $tags;
    }
    public function getUserAllCityCode($member){
        $UserService = new UserService(null);
        $cityCode = $UserService->getUserCityCode($member);
        $subCity = redis()->getWithSerialize('city:sub:list:'.$cityCode);
        if(!$subCity){
            $subCity = \AreaCnModel::where('parentid',$cityCode)->get();
            redis()->setWithSerialize('city:sub:list:'.$cityCode,$subCity,86400);
        }
        $return[] = $cityCode;
        if($subCity->count()){
            foreach($subCity as $v){
                $return[] = $v['id'];
            }
        }
        return $return;
    }
    public function getAllCityCode($cityCode){
        $subCity = \AreaCnModel::where('parentid',$cityCode)->get();
        $return[] = $cityCode;
        if($subCity->count()){
            foreach($subCity as $v){
                $return[] = $v['id'];
            }
        }
        return $return;
    }
    public function unsetInfo(&$info){
        unset($info['phone']);
        unset($info['address']);
        unset($info['source_link']);
    }
    public function unsetConfirm(&$confirm){
        unset($confirm->girl_service_detail);
        unset($confirm->photoAlbum);
    }
    function search($word,$offset,$limit,$member){
        $allCityCode = $this->getUserAllCityCode($member);
        $allCityCode = implode(',',$allCityCode);
        \KeywordModel::create(['word'=>$word]);
        $columns = 'title,girl_service_type';
        $query = "MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE) and cityCode in (".$allCityCode.") and status = 2 limit ? OFFSET ?";
        $results = InfoModel::whereRaw($query, array($word, $limit,$offset))->get();

        if($results->count()){
            $results = $results->toArray();
            foreach($results as &$info){
                $info['pic'] = $this->getInfoPic($info);
                $this->unsetInfo($info);
                // $info['status'] = InfoModel::STATUS[$info['status']];

            }
            return $results;
        }
        else{
            return null;
        }

    }
    function getCityCodeByPostNum(){
        $info = InfoVipModel::selectRaw('count(*) as num,cityCode')
        ->where('status',InfoVipModel::STATUS_PASS)
        ->groupBy('cityCode')
        ->orderBy('num','desc')
        ->get();
        $return = [];
        if($info->count()){
            // $info = $info->toArray();
            foreach($info as $v){
                $return[] = $v['cityCode'];
            }

        }
        return $return;
    }
    function getCoinByPrice($price){
        switch($price){
            case $price<=500:
                return 100;
            break;
            case $price>500 && $price <=1000:
                return 200;
            break;
            case $price>1000:
                return 300;
            break;
        }
    }
    function isPostInfo($phone){
        preg_match('/QQ([0]+)([0-9]+)/',$phone,$matches);
        if($matches){
            $phone = 'QQ'.$matches[2];
        }
        $info = InfoModel::where('phone',$phone)
        // $info = InfoModel::where('phone','like','%'.$phone.'%')
        ->where('status','!=',InfoModel::STATUS_DELETE)
        ->first();
        if($info){
            return true;
        }else{
            return false;
        }
    }
    function ifPostMoneyInfo($aff){
        $infos = InfoModel::where('uid',$aff)
        ->where('status','!=',InfoModel::STATUS_DELETE)
        ->get();
        $coin = 0;
        $money = 0;
        foreach($infos as $info){
            if($info->is_money == InfoModel::IS_COIN && $info->status == InfoModel::STATUS_PASS){
                $coin++;
            }else if($info->is_money == InfoModel::IS_MONEY
            && ($info->status == InfoModel::STATUS_PASS || $info->status == InfoModel::STATUS_INIT)){
                $money++;
            }
        }
        if($coin > $money){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获得城市code
     * @param $adcode
     * @return array|mixed
     */
    function getCityAdcode($adcode){
        if (empty($adcode))
            return [];

        if (substr($adcode,-4) !== '0000')
            return [$adcode];

        $list = cached(\CityModel::CITY_LISTS_CHILDREN.$adcode)
            ->expired(3600)
            ->serializerPHP()
            ->fetch(function () use ($adcode) {
                $list  = \CityModel::query()
                    ->where('parent',$adcode)
                    ->where('is_show',1)
                    ->pluck('adcode')
                    ->toArray();
                return $list;
            });
        return $list;
    }
}