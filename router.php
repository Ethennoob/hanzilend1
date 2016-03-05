<?php
/*
 |--------------------------------------------------------------------------
 | 应用路由
 |--------------------------------------------------------------------------
 |
 */

use System\Router;

/*
示例 
Router::get('/','Api/Test/index');
Router::get('/test/{id}','Api/Test/index');

Router::group(['prefix'=>'/bb/ccc','module'=>'Api'],function (){
    Router::get('/bb/ssadasd','Test/index');
    Router::get('/bb3333/{id}','Test/index');
    Router::resources('article','Test');

}); 

Router::group(['prefix'=>'/','module'=>'Api'],function (){
    //Router::get('/bb/ssadasd','Test/index');

    Router::resources('article','Article');

}); 

*/

/* Router::group(['prefix'=>'/api','module'=>'Api'],function (){
	Router::resources('articles','Article');
	Router::resources('versions','Version');
	Router::resources('sessions','Session');
	Router::resources('categories','Category');

}); */



