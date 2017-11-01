<?php
/**
 * Created by PhpStorm.
 * User: Mopkau
 * Date: 12.10.2017
 * Time: 13:59
 */

namespace bulk\actions;


use common\components\MainModel;
use notifier\helpers\HistoryHelper;
use notifier\helpers\SendHelper;
use notifier\models\db\NotifierTemplates;
use yii\base\Action;
use yii\db\ActiveRecord;
use yii\web\Controller;

class NotifierAction extends Action
{
    public $attribute = 'bulk';

    private $_ids;

    private $_templateID;

    public function init()
    {
        parent::init();
        $this->_templateID = $_GET['template'];
        $this->setIds(\Yii::$app->request->post($this->attribute));
    }

    public function run(){
        $sender = new SendHelper($this->prepareModels()->all() , new HistoryHelper(), NotifierTemplates::findOne(['id'=>$this->_templateID]));
        $sender->notify();

        return $this->prepareModels()->count();


    }

    private function setIds($value){
        $this->_ids = $value;
    }

    private function prepareModels(){
        $class = $this->controller->getModelClass();
        $searcher = new $class;
        return $searcher::find()->andFilterWhere(['in','id',$this->_ids]);
    }

}