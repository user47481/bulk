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
/**
 * Class NotifierAction
 * @package bulk\actions
 */
class NotifierAction extends Action
{
    /**
     * @var string
     */
    public $attribute = 'bulk';

    /**
     * @var $_ids
     */
    private $_ids;

    /**
     * @var $_templateID
     */
    private $_templateID;

    /**
     * @var array
     */
    public $callbacks = [];

    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->_templateID = $_GET['template'];
        $this->setIds(\Yii::$app->request->post($this->attribute));
    }

    /**
     * @return mixed
     */
    public function run(){
        foreach ($this->prepareModels()->all() as $model){
            /* @var $model MainModel */
            $service = new SendHelper(new HistoryHelper(), $this->loadTemplate($model,$model->language));
            $sms = $service->send($model);
            s($sms);
            exit;
            if($sms){
                $model->detachBehaviors();
                $model->is_sms_send = 1;
                $model->update(false);
            }else{
                return s($sms,$service);
                exit;
            }

        }


        return $this->prepareModels()->count();
    }

    /**
     * @param $model MainModel
     * @param $lang string
     * @return static
     */
    public function loadTemplate($model, $lang){

        $template = NotifierTemplates::find()->localized($lang)->where(['id'=>$this->_templateID])->one();

        if($template->label == 'Форма посетителя'){
            $template->message = str_replace( '{{$ticket_url}}', $model->pdfPath, $template->message );
        }

        return $template;

    }

    /**
     * @param $value
     */
    private function setIds($value){
        $this->_ids = $value;
    }

    /**
     * @return mixed
     */
    private function prepareModels(){
        $class = $this->controller->getModelClass();
        $searcher = new $class;
        return $searcher::find()->andFilterWhere(['in','id',$this->_ids]);
    }

}