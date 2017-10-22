<?php
/**
 * Created by PhpStorm.
 * User: Mopkau
 * Date: 12.10.2017
 * Time: 13:59
 */

namespace bulk\actions;


use yii\base\Action;
use yii\web\Controller;

class UnpublishAction extends Action
{
    public $attribute = 'bulk';

    private $_ids;

    public function __construct($id, Controller $controller, array $config = [])
    {
        parent::__construct($id, $controller, $config);

    }

    public function run(){
        s( \Yii::$app->request->post($this->attribute));
    }

    private function setIds($value){
        $postData = \Yii::$app->request->post($this->attribute);
    }

}