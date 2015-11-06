<?php
/**
 * 盛世分销系统---文章管理类
 * @authors 凌翔 (553299576@qq.com)
 * @date    2015-10-21 15:10:49
 * @version $Id$
 */

namespace AppMain\controller\Admin;
use \System\BaseClass;

class ArticleController extends BaseClass {
    /**
     *   文章数据的增，删，改，查；
     *   文章字段的验证；
     *   memcache添加，获取，删除
     *   获取配置文件信息
     *   常用方法/System/database/BaseTable.class.php(部分案例见本文件最后)
     */
    
    /*
    文章添加活动
     */
    public function articleAdd(){
        //-----------字段验证-----------
        $rule = [
            'cat_id'        =>['egNum',null,true],
            'title'         =>[],
            'content'       =>[],
        ];
        $this->V($rule);

    
        foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        //图片上传
        $pictureName = $_FILES['Pic'];
        $imgarray = $this->H('PictureUpload')->pictureUpload($pictureName,'article',false);


        $data['Pic'] = $imgarray['a1'];
        $data['add_time']     = time();
    
        $article = $this->table('article')->save($data);
        if(!$article){
            $this->R('',40001);
        }
    
        $this->R();
    }

    /**
     * 文章内容列表___查询列表
     */
    public function articleOneList(){
        $this->V(['is_show'=>['in',[0,1],false]]);
        $where=['is_on'=>1];
        //$this->queryFilter，拼接查询字段
        $whereFilter=$this->queryFilter($where,['is_show']);

        $pageInfo = $this->P();

        $class = $this->table('article')->where($whereFilter)->order('add_time desc');
        //查询并分页
        $articlelist = $this->getOnePageData($pageInfo,$class,'get','getListLength',null,false);
        if($articlelist){
            foreach ($articlelist as $k=>$v){
                $articlelist[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $articlelist[$k]['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
            }
        }else{
            $articlelist = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['articlelist'=>$articlelist,'pageInfo'=>$pageInfo]);
    }

     /**
     * 文章内容查询___一条数据
     */
    public function articleOneDetail(){
    
        $this->V(['id'=>['egNum',null,true]]);

        $id = intval($_POST['id']);
        //查找memcache缓存
        $articleDetail=$this->S()->get('article_'.$id);
        if (!$articleDetail){
            //查询一条数据
            $article = $this->table('article')->where(['is_on'=>1,'id'=>$id])->get(null,true);
            if(!$article){
                $this->R('',70009);
            }

            $article['update_time'] = date('Y-m-d H:i:s',$article['update_time']);
            $article['add_time'] = date('Y-m-d H:i:s',$article['add_time']);
            //设置memcache缓存，serialize（序列化），兼容window和linux系统
            $this->S()->set('article_'.$id , serialize($articleDetail),60*60);
        }
        else{
            //设置memcache缓存，unserialize（反序列化），兼容window和linux系统
            $articleDetail=unserialize($articleDetail);
        }
        
        $this->R(['article'=>$article]);
    }
    /**
     * 修改一条文章数据
     */
    public function articleOneEdit(){
        $rule = [
            'id'            =>['egNum',null,true],
            'cat_id'        =>['egNum',null,true],
            'title'         =>[null,null,true],
            'content'       =>[null,null,true],
        ];
        $this->V($rule);
        $id = intval($_POST['id']);

        $article = $this->table('article')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
        if(!$article){
            $this->R('',70009);
        }

        unset($rule['id']);
        foreach ($rule as $k=>$v){
            if(isset($_POST[$k])){
                $data[$k] = $_POST[$k];
            }
        }
        
       //图片上传
        $pictureName = $_FILES['Pic'];
        $imgarray = $this->H('PictureUpload')->pictureUpload($pictureName,'article',false);

        //删除图片文件
        $pic_url = $this->table('article')->where(['id'=>$id,'is_on'=>1])->get(['Pic'],true);
        foreach ($pic_url as $key => $v) {
             $delete = unlink("../html".$v);
         }
        if (!$delete) {
            $this->R('',40020);
        }
        /*---------------------------------------------*/
        $data['Pic'] = $imgarray['a1'];
        $data['update_time']  = time();
    
        $article = $this->table('article')->where(['id'=>$id])->update($data);
        if(!$article){
            $this->R('',40001);
        }
        //活动更改了内容，删除活动信息的memcache,缓存
        $this->S()->delete('aticle_'.$id);
        $this->R();
    }
    /**
     *删除一条文章数据（设置数据库字段为0，相当于回收站）
     */
    public function articleOneDelete(){
    
        $this->V(['id'=>['egNum',null,true]]);
        $id = intval($_POST['id']);
         
        $article = $this->table('article')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
    
        if(!$article){
            $this->R('',70009);
        }
    
        $article = $this->table('article')->where(['id'=>$id])->update(['is_on'=>0]);
        if(!$article){
            $this->R('',40001);
        }
        $this->S()->delete('aticle_'.$id);
        $this->R();
    }
    /**
     *删除一条文章数据（清除数据）
     */
    public function articleOneDeleteconfirm(){
    
        $this->V(['id'=>['egNum',null,true]]);
        $id = intval($_POST['id']);
         
        $article = $this->table('article')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
    
        if(!$article){
            $this->R('',70009);
        }
        //删除图片文件
        $pic_url = $this->table('article')->where(['id'=>$id,'is_on'=>1])->get(['Pic'],true);
        foreach ($pic_url as $key => $v) {
             $delete = unlink("../html".$v);
         }
        if (!$delete) {
            $this->R('',40020);
        }

        $article = $this->table('article')->where(['id'=>$id])->delete();
        if(!$article){
            $this->R('',40001);
        }
        $this->S()->delete('aticle_'.$id);
        $this->R();
    }
    
}