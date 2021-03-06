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
    public $pjaxID = 'pjax-id';
    public $bulk_data_attr = 'data-action';
    public $bulk_checkbox_selector_checked = '.kv-row-checkbox:checkbox:checked';
    public $bulk_checkbox_selector = '.kv-row-checkbox';
    public $bulk_check_all_selector = '.select-on-check-all';
    public $bulk_check_all_selector_checked = '.select-on-check-all:checkbox:checked';
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

    private function getUniqueID(){
        return str_replace('-','',$this->bulk_id);
    }

    public function registerScript(){
        $uniq = $this->getUniqueID();
        $this->getView()->registerJs("
                 function init{$this->getUniqueID()}(){
    alertify.defaults.transition = 'slide';
    alertify.defaults.theme.ok = 'btn btn-primary';
    alertify.defaults.theme.cancel = 'btn btn-danger';
    alertify.defaults.theme.input = 'form-control';
    alertify.defaults.glossary.ok = 'Да';
    alertify.defaults.glossary.cancel = 'Отменить';

    $('{$this->bulk_checkbox_selector}').iCheck({checkboxClass: 'icheckbox_square-aero'});
    $('{$this->bulk_check_all_selector}').iCheck({checkboxClass: 'icheckbox_square-aero'});
    
    $('{$this->bulk_check_all_selector}').on('ifChecked', function(event){
        $(document).find('{$this->bulk_checkbox_selector}').iCheck('check')
    });

    $('{$this->bulk_check_all_selector}').on('ifUnchecked', function(event){
        $(document).find('{$this->bulk_checkbox_selector}').iCheck('uncheck')
    });

    $('{$this->bulk_checkbox_selector}').on('ifToggled', function(event){
        if($(document).find('{$this->bulk_checkbox_selector_checked}:checked').length == 0){
            $('#{$this->bulk_id}').attr('disabled','disabled');
        }else{
            $('#{$this->bulk_id}').removeAttr('disabled');
        }
    });
}

init{$this->getUniqueID()}();
bulkChange{$this->getUniqueID()}();

$(document).on('ready pjax:end', function(event) {
    init{$this->getUniqueID()}();
    bulkChange{$this->getUniqueID()}();
});



function bulkChange{$this->getUniqueID()}(){
    $('#{$this->bulk_id}').on('change',function(){

        var action = $(this).find(':selected').attr('{$this->bulk_data_attr}'),
            actionTitle = $('#{$this->bulk_id} option:selected').text(),
            actionOptGroupTitle = $('#{$this->bulk_id} option:selected').parent().attr('label'),
            ids = []
            ;

        $('{$this->bulk_checkbox_selector_checked}').each(function () {
            ids.push($(this).val());
        });


        alertify.confirm(
        'Подтвердите действие | '+actionOptGroupTitle+' | -> | '+actionTitle+' |',
        'Выбрано: '+ ids.length+' ед. '+' Вы уверены?',
        function(){
            alertify.success('Запрос отправлен');
            $.post( action, {bulk:ids} )
                .done(function() {
                    console.log('done');
                    $.pjax.reload('#{$this->pjaxID}');
                    alertify.success('Запрос выполнен успешно');
                })
                .fail(function() {
                    alertify.success('Ошибка - смотрите в консоль XHR запросы');
                });
        },
        function(){
            $('#{$this->bulk_id}').prop('selectedIndex',0);
            alertify.error('Действие отменено')
        }
    );
    });
}
        ",View::POS_END,$this->bulk_id);
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



