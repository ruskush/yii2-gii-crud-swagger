<?php
/**
 * This is the template for generating a CRUD API controller class file with Swagger notifications.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator ruskush\giiCrudSwagger\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : '') ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\CreateAction;
use yii\rest\DeleteAction;
use yii\rest\IndexAction;
use yii\rest\UpdateAction;
use yii\rest\ViewAction;

/**
* <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
*/
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . " {\n" ?>
    public $modelClass = <?= $modelClass ?>::class;

    /**
    * {@inheritdoc}
    */
    public function behaviors(): array {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'list' => ['get'],
                    'create' => ['post'],
                    'update' => ['patch', 'put'],
                    'delete' => ['delete'],
                ],
            ],
        ]);
    }

    public function actions(): array {
        return[
            /**
            * @SWG\Post(path="<?= $generator->getCreateUrl() ?>",
<?php if (!empty($generator->swaggerTag)): ?>
            *     tags={"<?= $generator->swaggerTag ?>"},
<?php endif; ?>
            *     summary="Создать",
            *     security={{"Bearer": {}}},
            * )
            */
            'create' => [
                'class' => CreateAction::class,
                'modelClass' => $this->modelClass,
            ],
            /**
            * @SWG\Post(path="<?= $generator->getViewUrl() ?>",
<?php if (!empty($generator->swaggerTag)): ?>
            *     tags={"<?= $generator->swaggerTag ?>"},
<?php endif; ?>
            *     summary="Просмотр записи",
            *     security={{"Bearer": {}}},
            * )
            */
            'view' => [
                'class' => ViewAction::class,
                'modelClass' => $this->modelClass,
            ],
            /**
            * @SWG\Post(path="<?= $generator->getListUrl() ?>",
<?php if (!empty($generator->swaggerTag)): ?>
            *     tags={"<?= $generator->swaggerTag ?>"},
<?php endif; ?>
            *     summary="Список записей",
            *     security={{"Bearer": {}}},
            * )
            */
            'list' => [
                'class' => IndexAction::class,
                'modelClass' => $this->modelClass,
<?php if (!empty($generator->searchModelClass)): ?>
                'prepareDataProvider' => function ($action) {
                    $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
                    return $searchModel->search(Yii::$app->request->queryParams);
                },
<?php endif; ?>
            ],
            /**
            * @SWG\Post(path="<?= $generator->getUpdateUrl() ?>",
<?php if (!empty($generator->swaggerTag)): ?>
            *     tags={"<?= $generator->swaggerTag ?>"},
<?php endif; ?>
            *     summary="Редактировать",
            *     security={{"Bearer": {}}},
            * )
            */
            'update' => [
                'class' => UpdateAction::class,
                'modelClass' => $this->modelClass,
            ],
            /**
            * @SWG\Post(path="<?= $generator->getDeleteUrl() ?>",
<?php if (!empty($generator->swaggerTag)): ?>
            *     tags={"<?= $generator->swaggerTag ?>"},
<?php endif; ?>
            *     summary="Удалить",
            *     security={{"Bearer": {}}},
            * )
            */
            'delete' => [
                'class' => DeleteAction::class,
                'modelClass' => $this->modelClass,
            ],
        ];
    }
}
