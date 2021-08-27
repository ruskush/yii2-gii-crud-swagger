<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator ruskush\giiCrudSwagger\Generator */
$modulesList = [];
foreach ($generator->getModules() as $module) {
    $modulesList[$module] = $module;
}
$modulesList = array_merge($modulesList, ['' => 'No module']);

echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'controllerClass');
echo $form->field($generator, 'searchModelClass');
echo $form->field($generator, 'swaggerTag');
echo $form->field($generator, 'baseControllerClass');
echo $form->field($generator, 'module')->dropDownList($modulesList);
