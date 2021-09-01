<?php
/** @var $generator \ruskush\giiCrudSwagger\Generator */

$modelClassName = $generator->getModelClassName();
echo "<?php\n";
?>

namespace <?=$generator->resourceNamespace?>;

use yii\helpers\ArrayHelper;

class <?=$modelClassName?> extends \<?=$generator->modelClass?> {
    public function fields() {
        return ArrayHelper::merge(parent::fields(), [
            //TODO дополнительные поля
        ]);

        /*
        return [
            //TODO список полей ресурса
        ];
        */
    }
}