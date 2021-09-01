<?php
/**
 * This is the template for generating a CRUD API controller class file with Swagger notifications.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator ruskush\giiCrudSwagger\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = $generator->getModelClassName();
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}
$fullModelClassName = $generator->useResourceFile ? $generator->getResourceClassName() : $generator->modelClass;
$listDefinitionClassName = $generator->useCollectionEnvelope ? $generator->getDefinitionCollectionClassName() :
    $modelClass;

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();

$modelFields = $generator->getmodelFields();
$sortEnum = $generator->getSortEnum();
echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use <?= ltrim($fullModelClassName, '\\') ?>;
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
<?php if ($generator->useCollectionEnvelope): ?>
    public $serializer = [
        'class' => \yii\rest\Serializer::class,
        'collectionEnvelope' => '_items',
    ];
<?php endif; ?>

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'list' => ['get'],
                    'view' => ['get'],
                    'create' => ['post'],
                    'update' => ['patch', 'put'],
                    'delete' => ['delete'],
                ],
            ],
        ]);
    }

    public function actions(): array {
        $parentActions = parent::actions();
        $actions = [
            /**
             * @SWG\Post(path="<?= $generator->getCreateUrl() ?>",
<?php if (!empty($generator->swaggerTag)): ?>
             *     tags={"<?= $generator->swaggerTag ?>"},
<?php endif; ?>
             *     summary="Создать",
             *     security={{"Bearer": {}}},
<?php foreach ($modelFields as $field): ?>
<?php $required = $field['required'] ? 'true' : 'false' ?>
             *     @SWG\Parameter(
             *         name="<?=$field['name']?>",
             *         description="<?=$field['comment']?>",
             *         type="<?=$field['type']?>",
             *         in="formData",
             *         required=<?=$required?>,
             *     ),
<?php endforeach; ?>
             *     @SWG\Response(
             *         response = 201,
             *         description="Объект созданной записи",
             *         @SWG\Schema(ref = "#/definitions/<?=$modelClass?>")
             *     )
             * ),
             */
            'create' => [
                'class' => CreateAction::class,
                'modelClass' => $this->modelClass,
            ],
            /**
             * @SWG\Get(path="<?= $generator->getViewUrl() ?>",
<?php if (!empty($generator->swaggerTag)): ?>
             *     tags={"<?= $generator->swaggerTag ?>"},
<?php endif; ?>
             *     summary="Просмотр записи",
             *     security={{"Bearer": {}}},
<?php foreach ($modelFields as $field): ?>
<?php if ($field['isPrimaryKey']): ?>
             *     @SWG\Parameter(
             *         name="<?=$field['name']?>",
             *         description="<?=$field['comment']?>",
             *         type="<?=$field['type']?>",
             *         in="query",
             *         required=true
             *     ),
<?php endif; ?>
<?php endforeach; ?>
             *     @SWG\Response(
             *         response = 200,
             *         description = "Объект записи",
             *         @SWG\Schema(ref = "#/definitions/<?=$modelClass?>")
             *     )
             * )
             */
            'view' => [
                'class' => ViewAction::class,
                'modelClass' => $this->modelClass,
            ],
            /**
             * @SWG\Get(path="<?= $generator->getListUrl() ?>",
<?php if (!empty($generator->swaggerTag)): ?>
             *     tags={"<?= $generator->swaggerTag ?>"},
<?php endif; ?>
             *     summary="Список записей",
             *     security={{"Bearer": {}}},
<?php foreach ($modelFields as $field): ?>
             *     @SWG\Parameter(
             *         name="<?=$field['name']?>",
             *         description="<?=$field['comment']?>",
             *         type="<?=$field['type']?>",
             *         in="query",
             *         required=false,
             *     ),
<?php endforeach; ?>
             *     @SWG\Parameter(
             *         name="page",
             *         description="Страница",
             *         type="integer",
             *         in="query",
             *         required=false,
             *     ),
             *     @SWG\Parameter(
             *         name="per-page",
             *         description="Объектов на странице",
             *         type="integer",
             *         in="query",
             *         required=false,
             *     ),
             *     @SWG\Parameter(
             *         name="sort",
             *         description="Сортировка столбцов",
             *         type="string",
             *         enum={<?=$sortEnum?>},
             *         in="query"
             *     ),
             *     @SWG\Response(
             *         response = 200,
             *         description = "Список объектов",
             *         @SWG\Schema(ref = "#/definitions/<?=$listDefinitionClassName?>")
             *     )
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
             * @SWG\Patch(path="<?= $generator->getUpdateUrl() ?>",
<?php if (!empty($generator->swaggerTag)): ?>
             *     tags={"<?= $generator->swaggerTag ?>"},
<?php endif; ?>
             *     summary="Редактировать",
             *     security={{"Bearer": {}}},
<?php foreach ($modelFields as $field): ?>
<?php $required = $field['required'] ? 'true' : 'false' ?>
             *     @SWG\Parameter(
             *         name="<?=$field['name']?>",
             *         description="<?=$field['comment']?>",
             *         type="<?=$field['type']?>",
<?php if ($field['isPrimaryKey']): ?>
             *         in="query",
             *         required=true
<?php else: ?>
             *         in="formData"
<?php endif; ?>
             *     ),
<?php endforeach; ?>
             *     @SWG\Response(
             *         response = 200,
             *         description="Объект обновлённой записи",
             *         @SWG\Schema(ref = "#/definitions/<?=$modelClass?>")
             *     )
             * )
             */
            'update' => [
                'class' => UpdateAction::class,
                'modelClass' => $this->modelClass,
            ],
            /**
             * @SWG\Delete(path="<?= $generator->getDeleteUrl() ?>",
<?php if (!empty($generator->swaggerTag)): ?>
             *     tags={"<?= $generator->swaggerTag ?>"},
<?php endif; ?>
             *     summary="Удалить",
             *     security={{"Bearer": {}}},
<?php foreach ($modelFields as $field): ?>
<?php if ($field['isPrimaryKey']): ?>
             *     @SWG\Parameter(
             *         name="<?=$field['name']?>",
             *         description="<?=$field['comment']?>",
             *         type="<?=$field['type']?>",
             *         in="query",
             *         required=true
             *     ),
<?php endif; ?>
<?php endforeach; ?>
             *     @SWG\Response(
             *         response = 204,
             *         description = "Запись удалена",
             *     ),
             * )
             */
            'delete' => [
                'class' => DeleteAction::class,
                'modelClass' => $this->modelClass,
            ],
        ];
        return ArrayHelper::merge($parentActions, $actions);
    }
}
