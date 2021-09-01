<?php

namespace ruskush\giiCrudSwagger;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\StringHelper;
use yii\rest\Controller;
use yii\db\TableSchema;

class Generator extends \yii\gii\generators\crud\Generator {
    /**
     * @var string Класс контроллера, от которого будет наследоваться создаваемый CRUD контроллер. Имя класса должно
     * включать в себя пространство имён.
     */
    public $baseControllerClass = Controller::class;
    /**
     * @var string Значение параметра tags={} в аннотациях Swagger для группировки эндпоинтов
     */
    public $swaggerTag;
    /**
     * @var string Модуль, в котором создаётся контроллер. Параметр используется для генерации url в аннотациях Swagger
     */
    public $module;
    /**
     * @var string ID компонента приложения для доступа к БД
     */
    public $db = 'db';
    /**
     * @var string Пространство имён, в котором должен сгенерироваться definition-файл Swagger-а. Имя сгенерированного
     * файла будет такое-же, как и у ActiveRecord класса.
     */
    public $swaggerDefinitionsNamespace = 'api\modules\v1\models\definitions';
    /**
     * @var bool Использовать ли сериализатор. Если свойство установлено в true, то данные для запроса list вернуться
     * в виде:
     * ```json
     * {
     *     "items": [
     *         {
     *             "id": 1,
     *             ...
     *         },
     *         {
     *             "id": 2,
     *             ...
     *         },
     *             ...
     *     ],
     *     "_links": {
     *         "self": {
     *             "href": "http://localhost/users?page=1"
     *         },
     *         "next": {
     *             "href": "http://localhost/users?page=2"
     *         },
     *         "last": {
     *             "href": "http://localhost/users?page=50"
     *         }
     *     },
     *     "_meta": {
     *         "totalCount": 1000,
     *         "pageCount": 50,
     *         "currentPage": 1,
     *         "perPage": 20
     *     }
     * }
     * ```
     */
    public $useCollectionEnvelope = true;
    /**
     * @var bool Нужно ли генерировать файл ресурса
     */
    public $useResourceFile = true;
    /**
     * @var string Пространство имён, в котором должен сгенерироваться файл ресурса. Имя сгенерированного
     * файла будет такое-же, как и у ActiveRecord класса
     */
    public $resourceNamespace = 'api\modules\v1\resources';
    /**
     * @var array Массив с данными о полях модели, для которой происходит генерация
     */
    protected $modelFields = [];
    /**
     * @var string Значение параметра enum в аннотации для параметра сортировки
     */
    protected $sortEnum = '';
    /**
     * @var string Имя ActiveRecord класса, для которого происходит генерация (без пространства имён)
     */
    protected $modelClassName = '';
    /**
     * @inheritDoc
     */
    public function getName() {
        return 'CRUD API Swagger';
    }

    public function getDescription() {
        return 'Генерация кода для CRUD API контроллера с аннотациями Swagger';
    }
    public function rules() {
        return array_merge(parent::rules(), [
            [['module', 'swaggerTag'], 'safe'],
            [['swaggerDefinitionsNamespace', 'resourceNamespace'], 'validatePath'],
            [['useCollectionEnvelope', 'useResourceFile'], 'boolean'],
        ]);
    }

    public function validatePath($attribute, $params) {
        $path = Yii::getAlias('@' . str_replace('\\', '/', $this->$attribute));
        if ($path === false) {
            $this->addError($attribute, "The class namespace is invalid: $path");
        } elseif (!is_dir($path)) {
            $this->addError($attribute, "Please make sure the directory exists: $path");
        }
    }

    public function attributeLabels() {
        return array_merge(parent::attributeLabels(), [
            'db' => 'Database Connection ID',
            'swaggerDefinitionsNamespace' => 'Swagger Definitions Namespace',
        ]);
    }

    public function hints() {
        return [
            'db' => 'ID компонента приложения для доступа к БД',
            'swaggerDefinitionsNamespace' => 'Пространство имён, в котором должен сгенерироваться definition-файл 
            Swagger-а. Имя сгенерированного файла будет такое-же, как и у ActiveRecord класса.',
            'useCollectionEnvelope' => 'Возвращать данные для запроса list в виде коллекции (с пагинацией)',
            'useResourceFile' => 'Генерировать файл ресурса',
            'resourceNamespace' => 'Пространство имён, в котором должен сгенерироваться файл ресурса. Имя сгенерированного
            файла будет такое-же, как и у ActiveRecord класса',
            'modelClass' => 'ActiveRecord класс, связанный с таблицей в БД, для которой генерируется CRUD API контроллер. Имя класса
                должно включать в себя пространство имён, например <code>app\models\Post</code>.',
            'controllerClass' => 'Имя контроллера, который должен быть сгенерирован. Имя должно включать в себя 
                пространство имён (например <code>app\controllers\PostController</code>), стиль имени -  CamelCase, 
                заканчиваться должно суффиксом <code>Controller</code>',
            'searchModelClass' => 'Класс search модели, для которой генерируется CRUD API контроллер. Имя класса
                должно включать в себя пространство имён, например <code>app\models\Post</code>',
            'baseControllerClass' => 'Класс контроллера, от которого будет наследоваться создаваемый CRUD контроллер.
            Имя класса должно включать в себя пространство имён, например <code>yii\rest\Controller</code>',
            'module' =>
                'Модуль, в котором создаётся контроллер. Параметр используется для генерации url в аннотациях Swagger.',
            'swaggerTag' => 'Значение параметра <code>tags={}</code> в аннотациях Swagger для группировки эндпоинтов',
        ];
    }

