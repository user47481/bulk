<?php
/**
 * Created by PhpStorm.
 * User: Mopkau
 * Date: 12.10.2017
 * Time: 15:28
 */

namespace bulk\widgets;


use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;

class BulkDrop extends Widget
{
    public $actions = [];
    public $model;

    public $bulk_id = 'bulk-actions';
    public $bulk_data_attr = 'data-action';
    public $bulk_checkbox_selector_checked = 'kv-row-checkbox:checkbox:checked';
    public $bulk_checkbox_selector = 'kv-row-checkbox';
    public $bulk_check_all_selector = 'select-on-check-all';
    public $bulk_check_all_selector_checked = 'select-on-check-all:checkbox:checked';
    public $bulk_post_param = 'selection';
    public $bulk_option_attr = 'data-action';

    private $_optionsArray;
    private $_optionsAttrArray;

    public function init()
    {
        parent::init();
        $this->prepare();
    }

    public function run()
    {
        parent::run();

        return \yii\bootstrap\Html::dropDownList($this->bulk_post_param,'',$this->_optionsArray,[
            'id'=>$this->bulk_id,
            'class'=>'form-control col-md-3',
            'style'=>'width: 15%;margin-right: 4px;',
            'disabled'=>true,
            'options'=> $this->_optionsAttrArray,
            'prompt'=>'Выберите действие'
        ]);

    }

    public function prepare(){
        $this->registerScript();
        $this->prepareOptionsArray();
        $this->prepareOptionsAttrArray();
    }

    public function registerScript(){
        $this->getView()->registerJs("
            $('.{$this->bulk_checkbox_selector}').iCheck({checkboxClass: 'icheckbox_square-aero'});
            $('.{$this->bulk_check_all_selector}').iCheck({checkboxClass: 'icheckbox_square-aero'});
            
            $('.{$this->bulk_check_all_selector}').on('ifChecked', function(event){
                $(document).find('.{$this->bulk_checkbox_selector}').iCheck('check')
            });
            
            $('.{$this->bulk_check_all_selector}').on('ifUnchecked', function(event){
                $(document).find('.{$this->bulk_checkbox_selector}').iCheck('uncheck')
            });
            
            
            
            $('.{$this->bulk_checkbox_selector}').on('ifToggled', function(event){
                 if($(document).find('.{$this->bulk_checkbox_selector_checked}:checked').length == 0){
                     $('#{$this->bulk_id}').attr('disabled','disabled');
                 }else{
                     $('#{$this->bulk_id}').removeAttr('disabled');
                 }
            });
        ",View::POS_END,'ch-1');
        $this->getView()->registerJs("
            $('#{$this->bulk_id}').on('change',function(){
                var action = $(this).find(':selected').attr('{$this->bulk_data_attr}');
                var ids = [];
                $('.{$this->bulk_checkbox_selector_checked}').each(function () {
                    ids.push($(this).val());
                });
                $.post( action, {bulk:ids} )
                .done(function() {
                    location.reload();
                })
                .fail(function() {
                    alert( 'Ошибка - смотрите в консоль XHR запросы' );
                }); 
                    
                console.log(ids);
            })
        ",View::POS_END);
    }

    private function prepareOptionsArray(){
        $i = 1;
        foreach($this->actions as $group=>$items){
            foreach ($items as $label=>$action){
                $this->_optionsArray[$group][$i++] = $label;
            }
        }
    }

    private function prepareOptionsAttrArray(){
        $i = 1;
        foreach($this->actions as $group=>$items){
            foreach ($items as $label=>$action){
                $this->_optionsAttrArray[$i++] = [
                    $this->bulk_data_attr => $action
                ];
            }
        }
        return $this->_optionsAttrArray;
    }



}