    public function stickyAttributes() {
        return ['baseControllerClass', 'db', 'swaggerDefinitionsNamespace', 'module', 'useResourceFile', 'resourceNamespace'];
    }

    /**
     * @inheritDoc
     */
    public function generate() {
        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');

        /** @var ActiveRecord|string $modelClass */
        $modelClass = $this->modelClass;
        $tableName = $modelClass::tableName();

        /** @var Connection $db */
        $db = Yii::$app->get($this->db, false);
        $tableSchema = $db->getTableSchema($tableName);
        $this->modelFields = $this->generateProperties($tableSchema);
        $this->sortEnum = $this->generateSortEnum($this->modelFields);

        $this->modelClassName = StringHelper::basename($modelClass);
        $definitionDir = str_replace('\\', '/', ltrim($this->swaggerDefinitionsNamespace, '\\'));
        $definitionFile = Yii::getAlias('@' . $definitionDir . '/' . $this->modelClassName .'.php');

        $files = [new CodeFile($definitionFile, $this->render('definition.php'))];

        if ($this->useCollectionEnvelope) {
            $definitionCollectionFile = Yii::getAlias('@' . $definitionDir . '/' . $this->getDefinitionCollectionClassName() .'.php');
            $files[] = new CodeFile($definitionCollectionFile, $this->render('definition_collection.php'));
        }

        if ($this->useResourceFile) {
            $resorceDir = str_replace('\\', '/', ltrim($this->resourceNamespace, '\\'));
            $resorceFile = Yii::getAlias('@' . $resorceDir . '/' . $this->modelClassName .'.php');
            $files[] = new CodeFile($resorceFile, $this->render('resource.php'));
        }

        $files[] = new CodeFile($controllerFile, $this->render('controller.php'));

        if (!empty($this->searchModelClass)) {
            $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
            $files[] = new CodeFile($searchModel, $this->render('search.php'));
        }
        return $files;
    }

    /**
     * @return string[] список модулей в приложении, за исключением модулей gii и debug
     */
    public function getModules(): array {
        $modules = array_keys(Yii::$app->modules);
        $modules = array_flip($modules);
        unset($modules['gii'], $modules['debug']);
        return array_flip($modules);
    }

    /**
     * @return string Url for 'create' action
     */
    public function getCreateUrl(): string {
        return $this->getControllerUrlPart() . '/create';
    }

    /**
     * @return string Url for 'view' action
     */
    public function getViewUrl(): string {
        return $this->getControllerUrlPart() . '/view';
    }

    /**
     * @return string Url for 'list' action
     */
    public function getListUrl(): string {
        return $this->getControllerUrlPart() . '/list';
    }

    /**
     * @return string Url for 'update' action
     */
    public function getUpdateUrl(): string {
        return $this->getControllerUrlPart() . '/update';
    }

    /**
     * @return string Url for 'delete' action
     */
    public function getDeleteUrl(): string {
        return $this->getControllerUrlPart() . '/delete';
    }

    /**
     * @return string Url part with module ID (if exist) and with controller ID
     */
    protected function getControllerUrlPart(): string {
        return '/' . ($this->module ?? '') . '/' . $this->getControllerID();
    }

    protected function generateProperties(TableSchema $table)
    {
        $properties = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_TINYINT:
                    $type = 'integer';
                    break;
                case Schema::TYPE_BOOLEAN:
                    $type = 'boolean';
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $type = 'number';
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                case Schema::TYPE_JSON:
                    $type = 'string';
                    break;
                default:
                    $type = $column->phpType;
            }

            $properties[$column->name] = [
                'type' => $type,
                'name' => $column->name,
                'comment' => $column->comment,
                'required' => !$column->allowNull && is_null($column->defaultValue),
                'isPrimaryKey' => $column->isPrimaryKey,
            ];
        }

        return $properties;
    }

    public function getmodelFields() {
        return $this->modelFields;
    }

    protected function generateSortEnum(array $properties):string {
        $list = '';
        foreach ($properties as $property) {
            $list .= "\"{$property['name']}\", ";
            $list .= "\"-{$property['name']}\", ";
        }
        $list = substr(trim($list), 0, -1);
        return $list;
    }

    /**
     * @return string
     */
    public function getSortEnum() {
        return $this->sortEnum;
    }

    /**
     * @return string Имя ActiveRecord класса, для которого происходит генерация (без пространства имён)
     */
    public function getModelClassName() {
        return $this->modelClassName;
    }

    public function getDefinitionCollectionClassName() {
        return $this->modelClassName . 'Collection';
    }

    public function getResourceClassName() {
        return $this->resourceNamespace . '\\' . $this->modelClassName;
    }
}
